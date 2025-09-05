<?php

use backend\models\business\InvoiceDocuments;
use kartik\date\DatePicker;
use kartik\file\FileInput;
use kartik\select2\Select2;
use wbraganca\dynamicform\DynamicFormWidget;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;
?>

<div class="padding-v-md">
    <div class="line line-dashed"></div>
</div>
<?php DynamicFormWidget::begin([
    'widgetContainer' => 'dynamicform_wrapper_documentos', // required: only alphanumeric characters plus "_" [A-Za-z0-9_]
    'widgetBody' => '.container-items_documentos', // required: css class selector
    'widgetItem' => '.item_documentos', // required: css class
    'limit' => 2, // the maximum times, an element can be cloned (default 999)
    'min' => 0, // 0 or 1 (default 1)
    'insertButton' => '.add-item_documentos', // css class
    'deleteButton' => '.remove-item_documentos', // css class
    'model' => $modelDocumentos[0],
    'formId' => 'dynamic-form',
    'formFields' => [
        //'descripcion',
        'documento',
        //'fecha',
        //'adjuntar_a_factura',
    ],
]); ?>
<div class="panel panel-default">
    <div class="panel-heading">
        <i class="fa fa-envelope"></i> Documentos
        <?php
        if ($modificar == true) : ?>
            <button type="button" class="pull-right add-item_documentos btn btn-success btn-xs"><i class="fa fa-plus"></i> Adicionar documento</button>
        <?php
        endif;
        ?>
        <div class="clearfix"></div>
    </div>
    <div class="panel-body container-items_documentos">
        <!-- widgetContainer -->
        <?php foreach ($modelDocumentos as $index => $modeldocumento) : ?>
            <div class="item_documentos panel panel-default col-sm-4" style="margin-right: 10px;">
                <!-- widgetBody -->
                <div class="panel-heading">
                    <span class="panel-title-address">Documento: <?= ($index + 1) ?></span>
                    <button type="button" class="pull-right remove-item_documentos btn btn-danger btn-xs"><i class="fa fa-minus"></i></button>
                    <div class="clearfix"></div>
                </div>
                <div class="panel-body">
                    <?php
                    // necessary for update action.
                    if (!$modeldocumento->isNewRecord) {
                        echo Html::activeHiddenInput($modeldocumento, "[{$index}]id");
                    }
                    ?>
                    <?php
                    $modelfacturaDocumento = InvoiceDocuments::findOne(['id' => $modeldocumento->id]);
                    $initialPreview = [];
                    $initialPreviewConfig = [];

                    if ($modelfacturaDocumento) {
                        //$pathImg = '/backend/web/uploads/documents/' . $modelfacturaDocumento->documento;
                        //$initialPreview[] = Html::img($pathImg, ['class' => 'file-preview-image', 'width'=>'50%']);
                        $extension = substr($modelfacturaDocumento->documento, strrpos($modelfacturaDocumento->documento, '.') + 1);
                        if ($extension == 'pdf') {

                            $initialPreview =  $modelfacturaDocumento->documento ? [
                                Url::to('@web/uploads/documents/' . $modelfacturaDocumento->documento),
                            ] : [];

                            $initialPreviewConfig = $modelfacturaDocumento->documento ? [
                                [
                                    'type' => 'pdf',
                                    'key' => $modelfacturaDocumento->id,
                                    'caption' => $modelfacturaDocumento->documento,
                                    'filename' => $modelfacturaDocumento->documento,
                                    'downloadUrl' => Url::to('@web/uploads/documents/' . $modelfacturaDocumento->documento),
                                    'url' => '/invoice/delete-document?id=' . $modelfacturaDocumento->id
                                ],
                            ] : [];
                            /*
                                    $initialPreviewConfig[] = array('type' => 'pdf', 'key' => $modelfacturaDocumento->id, 'caption' => $modelfacturaDocumento->descripcion, 'url' => '/backend/facturacion/proformas/delete-document?id=' . $modelfacturaDocumento->id);
                                    $initialPreview[] = !is_null($modelfacturaDocumento->documento) ? Url::base(true) . '/web/uploads/documents/' . $modelfacturaDocumento->documento : NULL;
                                    */
                        } else
                                if ($extension == 'doc' || $extension == 'docx') {
                            $initialPreview =  $modelfacturaDocumento->documento ? [
                                Url::to('@web/uploads/documents/' . $modelfacturaDocumento->documento),
                            ] : [];

                            $initialPreviewConfig = $modelfacturaDocumento->documento ? [
                                [
                                    'type' => 'object',
                                    'caption' => $modelfacturaDocumento->documento,
                                    'filename' => $modelfacturaDocumento->documento,
                                    'downloadUrl' => Url::to('@web/uploads/documents/' . $modelfacturaDocumento->documento),
                                    'url' => '/invoice/delete-document?id=' . $modelfacturaDocumento->id
                                ],
                            ] : [];

                            /*
                                    $initialPreviewConfig[] = array('type' => 'other', 'key' => $modelfacturaDocumento->id, 'caption' => $modelfacturaDocumento->descripcion, 'url' => '/backend/facturacion/proformas/delete-document?id=' . $modelfacturaDocumento->id);
                                    $initialPreview[] = !is_null($modelfacturaDocumento->documento) ? Url::base(true) . '/web/uploads/documents/' . $modelfacturaDocumento->documento : NULL;
                                    */
                        } else
                                if ($extension == 'xls' || $extension == 'xlsx') {
                            $initialPreview =  $modelfacturaDocumento->documento ? [
                                Url::to('@web/uploads/documents/' . $modelfacturaDocumento->documento),
                            ] : [];

                            $initialPreviewConfig = $modelfacturaDocumento->documento ? [
                                [
                                    'type' => 'object',
                                    'caption' => $modelfacturaDocumento->documento,
                                    'filename' => $modelfacturaDocumento->documento,
                                    'downloadUrl' => Url::to('@web/uploads/documents/' . $modelfacturaDocumento->documento),
                                    'url' => '/invoice/delete-document?id=' . $modelfacturaDocumento->id
                                ],
                            ] : [];
                            /*
                                    $initialPreviewConfig[] = array('type' => 'other', 'key' => $modelfacturaDocumento->id, 'caption' => $modelfacturaDocumento->descripcion, 'url' => '/backend/facturacion/proformas/delete-document?id=' . $modelfacturaDocumento->id);
                                    //$initialPreviewConfig[] = Json::encode($s);
                                    //{type: "office", description: "<h5>Excel Spreadsheet</h5> This is a representative description number five for this file.", size: 45056, caption: "SampleXLSFile_38kb.xls", url: "/file-upload-batch/2", key: 5},
                                    $initialPreview = !is_null($modelfacturaDocumento->documento) ? Url::base(true) . '/web/uploads/documents/' . $modelfacturaDocumento->documento : NULL;
                                    */
                        } else
                                if ($extension == 'txt') {
                            $initialPreview =  $modelfacturaDocumento->documento ? [
                                Url::to('@web/uploads/documents/' . $modelfacturaDocumento->documento),
                            ] : [];

                            $initialPreviewConfig = $modelfacturaDocumento->documento ? [
                                [
                                    'type' => 'object',
                                    'caption' => $modelfacturaDocumento->documento,
                                    'filename' => $modelfacturaDocumento->documento,
                                    'downloadUrl' => Url::to('@web/uploads/documents/' . $modelfacturaDocumento->documento),
                                    'url' => '/invoice/delete-document?id=' . $modelfacturaDocumento->id
                                ],
                            ] : [];
                            /*
                                    $initialPreviewConfig[] = array('type' => 'text', 'key' => $modelfacturaDocumento->id, 'caption' => $modelfacturaDocumento->descripcion, 'url' => '/backend/facturacion/proformas/delete-document?id=' . $modelfacturaDocumento->id);
                                    $initialPreview[] = !is_null($modelfacturaDocumento->documento) ? Url::base(true) . '/web/uploads/documents/' . $modelfacturaDocumento->documento : NULL;
                                    */
                        } else
                                if ($extension == 'ppt' || $extension == 'pptx') {
                            $initialPreview =  $modelfacturaDocumento->documento ? [
                                Url::to('@web/uploads/documents/' . $modelfacturaDocumento->documento),
                            ] : [];

                            $initialPreviewConfig = $modelfacturaDocumento->documento ? [
                                [
                                    'type' => 'object',
                                    'caption' => $modelfacturaDocumento->documento,
                                    'filename' => $modelfacturaDocumento->documento,
                                    'downloadUrl' => Url::to('@web/uploads/documents/' . $modelfacturaDocumento->documento),
                                    'url' => '/invoice/delete-document?id=' . $modelfacturaDocumento->id
                                ],
                            ] : [];
                            /*
                                    $initialPreviewConfig[] = array('type' => 'object', 'key' => $modelfacturaDocumento->id, 'caption' => $modelfacturaDocumento->descripcion, 'url' => '/backend/facturacion/proformas/delete-document?id=' . $modelfacturaDocumento->id);
                                    $initialPreview[] = !is_null($modelfacturaDocumento->documento) ? Url::base(true) . '/web/uploads/documents/' . $modelfacturaDocumento->documento : NULL;
                                    */
                        } else
                                if ($extension == 'zip') {
                            $initialPreview =  $modelfacturaDocumento->documento ? [
                                Url::to('@web/uploads/documents/' . $modelfacturaDocumento->documento),
                            ] : [];

                            $initialPreviewConfig = $modelfacturaDocumento->documento ? [
                                [
                                    'type' => 'object',
                                    'caption' => $modelfacturaDocumento->documento,
                                    'filename' => $modelfacturaDocumento->documento,
                                    'downloadUrl' => Url::to('@web/uploads/documents/' . $modelfacturaDocumento->documento),
                                    'url' => '/invoice/delete-document?id=' . $modelfacturaDocumento->id
                                ],
                            ] : [];
                            /*
                                    $initialPreviewConfig[] = array('type' => 'object', 'key' => $modelfacturaDocumento->id, 'caption' => $modelfacturaDocumento->descripcion, 'url' => '/backend/facturacion/proformas/delete-document?id=' . $modelfacturaDocumento->id);
                                    $initialPreview[] = !is_null($modelfacturaDocumento->documento) ? Url::base(true) . '/web/uploads/documents/' . $modelfacturaDocumento->documento : NULL;
                                    */
                        } else {
                            $initialPreview =  $modelfacturaDocumento->documento ? [
                                Url::to('@web/uploads/documents/' . $modelfacturaDocumento->documento),
                            ] : [];

                            $initialPreviewConfig = $modelfacturaDocumento->documento ? [
                                [
                                    'type' => 'image',
                                    'caption' => $modelfacturaDocumento->documento,
                                    'filename' => $modelfacturaDocumento->documento,
                                    'downloadUrl' => Url::to('@web/uploads/documents/' . $modelfacturaDocumento->documento),
                                    'url' => '/invoice/delete-document?id=' . $modelfacturaDocumento->id
                                ],
                            ] : [];
                            /*
                                    $initialPreviewConfig[] = array('type' => 'image', 'key' => $modelfacturaDocumento->id, 'caption' => $modelfacturaDocumento->descripcion, 'url' => '/backend/facturacion/proformas/delete-document?id=' . $modelfacturaDocumento->id);
                                    $initialPreview[] = !is_null($modelfacturaDocumento->documento) ? Url::base(true) . '/web/uploads/documents/' . $modelfacturaDocumento->documento : NULL;
                                    */
                        }
                    }

                    $doc = '';
                    if (!is_null($modelfacturaDocumento))
                        $doc = $modelfacturaDocumento->documento;


                    // die(var_dump($initialPreview));
                    ?>
                    <?php
                    /*
                                        {
                                            "showPreview":true,
                                            "overwriteInitial":false,
                                            "initialPreview":["http:\/\/www.retailcobranzas.net\/backend\/web\/documentos\/ayM8wErhxEMKf0XzP1YCEvgPFuOot69C.xlsx"],
                                            "initialPreviewAsData":true,
                                            "initialPreviewDownloadUrl":"http:\/\/www.retailcobranzas.net\/backend\/web\/documentos\/ayM8wErhxEMKf0XzP1YCEvgPFuOot69C.xlsx",
                                            "initialPreviewConfig":[{"type":"office","key":7,"caption":"99","url":"\/backend\/facturacion\/proformas\/delete-document?id=7"}],
                                            "preferIconicPreview":true,
                                            "previewFileIconSettings":{"doc":"\u003Ci class = \u0022fa fa-file-word-o text-primary\u0022 aria-hidden=\u0022true\u0022\u003E\u003C\/i\u003E","xls":"\u003Ci class = \u0022fa fa-file-excel-o text-success\u0022 aria-hidden=\u0022true\u0022\u003E \u003C\/i\u003E","ppt":"\u003Ci class = \u0022fa fa-file-powerpoint-o text-danger\u0022 aria-hidden=\u0022true\u0022\u003E \u003C\/i\u003E","pdf":"\u003Ci class = \u0022fa fa-file-pdf-o text-danger\u0022 aria-hidden=\u0022true\u0022\u003E \u003C\/i\u003E","zip":"\u003Ci class = \u0022fa fa-file-archive-o text-muted\u0022 aria-hidden=\u0022true\u0022\u003E \u003C\/i\u003E","htm":"\u003Ci class = \u0022fa fa-file-code-o text-info\u0022 aria-hidden=\u0022true\u0022\u003E \u003C\/i\u003E","txt":"\u003Ci class = \u0022fa fa-file-text text-info\u0022 aria-hidden=\u0022true\u0022\u003E \u003C\/i\u003E","mov":"\u003Ci class = \u0022fa fa-file-video-o text-warning\u0022 aria-hidden=\u0022true\u0022\u003E \u003C\/i\u003E","mp3":"\u003Ci class = \u0022fa fa-file-audio-o text-warning\u0022 aria-hidden=\u0022true\u0022\u003E \u003C\/i\u003E","jpg":"\u003Ci class = \u0022fa fa-file-image-o text-danger\u0022 aria-hidden=\u0022true\u0022\u003E \u003C\/i\u003E","gif":"\u003Ci class = \u0022fa fa-file-image-o text-muted\u0022 aria-hidden=\u0022true\u0022\u003E \u003C\/i\u003E","png":"\u003Ci class = \u0022fa fa-file-image-o text-primary\u0022 aria-hidden=\u0022true\u0022\u003E \u003C\/i\u003E"},
                                            "language":"es",
                                            "resizeImage":false,
                                            "autoOrientImage":true,
                                            "purifyHtml":true};                        
                                        */
                    ?>

                    <?= $form->field($modeldocumento, "[{$index}]documento")->label(false)->widget(FileInput::classname(), [
                        'options' => [
                            'multiple' => false,
                            'accept' => 'file/*',
                            //'class' => 'optionvalue-img',
                            //'theme'=>'',
                        ],
                        'pluginOptions' => [
                            //'uploadUrl'=> "/file-upload-batch/1",
                            //'uploadAsync'=> false,
                            //'minFileCount'=> 2,
                            //'maxFileCount'=> 5,
                            //'otherActionButtons' => '<button class="set-main" type="button" {dataKey} ><i class="glyphicon glyphicon-star"></i></button>',
                            'purifyHtml' => false,
                            'showPreview' => true,
                            'overwriteInitial' => false,
                            'initialPreview' => $initialPreview,
                            'initialPreviewAsData' => true, // identify if you are sending preview data only and not the raw markup
                            //'initialPreviewFileType'=> 'image', // image is the default and can be overridden in config below                                
                            'initialPreviewDownloadUrl' => !is_null($doc) ? Url::base(true) . '/web/uploads/documents/' . $doc : NULL,
                            'initialPreviewConfig' => $initialPreviewConfig,
                            'preferIconicPreview' => true, //this will force thumbnails to display icons for following file extensions
                            'previewFileIconSettings' => [ //configure your icon file extensions
                                'doc' => '<i class = "fa fa-file-word-o text-primary" aria-hidden="true"></i>',
                                'xls' => '<i class = "fa fa-file-excel-o text-success" aria-hidden="true"> </i>',
                                'ppt' => '<i class = "fa fa-file-powerpoint-o text-danger" aria-hidden="true"> </i>',
                                'pdf' => '<i class = "fa fa-file-pdf-o text-danger" aria-hidden="true"> </i>',
                                'zip' => '<i class = "fa fa-file-archive-o text-muted" aria-hidden="true"> </i>',
                                'htm' => '<i class = "fa fa-file-code-o text-info" aria-hidden="true"> </i>',
                                'txt' => '<i class = "fa fa-file-text text-info" aria-hidden="true"> </i>',
                                'mov' => '<i class = "fa fa-file-video-o text-warning" aria-hidden="true"> </i>',
                                'mp3' => '<i class = "fa fa-file-audio-o text-warning" aria-hidden="true"> </i>',
                                //note for these file types below no extension determination logic 
                                //has been configured (the keys itself will be used as extensions)
                                'jpg' => '<i class = "fa fa-file-image-o text-danger" aria-hidden="true"> </i>',
                                'gif' => '<i class = "fa fa-file-image-o text-muted" aria-hidden="true"> </i>',
                                'png' => '<i class = "fa fa-file-image-o text-primary" aria-hidden="true"> </i>'
                            ],
                            /*
                                'previewFileExtSettings'=> [//configure the logic for determining icon file extensions
                                    'doc'=> "function (ext) [
                                        return ext.match (/(doc | docx) $/i);
                                    ]",
                                    'xls'=> "function (ext) [
                                        return ext.match (/(xls | xlsx) $/i);
                                    ]",
                                    'ppt'=> "function (ext) [
                                        return ext.match (/(ppt | pptx) $/i);
                                    ]",
                                    'zip'=> "function (ext) [
                                        return ext.match (/(zip | rar | tar | gzip | gz | 7z) $/i);
                                    ]",
                                    'htm'=> "function (ext) [
                                        return ext.match (/(htm | html) $/i);
                                    ]",
                                    'txt'=> "function (ext) [
                                        return ext.match (/(txt | ini | csv | java | php | js | css) $/i);
                                    ]",
                                    'mov'=> "function (ext) [
                                        return ext.match (/(avi | mpg | mkv | mov | mp4 | 3gp | webm | wmv) $/i);
                                    ]",
                                    'mp3'=> "function (ext) [
                                        return ext.match (/(mp3 | wav) $/i);
                                    ]",
                                ] 
                                */
                        ],

                    ]) ?>
                </div>
                <?php
                /*
                            <div class="col-sm-3">
                                <?= $form->field($modeldocumento, "[{$index}]descripcion")->textInput(['maxlength' => true]) ?>
                            </div>
                            */
                ?>
                <?php
                /*                       
                            <div class="col-sm-3">
                                <?=
                                $form->field($modeldocumento, "[{$index}]fecha")->widget(DatePicker::classname(), [
                                    "options" => ["placeholder" => "Enter date ..."],
                                    "pluginOptions" => [
                                        "autoclose" => true,
                                        "format" => "dd-mm-yyyy"
                                    ]
                                ])
                                ?>                            
                            </div>
                            */
                ?>
                <?php
                /*        
                            <div class="col-sm-3">
                                <?=
                                $form->field($modeldocumento, "[{$index}]adjuntar_a_factura")->widget(Select2::classname(), [
                                    "data" => [1 => 'Si', 0 => 'No'],
                                    "language" => "es",
                                    "options" => ["placeholder" => Yii::t("app", "-- Selecione --"), "multiple" => false],
                                    "pluginOptions" => [
                                        "allowClear" => true
                                    ],
                                ]);
                                ?>
                            </div>
                            */
                ?>
            </div>
        <?php endforeach; ?>
    </div>

