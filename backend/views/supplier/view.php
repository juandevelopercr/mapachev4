<?php

use yii\helpers\Html;
use common\widgets\DetailView;
use mdm\admin\components\Helper;
use common\models\GlobalFunctions;
use kartik\tabs\TabsX;
use yii\widgets\ListView;

/* @var $this yii\web\View */
/* @var $model backend\models\business\Supplier */
/* @var $dataProviderContacts yii\data\ActiveDataProvider */
/* @var $dataProviderBank yii\data\ActiveDataProvider */

$controllerId = '/'.$this->context->uniqueId.'/';
$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('backend', 'Proveedores'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
    <div class="box-header">
        <?php 
        if (Helper::checkRoute($controllerId . 'update')) {
            echo Html::a('<i class="fa fa-pencil"></i> '.Yii::t('yii','Update'), ['update', 'id' => $model->id], ['class' => 'btn btn-default btn-flat margin']);
        }

        echo Html::a('<i class="fa fa-remove"></i> '.Yii::t('backend','Cancelar'), ['index'], ['class' => 'btn btn-default btn-flat margin', 'title' => Yii::t('backend','Cancelar')]);

        if (Helper::checkRoute($controllerId . 'delete')) {
            echo Html::a('<i class="fa fa-trash"></i> '.Yii::t('yii','Delete'), ['delete', 'id' => $model->id], [
                'class' => 'btn btn-danger btn-flat margin',
                'data' => [
                    'confirm' => Yii::t('yii','Are you sure you want to delete this item?'),
                    'method' => 'post',
                ],
            ]);
        }
        ?>
    </div>
    <div class="box-body">


        <?php

        $main_data = DetailView::widget([
            'model' => $model,
            'labelColOptions' => ['style' => 'width: 40%'],
            'attributes' => [
                'id',
                'name',
                'code',
                'identification',
                'phone',
                [
                    'attribute'=> 'address',
                    'value'=> $model->address,
                    'format'=> 'html',
                ],

                'web_site',
                [
                    'attribute'=> 'entry_date',
                    'value'=> GlobalFunctions::formatDateToShowInSystem($model->entry_date),
                    'format'=> 'html',
                ],

                [
                    'attribute'=> 'created_at',
                    'value'=> GlobalFunctions::formatDateToShowInSystem($model->created_at),
                    'format'=> 'html',
                ],

            ],
        ]);

        $price_data = DetailView::widget([
            'model' => $model,
            'labelColOptions' => ['style' => 'width: 40%'],
            'attributes' => [
                [
                    'attribute'=> 'condition_sale_id',
                    'value'=> (isset($model->conditionSale->name) && !empty($model->conditionSale->name))? $model->conditionSale->code.' - '.$model->conditionSale->name : null,
                    'format'=> 'html',
                ],

                [
                    'attribute'=> 'colon_credit',
                    'value'=> GlobalFunctions::formatNumber($model->colon_credit,2),
                    'format'=> 'html',
                ],

                [
                    'attribute'=> 'dollar_credit',
                    'value'=> GlobalFunctions::formatNumber($model->dollar_credit,2),
                    'format'=> 'html',
                ],

                [
                    'attribute'=> 'max_credit',
                    'value'=> GlobalFunctions::getStatusValue($model->max_credit,'true','badge bg-gray'),
                    'format'=> 'html',
                ],

                [
                    'attribute'=> 'credit_days_id',
                    'value'=> (isset($model->creditDays->name) && !empty($model->creditDays->name))? $model->creditDays->name : null,
                    'format'=> 'html',
                ],
            ],
        ]);

        $price_items = ListView::widget([
            'dataProvider' => $dataProviderBank,
            'options' => ['class' => 'row'],
            'itemOptions' => ['class' => 'col-md-12'],
            'itemView' => '_items_financial',
            'summary' => false,
        ]);

        $contacts_items = ListView::widget([
            'dataProvider' => $dataProviderContacts,
            'options' => ['class' => 'row'],
            'itemOptions' => ['class' => 'col-md-12'],
            'itemView' => '_items_contacts',
            'summary' => false,
        ]);

        echo TabsX::widget([
            'position' => TabsX::POS_ABOVE,
            'encodeLabels' => false,
            'items' => [
                [
                    'label' => '<i class="glyphicon glyphicon-list-alt"></i> '.Yii::t('backend', 'Datos Generales'),
                    'content' => $main_data,
                    'active' => true
                ],

                [
                    'label' => '<i class="fa fa-money"></i> '.Yii::t('backend', 'Datos venta'),
                    'content' => $price_data,
                    'active' => false
                ],

                [
                    'label' => '<i class="fa fa-dollar"></i> '.Yii::t('backend', 'Información Bancaria'),
                    'content' => $price_items,
                    'active' => false
                ],

                [
                    'label' => '<i class="fa fa-users"></i> '.Yii::t('backend', 'Contactos'),
                    'content' => $contacts_items,
                    'active' => false
                ],
            ],
        ]);
        ?>
    </div>
