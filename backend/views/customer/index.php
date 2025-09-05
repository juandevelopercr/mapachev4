<?php

use backend\components\Custom_Settings_Column_GridView;
use backend\components\Footer_Bulk_Delete;
use backend\models\business\CollectorHasCustomer;
use backend\models\business\SellerHasCustomer;
use backend\models\nomenclators\RouteTransport;
use common\models\GlobalFunctions;
use common\models\User;
use common\widgets\GridView;
use mdm\admin\components\Helper;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;



/* @var $this yii\web\View */
/* @var $searchModel backend\models\business\CustomerSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$controllerId = '/' . $this->context->uniqueId . '/';
$this->title = Yii::t('backend', 'Clientes');
$this->params['breadcrumbs'][] = $this->title;

$create_button = '';
?>

<?php
if (Helper::checkRoute($controllerId . 'create')) {
    $create_button = Html::a('<i class="fa fa-plus"></i> ' . Yii::t('backend', 'Crear'), ['create'], ['class' => 'btn btn-default btn-flat margin', 'title' => Yii::t('backend', 'Crear') . ' ' . Yii::t('backend', 'Cliente')]);
}

//$import_button = Html::a('<i class="fa fa-file"></i> '.Yii::t('backend', 'Importar Datos FTP'), '#', ['class' => 'btn btn-default btn-flat margin btn-pdf-preparation', 'id'=>'btn_import_data', 'title' => Yii::t('backend', 'Importar datos de FTP')]);
$import_button = '';


$custom_elements_gridview = new Custom_Settings_Column_GridView($create_button. $import_button, $dataProvider);


?>

<div class="box-body">
    <?php // echo $this->render('_search', ['model' => $searchModel]); 
    ?>
    <?= GridView::widget([
        'id' => 'grid',
        'dataProvider' => $dataProvider,
        'pjax' => true,
        'pjaxSettings' => [
            'neverTimeout' => true,
            'options' => [
                'enablePushState' => false,
                'scrollTo' => false,
            ],
        ],
        'autoXlFormat' => true,
        'responsiveWrap' => false,
        'floatHeader' => true,
        'floatHeaderOptions' => [
            'position' => 'absolute',
            'top' => 50
        ],
        'hover' => true,
        'pager' => [
            'firstPageLabel' => Yii::t('backend', 'Primero'),
            'lastPageLabel' => Yii::t('backend', 'Último'),
        ],
        'hover' => true,
        'persistResize' => true,
        'filterModel' => $searchModel,
        'as filterBehavior' => \thrieu\grid\FilterStateBehavior::className(),
        'columns' => [

            $custom_elements_gridview->getSerialColumn(),

            [
                'attribute' => 'code',
                'contentOptions' => ['class' => 'kv-align-left kv-align-middle'],
                'hAlign' => 'center',
                'format' => 'html',
                'value' => function ($data) {
                    return $data->code;
                }
            ],

            [
                'attribute' => 'name',
                //'contentOptions' => ['class' => 'kv-align-left kv-align-middle'],
                'contentOptions' => [
                    'class' => 'kv-align-left kv-align-middle nowrap', 
                    'style' => 'min-width:300px; max-width:300px; white-space: normal;',
                ],
                'hAlign' => 'center',
                'format' => 'html',
                'value' => function ($data) {
                    return $data->name;
                }
            ],

            [
                'attribute' => 'commercial_name',
                'contentOptions' => ['class' => 'kv-align-left kv-align-middle'],
                'hAlign' => 'center',
                'format' => 'html',
                'value' => function ($data) {
                    return $data->commercial_name;
                }
            ],

            [
                'attribute' => 'identification',
                'contentOptions' => ['class' => 'kv-align-left kv-align-middle'],
                'hAlign' => 'center',
                'format' => 'html',
                'value' => function ($data) {
                    return $data->identification;
                }
            ],

            [
                'attribute' => 'phone',
                'contentOptions' => ['class' => 'kv-align-left kv-align-middle'],
                'hAlign' => 'center',
                'format' => 'html',
                'value' => function ($data) {
                    return $data->phone;
                }
            ],

            [
                'attribute' => 'email',
                'contentOptions' => ['class' => 'kv-align-left kv-align-middle'],
                'hAlign' => 'center',
                'format' => 'html',
                'value' => function ($data) {
                    return $data->email;
                }
            ],
            [
                'attribute' => 'created_at',
                'contentOptions' => ['class' => 'kv-align-left kv-align-middle'],
                'filter' => false,
                'hAlign' => 'center',
                'format' => 'html',
                'value' => function ($data) {
                    return GlobalFunctions::formatDateToShowInSystem($data->created_at);
                }
            ],
            [
                'attribute' => 'user_id',
                'format' => 'html',
                'contentOptions' => ['class' => 'kv-align-left kv-align-middle'],
                'filterType' => GridView::FILTER_SELECT2,
                //'filter' => User::find()->select(concat_ws('name', 'last_name'))->all(),
                'filter' => ArrayHelper::map(User::find()->select(['id', "CONCAT(name, ' ', last_name) AS full_name"])->asArray()->all(), 'id', 'full_name'),
                'filterWidgetOptions' => [
                    'pluginOptions' => ['allowClear' => true],
                    'options' => ['multiple' => false],
                ],
                'value' => function ($data) {
                    return $data->user->name . "&nbsp;" . $data->user->last_name;
                },
                'filterInputOptions' => ['placeholder' => '------'],
                'hAlign' => 'center',
            ],  

            /*
            [
                'attribute' => 'sellers',
                'format' => 'html',
                'contentOptions' => ['class' => 'kv-align-left kv-align-middle'],
                'filterType' => GridView::FILTER_SELECT2,
                'filter' => User::getSelectMapAgents(true, true),
                'filterWidgetOptions' => [
                    'pluginOptions' => ['allowClear' => true],
                    'options' => ['multiple' => false],
                ],
                'value' => function ($model) {
                    return SellerHasCustomer::getSellerStringByCustomer($model->id);
                },
                'filterInputOptions' => ['placeholder' => '------'],
                'hAlign' => 'center',
            ],
            
            [
                'attribute' => 'collectors',
                'format' => 'html',
                'contentOptions' => ['class' => 'kv-align-left kv-align-middle'],
                'filterType' => GridView::FILTER_SELECT2,
                'filter' => User::getSelectMapAgents(true, true),
                'filterWidgetOptions' => [
                    'pluginOptions' => ['allowClear' => true],
                    'options' => ['multiple' => false],
                ],
                'value' => function ($model) {
                    return CollectorHasCustomer::getCollectorStringByCustomer($model->id);
                },
                'filterInputOptions' => ['placeholder' => '------'],
                'hAlign' => 'center',
            ],
           
            [
                'attribute' => 'route_transport_id',
                'format' => 'html',
                'contentOptions' => ['class' => 'kv-align-left kv-align-middle'],
                'filterType' => GridView::FILTER_SELECT2,
                'filter' => RouteTransport::getSelectMap(),
                'filterWidgetOptions' => [
                    'pluginOptions' => ['allowClear' => true],
                    'options' => ['multiple' => false],
                ],
                'value' => function ($model) {
                    return (isset($model->route_transport_id) && !empty($model->route_transport_id)) ? $model->routeTransport->code . ' - ' . $model->routeTransport->name : '';
                },
                'filterInputOptions' => ['placeholder' => '------'],
                'hAlign' => 'center',
            ],
            */

            $custom_elements_gridview->getActionColumn(),

            $custom_elements_gridview->getCheckboxColumn(),

        ],

        'toolbar' =>  $custom_elements_gridview->getToolbar(),

        'panel' => $custom_elements_gridview->getPanel(),

        'toggleDataOptions' => $custom_elements_gridview->getTogleDataOptions(),
    ]); ?>
