<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use mdm\admin\components\Helper;
use yii\web\View;
use yii\helpers\Url;
use backend\components\Footer_Bulk_Delete;
use backend\components\Custom_Settings_Column_GridView;
use common\models\GlobalFunctions;
use backend\models\business\Customer;
use backend\models\nomenclators\ConditionSale;
use backend\models\nomenclators\Currency;
use backend\models\nomenclators\UtilsConstants;
use backend\models\nomenclators\Zone;
use common\models\User;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\business\InvoiceSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$controllerId = '/' . $this->context->uniqueId . '/';
$this->title = Yii::t('backend', 'Facturas y tiquetes TPV');
$this->params['breadcrumbs'][] = $this->title;
?>


<section data-role="section" id="section-section1">
    <div class="row" style="background-color: #222222;">
        <!--<div>-->
        <div style="float: left; width: 20%; text-align: right; padding-left: 8px;">
            <div class="mmn">
                <a href="#" class="ssm-toggle-nav" title="open nav" onClick="Update_sales();"> <span class="glyphicon glyphicon-menu-hamburger" style="color: #F1f1f1; font-size: 18px; font-weight: 600;"></span>
                </a>
            </div>
        </div>
        <div class="mn_kebab dropdown">
            <!--<a href="#" onClick="view_pre_orders();" > <span class="glyphicon  glyphicon-option-vertical" title="Ventas aplazadas."  style="color:#f1f1f1;margin-top: 3px;  font-size: 18px ; font-weight:400; "></span> </a>-->
        </div>
    </div>

    </div>
    <!--************* pagina grande  ****************************-->
    <div id="uno" style="border-right: 2px solid #aaa;">
        <div class="row" style="padding: 0 1px;">
            <div class="col-xs-6" style="padding-right: 0 !important;">

                <div class="btn-group btn-group-justified" role="group" aria-label="">
                    <div class="btn-group" role="group">
                        <div class="inner-addon left-addon">
                            <i class="glyphicon glyphicon-search"></i> <input id="search_in" type="text" class="form-control" placeholder="Introduce modelo" style="border-radius: 0;" autofocus onp />
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xs-3" style="padding-left: 0 !important; padding-right: 0 !important;">
                <div class="btn-group btn-group-justified" role="group" aria-label="">
                    <div class="btn-group" role="group" id="mid_preorder">
                        <button id="" class="btn btn-default btn-square" type="button" title="Ventas aplazadas" onClick="view_pre_orders(); return false;" style="width: 100%;">
                            <span class="glyphicon glyphicon-th" style="margin: 0 10px;"></span>
                        </button>

                    </div>
                </div>
            </div>
            <div class="col-xs-3" style="padding-left: 0 !important;">
                <div class="btn-group btn-group-justified" role="group" aria-label="">
                    <div class="btn-group" role="group" id="mservir_s">
                        <button id="s_in_b" class="btn btn-default btn-square" type="button" title="Nuevo servicio" onClick="service(); return false;" style="width: 100%;">
                            <span class="glyphicon glyphicon-edit" style="margin: 0 10px;"></span>
                        </button>

                    </div>
                </div>
            </div>
        </div>
        <div id="mbar_g">
            <div id="uno_in" style="margin: 0; padding: 0; borde: 1px solid #f00;"></div>
        </div>
    </div>
    <div id="dos" style="padding-left: 3px;">
        <div class="btn-group btn-group-justified" id="btns_cata">
            <a href="#" class="btn btn-default" onClick="discount();"><span style="font-weight: 800;">% </span>Descuento</a> <a data-toggle="modal" href="#" onClick="add_customer();" class="btn btn-default"> <span class="glyphicon glyphicon-user"></span> Cliente
            </a> <a data-toggle="modal" href="#" class="btn btn-default" onClick="read_order();" title="Guardar"> <span class="glyphicon glyphicon-saved"></span> Guardar
            </a> <a data-toggle="modal" href="#" class="btn btn-default" onClick="del_order();" title="Borrar"> <span class="glyphicon glyphicon-trash"></span> Borrar
            </a>
        </div>
        <div class="btn-group btn-group-justified" id="btns_vent" style="display: none;">
            <a href="#" class="btn btn-default" onClick="factur();">Imprimir</a>
            <a data-toggle="modal" href="#" class="btn btn-default" onClick="refund(); return false;"> Devolución </a>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <div class="alert alert-success" id="xst" style="display: none;">
                    <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a> La busqueda ha tenido exito
                </div>
                <div class="alert alert-danger" id="fcs" style="display: none;">
                    <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a> No se ha podido encontrar ta id.
                </div>
            </div>
        </div>

        <div id='order'>
            <table class="table table-bordered table-hover text-right" id='orderTable' style="margin-top: 20px;">
                <tr class='toprow' style="display: none; padding-bottom: 20px;">
                    <td class="text-left">
                        Artículo </td>
                    <td width='17%' class="text-left">
                        Precio </td>
                    <td width='10%' class="text-left">
                        Cantidad </td>
                    <td width='17%' class="text-left">
                        Total </td>
                    <td width='12%'></td>
                </tr>
            </table>
            <div class="row" style="margin-top: 15px; padding-left: 16px" id="orderCustomer"></div>
            <div class="row" style="margin-top: 15px;">
                <div class="col-xs-12" style="padding: 0 20px 12px 10px;">
                    <button type="button" class="btn btn-danger btn-sm btn-square btn-block" onclick="checkout(); return false;" style="padding: 15px 0;">
                        <!--Pagar:-->
                        <span id="orderTotal2" style="font-size: 35px; font-weight: 100;">
                            &nbsp;</span>

                    </button>
                </div>
            </div>
        </div>
    </div>
    <!--************* FIN pagina grande  ****************************-->
