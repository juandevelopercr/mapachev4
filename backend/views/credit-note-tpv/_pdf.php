<?php
use backend\models\nomenclators\ConditionSale;
use common\models\User;
use common\models\GlobalFunctions;
use backend\models\nomenclators\UtilsConstants;
use backend\models\business\CreditNote;
use backend\models\business\SellerHasCreditNote;
use backend\models\settings\Issuer;

/* @var $credit_note backend\models\business\CreditNote */
/* @var $items_credit_note backend\models\business\ItemCreditNote[] */

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
<table class="table-bordered" border="0" cellpadding="5" cellspacing="0" width="100%">

    <tr style="background-color: #e5e7ea; padding: 5px;">
        <th width="14%" style="font-size: 10px; font-weight: bold; text-align:center"><?= Yii::t('backend','Cod. barras') ?></th>
        <th width="10%" style="font-size: 10px; font-weight: bold; text-align:center"><?= Yii::t('backend','Cantidad') ?></th>
        <th width="8%" style="font-size: 10px; font-weight: bold; text-align:center"><?= Yii::t('backend','Unidad') ?></th>
        <th width="35%" style="font-size: 10px; font-weight: bold; text-align:center"><?= Yii::t('backend','Descripción') ?></th>
        <th width="15%" style="font-size: 10px; font-weight: bold; text-align:center"><?= Yii::t('backend','Precio unitario') ?></th>
        <th width="15%" style="font-size: 10px; font-weight: bold; text-align:center"><?= Yii::t('backend','Importe') ?></th>
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
    foreach ($items_credit_note as $index => $item)
    {
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
                    if ($credit_note->currency->symbol == 'CRC' && $moneda != 'COLONES')
                        $price_unit = $price_unit / $issuer->change_type_dollar;
                    else
                        if ($credit_note->currency->symbol == 'USD' && $moneda != 'DOLAR')
                            $price_unit = $price_unit * $issuer->change_type_dollar;
                    ?>
                    <?= $simbolo.' '.GlobalFunctions::formatNumber($price_unit, 2); ?>
                </span>
            </td>
            <td align="right">
                <span style="font-size: 10px; font-weight: normal;">
                    <?php
                    $subtotal = $item->subtotal;
                    if ($credit_note->currency->symbol == 'CRC' && $moneda != 'COLONES')
                        $subtotal = $subtotal / $issuer->change_type_dollar;
                    else
                        if ($credit_note->currency->symbol == 'USD' && $moneda != 'DOLAR')
                            $subtotal = $subtotal * $issuer->change_type_dollar;
                    ?>
                    <?= $simbolo.' '.GlobalFunctions::formatNumber($subtotal, 2); ?></span>
                </span>
            </td>

        </tr>
        <?php
    }

    $model = CreditNote::getResumeCreditNote($credit_note->id);

    $total_subtotal = $model->subtotal;
    $total_tax = $model->tax_amount;
    $total_discount = $model->discount_amount + $model->exonerate_amount;
    $total_price = $model->price_total;

    if ($credit_note->currency->symbol == 'CRC' && $moneda != 'COLONES')
    {
        $total_subtotal = $model->subtotal / $issuer->change_type_dollar;
        $total_tax = $model->tax_amount / $issuer->change_type_dollar;
        $total_discount = ($model->discount_amount + $model->exonerate_amount) / $issuer->change_type_dollar;
        $total_price = $model->price_total / $issuer->change_type_dollar;
    }
    elseif ($credit_note->currency->symbol == 'USD' && $moneda != 'DOLAR')
    {
        $total_subtotal = $model->subtotal * $issuer->change_type_dollar;
        $total_tax = $model->tax_amount * $issuer->change_type_dollar;
        $total_discount = ($model->discount_amount + $model->exonerate_amount) * $issuer->change_type_dollar;
        $total_price = $model->price_total * $issuer->change_type_dollar;
    }
    ?>
    </tbody>
