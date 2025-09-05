<?php

use backend\models\nomenclators\ConditionSale;
use common\models\GlobalFunctions;
use backend\models\business\PaymentOrder;

/* @var $model backend\models\business\PaymentOrder */
/* @var $reception_items backend\models\business\ReceptionItemPo[] */
/* @var $this yii\web\View */
$this->title = Yii::t('backend','RECEPCIÓN DE MERCANCÍAS');
$current_date = date('Y-m-d');
?>

<table width="100%" cellpading="10">
	<tr>
        <td width="15%" valign="middle">
            &nbsp;
        </td>
        <td width="70%" align="center" valign="middle">
            <h4><?= Yii::t('backend','RECEPCIÓN DE MERCANCÍAS') ?></h4>

        </td>
        <td width="15%" valign="middle">
            &nbsp;
        </td>
    </tr>
</table>
<br />
<br />
<br />
<table>
    <tr>
        <td>
            <span style="font-size: 13px;"><strong><?= Yii::t('backend','ORDEN DE COMPRA').': </strong>'.$model->number ?></span><br />
        </td>
    </tr>
    <tr>
        <td>
            <span style="font-size: 13px;"><strong><?= Yii::t('backend','PROVEEDOR').': </strong>'.$model->supplier->name ?></span><br />
        </td>
    </tr>
    <tr>
        <td>
            <span style="font-size: 13px;"><strong><?= Yii::t('backend','FECHA RECEPCIÓN').': </strong>'.GlobalFunctions::formatDateToShowInSystem($current_date) ?></span><br />
        </td>
    </tr>
</table>
<br />
<br />
<br />
<table class="table-bordered" border="0" cellpadding="5" cellspacing="0" width="100%">

		<tr style="background-color: #e5e7ea; padding: 5px;">
			<th width="5%" style="font-size: 10px; font-weight: bold; text-align:center"><?= Yii::t('backend','Línea') ?></th>
   			<th width="16%" style="font-size: 10px; font-weight: bold; text-align:center"><?= Yii::t('backend','Código barras') ?></th>
   			<th width="16%" style="font-size: 10px; font-weight: bold; text-align:center"><?= Yii::t('backend','Código proveedor') ?></th>
   			<th width="35%" style="font-size: 10px; font-weight: bold; text-align:center"><?= Yii::t('backend','Descripción') ?></th>
   			<th width="15%" style="font-size: 10px; font-weight: bold; text-align:center"><?= Yii::t('backend','Solicitado') ?></th>
            <th width="15%" style="font-size: 10px; font-weight: bold; text-align:center"><?= Yii::t('backend','Recibido') ?></th>
		</tr>

	<tbody>
	<!-- ITEMS HERE -->
	<?php

		foreach ($reception_items as $index => $item)
		{

			?>
			<tr>
				<td align="center"><span style="font-size: 10px; font-weight: normal;"><?= $index + 1 ?></span></td>
				<td align="left">
					<span style="font-size: 10px; font-weight: normal;"><?= $item->bar_code; ?></span>
				</td>
                <td align="left">
					<span style="font-size: 10px; font-weight: normal;"><?= $item->supplier_code; ?></span>
				</td>
                <td align="left">
					<span style="font-size: 10px; font-weight: normal;"><?= $item->description; ?></span>
                </td>
				<td align="right">
                    <span style="font-size: 10px; font-weight: normal;"><?= GlobalFunctions::formatNumber($item->quantity,2) ?></span>
                </td>
                <td align="right">
                    <span style="font-size: 10px; font-weight: normal;"></span>
                </td>
			</tr>
            <?php
		}
	?>
    </tbody>
</table>
<br>
<br>
<table width="100%" border="0" style="border:hidden" cellpadding="0">
<tr>
    <td width="20%" valign="center" align="center">
        <span>____________________________________</span>
        <br /><br />
        <span style="font-size: 12px; font-weight: bold;"><?= Yii::t('backend','Recibido por') ?></span>
    </td>
    <td width="60%">
        &nbsp;
    </td>
    <td width="20%" valign="center" align="center">
        <span>____________________________________</span>
        <br /><br />
        <span style="font-size: 12px; font-weight: bold;"><?= Yii::t('backend','Proveedor') ?></span>
    </td>
</tr>
</table>