<?php

namespace backend\modules\v1\controllers;

use backend\modules\v1\ApiUtilsFunctions;
use common\models\LoginForm;
use common\models\PasswordResetRequest;
use common\models\ResetPassword;
use common\models\User;
use yii\helpers\ArrayHelper;
use Yii;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;

class AuthController extends ApiController
{
    protected function verbs()
    {
        return [
            'login' => ['POST'],
            'password-recovery' => ['POST'],
            'request-password-reset' => ['POST'],
            'reset-password' => ['POST'],
            'reset-password-confirm' => ['POST'],
        ];
    }

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['create'], $actions['index'], $actions['view'], $actions['update']);

        return $actions;
    }

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        unset($behaviors['authenticator']);
        return $behaviors;
    }

    /**
     * Allow to generate access token for users
     * @return array
     * @throws BadRequestHttpException
     * @throws NotFoundHttpException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionLogin()
    {
        $params = $this->getRequestParamsAsArray();
        return $this->loginUser($params);
    }

    /**
     * Allow to login user using username and password params
     * @param $params
     * @return array
     * @throws \yii\base\InvalidConfigException
     * @throws NotFoundHttpException
     * @throws BadRequestHttpException
     */
    private function loginUser($params)
    {
        // *********  Check for username:password valid  ********
        if (ArrayHelper::keyExists("username", $params) && ArrayHelper::keyExists("password", $params))
        {
            $username = (string)ArrayHelper::getValue($params, 'username');
            $password = (string)ArrayHelper::getValue($params, 'password');

            if (($user = User::findByUsername($username)) !== null)
            {
                $loginForm = new LoginForm();
                $loginForm->username = $username;
                $loginForm->password = $password;

                if ($loginForm->login())
                {
                    $user->generateAuthKey();
                    $user->save(false);
                    Yii::$app->user->logout();

                    $message = Yii::t('backend', 'Usuario autenticado');
                    return ApiUtilsFunctions::getResponseType(ApiUtilsFunctions::TYPE_SUCCESS,$message,$user->getModelAsJson());
                }
                else
                {
                    $message = Yii::t('backend', 'Credenciales inválidas');
                    return ApiUtilsFunctions::getResponseType(ApiUtilsFunctions::TYPE_ERROR,$message,$loginForm->getFirstErrors());
                }

            }
            else
            {
                ApiUtilsFunctions::getResponseType(ApiUtilsFunctions::TYPE_NOTFOUND);
            }
        }

        throw new BadRequestHttpException(Yii::t("backend", "Faltan parámetros para ejecutar la consulta"));
    }

    /**
     * Send email for user recovery password
     * @return array
     * @throws NotFoundHttpException
     * @throws BadRequestHttpException
     */
    public function actionPasswordRecovery()
    {
        if (($user = $this->validateUser()) != false)
        {
            $message = Yii::t("backend", "Usted ya se encuentra autenticado en el sistema.");
            return ApiUtilsFunctions::getResponseType(ApiUtilsFunctions::TYPE_SUCCESS,$message);
        }

        $params = $this->getRequestParamsAsArray();

        $model = new PasswordResetRequest();
        $model->email = ArrayHelper::getValue($params, "email", null);

        if (User::findByEmail($model->email) !== null) {
            if ($model->sendEmail()) {
                $message = Yii::t("backend", "Se enviaron instrucciones a su correo para recuperar la contraseña.");
                return ApiUtilsFunctions::getResponseType(ApiUtilsFunctions::TYPE_SUCCESS,$message);
            }
            else
             {
                throw new BadRequestHttpException(Yii::t("backend", "No se pudo enviar el correo."));
             }
        } else {
            ApiUtilsFunctions::getResponseType(ApiUtilsFunctions::TYPE_NOTFOUND);
        }
    }

    /* Funciones para cambiar contraseña en APK con el flujo de solicitar cambio, envio de codigo al correo del usuario y una vista
       en la apk que tiene que poner ese codigo + nueva contraseña
     */
    /**
     * Request reset password
     * @return string
     */
    public function actionRequestPasswordReset()
    {
        $data = [];
        $model = new PasswordResetRequest();

        if ($model->load(Yii::$app->getRequest()->getBodyParams(), '') && $model->validate())
        {
            $user_model = $model->sendEmail(true);

            if ($user_model !== false)
            {
                $data[] = ['id' => $user_model->id];

                $message = Yii::t('backend','Código para recuperar contraseña enviado por correo');
                return ApiUtilsFunctions::getResponseType(ApiUtilsFunctions::TYPE_SUCCESS,$message,$data);
            }
            else
            {
                throw new BadRequestHttpException(Yii::t("backend", "No se pudo enviar el correo."));
            }
        }
        else
        {
            ApiUtilsFunctions::getResponseType(ApiUtilsFunctions::TYPE_NOTFOUND);
        }
    }

    /**
     * Reset password
     * @return string
     */
    public function actionResetPassword()
    {
        $request = Yii::$app->getRequest()->getBodyParams();
        $missing = [];

        if(!isset($request['id']))
        {
            $missing[] = 'id';
        }

        if(!isset($request['code']))
        {
            $missing[] = 'code';
        }

        if(!isset($request['password']))
        {
            $missing[] = 'password';
        }

        if(count($missing) > 0)
        {
            throw new BadRequestHttpException(Yii::t('yii', 'Missing required parameters: {params}', [
                'params' => implode(', ', $missing),
            ]));
        }

        $user_id = $request['id'];
        $code = $request['code'];
        $password = $request['password'];
        $data = [];

        $user = User::findOne($user_id);
        if($user)
        {
            if(User::checkCodeToken($user_id,$code))
            {
                try {
                    $model = new ResetPassword($user->password_reset_token);
                } catch (\Exception $e) {
                    throw new BadRequestHttpException($e->getMessage());
                }

                $model->password = $password;

                if($model->resetPassword())
                {
                    $data[] = ['id' => $user_id];

                    $message = Yii::t('backend','Elemento actualizado correctamente');
                    return ApiUtilsFunctions::getResponseType(ApiUtilsFunctions::TYPE_SUCCESS,$message,$data);
                }
                else
                {
                    $message = Yii::t('backend','Error actualizando el elemento');
                    return ApiUtilsFunctions::getResponseType(ApiUtilsFunctions::TYPE_ERROR,$message,[]);
                }
            }
            else
            {
                $message = Yii::t('backend','El código enviado es incorrecto');
                return ApiUtilsFunctions::getResponseType(ApiUtilsFunctions::TYPE_ERROR,$message,[]);
            }
        }
        else
        {
            ApiUtilsFunctions::getResponseType(ApiUtilsFunctions::TYPE_NOTFOUND);
        }
    }

    /**
     * @return array
     * @throws BadRequestHttpException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionResetPasswordConfirm()
    {
        $request = Yii::$app->getRequest()->getBodyParams();
        $missing = [];

        if(!isset($request['id']))
        {
            $missing[] = 'id';
        }

        if(!isset($request['code']))
        {
            $missing[] = 'code';
        }

        if(!isset($request['password']))
        {
            $missing[] = 'password';
        }

        if(!isset($request['retypePassword']))
        {
            $missing[] = 'retypePassword';
        }

        if(count($missing) > 0)
        {
            throw new BadRequestHttpException(Yii::t('yii', 'Missing required parameters: {params}', [
                'params' => implode(', ', $missing),
            ]));
        }

        $user_id = $request['id'];
        $code = $request['code'];
        $password = $request['password'];
        $retypePassword = $request['retypePassword'];
        $data = [];

        $user = User::findOne($user_id);
        if($user)
        {
            if(User::checkCodeToken($user_id,$code))
            {
                try {
                    $model = new ResetPassword($user->password_reset_token);
                } catch (\Exception $e) {
                    throw new BadRequestHttpException($e->getMessage());
                }

                $model->password = $password;
                $model->retypePassword = $retypePassword;

                if($model->validate() && $model->resetPassword())
                {
                    $data[] = ['id' => $user_id];

                    $message = Yii::t('backend','Elemento actualizado correctamente');
                    return ApiUtilsFunctions::getResponseType(ApiUtilsFunctions::TYPE_SUCCESS,$message,$data);
                }
                else
                {
                    $message = Yii::t('backend','Error actualizando el elemento');
                    return ApiUtilsFunctions::getResponseType(ApiUtilsFunctions::TYPE_ERROR,$message,[]);
                }
            }
            else
            {
                $message = Yii::t('backend','El código enviado es incorrecto');
                return ApiUtilsFunctions::getResponseType(ApiUtilsFunctions::TYPE_ERROR,$message,[]);
            }
        }
        else
        {
            ApiUtilsFunctions::getResponseType(ApiUtilsFunctions::TYPE_NOTFOUND);
        }
    }
}