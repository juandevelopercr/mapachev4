<?php

use common\models\User;
use common\models\GlobalFunctions;
use backend\models\nomenclators\UtilsConstants;
use backend\models\settings\Issuer;

/* @var $invoice backend\models\business\Invoice */
/* @var $items_invoice backend\models\business\ItemInvoice[] */

$issuer = Issuer::find()->one();
?>
<table width="100%">
    <tr>
        <td style="padding: 5px;" width="100%" align="center">
            <span style="font-size: 14px;"><?= $setting->cashier_report_header ?></span><br />
            <span style="font-size: 12px;"><strong>REPORTE DE CIERRE DE CAJA</strong></span><br /><br/><br/>
            <?php
            /*
            <span style="font-size: 12px;"><strong>Fecha Inicial: <?= date('d-m-Y', strtotime($cashRegister->opening_date)) ?></strong></span><br />
            <span style="font-size: 12px;"><strong>Fecha Final: <?= date('d-m-Y', strtotime($cashRegister->closing_date)) ?></strong></span><br />
            <span style="font-size: 12px;"><strong>Usuario: <?= !empty($issuer->name) ? $issuer->name : '-' ?></strong></span><br />
            <span style="font-size: 12px;"><strong>CAJA: <?= $cashRegister->box->name ?></strong></span><br />
            */
            ?>
        </td>
    </tr>
</table>

<table cellpadding="1" cellspacing="1" width="100%" class="table table-sm table-dark">
    <tbody>
        <tr>
            <td align="center" colspan="2">
                <span style="font-size: 10px; font-weight: normal;">Detalle de Caja: <?= $cashRegister->box->name ?></span>
            </td>
        </tr>
        <tr>
            <td align="left">
                <span style="font-size: 10px; font-weight: normal;">Usuario:</span>
            </td>
            <td align="right">
                <span style="font-size: 10px; font-weight: normal;"><?= $cashRegister->seller->name; ?></span>
            </td>
        </tr>
        <tr>
            <td align="left">
                <span style="font-size: 10px; font-weight: normal;">Apertura:</span>
            </td>
            <td align="right">
                <span style="font-size: 10px; font-weight: normal;"><?= date('d-m-Y', strtotime($cashRegister->opening_date)) . ' ' . date('h:i:s', strtotime($cashRegister->opening_time)); ?></span>
            </td>
        </tr>
        <tr>
            <td align="left">
                <span style="font-size: 10px; font-weight: normal;">Cierre:</span>
            </td>
            <td align="right">
            <span style="font-size: 10px; font-weight: normal;"><?= date('d-m-Y', strtotime($cashRegister->closing_date)) . ' ' . date('h:i:s', strtotime($cashRegister->closing_time)); ?></span>
            </td>
        </tr>
        <tr>
            <td align="left">
                <span style="font-size: 10px; font-weight: normal;">Efectivo de Apertura:</span>
            </td>
            <td align="right">
                <span style="font-size: 10px; font-weight: normal;"><?= GlobalFunctions::formatNumber($cashRegister->initial_amount, 2) ?></span>
            </td>
        </tr>
        <tr>
            <td align="left">
                <span style="font-size: 10px; font-weight: normal;">Entradas de efectivo:</span>
            </td>
            <td align="right">
                <span style="font-size: 10px; font-weight: normal;"><?= GlobalFunctions::formatNumber($monto_adicionado, 2) ?></span>
            </td>
        </tr>
        <tr>
            <td align="left">
                <span style="font-size: 10px; font-weight: normal;">Salidas de efectivo:</span>
            </td>
            <td align="right">
                <span style="font-size: 10px; font-weight: normal;"><?= GlobalFunctions::formatNumber($monto_retirado, 2) ?></span>
            </td>
        </tr>
        <tr>
            <td align="left">
                <span style="font-size: 10px; font-weight: normal;">Total de Ventas:</span>
            </td>
            <td align="right">
                <span style="font-size: 10px; font-weight: normal;"><?= GlobalFunctions::formatNumber($cashRegister->total_sales, 2) ?></span>
            </td>
        </tr>
        <tr>
            <td align="left">
                <span style="font-size: 10px; font-weight: normal;">Total a entregar:</span>
            </td>
            <td align="right">
                <span style="font-size: 10px; font-weight: normal;"><?= GlobalFunctions::formatNumber($cashRegister->initial_amount + $monto_adicionado - $monto_retirado + $cashRegister->total_sales, 2) ?></span>
            </td>
        </tr>
        <br />
        <br />

        <tr>
            <td align="center" colspan="2">
                <span style="font-size: 10px; font-weight: normal;">Detalle de ventas</span>
            </td>
        </tr>
        <!-- ITEMS HERE -->
        <tr>
            <td align="left" width="50%">
                <span style="font-size: 10px; font-weight: normal;">Efectivo</span>
            </td>
            <td align="right" width="50%">
                <span style="font-size: 10px; font-weight: normal;"><?= GlobalFunctions::formatNumber($pago_efectivo, 2) ?></span>
            </td>
        </tr>
        <tr>
            <td align="left" width="50%">
                <span style="font-size: 10px; font-weight: normal;">Tarjeta</span>
            </td>
            <td align="right" width="50%">
                <span style="font-size: 10px; font-weight: normal;"><?= GlobalFunctions::formatNumber($pago_tarjeta, 2) ?></span>
            </td>
        </tr>  
        <?php
        /*
        <tr>
            <td align="left" width="50%">
                <span style="font-size: 10px; font-weight: normal;">Cheques</span>
            </td>
            <td align="right" width="50%">
                <span style="font-size: 10px; font-weight: normal;"><?= GlobalFunctions::formatNumber($pago_cheques, 2) ?></span>
            </td>
        </tr> 
        <tr>
            <td align="left" width="50%">
                <span style="font-size: 10px; font-weight: normal;">Transferencia</span>
            </td>
            <td align="right" width="50%">
                <span style="font-size: 10px; font-weight: normal;"><?= GlobalFunctions::formatNumber($pago_transferencia, 2) ?></span>
            </td>
        </tr>
        */
        ?>                                
        <tr>
            <td align="right" colspan="3">
                <span style="font-size: 10px; font-weight: normal;"><strong>TOTAL VENTAS: <?= GlobalFunctions::formatNumber($pago_efectivo + $pago_tarjeta + $pago_cheques + $pago_transferencia, 2) ?></strong></span>
            </td>
        </tr>

        <br /><br />        
        <br /><br />
        <br />        
    <tfoot>
        <tr>
            <td align="center" colspan="2">
                <span style="font-size: 10px; font-weight: normal;"><strong>Firma del usuario</strong></span>
            </td>
        </tr>
    </tfoot>


    </tbody>
</table>