<?php

namespace backend\controllers;

use backend\models\business\AssignLocation;
use backend\models\business\ChangePrice;
use backend\models\business\AttachPo;
use backend\models\business\Product;
use Yii;
use backend\models\business\AttachPoSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use common\models\GlobalFunctions;
use yii\helpers\Url;
use yii\db\Exception;

/**
 * AttachPoController implements the CRUD actions for AttachPo model.
 */
class AttachPoController extends Controller
{
    /**
     * Creates a new AttachPo model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate_ajax()
    {
        $model = new AttachPo();
        $model->scenario = 'create';
        $model->user_id = Yii::$app->user->id;
        if (isset($_REQUEST['id']))
        {
            $model->payment_order_id = $_REQUEST['id'];
        }

        Yii::$app->response->format = 'json';

        if ($model->load(Yii::$app->request->post()))
        {
            $model->payment_order_id = Yii::$app->request->post()['payment_order_id'];
            $image = $model->uploadImage();

            if ($model->save())
            {
                if($image){
                    $path = $model->getImageFile();
                    $image->saveAs($path);
                }

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
        return $this->renderAjax('_form_ajax', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing AttachPo() model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate_ajax()
    {
        $id = $_REQUEST['id'];

        $model = AttachPo::findOne($id);
        Yii::$app->response->format = 'json';

        $old_image_file = $model->getImageFile();
        $old_image = $model->document_file;


        if ($model->load(Yii::$app->request->post()))
        {
            $image = $model->uploadImage();

            // revert back if no valid file instance uploaded
            if ($image === false) {
                $model->document_file = $old_image;
            }

            if ($model->save())
            {
                // upload only if valid uploaded file instance found by main logo
                if ($image !== false) // delete old and overwrite
                {
                    if(file_exists($old_image_file))
                    {
                        try{
                            unlink($old_image_file);
                        }catch (\Exception $exception){
                            Yii::info("Error deleting image on AttachPo: " . $old_image_file);
                            Yii::info($exception->getMessage());
                        }
                    }

                    $path = $model->getImageFile();
                    $image->saveAs($path);
                }

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
            $model = AttachPo::findOne($id);

            if ($model->delete())
            {
                $model->deleteImage();
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
