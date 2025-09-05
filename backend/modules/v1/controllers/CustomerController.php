<?php

namespace backend\modules\v1\controllers;

use backend\models\business\CustomerSearch;
use backend\models\nomenclators\UtilsConstants;
use backend\modules\v1\ApiUtilsFunctions;
use common\models\ChangePassword;
use common\models\GlobalFunctions;
use common\models\User;
use Yii;
use yii\filters\auth\QueryParamAuth;
use yii\helpers\ArrayHelper;
use backend\models\business\Customer;

/**
 * Customer controller for the `v1` module
 */
class CustomerController extends ApiController
{
    public $modelClass = 'backend\models\business\Customer';

    public $serializer = [
        'class' => 'backend\modules\v1\CustomSerializer',
        'collectionEnvelope' => 'items',
    ];

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => QueryParamAuth::className(),
        ];
        return $behaviors;
    }

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['index'],$actions['view'],$actions['delete']);
        $actions['index'] = [
            'class' => 'yii\rest\IndexAction',
            'modelClass' => $this->modelClass,
            'prepareDataProvider' => function () {
                $searchModel = new CustomerSearch();
                return $searchModel->search(Yii::$app->request->queryParams);
            },
        ];

        return $actions;
    }

    public function actionIndex()
    {
        $data = Customer::find()->all();

        return ApiUtilsFunctions::getResponseType(ApiUtilsFunctions::TYPE_INDEX_RESPONSE,'',$data);
    }

    public function actionView($id)
    {
        $model = User::findOne($id);
        $params = Yii::$app->request->queryParams;
        $this->checkAccess($this->action->id,$model,$params);

        if ($model !== null) {
            return ApiUtilsFunctions::getResponseType(ApiUtilsFunctions::TYPE_SUCCESS,'',$model);
        } else {
            ApiUtilsFunctions::getResponseType(ApiUtilsFunctions::TYPE_NOTFOUND);
        }
    }

    public function checkAccess($action, $model = null, $params = [])
    {
        $block_access = false;
        $is_superadmin = (GlobalFunctions::getRol() === User::ROLE_SUPERADMIN)? true : false;

        if(!$is_superadmin)
        {
            if(in_array($action,['index','view','create','update','delete']))
            {
                $block_access = true;
            }
        }

        if($block_access) {
            ApiUtilsFunctions::getResponseType(ApiUtilsFunctions::TYPE_FORBIDDEN);
        }
    }
}
