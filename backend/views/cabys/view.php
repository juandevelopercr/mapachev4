<?php

use yii\helpers\Html;
use common\widgets\DetailView;
use mdm\admin\components\Helper;
use common\models\GlobalFunctions;

/* @var $this yii\web\View */
/* @var $model backend\models\nomenclators\Cabys */

$controllerId = '/'.$this->context->uniqueId.'/';
$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => Yii::t('backend', 'Cabys'), 'url' => ['index']];
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
                'code',
                [
                    'attribute'=> 'description_service',
                    'value'=> $model->description_service,
                    'format'=> 'html',
                ],

                'tax',

                'category1',
                [
                    'attribute'=> 'description1',
                    'value'=> $model->description1,
                    'format'=> 'html',
                ],

                'category2',
                [
                    'attribute'=> 'description2',
                    'value'=> $model->description2,
                    'format'=> 'html',
                ],

                'category3',
                [
                    'attribute'=> 'description3',
                    'value'=> $model->description3,
                    'format'=> 'html',
                ],

                'category4',
                [
                    'attribute'=> 'description4',
                    'value'=> $model->description4,
                    'format'=> 'html',
                ],

                'category5',
                [
                    'attribute'=> 'description5',
                    'value'=> $model->description5,
                    'format'=> 'html',
                ],

                'category6',
                [
                    'attribute'=> 'description6',
                    'value'=> $model->description6,
                    'format'=> 'html',
                ],

                'category7',
                [
                    'attribute'=> 'description7',
                    'value'=> $model->description7,
                    'format'=> 'html',
                ],

                'category8',
                [
                    'attribute'=> 'description8',
                    'value'=> $model->description8,
                    'format'=> 'html',
                ],

                [
                    'attribute'=> 'status',
                    'value'=> GlobalFunctions::getStatusValue($model->status),
                    'format'=> 'html',
                ],
                
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

            ],
        ]) ?>
    </div>
