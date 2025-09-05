<?php
use backend\models\nomenclators\ConditionSale;
use common\models\User;
use common\models\GlobalFunctions;
use backend\models\nomenclators\UtilsConstants;
use backend\models\business\Invoice;
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
    <tr style="">
        <td style="padding: 5px;" width="45%">
            <span style="font-size: 12px;"><strong><?= !empty($issuer->name) ? $issuer->name : 'Herbavi' ?></strong></span><br />
            <span style="font-size: 10px;">Identificación: <?= $issuer->identification ?></span><br />
            <span style="font-size: 10px;">Dirección: <?= wordwrap( $issuer->address, 50 ) ?></span><br />
            <span style="font-size: 10px;">Teléfono: <?= $issuer->phone ?></span><br />
            <span style="font-size: 10px;">Correo: <?= $issuer->email ?></span><br />
        </td>
        <td width="10%" valign="top" style="padding-left: 5px; padding-right: 5px;"><?= $logo ?></td>
        <td width="45%" style="text-align: right;">
            <span style="font-size: 12px;"><strong><?= UtilsConstants::getPreInvoiceSelectType($invoice->invoice_type) ?> - <?= $original ?></strong></span><br />
            <span style="font-size: 12px;"><strong>No: <?= $invoice->consecutive ?></strong></span><br />
            <span style="font-size: 10px;"><strong>Clave numérica: </strong><?= $invoice->key ?></span><br />
            <span style="font-size: 10px;"><strong><?= GlobalFunctions::formatDateToShowInSystem($invoice->emission_date) ?></strong></span><br />
        </td>
    </tr>
</table>

<div style="padding: 5px; border: solid 1px #E3E3E3;">
    <table width="100%">
        <tr>
            <td width="5%" valign="top">
                <span style="font-size: 10px;"><strong>CLIENTE: </strong></span><br/>
            </td>
            <td width="45%"  valign="top" style="padding-top: 1px;">
                <span style="font-size: 10px;"><strong><?= $invoice->customer->name ?></strong></span><br />
                <span style="font-size: 10px;">Identificación: <?= $invoice->customer->identification ?></span><br />
                <span style="font-size: 10px;">Dirección: <?= wordwrap( $invoice->customer->address, 50 ) ?></span><br />
                <span style="font-size: 10px;">Teléfono: <?= $invoice->customer->phone ?></span><br />
                <span style="font-size: 10px;">Correo: <?= $invoice->customer->email ?></span><br />
                <span style="font-size: 10px;">Condición de venta: <?= $invoice->conditionSale->name ?></span><br />
                <?php
                if($invoice->condition_sale_id == ConditionSale::getIdCreditConditionSale())
                {
                    echo '<span style="font-size: 10px;">Plazo crédito:'.$invoice->creditDays->name.'</span><br />';
                }
                ?>
            </td>

            <td width="45%" align="right" valign="top" style="padding: 5px;">

                <span style="font-size: 10px;"><strong><?= Yii::t('backend','Estado').': </strong>'.UtilsConstants::getInvoiceStatusSelectType($invoice->status) ?></span><br/>
                <span style="font-size: 10px;"><strong><?= Yii::t('backend','Sucursal').': </strong>'.$invoice->branchOffice->name ?></span><br/>
                <span style="font-size: 10px;"><strong><?= Yii::t('backend','Vendedor').': </strong>'.User::getFullNameByUserId($invoice->seller_id) ?></span><br/>
                <br/>
                <span style="font-size: 10px;"><strong><?= Yii::t('backend','Elaborado por').': </strong>'.User::getFullNameByActiveUser() ?></span><br/>
                <span style="font-size: 10px;"><strong><?= Yii::t('backend','Fecha elaborado').': </strong>'.GlobalFunctions::formatDateToShowInSystem(date('Y-m-d')).' '.date('h:i a') ?></span><br/>
            </td>
        </tr>
    </table>
</div>

