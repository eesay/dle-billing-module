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
	var $plugin_config = false;

	function __construct()
	{
		if( file_exists( MODULE_DATA . "/plugin.refund.php" ) )	include MODULE_DATA . "/plugin.refund.php";
		
		$this->plugin_config = $plugin_config;  
	}
	
	function main( $GET )
	{
		/* Login */
		if( !$this->DevTools->member_id['name'] ) return $this->DevTools->lang['pay_need_login'];
	
		/* Status */
		if( !$this->plugin_config['status'] ) return $this->DevTools->ThemeMsg( $this->DevTools->lang['pay_error_title'], $this->DevTools->lang['cabinet_off'], "Refund" );

		/* Post */
		if( isset( $_POST['submit'] ) )
		{
			$PostRequisites = $this->DevTools->Model->db->safesql( $_POST['bs_requisites'] );
			$PostMoney = $this->DevTools->Model->db->safesql( $_POST['bs_summa'] );
			$PostMoneyCommission = $this->DevTools->API->Convert( ( $PostMoney / 100 ) * $this->plugin_config['com'] );
			
			$Error = "";

			if( !isset( $_POST['bs_hash'] ) OR $_POST['bs_hash'] != $this->DevTools->hash() )
				$Error = $this->DevTools->lang['pay_hash_error'];
			
			else if( !$PostMoney )
				$Error = $this->DevTools->lang['pay_summa_error'];
	
			else if( !$PostRequisites )
				$Error = $this->DevTools->lang['refund_error_requisites'];
	
			else if( $PostMoney > $this->DevTools->BalanceUser )
				$Error = $this->DevTools->lang['refund_error_balance'];
			
			else if( $PostMoney < $this->plugin_config['minimum'] )
				$Error = $this->DevTools->lang['refund_error_minimum'] . $this->plugin_config['minimum']." ".$this->DevTools->API->Declension( $this->plugin_config['minimum'] );	
			
			if( $Error )
				return $this->DevTools->ThemeMsg( $this->DevTools->lang['pay_error_title'], $Error, "Refund" );

			// - Creat moneyback
			$PostMoney = $this->DevTools->API->Convert( $_POST['bs_summa'] );
			$RefundId = $this->DevTools->Model->DbCreatRefund( $this->DevTools->member_id['name'], $PostMoney, $PostMoneyCommission, $PostRequisites );

			$this->DevTools->API->MinusMoney( $this->DevTools->member_id['name'], $PostMoney, $this->DevTools->lang['refund_msgOk'].$RefundId, '', 'refund', $RefundId );

				// - email to admin
				if( $this->plugin_config['email'] )
				{
					include_once ENGINE_DIR . '/classes/mail.class.php';
					
					$mail = new dle_mail( $this->DevTools->config_dle, true);
					$mail->send( $this->DevTools->config_dle['admin_mail'], $this->DevTools->lang['refund_email_title'], $this->DevTools->lang['refund_email_msg'].$this->DevTools->config_dle['http_home_url'].$this->DevTools->config_dle['admin_path']."?mod=billing&c=Refund" );
					
					unset( $mail );
				}
	
		}
	
		/* PAGE */
		$this->DevTools->ThemeSetElement( "{hash}", $this->DevTools->hash() );
		$this->DevTools->ThemeSetElement( "{requisites}", $this->xfield( $this->plugin_config['requisites'] ) );
		$this->DevTools->ThemeSetElement( "{minimum}", $this->plugin_config['minimum'] );
		$this->DevTools->ThemeSetElement( "{minimum_valuta}", $this->DevTools->API->Declension( $this->plugin_config['minimum'] ) );
		$this->DevTools->ThemeSetElement( "{commission}", intval( $this->plugin_config['com'] ) );
		$this->DevTools->ThemeSetElement( "{mask}", $this->plugin_config['format'] );

		/* History */
		$Content = $this->DevTools->ThemeLoad( "plugins/refund" );
		$Line = "";
			
		$TplLine = $this->DevTools->ThemePregMatch( $Content, '~\[history\](.*?)\[/history\]~is' );
		$TplLineNull = $this->DevTools->ThemePregMatch( $Content, '~\[not_history\](.*?)\[/not_history\]~is' );
		$TplLineDate = $this->DevTools->ThemePregMatch( $TplLine, '~\{date=(.*?)\}~is' );
			
		/* DB Filter */
		$this->DevTools->Model->DbWhere( array( "refund_user = '{s}' " => $this->DevTools->member_id['name'] ) );
			
		/* DB Sql */
		$Data = $this->DevTools->Model->DbGetRefund( $GET['page'], $this->DevTools->config['paging'] );
		$NumData = $this->DevTools->Model->DbGetRefundNum();

		/* Data */
		foreach( $Data as $Value )
		{
			$TimeLine = $TplLine;
			$TimeLine = str_replace("{date=".$TplLineDate."}", $this->DevTools->ThemeChangeTime( $Value['refund_date'], $TplLineDate ), $TimeLine);
			$TimeLine = str_replace("{refund_requisites}", $Value['refund_requisites'], $TimeLine);
				
			$TimeLine = str_replace("{refund_commission}",$Value['refund_commission'], $TimeLine);
			$TimeLine = str_replace("{refund_commission_valuta}", $this->DevTools->API->Declension( $Value['refund_commission'] ), $TimeLine);
				
			$TimeLine = str_replace("{refund_summa}", $Value['refund_summa'], $TimeLine);
			$TimeLine = str_replace("{refund_summa_valuta}",  $this->DevTools->API->Declension( $Value['refund_summa'] ), $TimeLine);
				
			$TimeLine = str_replace("{refund_status}", $Value['refund_date_return'] ? "<font color=\"green\">".langdate( $TplLineDate, $Value['refund_date_return'])."</a>": "<font color=\"red\">".$this->DevTools->lang['refund_wait']."</a>", $TimeLine);

			$Line .= $TimeLine;
		}

		/* Paging */
		if( $NumData > $this->DevTools->config['paging'] )
		{				
			$TplPagination = $this->DevTools->ThemePregMatch( $Content, '~\[paging\](.*?)\[/paging\]~is' );
			$TplPaginationLink = $this->DevTools->ThemePregMatch( $Content, '~\[page_link\](.*?)\[/page_link\]~is' );
			$TplPaginationThis = $this->DevTools->ThemePregMatch( $Content, '~\[page_this\](.*?)\[/page_this\]~is' );
		
			$this->DevTools->ThemePregReplace( "page_link", $TplPagination, $this->DevTools->API->Pagination( $NumData, $GET['page'], $this->DevTools->config_dle['http_home_url'] . $this->DevTools->config['page'] . ".html/Refund/main/page:{p}", $TplPaginationLink, $TplPaginationThis ) );
			$this->DevTools->ThemePregReplace( "page_this", $TplPagination );
						
			$this->DevTools->ThemeSetElementBlock( "paging", $TplPagination );
				
		} else
			$this->DevTools->ThemeSetElementBlock( "paging", "" );
			
		/* LOG NULL */
		if( $Line )	$this->DevTools->ThemeSetElementBlock( "not_history", "" );
		else 		$this->DevTools->ThemeSetElementBlock( "not_history", $TplLineNull );
	
		$this->DevTools->ThemeSetElementBlock( "history", $Line );
		/* History END */
	
		return $this->DevTools->Show( $Content, "Refund" );
	}
	
	private function xfield( $key )
	{
		$arrUserfields = $this->DevTools->ParsUserXFields( $this->DevTools->member_id['xfields'] );
		
		return $arrUserfields[$key];
	}

}
?>