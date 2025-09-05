<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\business\CashRegister */

$this->title = 'Actualizar Apertura de caja: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Arqueos de Caja', 'url' => ['arqueo', 'box_id'=>$model->box_id]];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="cash-register-update">

    <?= $this->render('_form_update', [
        'model' => $model,
        'movimiento'=>$movimiento,
        'movimiento_detail'=> $movimiento_detail,        
    ]) ?>

</div>
