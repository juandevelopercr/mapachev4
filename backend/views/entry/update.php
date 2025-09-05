<?php

use kartik\tabs\TabsX;

/* @var $this yii\web\View */
/* @var $model backend\models\business\Entry */
/* @var $searchModelItemEntry \backend\models\business\ItemEntrySearch */
/* @var $dataProviderItemEntry yii\data\ActiveDataProvider */
/* @var $searchModelItemImported \backend\models\business\ItemImportedSearch */
/* @var $dataProviderItemImported yii\data\ActiveDataProvider */

$this->title = Yii::t('backend', 'Actualizar').' '. Yii::t('backend', 'Entrada').': '. $model->id;
$this->params['breadcrumbs'][] = ['label' => Yii::t('backend', 'Entradas'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('backend', 'Actualizar');
?>
<div class="entry-update">

    <?=TabsX::widget([
        'position' => TabsX::POS_ABOVE,
        'encodeLabels' => false,
        'items' => [
            [
                'label' => '<i class="glyphicon glyphicon-list-alt"></i> '.Yii::t('backend', 'Datos Generales'),
                'content' => $this->render('_form', ['model' => $model]),
                'active' => true
            ],
            [
                'label' => '<i class="glyphicon glyphicon-list"></i> '.Yii::t('backend', 'Items'),
                'content' => $this->render('_tab_items', ['model' => $model,
                    'searchModel' => $searchModelItemEntry,
                    'dataProvider' => $dataProviderItemEntry]),
                'active' => false
            ],
            [
                'label' => '<i class="glyphicon glyphicon-warning-sign"></i> '.Yii::t('backend', 'Items pendientes'),
                'content' => $this->render('_tab_import', ['model' => $model,
                    'searchModel' => $searchModelItemImported,
                    'dataProvider' => $dataProviderItemImported]),
                'active' => false
            ],
        ],
    ]);
    ?>


</div>
