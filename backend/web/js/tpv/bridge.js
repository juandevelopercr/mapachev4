//JavaScript Document
var baliza_i; //codifica los datos de la impresión
var log_dat;   //recoge los sucesos de la impresión para log
//print_attempt  ===> Intento de impresión: 1, 2
var matriz;
var cash_delivered;
var returned_client;
var last_printer;

qz.security.setCertificatePromise(function (resolve, reject) {
	//Preferred method - from server
	$.ajax({
		url: "zq/assets/override.crt",
		cache: false,
		dataType: "text"
	}).then(resolve, reject);
});

qz.security.setSignaturePromise(function (toSign) {
	return function (resolve, reject) {
		//Preferred method - from server
		$.post("zq/assets/signing/sign-message.php", {
			request: toSign
		}).then(resolve, reject);
	};
});
//******************************
function init_qz(){
    alert('INICIO QZ');    
	var impres = $('#printer_default').val(); //"Tickets1"	
	if (!qz.websocket.isActive()) {;
		qz.websocket.connect({ retries: 5, delay: 1 }).then(function () {
			findPrinter_a(impres);
		});
	}
}

function findPrinter_a(query, mtz) {
//console.log("findPrinter LN-36 <========================")
	qz.printers.find(query).then(function (data) {
       //console.log("findPrinter LN-38 <========================")     
	});

}

//*********************************************************

async function inbox(ar, print_attempt = 1) { //codigo esc-pos | order id|cash_delivered	
//console.log("PRINCIPIO PRINT print_attempt | log_dat =55==> ", print_attempt + " | " + log_dat );	
	if (print_attempt == 1){
		log_dat = ""
;		log_dat = "Print --> uno ";
	}else{
		log_dat += " | Print 2 --> ";
	}
	//print_attempt = 1;
//console.log("PRINCIPIO PRINT print_attempt | log_dat =55==> ", print_attempt + " | " + log_dat );
	mtz = ar.split('|');
	cash_delivered = mtz[2];
	if (mtz[0].length === 0) {
		$.ajax({
			url: 'receipt3.php?id=' + mtz[1] + '&mg=' + mtz[3] + '&committed=' + mtz[2],
			success: function (data) {
				resu = data.split("||");
				data = resu[1]
				matriz = $.parseJSON(data);
				mtz = matriz;
//console.log("MTZ (LN-44)===> ", mtz);
				startConnection({ retries: 5, delay: 1 }, mtz); //conctar
			}
		});
	} else {
		matriz = mtz[0];
		mtz = matriz;
		startConnection("", mtz);
	}
	
	await testAsync();
	console.log("tras espera -----> ", baliza_i);
	if(baliza_i == 'cinco'){
		print_attempt = 0
		console.log("Impresión correcta -----> ");
		log_dat += ";Print completada.";
	}else{
		if(print_attempt < 3){
			console.log("Impresión Fallida -----> ");
			print_attempt += 1;
			inbox(ar, print_attempt);
		}else{
			console.log("Impresión finalmente fallida");
			log_dat += ";Print Fracasada.";
		}
	}
console.log("LOG_DAT =90===> ", log_dat);
	writte_log(log_dat );
}

function writte_log(lin){
console.log("writte_log =99===> ", lin);	
    catId2 = '?action=in_login_print&value=' + lin;
//console.log("CATID2 =97==> ", catId2);
    $.ajax({
        url: 'ajax.php' + catId2,
        success: function (data) {

        }
    });	
	
}

function testAsync(){
    return new Promise((resolve,reject)=>{
        //here our function should be implemented 
        setTimeout(()=>{
            console.log("Hello from inside the testAsync function");
            resolve();
        ;} , 3000
        );
    });
}

function inbox_return(data) { //codigo esc-pos 	
	matriz = $.parseJSON(data);
	//console.log("DATOS DEVOCIÓN ====> ", matriz);
	startConnection_return(); //conctar
}

function inbox_close_box(data) {
	matriz = $.parseJSON(data);
	var rt = startConnection_close_box(); //conctar
	if (rt == '1') {

	}
}

function startConnection(config, mtz) { //LN 870
baliza_i= 'dos';
log_dat += ";dos ";
console.log("startConnection LN-84 DOS==========> ", baliza_i);
	var impres = $('#printer_default').val(); //"Tickets1"	
	if (!qz.websocket.isActive()) {;
		qz.websocket.connect(config).then(function () {
			findPrinter(impres, mtz);
		});
	} else {
		findPrinter(impres, mtz);
	}
}

function startConnection_return(config) { //LN 870
	impres = $('#printer_default').val(); //"Tickets1"	
 //console.log("IMPRES ===> ", impres);    
	if (!qz.websocket.isActive()) {
		qz.websocket.connect().then(function () {
			findPrinter_return(impres);
		});
	} else {
//console.log("LN88 ===> "); 
		findPrinter_return(impres);
	}
}

function startConnection_close_box(config) { //LN 870
	impres = $('#printer_default').val(); //"Tickets1"	
	if (!qz.websocket.isActive()) {;
		qz.websocket.connect().then(function () {
			findPrinter_close_box(impres);
		});
	} else {
		findPrinter_close_box(impres);
	}
	return '1';
}

function endConnection() {
	if (qz.websocket.isActive()) {
		qz.websocket.disconnect().then(function () {
			updateState('Inactive', 'default');
		}).catch(handleConnectionError);
	} else {
		displayMessage('No active connection with QZ exists.', 'alert-warning');
	}
}

/// Detection ///
function findPrinter_return(query) {
	qz.printers.find(query).then(function (data) {
		var def_invoice = $('#default_invoice').val();
		printRETURN();
	});
}

/// Detection ///
function findPrinter_close_box(query) {
	qz.printers.find(query).then(function (data) {
		var def_invoice = $('#default_invoice').val();
		printCLOSEBOX();
	});
}