</div>

<?php
$url = Url::to([$controllerId . 'multiple_delete'], GlobalFunctions::URLTYPE);
$js = Footer_Bulk_Delete::getFooterBulkDelete($url);
$this->registerJs($js, View::POS_READY);
?>

<?php
// Register action buttons js
$this->registerJs('
    $(document).ready(function()
    {

        function init_click_handlers(){
            $("#btn_import_data").click(function(e) {
                e.preventDefault();
                var url = "'.Url::to(['/customer/import-data'], GlobalFunctions::URLTYPE).'";
                $.LoadingOverlay("show");											
                $.ajax({
                    type: \'GET\',
                    url : url,
                    data : {},
                    success : function(data) {
                        $.LoadingOverlay("hide");
                        $.notify({
                                "message": "Se ha ejecutado el proceso de importación de datos",
                                "icon": "glyphicon glyphicon-ok-sign",
                                "title": "Infomación <br />",						
                                "showProgressbar": false,
                                "url":"",						
                                "target":"_blank"},{"type": "success"}
                        );
                        $.pjax.reload({container:"#grid-pjax"});
                    },
                    error: function(XMLHttpRequest, textStatus, errorThrown) {
                        $.LoadingOverlay("hide");
                        $.notify({
                            "message": "Ha ocurrido un error. Inténtelo nuevamente, si el error persiste, póngase en contacto con el administrador del sistema",
                            "icon": "glyphicon glyphicon-remove text-danger-sign",
                            "title": "Error <hr class=\"kv-alert-separator\">",					
                            "showProgressbar": false,
                            "url":"",						
                            "target":"_blank"},{"type": "danger"}
                        );
                    }						
                });						 								
            });
        }

        init_click_handlers();
        $("#grid-pjax").on("pjax:success", function() {
            init_click_handlers(); //reactivate links in grid after pjax update
        });

    });
');