</table>
<br>
<table width="100%">
    <tr>
        <td width="50%">
            <span style="font-size: 12px;"><strong><?= Yii::t('backend','Razón') ?>: </strong></span>
            <?= $credit_note->reference_reason ?>
            <br>
            <br>
            <span style="font-size: 12px;"><strong><?= Yii::t('backend','Observaciones') ?>: </strong></span>
            <?= $credit_note->observations ?>
        </td>
        <td width="25%">

        </td>

        <td width="25%" align="right" valign="top" style="background-color: #e5e7ea; border-left: 3px solid royalblue;">
            <table border="0" cellspacing="0" cellpadding="5" width="100%">
                <tr>
                    <td><span style="font-size: 12px; font-weight: bold;">Subtotal</span></td>
                    <td align="right"><span style="font-size: 12px; font-weight: bold;"><?= $simbolo.' '.GlobalFunctions::formatNumber($total_subtotal, 2) ?></span></td>
                </tr>
                <tr>
                    <td><span style="font-size: 12px; font-weight: bold;">Descuento</span></td>
                    <td align="right"><span style="font-size: 12px; font-weight: bold;"><?= $simbolo.' '.GlobalFunctions::formatNumber($total_discount, 2) ?></span></td>
                </tr>
                <tr>
                    <td><span style="font-size: 12px; font-weight: bold;">IVA</span></td>
                    <td align="right"><span style="font-size: 12px; font-weight: bold;"><?= $simbolo.' '.GlobalFunctions::formatNumber($total_tax, 2)?></span></td>
                </tr>
                <tr>
                    <td width="20%"><span style="font-size: 16px; font-weight: bold;"><strong>Total</strong></span></td>
                    <td width="20%" align="right"><span style="font-size: 16px; font-weight: bold;"><strong><?= $simbolo.' '.GlobalFunctions::formatNumber($total_price, 2) ?></strong></span></td>
                </tr>
            </table>


            <br />
            <br />

            <table border="0" cellspacing="0" cellpadding="5" width="100%" style="border-width: solid 1px;">
                <tr>
                    <th width="10%" style="background-color: royalblue; border: 1px solid royalblue;"><span style="font-size: 12px; font-weight: bold; float: left; color:#FFF">0 %</span></th>
                    <td width="40%" style="background-color: #e5e7ea; border: 1px solid royalblue;"><span style="font-size: 12px; font-weight: bold; float: right;"><?= $simbolo . ' ' . GlobalFunctions::formatNumber($total_iva_0, 2) ?></div></td>

                    <th width="10%" style="background-color: royalblue; border: 1px solid royalblue;"><span style="font-size: 12px; font-weight: bold; float: left; color:#FFF">4 %</span></th>
                    <td width="40%" style="background-color: #e5e7ea; border: 1px solid royalblue;"><span style="font-size: 12px; font-weight: bold; float: right;"><?= $simbolo . ' ' . GlobalFunctions::formatNumber($total_iva_4, 2) ?></div></td>
                </tr>
                <tr>
                    <th width="10%" style="background-color: royalblue; border: 1px solid royalblue;"><span style="font-size: 12px; font-weight: bold; float: left; color:#FFF">1 %</span></th>
                    <td width="40%" style="background-color: #e5e7ea; border: 1px solid royalblue;"><span style="font-size: 12px; font-weight: bold; float: right;"><?= $simbolo . ' ' . GlobalFunctions::formatNumber($total_iva_1, 2) ?></div></td>

                    <th width="10%" style="background-color: royalblue; border: 1px solid royalblue;"><span style="font-size: 12px; font-weight: bold; float: left; color:#FFF">8 %</span></th>
                    <td width="40%" style="background-color: #e5e7ea; border: 1px solid royalblue;"><span style="font-size: 12px; font-weight: bold; float: right;"><?= $simbolo . ' ' . GlobalFunctions::formatNumber($total_iva_8, 2) ?></div></td>
                </tr>
                <tr>
                    <th width="10%" style="background-color: royalblue; border: 1px solid royalblue;"><span style="font-size: 12px; font-weight: bold; float: left; color:#FFF">2 %</span></th>
                    <td width="40%" style="background-color: #e5e7ea; border: 1px solid royalblue;"><span style="font-size: 12px; font-weight: bold; float: right;"><?= $simbolo . ' ' . GlobalFunctions::formatNumber($total_iva_2, 2) ?></div></td>

                    <th width="10%" style="background-color: royalblue; border: 1px solid royalblue;"><span style="font-size: 12px; font-weight: bold; float: left; color:#FFF">13 %</span></th>
                    <td width="40%" style="background-color: #e5e7ea; border: 1px solid royalblue;"><span style="font-size: 12px; font-weight: bold; float: right;"><?= $simbolo . ' ' . GlobalFunctions::formatNumber($total_iva_13, 2) ?></div></td>
                </tr>                
            </table>              
        </td>
    </tr>
</table>
<br>
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
