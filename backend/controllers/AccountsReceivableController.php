<?php

namespace backend\controllers;

use backend\components\ApiBCCR;
use backend\models\business\Customer;
use backend\models\business\ItemInvoice;
use backend\models\business\ItemInvoiceSearch;
use backend\models\business\InvoiceAbonos;
use backend\models\business\InvoiceAbonosSearch;
use backend\models\business\PaymentMethodHasInvoice;
use backend\models\business\SellerHasInvoice;
use backend\models\business\PurchaseOrder;
use backend\models\business\CollectorHasInvoice;
use backend\models\business\Product;
use backend\models\nomenclators\BranchOffice;
use backend\models\nomenclators\ConditionSale;
use backend\models\nomenclators\Currency;
use backend\models\nomenclators\UtilsConstants;
use backend\models\nomenclators\PaymentMethod;
use backend\models\settings\Issuer;
use backend\models\settings\Setting;
use common\components\ApiV43\ApiAccess;
use common\components\ApiV43\ApiConsultaHacienda;
use common\components\ApiV43\ApiEnvioHacienda;
use common\components\ApiV43\ApiFirmadoHacienda;
use common\components\ApiV43\ApiXML;
use common\models\EnviarEmailForm;
use common\models\User;
use kartik\mpdf\Pdf;
use Mpdf\QrCode\QrCode;
use Mpdf\QrCode\Output;
use Yii;
use backend\models\business\Invoice;
use backend\models\business\InvoiceSearch;
use yii\helpers\FileHelper;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use common\models\GlobalFunctions;
use yii\db\Exception;
use yii\web\Response;

/**
 * InvoiceController implements the CRUD actions for Invoice model.
 */
class AccountsReceivableController extends Controller
{

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                    'multiple_delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all Invoice models.
     * @return mixed
     */
    public function actionIndex()
    {        
        /*
        $searchModel = new InvoiceSearch();
        $dataProvider = $searchModel->AccountReceivableSearch(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
        */

        $searchModelPendientes = new InvoiceSearch();
        $dataProviderPendientes = $searchModelPendientes->AccountReceivablePendientesSearch(Yii::$app->request->queryParams);

        $searchModelCanceladas = new InvoiceSearch();
        $dataProviderCanceladas = $searchModelCanceladas->AccountReceivableCanceladasSearch(Yii::$app->request->queryParams);

        $searchModelCanceladasNotas = new InvoiceSearch();
        $dataProviderCanceladasNotas = $searchModelCanceladasNotas->AccountReceivableCanceladasNotasSearch(Yii::$app->request->queryParams);

        $searchModelAbonos = new InvoiceSearch();
        $dataProviderAbonos = $searchModelAbonos->AccountReceivableAbonosSearch(Yii::$app->request->queryParams);

        $searchModelAbonosSinpe = new InvoiceSearch();
        $dataProviderAbonosSinpe = $searchModelAbonosSinpe->AccountReceivableAbonosSinpeSearch(Yii::$app->request->queryParams);

        

        return $this->render('index', [
            'searchModelPendientes' => $searchModelPendientes,
            'dataProviderPendientes' => $dataProviderPendientes,
			
            'searchModelCanceladas' => $searchModelCanceladas,
            'dataProviderCanceladas' => $dataProviderCanceladas,

            'searchModelCanceladasNotas' => $searchModelCanceladasNotas,
            'dataProviderCanceladasNotas' => $dataProviderCanceladasNotas,
            
            'searchModelAbonos' => $searchModelAbonos,
            'dataProviderAbonos' => $dataProviderAbonos,

            'searchModelAbonosSinpe' => $searchModelAbonosSinpe,
            'dataProviderAbonosSinpe' => $dataProviderAbonosSinpe,
        ]);        
    }

