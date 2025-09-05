<?php
use backend\models\business\CollectorHasInvoice;
use backend\models\business\SellerHasInvoice;
use backend\models\nomenclators\UtilsConstants;
?>
<tr>
  <td class='kv-align-center kv-align-middle' data-col-seq='2' style='mso-number-format: &quot;\@&quot;;'><?= $invoice->consecutive; ?></td>
  <td class='kv-align-center kv-align-middle' data-col-seq='2' style='mso-number-format: &quot;\@&quot;;'><?= date('d-M-Y', strtotime($invoice->emission_date)); ?></td>
  <td class='kv-align-center kv-align-middle' data-col-seq='2' style='mso-number-format: &quot;\@&quot;;'><?= $invoice->cliente; ?></td>
  <td class='kv-align-center kv-align-middle' data-col-seq='2' style='mso-number-format: &quot;\@&quot;;'><?= UtilsConstants::getPreInvoiceSelectType($invoice->invoice_type); ?></td>
  <?php 
  /*
  <td class='kv-align-center kv-align-middle' data-col-seq='3' style='mso-number-format: &quot;\@&quot;;'>
    <?= SellerHasInvoice::getSellerStringByInvoice($invoice->id); ?>
  </td>
  <td class='kv-align-center kv-align-middle' data-col-seq='3' style='mso-number-format: &quot;\@&quot;;'>
    <?= CollectorHasInvoice::getCollectorStringByInvoice($invoice->id); ?>
  </td>
  */
  ?>
  <td class='kv-align-center kv-align-middle' data-col-seq='3' style='mso-number-format: &quot;\#\,\#\#0\.00&quot;;'><?= $cantidad_item; ?></td>
  <td class='kv-align-right kv-align-middle' data-col-seq='3' style='mso-number-format: &quot;\#\,\#\#0\.00&quot;;'><?= $discount; ?></td>
  <td class='kv-align-right kv-align-middle' data-col-seq='4' style='mso-number-format: &quot;\#\,\#\#0\.00&quot;;'><?= $tax ?></td>
  <td class='kv-align-right kv-align-middle' data-col-seq='4' style='mso-number-format: &quot;\#\,\#\#0\.00&quot;;'><?= $exonerate ?></td>
  <td class='kv-align-right kv-align-middle' data-col-seq='4' style='mso-number-format: &quot;\#\,\#\#0\.00&quot;;'><?= $total ?></td>
</tr>