</div>
<?php DynamicFormWidget::end(); ?>

<?php
$js = '
$(".dynamicform_wrapper_documentos").on("afterInsert", function(event, item) {    
    var hasSelect2 = $(item).find("[data-krajee-select2]");
    if (hasSelect2.length > 0) {
        var i = 0;
        hasSelect2.each(function() {
            var id = $(this).attr("id");
            $("#" + id).val("").trigger("change"); 
        });
    }

    var hasinput = $(item).find("[data-krajee-fileinput]");    
    if (hasinput.length > 0) {        
        hasinput.each(function() {
            var id = $(this).attr("id");
            $("#" + id).fileinput("reset").trigger("custom-event");
            $("#" + id).fileinput("destroy").fileinput({showPreview: true});
        });
    }
});

jQuery(".dynamicform_wrapper_documentos").on("afterInsert", function(e, item) {
    jQuery(".dynamicform_wrapper_documentos .panel-title-address").each(function(index) {
        jQuery(this).html("Documentos: " + (index + 1))
    });
});
jQuery(".dynamicform_wrapper_documentos").on("afterDelete", function(e) {
    jQuery(".dynamicform_wrapper_documentos .panel-title-address").each(function(index) {
        jQuery(this).html("Documento: " + (index + 1))
    });
});

';
$this->registerJs($js);
?>