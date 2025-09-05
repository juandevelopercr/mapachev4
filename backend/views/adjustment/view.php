<?php

use yii\helpers\Html;
use common\widgets\DetailView;
use mdm\admin\components\Helper;
use common\models\GlobalFunctions;
use backend\models\business\Product;
use common\models\User;
use backend\models\nomenclators\UtilsConstants;


/* @var $this yii\web\View */
/* @var $model backend\models\business\Adjustment */

$controllerId = '/'.$this->context->uniqueId.'/';
$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => UtilsConstants::getAdjustmentType($model->type), 'url' => [UtilsConstants::getRedirectAdjustmentType($model->type)]];
$this->params['breadcrumbs'][] = $this->title;
?>
    <div class="box-header">
        <?php 

        echo Html::a('<i class="fa fa-remove"></i> '.Yii::t('backend','Cancelar'), [UtilsConstants::getRedirectAdjustmentType($model->type)], ['class' => 'btn btn-default btn-flat margin', 'title' => Yii::t('backend','Cancelar')]);

        ?>
    </div>
    <div class="box-body">
        <?= DetailView::widget([
            'model' => $model,
            'labelColOptions' => ['style' => 'width: 40%'],
            'attributes' => [

                'consecutive',
                [
                    'attribute'=> 'product_id',
                    'value'=> (isset($model->product_id) && !empty($model->product_id))? $model->product->bar_code.' - '.$model->product->description : null,
                    'format'=> 'html',
                ],

                [
                    'attribute'=> 'origin_branch_office_id',
                    'label' => ($model->type !== UtilsConstants::ADJUSTMENT_TYPE_TRANFER)? Yii::t('backend', 'Sucursal') : Yii::t('backend', 'Sucursal origen') ,
                    'value'=> (isset($model->originBranchOffice->name) && !empty($model->originBranchOffice->name))? $model->originBranchOffice->code.' - '.$model->originBranchOffice->name : null,
                    'format'=> 'html',
                ],

                [
                    'attribute'=> 'target_branch_office_id',
                    'value'=> (isset($model->targetBranchOffice->name) && !empty($model->targetBranchOffice->name))? $model->targetBranchOffice->code.' - '.$model->originBranchOffice->name : null,
                    'format'=> 'html',
                    'visible'=> ($model->type === UtilsConstants::ADJUSTMENT_TYPE_TRANFER),
                ],

                [
                    'attribute'=> 'past_quantity',
                    'value'=> GlobalFunctions::formatNumber($model->past_quantity,2),
                    'format'=> 'html',
                    'visible'=> ($model->type !== UtilsConstants::ADJUSTMENT_TYPE_TRANFER),
                ],
                
                [
                    'attribute'=> 'entry_quantity',
                    'value'=> GlobalFunctions::formatNumber($model->entry_quantity,2),
                    'format'=> 'html',
                ],

                [
                    'attribute'=> 'new_quantity',
                    'value'=> GlobalFunctions::formatNumber($model->new_quantity,2),
                    'format'=> 'html',
                    'visible'=> ($model->type !== UtilsConstants::ADJUSTMENT_TYPE_TRANFER),
                ],
                
                [
                    'attribute'=> 'observations',
                    'value'=> $model->observations,
                    'format'=> 'html',
                ],
                
                [
                    'attribute'=> 'user_id',
                    'value'=> (isset($model->user->name) && !empty($model->user->name))? User::getFullNameByUserId($model->user_id) : null,
                    'format'=> 'html',
                ],

                [
                    'attribute'=> 'created_at',
                    'value'=> GlobalFunctions::formatDateToShowInSystem($model->created_at),
                    'format'=> 'html',
                ],

            ],
        ]) ?>
    </div>
