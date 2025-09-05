<?php

namespace backend\controllers;

use backend\models\business\Adjustment;
use backend\models\business\Entry;
use backend\models\business\PhysicalLocation;
use backend\models\business\Product;
use backend\models\business\ProductHasBranchOffice;
use backend\models\business\SectorLocation;
use backend\models\nomenclators\UtilsConstants;
use Yii;
use backend\models\business\ItemEntry;
use backend\models\business\ItemEntrySearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use common\models\GlobalFunctions;
use yii\helpers\Url;
use yii\db\Exception;

/**
 * ItemEntryController implements the CRUD actions for ItemEntry model.
 */
class ItemEntryController extends Controller
{

    /**
     * Creates a new ItemEntry model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate_ajax()
    {
        $model = new ItemEntry(['entry_quantity' => 1]);
        if (isset($_REQUEST['id']))
        {
            $model->entry_id = $_REQUEST['id'];
            $entry = Entry::findOne($model->entry_id);
            $default_location = SectorLocation::getDefaultSectorLocation($entry->branch_office_id);
            if($default_location !== null)
            {
                $model->sector_location_id = $default_location;
            }
        }

        Yii::$app->response->format = 'json';

        if ($model->load(Yii::$app->request->post()))
        {
            $model->entry_id = Yii::$app->request->post()['entry_id'];
            $model->user_id = Yii::$app->user->id;
            $model->extractInfoProduct();            

            // Se aplica el descuento a nivel de producto o servicio                
            $subtotal = $model->price * $model->entry_quantity;
            $model->subtotal = (isset($subtotal) && !empty($subtotal)) ? $subtotal : 0;

            $tax_calculate = $subtotal * ($model->tax_rate_percent / 100);
            $tax = (isset($tax_calculate) && !empty($tax_calculate)) ? $tax_calculate : 0;

            $model->tax_amount = $tax;            
            $model->subtotal = $subtotal + $tax;

            if ($model->save())
            {
                //Actualizar cantidad en la ubicacion especifica del sector de una sucursal
                PhysicalLocation::updateQuantity($model->product_id, $model->sector_location_id, $model->entry_quantity, PhysicalLocation::CHANGE_QUANTITY_PLUS);

                //Actualizar cantidad general de una sucursal
                ProductHasBranchOffice::updateQuantity($model->product_id, $model->entry->branch_office_id, $model->entry_quantity,ProductHasBranchOffice::CHANGE_QUANTITY_PLUS);

                //Actualizar cantidad total del producto
                Product::updateQuantity($model->product_id, $model->entry_quantity,Product::CHANGE_QUANTITY_PLUS);

                //Registrar el tipo de ajuste realizado
                Adjustment::add(
                    $model->product_id,
                    UtilsConstants::ADJUSTMENT_TYPE_ENTRY,
                    $model->entry_quantity,
                    $model->new_quantity,
                    $model->past_quantity,
                    $model->entry->branch_office_id,
                    $model->sector_location_id,
                    $model->entry->invoice_number
                );

                $entry = Entry::findOne($model->entry_id);
                // Para que actualice los montos
                $entry->save();

                // upload only if valid uploaded file instance found
                $model->refresh();
                $msg = 'Se ha creado el registro satisfactoriamente';
                $type = 'success';
                $titulo = "Informaci&oacute;n <hr class=\"kv-alert-separator\">";
            } else {
                $msg = 'Ha ocurrido un error al intentar crear el registro';
                $type = 'danger';
                $titulo = "Informaci&oacute;n <hr class=\"kv-alert-separator\">";
            }
            return [
                'message' => $msg,
                'type'=> $type,
                'titulo'=>"Informaci&oacute;n <hr class=\"kv-alert-separator\">",
                'total_tax'=> $entry->total_tax,
                'amount'=> $entry->amount,
            ];
        }
        return $this->renderAjax('_form-item_entry', [
            'model' => $model,
        ]);
    }
}
