<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model backend\models\business\XmlImportedSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="xml-imported-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'currency_code') ?>

    <?= $form->field($model, 'currency_change_value') ?>

    <?= $form->field($model, 'invoice_key') ?>

    <?= $form->field($model, 'invoice_activity_code') ?>

    <?php // echo $form->field($model, 'invoice_consecutive_number') ?>

    <?php // echo $form->field($model, 'invoice_date') ?>

    <?php // echo $form->field($model, 'user_id') ?>

    <?php // echo $form->field($model, 'entry_id') ?>

    <?php // echo $form->field($model, 'xml_file') ?>

    <?php // echo $form->field($model, 'created_at') ?>

    <?php // echo $form->field($model, 'updated_at') ?>

    <?php // echo $form->field($model, 'supplier_identification') ?>

    <?php // echo $form->field($model, 'supplier_identification_type') ?>

    <?php // echo $form->field($model, 'supplier_name') ?>

    <?php // echo $form->field($model, 'supplier_province_code') ?>

    <?php // echo $form->field($model, 'supplier_canton_code') ?>

    <?php // echo $form->field($model, 'supplier_district_code') ?>

    <?php // echo $form->field($model, 'supplier_barrio_code') ?>

    <?php // echo $form->field($model, 'supplier_other_signals') ?>

    <?php // echo $form->field($model, 'supplier_phone_country_code') ?>

    <?php // echo $form->field($model, 'supplier_phone') ?>

    <?php // echo $form->field($model, 'supplier_email') ?>

    <?php // echo $form->field($model, 'invoice_condition_sale_code') ?>

    <?php // echo $form->field($model, 'invoice_credit_time_code') ?>

    <?php // echo $form->field($model, 'invoice_payment_method_code') ?>

    <?php // echo $form->field($model, 'supplier_id') ?>

    <?php // echo $form->field($model, 'branch_office_id') ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('backend', 'Buscar'), ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton(Yii::t('backend', 'Resetear'), ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
