<?php

use yii\helpers\Html;
use kartik\form\ActiveForm;
use kartik\tabs\TabsX;

/* @var $this yii\web\View */
/* @var $model backend\models\business\Customer */

$this->title = Yii::t('backend', 'Crear').' '. Yii::t('backend', 'Cliente');
$this->params['breadcrumbs'][] = ['label' => Yii::t('backend', 'Clientes'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="customer-create">

    <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>

    <?=TabsX::widget([
        'position' => TabsX::POS_ABOVE,
        'encodeLabels' => false,
        'items' => [
            [
                'label' => '<i class="glyphicon glyphicon-list-alt"></i> '.Yii::t('backend', 'Datos Generales'),
                'content' => $this->render('_tab_general', ['model' => $model,'form' => $form,]),
                'active' => true
            ],

            [
                'label' => '<i class="glyphicon glyphicon-globe"></i> '.Yii::t('backend', 'Datos de contacto'),
                'content' => $this->render('_tab_adresses', ['model' => $model,'form' => $form,]),
                'active' => false
            ],

            [
                'label' => '<i class="glyphicon glyphicon-user"></i> '.Yii::t('backend', 'Contactos'),
                'content' => $this->render('_tab_contacts', ['model' => $model,
                    'searchModel'=>NULL,
                    'dataProvider'=>NULL]),
                'active' => false
            ],
        ],
    ]);
    ?>

    <div class="box-footer">
        <?= Html::submitButton($model->isNewRecord ? '<i class="fa fa-plus"></i> '.Yii::t('backend','Crear') : '<i class="fa fa-pencil"></i> '.Yii::t('yii', 'Update'), ['class' => 'btn btn-default btn-flat']) ?>
        <?= Html::a('<i class="fa fa-remove"></i> '.Yii::t('backend','Cancelar'),['index'], ['class' => 'btn btn-default btn-flat margin', 'title' => Yii::t('backend','Cancelar')]) ?>
    </div>
    <?php ActiveForm::end(); ?>

</div>
