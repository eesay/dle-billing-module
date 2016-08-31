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
		/* Save */
		if( isset( $_POST['save'] ) )
		{
			if( $_POST['user_hash'] == "" or $_POST['user_hash'] != $this->DevTools->hash )
			{				
				return "Hacking attempt! User not found {$_POST['user_hash']}";   
			}

			$this->DevTools->SaveConfig("plugin.transfer", $_POST['save_con'], "plugin_config");
			$this->DevTools->ThemeMsg( $this->DevTools->lang['ok'], $this->DevTools->lang['save_settings'] );
		}
		
		/* Load and Install */
		$plugin_config = $this->DevTools->LoadConfig( "transfer", "plugin_config", true, array('status'=>"0") );

		/* Page */
		$this->DevTools->ThemeEchoHeader();
		
		$Content .= $this->DevTools->ThemeHeadStart( $this->DevTools->lang['transfer_title'] );
		
		$this->DevTools->ThemeAddStr( $this->DevTools->lang['settings_status'], $this->DevTools->lang['refund_status_desc'], $this->DevTools->MakeCheckBox("save_con[status]", $plugin_config['status']) );
		$this->DevTools->ThemeAddStr( $this->DevTools->lang['paysys_name'], $this->DevTools->lang['refund_name_desc'], "<input name=\"save_con[name]\" class=\"edit bk\" type=\"text\" size=\"50\" value=\"" . $plugin_config['name'] ."\">" );
		$this->DevTools->ThemeAddStr( $this->DevTools->lang['transfer_minimum'], $this->DevTools->lang['transfer_minimum_desc'], "<input name=\"save_con[minimum]\" class=\"edit bk\" type=\"text\" value=\"" . $plugin_config['minimum'] ."\"> " . $this->DevTools->API->Declension( $plugin_config['minimum'] ) );
		$this->DevTools->ThemeAddStr( $this->DevTools->lang['refund_commision'], $this->DevTools->lang['refund_commision_desc'], "<input name=\"save_con[com]\" class=\"edit bk\" type=\"text\" value=\"" . $plugin_config['com'] ."\">%" );

		$Content .= $this->DevTools->ThemeParserStr();
		$Content .= $this->DevTools->ThemePadded( $this->DevTools->MakeButton("save", $this->DevTools->lang['save'], "green") );			 
		
		$Content .= $this->DevTools->ThemeHeadClose();
		$Content .= $this->DevTools->ThemeEchoFoother();
		
		return $Content;
	}
 
}
?>