<?php

use yii\helpers\Html;
use common\widgets\DetailView;
use mdm\admin\components\Helper;
use common\models\GlobalFunctions;
use kartik\tabs\TabsX;
use backend\models\nomenclators\Cabys;
use backend\models\nomenclators\Family;
use backend\models\nomenclators\Category;
use backend\models\nomenclators\UnitType;
use backend\models\nomenclators\BranchOffice;
use backend\models\business\Supplier;
use backend\models\nomenclators\InventoryType;
use backend\models\nomenclators\TaxType;
use backend\models\nomenclators\TaxRateType;
use backend\models\nomenclators\ExonerationDocumentType;
use kartik\dialog\Dialog;

/* @var $this yii\web\View */
/* @var $model backend\models\business\Product */
/* @var $searchModel backend\models\business\AdjustmentSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$controllerId = '/'.$this->context->uniqueId.'/';
$this->title = $model->description;
$this->params['breadcrumbs'][] = ['label' => Yii::t('backend', 'Productos'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

Dialog::widget();
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
        <?= GlobalFunctions::showModalHtmlContent(Yii::t('backend','Imagen'),'modal-lg') ?>
        <?php

        $main_data = $this->render('_tab_main', [
            'model' => $model,
        ]);

        $price_data = $this->render('_tab_prices', [
            'model' => $model,
        ]);

        $extra_data = $this->render('_tab_extra', [
            'model' => $model,
        ]);

        $location_data = $this->render('_tab_location_view', [
            'model' => $model,
        ]);

        $move_data = $this->render('_tab_move', [
            'model' => $model,
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
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
                    'label' => '<i class="fa fa-money"></i> '.Yii::t('backend', 'Costos y precios'),
                    'content' => $price_data,
                    'active' => false
                ],

                [
                    'label' => '<i class="fa fa-asterisk"></i> '.Yii::t('backend', 'Datos de hacienda'),
                    'content' => $extra_data,
                    'active' => false
                ],

                [
                    'label' => '<i class="fa fa-archive"></i> '.Yii::t('backend', 'Ubicación física'),
                    'content' => $location_data,
                    'active' => false
                ],

                [
                    'label' => '<i class="fa fa-exchange"></i> '.Yii::t('backend', 'Movimientos'),
                    'content' => $move_data,
                    'active' => false
                ],
            ],
        ]);
        ?>
    </div>
