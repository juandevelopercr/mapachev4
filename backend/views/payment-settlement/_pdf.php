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

if ($original == true)
    $original = 'ORIGINAL';
else
    $original = 'COPIA';

$issuer = Issuer::find()->one();
?>

<table width="100%">
    <tr>
        <td width="45%">
            <h2><?= UtilsConstants::getPreInvoiceSelectType($invoice->invoice_type) ?></h2>
            <h4><?= $original ?></h4>

            <br>
            <?= $logo ?>
        </td>
        <td width="5%">

        </td>

        <td width="45%" align="right" valign="top">
            <table width="100%">
                <tr>
                    <td style="background-color: #168c18; color: white; padding: 5px;">
                        <h5>No. Factura: <?= $invoice->consecutive ?></h5>
                        <h5>Clave numérica: <?= $invoice->key ?></h5>
                    </td>
                </tr>
                <tr style="background-color: #e5e7ea; padding: 5px;">
                    <td style="padding: 5px;">
                        <span style="font-size: 13px;"><strong><?= !empty($issuer->name) ? $issuer->name : 'Herbavi' ?></strong></span><br />
                        <span style="font-size: 12px;">Identificación: <?= $issuer->identification ?></span><br />
                        <span style="font-size: 12px;">Dirección: <?= wordwrap( $issuer->address, 50 ) ?></span><br />
                        <span style="font-size: 12px;">Teléfono: <?= $issuer->phone ?></span><br />
                        <span style="font-size: 12px;">Correo: <?= $issuer->email ?></span><br />
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
<br>

<table width="100%">
    <tr>
        <td width="45%"  valign="top" style="background-color: #e5e7ea; padding: 5px;">
            <span style="font-size: 13px;"><strong><?= $invoice->customer->name ?></strong></span><br />
            <span style="font-size: 12px;">Identificación: <?= $invoice->customer->identification ?></span><br />
            <span style="font-size: 12px;">Dirección: <?= wordwrap( $invoice->customer->address, 50 ) ?></span><br />
            <span style="font-size: 12px;">Teléfono: <?= $invoice->customer->phone ?></span><br />
            <span style="font-size: 12px;">Correo: <?= $invoice->customer->email ?></span><br />
            <span style="font-size: 12px;">Condición de venta: <?= $invoice->conditionSale->name ?></span><br />
            <?php
            if($invoice->condition_sale_id == ConditionSale::getIdCreditConditionSale())
            {
                echo '<span style="font-size: 12px;">Plazo crédito:'.$invoice->creditDays->name.'</span><br />';
            }
            ?>
        </td>
        <td width="5%">

        </td>
        <td width="45%" align="right" valign="top" style="background-color: #e5e7ea; padding: 5px;">

            <span style="font-size: 12px;"><strong><?= Yii::t('backend','Fecha de solicitud').': </strong>'.GlobalFunctions::formatDateToShowInSystem($invoice->emission_date) ?></span><br/>
            <span style="font-size: 12px;"><strong><?= Yii::t('backend','Estado').': </strong>'.UtilsConstants::getInvoiceStatusSelectType($invoice->status) ?></span><br/>
            <span style="font-size: 12px;"><strong><?= Yii::t('backend','Sucursal').': </strong>'.$invoice->branchOffice->name ?></span><br/>
            <span style="font-size: 12px;"><strong><?= Yii::t('backend','Agente vendedor').': </strong>'.User::getFullNameByUserId($invoice->seller_id) ?></span><br/>
            <br/>
            <span style="font-size: 12px;"><strong><?= Yii::t('backend','Elaborado por').': </strong>'.User::getFullNameByActiveUser() ?></span><br/>
            <span style="font-size: 12px;"><strong><?= Yii::t('backend','Fecha elaborado').': </strong>'.GlobalFunctions::formatDateToShowInSystem(date('Y-m-d')).' '.date('h:i a') ?></span><br/>
        </td>
    </tr>
</table>

<br />

