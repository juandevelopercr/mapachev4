<?php

namespace backend\controllers;

use Yii;
use backend\models\business\SupplierContact;
use backend\models\business\SupplierContactSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use common\models\GlobalFunctions;
use yii\helpers\Url;
use yii\db\Exception;

/**
 * SupplierContactController implements the CRUD actions for SupplierContact model.
 */
class SupplierContactController extends Controller
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
     * Lists all SupplierContact models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new SupplierContactSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single SupplierContact model.
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
     * Creates a new SupplierContact model.
     * If creation is successful, the browser will be redirected to the 'index' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new SupplierContact();
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
     * Updates an existing SupplierContact model.
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
     * Deletes an existing SupplierContact model.
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
     * Finds the SupplierContact model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return SupplierContact the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = SupplierContact::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('backend','La página solicitada no existe'));
        }
    }

    /**
    * Bulk Deletes for existing SupplierContact models.
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
    / 									METODOS AJAX PARA CONTACTOS
    /**********************************************************************************************

    /**
     * Creates a new SupplierContact model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate_ajax()
    {
        $model = new SupplierContact();
        $model->status = 1;
        if (isset($_REQUEST['id']))
            $model->supplier_id = $_REQUEST['id'];
        Yii::$app->response->format = 'json';
        if ($model->load(Yii::$app->request->post())) {
            $model->supplier_id = Yii::$app->request->post()['supplier_id'];
            // process uploaded image file instance
            if ($model->save()) {
                // upload only if valid uploaded file instance found
                $model->refresh();
                $msg = 'Se ha creado el registro satisfactoriamente';
                $type = 'success';
                $titulo = "Informaci&oacute;n <hr class=\"kv-alert-separator\">";

            } else {
                $msg = 'Ha ocurrido un error al intentar crear el registro';
                $type = 'danger';
                $titulo = "Informaci&oacute;n <hr class=\"kv-alert-separator\">";
            }
            return [
                'message' => $msg,
                'type'=> $type,
                'titulo'=>"Informaci&oacute;n <hr class=\"kv-alert-separator\">",
            ];
        }
        return $this->renderAjax('_form-contact', [
            'model' => $model,
        ]);
    }


    /**
     * Updates an existing SupplierContact model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate_ajax()
    {
        $id = $_REQUEST['id'];

        $model = SupplierContact::findOne($id);
        Yii::$app->response->format = 'json';
        if ($model->load(Yii::$app->request->post())) {
            $model->status = 1;
            if ($model->save()) {
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

            return $this->renderAjax('_form-contact', [
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
        foreach ($ids as $id)
        {
            $model = SupplierContact::findOne($id);
            if ($model->delete()) {
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

}
