<?php

use yii\helpers\Html;
use kartik\form\ActiveForm;
use kartik\switchinput\SwitchInput;
use kartik\number\NumberControl;
use common\models\GlobalFunctions;
use kartik\datecontrol\DateControl;
use kartik\select2\Select2;
use backend\models\nomenclators\BranchOffice;
use backend\models\nomenclators\Boxes;
use backend\models\business\Customer;
use backend\models\nomenclators\CreditDays;
use backend\models\nomenclators\ConditionSale;
use backend\models\nomenclators\Currency;
use backend\models\nomenclators\UtilsConstants;
use common\models\User;
use backend\models\nomenclators\PaymentMethod;
use yii\helpers\Url;
use kartik\depdrop\DepDrop;

/* @var $this yii\web\View */
/* @var $model backend\models\business\Invoice */
/* @var $form yii\widgets\ActiveForm */
/* @var $searchModelItems \backend\models\business\ItemInvoiceSearch */
/* @var $dataProviderItems yii\data\ActiveDataProvider */

?>

<div class="container-fluid">
    <form role="form" onsubmit="checkoutAddPayment(); return false;" name="payment" id="payment" name="f_payment">
        <div class="form-group">
            <label style="font-size: 22px;">Forma de pago</label>
                <div id="paymentCheckout" class="form-group" style="padding-left: 10px;">
                    <?php                    
                    foreach ($metodosPagos as $key => $value) : ?>
                        <?php
                        $checked = '';
                        if ($key == 1)
                            $checked = 'checked';
                        ?>
                        <label for="PM0" class="radio-inline" style="font-size: 22px;">
                        <input type="radio" id="PM<?= $key ?>" name="PM" value="<?= $key ?>" <?= $checked ?> class="customctrl" 
                            style="width:0.8em; height:0.8em; padding: 2px;"><?= $value ?></label>
                    <?php
                    endforeach;
                    ?>
                </div>
        </div>
        <div class="col-md-6">            
            <div class="form-group">
                <label style="font-size: 22px;">Total Compra:</label>
                <input type="text" id="PMAmount" class="form-control" disabled style="font-size: 22px;">
                <input type="hidden" id="HPMAmount">
            </div>

            <div class="form-group" id="ctr_pay_banco_cheque" style="display: none;">
                <label style="font-size: 22px;">Cheque No.:</label>
                <input type="text" id="PMBancoCheque" name="PMBancoCheque" class="form-control" style="font-size: 22px;">
            </div>            
        </div>                    
        <?php
        /*
        <div class="form-group">
            <label class="col-lg-3 control-label">Pendiente:</label>
            <div class="col-lg-9">
                <input type="text" id="PMAmountPendiente" class="form-control" disabled>
            </div>
        </div> 
        */
        ?>
        <div class="col-md-6">
            <div class="form-group" id="ctr_pay_paga_con">
                <label style="font-size: 22px;">Paga con:</label>
                <input type="text" id="PMValue" name="PMValue" class="form-control" style="font-size: 22px;">
                <input type="hidden" id="HPMValue" name="HPMValue">
            </div>

            <?php 
            /*                    
            <div class="form-group" id="ctr_pay_tarjeta" style="display: none;">
                <label style="font-size: 22px;">4 Últimos dígitos:</label>
                <input type="text" id="PMTarjeta" name="PMTarjeta" class="form-control" style="font-size: 22px;">
            </div>
            */
            ?>        

            <div class="form-group" id="ctr_pay_banco" style="display: none;">
                <label style="font-size: 22px;">Banco:</label>
                <input type="text" id="PMBanco" name="PMBanco" class="form-control" style="font-size: 22px;">
            </div>




        </div>   
        
        <div class="col-md-6">
            <div class="form-group" id="ctr_pay_vuelto">
                <label style="font-size: 22px;">Vuelto:</label>
                <input type="text" id="PMVuelto" name="PMVuelto" class="form-control" disabled style="font-size: 22px;">
            </div>

            <div class="form-group" id="ctr_pay_tarjeta_referencia" style="display: none;">
                <label style="font-size: 22px;">Referencia:</label>
                <input type="text" id="PMTarjetaReferencia" name="PMTarjetaReferencia" class="form-control" style="font-size: 22px;">
            </div>  
            
            <div class="form-group" id="ctr_pay_banco_comprobante" style="display: none;">
                <label style="font-size: 22px;">Comprobante:</label>
                <input type="text" id="PMBancoComprobante" name="PMBancoComprobante" class="form-control" style="font-size: 22px;">
            </div>            
        </div>

        <?php
        /*
        <div class="form-group">
            <div class="col-lg-2"></div>
            <div class="col-md-10">
                <div class="panel panel-default">
                    <div class="panel-body">
                        <input type='checkbox' id='mgift' class='miregalo'>
                        <label for='checkbox'>Para regalo (sin precio visible)</label>
                    </div>
                </div>
            </div>
        </div>
        */
        ?>
        <div class="form-group">
            <div class="panel panel-default" style="padding: 5px;">
                <input type='hidden' id='paymentLines'> 
                <input type="radio" name="impreimfac" value="fac" />
                <label for="impreimfac" style="font-size: 22px;">Factura electrónica</label>
                <br />
                <input type="radio" name="impreimfac" value="simple" Checked style="font-size: 22px;" />
                <label for="impreimfac" style="font-size: 22px;">Tiquete electrónico</label>
            </div>
        </div>