    /**
     * Displays a single Invoice model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        $searchModelItems = new ItemInvoiceSearch(['invoice_id' => $id]);
        $dataProviderItems = $searchModelItems->search(Yii::$app->request->queryParams);

        $searchModelAbonos = new InvoiceAbonosSearch(['invoice_id' => $id]);        
        $dataProviderAbonos = $searchModelAbonos->search(Yii::$app->request->queryParams);


        $total = sprintf('%0.2f', $model->total_comprobante);
        $abonado = sprintf('%0.2f', InvoiceAbonos::getAbonosByInvoiceID($model->id));
        $pendiente = $total - $abonado;
        $pendiente = sprintf('%0.2f', $pendiente);

        return $this->render('view', [
            'model' => $model,
            'dataProviderItems' => $dataProviderItems,
            'dataProviderAbonos'=>$dataProviderAbonos,
            'total'=>$total,
            'abonado'=>$abonado,
            'pendiente'=>$pendiente,
        ]);
    }

/**
     * Updates an existing Invoice model.
     * If update is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $status_hacienda = (int) $model->status_hacienda;
        $old_ready_to_send = (int) $model->ready_to_send_email;
        $old_email_sent = (int) $model->email_sent;

        /*
        if ($status_hacienda !== UtilsConstants::HACIENDA_STATUS_NOT_SENT) {
            GlobalFunctions::addFlashMessage('danger', Yii::t('backend', 'No es posible actualizar una factura enviada a hacienda'));
            return $this->redirect(['index']);
        }
        */

        if (isset($model) && !empty($model)) {
            $searchModelItems = new ItemInvoiceSearch();
            $searchModelItems->invoice_id = $model->id;
            $dataProviderItems = $searchModelItems->search(Yii::$app->request->queryParams);
            if (is_null($model->status_hacienda)) {
                $model->status_hacienda = UtilsConstants::HACIENDA_STATUS_NOT_SENT;
            }

            //BEGIN payment method has invoice
            $payment_methods_assigned = PaymentMethodHasInvoice::getPaymentMethodByInvoiceId($id);

            $payment_methods_assigned_ids = [];
            foreach ($payment_methods_assigned as $value) {
                $payment_methods_assigned_ids[] = $value['payment_method_id'];
            }

            $model->payment_methods = $payment_methods_assigned_ids;
            //END payment method has invoice


            //BEGIN seller has seller
            $seller_assigned = SellerHasInvoice::getSellerByInvoiceId($id);

            $seller_assigned_ids = [];
            foreach ($seller_assigned as $value) {
                $seller_assigned_ids[] = $value['seller_id'];
            }

            $model->sellers = $seller_assigned_ids;
            //END seller method has seller


            //BEGIN collector has collector
            $collector_assigned = CollectorHasInvoice::getCollectorByInvoiceId($id);

            $collector_assigned_ids = [];
            foreach ($collector_assigned as $value) {
                $collector_assigned_ids[] = $value['collector_id'];
            }

            $model->collectors = $collector_assigned_ids;
            //END seller method has collector
            

            $old_status = (int)$model->status;

            if ($model->load(Yii::$app->request->post())) {
                $transaction = \Yii::$app->db->beginTransaction();
                $total_items = ItemInvoice::find()->where(['invoice_id' => $id])->count();
                $ready_to_send = (int) $model->ready_to_send_email;

                if ($ready_to_send === 1 && $total_items === 0) {
                    $model->ready_to_send_email = 0;
                    $model->addError('ready_to_send_email', 'No es posible marcar como "Lista para enviar" una factura sin items');
                    GlobalFunctions::addFlashMessage('danger', Yii::t('backend', 'No es posible marcar como "Lista para enviar" una factura sin items'));

                    return $this->render('update', [
                        'model' => $model,
                        'searchModelItems' => $searchModelItems,
                        'dataProviderItems' => $dataProviderItems,
                    ]);
                }

                try {
                    PaymentMethodHasInvoice::updateRelation($model, $payment_methods_assigned, 'payment_methods', 'payment_method_id');

                    SellerHasInvoice::updateRelation($model, $seller_assigned, 'sellers', 'seller_id');

                    CollectorHasInvoice::updateRelation($model, $collector_assigned, 'collectors', 'collector_id');

                    if ($model->save()) {
                        $new_status = (int) $model->status;
                        if ($old_status !== $new_status) {
                            $model->verifyStock();
                        }

                        //Enviar factura por correo si aplica
                        if ($old_email_sent === 0 && $old_ready_to_send === 0 && $old_ready_to_send !== $ready_to_send) {
                            $email_model = new EnviarEmailForm();
                            $issuer = Issuer::find()->one();
                            $email_model->id = $model->id;
                            $email_model->de = $issuer->email;
                            $email_model->para = $model->customer->email;
                            $email_model->nombrearchivo = $model->key . '.pdf';
                            $email_model->asunto = 'Envío de Factura Electrónica';
                            $response = $this->enviareamil($email_model, $model);

                            $model->email_sent = 1;
                            $model->save();
                        }

                        $transaction->commit();

                        GlobalFunctions::addFlashMessage('success', Yii::t('backend', 'Elemento actualizado correctamente'));

                        return $this->redirect(['index']);
                    } else {
                        GlobalFunctions::addFlashMessage('danger', Yii::t('backend', 'Error actualizando el elemento'));
                    }
                } catch (Exception $e) {
                    GlobalFunctions::addFlashMessage('danger', Yii::t('backend', 'Error, ha ocurrido una excepción actualizando el elemento'));
                    $transaction->rollBack();
                }
            }
        } else {
            GlobalFunctions::addFlashMessage('warning', Yii::t('backend', 'El elemento buscado no existe'));
        }

