<?php

namespace backend\controllers;

use Yii;
use backend\models\business\ReceptionItemPo;
use backend\models\business\ReceptionItemPoSearch;
use yii\helpers\Json;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use common\models\GlobalFunctions;
use yii\helpers\Url;
use yii\db\Exception;

/**
 * ReceptionItemPoController implements the CRUD actions for ReceptionItemPo model.
 */
class ReceptionItemPoController extends Controller
{

    /**
     * Updates an existing ReceptionItemPo() model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate_ajax()
    {
        $id = $_REQUEST['id'];

        $model = ReceptionItemPo::findOne($id);
        Yii::$app->response->format = 'json';

        if ($model->load(Yii::$app->request->post()))
        {

            if ($model->save())
            {

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

    public function actionEditable_ajax()
    {
        Yii::$app->response->format = 'json';
        $out = "";
        // validate if there is a editable input saved via AJAX
        if (Yii::$app->request->post('hasEditable'))
        {
            // instantiate your book model for saving
            $id = Yii::$app->request->post('editableKey');
            $model = ReceptionItemPo::findOne($id);

            $posted = current($_POST['ReceptionItemPo']);
            $post = ['ReceptionItemPo' => $posted];

            if ($model->load($post))
            {
                if($model->save())
                {
                    $out = ["output" => "", "message" => ""];
                }
                else
                {
                    $errors = $model->getFirstErrors();
                    $err = array_shift($errors);
                    $out = ["output" => "error", "message" => $err];
                }
            }

            // return ajax json encoded response and exit
            return $out;
        }
    }

}
