<?php

namespace backend\controllers;

use backend\models\business\Product;
use backend\models\business\ProductHasBranchOffice;
use backend\models\business\Sector;
use backend\models\business\SectorLocation;
use Yii;
use backend\models\business\PhysicalLocation;
use backend\models\business\PhysicalLocationSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use common\models\GlobalFunctions;
use yii\helpers\Url;
use yii\db\Exception;

/**
 * PhysicalLocationController implements the CRUD actions for PhysicalLocation model.
 */
class PhysicalLocationController extends Controller
{

    /**
     * Creates a new PhysicalLocation model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate_ajax()
    {
        $model = new PhysicalLocation(['quantity' => 1]);
        if (isset($_REQUEST['id']))
        {
            $model->product_id = $_REQUEST['id'];
        }

        $default_location = PhysicalLocation::getDefaultLocation();
        if($default_location !== null)
        {
            $model->sector_location_id = $default_location->id;
        }

        Yii::$app->response->format = 'json';
        if ($model->load(Yii::$app->request->post()))
        {
            $model->product_id = Yii::$app->request->post()['product_id'];

            if(PhysicalLocation::find()->where(['product_id' => $model->product_id, 'sector_location_id' => $model->sector_location_id])->exists())
            {
                $model->addError('sector_location_id', Yii::t('backend','Ubicación ya está en uso'));

                return [
                    'message' => 'Ha ocurrido un error al intentar crear el registro',
                    'type'=> 'danger',
                    'titulo'=>"Informaci&oacute;n <hr class=\"kv-alert-separator\">",
                    'errors' => $model->getErrors(),
                ];
            }

            if ($model->save())
            {
                $sector = Sector::findOne($model->sectorLocation->sector_id);

                $product_has_branch_office = ProductHasBranchOffice::find()->where(['product_id' => $model->product_id,'branch_office_id'=> $sector->branch_office_id])->exists();

                if($product_has_branch_office)
                {
                    ProductHasBranchOffice::updateQuantity($model->product_id, $sector->branch_office_id, $model->quantity,ProductHasBranchOffice::CHANGE_QUANTITY_PLUS);
                }
                else
                {
                    ProductHasBranchOffice::addRelation($model->product_id, $sector->branch_office_id, $model->quantity);
                }

                Product::updateQuantity($model->product_id, $model->quantity,Product::CHANGE_QUANTITY_PLUS);

                $model->refresh();
                $msg = 'Se ha creado el registro satisfactoriamente';
                $type = 'success';
                $titulo = "Informaci&oacute;n <hr class=\"kv-alert-separator\">";
                $errors = '';
            }
            else
            {
                $msg = 'Ha ocurrido un error al intentar crear el registro';
                $type = 'danger';
                $titulo = "Informaci&oacute;n <hr class=\"kv-alert-separator\">";
                $errors = $model->getErrors();
            }

            return [
                'message' => $msg,
                'type'=> $type,
                'titulo'=>"Informaci&oacute;n <hr class=\"kv-alert-separator\">",
                'errors' => $errors,
            ];
        }
        return $this->renderAjax('_form-locations', [
            'model' => $model,
        ]);
    }

}