<table class="table-bordered" border="0" cellpadding="5" cellspacing="0" width="100%">

    <tr style="background-color: #e5e7ea; padding: 5px;">
        <th width="10%" style="font-size: 10px; font-weight: bold; text-align:center"><?= Yii::t('backend','Cod. barras') ?></th>
        <th width="6%" style="font-size: 10px; font-weight: bold; text-align:center"><?= Yii::t('backend','Cant.') ?></th>
        <th width="5%" style="font-size: 10px; font-weight: bold; text-align:center; vertical-align: middle;"><?= Yii::t('backend','UM') ?></th>
        <th width="30%" style="font-size: 10px; font-weight: bold; text-align:center"><?= Yii::t('backend','Descripción') ?></th>
        <th width="10%" style="font-size: 10px; font-weight: bold; text-align:center"><?= Yii::t('backend','Precio unitario') ?></th>
        <th width="13%" style="font-size: 10px; font-weight: bold; text-align:center"><?= Yii::t('backend','Descuento') ?></th>
        <th width="12%" style="font-size: 10px; font-weight: bold; text-align:center"><?= Yii::t('backend','Iva') ?></th>
        <th width="14%" style="font-size: 10px; font-weight: bold; text-align:center"><?= Yii::t('backend','Importe') ?></th>
    </tr>

    <tbody>
    <!-- ITEMS HERE -->
    <?php
    $total_quantity = 0;
    foreach ($items_invoice as $index => $item)
    {
        $total_quantity += $item->quantity;
        ?>
        <tr>
            <td align="left">
                <span style="font-size: 10px; font-weight: normal;"><?= $item->code; ?></span>
            </td>
            <td align="left">
                <span style="font-size: 10px; font-weight: normal;"><?= GlobalFunctions::formatNumber($item->quantity,2) ?></span>
            </td>

                <?php
                   if(isset($item->unit_type_id)){
                       echo '<td align="left">
                                <span style="font-size: 10px; font-weight: normal;">'. $item->unitType->code .'</span>
                            </td>';
                   }
                   else
                   {
                       echo '<td align="left">
                                <span style="font-size: 10px; font-weight: normal;"> </span>
                            </td>';
                   }
                ?>

            <td align="left">
                <span style="font-size: 10px; font-weight: normal;"><?= $item->description ?></span>
            </td>
            <td align="right">
                <span style="font-size: 10px; font-weight: normal;">
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
                <span style="font-size: 10px; font-weight: normal;">
                    <span style="font-size: 10px; font-weight: normal;">
                    <?php
                    $discount = $item->discount_amount;
                    $discount_percent = ($discount * 100) / ($item->price_unit * $item->quantity);

                    if ($invoice->currency->symbol == 'CRC' && $moneda != 'COLONES')
                        $discount = $discount / $issuer->change_type_dollar;
                    else
                        if ($invoice->currency->symbol == 'USD' && $moneda != 'DOLAR')
                            $discount = $discount * $issuer->change_type_dollar;
                    ?>
                    <?= $simbolo.' '.GlobalFunctions::formatNumber($discount, 2); ?><strong><?= ' ('. GlobalFunctions::formatNumber($discount_percent, 2) . '%)' ?></strong></span>                    
                </span>
            </td>
            <td align="right">
                <span style="font-size: 10px; font-weight: normal;">
                <span style="font-size: 10px; font-weight: normal;">
                    <span style="font-size: 10px; font-weight: normal;">
                    <?php
                    $taxt = $item->tax_amount;
                    if ($invoice->currency->symbol == 'CRC' && $moneda != 'COLONES')
                        $taxt = $taxt / $issuer->change_type_dollar;
                    else
                        if ($invoice->currency->symbol == 'USD' && $moneda != 'DOLAR')
                            $taxt = $taxt * $issuer->change_type_dollar;
                    ?>
                    <?= $simbolo.' '.GlobalFunctions::formatNumber($taxt, 2); ?></span>                    
                </span>                
            </td>
            <td align="right">
                <span style="font-size: 10px; font-weight: normal;">
                    <?php
                    $importe = $item->getMontoTotalLinea();
                    if ($invoice->currency->symbol == 'CRC' && $moneda != 'COLONES')
                        $importe = $importe / $issuer->change_type_dollar;
                    else
                    if ($invoice->currency->symbol == 'USD' && $moneda != 'DOLAR')
                        $importe = $importe * $issuer->change_type_dollar;
                    ?>
                    <?= $simbolo.' '.GlobalFunctions::formatNumber($importe, 2); ?></span>
                </span>
            </td>

        </tr>
        <?php
    }

    if (count($items_invoice) > 0) {
        ?>
        <tr>
            <td>
                <span style="font-size: 10px; font-weight: bold;">Total</span>
            </td>
            <td>
                <span style="font-size: 10px; font-weight: bold;"><?= GlobalFunctions::formatNumber($total_quantity,2) ?></span>
            </td>
            <td colspan="6">
                
            </td>
        </tr>
        <?php
    }    

    $model = Invoice::getResumeInvoice($invoice->id);

    $total_subtotal = $model->subtotal;
    $total_tax = $model->tax_amount;
    $total_discount = $model->discount_amount;
    $total_exonerado = $model->exonerate_amount;
    $total_price = $model->price_total;

    if ($invoice->currency->symbol == 'CRC' && $moneda != 'COLONES')
    {
        $total_subtotal = $model->subtotal / $issuer->change_type_dollar;
        $total_tax = $model->tax_amount / $issuer->change_type_dollar;
        $total_discount = $model->discount_amount / $issuer->change_type_dollar;
        $total_exonerado = $model->exonerate_amount / $issuer->change_type_dollar;
        $total_price = $model->price_total / $issuer->change_type_dollar;
    }
    elseif ($invoice->currency->symbol == 'USD' && $moneda != 'DOLAR')
    {
        $total_subtotal = $model->subtotal * $issuer->change_type_dollar;
        $total_tax = $model->tax_amount * $issuer->change_type_dollar;
        $total_discount = $model->discount_amount * $issuer->change_type_dollar;
        $total_exonerado = $model->exonerate_amount * $issuer->change_type_dollar;
        $total_price = $model->price_total * $issuer->change_type_dollar;
    }
    ?>
    </tbody>
