<?php

use backend\models\settings\Issuer;

$simbolo = '¢';
$issuer = Issuer::find()->one();

?>
<p style="text-align:center"><strong>COMPROBANTE PROVISIONAL</strong></p>

<table width="100%" cellpading="10" cellpadding="10">
	<tr>
		<td width="15%" valign="top">
        	<?= $logo ?>
        </td>
        <td width="40%" align="left" valign="top">
			<span style="font-size: 11px;">Identificación: <?= $issuer->identification ?></span><br />
			<span style="font-size: 11px; margin-top:15px">Dirección: <?= wordwrap( $issuer->address, 50 ) ?></span><br />
			<span style="font-size: 11px; margin-top:15px">Teléfono: <?= $issuer->phone ?></span><br />
			<span style="font-size: 11px; margin-top:15px">Correo: <?= $issuer->email ?></span><br />
        </td>
        <td align="left" style="padding-right:5px;" width="45%">
            <span style="font-weight: bold; font-size: 12px;">Comprobante Provisional No. ___________ </span><br />
			<span style="font-weight: normal; font-size: 12px; margin-top:15px">[x]Factura [ ]Nota Crédito [ ]Nota Débito</span><br />
			<span style="font-weight: normal; font-size: 12px; margin-top:15px">Fecha de Creación: </span>___/____/_______<br />
			<span style="font-weight: normal; font-size: 12px; margin-top:15px">Fecha de Vencimiento: </span>___/____/_______<br />            
        </td>
    </tr>
</table>    
<br />
<table width="100%">
    <tr>
        <td align="left" style="border-top:#000 solid 1px;" colspan="3">
            
        </td>
    </tr>
    <tr>
        <td align="left" colspan="3">
            
        </td>
    </tr>
    <tr>
    	<td width="40%">
        	<span style="font-size: 10px; font-weight: normal;">Cliente: ___________________________________________</span>
        </td>
    	<td width="30%" align="left">
        	<span style="font-size: 10px; font-weight: normal;">Contacto: ______________________________</span>
        </td>
    	<td width="30%">
        	<span style="font-size: 10px; font-weight: bold;">Información de Pago:</span>
        </td>       
    </tr>
    
    
    <tr>
    	<td>
        	<span style="font-size: 10px; font-weight: normal;">Número de Identificación: _________________________</span>
        </td>
    	<td align="left">
        	<span style="font-size: 10px; font-weight: normal;">Teléfono: ______________________________</span>
        </td>
    	<td>
        	<span style="font-size: 10px; font-weight: bold;">Condición de Venta:</span>
        </td>       
    </tr>   
    
    <tr>
    	<td>
        	<span style="font-size: 10px; font-weight: normal;">Dirección: _______________________________________</span>
        </td>
    	<td align="left">
        	<span style="font-size: 10px; font-weight: normal;">E-mail: _________________________________</span>
        </td>
    	<td>
        	<span style="font-size: 10px; font-weight: bold;">[ ]Contado [ ]Crédito</span>
        </td>       
    </tr>
    
    <tr>
    	<td>
        	<span style="font-size: 10px; font-weight: normal;">Teléfono: ________________________________________</span>
        </td>
    	<td align="left">
        	<span style="font-size: 10px; font-weight: normal;"></span>
        </td>
    	<td>
        	<span style="font-size: 10px; font-weight: bold;">Código Moneda:______________</span>
        </td>       
    </tr>            
    
    <tr>
    	<td>
        	<span style="font-size: 10px; font-weight: normal;">E-mail: _________________________________________</span>
        </td>
    	<td align="left">
        	<span style="font-size: 10px; font-weight: normal;"></span>
        </td>
    	<td>
        	<span style="font-size: 10px; font-weight: bold;">Tipo de Cambio: <?= $simbolo ?> ____________</span>
        </td>       
    </tr>            
    
</table>    


<br />
<table class="table table-bordered" border="1" cellspacing="0" cellpadding="0">
	<thead>
		<tr>
			<td width="5%" style="font-size: 10px; font-family:Verdana, Geneva, sans-serif; font-weight: bold; text-align:center">No.</td>
   			<td width="25%" style="font-size: 10px; font-family:Verdana, Geneva, sans-serif; font-weight: bold; text-align:center">COD / PROD / SERVICIO</td>
   			<td width="10%" style="font-size: 10px; font-family:Verdana, Geneva, sans-serif; font-weight: bold; text-align:center">CANT</td> 
  			<td width="15%" style="font-size: 10px; font-family:Verdana, Geneva, sans-serif; font-weight: bold; text-align:center">PRECIO</td>                 
   			<td width="15%" style="font-size: 10px; font-family:Verdana, Geneva, sans-serif; font-weight: bold; text-align:center">DESCUENTO</td>   
   			<td width="15%" style="font-size: 10px; font-family:Verdana, Geneva, sans-serif; font-weight: bold; text-align:center">IMPUESTO</td>                       
			<td width="15%" style="font-size: 10px; font-family:Verdana, Geneva, sans-serif; font-weight: bold; text-align:center">SUBTOTAL</td>
		</tr>
	</thead>
	<tbody>
	<!-- ITEMS HERE -->
	<?php
	$i = 0;
	while ($i < 13)
	{
		?>
		<tr>
			<td align="center"><span style="font-size: 10px; font-weight: normal;"><?= $i + 1 ?></span></td>
			<td>&nbsp;</td>                    			
			<td>&nbsp;</td>    				
			<td>&nbsp;</td>                    			
			<td>&nbsp;</td>    				
			<td>&nbsp;</td>                    			
			<td>&nbsp;</td>    				
		</tr>            
		<?php
		$i++;			
	}
	?>
    </tbody>
</table>    

<table class="table table-bordered" border="1" cellspacing="0" cellpadding="5" width="100%">
  <tr>
    <td rowspan="4" valign="top" width="60%">Observaciones</td>
    <td><span style="font-size: 10px; font-weight: bold;" width="20%">Subtotal</span></td>
    <td align="right" width="20%">&nbsp;</td>
  </tr>
  <tr>
    <td><span style="font-size: 10px; font-weight: bold;">Descuento</span></td>
    <td align="right">&nbsp;</td>
  </tr>
  <tr> 
    <td><span style="font-size: 10px; font-weight: bold;">Impuesto</span></td>
    <td align="right">&nbsp;</td>
  </tr>
  <tr>
    <td><span style="font-size: 10px; font-weight: bold;">Total</span></td>
    <td align="right">&nbsp;</td>
  </tr>
</table>

<table class="table" border="0" cellspacing="0" cellpadding="0">
  <tr>
  	<td colspan="2" align="center">
	    <span style="font-size: 10px; font-weight: bold;">"Documento emitido conforme lo establecido en la resolución de Factura Electrónica, Nº DGT-R-48-2016 del siete de octubre de dos mil dieciséis de la Dirección General de Tributación."</span><br />
	    <span style="font-size: 10px; font-weight: bold;">Este comprobante no puede ser utilizado para fines tributarios, por lo cual no se permitirá su uso para respaldo de créditos o gastos</span>        
    </td>
  </tr>
</table>
