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
		if( file_exists( MODULE_DATA."/plugin.transfer.php" ) )	include MODULE_DATA."/plugin.transfer.php";
		
		$this->plugin_config = $plugin_config;
	}
	
	function ok( $GET )
	{
		/* Login */
		if( !$this->DevTools->member_id['name'] ) return $this->DevTools->lang['pay_need_login'];
	
		/* Status */
		if( !$this->plugin_config['status'] ) return $this->DevTools->ThemeMsg( $this->DevTools->lang['pay_error_title'], $this->DevTools->lang['cabinet_off'], "Transfer" );

		$Get = explode("|", base64_decode( urldecode( $GET['sign'] ) ) );

		if( count($Get) != 3 )
				return $this->DevTools->lang['pay_hash_error'];
		
		return $this->DevTools->ThemeMsg( $this->DevTools->lang['transfer_msgOk'], str_replace( "{link}", $this->DevTools->config_dle['http_home_url']."user/".urlencode( $Get[0] ), 
																					str_replace( "{user}", $Get[0], 
																						str_replace( "{com}", $Get[1]." ".$Get[2], $this->DevTools->lang['transfer_log_text'] ) ) ), "Transfer" );
	}
	
	function main( $GET )
	{
		/* Login */
		if( !$this->DevTools->member_id['name'] ) return $this->DevTools->lang['pay_need_login'];
	
		/* Status */
		if( !$this->plugin_config['status'] ) return $this->DevTools->ThemeMsg( $this->DevTools->lang['pay_error_title'], $this->DevTools->lang['cabinet_off'], "Transfer" );

		/* Post */
		if( isset($_POST['submit']) )
		{
			$PostSearchUser = $this->DevTools->Model->DbSearchUserByName( htmlspecialchars( trim( $_POST['bs_user_name'] ), ENT_COMPAT, $this->DevTools->config_dle['charset'] ) );
			$PostMoney = $this->DevTools->Model->db->safesql( $_POST['bs_summa'] );
			$PostMoneyCommission = $this->DevTools->API->Convert( ( $PostMoney / 100 ) * $this->plugin_config['com'] );

			$Error = "";

			if( !isset( $_POST['bs_hash'] ) OR $_POST['bs_hash'] != $this->DevTools->hash() )
				$Error = $this->DevTools->lang['pay_hash_error'];
			
			else if( !$PostMoney )
				$Error = $this->DevTools->lang['pay_summa_error'];
	
			else if( !$PostSearchUser['name'] )
				$Error = $this->DevTools->lang['transfer_error_get'];
	
			else if( $PostMoney > $this->DevTools->BalanceUser )
				$Error = $this->DevTools->lang['refund_error_balance'];
	
			else if( $PostSearchUser['name'] == $this->DevTools->member_id['name'] )
				$Error = $this->DevTools->lang['transfer_error_name_me'];
			
			else if( $PostMoney < $this->plugin_config['minimum'] )
				$Error = $this->DevTools->lang['transfer_error_minimum'] . $this->plugin_config['minimum']." ".$this->DevTools->API->Declension( $this->plugin_config['minimum'] );	
			
			if( $Error ) 
				return $this->DevTools->ThemeMsg( $this->DevTools->lang['pay_error_title'], $Error, "Transfer" );

			// - Process
			$PostMoney = $this->DevTools->API->Convert( $_POST['bs_summa'] );
			
			$this->DevTools->API->MinusMoney( $this->DevTools->member_id['name'], $PostMoney, str_replace( "{login}", $PostSearchUser['name'], $this->DevTools->lang['transfer_log_for'] ), '', "transfer", $PostSearchUser['user_id'] );
			$this->DevTools->API->PlusMoney( $PostSearchUser['name'], ($PostMoney-$PostMoneyCommission), str_replace( "{login}", $this->DevTools->member_id['name'], $this->DevTools->lang['transfer_log_from'] ), '', "transfer", $PostSearchUser['user_id']);
			
			header( 'Location: '.$this->DevTools->config_dle['http_home_url'].$this->DevTools->config['page'].'.html/Transfer/ok/sign:'.urlencode( base64_encode($PostSearchUser['name']."|".$PostMoneyCommission ."|".$this->DevTools->API->Declension( $PostMoneyCommission ) ) ) );

			return TRUE;
		}
	
		/* PAGE */
		$GetSum = $GET['sum'] ? $this->DevTools->API->Convert( $GET['sum'] ) : $this->plugin_config['minimum'];
		$GetTo = $this->DevTools->Model->db->safesql( $GET['to'] );
		
		$this->DevTools->ThemeSetElement( "{hash}", $this->DevTools->hash() );
		$this->DevTools->ThemeSetElement( "{get_summ}", $GetSum );
		$this->DevTools->ThemeSetElement( "{get_summ_valuta}", $this->DevTools->API->Declension( $GetSum ) );
		$this->DevTools->ThemeSetElement( "{minimum}", $this->plugin_config['minimum'] );
		$this->DevTools->ThemeSetElement( "{minimum_valuta}", $this->DevTools->API->Declension( $this->plugin_config['minimum'] ) );
		$this->DevTools->ThemeSetElement( "{commission}", intval( $this->plugin_config['com'] ) );
		$this->DevTools->ThemeSetElement( "{to}", $GetTo );
	
		/* History */
		$Content = $this->DevTools->ThemeLoad( "plugins/transfer" );
		$Line = '';

		$TplLine = $this->DevTools->ThemePregMatch( $Content, '~\[history\](.*?)\[/history\]~is' );
		$TplLineNull = $this->DevTools->ThemePregMatch( $Content, '~\[not_history\](.*?)\[/not_history\]~is' );
		$TplLineDate = $this->DevTools->ThemePregMatch( $TplLine, '~\{date=(.*?)\}~is' );
		
		/* DB Filter */
		$this->DevTools->Model->DbWhere( array( "history_plugin = '{s} ' "=>'transfer', "history_user_name = '{s}' " => $this->DevTools->member_id['name'] ) );

		/* DB Sql */
		$Data = $this->DevTools->Model->DbGetHistory( $GET['page'], $this->DevTools->config['paging'] );
		$NumData = $this->DevTools->Model->DbGetHistoryNum();

			foreach( $Data as $Value )
			{
				$TimeLine = $TplLine;
				$TimeLine = str_replace("{date={$TplLineDate}}", $this->DevTools->ThemeChangeTime( $Value['history_date'], $TplLineDate ), $TimeLine);
				$TimeLine = str_replace("{transfer_summa}", $Value['history_plus'] ? "<font color=\"green\">+".$Value['history_plus']." ".$Value['history_currency']."</font>":"<font color=\"red\">-".$Value['history_minus']." ".$Value['history_currency']."</font>", $TimeLine);
				$TimeLine = str_replace("{transfer_user}", $Value['history_text'], $TimeLine);

				$Line .= $TimeLine;
			}

		/* Pagination */
		if( $NumData > $this->DevTools->config['paging'] )
		{
			$TplPagination = $this->DevTools->ThemePregMatch( $Content, '~\[paging\](.*?)\[/paging\]~is' );
			$TplPaginationLink = $this->DevTools->ThemePregMatch( $Content, '~\[page_link\](.*?)\[/page_link\]~is' );
			$TplPaginationThis = $this->DevTools->ThemePregMatch( $Content, '~\[page_this\](.*?)\[/page_this\]~is' );
	
			$this->DevTools->ThemePregReplace( "page_link", $TplPagination, $this->DevTools->API->Pagination( $NumData, $GET['page'], $this->DevTools->config_dle['http_home_url'] . $this->DevTools->config['page'] . ".html/Transfer/main/page:{p}", $TplPaginationLink, $TplPaginationThis ) );
			$this->DevTools->ThemePregReplace( "page_this", $TplPagination );
			
			$this->DevTools->ThemeSetElementBlock( "paging", $TplPagination );
		}
		else
			$this->DevTools->ThemeSetElementBlock( "paging", "" );
			
		/* LOG NULL */
		if( $Line )	$this->DevTools->ThemeSetElementBlock( "not_history", "" );
		else 		$this->DevTools->ThemeSetElementBlock( "not_history", $TplLineNull );
	
		$this->DevTools->ThemeSetElementBlock( "history", $Line );
		/* History END */

		return $this->DevTools->Show( $Content, "transfer" );
	}

}
?>