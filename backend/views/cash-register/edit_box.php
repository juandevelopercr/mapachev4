<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\business\CashRegister */

$this->title = 'Apertura de Caja';
$this->params['breadcrumbs'][] = ['label' => 'Arqueos de caja', 'url' => ['arqueo', 'box_id'=>$box_id]];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="cash-register-update">

    <?= $this->render('_form', [
        'model' => $model,
        'box_id'=>$box_id,
        'coins'=>$coins,
    ]) ?>

</div>