/// Detection ///
function findPrinter(query, mtz) {
baliza_i = 'tres';
log_dat += ";tres ";
console.log("findPrinter LN-14 TRES===========> ", baliza_i);
	qz.printers.find(query).then(function (data) {
		var def_invoice = $('#default_invoice').val();
		if (def_invoice === 'esc58') {
			printESCPOS_58(mtz);
		} else {
			printESCPOS_80(mtz);
		}
	});

}

function findDefaultPrinter(set) {
	qz.printers.getDefault().then(function (data) {
		displayMessage("<strong>Found:</strong> " + data);
		if (set) {
			setPrinter(data);
		}
		printESCPOS(); //imprimir con matriz en printESCPOS(mtz) 
	}).catch(displayError);
}

function findPrinters() {
	qz.printers.find().then(function (data) {
		var list = '';
		for (var i = 0; i < data.length; i++) {
			list += "&nbsp; " + data[i] + "<br/>";
		}

		displayMessage("<strong>Available printers:</strong><br/>" + list);
	}).catch(displayError);
}

function setPrinter(printer) {
	var cf = getUpdatedConfig();
	cf.setPrinter(printer);

	if (printer && typeof printer === 'object' && printer.name == undefined) {
		var shown;
		if (printer.file != undefined) {
			shown = "<em>FILE:</em> " + printer.file;
		}
		if (printer.host != undefined) {
			shown = "<em>HOST:</em> " + printer.host + ":" + printer.port;
		}

		$("#configPrinter").html(shown);
	} else {
		if (printer && printer.name != undefined) {
			printer = printer.name;
		}

		if (printer == undefined) {
			printer = 'NONE';
		}
		$("#configPrinter").html(printer);
	}
}

/// QZ Config ///
var cfg = null;

function getUpdatedConfig() {
	if (cfg == null) {
		cfg = qz.configs.create(null);
	}

	updateConfig();
	return cfg
}

function updateConfig() {
	var pxlSize = null;
	if ($("#pxlSizeActive").prop('checked')) {
		pxlSize = {
			width: $("#pxlSizeWidth").val(),
			height: $("#pxlSizeHeight").val()
		};
	}

	var pxlMargins = $("#pxlMargins").val();
	if ($("#pxlMarginsActive").prop('checked')) {
		pxlMargins = {
			top: $("#pxlMarginsTop").val(),
			right: $("#pxlMarginsRight").val(),
			bottom: $("#pxlMarginsBottom").val(),
			left: $("#pxlMarginsLeft").val()
		};
	}

	var copies = 1;
	var jobName = null;
	if ($("#rawTab").hasClass("active")) {
		copies = $("#rawCopies").val();
		jobName = $("#rawJobName").val();
	} else {
		copies = $("#pxlCopies").val();
		jobName = $("#pxlJobName").val();
	}

	cfg.reconfigure({
		altPrinting: $("#rawAltPrinting").prop('checked'),
		encoding: $("#rawEncoding").val(),
		endOfDoc: $("#rawEndOfDoc").val(),
		perSpool: $("#rawPerSpool").val(),

		colorType: $("#pxlColorType").val(),
		copies: copies,
		density: $("#pxlDensity").val(),
		duplex: $("#pxlDuplex").prop('checked'),
		interpolation: $("#pxlInterpolation").val(),
		jobName: jobName,
		legacy: $("#pxlLegacy").prop('checked'),
		margins: pxlMargins,
		orientation: $("#pxlOrientation").val(),
		paperThickness: $("#pxlPaperThickness").val(),
		printerTray: $("#pxlPrinterTray").val(),
		rasterize: $("#pxlRasterize").prop('checked'),
		rotation: $("#pxlRotation").val(),
		scaleContent: $("#pxlScale").prop('checked'),
		size: pxlSize,
		units: $("input[name='pxlUnits']:checked").val()
	});
}

