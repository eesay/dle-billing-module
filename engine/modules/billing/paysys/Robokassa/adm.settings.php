<?php	if( !defined( 'BILLING_MODULE' ) ) die( "Hacking attempt!" );
/*
=====================================================
 Billing
-----------------------------------------------------
 evgeny.tc@gmail.com
-----------------------------------------------------
 This code is copyrighted
=====================================================
*/

Class PAYSYS 
{
	var $doc = "http://forum.dle-billing.ru/topic6.html";
	var $server = 0;

	function Settings( $config ) 
	{
		$Form = array();
	
		$Form[] = array("Идентификатор магазина:", "Ваш идентификатор в системе Робокасса.", "<input name=\"save_con[login]\" class=\"edit bk\" type=\"text\" value=\"" . $config['login'] ."\">" );
		$Form[] = array("Пароль #1:", "Используется интерфейсом инициализации оплаты.", "<input name=\"save_con[pass1]\" class=\"edit bk\" type=\"password\" value=\"" . $config['pass1'] ."\">" );
		$Form[] = array("Пароль #2:", "Используется интерфейсом оповещения о платеже, XML-интерфейсах.", "<input name=\"save_con[pass2]\" class=\"edit bk\" type=\"password\" value=\"" . $config['pass2'] ."\">" );
		$Form[] = array("Режим работы:", "Используется сервер робокассы.", "<select name=\"save_con[server]\" class=\"uniform\"><option value=\"0\" " . ( $config['server'] == 0 ? "selected" : "" ) . ">Тестовый</option><option value=\"1\" " . ( $config['server'] == 1 ? "selected" : "" ) . ">Рабочий</option></select>" );

		return $Form;
	}

	function form( $id, $config, $invoice, $currency, $desc ) 
	{
		$sign_hash = md5("$config[login]:$invoice[invoice_pay]:$id:$config[pass1]");
		$server = $config['server'] == 0 ? "http://test.robokassa.ru/Index.aspx" : "https://merchant.roboxchange.com/Index.aspx";

		return '
			<form method="post" id="paysys_form" action="' . $server . '">
				
				<input type=hidden name=MerchantLogin value="' . $config['login'] . '">
				<input type=hidden name=OutSum value="' . $invoice['invoice_pay'] . '">
				<input type=hidden name=InvId value="' . $id . '">
				<input type=hidden name=Desc value="' . $desc . '">
				<input type=hidden name=SignatureValue value="' . $sign_hash . '">

				<input type="submit" name="process" class="bs_button" value="Оплатить" />
			</form>';
		
	}
	
	function check_id( $DATA ) 
	{
		return $DATA["InvId"];
	}
	
	function check_ok( $DATA ) 
	{
		return 'OK'.$DATA["InvId"];
	}
	
	function check_out( $DATA, $CONFIG, $INVOICE ) 
	{
		$out_summ = $DATA['OutSum'];
		$inv_id = $DATA["InvId"];
		$crc = $DATA["SignatureValue"];

		$crc = strtoupper($crc);

		$my_crc = strtoupper(md5("$out_summ:$inv_id:$CONFIG[pass2]"));
	
		if ($my_crc !=$crc)
			return "bad sign\n";

		return 200;
	}
	
}

$Paysys = new PAYSYS;
?>