</section>
<section data-role="section" id="section-section2">
    <div class="row" style="background-color: #222222;" style="padding-left: 14px;">
        <div style="float: left; width: 20%; text-align: right">
            <div class="mmn">
                <a href="#" class="ssm-toggle-nav" title="open nav"> <span class="glyphicon glyphicon-menu-hamburger" style="color: #F1f1f1; font-size: 18px; padding-left: 8px; font-weight: 600;"></span>
                </a>
            </div>
        </div>
        <div class="mn_kebab dropdown" style="visibility: hidden" onClick="view_pre_orders();">
            <!--<a href="#" onClick="view_pre_orders();" >-->
            <span class="glyphicon  glyphicon-option-vertical" style="color: #f1f1f1; margin-top: 3px; font-size: 18px; font-weight: 400;"></span>
            <!--</a>-->
        </div>
    </div>
    <div id="to_car" class="row sub_bar2">
        <div id="custom-search-input" class="col-xs-5" style="border: 1px solid #CDCECF; padding-right: 0px !important">
            <input id="search_in2" type="text" placeholder="Introduce modelo" class="form-control" style="border-radius: 0;" autofocus />
        </div>
        <div class="col-xs-2 text-left" style="padding-left: 0 !important; padding-right: 0 !important;">
            <!--<button id="search_in_b" class="btn btn-default btn-block" type="button" title="Nuevo servicio" onClick="service();" > <span class="glyphicon glyphicon-saved" style="margin:0 10px;"></span> </button>-->
            <button id="" class="btn btn-default btn-square" type="button" title="Ventas aplazadas" onClick="view_pre_orders(); return false;" style="width: 100%; border-width: 2px solid;">
                <span class="glyphicon glyphicon-th" style="margin: 0 10px;"></span>
            </button>

        </div>
        <div class="col-xs-2 text-left" style="padding-left: 0 !important; padding-right: 1px !important;">
            <!--<button id="search_in_b" class="btn btn-default btn-block" type="button" title="Nuevo servicio" onClick="service();" > <span class="glyphicon glyphicon-saved" style="margin:0 10px;"></span> </button>-->
            <button id="search_in_b" class="btn btn-default btn-square" type="button" title="Nuevo servicio" onClick="service();" style="width: 100%; border-width: 2px solid; padding-left: 3px !important; padding-right: 1px !important;">
                <span class="glyphicon glyphicon-edit" style="margin: 0 10px;"></span>
            </button>

        </div>
        <div class="col-xs-1 text-left" style="margin: 0; text-align: center; padding-left: 1px !important; padding-right: 0 !important;">
            <span id="nucar" class="badge" style="color: #ff0000; background-color: #ffffff; border: 1px solid #cc0000; font-size: 11px !important; padding: 2px 5px;">0</span>
        </div>
        <div class="col-xs-1" style="margin: 3px 0 0 0; text-align: left; padding-left: 0 !important; padding-right: 0 !important;">
            <a href="#section-section3" style="font-size: 11px !important; text-decoration: none"> <span style="font-size: 40px; font-weight: 800; line-height: 12px; color: #000000; text-decoration: none; margin-top: 6px;">&rarr;</span>
            </a>
        </div>
        <div class="col-xs-1 text-left" style="margin: 0; text-align: left;">
            <!--<span id="nucar" class="badge" style="color:#ff0000;background-color:#ffffff;border:1px solid #cc0000; font-size: 11px !important; padding: 1px 7px;">0</span>-->
        </div>
    </div>
    <div id="mbar" style="">
        <div id="tres"></div>
    </div>
    <!--************* FIN pagina pagina catalogo  ****************************-->
