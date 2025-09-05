<?php
use common\models\User;
use common\models\GlobalFunctions;
use backend\models\nomenclators\UtilsConstants;
use backend\models\settings\Issuer;

/* @var $invoice backend\models\business\Invoice */
/* @var $items_invoice backend\models\business\ItemInvoice[] */

$issuer = Issuer::find()->one();
?>
<table width="100%">
    <tr>
        <td style="padding: 5px;" width="100%" align="center">
            <span style="font-size: 14px;"><?= $setting->cashier_report_header ?></span><br />
            ===================================<br />
            <span style="font-size: 12px;"><strong>APERTURA DE CAJA</strong></span><br /><br />
            <span style="font-size: 12px;"><strong>Usuario: <?= !empty($issuer->name) ? $issuer->name : '-' ?></strong></span><br />
            <span style="font-size: 12px;"><strong>Fecha: <?= date('d-m-Y', strtotime($cashRegister->opening_date)). ' '.date('h:i:s a', strtotime($cashRegister->opening_time)); ?></strong></span><br />
        </td>
    </tr>
</table>

<table cellpadding="1" cellspacing="1" width="100%" class="table">
    <tr style="border-bottom:1pt solid black;">
        <th width="6%" style="font-size: 10px; font-weight: bold; text-align:center;"><?= Yii::t('backend','CANT.') ?></th>
        <th width="30%" style="font-size: 10px; font-weight: bold; text-align:center"><?= Yii::t('backend','DESCRIPCION') ?></th>
        <th width="10%" style="font-size: 10px; font-weight: bold; text-align:center"><?= Yii::t('backend','IMPORTE') ?></th>        
    </tr>

    <tbody>
    <!-- ITEMS HERE -->
    <?php
    $total = 0;
    foreach ($movimiento_detail as $index => $item)
    {
        $total += $item->value * $item->count;
        ?>
        <tr>
            <td align="center">
                <span style="font-size: 10px; font-weight: normal;"><?= (int)$item->count; ?></span>
            </td>
            <td align="left">
                <span style="font-size: 10px; font-weight: normal;"><?= $item->comment ?></span>
            </td>
            <td align="right">
                <span style="font-size: 10px; font-weight: normal;"><?= GlobalFunctions::formatNumber($item->value * $item->count,2) ?></span>
            </td>
        </tr>
        <?php
    }
    ?>
    <tr>
        <td align="right" colspan="3">
            <span style="font-size: 10px; font-weight: normal;"><strong>TOTAL: <?= GlobalFunctions::formatNumber($total,2) ?></strong></span>
        </td>
    </tr>    
    <br /><br />        
    <br /><br />  
    <br />          
    <tfoot>
        <tr>
            <td align="center" colspan="3">
                <span style="font-size: 10px; font-weight: normal;"><strong>Firma del usuario</strong></span>
            </td>
        </tr>
    </tfoot>



    </tbody>
</table>
<p style="font-size: 10px; font-weight: normal; text-align: center;"><strong>Generado <?= date('d-m-Y h:i:s a')?></strong></p>
