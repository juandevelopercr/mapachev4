<?php

use kartik\tabs\TabsX;

/* @var $this yii\web\View */
/* @var $model backend\models\business\PaymentOrder */

$this->title = Yii::t('backend', 'Crear').' '. Yii::t('backend', 'Orden de compra');
$this->params['breadcrumbs'][] = ['label' => Yii::t('backend', 'Órdenes de compra'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="payment-order-create">

    <?=TabsX::widget([
        'position' => TabsX::POS_ABOVE,
        'encodeLabels' => false,
        'items' => [
            [
                'label' => '<i class="glyphicon glyphicon-list-alt"></i> '.Yii::t('backend', 'Datos Generales'),
                'content' => $this->render('_form', [
                    'model' => $model,
                    'searchModel'=>NULL,
                    'dataProvider'=>NULL,
                ]),
                'active' => true
            ],

            [
                'label' => '<i class="glyphicon glyphicon-list"></i> '.Yii::t('backend', 'Productos / Servicios'),
                'content' => $this->render('_tab_items', ['model' => $model,
                    'searchModel'=>NULL,
                    'dataProvider'=>NULL]),
                'active' => false
            ],
        ],
    ]);
    ?>

</div>
