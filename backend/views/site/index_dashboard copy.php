<?php

use common\models\GlobalFunctions;
use kartik\builder\Form;
use kartik\form\ActiveForm;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;


$this->title = 'Panel de Administración';
?>

<div class="row">
	<div class="col-xs-12">
		<div class="box box-primary">
			<!-- /.box-header -->
			<div class="box-body">
				<?php
				$form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]);
				?>
				<div class="row">
					<div class="col-md-3">
						<?=
						$form->field($model, "anno")->widget(Select2::classname(), [
							"data" => $dataannos,
							"language" => "es",
							"options" => ["placeholder" => Yii::t("app", "-- Selecione --"), "multiple" => false],
							"pluginOptions" => [
								"allowClear" => true
							],
							'disabled' => false,
						]);
						?>
					</div>
					<div class="col-md-3">
						<?=
						$form->field($model, "mes")->widget(Select2::classname(), [
							"data" => $dataMeses,
							"language" => "es",
							"options" => ["placeholder" => Yii::t("app", "-- Selecione --"), "multiple" => false],
							"pluginOptions" => [
								"allowClear" => true
							],
							'disabled' => false,
						]);
						?>
					</div>
					<div class="col-md-3">
						<?=
						$form->field($model, "moneda")->widget(Select2::classname(), [
							"data" => [1 => 'CRC', 2 => 'USD'],
							"language" => "es",
							"options" => ["placeholder" => Yii::t("app", "-- Selecione --"), "multiple" => false],
							"pluginOptions" => [
								"allowClear" => true
							],
							'disabled' => false,
						]);
						?>
					</div>
					<div class="col-md-3">

						<div class="form-group pull-right" style="margin-top: 25px;">
							<?= Html::submitButton("<i class='glyphicon glyphicon-picture text-info'></i> Filtrar", ['class' => 'btn btn-default', 'name' => 'btnguardar']); ?>
						</div>

					</div>
				</div>

				<?php ActiveForm::end(); ?>

			</div>
		</div>
	</div>
</div>



<div class="row">
	<div class="col-md-4 col-xs-6">
		<!-- small box -->
		<div class="small-box bg-red">
			<div class="inner">
				<h3><?= $moneda ?><?= number_format($total_mes_crc, 2, ',', '.'); ?></h3>
				<p>
					Total Facturado Con IVA <?= $nombre_mes ?> <?= $anno ?>
				</p>
			</div>
			<div class="icon">
				<i class="ion ion-social-usd"></i>
			</div>
			<a href="<?= \yii\helpers\Url::to(['/invoice'], GlobalFunctions::URLTYPE) ?>" class="small-box-footer">
				Ir a Facturas <i class="fa fa-arrow-circle-right"></i>
			</a>
		</div>
	</div>
	<?php
	//	die(var_dump($total_descuentos_usd));
	?>
	<div class="col-lg-4 col-xs-6">
		<div class="small-box bg-green">
			<div class="inner">
				<h3><?= $moneda ?><?= number_format($total_iva_mes_crc, 2, ',', '.'); ?></h3>

				<p>Total IVA <?= $nombre_mes ?> <?= $anno ?></p>
			</div>
			<div class="icon">
				<i class="ion ion-social-usd"></i>
			</div>
			<a href="<?= \yii\helpers\Url::to(['/invoice'], GlobalFunctions::URLTYPE) ?>" class="small-box-footer">
				Ir a Facturas <i class="fa fa-arrow-circle-right"></i>
			</a>
		</div>
	</div>

	<div class="col-lg-4 col-xs-6">
		<div class="small-box bg-yellow">
			<div class="inner">
				<h3><?= $moneda ?><?= number_format($total_descuentos_crc, 2, ',', '.'); ?></h3>

				<p>Total descuento <?= $nombre_mes ?> <?= $anno ?></p>
			</div>
			<div class="icon">
				<i class="ion ion-social-usd"></i>
			</div>
			<a href="<?= \yii\helpers\Url::to(['/invoice'], GlobalFunctions::URLTYPE) ?>" class="small-box-footer">
				Ir a Facturas <i class="fa fa-arrow-circle-right"></i>
			</a>
		</div>
	</div>
</div>


<div class="row">
	<div class="col-md-6">
		<div id="chart-container-line" style="text-align:center; width:100%"></div>
	</div>
	<div class="col-md-6">
		<div id="chart-container-pie" style="text-align:center; width:100%"></div>
	</div>
</div>

<div class="row">
	<div class="col-md-6">
		<div id="chart-container-bar" style="text-align:center; width:100%"></div>
	</div>
</div>

<?php
//if (!empty($categorias))
//{

$desde = $anno - 1;
$subcaption = $anno;

$js = <<<JS


