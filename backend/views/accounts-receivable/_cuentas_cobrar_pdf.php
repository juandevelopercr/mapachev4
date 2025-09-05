<?php
use backend\models\business\Customer;
use backend\models\business\ItemInvoice;
use backend\models\business\InvoiceAbonos;
use common\models\GlobalFunctions;
?>
<table width="100%" cellpading="10">
	<tr>
		<td width="33%" valign="middle">
        	<?= $logo ?>
        </td>
        <td width="33%" align="center" valign="top">
            <br />
   			<span style="font-size: 13px;"><strong><?= $issuer->name ?></strong></span><br />
			<span style="font-size: 11px;">Identificación: <?= $issuer->identification ?></span><br /> 
			<span style="font-size: 11px;">Dirección: <?= wordwrap( $issuer->address, 50 ) ?></span><br />                           
			<span style="font-size: 11px;">Teléfono: <?= $issuer->phone ?></span><br />      
			<span style="font-size: 11px;">Correo: <?= $issuer->email ?></span><br />    			
        </td>
        <td align="right" style="padding-right:5;" width="33%">  
        	<br />
            <br />  
			<span style="font-weight: normal; font-size: 12px;">Fecha:</span><span style="font-size: 12px;"> <?= date('d-m-Y h:i a'); ?></span><br /><br />
        </td>
    </tr> 
</table>    

<table width="100%" cellpadding="2" cellspacing="2">
<tr>
    <td align="center">
        <span style="font-size: 14px; font-weight: bold;">ESTADO DE CUENTA</span>
    </td>
</tr>
</table>


<table width="100%">
    <tr>
        <td align="left" style="border-bottom:#06C solid 1px; border-top:#06C solid 1px;" colspan="4">
            <span style="font-size: 10px; font-weight: bold;">DATOS DEL CLIENTE</span>
        </td>
    </tr>
    <tr>
    	<td width="20%">
        	<span style="font-size: 10px; font-weight: normal;">Nombre:</span>
        </td>
    	<td width="50%" align="left">
        	<span style="font-size: 10px; font-weight: normal;"><?= $invoices[0]->customer->name ?></span>
        </td>
    	<td width="15%">
        	<span style="font-size: 10px; font-weight: normal;">Identificación:</span>
        </td>
    	<td width="15%" align="left">
        	<span style="font-size: 10px; font-weight: normal;"><?= $invoices[0]->customer->identification ?></span>
        </td>        
    </tr>
    <tr>
    	<td>
        	<span style="font-size: 10px; font-weight: normal;">Nombre Comercial:</span>
        </td>
    	<td align="left">
        	<span style="font-size: 10px; font-weight: normal;"><?= $invoices[0]->customer->commercial_name ?></span>
        </td>
    	<td>

        </td>
    	<td align="left">
        
        </td>        
    </tr>
    <tr>
    	<td>
        	<span style="font-size: 10px; font-weight: normal;">E-Mail:</span>
        </td>
    	<td align="left">
        	<span style="font-size: 10px; font-weight: normal;"><?= $invoices[0]->customer->email ?></span>
        </td>
    	<td>

        </td>
    	<td align="left">
        
        </td>        
    </tr>   
    <tr>
    	<td>
        	<span style="font-size: 10px; font-weight: normal;">Teléfono:</span>
        </td>
    	<td align="left">
        	<span style="font-size: 10px; font-weight: normal;"><?= $invoices[0]->customer->country_code_phone ?>-<?= $invoices[0]->customer->phone ?></span>
        </td>
    	<td>
			<span style="font-size: 10px; font-weight: normal;">Fax</span>
        </td>
    	<td align="left">
        	<span style="font-size: 10px; font-weight: normal;"><?= $invoices[0]->customer->country_code_fax ?>-<?= $invoices[0]->customer->fax ?></span>
        </td>        
    </tr>  
    <tr>
    	<td>
        	<span style="font-size: 10px; font-weight: normal;">Provincia:</span>
        </td>
    	<td align="left">
        	<span style="font-size: 10px; font-weight: normal;"><?= $invoices[0]->customer->province->name ?></span>
        </td>
    	<td>
			<span style="font-size: 10px; font-weight: normal;">Cantón</span>
        </td>
    	<td align="left">
        	<span style="font-size: 10px; font-weight: normal;"><?= $invoices[0]->customer->canton->name ?></span>
        </td>        
    </tr> 
    <tr>
    	<td>
        	<span style="font-size: 10px; font-weight: normal;">Distrito:</span>
        </td>
    	<td align="left">
        	<span style="font-size: 10px; font-weight: normal;"><?= $invoices[0]->customer->disctrict->name ?></span>
        </td>
    	<td>

        </td>
    	<td align="left">

        </td>        
    </tr>  
