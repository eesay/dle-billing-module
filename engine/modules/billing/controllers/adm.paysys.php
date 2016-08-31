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
	function main( $Get ) 
	{
		$Name = $this->DevTools->Model->parsVar( $Get['billing'], "/[^a-zA-Z0-9\s]/" );
		
		/* Save */
		if( isset( $_POST['save'] ) )
		{
			if( $_POST['user_hash'] == "" or $_POST['user_hash'] != $this->DevTools->hash )
			{       
				return "Hacking attempt! User not found {$_POST['user_hash']}";   
			}
		
			$SaveData = $_POST['save_con'];
			$SaveData['convert'] = preg_replace ("/[^0-9.\s]/", "", $SaveData['convert'] );
			$SaveData['minimum'] = $this->DevTools->API->Convert( $SaveData['minimum'], $SaveData['format'] );
			$SaveData['max'] = $this->DevTools->API->Convert( $SaveData['max'], $SaveData['format'] );
			
			$this->DevTools->SaveConfig( "pasys." . $Name, $SaveData, "paysys_config" );
			
			$this->DevTools->ThemeMsg( $this->DevTools->lang['ok'], $this->DevTools->lang['paysys_save_ok'] );
		}
		
		/* Load */
		if( file_exists( MODULE_PATH."/paysys/" . $Name . "/adm.settings.php" ) )
			require_once MODULE_PATH . '/paysys/' . $Name . '/adm.settings.php';
		else
			$this->DevTools->ThemeMsg( $this->DevTools->lang['error'], $this->DevTools->lang['paysys_fail_error'] );

		/* Config paysys */
		$GetPaysysArray = $this->DevTools->GetPaysysArray();
		
		$PaysysConfig = $GetPaysysArray[$Name];
		
		/* Settings */
		$this->DevTools->ThemeEchoHeader();
		
		$Content = $this->DevTools->ThemeHeadStart( $this->DevTools->lang['main_settings'] . " " . $Name . " v." . $this->DevTools->PaysysVersion( $Name ), "<a href=\"" . $Paysys->doc . "\" target=\"_blank\">" . $this->DevTools->lang['catalog_doc'] . "</a>" );

		$this->DevTools->ThemeAddStr( $this->DevTools->lang['paysys_url'], $this->DevTools->lang['paysys_url_desc'], $this->DevTools->config_dle['http_home_url'] . "pay/" . $this->DevTools->config['page'] . "/from:". $Name .":key:". $this->DevTools->config['secret'] .".html" ); 
		$this->DevTools->ThemeAddStr( $this->DevTools->lang['paysys_on']." {$Name}:", $this->DevTools->lang['paysys_status_desc'], $this->DevTools->MakeCheckBox("save_con[status]", $PaysysConfig['status']) );
		$this->DevTools->ThemeAddStr( $this->DevTools->lang['paysys_name'], $this->DevTools->lang['paysys_name_desc'], "<input name=\"save_con[title]\" class=\"edit bk\" type=\"text\" value=\"" . $PaysysConfig['title'] ."\">" );
		$this->DevTools->ThemeAddStr( $this->DevTools->lang['paysys_convert'], $this->DevTools->lang['paysys_convert_desc'], "<input name=\"save_con[convert]\" class=\"edit bk\" type=\"text\" value=\"" . $PaysysConfig['convert'] ."\"> = 1 " . $this->DevTools->API->Declension( 1 ) );
		$this->DevTools->ThemeAddStr( $this->DevTools->lang['paysys_minimum'], $this->DevTools->lang['paysys_minimum_desc'], "<input name=\"save_con[minimum]\" class=\"edit bk\" type=\"text\" value=\"" . $PaysysConfig['minimum'] ."\">" );
		$this->DevTools->ThemeAddStr( $this->DevTools->lang['paysys_max'], $this->DevTools->lang['paysys_max_desc'], "<input name=\"save_con[max]\" class=\"edit bk\" type=\"text\" value=\"" . $PaysysConfig['max'] ."\">" );
		$this->DevTools->ThemeAddStr( $this->DevTools->lang['paysys_currency'], $this->DevTools->lang['paysys_currency_desc'], "<input name=\"save_con[currency]\" class=\"edit bk\" type=\"text\" value=\"" . $PaysysConfig['currency'] ."\">" );
		$this->DevTools->ThemeAddStr( $this->DevTools->lang['paysys_format'], $this->DevTools->lang['paysys_format_desc'], "<input name=\"save_con[format]\" class=\"edit bk\" type=\"text\" value=\"" . $PaysysConfig['format'] ."\">" );
		$this->DevTools->ThemeAddStr( $this->DevTools->lang['paysys_icon'], $this->DevTools->lang['paysys_icon_desc']."/templates/".$this->DevTools->config_dle['skin']."/billing/icons/{$Name}.png", "<input name=\"save_con[icon]\" class=\"edit bk\" type=\"text\" value=\"" . $PaysysConfig['icon'] ."\" style=\"width: 100%\">" );
		$this->DevTools->ThemeAddStr( $this->DevTools->lang['paysys_about'], $this->DevTools->lang['paysys_about_desc'], "<input name=\"save_con[text]\" class=\"edit bk\" type=\"text\" value=\"" . $PaysysConfig['text'] ."\">" );	
	
		foreach( $Paysys->Settings( $PaysysConfig ) as $Form )
			$this->DevTools->ThemeAddStr( $Form[0], $Form[1], $Form[2] );	
	
		$Content .= $this->DevTools->ThemeParserStr();
	
		$Content .= $this->DevTools->ThemePadded( $this->DevTools->MakeButton("save", $this->DevTools->lang['save'], "green")  );			 
		
		$Content .= $this->DevTools->ThemeHeadClose();
		$Content .= $this->DevTools->ThemeEchoFoother();

		return $Content;
	}

}
?>