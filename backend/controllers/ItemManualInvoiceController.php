<?php

namespace backend\controllers;

use Yii;
use backend\models\business\ManualInvoice;
use backend\models\business\ItemManualInvoice;
use backend\models\business\ItemManualInvoiceSearch;
use backend\models\business\Service;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * ItemManualInvoiceController implements the CRUD actions for ItemManualInvoice model.
 */
class ItemManualInvoiceController extends Controller
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
     * Updates an existing ItemManualInvoice model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Creates a new ItemInvoice model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate_ajax()
    {
        Yii::$app->response->format = 'json';

        if (Yii::$app->request->post()) {
            $array_posted = Yii::$app->request->post('ItemManualInvoiceForm');

            $service_id = $array_posted['service'];
            $explode = explode('-', $service_id);

            if (isset($service_id) && !empty($service_id)) {

                $item_exist = ItemManualInvoice::find()->where(['invoice_id' => $array_posted['invoice_id'], 'service_id' => $explode[1]])->one();
                if ($item_exist !== null) {
                    $model = $item_exist;
                    $model->service_id = $explode[1];
                    $model_reference = Service::findOne($explode[1]);
                    //$model->code = $model_reference->code;
                    $model->description = $model_reference->name;
                    $model->quantity = $array_posted['quantity'];
                    $request_quantity = $model->quantity;
                    $model->price = $model_reference->price;
                    $model->user_id = Yii::$app->user->id;
                } else {
                    $model = new ItemManualInvoice;
                    $model->service_id = $explode[1];
                    $model_reference = Service::findOne($explode[1]);
                    //$model->code = $model_reference->code;
                    $model->description = $model_reference->name;
                    $model->quantity = $array_posted['quantity'];
                    $request_quantity = $model->quantity;
                    $model->price = $model_reference->price;
                    $model->user_id = Yii::$app->user->id;
                }
            }

            if ($model_reference !== null) {
                $model->invoice_id = $array_posted['invoice_id'];
                //$model->quantity = $array_posted['quantity'];
                $model->unit_type_id = (isset($array_posted['unit_type_id']) && !empty($array_posted['unit_type_id'])) ? $array_posted['unit_type_id'] : $model_reference->unit_type_id;
            }

            if ($model->save()) {
                $msg = 'Se ha creado el registro satisfactoriamente';
                $type = 'success';
                $manualInvoice = ManualInvoice::find()->where(['id'=>$model->invoice_id])->one();
                $manualInvoice->save();
                
                $titulo = "Informaci&oacute;n <hr class=\"kv-alert-separator\">";
                //$item_createid = $model->id;

            } else {
                $msg = 'Ha ocurrido un error al intentar crear el registro';
                $type = 'danger';
                $itemCount = '';
                $titulo = "Informaci&oacute;n <hr class=\"kv-alert-separator\">";
                //$item_createid = '';
            }

            return [
                'message' => $msg,
                'type' => $type,
                'titulo' => "Informaci&oacute;n <hr class=\"kv-alert-separator\">",
                'itemCount' => '',
                //'id'=>$item_createid,
            ];
        }
    }

    /**
     * Updates an existing ItemInvoice() model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate_ajax($id)
    {
        $model = ItemManualInvoice::findOne($id);
        Yii::$app->response->format = 'json';

        if ($model->load(Yii::$app->request->post())) {
            $request_quantity = $model->quantity;

            if (isset($model->service_id) && !empty($model->service_id)) {
                $model_reference = Service::findOne($model->service_id);
            }

            if ($model->save()) {
                //Actualizar los totales de la factura
                $manualInvoice = ManualInvoice::find()->where(['id'=>$model->invoice_id])->one();
                $manualInvoice->save();
                $model->refresh();
                $msg = 'Se ha actualizado el registro satisfactoriamente';
                $type = 'success';
                $titulo = "Informaci&oacute;n <hr class=\"kv-alert-separator\">";
            } else {
                $msg = 'Ha ocurrido un error al intentar actualizar el registro';
                $type = 'danger';
                $titulo = "Informaci&oacute;n <hr class=\"kv-alert-separator\">";
            }
            return [
                'message' => $msg,
                'type' => $type,
                'titulo' => "Informaci&oacute;n <hr class=\"kv-alert-separator\">",
            ];
        } else {

            return $this->renderAjax('_form-item', [
                'model' => $model,
            ]);
        }
    }


    public function actionDeletemultiple_ajax()
    {
        $ids = (array)Yii::$app->request->post('ids');
        Yii::$app->response->format = 'json';
        if (!$ids) {
            return;
        }

        $eliminados = 0;
        $noeliminados = 0;
        $invoice_id = NULL;
        foreach ($ids as $id) {
            $model = ItemManualInvoice::findOne($id);
            $tempinvoice_id = $model->invoice_id;
            if (is_null($invoice_id))
                $invoice_id = $model->invoice_id;

            if ($model->delete()) {
                //Actualizar los totales de la factura
                $eliminados++;
            } else {
                $noeliminados++;
            }
        }

        $msg = $eliminados > 1 ? 'Se han eliminado ' . $eliminados : 'Se ha eliminado ' . $eliminados;
        $msg .= $eliminados > 1 ? ' registros <br />' : ' registro <br />';
        if ($noeliminados >= 1) {
            $msg .= $noeliminados > 1 ?  $noeliminados . ' Registros no pudieron ser eliminados' : ' Registro no pudo ser eliminado';
            $type = 'warning';
        } else
            $type = 'success';

        $itemCount = 0;
        if (!is_null($invoice_id)) {
            $invoice = ManualInvoice::findOne($invoice_id);
            $invoice->save(false);
        }

        return [
            'message' => $msg,
            'type' => $type,
            'itemCount' => '',
            'titulo' => "Informaci&oacute;n <hr class=\"kv-alert-separator\">",
        ];
    }

    public function actionDelete_ajax($id)
    {
        Yii::$app->response->format = 'json';

        $eliminados = 0;
        $noeliminados = 0;

        $model = ItemManualInvoice::findOne($id);
        $invoice_id = $model->invoice_id;
        if ($model->delete()) {
            //Actualizar los totales de la factura
            $invoice = ManualInvoice::find()->where(['id' => $invoice_id])->one();
            $invoice->save(false);
            $eliminados++;
        } else {
            $noeliminados++;
        }

        $itemCount = 0;
        if (!is_null($invoice_id)) {
            $invoice = ManualInvoice::findOne($invoice_id);
            $invoice->save();
        }

        $msg = $eliminados > 1 ? 'Se han eliminado ' . $eliminados : 'Se ha eliminado ' . $eliminados;
        $msg .= $eliminados > 1 ? ' registros <br />' : ' registro <br />';
        if ($noeliminados >= 1) {
            $msg .= $noeliminados > 1 ?  $noeliminados . ' Registros no pudieron ser eliminados' : ' Registro no pudo ser eliminado';
            $type = 'warning';
        } else
            $type = 'success';

        return [
            'message' => $msg,
            'type' => $type,
            'itemCount' => $itemCount,
            'titulo' => "Informaci&oacute;n <hr class=\"kv-alert-separator\">",
        ];
    }
}
