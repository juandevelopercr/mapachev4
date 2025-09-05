<?php

namespace backend\controllers;

use backend\models\business\MovementCashRegister;
use Yii;
use backend\models\business\MovementCashRegisterDetail;
use backend\models\business\MovementCashRegisterDetailSearch;
use common\models\GlobalFunctions;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\db\Exception;
use yii\web\Response;

/**
 * MovementCashRegisterDetailController implements the CRUD actions for MovementCashRegisterDetail model.
 */
class MovementCashRegisterDetailController extends Controller
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
     * Lists all MovementCashRegisterDetail models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new MovementCashRegisterDetailSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single MovementCashRegisterDetail model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new MovementCashRegisterDetail model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new MovementCashRegisterDetail();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing MovementCashRegisterDetail model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing MovementCashRegisterDetail model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDeleteEntrada($id, $cash_register_id, $box_id)
    {
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $movimiento_detail = $this->findModel($id);
            $movimiento = MovementCashRegister::find()->where(['id'=>$movimiento_detail->movement_cash_register_id])->one();
            
            if ($movimiento->delete())
                $transaction->commit();

        } catch (Exception $e) {
            $transaction->rollBack();
            
            GlobalFunctions::addFlashMessage('danger', Yii::t('backend', 'Error, ha ocurrido una excepción abriendo la caja'));                
        }            
        return $this->redirect(['/cash-register/adicionar', 'cash_register_id'=>$cash_register_id, 'box_id'=>$box_id]);
    }

    public function actionDeleteSalida($id, $cash_register_id, $box_id)
    {
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $movimiento_detail = $this->findModel($id);
            $movimiento = MovementCashRegister::find()->where(['id'=>$movimiento_detail->movement_cash_register_id])->one();
            
            if ($movimiento->delete())
                $transaction->commit();

        } catch (Exception $e) {
            $transaction->rollBack();
            
            GlobalFunctions::addFlashMessage('danger', Yii::t('backend', 'Error, ha ocurrido una excepción abriendo la caja'));                
        }            
        return $this->redirect(['/cash-register/retirar', 'cash_register_id'=>$cash_register_id, 'box_id'=>$box_id]);
    }    

    /**
     * Finds the MovementCashRegisterDetail model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return MovementCashRegisterDetail the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = MovementCashRegisterDetail::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
