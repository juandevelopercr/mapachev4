<?php

namespace backend\controllers;

use common\components\Notification;
use common\models\GlobalFunctions;
use Yii;
use common\models\User;
use common\models\UserSearch;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;

/**
 * Agent controller
 */
class AgentController extends Controller
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
     * Lists all User models agents.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new UserSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->query->where(['item_name' => User::ROLE_AGENT])->all();
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single User model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
        
    }

    /**
     * Deletes an existing User model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
	    $model = $this->findModel($id);

	    if($model->username != User::IS_SUPERADMIN)
	    {
		    $avatar= $model->avatar;
		    $fileAvatar = $model->getImageFile();

		    if($model->delete())
		    {
				Yii::$app->authManager->revokeAll($model->id);
				
			    if ($avatar != null || $avatar != '')
			    {
				    if(file_exists($fileAvatar))
				    {
                        try{
                            unlink($fileAvatar);
                        }catch (\Exception $exception){
                            Yii::info("Error deleting image on UserController: " . $fileAvatar);
                            Yii::info($exception->getMessage());
                        }
				    }
			    }

			    GlobalFunctions::addFlashMessage('success',Yii::t('backend','Usuario eliminado satisfactoriamente'));
		    }
		    else
			    GlobalFunctions::addFlashMessage('danger',Yii::t('backend','Error eliminando el usuario'));
	    }
	    else
	    {
		    GlobalFunctions::addFlashMessage('danger',Yii::t('backend','No se puede eliminar el usuario superadmin del sistema'));
	    }

	    return $this->redirect(['index']);
    }

    /**
     * Finds the User model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return User the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = User::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    /**
     * Creates a new User model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     * @throws \yii\base\Exception
     */
	public function actionCreate()
	{
		$model = new User();
		$model->scenario= User::SCENARIO_CREATE;
        $model->switch_status = 1;
        $model->role = User::ROLE_AGENT;

		if($model->load(Yii::$app->request->post()))
		{
			$image = $model->uploadImage();

			$model->password_hash = Yii::$app->security->generatePasswordHash($model->password_hash);
			$model->auth_key= Yii::$app->security->generateRandomString();
			$model->auth_key_test= Yii::$app->security->generateRandomString();

			if($model->switch_status === '1')
				$model->status = 10;
			else
				$model->status = 0;

			$model_role = $model->role;

			$allScenarios= $model->scenarios();

			if($model->save(true,$allScenarios[$model->scenario]))
			{
				if($model->save())
                {
                    $role = Yii::$app->authManager->getRole($model_role);
                    Yii::$app->authManager->revokeAll($model->id);
                    Yii::$app->authManager->assign($role, $model->id);
                }

				// upload only if valid uploaded file instance found
				if ($image !== false) {
					$path = $model->getImageFile();
					$image->saveAs($path);
				}

                Notification::notify(Notification::KEY_NEW_USER_REGISTRED, 1, $model->id);

				GlobalFunctions::addFlashMessage('success',Yii::t('backend','Usuario creado satisfactoriamente'));

				return $this->redirect(['index']);

			}
			else
			{
                die(var_dump($model->getErrors()));
				GlobalFunctions::addFlashMessage('danger',Yii::t('backend','Error creando el usuario'));

				return $this->render('create', ['model' => $model]);
			}

		}

		return $this->render('create', ['model' => $model]);
	}

	/**
	 * Updates an existing User model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 * @param integer $id
	 * @return mixed
	 */
	public function actionUpdate($id)
	{
		$model = $this->findModel($id);
		$model->scenario= User::SCENARIO_UPDATE;
		$model->password_hash= '';

		$oldFile = $model->getImageFile();
		$oldAvatar = $model->avatar;

		if($model->status === 10)
			$model->switch_status = 1;
		else
			$model->switch_status = 0;

		$old_role = GlobalFunctions::getRol($model->id);

		$model->role = $old_role;

		if($model->load(Yii::$app->request->post()))
		{
			if($model->switch_status === '1')
				$model->status = 10;
			else
				$model->status = 0;

			// process uploaded image file instance
			$image = $model->uploadImage();

			// revert back if no valid file instance uploaded
			if ($image === false) {
				$model->avatar = $oldAvatar;
			}

			if(empty($model->password_hash))
				$model->password_hash = $model->getOldAttribute('password_hash');
			else
				$model->password_hash = Yii::$app->security->generatePasswordHash($model->password_hash);

			$allScenarios= $model->scenarios();

			if($model->save(true,$allScenarios[$model->scenario]))
			{
                $model->save();

				// upload only if valid uploaded file instance found
				if ($image !== false) // delete old and overwrite
				{
					if(file_exists($oldFile))
					{
                        try{
                            unlink($oldFile);
                        }catch (\Exception $exception){
                            Yii::info("Error deleting image on AgentController: " . $oldFile);
                            Yii::info($exception->getMessage());
                        }
					}

					$path = $model->getImageFile();
					$image->saveAs($path);
				}

				GlobalFunctions::addFlashMessage('success',Yii::t('backend','Usuario actualizado satisfactoriamente'));
				return $this->redirect(['index']);
			}
			else
			{
				GlobalFunctions::addFlashMessage('danger',Yii::t('backend','Error actualizando el usuario'));
				return $this->render('update', ['model' => $model,]);
			}


		} else {
			return $this->render('update', ['model' => $model,]);
		}
	}

    /**
     * Bulk Deletes for existing User models.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @return mixed
     */
    public function actionMultiple_delete()
    {
        if(Yii::$app->request->post('row_id'))
        {
            $pk = Yii::$app->request->post('row_id');
            $count_elements = count($pk);

            $deleteOK = true;
            $nameErrorDelete = '';
            $contNameErrorDelete = 0;

            foreach ($pk as $key => $value)
            {
                $model= $this->findModel($value);

                if($model->username !== User::IS_SUPERADMIN)
                {
                    $avatar= $model->avatar;
                    $fileAvatar = $model->getImageFile();

                    if($model->delete())
                    {
						Yii::$app->authManager->revokeAll($model->id);
						
                        if ($avatar != null || $avatar != '')
                        {
                            if(file_exists($fileAvatar))
                            {
                                try{
                                    unlink($fileAvatar);
                                }catch (\Exception $exception){
                                    Yii::info("Error deleting image on UserController: " . $fileAvatar);
                                    Yii::info($exception->getMessage());
                                }
                            }
                        }
                    }
                    else
                    {
                        $deleteOK=false;
                        $nameErrorDelete= $nameErrorDelete.'['.$model->name.'] ';
                        $contNameErrorDelete++;
                    }
                }
            }

            if($deleteOK)
            {
                if($count_elements === 1)
                    GlobalFunctions::addFlashMessage('success',Yii::t('backend','Elemento eliminado correctamente'));
                else
                    GlobalFunctions::addFlashMessage('success',Yii::t('backend','Elementos eliminados correctamente'));
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

            return $this->redirect(['index']);
        }
    }
}