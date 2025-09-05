<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\business\Documents */

$this->title = 'Crear Documentos';
$this->params['breadcrumbs'][] = ['label' => 'Documentos', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="documents-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