function printESCPOS_58(mtz) {
baliza_i= 'cuatro';
log_dat += ";cuatro ";
console.log("printESCPOS_58 LN-273 CUATRO-A===========> ", baliza_i);
	var impres = $('#printer_default').val(); //"Tickets1"
	var prod = mtz['products'];
	var config = qz.configs.create(impres, {
		encoding: 'Cp850'
	}); // Toggle CP1252 in Java 
	var rs = new Array();
    
	var mrt = '../image/catalog/demo/' + mtz['company']['rut_lg']; //'descarga_771688933.jpg';

	rs.push('\x1B' + '\x61' + '\x31'); // center align
//console.log("RUT IMG =272==> ",mrt);
	//if (mtz['company']['rut_lg'] == '') {
//console.log("SI EXISTE IMG =272==> ",mtz['order']['ticket_code']);
    if (mtz['company']['lg_exit'] == '0') {    
		//rs.push('\x0A');
	} else {
		rs.push({
			type: 'raw',
			format: 'image',
			flavor: 'file',
			data: mrt,
			options: {
				language: "escp",
				dotDensity: 'double'
			}
		});
	}
	rs.push('\x1B' + '\x40'); // init
	rs.push('\x1B' + '\x61' + '\x31'); // center align	
	rs.push('\x1B' + '\x45' + '\x0D'); // bold on
	rs.push('\x1B' + '\x21' + '\x30'); // em mode on
	rs.push(mtz['company']['name'] + '');
	rs.push('\x1B' + '\x21' + '\x0A' + '\x1B' + '\x45' + '\x0A'); // em mode off
	rs.push('\x1B' + '\x45' + '\x0A'); // bold off
	rs.push('\x0A' + '\x0A');
	rs.push('FACTURA SIMPLIFICADA' + '\x0A');
	rs.push(mtz['company']['busines_name'] + '\x0A');
	rs.push(mtz['company']['address'] + '\x0A');
	if (!isEmpty(mtz['company']['cif'])) {
		rs.push('CIF ' + mtz['company']['cif'] + '\x0A');
	}
	if (!isEmpty(mtz['company']['tf'])) {
		rs.push('Tel. :' + mtz['company']['tf'] + '\x0A');
	}
	if (!isEmpty(mtz['till'])) {
		rs.push('Caja: \"' + mtz['till'] + '\"\x0A');
	}
	if (!isEmpty(mtz['order']['id'])) {
		rs.push('Ref. Factura: ' + mtz['order']['code_prefix'] + '-s-' + mtz['order']['id'] + '\x0A');
	}
	if (!isEmpty(mtz['order']['date'])) {
		rs.push(mtz['order']['date'] + '\x0A');
	}
	rs.push('\x0A');	
	if (!isEmpty(mtz['customer']['firstname']) || !isEmpty(mtz['customer']['lastname'])) {
		if ((mtz['customer']['firstname']).trim() != 'POS_USER') {
			rs.push('CLIENTE: ' + mtz['customer']['firstname'] + " " + mtz['customer']['lastname'] + '\x0A');
			var c_id_tx = mtz['customer']['id_tax'];		
			if (c_id_tx === null) {
			} else {
				if (c_id_tx.length > 0) {
					rs.push('CIF ' + mtz['customer']['id_tax'] + '\x0A');
				}
			}
			if (mtz['customer']['address_1']=== null) {
			}else{
				if (mtz['customer']['address_1'].length > 1) {
					rs.push('Dir.: ' + mtz['customer']['address_1'] + '\x0A');
				}
			}
			rs.push('\x0A');
		}
	}	
	rs.push('\x1B' + '\x61' + '\x30'); // left align
	rs.push('\x0A');	
	//###############PRODUCTOS###################################
	$.each(prod, function (ind, elem) { // 32= 2+ 14 + 8 + 8
		var resta = 0;
		var res = "";
		var num = elem['quantity'];
		var nm = num.toString();
		var xnm = nm.length;
        var name = elem['name'];
	    name = name.replace("<!--@@@-->", "");
		var name_cab = elem['name2'];
		if (name == "Discount") {
			name_cab = "Descuento"
		}
        //console.log("NAME =344==> ",name );
		var mtr_name = name.split("<br />")
        //console.log("MTR_NAME =346==> ", mtr_name);      
		//name_cab= mtr_name[0];
		if (name == "Discount") {
			name_cab = "Descuento"
		}        
		var nm_mtr_name = mtr_name.length;
        //console.log("NM_MTR_NAME ===> ", nm_mtr_name);
        
		var prt = (elem['p_total']).replace("€", "");
		var xname = name_cab.length;
		//var xpr = pr.length;
		var xprt = prt.length;

		var disc = (elem['discount']).replace("€", "");
		var xdisc = disc.length;
		//Productos
		resta = 3 - xnm;
		if (resta == 0) {
			res += nm.toString();
			resta = 0;
		} else if (resta > 0) {
			res += nm.toString() + spaces(xnm);
			resta = 0;
		} else {
			res += nm.toString();
		}
		porcion = 13 + resta;
		resta = porcion - xname;
		if (resta > 0) {
			res += " " + name_cab + spaces(resta);
			resta = 0;
		} else if (resta < 0) {
			res += " " + recorte(name_cab, resta);
		} else { // = 0;
			res += name_cab;
			resta = 0;
		}
		resta = 8 - xprt;
		if (resta == 0) {
			res += prt;
		} else if (resta > 0) {
			res += spaces(resta) + prt;
		} else {
			res += recorte(prt, resta);
		}

		resta = 8 - xprt;
		if (resta == 0) {
			res += prt;
		} else if (resta > 0) {
			res += spaces(resta) + prt;
		} else {
			res += recorte(prt, resta);
		}
		res += '\x0A';
		var i;
		var minm = nm_mtr_name - 1;
        //console.log("MTR_NAME ===> ",mtr_name );
		if (nm_mtr_name > 1) {
			for (i = 1; i <= minm; i++) {
				if (mtr_name[i] != "rebaja") {
					res += spaces(3) + mtr_name[i] + '\x0A';
                    //console.log("mtr_name-i] ===> ", mtr_name[i]);                    
				}
			}
		}
		rs.push(res);
		if (disc != 0) {
			res = spaces(2) + spaces(5) + 'rebaja' + spaces(4);
			resta = 8 - xdisc - 1;
			res += spaces(resta) + disc + spaces(8);
			res += '\x0A';
			rs.push(res);
		}

	});

	if (mtz['order']['fac_gift'] == '0') {
		rs.push('\x0A');
		rs.push('\x1B' + '\x45' + '\x0D'); // bold on
		pay_tt = mtz['order']['final_payment'];
		pay_tt = pay_tt.replace("€", "");
		xpay_tt = pay_tt.length;
		xsp = 32 - (13 + xpay_tt);
		rs.push('TOTAL A PAGAR' + spaces(xsp) + pay_tt + '\x0A');
		rs.push('\x1B' + '\x45' + '\x0A'); // bold off 
		var tvp = (mtz['order']['ticket_val_pay']);
		if (tvp != "0") {
			tk_val = mtz['order']['ticket_val_pay'];
			tk_val = tk_val.replace("€", "");
			xtk_val = tk_val.length;
			xsp = 32 - (20 + xtk_val);
			rs.push('TICKET DE DEVOLUCION' + spaces(xsp) + tk_val + '\x0A');
		} else {
			tk_val = "0"
		}		
		if (cash_delivered.length > 0) {
			cash_delivered = (parseFloat(cash_delivered)).toFixed(2);
			xcash_delivered = cash_delivered.length;
			var mode_pay = mtz['order']['pay_mode'];
			mode_pay = mode_pay.toUpperCase();
			xmode_pay = mode_pay.length;
			var moneda = mtz['order']['money'];
			var xmoneda = moneda.length + 1;
			xsp = 32 - (xmode_pay + xmoneda + xcash_delivered);
			rs.push(mode_pay + ' ' + moneda + spaces(xsp) + cash_delivered + '\x0A');
			returned_client = mtz['order']['Returned']; //parseInt(cash_delivered) -  parseInt(pay_tt);
			returned_client = returned_client.toFixed(2);
			xreturned_client = returned_client.length;
			xsp = 32 - (10 + xmoneda + xreturned_client);
			rs.push('DEVOLUCI0N ' + moneda + spaces(xsp) + returned_client + '\x0A');
		}
		rs.push('\x1B' + '\x45' + '\x0D'); // bold on
		discount_tt = mtz['order']['discount'];
		discount_tt = discount_tt.replace('€', '');
		xdiscount_tt = discount_tt.length;
		if (xdiscount_tt > 0) {
			xsp = 32 - (12 + xdiscount_tt);
			rs.push('TOTAL AHORRO' + spaces(xsp) + discount_tt + '\x0A');
		}
		rs.push('\x1B' + '\x45' + '\x0A'); // bold off 
		rs.push('\x0A');
		//################IMPUESTOS####################  
		rs.push('\x1B' + '\x45' + '\x0D'); // bold on
		rs.push('IVA         B. IMPONIBLE   CUOTA' + '\x0A');
		rs.push('\x1B' + '\x45' + '\x0A'); // bold off
		var resta;
		var miva;
		var xmiva;
		var bi;
		var xbi;
		var mnu1;
		var mnu2;
		var mtx = mtz['taxation'];
		$.each(mtx, function (ind, elem) { // 32= 12+ 12 + 8 
			miva = elem['iva'];
			miva = miva.replace("<br />", " - ");
			xmiva = miva.length;
			resta = 12 - xmiva;
			if (xmiva < 13) {
				miva = miva + spaces(resta);
			} else {
				miva = recorte(miva, resta);
			}
			bi = elem['tax_base'];
			bi = (String(bi)).replace("€", "");
			xbi = (String(bi)).length;
			if (xbi < 12) {
				mnu2 = 1
				mnu1 = 12 - (1 + xbi)
				bi = spaces(mnu1) + bi + spaces(mnu2);
			} else {
				resta = (xbi + 1) - 12;
				bi = recorte(bi, resta);
			}
			cu = elem['share'];
			cu = cu.replace("€", "");
			xcu = cu.length;
			resta = 8 - xcu;
			if (xcu < 9) {
				cu = spaces(resta) + cu;
			} else {
				cu = recorte(cu, resta);
			}
			rs.push(miva + bi + cu + '\x0A');
		});
	} else {
		var tk_val = "0";
	}
	rs.push('\x0A');
	rs.push('LE ATENDIO: ' + mtz['company']['user'] + '\x0A');
	rs.push('\x0A');
	rs.push(mtz['order']['msg'] + '\x0A');
	rs.push({
		type: 'raw',
		format: 'image',
		flavor: 'file',
		data: 'zq/assets/img/cb_o.jpg',

		options: {
			language: "escp",
			dotDensity: 'double'
		}
	});
	var tkt_d = mtz['order']['ticket_credit'];
	var xtkt_d = (String(tkt_d)).length;
	xtkt_d = xtkt_d.toString();
	xtkt_d = xtkt_d.replace("€", "");
	if (tk_val != "0") {
		rs.push('\x0A' + '\x0A');
		rs.push('= = = = = = = = = = = = = = = = = = = = = = = = ' + '\x0A');
		tkt_d = spaces(10 - xtkt_d) + tkt_d;
		rs.push('TICKET DE DEVOLUCION:' + '\x0A');
		rs.push('CREDITO AUN DISPONIBLE' + tkt_d + '\x0A');
		rs.push('CODIGO: ' + mtz['order']['ticket_code']);
		rs.push('\x0A');
		rs.push({
			type: 'raw',
			format: 'image',
			flavor: 'file',
			data: 'zq/assets/img/cb_tr.jpg',
			options: {
				language: "escp",
				dotDensity: 'double'
			}
		});
	}
	rs.push('\x1B' + '\x61' + '\x30');
	rs.push('\x0A' + '\x0A' + '\x0A' + '\x0A');
    rs.push('\x0A' + '\x0A' + '\x0A' + '\x0A');
    /*rs.push('\x0A' + '\x0A' + '\x0A' + '\x0A');*/
    rs.push('\x1B' + '\x69');
	rs.push('\x10' + '\x14' + '\x01' + '\x00' + '\x05');
	baliza_i= 'cinco';
console.log("printESCPOS_58 =638==>", baliza_i)
	qz.print(config, rs);
}

