<?php

use yii\helpers\Html;
use common\widgets\DetailView;
use mdm\admin\components\Helper;
use common\models\GlobalFunctions;
use common\models\User;
use backend\models\business\Entry;
use backend\models\business\Supplier;
use backend\models\nomenclators\BranchOffice;

/* @var $this yii\web\View */
/* @var $model backend\models\business\XmlImported */

$controllerId = '/'.$this->context->uniqueId.'/';
$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => Yii::t('backend', 'Xml Importeds'), 'url' => ['index']];
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
        <?= DetailView::widget([
            'model' => $model,
            'labelColOptions' => ['style' => 'width: 40%'],
            'attributes' => [
                'id',
                'currency_code',
                [
                    'attribute'=> 'currency_change_value',
                    'value'=> GlobalFunctions::formatNumber($model->currency_change_value,2),
                    'format'=> 'html',
                ],
                
                'invoice_key',
                'invoice_activity_code',
                'invoice_consecutive_number',
                'invoice_date',
                [
                    'attribute'=> 'user_id',
                    'value'=> (isset($model->user->name) && !empty($model->user->name))? $model->user->name : null,
                    'format'=> 'html',
                ],
        
                [
                    'attribute'=> 'entry_id',
                    'format'=> 'html',
                ],
        
                'xml_file',
                [
                    'attribute'=> 'created_at',
                    'value'=> GlobalFunctions::formatDateToShowInSystem($model->created_at),
                    'format'=> 'html',
                ],
                
                [
                    'attribute'=> 'updated_at',
                    'value'=> GlobalFunctions::formatDateToShowInSystem($model->updated_at),
                    'format'=> 'html',
                ],
                
                'supplier_identification',
                'supplier_identification_type',
                'supplier_name',
                'supplier_province_code',
                'supplier_canton_code',
                'supplier_district_code',
                'supplier_barrio_code',
                [
                    'attribute'=> 'supplier_other_signals',
                    'value'=> $model->supplier_other_signals,
                    'format'=> 'html',
                ],
                
                'supplier_phone_country_code',
                'supplier_phone',
                'supplier_email:email',
                'invoice_condition_sale_code',
                'invoice_credit_time_code',
                'invoice_payment_method_code',
                [
                    'attribute'=> 'supplier_id',
                    'value'=> (isset($model->supplier->name) && !empty($model->supplier->name))? $model->supplier->name : null,
                    'format'=> 'html',
                ],
        
                [
                    'attribute'=> 'branch_office_id',
                    'value'=> (isset($model->branchOffice->name) && !empty($model->branchOffice->name))? $model->branchOffice->name : null,
                    'format'=> 'html',
                ],
        
            ],
        ]) ?>
    </div>
