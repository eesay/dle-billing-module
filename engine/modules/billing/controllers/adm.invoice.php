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
		$GetPaysysArray = $this->DevTools->GetPaysysArray();

		/* Act */
		if( isset( $_POST['act_do'] ) )
		{
			if( $_POST['user_hash'] == "" or $_POST['user_hash'] != $this->DevTools->hash )
			{
				return "Hacking attempt! User not found {$_POST['user_hash']}";
			}

			$MassList = $_POST['massact_list'];
			$MassAct = $_POST['act'];

			foreach( $MassList as $id )
			{
				$id = intval( $id );

				if( !$id ) continue;

				// - remove
				if( $MassAct == "remove" )
					$this->DevTools->Model->DbInvoiceRemove( $id );

				// - status ok
				if( $MassAct == "ok" )
					$this->DevTools->Model->DbInvoiceUpdate( $id );

				// - status no
				if( $MassAct == "no" )
					$this->DevTools->Model->DbInvoiceUpdate( $id, true );

				// - status ok and pay
				if( $MassAct == "ok_pay" )
				{
					$Invoice = $this->DevTools->Model->DbGetInvoiceByID( $id );

					if( $Invoice['invoice_user_name'] and !$Invoice['invoice_date_pay'] )
					{
						$this->DevTools->Model->DbInvoiceUpdate( $id );

						$this->DevTools->API->PlusMoney( $Invoice['invoice_user_name'], $Invoice['invoice_get'], str_replace( "{paysys}", $GetPaysysArray[$Invoice['invoice_paysys']]['title'], str_replace( "{money}", "{$Invoice['invoice_pay']} {$GetPaysysArray[$Invoice['invoice_paysys']]['currency']}", $this->DevTools->lang['pay_msgOk'] ) ), '', "pay", $id );
					}
				}
			}

			$this->DevTools->ThemeMsg( $this->DevTools->lang['ok'], $this->DevTools->lang['invoice_ok'], $PHP_SELF . "?mod=billing&c=invoice" );
		}

		/* Page */
		$this->DevTools->ThemeEchoHeader();

		$Content = $Get['user'] ? $this->DevTools->MakeMsgInfo( "<a href='{$PHP_SELF}?mod=billing&c=invoice' title='{$this->DevTools->lang['remove']}' class='btn btn-red'><i class='icon-remove'></i> " . $Get['user'] . "</a> {$this->DevTools->lang['info_login']}", "icon-user", "blue") : "";

		$Content .= $this->DevTools->ThemeHeadStart( $this->DevTools->lang['invoice_title'], "<a href=\"javascript:ShowOrHide('searchhistory');\"><i class=\"icon-search\"></i> ".$this->DevTools->lang['history_search']."</a>" );

		/* Search Form */
		$Content .= "<div style=\"display:none\" name=\"searchhistory\" id=\"searchhistory\">";

			$SelectPaysys = array();
			$SelectPaysys[] = $this->DevTools->lang['statistics_clean_4_s1'];

			foreach( $GetPaysysArray as $name=>$info )
				$SelectPaysys[$name] = $info['title'];

			$this->DevTools->ThemeAddStr( $this->DevTools->lang['invoice_ps'], $this->DevTools->lang['invoice_ps_desc'], $this->DevTools->MakeDropDown( $SelectPaysys, "search_paysys", $_POST['search_paysys'] ) );
			$this->DevTools->ThemeAddStr( $this->DevTools->lang['invoice_summa'], $this->DevTools->lang['invoice_summa_desc'], $this->DevTools->MakeDropDown( $this->DevTools->lang['search_type_operation'], "search_logick", $_POST['search_logick'] ) . "<br /><br /><input name=\"search_summa\" class=\"edit bk\" type=\"text\" value=\"" . $_POST['search_summa'] ."\" style=\"width: 100%\">" );
			$this->DevTools->ThemeAddStr( $this->DevTools->lang['search_user'], $this->DevTools->lang['search_user_desc'], "<input name=\"search_login\" class=\"edit bk\" type=\"text\" value=\"" . $_POST['search_login'] ."\" style=\"width: 100%\">" );
			$this->DevTools->ThemeAddStr( $this->DevTools->lang['invoice_status'], $this->DevTools->lang['invoice_status_desc'], $this->DevTools->MakeDropDown( array(''=>$this->DevTools->lang['invoice_status_1'], 'ok'=>$this->DevTools->lang['invoice_status_2'], 'no'=>$this->DevTools->lang['invoice_status_3'] ), "search_status", $_POST['search_status'] ) );
			$this->DevTools->ThemeAddStr( $this->DevTools->lang['search_date'], $this->DevTools->lang['search_date_desc'], $this->DevTools->MakeDropDown( $this->DevTools->lang['search_type_operation'], "search_date_logick", $_POST['search_date_logick'] ) . "<br /><br />" . $this->DevTools->MakeCalendar("search_date", $_POST['search_date'], "width: 100%") );

			$Content .= $this->DevTools->ThemeParserStr();
			$Content .= $this->DevTools->ThemePadded( $this->DevTools->MakeButton("search_btn", $this->DevTools->lang['history_search_btn'], "green") . "<a href=\"\" class=\"btn btn-default\" style=\"margin:7px;\">{$this->lang['history_search_btn_null']}</a>" );

		$Content .= "</div>";

		$this->DevTools->ThemeAddTR( array
								( 	'<td width="1%">#</td>',
									'<td width="10%">'.$this->DevTools->lang['invoice_str_payok'].'</td>',
									'<td width="10%">'.$this->DevTools->lang['invoice_str_get'].'</td>',
									'<td>'.$this->DevTools->lang['history_date'].'</td>',
									'<td>'.$this->DevTools->lang['invoice_str_ps'].'</td>',
									'<td>'.$this->DevTools->lang['history_user'].'</td>',
									'<td width="15%">'.$this->DevTools->lang['invoice_str_status'].'</td>',
									'<td width="20%">'.$this->DevTools->lang['invoice_info'].'</td>',
									'<td width="5%"><center><input type="checkbox" value="" name="massact_list[]" onclick="checkAll(this)" /></center></td>',
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

			if( $_POST['search_status']=="ok" )
				$SearchStatus = "invoice_date_pay!='0'";
			elseif( $_POST['search_status']=="no" )
				$SearchStatus = "invoice_date_pay='0'";
			else
				$SearchStatusValue = 0;

				$SearchArray = array
				(
					"invoice_pay {$_POST['search_logick']}'{s}' " => $_POST['search_summa'],
					"invoice_paysys='{s}' " => $_POST['search_paysys'],
					"invoice_user_name LIKE '{s}' " => $_POST['search_login'],
					"$SearchStatus " => $SearchStatusValue,
					"invoice_date_creat ".$_POST['search_date_logick']."'{s}' " => strtotime( $_POST['search_date'] )
				);

			$this->DevTools->Model->DbWhere( $SearchArray );

			$PerPage = 100;
			$Data = $this->DevTools->Model->DbGetInvoice( 1, $PerPage );
		}
		else
		{
			$this->DevTools->Model->DbWhere( array( "invoice_user_name = '{s}' " => $Get['user'] ) );

			$PerPage = 30;
			$Data = $this->DevTools->Model->DbGetInvoice( $Get['page'], $PerPage );
		}

		$NumData = $this->DevTools->Model->DbGetInvoiceNum();

		foreach( $Data as $Value )
		{
			$this->DevTools->ThemeAddTR( array
										( 		$Value['invoice_id'],
												$Value['invoice_pay'] . " ". $GetPaysysArray[$Value['invoice_paysys']]['currency'],
												$Value['invoice_get'] ." ". $this->DevTools->API->Declension( $Value['invoice_pay'] ),
												$this->DevTools->ThemeChangeTime( $Value['invoice_date_creat'] ),
												$this->DevTools->ThemeInfoBilling( $GetPaysysArray[$Value['invoice_paysys']] ),
												$this->DevTools->ThemeInfoUser( $Value['invoice_user_name'] ),
												"<center>".( $Value['invoice_date_pay'] ? "<span class=\"label\" style=\"background: #5cb85c\">{$this->DevTools->lang['invoice_payok']}</span>" : "<span class=\"label\" style=\"background: #377ca8\">".$this->DevTools->lang['invoice_status_3']."</span>" )."</center>",
												( $Value['invoice_date_pay'] ? $this->DevTools->lang['invoice_summa'] . " " . $this->DevTools->ThemeChangeTime( $Value['invoice_date_pay'] ) : "" ),
												"<center>".$this->DevTools->MakeCheckBox("massact_list[]", false, $Value['invoice_id'], false)."</center>"
										) );
		}

		$Content .= $this->DevTools->ThemeParserTable();

		/* Act and Paging */
		if( $NumData )
			$Content .= $this->DevTools->ThemePadded( '
											<div class="pull-left" style="margin:7px; vertical-align: middle">
												<ul class="pagination pagination-sm">
													'.$this->DevTools->API->Pagination( $NumData, $Get['page'], $PHP_SELF . "?mod=billing&c=invoice&p=user/{$Get['user']}/page/{p}", " <li><a href=\"{page_num_link}\">{page_num}</a></li>", "<li class=\"active\"><span>{page_num}</span></li>", $PerPage ).'
												</ul>
											</div>
											<select name="act" class="uniform">
												<option value="ok">'.$this->DevTools->lang['invoice_edit_1'].'</option>
												<option value="no">'.$this->DevTools->lang['invoice_edit_2'].'</option>
												<option value="ok_pay">'.$this->DevTools->lang['invoice_edit_3'].'</option>
												<option value="remove">'.$this->DevTools->lang['remove'].'</option>
											</select>

											'.$this->DevTools->MakeButton("act_do", $this->DevTools->lang['act'], "gold").'

						', 'box-footer', 'right' );

		/* Null */
		else
			$Content .= $this->DevTools->ThemePadded( $this->DevTools->lang['history_no'], '' );

		$Content .= $this->DevTools->ThemeHeadClose();
		$Content .= $this->DevTools->ThemeEchoFoother();

		return $Content;
	}

}
?>
