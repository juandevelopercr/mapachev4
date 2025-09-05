<?php

namespace backend\controllers;

use Yii;
use backend\models\business\ManualInvoice;
use backend\models\business\ManualInvoiceSearch;
use backend\models\business\ItemManualInvoice;
use backend\models\business\ItemInvoiceSearch;
use backend\models\business\ItemManualInvoiceSearch;
use backend\models\nomenclators\UtilsConstants;
use backend\models\nomenclators\Currency;
use common\models\GlobalFunctions;
use common\models\User;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\db\Exception;
use yii\web\Response;

/**
 * ManualInvoiceController implements the CRUD actions for ManualInvoice model.
 */
class ManualInvoiceController extends Controller
{
    /**
     * {@inheritdoc}
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
     * Lists all ManualInvoice models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new ManualInvoiceSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

/**
     * Displays a single Invoice model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        $searchModelItems = new ItemInvoiceSearch(['invoice_id' => $id]);
        $dataProviderItems = $searchModelItems->search(Yii::$app->request->queryParams);

        return $this->render('view', [
            'model' => $model,
            'dataProviderItems' => $dataProviderItems,
        ]);
    }

    /**
     * Creates a new Invoice model.
     * If creation is successful, the browser will be redirected to the 'index' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new ManualInvoice();
        $model->loadDefaultValues();
        $model->status = UtilsConstants::INVOICE_STATUS_PENDING;
        $currency = Currency::findOne(['symbol' => 'CRC']);
        if ($currency !== null) {
            $model->currency_id = $currency->id;
        }
        $model->emission_date = date('Y-m-d H:i:s');
        $model->branch_office_id = User::getBranchOfficeIdOfActiveUser();

        if ($model->load(Yii::$app->request->post())) {
            $model->emission_date = date('Y-m-d H:i:s');
            $model->consecutive = $model->generateConsecutive();
            $transaction = \Yii::$app->db->beginTransaction();

            try {
                if (ManualInvoice::find()->select(['consecutive'])->where(['consecutive' => $model->consecutive])->exists()) {
                    $model->consecutive = $model->generateConsecutive();
                }

                if ($model->save()) {

                    $transaction->commit();

                    GlobalFunctions::addFlashMessage('success', Yii::t('backend', 'Elemento creado correctamente'));

                    return $this->redirect(['update', 'id' => $model->id]);
                } else {
                    GlobalFunctions::addFlashMessage('danger', Yii::t('backend', 'Error creando el elemento'));
                }
            } catch (Exception $e) {
                GlobalFunctions::addFlashMessage('danger', Yii::t('backend', 'Error, ha ocurrido una excepción creando el elemento'));
                $transaction->rollBack();
            }
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Invoice model.
     * If update is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if (isset($model) && !empty($model)) {
            $searchModelItems = new ItemManualInvoiceSearch();
            $searchModelItems->invoice_id = $model->id;
            $dataProviderItems = $searchModelItems->search(Yii::$app->request->queryParams);

            $old_status = (int)$model->status;

            if ($model->load(Yii::$app->request->post())) {
                $transaction = \Yii::$app->db->beginTransaction();
                $total_items = ItemManualInvoice::find()->where(['invoice_id' => $id])->count();

                try {
                    if ($model->save()) {

                        $transaction->commit();

                        GlobalFunctions::addFlashMessage('success', Yii::t('backend', 'Elemento actualizado correctamente'));

                        return $this->redirect(['index']);
                    } else {
                        GlobalFunctions::addFlashMessage('danger', Yii::t('backend', 'Error actualizando el elemento'));
                    }
                } catch (Exception $e) {
                    GlobalFunctions::addFlashMessage('danger', Yii::t('backend', 'Error, ha ocurrido una excepción actualizando el elemento'));
                    $transaction->rollBack();
                }
            }
        } else {
            GlobalFunctions::addFlashMessage('warning', Yii::t('backend', 'El elemento buscado no existe'));
        }

        return $this->render('update', [
            'model' => $model,
            'searchModelItems' => $searchModelItems,
            'dataProviderItems' => $dataProviderItems,
        ]);
    }

    /**
     * Deletes an existing Invoice model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $transaction = \Yii::$app->db->beginTransaction();
        try {

            if ($model->delete()) {
                $transaction->commit();

                GlobalFunctions::addFlashMessage('success', Yii::t('backend', 'Elemento eliminado correctamente'));
            } else {
                GlobalFunctions::addFlashMessage('danger', Yii::t('backend', 'Error eliminando el elemento'));
            }
        } catch (Exception $e) {
            GlobalFunctions::addFlashMessage('danger', Yii::t('backend', 'Error, ha ocurrido una excepción eliminando el elemento'));
            $transaction->rollBack();
        }
        return $this->redirect(['index']);
    }

    /**
     * Finds the ManualInvoice model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return ManualInvoice the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = ManualInvoice::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    public function actionGetResumeInvoice($id)
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $model = ManualInvoice::find()->where(['id'=>$id])->one();
        
        $data = $model->getResumeInvoiceById($id);

        return \Yii::$app->response->data = [
            'total' => GlobalFunctions::formatNumber($data['total'], 2),
        ];
    }
    
}
