<?php	if( !defined( 'DATALIFEENGINE' ) ) die( "Hacking attempt!" );
/*
=====================================================
 Billing
-----------------------------------------------------
 evgeny.tc@gmail.com
-----------------------------------------------------
 This code is copyrighted
=====================================================
*/

Class PAY_API
{
	var $config = false;
	var $db = false;
	var $member_id = false;
	var $_TIME = false;

	/*
		Add money
	*/
	function PlusMoney( $user, $money, $desc, $currency = '', $plugin = 'api', $plugin_id = 0 )
	{
		$user = $this->db->safesql( $user );
		$money = $this->Convert( $money );
		$currency = $currency ? $currency : $this->Declension( $money );
	
		if( $this->member_id['name'] == $user )
		{
			$balance = $this->member_id[$this->config['fname']]+$money;
		}
		else
		{
			$SearchUser = $this->db->super_query( "SELECT " . $this->config['fname'] . " FROM " . USERPREFIX . "_users WHERE name='$user' LIMIT 1" );
			$balance = $SearchUser[$this->config['fname']] + $money;
		}

		$this->db->query( "UPDATE " . USERPREFIX . "_users SET {$this->config['fname']}={$this->config['fname']}+'$money' where name='$user' LIMIT 1");
			
		$this->SetHistory( $user, $money, 0, $balance, $desc, $currency, $plugin, $plugin_id );

		return true;
	}

	/*
		Minus money
	*/
	function MinusMoney( $user, $money, $desc, $currency = '', $plugin = 'api', $plugin_id = 0 )
	{
		$user = $this->db->safesql( $user );
		$money = $this->Convert( $money );
		$currency = $currency ? $currency : $this->Declension( $money );
	
		if( $this->member_id['name'] == $user )
		{
			$balance = $this->member_id[$this->config['fname']]-$money;
		}
		else
		{
			$SearchUser = $this->db->super_query( "SELECT " . $this->config['fname'] . " FROM " . USERPREFIX . "_users WHERE name='$user' LIMIT 1" );
			$balance = $SearchUser[$this->config['fname']] - $money;
		}

		if( $balance < 0 ) return false;
			
		$this->db->query( "UPDATE " . USERPREFIX . "_users SET {$this->config['fname']}={$this->config['fname']}-'$money' where name='$user' LIMIT 1");
			
		$this->SetHistory( $user, 0, $money, $balance, $desc, $currency, $plugin, $plugin_id );

		return true;
	}
	
	/*
		Send pm to user
	*/
	function SendAlertToUser( $theme, $data, $user_id = 0, $user_email = '', $from = '' )
	{
		global $config;
		
		$user_id = intval( $user_id );
		$user_email = preg_replace("/[^a-zA-Z0-9@.-\s]/", "", $user_email );
		$theme = preg_replace("/[^a-zA-Z0-9_-\s]/", "", $theme );
		
		$from = $from ? $this->db->safesql( $from ) : $this->config['admin'];

		$Text = @file_get_contents( ROOT_DIR . "/templates/". $config['skin'] ."/billing/mail/". $theme .".tpl");	
	
		if( !$Text ) return false;
		
		preg_match('~\[title\](.*?)\[/title\]~is', $Text, $Title);
		
		$Text = preg_replace("'\\[title\\].*?\\[/title\\]'si", '', $Text);
		
		foreach( $data as $key=>$value )
		{
			$Text = str_replace( $key, $this->db->safesql( $value ), $Text);
		}

		$now = time();
			
		if( $user_id )
		{
			$sqlSet = $this->db->query( "insert into " . PREFIX . "_pm 
								(subj, text, user, user_from, date, pm_read, folder) VALUES 
								('$Title[1]', '$Text', '$user_id', '$from', '$now', '0', 'inbox')" );
			
			$this->db->query( "update " . USERPREFIX . "_users set pm_unread = pm_unread + 1, pm_all = pm_all+1  where user_id = '$user_id'" );
		}
		
		if( $user_email )
		{
			include_once ENGINE_DIR . '/classes/mail.class.php';
			
			$mail = new dle_mail( $config, true );
			
			$mail->send( $user_email, $Title[1], $Text );
			
			unset( $mail );
		}
		
		return true;
	}

	/*
		Pagination
	*/
	function Pagination( $all_count, $this_page, $link, $tpl_link, $tpl_this_num, $per_page )
	{
		$all_count = intval( $all_count ) > 0 ? intval( $all_count ): 1;
		$this_page = intval( $this_page ) > 0 ? intval( $this_page ): 1;
		$per_page = intval( $per_page ) > 0 ? intval( $per_page ): $this->config['paging'];

		$enpages_count = @ceil( $all_count / $per_page );
		$enpages_start_from = 0;
		$enpages = "";

		if( $enpages_count==1 ) return $this->PaginationForm( 1, $tpl_link, "#" );
		
		$min = false;
		
		// left
		if( $this_page > 1 )
			$enpages = $this->PaginationForm( ($this_page-1), $tpl_link, $link, "&laquo;" );
		
		// center
		for($j = 1; $j <= $enpages_count; $j ++)
		{
			// min limit
			if( $j < ( $this_page - 4 ) )
			{
				if( !$min )
				{
					$j++;
					$min = true;
			
					$enpages .= $this->PaginationForm( 1, $tpl_link, $link, "1.." );
				} 
				continue;
			}
		
			// max limit
			if( $j > ( $this_page + 5 ) )
			{
				$enpages .= $this->PaginationForm( $enpages_count, $tpl_link, $link, "..{$enpages_count}" );
				
				break;
			}
			
			if( $this_page != $j )
			{
				$enpages .= $this->PaginationForm( $j, $tpl_link, $link );
			}
			else
			{
				$enpages .= $this->PaginationForm( $j, $tpl_this_num, $link );
			}
			
			$enpages_start_from += $per_page;
		}

		// right
		if( $this_page < $enpages_count )
			$enpages .= $this->PaginationForm( ($this_page+1), $tpl_link, $link, "&raquo;" );
		
		return $enpages;
	}

	private function PaginationForm( $page, $form_link, $link, $title = '' )
	{
		$link = str_replace( "{p}", $page, $link);
		
		$answer = str_replace( "{page_num}", ( $title ? $title : $page ), $form_link);
		$answer = str_replace( "{page_num_link}", $link, $answer);
		
		return $answer;
	}
	
	/*
		Сonversion
	*/
	function Convert( $money, $format = '' )
	{
		if( !$format ) $format = $this->config['format'];
		if( !$money ) $money = $format;
	
		$get = explode(".", str_replace(",", ".", $format) );

		if( !iconv_strlen($get[1]) ) return intval( $money );
		
		return number_format( str_replace(",", ".", $money) , iconv_strlen($get[1]), '.', '');
	}

	/*
		Declension
	*/
	function Declension( $number, $titles = '' )
	{
		$number = intval( $number );
	
		if( !$titles ) $titles = $this->config['currency'];
	
		$titles = explode(",", $titles );

		if( count( $titles ) != 3 ) return $titles[0];

		$cases = array (2, 0, 1, 1, 1, 2);

		return $titles[ ($number%100 > 4 && $number %100 < 20) ? 2 : $cases[min($number%10, 5)] ];
	}
	
	/*
		Include hooks metods
	*/
	private function Hooks( $user, $plus, $minus, $balance, $desc, $currency, $plugin = '', $plugin_id = '' )
	{
		$List = opendir( MODULE_PATH . "/plugins/" );
	
		while ( $name = readdir($List) )
		{
			if ( in_array($name, array(".", "..", "/", "index.php", ".htaccess")) ) continue;
			
			if( file_exists( MODULE_PATH . "/plugins/" . $name . "/payHook.php" ) )
			{
				include( MODULE_PATH . "/plugins/" . $name . "/payHook.php" );
				
				$Hook = new Hook();
				
				$Hook->api = $this;
				$Hook->get( $user, $plus, $minus, $balance, $desc, $currency, $plugin = '', $plugin_id = '' );
				
				unset($Hook);
			}
		}
		
		return;
	}
	
	private function SetHistory( $user, $plus, $minus, $balance, $desc, $currency, $plugin = '', $plugin_id = '' )
	{
		if( $plus <= 0 and $minus <= 0 ) return false;
	
		$desc = $this->db->safesql( $desc );
		$currency = $this->db->safesql( $currency );
		$balance = $this->Convert( $balance );
	
		$this->Hooks( $user, $plus, $minus, $balance, $desc, $currency, $plugin, $plugin_id );
	
		$dataMail = array
						(
							'{date}'=>langdate( "j F Y  G:i", $this->_TIME ),
							'{login}'=>$user,
							'{summa}'=>( $plus ? "+$plus" : "-$minus" ) . " $currency",
							'{comment}'=>$desc,
							'{balance}'=>$balance . " " . $this->Declension( $balance ),
						);
	
		if( $this->config['mail_balance_pm'] )
		{
			$arrUser = $this->db->super_query( "SELECT user_id, email FROM " . USERPREFIX . "_users WHERE name='" . $user . "'" );
			
			if( $arrUser['user_id'] ) $this->SendAlertToUser( "balance", $dataMail, $arrUser['user_id'] );
		}	
		
		if( $this->config['mail_balance_email'] )
		{
			if( !$arrUser['email'] ) $arrUser = $this->db->super_query( "SELECT email FROM " . USERPREFIX . "_users WHERE name='" . $user . "'" );
			
			if( $arrUser['email'] ) $this->SendAlertToUser( "balance", $dataMail, 0, $arrUser['email'] );
		}			
	
		$this->db->query( "INSERT INTO " . PREFIX . "_billing_history 
							(history_plugin, history_plugin_id, history_user_name, history_plus, history_minus, history_balance, history_currency, history_text, history_date) values 
							('$plugin', '$plugin_id', '$user', '$plus', '$minus', '$balance', '$currency', '$desc', '".$this->_TIME."')" );

		return true;
	}
}
?>