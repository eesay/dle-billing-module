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

Class ADMIN
{
	function main()
	{
		$this->DevTools->ThemeEchoHeader();

		/* Menu */
		$Content .= $this->DevTools->ThemeHeadStart( $this->DevTools->lang['main'] );
		
		$this->DevTools->ThemeMenuSection( $this->DevTools->lang['main_settings'], $this->DevTools->lang['main_settings_desc'], "engine/modules/billing/theme/icons/configure.png", $PHP_SELF."?mod=billing&m=settings" );
		$this->DevTools->ThemeMenuSection( $this->DevTools->lang['history_title'], $this->DevTools->lang['history_desc'], "engine/modules/billing/theme/icons/history.png", $PHP_SELF."?mod=billing&c=history" );
		$this->DevTools->ThemeMenuSection( $this->DevTools->lang['users_title'], $this->DevTools->lang['users_desc'], "engine/modules/billing/theme/icons/users.png", $PHP_SELF."?mod=billing&c=users" );
		$this->DevTools->ThemeMenuSection( $this->DevTools->lang['invoice_title'], $this->DevTools->lang['invoice_desc'], "engine/modules/billing/theme/icons/invoice.png", $PHP_SELF."?mod=billing&c=invoice" );
		$this->DevTools->ThemeMenuSection( $this->DevTools->lang['statistics_title'], $this->DevTools->lang['statistics_title_desc'], "engine/modules/billing/theme/icons/statistics.png", $PHP_SELF."?mod=billing&c=statistics" );
		$this->DevTools->ThemeMenuSection( $this->DevTools->lang['catalog_title'], $this->DevTools->lang['catalog_desc'], "engine/modules/billing/theme/icons/catalog.png", $PHP_SELF."?mod=billing&c=catalog" );
		
		$Content .= $this->DevTools->ThemeMenuSectionParser( 'main', 6 );
		$Content .= $this->DevTools->ThemeHeadClose();

		/* Paysys */
		$Content .= $this->DevTools->ThemeHeadStart( $this->DevTools->lang['main_paysys'] );
		$Content .= $this->DevTools->ThemeMenuPaysys();
		$Content .= $this->DevTools->ThemeHeadClose();
			
		/* Plugins */
		$Content .= $this->DevTools->ThemeHeadStart( $this->DevTools->lang['main_plugins'] );
		$Content .= $this->DevTools->MenuPlugins();
		$Content .= $this->DevTools->ThemeHeadClose();
	
		$Content .= $this->DevTools->ThemeEchoFoother();

		return $Content;
	}

	function settings()
	{
		// - save
		if( isset( $_POST['save'] ) )
		{
			if( $_POST['user_hash'] == "" or $_POST['user_hash'] != $this->DevTools->hash )
			{       
				return "Hacking attempt! User not found {$_POST['user_hash']}";   
			}

			$_POST['save_con']['version'] = $this->DevTools->config['version'];
			$_POST['save_con']['informers'] = implode(",", $_POST['informers']);

			$this->DevTools->SaveConfig("config", $_POST['save_con'], "billing_config");
			$this->DevTools->ThemeMsg( $this->DevTools->lang['ok'], $this->DevTools->lang['save_settings'] );
		}

		// - save Mail
		if( isset( $_POST['saveMail'] ) )
		{
			if( $_POST['user_hash'] == "" or $_POST['user_hash'] != $this->DevTools->hash )
			{       
				return "Hacking attempt! User not found {$_POST['user_hash']}";   
			}

			$this->DevTools->SaveConfig( "mail", $_POST['save_con'], "billing_mail" );
			
			$this->DevTools->ThemeMsg( $this->DevTools->lang['ok'], $this->DevTools->lang['save_mail'] );
		}

		// - informers
		$arrInformers = array( 'invoice' => $this->DevTools->lang['invoice_new'] );
		
		foreach( $this->DevTools->GetPluginsArray() as $name => $config )
		{
			if( !isset( $config['informers'] ) ) continue;
			
			foreach( explode(",", $config['informers'] ) as $conInformer )
			{ 
				$arrConInformer = explode(":", $conInformer );
				$arrInformers[$name.".".$arrConInformer[1]] = $config['title'] . " &raquo; " . $arrConInformer[0];
			}
		}
		
		// - Page
		$this->DevTools->ThemeEchoHeader();
	
		$this->DevTools->ThemeAddStr( $this->DevTools->lang['settings_status'], $this->DevTools->lang['settings_status_desc'], $this->DevTools->MakeCheckBox("save_con[status]", $this->DevTools->config['status']) );
		$this->DevTools->ThemeAddStr( $this->DevTools->lang['settings_redirect'], $this->DevTools->lang['settings_redirect_desc'], $this->DevTools->MakeICheck("save_con[redirect]", $this->DevTools->config['redirect']) );
		$this->DevTools->ThemeAddStr( $this->DevTools->lang['settings_page'], $this->DevTools->lang['settings_page_desc'], "<input name=\"save_con[page]\" class=\"edit bk\" type=\"text\" value=\"" . $this->DevTools->config['page'] ."\" style=\"width: 50%\" required>.html" );
		$this->DevTools->ThemeAddStr( $this->DevTools->lang['settings_currency'], $this->DevTools->lang['settings_currency_desc'], "<input name=\"save_con[currency]\" class=\"edit bk\" type=\"text\" value=\"" . $this->DevTools->config['currency'] ."\" style=\"width: 50%\" required>" );
		$this->DevTools->ThemeAddStr( $this->DevTools->lang['settings_paging'], $this->DevTools->lang['settings_paging_desc'], "<input name=\"save_con[paging]\" class=\"edit bk\" type=\"text\" value=\"" . $this->DevTools->config['paging'] ."\" style=\"width: 50%\" required>" );
		$this->DevTools->ThemeAddStr( $this->DevTools->lang['settings_informers'], $this->DevTools->lang['settings_informers_desc'], $this->DevTools->MakeDropDown( $arrInformers, "informers[]", explode(",", $this->DevTools->config['informers'] ), true ) );
		
		$settingMain = $this->DevTools->ThemeParserStr();
		
		$this->DevTools->ThemeAddStr( $this->DevTools->lang['settings_admin'], $this->DevTools->lang['settings_admin_desc'], "<input name=\"save_con[admin]\" class=\"edit bk\" type=\"text\" value=\"" . $this->DevTools->config['admin'] ."\" style=\"width: 50%\" required>" );
		$this->DevTools->ThemeAddStr( $this->DevTools->lang['settings_field'], $this->DevTools->lang['settings_field_desc'], "<input name=\"save_con[fname]\" class=\"edit bk\" type=\"text\" value=\"" . $this->DevTools->config['fname'] ."\" style=\"width: 50%\" required>" );
		$this->DevTools->ThemeAddStr( $this->DevTools->lang['settings_start'], $this->DevTools->lang['settings_start_desc'], "<input name=\"save_con[start]\" class=\"edit bk\" type=\"text\" value=\"" . $this->DevTools->config['start'] ."\" style=\"width: 50%\" required>" );
		$this->DevTools->ThemeAddStr( $this->DevTools->lang['settings_summ'], $this->DevTools->lang['settings_summ_desc'], "<input name=\"save_con[sum]\" class=\"edit bk\" type=\"text\" value=\"" . $this->DevTools->config['sum'] ."\" style=\"width: 50%\" required>" );
		$this->DevTools->ThemeAddStr( $this->DevTools->lang['settings_format'], $this->DevTools->lang['settings_format_desc'], "<input name=\"save_con[format]\" class=\"edit bk\" type=\"text\" value=\"" . $this->DevTools->config['format'] ."\" style=\"width: 50%\" required>" );
			
		$settingMore = $this->DevTools->ThemeParserStr();
		
		$this->DevTools->ThemeAddStr( $this->DevTools->lang['settings_test'], $this->DevTools->lang['settings_test_desc'], $this->DevTools->MakeICheck("save_con[test]", $this->DevTools->config['test']) );
		$this->DevTools->ThemeAddStr( $this->DevTools->lang['settings_key'], $this->DevTools->lang['settings_key_desc'], "<input name=\"save_con[secret]\" class=\"edit bk\" type=\"text\" value=\"" . $this->DevTools->config['secret'] ."\" style=\"width: 50%\" required>" );
		$this->DevTools->ThemeAddStr( $this->DevTools->lang['settings_catalog'], $this->DevTools->lang['settings_catalog_desc'], "<input name=\"save_con[url_catalog]\" class=\"edit bk\" type=\"text\" value=\"" . $this->DevTools->config['url_catalog'] ."\" style=\"width: 50%\" required>" );

		$settingSecurity = $this->DevTools->ThemeParserStr();

		$this->DevTools->ThemeAddTR( $this->DevTools->lang['mail_table'] );
		$this->DevTools->ThemeAddTR( array( $this->DevTools->lang['mail_pay_ok'], "<div style=\"text-align: center; margin-top: 15px\">".$this->DevTools->MakeICheck("save_con[mail_payok_pm]", $this->DevTools->config['mail_payok_pm'] )."</div>", "<div style=\"text-align: center; margin-top: 15px\">".$this->DevTools->MakeICheck("save_con[mail_payok_email]", $this->DevTools->config['mail_payok_email'] )."</div>") );
		$this->DevTools->ThemeAddTR( array( $this->DevTools->lang['mail_pay_new'], "<div style=\"text-align: center; margin-top: 15px\">".$this->DevTools->MakeICheck("save_con[mail_paynew_pm]", $this->DevTools->config['mail_paynew_pm'] )."</div>", "<div style=\"text-align: center; margin-top: 15px\">".$this->DevTools->MakeICheck("save_con[mail_paynew_email]", $this->DevTools->config['mail_paynew_email'] )."</div>") );
		$this->DevTools->ThemeAddTR( array( $this->DevTools->lang['mail_balance'], "<div style=\"text-align: center; margin-top: 15px\">".$this->DevTools->MakeICheck("save_con[mail_balance_pm]", $this->DevTools->config['mail_balance_pm'] )."</div>", "<div style=\"text-align: center; margin-top: 15px\">".$this->DevTools->MakeICheck("save_con[mail_balance_email]", $this->DevTools->config['mail_balance_email'] )."</div>") );

		$settingMail = $this->DevTools->ThemeParserTable();
	
$Content .= <<<HTML
<div class="box">
		    <div class="box-header">
				<ul class="nav nav-tabs nav-tabs-left">
					<li class="active"><a href="#settingMain" data-toggle="tab">{$this->DevTools->lang['main_settings_1']}</a></li>
					<li><a href="#settingMore" data-toggle="tab">{$this->DevTools->lang['main_settings_2']}</a></li>
					<li><a href="#settingSecurity" data-toggle="tab">{$this->DevTools->lang['main_settings_3']}</a></li>
					<li><a href="#settingMail" data-toggle="tab">{$this->DevTools->lang['main_mail']}</a></li>
				</ul>
			</div>
		
            <div class="box-content">
				<form action="" method="post">
                 <div class="tab-content">
					<div class="tab-pane active" id="settingMain">
							{$settingMain}
					</div>
					<div class="tab-pane" id="settingMore">
							{$settingMore}
					</div>
					<div class="tab-pane" id="settingSecurity">
							{$settingSecurity}
					</div>
					<div class="tab-pane" id="settingMail">
							{$settingMail}
					</div>
				</div>
				{$this->DevTools->ThemePadded( $this->DevTools->MakeButton( "save", $this->DevTools->lang['save'], "green" ) )}
				</form>	
			</div>
</div>
HTML;

		$Content .= $this->DevTools->ThemeEchoFoother();

		return $Content;
	}

	function report()
	{
		// - Send
		if( isset( $_POST['send'] ) and $_POST['report'] )
		{
			if( $_POST['user_hash'] == "" or $_POST['user_hash'] != $this->DevTools->hash )
			{       
				return "Hacking attempt! User not found {$_POST['user_hash']}";   
			}
			
			include_once ENGINE_DIR . '/classes/mail.class.php';
					
			$mail = new dle_mail( $this->DevTools->config_dle, true);
			$mail->send( "info@dle-billing.ru", $this->DevTools->lang['report_email_title'], $_POST['report'] );
					
			unset( $mail );
				
			$this->DevTools->ThemeMsg( $this->DevTools->lang['report_ok'], $this->DevTools->lang['report_oktext'], $PHP_SELF . "?mod=billing" );
		}
		
		// - Page
		$this->DevTools->ThemeEchoHeader();
	
		$Content .= $this->DevTools->MakeMsgInfo( $this->DevTools->lang['report_info'], "icon-info-sign", "green");
			
		$Content .= $this->DevTools->ThemeHeadStart( $this->DevTools->lang['main_report'] );

		$pars_url = explode("?", $_SERVER['HTTP_REFERER']);
		
		$report = $this->DevTools->lang['report_str1'] . end($pars_url);
		$report .= $this->DevTools->lang['report_f1'] . $this->DevTools->member_id['email'];
		$report .= $this->DevTools->lang['report_str2'] . $this->DevTools->config_dle['version_id'];
		$report .= $this->DevTools->lang['report_str3'] . $this->DevTools->config['version'];
		$report .= $this->DevTools->lang['report_str4'] . $this->DevTools->config_dle['charset'];
		$report .= "\n====================";
		$report .= $this->DevTools->lang['report_str6'];

		$Content .= "<textarea style=\"width: 98%; height: 200px; margin: 10px\" name=\"report\" required>" . $report . "</textarea>";
		$Content .= $this->DevTools->ThemePadded( $this->DevTools->MakeButton("send", $this->DevTools->lang['report_send'], "green") );
		
		$Content .= $this->DevTools->ThemeHeadClose();
		$Content .= $this->DevTools->ThemeEchoFoother();

		return $Content;
	}
	
	function log()
	{
		// - Clear
		if( isset( $_POST['clear'] ) )
		{
			if( $_POST['user_hash'] == "" or $_POST['user_hash'] != $this->DevTools->hash )
			{       
				return "Hacking attempt! User not found {$_POST['user_hash']}";   
			}
			
			unlink("log_pay.php");
		}
		
		// - Page
		$this->DevTools->ThemeEchoHeader();

		$Content = $this->DevTools->ThemeHeadStart( $this->DevTools->lang['main_log'] );

		$Content .= "<textarea style=\"width: 98%; height: 200px; margin: 10px\" name=\"read\">" . @file_get_contents("log_pay.php") . "</textarea>";
		$Content .= $this->DevTools->ThemePadded( $this->DevTools->MakeButton("clear", $this->DevTools->lang['history_search_btn_null'], "blue") );
		
		$Content .= $this->DevTools->ThemeHeadClose();
		$Content .= $this->DevTools->ThemeEchoFoother();

		return $Content;
	}
	
}
?>