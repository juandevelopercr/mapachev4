// JavaScript Document
//1.8.0.4
var discountNr = 0;
var serviceNr = 0;
var barcodeActive = "";
var last_load = 0;
var order_imprent = '0';
/**
 * Initiate page on loadFreceipt.php
 * 
 **/
function init() {
//load_printer();
    $.ajax({
        url: 'ajax.php?action=getProducts&value=0&barcode=' + barcodeActive,
        success: function (data) {
            var data2 = data;
            //console.log("DATA LN16 ==============>", data);
            data2 = data2.replace('prodContainer', 'prodContainer2');
            data2 = data2.replace(/class='prod'/g, "class='prod2'");
            data2 = data2.replace(/class="prod"/g, 'class="prod2"');
            $('#uno_in').append(data);
            $('#tres').append(data2);
            mmargin();
            mmargin2();
            var m_nul = $("#symbol_left").val() + " 0.00 " + $("#symbol_right").val();
            $("#orderTotal2").html(m_nul);
            $("#orderTotal2_2").html(m_nul);
            searchfoc();
        }
    });
}

function load_printer(){
    var res = true
    var status = sessionStorage.getItem("printer_ini");
//console.log("STATUS ====> ", status);
    if (var_is_empty(status) == true){
        res = startConnection_inicialize("");
        sessionStorage.setItem("printer_ini", res);
    }
//console.log("RES ====> ", res);
}

function searchfoc() {
    var ancho = $(window).width();
    var status_w;
    if (ancho < lim_W) {
        status_w = 'pequegno';
    } else {
        status_w = 'grande';
    }
    if (status_w === "grande") {
        $("#search_in").show().focus();
        //console.log("GRANDE");
    }
    if (status_w === "pequegno") {
        //console.log("PEQUEÃ'O");		
        $("#search_in2").show().focus();
    }
}

function deleteLoad() {
    $('#uno_in').html("");
    $('#tres').html("");
}

/**
 * Load new category
 * @param int catId Category to load
 **/
function loadProducts(catId) {
    $.ajax({
        url: 'ajax.php?action=getProducts&value=' + catId + '&barcode=' + barcodeActive,
        success: function (data) {
            //console.log("DATA LN63 ==============>", data);
            data2 = data;
            data2 = data2.replace("id='prodContainer'", "id='prodContainer2'");
            data2 = data2.replace(/class='prod'/g, "class='prod2'");
            data2 = data2.replace(/class="prod"/g, 'class="prod2"');
            $('#prodContainer').replaceWith(data);
            $('#tres').html(data2);
            mmargin();
            mmargin2();
            last_load = catId;

            $("#tres").animate({
                scrollTop: $('#tres')[0].scrollHeight
            }, 1000);

        }
    });
}

function return_search_loadProducts(catId) {
    catId2 = '?action=getProducts&value=' + catId + '&unomas:load';
    //console.log("loadProducts(catId)", catId2);
    $.ajax({
        url: 'ajax.php' + catId2,
        success: function (data) {
            data2 = data;
            data2 = data2.replace(/class='prod'/g, "class='prod2'");
            //console.log(data2==> ", data2);			
            $('#uno_in').html(data);
            $('#tres').html(data2);
            mmargin();
            mmargin2();
            last_load = catId;
        }
    });
}

/**
 * Add new product to order
 * @param int produtId Id of product to add
 * @param string name product name
 * @param int price price of a single product
 */
function addProduct(productId, name, price, options, mvw) {
    //console.log("productId, name, price, options, mvw ==121===> ", productId + " | " + name + " | " + price + " | " + options + " | " + mvw)
    var nme = name.search("- stock:");
    if (nme == -1) {

    } else {
        name = name.substring(0, nme);
    }

    if (mvw = 1) {
        mmd_closed();

        var nme = name.search("- stock:");
        if (nme == -1) {

        } else {
            name = name.substring(0, nme);
        }


    }
    if (parseInt(options) > 0) {
        //console.log("VARIAS OPCIONES: productId, name, price ===> ", productId + " + " + name + " + " + price)
        showProductOptions(productId, name, price);
        return false;
    }
    //Nuevo producto descarga el "todos un descuento"
    $('#all_percentage_discount').val('0');

    marray = productId.split("[H]");
    mynm = marray[0];
    nid = 'orderRow_' + productId;
    var ifexr = ifexitrow(nid);
    if (ifexr == 0) {
        $('#orderTable').append('<tr id="orderRow_' + productId + '">' + '<td style="text-align:left;padding-left: 6px;">' + name.replace(/&quot;/i,
            "'") + '</td>' + '<td>' + format(price) + '</td>' + '<td id="nm_' + mynm + '">1</td>' + '<td>' + format(price) + '</td>' + '<td>' + '<button type="button" class="btn btn-primary btn-xs" onclick=\'appendProduct("' + mynm + '"); return false;\'><span class="glyphicon glyphicon-plus"></span></button>' + '<button type="button" class="btn btn-primary btn-xs" onclick=\'removeProduct("' + mynm + '"); return false;\'><span class="glyphicon glyphicon-minus"></span></button>' + '</td>' + '</tr>');

        $('#orderTable2').append('<tr id="orderRow_' + productId + '">' + '<td style="text-align:left;padding-left: 6px;">' + name.replace(/&quot;/i,
            "'") + '</td>' + '<td>' + format(price) + '</td>' + '<td id="nm2_' + mynm + '">1</td>' + '<td>' + format(price) + '</td>' + '<td>' + '<button type="button" class="btn btn-primary btn-xs" onclick=\'appendProduct("' + mynm + '"); return false;\'><span class="glyphicon glyphicon-plus"></span></button>' + '<button type="button" class="btn btn-primary btn-xs" onclick=\'removeProduct("' + mynm + '"); return false;\'><span class="glyphicon glyphicon-minus"></span></button>' + '</td>' + '</tr>');
        $("#order").animate({
            scrollTop: $('#order')[0].scrollHeight
        }, 1000);
        $("#order2").animate({
            scrollTop: $('#order2')[0].scrollHeight
        }, 1000);
    } else {}

    // re-calculate
    calculateTotal();
    $.ajax({
        url: 'ajax.php?action=login_controls',
        success: function (data) {
            if (data != "no") {
                window.location = 'index.php';
            }
        }
    });
}

function addProduct2(productId, name, price, options, mvw) {
    //console.log("ADDPRODUCT-productId, name, price, options, mvw- ==> ", productId + " | " + name + " | " + price + " | " + options + " | " + mvw);
    if (mvw = 1) {
        mmd_closed();
    }
    if (parseInt(options) > 0) {
        showProductOptions(productId, name, price);
        return false;
    }
    marray = productId.split("[H]");
    mynm = marray[0];
    nid = 'orderRow_' + productId;
    var ifexr = ifexitrow(nid);
    if (ifexr == 0) {
        $('#orderTable').append('<tr id="orderRow_' + productId + '">' + '<td style="text-align:left;padding-left: 6px;">' + name.replace(/&quot;/i,
            "'") + '</td>' + '<td>' + format(price) + '</td>' + '<td id="nm_' + mynm + '">1</td>' + '<td>' + format(price) + '</td>' + '<td>' + '<button type="button" class="btn btn-primary btn-xs" onclick=\'appendProduct("' + mynm + '"); return false;\'><span class="glyphicon glyphicon-plus"></span></button>' + '<button type="button" class="btn btn-primary btn-xs" onclick=\'removeProduct("' + mynm + '"); return false;\'><span class="glyphicon glyphicon-minus"></span></button>' + '</td>' + '</tr>');

        $('#orderTable2').append('<tr id="orderRow_' + productId + '">' + '<td style="text-align:left;padding-left: 6px;">' + name.replace(/&quot;/i,
            "'") + '</td>' + '<td>' + format(price) + '</td>' + '<td id="nm2_' + mynm + '">1</td>' + '<td>' + format(price) + '</td>' + '<td>' + '<button type="button" class="btn btn-primary btn-xs" onclick=\'appendProduct("' + mynm + '"); return false;\'><span class="glyphicon glyphicon-plus"></span></button>' + '<button type="button" class="btn btn-primary btn-xs" onclick=\'removeProduct("' + mynm + '"); return false;\'><span class="glyphicon glyphicon-minus"></span></button>' + '</td>' + '</tr>');
        $("#order").animate({
            scrollTop: $('#order')[0].scrollHeight
        }, 1000);
        $("#order2").animate({
            scrollTop: $('#order2')[0].scrollHeight
        }, 1000);
        calculateTotal();
        //nucar();
        //$("#searchBar").val('');
        //$("#searchBar").focus();
        $.ajax({
            url: 'ajax.php?action=login_controls',
            success: function (data) {
                if (data != "no") {
                    window.location = 'index.php';
                }
            }
        });

    }
}

function addProduct_b(productId, name, price, options, mvw) {
    //*//console.log("ADDPRODUCT-productId, name, price, options, mvw- ==> ", productId + " | " + name + " | " + price + " | " + options + " | " + mvw);
    if (mvw = 1) {
        mmd_closed();
    }
    if (parseInt(options) > 0) {
        showProductOptions(productId, name, price);
        return false;
    }
    marray = productId.split("[H]");
    mynm = marray[0];
    nid = 'orderRow_' + productId;
    var ifexr = ifexitrow2(nid);
    if (ifexr == 0) {
        $('#orderTable').append('<tr id="orderRow_' + productId + '">' + '<td style="text-align:left;padding-left: 6px;">' + name.replace(/&quot;/i,
            "'") + '</td>' + '<td>' + format(price) + '</td>' + '<td id="nm_' + mynm + '">1</td>' + '<td>' + format(price) + '</td>' + '<td>' + '<button type="button" class="btn btn-primary btn-xs" onclick=\'appendProduct("' + mynm + '"); return false;\'><span class="glyphicon glyphicon-plus"></span></button>' + '<button type="button" class="btn btn-primary btn-xs" onclick=\'removeProduct("' + mynm + '"); return false;\'><span class="glyphicon glyphicon-minus"></span></button>' + '</td>' + '</tr>');

        $('#orderTable2').append('<tr id="orderRow_' + productId + '">' + '<td style="text-align:left;padding-left: 6px;">' + name.replace(/&quot;/i,
            "'") + '</td>' + '<td>' + format(price) + '</td>' + '<td id="nm2_' + mynm + '">1</td>' + '<td>' + format(price) + '</td>' + '<td>' + '<button type="button" class="btn btn-primary btn-xs" onclick=\'appendProduct("' + mynm + '"); return false;\'><span class="glyphicon glyphicon-plus"></span></button>' + '<button type="button" class="btn btn-primary btn-xs" onclick=\'removeProduct("' + mynm + '"); return false;\'><span class="glyphicon glyphicon-minus"></span></button>' + '</td>' + '</tr>');
        $("#order").animate({
            scrollTop: $('#order')[0].scrollHeight
        }, 1000);
        $("#order2").animate({
            scrollTop: $('#order2')[0].scrollHeight
        }, 1000);
    }
    calculateTotal();
}


function nucar() {
    var total = 0;
    // iterate table rows
    $('#orderTable tr').each(function () {
        var number = parseInt($(this).find("td").eq(2).html());
        number = isNaN(number) ? 0 : parseInt(number);
        total = total + number;
    });
    $('#nucar').text(total);
}

function ifexitrow(nfil) {
    var res = 0;
    $("#orderTable tr").each(function () {
        var oID = $(this).attr("id");
        if (oID === nfil) {
            var currentTottal = $(this).find("td").eq(2).html();
            msum = parseInt(currentTottal) + 1;
            $(this).find("td").eq(2).html(msum);
            res = 1;
        }
    });
    //console.log("RES ==> ", res);
    return res
}

function ifexitrow2(nfil) {
    var res = 0;
    $("#orderTable tr").each(function () {
        var oID = $(this).attr("id");
        if (oID === nfil) {
            $(this).find("td").eq(2).html('1');
            res = 1;
        }
    });
    //console.log("RES ==> ", res);
    return res
}

/**
 * Show options for product
 *
 * Retrieve product options with Ajax call
 * @param int productId Id of product
 */
function showProductOptions(productId, name, price) {
    //console.log("showProductOptions(productId, name, price)==> ", productId + " | "  + name + "| " + price);
    $('#prodName').val(name);
    var params = {
        value: productId
    };
    $.ajax({
        url: 'ajax.php?action=productOptions&' + $.param(params),
        success: function (data) {
            //console.log("DATA ==304====> ", data);			
            html = data;
            title = "Opciones de producto"; //lang_prodOption;
            midir = "#optionForm";
            mfoot = mfoot = '<button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button><button type="button" class="btn btn-primary" onclick="midir = $(midir).submit()">Continuar</button>';
            width = "2";
            mtype = '';
            mmd(html, title, mfoot, width, mtype);
        }
    });
}
/**
 * Remove a product from table
 * @param int productId Id of product to add
 */
function removeProduct(nm) {
    mid = "nm_" + nm;
    mid2 = "nm2_" + nm;
    var currentTottal = parseInt($("#" + mid).text());
    currentTottal = currentTottal - 1;
    if (currentTottal > 0) {
        // still one or more items available after subtraction
        $("[id=" + mid + "]").text(currentTottal);
        $("[id=" + mid2 + "]").text(currentTottal);
    } else {
        next_row_discount_delete(mid);
        //console.log("PRIMER ID ==> ", mid);
        // remove last item : remove row
        $("[id=" + mid + "]").closest("tr").remove();
        $("[id=" + mid2 + "]").closest("tr").remove();
    }

    // re-calculate
    calculateTotal();
	mfocus_search();
}

/**
 * Get id from the next row of table y si contiene '_dis_' (es descuento) la borra.
 * @param string $id_row, id of row previous to the sought
 * Return: id of searched row
 */
function next_row_discount_delete(id_row) {
    //console.log("Id de entrada ==>",  id_row);
    var msee = 0;
    var mid = '0';
    var cuen = 0;
    $('#orderTable tr').each(function () {
        if (cuen > 0) {
            //console.log("mid =====> ", mid + " | " + $(this).attr('id') + " | " +  msee )
            if (msee == 1) {
                mid = $(this).attr('id');
                msee = 0;
                //console.log("SIGUIENTE ID ==> ", mid);
                //return false;
            }
            previousprevious_id = $(this).attr('id');
            var prev = previousprevious_id.split("_");
            var irow = id_row.split("_");
            if (prev['1'] == irow['1']) {
                msee = 1;
            }
        }
        cuen += 1;
    });
    //console.log("mid ===> ", mid)
    var buscar = mid.search('_dis_');
    //console.log("buscar ===> ", buscar + " | " + (buscar > -1))
    if (buscar > -1) {
        //console.log("Borrar fila en 336  ==> ", '#' + mid);
        $('#' + mid).remove();
    }

}


