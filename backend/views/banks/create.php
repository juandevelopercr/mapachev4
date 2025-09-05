<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\nomenclators\Boxes */

$this->title = 'Crear Banco';
$this->params['breadcrumbs'][] = ['label' => 'Bancos', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="boxes-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