</table>    
<br />

<table class="table table-bordered" border="1" cellspacing="0" cellpadding="5" widht="100%">
	<thead>
		<tr>
            <th align="center" width="10%" style="font-size: 10px; font-weight: bold; text-align:center">
                No Factura
            </th>
            <th align="center" width="10%" style="font-size: 10px; font-weight: bold; text-align:center">
                Fecha Factura
            </th>
            <th align="center" width="10%" style="font-size: 10px; font-weight: bold; text-align:center">
                Fecha Tramite
            </th>
            <th align="center" width="10%" style="font-size: 10px; font-weight: bold; text-align:center">
                Dias Credito
            </th>
            <th align="center" width="10%" style="font-size: 10px; font-weight: bold; text-align:center">
                Días Vencidos
            </th>
            <th align="center" width="25%" style="font-size: 10px; font-weight: bold; text-align:center">
                Monto <?= $invoices[0]->currency->symbol; ?>
            </th>
        </tr>
	</thead>
	<tbody>
	<!-- ITEMS HERE -->

        <?php
        $total_CRC = 0;        
        $total_USD = 0;        
        $simbolo = '';
		$suma_abonos_CRC = 0;
		$suma_abonos_USD = 0;
		$pendiente_pago_CRC = 0;
		$pendiente_pago_USD = 0;		
        foreach ($invoices as $invoice){
			$total_factura_CRC = 0;			
			$total_factura_USD = 0;
			
            $emailcliente = $invoice->customer->email;
            $simbolo = $invoice->currency->symbol;
			if ($invoice->currency_id == 1)			
			{
            	$total_CRC += $invoice->total_comprobante;
	            $total_factura_CRC = $invoice->total_comprobante;				
			}
			else	
			{
	            $total_USD += $invoice->total_comprobante;
				$total_factura_USD = $invoice->total_comprobante;				
			}
            ?>
            <tr>
                <td align="center">
                    <span style="font-size: 10px; font-weight: normal;"><?= $invoice->consecutive ?></span>
                </td>
                <td align="center">
                    <span style="font-size: 10px; font-weight: normal;"><?= date('d-m-Y', strtotime($invoice->emission_date)) ?></span>
                </td>
                <td align="center">
                    <span style="font-size: 10px; font-weight: normal;"><?= date('d-m-Y') ?></span>
                </td>
                <td align="center">
                    <span style="font-size: 10px; font-weight: normal;"><?= $invoice->creditDays->name ?></span>
                </td>
                <td align="center">
                    <span style="font-size: 10px; font-weight: normal;"><?= $invoice->dias_vencidos ?></span>
                </td>
                <td align="right">
                    <span style="font-size: 10px; font-weight: normal;">
						<?= $invoice->currency->symbol . '&nbsp;' . GlobalFunctions::formatNumber($invoice->total_comprobante,2) ?>
                    </span>
                </td>
            </tr>    
            <?php
            $invoice_abonos = InvoiceAbonos::find()->where(['invoice_id'=>$invoice->id])->orderBy('emission_date ASC')->all();
            if (!empty($invoice_abonos)) : ?>
                <tr>
                    <th align="center" style="font-size: 10px; font-weight: bold; text-align:center">
                        Abonos
                    </th>
                    <th align="center" style="font-size: 10px; font-weight: bold; text-align:center">
                        Fecha
                    </th>
                    <th align="center" style="font-size: 10px; font-weight: bold; text-align:center">
                        Tipo
                    </th>
                    <th align="center" style="font-size: 10px; font-weight: bold; text-align:center">
                        Referencia
                    </th>
                    <th align="center" style="font-size: 10px; font-weight: bold; text-align:center">
                        Banco
                    </th>
                    <th align="center" style="font-size: 10px; font-weight: bold; text-align:center">
                        Monto <?= $invoice->currency->symbol; ?>
                    </th>
                </tr> 
                <?php
                $monto_abonos_CRC = 0;
                $monto_abonos_USD = 0;
                $index = 1;
                foreach ($invoice_abonos as $abono) : ?>
                <tr>
                    <td align="left">
                        <span style="font-size: 10px; font-weight: normal;"><?= $i; ?></span>
                    </td>
                    <td align="center">
                        <span style="font-size: 10px; font-weight: normal;"><?= date('d-m-Y', strtotime($abono->emission_date)) ?></span>
                    </td>
                    <td align="center">
                        <span style="font-size: 10px; font-weight: normal;"><?= $abono->paymentMethod->name ?></span>
                    </td>
                    <td align="center">
                        <span style="font-size: 10px; font-weight: normal;"><?= $abono->reference ?></span>
                    </td>
                    <td align="center">
                        <span style="font-size: 10px; font-weight: normal;"><?= $abono->bank->name ?></span>
                    </td>
                    <td align="right">
                        <span style="font-size: 10px; font-weight: normal;">
						<?php                        
							echo $invoice->currency->symbol . '&nbsp;' . GlobalFunctions::formatNumber($abono->amount, 2);
                            $monto_abonos_CRC += $abono->monto;			
						?>    
                        </span>
                    </td>
                </tr>  
                <?php
                $index++;
                endforeach;
                
                $suma_abonos_CRC += $monto_abonos_CRC;
                $suma_abonos_USD += $monto_abonos_USD;				
                $saldo_CRC = $total_factura_CRC - $monto_abonos_CRC;	
                $saldo_USD = $total_factura_USD - $monto_abonos_USD;									
                ?>
                <tr>
                    <th align="right" colspan="5" style="font-size: 10px; font-weight: bold; text-align:center">
                        TOTAL ABONO(S): <br />
                        PENDIENTE DE PAGO:
                    </th>
                    <th align="right" style="font-size: 10px; font-weight: bold; text-align:center">
                        <?= 'CRC' . '&nbsp;' . GlobalFunctions::formatNumber($monto_abonos_CRC, 2) ?><br />
                        <?= 'CRC' . '&nbsp;' . GlobalFunctions::formatNumber($saldo_CRC, 2) ?></span>
                    </th>
                </tr>  		                    
            <?php
            endif;
            ?>                                      
        <?php
        }
        ?>
        
        <tr>
            <td align="right" colspan="5" style="font-size: 10px; font-weight: bold; text-align:center">
                <strong>TOTAL FACTURA(S):</strong><br />
                <strong>TOTAL ABONO(S):</strong><br />             
                <strong><span style="color:#F00">TOTAL PENDIENTE:</span></strong><br />                    
            </td>
            <td align="right" style="font-size: 10px; font-weight: bold; text-align:center"> 	
                <?= 'CRC' . '&nbsp;' . GlobalFunctions::formatNumber($total_CRC, 2) ?><br />
                <?= 'CRC' . '&nbsp;' . GlobalFunctions::formatNumber($suma_abonos_CRC, 2) ?><br />
                <span style="color:#F00"><?= 'CRC' . '&nbsp;' . GlobalFunctions::formatNumber($total_CRC - $suma_abonos_CRC, 2) ?></span></strong>
            </td>
        </tr>   
		</tbody>                 
    </table>

    <table width="100%">
        <tr>
            <td align="center" style="font-size: 10px; font-weight: normal; text-align:center">
                <?php //$configuracion->piepagina_estado_cuenta; ?>
            </td>
        </tr>
    </table>