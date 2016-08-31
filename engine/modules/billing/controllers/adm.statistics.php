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
	var $StartTime = 0;
	var $EndTime = 0;
	var $Sector = "D";
	
	function __construct()
	{
		$this->StartTime = $_POST['editDateStart'] ? strtotime( $_POST['editDateStart'] ) : mktime(0,0,0 );
		$this->EndTime = $_POST['editDateEnd'] ? strtotime( $_POST['editDateEnd'] ) : time() + (24*60*60);

		if( ( $this->EndTime - $this->StartTime )>32140800 )
			$this->Sector = "Y"; 
		else if( ( $this->EndTime - $this->StartTime )>2678400 )
			$this->Sector = "M";
		else
			$this->Sector = "D";
	}
	
	/* Header menu */
	private function menu() 
	{
		return <<<HTML
<div class="box">
  <div class="box-content">
	<div class="row box-section">
		<ul class="settingsb">
		 <li style="min-width:90px;"><a href="{$PHP_SELF}?mod=billing&c=statistics" class="tip" title="" data-original-title="{$this->DevTools->lang['statistics_0']}"><i class="icon-money"></i><br />{$this->DevTools->lang['statistics_0']}</a></li>
		 <li style="min-width:90px;"><a href="{$PHP_SELF}?mod=billing&c=statistics&m=billings" class="tip" title="" data-original-title="{$this->DevTools->lang['statistics_2']}"><i class="icon-bar-chart"></i><br />{$this->DevTools->lang['statistics_2_title']}</a></li>
		 <li style="min-width:90px;"><a href="{$PHP_SELF}?mod=billing&c=statistics&m=plugins" class="tip" title="" data-original-title="{$this->DevTools->lang['statistics_3']}"><i class="icon-cogs"></i><br />{$this->DevTools->lang['statistics_3_title']}</a></li>
		 <li style="min-width:90px;"><a href="{$PHP_SELF}?mod=billing&c=statistics&m=users&p=user/{$this->DevTools->member_id['name']}" class="tip" title="" data-original-title="{$this->DevTools->lang['statistics_4']}"><i class="icon-group"></i><br />{$this->DevTools->lang['statistics_4_title']}</a></li>
		 <li style="min-width:90px;"><a href="{$PHP_SELF}?mod=billing&c=statistics&m=clean" class="tip" title="" data-original-title="{$this->DevTools->lang['statistics_5']}"><i class="icon-trash"></i><br />{$this->DevTools->lang['statistics_5']}</a></li>
		 <li style="min-width:90px; margin-left: 50px"><a href="{$PHP_SELF}?mod=billing" class="tip" title="" data-original-title="{$this->DevTools->lang['statistics_6']}"><i class="icon-reply"></i><br />{$this->DevTools->lang['statistics_6_title']}</a></li>
		</ul>
     </div>
   </div>
</div>
HTML;
	}
	
	private function EditDate()
	{
		return "
			<div style='padding: 10px; text-align: center; border-bottom: 1px solid #ccc'>
				".$this->DevTools->lang['statistics_interval']." 
				".$this->DevTools->MakeCalendar("editDateStart", date( "Y-m-j", $this->StartTime ) )."
					-
				".$this->DevTools->MakeCalendar("editDateEnd", date( "Y-m-j", $this->EndTime ) )."
				".$this->DevTools->MakeButton("sort", $this->DevTools->lang['statistics_show'], "green")."	
			</div>";
	}
	
	function main()
	{
		# View page
		$this->DevTools->ThemeEchoHeader();
		
		$Content .= $this->menu();	

		# SQL
		$allBalance = $this->DevTools->Model->db->super_query( "SELECT count(name) as `count`, SUM({$this->DevTools->config['fname']}) as `summa`
															FROM " . USERPREFIX . "_users 
															WHERE {$this->DevTools->config['fname']}  != 0" );
															
		$todayBalance = $this->DevTools->Model->db->super_query( "SELECT SUM(invoice_get) as `summa`
																			FROM " . USERPREFIX . "_billing_invoice 
																			WHERE invoice_date_pay>='".mktime(0,0,0)."'" );
		
		$allRefund = $this->DevTools->Model->db->super_query( "SELECT SUM(refund_summa) as `summa`, SUM(refund_commission) as `commission` 
																FROM " . USERPREFIX . "_billing_refund 
																WHERE refund_date_return  != 0" );
		
		$waitRefund = $this->DevTools->Model->db->super_query( "SELECT SUM(refund_summa) as `summa`
																	FROM " . USERPREFIX . "_billing_refund 
																	WHERE refund_date_return  = 0" );

		$allInvoice = $this->DevTools->Model->db->super_query( "SELECT SUM(invoice_get) as `summa` 
																FROM " . USERPREFIX . "_billing_invoice 
																WHERE invoice_date_pay  != 0" );
		
		$WaitInvoice = $this->DevTools->Model->db->super_query( "SELECT SUM(invoice_get) as `summa` 
																	FROM " . USERPREFIX . "_billing_invoice 
																	WHERE invoice_date_pay  = 0" );
																	
		$allTransfer = $this->DevTools->Model->db->super_query( "SELECT SUM(history_plus) as `plus`, SUM(history_minus) as `minus` 
																	FROM " . USERPREFIX . "_billing_history 
																	WHERE history_plugin = 'transfer'" );
																		
																	
		$todayBalanceOn = $todayBalance['summa'] ? "money_plus" : "";
		$commissionRefundOn = $allRefund['commission'] ? "money_plus" : "";
		$waitInvoiceOn = $WaitInvoice['summa'] ? "money_plus" : "";
		$allTransferOn = $allTransfer['minus'] - $allTransfer['plus'] ? "money_plus" : "";
		
		/* Main Info */
		$Content .= $this->DevTools->ThemeHeadStart( $this->DevTools->lang['statistics_0_title'] );
		
		$Content .= <<<HTML
			<table class="statistics_table">
				<tr>
					<td valign="top">
						Всего средств в системе
						<h3>{$this->DevTools->API->Convert( $allBalance['summa'] )} {$this->DevTools->API->Declension( $allBalance['summa'] )}</h3>
						<span class="{$todayBalanceOn}">{$this->DevTools->API->Convert( $todayBalance['summa'] )} {$this->DevTools->API->Declension( $todayBalance['summa'] )}</span>
						<br /><span class="statistics_table_desc">за сегодня</span>
					</td>
					<td valign="top">
						Выведено из системы
						<h3>{$this->DevTools->API->Convert( $allRefund['summa'] )} {$this->DevTools->API->Declension( $allRefund['summa'] )}</h3>
						<span class="{$commissionRefundOn}">{$this->DevTools->API->Convert( $allRefund['commission'] )} {$this->DevTools->API->Declension( $allRefund['commission'] )}</span>
						<br /><span class="statistics_table_desc">комиссия составила</span>	
						<p>
							<br /><span>{$this->DevTools->API->Convert( $waitRefund['summa'] )} {$this->DevTools->API->Declension( $waitRefund['summa'] )}</span>
							<br /><span class="statistics_table_desc">заявлено к выводу</span>
						</p>
					</td>
					<td valign="top">
						Пополнено через платёжные системы
						<h3>{$this->DevTools->API->Convert( $allInvoice['summa'] )} {$this->DevTools->API->Declension( $allInvoice['summa'] )}</h3>
						<span class="{$waitInvoiceOn}">{$this->DevTools->API->Convert( $WaitInvoice['summa'] )} {$this->DevTools->API->Declension( $WaitInvoice['summa'] )}</span>
						<br /><span class="statistics_table_desc">ожидается к пополнению</span>
					</td>
					<td valign="top">
						Переведено между пользователями 
						<h3>{$this->DevTools->API->Convert( $allTransfer['minus'] )} {$this->DevTools->API->Declension( $allTransfer['minus'] )}</h3>
						<span class="{$allTransferOn}">{$this->DevTools->API->Convert( $allTransfer['minus'] - $allTransfer['plus'] )} {$this->DevTools->API->Declension( $allTransfer['minus'] - $allTransfer['plus'] )}</span>
						<br /><span class="statistics_table_desc">комиссия составила</span>
					</td>
				</tr>
				<tr>
					<td><a href="{$PHP_SELF}?mod=billing&c=users">Поиск по пользователям</a></td>
					<td><a href="{$PHP_SELF}?mod=billing&c=Refund">Все запросы вывода средств</a></td>
					<td><a href="{$PHP_SELF}?mod=billing&c=invoice">Обработка квитанций</a></td>
					<td><a href="{$PHP_SELF}?mod=billing&c=history">Поиск переводов</a></td>
				</tr>
			</table>
HTML;
		
		$Content .= $this->DevTools->ThemeHeadClose();
		$Content .= $this->DevTools->ThemeEchoFoother();
		
		return $Content;
	}
	
	function users( $GET )
	{
		/* Search */
		$Result = array();
		
		if( isset( $_POST['search_btn'] ) )
		{
			header( 'Location: /' . $this->DevTools->config_dle['admin_path'] . '?mod=billing&c=statistics&m=users&p=user/' . $this->DevTools->Model->parsVar( $_POST['search_user'] ) );
		}
		
		if( $GET['user'] )
		{	
			$search_user = $this->DevTools->Model->parsVar( $GET['user'] );
			
			$this->DevTools->Model->DbWhere( array( "name LIKE '{s}' or email LIKE '{s}' " => $search_user  ) );
			
			$Result = $this->DevTools->Model->DbSearchUsers( 1 );
			$Result = $Result[0];
		}
		
		/* Error search */
		if( !$Result['user_id'] )
		{
			$this->DevTools->ThemeMsg( $this->DevTools->lang['error'], $this->DevTools->lang['statistics_users_error'], "{$PHP_SELF}?mod=billing&c=statistics&m=users&p=user/{$this->DevTools->member_id['name']}" );
		}
		
		# View page
		$this->DevTools->ThemeEchoHeader();
		
		$Content .= $this->menu();	
		
		$Content .= "<form method=\"post\">" . 
						$this->DevTools->MakeMsgInfo( "<input name=\"search_user\" class=\"edit bk\" type=\"text\" style=\"width: 92%\" value=\"" . $GET['user'] ."\" required>" . 
						$this->DevTools->MakeButton("search_btn", $this->DevTools->lang['users_btn'], "green") , "icon-user", "green") . 
					"</form>";
		
			# Stats
			$StatsView = $this->DevTools->ThemeHeadStart( $this->DevTools->lang['statistics_4'] . "&nbsp;" . $Result['name'] );
			$StatsView .= $this->EditDate();
			
				# Billings SQL
				$sql = "SELECT count(*) as `rows`, invoice_paysys, SUM(invoice_get) as `get` 
							FROM " . USERPREFIX . "_billing_invoice 
							WHERE invoice_date_pay  != 0 and invoice_date_creat>'".$this->StartTime."' and invoice_date_creat<'".$this->EndTime."' and invoice_user_name='".$Result['name']."'
							GROUP BY invoice_paysys";
				
				$sqlNull = "SELECT count(*) as `rows`, invoice_paysys, SUM(invoice_get) as `get` 
							FROM " . USERPREFIX . "_billing_invoice 
							WHERE invoice_date_pay  = 0 and invoice_date_creat>'".$this->StartTime."' and invoice_date_creat<'".$this->EndTime."' and invoice_user_name='".$Result['name']."'
							GROUP BY invoice_paysys";
							
				$StatsView .= $this->from_invoice_to_stats( $sql, $sqlNull );
			
			$StatsView .= $this->DevTools->ThemeHeadClose();
		
		$Content .= <<<HTML
	<table width="100%" border="0">
		<tr>
			<td width="70%" valign="top">{$StatsView}</td>
			<td width="1%"></td>
			<td valign="top">
				<div id="general" class="box" style="padding: 10px">
					<table width="100%">
						<tr>
							<td width="100" valign="top">
								<img src="{$this->ThemeInfoUserFoto( $Result['foto'] )}" style="max-width: 100px; border-radius: 5px" title="{$Result['name']}" alt="{$Result['name']}">
							</td>
							<td width="10"></td>
							<td>
								<table width="100%" class="table table-bordered">
									<tr><td>{$this->DevTools->ThemeInfoUser( $Result['name'] )}</td></tr>
									<tr><td>{$this->ThemeInfoUserGroup( $Result )}</td></tr>
									<tr><td>{$this->DevTools->API->Convert( $Result[ $this->DevTools->config['fname'] ] )} {$this->DevTools->API->Declension( $Result[ $this->DevTools->config['fname'] ] )}</td></tr>
								</table>
							</td>
						</tr>
					</table>
					<p style="text-align: center">
						<a href="{$this->DevTools->config_dle['http_home_url']}index.php?do=pm&doaction=newpm&user={$Result['user_id']}" target="_blank"><i class="icon-comments" style="margin-left: 10px; margin-right: 5px; vertical-align: middle"></i> {$this->DevTools->lang['statistics_users_9']}</a>
						<a href="{$this->DevTools->config_dle['http_home_url']}index.php?do=feedback&user={$Result['user_id']}" target="_blank"><i class="icon-share-alt" style="margin-left: 10px; margin-right: 5px; vertical-align: middle"></i> {$this->DevTools->lang['statistics_users_10']}</a>
					</p>
				</div>
			</td>
		</tr>
	</table>
HTML;
		
			# Plugins SQL
			$GetMainStatistics = $this->DevTools->Model->db->super_query( "SELECT SUM(history_plus) as `plus`,  SUM(history_minus) as `minus`  
																			FROM " . USERPREFIX . "_billing_history 
																			where history_date>'".$this->StartTime."' 
																				and history_date<'".$this->EndTime."' and history_user_name='".$Result['name']."'" );
				
			$from_history_to_costs = "SELECT DAY(FROM_UNIXTIME(`history_date`)) as `D`, 
												MONTH(FROM_UNIXTIME(`history_date`)) as `M`, 
												YEAR(FROM_UNIXTIME(`history_date`)) as `Y`, 
												SUM(history_plus) as `plus`, 
												SUM(history_minus) as `minus`, history_plus 
											FROM " . USERPREFIX . "_billing_history 
											WHERE history_date>'".$this->StartTime."' and history_date<'".$this->EndTime."' and history_user_name='".$Result['name']."' 
											GROUP BY ".$this->Sector;
				
			$from_history_to_popular = "SELECT count(*) as `rows`, 
															`history_plugin`, 
															SUM(history_minus) as `pay` 
														FROM " . USERPREFIX . "_billing_history 
														WHERE history_minus  != 0 and history_date>'".$this->StartTime."' and history_user_name='".$Result['name']."' 
															and history_date<'".$this->EndTime."' GROUP BY history_plugin";
					
			$from_history_to_popular_plus = "SELECT count(*) as `rows`, 
															`history_plugin`, 
															SUM(history_plus) as `pay` 
														FROM " . USERPREFIX . "_billing_history 
														WHERE history_minus  = 0 and history_date>'".$this->StartTime."' and history_user_name='".$Result['name']."' 
															and history_date<'".$this->EndTime."' GROUP BY history_plugin";	
			
			$Content .= $this->DevTools->ThemeHeadStart( $this->DevTools->lang['statistics_3'] );
			
			if( !$GetMainStatistics['plus'] and !$GetMainStatistics['minus'] )
				$Content .= $this->DevTools->lang['statistics_null'];
			else
			{
				$Content .= $this->from_history_to_costs( $from_history_to_costs, $this->Sector );
				$Content .= "<table width='100%'><tr><td width='50%'>".$this->from_history_to_popular( $from_history_to_popular, $GetMainStatistics['minus']/100, 2, $this->DevTools->lang['statistics_d_title1'] )."</td><td>".$this->from_history_to_popular( $from_history_to_popular_plus, $GetMainStatistics['plus']/100, 3, $this->DevTools->lang['statistics_d_title2'] )."</td></tr></table>";
			}
			
			$Content .= $this->DevTools->ThemeHeadClose();
		
		$Content .= $this->DevTools->ThemeEchoFoother();
		
		return $Content;	
	}
	
	function billings()
	{
		# SQL
		$sql = "SELECT count(*) as `rows`, invoice_paysys, SUM(invoice_get) as `get` 
					FROM " . USERPREFIX . "_billing_invoice 
					WHERE invoice_date_pay  != 0 and invoice_date_creat>'".$this->StartTime."' and invoice_date_creat<'".$this->EndTime."' 
					GROUP BY invoice_paysys";
		
		$sqlNull = "SELECT count(*) as `rows`, invoice_paysys, SUM(invoice_get) as `get` 
					FROM " . USERPREFIX . "_billing_invoice 
					WHERE invoice_date_pay  = 0 and invoice_date_creat>'".$this->StartTime."' and invoice_date_creat<'".$this->EndTime."' 
					GROUP BY invoice_paysys";

		# View page
		$this->DevTools->ThemeEchoHeader();
		
		$Content .= $this->menu();									
		$Content .= $this->DevTools->ThemeHeadStart( $this->DevTools->lang['statistics_2'] );
		$Content .= $this->EditDate();

		$Content .= $this->from_invoice_to_stats( $sql, $sqlNull );
		
		$Content .= $this->DevTools->ThemeHeadClose();
		$Content .= $this->DevTools->ThemeEchoFoother();
		
		return $Content;	
	}
	
	private function from_invoice_to_stats( $sql, $sqlNull )
	{
		$arrBilings = array();
		$PaysysArray = $this->DevTools->GetPaysysArray();
		
		# JS vars
		$jsNames = "";
		$jsPay = "";
		$jsWait = "";

		$this->DevTools->Model->db->query( $sql );
		
		while ( $row = $this->DevTools->Model->db->get_row() )
		{
			$arrBilings[$row['invoice_paysys']] = array();
			$arrBilings[$row['invoice_paysys']]['ok_allids'] = intval( $row['rows'] );
			$arrBilings[$row['invoice_paysys']]['ok_get'] = $row['get'];
		}

		$this->DevTools->Model->db->query( $sqlNull );
		
		while ( $row = $this->DevTools->Model->db->get_row() )
		{
			$arrBilings[$row['invoice_paysys']]['wait_allids'] = intval($row['rows']);
			$arrBilings[$row['invoice_paysys']]['wait_get'] = $row['get'];	
		}

		foreach( $arrBilings as $BillName=>$BillInfo)
		{
			if(!$BillInfo['wait_allids']) $BillInfo['wait_allids'] = 0;
			if(!$BillInfo['ok_allids']) $BillInfo['ok_allids'] = 0;

			$jsNames .= "'{$PaysysArray[$BillName]['title']} <br>({$BillInfo['ok_allids']} {$this->DevTools->lang['statistics_billings_invoices_0']} ".($BillInfo['wait_allids']+$BillInfo['ok_allids'])." {$this->DevTools->lang['statistics_billings_invoices_1']})',";
			$jsPay .= "". $this->DevTools->API->Convert( $BillInfo['ok_get'] ) .", ";
			$jsWait .= "". $this->DevTools->API->Convert( $BillInfo['wait_get'] ) .", "; 
		}

		if( !$jsNames ) return $this->DevTools->lang['statistics_null'];
		
		return <<<HTML
<script>
$(function () {
    $('#container_4').highcharts({
        chart: {
            type: 'bar'
        },
        title: {
            text: '{$this->DevTools->lang['statistics_2_title']}'
        },
        subtitle: {
            text: '{$this->DevTools->lang['statistics_billings_invoices_summ']}'
        },
        xAxis: {
            categories: [{$jsNames}],
            title: {
                text: null
            }
        },
        yAxis: {
            min: 0,
            title: {
                text: '{$this->DevTools->lang['history_summa']} (N {$this->DevTools->API->Declension( 10 )})',
                align: 'high'
            },
            labels: {
                overflow: 'justify'
            }
        },
        plotOptions: {
            bar: {
                dataLabels: {
                    enabled: true
                }
            }
        },
        legend: {
            layout: 'vertical',
            align: 'right',
            verticalAlign: 'top',
            x: -40,
            y: 80,
            floating: true,
            borderWidth: 1,
            backgroundColor: ((Highcharts.theme && Highcharts.theme.legendBackgroundColor) || '#FFFFFF'),
            shadow: true
        },
        credits: {
            enabled: false
        },
        series: [{
            name: '{$this->DevTools->lang['statistics_clean_4_s2']}',
            data: [{$jsPay}]
        }, {
            name: '{$this->DevTools->lang['refund_wait']}',
            data: [{$jsWait}]
        }]
    });
});
</script>
		
<div id="container_4" style="min-width: 310px; width: 100%; height: 400px; margin: 0 auto"></div>		
HTML;
	}	
	
	function plugins() 
	{
		$GetPluginsArray = $this->DevTools->GetPluginsArray();
		
		# Main Statistics
		$GetMainStatistics = $this->DevTools->Model->db->super_query( "SELECT SUM(history_plus) as `plus`,  SUM(history_minus) as `minus`  
																		FROM " . USERPREFIX . "_billing_history 
																		where history_date>'".$this->StartTime."' 
																			and history_date<'".$this->EndTime."'" );
		# SQL
		$from_history_to_costs = "SELECT DAY(FROM_UNIXTIME(`history_date`)) as `D`, 
											MONTH(FROM_UNIXTIME(`history_date`)) as `M`, 
											YEAR(FROM_UNIXTIME(`history_date`)) as `Y`, 
											SUM(history_plus) as `plus`, 
											SUM(history_minus) as `minus`, history_plus 
										FROM " . USERPREFIX . "_billing_history 
										WHERE history_date>'".$this->StartTime."' and history_date<'".$this->EndTime."' 
										GROUP BY ".$this->Sector;
				
		$from_history_to_popular = "SELECT count(*) as `rows`, 
														`history_plugin`, 
														SUM(history_minus) as `pay` 
													FROM " . USERPREFIX . "_billing_history 
													WHERE history_minus != 0 and history_date>'".$this->StartTime."' 
														and history_date<'".$this->EndTime."' GROUP BY history_plugin";
				
		$from_history_to_popular_plus = "SELECT count(*) as `rows`, 
														`history_plugin`, 
														SUM(history_plus) as `pay` 
													FROM " . USERPREFIX . "_billing_history 
													WHERE history_minus = 0 and history_date>'".$this->StartTime."' 
														and history_date<'".$this->EndTime."' GROUP BY history_plugin";
				
		# View page
		$this->DevTools->ThemeEchoHeader();
		
		$Content .= $this->menu();
		$Content .= $this->DevTools->MakeMsgInfo( "	{$this->DevTools->lang['statistics_info1']}
													<ul style='margin: 10px; padding-left: 20px'>
														<li>{$this->DevTools->lang['statistics_info2']} {$this->DevTools->API->Convert( $GetMainStatistics['plus'] )} RUR</li>
														<li>{$this->DevTools->lang['statistics_info3']} {$this->DevTools->API->Convert( $GetMainStatistics['minus'] )} RUR</li>
													</ul>", "icon-info-sign", "green");
													
		$Content .= $this->DevTools->ThemeHeadStart( $this->DevTools->lang['statistics_3'] );
		$Content .= $this->EditDate();

		if( !$GetMainStatistics['plus'] and !$GetMainStatistics['minus'] )
			$Content .= $this->DevTools->lang['statistics_null'];
		else
		{
			$Content .= $this->from_history_to_costs( $from_history_to_costs, $this->Sector );
			$Content .= "<table width='100%'><tr><td width='50%'>".$this->from_history_to_popular( $from_history_to_popular, $GetMainStatistics['minus']/100, 2, $this->DevTools->lang['statistics_d_title1'] )."</td><td>".$this->from_history_to_popular( $from_history_to_popular_plus, $GetMainStatistics['plus']/100, 3, $this->DevTools->lang['statistics_d_title2'] )."</td></tr></table>";
		}
		
		$Content .= $this->DevTools->ThemeHeadClose();
		$Content .= $this->DevTools->ThemeEchoFoother();
		
		return $Content;
	}
	
	function clean() 
	{
		$GetPluginsArray = $this->DevTools->GetPluginsArray();
		$GetPluginsArray['pay']['title'] = $this->DevTools->lang['statistics_pay'];
		$GetPluginsArray['users']['title'] = $this->DevTools->lang['statistics_admin'];
		
		/* Act */
		if( isset( $_POST['act'] ) )
		{
			if( $_POST['user_hash'] == "" or $_POST['user_hash'] != $this->DevTools->hash )
			{       
				return "Hacking attempt! User not found {$_POST['user_hash']}";   
			}
			
			// - plugins
			foreach( $_POST['clean_plugins'] as $PlaginName ) 
				$this->DevTools->Model->db->super_query( "DELETE FROM " . USERPREFIX . "_billing_history 
															WHERE history_plugin='".$this->DevTools->Model->db->safesql($PlaginName)."'" );
			
			// - invoice
			if( $_POST['clear_invoice'] == "all" )
				$this->DevTools->Model->db->super_query( "DELETE FROM " . USERPREFIX . "_billing_invoice" );
			elseif( $_POST['clear_invoice'] == "ok" )
				$this->DevTools->Model->db->super_query( "DELETE FROM " . USERPREFIX . "_billing_invoice WHERE invoice_date_pay  != 0" );
			elseif( $_POST['clear_invoice'] == "no" )
				$this->DevTools->Model->db->super_query( "DELETE FROM " . USERPREFIX . "_billing_invoice WHERE invoice_date_pay  = 0" );
						
			// - refund
			if( $_POST['clear_refund'] == "all" )
				$this->DevTools->Model->db->super_query( "DELETE FROM " . USERPREFIX . "_billing_refund" );
			elseif( $_POST['clear_refund'] == "ok" )
				$this->DevTools->Model->db->super_query( "DELETE FROM " . USERPREFIX . "_billing_refund WHERE refund_date_return  != 0" );
			elseif( $_POST['clear_refund'] == "no" )
				$this->DevTools->Model->db->super_query( "DELETE FROM " . USERPREFIX . "_billing_refund WHERE refund_date_return  = 0" );
			
			// - balance
			if( $_POST['clear_balance'] )
				$this->DevTools->Model->db->query( "UPDATE " . USERPREFIX . "_users SET {$this->DevTools->Model->config['fname']}  = 0");
			
			$this->DevTools->ThemeMsg( $this->DevTools->lang['ok'], $this->DevTools->lang['statistics_clean_1_ok'] );
		}
		
		/* Page */
		$this->DevTools->ThemeEchoHeader();
		
		$Content .= $this->menu();
		$Content .= $this->DevTools->MakeMsgInfo( $this->DevTools->lang['statistics_clean_info'], "icon-warning-sign", "red");
		$Content .= $this->DevTools->ThemeHeadStart( $this->DevTools->lang['statistics_5'] );

		/* Plugins */
		$PluginsSelect = "<div class=\"checkbox\">
									<label>
									  <input type=\"checkbox\" value=\"\" onclick=\"checkAll(this)\" /> {$this->DevTools->lang['statistics_clean_2']}
									</label>
								</div>";
		
		$this->DevTools->Model->db->query( "SELECT history_plugin FROM " . USERPREFIX . "_billing_history GROUP BY history_plugin" );
		
		while ( $row = $this->DevTools->Model->db->get_row() )
		{
			$PluginsSelect .= "<div class=\"checkbox\">
									<label>
									  <input type=\"checkbox\" name=\"clean_plugins[]\" value=\"{$row['history_plugin']}\"> " . ( $GetPluginsArray[$row['history_plugin']]['title'] ? $GetPluginsArray[$row['history_plugin']]['title'] : $row['history_plugin'] ) . "
									</label>
								</div>";
		}
		
		$this->DevTools->ThemeAddStr( $this->DevTools->lang['statistics_clean_3'], $this->DevTools->lang['statistics_clean_3d'], $PluginsSelect );
		$this->DevTools->ThemeAddStr( $this->DevTools->lang['statistics_clean_4'], $this->DevTools->lang['statistics_clean_4d'], $this->DevTools->MakeDropDown( array(''=>"",'all'=>$this->DevTools->lang['statistics_clean_4_s1'], 'ok'=>$this->DevTools->lang['statistics_clean_4_s2'], 'no'=>$this->DevTools->lang['statistics_clean_4_s3'] ), "clear_invoice" ) );
		$this->DevTools->ThemeAddStr( $this->DevTools->lang['statistics_clean_5'], $this->DevTools->lang['statistics_clean_5d'], $this->DevTools->MakeDropDown( array(''=>"",'all'=>$this->DevTools->lang['statistics_clean_4_s1'], 'ok'=>$this->DevTools->lang['statistics_clean_5_s1'], 'no'=>$this->DevTools->lang['statistics_clean_5_s2'] ), "clear_refund" ) );
		$this->DevTools->ThemeAddStr( $this->DevTools->lang['statistics_clean_6'], $this->DevTools->lang['statistics_clean_6d'], $this->DevTools->MakeDropDown( array(''=>"",'1'=>$this->DevTools->lang['statistics_clean_6d_yep'] ), "clear_balance" ) );
																											
		$Content .= $this->DevTools->ThemeParserStr();
		
		$Content .= $this->DevTools->ThemePadded( $this->DevTools->MakeButton("act", $this->DevTools->lang['act'], "gold", true) );			 

		$Content .= $this->DevTools->ThemeHeadClose();
		$Content .= $this->DevTools->ThemeEchoFoother();
		
		return $Content;
	}
	
	/*
		Diagrams
	*/
	private function from_history_to_costs( $sql, $sect = 'D' )
	{
		# JS vars
		$categories = '';
		$plus = '';
		$minus = '';
		
		$this->DevTools->Model->db->query( $sql );

		while ( $row = $this->DevTools->Model->db->get_row() )
		{
			if( $sect == 'D' )
				$categories .= "'" . $row['D'] . " " . $this->DevTools->lang['months'][$row['M']] . "', ";
			else if( $sect == 'M' )
				$categories .= "'" . $this->DevTools->lang['months_full'][$row['M']] . "', ";
			else
				$categories .= "'" . $row['Y'] . "', ";
			
			$plus .= "{$row['plus']}, ";
			$minus .= "{$row['minus']}, ";
		}

		return <<<HTML
<script>
$(function () {
    $('#container').highcharts({
        chart: {
            type: 'column'
        },
        title: {
            text: '{$this->DevTools->lang['statistics_diagram_1']}'
        },
		subtitle: {
            text: '{$this->DevTools->lang['sectors'][$sect]}'
        },
        xAxis: {
            categories: [{$categories}]
        },
		yAxis: {
            min: 0,
            title: {
                text: '{$this->DevTools->lang['history_summa']}'
            }
        },
        credits: {
            enabled: false
        },
        series: [{
            name: '{$this->DevTools->lang['statistics_plus']}',
            data: [{$plus}]
        }, {
            name: '{$this->DevTools->lang['statistics_minus']}',
            data: [{$minus}]
        }]
    });
});
</script>
		
<div id="container" style="min-width: 310px; height: 400px; margin: 0 auto"></div>
HTML;
		
	}
	
	private function from_history_to_popular( $sql, $onePercent, $id, $title )
	{	
		$jsDB = "";
		
		$GetPluginsArray = $this->DevTools->GetPluginsArray();
		$GetPluginsArray['pay']['title'] = $this->DevTools->lang['statistics_pay'];
		$GetPluginsArray['users']['title'] = $this->DevTools->lang['statistics_admin'];

		$this->DevTools->Model->db->query( $sql );
		
		while ( $row = $this->DevTools->Model->db->get_row() )
		{ 
			$name = $GetPluginsArray[$row['history_plugin']]['title'] ? $GetPluginsArray[$row['history_plugin']]['title'] : $row['history_plugin'];
			
			$jsDB .= '{name: "'.$name.' <br> '.$row['pay'].' '.$this->DevTools->API->Declension( $row['pay'] ).' <br>('.$row['rows'] . $this->DevTools->lang['statistics_d_per'] . ')", y: '.number_format(($row['pay']/$onePercent), 2, '.', '').'},';
		}

		return <<<HTML
		<script>
$(function () {

    $('#container_{$id}').highcharts({
        chart: {
            plotBackgroundColor: null,
            plotBorderWidth: null,
            plotShadow: false,
            type: 'pie'
        },
        title: {
            text: '{$title}'
        },
        tooltip: {
            pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
        },
        plotOptions: {
            pie: {
                allowPointSelect: true,
                cursor: 'pointer',
                dataLabels: {
                    enabled: true,
                    format: '<b>{point.name}</b>: {point.percentage:.1f} %',
                    style: {
                        color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black'
                    },
                    connectorColor: 'silver'
                }
            }
        },
        series: [{
            name: "{$this->DevTools->lang['statistics_d_end']}",
            data: [
				{$jsDB}
            ]
        }]
    });
});
		</script>
		
		<div id="container_{$id}" style="min-width: 310px; width:100%; height: 400px; border-top: 1px solid #ccc; border-right: 1px solid #ccc"></div>
HTML;
	}
	
	private function ThemeInfoUserFoto( $foto ) 
	{
	    if ( count(explode("@", $foto)) == 2 )
            $foto = 'http://www.gravatar.com/avatar/' . md5(trim($foto)) . '?s=150';
		
        elseif( $foto and ( file_exists( ROOT_DIR . "/uploads/fotos/" . $foto )) ) 
			$foto = $config['http_home_url'] . "uploads/fotos/" . $foto;
			
        else
			$foto = "{$this->config_dle['http_home_url']}templates/{$this->config_dle['skin']}/dleimages/noavatar.png";
		
		return $foto;
	}
	
	function ThemeInfoUserGroup( $userInfo ) 
	{
		if( $userInfo['banned'] == 'yes' )
			$answer = $this->lang['statistics_users_2'];
		
		if( $this->user_group[$userInfo['user_group']]['time_limit'] )
			if( $userInfo['time_limit'] )
				$answer .= "&nbsp;<a style=\"cursor: info\" data-toggle=\"dropdown\" data-original-title=\"" . $this->lang['statistics_users_21'] . langdate( "j F Y H:i", $userInfo['time_limit'] ) . "\" class=\"status-info tip\"><i class=\"icon-info-sign\"></i></a>";
			else
				$answer .= $this->lang['statistics_users_22'];
		
		return $this->user_group[$userInfo['user_group']]['group_name'] . $answer;
	}
}
?>