<table class="table-bordered" border="0" cellpadding="0" cellspacing="0" width="100%">

    <tr style="background-color: #e5e7ea; padding: 5px;">
        <th width="14%" style="font-size: 9px; font-weight: bold; text-align:center"><?= Yii::t('backend','Cod. barras') ?></th>
        <th width="10%" style="font-size: 9px; font-weight: bold; text-align:center"><?= Yii::t('backend','Cantidad') ?></th>
        <th width="8%" style="font-size: 9px; font-weight: bold; text-align:center"><?= Yii::t('backend','Unidad') ?></th>
        <th width="35%" style="font-size: 9px; font-weight: bold; text-align:center"><?= Yii::t('backend','Descripción') ?></th>
        <th width="15%" style="font-size: 9px; font-weight: bold; text-align:center"><?= Yii::t('backend','Precio unitario') ?></th>
        <th width="15%" style="font-size: 9px; font-weight: bold; text-align:center"><?= Yii::t('backend','Importe') ?></th>
    </tr>

    <tbody>
    <!-- ITEMS HERE -->
    <?php

    foreach ($items_invoice as $index => $item)
    {
        ?>
        <tr>
            <td align="left">
                <span style="font-size: 9px; font-weight: normal;"><?= $item->code; ?></span>
            </td>
            <td align="left">
                <span style="font-size: 9px; font-weight: normal;"><?= GlobalFunctions::formatNumber($item->quantity,2) ?></span>
            </td>

                <?php
                   if(isset($item->unit_type_id)){
                       echo '<td align="left">
                                <span style="font-size: 9px; font-weight: normal;">'. $item->unitType->code .'</span>
                            </td>';
                   }
                   else
                   {
                       echo '<td align="left">
                                <span style="font-size: 9px; font-weight: normal;"> </span>
                            </td>';
                   }
                ?>

            <td align="left">
                <span style="font-size: 9px; font-weight: normal;"><?= $item->description ?></span>
            </td>
            <td align="right">
                <span style="font-size: 9px; font-weight: normal;">
                    <?php
                    $price_unit = $item->price_unit;
                    if ($invoice->currency->symbol == 'CRC' && $moneda != 'COLONES')
                        $price_unit = $price_unit / $issuer->change_type_dollar;
                    else
                        if ($invoice->currency->symbol == 'USD' && $moneda != 'DOLAR')
                            $price_unit = $price_unit * $issuer->change_type_dollar;
                    ?>
                    <?= $simbolo.' '.GlobalFunctions::formatNumber($price_unit, 2); ?>
                </span>
            </td>
            <td align="right">
                <span style="font-size: 9px; font-weight: normal;">
                    <?php
                    $subtotal = $item->subtotal;
                    if ($invoice->currency->symbol == 'CRC' && $moneda != 'COLONES')
                        $subtotal = $subtotal / $issuer->change_type_dollar;
                    else
                        if ($invoice->currency->symbol == 'USD' && $moneda != 'DOLAR')
                            $subtotal = $subtotal * $issuer->change_type_dollar;
                    ?>
                    <?= $simbolo.' '.GlobalFunctions::formatNumber($subtotal, 2); ?></span>
                </span>
            </td>

        </tr>
        <?php
    }

    $model = Invoice::getResumeInvoice($invoice->id);

    $total_subtotal = $model->subtotal;
    $total_tax = $model->tax_amount;
    $total_discount = $model->discount_amount + $model->exonerate_amount;
    $total_price = $model->price_total;

    if ($invoice->currency->symbol == 'CRC' && $moneda != 'COLONES')
    {
        $total_subtotal = $model->subtotal / $issuer->change_type_dollar;
        $total_tax = $model->tax_amount / $issuer->change_type_dollar;
        $total_discount = ($model->discount_amount + $model->exonerate_amount) / $issuer->change_type_dollar;
        $total_price = $model->price_total / $issuer->change_type_dollar;
    }
    elseif ($invoice->currency->symbol == 'USD' && $moneda != 'DOLAR')
    {
        $total_subtotal = $model->subtotal * $issuer->change_type_dollar;
        $total_tax = $model->tax_amount * $issuer->change_type_dollar;
        $total_discount = ($model->discount_amount + $model->exonerate_amount) * $issuer->change_type_dollar;
        $total_price = $model->price_total * $issuer->change_type_dollar;
    }
    ?>
    </tbody>
</table>

<table width="100%">
    <tr>
            <td width="50%" style="text-align: justify;" >
            <span style="font-size: 10px; font-weight: normal;"><?= Issuer::getValueByField('electronic_invoice_footer') ?></span><br>
            <span style="font-size: 9px; font-weight: normal;"><strong><?= Issuer::getValueByField('footer_one_receipt') ?></strong></span>
        </td>

        <td width="48%" align="right" valign="top">
            <table border="0" cellspacing="0" cellpadding="5" width="100%">
                <tr>
                    <td><span style="font-size: 10px; font-weight: bold;">Subtotal</span></td>
                    <td align="right"><span style="font-size: 10px;font-weight: bold;"><?= $simbolo.' '.GlobalFunctions::formatNumber($total_subtotal, 2) ?></span></td>
                </tr>
                <tr>
                    <td><span style="font-size: 10px; font-weight: bold;">Descuento</span></td>
                    <td align="right"><span style="font-size: 10px; font-weight: bold;"><?= $simbolo.' '.GlobalFunctions::formatNumber($total_discount, 2) ?></span></td>
                </tr>
                <tr>
                    <td><span style="font-size: 10px; font-weight: bold;">IVA</span></td>
                    <td align="right"><span style="font-size: 10px; font-weight: bold;"><?= $simbolo.' '.GlobalFunctions::formatNumber($total_tax, 2)?></span></td>
                </tr>
                <tr>
                    <td width="20%"><span style="font-size: 10px; font-weight: bold;">Total</span></td>
                    <td width="20%" align="right"><span style="font-size: 10px; font-weight: bold;"><?= $simbolo.' '.GlobalFunctions::formatNumber($total_price, 2) ?></span></td>
                </tr>
            </table>
        </td>
    </tr>
</table>
<br>
<table width="100%">
    <tr>
        <td width="50%" style="border-top: 1px solid;">
            <span style="font-size: 9px; font-weight: normal;">RECIBIDO CONFORME (NOMBRE)</span>
        </td>
        <td width="25%" align="left" style="border-top: 1px solid;">
            <span style="font-size: 9px; font-weight: normal;">IDENTIFICACIÓN</span>
        </td>
        <td width="15%" style="border-top: 1px solid;">
            <span style="font-size: 9px; font-weight: bold;">FIRMA</span>
        </td>
    </tr>
</table>

<table width="100%">
    <tr>
        <td width="10%" style="text-align: left;">
            <?= $img_qr ?>
        </td>

        <td width="90%" style="text-align: left; ">
            <span style="font-size: 10px;">Autorizado mediante la resolución de facturación electrónica No DGT-R-0027-2024 del 13-11-2024. V4.4</span><br>
            <br><span style="font-size: 10px;"><b>Factura Generada Por:</b> www.softwaresolutions.co.cr</span>
            <br><span style="font-size: 10px;"style="font-size: 10px;"><b>Teléfono:</b> 7272-2255</span>
        </td>
    </tr>
</table>