</table>
<br>
<table width="100%">
    <tr>
        <td width="50%">
            <span style="font-size: 12px;"><strong><?= Yii::t('backend','Observaciones') ?>: </strong></span><br/>

            <?= $invoice->observations ?>
        </td>
        <td width="25%">

        </td>

        <td width="25%" align="right" valign="top" style="background-color: #e5e7ea; border-left: 3px solid royalblue;">
            <table border="0" cellspacing="0" cellpadding="5" width="100%">
                <tr>
                    <td><span style="font-size: 12px; font-weight: bold;">Descuento</span></td>
                    <td align="right"><span style="font-size: 12px; font-weight: bold;"><?= $simbolo.' '.GlobalFunctions::formatNumber($total_discount, 2) ?></span></td>
                </tr>                   
                <tr>
                    <td><span style="font-size: 12px; font-weight: bold;">Subtotal</span></td>
                    <td align="right"><span style="font-size: 12px; font-weight: bold;"><?= $simbolo.' '.GlobalFunctions::formatNumber($total_subtotal, 2) ?></span></td>
                </tr>             
                <tr>
                    <td><span style="font-size: 12px; font-weight: bold;">IVA</span></td>
                    <td align="right"><span style="font-size: 12px; font-weight: bold;"><?= $simbolo.' '.GlobalFunctions::formatNumber($total_tax, 2)?></span></td>
                </tr>
                <tr>
                    <td><span style="font-size: 12px; font-weight: bold;">Exonerado</span></td>
                    <td align="right"><span style="font-size: 12px; font-weight: bold;"><?= $simbolo.' '.GlobalFunctions::formatNumber($total_exonerado, 2)?></span></td>
                </tr>
                <tr>
                    <td width="20%"><span style="font-size: 12px; font-weight: bold;">Total</span></td>
                    <td width="20%" align="right"><span style="font-size: 12px; font-weight: bold;"><?= $simbolo.' '.GlobalFunctions::formatNumber($total_price, 2) ?></span></td>
                </tr>
            </table>
        </td>
    </tr>
</table>
<br>
<hr>
<table width="100%">
    <tr>
        <td>
            <span style="font-size: 12px;"><?= $textCuentas ?></span>
        </td>
    </tr>
</table>
<hr>
<table width="100%">
    <tr>
        <td width="10%" style="text-align: left;">
            <?= $img_qr ?>
        </td>

        <td width="90%" style="text-align: left;">
            <span>Autorizado mediante la resolución de facturación electrónica No DGT-R-0027-2024 del 13-11-2024. V4.4</span><br>
            <br><span><b>Factura Generada Por:</b> www.softwaresolutions.co.cr</span>
            <br><span><b>Teléfono:</b> 7272-2255</span>
        </td>
    </tr>
</table>
