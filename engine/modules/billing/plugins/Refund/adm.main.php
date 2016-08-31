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
		/* Save */
		if( isset( $_POST['save'] ) )
		{
			if( $_POST['user_hash'] == "" or $_POST['user_hash'] != $this->DevTools->hash )
			{       
				return "Hacking attempt! User not found {$_POST['user_hash']}";   
			}

			$this->DevTools->SaveConfig("plugin.refund", $_POST['save_con'], "plugin_config");
			$this->DevTools->ThemeMsg( $this->DevTools->lang['ok'], $this->DevTools->lang['save_settings'] );
		}
		
		/* Act */
		if( isset( $_POST['act_do'] ) )
		{
			if( $_POST['user_hash'] == "" or $_POST['user_hash'] != $this->DevTools->hash )
			{       
				return "Hacking attempt! User not found {$_POST['user_hash']}";   
			}
		
			$RemoveList = $_POST['remove_list'];
			$RemoveAct = $_POST['act'];
		  
			foreach($RemoveList as $remove_id)
			{
				$remove_id = intval( $remove_id );
				
				if( !$remove_id ) continue;
				
				if( $RemoveAct == "ok" )
					$this->DevTools->Model->DbRefundStatus( $remove_id, $this->DevTools->_TIME );
				else if( $RemoveAct == "wait" )
					$this->DevTools->Model->DbRefundStatus( $remove_id );
				else if( $RemoveAct == "remove" )
					$this->DevTools->Model->DbRefundRemore( $remove_id );
				else if( $RemoveAct == "back" ) {
					
					$GetRefund = $this->DevTools->Model->DbGetRefundById( $remove_id );
					
					$this->DevTools->API->PlusMoney(	$GetRefund['refund_user'], 
														$this->DevTools->API->Convert( $GetRefund['refund_summa'] ), 
														str_replace("{remove_id}", $remove_id, $this->DevTools->lang['refund_back']), 
														'', 
														"refund", 
														$remove_id );
					
					$this->DevTools->Model->DbRefundRemore( $remove_id );
				}
			}
		
			$this->DevTools->ThemeMsg( $this->DevTools->lang['ok'], $this->DevTools->lang['refund_act'], $PHP_SELF . "?mod=billing&c=Refund" );
		}
	
		/* Load and Install */
		$plugin_config = $this->DevTools->LoadConfig( "refund", "plugin_config", true, array('status'=>"0") );

		/* Page */
		$this->DevTools->ThemeEchoHeader();
		
		$Content = $Get['user'] ? $this->DevTools->MakeMsgInfo( "<a href='{$PHP_SELF}?mod=billing&c=refund' title='{$this->DevTools->lang['remove']}' class='btn btn-red'><i class='icon-remove'></i> " . $Get['user'] . "</a> {$this->DevTools->lang['info_login']}", "icon-user", "blue") : "";
		
		$Content .= $this->DevTools->ThemeHeadStart( $this->DevTools->lang['refund_title'], "<a href=\"javascript:ShowOrHide('searchhistory');\"><i class=\"icon-search\"></i> ".$this->DevTools->lang['history_search']."</a>" ); 
		
		/* Search Form */
		$Content .= "<div style=\"display:none\" name=\"searchhistory\" id=\"searchhistory\">";

			$this->DevTools->ThemeAddStr( $this->DevTools->lang['refund_se_summa'], $this->DevTools->lang['refund_se_summa_desc'], $this->DevTools->MakeDropDown( $this->DevTools->lang['search_type_operation'], "search_logick", $_POST['search_logick'] ) . "<br /><br /><input name=\"search_summa\" value=\"".$_POST['search_summa']."\" class=\"edit bk\" style=\"width: 100%\" type=\"text\">" );
			$this->DevTools->ThemeAddStr( $this->DevTools->lang['refund_se_req'], $this->DevTools->lang['refund_se_req_desc'], "<input name=\"search_requisites\" value=\"".$_POST['search_requisites']."\" class=\"edit bk\" style=\"width: 100%\" type=\"text\">" );
			$this->DevTools->ThemeAddStr( $this->DevTools->lang['search_user'], $this->DevTools->lang['search_user_desc'], "<input name=\"search_login\" value=\"".$_POST['search_login']."\" class=\"edit bk\" style=\"width: 100%\" type=\"text\">" );
			$this->DevTools->ThemeAddStr( $this->DevTools->lang['refund_se_status'], $this->DevTools->lang['refund_se_status_desc'], $this->DevTools->MakeDropDown( $this->DevTools->lang['refund_search'], "search_status", $_POST['search_status'] ) );
			$this->DevTools->ThemeAddStr( $this->DevTools->lang['search_date'], $this->DevTools->lang['search_date_desc'], $this->DevTools->MakeDropDown( $this->DevTools->lang['search_type_operation'], "search_date_logick", $_POST['search_date_logick'] ) . "<br /><br /><input data-rel=\"calendardate\" type=\"text\" name=\"search_date\" value=\"".$_POST['search_date']."\" class=\"edit bk\" style=\"width: 100%\" >" );
																																		
			$Content .= $this->DevTools->ThemeParserStr();
			$Content .= $this->DevTools->ThemePadded( "<input class=\"btn btn-blue\" style=\"margin:7px;\" name=\"search_btn\" type=\"submit\" value=\"".$this->DevTools->lang['history_search_btn']."\"><a href=\"\" class=\"btn btn-default\">".$this->DevTools->lang['history_search_btn_null']."</a><input type=\"hidden\" name=\"user_hash\" value=\"" . $this->DevTools->hash . "\" />" );			 
			
		$Content .= "</div>";
		
		$this->DevTools->ThemeAddTR( array( 	'<td width="1%"><b>#</b></td>',
												'<td width="15%">'.$this->DevTools->lang['refund_summa'].'</td>',
												'<td width="15%">'.$this->DevTools->lang['refund_commision_list'].'</td>',
												'<td width="20%">'.$this->DevTools->lang['refund_requisites'].'</td>',
												'<td>'.$this->DevTools->lang['history_date'].'</td>',
												'<td>'.$this->DevTools->lang['history_user'].'</td>',
												'<td>'.$this->DevTools->lang['status'].'</td>',
												'<td><center><input type="checkbox" value="" name="remove_list[]" onclick="checkAll(this)" /></center></td>'
											) );

		/* DB | Search */
		if( isset( $_POST['search_btn'] ) )
		{	
			if( $_POST['user_hash'] == "" or $_POST['user_hash'] != $this->DevTools->hash )
			{       
				return "Hacking attempt! User not found {$_POST['user_hash']}";   
			}
			
			if( !in_array( $_POST['search_logick'], array('>', '<', '=', '!=') ) ) $_POST['search_logick'] = "=";
			if( !in_array( $_POST['search_date_logick'], array('>', '<', '=', '!=') ) ) $_POST['search_date_logick'] = "=";
			
			$SearchStatusValue = 1;
			
			if( $_POST['search_status']=="wait" )
				$SearchStatus = "refund_date_return='0'";			
			elseif( $_POST['search_status']=="ok" )
				$SearchStatus = "refund_date_return!='0'";
			else 
				$SearchStatusValue = 0;

			$this->DevTools->Model->DbWhere( array
											(
												"refund_summa-refund_commission {$_POST['search_logick']}'{s}' " => $_POST['search_summa'],
												"refund_requisites LIKE '{s}' " => $_POST['search_requisites'],
												"refund_requisites LIKE '{s}' " => $_POST['search_requisites'],
												"refund_user LIKE '{s}' " => $_POST['search_login'],
												"$SearchStatus " => $SearchStatusValue,
												"refund_date ".$_POST['search_date_logick']."'{s}' " => strtotime( $_POST['search_date'] ),
											) );

            $PerPage = 100;
			$Data = $this->DevTools->Model->DbGetRefund( 1, $PerPage );
		}
		else
		{		
			$this->DevTools->Model->DbWhere( array( "refund_user = '{s}' " => $Get['user'] ) );
	
			$PerPage = 30;
			$Data = $this->DevTools->Model->DbGetRefund( $Get['page'], $PerPage );
		}

		$NumData = $this->DevTools->Model->DbGetRefundNum();
		
		foreach( $Data as $Value )
		{
			$this->DevTools->ThemeAddTR( array( 	$Value['refund_id'],
													$this->DevTools->API->Convert( $Value['refund_summa']-$Value['refund_commission'] )." ".$this->DevTools->API->Declension(($Value['refund_summa']-$Value['refund_commission']) ),
													$this->DevTools->API->Convert( $Value['refund_commission'] )." ".$this->DevTools->API->Declension( $Value['refund_commission'] ),
													$Value['refund_requisites'],
													$this->DevTools->ThemeChangeTime( $Value['refund_date']),
													$this->DevTools->ThemeInfoUser( $Value['refund_user'] ),
													$Value['refund_date_return'] ? "<font color=\"green\">".$this->DevTools->lang['refund_act_ok'] . ": " . langdate( "j F Y  G:i", $Value['refund_date_return'])."</font>": "<font color=\"red\">".$this->DevTools->lang['refund_wait']."</a>",
													'<center><input name="remove_list[]" value="'.$Value['refund_id'].'" type="checkbox"></center>'
												) );
		}
							
		$Content .= $this->DevTools->ThemeParserTable();

		/* Act and Paging */
		if( $NumData )	
			$Content .= $this->DevTools->ThemePadded( '
						<div class="pull-left" style="margin:7px; vertical-align: middle"><ul class="pagination pagination-sm">'.$this->DevTools->API->Pagination( $NumData, $Get['page'], $PHP_SELF . "?mod=billing&c=Refund&p=user/{$Get['user']}/page/{p}", " <li><a href=\"{page_num_link}\">{page_num}</a></li>", "<li class=\"active\"><span>{page_num}</span></li>", $PerPage ).'</ul></div>
											<select name="act" class="uniform">
												<option value="ok">'.$this->DevTools->lang['refund_act_ok'].'</option>
												<option value="wait">'.$this->DevTools->lang['refund_wait'].'</option>
												<option value="back">'.$this->DevTools->lang['refund_act_no'].'</option>
												<option value="remove">'.$this->DevTools->lang['remove'].'</option>
											</select>
											' . $this->DevTools->MakeButton("act_do", $this->DevTools->lang['act'], "gold") . '
						', 'box-footer', 'right' );

		/* Null */
		else
			$Content .= $this->DevTools->ThemePadded( $this->DevTools->lang['history_no'], '' );
		
		$Content .= $this->DevTools->ThemeHeadClose();

		$Content .= $this->DevTools->ThemeHeadStart( $this->DevTools->lang['main_settings'] );
		
		$this->DevTools->ThemeAddStr( $this->DevTools->lang['settings_status'], $this->DevTools->lang['refund_status_desc'], $this->DevTools->MakeCheckBox("save_con[status]", $plugin_config['status']) );
		$this->DevTools->ThemeAddStr( $this->DevTools->lang['refund_email'], $this->DevTools->lang['refund_email_desc'], $this->DevTools->MakeCheckBox("save_con[email]", $plugin_config['email']) );
		$this->DevTools->ThemeAddStr( $this->DevTools->lang['paysys_name'], $this->DevTools->lang['refund_name_desc'], "<input name=\"save_con[name]\" class=\"edit bk\" type=\"text\" size=\"50\" value=\"" . $plugin_config['name'] ."\">" );
		$this->DevTools->ThemeAddStr( $this->DevTools->lang['refund_minimum'], $this->DevTools->lang['refund_minimum_desc'], "<input name=\"save_con[minimum]\" class=\"edit bk\" type=\"text\" value=\"" . $plugin_config['minimum'] ."\"> " . $this->DevTools->API->Declension( $plugin_config['minimum'] ) );
		$this->DevTools->ThemeAddStr( $this->DevTools->lang['refund_commision'], $this->DevTools->lang['refund_commision_desc'], "<input name=\"save_con[com]\" class=\"edit bk\" type=\"text\" value=\"" . $plugin_config['com'] ."\"> %" );
		$this->DevTools->ThemeAddStr( $this->DevTools->lang['refund_field'], $this->DevTools->lang['refund_field_desc'], $this->DevTools->MakeDropDown( $this->DevTools->ThemeInfoUserXfields(), "save_con[requisites]", $plugin_config['requisites'] ) );
		$this->DevTools->ThemeAddStr( $this->DevTools->lang['refund_format'], $this->DevTools->lang['refund_format_desc'], "<input name=\"save_con[format]\" class=\"edit bk\" type=\"text\" value=\"" . $plugin_config['format'] ."\"> " );

		$Content .= $this->DevTools->ThemeParserStr();
		$Content .= $this->DevTools->ThemePadded( $this->DevTools->MakeButton("save", $this->DevTools->lang['act'], "green") );			 
		
		$Content .= $this->DevTools->ThemeHeadClose();
		$Content .= $this->DevTools->ThemeEchoFoother();
		
		return $Content;
	}
	
}
?>