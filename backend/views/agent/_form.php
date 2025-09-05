<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\file\FileInput;
use common\models\User;
use kartik\switchinput\SwitchInput;
use backend\models\nomenclators\Boxes;
use common\models\GlobalFunctions;
use kartik\select2\Select2;
use dosamigos\ckeditor\CKEditor;
use backend\models\nomenclators\BranchOffice;
use kartik\depdrop\DepDrop;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model common\models\User */
/* @var $form yii\widgets\ActiveForm */
?>

<?php $form = ActiveForm::begin(); ?>

<div class="box-body">

    <div class="row">
        <div class="col-md-3">
            <?php

            if ($model->isNewRecord)
                $urlAvatar = User::getUrlAvatarByUserID();
            else
                $urlAvatar = User::getUrlAvatarByUserID($model->id);
            ?>

            <?= $form->field($model, 'fileAvatar')->widget(FileInput::classname(), [
                'options' => ['accept' => 'image/*'],
                'pluginOptions' => [
                    //'browseIcon'=>'<i class="fa fa-camera"></i> ',
                    //'allowedFileExtensions'=>['jpg','gif','png'],
                    'defaultPreviewContent' => '<img src="' . $urlAvatar . '" class="previewAvatar">',
                    'showUpload' => false,
                    'layoutTemplates' => [
                        'main1' =>  '{preview}<div class=\'input-group {class}\'><div class=\'input-group-btn\'>{browse}{upload}{remove}</div>{caption}</div>',
                    ],
                ]
            ]);
            ?>
        </div>
        <div class="col-md-9">
            <div class="row">
                <div class="col-md-6">
                    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
                </div>
                <div class="col-md-6">
                    <?= $form->field($model, 'last_name')->textInput(['maxlength' => true]) ?>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <?= $form->field($model, 'username')->textInput(['maxlength' => true]) ?>
                </div>
                <div class="col-md-6">
                    <?= $form->field($model, 'email')->textInput(['maxlength' => true]) ?>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <?= $form->field($model, 'password_hash')->passwordInput(['maxlength' => true]) ?>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <?=
                    $form->field($model, "branch_office_id")->widget(Select2::classname(), [
                        "data" => BranchOffice::getSelectMap(),
                        "language" => Yii::$app->language,
                        "options" => ["placeholder" => "----", "multiple" => false],
                        "pluginOptions" => [
                            "allowClear" => true
                        ],
                    ]);
                    ?>
                </div>
                <div class="col-md-6">
                    <?= $form->field($model, 'box_id')->widget(DepDrop::classname(), [
                        'type' => DepDrop::TYPE_SELECT2,
                        'data' => ($model->branch_office_id !== '') ? Boxes::getSelectMap($model->branch_office_id) : array(),
                        'options' => ['placeholder' => "----"],
                        'select2Options' => ['pluginOptions' => ['allowClear' => true]],
                        'pluginOptions' => [
                            'depends' => ['user-branch_office_id'],
                            'url' => Url::to(['/util/get-boxes'], GlobalFunctions::URLTYPE),
                            'params' => ['input-type-1', 'input-type-2']
                        ]
                    ]);
                    ?>
                </div>
            </div>

        </div>
    </div>

</div>
<div class="box-footer">
    <?= Html::submitButton($model->isNewRecord ? '<i class="fa fa-plus"></i> ' . Yii::t('backend', 'Crear') : '<i class="fa fa-pencil"></i> ' . Yii::t('backend', 'Actualizar'), ['class' => 'btn btn-default btn-flat']) ?>
    <?= Html::a('<i class="fa fa-remove"></i> ' . Yii::t('backend', 'Cancelar'), ['index'], ['class' => 'btn btn-default btn-flat margin', 'title' => Yii::t('backend', 'Cancelar')]) ?>
</div>
<?php ActiveForm::end(); ?>