<?php

use kartik\tabs\TabsX;

/* @var $this yii\web\View */
/* @var $model backend\models\business\Supplier */
/* @var $searchModelContacts backend\models\business\SupplierContactSearch */
/* @var $dataProviderContacts yii\data\ActiveDataProvider */
/* @var $searchModelBankInformation backend\models\business\SupplierBankInformationSearch */
/* @var $dataProviderBankInformation yii\data\ActiveDataProvider */

$this->title = Yii::t('backend', 'Actualizar').' '. Yii::t('backend', 'Proveedor').': '. $model->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('backend', 'Proveedores'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('backend', 'Actualizar');
?>
<div class="supplier-update">

    <?=TabsX::widget([
        'position' => TabsX::POS_ABOVE,
        'encodeLabels' => false,
        'items' => [
            [
                'label' => '<i class="glyphicon glyphicon-list-alt"></i> '.Yii::t('backend', 'Datos Generales'),
                'content' => $this->render('_tab_general', ['model' => $model]),
                'active' => true
            ],
            [
                'label' => '<i class="glyphicon glyphicon-usd"></i> '.Yii::t('backend', 'Información Bancaria'),
                'content' => $this->render('_tab_bank', ['model' => $model,
                    'searchModel' => $searchModelBankInformation,
                    'dataProvider' => $dataProviderBankInformation]),
                'active' => false
            ],
            [
                'label' => '<i class="glyphicon glyphicon-user"></i> '.Yii::t('backend', 'Contactos'),
                'content' => $this->render('_tab_contacts', ['model' => $model,
                    'searchModel' => $searchModelContacts,
                    'dataProvider' => $dataProviderContacts]),
                'active' => false
            ],
        ],
    ]);
    ?>

</div>