function printESCPOS_80(mtz) {
	baliza_i= 'cuatro';
	log_dat += ";cuatro ";
	console.log("printESCPOS_58 LN-273 CUATRO-A===========> ", baliza_i);
	var impres = $('#printer_default').val(); //"Tickets1"
	var prod = mtz['products'];
    //prod = prod.replace("<!--@@@-->", "");
	var config = qz.configs.create(impres, {
		encoding: 'Cp850'
	}); // Toggle CP1252 in Java  
	var rs = new Array();
	var mrt = '../image/catalog/demo/' + mtz['company']['rut_lg'];
	rs.push('\x1B' + '\x61' + '\x31'); // center align
    //console.log("LN 567 <=====================");
	if (mtz['company']['lg_exit'] == '0') {  
		//rs.push('\x0A');
	} else {
		rs.push({
			type: 'raw',
			format: 'image',
			flavor: 'file',
			data: mrt,
			options: {
				language: "escp",
				dotDensity: 'double'
			}
		});
	}
    //console.log("LN 581 <=====================");
	rs.push('\x1B' + '\x40'); // init
	rs.push('\x1B' + '\x61' + '\x31'); // center align
	rs.push('\x1B' + '\x45' + '\x0D'); // bold on
	rs.push('\x1B' + '\x21' + '\x30'); // em mode on
	rs.push(mtz['company']['name'] + '');
	rs.push('\x1B' + '\x21' + '\x0A' + '\x1B' + '\x45' + '\x0A'); // em mode off
	rs.push('\x1B' + '\x45' + '\x0A'); // bold off
	rs.push('\x0A' + '\x0A');
	rs.push('FACTURA SIMPLIFICADA' + '\x0A');
	rs.push(mtz['company']['busines_name'] + '\x0A');
	rs.push(mtz['company']['address'] + '\x0A');
	if (!isEmpty(mtz['company']['cif'])) {
		rs.push('CIF ' + mtz['company']['cif'] + '\x0A');
	}
	if (!isEmpty(mtz['company']['tf'])) {
		rs.push(mtz['company']['tf'] + '\x0A');
	}
	if (!isEmpty(mtz['till'])) {
		rs.push('Caja: \"' + mtz['till'] + '\"\x0A');
	}
	if (!isEmpty(mtz['order']['id'])) {
		rs.push('Ref. Factura: ' + mtz['order']['code_prefix'] + '-s-' + mtz['order']['id'] + '\x0A');
	}
	if (!isEmpty(mtz['order']['date'])) {
		rs.push(mtz['order']['date'] + '\x0A');
	}
	rs.push('\x0A');
	rs.push('\x0A');
	if (!isEmpty(mtz['customer']['firstname']) || !isEmpty(mtz['customer']['lastname'])) {
		if ((mtz['customer']['firstname']).trim() != 'POS_USER') {
			rs.push('CLIENTE: ' + mtz['customer']['firstname'] + " " + mtz['customer']['lastname'] + '\x0A');
			var c_id_tx = mtz['customer']['id_tax'];
			if (c_id_tx === null) {} else {
				if (c_id_tx.length > 0) {
					rs.push('CIF ' + mtz['customer']['id_tax'] + '\x0A');
				}
			}
			if (mtz['customer']['address_1'] != null) {
				rs.push('Dir.: ' + mtz['customer']['address_1'] + '\x0A');
			}
			rs.push('\x0A');
		}
	}
	rs.push('\x1B' + '\x61' + '\x30'); // left align
	rs.push('\x0A');
	//###############PRODUCTOS###################################
//console.log("ENTRO A PRODUCTO <======LN 640========");
	$.each(prod, function (ind, elem) { // 32= 12+ 12 + 8 ===> 48 = 16 + 16 + 16 =====> 15 +15 + 15 =  45
		var resta = 0;
		var res = "";
		var num = elem['quantity']; //numero
		var nm = num.toString(); //string
        var name = elem['name']; 
        name = name.replace("<!--@@@-->", "");
		var name_cab = elem['name2'];
		if (name_cab == "Discount" || name_cab == "DISCOUNT") {
			name_cab = "Descuento"
		}
//console.log("NAME CAB =645==> ", name_cab);
		var mtr_name = name.split("<br />")
  //console.log("MTR_NAME ===> ", mtr_name);
		//name_cab = mtr_name[0];
		var nm_mtr_name = mtr_name.length;
  //console.log("NM_MTR_NAME ===> ", nm_mtr_name);
		var pr = (elem['price']).replace("€", "");
		var prt = (elem['p_total']).replace("€", "");
		var xnm = nm.length;
var xname = name_cab.length;
		var xpr = pr.length;
		var xprt = prt.length;
		var disc = (elem['discount']).replace("€", "");
		var xdisc = disc.length;
		resta = 2 - xnm;
		if (resta == 0) {
			res += nm;
			resta = 0;
		} else if (resta > 0) {
			res += spaces(resta) + nm;
			resta = 0;
		} else {
			res += nm;
		}
		porcion = 19 - resta;
		resta = porcion - xname;
//console.log("ENTRO A PRODUCTO <======LN 678========");
		if (resta > 0) {
			res += " " + name_cab + spaces(resta);
			resta = 0;
		} else if (resta < 0) {
			res += " " + recorte(name_cab, resta);
		} else { // = 0;
			res += name_cab;
			resta = 0;
		}
		resta = 11 - xpr;
		if (resta == 0) {
			res += pr;
		} else if (resta > 0) {
			res += spaces(resta) + pr;
		} else {
			res += recorte(pr, resta);
		}
		resta = 11 - xprt;
		if (resta == 0) {
			res += prt;
		} else if (resta > 0) {
			res += spaces(resta) + prt;
		} else {
			res += recorte(prt, resta);
		}
		res += '\x0A';
	
		var i;
		var minm = nm_mtr_name - 1;
 //console.log("MTR_NAME ===> ",mtr_name );
		if (nm_mtr_name > 1) {
			for (i = 1; i <= minm; i++) {
				if (mtr_name[i] != "rebaja") {
					res +=  spaces(3) + mtr_name[i] + '\x0A';
                    //res += elem['name3'] + '\x0A';
                    //console.log("NAME-3 ===> ", elem['name3']);
				}
			}
		}		
		
		
		rs.push(res);
		if (disc != 0) {
			res = spaces(3) + 'rebaja' + spaces(6) + spaces(5);
			resta = 13 - xdisc;
			res += spaces(resta) + disc + spaces(10);
			res += '\x0A';
			rs.push(res);
		}
	});
	//######TOTALES###########################
 //console.log("ENTRO A PRODUCTO <======LN 730========");   
	if (mtz['order']['fac_gift'] == '0') {
		rs.push('\x0A');
		rs.push('\x1B' + '\x45' + '\x0D'); // bold on
		pay_tt = mtz['order']['final_payment'];
		pay_tt = pay_tt.replace("€", "");
		pay_tt = pay_tt.replace(",", "");
		xpay_tt = pay_tt.length;
		xsp = 44 - (13 + xpay_tt);
		rs.push('TOTAL A PAGAR' + spaces(xsp) + pay_tt + '\x0A');
		rs.push('\x1B' + '\x45' + '\x0A'); // bold off 
		var tvp = (mtz['order']['ticket_val_pay']);
		if (tvp != "0") {
			tk_val = mtz['order']['ticket_val_pay'];
			tk_val = tk_val.replace("€", "");
			xtk_val = tk_val.length;
			if (tk_val != 0) {
				xsp = 44 - (20 + xtk_val);
				rs.push('TICKET DE DEVOLUCION' + spaces(xsp) + tk_val + '\x0A');
			}
		} else {
			tk_val = "0"
		}
		cash_delivered_s = cash_delivered.toString();
		cash_delivered_s = cash_delivered_s.replace(",", "");
		cash_delivered = parseFloat(cash_delivered_s);
		if (cash_delivered_s.length > 0) {
			xcash_delivered = cash_delivered_s.length;
			var mode_pay = mtz['order']['pay_mode'];
			mode_pay = mode_pay.toUpperCase();
			xmode_pay = mode_pay.length;
			cash_delivered2 = cash_delivered.toFixed(2);
			xcash_delivered2 = cash_delivered2.length;
			var moneda = mtz['order']['money'];
			var xmoneda = moneda.length + 1;
			xsp = 44 - (xmode_pay + xmoneda + xcash_delivered2);
			rs.push(mode_pay + ' ' + moneda + spaces(xsp) + cash_delivered2 + '\x0A');
			returned_client = mtz['order']['Returned'];
			returned_client = returned_client.toFixed(2);
			xreturned_client = returned_client.length;
			xsp = 44 - (10 + xmoneda + xreturned_client);
			rs.push('DEVOLUCI0N ' + moneda + spaces(xsp) + returned_client + '\x0A');
		}
		rs.push('\x1B' + '\x45' + '\x0D'); // bold on
		discount_tt = mtz['order']['discount'];
		discount_tt = discount_tt.replace('€', '');
		xdiscount_tt = discount_tt.length;
		int_discount_tt = parseInt(discount_tt);
		if (int_discount_tt > 0) {
			xsp = 44 - (12 + xdiscount_tt);
			rs.push('TOTAL AHORRO' + spaces(xsp) + discount_tt + '\x0A');
		}
		rs.push('\x1B' + '\x45' + '\x0A'); // bold off 
		rs.push('\x0A');
		//################IMPUESTOS####################  
//console.log("ENTRO A PRODUCTO <======LN 785========");
		rs.push('\x1B' + '\x45' + '\x0D'); // bold on
		rs.push('IVA             B. IMPONIBLE           CUOTA' + '\x0A');
		rs.push('\x1B' + '\x45' + '\x0A'); // bold off
		var resta;
		var miva;
		var xmiva;
		var bi;
		var xbi;
		var mnu1;
		var mnu2;
		var mtx = mtz['taxation'];
		$.each(mtx, function (ind, elem) { // 32= 12+ 12 + 8 ===> 48 = 16 + 16 + 16
			miva = elem['iva'];
			miva = miva.replace("<br />", " - ");
			xmiva = miva.length;
			resta = 15 - xmiva;
			if (xmiva < 16) {
				miva = miva + spaces(resta);
			} else {
				miva = recorte(miva, resta);
			}
			bi = elem['tax_base'];
			bi = (String(bi)).replace("€", "");
			xbi = (String(bi)).length;
			if (xbi < 15) {
				mnu1 = 14 - (xbi)
				bi = spaces(mnu1) + bi;
			} else {
				resta = (xbi) - 14;
				bi = recorte(bi, resta);
			}
			cu = elem['share'];
			cu = cu.replace("€", "");
			xcu = cu.length;
			resta = 15 - xcu;
			if (xcu < 15) {
				cu = spaces(resta) + cu;
			} else {
				cu = recorte(cu, resta);
			}
			rs.push(miva + bi + cu + '\x0A');
		});
	}
//console.log("ENTRO A PRODUCTO <======LN 829========");
	rs.push('\x0A');
	rs.push('LE ATENDIO: ' + mtz['company']['user'] + '\x0A');
	rs.push('\x0A');
	rs.push(mtz['order']['msg'] + '\x0A');
	//rs.push('TIENE 30 DIAS PARA DEVOLVER' + '\x0A');
	rs.push({
		type: 'raw',
		format: 'image',
		flavor: 'file',
		data: 'zq/assets/img/cb_o.jpg',
		options: {
			language: "escp",
			dotDensity: 'double'
		}
	});
	var tkt_d = mtz['order']['ticket_credit'];
	var xtkt_d = (String(tkt_d)).length;
	var xtkt_d = xtkt_d.toString();
	var xtkt_d = xtkt_d.replace("€", "");
//console.log("DEREGALO == LN 849 ===> ",mtz['order']['total']);
 //console.log("MIVIS <======LN 850 ========",mtz[ 'mis' ]);   
 if(mtz[ 'order' ][ 'total' ] != '-'){
//console.log("ENTRO PORQUE TIENE PRECIOS <============================");
	if (tk_val != 0) {
		rs.push('\x0A' + '\x0A');
		rs.push('= = = = = = = = = = = = = = = = = = = = = = = = ' + '\x0A');
		tkt_d = spaces(9 - xtkt_d) + tkt_d;
		rs.push('TICKET DE DEVOLUCION:' + '\x0A');
		rs.push('CREDITO AUN DISPONIBLE' + tkt_d + '\x0A');
		rs.push('CODIGO: ' + mtz['order']['ticket_code']);
		rs.push('\x0A' + '\x0A' + '\x0A');
		rs.push({
			type: 'raw',
			format: 'image',
			flavor: 'file',
			data: 'zq/assets/img/cb_tr.jpg',
			options: {
				language: "escp",
				dotDensity: 'double'
			}
		});
	}
  }
//console.log("ENTRO A PRODUCTO <======LN 869========");
	rs.push('\x1B' + '\x61' + '\x30');
	rs.push('\x0A' + '\x0A' + '\x0A' + '\x0A' + '\x0A' + '\x0A' + '\x0A' + '\x0A' + '\x0A');
	rs.push('\x1B' + '\x69');
	rs.push('\x10' + '\x14' + '\x01' + '\x00' + '\x05');
	baliza_i= 'cinco';
console.log("printESCPOS_58 =638==>", baliza_i)   
	qz.print(config, rs).catch(displayError);
}

