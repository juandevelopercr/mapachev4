// make console.log safe to use
//window.console||(console={log:function(){}});
// call jRespond and add breakpoints
var mvarresize;
var jRes = jRespond([{
		label: 'pequegno',
		enter: 0,
		exit: 600 /*767*/
	}, {
		label: 'grande',
		enter: 600,
		/*768,*/
		exit: 10000
	}

]);

// usage
var outputStr = document.getElementById('output');

jRes.addFunc({
	breakpoint: 'pequegno',
	enter: function () {
		/*outputStr.innerHTML = 'Actualmente la pantalla es pequeña.';*/
		xpequegno();
	}
});

jRes.addFunc({
	breakpoint: 'grande',
	enter: function () {
		/*outputStr.innerHTML = 'Actualmente la pantalla es grande.';*/
		xgrande()
	}
});


function xgrande() {
	$('#section-section1').css("display", "inline");
	$('#section-section2').css("display", "none");
	$('#section-section3').css("display", "none");
	window.location.hash = "#section-section1";
	altura();
}

function xpequegno() {
	$('#section-section1').css("display", "none");
	$('#section-section2').css("display", "inline");
	$('#section-section3').css("display", "inline");
	altura();
}

function altura() {
	height = $(window).height();
	height = height;
	$("#uno").height(height);
	$("#dos").height(height);
	$("#tres").height(height);
	$("#cuatro").height(height);
}

function anchura() {
	width = $(window).width();
	$("#tres").width(width);
	$("#cuatro").width(width);
}
$(window).resize(function () {
	altura();
});
