<?php

use yii\helpers\Html;
use kartik\form\ActiveForm;
use kartik\builder\Form;
use kartik\widgets\FileInput;
use kartik\switchinput\SwitchInput;
use dosamigos\ckeditor\CKEditor;
use kartik\date\DatePicker;
use kartik\number\NumberControl;
use common\models\GlobalFunctions;
use kartik\datecontrol\DateControl;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use common\models\User;
use backend\models\nomenclators\Boxes;
use backend\models\nomenclators\BranchOffice;
use kartik\depdrop\DepDrop;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model backend\models\business\CashRegister */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="box-body">
    <?php
    $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data'], 'id' => $model->formName()]); ?>
    <input type="hidden" name="CashRegister[status]" value="1">
    <input type="hidden" name="CashRegister[initial_amount]" value="0">

    <div class="row">
        <div class="col-md-4">
            <div class="row">
                <div class="col-md-12">
                    <?=
                    $form->field($model, "branch_office_id")->widget(Select2::classname(), [
                        "disabled" => GlobalFunctions::getRol() === User::ROLE_AGENT,
                        "data" => BranchOffice::getSelectMap(),
                        "language" => Yii::$app->language,
                        "options" => ["placeholder" => "----", "multiple" => false],
                        "pluginOptions" => [
                            "allowClear" => true
                        ],
                    ]);
                    ?>
                </div>
                <div class="col-md-12">
                    <?= $form->field($model, 'box_id')->widget(DepDrop::classname(), [
                        "disabled" => GlobalFunctions::getRol() === User::ROLE_AGENT,
                        'type' => DepDrop::TYPE_SELECT2,
                        'data' => ($model->branch_office_id !== '') ? Boxes::getSelectMap($model->branch_office_id) : array(),
                        'options' => ['placeholder' => "----"],
                        'select2Options' => ['pluginOptions' => ['allowClear' => true]],
                        'pluginOptions' => [
                            'depends' => ['cashregister-branch_office_id'],
                            'url' => Url::to(['/util/get-boxes'], GlobalFunctions::URLTYPE),
                            'params' => ['input-type-1', 'input-type-2']
                        ]
                    ]);
                    ?>
                </div>
                <div class="col-md-12">
                    <?=
                    $form->field($model, "seller_id")->widget(Select2::classname(), [
                        "data" => User::getSelectMapAgentsByBoxId($box_id, true),
                        "language" => Yii::$app->language,
                        "options" => ["placeholder" => "----", "multiple" => false],
                        "pluginOptions" => [
                            "allowClear" => true
                        ],
                    ]);
                    ?>
                </div>
                <div class="col-md-12">
                    <?=
                    $form->field($model, "opening_date")->widget(DateControl::classname(), [
                        "type" => DateControl::FORMAT_DATE,
                        "disabled" => true
                    ])
                    ?>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <?= $form->field($model, 'opening_time')->textInput(['readonly' => true]) ?>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="row">
                <div class="col-md-12">
                    <?= $this->render('_cajaOpenCoins', ['coins' => $coins]) ?>
                </div>
            </div>
        </div>
    </div>
    <div class="box-footer">
        <?= Html::submitButton($model->isNewRecord ? '<i class="fa fa-plus"></i> ' . Yii::t('backend', 'Abrir Caja') : '<i class="fa fa-pencil"></i> ' . Yii::t('yii', 'Editar Caja'), ['class' => 'btn btn-default btn-flat', 'confirm' => 'Are you Sure']) ?>
        <?= Html::a('<i class="fa fa-remove"></i> ' . Yii::t('backend', 'Cancelar'), ['#'], ['class' => 'btn btn-default btn-flat margin', 'id'=>'btn-cancel', 'title' => Yii::t('backend', 'Cancelar')]) ?>
    </div>
</div>

<?php ActiveForm::end(); ?>


<?php
$js = <<<JS
// get the form id and set the event
function init()
{
	$('form#{$model->formName()}').on('beforeSubmit', function(e) {
		e.preventDefault();
		e.stopImmediatePropagation();	
		var formdata = false;
		if(window.FormData){
			formdata = new FormData($(this)[0]);
		}
		
		var formAction = $(this).attr('action');
		$("#spinner").show();
		$.ajax({
			type        : 'POST',
			url         : formAction,
			cache       : false,
			data        : formdata ? formdata : form.serialize(),
			contentType : false,
			processData : false,
	
			success: function(response) {
                    $.notify({
                        "message": response,
                        "icon": "glyphicon glyphicon-ok-sign",
                        "title": 'Información',						
                        "showProgressbar": false,
                        "url":"",						
                        "target":"_blank"},{"type": 'warning'});

                    //mmd_closed();
                    $("#myModal2").modal('hide');
                    $('form#{$model->formName()}').trigger("reset");
                    mmd_closed();									
					$("#spinner").hide();
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
				$("#spinner").hide();
			}				
		});
		return false;
		// do whatever here, see the parameter \$form1? is a jQuery Element to your form
	}).on('submit', function(e){
		e.preventDefault();
		e.stopImmediatePropagation();	
	});

    $("#btn-cancel").click(function(e) {
		e.preventDefault();	
	});	

}
init();

$( document ).ajaxComplete(function() {
  init();
});


JS;
$this->registerJs($js);
?>