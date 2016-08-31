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
	function main( $GET )
	{
		/* Login */
		if( !$this->DevTools->member_id['name'] ) return $this->DevTools->lang['pay_need_login'];

		/* Loaded tpl in memory */
		$Content = $this->DevTools->ThemeLoad( "history" );
		
		$Line = '';

		$TplLine = $this->DevTools->ThemePregMatch( $Content, '~\[history\](.*?)\[/history\]~is' );
		$TplLineNull = $this->DevTools->ThemePregMatch( $Content, '~\[not_history\](.*?)\[/not_history\]~is' );
		$TplLineDate = $this->DevTools->ThemePregMatch( $TplLine, '~\{date=(.*?)\}~is' );
		
		/* DB Filter */
		$this->DevTools->Model->DbWhere( array( "history_user_name = '{s}' " => $this->DevTools->member_id['name'] ) );
		
		/* DB sql */
		$Data = $this->DevTools->Model->DbGetHistory( $GET['page'], $this->DevTools->config['paging'] );
		$NumData = $this->DevTools->Model->DbGetHistoryNum();

		/* Data */
		foreach( $Data as $Value )
		{
			$TimeLine = $TplLine;
			$TimeLine = str_replace("{date={$TplLineDate}}", $this->DevTools->ThemeChangeTime( $Value['history_date'], $TplLineDate ), $TimeLine);
			$TimeLine = str_replace("{comment}", $Value['history_text'], $TimeLine);
			$TimeLine = str_replace("{icon}", $this->DevTools->ThemeIconPlugin( $Value['history_plugin'] ), $TimeLine);
			$TimeLine = str_replace("{balance}", "{$Value['history_balance']} {$this->DevTools->API->Declension( $Value['history_balance'] )}", $TimeLine);
			$TimeLine = str_replace("{summa}", $Value['history_plus']	? "<font color=\"green\">+{$Value['history_plus']} {$Value['history_currency']}</font>"
																		: "<font color=\"red\">-{$Value['history_minus']} {$Value['history_currency']}</font>", $TimeLine);

			$Line .= $TimeLine;
		}
	
		/* Pagination */
		if( $NumData > $this->DevTools->config['paging'] )
		{
			$TplPagination = $this->DevTools->ThemePregMatch( $Content, '~\[paging\](.*?)\[/paging\]~is' );
			$TplPaginationLink = $this->DevTools->ThemePregMatch( $Content, '~\[page_link\](.*?)\[/page_link\]~is' );
			$TplPaginationThis = $this->DevTools->ThemePregMatch( $Content, '~\[page_this\](.*?)\[/page_this\]~is' );
	
			$this->DevTools->ThemePregReplace( "page_link", $TplPagination, $this->DevTools->API->Pagination( $NumData, $GET['page'], $this->DevTools->config_dle['http_home_url'] . $this->DevTools->config['page'] . ".html/log/main/page:{p}", $TplPaginationLink, $TplPaginationThis ) );
			$this->DevTools->ThemePregReplace( "page_this", $TplPagination );
			
			$this->DevTools->ThemeSetElementBlock( "paging", $TplPagination );
		} else
			$this->DevTools->ThemeSetElementBlock( "paging", "" );
	
		/* LOG NULL */
		if( $Line )	$this->DevTools->ThemeSetElementBlock( "not_history", '' );
		else 		$this->DevTools->ThemeSetElementBlock( "not_history", $TplLineNull );
	
		$this->DevTools->ThemeSetElementBlock( "history", $Line );

		return $this->DevTools->Show( $Content );
	}

}
?>