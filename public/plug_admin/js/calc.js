
function calculate_loan_amount() {
	var form = document.mortgage_calc_form;
	form.loan.value = (form.total_property_value.value - form.deposit.value); 
}

var calculate_mortgage = function(obj){
	var $this = $(obj);
	var form = document.mortgage_calc_form;
	// do field validation
	/*if (form.pcs.value == "" & !isNaN(form.pcs.value)){
		alert( "N of pieces is required." );
		form.pcs.focus();
	} else */
    if (form.lgth.value == "" || isNaN(form.lgth.value)){
		systemMessages("Lenght is required.", "message-error");
		form.lgth.value = "";
		form.lgth.focus();
	} else if (form.wdth.value == "" || isNaN(form.wdth.value)){
		systemMessages("Width is required.", "message-error");
		form.wdth.value = "";
		form.wdth.focus();
	} else if (form.hght.value == "" || isNaN(form.hght.value)){
		systemMessages("Height is required.", "message-error");
		form.hght.value = "";
		form.hght.focus();
	} else if (form.wght.value == "" || isNaN(form.wght.value)){
		systemMessages("Weight is required.", "message-error");
		form.wght.value = "";
		form.wght.focus();
	} else {
		// crunch: calculations
		var aird, airwround, air, oceand, oceanw, ocean, oceane, vol, bigvol, bigreal, txtair, dimunit, mydims;
		var dims = (form.lgth.value * form.wdth.value * form.hght.value);
		var pieces = 1;
		var weight = (form.wght.value);
		var bigvol = ("The charges will be based on the product's Volume weight.");
		var bigreal = ("The charges will be based on the product's Real weight.");
		var airw = (weight * pieces);
		// Air Calculations
		if (form.dimmeas[0].checked) {
			aird = (dims / 6000 * pieces);
			mydims = dims;
			dimunit = "kgs."
		}else {
			aird = (dims / 166 * pieces);
			mydims = dims * 16.38;
			dimunit = "lbs."
		}
		in_db = Math.round(mydims / 6000 * pieces);
		winp = document.getElementById('item_weight');

		if (aird >= airw) {
			txtair = bigvol
		}else {
			txtair = bigreal;
			in_db = airw;
		}

		air = Math.round(Math.max(aird,airw));
		vol = Math.round(aird);
		airwround = Math.round(airw);
		//alert(in_db + ' ' + winp.value);
		winp.value = in_db;
		// Results
		//document.getElementById("hidden_row").style.display="table-row";
		form.calc_weight.value = (
			"Cargo size: " + form.lgth.value + "x" + form.wdth.value + "x" + form.hght.value + "\n" +
			"Real weight: " + airwround + " " + dimunit + "\n" +
			"Volume weight: " + vol + " " + dimunit + "\n" + txtair + "\n" +
			"Chargeable weight: " + air + " " + dimunit);

		form.calc_weight.focus();
	}
}

// Check for a blank field
function isBlank(theField) {
	if (theField.value == "") {
    	return true; 
	}else {
    return false; 
	}
}

// Check that it is a valid number
function isValidNumber(theField) {
	inStr = theField.value;
    inLen = inStr.length;
    for (var i=0; i<inLen; i++) {
		var ch = inStr.substring(i,i+1)
        if (ch < "0" || "9" < ch) {
    		return false;
		}
	}
	return true; 
}

// Ocean weight rounding
function rounder(n) { 
    var multby10 = n * 10; 
    var extra0 = Math.round(multby10); 
	var pen = new String (extra0); 
	pen1 = pen.substring(0,pen.length-1);
	pen2 = pen.substring(pen.length-1,pen.length);
	return pen1 + "." + pen2; 
}