function appendProduct(nm) {
    mid = "nm_" + nm;
    mid2 = "nm2_" + nm;
    var currentTottal = parseInt($("#" + mid).text());
    $("[id=" + mid + "]").text(currentTottal + 1);
    $("[id=" + mid2 + "]").text(currentTottal + 1);
    // re-calculate
    calculateTotal();
	mfocus_search();
}

function calculateTotal() {
    all_discounted();
    var rows = 0;
    var total = 0;
    var percent = 0;
    $('#orderTable tr').each(function () { // iterate table rows
        if (rows) {
        //console.log("($(this).attr('id') ==396===> ", $(this).attr('id'));
            if ($(this).attr('id').indexOf('dis') > 0) { //discount row
                if ($(this).attr('id') == "orderRow_dis_percent") { // percentual discount																			
                    //console.log(" percentual discount ==> ");                   
                    var addValue = 0;
                    percent = $(this).find("td").eq(1).html();
                    percent = percent.substring(0, percent.length - 2); //(porcentaje de descuento)										
                    if (percent > 0) {
                        if (total > 0) {
                            var discountSum = -Math.round(total * (percent / 100));
                        } else {
                            var discountSum = 0;
                        }
                        $('#orderRow_dis_percent').find("td").eq(3).html(format("-" + discountSum));
                        $('#orderRow_dis_percent_2').find("td").eq(3).html(format("-" + discountSum));
                        var addValue = discountSum;
                    }  
                } else if ($(this).attr('id').indexOf('return_ticket')) { // ticket discount	  				
                    var addValue = clean_number($(this).find("td").eq(3).html(), true);
//console.log("CLEAN_NUMBER ==414===> ", addValue);
                } else { // normal discount 	                   	
                    var addValue = clean_number($(this).find("td").eq(3).html(), true);
                }
            } else { // product row               
                var price = clean_number($(this).find("td").eq(1).html(), true);
                var number = parseInt($(this).find("td").eq(2).html());
                var addValue = (price * number);
//console.log("PRICE | NUMBER | ADDVALUE ===247===> ", price + " | " + number + " | " + addValue );
//console.log("ADDVALUE | format(addValue) ===247===> ", addValue + " | " + format(addValue));
                var rowIndex = $('#orderTable tr').index(this);
                $($('#orderTable2').find('tr')[rowIndex]).children('td')[2].innerHTML = number;
                $($('#orderTable2').find('tr')[rowIndex]).children('td')[3].innerHTML = format(addValue);
            }
            //console.log("ADDVALUE ===432=calculateTotal===> ", addValue);
            //console.log("FORMAT---ADDVALUE ===433=calculateTotal===> ",format(addValue));
            $(this).find("td").eq(3).html(format(addValue));
			total = (Math.round(total * 100) / 100).toFixed(2);
			//console.log("total - addValue ===433==> ", total + " - " + addValue);
            total = (total * 1) + (addValue * 1);
        }
        rows++;
    });

    $('#orderTable2 tr').each(function () {
        // product row
        var price = clean_number($(this).find("td").eq(1).html(), true);
        var number = parseInt($(this).find("td").eq(2).html());
        var addValue = (price * number);

        rows++;
    });
    $("#orderTotal2").html(format(total))
    $("#orderTotal2_2").html(format(total))
}
/**
 * Get discount for order
 **/

function discount() {
    var strVar = "";
    strVar += "<div class=\"container-fluid\">";
    strVar += "    <div class=\"row\" style=\"padding: 10px 1px;\">";
    strVar += "        <div class=\"col-sm-12\"><strong>" + lang_discount_val + "<\/strong><\/div>";
    strVar += "    <\/div>";
    strVar += "    <form class=\"form-horizontal\" onSubmit='discountAdd(); return false;'>";
    strVar += "        <div class=\"row\">";
    strVar += "            <div class=\"col-xs-3 text-right\">" + lang_amount + " (" + mysymbol() + "):<\/div>";
    strVar += "            <div class=\"col-xs-9\">";
    strVar += "                <input type='text' class=\"form-control\" id='discountValue' autocomplete='off'>";
    strVar += "            <\/div>";
    strVar += "        <\/div>";
    strVar += "        <div class=\"row  text-right\">";
    strVar += "            <div class=\"col-xs-12\" style=\"padding: 5px 15px;\">";
    strVar += "                <button type='submit' class=\"btn btn-primary\"> " + lang_add + "</button>";
    strVar += "            <\/div>";
    strVar += "        <\/div>";
    strVar += "    <\/form>";
    strVar += "    <hr\/>";

    strVar += "<div class=\"container-fluid\">";
    strVar += "    <div class=\"row\" style=\"padding: 10px 1px;\">";
    strVar += "        <div class=\"col-sm-12\"><strong>" + lang_porcentage_disc + " (%)<\/strong><\/div>";
    strVar += "    <\/div>";
    strVar += "    <form class=\"form-horizontal\" onSubmit='discountPercentAdd(); return false;'>";
    strVar += "        <div class=\"row\">";
    strVar += "            <div class=\"col-xs-3 text-right\">" + lang_percentage + ":<\/div>";
    strVar += "            <div class=\"col-xs-9\">";
    strVar += "                <input type='text' class=\"form-control\" id='dPercent' autocomplete='off'>";
    strVar += "            <\/div>";
    strVar += "        <\/div>";

    strVar += "        <div class=\"row text-right\" style=\"padding: 10px 20px 5px 0px;\">";

    strVar += "            	<strong>Aplicar a todo el carrito: <input type='checkbox' value='' id= 'ollporcent' name= 'ollporcent'></strong>";

    strVar += "        <\/div>";

    strVar += "        <div class=\"row  text-right\">";
    strVar += "            <div class=\"col-xs-12\" style=\"padding: 5px 15px;\">";
    strVar += "                <button type='submit' class=\"btn btn-primary\"> " + lang_add + "</button>";
    strVar += "            <\/div>";
    strVar += "        <\/div>";
    strVar += "    <\/form>";
    strVar += "    <hr\/>";

    strVar += "    <div class=\"row\" style=\"padding: 10px 1px;\">";
    strVar += "        <div class=\"col-sm-12\"><strong>" + lang_refund_ticquet + "<\/strong><\/div>";
    strVar += "    <\/div>";
    strVar += "    <form class=\"form-horizontal\" onSubmit='return_ticket(); return false;'>";
    strVar += "        <div class=\"row\">";
    strVar += "            <div class=\"col-xs-3 text-right\">" + lang_refund_code + ":<\/div>";
    strVar += "            <div class=\"col-xs-9\">";
    strVar += "                <input type='text' class=\"form-control\" id='code_t'  autocomplete='off'>";
    strVar += "            <\/div>";
    strVar += "        <\/div>";
    strVar += "        <div class=\"row  text-right\">";
    strVar += "            <div class=\"col-xs-12\" style=\"padding: 5px 15px;\">";
    strVar += "                <button type='submit' class=\"btn btn-primary\"> " + lang_add + "</button>";
    strVar += "            <\/div>";
    strVar += "        <\/div>";
    strVar += "    <\/form>";
    strVar += "<\/div>";

    html_d = strVar
    title = lang_discount;    
    mfoot = mfoot = '<button type="button" class="btn btn-default" data-dismiss="modal">Salir</button>'; 
    width = "2";
    mtype = '';
    mmd(html_d, title, mfoot, width, mtype);
}

function add_customer(){
    //alert("ENTRO ADD CUSTOMER 1");
    var strVar = '';
    strVar += '<div class="modal-body">\n\n';
    strVar += '<form onsubmit="customerAdd(); return false;" id="md_frm1">\n';
    strVar += '<div class="row mdal ln-s">\n';
    strVar += '<div class="col-xs-4 col-md-2">Buscar cliente</div>\n';
    strVar += '<div class="col-xs-8 col-md-4">\n';
    strVar += '<input type="text" id="customern" class="mdl" value="" style="width: 100% !important;">\n';
    strVar += '</div>\n';
    strVar += '<div class="col-xs-4 col-md-2 text-right"></div>\n';
    strVar += '<div class="col-xs-8 col-md-4 text-right">\n';
    strVar += '<button type="submit" class="btn btn-primary">Continuar</button>\n';
    strVar += '</div>\n';
    strVar += '</div>\n';
    strVar += '</form>\n';
    strVar += '<form onsubmit="customerAddNew(); return false;" id="md_frm2">';
    strVar += '<div class="row mdal">';
    strVar += '<div class="col-xs-4 col-md-2">Nombre</div>';
    strVar += '<div class="col-xs-8 col-md-4">';
    strVar += '<input type="text" id="customerFirstname" class="mdl" value=""';
    strVar += 'style="width: 100%;">';
    strVar += '</div>';
    strVar += '<div class="col-xs-4 col-md-2">Apellido</div>\n';
    strVar += '<div class="col-xs-8 col-md-4 text-right">\n';
    strVar += '<input type="text" id="customerLastname" class="mdl" value="">\n';
    strVar += '</div>\n';
    strVar += '</div>\n';
    strVar += '<div class="row mdal">\n';
    strVar += '<div class="col-xs-4 col-md-2">Id Fiscal</div>\n';
    strVar += '<div class="col-xs-8 col-md-4">\n';
    strVar += '<input type="text" id="customerIdtax" class="mdl" value="">\n';
    strVar += '</div>\n';
    strVar += '<div class="col-xs-4 col-md-2">Dirección</div>\n';
    strVar += '<div class="col-xs-8 col-md-4 text-right">\n';
    strVar += '<input type="text" id="customerAddress" class="mdl" value="">\n';
    strVar += '</div>\n';
    strVar += '</div>\n';
    strVar += '<div class="row mdal">\n';
    strVar += '<div class="col-xs-4 col-md-2">Ciudad</div>\n';
    strVar += '<div class="col-xs-8 col-md-4">\n';
    strVar += '<input type="text" id="customerLastCity" class="mdl" value="">\n';
    strVar += '</div>\n';
    strVar += '<div class="col-xs-4 col-md-2">Codigo postal</div>\n';
    strVar += '<div class="col-xs-8 col-md-4 text-right">\n';
    strVar += '<input type="text" id="customerPostcode" class="mdl" value="">\n';
    strVar += '</div>\n';
    strVar += '</div>\n';
    strVar += '<div class="row mdal">\n';
    strVar += '<div class="col-xs-4 col-md-2">Correo-e</div>\n';
    strVar += '<div class="col-xs-8 col-md-4">\n';
    strVar += '<input type="text" id="customerEmail" class="mdl" value="">\n';
    strVar += '</div>\n';
    strVar += '<div class="col-xs-4 col-md-2">Telefono</div>\n';
    strVar += '<div class="col-xs-8 col-md-4 text-right">\n';
    strVar += '<input type="text" id="customerPhone" class="mdl" value="">\n';
    strVar += '</div>\n';
    strVar += '</div>\n';
    strVar += '<div class="row mdal">\n';
    strVar += '<div class="col-xs-12 col-md-12 text-right">\n';
    strVar += '<button type="submit" class="btn btn-primary">Continuar</button>\n';
    strVar += '</div>\n';
    strVar += '</div>\n';
    strVar += '</form>\n';
    strVar += '<div class="alert alert-danger" id="alert-crea-cus"\n';
    strVar += 'style="display: none"></div>\n';

    strVar += "<script>";
    strVar += "var input = document.getElementById(\"customern\");\n";
    strVar += "       var awesomplete = new Awesomplete(input, {\n";
    strVar += "         minChars: 1,\n";
    strVar += "         autoFirst: true\n";
    strVar += "        });\n";
    strVar += "        //awesomplete.list = [\"Ada\", \"Java\", \"JavaScript\", \"Brainfuck\", \"LOLCODE\", \"Node.js\", \"Ruby on Rails\"];\n";
    strVar += "        $(\"#customern\").on(\"keyup\", function(){\n";
    strVar += "            //alert(\"==>\");\n";
    strVar += "            myf = $(\"#customern\").val();\n";
    strVar += "            //console.log(\"MYF ==> \", myf);  \n";
    strVar += "            $.ajax({\n";
    strVar += "                url: 'ajax.php?action=searchCustomer&term=' + myf,\n";
    strVar += "                type: 'GET',\n";
    strVar += "                dataType: 'json',\n";
    strVar += "                success: function (data) { \n";
    strVar += "                    //console.log(\"DATA ==> \", data)\n";
    strVar += "                    var list = [];\n";
    strVar += "                    var mv = \"\";\n";
    strVar += "                    $.each(data, function(key, value) {\n";
    strVar += "                    mv = value.value + \" (\" + value.id + \") \"; \n";
    strVar += "                    list.push(mv)\n";
    strVar += "                    });\n";
    strVar += "                    //console.log(\"LIST ==> \", list)\n";
    strVar += "                    awesomplete.list = list;\n";
    strVar += "                }\n";
    strVar += "            });\n";
    strVar += "        });\n";
    strVar += "<\/script>\n";

    strVar += "<hr/>\n"; 
    strVar += "<!--El pié -->\n"; 
    strVar += "</div>\n"; 
    strVar += "<div class='modal-foote'>\n"; 
    /*/strVar += "<button type='button' class='btn btn-default' onClick='mmd_closed2();'>Close</button>\n"; */
    /*strVar += "</div>"; */
    //console.log("CODIGO ===> ", strVar);
    
    html_d = strVar;
    title = 'Cliente'; //lang_discount;
    mfoot = mfoot = '<button type="button" class="btn btn-default" data-dismiss="modal">Salir</button></div>'; 
    width = "2";
    mtype = '';
    mmd(html_d, title, mfoot, width, mtype);
}

function mysymbol() {
    var symbol_right = $('#symbol_right').val();
    var symbol_left = $('#symbol_left').val();
    var mysymbol = symbol_right + symbol_left;
    return mysymbol;
}

