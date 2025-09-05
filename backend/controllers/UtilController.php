<?php

namespace backend\controllers;

use backend\models\nomenclators\Canton;
use backend\models\nomenclators\Category;
use backend\models\nomenclators\Disctrict;
use backend\models\nomenclators\UtilsConstants;
use backend\models\nomenclators\PointsSale;
use backend\models\nomenclators\Boxes;
use yii\web\Controller;
use yii\web\Response;

class UtilController extends Controller
{

    public function actionGet_cantons()
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        $out = [];
        if (isset($_POST['depdrop_parents'])) {
            $parents = $_POST['depdrop_parents'];
            if ($parents != null) {
                $province_id = $parents[0];
                $param1 = null;
                $param2 = null;
                $list = Canton::find()->andWhere(['province_id'=>$province_id])->asArray()->all();
                $selected  = null;
                if ($province_id != null && count($list) > 0) {
                    $selected = '';
                    foreach ($list as $i => $data) {
                        $out[] = ['id' => $data['id'], 'name' => $data['code'].' - '.$data['name']];
                    }
                    // Shows how you can preselect a value
                    return \Yii::$app->response->data  =  ['output' => $out, 'selected'=>$selected];
                }
            }
        }
        return \Yii::$app->response->data  =  ['output' => '', 'selected'=>''];
    }

    public function actionGet_dictrict()
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        $out = [];
        if (isset($_POST['depdrop_parents'])) {
            $parents = $_POST['depdrop_parents'];
            if ($parents != null) {
                $canton_id = $parents[1];
                $param2 = null;
                $list = Disctrict::find()->andWhere(['canton_id'=>$canton_id])->asArray()->all();
                $selected  = null;
                if ($canton_id != null && count($list) > 0) {
                    $selected = '';
                    foreach ($list as $i => $data) {
                        $out[] = ['id' => $data['id'], 'name' => $data['code'].' - '.$data['name']];
                    }
                    // Shows how you can preselect a value
                    return \Yii::$app->response->data  =  ['output' => $out, 'selected'=>$selected];

                }
            }
        }
        return \Yii::$app->response->data  =  ['output' => '', 'selected'=>''];
    }

    /**
     * @param $path //folder name under uploads directory
     * @param $file_name
     * @param $name_to_download
     * @return \yii\console\Response|Response
     */
    public function actionDownload_file($path, $file_name, $name_to_download)
    {
        $explode = explode('.',$file_name);
        $ext = end($explode);
        $name_download = $name_to_download.'.'.$ext;
        return \Yii::$app->response->sendFile(dirname(dirname(__DIR__)) . '/backend/web/uploads/'.$path.'/'.$file_name,$name_download);
    }

    public function actionGet_categories()
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        $out = [];
        if (isset($_POST['depdrop_parents'])) {
            $parents = $_POST['depdrop_parents'];
            if ($parents != null) {
                $family_id = $parents[0];
                $param1 = null;
                $param2 = null;
                $list = Category::find()->andWhere(['family_id'=>$family_id])->asArray()->all();
                $selected  = null;
                if ($family_id != null && count($list) > 0) {
                    $selected = '';
                    foreach ($list as $i => $data) {
                        $out[] = ['id' => $data['id'], 'name' => $data['code'].' - '.$data['name']];
                    }
                    // Shows how you can preselect a value
                    return \Yii::$app->response->data  =  ['output' => $out, 'selected'=>$selected];
                }
            }
        }
        return \Yii::$app->response->data  =  ['output' => '', 'selected'=>''];
    }

    public function actionGet_price_types_product()
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        $out = [];
        if (isset($_POST['depdrop_parents'])) {
            $parents = $_POST['depdrop_parents'];
            if ($parents != null) {
                $product_service = $parents[0];
                $param1 = null;
                $param2 = null;
                $list = UtilsConstants::getPriceTypeSelectByProduct(null,false, $product_service);
                $selected  = null;
                if ($product_service != null && count($list) > 0) {
                    $selected = '';
                    foreach ($list as $key => $value) {
                        $out[] = ['id' => $key, 'name' => $value];
                    }
                    // Shows how you can preselect a value
                    return \Yii::$app->response->data  =  ['output' => $out, 'selected'=> UtilsConstants::CUSTOMER_ASSIGN_PRICE_1];
                }
            }
        }
        return \Yii::$app->response->data  =  ['output' => '', 'selected'=>''];
    }

    public function actionGetBoxes()
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        $out = [];
        if (isset($_POST['depdrop_parents']) && $_POST['depdrop_parents'][0] > 0) {
            $parents = $_POST['depdrop_parents'];
            if ($parents != null) {
                $branch_office_id = $parents[0];
                $param1 = null;
                $param2 = null;
                $list = Boxes::find()->andWhere(['branch_office_id'=>$branch_office_id, 'is_point_sale'=>0])->asArray()->all();
                $selected  = null;
                if ($branch_office_id != null && count($list) > 0) {
                    $selected = '';
                    foreach ($list as $i => $data) {
                        $out[] = ['id' => $data['id'], 'name' => $data['numero'].' - '.$data['name']];
                    }
                    // Shows how you can preselect a value
                    return \Yii::$app->response->data  =  ['output' => $out, 'selected'=>$selected];
                }
            }
        }
        return \Yii::$app->response->data  =  ['output' => '', 'selected'=>''];
    }    

    public function actionGetAllBoxes()
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        $out = [];
        if (isset($_POST['depdrop_parents']) && $_POST['depdrop_parents'][0] > 0) {
            $parents = $_POST['depdrop_parents'];
            if ($parents != null) {
                $branch_office_id = $parents[0];
                $param1 = null;
                $param2 = null;
                $list = Boxes::find()->andWhere(['branch_office_id'=>$branch_office_id])->asArray()->all();
                $selected  = null;
                if ($branch_office_id != null && count($list) > 0) {
                    $selected = '';
                    foreach ($list as $i => $data) {
                        $out[] = ['id' => $data['id'], 'name' => $data['numero'].' - '.$data['name']];
                    }
                    // Shows how you can preselect a value
                    return \Yii::$app->response->data  =  ['output' => $out, 'selected'=>$selected];
                }
            }
        }
        return \Yii::$app->response->data  =  ['output' => '', 'selected'=>''];
    }  
    

}