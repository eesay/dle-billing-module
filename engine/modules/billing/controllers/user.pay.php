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

Class USER
{
	# Paysys list
	var $PaysysList = array();

	function main( $GET )
	{
		/* Login */
		if( !$this->DevTools->member_id['name'] ) return $this->DevTools->lang['pay_need_login'];

		$PaysysArray = $this->PaysysList();

		/* Creat invoice */
		if( isset($_POST['submit']) )
		{
			$this->DevTools->Model->parsVar( $_POST['bs_paysys'], '~[^a-z|0-9|\-|.]*~is' );

			$PostPaysys = trim( $_POST['bs_paysys'] );
			$PostMoney = $this->DevTools->Model->db->safesql( $_POST['bs_summa'] );

			$ToPay = $this->DevTools->API->Convert( ($PostMoney*$PaysysArray[$PostPaysys]['convert']), $PaysysArray[$PostPaysys]['format'] );

			$Error = "";

			if( !isset( $_POST['bs_hash'] ) OR $_POST['bs_hash'] != $this->DevTools->hash() )
				$Error = $this->DevTools->lang['pay_hash_error'];

			else if( !$PostPaysys OR !isset( $PaysysArray[$PostPaysys]['status'] ) )
				$Error = $this->DevTools->lang['pay_paysys_error'];

			else if( !$PostMoney )
				$Error = $this->DevTools->lang['pay_summa_error'];

			else if( $ToPay < $PaysysArray[$PostPaysys]['minimum'] )
				$Error = $this->DevTools->lang['pay_minimum_error'] . $PaysysArray[$PostPaysys]['title']." - ".$PaysysArray[$PostPaysys]['minimum']." ".$this->DevTools->API->Declension( $PaysysArray[$PostPaysys]['minimum'] );

			else if( $ToPay > $PaysysArray[$PostPaysys]['max'] )
				$Error = $this->DevTools->lang['pay_max_error'] . $PaysysArray[$PostPaysys]['title']." - ".$PaysysArray[$PostPaysys]['max']." ".$this->DevTools->API->Declension( $PaysysArray[$PostPaysys]['max'] );

			if( $Error )
				return $this->DevTools->ThemeMsg( $this->DevTools->lang['pay_error_title'], $Error, "pay" );

			/* Creat pay */
			$PostMoney = $this->DevTools->API->Convert( $_POST['bs_summa'] );

			$InvoiceID = $this->DevTools->Model->DbCreatInvoice( $PostPaysys, $this->DevTools->member_id['name'], $PostMoney, $ToPay );

			$dataMail = array(	'{id}' => $InvoiceID,
								'{summa}' => $ToPay ." ". $this->DevTools->API->Declension( $ToPay, $PaysysArray[$PostPaysys]['currency'] ),
								'{login}' => $this->DevTools->member_id['name'],
								'{summa_get}' => $PostMoney ." ". $this->DevTools->API->Declension( $PostMoney ),
								'{payments}' => $PaysysArray[$PostPaysys]['title'],
								'{link}' => $this->DevTools->config_dle['http_home_url'].$this->DevTools->config['page'].'.html/pay/waiting/id:'.$InvoiceID,
							);

			if( $this->DevTools->config['mail_paynew_pm'] )
				$this->DevTools->API->SendAlertToUser( "new", $dataMail, $this->DevTools->member_id['user_id'] );
			if( $this->DevTools->config['mail_paynew_email'] )
				$this->DevTools->API->SendAlertToUser( "new", $dataMail, 0, $this->DevTools->member_id['email'] );

			header( 'Location: '.$this->DevTools->config_dle['http_home_url'].$this->DevTools->config['page'].'.html/pay/waiting/id:'.$InvoiceID );

			return true;

		}
		/* -- END Creat -- */

		/* FORM */
		$Tpl = $this->DevTools->ThemeLoad( "pay/start" );

		$PaysysList = '';

		$TplSelect = $this->DevTools->ThemePregMatch( $Tpl, '~\[paysys\](.*?)\[/paysys\]~is' );

		$GetSum = $GET['sum'] ? $this->DevTools->API->Convert( $GET['sum'] ) : $this->DevTools->config['sum'];

		/* Paysys */
		if( count( $PaysysArray ) )
			foreach( $PaysysArray as $Name=>$Info )
			{
				$TimeLine = $TplSelect;
				$TimeLine = str_replace("{paysys_name}", $Name, $TimeLine);
				$TimeLine = str_replace("{paysys_title}", $Info['title'], $TimeLine);
				$TimeLine = str_replace("{paysys_convert}", $Info['convert'], $TimeLine);
				$TimeLine = str_replace("{paysys_minimum}", $Info['minimum'], $TimeLine);
				$TimeLine = str_replace("{paysys_maximum}", $Info['max'], $TimeLine);
				$TimeLine = str_replace("{paysys_icon}", $Info['icon'], $TimeLine);
				$TimeLine = str_replace("{paysys_text}", $Info['text'], $TimeLine);

				$TimeLine = str_replace("{paysys_valuta}", $this->DevTools->API->Declension( 1, $Info['currency'] ), $TimeLine);
				$TimeLine = str_replace("{module_valuta}", $this->DevTools->API->Declension( 1 ), $TimeLine);
				$TimeLine = str_replace("{minimum_valuta}", $this->DevTools->API->Declension( $Info['minimum'] ), $TimeLine);

				$PaysysList .= $TimeLine;
			}
		else
			$PaysysList = $this->DevTools->lang['pay_main_error'];

		$this->DevTools->ThemeSetElementBlock( "paysys", $PaysysList );
		$this->DevTools->ThemeSetElement( "{module_valuta}", $this->DevTools->API->Declension( $GetSum ) );
		$this->DevTools->ThemeSetElement( "{get_summ}", $GetSum );
		$this->DevTools->ThemeSetElement( "{hash}", $this->DevTools->Hash() );
		$this->DevTools->ThemeSetElement( "{user_login}", $this->DevTools->member_id['name'] );

		return $this->DevTools->Show( $Tpl, "pay" );
	}

	function ok()
	{
		return $this->DevTools->Show( $this->DevTools->ThemeLoad( "pay/success" ), "pay" );
	}

	function bad()
	{
		return $this->DevTools->Show( $this->DevTools->ThemeLoad( "pay/fail" ), "pay" );
	}

	function waiting( $GET )
	{
		/* Login */
		if( !$this->DevTools->member_id['name'] ) return $this->DevTools->lang['pay_need_login'];

		$Invoice = $this->DevTools->Model->DbGetInvoiceByID( $GET['id'] );
		$PaysysArray = $this->PaysysList();
		$Echo = "";

		if( !isset( $Invoice['invoice_paysys'] ) or $Invoice['invoice_user_name'] != $this->DevTools->member_id['name'] )
		{
			$Echo = $this->DevTools->lang['pay_invoice_error'];
		}
		else
		{
			$this->DevTools->ThemeSetElement( "{paysys_title}", $PaysysArray[$Invoice['invoice_paysys']]['title'] );
			$this->DevTools->ThemeSetElement( "{summa}", $Invoice['invoice_pay'] );
			$this->DevTools->ThemeSetElement( "{valuta}",  $PaysysArray[$Invoice['invoice_paysys']]['currency'] );
			$this->DevTools->ThemeSetElement( "{money}", $Invoice['invoice_get']." ".$this->DevTools->API->Declension( $Invoice['invoice_pay'] ) );

			// - Was pay
			if( $Invoice['invoice_date_pay'] )
			{
				$Echo = $this->DevTools->ThemeLoad( "pay/ok" );
			}
			else
			{
				/* Load */
				if( file_exists( MODULE_PATH."/paysys/" . $this->DevTools->ClearUrlDir( $PaysysArray[$Invoice['invoice_paysys']]['file'] ) . "/adm.settings.php" ) ) {

					// - Get form pay
					require_once MODULE_PATH . '/paysys/' . $this->DevTools->ClearUrlDir( $PaysysArray[$Invoice['invoice_paysys']]['file'] ) . '/adm.settings.php';

					// - redirect
					if( $this->DevTools->config['redirect'] )
						$RedirectForm = '	<script type="text/javascript">
												window.onload = function()
												{
													document.getElementById("paysys_form").submit();
												}
											</script>';
					else
						$RedirectForm = '';

					$this->DevTools->ThemeSetElement( "{button}", $RedirectForm . $Paysys->form( $GET['id'], $PaysysArray[$Invoice['invoice_paysys']], $Invoice, $this->DevTools->API->Declension( $Invoice['invoice_get'] ), "{$this->DevTools->lang['pay_desc_1']} {$this->DevTools->member_id['name']} {$this->DevTools->lang['pay_desc_2']} {$Invoice['invoice_get']} {$this->DevTools->API->Declension( $Invoice['invoice_get'] )}" ) );

				} else
					$this->DevTools->ThemeSetElement( "{button}", $this->DevTools->lang['pay_file_error'] );

				$this->DevTools->ThemeSetElement( "{title}", str_replace("{id}", $GET['id'], $this->DevTools->lang['pay_invoice']) );

				$Echo = $this->DevTools->ThemeLoad( "pay/waiting" );
			}
		}

		return $this->DevTools->Show( $Echo, "pay" );
	}

	/* Pay process */
	function get( $GET )
	{
		@header( "Content-type: text/html; charset=" . $this->DevTools->config_dle['charset'] );

		$SecretKey = $GET['key'];
		$GetPaysys = $GET['from'];
		$PaysysArray = $this->PaysysList();

		// - log
		$this->log( "Start: {$GetPaysys}" );

		// GET and POST
		$DATA = $this->ClearData( $_REQUEST );

		$this->log( "Get data: " . serialize($DATA) );

		/* Error key */
		if( !isset( $SecretKey ) or $SecretKey != $this->DevTools->config['secret'] )
		{
			$this->log( "Error: {$this->DevTools->lang['pay_getErr_key']}" );

			die( $this->DevTools->lang['pay_getErr_key'] );
		}

		/* Error paysys */
		if( !isset( $GetPaysys ) or !$PaysysArray[$GetPaysys]['status'] )
		{
			$this->log( "Error: {$this->DevTools->lang['pay_getErr_paysys']}" );

			die( $this->DevTools->lang['pay_getErr_paysys'] );
		}

		$this->log( "Test billing: OK" );

		/* Start pay test */
		if( file_exists( MODULE_PATH."/paysys/" . $this->DevTools->ClearUrlDir( $PaysysArray[$GetPaysys]['file'] ) . "/adm.settings.php" ) )
		{
			require_once MODULE_PATH . '/paysys/' . $this->DevTools->ClearUrlDir( $PaysysArray[$GetPaysys]['file'] ) . '/adm.settings.php';

			$this->log( "Load adm.settings.php: OK" );

			/* Get ID Invoice from data paysys */
			$CheckID = $Paysys->check_id( $DATA );

			if( !intval($CheckID) )
			{
				$this->log( "Error: get ID invoice" );

				die('Error get ID');
			}

			$this->log( "Test ID invoice ({$CheckID}): OK" );

			$Invoice = $this->DevTools->Model->DbGetInvoiceByID( $CheckID );

			/* Test data paysys */
			$CheckInvoice = $Paysys->check_out( $DATA, $PaysysArray[$GetPaysys], $Invoice );

			$this->log( "Test billing hash: {$CheckInvoice}" );

			if( $CheckInvoice == "200" )
			{
				if( $this->PayOk( $CheckID ) )
				{
					$this->log( "Send money: OK" );

					echo $Paysys->check_ok( $DATA );
				}
				else
				{
					$this->log( "Error: {$this->DevTools->lang['pay_getErr_invoice']}" );

					echo $this->DevTools->lang['pay_getErr_invoice'];
				}
			} else
				echo $CheckInvoice;
		}
		else
		{
			$this->log( "Load adm.settings.php: NO" );

			echo $this->DevTools->lang['pay_file_error'];
		}

		// - log
		$this->log( "End" );

		exit();
	}

	private function log( $info = '' )
	{
		if( !$this->DevTools->config['test'] ) return false;

		$handler = fopen( 'log_pay.php', "a" );

		fwrite( $handler, langdate( "j F Y H:i", $this->_TIME) . " : " . iconv('windows-1251','utf-8', $info) . "\n" );

		fclose( $handler );

		return true;
	}

	private function ClearData( $DATA )
	{
		foreach( $DATA as $key=>$val )
		{
			if( in_array( $key, array( 'do', 'page', 'seourl', 'c', 'm', 'p', 'key' ) ) ) unset( $DATA[$key] );
		}

		return $DATA;
	}

	private function PayOk( $invoice_id )
	{
		$Invoice = $this->DevTools->Model->DbGetInvoiceByID( $invoice_id );

		$PaysysArray = $this->PaysysList();

		if( !isset( $Invoice ) OR $Invoice['invoice_date_pay'] ) return false;

		$this->DevTools->Model->DbInvoiceUpdate( $invoice_id, false );

			/* Send info to mail */
			$dataMail = array(	'{id}' => $invoice_id,
								'{summa}' => $Invoice['invoice_pay'] ." ". $this->DevTools->API->Declension( $Invoice['invoice_pay'], $PaysysArray[$Invoice['invoice_paysys']]['currency'] ),
								'{login}' => $Invoice['invoice_user_name'],
								'{summa_get}' => $Invoice['invoice_get'] ." ". $this->DevTools->API->Declension( $Invoice['invoice_get'] ),
								'{payments}' => $PaysysArray[$Invoice['invoice_paysys']]['title']
							);

			$SearchUser = $this->DevTools->Model->DbSearchUserByName( $Invoice['invoice_user_name'] );

			if( $this->DevTools->config['mail_payok_pm'] )
				$this->DevTools->API->SendAlertToUser( "payok", $dataMail, $SearchUser['user_id'] );

			if( $this->DevTools->config['mail_payok_email'] )
				$this->DevTools->API->SendAlertToUser( "payok", $dataMail, 0, $SearchUser['email'] );

		$this->DevTools->API->PlusMoney( $SearchUser['name'], $Invoice['invoice_get'], str_replace( "{paysys}", $PaysysArray[$Invoice['invoice_paysys']]['title'], str_replace( "{money}", "{$Invoice['invoice_pay']} {$PaysysArray[$Invoice['invoice_paysys']]['currency']}", $this->DevTools->lang['pay_msgOk'] ) ), '', "pay", $invoice_id );

		# Bonuses
		#
		if( file_exists( ENGINE_DIR . '/data/billing/plugin.bonuses.php' ) )
			require_once ENGINE_DIR . '/data/billing/plugin.bonuses.php';

		# Firset
		#
		if( $plugin_config['status'] )
		{
			$this->DevTools->Model->DbWhere( array( "invoice_user_name = '{s}' " => $SearchUser['name'], "invoice_date_pay != 0" => 1 ) );
			$countPay = $this->DevTools->Model->DbGetInvoiceNum();

			if( ! $countPay and $Invoice['invoice_get'] >= $plugin_config['f_sum'] )
			{
				if( $plugin_config['f_bonus_sum'] )
				{
					$this->DevTools->API->PlusMoney( $SearchUser['name'], $plugin_config['f_bonus_sum'], $this->DevTools->lang['bonus_first_comment'], '', "bonuses", $invoice_id );
				}
				else
				{
					$this->DevTools->API->PlusMoney( $SearchUser['name'], ( ($Invoice['invoice_get']/100) * $plugin_config['f_bonus_percent']), $this->DevTools->lang['bonus_first_comment'], '', "bonuses", $invoice_id );
				}
			}
		}

		# Second and more
		#
		if( $plugin_config['s_status'] )
		{
			$this->DevTools->Model->DbWhere( array( "invoice_user_name = '{s}' " => $SearchUser['name'], "invoice_date_pay != 0" => 1 ) );
			$countPay = $this->DevTools->Model->DbGetInvoiceNum();

			if( $countPay and $Invoice['invoice_get'] >= $plugin_config['s_sum'] )
			{
				if( $plugin_config['s_bonus_sum'] )
				{
					$this->DevTools->API->PlusMoney( $SearchUser['name'], $plugin_config['s_bonus_sum'], $this->DevTools->lang['bonus_first_comment'], '', "bonuses", $invoice_id );
				}
				else
				{
					$this->DevTools->API->PlusMoney( $SearchUser['name'], ( ($Invoice['invoice_get']/100) * $plugin_config['s_bonus_percent']), $this->DevTools->lang['bonus_comment'], '', "bonuses", $invoice_id );
				}
			}
		}

		# Firset
		#
		if( $plugin_config['status'] )
		{
			$this->DevTools->Model->DbWhere( array( "invoice_user_name = '{s}' " => $SearchUser['name'], "invoice_date_pay != 0" => 1 ) );
			$countPay = $this->DevTools->Model->DbGetInvoiceNum();

			if( ! $countPay and $Invoice['invoice_get'] >= $plugin_config['f_sum'] )
			{
				if( $plugin_config['f_bonus_sum'] )
				{
					$this->DevTools->API->PlusMoney( $SearchUser['name'], $plugin_config['f_bonus_sum'], $this->DevTools->lang['bonus_first_comment'], '', "bonuses", $invoice_id );
				}
			}
		}

		return true;
	}

	private function PaysysList()
	{
		if( $this->PaysysList ) return $this->PaysysList;

		$load_list = opendir( MODULE_PATH . "/paysys/" );

		while ( $name = readdir($load_list) )
		{
			if ( in_array($name, array(".", "..", "/", "index.php", ".htaccess")) ) continue;

			/* Config */
			if( file_exists( MODULE_DATA."/pasys." . $name . ".php" ) )
				require_once MODULE_DATA."/pasys." . $name . ".php";
			else
				continue;

			if( !$paysys_config['status'] ) continue;

			$this->PaysysList[$name] = $paysys_config;
			$this->PaysysList[$name]['file'] = $name;
		}

		return $this->PaysysList;
	}

}
?>
