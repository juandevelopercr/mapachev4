<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\business\Documents */

$this->title = 'Actualizare Documento: ' . $model->consecutive;
$this->params['breadcrumbs'][] = ['label' => 'Documentos', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->consecutive, 'url' => ['index']];
$this->params['breadcrumbs'][] = 'Actualizar';
?>
<div class="documents-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
