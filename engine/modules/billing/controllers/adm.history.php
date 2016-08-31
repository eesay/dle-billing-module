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
		$this->DevTools->ThemeEchoHeader();
		
		$Content = $Get['user'] ? $this->DevTools->MakeMsgInfo( "<a href='{$PHP_SELF}?mod=billing&c=history' title='{$this->DevTools->lang['remove']}' class='btn btn-red'><i class='icon-remove'></i> " . $Get['user'] . "</a> {$this->DevTools->lang['info_login']}", "icon-user", "blue") : "";
		
		$Content .= $this->DevTools->ThemeHeadStart( $this->DevTools->lang['history_title'], "<a href=\"javascript:ShowOrHide('searchhistory');\"><i class=\"icon-search\"></i> ".$this->DevTools->lang['history_search']."</a>" );
		
		/* Search Form */
		$Content .= "<div style=\"display:none\" id=\"searchhistory\">";

			$this->DevTools->ThemeAddStr( $this->DevTools->lang['search_pcode'], $this->DevTools->lang['search_pcode_desc'], "<input name=\"search_plugin\" class=\"edit bk\" type=\"text\" value=\"" . $_POST['search_plugin'] ."\" style=\"width: 100%\">" );
			$this->DevTools->ThemeAddStr( $this->DevTools->lang['search_pid'], $this->DevTools->lang['search_pcode_desc'], "<input name=\"search_plugin_id\" class=\"edit bk\" type=\"text\" value=\"" . $_POST['search_plugin_id'] ."\" style=\"width: 100%\">" );
			$this->DevTools->ThemeAddStr( $this->DevTools->lang['users_summa'], $this->DevTools->lang['search_summa_desc'], $this->DevTools->MakeDropDown( $this->DevTools->lang['search_tsd'], "search_type", $_POST['search_type'] )
																															. $this->DevTools->MakeDropDown( $this->DevTools->lang['search_type_operation'], "search_logick", $_POST['search_logick'] )
																															. "<br /><br /><input name=\"search_summa\" class=\"edit bk\" type=\"text\" value=\"" . $_POST['search_summa'] ."\" style=\"width: 100%\">" );
			$this->DevTools->ThemeAddStr( $this->DevTools->lang['search_user'], $this->DevTools->lang['search_user_desc'], "<input name=\"search_login\" class=\"edit bk\" type=\"text\" value=\"" . $_POST['search_login'] ."\" style=\"width: 100%\">" );
			$this->DevTools->ThemeAddStr( $this->DevTools->lang['search_comm'], $this->DevTools->lang['search_comm_desc'], "<input name=\"search_comment\" class=\"edit bk\" type=\"text\" value=\"" . $_POST['search_comment'] ."\" style=\"width: 100%\">" );
			$this->DevTools->ThemeAddStr( $this->DevTools->lang['search_date'], $this->DevTools->lang['search_date_desc'], $this->DevTools->MakeDropDown( $this->DevTools->lang['search_type_operation'], "search_date_logick", $_POST['search_date_logick'] ) . "<br /><br />" . $this->DevTools->MakeCalendar("search_date", $_POST['search_date'], "width: 100%") );
																																		
			$Content .= $this->DevTools->ThemeParserStr();
			$Content .= $this->DevTools->ThemePadded( $this->DevTools->MakeButton("search_btn", $this->DevTools->lang['history_search_btn'], "green") . "<a href=\"\" class=\"btn btn-default\" style=\"margin:7px;\">{$this->lang['history_search_btn_null']}</a>" );			 
			
		$Content .= "</div>";
		
		/* List */
		$this->DevTools->ThemeAddTR( array
									( 	
										'<td width="1%">#</td>',
										'<td width="12%">'.$this->DevTools->lang['history_code'].'</td>',
										'<td width="12%">'.$this->DevTools->lang['history_summa'].'</td>',
										'<td width="15%">'.$this->DevTools->lang['history_date'].'</td>',
										'<td width="12%">'.$this->DevTools->lang['history_user'].'</td>',
										'<td width="15%">'.$this->DevTools->lang['history_balance'].'</td>',
										'<td>'.$this->DevTools->lang['history_comment'].'</td>'
									) );

		/* DB | Search */
		if( isset( $_POST['search_btn'] ) )
		{
			if( $_POST['user_hash'] == "" or $_POST['user_hash'] != $this->DevTools->hash )
			{       
				return "Hacking attempt! User not found {$_POST['user_hash']}";   
			}
			
			if( !in_array( $_POST['search_logick'], array('>', '<', '=', '!=') ) ) 			$_POST['search_logick'] = "=";
			if( !in_array( $_POST['search_date_logick'], array('>', '<', '=', '!=') ) ) 	$_POST['search_date_logick'] = "=";
			
			if( $_POST['search_type'] == "plus" )			$SearchTypeSumma = "history_plus ".$_POST['search_logick']."'{s}' and history_minus='0' "; 
			elseif( $_POST['search_type'] == "minus" )		$SearchTypeSumma = "history_minus".$_POST['search_logick']."'{s}' and history_plus='0' ";
			else 											$_POST['search_summa'] = "";

			$this->DevTools->Model->DbWhere( array(
													"history_plugin ='{s}' " => $_POST['search_plugin'],
													"history_plugin_id ='{s}' " => intval( $_POST['search_plugin_id'] ),
													"$SearchTypeSumma" => $_POST['search_summa'],
													"history_date ".$_POST['search_date_logick']."'{s}' " => strtotime( $_POST['search_date'] ),
													"history_user_name LIKE '{s}' " => $_POST['search_login'],
													"history_text LIKE '{s}' " => $_POST['search_comment']
												), true );

			$PerPage = 100;
			$Data = $this->DevTools->Model->DbGetHistory( 1, $PerPage );
		} 
		else
		{	
			$this->DevTools->Model->DbWhere( array( "history_user_name = '{s}' " => $Get['user'] ) );
					
			$PerPage = 30;
			$Data = $this->DevTools->Model->DbGetHistory( $Get['page'], $PerPage );
		}

		// - all lines
		$NumData = $this->DevTools->Model->DbGetHistoryNum();
			
		foreach( $Data as $Value )
		{
			$this->DevTools->ThemeAddTR( array( 	$Value['history_id'],
													$Value['history_plugin'].":".$Value['history_plugin_id'],
													$Value['history_plus'] ? "<font color=\"green\">+".$Value['history_plus']." ".$Value['history_currency']."</font>":"<font color=\"red\">-".$Value['history_minus']." ".$Value['history_currency']."</font>",
													$this->DevTools->ThemeChangeTime( $Value['history_date'] ),
													$this->DevTools->ThemeInfoUser( $Value['history_user_name'] ),
													$this->DevTools->API->Convert( $Value['history_balance'] ) ." ". $this->DevTools->API->Declension( $Value['history_balance'] ),
													$Value['history_text']
										) );
		}
									
		$Content .= $this->DevTools->ThemeParserTable();
				
		/* Null */
		if( !$NumData )
			$Content .= $this->DevTools->ThemePadded( $this->DevTools->lang['history_no'], '' );

		/* Paging */
		else 
			$Content .= $this->DevTools->ThemePadded( "<ul class=\"pagination pagination-sm\">".$this->DevTools->API->Pagination( $NumData, $Get['page'], $PHP_SELF . "?mod=billing&c=history&p=" . ( $Get['user'] ? "user/{$Get['user']}/" : "" ) . "page/{p}", " <li><a href=\"{page_num_link}\">{page_num}</a></li>", "<li class=\"active\"><span>{page_num}</span></li>", $PerPage )."</ul>" );

		$Content .= $this->DevTools->ThemeHeadClose();
		$Content .= $this->DevTools->ThemeEchoFoother();
		
		return $Content;
	}
	
}
?>