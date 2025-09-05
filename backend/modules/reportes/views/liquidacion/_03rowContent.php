<?php
use backend\models\business\SellerHasInvoice;
use backend\models\business\CollectorHasInvoice;
?>
  <tr>
    <td></td>
    <td class='kv-align-center kv-align-middle' data-col-seq='2' style='mso-number-format: &quot;\@&quot;;'><?= $data['consecutive']; ?></td>
    <td class='kv-align-center kv-align-middle' data-col-seq='2' style='mso-number-format: &quot;\@&quot;;'><?= $data['invoice_type']; ?></td>  
    <td class='kv-align-center kv-align-middle' data-col-seq='2' style='mso-number-format: &quot;\@&quot;;' style="text-align: center;"><?= date('d-M-Y', strtotime($data['emission_date'])); ?></td>
    <td class='kv-align-center kv-align-middle' data-col-seq='2' style='mso-number-format: &quot;\@&quot;;'><?= $data['customer']; ?></td>
    <td class='kv-align-center kv-align-middle' data-col-seq='3' style='mso-number-format: &quot;\@&quot;;' style="text-align: center;"><?= date('d-M-Y', strtotime($abono['emission_date'])); ?></td>
    <td class='kv-align-center kv-align-middle' data-col-seq='3' style='mso-number-format: &quot;\@&quot;;' style="text-align: right;"><?= number_format($saldo, 2, '.', ',') ?></td>
    <td class='kv-align-center kv-align-middle' data-col-seq='3' style='mso-number-format: &quot;\@&quot;;' style="text-align: right;"><?= number_format($abonado, 2, '.', ',') ?></td>
    <td class='kv-align-center kv-align-middle' data-col-seq='3' style='mso-number-format: &quot;\@&quot;;' style="text-align: right;"><?= number_format($saldofinal, 2, '.', ',') ?></td>
    <td class='kv-align-center kv-align-middle' data-col-seq='3' style='mso-number-format: &quot;\@&quot;;' style="text-align: right;"><?= number_format($abonado_dia, 2, '.', ',') ?></td>
    <td class='kv-align-center kv-align-middle' data-col-seq='2' style='mso-number-format: &quot;\@&quot;;' style="text-align: center;"><?= $data['status']; ?></td>   
  </tr>