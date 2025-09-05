<?php

use backend\components\Custom_Settings_Column_GridView;
use backend\components\Footer_Bulk_Delete;
use common\models\GlobalFunctions;
use common\models\User;
use common\widgets\GridView;
use mdm\admin\components\Helper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;


/* @var $this yii\web\View */
/* @var $searchModel \common\models\UserSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$controllerId = '/'.$this->context->uniqueId.'/';
$this->title = Yii::t('backend', 'Agentes');
$this->params['breadcrumbs'][] = $this->title;

$create_button='';
?>

<?php 
	if (Helper::checkRoute($controllerId . 'create')) {
		$create_button = Html::a('<i class="fa fa-plus"></i> '.Yii::t('backend', 'Crear'), ['create'], ['class' => 'btn btn-default btn-flat margin', 'title' => Yii::t('backend', 'Crear').' '.Yii::t('backend', 'Agente')]);
	}

	$custom_elements_gridview = new Custom_Settings_Column_GridView($create_button,$dataProvider);
?>

<?=
GridView::widget([
    'id'=>'grid',
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'as filterBehavior' => \thrieu\grid\FilterStateBehavior::className(),	
    'pager' => [
        'firstPageLabel' => Yii::t('backend','Primero'),
        'lastPageLabel' => Yii::t('backend','Último'),
    ],
            'autoXlFormat'=>true,
        'responsiveWrap' => false,
    'floatHeader' => true,
    'floatHeaderOptions' => [
        'position'=>'absolute',
        'top' => 50
    ],
    'pjax'=>true,
    'pjaxSettings'=>[
        'neverTimeout'=>true,
        'options'=>[
            'enablePushState'=>false,
        ],
    ],
    'hover' => true,
    'columns' => [
        $custom_elements_gridview->getSerialColumn(),
        'name',
        'last_name',
        'username',
        'email',

        $custom_elements_gridview->getActionColumn(),

        $custom_elements_gridview->getCheckboxColumn(),

    ],
    'toolbar' =>  $custom_elements_gridview->getToolbar(),

    'panel' => $custom_elements_gridview->getPanel(),

    'toggleDataOptions' => $custom_elements_gridview->getTogleDataOptions(),

]);
?>
</div>

<?php
    $url = Url::to([$controllerId.'multiple_delete'], GlobalFunctions::URLTYPE);
    $js = Footer_Bulk_Delete::getFooterBulkDelete($url);
    $this->registerJs($js, View::POS_READY);
?>

