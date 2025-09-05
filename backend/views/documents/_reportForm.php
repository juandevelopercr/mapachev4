<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use kartik\file\FileInput;
use kartik\select2\Select2;
use backend\models\business\DocumentsState;
use yii\helpers\Url;
use kartik\daterange\DateRangePicker;
use backend\models\business\Customer;
use backend\models\nomenclators\Currency;
/* @var $this yii\web\View */
/* @var $searchModel backend\modules\facturacion\models\FacturasSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Reporte Recepción de Documentos';
$this->params['breadcrumbs'][] = $this->title;

// add conditions that should always apply here
?>
<div class="row">
	<div class="col-xs-12">
		<div class="box box-primary">
			<!-- /.box-header -->
			<div class="box-body">
				<?php 
                $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]);
                ?>
                <input type="hidden" name="module_id" value='gridview' />
                <input type="hidden" name="export_filetype" value="xls" />
                <input type="hidden" name="export_filename" value="recepcion-de-documentos" id="inpFileName"/>
                <input type="hidden" name="export_mime" value="application/vnd.ms-excel" />
                <input type="hidden" name="export_config" value='{"worksheet":"Recepcion Documentos","cssFile":""}' />
                <input type="hidden" name="export_encoding" value="utf-8" />
                <input type="hidden" name="export_bom" value="1"  />                
                <div class="row">
                  <div class="col-md-6">     
                    <?=
                        $form->field($model, "emisor")->widget(Select2::classname(), [
                            "data" => Customer::getSelectMap(true, NULL),
                            "language" => "es",
                            "options" => ["placeholder" => Yii::t("app", "-- Selecione --"), "multiple"=>true],
                            "pluginOptions" => [
                                "allowClear" => true
                            ],
                            'disabled'=>false,
                        ]);
                      ?>
                  </div>    
                  <div class="col-md-3">     
                    <?=
                        $form->field($model, "tipo")->widget(Select2::classname(), [
                            "data" => ['01'=>'FE', '02'=>'ND', '03'=>'NC', '04'=>'TE', '08'=>'FEC', '09'=>'FEE'],
                            "language" => "es",
                            "options" => ["placeholder" => Yii::t("app", "-- Selecione --"), "multiple"=>false],
                            "pluginOptions" => [
                                "allowClear" => true
                            ],
                            'disabled'=>false,
                        ]);
                      ?>
                  </div> 
                  <div class="col-md-3">     
                    <?=
                        $form->field($model, "moneda")->widget(Select2::classname(), [
                            "data" => Currency::getSelectMap(),
                            "language" => "es",
                            "options" => ["placeholder" => Yii::t("app", "-- Selecione --"), "multiple"=>false],
                            "pluginOptions" => [
                                "allowClear" => true
                            ],
                            'disabled'=>false,
                        ]);
                      ?>
                  </div>  
                </div>
                <div class="row">                                                      
                  <div class="col-md-3">    
						<?=
                            $form->field($model, 'fecha')->widget(DateRangePicker::classname(), [
                                "options" => ["placeholder" => "Enter date ..."],
								'convertFormat'=>true,
                                "pluginOptions" => [
									'timePicker'=>false,
									'timePickerIncrement'=>30,
									'locale'=>[
										'format'=>'d-m-Y'
									]
                                ]
                            ])
                        ?>                      
                  </div>   
                  <div class="col-md-3">     
                    <?=
                        $form->field($model, "estado_id")->widget(Select2::classname(), [
                            "data" => ArrayHelper::map(DocumentsState::find()->asArray()->all(), "id", "name"),
                            "language" => "es",
                            "options" => ["placeholder" => Yii::t("app", "-- Selecione --"), "multiple"=>false],
                            "pluginOptions" => [
                                "allowClear" => true
                            ],
                            'disabled'=>false,
                        ]);
                      ?>
                  </div>                                                     
				</div>
				<?php 
                $mostrar = 'Mostrar Reporte';		
                ?>
                <div class="form-group pull-right">
                  <?=  Html::submitButton("<i class='glyphicon glyphicon-print text-info'></i> ".$mostrar."", ['class'=> 'btn btn-default', 'name'=>'btnguardar']); ?>
                </div>
                <?php ActiveForm::end(); ?>
			</div>
		</div>
	</div>
</div>

