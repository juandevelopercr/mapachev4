<?php

use yii\helpers\Html;
use kartik\form\ActiveForm;
use common\models\GlobalFunctions;
?>

<?php
$i = 0;
?>
<div class="panel panel-default">
    <div class="panel-heading"><strong><?= Yii::t('backend', 'Efectivo Inicial en Caja') ?></strong></div>        
    <div class="panel-body">
        <table class="table table-hover" id="tablecaja">
            <thead>
                <tr>
                    <th><?= Yii::t('backend', 'Descripción') ?></th>
                    <th><?= Yii::t('backend', 'Valor') ?></th>
                    <th><?= Yii::t('backend', 'Cantidad') ?></th>
                    <th><?= Yii::t('backend', 'Importe') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php
                $index = 0;
                $total = 0;
                foreach ($coins as $c) : ?>
                    <tr>
                        <td><?= $c['description'] ?></td>
                        <td><span class="cellvalue" required><?= GlobalFunctions::formatNumber($c['value'], 2) ?></td>
                        <input type="hidden" name="efectivo[<?= $index ?>][value]" value="<?= $c['value'] ?>">      
                        <input type="hidden" name="efectivo[<?= $index ?>][description]" value="<?= $c['description'] ?>">                   
                        <input type="hidden" name="efectivo[<?= $index ?>][denominations_id]" value="<?= $c['id'] ?>">                   
                        <td><input type="text" name="efectivo[<?= $index ?>][count]" class="form-control cellcantidad" size="5" value="0" required> </td>
                        <td><span class="cellimporte"></span></td>
                    </tr>
                <?php
                $index++;
                endforeach;
                ?>
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="3"><?= Yii::t('backend', 'Total') ?></th>
                    <th><span id="celltotal"></span></th>
                </tr>
            </tfoot>
        </table>
    </div>
</div> 

<?php
$js = <<<JS
// get the form id and set the event
$(document).ready(function(e) {

    $('.cellcantidad').keypress(function(e) {    
          // Only ASCII character in that range allowed   
          var ASCIICode = (e.which) ? e.which : e.keyCode
          if (ASCIICode > 31 && (ASCIICode < 48 || ASCIICode > 57))
              return false;
          return true;
    });

    $('.cellcantidad').keyup(function(e) {    
          // Only ASCII character in that range allowed 
          var value = $(this).parent().parent().find('.cellvalue').html();  
          value = value.replace(/\./g,"");			
          value = value.replace(/\,/g,".");	
          if(!isNaN(value)){		
             var importe = value * $(this).val();
             importe = importe.toFixed(2);             
             importe = importe.replace(/\./g,",");	             
             
             importe = format(importe);            
             $(this).parent().parent().find('.cellimporte').html(importe);
          }
          else
            $(this).parent().parent().find('.cellimporte').html(0);

          updateTotal();  
    });

    function format(num)
    {
        num = num.toString().split('').reverse().join('').replace(/(?=\d*\.?)(\d{3})/g,'$1.');
        num = num.split('').reverse().join('').replace(/^[\.]/,'');
        return num;
    }

    function updateTotal()
    {
        // Recorrer la tabla completa
        var Sumasubtotal = 0;
        let tabla = document.getElementById('tablecaja');
        
        $("#tablecaja tr").each(function () {
        //for (const row of tabla.rows) {
            //alert("ok");
            let subtotal = 0;
            let value = $(this).find('.cellvalue').html();
            if (value)
                value = value.replace(/\./g,"");	

            let cantidad = $(this).find('.cellcantidad').val();
            if (cantidad)
                cantidad = cantidad.replace(/\./g,"");	
            console.log('Value: ' + value);
            console.log('Cantidad: ' + cantidad);
            if (value && cantidad) {
                subtotal = parseFloat(value) * parseInt(cantidad);
            }
            Sumasubtotal += subtotal;   
            console.log("SUBTOTAL: " + Sumasubtotal);        
        });
        Sumasubtotal = Sumasubtotal.toFixed(2);             
        Sumasubtotal = Sumasubtotal.replace(/\./g,",");	             
        
        console.log("SUBTOTAL: " + Sumasubtotal);        

        Sumasubtotal = format(Sumasubtotal);     
        
        console.log("SUBTOTAL: " + Sumasubtotal);        
        $('#celltotal').html(Sumasubtotal);
    }

    //updateTotal();
});
JS;
$this->registerJs($js);
?>