</section>
<section data-role="section" id="section-section3">
    <div class="row" style="background-color: #222222;">
        <div style="float: left; width: 20%; text-align: right; padding: 0 8px;">
            <div class="mmn" style="visibility: hidden;">
                <a href="#" class="ssm-toggle-nav" title="open nav"> <span class="glyphicon glyphicon-menu-hamburger" style="color: #F1f1f1; font-size: 18px; font-weight: 600;"> </span>
                </a>
            </div>
        </div>
        <div class="mn_kebab dropdown" style="padding-right: 8px;">
            <!--  <span class="glyphicon  glyphicon-option-vertical"  style="color:#f1f1f1;margin-bottom: 8px; padding-right:4px; font-size: 18px ; font-weight:400; "> </span> -->
        </div>
    </div>
    <!--************* pagina pagina carro  ****************************-->
    <!--<div id="cuatro">-->
    <div id="to_catal" class="row sub_bar2">
        <div class="btn-group btn-group-justified" id="bot_group" style="padding-left: 15px">
            <a href="#section-section2" class="btn btn-default" style="font-size: 11px !important; text-decoration: none"> <span style="font-size: 40px; font-weight: 800; line-height: 12px; color: #000000; text-decoration: none; margin-top: 6px;">&larr;</span>
            </a> <a href="#" class="btn btn-default btn-square" onClick="discount();">%</a> <a data-toggle="modal" href="#" onClick="add_customer();" class="btn btn-default"> <span class="glyphicon glyphicon-user"></span>
            </a> <a data-toggle="modal" href="#" class="btn btn-default" onClick="read_order();"> <span class="glyphicon glyphicon-saved"></span></a>
            <a data-toggle="modal" href="#" class="btn btn-default" onClick="del_order();"> <span class="glyphicon glyphicon-trash"></span></a>
        </div>
    </div>
    <div id='order2'>
        <table class="table table-bordered table-hover" id='orderTable2'>
            <tr class='toprow' style="display: none;">
                <td width='170px'>
                    Artículo </td>
                <td width='70px'>
                    Precio </td>
                <td width='40px'>Nº</td>
                <td width='70px'>
                    Total </td>
                <td width='50px'></td>
            </tr>
        </table>
        <div class="row">
            <div class="col-xs-12" style="margin-top: 15px; margin-bottom: 6px; padding-left: 22px" id="orderCustomer2"></div>
            <div class="col-xs-12" style="padding-bottom: 20px; padding-left: 20px; padding-right: 25px;">
                <button type="button" class="btn btn-danger btn-lg btn-square btn-block" style="padding: 15px !important; width: 100%" onclick="checkout(); return false;">
                    <!--Pagar: -->
                    <span id="orderTotal2_2" style="margin-left: 8px; font-size: 16px;"> &nbsp;</span>
                </button>
            </div>
        </div>
    </div>
    <!--************* FIN pagina pagina carro  *************************************************************************-->
</section>
<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" id="my_long" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>

                <h4 class="modal-title" id="myModalLabel1">Modal title</h4>
            </div>
            <div class="modal-body" id="mymodal-body1">...</div>
            <div class="modal-footer" id="mymodal-footer1">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary">Save changes</button>
            </div>
        </div>
    </div>
</div>
<!--******************************************************************************************************************-->

<div class="ssm-overlay ssm-toggle-nav"></div>
<footer data-role="footer" id="footer">
    <div style="display: none;">
        <div id="mi_print" style="padding: 0;"></div>
    </div>
