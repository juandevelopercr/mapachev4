<?php

use yii\helpers\Html;
use common\widgets\DetailView;
use mdm\admin\components\Helper;
use common\models\GlobalFunctions;
use kartik\tabs\TabsX;
use backend\models\nomenclators\UtilsConstants;
use backend\models\business\SellerHasCustomer;
use backend\models\business\CollectorHasCustomer;
use yii\widgets\ListView;
use common\models\User;

/* @var $this yii\web\View */
/* @var $model backend\models\business\Customer */
/* @var $dataProviderContacts yii\data\ActiveDataProvider */

$controllerId = '/'.$this->context->uniqueId.'/';
$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('backend', 'Clientes'), 'url' => ['index']];
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
                    'commercial_name',
                    'code',
                    [
                        'attribute'=> 'description',
                        'value'=> $model->description,
                        'format'=> 'html',
                    ],

                    [
                        'attribute'=> 'identification_type_id',
                        'value'=> (isset($model->identificationType->name) && !empty($model->identificationType->name))? $model->identificationType->code.' - '.$model->identificationType->name : null,
                        'format'=> 'html',
                    ],

                    'identification',
                    'foreign_identification',
                    /*
                    [
                        'attribute'=> 'customer_type_id',
                        'value'=> (isset($model->customerType->name) && !empty($model->customerType->name))? $model->customerType->code.' - '.$model->customerType->name : null,
                        'format'=> 'html',
                    ],

                    [
                        'attribute'=> 'customer_classification_id',
                        'value'=> (isset($model->customerClassification->name) && !empty($model->customerClassification->name))? $model->customerClassification->name : null,
                        'format'=> 'html',
                    ],
                    */
                    'country_code_phone',
                    'phone',
                    'country_code_fax',
                    'fax',
                    'email:email',

                    [
                        'attribute'=> 'created_at',
                        'value'=> GlobalFunctions::formatDateToShowInSystem($model->created_at),
                        'format'=> 'html',
                    ],
                    [
                        'attribute'=> 'user_id',
                        'value'=> $model->user->name . "&nbsp;" . $model->user->last_name,
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
                    /*
                    [
                        'attribute'=> 'credit_amount_colon',
                        'value'=> GlobalFunctions::formatNumber($model->credit_amount_colon,2),
                        'format'=> 'html',
                    ],

                    [
                        'attribute'=> 'credit_amount_usd',
                        'value'=> GlobalFunctions::formatNumber($model->credit_amount_usd,2),
                        'format'=> 'html',
                    ],
                    */
                    [
                        'attribute'=> 'credit_days_id',
                        'value'=> (isset($model->creditDays->name) && !empty($model->creditDays->name))? $model->creditDays->name : null,
                        'format'=> 'html',
                    ],
                    /*
                    [
                        'attribute'=> 'enable_credit_max',
                        'value'=> GlobalFunctions::getStatusValue($model->enable_credit_max,'true','badge bg-gray'),
                        'format'=> 'html',
                    ],

                    [
                        'attribute'=> 'price_assigned',
                        'value'=> UtilsConstants::getCustomerAsssignPriceSelectType($model->price_assigned),
                        'format'=> 'html',
                    ],

                    [
                        'label'=> 'Agente Cobrador',
                        'value'=> CollectorHasCustomer::getCollectorStringByCustomer($model->id),
                        'format'=> 'html',
                    ],

                    [
                        'label'=> 'Agente Vendedor',
                        'value'=> SellerHasCustomer::getSellerStringByCustomer($model->id),
                        'format'=> 'html',
                    ],
                    */
                ],
            ]);

            $location_data = DetailView::widget([
                'model' => $model,
                'labelColOptions' => ['style' => 'width: 40%'],
                'attributes' => [
                    [
                        'attribute'=> 'province_id',
                        'value'=> (isset($model->province->name) && !empty($model->province->name))? $model->province->code. ' - '. $model->province->name : null,
                        'format'=> 'html',
                    ],

                    [
                        'attribute'=> 'canton_id',
                        'value'=> (isset($model->canton->name) && !empty($model->canton->name))? $model->canton->code. ' - '.$model->canton->name : null,
                        'format'=> 'html',
                    ],

                    [
                        'attribute'=> 'disctrict_id',
                        'value'=> (isset($model->disctrict->name) && !empty($model->disctrict->name))? $model->disctrict->code. ' - '.$model->disctrict->name : null,
                        'format'=> 'html',
                    ],

                    'address',
                    'other_signs',
                ],
            ]);

            $exonerate_data = DetailView::widget([
                'model' => $model,
                'labelColOptions' => ['style' => 'width: 40%'],
                'attributes' => [

                    [
                    'attribute'=> 'is_exonerate',
                    'value'=> GlobalFunctions::getStatusValue($model->is_exonerate,'true','badge bg-gray'),
                    'format'=> 'html',
                    ],

                    [
                        'attribute'=> 'exoneration_document_type_id',
                        'value'=> (isset($model->exonerationDocumentType->name) && !empty($model->exonerationDocumentType->name))? $model->exonerationDocumentType->code.' - '.$model->exonerationDocumentType->name : null,
                        'format'=> 'html',
                    ],

                    'number_exoneration_doc',
                    'name_institution_exoneration',
                    [
                        'attribute'=> 'exoneration_date',
                        'value'=> GlobalFunctions::formatDateToShowInSystem($model->exoneration_date),
                        'format'=> 'html',
                    ],

                    [
                        'attribute'=> 'exoneration_purchase_percent',
                        'value'=> GlobalFunctions::formatNumber($model->exoneration_purchase_percent,2).' %',
                        'format'=> 'html',
                    ],

                ],
            ]);

            $contacts_items = ListView::widget([
                'dataProvider' => $dataProviderContacts,
                'options' => ['class' => 'row'],
                'itemOptions' => ['class' => 'col-md-12'],
                'itemView' => '_items',
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
                        'label' => '<i class="fa fa-money"></i> '.Yii::t('backend', 'Datos Venta'),
                        'content' => $price_data,
                        'active' => false
                    ],

                    [
                        'label' => '<i class="fa fa-location-arrow"></i> '.Yii::t('backend', 'Localización'),
                        'content' => $location_data,
                        'active' => false
                    ],

                    [
                        'label' => '<i class="fa fa-asterisk"></i> '.Yii::t('backend', 'Exoneración'),
                        'content' => $exonerate_data,
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
