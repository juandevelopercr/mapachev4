<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\nomenclators\Boxes */

$this->title = 'Crear Caja';
$this->params['breadcrumbs'][] = ['label' => 'Cajas', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="boxes-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
