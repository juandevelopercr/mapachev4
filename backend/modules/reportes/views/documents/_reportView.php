<?php
use backend\models\nomenclators\IdentificationType;
use backend\models\nomenclators\UtilsConstants;
?>
<html xmlns:o='urn:schemas-microsoft-com:office:office' xmlns:x='urn:schemas-microsoft-com:office:excel' xmlns='http://www.w3.org/TR/REC-html40'>
  <head>
  <meta http-equiv='Content-Type' content='text/html;charset=utf-8'/>
  <!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>Exportar Hoja de Trabajo</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]-->
  </head>
  <body>
  <table class='kv-grid-table table table-bordered table-striped kv-table-wrap'>
    <thead>
      <tr style="background-color: #f4f4f4;">
            <th class='kv-align-center kv-align-middle' colspan="8" data-col-seq='2' style='font-size:18px'>
                <strong>REPORTE DE RECEPCIÓN DE DOCUMENTOS <?= $fecha_ini ?> al <?= $fecha_fin ?></strong>          
            </th>    
      </tr>          
      <tr>
        <th class='kv-align-center kv-align-middle' data-col-seq='2' style='width: 15.04%;'>Fecha Emisión</th>
        <th class='kv-align-center kv-align-middle' data-col-seq='3' style='width: 15.04%;'>Fecha Recepción</th>
        <th class='kv-align-center kv-align-middle' data-col-seq='3' style='width: 15.04%;'>Receptor</th>        
        <th class='kv-align-center kv-align-middle' data-col-seq='4' style='width: 15.04%;'>Emisor</th>
        <th class='kv-align-center kv-align-middle' data-col-seq='4' style='width: 15.04%;'>Emisor Email</th>        
        <th class='kv-align-center' data-col-seq='8' style='width: 8.66%;'>Tipo de Identificación Emisor</th>
        <th class='kv-align-center' data-col-seq='8' style='width: 8.66%;'>Identificación Emisor</th>
        <th class='kv-align-center' data-col-seq='8' style='width: 8.66%;'>Consecutivo</th>
        <th class='kv-align-center' data-col-seq='8' style='width: 8.66%;'>Moneda</th>
        <th class='kv-align-right' data-col-seq='9' style='width:  5.16%;'>Tipo de Cambio </th>  
        <th class='kv-align-right' data-col-seq='11' style='width: 10.16%;'>Tipo de Documento</th>         
		    <th class='kv-align-right' data-col-seq='11' style='width: 10.16%;'>Tipo de Impuesto</th>                  
        <th class='kv-align-right' data-col-seq='5' style='width: 10.57%;'>Subtotal</th>
        <th class='kv-align-right' data-col-seq='5' style='width: 10.57%;'>I.V.A</th>
        <th class='kv-align-right' data-col-seq='6' style='width: 10.04%;'>Total</th>
        <th class='kv-align-right' data-col-seq='10' style='width: 20.16%;'>Mensaje de Aceptación</th>  
      </tr>
    </thead>
    <tbody>
	<?php
	$index = 1;
	
	$total = 0;
	$total_iva = 0;
	
	$sum_total = 0;
	$sum_total_iva = 0;	
	$sum_subtotal = 0;

	foreach ($datos as $d) : ?>  
    	<?php
		  $tipo_identificacion = IdentificationType::find()->where(['code'=>$d->transmitter_identification_type])->one();
		  ?>	  
          <tr data-key="<?= $index ?>">
            <td class='kv-align-left kv-align-middle kv-align-center' data-col-seq='3' style='mso-number-format: &quot;\@&quot;;'><?= date('d-m-Y', strtotime($d->emission_date)) ?></td>
            <td class='kv-align-left kv-align-middle kv-align-center' data-col-seq='3' style='mso-number-format: &quot;\@&quot;;'><?= date('d-m-Y', strtotime($d->reception_date)) ?></td>            
            <td class='kv-align-center kv-align-middle' data-col-seq='2' style='mso-number-format: &quot;\@&quot;;'><?= $d->receiver->name ?></td>
            <td class='kv-align-center kv-align-middle' data-col-seq='2' style='mso-number-format: &quot;\@&quot;;'><?= $d->transmitter ?></td>
            <td class='kv-align-center kv-align-middle' data-col-seq='2' style='mso-number-format: &quot;\@&quot;;'><?= $d->transmitter_email ?></td>            
            <td class='kv-align-center kv-align-middle' data-col-seq='2' style='mso-number-format: &quot;\@&quot;;'><?= !is_null($tipo_identificacion) ? $tipo_identificacion->name: '-' ?></td>
            <td class='kv-align-center kv-align-middle' data-col-seq='2' style='mso-number-format: &quot;\@&quot;;'><?= $d->transmitter_identification ?></td>
            <td class='kv-align-center kv-align-middle' data-col-seq='2' style='mso-number-format: &quot;\@&quot;;'><?= substr($d->key, 21, 20) ?></td>                                    
            <td class='kv-align-center kv-align-middle' data-col-seq='2' style='mso-number-format: &quot;\@&quot;;'><?= $d->currency ?></td>
            <td class='kv-align-right kv-align-middle' data-col-seq='8'  style='mso-number-format: &quot;\#\,\#\#0\.00&quot;;'><?= number_format($d->change_type, 2, ".", ",") ?></td>
            <td class='kv-align-center kv-align-middle' data-col-seq='2' style='mso-number-format: &quot;\@&quot;;'>
				<?php 
				$str = '';
				switch ($d->document_type){ 
					case '01':$str = 'FE';
							  break;	 
					case '02':$str = 'ND';
							  break;	 
					case '03':$str = 'NC';
							  break;	 
					case '04':$str = 'TE';
							  break;	
					case '05':$str = 'MR';
							  break;
					case '06':$str = 'MR';
							  break;
					case '07':$str = 'MR';
							  break;
					case '08':$str = 'FEC';
							  break;
					case '09':$str = 'FEE';							  								   
						      break;
				}  
				echo $str;	
			?>
          </td>
            <td class='kv-align-center kv-align-middle' data-col-seq='2' style='mso-number-format: &quot;\@&quot;;'><?= $d->total_tax > 0 ? 'Gravado': 'Exento' ?></td>
            <td class='kv-align-right kv-align-middle' data-col-seq='11'  style='mso-number-format: &quot;\#\,\#\#0\.00&quot;;'><?= number_format($d->total_invoice - $d->total_tax, 2, ".", ",") ?></td>
            <td class='kv-align-right kv-align-middle' data-col-seq='11'  style='mso-number-format: &quot;\#\,\#\#0\.00&quot;;'><?= number_format($d->total_tax, 2, ".", ",") ?></td>            
            <td class='kv-align-right kv-align-middle' data-col-seq='11'  style='mso-number-format: &quot;\#\,\#\#0\.00&quot;;'><?= number_format($d->total_invoice, 2, ".", ",") ?></td>    
			      <td class='kv-align-center kv-align-middle' data-col-seq='2' style='mso-number-format: &quot;\@&quot;;'><?= UtilsConstants::getDocumentStatusSelectType($d->status); ?></td>            
          </tr>
	  <?php
	  $sum_total += $d->total_invoice;
	  $sum_subtotal += $d->total_invoice - $d->total_tax;
	  $sum_total_iva += $d->total_tax;	
  
	  $index++;
	endforeach;
	
	
	if (empty($datos)) :?>
		<tr><td colspan='11'><div class='empty'>No se encontraron resultados.</div></td></tr>	
	<?php
	endif;        
	?>      
    </tbody>
    <?php
	if (!empty($datos)) :?>
    <tfoot class='kv-page-summary-container'>
      <tr class='kv-page-summary warning'>
	    <td colspan="12" class='kv-align-right kv-align-middle'><strong>Total General</strong></td>
			<td class='kv-align-right kv-align-middle' data-col-seq='12' style='mso-number-format: &quot;\#\,\#\#0\.00&quot;;'><strong><?= number_format($sum_subtotal, 2, ".", ",") ?></strong></td>            
			<td class='kv-align-right kv-align-middle' data-col-seq='13' style='mso-number-format: &quot;\#\,\#\#0\.00&quot;;'><strong><?= number_format($sum_total_iva, 2, ".", ",") ?></strong></td>
			<td class='kv-align-right kv-align-middle' data-col-seq='14' style='mso-number-format: &quot;\#\,\#\#0\.00&quot;;'><strong><?= number_format($sum_total, 2, ".", ",") ?></strong></td>
      </tr>
    </tfoot>
	<?php
	endif;
	?>
  </table>
  </body>
</html> 