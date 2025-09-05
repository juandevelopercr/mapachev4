<?php

use kartik\tabs\TabsX;

/* @var $this yii\web\View */
/* @var $model backend\models\business\Supplier */

$this->title = Yii::t('backend', 'Crear').' '. Yii::t('backend', 'Proveedor');
$this->params['breadcrumbs'][] = ['label' => Yii::t('backend', 'Proveedores'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="supplier-create">


    <?=TabsX::widget([
        'position' => TabsX::POS_ABOVE,
        'encodeLabels' => false,
        'items' => [
            [
                'label' => '<i class="glyphicon glyphicon-list-alt"></i> '.Yii::t('backend', 'Datos Generales'),
                'content' => $this->render('_tab_general', ['model' => $model,'return_import' => $return_import]),
                'active' => true
            ],
            [
                'label' => '<i class="glyphicon glyphicon-usd"></i> '.Yii::t('backend', 'Información Bancaria'),
                'content' => $this->render('_tab_bank', ['model' => $model,
                    'searchModel'=>NULL,
                    'dataProvider'=>NULL]),
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

</div>
