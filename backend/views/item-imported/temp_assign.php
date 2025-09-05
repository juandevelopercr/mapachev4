<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use backend\models\business\SectorLocation;
use backend\models\business\ItemImported;

/* @var $this yii\web\View */
/* @var $model \backend\models\business\AssignLocation */
/* @var $branch_office_id */
/* @var $form yii\widgets\ActiveForm */
?>
<div class="row">
    <div class="col-xs-12">
        <div class="box box-primary">
            <!-- /.box-header -->
            <div class="box-body">
                <?php
                $form = ActiveForm::begin([
                    'options' => ['enctype' => 'multipart/form-data'], 'id' => $model->formName(),
                    'action' => ["/item-imported/assign_multiple_location_ajax?branch_office_id=".$branch_office_id]
                ]);?>

                <div class="row">
                    <div class="col-md-12">
                        <?=
                        $form->field($model, 'item_imported_ids')->widget(Select2::classname(), [
                            'readonly' => true,
                            'data' => ItemImported::getSelectMap(false,$branch_office_id),
                            'language' => Yii::$app->language,
                            'maintainOrder' => true,
                            'options' => [
                                'placeholder' => Yii::t('backend','Items pendientes'),
                                'multiple' => true
                            ],
                        ])
                        ?>
                    </div>
                    <div class="col-md-4">
                        <?=
                        $form->field($model, "sector_location_id")->widget(Select2::classname(), [
                            "data" => SectorLocation::getSelectMap(false,null,$branch_office_id),
                            "language" => Yii::$app->language,
                            "options" => ["placeholder" => "Ubicación [Sector])", "multiple"=>false],
                            "pluginOptions" => [
                                "allowClear" => true
                            ],
                        ]);
                        ?>
                    </div>
                </div>


                <div class="form-group pull-right">
                    <?= Html::a('<i class="glyphicon glyphicon-remove text-danger"></i> '.Yii::t('backend','Cancelar'), ['index'], ['data-pjax'=>0, 'class' => 'btn btn-default', 'id'=>'btn-cancel-item_imported']); ?>&nbsp;
                    <?= Html::submitButton("<i class=\"glyphicon glyphicon-floppy-save text-success\"></i> ".Yii::t('backend','Asignar'), ['class'=> 'btn btn-default', 'name'=>'btnguardar']); ?>
                </div>
                <?php ActiveForm::end(); ?>
            </div>
        </div>
    </div>
</div>

<?php

$js = <<<JS
$(document).ready(function(e) {
       
    // get the form id and set the event
    $('form#{$model->formName()}').on('beforeSubmit', function(e) {
        var formdata = false;
        if(window.FormData){
            formdata = new FormData($(this)[0]);
        }
    
        var formAction = $(this).attr('action');
        $.ajax({
            type        : 'POST',
            url         : formAction,
            cache       : false,
            data        : formdata ? formdata : form.serialize(),
            contentType : false,
            processData : false,
    
            success: function(response) {
                    $.pjax.reload({container: '#grid-item_imported-pjax', timeout: 2000});
                    $.notify({
                            "message": response.message,
                            "icon": "glyphicon glyphicon-ok-sign",
                            "title": response.titulo,						
                            "showProgressbar": false,
                            "url":"",						
                            "target":"_blank"},{"type": response.type});
                    showPanels();
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                $.notify({
                    "message": "Ha ocurrido un error. Inténtelo nuevamente, si el error persiste, póngase en contacto con el administrador del sistema",
                    "icon": "glyphicon glyphicon-remove text-danger-sign",
                    "title": "Error <hr class='kv-alert-separator'>",			
                    "showProgressbar": false,
                    "url":"",						
                    "target":"_blank"},{"type": "danger"}
                );
            }				
            
        });
        return false;
        // do whatever here, see the parameter \$form1? is a jQuery Element to your form
    }).on('submit', function(e){
        e.preventDefault();
    });
		
    $("#btn-cancel-item_imported").click(function(e) {
        e.preventDefault();
        showPanels();
    });			
    
    function showPanels() {
        $("#panel-form-item_imported").html('');
        $("#panel-grid-item_imported").show(15);	
        $.pjax.reload({container: '#grid-item_imported-pjax', timeout: 2000});			
    }
    });
JS;
$this->registerJs($js);
?>

