<?php

use common\models\User;
use common\models\GlobalFunctions;
use backend\models\business\Invoice;
use backend\models\business\SellerHasInvoice;
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
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <!-- <link rel="stylesheet" href="style.css"> -->
    <title><?= $invoice->key ?></title>
    <style type="text/css">
        body{
            margin-left: 0px;
            margin-right: 0px;
            margin-top: 0px;
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
<?php 
/*
<table width="100%">
    <tr>
        <td width="5%">

        </td>
        <td width="45%" style="text-align: center;">
            <?= $logo ?>
        </td>
        <td width="5%">

        </td>
    </tr>
</table>
*/
?>

<table width="100%">
    <tr>
        <td align="center" valign="top" style="font-weight: bold; font-size: 10px;">
            <?= (isset($issuer->name) && !empty($issuer->name)) ? $issuer->name : 'Herbavi' ?>
        </td>
    </tr>
</table>  

<table width="100%">
    <tr>
        <td align="center" valign="top" style="font-weight: bold; font-size: 10px;">
            <span>
                <?= Yii::t('backend', 'Ced') . ': ' ?>
                <?= $issuer->identification . ' '.wordwrap($issuer->address, 50). ' '. $issuer->phone. ' '. $issuer->email?>
            </span>
        </td>
    </tr>

</table>

<table width="100%">
    <tr>
        <td align="center" valign="top" style="font-weight: bold; font-size: 10px;">
            TIQUETE ELECTRÓNICO 
        </td>
    </tr>
</table>    

<table width="100%">
    <tr>
        <td align="left" valign="top" width="20%" style="font-weight: bold; font-size: 10px;">
            <span><?= Yii::t('backend', 'No') . ': ' ?></span>
        </td>
        <td align="left" valign="top" width="80%" style="font-weight: bold; font-size: 10px;">
            <span><?= $invoice->consecutive ?></span>
        </td>
    </tr>
    <tr>        
        <td align="left" valign="top" width="20%" style="font-weight: bold; font-size: 10px;">
            <span><?= Yii::t('backend', 'Clave numérica') . ': ' ?></span>
        </td>
        <td align="left" valign="top" width="80%" style="font-weight: bold; font-size: 10px;">
            <span><?= $invoice->key ?></span>
        </td>
    </tr>        
    <tr>
        <td align="left" valign="top" width="20%" style="font-weight: bold; font-size: 10px;">
            <span><?= Yii::t('backend', 'Fecha') . ': ' ?></span>
        </td>
        <td align="left" valign="top" width="80%" style="font-weight: bold; font-size: 10px;">
            <span><?= GlobalFunctions::formatDateToShowInSystem($invoice->emission_date) ?></span>
        </td>        
    </tr>
    <tr>
        <td align="left" valign="top" width="20%" style="font-weight: bold; font-size: 10px;">
            <span><?= Yii::t('backend', 'Cliente') . ': ' ?></span>
        </td>
        <td align="left" valign="top" width="80%" style="font-weight: bold; font-size: 10px;">
            <span><?= $invoice->customer->name ?></span>
        </td>  
    </tr>
</table>
<br />

<?php 
/*
<table width="100%">
    <tr>
        <th width="25%" style="text-align:left; border-bottom: 1px solid grey; font-size: 10px;"><?= Yii::t('backend', 'ITEM') ?></th>
        <th width="25%" style="text-align:left; border-bottom: 1px solid grey; font-size: 10px;"><?= Yii::t('backend', 'CANT') ?></th>
        <th width="25%" style="text-align:left; border-bottom: 1px solid grey; font-size: 10px;"><?= Yii::t('backend', 'PRECIO') ?></th>
        <th width="25%" style="text-align:left; border-bottom: 1px solid grey; font-size: 10px;"><?= Yii::t('backend', 'TOTAL') ?></th>
    </tr>

    <tbody>
        <!-- ITEMS HERE -->
        <?php
        $total_iva = 0;
        $total_iva_0 = 0;
        $total_iva_1 = 0;
        $total_iva_2 = 0;
        $total_iva_4 = 0;
        $total_iva_8 = 0;
        $total_iva_13 = 0;
        foreach ($items_invoice as $index => $item) {
            $subtotal = $item->getSubTotal();
            $iva = $item->getMontoImpuesto();

            $total_iva = $total_iva + $iva;

            if ((int)$item->tax_rate_percent == 0) {
                $total_iva_0 = $total_iva_0 + $iva;
            } else
            if ((int)$item->tax_rate_percent == 1) {
                $total_iva_1 = $total_iva_1 + $iva;
            } else
            if ((int)$item->tax_rate_percent == 2) {
                $total_iva_2 = $total_iva_2 + $iva;
            } else 
            if ((int)$item->tax_rate_percent == 4) {
                $total_iva_4 = $total_iva_4 + $iva;
            }else
            if ((int)$item->tax_rate_percent == 8) {
                $total_iva_8 = $total_iva_8 + $iva;
            }else
            if ((int)$item->tax_rate_percent == 13) {
                $total_iva_13 = $total_iva_13 + $iva;
            }  
        ?>
            <tr>
                <td align="left" style="margin-top: 5px; font-weight: bold; font-size: 10px;">
                    <?= $item->code; ?>
                </td>
                <td align="left" style="margin-top: 5px; font-weight: bold;font-size: 10px;">
                    <?php
                    $label_unit = 'Unid';

                    if (isset($item->unit_type_id) && !empty($item->unit_type_id)) {
                        $label_unit = $item->unitType->code;
                    }

                    ?>
                    <?= GlobalFunctions::formatNumber($item->quantity, 0) . ' ' . $label_unit  ?>
                </td>
                <td align="left" style="margin-top: 5px; font-weight: bold;font-size: 10px;">
                        <?php
                        $price_unit = $item->price_unit;
                        if ($invoice->currency->symbol == 'CRC' && $moneda != 'COLONES')
                            $price_unit = $price_unit / $issuer->change_type_dollar;
                        else
                        if ($invoice->currency->symbol == 'USD' && $moneda != 'DOLAR')
                            $price_unit = $price_unit * $issuer->change_type_dollar;
                        ?>
                        <?= GlobalFunctions::formatNumber($price_unit, 2); ?>
                </td>
                <td align="left" style="margin-top: 5px; font-weight: bold;font-size: 10px;">
                    <?php
                    $subtotal = $item->subtotal;
                    if ($invoice->currency->symbol == 'CRC' && $moneda != 'COLONES')
                        $subtotal = $subtotal / $issuer->change_type_dollar;
                    else
                    if ($invoice->currency->symbol == 'USD' && $moneda != 'DOLAR')
                        $subtotal = $subtotal * $issuer->change_type_dollar;
                    ?>
                    <?= GlobalFunctions::formatNumber($subtotal, 2); ?>
                </td>
            </tr>
            <tr align="left" style="font-size: 10px;">
                <?php
                $descripcion = $item->description . $item->getSimboloDescriptPercentIvaToApply();
                ?>
                <td colspan="12" style="border-bottom: 1px dotted grey;">
                    <span style="font-weight: bold; padding-bottom: 5px;font-size: 12px;"><?= $descripcion ?></span>
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

        if ($invoice->currency->symbol == 'CRC' && $moneda != 'COLONES') {
            $total_subtotal = $model->subtotal / $issuer->change_type_dollar;
            $total_tax = $model->tax_amount / $issuer->change_type_dollar;
            $total_discount = $model->discount_amount / $issuer->change_type_dollar;
            $total_exonerado = $model->exonerate_amount / $issuer->change_type_dollar;
            $total_price = $model->price_total / $issuer->change_type_dollar;
        } elseif ($invoice->currency->symbol == 'USD' && $moneda != 'DOLAR') {
            $total_subtotal = $model->subtotal * $issuer->change_type_dollar;
            $total_tax = $model->tax_amount * $issuer->change_type_dollar;
            $total_discount = $model->discount_amount * $issuer->change_type_dollar;
            $total_exonerado = $model->exonerate_amount * $issuer->change_type_dollar;
            $total_price = $model->price_total * $issuer->change_type_dollar;
        }
        ?>
    </tbody>
</table>
*/
?>

<table width="100%">
    <tr>
        <th width="15%" style="text-align:center; border-bottom: 1px solid grey; font-size: 8px;"><?= Yii::t('backend', 'CANT') ?></th>
        <th width="60%" style="text-align:center; border-bottom: 1px solid grey; font-size: 8px;"><?= Yii::t('backend', 'ITEM') ?></th>
        <th width="25%" style="text-align:center; border-bottom: 1px solid grey; font-size: 8px;"><?= Yii::t('backend', 'MONTO') ?></th>
    </tr>
    <tbody>
        <!-- ITEMS HERE -->
        <?php
        $total_iva = 0;
        $total_iva_0 = 0;
        $total_iva_1 = 0;
        $total_iva_2 = 0;
        $total_iva_4 = 0;
        $total_iva_8 = 0;
        $total_iva_13 = 0;
        foreach ($items_invoice as $index => $item) {
            $subtotal = $item->getSubTotal();
            $iva = $item->getMontoImpuesto();

            $total_iva = $total_iva + $iva;

            if ((int)$item->tax_rate_percent == 0) {
                $total_iva_0 = $total_iva_0 + $iva;
            } else
            if ((int)$item->tax_rate_percent == 1) {
                $total_iva_1 = $total_iva_1 + $iva;
            } else
            if ((int)$item->tax_rate_percent == 2) {
                $total_iva_2 = $total_iva_2 + $iva;
            } else 
            if ((int)$item->tax_rate_percent == 4) {
                $total_iva_4 = $total_iva_4 + $iva;
            }else
            if ((int)$item->tax_rate_percent == 8) {
                $total_iva_8 = $total_iva_8 + $iva;
            }else
            if ((int)$item->tax_rate_percent == 13) {
                $total_iva_13 = $total_iva_13 + $iva;
            }  
        ?>
            <tr>
                <td align="center" style="margin-top: 5px; font-weight: bold;font-size: 8px;">
                    <?= GlobalFunctions::formatNumber($item->quantity, 0) ?>
                </td>
                <td align="left" style="margin-top: 5px; font-weight: bold;font-size: 8px;">
                    <?php
                    $descripcion = $item->description . $item->getSimboloDescriptPercentIvaToApply();
                    ?>
                    <?= $descripcion ?>
                </td>
                <td align="right" style="margin-top: 5px; font-weight: bold;font-size: 8px; border-bottom: 1px dotted grey;">
                    <?php
                    $subtotal = $item->subtotal;
                    if ($invoice->currency->symbol == 'CRC' && $moneda != 'COLONES')
                        $subtotal = $subtotal / $issuer->change_type_dollar;
                    else
                    if ($invoice->currency->symbol == 'USD' && $moneda != 'DOLAR')
                        $subtotal = $subtotal * $issuer->change_type_dollar;
                    ?>
                    <?= GlobalFunctions::formatNumber($subtotal, 2); ?>
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

        if ($invoice->currency->symbol == 'CRC' && $moneda != 'COLONES') {
            $total_subtotal = $model->subtotal / $issuer->change_type_dollar;
            $total_tax = $model->tax_amount / $issuer->change_type_dollar;
            $total_discount = $model->discount_amount / $issuer->change_type_dollar;
            $total_exonerado = $model->exonerate_amount / $issuer->change_type_dollar;
            $total_price = $model->price_total / $issuer->change_type_dollar;
        } elseif ($invoice->currency->symbol == 'USD' && $moneda != 'DOLAR') {
            $total_subtotal = $model->subtotal * $issuer->change_type_dollar;
            $total_tax = $model->tax_amount * $issuer->change_type_dollar;
            $total_discount = $model->discount_amount * $issuer->change_type_dollar;
            $total_exonerado = $model->exonerate_amount * $issuer->change_type_dollar;
            $total_price = $model->price_total * $issuer->change_type_dollar;
        }
        ?>
    </tbody>
</table>


<table width="100%">
    <tr>
        <td align="right" width="20%"><span style="font-weight: bold; font-size: 9px;">TOTAL</span></td>
        <td width="20%" align="right"><span style="font-weight: bold; font-size: 9px;"><?= $simbolo . ' ' . GlobalFunctions::formatNumber($total_price, 2) ?></span></td>
    </tr>
    <?php
    if ($total_iva_13 > 0 || ($total_iva_1 == 0 && $total_iva_2 == 0)) : ?>
        <tr>
            <td align="right"><span style="font-size: 12px; font-weight: bold; font-size: 9px;">IVA</span></td>
            <td align="right"><span style="font-size: 12px; font-weight: bold; font-size: 9px;"><?= $simbolo . ' ' . GlobalFunctions::formatNumber($total_iva_13, 2) ?></span></td>
        </tr>
    <?php
    endif;
    ?>

    <?php
    if ($total_iva_1 > 0) : ?>
        <tr>
            <td align="right"><span style="font-size: 12px; font-weight: bold; font-size: 9px;">IVA 1% (*)</span></td>
            <td align="right"><span style="font-size: 12px; font-weight: bold; font-size: 9px;"><?= $simbolo . ' ' . GlobalFunctions::formatNumber($total_iva_1, 2) ?></span></td>
        </tr>
    <?php
    endif;
    ?>

    <?php
    if ($total_iva_2 > 0) : ?>
        <tr>
            <td align="right"><span style="font-size: 12px; font-weight: bold; font-size: 9px;">IVA 2% (#)</span></td>
            <td align="right"><span style="font-size: 12px; font-weight: bold; font-size: 9px;"><?= $simbolo . ' ' . GlobalFunctions::formatNumber($total_iva_2, 2) ?></span></td>
        </tr>
    <?php
    endif;
    ?>
</table>
<?php 
/*
<br>
<div style="text-align: center; font-weight: bold; font-size: 9px;">
    <?= Issuer::getValueByField('footer_one_receipt') ?>
</div>
<br>
<div style="clear:both; border: 1px solid;"></div>
<div style="text-align:left" class="border">
    <div style="width:30%; float:left; text-align:left;">
        <?= $img_qr ?>
    </div>
    <div style="margin-top: 15px; font-size: 9px;font-weight: bold;"> Clave Numérica <br /><?= $invoice->key ?></div>
</div>
*/
?>
<div style="clear:both;"></div>
<div style="width:100%; text-align:center;">
    <span style="font-size: 8px;font-weight: bold;">Autorizado mediante la resolución de facturación electrónica No DGT-R-0027-2024 del 13-11-2024. V4.4</span><br>
    <span style="font-size: 8px; font-weight: bold;">GRACIAS POR SU COMPRA</span><br/>
    <span style="font-size: 8px; font-weight: bold;">LE ESPERAMOS DE NUEVO</span>
</div>

</body>

</html>


