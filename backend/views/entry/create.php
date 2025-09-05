<?php

use kartik\tabs\TabsX;

/* @var $this yii\web\View */
/* @var $model backend\models\business\Entry */

$this->title = Yii::t('backend', 'Crear').' '. Yii::t('backend', 'Entrada');
$this->params['breadcrumbs'][] = ['label' => Yii::t('backend', 'Entradas'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="entry-create">

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
                    'searchModel'=>NULL,
                    'dataProvider'=>NULL]),
                'active' => false
            ],
        ],
    ]);
    ?>

</div>
