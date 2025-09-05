<?php

use backend\models\nomenclators\ConditionSale;
use common\models\User;
use common\models\GlobalFunctions;
use backend\models\nomenclators\UtilsConstants;
use backend\models\business\Invoice;
use backend\models\business\SellerHasInvoice;
use backend\models\settings\Issuer;

/* @var $invoice backend\models\business\Invoice */
/* @var $items_invoice backend\models\business\ItemInvoice[] */

if ($moneda == 'COLONES')
    $simbolo = '¢';
else
    $simbolo = '$';

if ($original === true)
    $original = 'ORIGINAL';
else
    $original = 'COPIA';

$issuer = Issuer::find()->one();
?>
<table width="100%">
    <tr>
        <td style="padding: 5px;" width="45%">
            <span style="font-size: 12px;"><strong><?= !empty($issuer->name) ? $issuer->name : 'Herbavi' ?></strong></span><br />
            <span style="font-size: 9px;">Identificación: <?= $issuer->identification ?></span><br />
            <span style="font-size: 9px;">Dirección: <?= wordwrap($issuer->address, 50) ?></span><br />
            <span style="font-size: 9px;">Teléfono: <?= $issuer->phone ?></span><br />
            <span style="font-size: 9px;">Correo: <?= $issuer->email ?></span><br />
        </td>
        <td width="10%" valign="top" style="padding-left: 5px; padding-right: 5px;"><?= $logo ?></td>
        <td width="45%" style="text-align: right;">
            <span style="font-size: 12px;"><strong><?= UtilsConstants::getPreInvoiceSelectType($invoice->invoice_type) ?> - <?= $original ?></strong></span><br />
            <span style="font-size: 16px;"><strong>No: <?= $invoice->consecutive ?></strong></span><br />
            <span style="font-size: 9px;"><strong>Clave numérica: </strong><?= $invoice->key ?></span><br />
            <span style="font-size: 9px;"><strong><?= GlobalFunctions::formatDateToShowInSystem($invoice->emission_date) ?></strong></span><br />
        </td>
    </tr>
</table>

<div style="padding: 5px; border: solid 1px #E3E3E3;">
    <table width="100%">
        <tr>
            <td width="50%">
                <span style="font-size: 12px;"><strong>CLIENTE: <?= $invoice->customer->name ?></strong></span><br />
                <span style="font-size: 9px;">Identificación: <?= $invoice->customer->identification ?></span><br />
                <span style="font-size: 9px;">Dirección: <?= wordwrap($invoice->customer->address, 50) ?></span><br />
                <span style="font-size: 9px;">Teléfono: <?= $invoice->customer->phone ?></span><br />
                <span style="font-size: 9px;">Correo: <?= $invoice->customer->email ?></span><br />
                <span style="font-size: 9px;">Condición de venta: <?= $invoice->conditionSale->name ?></span><br />
                <?php
                if ($invoice->condition_sale_id == ConditionSale::getIdCreditConditionSale()) {
                    echo '<span style="font-size: 9px;">Plazo crédito:' . $invoice->creditDays->name . '</span><br />';
                }
                ?>                
            </td>

            <td width="45%" align="right" style="padding: 5px;">

                <span style="font-size: 9px;"><strong><?= Yii::t('backend', 'Estado') . ': </strong>' . UtilsConstants::getInvoiceStatusSelectType($invoice->status) ?></span><br />
                <span style="font-size: 9px;"><strong><?= Yii::t('backend', 'Sucursal') . ': </strong>' . $invoice->branchOffice->name ?></span><br />
                <?php 
                /*
                <span style="font-size: 9px;"><strong><?= Yii::t('backend', 'Vendedor') . ': </strong>' . SellerHasInvoice::getSellerStringByInvoice($invoice->id) ?></span><br />
                */
                ?>
                <br />
                <span style="font-size: 9px;"><strong><?= Yii::t('backend', 'Elaborado por') . ': </strong>' . $invoice->user->name . ' '. $invoice->user->last_name ?></span><br />
                <span style="font-size: 9px;"><strong><?= Yii::t('backend', 'Fecha elaborado') . ': </strong>' . GlobalFunctions::formatDateToShowInSystem($invoice->emission_date) . ' ' . date('h:i a', strtotime($invoice->emission_date)) ?></span><br />
            </td>
        </tr>

        <tr>
            <td>
                <span style="font-size: 12px;"><strong>CONTRATO: <?= $invoice->contract ?></strong></span><br />
            </td>

            <td align="right" valign="top" style="padding: 5px;">
            </td>
        </tr>

    </table>
</div>