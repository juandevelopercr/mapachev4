<?php

namespace backend\modules\v1\controllers;

use backend\modules\v1\ApiUtilsFunctions;
use common\models\ChangePassword;
use common\models\GlobalFunctions;
use common\models\User;
use Yii;
use yii\filters\auth\QueryParamAuth;
use yii\helpers\ArrayHelper;

/**
 * User controller for the `v1` module
 */
class UserController extends ApiController
{
    public $modelClass = 'common\models\User';

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

        return $actions;
    }

    public function actionIndex()
    {
        $data = User::find()->all();

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

    public function actionDelete($id)
    {
        $model = User::findOne($id);
        $params = Yii::$app->request->queryParams;
        $this->checkAccess($this->action->id,$model,$params);

        if ($model !== null)
        {
            if ($model->delete()) {
                $message = Yii::t('backend','Elemento eliminado correctamente');
                return ApiUtilsFunctions::getResponseType(ApiUtilsFunctions::TYPE_SUCCESS,$message,[]);

            } else {
                $message = Yii::t('backend','Error eliminando el elemento');
                return ApiUtilsFunctions::getResponseType(ApiUtilsFunctions::TYPE_ERROR,$message,$model->getFirstErrors());
            }

        } else {
            ApiUtilsFunctions::getResponseType(ApiUtilsFunctions::TYPE_NOTFOUND);
        }
    }

    /**
     * Renders the user profile
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    public function actionProfile()
    {
        $model = User::findOne(Yii::$app->user->id);

        if($model !== null)
        {
            return ApiUtilsFunctions::getResponseType(ApiUtilsFunctions::TYPE_SUCCESS,'',$model);
        }
        else
        {
            ApiUtilsFunctions::getResponseType(ApiUtilsFunctions::TYPE_NOTFOUND);
        }
    }

    public function actionUpdate_profile()
    {
        $id = Yii::$app->user->id;
        $model = User::findOne($id);
        $params = $this->getRequestParamsAsArray();

        if($model !== null)
        {
            $model->scenario= User::SCENARIO_UPDATE;
            $old_role = GlobalFunctions::getRol($model->id);
            $model->role = $old_role;

            $oldFile = $model->getImageFile();
            $oldAvatar = $model->avatar;

            $model->load(Yii::$app->getRequest()->getBodyParams(), '' );
            $image = \yii\web\UploadedFile::getInstanceByName('fileAvatar' );

            if (isset($image) && !empty($image) && is_object($image))
            {
                if (!empty($image)) {
                    $explode= explode('.',$image->name);
                    $ext = end($explode);
                    $hash_name = GlobalFunctions::generateRandomString(10);
                    $model->avatar = "{$hash_name}.{$ext}";
                }
            }
            else
            {
                $image = false;
            }

            $model->switch_status = 1;

            $params_user = [
               'name' => ArrayHelper::getValue($params, "name"),
               'last_name' => ArrayHelper::getValue($params, "last_name"),
               'username' => ArrayHelper::getValue($params, "username"),
               'email' => ArrayHelper::getValue($params, "email"),
            ];

            $model->load($params_user, '');

            // revert back if no valid file instance uploaded
            if ($image === false) {
                $model->avatar = $oldAvatar;
            }

            $allScenarios= $model->scenarios();

            if($model->save(true,$allScenarios[$model->scenario]))
            {
                $model->save();

                if (is_object($image))
                {
                    if (!empty($image))
                    {
                        if(file_exists($oldFile))
                        {
                            try{
                                unlink($oldFile);
                            }catch (\Exception $exception){
                                Yii::info("Error deleting image on UserController: " . $oldFile);
                                Yii::info($exception->getMessage());
                            }
                        }

                        $path = $model->getImageFile();
                        $image->saveAs($path);
                    }
                }

                $model->refresh();
                $message = Yii::t('backend','Elemento actualizado correctamente');
                return ApiUtilsFunctions::getResponseType(ApiUtilsFunctions::TYPE_SUCCESS,$message,$model);
            }
            else
            {
                $message = Yii::t('backend','Error actualizando el elemento');
                $errors = [];

                $user_errors =  $model->getFirstErrors();

                if(isset($user_errors) && !empty($user_errors))
                {
                    $errors[] = $user_errors;
                }

                return ApiUtilsFunctions::getResponseType(ApiUtilsFunctions::TYPE_ERROR,$message,$errors);
            }
        }
        else
        {
            ApiUtilsFunctions::getResponseType(ApiUtilsFunctions::TYPE_NOTFOUND);
        }
    }

    /**
     * Allow to change own password for authenticated users
     * @return array
     */
    public function actionChangeOwnPassword()
    {
        $params = $this->getRequestParamsAsArray();

        $model = new ChangePassword();

        $model->oldPassword = ArrayHelper::getValue($params, "oldPassword", null);
        $model->newPassword = ArrayHelper::getValue($params, "newPassword", null);
        $model->retypePassword = ArrayHelper::getValue($params, "retypePassword", null);

        if ($model->change()) {
            $message = Yii::t("backend", "Su contraseña ha sido cambiada correctamente.");
            return ApiUtilsFunctions::getResponseType(ApiUtilsFunctions::TYPE_SUCCESS,$message,$model);
        }
        else
        {
            $message = Yii::t("backend", "Ha ocurrido un error cambiando la contraseña.");
            return ApiUtilsFunctions::getResponseType(ApiUtilsFunctions::TYPE_ERROR,$message,$model->getFirstErrors());
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
