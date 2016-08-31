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
		# Edit
		if( isset( $_POST['edit_btn'] ) )
		{
			if( $_POST['user_hash'] == "" or $_POST['user_hash'] != $this->DevTools->hash )
			{       
				return "Hacking attempt! User not found {$_POST['user_hash']}";   
			}
		
			$EditName = explode(",", $this->DevTools->Model->db->safesql( $_POST['edit_name'] ) );
			
			$EditComm = $this->DevTools->Model->db->safesql( $_POST['edit_comm'] );
			$EditGroup = intval( $_POST['edit_group'] );
			$EditDo = intval( $_POST['edit_do'] );
			$EditSumma = $_POST['edit_summa'];
		
			$Error = "";
			
			if( !count( $EditName ) and !$EditGroup )
				$Error = $this->DevTools->lang['users_er_user'];
			if( !$EditSumma )
				$Error = $this->DevTools->lang['users_er_summa'];

			$EditSumma= $this->DevTools->API->Convert( $EditSumma );
			
				if( $Error )
					
					$this->DevTools->ThemeMsg( $this->DevTools->lang['error'], $Error );
					
				else if( $EditGroup )
				{
					/* Edit by group */
					if( $EditDo )
						$this->DevTools->Model->db->query( "UPDATE " . USERPREFIX . "_users SET {$this->DevTools->config['fname']}={$this->DevTools->config['fname']}+'$EditSumma' where user_group='$EditGroup'");
					else
						$this->DevTools->Model->db->query( "UPDATE " . USERPREFIX . "_users SET {$this->DevTools->config['fname']}={$this->DevTools->config['fname']}-'$EditSumma' where user_group='$EditGroup'");
							
					$this->DevTools->ThemeMsg( $this->DevTools->lang['ok'], $this->DevTools->lang['users_ok_group'] );					
				}
				else
				{	
					/* Edit by name */
					if( $EditDo )
					{	
						foreach( $EditName as $name )
							if( trim($name) ) $this->DevTools->API->PlusMoney( $name, $EditSumma, $EditComm, '', "users", $this->DevTools->member_id['user_id'] );
					}
					else
					{
						foreach( $EditName as $name )
							if( trim($name) ) $this->DevTools->API->MinusMoney( $name, $EditSumma, $EditComm, '', "users", $this->DevTools->member_id['user_id'] );

					}
							
					$this->DevTools->ThemeMsg( $this->DevTools->lang['ok'], $this->DevTools->lang['users_ok'], $PHP_SELF . "?mod=billing&c=users" );
				}
			
		}
		
		# Page
		$this->DevTools->ThemeEchoHeader();
		
		$Content = $this->DevTools->ThemeHeadStart( $this->DevTools->lang['users_title_full'], "<a href=\"javascript:ShowOrHide('searchusers');\"><i class=\"icon-search\"></i> ". $this->DevTools->lang['users_search'] ."</a>" );
		
		# Search form
		$Content .= "<div style=\"display:none\" name=\"searchusers\" id=\"searchusers\">";
			
			$this->DevTools->ThemeAddStr( $this->DevTools->lang['users_label'], $this->DevTools->lang['users_label_desc'], "<input name=\"search_name\" class=\"edit bk\" type=\"text\" style=\"width: 100%\" value=\"" . $_POST['search_name'] ."\">" );
			$this->DevTools->ThemeAddStr( $this->DevTools->lang['user_se_balance'], $this->DevTools->lang['user_se_balance_desc'], $this->DevTools->MakeDropDown( $this->DevTools->lang['search_type_operation'], "search_logick", $_POST['search_logick'] ) . "<br /><br /><input name=\"search_balance\" class=\"edit bk\" type=\"text\" style=\"width: 100%\" value=\"" . $_POST['search_balance'] ."\">" );
																					
			$Content .= $this->DevTools->ThemeParserStr();
			$Content .= $this->DevTools->ThemePadded( $this->DevTools->MakeButton("search_btn", $this->DevTools->lang['history_search_btn'], "green") . $this->DevTools->MakeButton("reset_btn", $this->DevTools->lang['history_search_btn_null'], "default", false) );			 
			
		$Content .= "</div>";
		
		# Data list
		$this->DevTools->ThemeAddTR( array( 	'<td width="15%">'.$this->DevTools->lang['users_tanle_login'].'</td>',
												'<td>'.$this->DevTools->lang['users_tanle_email'].'</td>',
												'<td>'.$this->DevTools->lang['users_tanle_group'].'</td>',
												'<td>'.$this->DevTools->lang['users_tanle_datereg'].'</td>',
												'<td>'.$this->DevTools->lang['users_tanle_balance'].'</td>'
											) );

		/* Search data */
		if( isset( $_POST['search_btn'] ) )
		{
			if( $_POST['user_hash'] == "" or $_POST['user_hash'] != $this->DevTools->hash )
			{       
				return "Hacking attempt! User not found {$_POST['user_hash']}";   
			}		
			
			if( !in_array( $_POST['search_logick'], array('>', '<', '=', '!=') ) ) $_POST['search_logick'] = "=";

			$this->DevTools->Model->DbWhere( array( 
													"name LIKE '%{s}%' or email LIKE '%{s}%' " => $_POST['search_name'],
													"{$this->DevTools->config['fname']} {$_POST['search_logick']}'{s}' " => $_POST['search_balance']
											) );

			$Data = $this->DevTools->Model->DbSearchUsers();
		}
		else
		{
			$this->DevTools->Model->DbWhere( array( "{$this->DevTools->config['fname']}>0 " => 1 ) );
			
			$Data = $this->DevTools->Model->DbSearchUsers( 10 );
		}

		/* Users list */
		foreach( $Data as $Value )
		{
			$this->DevTools->ThemeAddTR( array
												( 	
													"<span onClick=\"usersAdd( '" . $Value['name'] . "' )\" id=\"user_".$Value['name']."\" style=\"cursor: pointer\"><i class=\"icon-plus\" style=\"margin-left: 10px; vertical-align: middle\"></i></span>" . 
													$this->DevTools->ThemeInfoUser( $Value['name'] ),
													$Value['email'],
													$this->user_group[$Value['user_group']]['group_name'],
													$this->DevTools->ThemeChangeTime( $Value['reg_date']),
													$this->DevTools->API->Convert( $Value[$this->DevTools->config['fname']] ) ." ". $this->DevTools->API->Declension( $Value[$this->DevTools->config['fname']] )
												) );
		}
									
		$Content .= $this->DevTools->ThemeParserTable();

		/* Null */
		if( !count($Data) )
			$Content .=  $this->DevTools->ThemePadded( $this->DevTools->lang['history_no'], '' );

		$Content .= $this->DevTools->ThemeHeadClose();

		/* Settings */
		$Content .= $this->DevTools->ThemeHeadStart( $this->DevTools->lang['users_edit'] );

		$this->DevTools->ThemeAddStr( $this->DevTools->lang['users_login'], $this->DevTools->lang['users_login_desc'], "<input name=\"edit_name\" id=\"edit_name\" class=\"edit bk\" value=\"". $_GET['login'] ."\" type=\"text\">" );
		$this->DevTools->ThemeAddStr( $this->DevTools->lang['users_group'], $this->DevTools->lang['users_group_desc'], "<select name=\"edit_group\" id=\"edit_group\" class=\"uniform\" onchange=\"usersSelectSend(this)\"><option value=\"\"></option>".$this->DevTools->GetGroups(false, 5)."</select>" );
		$this->DevTools->ThemeAddStr( $this->DevTools->lang['users_edit_do'], $this->DevTools->lang['users_edit_do_desc'], "<select name=\"edit_do\" class=\"uniform\"><option value=\"1\">".$this->DevTools->lang['users_plus']."</option><option value=\"0\">".$this->DevTools->lang['users_minus']."</option></select>" );
		$this->DevTools->ThemeAddStr( $this->DevTools->lang['users_summa'], $this->DevTools->lang['users_summa_desc'], "<input name=\"edit_summa\" class=\"edit bk\" type=\"text\" style=\"width: 50%\" required>" . $this->DevTools->API->Declension( 10 ) );
		$this->DevTools->ThemeAddStr( $this->DevTools->lang['users_comm'], $this->DevTools->lang['users_comm_desc'], "<input name=\"edit_comm\" id=\"edit_comm\" class=\"edit bk\" type=\"text\" style=\"width: 100%\">" );

		$Content .= $this->DevTools->ThemeParserStr();
		$Content .= $this->DevTools->ThemePadded( $this->DevTools->MakeButton("edit_btn", $this->DevTools->lang['act'], "green") );			 
			
		$Content .= $this->DevTools->ThemeHeadClose();
		$Content .= $this->DevTools->ThemeEchoFoother();

		return $Content;
	}
	
}
?>