function printRETURN() {
//console.log("MATRIZ LN821 ===> ", matriz);
//console.log("matriz['rut_lg']==965===> ", matriz['company']['rut_lg']);
	var impres = $('#printer_default').val(); //"Tickets1"
	var config = qz.configs.create(impres, {
		encoding: 'Cp850' // Toggle CP1252 in Java 
	});
	var rs = new Array();
    
	//***********************************
	var mrt = '../image/catalog/demo/' + matriz['company']['rut_lg']; //'descarga_771688933.jpg';
//console.log("MRT==974===> ", mrt );
	rs.push('\x1B' + '\x61' + '\x31'); // center align

	if (matriz['company']['rut_lg'] == '') {
		//rs.push('\x0A');
	} else {
		rs.push({
			type: 'raw',
			format: 'image',
			flavor: 'file',
			data: mrt,
			options: {
				language: "escp",
				dotDensity: 'double'
			}
		});
	}
	//***********************************    
    
	rs.push('\x1B' + '\x40'); // init
	rs.push('\x1B' + '\x61' + '\x31'); // center align
	rs.push('\x1B' + '\x45' + '\x0D'); // bold on
	rs.push('\x1B' + '\x21' + '\x30'); // em mode on
	rs.push(matriz['company']['name'] + '');
	rs.push('\x1B' + '\x21' + '\x0A' + '\x1B' + '\x45' + '\x0A'); // em mode off
	rs.push('\x1B' + '\x45' + '\x0A'); // bold off
	rs.push('\x0A');
	rs.push(matriz['company']['busines_name'] + '\x0A');
	if (!isEmpty(matriz['company']['cif'])) {
		rs.push('CIF ' + matriz['company']['cif'] + '\x0A');
	}
	if (!isEmpty(matriz['company']['tf'])) {
		rs.push(matriz['company']['tf'] + '\x0A');
	}
	if (!isEmpty(matriz['order']['id'])) {
		rs.push('Ref. Factura: ' + matriz['order']['code_prefix'] + '-s-' + matriz['order']['id'] + '\x0A');
	}
	if (!isEmpty(matriz['order']['date'])) {
		rs.push(matriz['order']['date'] + '\x0A');
	}

	if (!isEmpty(matriz['customer']['firstname']) || !isEmpty(matriz['customer']['lastname'])) {
		if ((matriz['customer']['firstname']).trim() != 'POS_USER') {
			rs.push('\x0A');
			rs.push('CLIENTE: ' + matriz['customer']['firstname'] + " " + matriz['customer']['lastname'] + '\x0A');
			if (!isEmpty(matriz['customer']['id_tax'])) {
				rs.push('CIF ' + matriz['customer']['id_tax'] + '\x0A');
			}
			if (matriz['customer']['address_1'].length > 1) {
				rs.push('Dir.: ' + matriz['customer']['address_1'] + '\x0A');
			}
		}
	}
	var moneda = matriz['order']['money'];
	rs.push('\x1B' + '\x61' + '\x30'); // left align
	//############### DEVOLUCIÓN ###################################
	rs.push('\x1B' + '\x61' + '\x31'); // center align
	rs.push('\x1B' + '\x45' + '\x0D'); // bold on
	rs.push('\x0A' + '\x0A');
	rs.push('DEVOLUCION COMPRA ');
	rs.push('\x1B' + '\x21' + '\x0A' + '\x1B' + '\x45' + '\x0A'); // em mode off
	rs.push('\x1B' + '\x45' + '\x0A'); // bold off
	rs.push('\x0A');
	rs.push('Tipo: ' + matriz['return']['type'] + '\x0A' + '\x0A');
	rs.push('C0DIGO: ' + '\x0A');
	rs.push('\x1B' + '\x45' + '\x0D');
	rs.push(matriz['return']['code'] + '\x0A');
	rs.push('\x1B' + '\x45' + '\x0A'); // bold off
	rs.push('\x0A');
	rs.push('VALOR: ' + moneda + '\x0A');
	rs.push('\x1B' + '\x61' + '\x31'); // center align
	rs.push('\x1B' + '\x45' + '\x0D'); // bold on
	rs.push('\x1B' + '\x21' + '\x30'); // em mode on
	rs.push(matriz['return']['value']);
	rs.push('\x1B' + '\x21' + '\x0A' + '\x1B' + '\x45' + '\x0A'); // em mode off
	rs.push('\x1B' + '\x45' + '\x0A'); // bold off	
	rs.push('\x0A' + '\x0A');
	rs.push('Caja: \"' + matriz['return']['box'] + '\"\x0A');
	rs.push('Fecha: ' + matriz['return']['date'] + '\x0A');
	rs.push('Le atendió: ' + matriz['return']['user'] + '\x0A');
	rs.push({
		type: 'raw',
		format: 'image',
		flavor: 'file',
		data: 'zq/assets/img/cb_tr.jpg',
		options: {
			language: "escp",
			dotDensity: 'double'
		}
	});
	rs.push('\x1B' + '\x61' + '\x30');
	rs.push('\x0A' + '\x0A' + '\x0A' + '\x0A' + '\x0A' + '\x0A' + '\x0A' + '\x0A' + '\x0A');
	rs.push('\x1B' + '\x69');
	rs.push('\x10' + '\x14' + '\x01' + '\x00' + '\x05');
	qz.print(config, rs);
}

