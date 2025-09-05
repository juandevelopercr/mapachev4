<?php

namespace backend\controllers;

use backend\models\business\AdjustmentSearch;
use backend\models\business\ItemImported;
use backend\models\business\PhysicalLocationSearch;
use backend\models\business\ProductHasBranchOffice;
use backend\models\business\ProductHasSupplier;
use backend\models\business\CustomerHasProducts;
use backend\models\business\Sector;
use backend\models\Model;
use backend\models\nomenclators\BranchOffice;
use backend\models\nomenclators\UnitType;
use backend\models\nomenclators\UtilsConstants;
use Yii;
use backend\models\business\Product;
use backend\models\business\ProductSearch;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use common\models\GlobalFunctions;
use yii\helpers\Url;
use yii\db\Exception;
use yii\web\Response;
use backend\models\business\PhysicalLocation;

/**
 * ProductController implements the CRUD actions for Product model.
 */
class ProductController extends Controller
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
     * Lists all Product models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new ProductSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        //Product::clearProducts();

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Product model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);

        $searchModel = new AdjustmentSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->query->where(['product_id' => $id]);

        return $this->render('view', [
            'model' => $model,
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Creates a new Product model.
     * If creation is successful, the browser will be redirected to the 'index' page.
     * @return mixed
     */
    public function actionCreate($pre_model = null)
    {
        $branch_model_list = BranchOffice::find()->all();
        $total_branchs = count($branch_model_list);

        if($total_branchs === 0)
        {
            GlobalFunctions::addFlashMessage('warning',Yii::t('backend','Debe crear una sucursal antes de crear productos'));
            return $this->redirect(['branch-office/create']);
        }

        if($pre_model === null)
        {
            $model = new Product();
            $model->loadDefaultValues();
            $model->entry_date = date('Y-m-d');
            $model->unit_type_id = UnitType::getUnitTypeIdByCode('Unid');
            $model->price_custom = $model->price = $model->percent1 = $model->percent2 = $model->percent3 = $model->percent4 = $model->percent_detail = $model->price1 = $model->price2 = $model->price3 = $model->price4 = $model->price_detail = 0;
        }
        else
        {
            $model = unserialize(urldecode($pre_model));
            $model->price1 = $model->price2 = $model->price3 = $model->price4 = $model->price_detail = $model->price;
            $model->price_custom = $model->percent1 = $model->percent2 = $model->percent3 = $model->percent4 = $model->percent_detail = 0;
        }
        $model->initial_existence = 0;
        $model->code = $model->generateCode();

        if ($model->load(Yii::$app->request->post()))
        {
            $errors = 0;
            $model->price_custom = (is_null($model->price_custom) || empty($model->price_custom)) ? 0: $model->price_custom;
            $price = GlobalFunctions::formatNumber($model->price,2,true);
            $price_custom = GlobalFunctions::formatNumber($model->price_custom,2,true);

            if($price < 0)
            {
                $errors++;
                $model->addError('price', Yii::t('backend','Precio - Costo debe ser mayor que 0'));
            }

            /*
            if($price_custom < $price)
            {
                $errors++;
                $model->addError('price_custom', Yii::t('backend','Precio Personalizado debe ser mayor o igual a Precio - Costo'));
            }
            */
            if(is_null($price_custom) || empty($price_custom))
            {
                $errors++;
                $model->addError('price_custom', Yii::t('backend','Precio Personalizado debe ser mayor o igual a cero'));
            }


            if($errors > 0)
            {
                GlobalFunctions::addFlashMessage('danger',Yii::t('backend','Error actualizando el elemento'));

                return $this->render('create', [
                    'model' => $model,
                ]);
            }

            $transaction = \Yii::$app->db->beginTransaction();

            $model->filterPrices();

            try
            {
                $image = $model->uploadImage();

                if(Product::find()->select(['code'])->where(['code' => $model->code])->exists())
                {
                    $model->code = $model->generateCode();
                }

                if($model->save())
                {
                    if($image){
                        $path = $model->getImageFile();
                        $image->saveAs($path);
                    }

                    ProductHasSupplier::updateRelation($model,[],'suppliers','supplier_id');

                    ItemImported::scanAndUpdateStatus($model->supplier_code, $model->price);

                    $transaction->commit();

                    GlobalFunctions::addFlashMessage('success',Yii::t('backend','Elemento creado correctamente'));

                    return $this->redirect(['update','id'=>$model->id]);
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
     * Updates an existing Product model.
     * If update is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $searchModel = new AdjustmentSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->query->where(['product_id' => $id]);

        $searchModelPhysicalLocations = new PhysicalLocationSearch();
        $dataProviderPhysicalLocation = $searchModelPhysicalLocations->search(Yii::$app->request->queryParams);
        $dataProviderPhysicalLocation->query->where(['product_id' => $id]);

        if(isset($model) && !empty($model))
        {
            $old_image_file = $model->getImageFile();
            $old_image = $model->image;

            //BEGIN product has supplier
            $suppliers_assigned = ProductHasSupplier::getSuppliersByProductId($id);

            $suppliers_assigned_ids= [];
            foreach ($suppliers_assigned as $value)
            {
                $suppliers_assigned_ids[]= $value['supplier_id'];
            }

            $model->suppliers = $suppliers_assigned_ids;
            //END product has supplier

            if(isset($model->discount_amount) && !empty($model->discount_amount))
            {
                $model->discount_amount = GlobalFunctions::formatNumber($model->discount_amount,2,true);
            }

            if ($model->load(Yii::$app->request->post()))
            {
                $errors = 0;
                $model->price_custom = (is_null($model->price_custom) || empty($model->price_custom)) ? 0: $model->price_custom;
                $price = GlobalFunctions::formatNumber($model->price,2,true);
                $price_custom = GlobalFunctions::formatNumber($model->price_custom,2,true);

                if($price < 0)
                {
                    $errors++;
                    $model->addError('price', Yii::t('backend','Precio - Costo debe ser mayor que 0'));
                }

                /*
                if($price_custom < $price)
                {
                    $errors++;
                    $model->addError('price_custom', Yii::t('backend','Precio Personalizado debe ser mayor o igual a Precio - Costo'));
                }
                */

                if(is_null($price_custom) || empty($price_custom))
                {
                    $errors++;
                    $model->addError('price_custom', Yii::t('backend','Precio Personalizado debe ser mayor o igual a cero'));
                }

                if($errors > 0)
                {
                    GlobalFunctions::addFlashMessage('danger',Yii::t('backend','Error actualizando el elemento'));

                    return $this->render('update', [
                        'model' => $model,
                        'dataProvider' => $dataProvider,
                        'searchModelPhysicalLocations' => $searchModelPhysicalLocations,
                        'dataProviderPhysicalLocation' => $dataProviderPhysicalLocation,
                    ]);
                }

                $transaction = \Yii::$app->db->beginTransaction();

                try
                {
                    $image = $model->uploadImage();

                    // revert back if no valid file instance uploaded
                    if ($image === false) {
                        $model->image = $old_image;
                    }

                    $model->filterPrices();

                    ProductHasSupplier::updateRelation($model,$suppliers_assigned,'suppliers','supplier_id');

                    if($model->save())
                    {
                        // upload only if valid uploaded file instance found by main logo
                        if ($image !== false) // delete old and overwrite
                        {
                            if(file_exists($old_image_file))
                            {
                                try{
                                    unlink($old_image_file);
                                }catch (\Exception $exception){
                                    Yii::info("Error deleting image on Product: " . $old_image_file);
                                    Yii::info($exception->getMessage());
                                }
                            }

                            $path = $model->getImageFile();
                            $image->saveAs($path);
                        }

                        ItemImported::scanAndUpdateStatus($model->supplier_code, $model->price);
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
                    $transaction->rollBack();
                    GlobalFunctions::addFlashMessage('danger',Yii::t('backend','Error, ha ocurrido una excepción actualizando el elemento'));            
                }
            }
        }
        else
        {
            GlobalFunctions::addFlashMessage('warning',Yii::t('backend','El elemento buscado no existe'));
        }
        
        return $this->render('update', [
            'model' => $model,
            'dataProvider' => $dataProvider,
            'searchModelPhysicalLocations' => $searchModelPhysicalLocations,
            'dataProviderPhysicalLocation' => $dataProviderPhysicalLocation,
        ]);

    }

    /**
     * Deletes an existing Product model.
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
                $model->deleteImage();
                $transaction->commit();

                GlobalFunctions::addFlashMessage('success',Yii::t('backend','Elemento eliminado correctamente'));
            }
            else
            {
                GlobalFunctions::addFlashMessage('danger',Yii::t('backend','Error eliminando el elemento'));
            }
        }
        catch (Exception $e)
        {
            $transaction->rollBack();
            GlobalFunctions::addFlashMessage('danger',Yii::t('backend','Error, ha ocurrido una excepción eliminando el elemento'));            
        }
        return $this->redirect(['index']);
    }

    /**
     * Finds the Product model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Product the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Product::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('backend','La página solicitada no existe'));
        }
    }

    /**
    * Bulk Deletes for existing Product models.
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
                    } else {
                        $model->deleteImage();
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

    /**
     * @param $id
     * @return array
     * @throws NotFoundHttpException
     */
    public function actionGetPrice($id)
    {
       $model = $this->findModel($id);
       Yii::$app->response->format = Response::FORMAT_JSON;

       $value = (isset($model->price) && !empty($model->price))? $model->price : 0;

        return [
            'quantity' => $value,
            'tax_type_id'=> $model->tax_type_id,
            'tax_rate_type_id'=> $model->tax_rate_type_id,
            'tax_rate_percent'=> $model->tax_rate_percent,
            //'tax_type_id'=> $model->tax_type_id,
        ];
    }

    /**
     * @param $id
     * @return array
     * @throws NotFoundHttpException
     */
    public function actionGetCode($id)
    {
        $model = $this->findModel($id);
        Yii::$app->response->format = Response::FORMAT_JSON;

        $default_unid = UnitType::findOne(['code' => 'Unid']);
        $default_unid_id = ($default_unid !== null)? $default_unid->id : null;

        return [
            'code' => $model->bar_code,
            'unit_type_id' => (isset($model->unit_type_id) && !empty($model->unit_type_id))? $model->unit_type_id : $default_unid_id
        ];
    }

    /**
     * @param $id
     * @return array
     * @throws NotFoundHttpException
     */
    public function actionGet_info_by_code($code)
    {
        $model = Product::find()->where(['bar_code' => $code])->one();
        $default_unid = UnitType::findOne(['code' => 'Unid']);
        $default_unid_id = ($default_unid !== null)? $default_unid->id : null;

        Yii::$app->response->format = Response::FORMAT_JSON;

        if($model !== null)
        {
            $response = [
                'success' => true,
                'code' => $model->bar_code,
                'unit_type_id' => (isset($model->unit_type_id) && !empty($model->unit_type_id))? $model->unit_type_id : $default_unid_id,
                'price_type' => UtilsConstants::CUSTOMER_ASSIGN_PRICE_1
            ];
        }
        else
        {
            $response = [
                'success' => false,
                'code' => '',
                'unit_type_id' => '',
                'price_type' => ''
            ];
        }

        return $response;
    }

    /**
     * @param $id
     * @param $type_price
     * @return null|string
     * @throws NotFoundHttpException
     */
    public function actionGetPriceByTypeAndUnitType($id, $type_price = 0, $unit_type = NULL)
    {
        $model = $this->findModel($id);
        Yii::$app->response->format = Response::FORMAT_JSON;

        $value_return = $model->price;
        $type = (int) $type_price;

        if($type !== 0)
        {
            if($type === UtilsConstants::CUSTOMER_ASSIGN_PRICE_1)
            {
                $value_return = (isset($model->price1) && !empty($model->price1))? $model->price1 : $model->price;
            }
            elseif($type === UtilsConstants::CUSTOMER_ASSIGN_PRICE_2)
            {
                $value_return = (isset($model->price2) && !empty($model->price2))? $model->price2 : $model->price;
            }
            elseif($type === UtilsConstants::CUSTOMER_ASSIGN_PRICE_3)
            {
                $value_return = (isset($model->price3) && !empty($model->price3))? $model->price3 : $model->price;
            }
            elseif($type === UtilsConstants::CUSTOMER_ASSIGN_PRICE_4)
            {
                $value_return = (isset($model->price4) && !empty($model->price4))? $model->price4 : $model->price;
            }
            elseif($type === UtilsConstants::CUSTOMER_ASSIGN_PRICE_CUSTOM)
            {
                $value_return = (isset($model->price_custom) && !empty($model->price_custom))? $model->price_custom : $model->price;
            }
            elseif($type === UtilsConstants::CUSTOMER_ASSIGN_PRICE_DETAIL)
            {
                $value_return = (isset($model->price_detail) && !empty($model->price_detail))? $model->price_detail : $model->price;
            }
        }

        if (!is_null($unit_type) && !empty($unit_type)){
            $unit_type = (int)$unit_type;
            if ($model->unit_type_id != $unit_type){
                // Si la unidad de medida del producto es diferente a la unidad de medida seleccionado hay que hacer conversión
                $itemUnitType = UnitType::find()->where(['id'=>$unit_type])->one();
                if ($model->unitType->code == 'UN' || $model->unitType->code == 'UND' || $model->unitType->code == 'Unid'){                    
                    $unit_type_code = $itemUnitType->code;

                    if ($unit_type_code == 'CAJ' || $unit_type_code == 'CJ') {
                        if (isset($model->quantity_by_box)) {
                            $value_return *= $model->quantity_by_box;
                        }
                    } elseif ($unit_type_code == 'BULT' || $unit_type_code == 'PAQ') {
                        if (isset($model->package_quantity)) {
                            $value_return *= $model->package_quantity;
                        }
                    }                    
                }
                else
                if ($model->unitType->code == 'PAQ' || $model->unitType->code == 'BULT' || $model->unitType->code == 'CJ' || $model->unitType->code == 'CAJ'){                    
                    $unit_type_code = $model->unitType->code;

                    if ($unit_type_code == 'CAJ' || $unit_type_code == 'CJ') {
                        if (isset($model->quantity_by_box) && $model->quantity_by_box > 0) {
                            $value_return = $value_return / $model->quantity_by_box;
                        }
                    } elseif ($unit_type_code == 'BULT' || $unit_type_code == 'PAQ') {
                        if (isset($model->package_quantity) && $model->package_quantity > 0) {
                            $value_return = $value_return / $model->package_quantity;
                        }
                    }                    
                }                
            }
        }

        return [
                 'price'=>$value_return,                 
        ];
    }

}
