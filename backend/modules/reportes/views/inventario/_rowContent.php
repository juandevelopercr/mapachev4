<?php
use backend\models\business\PhysicalLocation;
use backend\models\business\ProductHasBranchOffice;

foreach ($productos as $product) 
{
  $productBranchOffice = ProductHasBranchOffice::find()->where(['product_id'=>$product->id])->all();
  $i = 0;
  foreach ($productBranchOffice as $pb)
  {    
  ?>
  <tr>
    <td class='kv-align-center kv-align-middle' data-col-seq='2'><?= $i == 0 ? $product->family->name: ''; ?></td>
    <td class='kv-align-center kv-align-middle' data-col-seq='3'><?= $i == 0 ? $product->category->name: ''; ?></td>
    <td class='kv-align-center kv-align-middle' data-col-seq='3'><?= $i == 0 ? $product->code . '-' . $product->description: '' ?></td>
    <td class='kv-align-center kv-align-middle' data-col-seq='4'><?= $pb->branchOffice->code.'- '.$pb->branchOffice->name ?></td>
    <td class='kv-align-center kv-align-middle' data-col-seq='4'><?= $pb->quantity ?></td>
  </tr>
<?php
$i++;
  }
}
?>