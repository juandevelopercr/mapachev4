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
                    <td style="background-color: royalblue; color: white; padding: 5px;">
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
            <span style="font-size: 12px;"><strong><?= Yii::t('backend','Agente vendedor').': </strong>'.SellerHasInvoice::getSellerStringByInvoice($invoice->id) ?></span><br/>
            <br/>
            <span style="font-size: 12px;"><strong><?= Yii::t('backend','Elaborado por').': </strong>'.User::getFullNameByActiveUser() ?></span><br/>
            <span style="font-size: 12px;"><strong><?= Yii::t('backend','Fecha elaborado').': </strong>'.GlobalFunctions::formatDateToShowInSystem(date('Y-m-d')).' '.date('h:i a') ?></span><br/>
        </td>
    </tr>
</table>