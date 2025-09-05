<?php

namespace backend\modules\v1\controllers;

use backend\components\ApiBCCR;
use backend\models\business\PaymentMethodHasPurchaseOrder;
use backend\models\business\CollectorHasPurchaseOrder;
use backend\models\business\PurchaseOrder;
use backend\models\business\PurchaseOrderSearch;
use backend\models\nomenclators\BranchOffice;
use backend\models\nomenclators\Currency;
use backend\models\nomenclators\UtilsConstants;
use backend\modules\v1\ApiUtilsFunctions;
use common\models\GlobalFunctions;
use yii\db\Exception;
use yii\filters\auth\QueryParamAuth;
use Yii;
use common\models\User;
use yii\web\ForbiddenHttpException;

/**
 * PurchaseOrderController for the `v1` module
 */
class PurchaseOrderController extends ApiController
{
    public $modelClass = 'backend\models\business\PurchaseOrder';

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => QueryParamAuth::className(),
        ];
        return $behaviors;
    }

    public $serializer = [
        'class' => 'backend\modules\v1\CustomSerializer',
        'collectionEnvelope' => 'items',
    ];

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['view'], $actions['create'], $actions['update'], $actions['delete']);
        $actions['index'] = [
            'class' => 'yii\rest\IndexAction',
            'modelClass' => $this->modelClass,
            'prepareDataProvider' => function () {
                $searchModel = new PurchaseOrderSearch();
                return $searchModel->search(Yii::$app->request->queryParams);
            },
        ];

        return $actions;
    }

    protected function verbs()
    {
        return [
            'index' => ['GET', 'HEAD'],
            'view' => ['GET'],
            'create' => ['POST'],
            'update' => ['POST', 'PUT', 'PATCH'],
            'delete' => ['POST', 'DELETE'],
        ];
    }

    public function actionView($id)
    {
        $model = PurchaseOrder::findOne($id);

        if ($model !== null) {

            return ApiUtilsFunctions::getResponseType(ApiUtilsFunctions::TYPE_SUCCESS, '', $model);
        } else {
            ApiUtilsFunctions::getResponseType(ApiUtilsFunctions::TYPE_NOTFOUND);
        }
    }

    public function actionCreate()
    {
        $model = new PurchaseOrder();
        $model->scenario = 'create';

        $model->load(Yii::$app->getRequest()->getBodyParams(), '');

        $model->is_editable = 1;
        $model->consecutive = $model->generateConsecutive();
        $model->status = UtilsConstants::PURCHASE_ORDER_STATUS_STARTED;
        $model->change_type = ApiBCCR::getChangeTypeOfIssuer();

        if (!$model->validate()) {
            $message = Yii::t('backend', 'Error creando el elemento');
            return ApiUtilsFunctions::getResponseType(ApiUtilsFunctions::TYPE_ERROR, $message, $model->getFirstErrors());
        }

        $transaction = \Yii::$app->db->beginTransaction();

        try {
            if(PurchaseOrder::find()->select(['consecutive'])->where(['consecutive' => $model->consecutive])->exists())
            {
                $model->consecutive = $model->generateConsecutive();
            }

            if ($model->save()) {
                PaymentMethodHasPurchaseOrder::updateRelation($model,[],'payment_methods','payment_method_id');

                CollectorHasPurchaseOrder::updateRelation($model, [], 'collectors', 'collector_id');

                $transaction->commit();
                $model->refresh();
                $message = Yii::t('backend', 'Elemento creado correctamente');
                return ApiUtilsFunctions::getResponseType(ApiUtilsFunctions::TYPE_SUCCESS, $message, $model);
            } else {
                $message = Yii::t('backend', 'Error creando el elemento');
                return ApiUtilsFunctions::getResponseType(ApiUtilsFunctions::TYPE_ERROR, $message, $model->getFirstErrors());
            }
        } catch (Exception $e) {
            $transaction->rollBack();
            $message = Yii::t('backend', 'Error, ha ocurrido una excepción creando el elemento');
            return ApiUtilsFunctions::getResponseType(ApiUtilsFunctions::TYPE_ERROR, $message, $e->getMessage());
        }
    }

    public function actionUpdate($id)
    {
        $model = PurchaseOrder::findOne($id);

        if (isset($model) && !empty($model))
        {
            $old_status = (int)$model->status;
            $payment_methods_assigned = PaymentMethodHasPurchaseOrder::getPaymentMethodByPurchaseOrderId($id);

            $model->scenario = 'update';
            $is_editable = (int) $model->is_editable;
            if(GlobalFunctions::getRol() !== User::ROLE_SUPERADMIN)
            {
                if($is_editable === 0)
                {
                    $message = Yii::t('backend', 'La orden de pedido no es editable');
                    return ApiUtilsFunctions::getResponseType(ApiUtilsFunctions::TYPE_BADREQUEST, $message, $model);
                }
            }

            $params = $this->getRequestParamsAsArray();

            $this->checkAccess($this->action->id, $model, $params);
            $model->load($params, '');
            if(!isset($model->status))
            {
                $model->status = $old_status;
            }

            if (!$model->validate()) {
                $message = Yii::t('backend', 'Error actualizando el elemento');
                return ApiUtilsFunctions::getResponseType(ApiUtilsFunctions::TYPE_ERROR, $message, $model->getFirstErrors());
            }

            $transaction = \Yii::$app->db->beginTransaction();

            try {
                PaymentMethodHasPurchaseOrder::updateRelation($model,$payment_methods_assigned,'payment_methods','payment_method_id');

                if ($model->save())
                {
                    $new_status = (int) $model->status;
                    if($old_status !== $new_status && $new_status === UtilsConstants::PURCHASE_ORDER_STATUS_STARTED)
                    {
                        $model->verifyStock();
                    }

                    $transaction->commit();
                    $model->refresh();
                    $message = Yii::t('backend', 'Elemento actualizado correctamente');
                    return ApiUtilsFunctions::getResponseType(ApiUtilsFunctions::TYPE_SUCCESS, $message, $model);
                } else {
                    $message = Yii::t('backend', 'Error actualizando el elemento');
                    return ApiUtilsFunctions::getResponseType(ApiUtilsFunctions::TYPE_ERROR, $message, $model->getFirstErrors());
                }
            } catch (Exception $e) {
                $transaction->rollBack();
                $message = Yii::t('backend', 'Error, ha ocurrido una excepción creando el elemento');
                return ApiUtilsFunctions::getResponseType(ApiUtilsFunctions::TYPE_ERROR, $message, $e->getMessage());
            }
        } else {
            ApiUtilsFunctions::getResponseType(ApiUtilsFunctions::TYPE_NOTFOUND);
        }
    }

    public function actionDelete($id)
    {
        $model = PurchaseOrder::findOne($id);
        $params = Yii::$app->request->queryParams;
        $this->checkAccess($this->action->id, $model, $params);

        if ($model !== null) {
            if ($model->delete()) {
                $message = Yii::t('backend', 'Elemento eliminado correctamente');
                return ApiUtilsFunctions::getResponseType(ApiUtilsFunctions::TYPE_SUCCESS, $message, []);

            } else {
                $message = Yii::t('backend', 'Error eliminando el elemento');
                return ApiUtilsFunctions::getResponseType(ApiUtilsFunctions::TYPE_ERROR, $message, $model->getFirstErrors());
            }
        } else {
            ApiUtilsFunctions::getResponseType(ApiUtilsFunctions::TYPE_NOTFOUND);
        }
    }

    /**
     * @param string $action
     * @param null $model
     * @param array $params
     * @return bool|void
     * @throws ForbiddenHttpException
     * @throws \yii\web\BadRequestHttpException
     * @throws \yii\web\NotFoundHttpException
     */
    public function checkAccess($action, $model = null, $params = [])
    {
        if (GlobalFunctions::getRol() !== User::ROLE_SUPERADMIN) {
            if (Yii::$app->user->id !== $model->user_id) {
                ApiUtilsFunctions::getResponseType(ApiUtilsFunctions::TYPE_FORBIDDEN);
            }
        }

        return true;
    }
}
