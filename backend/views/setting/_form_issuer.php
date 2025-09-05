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
use backend\models\nomenclators\IdentificationType;
use backend\models\nomenclators\Province;
use backend\models\nomenclators\Canton;
use backend\models\nomenclators\Disctrict;
use backend\models\settings\Issuer;
use yii\helpers\Url;
use kartik\depdrop\DepDrop;

/* @var $this yii\web\View */
/* @var $model backend\models\settings\Issuer */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="box-body">
    <?php
    $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>

    <div class="row">
        <div class="col-md-2">
            <?= $form->field($model, 'code')->textInput(['maxlength' => true]) ?>
        </div>
        <div class="col-md-3">
            <?=
            $form->field($model, "identification_type_id")->widget(Select2::classname(), [
                "data" => IdentificationType::getSelectMap(),
                "language" => Yii::$app->language,
                "options" => ["placeholder" => "----", "multiple"=>false],
                "pluginOptions" => [
                    "allowClear" => true
                ],
            ]);
            ?>
        </div>
        <div class="col-md-3">
            <?= $form->field($model, 'identification')->textInput(['maxlength' => true]) ?>
        </div>
        <div class="col-md-3">
            <?= $form->field($model, 'code_economic_activity')->textInput(['maxlength' => true]) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-5">
            <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
        </div>
        <div class="col-md-7">
            <?= $form->field($model, 'address')->textInput(['maxlength' => true]) ?>
        </div>

    </div>

    <div class="row">
        <div class="col-md-2">
            <?=
            $form->field($model, "country_code_phone")->widget(NumberControl::classname(), [
                "maskedInputOptions" => [
                    "allowMinus" => false,
                    "groupSeparator" => "",
                    "radixPoint" => "",
                    "digits" => 0,
                ],
                "displayOptions" => ["class" => "form-control kv-monospace"],
                "saveInputContainer" => ["class" => "kv-saved-cont"]
            ])
            ?>
        </div>
        <div class="col-md-3">
            <?=
            $form->field($model, "phone")->widget(NumberControl::classname(), [
                "maskedInputOptions" => [
                    "allowMinus" => false,
                    "groupSeparator" => "",
                    "radixPoint" => "",
                    "digits" => 0,
                ],
                "displayOptions" => ["class" => "form-control kv-monospace"],
                "saveInputContainer" => ["class" => "kv-saved-cont"]
            ])
            ?>
        </div>
        <div class="col-md-2">

            <?=
            $form->field($model, "country_code_fax")->widget(NumberControl::classname(), [
                "maskedInputOptions" => [
                    "allowMinus" => false,
                    "groupSeparator" => "",
                    "radixPoint" => "",
                    "digits" => 0,
                ],
                "displayOptions" => ["class" => "form-control kv-monospace"],
                "saveInputContainer" => ["class" => "kv-saved-cont"]
            ])
            ?>

        </div>
        <div class="col-md-3">

            <?=
            $form->field($model, "fax")->widget(NumberControl::classname(), [
                "maskedInputOptions" => [
                    "allowMinus" => false,
                    "groupSeparator" => "",
                    "radixPoint" => "",
                    "digits" => 0,
                ],
                "displayOptions" => ["class" => "form-control kv-monospace"],
                "saveInputContainer" => ["class" => "kv-saved-cont"]
            ])
            ?>

        </div>
        <div class="col-md-2">
            <?=
            $form->field($model, "change_type_dollar")->widget(NumberControl::classname(), [
                "maskedInputOptions" => [
                    "allowMinus" => false,
                    "groupSeparator" => ".",
                    "radixPoint" => ",",
                    "digits" => 2
                ],
                "displayOptions" => ["class" => "form-control kv-monospace"],
                "saveInputContainer" => ["class" => "kv-saved-cont"]
            ])
            ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <?= $form->field($model, 'email')->textInput(['maxlength' => true]) ?>
        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'name_brach_office')->textInput(['maxlength' => true]) ?>
        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'number_brach_office')->textInput(['maxlength' => true]) ?>
        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'number_box')->textInput(['maxlength' => true]) ?>
        </div>
    </div>
    <div class="row">
        <div class="col-md-4">
            <?=
            $form->field($model, "province_id")->widget(Select2::classname(), [
                "data" => Province::getSelectMap(),
                "language" => Yii::$app->language,
                "options" => ["placeholder" => "----", "multiple"=>false],
                "pluginOptions" => [
                    "allowClear" => true
                ],
            ]);
            ?>
        </div>

        <div class="col-md-4">
            <?= $form->field($model, 'canton_id')->widget(DepDrop::classname(), [
                'type'=>DepDrop::TYPE_SELECT2,
                'data'=>($model->province_id > 0) ? Canton::getSelectMapSpecific($model->province_id): array(),
                'options'=>['placeholder'=> "----"],
                'select2Options'=>['pluginOptions'=>['allowClear'=>true]],
                'pluginOptions'=>[
                    'depends'=>['issuer-province_id'],
                    'url'=>Url::to(['/util/get_cantons'], GlobalFunctions::URLTYPE),
                    'params'=>['input-type-1', 'input-type-2']
                ]
            ]);
            ?>
        </div>
        <div class="col-md-4">

            <?= $form->field($model, 'disctrict_id')->widget(DepDrop::classname(), [
                'type'=>DepDrop::TYPE_SELECT2,
                'data'=>($model->canton_id > 0) ? Disctrict::getSelectMapSpecific($model->canton_id): array(),
                'options'=>['placeholder'=> "----"],
                'select2Options'=>['pluginOptions'=>['allowClear'=>true]],
                'pluginOptions'=>[
                    'depends'=>['issuer-province_id', 'issuer-canton_id'],
                    'url'=>Url::to(['/util/get_dictrict'], GlobalFunctions::URLTYPE),
                    'params'=>['input-type-1', 'input-type-2']
                ]
            ]);
            ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <?= $form->field($model, 'other_signs')->textInput(['maxlength' => true]) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-2">
            <?= $form->field($model, 'certificate_pin')->passwordInput(['maxlength' => true]) ?>
        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'api_user_hacienda')->textInput(['maxlength' => true]) ?>
        </div>
        <div class="col-md-3">
            <?= $form->field($model, 'api_password')->passwordInput(['maxlength' => true]) ?>
        </div>
        <div class="col-md-3">

            <?=
            $form->field($model,"enable_prod_enviroment")->widget(SwitchInput::classname(), [
                "type" => SwitchInput::CHECKBOX,
                "pluginOptions" => [
                    "onText"=> Yii::t("backend","Activo"),
                    "offText"=> Yii::t("backend","Inactivo")
                ]
            ])
            ?>
        </div>
    </div>
    <div class="row">
        <div class="col-md-4">
            <?=
            $form->field($model, "digital_invoice_footer")->textarea()
            ?>
        </div>

        <div class="col-md-4">
            <?=
            $form->field($model, "electronic_invoice_footer")->textarea()
            ?>
        </div>

        <div class="col-md-4">
            <?= $form->field($model, 'footer_one_receipt')->textarea() ?>
        </div>
    </div>



    <div class="row">
        <div class="col-md-3">
            <?= $form->field($model, 'host_smpt')->textInput(['maxlength' => true]) ?>
        </div>
        <div class="col-md-3">
            <?= $form->field($model, 'user_smtp')->textInput(['maxlength' => true]) ?>
        </div>
        <div class="col-md-3">
            <?= $form->field($model, 'pass_smtp')->passwordInput(['maxlength' => true]) ?>
        </div>
        <div class="col-md-3">
            <?= $form->field($model, 'repass_smtp')->passwordInput(['maxlength' => true]) ?>
        </div>        
    </div>  
    <div class="row">
        <div class="col-md-3">
            <?= $form->field($model, 'puerto_smpt')->textInput(['maxlength' => true]) ?>
        </div>  
        <div class="col-md-3">
            <?= $form->field($model, 'smtp_encryptation')->textInput(['maxlength' => true]) ?>
        </div>                  
        <div class="col-md-3">
            <?= $form->field($model, 'email_notificacion_smtp')->textInput(['maxlength' => true]) ?>
        </div>
    </div>    
    <div class="row">
        <div class="col-md-3">
            <?= $form->field($model, 'ftp_host')->textInput(['maxlength' => true]) ?>
        </div>  
        <div class="col-md-3">
            <?= $form->field($model, 'ftp_user')->textInput(['maxlength' => true]) ?>
        </div>                  
        <div class="col-md-3">
            <?= $form->field($model, 'ftp_password')->textInput(['maxlength' => true]) ?>
        </div>
    </div>     
    <div class="row">
        <div class="col-md-3">
            <?= $form->field($model, 'init_consecutive_invoice')->textInput(['maxlength' => true]) ?>
        </div>  
        <div class="col-md-3">
            <?= $form->field($model, 'init_consecutive_tiquete')->textInput(['maxlength' => true]) ?>
        </div>                  
        <div class="col-md-3">
            <?= $form->field($model, 'init_consecutive_credit_note')->textInput(['maxlength' => true]) ?>
        </div>
    </div>  

    
    <?php

    if($model->isNewRecord)
    {
        $url_logo_file = Issuer::getUrlLogoByIssuerAndType(1);
        $url_signature_digital_file = Issuer::getUrlLogoByIssuerAndType(2);
        $url_certificate_digital = '';
    }
    else
    {
        $url_logo_file = Issuer::getUrlLogoByIssuerAndType(1, $model->id);
        $url_signature_digital_file = Issuer::getUrlLogoByIssuerAndType(2, $model->id);
        $url_certificate_digital = $model->getCertificateUrl();
    }

    ?>
    <div class="row">
        <div class="col-md-4">
            <?= $form->field($model, 'file_main_logo')->widget(FileInput::classname(), [
                'options' => ['accept' => 'image/*'],
                'pluginOptions'=> [
                    'browseIcon'=>'<i class="fa fa-camera"></i> ',
                    'browseLabel'=> Yii::t('backend','Cambiar'),
                    'allowedFileExtensions'=>['jpg','jpeg','gif','png'],
                    'defaultPreviewContent'=> '<img src="'.$url_logo_file.'" class="previewAvatar">',
                    'showUpload'=> false,
                    'layoutTemplates'=> [
                        'main1'=>  '{preview}<div class=\'input-group {class}\'><div class=\'input-group-btn\'>{browse}{upload}{remove}</div>{caption}</div>',
                    ],
                ]
            ]);
            ?>
        </div>

        <div class="col-md-4">
            <?= $form->field($model, 'file_signature_digital')->widget(FileInput::classname(), [
                'options' => ['accept' => 'image/*'],
                'pluginOptions'=> [
                    'browseIcon'=>'<i class="fa fa-camera"></i> ',
                    'browseLabel'=> Yii::t('backend','Cambiar'),
                    'allowedFileExtensions'=>['jpg','jpeg','gif','png'],
                    'defaultPreviewContent'=> '<img src="'.$url_signature_digital_file.'" class="previewAvatar">',
                    'showUpload'=> false,
                    'layoutTemplates'=> [
                        'main1'=>  '{preview}<div class=\'input-group {class}\'><div class=\'input-group-btn\'>{browse}{upload}{remove}</div>{caption}</div>',
                    ],
                ]
            ]);
            ?>
        </div>

        <div class="col-md-4">
            <?php
                if(isset($model->certificate_digital_file) && !empty($model->certificate_digital_file))
                {
                    $url_down = Url::to(['util/download_file','path' => 'certificates', 'file_name' => $model->certificate_digital_file,'name_to_download' => Yii::t('backend','Certificado')], GlobalFunctions::URLTYPE);
                    $btn_download = '
                    <a target="_blank" href="'.$url_down.'" tabindex="500" title="Descargar" class="btn btn-warning btn-secondary"><i class="glyphicon glyphicon-download"></i></a>
                    ';

                    $preview_cert = GlobalFunctions::renderPreviewForIndex('/certificates/'.$model->certificate_digital_file,'Certificado');
                }
                else
                {
                    $btn_download = '';
                    $preview_cert = '';
                }

            ?>
            <?= $form->field($model, 'certificate_digital_file')->widget(FileInput::classname(), [
                'pluginOptions'=> [
                    'browseIcon'=>'<i class="fa fa-camera"></i> ',
                    'browseLabel'=> Yii::t('backend','Cambiar'),
                    'allowedFileExtensions'=>['p12'],
                    'defaultPreviewContent'=> $preview_cert,
                    'showUpload'=> false,
                    'layoutTemplates'=> [
                        'main1'=>  '{preview}<div class=\'input-group {class}\'><div class=\'input-group-btn\'>{browse}{upload}{remove}'.$btn_download.'</div>{caption}</div>',
                    ],
                ]
            ]);
            ?>
        </div>
    </div>

</div>
<div class="box-footer">
    <?= Html::submitButton($model->isNewRecord ? '<i class="fa fa-plus"></i> '.Yii::t('backend','Crear') : '<i class="fa fa-pencil"></i> '.Yii::t('yii', 'Update'), ['class' => 'btn btn-default btn-flat']) ?>
    <?= Html::a('<i class="fa fa-remove"></i> '.Yii::t('backend','Cancelar'),['index'], ['class' => 'btn btn-default btn-flat margin', 'title' => Yii::t('backend','Cancelar')]) ?>
</div>
<?php ActiveForm::end(); ?>
