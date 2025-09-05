<?php

namespace backend\controllers;

use backend\models\business\SupplierBankInformationSearch;
use backend\models\business\SupplierContactSearch;
use backend\models\nomenclators\ConditionSale;
use Yii;
use backend\models\business\Supplier;
use backend\models\business\SupplierSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use common\models\GlobalFunctions;
use yii\helpers\Url;
use yii\db\Exception;

/**
 * SupplierController implements the CRUD actions for Supplier model.
 */
class SupplierController extends Controller
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
     * Lists all Supplier models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new SupplierSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Supplier model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);

        $searchModelContacts = new SupplierContactSearch(['supplier_id' => $id]);
        $dataProviderContacts = $searchModelContacts->search(Yii::$app->request->queryParams);

        $searchModelBank = new SupplierBankInformationSearch(['supplier_id' => $id]);
        $dataProviderBank = $searchModelBank->search(Yii::$app->request->queryParams);

        return $this->render('view', [
            'model' => $model,
            'dataProviderContacts' => $dataProviderContacts,
            'dataProviderBank' => $dataProviderBank,
        ]);
    }

    /**
     * Creates a new Supplier model.
     * If creation is successful, the browser will be redirected to the 'index' page.
     * @return mixed
     */
    public function actionCreate($pre_model = null, $return_import = false)
    {

        if($pre_model === null)
        {
            $model = new Supplier();
            $model->loadDefaultValues();
            $model->status = 1;
            $model->entry_date = date('Y-m-d');
        }
        else
        {
            $model = unserialize(urldecode($pre_model));
        }

        $model->code = $model->generateCode();

        if ($model->load(Yii::$app->request->post()))
        {
            $transaction = \Yii::$app->db->beginTransaction();

            try
            {
                if(Supplier::find()->select(['code'])->where(['code' => $model->code])->exists())
                {
                    $model->code = $model->generateCode();
                }
                $errors = 0;

                if ($model->condition_sale_id == ConditionSale::getIdCreditConditionSale() && ((empty($model->colon_credit) || $model->colon_credit <= 0)) && (empty($model->dollar_credit) || $model->dollar_credit <= 0))
                {
                    $errors++;
                    $model->addError('colon_credit',Yii::t('backend','Monto de crédito ¢ no puede estar vacío'));
                    $model->addError('dollar_credit',Yii::t('backend','Monto de crédito $ no puede estar vacío'));
                }


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
                        'return_import' => $return_import,

                    ]);
                }

                if($model->save())
                {
                    $transaction->commit();

                    GlobalFunctions::addFlashMessage('success',Yii::t('backend','Elemento creado correctamente'));

                    if($return_import)
                    {
                        return $this->redirect(['entry/import_xml','supplier_id'=>$model->id]);
                    }
                    else
                    {
                        return $this->redirect(['index']);
                    }
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
            'return_import' => $return_import,
        ]);

    }

    /**
     * Updates an existing Supplier model.
     * If update is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $model->status = 1;

        $searchModelContacts = new SupplierContactSearch();
        $searchModelContacts->supplier_id = $model->id;
        $dataProviderContacts = $searchModelContacts->search(Yii::$app->request->queryParams);

        $searchModelBankInformation = new SupplierBankInformationSearch();
        $searchModelBankInformation->supplier_id = $model->id;
        $dataProviderBankInformation = $searchModelBankInformation->search(Yii::$app->request->queryParams);

        if(isset($model) && !empty($model))
        {
            if ($model->load(Yii::$app->request->post()))
            {
                $transaction = \Yii::$app->db->beginTransaction();

                try
                {
                    $errors = 0;

                    if ($model->condition_sale_id == ConditionSale::getIdCreditConditionSale() && ((empty($model->colon_credit) || $model->colon_credit <= 0)) && (empty($model->dollar_credit) || $model->dollar_credit <= 0))
                    {
                        $errors++;
                        $model->addError('colon_credit',Yii::t('backend','Monto de crédito ¢ no puede estar vacío'));
                        $model->addError('dollar_credit',Yii::t('backend','Monto de crédito $ no puede estar vacío'));
                    }


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
                            'searchModelBankInformation' => $searchModelBankInformation,
                            'dataProviderBankInformation' => $dataProviderBankInformation,
                        ]);
                    }

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
            'searchModelBankInformation' => $searchModelBankInformation,
            'dataProviderBankInformation' => $dataProviderBankInformation,
        ]);

    }

    /**
     * Deletes an existing Supplier model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);

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
            GlobalFunctions::addFlashMessage('danger',Yii::t('backend','Error, ha ocurrido una excepción eliminando el elemento'));
            $transaction->rollBack();
        }
    }

    /**
     * Finds the Supplier model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Supplier the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Supplier::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('backend','La página solicitada no existe'));
        }
    }

    /**
    * Bulk Deletes for existing Supplier models.
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

        return [
          'condition_sale_id' => $model->condition_sale_id,
          'credit_days_id' => (isset($model->credit_days_id) && !empty($model->credit_days_id))? $model->credit_days_id : 0,
        ];
    }
}
