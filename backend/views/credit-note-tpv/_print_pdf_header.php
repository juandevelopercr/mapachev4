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
            <span style="font-size: 9px;">Identificación: <?= $issuer->identification ?></span><br />
            <span style="font-size: 9px;">Dirección: <?= wordwrap( $issuer->address, 50 ) ?></span><br />
            <span style="font-size: 9px;">Teléfono: <?= $issuer->phone ?></span><br />
            <span style="font-size: 9px;">Correo: <?= $issuer->email ?></span><br />
        </td>
        <td width="10%" valign="top" style="padding-left: 5px; padding-right: 5px;"><?= $logo ?></td>
        <td width="45%" style="text-align: right;">
            <span style="font-size: 12px;"><strong>Nota de crédito - <?= $original ?></strong></span><br />
            <span style="font-size: 16px;"><strong>No: <?= $credit_note->consecutive ?></strong></span><br />
            <span style="font-size: 9px;"><strong>Clave numérica: </strong><?= $credit_note->key ?></span><br />
            <span style="font-size: 9px;"><strong><?= GlobalFunctions::formatDateToShowInSystem($credit_note->emission_date) ?></strong></span><br />
        </td>
    </tr>
</table>

<div style="padding: 5px; border: solid 1px #E3E3E3;">
    <table width="100%">
        <tr>
            <td width="5%" valign="top">
                <span style="font-size: 12px;"><strong>CLIENTE: </strong></span><br/>
            </td>
            <td width="45%"  valign="top" style="padding: 5px;">
                <span style="font-size: 12px;"><strong><?= $credit_note->customer->name ?></strong></span><br />
                <span style="font-size: 9px;">Identificación: <?= $credit_note->customer->identification ?></span><br />
                <span style="font-size: 9px;">Dirección: <?= wordwrap( $credit_note->customer->address, 50 ) ?></span><br />
                <span style="font-size: 9px;">Teléfono: <?= $credit_note->customer->phone ?></span><br />
                <span style="font-size: 9px;">Correo: <?= $credit_note->customer->email ?></span><br />
                <span style="font-size: 9px;">Condición de venta: <?= $credit_note->conditionSale->name ?></span><br />
                <?php
                if($credit_note->condition_sale_id == ConditionSale::getIdCreditConditionSale())
                {
                    echo '<span style="font-size: 9px;">Plazo crédito:'.$credit_note->creditDays->name.'</span><br />';
                }
                ?>
            </td>

            <td width="45%" align="right" valign="top" style="padding: 5px;">

                <span style="font-size: 9px;"><strong><?= Yii::t('backend','Estado').': </strong>'.UtilsConstants::getCreditNoteStatusSelectType($credit_note->status) ?></span><br/>
                <span style="font-size: 9px;"><strong><?= Yii::t('backend','Sucursal').': </strong>'.$credit_note->branchOffice->name ?></span><br/>
                <span style="font-size: 9px;"><strong><?= Yii::t('backend','Vendedor').': </strong>'.SellerHasCreditNote::getSellerStringByCreditNote($credit_note->id) ?></span><br/>
                <br/>
                <span style="font-size: 9px;"><strong><?= Yii::t('backend','Elaborado por').': </strong>'.User::getFullNameByActiveUser() ?></span><br/>
                <span style="font-size: 9px;"><strong><?= Yii::t('backend','Fecha elaborado').': </strong>'.GlobalFunctions::formatDateToShowInSystem(date('Y-m-d')).' '.date('h:i a') ?></span><br/>
            </td>
        </tr>
    </table>
</div>