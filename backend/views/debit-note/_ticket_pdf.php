<?php

use common\models\User;
use common\models\GlobalFunctions;
use backend\models\business\DebitNote;
use backend\models\business\SellerHasDebitNote;
use backend\models\settings\Issuer;

/* @var $debit_note backend\models\business\DebitNote */
/* @var $items_debit_note backend\models\business\ItemDebitNote[] */

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
        <td width="5%">

        </td>
        <td width="45%" style="text-align: center;">
            <?= $logo ?>
        </td>
        <td width="5%">

        </td>
    </tr>
</table>

<div style="text-align: center;  margin-top: 10px; font-weight: bold;">
    <?= (isset($issuer->name) && !empty($issuer->name)) ? $issuer->name : 'Herbavi' ?><br />
</div>

<table width="100%">
    <tr>
        <td style="text-align: center; font-weight: bold;">
            <span>Cédula jurídica: <?= $issuer->identification ?></span><br />
            <span style="text-align: center">Dirección: <?= wordwrap($issuer->address, 50) ?></span><br />
            <span>Teléfono: <?= $issuer->phone ?></span><br />
            <span><?= $issuer->email ?></span><br />
        </td>
    </tr>
</table>

<div style="text-align: center;  margin-top: 10px; font-weight: bold;">
    TIQUETE ELECTRÓNICO<br />
    No: <?= $debit_note->consecutive ?><br />
</div>
<span style="text-align: left; font-weight: bold;">Clave numérica: <br />
    <?= $debit_note->key ?></span><br />


<table width="100%">
    <tr>
        <td align="left" valign="top" style="font-weight: bold;">
            <span><?= Yii::t('backend', 'Fecha') . ': ' . GlobalFunctions::formatDateToShowInSystem($debit_note->emission_date) ?></span><br />
            <span><?= Yii::t('backend', 'Cliente') . ': ' . $debit_note->customer->name ?></span><br />
            <?php
            $commercial_name = (isset($debit_note->customer->commercial_name) && !empty($debit_note->customer->commercial_name)) ? $debit_note->customer->commercial_name : '';
            if ($commercial_name !== '') {
                echo '<span>' . $commercial_name . '</span><br>';
            }
            ?>
            <span><?= Yii::t('backend', 'Dirección') . ': ' . wordwrap($debit_note->customer->address, 50) ?></span><br />
            <span><?= Yii::t('backend', 'Vendedor') . ': ' . SellerHasDebitNote::getSellerStringByDebitNote($debit_note->id) ?></span><br />
        </td>
    </tr>
</table>

<br />

