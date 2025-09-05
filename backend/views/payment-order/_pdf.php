<?php
use backend\models\nomenclators\ConditionSale;
use common\models\User;
use common\models\GlobalFunctions;
use backend\models\nomenclators\UtilsConstants;
use backend\models\business\PaymentOrder;

/* @var $orden backend\models\business\PaymentOrder */
/* @var $detalles backend\models\business\ItemPaymentOrder[] */

if ($moneda == 'COLONES')
	$simbolo = '¢';
else
	$simbolo = '$';

if ($original == true)
	$original = 'ORIGINAL';
else
	$original = 'COPIA';

$issuer = \backend\models\settings\Issuer::find()->one();
?>

<table width="100%">
	<tr>
		<td width="45%">
            <h4><?= $original ?></h4>
            <br>
        	<?= $logo ?>
        </td>
        <td width="5%">
        	&nbsp;
        </td>

        <td width="45%" align="right" valign="top">
            <table width="100%">
                <tr>
                    <td style="background-color: royalblue; color: white; padding: 5px;">
                        <h5>Orden de Compra: <?= $orden->number ?></h5>
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
            <span style="font-size: 13px;"><strong><?= $orden->supplier->name ?></strong></span><br />
            <span style="font-size: 12px;">Identificación: <?= $orden->supplier->identification ?></span><br />
            <span style="font-size: 12px;">Dirección: <?= wordwrap( $orden->supplier->address, 50 ) ?></span><br />
            <span style="font-size: 12px;">Teléfono: <?= $orden->supplier->phone ?></span><br />
            <span style="font-size: 12px;">Condición de venta: <?= $orden->conditionSale->name ?></span><br />
            <?php
                if($orden->condition_sale_id == ConditionSale::getIdCreditConditionSale())
                {
                    echo '<span style="font-size: 12px;">Plazo crédito:'.$orden->creditDays->name.'</span><br />';
                }
            ?>
        </td>
        <td width="5%">
            &nbsp;
        </td>
        <td width="45%" align="right" valign="top" style="background-color: #e5e7ea; padding: 5px;">

            <span style="font-size: 12px;"><strong><?= Yii::t('backend','Fecha de solicitud').': </strong>'.GlobalFunctions::formatDateToShowInSystem($orden->request_date) ?></span><br/>
            <span style="font-size: 12px;"><strong><?= Yii::t('backend','Estado').': </strong>'.UtilsConstants::getStatusPaymentOrderSelectMap($orden->status_payment_order_id) ?></span><br/>

            <span style="font-size: 12px;"><strong><?= Yii::t('backend','Elaborado por').': </strong>'.User::getFullNameByActiveUser() ?></span><br/>
            <span style="font-size: 12px;"><strong><?= Yii::t('backend','Fecha elaborado').': </strong>'.GlobalFunctions::formatDateToShowInSystem(date('Y-m-d')).' '.date('h:i a') ?></span><br/>
        </td>
    </tr>
</table>

<br />
<br />
<table class="table-bordered" border="0" cellpadding="5" cellspacing="0" width="100%">

    <tr style="background-color: #e5e7ea; padding: 5px;">
        <th width="14%" style="font-size: 10px; font-weight: bold; text-align:center"><?= Yii::t('backend','Cod. barras') ?></th>
        <th width="14%" style="font-size: 10px; font-weight: bold; text-align:center"><?= Yii::t('backend','Cod. proveedor') ?></th>
        <th width="10%" style="font-size: 10px; font-weight: bold; text-align:center"><?= Yii::t('backend','Cantidad') ?></th>
        <th width="40%" style="font-size: 10px; font-weight: bold; text-align:center"><?= Yii::t('backend','Descripción') ?></th>
        <th width="15%" style="font-size: 10px; font-weight: bold; text-align:center"><?= Yii::t('backend','Precio unitario') ?></th>
        <th width="15%" style="font-size: 10px; font-weight: bold; text-align:center"><?= Yii::t('backend','Importe') ?></th>
    </tr>

    <tbody>
    <!-- ITEMS HERE -->
    <?php

    foreach ($detalles as $index => $item)
    {
        ?>
        <tr>
            <td align="left">
                <span style="font-size: 10px; font-weight: normal;"><?= $item->product->bar_code; ?></span>
            </td>
            <td align="left">
                <span style="font-size: 10px; font-weight: normal;"><?= $item->product->supplier_code; ?></span>
            </td>
            <td align="left">
                <span style="font-size: 10px; font-weight: normal;"><?= GlobalFunctions::formatNumber($item->quantity,2) ?></span>
            </td>
            <td align="left">
                <span style="font-size: 10px; font-weight: normal;"><?= $item->description; ?></span>
            </td>
            <td align="right">
                <span style="font-size: 10px; font-weight: normal;">
                    <?php
                    $price_unit = $item->price_unit;
                    if ($orden->currency->symbol == 'CRC' && $moneda != 'COLONES')
                        $price_unit = $price_unit / $issuer->change_type_dollar;
                    else
                        if ($orden->currency->symbol == 'USD' && $moneda != 'DOLAR')
                            $price_unit = $price_unit * $issuer->change_type_dollar;
                    ?>
                    <?= $simbolo.' '.GlobalFunctions::formatNumber($price_unit, 2); ?>
                </span>
            </td>
            <td align="right">
                <span style="font-size: 10px; font-weight: normal;">
                    <?php
                    $subtotal = $item->subtotal;
                    if ($orden->currency->symbol == 'CRC' && $moneda != 'COLONES')
                        $subtotal = $subtotal / $issuer->change_type_dollar;
                    else
                        if ($orden->currency->symbol == 'USD' && $moneda != 'DOLAR')
                            $subtotal = $subtotal * $issuer->change_type_dollar;
                    ?>
                    <?= $simbolo.' '.GlobalFunctions::formatNumber($subtotal, 2); ?></span>
                </span>
            </td>

        </tr>
        <?php
    }

    $model = PaymentOrder::getResumePaymentOrder($orden->id);

    $total_subtotal = $model->subtotal;
    $total_tax = $model->tax_amount;
    $total_discount = $model->discount_amount + $model->exonerate_amount;
    $total_price = $model->price_total;

    if ($orden->currency->symbol == 'CRC' && $moneda != 'COLONES')
    {
        $total_subtotal = $model->subtotal / $issuer->change_type_dollar;
        $total_tax = $model->tax_amount / $issuer->change_type_dollar;
        $total_discount = ($model->discount_amount + $model->exonerate_amount) / $issuer->change_type_dollar;
        $total_price = $model->price_total / $issuer->change_type_dollar;
    }
    elseif ($orden->currency->symbol == 'USD' && $moneda != 'DOLAR')
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
            <span style="font-size: 12px;"><strong><?= Yii::t('backend','Observaciones') ?>: </strong></span><br/>

            <?= $orden->observations ?>
        </td>
        <td width="25%">
            &nbsp;
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
                    <td width="20%"><span style="font-size: 12px; font-weight: bold;">Total</span></td>
                    <td width="20%" align="right"><span style="font-size: 12px; font-weight: bold;"><?= $simbolo.' '.GlobalFunctions::formatNumber($total_price, 2) ?></span></td>
                </tr>
            </table>
        </td>
    </tr>
</table>