</div>
</form>

<?php
$js = <<<JS
    // get the form id and set the event
    function submi_Form() {
        $("#payment").submit();
    }

    $(document).ready(function() {
        $('.f_table_teclado tr td').click(function() {
            var number = $(this).text();

            if (number == '') {
                $('#PMValue').val($('#PMValue').val().substr(0, $('#PMValue').val().length - 1)).focus();
                $('#HPMAmount').val($('#HPMAmount').val().substr(0, $('#HPMAmount').val().length - 1));
                
            } else {
                $('#PMValue').val($('#PMValue').val() + number).focus();
                $('#HPMAmount').val($('#HPMAmount').val() + number);
            }
        });
    });

    $("#PMValue").keyup(function() {
        $("#HPMValue").val($(this).val());
        calculaVuelto();
    });      

    function calculaVuelto(){
        console.log("DATO: " + $('#HPMAmount').val())
        var total = parseFloat($('#HPMAmount').val().replace(/¢|,/g, ''));
        
        //total = parseFloat($('#HPMAmount').val().substr(0, $('#HPMAmount').val().length - 1));
        var pay = parseFloat($('#PMValue').val());
        console.log("Total: " + total);
        console.log("Pay: " + pay);
        
        var vuelto = pay - total;
        vuelto = parseFloat(vuelto).toFixed(2);
        $("#PMVuelto").val(vuelto);
    }  
    
    $("input[name=PM]").on('change', function() {
        if ($(this).val() == 1) {
            // Efectivo
            $("#ctr_pay_paga_con").show();
            $("#ctr_pay_vuelto").show();
            $("#ctr_pay_tarjeta").hide();
            $("#ctr_pay_tarjeta_referencia").hide();
            $("#ctr_pay_banco").hide();            
            $("#ctr_pay_banco_cheque").hide();
            $("#ctr_pay_banco_comprobante").hide();
        }
        else
        if ($(this).val() == 2) {
            // Tarjeta
            $("#ctr_pay_paga_con").hide();
            $("#ctr_pay_vuelto").hide();
            $("#ctr_pay_tarjeta").show();
            $("#ctr_pay_tarjeta_referencia").show();
            $("#ctr_pay_banco").hide();
            $("#ctr_pay_banco_cheque").hide();
            $("#ctr_pay_banco_comprobante").hide();
        }
        else
        if ($(this).val() == 3) {
            // Cheque
            $("#ctr_pay_paga_con").hide();
            $("#ctr_pay_vuelto").hide();
            $("#ctr_pay_tarjeta").hide();
            $("#ctr_pay_tarjeta_referencia").hide();
            $("#ctr_pay_banco").show();
            $("#ctr_pay_banco_cheque").show();
            $("#ctr_pay_banco_comprobante").hide();
        }        
        else
        if ($(this).val() == 4) {
            // Transferencia
            $("#ctr_pay_paga_con").hide();
            $("#ctr_pay_vuelto").hide();
            $("#ctr_pay_tarjeta").hide();
            $("#ctr_pay_tarjeta_referencia").hide();
            $("#ctr_pay_banco").show();
            $("#ctr_pay_banco_cheque").hide();
            $("#ctr_pay_banco_comprobante").show();
        }
    });

    $("#PMValue").focus();
    $("#PMValue").select();
    $("#PMValue").focus(function() {
        $(this).css("background-color", "#FFFFCC");
    });

/*
    $(function() {    
        //alert("S");    
        setTimeout(function() { jQuery("#PMValue").focus() }, 3000);            
        //jQuery("#PMValue").focus();
        document.getElementById('PMValue').click();
        jQuery("#PMValue").trigger("click");  
        jQuery("#PMValue").select(); 
        $('#PMValue').animate({left:0,duration:'slow'});
        $('#PMValue').focus(); 
    }); 
*/
    /*
    $("#PMValue").load(function() {        
        $(this).focus();
    });        
    */
JS;
$this->registerJs($js);
?>