function printCLOSEBOX() {
//console.log("matriz['rut_lg']==965===> ", matriz );
//console.log("matriz['rut_lg']==965===> ", matriz['rut_lg']);
	var impres = $('#printer_default').val(); //"Tickets1"
	var config = qz.configs.create(impres, {
		encoding: 'Cp850' // Toggle CP1252 in Java 
	});
	var rs = new Array();
    
	//***********************************
	var mrt = '../image/catalog/demo/' + matriz['rut_lg']; //'descarga_771688933.jpg';
//console.log("MRT==974===> ", mrt );
	rs.push('\x1B' + '\x61' + '\x31'); // center align

	if (matriz['logo_exit'] == false) {
		//rs.push('\x0A');
	} else {
		/*rs.push({
			type: 'raw',
			format: 'image',
			flavor: 'file',
			data: mrt,
			options: {
				language: "escp",
				dotDensity: 'double'
			}
		});*/
	}
	//***********************************        
    
    
	rs.push('\x1B' + '\x40'); // init
	rs.push('\x1B' + '\x61' + '\x31'); // center align
	rs.push('\x1B' + '\x45' + '\x0D'); // bold on
	rs.push('\x1B' + '\x21' + '\x30'); // em mode on
	rs.push('CIERRE DE CAJA');
	rs.push('\x1B' + '\x21' + '\x0A' + '\x1B' + '\x45' + '\x0A'); // em mode off
	rs.push('\x1B' + '\x45' + '\x0A' + '\x0A'); // bold off
	rs.push('\x1B' + '\x61' + '\x30'); // left align
	rs.push('\x0A');
	rs.push('Vendedor: ' + matriz['name_real'] + '\x0A');
	rs.push('Caja: ' + matriz['caja'] + '\x0A');
	rs.push('Apertura: ' + matriz['date_o'] + '\x0A');
	rs.push('Cierre: ' + matriz['date_c'] + '\x0A' + '\x0A');
    
	rs.push('ARQUEO DE CAJA' + '\x0A');
	rs.push('Efectivo: ' + matriz['date_box']['caja'] + '\x0A');
	rs.push('Moneda: ' + matriz['date_box']['currency'] + '\x0A');
	rs.push('\x0A');
        
	rs.push('VENTAS' + '\x0A');
	var mto = matriz['methos'];
	for (i = 0; i < mto.length; i++) {
        //if(mto[i]['name']=='Currency' ){mto[i]['name'] ='Moneda';}
		if(mto[i]['name']!='currency'){
			rs.push(mto[i]['name'] + ': ' + mto[i]['val'] + '\x0A');
	    }
	}
	rs.push('\x1B' + '\x45' + '\x0D'); // bold on
	rs.push('Total: ' + matriz['date_box']['sales'] + '\x0A');
	rs.push('\x1B' + '\x45' + '\x0A'); // bold off
	rs.push('\x0A');

	rs.push('DESCUENTOS: ' + matriz['date_box']['discount'] + '\x0A');
	rs.push('\x0A');
    
	rs.push('DEVOLUCIONES' + '\x0A');
    rs.push('En efectivo: ' + matriz['date_box']['r_efectivo'] + '\x0A');
    rs.push('Tiquet de credito: ' + matriz['date_box']['r_credito'] + '\x0A');
    rs.push('Reemplazado: ' + matriz['date_box']['r_reemplace'] + '\x0A');
	rs.push('Total devoluciones: ' + matriz['date_box']['returns'] + '\x0A');

	rs.push('\x0A');
	rs.push('\x1B' + '\x61' + '\x30');
	rs.push('\x0A' + '\x0A' + '\x0A' + '\x0A');
	rs.push('\x1B' + '\x69');
	rs.push('\x10' + '\x14' + '\x01' + '\x00' + '\x05');
	qz.print(config, rs);
	/*$.ajax({
		url: 'ajax.php?action=closeboxfinish&value=0',
		success: function () {
			window.location = 'index.php?action=logout';
		}
	});*/
}

function spaces(nm) {
	var r = '';
	for (i = 0; i < nm; i++) {
		r += ' ';
	}
	return r;
}

function recorte(st, nm) {
	nm = st.length + nm;
	if (nm > 0) {
		var r = '';
		var respu = st.split("");
		for (i = 0; i < nm; i++) {
			r += respu[i];
		}
	} else {
		r = '';
	}
	return r;
}

function isEmpty(str) {
	var re = 0;
	if (!str || 0 === str.length) {
		re = 1
	}
	if (!str || /^\s*$/.test(str)) {
		re = 1
	}
	return re;
}

//*********Inicialize printer***************************

function startConnection_inicialize(config) { //LN 870
//console.log("startConnection_inicialize ===> ");
	var impres = $('#printer_default').val(); //"Tickets1"	
	if (!qz.websocket.isActive()) {;
		qz.websocket.connect().then(function () {
			findPrinter_inicialize(impres);
		});
	} else {
		findPrinter_inicialize(impres);
	}
}

function findPrinter_inicialize(query) {
	qz.printers.find(query).then(function (data) {
		print_inicialize();
	});

}

