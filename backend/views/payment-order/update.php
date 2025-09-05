<?php

use kartik\tabs\TabsX;
use common\models\GlobalFunctions;

/* @var $this yii\web\View */
/* @var $model backend\models\business\PaymentOrder */
/* @var $searchModelItems backend\models\business\ItemPaymentOrderSearch */
/* @var $dataProviderItems yii\data\ActiveDataProvider */

$this->title = Yii::t('backend', 'Actualizar').' '. Yii::t('backend', 'Orden de compra').': '. $model->number;
$this->params['breadcrumbs'][] = ['label' => Yii::t('backend', 'Órdenes de compra'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->number, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('backend', 'Actualizar');
?>
<div class="payment-order-update">

    <?= GlobalFunctions::showModalHtmlContent(Yii::t('backend','Imagen'),'modal-lg') ?>

    <?=TabsX::widget([
        'position' => TabsX::POS_ABOVE,
        'encodeLabels' => false,
        'items' => [
            [
                'label' => '<i class="glyphicon glyphicon-list-alt"></i> '.Yii::t('backend', 'Datos Generales'),
                'content' => $this->render('_form', [
                    'model' => $model,
                    'searchModel'=>$searchModelItems,
                    'dataProvider'=>$dataProviderItems
                ]),
                'active' => true
            ],

            [
                'label' => '<i class="glyphicon glyphicon-copy"></i> '.Yii::t('backend', 'Recepción'),
                'content' => $this->render('_tab_receptions', [
                    'model' => $model,
                    'searchModel'=>$searchModelReceptionItems,
                    'dataProvider'=>$dataProviderReceptionItems
                ]),
                'active' => false
            ],

            [
                'label' => '<i class="glyphicon glyphicon-paperclip"></i> '.Yii::t('backend', 'Adjuntos'),
                'content' => $this->render('_tab_attachs', [
                    'model' => $model,
                    'searchModel'=>$searchModelAttachs,
                    'dataProvider'=>$dataProviderAttachs
                ]),
                'active' => false
            ],
        ],
    ]);
    ?>

</div>
