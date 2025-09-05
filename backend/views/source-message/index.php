<?php

use backend\components\Custom_Settings_Column_GridView;
use backend\components\Footer_Bulk_Delete;
use backend\models\i18n\Message;
use backend\models\i18n\SourceMessage;
use common\models\GlobalFunctions;
use kartik\export\ExportMenu;
use kartik\grid\GridView;
use mdm\admin\components\Helper;
use yii\helpers\BaseStringHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;


/* @var $this yii\web\View */
/* @var $searchModel backend\models\i18n\SourceMessageSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$controllerId = $this->context->uniqueId . '/';
$this->title = Yii::t('backend','Traducciones');
$this->params['breadcrumbs'][] = $this->title;

$create_button='';

?>
        <?php
        if (Helper::checkRoute($controllerId . 'create')) {
            $create_button =
                Html::a('<i class="fa fa-plus"></i> '.Yii::t('backend','Crear'), ['create'], ['class' => 'btn btn-default btn-flat margin', 'title' => Yii::t('backend','Crear Traducción')])
                .' '. Html::a('<i class="fa fa-file-excel-o"></i> '.Yii::t('backend','Exportar'), ['export'], ['class' => 'btn btn-default btn-flat margin', 'title' => Yii::t('backend','Exportar')])
            ;
        }

        $custom_elements_gridview = new Custom_Settings_Column_GridView($create_button,$dataProvider);

        ?>

    <div class="box-body">

        <?= GridView::widget([
            'id'=>'grid',
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
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
            'hover' => true,
            'columns' => [
                $custom_elements_gridview->getSerialColumn(),

                'category',
                [
                    'attribute' => 'message',
                    'format' => 'raw',
                    'value'=>function($model){
                        $tag_status =  SourceMessage::getIfExistTranslation($model->id);
                        $message_origin = $model->message;
                        $formattedDesc = BaseStringHelper::truncateWords($message_origin,5,'...',true);
                        return $formattedDesc. ' '.$tag_status;
                    },
                ],

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