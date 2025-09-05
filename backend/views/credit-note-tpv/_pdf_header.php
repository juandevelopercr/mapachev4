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
<table width="100%">
    <tr>
        <td width="45%">
            <h2>Nota de crédito</h2>
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
                        <h5>No.: <?= $credit_note->consecutive ?></h5>
                        <h5>Clave numérica: <?= $credit_note->key ?></h5>
                        <h5>Número de referencia: <?= $credit_note->reference_number ?></h5>
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
            <span style="font-size: 13px;"><strong><?= $credit_note->customer->name ?></strong></span><br />
            <span style="font-size: 12px;">Identificación: <?= $credit_note->customer->identification ?></span><br />
            <span style="font-size: 12px;">Dirección: <?= wordwrap( $credit_note->customer->address, 50 ) ?></span><br />
            <span style="font-size: 12px;">Teléfono: <?= $credit_note->customer->phone ?></span><br />
            <span style="font-size: 12px;">Correo: <?= $credit_note->customer->email ?></span><br />
            <span style="font-size: 12px;">Condición de venta: <?= $credit_note->conditionSale->name ?></span><br />
            <?php
            if($credit_note->condition_sale_id == ConditionSale::getIdCreditConditionSale())
            {
                echo '<span style="font-size: 12px;">Plazo crédito:'.$credit_note->creditDays->name.'</span><br />';
            }
            ?>
        </td>
        <td width="5%">

        </td>
        <td width="45%" align="right" valign="top" style="background-color: #e5e7ea; padding: 5px;">

            <span style="font-size: 12px;"><strong><?= Yii::t('backend','Fecha de solicitud').': </strong>'.GlobalFunctions::formatDateToShowInSystem($credit_note->emission_date) ?></span><br/>
            <span style="font-size: 12px;"><strong><?= Yii::t('backend','Estado').': </strong>'.UtilsConstants::getCreditNoteStatusSelectType($credit_note->status) ?></span><br/>
            <span style="font-size: 12px;"><strong><?= Yii::t('backend','Sucursal').': </strong>'.$credit_note->branchOffice->name ?></span><br/>
            <span style="font-size: 12px;"><strong><?= Yii::t('backend','Agente vendedor').': </strong>'.SellerHasCreditNote::getSellerStringByCreditNote($credit_note->id) ?></span><br/>
            <br/>
            <span style="font-size: 12px;"><strong><?= Yii::t('backend','Elaborado por').': </strong>'.User::getFullNameByActiveUser() ?></span><br/>
            <span style="font-size: 12px;"><strong><?= Yii::t('backend','Fecha elaborado').': </strong>'.GlobalFunctions::formatDateToShowInSystem(date('Y-m-d')).' '.date('h:i a') ?></span><br/>
        </td>
    </tr>
</table>