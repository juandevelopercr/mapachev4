<?php

namespace backend\controllers;

use backend\models\business\PhysicalLocation;
use backend\models\business\Product;
use backend\models\business\ProductHasBranchOffice;
use backend\models\business\SectorLocation;
use backend\models\nomenclators\BranchOffice;
use backend\models\nomenclators\UtilsConstants;
use Mpdf\Tag\U;
use Yii;
use backend\models\business\Adjustment;
use backend\models\business\AdjustmentSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use common\models\GlobalFunctions;
use yii\helpers\Url;
use yii\db\Exception;

/**
 * AdjustmentController implements the CRUD actions for Adjustment model.
 */
class AdjustmentController extends Controller
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
     * Finds the Adjustment model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Adjustment the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Adjustment::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('backend','La página solicitada no existe'));
        }
    }

    /**
     * Lists all Adjustment models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new AdjustmentSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->query->andWhere(['type' => UtilsConstants::ADJUSTMENT_TYPE_ADJUSTMENT])->all();

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Lists all Adjustment-Transfer models.
     * @return mixed
     */
    public function actionIndex_transfer()
    {
        $searchModel = new AdjustmentSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->query->andWhere(['type' => UtilsConstants::ADJUSTMENT_TYPE_TRANFER])->all();

        return $this->render('index_transfer', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Lists all Adjustment-Transfer models.
     * @return mixed
     */
    public function actionIndex_decrease()
    {
        $searchModel = new AdjustmentSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->query->andWhere(['type' => UtilsConstants::ADJUSTMENT_TYPE_DECREASE])->all();

        return $this->render('index_decrease', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Lists all Adjustment-Output models.
     * @return mixed
     */
    public function actionIndex_output_invoice()
    {
        $searchModel = new AdjustmentSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->query->andWhere(['type' => UtilsConstants::ADJUSTMENT_TYPE_INVOICE_SALES])->all();

        return $this->render('index_output_invoice', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Adjustment model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);

        return $this->render('view', [
            'model' => $model,
        ]);
    }

    /**
     * Creates a new Adjustment model.
     * If creation is successful, the browser will be redirected to the 'index' page.
     * @return mixed
     */
    public function actionCreate($type = null)
    {
        $model = new Adjustment();
        $model->loadDefaultValues();
        $model->consecutive = $model->generateCode();
        if($type === null)
        {
            $model->type = UtilsConstants::ADJUSTMENT_TYPE_ADJUSTMENT;
        }
        else
        {
            $model->type = $type;
        }

        $model_type = (int) $model->type;

        $model->user_id = Yii::$app->user->id;

        $default_branch_office = BranchOffice::find()->select(['id'])->one();
        if($default_branch_office !== null)
        {
            $model->origin_branch_office_id = $default_branch_office->id;

            $default_sector_location = SectorLocation::getDefaultSectorLocation($default_branch_office->id);
            if($default_sector_location !== null)
            {
                $model->origin_sector_location_id = $default_sector_location->id;
            }
        }


        if ($model->load(Yii::$app->request->post()))
        {
            $errors = 0;
            $model->user_id = Yii::$app->user->id;
            if($model_type === UtilsConstants::ADJUSTMENT_TYPE_TRANFER)
            {
                if($model->origin_sector_location_id == $model->target_sector_location_id)
                {
                    $model->addError('origin_sector_location_id',Yii::t('backend','Ubicación origen no puede ser igual a Ubicación destino'));
                    $model->addError('target_sector_location_id',Yii::t('backend','Ubicación destino no puede ser igual a Ubicación origen'));
                    $errors++;
                }
                else
                {
                    if(empty($model->target_sector_location_id))
                    {
                        $model->addError('target_sector_location_id',Yii::t('backend','Ubicación destino no puede estar vacía'));
                        $errors++;
                    }
                }

                $old_quantity = PhysicalLocation::getQuantity($model->product_id, $model->origin_sector_location_id);

                if($model->entry_quantity > $old_quantity)
                {
                    $model->addError('entry_quantity',Yii::t('backend','Cantidad a trasladar excede la cantidad de {total} almacenada de este producto',['total' => GlobalFunctions::formatNumber($old_quantity,2)]));
                    $errors++;
                }
            }
            elseif($model_type === UtilsConstants::ADJUSTMENT_TYPE_DECREASE)
            {
                $old_quantity_1 = PhysicalLocation::getQuantity($model->product_id, $model->origin_sector_location_id);
                if($model->entry_quantity > $old_quantity_1)
                {
                    $model->addError('entry_quantity',Yii::t('backend','Cantidad ingresa excede la cantidad de {total} almacenada de este producto',['total' => GlobalFunctions::formatNumber($old_quantity_1,2)]));
                    $errors++;
                }
            }

            if($errors > 0)
            {
                GlobalFunctions::addFlashMessage('danger',Yii::t('backend','Error creando el elemento'));

                return $this->render('create', [
                    'model' => $model,
                ]);
            }

            $transaction = \Yii::$app->db->beginTransaction();

            try
            {
                if(Adjustment::find()->select(['consecutive'])->where(['consecutive' => $model->consecutive])->exists())
                {
                    $model->consecutive = $model->generateCode();
                }

                $model->origin_branch_office_id = $model->originSectorLocation->sector->branch_office_id;
                $model->past_quantity = ProductHasBranchOffice::getQuantity($model->product_id, $model->origin_branch_office_id);
                if(isset($model->target_sector_location_id) && !empty($model->target_sector_location_id))
                {
                    $model->target_branch_office_id = $model->targetSectorLocation->sector->branch_office_id;
                }

                if($model->save())
                {
                    if($model_type === UtilsConstants::ADJUSTMENT_TYPE_ADJUSTMENT)
                    {
                        $model->new_quantity = $model->entry_quantity;

                        //Actualizar cantidad en la ubicacion especifica del sector de una sucursal
                        PhysicalLocation::updateQuantity($model->product_id, $model->origin_sector_location_id, $model->entry_quantity, PhysicalLocation::CHANGE_QUANTITY_SET);

                        if($model->entry_quantity !== $model->past_quantity)
                        {
                            if($model->entry_quantity > $model->past_quantity)
                            {
                                $total_add = $model->entry_quantity - $model->past_quantity;

                                //Actualizar cantidad general de una sucursal
                                ProductHasBranchOffice::updateQuantity($model->product_id, $model->origin_branch_office_id, $total_add,ProductHasBranchOffice::CHANGE_QUANTITY_PLUS);

                                //Actualizar cantidad total del producto
                                Product::updateQuantity($model->product_id,$total_add,Product::CHANGE_QUANTITY_PLUS);
                            }
                            else
                            {
                                $total_minus = $model->past_quantity - $model->entry_quantity;

                                //Actualizar cantidad general de una sucursal
                                ProductHasBranchOffice::updateQuantity($model->product_id, $model->origin_branch_office_id, $total_minus,ProductHasBranchOffice::CHANGE_QUANTITY_MINUS);

                                //Actualizar cantidad total del producto
                                Product::updateQuantity($model->product_id,$total_minus,Product::CHANGE_QUANTITY_MINUS);
                            }
                        }


                    }
                    elseif($model_type === UtilsConstants::ADJUSTMENT_TYPE_TRANFER)
                    {
                        //restar de sucursal origen

                            //Actualizar cantidad en la ubicacion especifica del sector de una sucursal
                            PhysicalLocation::updateQuantity($model->product_id, $model->origin_sector_location_id, $model->entry_quantity, PhysicalLocation::CHANGE_QUANTITY_MINUS);

                            //Actualizar cantidad general de una sucursal
                            ProductHasBranchOffice::updateQuantity($model->product_id, $model->origin_branch_office_id, $model->entry_quantity,ProductHasBranchOffice::CHANGE_QUANTITY_MINUS);

                        //sumar a sucursal destino
                            //Actualizar cantidad en la ubicacion especifica del sector de una sucursal
                            PhysicalLocation::updateQuantity($model->product_id, $model->target_sector_location_id, $model->entry_quantity, PhysicalLocation::CHANGE_QUANTITY_PLUS);

                            //Actualizar cantidad general de una sucursal
                            ProductHasBranchOffice::updateQuantity($model->product_id, $model->target_branch_office_id, $model->entry_quantity,ProductHasBranchOffice::CHANGE_QUANTITY_PLUS);

                        $model->new_quantity = $model->entry_quantity;
                    }
                    elseif($model_type === UtilsConstants::ADJUSTMENT_TYPE_DECREASE)
                    {
                        $model->new_quantity = $model->past_quantity - $model->entry_quantity;

                        //Actualizar cantidad en la ubicacion especifica del sector de una sucursal
                        PhysicalLocation::updateQuantity($model->product_id, $model->origin_sector_location_id, $model->entry_quantity, PhysicalLocation::CHANGE_QUANTITY_MINUS);

                        //Actualizar cantidad general de una sucursal
                        ProductHasBranchOffice::updateQuantity($model->product_id, $model->origin_branch_office_id, $model->entry_quantity,ProductHasBranchOffice::CHANGE_QUANTITY_MINUS);

                        //Actualizar cantidad total del producto
                        Product::updateQuantity($model->product_id, $model->entry_quantity,Product::CHANGE_QUANTITY_MINUS);
                    }
                    $model->save();

                    $transaction->commit();

                    GlobalFunctions::addFlashMessage('success',Yii::t('backend','Elemento creado correctamente'));

                    return $this->redirect([UtilsConstants::getRedirectAdjustmentType($model->type)]);
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

}