function discountAdd() {
    discountNr++;
    var value = $('#discountValue').val();
    value = clear_nm(value);
    var rept = $('#decimal_place').val();
    var repti = parseInt(rept);
    var trid = $('#orderTable tr:last').attr('id');
    if (trid === undefined) {

    } else {
        var buscar = trid.search('_dis_');
        if (buscar == -1) {
            var prod_val = $('#orderTable tr:last').find("td").eq(3).html();
            prod_val = clear_nm(prod_val); //valor limpio del producto
            //console.log("prod_val ==> ", prod_val);
            prod_val2 = clearNum_symbol(prod_val);
            //console.log("prod_val2 ==> ", prod_val2);	

            if (parseFloat(value) < parseFloat(prod_val2)) {
                midescuent = format(parseFloat("-" + value).toFixed(repti));
            } else {
                midescuent = "-" + prod_val;
            }
            //console.log("midescuent ==> ", midescuent);
            // add row to order
            $('#orderTable').append('<tr id="orderRow_dis_' + discountNr + '">' + '<td class="text-left">' + lang_discount + '</td>' + '<td></td>' + '<td>1</td>' + '<td>' + midescuent + '</td>' + '<td><button type="button" class="btn btn-primary btn-xs" onclick=\'deletediscount("dis_' + discountNr + '"); return false;\'><span class="glyphicon glyphicon-minus"></span></button></td>' + '</tr>');
            $('#orderTable2').append('<tr id="orderRow_dis_' + discountNr + '">' + '<td class="text-left">' + lang_discount + '</td>' + '<td></td>' + '<td>1</td>' + '<td>' + midescuent + '</td>' + '<td><button type="button" class="btn btn-primary btn-xs" onclick=\'deletediscount("dis_' + discountNr + '"); return false;\'><span class="glyphicon glyphicon-minus"></span></button></td>' + '</tr>');
            // re-calculate
            calculateTotal();
        }
        mmd_closed();
    }
}

function deletediscount(mid) {
    //console.log("Borrar fila en 560  ==>Borrar decuento ==> ", mid);
    if (mid.indexOf("orderRow_") != -1) { //encontrdo orderTable}
        $("#orderTable #" + mid).remove();
        $("#orderTable2 #" + mid).remove();
    } else {
        $("#orderTable #orderRow_" + mid).remove();
        $("#orderTable2 #" + "orderRow_" + mid).remove();
    }
    $("#all_percentage_discount").val(0);
    calculateTotal();

}


function discountPercentAdd() {
    discountNr++;
    var percen = $('#dPercent').val();
    percen = clear_nm(percen);
    var rept = $('#decimal_place').val();
    var repti = parseInt(rept);
    var trid = $('#orderTable tr:last').attr('id');
    //console.log("discountPercentAdd  562 percen ==> ", percen);	//#######################################
    //var nFilas = $("#orderTable tr").length;
    //console.log("NUMERO DE FILAS_ANTES_INI ===> ", nFilas);
    if (trid === undefined) {

    } else {
        var buscar = trid.search('_dis_');
        if (buscar == -1) {
            var prod_val = $('#orderTable tr:last').find("td").eq(3).html();
            prod_val = clear_nm(prod_val); //valor limpio del producto
            //console.log("prod_val ==> ", prod_val);//############################################################
            prod_val2 = clearNum_symbol(prod_val);
            //console.log("prod_val2 ==> ", prod_val2);	//#######################################//################
            value = (prod_val2 * percen) / 100;
            //console.log("value ==> ", value);//###################################################################
            if (value < prod_val2) {
                midescuent = format(parseFloat("-" + value).toFixed(repti));
            } else {
                midescuent = "-" + prod_val;
            }
            // add row to order
            $('#orderTable').append('<tr id="orderRow_dis_' + discountNr + '">' + '<td class="text-left">' + lang_discount + '</td>' + '<td>-' + percen + '%</td>' + '<td>1</td>' + '<td>' + midescuent + '</td>' + '<td><button type="button" class="btn btn-primary btn-xs" onclick=\'deletediscount("dis_' + discountNr + '"); return false;\'><span class="glyphicon glyphicon-minus"></span></button></td>' + '</tr>');
            $('#orderTable2').append('<tr id="orderRow_dis_' + discountNr + '">' + '<td class="text-left">' + lang_discount + '</td>' + '<td>-' + percen + '%</td>' + '<td>1</td>' + '<td>' + midescuent + '</td>' + '<td><button type="button" class="btn btn-primary btn-xs" onclick=\'deletediscount("dis_' + discountNr + '"); return false;\'><span class="glyphicon glyphicon-minus"></span></button></td>' + '</tr>');
            // re-calculate		    
        }
        var micheck = $('#ollporcent').is(':checked');
        if (micheck) {
            $("#all_percentage_discount").val(percen);
            //console.log("discountPercentAdd  594 all_percentage_discount ==> ", $("#all_percentage_discount").val());
            //all_discounted();			
        }
        //console.log("NUENA FILA ===> ", discountNr + ' | ' + lang_discount + ' | ' +  percen + ' | ' + midescuent );
        //var nFilas = $("#orderTable tr").length;
        //console.log("NUMERO DE FILAS_ANTES_FIN ===> ", nFilas);
        calculateTotal();
        mmd_closed();
    }
}

function all_discounted() {
    porcent = $("#all_percentage_discount").val();
    //console.log("all_discounted  606 all_percentage_discount ==> ", $("#all_percentage_discount").val());	
    if (porcent <= 0) {
        return
    }
    var rows = 0;
    var total = 0;
    var rept = $('#decimal_place').val();
    var repti = parseInt(rept);
    var val_pro = 0;
    var nFilas = $("#orderTable tr").length;
    $('#orderTable tr').each(function () { // iterate table rows
        if ($(this).attr('id') != undefined) {
            yid = $(this).attr('id') //console.log("YID===> ", yid );
                // //console.log("YID =======================> ", yid);
            var mtz = yid.split('_');
            if (mtz[1] == 'dis') { //Descuento 					
                if ((val_pro > 0) && (mtz[2] != "return")) { //el anterior es un producto. Este es un descuento ===> validar o cambiar.	
                    descuento = (val_pro * porcent) / 100;
                    descuento = format(parseFloat("-" + descuento).toFixed(repti));
                    $(this).find("td").eq(0).html(lang_discount);
                    $(this).find("td").eq(1).html("-" + porcent + "%");
                    $(this).find("td").eq(2).html('1');
                    $(this).find("td").eq(3).html(descuento);
                    //*************************************************************************
                    //$("#orderTable2  #" + yid).find("td").eq(0).html(lang_discount);
                    //$("#orderTable2  #" + yid).find("td").eq(1).html("-" + porcent + "%");
                    //$("#orderTable2  #" + yid).find("td").eq(2).html('1');
                    //$("#orderTable2  #" + yid).find("td").eq(3).html(descuento);
                    // //console.log("0º) ADD_TR 1 | id  ==============> ", "#orderTable2  #" + yid);
                    //***************************************************************************						
                    val_pro = 0;
                } else { //Anteriro es o tique devolución o decuento precio (absoluto o %)
                    if (val_pro > 0) {
                        discountNr++;
                        descuento = (val_pro * porcent) / 100;
                        descuento = format(parseFloat("-" + descuento).toFixed(repti));
                        adtr1 = '<tr id="orderRow_dis_' + discountNr + '">' + '<td class="text-left">' + lang_discount + '</td>' + '<td>-' + porcent + '%</td>' + '<td>1</td>' + '<td>' + descuento + '</td>' + '<td><button type="button" class="btn btn-primary btn-xs" onclick=\'deletediscount("dis_' + discountNr + '"); return false;\'><span class="glyphicon glyphicon-minus"></span></button></td>' + '</tr>';
                        //adtr2 = '<tr id="orderRow_dis_' + discountNr + '">' + '<td class="text-left">' + lang_discount + '</td>' + '<td>' + porcent + '%</td>' + '<td>1</td>' + '<td>' + '-' +  descuento + '</td>' + '<td><button type="button" class="btn btn-primary btn-xs" onclick=\'deletediscount("dis_' + discountNr + '"); return false;\'><span class="glyphicon glyphicon-minus"></span></button></td>' + '</tr>';
                        //console.log("1º) ADD_TR 2 ==============> ", adtr2);					  
                        $(this).before(adtr1);
                        // $("#orderTable2  #" + yid).before(adtr2);	
                        use_return = 1;
                    }
                }
                val_pro = 0;
            } else { //producto
                if (val_pro > 0) { //el anterior es un producto. Añadir descuento por encima 											
                    discountNr++;
                    descuento = (val_pro * porcent) / 100;
                    descuento = format(parseFloat("-" + descuento).toFixed(repti));
                    adtr1 = '<tr id="orderRow_dis_' + discountNr + '">' + '<td class="text-left">' + lang_discount + '</td>' + '<td>-' + porcent + '%</td>' + '<td>1</td>' + '<td>' + descuento + '==</td>' + '<td><button type="button" class="btn btn-primary btn-xs" onclick=\'deletediscount("dis_' + discountNr + '"); return false;\'><span class="glyphicon glyphicon-minus"></span></button></td>' + '</tr>';
                    //adtr2 = '<tr id="orderRow_dis_' + discountNr + '">' + '<td class="text-left">' + lang_discount + '</td>' + '<td>' + porcent + '%</td>' + '<td>1</td>' + '<td>'  +  descuento + '</td>' + '<td><button type="button" class="btn btn-primary btn-xs" onclick=\'deletediscount("dis_' + discountNr + '"); return false;\'><span class="glyphicon glyphicon-minus"></span></button></td>' + '</tr>';
                    //console.log("2º) ADD_TR 2 ==============> ", adtr2);					

                    $(this).before(adtr1);
                    //$("#orderTable2  #" + yid).before(adtr2);
                }
                val_prod_str = $(this).find("td").eq(1).html();
                val_pro1 = clear_nm(val_prod_str);
                val_pro = Number(clearNum_symbol(val_pro1));
                nmrs = Number($(this).find("td").eq(2).html());
                val_pro = val_pro * nmrs;
            }
        }
    });
    if (val_pro > 0) { //el anterior es un producto. Añadir descuento por encima
        discountNr++;
        descuento = (val_pro * porcent) / 100;
        descuento = format(parseFloat("-" + descuento).toFixed(repti));
        var mnme = discountNr;
        adtr1 = '<tr id="orderRow_dis_' + mnme + '">' + '<td class="text-left">' + lang_discount + '</td>' + '<td>' + porcent + '%</td>' + '<td>1</td>' + '<td>' + descuento + '</td>' + '<td><button type="button" class="btn btn-primary btn-xs" onclick=\'deletediscount("dis_' + discountNr + '"); return false;\'><span class="glyphicon glyphicon-minus"></span></button></td>' + '</tr>';
        //adtr2 = '<tr id="orderRow_dis_' + mnme + '">' + '<td class="text-left">' + lang_discount + '</td>' + '<td>' + porcent + '%</td>' + '<td>1</td>' + '<td>' + descuento + '</td>' + '<td><button type="button" class="btn btn-primary btn-xs" onclick=\'deletediscount("dis_' + discountNr + '"); return false;\'><span class="glyphicon glyphicon-minus"></span></button></td>' + '</tr>';
        //console.log("3º) ADD_TR 2 ==============> ", adtr2);		
        $("#orderTable").append(adtr1);
        //$("#orderTable2").append(adtr2); 
        val_pro = 0;
    }
    var nFilas = $("#orderTable tr").length;
    //@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
    var rows = 0;
    var total = 0;
    var rept = $('#decimal_place').val();
    var repti = parseInt(rept);
    var val_pro = 0;
    var nFilas = $("#orderTable tr").length;
    $('#orderTable2 tr').each(function () { // iterate table rows
        if ($(this).attr('id') != undefined) {
            yid = $(this).attr('id') //console.log("YID===> ", yid );
                //console.log("YID =======================> ", yid);
            var mtz = yid.split('_');
            if (mtz[1] == 'dis') { //Descuento 					
                if ((val_pro > 0) && (mtz[2] != "return")) { //el anterior es un producto. Este es un descuento ===> validar o cambiar.	
                    descuento = (val_pro * porcent) / 100;
                    descuento = format(parseFloat("-" + descuento).toFixed(repti));
                    $(this).find("td").eq(0).html(lang_discount);
                    $(this).find("td").eq(1).html("-" + porcent + "%");
                    $(this).find("td").eq(2).html('1');
                    $(this).find("td").eq(3).html(descuento);
                    //*************************************************************************
                    //$("#orderTable2  #" + yid).find("td").eq(0).html(lang_discount);
                    //$("#orderTable2  #" + yid).find("td").eq(1).html("-" + porcent + "%");
                    //$("#orderTable2  #" + yid).find("td").eq(2).html('1');
                    //$("#orderTable2  #" + yid).find("td").eq(3).html(descuento);
                    // //console.log("0º) ADD_TR 1 | id  ==============> ", "#orderTable2  #" + yid);
                    //***************************************************************************						
                    val_pro = 0;
                } else { //Anteriro es o tique devolución o decuento precio (absoluto o %)
                    if (val_pro > 0) {
                        discountNr++;
                        descuento = (val_pro * porcent) / 100;
                        descuento = format(parseFloat("-" + descuento).toFixed(repti));
                        adtr1 = '<tr id="orderRow_dis_' + discountNr + '">' + '<td class="text-left">' + lang_discount + '</td>' + '<td>-' + porcent + '%</td>' + '<td>1</td>' + '<td>' + descuento + '</td>' + '<td><button type="button" class="btn btn-primary btn-xs" onclick=\'deletediscount("dis_' + discountNr + '"); return false;\'><span class="glyphicon glyphicon-minus"></span></button></td>' + '</tr>';
                        //adtr2 = '<tr id="orderRow_dis_' + discountNr + '">' + '<td class="text-left">' + lang_discount + '</td>' + '<td>' + porcent + '%</td>' + '<td>1</td>' + '<td>' + '-' +  descuento + '</td>' + '<td><button type="button" class="btn btn-primary btn-xs" onclick=\'deletediscount("dis_' + discountNr + '"); return false;\'><span class="glyphicon glyphicon-minus"></span></button></td>' + '</tr>';
                        //console.log("1º) ADD_TR 2 ==============> ", adtr2);					  
                        $(this).before(adtr1);
                        // $("#orderTable2  #" + yid).before(adtr2);	
                        use_return = 1;
                    }
                }
                val_pro = 0;
            } else { //producto
                if (val_pro > 0) { //el anterior es un producto. Añadir descuento por encima 											
                    discountNr++;
                    descuento = (val_pro * porcent) / 100;
                    descuento = format(parseFloat("-" + descuento).toFixed(repti));
                    adtr1 = '<tr id="orderRow_dis_' + discountNr + '">' + '<td class="text-left">' + lang_discount + '</td>' + '<td>-' + porcent + '%</td>' + '<td>1</td>' + '<td>' + descuento + '</td>' + '<td><button type="button" class="btn btn-primary btn-xs" onclick=\'deletediscount("dis_' + discountNr + '"); return false;\'><span class="glyphicon glyphicon-minus"></span></button></td>' + '</tr>';
                    //adtr2 = '<tr id="orderRow_dis_' + discountNr + '">' + '<td class="text-left">' + lang_discount + '</td>' + '<td>' + porcent + '%</td>' + '<td>1</td>' + '<td>'  +  descuento + '</td>' + '<td><button type="button" class="btn btn-primary btn-xs" onclick=\'deletediscount("dis_' + discountNr + '"); return false;\'><span class="glyphicon glyphicon-minus"></span></button></td>' + '</tr>';
                    //console.log("2º) ADD_TR 2 ==============> ", adtr2);					

                    $(this).before(adtr1);
                    //$("#orderTable2  #" + yid).before(adtr2);
                }
                val_prod_str = $(this).find("td").eq(1).html();
                val_pro1 = clear_nm(val_prod_str);
                val_pro = Number(clearNum_symbol(val_pro1));
                nmrs = Number($(this).find("td").eq(2).html());
                val_pro = val_pro * nmrs;
            }
        }
    });
    if (val_pro > 0) { //el anterior es un producto. Añadir descuento por encima
        discountNr++;
        descuento = (val_pro * porcent) / 100;
        descuento = format(parseFloat("-" + descuento).toFixed(repti));
        var mnme = discountNr;
        adtr1 = '<tr id="orderRow_dis_' + mnme + '">' + '<td class="text-left">' + lang_discount + '</td>' + '<td>' + porcent + '%</td>' + '<td>1</td>' + '<td>' + descuento + '</td>' + '<td><button type="button" class="btn btn-primary btn-xs" onclick=\'deletediscount("dis_' + discountNr + '"); return false;\'><span class="glyphicon glyphicon-minus"></span></button></td>' + '</tr>';
        //adtr2 = '<tr id="orderRow_dis_' + mnme + '">' + '<td class="text-left">' + lang_discount + '</td>' + '<td>' + porcent + '%</td>' + '<td>1</td>' + '<td>' + descuento + '</td>' + '<td><button type="button" class="btn btn-primary btn-xs" onclick=\'deletediscount("dis_' + discountNr + '"); return false;\'><span class="glyphicon glyphicon-minus"></span></button></td>' + '</tr>';
        //console.log("3º) ADD_TR 2 ==============> ", adtr2);		
        $("#orderTable").append(adtr1);
        //$("#orderTable2").append(adtr2); 
        val_pro = 0;
    }
    var nFilas = $("#orderTable2 tr").length;
}

