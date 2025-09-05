<?php

use yii\helpers\Html;
use common\widgets\DetailView;
use mdm\admin\components\Helper;
use common\models\GlobalFunctions;
use kartik\tabs\TabsX;
use backend\models\nomenclators\Cabys;
use backend\models\nomenclators\Family;
use backend\models\nomenclators\Category;
use backend\models\nomenclators\UnitType;
use backend\models\nomenclators\BranchOffice;
use backend\models\business\Supplier;
use backend\models\nomenclators\InventoryType;
use backend\models\nomenclators\TaxType;
use backend\models\nomenclators\TaxRateType;
use backend\models\nomenclators\ExonerationDocumentType;
use backend\models\business\ProductHasSupplier;
use backend\models\business\ProductHasBranchOffice;


/* @var $this yii\web\View */
/* @var $model backend\models\business\Product */

?>

<div class="row">
    <div class="col-md-4">
        <img class="profile-user-img img-responsive img-bordered modalImage" src="<?= $model->getPreview() ?>">
        <h3 class="profile-username text-center"><?= $model->description ?></h3>
    </div>
    <div class="col-md-4">
        <div class="row">
            <div class="col-md-12 custom-padding-5">
                <?= '<b>'.$model->getAttributeLabel('code').': </b>'.$model->code ?>
            </div>
            <div class="col-md-12 custom-padding-5">
                <?= '<b>'.$model->getAttributeLabel('bar_code').': </b>'.$model->bar_code ?>
            </div>
            <div class="col-md-12 custom-padding-5">
                <?= '<b>'.$model->getAttributeLabel('supplier_code').': </b>'.$model->supplier_code ?>
            </div>
            <div class="col-md-12 custom-padding-5">
                <?= '<b>'.$model->getAttributeLabel('cabys_id').': </b>'.$model->cabys->code ?>
            </div>
            <div class="col-md-12 custom-padding-5">
                <?= '<b>'.$model->getAttributeLabel('entry_date').': </b>'.GlobalFunctions::formatDateToShowInSystem($model->entry_date) ?>
            </div>
            <div class="col-md-12 custom-padding-5">
                <?= '<b>'.$model->getAttributeLabel('inventory_type_id').': </b>'.$model->inventoryType->code.' - '.$model->inventoryType->name ?>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="row">
            <div class="col-md-12 custom-padding-5">
                <?= '<b>'.$model->getAttributeLabel('family_id').': </b>'.$model->family->code.' - '.$model->family->name ?>
            </div>
            <div class="col-md-12 custom-padding-5">
                <?= '<b>'.$model->getAttributeLabel('category_id').': </b>'.$model->category->code.' - '.$model->category->name ?>
            </div>
            <div class="col-md-12 custom-padding-5">
                <?= '<b>'.$model->getAttributeLabel('unit_type_id').': </b>'.$model->unitType->code.' - '.$model->unitType->name ?>
            </div>
            <div class="col-md-12 custom-padding-5">
                <?= '<b>'.$model->getAttributeLabel('branch').': </b>'.$model->branch ?>
            </div>
            <div class="col-md-12 custom-padding-5">
                <?= '<b>'.$model->getAttributeLabel('suppliers').': </b>'.ProductHasSupplier::getSuppliersStringByProduct($model->id) ?>
            </div>
        </div>
    </div>


</div>