<table width="100%">

    <tr width="100%">
        <th width="25%" style="text-align:left; border-bottom: 1px solid grey;"><?= Yii::t('backend', 'ITEM') ?></th>
        <th width="25%" style="text-align:left; border-bottom: 1px solid grey;"><?= Yii::t('backend', 'CANT') ?></th>
        <th width="25%" style="text-align:left; border-bottom: 1px solid grey;"><?= Yii::t('backend', 'PRECIO') ?></th>
        <th width="25%" style="text-align:left; border-bottom: 1px solid grey;"><?= Yii::t('backend', 'TOTAL') ?></th>
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
        foreach ($items_debit_note as $index => $item) {
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
                <td align="left" style="margin-top: 5px; font-weight: bold;">
                    <span style="font-size: 12px;"><?= $item->code; ?></span>
                </td>
                <td align="left" style="margin-top: 5px; font-weight: bold;font-size: 12px;">
                    <?php
                    $label_unit = 'Unid';

                    if (isset($item->unit_type_id) && !empty($item->unit_type_id)) {
                        $label_unit = $item->unitType->code;
                    }

                    ?>
                    <span style="font-weight: bold;">
                        <?= GlobalFunctions::formatNumber($item->quantity, 0) . ' ' . $label_unit  ?>
                    </span>
                </td>
                <td align="left" style="margin-top: 5px; font-weight: bold;font-size: 12px;">
                    <span style="">
                        <?php
                        $price_unit = $item->price_unit;
                        if ($debit_note->currency->symbol == 'CRC' && $moneda != 'COLONES')
                            $price_unit = $price_unit / $issuer->change_type_dollar;
                        else
                        if ($debit_note->currency->symbol == 'USD' && $moneda != 'DOLAR')
                            $price_unit = $price_unit * $issuer->change_type_dollar;
                        ?>
                        <?= GlobalFunctions::formatNumber($price_unit, 2); ?>
                    </span>
                </td>
                <td align="left" style="margin-top: 5px; font-weight: bold;font-size: 12px;">
                    <span style="">
                        <?php
                        $subtotal = $item->subtotal;
                        if ($debit_note->currency->symbol == 'CRC' && $moneda != 'COLONES')
                            $subtotal = $subtotal / $issuer->change_type_dollar;
                        else
                        if ($debit_note->currency->symbol == 'USD' && $moneda != 'DOLAR')
                            $subtotal = $subtotal * $issuer->change_type_dollar;
                        ?>
                        <?= GlobalFunctions::formatNumber($subtotal, 2); ?></span>
                    </span>
                </td>
            </tr>
            <tr align="left" style="font-size: 12px;">
                <?php
                $descripcion = $item->description . $item->getSimboloDescriptPercentIvaToApply();
                ?>
                <td colspan="12" style="border-bottom: 1px dotted grey;"><span style="font-weight: bold; padding-bottom: 5px;font-size: 12px;"><?= $descripcion ?></span></td>
            </tr>
        <?php
        }

        $model = DebitNote::getResumeDebitNote($debit_note->id);

        $total_subtotal = $model->subtotal;
        $total_tax = $model->tax_amount;
        $total_discount = $model->discount_amount + $model->exonerate_amount;
        $total_price = $model->price_total;

        if ($debit_note->currency->symbol == 'CRC' && $moneda != 'COLONES') {
            $total_subtotal = $model->subtotal / $issuer->change_type_dollar;
            $total_tax = $model->tax_amount / $issuer->change_type_dollar;
            $total_discount = ($model->discount_amount + $model->exonerate_amount) / $issuer->change_type_dollar;
            $total_price = $model->price_total / $issuer->change_type_dollar;
        } elseif ($debit_note->currency->symbol == 'USD' && $moneda != 'DOLAR') {
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
        <td align="right"><span style="font-weight: bold;">Subtotal</span></td>
        <td align="right"><span style="font-weight: bold;"><?= $simbolo . ' ' . GlobalFunctions::formatNumber($total_subtotal, 2) ?></span></td>
    </tr>
    <tr>
        <td align="right"><span style="font-weight: bold;">Descuento</span></td>
        <td align="right"><span style="font-weight: bold;"><?= $simbolo . ' ' . GlobalFunctions::formatNumber($total_discount, 2) ?></span></td>
    </tr>
    <?php
    if ($total_iva_13 > 0 || ($total_iva_1 == 0 && $total_iva_2 == 0)) : ?>
        <tr>
            <td><span style="font-size: 12px; font-weight: bold;">IVA</span></td>
            <td align="right"><span style="font-size: 12px; font-weight: bold;"><?= $simbolo . ' ' . GlobalFunctions::formatNumber($total_iva_13, 2) ?></span></td>
        </tr>
    <?php
    endif;
    ?>

    <?php
    if ($total_iva_1 > 0) : ?>
        <tr>
            <td><span style="font-size: 12px; font-weight: bold;">IVA 1% (*)</span></td>
            <td align="right"><span style="font-size: 12px; font-weight: bold;"><?= $simbolo . ' ' . GlobalFunctions::formatNumber($total_iva_1, 2) ?></span></td>
        </tr>
    <?php
    endif;
    ?>

    <?php
    if ($total_iva_2 > 0) : ?>
        <tr>
            <td><span style="font-size: 12px; font-weight: bold;">IVA 2% (#)</span></td>
            <td align="right"><span style="font-size: 12px; font-weight: bold;"><?= $simbolo . ' ' . GlobalFunctions::formatNumber($total_iva_2, 2) ?></span></td>
        </tr>
    <?php
    endif;
    ?>
    <tr>
        <td align="right" width="20%"><span style="font-weight: bold;">Total</span></td>
        <td width="20%" align="right"><span style="font-weight: bold;"><?= $simbolo . ' ' . GlobalFunctions::formatNumber($total_price, 2) ?></span></td>
    </tr>
    <tr>
        <td align="right" width="20%"><span style="font-weight: bold;"></span></td>
        <td width="20%" align="right"><span style="font-weight: bold;"><br><?= $original ?></span></td>
    </tr>
</table>
<br>
<div style="text-align: center; font-weight: bold; font-size: 12px;">
    <?= Issuer::getValueByField('footer_one_receipt') ?>
</div>
<br>
<div style="clear:both; border: 1px solid;"></div>
<div style="text-align:left" class="border">
    <div style="width:30%; float:left; text-align:left;">
        <?= $img_qr ?>
    </div>
    <div style="margin-top: 15px; font-size: 12px;font-weight: bold;"> Clave Numérica <br /><?= $debit_note->key ?></div>
</div>
<div style="clear:both; border: 1px solid;"></div>
<div style="width:100%; text-align:center;" class="bordeTop">
    <span style="font-size: 10px;font-weight: bold;" class="">Autorizado mediante la resolución de facturación electrónica No DGT-R-0027-2024 del 13-11-2024. V4.4</span><br>
    <span style="font-size: 10px; font-weight: bold;">Factura Generada Por: www.softwaresolutions.co.cr</span>
    <span style="font-size: 10px; font-weight: bold;">Teléfono: 7272-2255</span>
</div>