</footer>
<!-- consola| BORRAR -->
<?php
$this->registerJs('
    var pre_w_width;
    var lim_W = 700;
    var last_load = 0;
    var loaded_search = 0;
    var vary;
    
    $(window).resize(function() {
        apl_resize();
        searchfoc();
    })
    $(document).ready(function() {
        mmargin();
        mmargin2();
    });
    $("section").horizon();

    $(document).on("click", ".go-to-2", function() {
        $(document).horizon("scrollTo", "section-section2");
    });

    $("section").horizon();

    $(document).on("click", ".go-to-2", function() {
        $(document).horizon("scrollTo", "section-section2");
    });

    function apl_resize() {
        var ancho = $(window).width();
        if (ancho < lim_W) {
            var status_w = "pequegno";
        } else {
            var status_w = "grande";
        }

        if (pre_w_width < lim_W) {
            var pre_status = "pequegno";
        } else {
            var pre_status = "grande";
        }
        if (pre_status !== status_w) { //Hay salto
            if (status_w == "pequegno") {
                $("#section-section1").css("display", "none");
                $("#section-section2").css("display", "inline");
                $("#section-section3").css("display", "inline");
            } else {
                $("#section-section1").css("display", "inline");
                $("#section-section2").css("display", "none");
                $("#section-section3").css("display", "none");
            }
        }
        pre_w_width = ancho;
        mmargin();
        mmargin2();
    }

    function mmargin() {
        var str = $("#uno_in").css("width");
        var pattern = /[0-9]+/g;
        var ancho_total = str.match(pattern);
        var ancho_prod = 100; // + 10 + 6 + 2; //width + padding + margin + border 
        var msobra_t = ancho_total % ancho_prod;
        var num_prod = ((ancho_total - msobra_t) / ancho_prod);
        var mmarge = msobra_t / (num_prod + 1);
        var margen = Math.trunc(mmarge);
        var margen_px = margen + "px";
        $(".prod").css("margin-right", margen_px);
        $("#prodContainer").css("padding-left", margen_px);
        $(".prod").css("height", "158px");
    }

    function mmargin2() {
        var str = $("#tres").css("width");
        var pattern = /[0-9]+/g;
        var ancho_total = str.match(pattern);
        var ancho_prod = 106; // + 10 + 6 + 2; //width + padding + margin + border 
        var msobra_t = ancho_total % ancho_prod;
        var num_prod = ((ancho_total - msobra_t) / ancho_prod);
        var mmarge = msobra_t / (num_prod + 1);
        var margen = Math.trunc(mmarge);
        if (margen > 3) {
            margen = margen + 3; //margen de la izq. 3px;			
            var margen_px = margen + "px";
        } else {
            var margen_px = "3px";
        }
        $(".prod2").css("margin-right", margen_px);
        $("#prodContainer2").css("padding-left", margen_px);
        $(".prod2").css("height", "158px");
    }

    function mfocus_search() {
        var ancho = $(window).width();
        if (ancho < lim_W) {
            $("#search_in2").show().focus();
        } else {
            $("#search_in").show().focus();
        }
    }

    init();
    myVar = setTimeout(apl_resize, 300);
    // By default, swipe is enabled.
    $("section").horizon();
    $(document).on("click", ".go-to-2", function() {
        $(document).horizon("scrollTo", "section-section2");
    });

    function una() {
        $("#section-section1").css("display", "inline");
        $("#section-section2").css("display", "none");
        $("#section-section3").css("display", "none");
    }

    function dos() {
        $("#section-section1").css("display", "none");
        $("#section-section2").css("display", "inline");
        $("#section-section3").css("display", "inline");
    }

    $("section").horizon();
    $(document).ready(function() {
        $(".nav2").slideAndSwipe();
    });




    $("#search_in").keyup(function(e) {
        if (e.which == 13) {
            //console.log("El texto insertado es ===> ", $("#mitexto").val());
            var tx = $("#search_in").val();
            tx = tx.replace(String.fromCharCode(13), "");
            $("#search_in").val(tx);
            $("#search_in2").val(tx);
            //console.log("MIV ==> ", tx);
            newSearch(tx);
            vary = tx;
        }
    });

    $("#search_in2").keyup(function(e) {
        if (e.which == 13) {
            //console.log("El texto insertado es ===> ", $("#mitexto").val());
            var tx = $("#search_in2").val();
            tx = tx.replace(String.fromCharCode(13), "");
            $("#search_in").val(tx);
            $("#search_in2").val(tx);
            //console.log("TX2 ==> ", tx);
            newSearch(tx);
            vary = tx;
        }
    });

    $("#myModal").on("hidden.bs.modal", function(e) {
        mfocus_search();
    });

    $(".ssm-overlay").click(function() {
        setTimeout(function() {
            mfocus_search();
        }, 100);
    });
');
?>    