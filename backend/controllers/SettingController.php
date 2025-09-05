<?php

namespace backend\controllers;

use backend\models\settings\Issuer;
use Yii;
use backend\models\settings\Setting;
use yii\db\Exception;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use common\models\GlobalFunctions;

/**
 * SettingController implements the CRUD actions for Setting model.
 */
class SettingController extends Controller
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
                ],
            ],
        ];
    }

    /**
     * Updates an existing Setting model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if(isset($model) && !empty($model))
        {
	        $old_file_main_logo = $model->getImageFile(1);
            $old_file_header_logo = $model->getImageFile(2);
            $old_file_mini_header_logo = $model->getImageFile(3);
            $old_file_back_image_login = $model->getImageFile(4);

	        $old_main_logo = $model->main_logo;
            $old_header_logo = $model->header_logo;
            $old_mini_header_logo = $model->mini_header_logo;
            $old_back_image_login = $model->back_image_login;

            if ($model->load(Yii::$app->request->post()))
            {
	            // process uploaded main_logo file instance
	            $main_logo = $model->uploadImage('file_main_logo',1);
	            $header_logo = $model->uploadImage('file_header_logo',2);
	            $mini_header_logo = $model->uploadImage('file_mini_header_logo',3);
	            $back_image_login = $model->uploadImage('file_back_image_login',4);

	            // revert back if no valid file instance uploaded
	            if ($main_logo === false) {
		            $model->main_logo = $old_main_logo;
	            }

                // revert back if no valid file instance uploaded
                if ($header_logo === false) {
                    $model->header_logo = $old_header_logo;
                }

                // revert back if no valid file instance uploaded
                if ($mini_header_logo === false) {
                    $model->mini_header_logo = $old_mini_header_logo;
                }

                // revert back if no valid file instance uploaded
                if ($back_image_login === false) {
                    $model->back_image_login = $old_back_image_login;
                }

                if($model->save())
                {
	                // upload only if valid uploaded file instance found by main logo
	                if ($main_logo !== false) // delete old and overwrite
	                {
		                if(file_exists($old_file_main_logo))
		                {
                            try{
                                unlink($old_file_main_logo);
                            }catch (\Exception $exception){
                                Yii::info("Error deleting image on Setting: " . $old_file_main_logo);
                                Yii::info($exception->getMessage());
                            }
		                }

		                $path = $model->getImageFile(1);
		                $main_logo->saveAs($path);
	                }


                    // upload only if valid uploaded file instance found by header logo
                    if ($header_logo !== false) // delete old and overwrite
                    {
                        if(file_exists($old_file_header_logo))
                        {
                            try{
                                unlink($old_file_header_logo);
                            }catch (\Exception $exception){
                                Yii::info("Error deleting image on Setting: " . $old_file_header_logo);
                                Yii::info($exception->getMessage());
                            }
                        }

                        $path = $model->getImageFile(2);
                        $header_logo->saveAs($path);
                    }


                    // upload only if valid uploaded file instance found by mini header logo
                    if ($mini_header_logo !== false) // delete old and overwrite
                    {
                        if(file_exists($old_file_mini_header_logo))
                        {
                            try{
                                unlink($old_file_mini_header_logo);
                            }catch (\Exception $exception){
                                Yii::info("Error deleting image on Setting: " . $old_file_mini_header_logo);
                                Yii::info($exception->getMessage());
                            }
                        }

                        $path = $model->getImageFile(3);
                        $mini_header_logo->saveAs($path);
                    }

                    // upload only if valid uploaded file instance found by mini header logo
                    if ($back_image_login !== false) // delete old and overwrite
                    {
                        if(file_exists($old_file_back_image_login))
                        {
                            try{
                                unlink($old_file_back_image_login);
                            }catch (\Exception $exception){
                                Yii::info("Error deleting image on Setting: " . $old_file_back_image_login);
                                Yii::info($exception->getMessage());
                            }
                        }

                        $path = $model->getImageFile(4);
                        $back_image_login->saveAs($path);
                    }

                    GlobalFunctions::setFlashMessage('success',Yii::t('backend','Elemento actualizado correctamente'));

                    return $this->redirect(['update','id'=>$id]);
                }
                else
                {
                    GlobalFunctions::setFlashMessage('danger',Yii::t('backend','Error actualizando el elemento'));
                }
            }
        }
        else
        {
            GlobalFunctions::setFlashMessage('warning',Yii::t('backend','El elemento buscado no existe'));
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Setting model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionUpdateEmailAlert($id)
    {
        $model = $this->findModel($id);

        if(isset($model) && !empty($model))
        {

            if ($model->load(Yii::$app->request->post()))
            {
                $errors = 0;
                if(!GlobalFunctions::validateCCMails($model->proforma_stock_alert_mails))
                {
                    $errors++;
                    $model->addError('proforma_stock_alert_mails',Yii::t('backend','Formato de correos incorrecto'));
                }

                if(!GlobalFunctions::validateCCMails($model->product_price_change_mails))
                {
                    $errors++;
                    $model->addError('product_price_change_mails',Yii::t('backend','Formato de correos incorrecto'));
                }

                if($errors > 0)
                {
                    GlobalFunctions::setFlashMessage('danger',Yii::t('backend','Error actualizando el elemento'));

                    return $this->render('update_alerts', [
                        'model' => $model,
                    ]);
                }

                if($model->save())
                {

                    GlobalFunctions::setFlashMessage('success',Yii::t('backend','Elemento actualizado correctamente'));

                    return $this->redirect(['update-email-alert','id' => $id]);
                }
                else
                {
                    GlobalFunctions::setFlashMessage('danger',Yii::t('backend','Error actualizando el elemento'));
                }
            }
        }
        else
        {
            GlobalFunctions::setFlashMessage('warning',Yii::t('backend','El elemento buscado no existe'));
        }

        return $this->render('update_alerts', [
            'model' => $model,
        ]);
    }

    /**
     * Finds the Setting model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Setting the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Setting::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('backend','La página solicitada no existe'));
        }
    }

    /**
     * Updates an existing Issuer model.
     * If update is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate_issuer($id)
    {
        $model = $this->findModelIssuer($id);

        if(isset($model) && !empty($model))
        {
            $old_file_main_logo = $model->getImageFile(1);
            $old_file_signature_digital = $model->getImageFile(2);

            $old_main_logo = $model->logo_file;
            $old_signature_digital = $model->signature_digital_file;

            $old_certificate_file = $model->getCertificateFile();
            $old_certificate = $model->certificate_digital_file;

            //$model->change_type_dollar = ApiBCCR::getChangeTypeOfIssuer();

            if ($model->load(Yii::$app->request->post()))
            {
                $transaction = \Yii::$app->db->beginTransaction();

                try
                {
                    // process uploaded main_logo file instance
                    $main_logo = $model->uploadImage('file_main_logo',1);
                    $signature_digital = $model->uploadImage('file_signature_digital',2);
                    // revert back if no valid file instance uploaded
                    if ($main_logo === false) {
                        $model->logo_file = $old_main_logo;
                    }

                    // revert back if no valid file instance uploaded
                    if ($signature_digital === false) {
                        $model->signature_digital_file = $old_signature_digital;
                    }

                    $certificate = $model->uploadCertificate();

                    // revert back if no valid file instance uploaded
                    if ($certificate === false) {
                        $model->certificate_digital_file = $old_certificate;
                    }

                    if($model->save())
                    {
                        // upload only if valid uploaded file instance found by main logo
                        if ($main_logo !== false) // delete old and overwrite
                        {
                            if(file_exists($old_file_main_logo))
                            {
                                try{
                                    unlink($old_file_main_logo);
                                }catch (\Exception $exception){
                                    Yii::info("Error deleting image on Issuer: " . $old_file_main_logo);
                                    Yii::info($exception->getMessage());
                                }
                            }

                            $path = $model->getImageFile(1);
                            $main_logo->saveAs($path);
                        }


                        // upload only if valid uploaded file instance found by header logo
                        if ($signature_digital !== false) // delete old and overwrite
                        {
                            if(file_exists($old_file_signature_digital))
                            {
                                try{
                                    unlink($old_file_signature_digital);
                                }catch (\Exception $exception){
                                    Yii::info("Error deleting image on Setting: " . $old_file_signature_digital);
                                    Yii::info($exception->getMessage());
                                }
                            }

                            $path = $model->getImageFile(2);
                            $signature_digital->saveAs($path);
                        }

                        // upload only if valid uploaded file instance found by main logo
                        if ($certificate !== false) // delete old and overwrite
                        {
                            if(file_exists($old_certificate_file))
                            {
                                try{
                                    unlink($old_certificate_file);
                                }catch (\Exception $exception){
                                    Yii::info("Error deleting certificate on Issuer: " . $old_certificate_file);
                                    Yii::info($exception->getMessage());
                                }
                            }

                            $path = $model->getCertificateFile();
                            $certificate->saveAs($path);
                        }

                        $transaction->commit();

                        GlobalFunctions::addFlashMessage('success',Yii::t('backend','Elemento actualizado correctamente'));

                        return $this->redirect(['update_issuer','id'=>$id]);

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

        return $this->render('update_issuer', [
            'model' => $model,
        ]);

    }

    /**
     * Finds the Issuer model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Issuer the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModelIssuer($id)
    {
        if (($model = Issuer::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('backend','La página solicitada no existe'));
        }
    }
}
