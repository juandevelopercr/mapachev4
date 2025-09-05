<?php

namespace backend\controllers;

use backend\components\ApiBCCR;
use backend\models\business\Customer;
use backend\models\business\ItemInvoice;
use backend\models\business\AccountsPayableSearch;
use backend\models\business\AccountsPayableAbonosSearch;
use backend\models\business\AccountsPayableAbonos;
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
use backend\models\business\AccountsPayable;
use yii\helpers\FileHelper;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use common\models\GlobalFunctions;
use yii\db\Exception;
use yii\web\Response;

/**
 * InvoiceController implements the CRUD actions for AccountsPayable model.
 */
class AccountsPayableController extends Controller
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
     * Lists all AccountsPayable models.
     * @return mixed
     */
    public function actionIndex()
    {        
        /*
        $searchModel = new AccountsPayableSearch();
        $dataProvider = $searchModel->AccountReceivableSearch(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
        */

        $searchModelPendientes = new AccountsPayableSearch();
        $dataProviderPendientes = $searchModelPendientes->AccountReceivablePendientesSearch(Yii::$app->request->queryParams);

        
        $searchModelCanceladas = new AccountsPayableSearch();
        $dataProviderCanceladas = $searchModelCanceladas->AccountReceivableCanceladasSearch(Yii::$app->request->queryParams);

        /*
        $searchModelCanceladasNotas = new AccountsPayableSearch();
        $dataProviderCanceladasNotas = $searchModelCanceladasNotas->AccountReceivableCanceladasNotasSearch(Yii::$app->request->queryParams);

        $searchModelAbonos = new AccountsPayableSearch();
        $dataProviderAbonos = $searchModelAbonos->AccountReceivableAbonosSearch(Yii::$app->request->queryParams);

        $searchModelAbonosSinpe = new AccountsPayableSearch();
        $dataProviderAbonosSinpe = $searchModelAbonosSinpe->AccountReceivableAbonosSinpeSearch(Yii::$app->request->queryParams);
        */

        

        return $this->render('index', [
            'searchModelPendientes' => $searchModelPendientes,
            'dataProviderPendientes' => $dataProviderPendientes,

            'searchModelCanceladas' => $searchModelCanceladas,
            'dataProviderCanceladas' => $dataProviderCanceladas,

            /*
            'searchModelCanceladasNotas' => $searchModelCanceladasNotas,
            'dataProviderCanceladasNotas' => $dataProviderCanceladasNotas,
            
            'searchModelAbonos' => $searchModelAbonos,
            'dataProviderAbonos' => $dataProviderAbonos,

            'searchModelAbonosSinpe' => $searchModelAbonosSinpe,
            'dataProviderAbonosSinpe' => $dataProviderAbonosSinpe,
            */
        ]);        
    }

    /**
     * Creates a new Boxes model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new AccountsPayable();
        $model->status = UtilsConstants::ACCOUNT_PAYABLE_PENDING;
        if ($model->load(Yii::$app->request->post())){
            $model->emission_date = date('Y-m-d');
            $model->status = UtilsConstants::ACCOUNT_PAYABLE_PENDING;

            $model->save();
            return $this->redirect('index');
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }    

    /**
     * Displays a single AccountsPayable model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);

        $searchModelAbonos = new AccountsPayableAbonosSearch(['accounts_payable_abonos_id' => $id]);        
        $dataProviderAbonos = $searchModelAbonos->search(Yii::$app->request->queryParams);

        $total = sprintf('%0.2f', $model->total_invoice);
        $abonado = sprintf('%0.2f', AccountsPayableAbonos::getAbonosByInvoiceID($model->id));
        $pendiente = $total - $abonado;
        $pendiente = sprintf('%0.2f', $pendiente);

        return $this->render('view', [
            'model' => $model,
            'dataProviderAbonos'=>$dataProviderAbonos,
            'total'=>$total,
            'abonado'=>$abonado,
            'pendiente'=>$pendiente,
        ]);
    }

    /**
     * Updates an existing AccountsPayable model.
     * If update is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $status = (int) $model->status;

        if (isset($model) && !empty($model)) {
            if (is_null($model->status)) {
                $model->status = UtilsConstants::ACCOUNT_PAYABLE_PENDING;
            }

            $old_status = (int)$model->status;

            if ($model->load(Yii::$app->request->post())) {
                $transaction = \Yii::$app->db->beginTransaction();

                try {
                    if ($model->save()) {
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
        ]);
    }   

    /**
     * Creates a new AccountsPayable model.
     * If creation is successful, the browser will be redirected to the 'index' page.
     * @return mixed
     */
    public function actionAddAbono($id)
    {
        $AccountsPayable = $this->findModel($id);
        $model = new AccountsPayableAbonos;
        $model->accounts_payable_abonos_id = $AccountsPayable->id;
        $model->emission_date = date('Y-m-d');
        $abonos = AccountsPayableAbonos::find()->where(['accounts_payable_abonos_id'=>$AccountsPayable->id])->orderBy('emission_date ASC')->all();

        $total = sprintf('%0.2f', $AccountsPayable->total_invoice);
        $abonado = sprintf('%0.2f', AccountsPayableAbonos::getAbonosByInvoiceID($AccountsPayable->id));
        $pendiente = $total - $abonado;
        $pendiente = sprintf('%0.2f', $pendiente);
        $model->amount = $pendiente;
        if ($model->load(Yii::$app->request->post())) {
            $transaction = \Yii::$app->db->beginTransaction();
            try {                
                if ($model->save()) {
                    $totalAbonado = sprintf('%0.2f', $abonado + $model->amount);                    
                    if ($total == $totalAbonado){

                        $AccountsPayable->status = UtilsConstants::INVOICE_STATUS_CANCELLED;
                        $AccountsPayable->save();
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
            'AccountsPayable'=>$AccountsPayable,
            'total'=>$total,
            'abonado'=> $abonado,
            'pendiente'=> $pendiente,
        ]);
    }

    /**
     * Finds the AccountsPayable model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return AccountsPayable the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = AccountsPayable::findOne($id)) !== null) {
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

        $invoices = $query = AccountsPayable::find()->select('AccountsPayable.*, CAST(NOW() AS date) - CAST(emission_date AS date) as dias_trascurridos, 
                                                     (CAST(CAST(NOW() AS date) - CAST(emission_date AS date) as integer)) - CAST(credit_days.name AS INTEGER) AS dias_vencidos')
                                            ->join('INNER JOIN', 'credit_days', 'AccountsPayable.credit_days_id = credit_days.id')
                                            ->Where([
                                            'condition_sale_id' => ConditionSale::CREDITO,
                                            'status_hacienda'=> UtilsConstants::HACIENDA_STATUS_ACCEPTED,
                                            ])
                                            ->andWhere(['AccountsPayable.id'=>$ids])
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
