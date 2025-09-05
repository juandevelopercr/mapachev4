<?php

namespace backend\controllers;

use backend\models\business\AssignLocation;
use backend\models\business\ChangePrice;
use backend\models\business\ItemEntry;
use backend\models\business\Product;
use Yii;
use backend\models\business\ItemImported;
use backend\models\business\ItemImportedSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use common\models\GlobalFunctions;
use yii\helpers\Url;
use yii\db\Exception;

/**
 * ItemImportedController implements the CRUD actions for ItemImported model.
 */
class ItemImportedController extends Controller
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
     * Lists all ItemImported models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new ItemImportedSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single ItemImported model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);

        return $this->render('view', [
            'model' => $model,
        ]);
    }

    /**
     * Creates a new ItemImported model.
     * If creation is successful, the browser will be redirected to the 'index' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new ItemImported();
        $model->loadDefaultValues();

        if ($model->load(Yii::$app->request->post()))
        {
            $transaction = \Yii::$app->db->beginTransaction();

            try
            {
                if($model->save())
                {
                    $transaction->commit();

                    GlobalFunctions::addFlashMessage('success',Yii::t('backend','Elemento creado correctamente'));

                    return $this->redirect(['index']);
                }
                else
                {
                    GlobalFunctions::addFlashMessage('danger',Yii::t('backend','Error creando el elemento'));
                }
            }
            catch (Exception $e)
            {
                GlobalFunctions::addFlashMessage('danger',Yii::t('backend','Error, ha ocurrido una excepción creando el elemento'));
                $transaction->rollBack();
            }
        }

        return $this->render('create', [
            'model' => $model,
        ]);

    }

    /**
     * Updates an existing ItemImported model.
     * If update is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if(isset($model) && !empty($model))
        {
            if ($model->load(Yii::$app->request->post()))
            {
                $transaction = \Yii::$app->db->beginTransaction();

                try
                {
                    if($model->save())
                    {
                        $transaction->commit();

                        GlobalFunctions::addFlashMessage('success',Yii::t('backend','Elemento actualizado correctamente'));

                        return $this->redirect(['index']);
                    }
                    else
                    {
                        GlobalFunctions::addFlashMessage('danger',Yii::t('backend','Error actualizando el elemento'));
                    }
                }
                catch (Exception $e)
                {
                    GlobalFunctions::addFlashMessage('danger',Yii::t('backend','Error, ha ocurrido una excepción actualizando el elemento'));
                    $transaction->rollBack();
                }
            }
        }
        else
        {
            GlobalFunctions::addFlashMessage('warning',Yii::t('backend','El elemento buscado no existe'));
        }

        return $this->render('update', [
            'model' => $model,
        ]);

    }

    /**
     * Deletes an existing ItemImported model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        $transaction = \Yii::$app->db->beginTransaction();

        try
        {
            if($model->delete())
            {
                $transaction->commit();

                GlobalFunctions::addFlashMessage('success',Yii::t('backend','Elemento eliminado correctamente'));
            }
            else
            {
                GlobalFunctions::addFlashMessage('danger',Yii::t('backend','Error eliminando el elemento'));
            }

            return $this->redirect(['index']);
        }
        catch (Exception $e)
        {
            GlobalFunctions::addFlashMessage('danger',Yii::t('backend','Error, ha ocurrido una excepción eliminando el elemento'));
            $transaction->rollBack();
        }
    }

    /**
     * Finds the ItemImported model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return ItemImported the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = ItemImported::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('backend','La página solicitada no existe'));
        }
    }

    /**
    * Bulk Deletes for existing ItemImported models.
    * If deletion is successful, the browser will be redirected to the 'index' page.
    * @return mixed
    */
    public function actionMultiple_delete()
    {
        if(Yii::$app->request->post('row_id'))
        {
            $transaction = \Yii::$app->db->beginTransaction();

            try
            {
                $pk = Yii::$app->request->post('row_id');
                $count_elements = count($pk);

                $deleteOK = true;
                $nameErrorDelete = '';
                $contNameErrorDelete = 0;

                foreach ($pk as $key => $value)
                {
                    $model= $this->findModel($value);

                    if(!$model->delete())
                    {
                        $deleteOK=false;
                        $nameErrorDelete= $nameErrorDelete.'['.$model->name.'] ';
                        $contNameErrorDelete++;
                    }
                }

                if($deleteOK)
                {
                    if($count_elements === 1)
                    {
                        GlobalFunctions::addFlashMessage('success',Yii::t('backend','Elemento eliminado correctamente'));
                    }
                    else
                    {
                        GlobalFunctions::addFlashMessage('success',Yii::t('backend','Elementos eliminados correctamente'));
                    }

                    $transaction->commit();
                }
                else
                {
                    if($count_elements === 1)
                    {
                        if($contNameErrorDelete===1)
                        {
                            GlobalFunctions::addFlashMessage('danger',Yii::t('backend','Error eliminando el elemento').': <b>'.$nameErrorDelete.'</b>');
                        }
                    }
                    else
                    {
                        if($contNameErrorDelete===1)
                        {
                            GlobalFunctions::addFlashMessage('danger',Yii::t('backend','Error eliminando el elemento').': <b>'.$nameErrorDelete.'</b>');
                        }
                        elseif($contNameErrorDelete>1)
                        {
                            GlobalFunctions::addFlashMessage('danger',Yii::t('backend','Error eliminando los elementos').': <b>'.$nameErrorDelete.'</b>');
                        }
                    }
                }
            }
            catch (Exception $e)
            {
                GlobalFunctions::addFlashMessage('danger',Yii::t('backend','Error, ha ocurrido una excepción eliminando el elemento'));
                $transaction->rollBack();
            }

            return $this->redirect(['index']);
        }
    }

    /**********************************************************************************************
    / 									MÉTODOS AJAX
    /**********************************************************************************************

    /**
     * Updates an existing ItemImported() model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate_ajax()
    {
        $id = $_REQUEST['id'];

        $model = ItemImported::findOne($id);
        Yii::$app->response->format = 'json';
        $model->scenario = 'update';

        if ($model->load(Yii::$app->request->post()))
        {
            if(!Product::find()->where(['supplier_code' => $model->code])->exists())
            {
                $model->status = ItemImported::STATUS_PRODUCT_NOT_FOUND;
            }
            else
            {
                if(!isset($model->sector_location_id ) || empty($model->sector_location_id))
                {
                    $model->status = ItemImported::STATUS_NOT_LOCATION;
                }
                elseif($model->status != ItemImported::STATUS_PRODUCT_FORCE_APROV)
                {
                    $product = Product::find()->select(['price'])->where(['supplier_code' => $model->code])->one();

                    $product_price = GlobalFunctions::formatNumber($product->price,2);
                    $item_price = GlobalFunctions::formatNumber($model->price_by_unit,2);

                    if($product_price !== $item_price)
                    {
                        $model->status = ItemImported::STATUS_ALERT_PRICE_DISTINCT;
                    }
                    else
                    {
                        if(isset($model->sector_location_id )&& !empty($model->sector_location_id))
                        {
                            $model->status = ItemImported::STATUS_READY_TO_APPROV;
                        }
                        else
                        {
                            $model->status = ItemImported::STATUS_NOT_LOCATION;
                        }
                    }
                }
            }

            if ($model->save())
            {
                // upload only if valid uploaded file instance found
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
                'type'=> $type,
                'titulo'=>"Informaci&oacute;n <hr class=\"kv-alert-separator\">",
            ];

        } else {

            return $this->renderAjax('_form_ajax', [
                'model' => $model,
            ]);
        }
    }

    public function actionAprovmultiple_ajax()
    {
        Yii::$app->response->format = 'json';

        $aprov = 0;
        $no_aprov = 0;

        $models = ItemImported::find()->where(['IN','status', [ItemImported::STATUS_READY_TO_APPROV,ItemImported::STATUS_PRODUCT_FORCE_APROV]])->all();

        foreach ($models AS $key => $model)
        {
            if (ItemEntry::convertXmlItemToEntryItem($model)) {
                $aprov++;
                $model->delete();
            }
            else {
                $no_aprov++;
            }
        }

        $msg = $aprov > 1 ? 'Se han aprobado '.$aprov: 'Se ha aprobado '.$aprov;
        $msg .= $aprov > 1 ? ' registros <br />' : ' registro <br />';
        if ($no_aprov >= 1)
        {
            $msg .= $no_aprov > 1 ?  $no_aprov.' Registros no pudieron ser aprobados' : ' Registro no pudo ser aprobado';
            $type = 'warning';
        }
        else
        {
            $type = 'success';
        }

        return [
            'message' => $msg,
            'type'=> $type,
            'titulo'=>"Informaci&oacute;n <hr class=\"kv-alert-separator\">",
        ];
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
        foreach ($ids as $id)
        {
            $model = ItemImported::findOne($id);

            if ($model->delete())
            {
                $eliminados++;
            } else {
                $noeliminados++;
            }
        }

        $msg = $eliminados > 1 ? 'Se han eliminado '.$eliminados: 'Se ha eliminado '.$eliminados;
        $msg .= $eliminados > 1 ? ' registros <br />' : ' registro <br />';
        if ($noeliminados >= 1)
        {
            $msg .= $noeliminados > 1 ?  $noeliminados.' Registros no pudieron ser eliminados' : ' Registro no pudo ser eliminado';
            $type = 'warning';
        }
        else
            $type = 'success';

        return [
            'message' => $msg,
            'type'=> $type,
            'titulo'=>"Informaci&oacute;n <hr class=\"kv-alert-separator\">",
        ];
    }

    /**
     * Updates an existing ItemImported() model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionChange_price_ajax($id)
    {
        Yii::$app->response->format = 'json';
        $model_item_imported = ItemImported::findOne($id);

        $product_model = Product::findOne(['bar_code' => $model_item_imported->code]);
        $model = new ChangePrice(['product_id' => $product_model->id,'current_price' => $product_model->price,'new_price' => $model_item_imported->price_by_unit]);
        $old_price = $product_model->price;

        if ($model->load(Yii::$app->request->post()))
        {
            if ($model->validate() && $model->change_price())
            {
                //notificar a los responsables de un cambio de precio en producto
                $model_item_imported->sendEmail($old_price,$product_model->id);

                $msg = 'Se ha actualizado el registro satisfactoriamente';
                $type = 'success';
                $titulo = "Informaci&oacute;n <hr class=\"kv-alert-separator\">";
            }
            else
            {
                $msg = 'Ha ocurrido un error al intentar actualizar el registro';
                $type = 'danger';
                $titulo = "Informaci&oacute;n <hr class=\"kv-alert-separator\">";
            }

            return [
                'message' => $msg,
                'type'=> $type,
                'titulo'=>"Informaci&oacute;n <hr class=\"kv-alert-separator\">",
            ];

        } else {

            return $this->renderAjax('_form_change_price_ajax', [
                'model' => $model,
                'model_item_imported' => $model_item_imported,
            ]);
        }
    }

    public function actionAssign_multiple_location_ajax($branch_office_id)
    {
        Yii::$app->response->format = 'json';

        $model = new AssignLocation();
        if(isset($_REQUEST['ids']))
        {
            $ids = (array)$_REQUEST['ids'];
            $model->item_imported_ids = $ids;
        }

        if ($model->load(Yii::$app->request->post())) {

            if ($model->validate()) {
                foreach ($model->item_imported_ids AS $key => $id) {
                    $item = ItemImported::findOne($id);
                    $item->sector_location_id = $model->sector_location_id;
                    if(!Product::find()->where(['supplier_code' => $item->code])->exists())
                    {
                        $item->status = ItemImported::STATUS_PRODUCT_NOT_FOUND;
                    }
                    else
                    {
                        if(!isset($item->sector_location_id ) || empty($item->sector_location_id))
                        {
                            $item->status = ItemImported::STATUS_NOT_LOCATION;
                        }
                        elseif($item->status != ItemImported::STATUS_PRODUCT_FORCE_APROV)
                        {
                            $product = Product::find()->select(['price'])->where(['supplier_code' => $item->code])->one();

                            $product_price = GlobalFunctions::formatNumber($product->price,2);
                            $item_price = GlobalFunctions::formatNumber($item->price_by_unit,2);

                            if($product_price !== $item_price)
                            {
                                $item->status = ItemImported::STATUS_ALERT_PRICE_DISTINCT;
                            }
                            else
                            {
                                if(isset($item->sector_location_id )&& !empty($item->sector_location_id))
                                {
                                    $item->status = ItemImported::STATUS_READY_TO_APPROV;
                                }
                                else
                                {
                                    $item->status = ItemImported::STATUS_NOT_LOCATION;
                                }
                            }
                        }
                    }

                    $item->save();
                }

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

        }
        else
        {
            return $this->renderAjax('temp_assign', [
                'model' => $model,
                'branch_office_id' => $branch_office_id,
            ]);
        }
    }

}
