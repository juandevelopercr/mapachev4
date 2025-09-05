<?php
use backend\models\business\SellerHasInvoice;
use backend\models\business\CollectorHasInvoice;
?>
  <tr>
    <td></td>
    <td class='kv-align-center kv-align-middle' data-col-seq='2' style='mso-number-format: &quot;\@&quot;;'><?= $data['consecutive']; ?></td>
    <td class='kv-align-center kv-align-middle' data-col-seq='2' style='mso-number-format: &quot;\@&quot;; text-align:center'><?= date('d-M-Y', strtotime($data['emission_date'])); ?></td>
    <td class='kv-align-center kv-align-middle' data-col-seq='2' style='mso-number-format: &quot;\@&quot;;'><?= $data['customer']; ?></td>
    <td class='kv-align-center kv-align-middle' data-col-seq='3' style='mso-number-format: &quot;\@&quot;;'><?= $data['reference']; ?></td>
    <td class='kv-align-center kv-align-middle' data-col-seq='3' style='mso-number-format: &quot;\@&quot;;'><?= $data['payment_method'] ?></td>
    <td class='kv-align-right kv-align-middle' data-col-seq='3' style='mso-number-format: &quot;\#\,\#\#0\.00&quot;;'><?= $data['collector']; ?></td>
    <td class='kv-align-right kv-align-middle' data-col-seq='4' style='mso-number-format: &quot;\#\,\#\#0\.00&quot;; text-align:right'><strong><?= number_format($data['amount'], 2, '.', ',') ?></strong></td>
  </tr>