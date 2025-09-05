<?php

namespace backend\controllers;

use backend\models\business\CollectorHasCustomer;
use backend\models\business\Customer;
use backend\models\business\CustomerContactSearch;
use backend\models\business\CustomerSearch;
use backend\models\business\SellerHasCustomer;
use backend\models\nomenclators\ConditionSale;
use backend\models\settings\Issuer;
use common\components\ftp\FtpImportTask;
use common\models\GlobalFunctions;
use Yii;
use yii\db\Exception;
use yii\filters\VerbFilter;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * CustomerController implements the CRUD actions for Customer model.
 */
class CustomerController extends Controller
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
     * Lists all Customer models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new CustomerSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionImportData()
    {
        $emisor = Issuer::find()->where(['id'=>1])->one();            
        $ftpHost = $emisor->ftp_host;
        $ftpUser = $emisor->ftp_user;
        $ftpPass = $emisor->ftp_password;

        try {
            $ftpImportTask = new FtpImportTask($ftpHost, $ftpUser, $ftpPass);
            $ftpImportTask->run(true);
            Yii::$app->session->setFlash('success', 'Import completed successfully.');
        } catch (\Exception $e) {
            Yii::error('Error during FTP import: ' . $e->getMessage(), __METHOD__);
            Yii::$app->session->setFlash('error', 'Error during import: ' . $e->getMessage());
        }

        Yii::$app->response->format = 'json';
        return [
            'result'=>true
        ];
    }

    /**
     * Displays a single Customer model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);

        $searchModelContacts = new CustomerContactSearch(['customer_id' => $id]);
        $dataProviderContacts = $searchModelContacts->search(Yii::$app->request->queryParams);

        return $this->render('view', [
            'model' => $model,
            'dataProviderContacts' => $dataProviderContacts,
        ]);
    }

    /**
     * Creates a new Customer model.
     * If creation is successful, the browser will be redirected to the 'index' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Customer();
        $model->loadDefaultValues();
        $model->status = 1;
        $model->code = $model->generateCode();

        if ($model->load(Yii::$app->request->post()))
        {
            $transaction = \Yii::$app->db->beginTransaction();

            try
            {
                if(Customer::find()->select(['code'])->where(['code' => $model->code])->exists())
                {
                    $model->code = $model->generateCode();
                }
                $errors = 0;
                /*
                if ($model->condition_sale_id == ConditionSale::getIdCreditConditionSale() && ((empty($model->credit_amount_colon) || $model->credit_amount_colon <= 0)) && (empty($model->credit_amount_usd) || $model->credit_amount_usd <= 0))
                {
                    $errors++;
                    $model->addError('credit_amount_colon',Yii::t('backend','Monto de crédito ¢ no puede estar vacío'));
                    $model->addError('credit_amount_usd',Yii::t('backend','Monto de crédito $ no puede estar vacío'));
                }
                */

                if ($model->condition_sale_id == ConditionSale::getIdCreditConditionSale() && ((empty($model->credit_days_id) || is_null($model->credit_days_id))))
                {
                    $errors++;
                    $model->addError('credit_days_id',Yii::t('backend','Días de crédito no puede estar vacío'));
                }

                if($errors > 0)
                {
                    GlobalFunctions::addFlashMessage('danger',Yii::t('backend','Error creando el elemento'));

                    return $this->render('create', [
                        'model' => $model,
                    ]);
                }
                $model->is_exonerate = 0;
                if($model->save())
                {
                    //SellerHasCustomer::updateRelation($model, [], 'sellers', 'seller_id');

                    //CollectorHasCustomer::updateRelation($model, [], 'collectors', 'collector_id');

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
     * Updates an existing Customer model.
     * If update is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $model->status = 1;

        $searchModelContacts = new CustomerContactSearch();
        $searchModelContacts->customer_id = $model->id;
        $dataProviderContacts = $searchModelContacts->search(Yii::$app->request->queryParams);

        //BEGIN seller has seller
        /*
        $seller_assigned = SellerHasCustomer::getSellerByCustomerId($id);

        $seller_assigned_ids = [];
        foreach ($seller_assigned as $value) {
            $seller_assigned_ids[] = $value['seller_id'];
        }

        $model->sellers = $seller_assigned_ids;
        //END seller method has seller


        //BEGIN collector has collector
        $collector_assigned = CollectorHasCustomer::getCollectorByCustomerId($id);

        $collector_assigned_ids = [];
        foreach ($collector_assigned as $value) {
            $collector_assigned_ids[] = $value['collector_id'];
        }

        $model->collectors = $collector_assigned_ids;
        //END seller method has collector
        */


        if(isset($model) && !empty($model))
        {
            if ($model->load(Yii::$app->request->post()))
            {
                $transaction = \Yii::$app->db->beginTransaction();

                try
                {
                    $errors = 0;
                    /*
                    if ($model->condition_sale_id == ConditionSale::getIdCreditConditionSale() && ((empty($model->credit_amount_colon) || $model->credit_amount_colon <= 0)) && (empty($model->credit_amount_usd) || $model->credit_amount_usd <= 0))
                    {
                        $errors++;
                        $model->addError('credit_amount_colon',Yii::t('backend','Monto de crédito ¢ no puede estar vacío'));
                        $model->addError('credit_amount_usd',Yii::t('backend','Monto de crédito $ no puede estar vacío'));
                    }
                    */
                    if ($model->condition_sale_id == ConditionSale::getIdCreditConditionSale() && ((empty($model->credit_days_id) || is_null($model->credit_days_id))))
                    {
                        $errors++;
                        $model->addError('credit_days_id',Yii::t('backend','Días de crédito no puede estar vacío'));
                    }

                    if($errors > 0)
                    {
                        GlobalFunctions::addFlashMessage('danger',Yii::t('backend','Error creando el elemento'));

                        return $this->render('update', [
                            'model' => $model,
                            'searchModelContacts' => $searchModelContacts,
                            'dataProviderContacts' => $dataProviderContacts,
                        ]);
                    }

                    //SellerHasCustomer::updateRelation($model, $seller_assigned, 'sellers', 'seller_id');

                    //CollectorHasCustomer::updateRelation($model, $collector_assigned, 'collectors', 'collector_id');

                    $model->is_exonerate = 0;
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
            'searchModelContacts' => $searchModelContacts,
            'dataProviderContacts' => $dataProviderContacts,
        ]);

    }

    /**
     * Deletes an existing Customer model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        if ($model->id != 1)
        {
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
                GlobalFunctions::addFlashMessage('danger',Yii::t('backend','Error, ha ocurrido una excepción eliminando el elemento. El cliente tiene facturas asociadas, por lo que no se puede eliminar'));
                $transaction->rollBack();
                return $this->redirect(['index']); 
            }
        }
        else{
           GlobalFunctions::addFlashMessage('warning',Yii::t('backend','No se puede eliminar el cliente contado'));
           return $this->redirect(['index']); 
        }
    }

    /**
     * Finds the Customer model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Customer the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Customer::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('backend','La página solicitada no existe'));
        }
    }

    /**
    * Bulk Deletes for existing Customer models.
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

    public function actionGet_info($id)
    {
        $model = $this->findModel($id);
        Yii::$app->response->format = 'json';
        
        /*
        $sellers_assigned = SellerHasCustomer::getSellerByCustomerId($id);

        $sellers_assigned_ids = [];
        foreach ($sellers_assigned as $value) {
            $sellers_assigned_ids[] = $value['seller_id'];
        }

        $collectors_assigned = CollectorHasCustomer::getCollectorByCustomerId($id);
        $collectors_assigned_ids = [];
        foreach ($collectors_assigned as $value) {
            $collectors_assigned_ids[] = $value['collector_id'];
        }  
            */
        return [
            'condition_sale_id' => $model->condition_sale_id,
            'credit_days_id' => (isset($model->credit_days_id) && !empty($model->credit_days_id))? $model->credit_days_id : 0,            
            'commercial_name' => $model->commercial_name,
            //'sellers' => $sellers_assigned_ids,
            //'collectors' => $collectors_assigned_ids,
            //'route_transport_id' => (isset($model->route_transport_id) && !empty($model->route_transport_id))? $model->route_transport_id : 0,
            'invoice_type' => (isset($model->pre_invoice_type) && !empty($model->pre_invoice_type))? $model->pre_invoice_type : 0,
        ];
    }

}
