<?php
use backend\models\nomenclators\ConditionSale;
use common\models\User;
use common\models\GlobalFunctions;
use backend\models\nomenclators\UtilsConstants;
use backend\models\business\DebitNote;
use backend\models\business\SellerHasDebitNote;
use backend\models\settings\Issuer;

/* @var $debit_note backend\models\business\DebitNote */
/* @var $items_debit_note backend\models\business\ItemDebitNote[] */

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
            <span style="font-size: 9px;">Dirección: <?= wordwrap( $issuer->address, 50 ) ?></span><br />
            <span style="font-size: 9px;">Teléfono: <?= $issuer->phone ?></span><br />
            <span style="font-size: 9px;">Correo: <?= $issuer->email ?></span><br />
        </td>
        <td width="10%" valign="top" style="padding-left: 5px; padding-right: 5px;"><?= $logo ?></td>
        <td width="45%" style="text-align: right;">
            <span style="font-size: 12px;"><strong>Nota de débito - <?= $original ?></strong></span><br />
            <span style="font-size: 16px;"><strong>No: <?= $debit_note->consecutive ?></strong></span><br />
            <span style="font-size: 9px;"><strong>Clave numérica: </strong><?= $debit_note->key ?></span><br />
            <span style="font-size: 9px;"><strong><?= GlobalFunctions::formatDateToShowInSystem($debit_note->emission_date) ?></strong></span><br />
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
                <span style="font-size: 12px;"><strong><?= $debit_note->customer->name ?></strong></span><br />
                <span style="font-size: 9px;">Identificación: <?= $debit_note->customer->identification ?></span><br />
                <span style="font-size: 9px;">Dirección: <?= wordwrap( $debit_note->customer->address, 50 ) ?></span><br />
                <span style="font-size: 9px;">Teléfono: <?= $debit_note->customer->phone ?></span><br />
                <span style="font-size: 9px;">Correo: <?= $debit_note->customer->email ?></span><br />
                <span style="font-size: 9px;">Condición de venta: <?= $debit_note->conditionSale->name ?></span><br />
                <?php
                if($debit_note->condition_sale_id == ConditionSale::getIdCreditConditionSale())
                {
                    echo '<span style="font-size: 9px;">Plazo débito:'.$debit_note->creditDays->name.'</span><br />';
                }
                ?>
            </td>

            <td width="45%" align="right" valign="top" style="padding: 5px;">

                <span style="font-size: 9px;"><strong><?= Yii::t('backend','Estado').': </strong>'.UtilsConstants::getDebitNoteStatusSelectType($debit_note->status) ?></span><br/>
                <span style="font-size: 9px;"><strong><?= Yii::t('backend','Sucursal').': </strong>'.$debit_note->branchOffice->name ?></span><br/>
                <span style="font-size: 9px;"><strong><?= Yii::t('backend','Vendedor').': </strong>'.SellerHasDebitNote::getSellerStringByDebitNote($debit_note->id) ?></span><br/>
                <br/>
                <span style="font-size: 9px;"><strong><?= Yii::t('backend','Elaborado por').': </strong>'.User::getFullNameByActiveUser() ?></span><br/>
                <span style="font-size: 9px;"><strong><?= Yii::t('backend','Fecha elaborado').': </strong>'.GlobalFunctions::formatDateToShowInSystem(date('Y-m-d')).' '.date('h:i a') ?></span><br/>
            </td>
        </tr>
    </table>
</div>