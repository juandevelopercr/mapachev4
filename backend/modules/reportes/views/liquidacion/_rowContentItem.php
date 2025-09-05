  <?php
use backend\models\business\CollectorHasInvoice;
  ?>
  <tr>
    <td></td>
    <td class='kv-align-center kv-align-middle' data-col-seq='2' style='mso-number-format: &quot;\@&quot;;'><?= date('d-M-Y', strtotime($invoice->emission_date)); ?></td>
    <td class='kv-align-center kv-align-middle' data-col-seq='2' style='mso-number-format: &quot;\@&quot;;'><?= $item->reference; ?></td>
    <td class='kv-align-center kv-align-middle' data-col-seq='3' style='mso-number-format: &quot;\@&quot;;'><?= $item->paymentMethod->name; ?></td>
    <td class='kv-align-center kv-align-middle' data-col-seq='3' style='mso-number-format: &quot;\@&quot;;'><?= ''; ?></td>
    <td class='kv-align-center kv-align-middle' data-col-seq='3' style='mso-number-format: &quot;\@&quot;;'>
      <?= CollectorHasInvoice::getCollectorStringByInvoice($invoice->id); ?>
    </td>
    <td colspan="3" class='kv-align-center kv-align-middle' data-col-seq='2' style='mso-number-format: &quot;\@&quot;;'><?= $item->comment; ?></td>    
    <td class='kv-align-right kv-align-middle' data-col-seq='3' style='mso-number-format: &quot;\#\,\#\#0\.00&quot;;'><?= number_format($item->amount, 2, '.', ','); ?></td>    
  </tr>