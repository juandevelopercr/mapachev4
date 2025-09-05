<?php

use common\models\GlobalFunctions;
use yii\helpers\ArrayHelper;
use backend\models\business\ProductHasBranchOffice;
use backend\models\business\Product;

/* @var $items array */
/* @var $this yii\web\View */
$this->title = Yii::t('backend','REPORTE DE PREPARACIÓN DE MERCANCÍAS');
$current_date = date('Y-m-d');

?>

<table width="100%" cellpading="10">
    <tr>
        <td width="15%" valign="middle">
            &nbsp;
        </td>
        <td width="70%" align="center" valign="middle">
            <h4><?= $this->title ?></h4>

        </td>
        <td width="15%" valign="middle">
            &nbsp;
        </td>
    </tr>
</table>
<br />
<br />

<?php

$product_quantities = [];
$product_existences = [];
$product_descriptions = ArrayHelper::map($items,'product_id','description');
$products_id = array_keys($product_quantities);

foreach ($items as $index => $item)
{
    $quantity_to_check_origin = $quantity_to_check = (int) $item['quantity'];

    $quantity_label = '';
    if(isset($item['unit_type']))
    {
        $unit_type_code = $item['unit_type'];

        if(isset($item['quantity_by_box']) && ($unit_type_code === 'CAJ' || $unit_type_code === 'CJ'))
        {
            $quantity_label = ' [1x'.$item['quantity_by_box'].']';
            $quantity_to_check *= $item['quantity_by_box'];
        }
        elseif(isset($item['package_quantity']) && ($unit_type_code === 'BULT' || $unit_type_code === 'PAQ'))
        {
            $quantity_label = ' [1x'.$item['package_quantity'].']';
            $quantity_to_check *= $item['package_quantity'];
        }
    }
    else
    {
        $unit_type_code = 'Unid';
    }

    if(isset($product_quantities[$item['product_id']]) && !empty($product_quantities[$item['product_id']]))
    {
        $product_quantities[$item['product_id']] += $quantity_to_check;
    }
    else
    {
        $product_quantities[$item['product_id']] = $quantity_to_check;
    }

    $quantity_exist = ProductHasBranchOffice::getQuantity($item['product_id']);

    $product_existences[$item['product_id']] = (int) $quantity_exist;
}

$all_ok = true;
$prod_whith_stock_incomplete = [];
foreach ($product_quantities AS $idx => $quant)
{
    if($product_quantities[$idx] > $product_existences[$idx])
    {
        $all_ok = false;
        $insuf = $product_quantities[$idx] - $product_existences[$idx];
        $prod_whith_stock_incomplete[] = $product_descriptions[$idx].': '.GlobalFunctions::formatNumber($insuf,0).' Unid faltantes';
    }
}

if(!$all_ok)
{
    echo '<hr>';
    echo '<h5>'.Yii::t('backend','No es posible completar la preparación de mercancías por insuficiente stock de estos productos').': </h5>';
    echo '<ul>';
    foreach ($prod_whith_stock_incomplete AS $index_stock => $val_insufficient)
    {
        echo '<li>'.$val_insufficient.'</li>';
    }
    echo '</ul>';
    echo '<hr>';
}

?>
<table class="table-bordered" border="0" cellpadding="5" cellspacing="0" width="100%">

    <tr style="background-color: #e5e7ea; padding: 5px;">
        <th width="15%" style="font-size: 14px; font-weight: bold; text-align:center"><?= Yii::t('backend','Código') ?></th>
        <th width="35%" style="font-size: 14px; font-weight: bold; text-align:center"><?= Yii::t('backend','Descripción') ?></th>
        <th width="15%" style="font-size: 14px; font-weight: bold; text-align:center"><?= Yii::t('backend','Cantidad') ?></th>
        <th width="5%" style="font-size: 14px; font-weight: bold; text-align:center">&nbsp;</th>
    </tr>

    <tbody>
    <!-- ITEMS HERE -->
    <?php
        $color_background = false;
        foreach ($items as $index_result => $item_result)
        {
            $quantity_label = '';
            if(isset($item_result['unit_type']))
            {
                $unit_type_code = $item_result['unit_type'];

                if(isset($item_result['quantity_by_box']) && ($unit_type_code == 'CAJ' || $unit_type_code == 'CJ'))
                {
                    $quantity_label = ' [1x'.$item_result['quantity_by_box'].']';
                }
                elseif(isset($item_result['package_quantity']) && ($unit_type_code == 'BULT' || $unit_type_code == 'PAQ'))
                {
                    $quantity_label = ' [1x'.$item_result['package_quantity'].']';
                }
            }

            $cell_color = ($color_background)? '#F0F2F5' : '#FFFFFF';
        ?>
        <tr style="background: <?= $cell_color ?>">

            <td align="left">
                <span style="font-size: 14px; font-weight: normal;"><?= $item_result['code'] ?></span>
            </td>
            <td align="left">
                <span style="font-size: 14px; font-weight: normal;"><?= $item_result['description'] ?></span>
            </td>
            <td align="left">
                <span style="font-size: 14px; font-weight: normal;"><?= GlobalFunctions::formatNumber($item_result['quantity'],0).' '.$item_result['unit_type'].' '.$quantity_label  ?></span>
            </td>

            <td align="center"><i class="fa fa-check-square-o"></i></td>

        </tr>
    <?php
            $color_background = !$color_background;
        }
    ?>
    </tbody>
</table>