        return $this->render('update', [
            'model' => $model,
            'searchModelItems' => $searchModelItems,
            'dataProviderItems' => $dataProviderItems,
        ]);
    }   

    /**
     * Creates a new Invoice model.
     * If creation is successful, the browser will be redirected to the 'index' page.
     * @return mixed
     */
    public function actionAddAbono($id)
    {
        $invoice = $this->findModel($id);
        $model = new InvoiceAbonos;
        $model->invoice_id = $invoice->id;
        $model->emission_date = date('Y-m-d');
        $abonos = InvoiceAbonos::find()->where(['invoice_id'=>$invoice->id])->orderBy('emission_date ASC')->all();

        $total = sprintf('%0.2f', $invoice->total_comprobante);
        $abonado = sprintf('%0.2f', InvoiceAbonos::getAbonosByInvoiceID($invoice->id));
        $pendiente = $total - $abonado;
        $pendiente = sprintf('%0.2f', $pendiente);
        $model->amount = $pendiente;
        if ($model->load(Yii::$app->request->post())) {
            $transaction = \Yii::$app->db->beginTransaction();
            try {                
                if ($model->save()) {
                    $totalAbonado = sprintf('%0.2f', $abonado + $model->amount);                    
                    if ($total == $totalAbonado){

                        $invoice->status = UtilsConstants::INVOICE_STATUS_CANCELLED;
                        $invoice->save();
                    }
       
                    $transaction->commit();
                    GlobalFunctions::addFlashMessage('success', Yii::t('backend', 'El Abono se ha realizado satisfactoriamente'));

                    return $this->redirect(['index']);
                } else {
                    GlobalFunctions::addFlashMessage('danger', Yii::t('backend', 'Error actualizando el elemento'));
                }
            } catch (Exception $e) {
                $transaction->rollBack();
                GlobalFunctions::addFlashMessage('danger', Yii::t('backend', 'Error, ha ocurrido una excepción actualizando el elemento'));
            }            
        }

        return $this->render('_abono', [
            'model' => $model,
            'abonos'=>$abonos,
            'invoice'=>$invoice,
            'total'=>$total,
            'abonado'=> $abonado,
            'pendiente'=> $pendiente,
        ]);
    }

    /**
     * Finds the Invoice model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Invoice the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Invoice::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('backend', 'La página solicitada no existe'));
        }
    }

    public function actionEstadoCuentaPdf($ids)
    {
        return $this->printPdf($ids);
    }    

    
    public function printPdf($ids, $destino = 'browser')
    {
        $logo = "<img src=\"" . Setting::getUrlLogoBySettingAndType(1, Setting::SETTING_ID) . "\" width=\"100\"/>";
        $configuracion = Setting::find()->where(['id' => 1])->one();
        $textCuentas = $configuracion->bank_information;
        $issuer = \backend\models\settings\Issuer::find()->one();

        if (!is_array($ids) && !empty($ids))
            $ids = explode(',', $ids);

        $invoices = $query = Invoice::find()->select('invoice.*, CAST(NOW() AS date) - CAST(emission_date AS date) as dias_trascurridos, 
                                                     (CAST(CAST(NOW() AS date) - CAST(emission_date AS date) as integer)) - CAST(credit_days.name AS INTEGER) AS dias_vencidos')
                                            ->join('INNER JOIN', 'credit_days', 'invoice.credit_days_id = credit_days.id')
                                            ->Where([
                                            'condition_sale_id' => ConditionSale::CREDITO,
                                            'status_hacienda'=> UtilsConstants::HACIENDA_STATUS_ACCEPTED,
                                            ])
                                            ->andWhere(['invoice.id'=>$ids])
                                            ->orderBy('status_cuenta_cobrar ASC, dias_vencidos DESC')
                                            ->all();


        $data = $this->renderPartial('_cuentas_cobrar_pdf', [
            'invoices' => $invoices,
            'logo' => $logo,
            'original' => true,
            'textCuentas' => $textCuentas,
            'issuer'=>$issuer,
        ]);
        
        if ($destino == 'browser') {
            Yii::$app->response->format = \yii\web\Response::FORMAT_RAW;
            $pdf = new Pdf([
                'mode' => Pdf::MODE_CORE,
                //'orientation' => Pdf::ORIENT_LANDSCAPE,
                'orientation' => Pdf::ORIENT_PORTRAIT, // ORIENT_PORTRAIT
                'destination' => Pdf::DEST_BROWSER,
                'content' => $data,
                'filename' => "cuentas-por-cobrar.pdf",
                'options' => [
                    // any mpdf options you wish to set
                    'title' => 'Cuentas por cobrar',
                    'defaultheaderline' => 0,
                    //'default_font' => 'Calibri',
                    'setAutoTopMargin' => 'stretch',
                    'showWatermarkText' => false,
                ],
                'methods' => [
                    'SetTitle' => Yii::t('backend', 'Cuentas por Cobrar'),
                    'SetSubject' => 'Generating PDF files via yii2-mpdf extension has never been easy',
                    //'SetFooter' => ['|'.Yii::t('backend','Página').' {PAGENO}|'],
                ],
            ]);                
            $pdf->marginTop = 5;
            $pdf->marginBottom = 0;
            $pdf->marginHeader = 0;
            $pdf->marginFooter = 0;
            $pdf->defaultFontSize = 12;
    
            return $pdf->render();            
        } else {
            if (!file_exists("uploads/digital_invoices/") || !is_dir("uploads/digital_invoices/")) {
                try {
                    FileHelper::createDirectory("uploads/digital_invoices/", 0777);
                } catch (\Exception $exception) {
                    Yii::info("Error handling Factura folder resources");
                }
            }

            $filename = 'cuentas-por-cobrar.pdf';
            $file_pdf_save = Yii::getAlias('@backend') . '/web/uploads/digital_invoices/' . $filename;

            $pdf = new Pdf([
                'mode' => Pdf::MODE_CORE,
                'destination' => Pdf::DEST_FILE,
                'content' => $data,
                'filename' => $file_pdf_save,
                'options' => [
                    // any mpdf options you wish to set
                    'title' => 'Cuentas por Cobrar',
                    'defaultheaderline' => 0,
                    //'default_font' => 'Calibri',
                    'setAutoTopMargin' => 'stretch',
                    'showWatermarkText' => false,
                ],
                'methods' => [
                    'SetTitle' => Yii::t('backend', 'Cuentas por Cobrar'),
                    'SetWatermarkText' => '',
                    'SetSubject' => 'Generating PDF files via yii2-mpdf extension has never been easy',
                    'SetFooter' => ['|' . Yii::t('backend', 'Página') . ' {PAGENO}|'],
                ],
            ]);
            $pdf->render();

            return $file_pdf_save;
        }
    }

}