FusionCharts.ready(function () {
    var LineChartFiscal = new FusionCharts({
        type: "msline",
        renderAt: 'chart-container-line',
        width: '100%',
        height: '400',
        dataFormat: 'json',
        dataSource: 
		{
			"chart": {
				"theme": "zune",
				"caption": "Facturación por Meses en CRC",
				"subCaption": '$subcaption',
				"showhovereffect": "1",
				"drawcrossline": "1",
				"valueFontColor": "000000",
				"valueFontSize": "14",
				
				"xAxisName": 'Meses',
				"yAxisName": "Facturado",
				"lineThickness": "2",
				"decimals": "2",
				"showBorder": "1",
				"showLegend": "1",
				"exportEnabled": "1",
				
				// Color de fondo				
				"bgColor": "EEEEEE,CCCCCC",
				"bgratio": "60,40",
				"bgAlpha": "70,80",
				"bgAngle": "180",
				
				"paletteColors":"605CA8,FF0000,0372AB,FF5904,1D9050,29C3BE,E9E454,00593D",						
				
				"numberPrefix": '$moneda',
				//Disabling number scale compression
				"formatNumberScale": "0",
				//Defining custom decimal separator
				"decimalSeparator": ",",
				//Defining custom thousand separator
				"thousandSeparator": ".",
				"showValues": "1"															
			},
			"categories": [{
				"category": [
				{
					"label": "Ene"
				}, 
				{
					"label": "Feb"
				}, 
				{
					"label": "Mar"
				}, 
				{
					//"vline": "true",
					//"lineposition": "0",
					//"color": "#62B58F",
					//"labelHAlign": "center",
					//"labelPosition": "0",
					"label": "Abr",
					//"dashed": "1"
				}, 
				{
					"label": "May"
				}, 
				{
					"label": "Jun"
				}, 
				{
					"label": "Jul"
				}, 
				{
					"label": "Ago"
				},
				{
					"label": "Sep"
				},
				{
					"label": "Oct"
				},
				{
					"label": "Nov"
				},
				{
					"label": "Dic"
				}]
			}],
			"dataset": $graf_facturacion_meses
        }
    });
    
    LineChartFiscal.render();
});		


FusionCharts.ready(function () {
    var Pie3DChart = new FusionCharts({
        type: 'pie3d',
        renderAt: 'chart-container-pie',
        width: '100%',
        height: '400',
        dataFormat: 'json',
        dataSource: {
            "chart": {
                "caption": "Ventas por rutas",
				"subCaption": '$nombre_mes de $anno',
				"nfusionPrefix": "$",
				"showPercentInTooltip": "1",
				"decimals": "2",
				"useDataPlotColorForLabels": "1",
				"theme": "zune",
				"smartLineColor": "#ff0000",
				"smartLineThickness": "2",
				"smartLineAlpha": "100",
				"isSmartLineSlanted": "0",
				"showBorder": "1",
				"showLegend": "1",	
				"exportEnabled": "1",
				"valueFontColor": "000000",
				"valueFontSize": "14",
				
				"bgColor": "EEEEEE,CCCCCC",
				"bgratio": "60,40",
				"bgAlpha": "70,80",
				"bgAngle": "180",
				
				"paletteColors":"00593D,605CA8,FF0000,0372AB,29C3BE,E9E454,FF5904,1D9050",						
				
				
				"numberPrefix": "$",
				//Disabling number scale compression
				"formatNumberScale": "0",
				//Defining custom decimal separator
				"decimalSeparator": ",",
				//Defining custom thousand separator
				"thousandSeparator": ".",
				"showValues": "1"																
            },
            "data": $graf_facturacion_rutas,
        }
    }).render();	
});	


FusionCharts.ready(function(){
	var chartObj = new FusionCharts({
		type: 'column3d',
		renderAt: 'chart-container-bar',
		width: '100%',
		height: '400',
		dataFormat: 'json',
		dataSource: {
			"chart": {
				"theme": "zune",
				"caption": "Ventas por vendedor",
				"subCaption": '$nombre_mes de $anno',
				"xAxisName": 'Vendedores',
				"yAxisName": "Facturación",
				"lineThickness": "2",
				"decimals": "2",
				"showBorder": "1",
				"showLegend": "1",
				"exportEnabled": "1",
				"rotateLabels": "0",	
				"rotateValues": "0",
				
				// Color de fondo				
				"bgColor": "EEEEEE,CCCCCC",
				"bgratio": "60,40",
				"bgAlpha": "70,80",
				"bgAngle": "180",

				"valueFontColor": "000000",
				"valueFontSize": "14",
				"paletteColors":"00593D,0372AB,FF5904,FF0000,605CA8,29C3BE,E9E454,1D9050",					
				
				
				"numberPrefix": "",
				//Disabling number scale compression
				"formatNumberScale": "0",
				//Defining custom decimal separator
				"decimalSeparator": ",",
				//Defining custom thousand separator
				"thousandSeparator": ".",
				"showValues": "1"
			},
			data: $graf_facturacion_vendedor,	
		}
	});
	chartObj.render();
});


JS;
$this->registerJs($js);
?>