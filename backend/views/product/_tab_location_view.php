<?php

use backend\models\business\ProductHasBranchOffice;
use common\models\GlobalFunctions;
use backend\models\business\PhysicalLocation;


/* @var $this yii\web\View */
/* @var $model backend\models\business\Product */

?>
<div class="row">
    <div class="col-md-12">
        <h3><?= '<b>'.$model->getAttributeLabel('initial_existence').': </b>'.GlobalFunctions::formatNumber($model->initial_existence,2) ?></h3>
    </div>
</div>
<br>
<div class="row">
<?php
$all_relations = ProductHasBranchOffice::find()->where(['product_id'=>$model->id])->all();
if($all_relations !== null) {
    foreach ($all_relations AS $index => $relation) { ?>

        <div class="col-md-12">
            <div class="box box-default box-solid">
                <div class="box-header with-border">
                    <h3 class="box-title"><?= $relation->branchOffice->code . ' - ' . $relation->branchOffice->name. ': '.GlobalFunctions::formatNumber($relation->quantity,2) ?></h3>

                    <div class="box-tools pull-right">
                        <button type="button" class="btn btn-box-tool" data-widget="collapse"><i
                                    class="fa fa-minus"></i>
                        </button>
                    </div>
                    <!-- /.box-tools -->
                </div>
                <!-- /.box-header -->
                <div class="box-body">

                    <?php
                    $all_location = PhysicalLocation::find()
                        ->select([
                            'sector_location.code AS sector_location_code',
                            'sector_location.name AS sector_location_name',
                            'sector.name AS sector_name',
                            'sector.code AS sector_code',
                            'physical_location.quantity AS quantity',
                        ])
                        ->innerJoin('sector_location','physical_location.sector_location_id = sector_location.id')
                        ->innerJoin('sector','sector_location.sector_id = sector.id')
                        ->where([
                            'physical_location.product_id' => $relation->product_id,
                            'sector.branch_office_id' => $relation->branch_office_id
                        ])
                        ->asArray()
                        ->orderBy('sector.code, sector_location.code')
                        ->all();

                    foreach ($all_location AS $key => $location) { ?>

                        <div class="row">
                            <div class="col-md-4">
                                <?= '<b>'.Yii::t('backend','Sector').': </b>'.$location['sector_code'].' - '.$location['sector_name'] ?>
                            </div>
                            <div class="col-md-4">
                                <?= '<b>'.Yii::t('backend','Ubicación').': </b>'.$location['sector_location_code'].' - '.$location['sector_location_name'] ?>
                            </div>
                            <div class="col-md-4">
                                <?= '<b>'.Yii::t('backend','Cantidad').': </b>'.GlobalFunctions::formatNumber($location['quantity'],2) ?>
                            </div>
                        </div>
                        <br>
                   <?php } ?>

                </div>
                <!-- /.box-body -->
            </div>
            <!-- /.box -->
        </div>
    <?php }
}
?>
</div>