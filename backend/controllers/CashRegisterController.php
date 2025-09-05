<?php

namespace backend\controllers;

use Yii;
use backend\models\business\Invoice;
use backend\models\business\CashRegister;
use backend\models\business\CashRegisteSearch;
use backend\models\business\MovementCashRegister;
use backend\models\business\MovementCashRegisterDetail;
use backend\models\business\MovementCashRegisterDetailSearch;
use backend\models\nomenclators\Boxes;
use backend\models\nomenclators\CoinDenominations;
use backend\models\nomenclators\MovementTypes;
use backend\models\settings\Setting;
use common\models\User;
use common\models\GlobalFunctions;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\db\Exception;
use yii\web\Response;
use kartik\mpdf\Pdf;
use Mpdf\QrCode\QrCode;

/**
 * CashRegisterController implements the CRUD actions for CashRegister model.
 */
class CashRegisterController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

  /**
     * Creates a new CashRegister model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionOpenBox($box_id)
    {
        if ($this->cajaAbierta($box_id))
        {
            GlobalFunctions::addFlashMessage('danger', Yii::t('backend', 'Error, La Caja seleccionada se encuentra abierta, por favor cierrela e inténtelo de nuevo'));            
            return $this->redirect(['arqueo', 'box_id' => $box_id]);
        }

        $model = new CashRegister();
        $coins = CoinDenominations::find()->asArray()->orderBy('value DESC')->all();
        $box = Boxes::find()->where(['id' => $box_id])->one();
        $model->opening_date = date('Y-m-d');
        $model->opening_time = date('H:i:s');
        $model->initial_amount = 0;
        $model->seller_id = Yii::$app->user->id;
        if (!is_null($box))
            $model->branch_office_id = $box->branch_office_id;
        $model->box_id = $box_id;
     
        if ($model->load(Yii::$app->request->post())) {
            $transaction = \Yii::$app->db->beginTransaction();
            try {
                if ($model->save()) {
                    $movimiento = new MovementCashRegister;
                    $movimiento->cash_register_id = $model->id;
                    $movimiento->movement_type_id = MovementTypes::APERTURA_CAJA;
                    $movimiento->movement_date = date('Y-m-d');
                    $movimiento->movement_time = date('H:i:s');
                    $initial_amount = 0;
                    if ($movimiento->save()) {
                        $efectivo = Yii::$app->request->post()['efectivo'];
                        foreach ($efectivo as $e) {
                            if ($e['count'] > 0) {
                                $detail = new MovementCashRegisterDetail;
                                $detail->movement_cash_register_id = $movimiento->id;
                                $detail->value = $e['value'];
                                $detail->count = $e['count'];
                                $detail->comment = $e['description'];
                                $detail->coin_denomination_id = $e['denominations_id'];
                                $detail->save();
                                $initial_amount += $e['value'] * $e['count'];
                            }
                        }
                    }
                    $model->initial_amount = $initial_amount;
                    $model->save();
                    $transaction->commit();
                }
            } catch (Exception $e) {
                GlobalFunctions::addFlashMessage('danger', Yii::t('backend', 'Error, ha ocurrido una excepción abriendo la caja'));
                $transaction->rollBack();
            }

            GlobalFunctions::addFlashMessage('success', Yii::t('backend', 'Información. Se ha abierto la caja satisfactoriamente'));            
            return $this->redirect(['arqueo', 'box_id' => $box_id]);            
        }

        return $this->render('open_box', [
            'model' => $model,
            'box_id' => $box_id,
            'coins' => $coins,
        ]);
    }

    /**
     * Updates an existing CashRegister model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdateArqueo($id)
    {
        $model = $this->findModel($id);
        $movimiento = MovementCashRegister::find()->where(['cash_register_id' => $model->id])->one();
        $movimiento_detail = CoinDenominations::find()->select("coin_denominations.id, coin_denominations.description,  
                                                    coin_denominations.value,
                                                    movement_cash_register_detail.id as movement_cash_register_detail_id,
                                                    movement_cash_register_detail.movement_cash_register_id,
                                                    movement_cash_register_detail.count, 
                                                    movement_cash_register_detail.comment,
                                                    movement_cash_register_detail.coin_denomination_id")
            ->join('LEFT JOIN', "movement_cash_register", "movement_cash_register.cash_register_id = " . $model->id . " AND 
                                    movement_cash_register.movement_type_id = " . MovementTypes::APERTURA_CAJA . "")
            ->join('LEFT JOIN', "movement_cash_register_detail", "movement_cash_register_detail.movement_cash_register_id = movement_cash_register.id AND 
                                    movement_cash_register_detail.coin_denomination_id = coin_denominations.id")
            ->where(['<>', 'coin_denominations.id', 17])
            ->orderBy("coin_denominations.value DESC")
            ->all();         

        $box = Boxes::find()->where(['id' => $model->box_id])->one();
        if (!is_null($box))
            $model->branch_office_id = $box->branch_office_id;

           // die(var_dump($model));
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            $transaction = \Yii::$app->db->beginTransaction();
            try {
                if ($model->save()) {
                    $initial_amount = 0;
                    if ($movimiento->save()) {
                        $efectivo = Yii::$app->request->post()['efectivo'];
                        foreach ($efectivo as $e) {
                            if ($e['count'] > 0) {
                                if (is_null($e['movement_cash_register_detail_id']) || empty($e['movement_cash_register_detail_id']))
                                    $detail = new MovementCashRegisterDetail;
                                else
                                    $detail = MovementCashRegisterDetail::find()->where(['id'=>$e['movement_cash_register_detail_id']])->one();
                                $detail->movement_cash_register_id = $movimiento->id;   
                                $detail->value = $e['value'];
                                $detail->count = $e['count'];
                                $detail->comment = $e['description'];
                                $detail->coin_denomination_id = $e['coin_denomination_id'];
                                $detail->save();                                    
                                $initial_amount += $e['value'] * $e['count'];
                            }
                        }
                    }
                    $model->initial_amount = $initial_amount;
                    $model->save();
                    $transaction->commit();
                }
            } catch (Exception $e) {
                $transaction->rollBack();
                GlobalFunctions::addFlashMessage('danger', Yii::t('backend', 'Error, ha ocurrido una excepción abriendo la caja'));                
            }
            GlobalFunctions::addFlashMessage('success',Yii::t('backend','Elemento actualizado correctamente'));
            return $this->redirect(['update-arqueo', 'id' => $model->id]);
        }

        return $this->render('update', [            
            'model' => $model,
            'movimiento' => $movimiento,
            'movimiento_detail' => $movimiento_detail,
        ]);
    }

    public function actionArqueo($box_id)
    {
        $box = Boxes::find()->where(['id' => $box_id])->one();
        $searchModel = new CashRegisteSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, $box_id);

        return $this->render('arqueo', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'box' => $box,
        ]);
    }

    public function actionAdicionar($cash_register_id, $box_id)
    {
        $box = Boxes::find()->where(['id' => $box_id])->one();
        $movement_type_id = MovementTypes::ENTRADA_EFECTIVO;
        $titulo = 'Entrada de efectivo';
        $model = new MovementCashRegisterDetail;

        if ($model->load(Yii::$app->request->post())) {
            $transaction = \Yii::$app->db->beginTransaction();
            try {
                $movement = new MovementCashRegister;
                $movement->cash_register_id = Yii::$app->request->post()['cash_register_id'];
                $movement->movement_type_id = Yii::$app->request->post()['movement_type_id'];
                $movement->movement_date = date('Y-m-d');
                $movement->movement_time = date('h:i:s');
                if ($movement->save(false)){
                    $model->movement_cash_register_id = $movement->id;
                    if ($model->save()){
                        $transaction->commit();
                        GlobalFunctions::addFlashMessage('success',Yii::t('backend','Información. Se ha registrado la entrada de efectivo'));
                    }
                    else
                        GlobalFunctions::addFlashMessage('danger', Yii::t('backend', 'Error, ha ocurrido una excepción'));                
                }                    
                return $this->redirect(['arqueo', 'box_id' => $box_id]);                    

            } catch (Exception $e) {
                $transaction->rollBack();
                GlobalFunctions::addFlashMessage('danger', Yii::t('backend', 'Error, ha ocurrido una excepción'));                
            }
        }
        
        $searchModel = new MovementCashRegisterDetailSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, $cash_register_id, $movement_type_id);

        return $this->render('_movimientos', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'cash_register_id'=>$cash_register_id,
            'box' => $box,
            'movement_type_id' => $movement_type_id,
            'titulo'=>$titulo,
            'model'=>$model,
        ]);
    }

    public function actionRetirar($cash_register_id, $box_id)
    {
        $box = Boxes::find()->where(['id' => $box_id])->one();
        $movement_type_id = MovementTypes::SALIDA_EFECTIVO;
        $titulo = 'Salida de efectivo';
        $model = new MovementCashRegisterDetail;

        if ($model->load(Yii::$app->request->post())) {
            $transaction = \Yii::$app->db->beginTransaction();
            try {
                $movement = new MovementCashRegister;
                $movement->cash_register_id = Yii::$app->request->post()['cash_register_id'];
                $movement->movement_type_id = Yii::$app->request->post()['movement_type_id'];
                $movement->movement_date = date('Y-m-d');
                $movement->movement_time = date('h:i:s');
                if ($movement->save(false)){
                    $model->movement_cash_register_id = $movement->id;
                    if ($model->save()){
                        $transaction->commit();
                        GlobalFunctions::addFlashMessage('success',Yii::t('backend','Información. Se ha registrado la salida de efectivo'));
                    }
                    else
                        GlobalFunctions::addFlashMessage('danger', Yii::t('backend', 'Error, ha ocurrido una excepción'));                
                }     
                return $this->redirect(['arqueo', 'box_id' => $box_id]);                    

            } catch (Exception $e) {
                $transaction->rollBack();
                GlobalFunctions::addFlashMessage('danger', Yii::t('backend', 'Error, ha ocurrido una excepción'));                
            }
        }
        
        $searchModel = new MovementCashRegisterDetailSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, $cash_register_id, $movement_type_id);

        return $this->render('_movimientos', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'cash_register_id'=>$cash_register_id,
            'box' => $box,
            'movement_type_id' => $movement_type_id,
            'titulo'=>$titulo,
            'model'=>$model,
        ]);
    }    

    public function cajaAbierta($box_id)
    {
        return CashRegister::cajaAbierta($box_id);
    }

    /**
     * Deletes an existing CashRegister model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the CashRegister model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return CashRegister the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = CashRegister::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    public function actionViewCashOpeningReport($cash_register_id)
    {        
        $setting = Setting::find()->where(['id'=>1])->one();
        $cashRegister = $this->findModel($cash_register_id);
        $movimiento = MovementCashRegister::find()->where(['cash_register_id' => $cashRegister->id, 'movement_type_id'=>MovementTypes::APERTURA_CAJA])->one();
        $movimiento_detail = MovementCashRegisterDetail::find()->where(['movement_cash_register_id'=>$movimiento->id])->all();

        $pivot = $max_dynamic_height = 105;
        $dynamic_height = $pivot;
        $total_items = count($movimiento_detail);
        $dynamic_height += ($total_items*8);
        if($dynamic_height > $max_dynamic_height)
        {
            $max_dynamic_height = $dynamic_height;
        }

        $data = $this->renderPartial('_cash_open_pdf', [
            'setting'=> $setting,
            'cashRegister'=>$cashRegister,
            'movimiento' => $movimiento,
            'movimiento_detail' => $movimiento_detail,
        ]);

        $filename = 'apertura_caja-'.$cashRegister->box->name.'.pdf';
        Yii::$app->response->format = \yii\web\Response::FORMAT_RAW;
        $pdf = new Pdf([
            'mode' => Pdf::MODE_CORE,
            'destination' => Pdf::DEST_BROWSER,
            'content' => $data,
            'filename' => $filename,
            'options' => [
                // any mpdf options you wish to set
                'title' => 'Apertura de Caja: '.$cashRegister->box->name,
                'defaultheaderline' => 0,
                //'default_font' => 'Calibri',
                'setAutoTopMargin' => 'stretch',
                'showWatermarkText' => false,
            ],
            'methods' => [
                'SetTitle' => Yii::t('backend','Apertura de Caja'),
                'SetSubject' => 'Generating PDF files via yii2-mpdf extension has never been easy',
            ],
        ]);

        $pdf->format = [76.2,$max_dynamic_height];
        $pdf->marginLeft = 2.5;
        $pdf->marginRight = 2.5;
        $pdf->marginTop = 5;
        $pdf->marginBottom = 0;
        $pdf->marginHeader = 0;
        $pdf->marginFooter = 0;
        $pdf->defaultFontSize = 13;

        return $pdf->render();       
    }
    
    public function actionViewCashCloseReport($cash_register_id)
    {        
        $setting = Setting::find()->where(['id'=>1])->one();
        $user = User::find()->where(['id' => Yii::$app->user->id])->one();
        /*
        $cashRegister = $this->findModel($cash_register_id);
        $ventas = Invoice::getTotalVentasByMetodoPago($cashRegister->box->id, $cashRegister->opening_date, $cashRegister->opening_time, $cashRegister->seller_id);

        // Obtener el monto total de efectivo adicionado
        $monto_adicionado = MovementCashRegisterDetail::getMontoMovimiento($cashRegister->id, MovementTypes::ENTRADA_EFECTIVO);
        // Obtener el monto total de efectivo retirado
        $monto_retirado = MovementCashRegisterDetail::getMontoMovimiento($cashRegister->id, MovementTypes::SALIDA_EFECTIVO);


        //$movimiento = MovementCashRegister::find()->where(['cash_register_id' => $cashRegister->id, 'movement_type_id'=>MovementTypes::CIERRE_CAJA])->one();
        //$movimiento_detail = MovementCashRegisterDetail::find()->where(['movement_cash_register_id'=>$movimiento->id])->all();
        */

        $cashRegister = CashRegister::find()->where(['id' => $cash_register_id])->one();
        $pago_efectivo = 0;
        $pago_tarjeta = 0;
        $entrada_efectivo = 0;
        $salida_efectivo = 0;
        $apertura_efectivo = 0;

        if (!is_null($cashRegister))
        {
            $apertura_efectivo = MovementCashRegisterDetail::find()->join('INNER JOIN', 'movement_cash_register', "movement_cash_register_detail.movement_cash_register_id = movement_cash_register.id AND 
                                                                    movement_cash_register.movement_type_id = " . MovementTypes::APERTURA_CAJA . " ")
                ->where(['movement_cash_register.cash_register_id' => $cashRegister->id])
                ->sum('movement_cash_register_detail.value * movement_cash_register_detail.count');

            $entrada_efectivo = MovementCashRegisterDetail::find()->join('INNER JOIN', 'movement_cash_register', "movement_cash_register_detail.movement_cash_register_id = movement_cash_register.id AND 
                                                                    movement_cash_register.movement_type_id = " . MovementTypes::ENTRADA_EFECTIVO . " ")
                ->where(['movement_cash_register.cash_register_id' => $cashRegister->id])
                ->sum('movement_cash_register_detail.value * movement_cash_register_detail.count');

            $salida_efectivo = MovementCashRegisterDetail::find()->join('INNER JOIN', 'movement_cash_register', "movement_cash_register_detail.movement_cash_register_id = movement_cash_register.id AND 
                                                                        movement_cash_register.movement_type_id = " . MovementTypes::SALIDA_EFECTIVO . " ")
                ->where(['movement_cash_register.cash_register_id' => $cashRegister->id])
                ->sum('movement_cash_register_detail.value * movement_cash_register_detail.count');

            $apertura_efectivo = (is_null($apertura_efectivo) || empty($apertura_efectivo)) ? 0 : $apertura_efectivo;
            $entrada_efectivo = (is_null($entrada_efectivo) || empty($entrada_efectivo)) ? 0 : $entrada_efectivo;
            $salida_efectivo = (is_null($salida_efectivo) || empty($salida_efectivo)) ? 0 : $salida_efectivo;

            $monto_efectivo = $apertura_efectivo + $entrada_efectivo - $salida_efectivo;

            //$opendate = $cashRegister->opening_date.' '.$cashRegister->opening_time;
            //$actualdate = date('Y-m-d H:s:i');

            $subquery = MovementCashRegisterDetail::find()->select("DISTINCT(movement_cash_register_detail.invoice_id)")
                ->join('INNER JOIN', 'movement_cash_register', "movement_cash_register_detail.movement_cash_register_id = movement_cash_register.id AND 
                                                movement_cash_register.movement_type_id = " . MovementTypes::VENTA . " ")
                ->where(['movement_cash_register.cash_register_id' => $cashRegister->id]);

            $pago_efectivo = Invoice::find()->join('INNER JOIN', 'payment_method_has_invoice', 'payment_method_has_invoice.invoice_id = invoice.id AND payment_method_id = 1')
                ->where(['box_id' => $user->box_id, 'id' => $subquery])
                //->andWhere(['and', "emission_date >= '".$opendate."'",  "emission_date <= '".$actualdate."'"])
                ->sum('total_comprobante');

            $pago_tarjeta = Invoice::find()->join('INNER JOIN', 'payment_method_has_invoice', 'payment_method_has_invoice.invoice_id = invoice.id AND payment_method_id = 2')
                ->where(['box_id' => $user->box_id, 'id' => $subquery])
                //->andWhere(['and', "emission_date >= '".$opendate."'",  "emission_date <= '".$actualdate."'"])
                ->sum('total_comprobante');

            $pago_cheques = Invoice::find()->join('INNER JOIN', 'payment_method_has_invoice', 'payment_method_has_invoice.invoice_id = invoice.id AND payment_method_id = 3')
                ->where(['box_id' => $user->box_id, 'id' => $subquery])
                //->andWere(['and', "emission_date >= '".$opendate."'",  "emission_date <= '".$actualdate."'"])
                ->sum('total_comprobante');

            $pago_transferencia = Invoice::find()->join('INNER JOIN', 'payment_method_has_invoice', 'payment_method_has_invoice.invoice_id = invoice.id AND payment_method_id = 4')
                ->where(['box_id' => $user->box_id, 'id' => $subquery])
                //->andWere(['and', "emission_date >= '".$opendate."'",  "emission_date <= '".$actualdate."'"])
                ->sum('total_comprobante');                
                

            if (is_null($pago_efectivo) || empty($pago_efectivo))
                $pago_efectivo = 0;

            if (is_null($pago_tarjeta) || empty($pago_tarjeta))
                $pago_tarjeta = 0;

            if (is_null($pago_cheques) || empty($pago_cheques))
                $pago_cheques = 0;
                
            if (is_null($pago_transferencia) || empty($pago_transferencia))
                $pago_transferencia = 0;                
        }


        $pivot = $max_dynamic_height = 120;
        $dynamic_height = $pivot;
        $total_items = 10;
        $dynamic_height += ($total_items*8);
        if($dynamic_height > $max_dynamic_height)
        {
            $max_dynamic_height = $dynamic_height;
        }

        $data = $this->renderPartial('_cash_close_pdf', [
            'setting'=> $setting,
            'cashRegister'=>$cashRegister,
            'pago_efectivo'=>$pago_efectivo,
            'pago_tarjeta'=>$pago_tarjeta,
            'pago_cheques'=>$pago_cheques,
            'pago_transferencia'=>$pago_transferencia,
            'monto_adicionado'=>$entrada_efectivo,
            'monto_retirado'=>$salida_efectivo,
            //'movimiento' => $movimiento,
            //'movimiento_detail' => $movimiento_detail,
        ]);

        $filename = 'cierre_caja-'.$cashRegister->box->name.'.pdf';
        Yii::$app->response->format = \yii\web\Response::FORMAT_RAW;
        $pdf = new Pdf([
            'mode' => Pdf::MODE_CORE,
            'destination' => Pdf::DEST_BROWSER,
            'content' => $data,
            'filename' => $filename,
            'options' => [
                // any mpdf options you wish to set
                'title' => 'Cierre de Caja: '.$cashRegister->box->name,
                'defaultheaderline' => 0,
                //'default_font' => 'Calibri',
                'setAutoTopMargin' => 'stretch',
                'showWatermarkText' => false,
            ],
            'methods' => [
                'SetTitle' => Yii::t('backend','Cierre de Caja'),
                'SetSubject' => 'Generating PDF files via yii2-mpdf extension has never been easy',
            ],
        ]);

        $pdf->format = [76.2,$max_dynamic_height];
        $pdf->marginLeft = 2.5;
        $pdf->marginRight = 2.5;
        $pdf->marginTop = 5;
        $pdf->marginBottom = 0;
        $pdf->marginHeader = 0;
        $pdf->marginFooter = 0;
        $pdf->defaultFontSize = 13;

        return $pdf->render();       
    }    

    public function actionCerrar($cash_register_id)
    {
        $cashRegister = CashRegister::find()->where(['id' => $cash_register_id])->one();
        $movement_type_id = MovementTypes::CIERRE_CAJA;
        $titulo = 'Cierre de Caja';

        $movement = MovementCashRegister::find()->where(['cash_register_id'=>$cash_register_id, 'movement_type_id'=>MovementTypes::CIERRE_CAJA])->one();

        if (!is_null($movement)){
            $model = MovementCashRegisterDetail::find()->where(['movement_cash_register_id'=>$movement->id])->one();
        }
        else
        {
            $model = new MovementCashRegisterDetail;
            $movement = new MovementCashRegister;
        }

        // Obtener el monto inicial de la apertura de caja 
        $model->monto_inicial = MovementCashRegisterDetail::getMontoMovimiento($cashRegister->id, MovementTypes::APERTURA_CAJA);
        // Obtener el monto total de efectivo adicionado
        $model->monto_adicionado = MovementCashRegisterDetail::getMontoMovimiento($cashRegister->id, MovementTypes::ENTRADA_EFECTIVO);
        // Obtener el monto total de efectivo retirado
        $model->monto_retirado = MovementCashRegisterDetail::getMontoMovimiento($cashRegister->id, MovementTypes::SALIDA_EFECTIVO);

        $model->total_ventas = Invoice::getTotalVentas($cashRegister->box->id, $cashRegister->opening_date, $cashRegister->opening_time, $cashRegister->seller_id);

        //die(var_dump($model->monto_inicial));
        $model->monto_a_entregar = $model->monto_inicial + $model->monto_adicionado - $model->monto_retirado + $model->total_ventas;


        if ($model->load(Yii::$app->request->post())) {
            $transaction = \Yii::$app->db->beginTransaction();
            try {
                if (isset(Yii::$app->request->post()['movement_cash_register_id']) && Yii::$app->request->post()['movement_cash_register_id'] > 0) // Para saber si hay que actualizar o crear
                {
                    $movement = MovementCashRegister::find()->where(['id'=>Yii::$app->request->post()['movement_cash_register_id']])->one();
                    $model = MovementCashRegisterDetail::find()->where(['movement_cash_register_id'=>$movement->id])->one();
                }
                else
                    $movement = new MovementCashRegister;
                
                $movement->cash_register_id = Yii::$app->request->post()['cash_register_id'];
                $movement->movement_type_id = Yii::$app->request->post()['movement_type_id'];
                $movement->movement_date = date('Y-m-d');
                $movement->movement_time = date('h:i:s');
                if ($movement->save(false)){
                    $model->movement_cash_register_id = $movement->id;
                    if ($model->save()){
                        $model->total_ventas = Invoice::getTotalVentas($cashRegister->box->id, $cashRegister->opening_date, $cashRegister->opening_time, $cashRegister->seller_id);
                        $cashRegister->status = false; // Cierre de caja
                        $cashRegister->closing_date = date('Y-m-d');
                        $cashRegister->closing_time = date('h:i:s');
                        $cashRegister->end_amount = $model->value;
                        $cashRegister->total_sales = $model->total_ventas;
                        $cashRegister->save();

                        $transaction->commit();
                        GlobalFunctions::addFlashMessage('success', Yii::t('backend', 'Sea ha cerrado la caja satisfactoriamente'));                                        
                    }
                    else
                        GlobalFunctions::addFlashMessage('danger', Yii::t('backend', 'Error, ha ocurrido una excepción'));                
                }  
                return $this->redirect(['arqueo', 'box_id' => $cashRegister->box_id]);         

            } catch (Exception $e) {
                $transaction->rollBack();
                GlobalFunctions::addFlashMessage('danger', Yii::t('backend', 'Error, ha ocurrido una excepción'));                
            }
        }

        return $this->render('_cierre_caja', [
            'cashRegister'=>$cashRegister,
            'movement_type_id' => $movement_type_id,
            'movement'=> $movement,
            'titulo'=>$titulo,
            'model'=>$model,
        ]);
    }    
  }
