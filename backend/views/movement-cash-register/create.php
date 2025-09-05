<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\business\MovementCashRegister */

$this->title = 'Create Movement Cash Register';
$this->params['breadcrumbs'][] = ['label' => 'Movement Cash Registers', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="movement-cash-register-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
