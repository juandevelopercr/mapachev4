<?php

namespace backend\controllers;

use backend\models\business\ItemImported;
use backend\models\business\Supplier;
use common\models\User;
use Yii;
use backend\models\business\XmlImported;
use backend\models\business\XmlImportedSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use common\models\GlobalFunctions;
use yii\helpers\Url;
use yii\db\Exception;

/**
 * XmlImportedController implements the CRUD actions for XmlImported model.
 */
class XmlImportedController extends Controller
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
     * Lists all XmlImported models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new XmlImportedSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single XmlImported model.
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
     * Creates a new XmlImported model.
     * If creation is successful, the browser will be redirected to the 'index' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new XmlImported();
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
     * Updates an existing XmlImported model.
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
     * Deletes an existing XmlImported model.
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
                $model->deleteXml();

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
     * Finds the XmlImported model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return XmlImported the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = XmlImported::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('backend','La página solicitada no existe'));
        }
    }

    /**
    * Bulk Deletes for existing XmlImported models.
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
                    else{
                        $model->deleteXml();
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
     * Creates a new XmlImported model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate_ajax()
    {
        Yii::$app->response->format = 'json';
        $model = new XmlImported();
        $model->scenario = 'create';
        $model->user_id = Yii::$app->user->id;

        if (isset($_REQUEST['id']))
        {
            $model->entry_id = $_REQUEST['id'];
        }

        if ($model->load(Yii::$app->request->post()))
        {
            $model->entry_id = Yii::$app->request->post()['entry_id'];

            $xml = $model->uploadXml();
            $errors = 0;

            if($model->array_xml['Emisor'])
            {
                $supplier_model = Supplier::findOne(['identification' => $model->array_xml['Emisor']['Identificacion']['Numero']]);
                if($supplier_model !== null)
                {
                    $model->supplier_id = $supplier_model->id;
                }
                else
                {
                    $errors++;
                }
            }
            else{
                $errors++;
            }

            if($errors > 0)
            {
                $model->addError('supplier_id', 'Proveedor no puede estar vacío');

                return [
                    'message' => 'Ha ocurrido un error al intentar crear el registro',
                    'type'=> 'danger',
                    'titulo'=>"Información <hr class=\"kv-alert-separator\">",
                ];
            }

            if ($model->save())
            {
                if($xml){
                    $path = $model->getXmlFile();
                    $xml->saveAs($path);
                }

                $array_xml = GlobalFunctions::convertXmlInArrayPhp(Url::to(['/'.$model->getXmlFile()]));

                $model->currency_code = (isset($array_xml['ResumenFactura']['CodigoTipoMoneda']['CodigoMoneda']))? $array_xml['ResumenFactura']['CodigoTipoMoneda']['CodigoMoneda'] : null;
                $model->currency_change_value = (isset($array_xml['ResumenFactura']['CodigoTipoMoneda']['TipoCambio']))? $array_xml['ResumenFactura']['CodigoTipoMoneda']['TipoCambio'] : null;
                $model->invoice_key = (isset($array_xml['Clave']))? $array_xml['Clave'] : null;
                $model->invoice_activity_code = (isset($array_xml['CodigoActividad']))? $array_xml['CodigoActividad'] : null;
                $model->invoice_consecutive_number = (isset($array_xml['NumeroConsecutivo']))? $array_xml['NumeroConsecutivo'] : null;
                $model->invoice_date = (isset($array_xml['FechaEmision']))? $array_xml['FechaEmision'] : null;
                $array_items = (isset($array_xml['DetalleServicio']['LineaDetalle']))? $array_xml['DetalleServicio']['LineaDetalle'] : null;
                if($array_items !== null)
                {
                    ItemImported::addItems($model->id,$array_items);
                }

                $model->save();
                // upload only if valid uploaded file instance found
                $model->refresh();
                $msg = 'Se ha creado el registro satisfactoriamente';
                $type = 'success';
                $titulo = "Información <hr class=\"kv-alert-separator\">";
            }
            else
            {
                $msg = 'Ha ocurrido un error al intentar crear el registro';
                $type = 'danger';
                $titulo = "Información <hr class=\"kv-alert-separator\">";
            }

            return [
                'message' => $msg,
                'type'=> $type,
                'titulo'=>"Información <hr class=\"kv-alert-separator\">",
            ];
        }

        return $this->renderAjax('_form_import', [
            'model' => $model,
        ]);
    }
}