function return_ticket() {
    //console.log(" return_ticket ==> xxx");
    m_code = $('#code_t').val();
    //console.log("CODE1  =========> ", m_code)
    m_code = m_code.replace(/'/g, "-");
    //console.log("CODE2  =========> ", m_code)	
    discountqNr = discountNr + 1;
    if ($('#orderRow_dis_return_ticket_' + m_code).length > 0) {
        alert("Este codigo ya se esta utilizando en el carrito");
        return '';
    }
    $.ajax({
        url: 'ajax.php?action=return_t&code=' + m_code,
        success: function (data) {
            rs = data.split("|"); //ejemplo de tique de devoluciÃ³n ---> R|inv-74-12|75.460001232|75.46
            str = rs[0];
            str = str.trim();
            //console.log("Si error ==>", str);
            if (str == "ERROR") {
                //console.log("Error ==>Invalido");
                html = "<div>" + rs[1] + "<\/div>";
                html += "<div style='textalign:center;''>"
                title = "Tique invalido";
                midir = "#optionForm";
                width = "mcm";
                mtype = '';
                mmd(html, title, mfoot, width, mtype);
            } else {
                //console.log("No es Error ==>valido");
                var vv = row_table_tiquet(rs[0], rs[1], rs[2]);
            }
        }
    })
}

function row_table_tiquet(tip, cod, value_ticket) {
//console.log("value_ticket INI ==823===>", value_ticket);
    if (tip == "ERROR") {
        alert("Este codigo ya se esta utilizando en el carrito");
        return '';
    } else {
        if (tip == "D") {
            var trid = "orderRow_dis_descuent_ticket_" + cod;
            var todel = "dis_tique";
            var ttl = lang_tck_discount;
        } else { //Tique de devolucion    tip = "R"
            var trid = "orderRow_dis_return_ticket_" + cod;
            var todel = "dis_return_tique";
            var ttl = lang_tck_return;
        }
        //var total_car = clearNum($('#orderTotal2').text()) / 100;	
        var total_car = clean_number($('#orderTotal2').text(), true);
 //console.log("Precio del que hay que descontar  ==839===>", total_car);       
        if (parseInt(total_car) > parseInt(value_ticket)) {
            var resul_d = value_ticket;
            var msobra = 0;
//console.log("resul_d ==843===>", resul_d);
        } else {
            var resul_d = total_car;
            var msobra = value_ticket - total_car;
            //console.log("resul_d ==847===>", resul_d);
        }
		
//console.log("msobra ==847===> ", msobra);	
        var descuento = resul_d;
 //console.log("### descuento ==850===> ", descuento);	
        var rr = parseFloat("-" + descuento).toFixed(2);
        var rept = $('#decimal_place').val();
        var repti = parseInt(rept);
        var mvl = format(parseFloat("-" + descuento).toFixed(repti));
//console.log("### mvl ==855===> ", mvl);
        //$("#dialog").dialog('close');
        $('#orderTable').append('<tr id="' + trid + '">' + '<td class="text-left">' + ttl + '</td>' + '<td></td>' + '<td></td>' + '<td>' + mvl + '</td>' + '<td><button type="button" class="btn btn-primary btn-xs" onclick=\'deletediscount("' + trid + '"); return false;\'><span class="glyphicon glyphicon-minus"></span></button></td>' + '</tr>');
        $('#orderTable2').append('<tr id="' + trid + '">' + '<td>' + ttl + '</td>' + '<td></td>' + '<td></td>' + '<td>' + mvl + '</td>' + '<td><button type="button" class="btn btn-primary btn-xs" onclick=\'deletediscount("' + trid + '"); return false;\'><span class="glyphicon glyphicon-minus"></span></button></td>' + '</tr>');
        calculateTotal();
        mmd_closed();
    }
}

function service() {
    $.ajax({
        url: 'ajax.php?action=lst_taxclass_only',
        success: function (data) {
            mtx_class = data;
            var strVar = "";
            strVar += "<div class=\"container-fluid\">";
            strVar += "    <form>";
            strVar += "            ";
            strVar += "         <div class=\"row\">";
            strVar += "            <div class=\"col-xs-3 text-right\">* " + lang_server_name + ":<\/div>";
            strVar += "            <div class=\"col-xs-9\">";
            strVar += "                <input class=\"col-xs-12\" type='text' name='serv_name' id='serv_nome' autocomplete='off' \/>";
            strVar += "            <\/div>";
            strVar += "        <\/div>           ";
            strVar += "            ";
            strVar += "        <div class=\"row\">";
            strVar += "            <div class=\"col-xs-3 text-right\">* " + lang_tax_included + ":<\/div>";
            strVar += "            <div class=\"col-xs-9\">";
            //strVar += "                <input class=\"col-xs-12\" type='text' name='serv_preci' id='serv_preci' autocomplete='off'  onkeypress='return event.charCode >= 46 && event.charCode <= 57' \/>";
            strVar += "                <input class=\"col-xs-12\" type='number'  step='any' name='serv_preci' id='serv_preci' autocomplete='off' \/>";
            strVar += "            <\/div>";
            strVar += "        <\/div>           ";
            strVar += "";
            strVar += "        <div class=\"row\">";
            strVar += "            <div class=\"col-xs-3 text-right\">" + lang_class_tax + ":<\/div>";
            strVar += "            <div class=\"col-xs-9\">";
            strVar += data;
            strVar += "            <\/div>";
            strVar += "        <\/div> ";
            strVar += "";
            strVar += "        <div class=\"row\">";
            strVar += "            <div class=\"col-xs-3 text-right\">" + lang_description + ":<\/div>";
            strVar += "            <div class=\"col-xs-9\">";
            strVar += "                <textarea class=\"col-xs-12\" name='serv_decrip' id='serv_decrip' cols='25' rows='5' style='width:98%'><\/textarea>";
            strVar += "            <\/div>";
            strVar += "        <\/div>  ";
            strVar += "";
            strVar2 = "        <div class=\"row\">";
            strVar2 += "            <div class=\"col-xs-3 text-right\"><\/div>";
            strVar2 += "            <div class=\"col-xs-9\">";
            strVar2 += "            	<button type='button' class=\"btn btn-default\"  onClick='mmd_closed()'>Cancelar<\/button>";
            strVar2 += "                <button type='button' class=\"btn btn-primary\" onClick='serviceAdd(); return false;'>" + lang_add + "<\/button>";
            strVar2 += "            <\/div>";
            strVar2 += "        <\/div> ";
            strVar2 += "  ";
            strVar2 += "    <\/form>";
            strVar2 += "<\/div>     ";
            //console.log("strVar ==> ", strVar + strVar2 );			
            html = strVar;
            title = lang_service;
            mfoot = strVar2;
            width = "2";
            mtype = '';
            mmd(html, title, mfoot, width, mtype);
        }
    });
}
/**
 * Add a new service row to the order
 */
function serviceAdd() {
    discountNr++;
    var s_name = $('#serv_nome').val(); 
	var s_name = s_name.trim();
//console.log("Longitud =925==> ", s_name.length);
    if(s_name== null || s_name.length == 0){
//console.log("ENTRO 927");
        return false
    }
    s_name = s_name.replace(/_/g, "-"); //Se pasa despues un estring con un separador que es "_"
    var precio_user = $('#serv_preci').val();
//console.log("precio_user-lenth=932==> ", precio_user.length);
    if(precio_user== null || precio_user.length == 0){
//console.log("ENTRO 934");
        return false
    }    
    var s_price2 = format_price_user(precio_user);
    s_price2_cru = clean_number(s_price2, true);
    s_price2_cru_f = format(s_price2);
    if(s_price2_cru < 0){
        alert("El precio no puede ser negativo");
        return
    }
    var s_class_t = $('#mtaxclass').val();
    var s_descrip = $('#serv_decrip').val();
    if(s_descrip == null) { s_descrip="";}
    s_descrip = s_descrip.replace(/_/g, "-"); //Se pasa despues un estring con un separador que es "_"
//console.log("DESCRIPTION ==934===> ", s_descrip)
    var rept = $('#decimal_place').val();
    var repti = parseInt(rept);
    serviceNr = 'Servicio';
    $.ajax({
        url: 'ajax.php?action=price_withtax&price2=' + s_price2_cru + '&clastax=' + s_class_t,
        success: function (data) { //devuelve precio sin iva
            price_f = data;
//console.log("price_f  ==946===> ", price_f );
            price_f = clean_number(price_f, true);
//console.log("price_f tras clean_number true ==946===> ", price_f );
            mmd_closed();
//console.log("price_f | s_price2_cru ===> ", price_f + '|' +  s_price2_cru);
            mid = "orderRow_serv_" + discountNr; //1-2:id 
            $('#orderTable').append('<tr id="' + mid + '">' + '<td class="text-left">' + s_name + '</td>' + '<td>' + s_price2_cru_f + '</td>' + '<td id="nm_1">1</td>' + '<td>' + s_price2_cru + '</td>' + '<td>' + '<button type="button" class="btn btn-primary btn-xs" onclick="serviceAdding(' + discountNr + '); return false;"><span class="glyphicon glyphicon-plus"></span></button>' + '<button type="button" class="btn btn-primary btn-xs" onclick="serviceSubtract(' + discountNr + ');return false;"><span class="glyphicon glyphicon-minus"></span></button>' + '	</td>' + '<td style="display: none;">' + price_f + "_" + s_class_t + "_" + s_descrip + '</td>' + '</tr>'); // re-calculate
            $('#orderTable2').append('<tr id="' + mid + '">' + '<td class="text-left">' + s_name + '</td>' + '<td>' + s_price2_cru + '</td>' + '<td id="nm_1">1</td>' + '<td>' + s_price2_cru + '</td>' + '<td>' + '<button type="button" class="btn btn-primary btn-xs" onclick="serviceAdding(' + discountNr + '); return false;"><span class="glyphicon glyphicon-plus"></span></button>' + '<button type="button" class="btn btn-primary btn-xs" onclick="serviceSubtract(' + discountNr + ');return false;"><span class="glyphicon glyphicon-minus"></span></button>' + '	</td>' + '<td style="display: none;">' + price_f + "_" + s_class_t + "_" + s_descrip + '</td>' + '</tr>'); // re-calculate
            calculateTotal();
            nucar();
        }
    });
}

function serviceSubtract(servicId) {
    var number = parseInt($('#orderRow_serv_' + servicId).find("td").eq(2).html());
    if (number > 1) {
        // still one or more items available after subtraction
        $('#orderRow_serv_' + servicId).find("td").eq(2).html(number - 1);
    } else {
        // remove last item : remove row	
        mid = "#" + "orderRow_serv_" + servicId;
        $(mid).remove();
    }
    calculateTotal();
}

function serviceAdding(servicId) {
    var currentTottal = parseInt($('#orderRow_serv_' + servicId).find("td").eq(2).html());
    $('#orderRow_serv_' + servicId).find("td").eq(2).html(currentTottal + 1);
    calculateTotal();
}
/**
 * Add customer to order
 **/
function customer() {
    mcm = 2;
    var orderCustomerId = $('#orderCustomerId').val();
    var orderCustomerName = $('#orderCustomerName').val();
    html = "<div>x<input class=\"awesomplete\" data-list=\"Ada, Java, JavaScript, Brainfuck, LOLCODE, Node.js, Ruby on Rails\" />y<\/div>";
    title = lang_customerAdd;
    midir = "#optionForm";
    mfoot = mfoot = '<button type="button" class="btn btn-warning" data-dismiss="modal" style="padding-left: 30px; padding-right: 30px; margin-right: 12px;">Salir</button>';
    width = mcm;
    mtype = '';
    mmd(html, title, mfoot, width, mtype);

}

/*function string_html_jv_customer() {
    strVar += "<form onsubmit=\"customerAdd(); return false;\">";
    strVar += "    <div class=\"row mdal ln-s\">";
    strVar += "        <div class=\"col-xs-4 col-md-2\"> Buscar cliente <\/div>";
    strVar += "        <div class=\"col-xs-8 col-md-4\"><input class=\"awesomplete\"   data-list=\"Ada, Java, JavaScript, Brainfuck, LOLCODE, Node.js, Ruby on Rails\" \/><\/div>"
    strVar += "        <div class=\"col-xs-4 col-md-3\"><\/div>";
    strVar += "        <div class=\"col-xs-8 col-md-3 text-right\"> <button type=\"submit\" class=\"btn btn-primary\">Continuar<\/button> <\/div>";
    strVar += "    <\/div>";
    strVar += "<\/form>";
    strVar += "";
    strVar += "<form onsubmit=\"customerAdd(); return false;\">        ";
    strVar += "    <div class=\"row mdal\">";
    strVar += "        <div class=\"col-xs-4 col-md-2\"> Nombre <\/div>";
    strVar += "        <div class=\"col-xs-8 col-md-4\"> <input type=\"text\" id=\"customerFirstname\" class=\"mdl\" value=\"\" > <\/div>";
    strVar += "        <div class=\"col-xs-4 col-md-2\">Apellido<\/div>";
    strVar += "        <div class=\"col-xs-8 col-md-4 text-right\"> <input type=\"text\" id=\"customerLastname\" class=\"mdl\" value=\"\" > <\/div>";
    strVar += "    <\/div>";
    strVar += "    ";
    strVar += "    <div class=\"row mdal\">";
    strVar += "        <div class=\"col-xs-4 col-md-2\"> Id Fiscal <\/div>";
    strVar += "        <div class=\"col-xs-8 col-md-4\"> <input type=\"text\" id=\"customerIdtax\" class=\"mdl\" value=\"\" > <\/div>";
    strVar += "        <div class=\"col-xs-4 col-md-2\">DirecciÃ³n<\/div>";
    strVar += "        <div class=\"col-xs-8 col-md-4 text-right\"> <input type=\"text\" id=\"customerAddress\" class=\"mdl\" value=\"\" > <\/div>";
    strVar += "    <\/div>";
    strVar += "    ";
    strVar += "    <div class=\"row mdal\">";
    strVar += "        <div class=\"col-xs-4 col-md-2\"> Ciudad <\/div>";
    strVar += "        <div class=\"col-xs-8 col-md-4\"> <input type=\"text\" id=\"customerLastCity\" class=\"mdl\" value=\"\" > <\/div>";
    strVar += "        <div class=\"col-xs-4 col-md-2\">Codigo postal<\/div>";
    strVar += "        <div class=\"col-xs-8 col-md-4 text-right\"> <input type=\"text\" id=\"customerAddress\" class=\"mdl\" value=\"\" > <\/div>";
    strVar += "    <\/div>";
    strVar += "        ";
    strVar += "    <div class=\"row mdal\">";
    strVar += "        <div class=\"col-xs-4 col-md-2\"> Correo-e <\/div>";
    strVar += "        <div class=\"col-xs-8 col-md-4\"> <input type=\"text\" id=\"customerEmail\" class=\"mdl\" value=\"\" > <\/div>";
    strVar += "        <div class=\"col-xs-4 col-md-2\">Telefono<\/div>";
    strVar += "        <div class=\"col-xs-8 col-md-4 text-right\"> <input type=\"text\" id=\"customerPhone\" class=\"mdl\" value=\"\" > <\/div>";
    strVar += "    <\/div>";
    strVar += "    ";
    strVar += "    <div class=\"row mdal\">";
    strVar += "        <div class=\"col-xs-12 col-md-12 text-right\"> <button type=\"button\" class=\"btn btn-primary\" onclick=\"midir = $(midir).submit()\">Continuar<\/button> <\/div>";
    strVar += "    <\/div>";
    strVar += "<\/form>";
    return strVar;
}*/

/**
 * Add new customer to order
 **/
function customerAdd() {
    var cstm = $('#customern').val();
    //console.log("CSTM ==> ", cstm);
    var myarr = cstm.split("(");
    //console.log("myarr ==> ", myarr);
    var mtitle = myarr[0].trim();
    var midar = myarr[1].split(") ");
    var mid = midar[0].trim();
    $.ajax({
        url: 'ajax.php?action=search_client_name&id=' + mid + "&name=" + mtitle,
        success: function (data) {
            //console.log("ZZZZ ==> ", data);
            if (data == '1') {
                $('#orderCustomerId').val(mid);
                $('#orderCustomerName').val(mtitle);
                $('#orderCustomer').text(cstm);
                $('#orderCustomer2').text(cstm);
                mmd_closed();
            } else {
                msg = "Ese nombre no esta registrado.";
                alert(msg);
                $('#orderCustomerId').val('');
                $('#orderCustomerName').val('');
                $('#orderCustomer').text('');
                $('#orderCustomer2').text('');
            }
        }
    });
    //console.log("myarr2 ==> ", myarr);
    mfocus_search();
}
/**
 * Save new customer to system & add to order
 **/
function customerAddNew() {
    var name = $('#customerFirstname').val() + " " + $('#customerLastname').val();
    $.ajax({
        url: 'ajax.php?action=addNewCustomer&firstname=' + $('#customerFirstname').val() + '&lastname=' + $('#customerLastname').val() + '&idtax=' + $('#customerIdtax').val() + '&email=' + $('#customerEmail').val() + '&phone=' + $('#customerPhone').val() + '&address=' + $('#customerAddress').val() + '&city=' + $('#customerLastCity').val() + '&postcode=' + $('#customerPostcode').val(),
        success: function (data) {
            if (data == "00A") {
                msg = "Ese nombre y apellido ya estÃ¡ registrado";
                $("#alert-crea-cus").text(msg);
                $("#alert-crea-cus").css("display", "block");
            } else if (data == "00B") {
                msg = "Ese correo electronico ya está registrado";
                $("#alert-crea-cus").text(msg);
                $("#alert-crea-cus").css("display", "block");
            } else {
                $('#orderCustomerId').val(data);
                $('#orderCustomerName').val(name);
                $('#orderCustomer').html(name);
                //mmd_closed();
                $("#myModal2").modal('hide')
            }
        }
    });
}
/**
 * Return a purchase
 **/
function refund() {
    var url = $("#id_fac").html();
    myno = "";
    $.ajax({
        url: 'ajax.php?action=purchasereturn&value=' + url,
        success: function (data) {
            html = data;
            title = lang_return;
            if (data == "Esa orden fue devuelta anteriormente.") {
                myno = " disabled"
            }
            midir = "#optionForm";
            mfoot = "<input class=\"btn btn-danger " + myno + "\" type='button' onClick='refundfinish2()' value='Devolver orden'><button type=\"button\" class=\"btn btn-default\" data-dismiss=\"modal\">Cerrar</button>";
            width = 2;
            mtype = '';
            mmd(html, title, mfoot, width, mtype);
        }
    });
}

function refundfinish2() {
    var mopen = "&mopen=0";
    var mproduct = "mproduct=";
    var mcount = 0;
    var opid = "-_-";
    $("input:checkbox:checked").each(function () {
        var mv = $(this).val();
//console.log("MV ===> ", mv);
        if(mv!='on'){
            var midt = $(this).attr("id");
    //console.log("midt ===> ", midt);      
            var mvr = '#x_' + midt;
            var mquant = $(mvr).val();
            var mtz = midt.split("_");
            var mid = mtz[0];
            var order_product_id = mtz[1];
            mtz.length = 0;
            if (mv == "open_x") {
                mopen = "&mopen=1";
            } else {
                if (mcount > 0) {
                    mproduct += "|" + mv + "-" + mquant + "-" + order_product_id;
                } else {
                    mproduct += mv + "-" + mquant + "-" + order_product_id;
                }
                mcount = mcount + 1
    //console.log(" mproduct ===> ",  mproduct);
            }
        }
    });
    if (mcount < 1) {
        var msg = "ERROR";
        return msg;
    } else {
        var mres = "&" + mproduct;
    }
    var razon = $("input[name='reason_x']:checked").val();
    if (parseFloat(razon)) {
        mres += "&reason=" + razon;
    } else {
        mres += "&reason=0";
    }
    var accion = $("input[name='action_x']:checked").val();
    if (parseFloat(accion) > 0) {
        mres += "&accion=" + accion;
    } else {
        mres += "&accion=0";
    }
    var estatus = $("input[name='status_x']:checked").val();
    if (parseFloat(estatus) > 0) {
        mres += "&status=" + estatus;
    } else {
        mres += "&status=0";
    }
    var mtxtarea = $('#mcoment').val();
    var mid = $('#m_id').val();
    mres += "&coment=" + mtxtarea;
    mres = mid + mres + mopen;
    mmd_closed();
    var mtd_print = $('#default_invoice').val();
    mres += "&met_print=" + mtd_print;
//console.log("MRES ===> ", mres);
    $.ajax({
        url: 'ajax.php?action=purchasereturn2&value=' + mres,
        success: function (data) {
            if (mtd_print == 'Fac' || mtd_print == 'FacSimpli') {
                mtz = data.split("|");
                if (mtz[0] == "1") {
                    alert(mtz[1])
                } else {
//console.log("MTZ =script-1207====> ", mtz);
                    $('#mi_print').empty();
                    $('#mi_print').html(mtz[1]);
                    $('#btnr').remove();
                    $('#mi_print').print();
                    $('#mi_print').empty();
                    mfocus_search();
                }
            } else {
                mtz = data.split("|");
                //console.log ("SCRIPT:JS LN 1182 ===> ", mtz);
                inbox_return(mtz[1]);
            }
        }
    });
}

function factur() {
    var tipe_factur;
    $.ajax({
        url: 'ajax.php?action=tip_factur&value=' + order_imprent,
        success: function (data) {
			mtrix = data.split('|');
            tipe_factur = mtrix[0];
			num_fac = mtrix[1];
            mi_printer = $('#default_invoice').val();
            html = '    <form>\n';
            html += '      <table style=\"width:100%\">\n';
            html += '        <tbody>\n';
            html += '          <tr>\n';
            html += '            <td>Imprimir factura</td>\n';
            html += '            <td><input type=\"text\" id=\"idreturn\" value=\"INV-s-' + num_fac + '\"></td>\n';
            html += '            <td width=\"70px\"><br /></td>\n';
            html += '          </tr>\n';
            html += '          <tr>\n';
            html += '            <td colspan=\"3\"><br /></td>\n';
            html += '          </tr>\n';
            html += '            </tr>\n';
            html += '          \n';
            html += '        <td>Impresión</td>\n';
            html += '          <td><input type=\"hidden\" id=\"paymentLines\">\n';
            if (tipe_factur == 2) {
                html += '            <input type=\"radio\" name=\"impreimfac\" value=\"fac\">\n';
                html += '            <label for=\"impreimfac\">Factura</label>\n';
                html += '            <br>\n';

                html += '            <input type=\"radio\" name=\"impreimfac\" value=\"simple\" checked>\n';
                html += '            <label for=\"impreimfac\">Factura simplificada</label>\n';
                html += '            <br>\n';
            } else {
                html += '            <input type=\"radio\" name=\"impreimfac\" value=\"fac\" checked>\n';
                html += '            <label for=\"impreimfac\">Factura</label>\n';
                html += '            <br>\n';

                html += '            <input type=\"radio\" name=\"impreimfac\" value=\"txt\">\n';
                html += '            <label for=\"impreimfac\">Factura simplificada</label></td>\n';
            }
            html += '        </tr>\n';

            html += '        <tr>\n';
            html += '           <td>Ticket regalo</td>\n';
            html += '           <td><input type="checkbox" name"mgift" id="mgift" ></td>\n';
            html += '           <td width="70px"><br /></td>\n';
            html += '        </tr>\n';


            html += '          </tbody>\n';
            html += '        \n';
            html += '      </table>\n';
            html += '    </form>\n';

            title = lang_mprint;
            midir = "#optionForm";
            mfoot = "<input class=\"btn btn-danger\" type='button' onClick='facturfinish()' value='Imprimir factura'><button type=\"button\" class=\"btn btn-default\" data-dismiss=\"modal\">Cerrar</button>";
            width = 2;
            mtype = '';
            mmd(html, title, mfoot, width, mtype);

        }
    });

}

function facturfinish() {
    //console.log("MGIFT ===========> ", $("#mgift").prop("checked") )
    var mg
    var mg1 = $("#mgift").prop("checked");
    if (mg1) {
        mg = '1';
    } else {
        mg = '0';
    }
    //console.log("MG ==========> ", mg)
    var mid = $('#idreturn').val();
    //console.log("MID REEDITAR FACTURA ===> ", mid);
    if (mid.indexOf("-s-") !== -1) {
        var res = mid.split("-s-");
        rs = res[1];
    } else {
        rs = mid;
    }

    committed = 0;
    //mg = "";

    var factura = $('#receiptPrinting').val();
//console.log("FACTUR ==1290===> ", factura);
    var metodo_df = $('#default_invoice').val();
    metodo_df = metodo_df.toLowerCase();
//console.log("METODO_DF ==1293===> ", metodo_df);
    var metodo = $('input:radio[name=impreimfac]:checked').val()
    metodo = metodo.toLowerCase();
//console.log("METODO ==1296===> ", metodo);
    if (factura == 1) {

        if (metodo == 'fac') { //factura
//console.log("order_imprent ==1300=> ", order_imprent);
            printReceiptPopup(order_imprent, committed, mg);
        } else { //ticket  metodo= 'txt'
//console.log("entro en 2");			
            if (metodo_df == 'facsimpli') { //ticket wew
                printReceiptPopup2b(order_imprent, committed, mg);
            } else { //ticket text
//console.log("entro en 3");
                printReceiptPopup3c(order_imprent, committed, mg);
            }
        }

    }
    mmd_closed();
    mmd_closed2();
}


function outofbox() {
    mft = "<button type='button' class='btn btn-default' data-dismiss='modal'>Cerrar</button>";
    mft += "<button type='button' class='btn btn-danger'  onClick='outofboxfinish(); return false;'>" + lang_outofbox3 + "</button>";
    html = "<table style='width:100%' class='table'><tr>" + "<td>" + lang_outofbox1 + "</td>" + "<td style=\"margin-bottom: 16px;\"><input type='text' id='outofbox1' value=' '></td>" + "</tr><tr>" + "<td>" + lang_outofbox2 + "</td>" + "<td><input type='text' id='outofbox2' value=' '></td>" + "</tr></table>"
    title = lang_outofbox_ttl;
    mfoot = mft;
    width = "1";
    mtype = '';
    mmd(html, title, mfoot, width, mtype);

}

function outofboxfinish() {
    var cati = $('#outofbox1').val();
    var descrip = $('#outofbox2').val();
    //mmd_closed();
    //$("#dialog").dialog('close');
    $.ajax({
        url: 'ajax.php?action=outofbox&value=' + cati + '&coment=' + descrip,
        success: function (data) {
            html = data;
            title = lang_outofbox_ttl;
            mfoot = "<button type='button' class='btn btn-primary' data-dismiss='modal'>Aceptar</button>";
            width = "1";
            mtype = '';
            //mmd(html, title, mfoot, width, mtype);
            $("#mymodal-body1").html(html);
            $("#mymodal-footer1").html(mfoot);
        }
    });

    $.ajax({
        url: 'ajax.php?action=lst_box_operations',
        success: function (data) {
            //console.log("DATE B ===> ", data)			
            $('#uno_in').html(data);
            $('#tres').html(data);
            mmargin();
            mmargin2();
            display_status_box();
        }
    });
    $("#mpantalla").val("caj");
    mnu_up_right();
}

function boxstatus() {
    $.ajax({
        url: 'ajax.php?action=boxstatus&value=0',
        success: function (data) {
            $("#dialog").dialog({
                title: lang_statusbox_ttl,
                modal: true,
                width: 400,
                height: 330
            });
        }
    });
}

function boxsession($val) {
    $.ajax({
        url: 'ajax.php?action=boxsession&value= ' + $val,
        success: function (data) {
            location.reload();
        }
    });
}

function entryinbox() {
    mmd_closed();
    mft = "<button type='button' class='btn btn-default' data-dismiss='modal'>Cerrar</button>";
    mft += "<button type='button' class='btn btn-danger'  onClick='entryinboxfinish(); return false;'>" + lang_entryinbox3 + "</button>";
    html = "<table style='width:100%' class='table'><tr>" + "<td>" + lang_entryinbox1 + "</td>" + "<td style=\"margin-bottom: 16px;\"><input type='text' id='outofbox1' value=' '></td>" + "</tr><tr>" + "<td>" + lang_entryinbox2 + "</td>" + "<td><input type='text' id='outofbox2' value=' '></td>" + "</tr></table>"
    title = lang_entryinbox_ttl;
    mfoot = mft;
    width = "1";
    mtype = '';
    mmd(html, title, mfoot, width, mtype);

}

function entryinboxfinish() {
    //console.log("HASTA LN 1439");
    var cati = $('#outofbox1').val();
    var descrip = $('#outofbox2').val();
    $.ajax({
        url: 'ajax.php?action=entryinbox&value=' + cati + '&coment=' + descrip,
        success: function (data) {
            html = data;
            title = lang_entryinbox_ttl;
            mfoot = "<button type='button' class='btn btn-primary' data-dismiss='modal'>Aceptar</button>";
            width = "1";
            mtype = '';
            $("#mymodal-body1").html(html);
            $("#mymodal-footer1").html(mfoot);
        }
    });
    //console.log("HASTA LN 1453");
    $.ajax({
        url: 'ajax.php?action=lst_box_operations',
        success: function (data) {

            $('#uno_in').html(data);
            $('#tres').html(data);
            mmargin();
            mmargin2();
            display_status_box();
        }
    });
    //console.log("HASTA LN 1466");
    $("#mpantalla").val("caj");
    mnu_up_right();
    //mmd_closed();
}

function closebox() {
    logout_app();
}

function logout_app() {
    mht = " <table border='0' cellspacing='0' cellpadding='0' style='width:100%; margin-top: 25px;'> ";
    mht += "     <tr> ";
    mht += "         <td align='center'> ";
    mht += "             <form onSubmit='closeboxfinish(); return false;'> ";
    mht += "                 <input type='submit' value='Cerrar caja' class=\"btn btn-danger\" /> ";
    mht += "             </form> ";
    mht += "         </td> ";

    mht += "         <td align='center'> ";
    mht += "             <form onSubmit='closeboxfinish2(); return false;'> ";
    mht += "                 <input type='submit' value='Imprimir' class=\"btn btn-success\" /> ";
    mht += "             </form> ";
    mht += "         </td> ";

    mht += "         <td align='center'> ";
    mht += "             <form onSubmit='closeboxcancel(); return false;'> ";
    mht += "                 <input value='Cancelar' class=\"btn btn-default\" onclick=\"mmd_closed();\" /> ";
    mht += "             </form> ";
    mht += "         </td> ";
    mht += "     </tr> ";
    mht += " </table> ";
    $.ajax({
        url: 'ajax.php?action=logout_app&value=0',
        success: function (data) {
            //console.log(data);
            html = data;

            $('#mi_print').empty();
            $('#mi_print').html(html);

            title = lang_closebox_ttl;
            mfoot = mht; // '<button type="button" class="btn btn-warning" data-dismiss="modal" style="padding-left: 30px; padding-right: 30px; margin-right: 12px;">Salir</button>';
            width = "2";
            mtype = '';
            mmd(html, title, mfoot, width, mtype);
        }
    });
}

function closeboxfinish(c_val) {
    $('#mi_print').empty();
    $.ajax({
        url: 'ajax.php?action=closeboxfinish&value=0',
        success: function () {
            window.location = 'index.php?action=logout';
            // $("#dialog").dialog('close');
            mmd_closed();
        }
    });
}

function closeboxfinish2(c_val) {
//console.log("C_VAL ==1512==> ", c_val)
    var metodo = $('#default_invoice').val();
//console.log("metodo =1513=> ", metodo);
    switch (metodo) {
        case 'Fac':
            $('#mi_print').print();
            $('#mi_print').empty();
            $.ajax({
                url: 'ajax.php?action=closeboxfinish&value=0',
                success: function () {
                    //window.location = 'index.php?action=logout';
                }
            });
            break;
        case 'FacSimpli':
            $('#mi_print').print();
            $('#mi_print').empty();
            $.ajax({
                url: 'ajax.php?action=closeboxfinish&value=0',
                success: function () {
                    //window.location = 'index.php?action=logout';
                }
            });
            break;
        default:
            $.ajax({
                url: 'ajax.php?action=print_text_close_box&value=0',
                success: function (data) {
//console.log("DATA LN 1540 ===> ", data);
                    var res = inbox_close_box(data);
                }
            });
            break;
    }
}

function closeboxcancel() {
    // close window
    $("#dialog").dialog('close');
}
/**
 * Prepare for checkout, show payment options
 **/
function checkout() {
    cuent = 0;
    $('#orderTable tr').each(function () { 
        cuent +=1;
    });
 //console.log("CUENT ===============> ", cuent);                            
    if(cuent > 1){                          
        $.ajax({
            url: 'ajax.php?action=checkout',
            dataType: "html",
            success: function (data) {
                console.log("DATA CHECKOUT(1681)=============> ", data);
                title = lang_payment;
                mfoot = "<button type='button' class='btn btn-default' data-dismiss='modal'>Cerrar</button><button id='btnPayFinis' type='button' class='btn btn-primary' onClick='checkoutAddPayment(); return false;'>Aceptar</button></form>";
                mmd(data, title, mfoot, "2", "");
                mivl = $("#orderTotal2").html();
                $("#PMAmount").val(mivl);
                mires = $("#PMAmount").val();
                mires = clearNum_symbol(mires);
                $("#PMValue").val(mires);
            }
        });
        mpay = $("input[name='PM']:checked").val();
        //Metodo de pago
        $("#PMValue").select();

        $("#dialog input[type=radio]").change(function () {
            $("#PMValue").select();
        });
        $(document).ready(function () {
            $("input[name='PM']").change(function () {
                mpay = ($(this).val());
            });
        });
    }
}
/**
 * Add new payment line to order
 */
//var mpay;
function checkoutAddPayment() {
    var vueltas = $("#PMValue").val();
    vueltas = vueltas.replace(" ", "");
    $("#PMValue").val(vueltas);
    if (clean_number($("#PMAmount").val(), true) >= 0) {
        var current = $('#paymentLines').val();
console.log("CURRENT ==1717===> ",current )	
        if (current.length > 0) current += "|";
        var lineAddition = $('input:radio[name=PM]:checked').val() + ":" + clean_number($("#PMValue").val(), true);
console.log("CURRENT | lineAddition ==1720===> ", current + " | " + lineAddition);		
        $('#paymentLines').val(current + lineAddition);
    }
    current = clean_number($("#PMAmount").val(), true);
console.log("current  ==1724==> ", current);
    var replic = clean_number($("#PMValue").val(), true);
    current -= replic;
console.log("replic  ==1727==> ", replic)
    current = clean_number(current, true);
    $("#PMValue").val(clean_number(current, true));
    $("#PMAmount").val(clean_number(current, true));
    mpay = $("input[name='PM']:checked").val();
console.log("MPAY ==1732===> ", mpay);
    //Metodo de pago
    mrst = parseInt($("#PMValue").val());
console.log("Linea 1735  ==> ", mrst );
    var tipo_factur = $("input[name='impreimfac']:checked").val();
console.log("tipo_factur UNA ==1737==> ", tipo_factur);

    if (mrst == 0) {
        $('#btnPayFinis').attr("disabled", true);
        checkoutFinish(tipo_factur);
    } else {}
    $("#PMValue").select();
}
/**
 * Order finished : checkout
 **/
var Popup;

function checkoutFinish(tipo_factur) {
    var mg
    var mg1 = $("#mgift").prop("checked");
    if (mg1) {
        mg = '1';
    } else {
        mg = '0';
    }
    //console.log(" MG ====> ", mg );
    var committed = $('#paymentLines').val();
    committed2 = committed.split(":");
    committed = committed2[1];
    morig = $('#thousand_point').val();
    if (typeof (committed) === "undefined") {
        committed = '0';
    }
    committed = committed.replace(morig, "");
    committed = clearNum_symbol(committed);
    var miImpr = $('input:radio[name=impreimfac]:checked').val();
    var url = getOrderLines();
    //payment methods 
    var vueltas = $("#paymentLines").val();
    vueltas = vueltas.replace(" ", "");
//console.log("'#paymentLines' ==1635===> ",$('#paymentLines').val() )
    url += "&lines=" + $('#paymentLines').val() + "&customerId=" + $('#orderCustomerId').val() + "&mpay=" + mpay + "&yourpay=" + vueltas;
//console.log("URL ==1635===> ", url);  
    $.ajax({
        url: 'ajax.php?action=createOrder&value=' + url,
        success: function (data) {
//console.log("DESDE SERVER:CREATE ORDER- ORDERID ==1638====> ",data);
            title = lang_orderAdd; //lang_payment;
            mfoot = "<button type='button' class='btn btn-default' data-dismiss='modal'>Cerrar</button>";
            mmd(lang_orderAddT, title, mfoot, "1", "");
            setTimeout('mmd_closed()', 400);

            var rows = 0;
            $('#orderTable tr').each(function () {
                if (rows) {
                    $(this).remove();
                }
                rows++;
            }); // remove added customer
            $('#orderCustomerId').val('');
            $('#orderCustomerName').val($('#customerName').val());
            $('.customerAdd').val('');
            $('#customerDetails').val('');
            $('#orderCustomer').html(''); // re-calculate
            calculateTotal();
            // $("#searchBar").focus();

            var rows = 0;
            $('#orderTable2 tr').each(function () {
                if (rows) {
                    $(this).remove();
                }
                rows++;
            });
            $('#orderTotal2').text(format2(0));
            $('#orderTotal2_2').text(format2(0));
            var metodo = $('#printer_default').val(); //0: web || impresora text 
            
            var facturax = $('#receiptPrinting').val();
 //console.log("TIPO_FACTUR |  METODO ==1669 | facturax ===> ", tipo_factur + " | " +  metodo + " | " + facturax);           
            if (facturax == 1) {
            
                if (tipo_factur == "Fac" || tipo_factur == "fac") { //Factura
                    printReceiptPopup(data, committed, mg);
                } else { //Factura simplificada	
   // //console.log("ENTRO LINEA 1702");
                    if (metodo == '0') {
                        printReceiptPopup2b(data, committed, mg);
                    } else {
                        printReceiptPopup3c(data, committed, mg);
                    }
                }

                if (mg == '1') {
                    setTimeout(function() {without_price(tipo_factur, metodo, data, committed, mg);}, 1000);
//console.log("ENTRO LINEA 1710");
                    /*mg = '0';
                    if (tipo_factur == "Fac" || tipo_factur == "fac") { //Factura
                        printReceiptPopup(data, committed, mg);
                    } else { //Factura simplificada	
                        if (metodo == '0') {
                            printReceiptPopup2b(data, committed, mg);
                        } else {
                            printReceiptPopup3c(data, committed, mg);
                        }
                    }*/
                }
            }
        }
    });
    $("#all_percentage_discount").val(0);
    mfocus_search();
}

function without_price(tipo_factur, metodo, data, committed, mg){
//console.log("ENTRO LINEA 1732");
    mg = '0';
    if (tipo_factur == "Fac" || tipo_factur == "fac") { //Factura
        printReceiptPopup(data, committed, mg);
    } else { //Factura simplificada	
        if (metodo == '0') {
            printReceiptPopup2b(data, committed, mg);
        } else {
            printReceiptPopup3c(data, committed, mg);
        }
    }   
}

function reserve() {
    alert('Reservado!');
}
/**
 * Get order lines
 */
function getOrderLines() {
    var rows = 0;
    var quantity = 0;
    var url = '';
    //iterate table rows
    $('#orderTable tr').each(function () {
        if (rows) {
            if ($(this).attr('id').indexOf('dis') > 0) {
                //discount
                url += $(this).attr('id') + "_" + clean_number($(this).find("td").eq(3).html()) + "|";
            } else if ($(this).attr('id').indexOf('serv') > 0) {
                mtrz = $(this).find("td").eq(5).html();
                mmtrz = mtrz.split("\_");
                var u_id = $(this).attr('id');
                var u_name = $(this).find("td").eq(0).html();
                var u_price = clean_number($(this).find("td").eq(1).html(), true);
//console.log( "PRECIO ==1733===> ",$(this).find("td").eq(1).html() );
//console.log( "PRECIO con formateo ==1734===> ",u_price );
                var u_quantity = $(this).find("td").eq(2).html();
                var u_price_sin_tx = mmtrz[0];
                var u_tax_id = mmtrz[1];
                var u_coment = mmtrz[2];
                
                url += u_id + "_" + u_name   + "_" + u_quantity + "_" + u_price + "_" + u_price_sin_tx  + "_" + u_tax_id  + "_" +  u_coment	+ "|";
//console.log("URL servicio ======> ", url );               
               /* //console.log("COLUMNA 0 =============> ", $(this).find("td").eq(0).html()); 
                //console.log("COLUMNA 1 =============> ", $(this).find("td").eq(1).html()) ; 
                //console.log("COLUMNA 2 =============> ", $(this).find("td").eq(2).html())  ;       
                //console.log("COLUMNA 3 =============> ", $(this).find("td").eq(3).html());
                //console.log("COLUMNA 4 =============> ", $(this).find("td").eq(4).html()) ;
                //console.log("COLUMNA 5 =============> ", $(this).find("td").eq(5).html())  ;*/

            } else {
                //product row
                quantity = $(this).find("td").eq(2).html();
                url += $(this).attr('id') + "_" + quantity + "|";
            }
        }
        rows++;
    });
    return url;
}
/**
 * Set the invoice to print
 */
function printReceiptPopup(orderId, committed, mg) {
//console.log("COMMITTED1 ==1766===> ",committed);
    mv = '?orderId=' + orderId + '&committed=' + committed + "&mg=" + mg;
    $.ajax({
        url: 'receipt.php' + mv,
        dataType: "html",
        success: function (data) {
            $('#mi_print').empty();
            $('#mi_print').html(data);
            $('#btnr').remove();
            //$('#mi_print').printThis();	
            $('#mi_print').print();
            $('#mi_print').empty();
            mfocus_search()
        }
    });
}



function printReceiptPopup2b(orderId, committed, mg) {
//console.log("COMMITTED2 ==1785===> ",committed);
    mv = '?orderId=' + orderId + '&committed=' + committed + "&mg=" + mg;
    $.ajax({
        url: 'receipt2.php' + mv,
        dataType: "html",
        success: function (data) {
//console.log("PRINT DATA =============> ", data);
            $('#mi_print').empty();
            $('#mi_print').html(data);
            $('#btnr').remove();
            //$('#mi_print').printThis();	
            $('#mi_print').print({
                    globalStyles: false,
                    mediaPrint: false,
                    iframe: false,
                    noPrintSelector: ".avoid-this",
                }

            );
            //$('#mi_print').empty();

            mfocus_search()
        }
    });
}

function printReceiptPopup3c(orderId, committed, mg) {
startConnection_close_box("");    
    if (committed === 'undefined') {
        committed = 0;
    }
    var ar = "|" + orderId + "|" + committed + "|" + mg;
console.log("AR C ==============> ", ar);
    inbox(ar); //codigo esc-pos | order id|cash_delivered	
    $('#mi_print').empty();
    //$('#mi_print').html(data);
    $('#btnr').remove();
    mfocus_search();
}


/**
 * Remove the keypad from the bill
 */
function format_receipt(miH) {
    //console.log("miH en format_receipt =======================> ",miH)
    $('#mi_print').empty();
    $('#mi_print').html(miH);
    $('#btnr').remove();
}
/**
 * Print order receipt
 */

function printDiv(nombre) {
    var ficha = document.getElementById(nombre);
    var ventimp = window.open(' ', 'popimpr');
    ventimp.document.write(ficha.innerHTML);
    ventimp.document.close();
    ventimp.print();
    ventimp.close();
}

/**
 * Show categories & products to navigate
 **/
function optionNavigate() {
    $("#optionNavigate").addClass("selected");
    $("#optionSearch").removeClass("selected");
    $("#searcharea").hide();
    init();
}
/**
 * Show product search bar & results
 **/
function optionSearch() {
    $("#optionNavigate").removeClass("selected");
    $("#optionSearch").addClass("selected");
    //$("#searchBar").val('');
    $("#searcharea").show();
    $("#productarea").html("<div id='prodContainer2'>" + lang_searchText + "</div>");
    //$("#searchBar").focus();
}



/**
 * Initiate new product search
 **/
function newSearch(miv) {
    var res_p = $("#mpantalla").val();
    //console.log(res_p);
    if (res_p == "ini") {
        $.ajax({
            url: 'ajax.php?action=searchProducts&value=' + miv,
            success: function (data) {
                //console.log("DATA =====================> ", data);
                if (data != '0') {
                    var res = data.split("|");
                    //console.log("ARREGLO LN 1847 ===> ", res[0] + ", " + res[1] + ", " + res[2] + ", " + res[3] + ", " + res[4])
                    addProduct(res[0], res[1], res[2], res[3], res[4]);
                }
                $("#search_in").val('')
            }
        });
    } else if (res_p == "vent") {
        $.ajax({
            url: 'ajax.php?action=searchVent&value=' + miv,
            success: function (data) {
console.log("DATA search ----->>> ", data)	
                if (data != "0") {
                    var mtrz = data.split('|||');
                    order_imprent = mtrz[0];
                    data = mtrz[1];
                    $('#order').html(data);
                    $('#order2').html(data);
                    //console.log("FINALlll----->>> ", $('#order').html());			
                    mmargin();
                    mmargin2();
                    /*if (data = '0'){
                    	$("#fcs").css("display", "block");
                    }else{*/
                    $("#xst").css("display", "block");
                    //}
                    window.setTimeout("delmsg()", 3000);
                }
            }
        })
    }
}

function delmsg() {
    $("#xst").css("display", "none");
    $("#fcs").css("display", "none");
}

function load_product_php(productId, name, price, options, mvw) {
    addProduct(productId, name, price, options, mvw);
}

function mnu_up_right() {
    var res_p = $("#mpantalla").val();
    if (res_p == "ini") {
        $("#search_in").prop("disabled", false);
        $("#search_in_b").prop("disabled", false);
        $("#mid_preorder").show();
        $("#mservir_s").show();
        
    } else if (res_p == "vent") {
        $("#search_in").prop("disabled", false);
        $("#search_in_b").prop("disabled", true);
        $("#mid_preorder").hide();
        $("#mservir_s").hide();
        $('#search_in').attr('placeholder', 'codigo de venta');
    } else if (res_p == "caj") {
        var strVar = "";
        strVar += "                    <a data-toggle=\"modal\" href=\"#\" class=\"btn btn-default\" onClick=\"entryinbox();\"  title=\"Ingresar en caja\" > ";
        strVar += "						<span class=\"glyphicon glyphicon-log-in\"><\/span>&nbsp;&nbsp;Ingresar en caja";
        strVar += "					<\/a>";
        strVar += "                    <a data-toggle=\"modal\" href=\"#\" class=\"btn btn-default\"   onClick=\"outofbox();\"  title=\"Sacar de caja\"> ";
        strVar += "						<span class=\"glyphicon glyphicon-log-out\"><\/span>&nbsp;&nbsp;Sacar de caja";
        strVar += "					<\/a>";

        $("#btns_cata").css("display", "block");
        $("#btns_vent").css("display", "none");

        $("#btns_cata").html(strVar);
        $("#search_in").prop("disabled", true);
        $("#search_in_b").prop("disabled", true);
        $("#mid_preorder").hide();
        $("#mservir_s").hide();
        $('#search_in').attr('placeholder', '');
    }
    mfocus_search();
}

/**
 * Print Barcode
 * @param integer barcodeId
 */
function printBarcode(barcodeId) {
    var url = String(window.location);
    url = url.substr(0, url.length - 12);
    var thePopup = window.open('',
        "Barcode", "menubar=0,location=0,height=160,width=310");
    $("<img src='" + url + "/images/barcode.php?code=" + barcodeId + "&style=324&type=C39&width=300&height=150&xres=1&font=2' />").appendTo(thePopup.document.body);
}

/**
 * Print Barcode by print row
 * @param integer barcodeId
 */
function printBarcode2(barcodeId) {
    //console.log("barcodeId  ==> " , barcodeId);
    var url = String(window.location);
    //console.log("URL1  ==> " , url);
    //url = url.substr(0, url.length - 12);
    //console.log("URL2  ==> " , url);	

    mtz = url.split('tpv');
    //console.log('MTZ 0 ==> ', mtz[0]);
    url = mtz[0] + "tpv";
    var thePopup = window.open('',
        "Barcode", "menubar=0,location=0,height=160,width=310");
    $("<img src='" + "/images/barcode.php?code=" + barcodeId + "&style=324&type=C39&width=300&height=150&xres=1&font=2' />").appendTo(thePopup.document.body);
    //console.log("IMAGEN ==> ", "img src='" + url + "/images/barcode.php?code=" + barcodeId + "&style=324&type=C39&width=300&height=150&xres=1&font=2' /");
}

/**
 * Format value to be shown correct
 * @param string num: the number to format: con decimales(marcados por punto o sin marcar)
 * @param string no_marked_decimals: los decimales no vienen marcados por punto(true) o vienen marcados por punto(false)
 * @return  formato currency
 **/
function format(num) {
    //console.log("ENTRA NUMERO EN FORMAT ==1989=format===> ", num);	
    //console.log(" symbol_left  |  symbol_right ==1982===> ", symbol_left  + " | " +  symbol_right);
	var symbol_right = $('#symbol_right').val();
	var symbol_left = $('#symbol_left').val();
	var decimal_point = $('#decimal_point').val();
	var thousand_point = $('#thousand_point').val();
    var currency_decimal_place = '2';// parseInt($('currency_decimal_place').val());
    var sign, cents, i;
    var msgn = "";
    
 //console.log("format_NUM ==2002====> ", num);   
    num = clean_number(num, true);
//console.log("format_NUM ==2004====> ", num);
   // num = num/100;
    
//console.log("Numero con 2 decimales  ==2006=format=> ", num);	
    var nm = formatoNumero(num, 2, '.', ",");
//console.log("Numero  formateado ==2008=format=> ", nm);
    var posicion = nm.indexOf("-");
    var number_currency;
    if (posicion !== -1){
        number_currency = nm.replace("-", "-" + symbol_left) + symbol_right;
    }else{ 
        number_currency = symbol_left + nm + symbol_right;
    }
//console.log("Numero  currency ==2010=format=> ",  number_currency);
    return number_currency;
} 

/**
 * Da formato a un número para su visualización
 *
 * @param {(number|string)} numero Número que se mostrará
 * @param {number} [decimales=null] Nº de decimales (por defecto, auto); admite valores negativos
 * @param {string} [separadorDecimal=","] Separador decimal
 * @param {string} [separadorMiles=""] Separador de miles
 * @returns {string} Número formateado (si no tiene decimales añade ceros) o cadena vacía si no es un número
 *
 * @version 2014-07-18
 */
function formatoNumero(numero, decimales, separadorDecimal, separadorMiles) {
    var partes, array;
    
    numero = clean_number (numero, decimal = true);

    if ( !isFinite(numero) || isNaN(numero = parseFloat(numero)) ) {
        return "";
    }
    if (typeof separadorDecimal==="undefined") {
        separadorDecimal = ".";
    }
    if (typeof separadorMiles==="undefined") {
        separadorMiles = ",";
    }

    // Redondeamos
    if ( !isNaN(parseInt(decimales)) ) {
        if (decimales >= 0) {
            numero = numero.toFixed(decimales);
        } else {
            numero = (
                Math.round(numero / Math.pow(10, Math.abs(decimales))) * Math.pow(10, Math.abs(decimales))
            ).toFixed();
        }
    } else {
        numero = numero.toString();
    }

    // Damos formato
    partes = numero.split(".", 2);
    array = partes[0].split("");
    for (var i=array.length-3; i>0 && array[i-1]!=="-"; i-=3) {
        array.splice(i, 0, separadorMiles);
    }
    numero = array.join("");

    if (partes.length>1) {
        numero += separadorDecimal + partes[1];
    }

    return numero;   
}

/*

function format_deci(num) {
    num = clearNum_symbol(num);
    var thousand_point = $('#thousand_point').val();
    num = num.replace(thousand_point, '');
    if (num.indexOf('-') !== -1) {
        num.replace('-', '');
        sust = '-'
    } else {
        sust = ''
    }
    if (isNaN == (num)) num = "0";
    var rept = $('#decimal_place').val();
    rep = clearNum_symbol(rept);
    if (num == "0") {
        mdc = "";
        for (i = 1; i <= rep; i++) {
            mdc += "0";
        }
        result = num + "\." + mdc;
        return result;
    }
    mtr = num.split(".");
    if (mtr[1] !== undefined) {
        nm_str = mtr[1].length;
        var repti = parseInt(rep);
        dec_dif = repti - nm_str;
        if (dec_dif < 0) {
            //quitar decimales
            mst = parseFloat(num);
            nm_clear = mst.toFixed(rep);
        } else if (dec_dif == 0) {
            //Mantener decimales
            nm_clear = mtr[0].toString() + mtr[1].toString();
        } else {
            //aÃ±adir ceros
            multip = Math.pow(10,
                dec_dif);
            resul = parseInt(mtr[1]) * multip;
            nm_clear = mtr[0].toString();
            nm_clear += resul.toString();
        }
    } else {
        multip = Math.pow(10, rept);
        nm_clear = num * multip;
    }
    nmf = format(nm_clear);
    nmf = clearNum_symbol(nmf);
    return nmf;
}*/

function format2(num) {
    var symbol_right = $('#symbol_right').val();
    var symbol_left = $('#symbol_left').val();
    var decimal_point = $('#decimal_point').val();
    var thousand_point = $('#thousand_point').val();
    var sign, cents, i;
    var rept = $('#decimal_place').val();
    var rep = parseInt(rept);
    rece = "";
    for (i = 1; i <= rep; i++) {
        rece += "0";
    }
    return (symbol_left + num + decimal_point + rece + symbol_right);
}

function isDefined(variable) {
    if (variable == "") {
        return "vacia";
    } else {
        return "No vacia";
    }
}
/**
 * Clear formatting characters from variable
 * @param string num the number to remove characters from
 * @return string
 **/
function clearNum(num) {
    if (num === undefined) {
        return "0"
    }
    var symbol_right = $('#symbol_right').val();
    var symbol_left = $('#symbol_left').val();
    var decimal_point = $('#decimal_point').val();
    var thousand_point = $('#thousand_point').val();
    return num.toString().replace(symbol_left,
        '').replace(symbol_right, '').replace(/decimal_point/g, '').replace(/thousand_point/g, '');
}

/**
* @param {(number|string)}Recibe un mumero (comas, puntos, moneda).
* @param {(decimal|string)} Si true: devuelve numero decimal marcado con punto
* Si recibe negativo, devuelve negativo.
* @returns {string} Devuelve solo numeros con decimales (marcados por un punto si [decimal]=true)
*/
  
function clean_number (number, decimal = false){ 
    number = number + "";
    
    var posicion = number.indexOf("-");
    var msgn ="";
    if (posicion !== -1){
        msgn ="-";
    }    
    
    numbre2 = number.replace(/,/g, '.');
    mtr = numbre2.split('.');//mtr es un array de todos los grupos de numeros separados por un punto (el último decimales)
//console.log("MTR ==30===> ",mtr);
    nm_mtr = mtr.length; // numero de grupos de numeros
    nm =mtr.length;
    var i;
    var regex = /(\d+)/g;    //solo numeros
    for(i=0; i< nm; i++){
       mtr[i] = mtr[i].match(regex);
    }

    if(nm_mtr < 2){ //No tiene decimales
        mtr[1] = "00";
        nm_mtr += 1;
//console.log("MTR[1] ==38===> ", mtr[1])        
    }else{
        var decim = mtr[nm_mtr - 1][0];
        //console.log("DECIM ==44===> ", decim)
        var mtr1 = decim.split(''); //array del  ultimo grupo de numeros, que son decimales.  mtr1 es el array de ese grupo
        var nm_mtr1 = mtr1.length;
        //console.log("nm_mtr1==1 ==86===> ", nm_mtr1==1)
        if(nm_mtr1 == 1){
            mtr[nm_mtr - 1]= mtr1 + '0';
        }else if (nm_mtr1 >2){ // Tiene más de dos decimales. Y hay que reducirlo a dos decimales           
           if(parseInt( mtr1[2]) > 4){ //redondeo del tercer decimal
               if(parseInt( mtr1[1]) < 9){
                    mtr1[1] = (parseInt( mtr1[1] ) +1) + "";
               }else{
                   mtr1[1] = '0';
                   if(parseInt(mtr1[0]) < 9){
                      mtr1[0] = (parseInt( mtr1[0] ) +1) + "";
                   }else{
                      mtr1[0] = '0'; 
                      var indi = nm_mtr - 2;
                      //console.log("nm_mtr | INDI ==100===> ", nm_mtr + " | " + indi);
                      mtr[indi] = (parseInt( mtr[indi] ) + 1) + "";
                   }
               }
           }
           mtr[nm_mtr - 1] = mtr1[0] + mtr1[1];   // reducir a dos decimales
        }

    }
    var res = '';
    for (i=0; i <(nm_mtr-1);i++){
        res += mtr[i];
//console.log("i | res ==100===> ",i + " | " + res );
    }
    if(decimal == true){
        res += '.' + mtr[nm_mtr - 1];
    }else{
        res +=  mtr[nm_mtr - 1];
    }
    res = msgn + res
    return res;
}

/**
 *Input: A price currency symbol.
 *
 * Output: only the number.
 **/
function clearNum_symbol(num) {
    if (num === undefined) {
        return "0"
    }
    var symbol_right = $('#symbol_right').val();
    var symbol_left = $('#symbol_left').val();
    return num.toString().replace(symbol_left,
        '').replace(symbol_right, '')
}

function reser_cancel() {
    location.href = "reservations.php";
}

function reser_checket(order_id) {
    $.ajax({
        url: 'ajax.php?action=reserver_comple&order_id=' + order_id,
        success: function (data) {
            printReceiptPopup2(order_id, '1000');
        }
    });
}

function reset_del(order_id) {
    $.ajax({
        url: 'ajax.php?action=reserver_delete&order_id=' + order_id,
        success: function (data) {
            location.href = "reservations.php";
        }
    });
}

function view_close(sid) {
    $.ajax({
        url: 'ajax.php?action=view_closed&' + 'orderus_id=' + sid,
        dataType: "html",
        success: function (data) {
            $("#dialog").html(data);
            $("#dialog").dialog({
                title: lang_close_box,
                modal: true,
                width: 300,
                height: 250
            });
        }
    });
}

function status_ticket_d() {
    $("#dialog").html("<form onSubmit='resp_status_ticket_d(); return false;'><table style='width:100%'><tr>" + "<td style='width:150px'>" + lang_statusticketd + "</td></tr>" + "<tr><td style='padding: 10px 0 5px 0;'><input type='text' id='idticket' value='' style='width:45%'></td>" + "</table></form>");
    $('input:submit').button();
    $("#dialog").dialog({
        title: lang_ttl_status_t_d,
        modal: true,
        width: 450,
        height: 115
    });
}

function resp_status_ticket_d() {
    var id_ticket = $('#idticket').val();
    var params = {
        value: id_ticket
    };
    $.ajax({
        url: 'ajax.php?action=status_ticket_d&' + $.param(params),
        success: function (data) {
            $("#dialog").html(data);
            $("#dialog").dialog({
                title: lang_ttl_status_t_d,
                modal: true,
                width: 400,
                height: 110
            });
        }

    });
}

function create_ticket_d() {
    $("#dialog").html("<style>" + ".tb{width: 96%; margin:0 2%;}" + ".tb th{}" + ".tb td{padding: 8px 3px;}" + ".lg{width: 90%;}" + "</style>" + "<form onSubmit='end_crea_ticket_d(); return false;'>" + " <table style='' border='0' class='tb'>" + " <tr>" + " <th colspan='2'>" + lang_create_discount_t + "</th>" + " </tr>" + " <tr >" + " <td style='width: 50%;'>" + lang_dicount_value + ": </td>" + " <td style='width: 50%;'>" + " <input type='text' id='mval' value='0' class='lg'>" + " </td>" + " </tr>" + " <tr>" + " <td>" + " " + lang_days_validity + " </td>" + " <td>" + " <input type='text' id='mdays' value='0' class='lg'>" + " </td>" + " </tr>" + " <tr>" + " <td> " + lang_observations + ": </td>" + " <td>" + " <textarea rows='2' class='lg' id='mobserv'></textarea>" + " </td>" + " </tr>" + " <tr>" + " <td colspan='2' style='padding: 20px 10%;'><input type='submit' value='" + lang_crete + "'>" + " <input type='button' onClick='closeboxcancel()' value='" + lang_cancel + "' /></td>" + " </tr>" + "</table>" + "</form>");
    $('#discountPercent').buttonset();
    $('input:submit').button();
    $("#dialog").dialog({
        title: lang_ttl_create_ticket_d,
        modal: true,
        width: 450,
        height: 300
    });
}

function end_crea_ticket_d() {
    var mvl = $('#mval').val();
    var mdy = $('#mdays').val();
    var mob = $('#mobserv').val();
    var params = {
        val: mvl,
        days: mdy,
        obser: mob
    }
    var prs = $.param(params);
    $.ajax({
        url: 'ajax.php?action=create_ticket_d&' + prs,
        success: function (data) {
            miH = data;
            format_receipt(miH);
            $("#dialog").html(data);
            $("#dialog").dialog({
                title: lang_tck_discount,
                modal: true,
                width: 250,
                height: 600
            });
        }
    });
}

function view_last_receipt() {
    $.ajax({
        url: 'ajax.php?action=last_receipt',
        success: function (data) {
            mid = data;
            printReceiptPopup2(mid,
                0, 0);
        }
    });
}


function mmd(html, title, mfoot, width, mtype) {
    //console.log("title | width  ==> ", title + " | " + width);
    switch (width) {
        case '0':
            msize = "modal-dialog modal-xs";
            break
        case '1':
            msize = "modal-dialog modal-sm";
            break;
        case '3':
            msize = "modal-dialog modal-lg";
            break;
        default:
            msize = "modal-dialog";
            break;
    }
    $("#myModalLabel1").html(title);
    $("#mymodal-body1").html(html);
    $("#mymodal-footer1").html(mfoot);
    $("#my_long").removeClass("modal-dialog modal-xs");
    $("#my_long").removeClass("modal-dialog modal-sm");
    $("#my_long").removeClass("modal-dialog modal-lg");
    $("#my_long").removeClass("modal-dialog");
    $("#my_long").addClass(msize);
    $("#myModal").modal('show');
    $("#myModal").modal({
        keyboard: true,
        backdrop: false
    });

}

function mmd_closed() {
//alert("#############");
    $("#myModal").modal('hide')
    mfocus_search();
}

function mmd_closed2() {
    $("#discount").modal('hide');
    //setTimeout(mfocus_search(), 2000);
    mfocus_search();
}

function m_ventas() {
    var mres;
    $.ajax({
        url: 'ajax.php?action=mventas',
        success: function (data) {
            mres = data.split("###");
//console.log("MATRIZ 0 ==> ", mres["1"]);
            $('#uno_in').html(mres["1"]);
            $('#tres').html(mres["1"]);
            mmargin();
            mmargin2();
            display_orde(mres["0"])
        }
    });
    $("#mpantalla").val("vent");
    mnu_up_right();

}

function display_orde($nm) {
//console.log("$NM ===========================> ", $nm);
    $("#btns_cata").css("display", "none");
    $("#btns_vent").css("display", "block");
    $.ajax({
        url: 'ajax.php?action=display_order&order_id=' + $nm,
        success: function (data) {
//console.log("DATA ORDER ==> ", data);
            $('#order').html(data);
            $('#order2').html(data);
            order_imprent = $nm;
            //console.log("FINAL----->>> ", $('#order').html());			
            mmargin();
            mmargin2();
        }
    })
}

function find_orde($nm) {
    res = "<div class=\"alert alert-success\">Venta encontrada:</div>"
    $("#btns_cata").css("display", "none");
    $("#btns_vent").css("display", "block");
    $.ajax({
        url: 'ajax.php?action=display_order_f&order_id=' + $nm,
        success: function (data) {
            data = res + data;
            $('#order').html(data);
            $('#order2').html(data);
            mmargin();
            mmargin2();
        }
    })
}

function read_order() {
    var xmorder = $('#order').html();
    var mprop = $('#orderCustomer').html();
    var mnm = 0;
    $('#orderTable tr').each(function () {
        mnm = mnm + 1;
    })

    if (mnm > 1) {
        $.post("ajax.php", {
                action: "save_preorder",
                pro: mprop,
                morder: xmorder
            },
            function (data, status) {
                if (data == "1") {
                    html = "La order ha sigo guardada";
                    title = "";
                    mfoot = mfoot = '<button type="button" class="btn" data-dismiss="modal" style="padding-left: 30px; padding-right: 30px; margin-right: 12px;">Salir</button>';
                    width = "1";
                    mtype = '';
                    del_order()
                    mmd(html, title, mfoot, width, mtype);
                    del_order()
                }
            });
    }
    mfocus_search();
}

function view_pre_orders() {
    $.ajax({
        url: 'ajax.php?action=view_pre_orders',
        success: function (data) {
            //console.log("DATA ==> ", data);
            html = data;
            title = lang_view_preorder;
            mfoot = mfoot = '<button type="button" class="btn btn-warning" data-dismiss="modal" style="padding-left: 30px; padding-right: 30px; margin-right: 12px;">Salir</button>';
            width = "2";
            mtype = '';
            mmd(html, title, mfoot, width, mtype);

        }
    })
}

function see_pre_order(nm) {
    $.ajax({
        url: 'ajax.php?action=view_pre_order&value=' + nm,
        success: function (data) {
            //console.log("DATE--> ", data);
            data2 = data;
            data2 = data2.replace("orderTable", "orderTable2");
            data2 = data2.replace('id="orderCustomer"', 'id="orderCustomer2"');
            data2 = data2.replace('id="orderTotal2"', 'id="orderTotal2_2"');
            $('#order').html(data);
            $('#order2').html(data2);
            mmd_closed();
            nucar();
            del_pre_lst(nm);
        }
    })
}

function del_order() {
    var m_nul = $("#symbol_left").val() + " 0.00 " + $("#symbol_right").val();
    $("#orderTotal2").html(m_nul);
    $("#orderTotal2_2").html(m_nul);
    $("#orderCustomer").html('');
    $('#orderTable tr')
        .slice(1)
        .remove();
    $("#orderCustomer2").html('');
    $("#all_percentage_discount").val(0);
    $('#orderTable2 tr')
        .slice(1)
        .remove();
    mfocus_search();
}

function del_pre_lst(nm) {
    $.ajax({
        url: 'ajax.php?action=del_db_pre_order&value=' + nm,
        success: function (data) {
            $("#f-" + nm).remove();
        }
    })


}

function lst_box_operations() {
    $.ajax({
        url: 'ajax.php?action=lst_box_operations',
        success: function (data) {

            $('#uno_in').html(data);
            $('#tres').html(data);
            mmargin();
            mmargin2();
            display_status_box();
        }
    });
    $("#mpantalla").val("caj");
    mnu_up_right();
}

function display_status_box() {
    $.ajax({
        url: 'ajax.php?action=boxstatus&value=0',
        success: function (data) {
            //console.log("data_xyz  ==> ", data);
            $('#order').html(data);
            $('#order2').html(data);
            mmargin();
            mmargin2();
        }
    });
}

function dir_ini() {
    window.location.href = "index.php";
    $("#mpantalla").val("ini");
}

function Update_sales() {
    $.ajax({
        url: 'ajax.php?action=update_sales',
        success: function (data) {
            //console.log("update_sales ====> ", data);
            var mtr = data.split("|");
            $("#us_name").html(mtr[0]);
            var mirs = mtr[1] + " | " + mtr[2];
            $("#us_vent").html(mirs);
        }
    });

}

function clear_nm(nm) { // numero entra por formulario sale con decimales con punto y sin comas
    //encontrar comas
    var res = nm.split(",");
    its1 = res.length;
    if (its1 > 1) {
        var i;
        var coma = 0;
        for (i = 0; i < (its1 - 1); ++i) {
            coma += res[i].length;
        }
    } else {
        coma = 0;
    }
    //console.log("coma ==> ", coma);
    //encontrar puntos
    var res = nm.split(".");
    its2 = res.length;
    if (its2 > 1) {
        var i;
        var punto = 0;
        for (i = 0; i < (its2 - 1); ++i) {
            punto += res[i].length;
        }
    } else {
        punto = 0;
    }
    //console.log("punto ==> ", punto);	
    if (punto > coma) { //decimal punto 
        for (i = 0; i < (its1 - 1); ++i) {
            nm = nm.replace(",", "");
        }
    } else if (coma > punto) { //decimal coma
        for (i = 0; i < (its2 - 1); ++i) {
            nm = nm.replace(".", "");
        }
        nm = nm.replace(",", ".");
    } else if (coma == punto) { //ni punto, ni coma
        nm = nm;
    }
    //console.log("nm ==> ", nm);
    //devolver
    return nm;
}

// Entra un numero con formatos de miles y decimales (por. ej.: 12.345,345 ==> solo los decimales con punto ==> 12345.345)
function format_price_user(precio_user){
    var pu = precio_user.replace(",", ".");
    var r = pu.split(".");
    var cuent = r.length - 1;
    if(cuent== 0){return pu;}
    var res = "";
    for(var i=0;i<(cuent );i++){
        res = res + r[i];
//console.log("RES ciclo ===> ", res)
    }
    res = res + "." + r[cuent];
//console.log("RES FINAL ===> ", res)
    return res;
}
    
//si el valor del parametro está vacío, devuelve true, y en caso contrario false
function var_is_empty(item){
    var isEmpty = false;
 
    if (typeof item == 'undefined' || item === null || item === ''){
      isEmpty = true;
    }  
    
    if(isEmpty == false){
       item = item.trim(); 
       if (typeof item == 'undefined' || item === null || item === ''){
          isEmpty = true;
        }  
    }
       
    if (typeof item == 'number' && isNaN(item)){
      isEmpty = true;
    }
       
    if (item instanceof Date && isNaN(Number(item))){
      isEmpty = true;
    }
//console.log("isEmpty =========> ", isEmpty);       
    return isEmpty;
}
//console