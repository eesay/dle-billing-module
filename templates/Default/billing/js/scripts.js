/*
=====================================================
 Billing
-----------------------------------------------------
 evgeny.tc@gmail.com
-----------------------------------------------------
 This code is copyrighted
=====================================================
*/

var bs_pay_sys = "";
var bs_pay_convert = 0;
var bs_pay_minimum = 0;
var bs_pay_maximum = 0;
var bs_pay_currency = 0;

/* Select billing - process */
function bs_paysys( name, title, convert, currency, minimum, maximum )
{
	$("#"+bs_pay_sys).removeClass("bt_billing_active");
	$("#"+name).addClass("bt_billing_active");
	$("#bs_submit").css('opacity', "1");

	bs_pay_sys = name;
	bs_pay_sys_title = title;
	bs_pay_convert = parseFloat( convert );
	bs_pay_minimum = parseFloat( minimum );
	bs_pay_maximum = parseFloat( maximum );
	bs_pay_currency = currency;
	
	$("#bs_pay_currency").html( currency );
	$("#bs_paysys").val( name );

	bs_topay( convert );
	
	return true;
}

/* Convert */
function bs_topay( convert )
{
	if( !convert ) convert = bs_pay_convert;
	if( !convert ) convert = 1;

	$("#bs_pay").html( parseFloat( (convert*$("#bs_summa").val()).toFixed(2) ) );

	return true;
}

/* Form creat pay */
function bs_pay( form )
{
	error = "";

	if( !bs_pay_sys )
		error = bs_lang_select;
	
	else if( !parseFloat( bs_pay_convert*$("#bs_summa").val() ) )
		error = bs_lang_sum;

	else if( parseFloat( $("#bs_pay").html() ) < bs_pay_minimum )
		error = bs_lang_minimum+bs_pay_sys_title+" - "+bs_pay_minimum+" "+bs_pay_currency;
	
	else if( parseFloat( $("#bs_pay").html() ) > bs_pay_maximum )
		error = bs_lang_maximum+bs_pay_sys_title+" - "+bs_pay_maximum+" "+bs_pay_currency;
		
	if( error )
	{
		DLEalert( error, bs_lang_error );
	
		return false;
	}
	
	return true;
}