<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model backend\models\business\ProductSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="product-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'code') ?>

    <?= $form->field($model, 'image') ?>

    <?= $form->field($model, 'description') ?>

    <?= $form->field($model, 'entry_date') ?>

    <?php // echo $form->field($model, 'bar_code') ?>

    <?php // echo $form->field($model, 'cabys_id') ?>

    <?php // echo $form->field($model, 'family_id') ?>

    <?php // echo $form->field($model, 'category_id') ?>

    <?php // echo $form->field($model, 'unit_type_id') ?>

    <?php // echo $form->field($model, 'branch_office_id') ?>

    <?php // echo $form->field($model, 'supplier_id') ?>

    <?php // echo $form->field($model, 'inventory_type_id') ?>

    <?php // echo $form->field($model, 'location') ?>

    <?php // echo $form->field($model, 'branch') ?>

    <?php // echo $form->field($model, 'initial_existence') ?>

    <?php // echo $form->field($model, 'min_quantity') ?>

    <?php // echo $form->field($model, 'max_quantity') ?>

    <?php // echo $form->field($model, 'package_quantity') ?>

    <?php // echo $form->field($model, 'price') ?>

    <?php // echo $form->field($model, 'percent1') ?>

    <?php // echo $form->field($model, 'price1') ?>

    <?php // echo $form->field($model, 'percent2') ?>

    <?php // echo $form->field($model, 'price2') ?>

    <?php // echo $form->field($model, 'percent3') ?>

    <?php // echo $form->field($model, 'price3') ?>

    <?php // echo $form->field($model, 'percent4') ?>

    <?php // echo $form->field($model, 'price4') ?>

    <?php // echo $form->field($model, 'percent_detail') ?>

    <?php // echo $form->field($model, 'price_detail') ?>

    <?php // echo $form->field($model, 'price_custom') ?>

    <?php // echo $form->field($model, 'discount_amount') ?>

    <?php // echo $form->field($model, 'nature_discount') ?>

    <?php // echo $form->field($model, 'tax_type_id') ?>

    <?php // echo $form->field($model, 'tax_rate_type_id') ?>

    <?php // echo $form->field($model, 'tax_rate_percent') ?>

    <?php // echo $form->field($model, 'exoneration_document_type_id') ?>

    <?php // echo $form->field($model, 'number_exoneration_doc') ?>

    <?php // echo $form->field($model, 'name_institution_exoneration') ?>

    <?php // echo $form->field($model, 'exoneration_date') ?>

    <?php // echo $form->field($model, 'exoneration_purchase_percent') ?>

    <?php // echo $form->field($model, 'created_at') ?>

    <?php // echo $form->field($model, 'updated_at') ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('backend', 'Buscar'), ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton(Yii::t('backend', 'Resetear'), ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
