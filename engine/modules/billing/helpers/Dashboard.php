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

/*
	Tools for the development: adminpanel
*/

Class Dashboard
{
	private static $instance;
    private function __construct(){} 
    private function __clone()    {} 
    private function __wakeup()   {} 
    public static function getInstance()
	{ 
        if ( empty(self::$instance) )
		{
            self::$instance = new self();
        }
        return self::$instance;
    }
	
	// Loader
	var $Loader = false;
	// Config array module
	var $config = array();
	// Config - array DLE
	var $config_dle = array();
	// Info User array
	var $member_id = array();
	// Lang admin.php
	var $lang = array();
	// Var dle_login_hash
	var $hash = array();
	
	// Int time dle
	var $_TIME = false;
	// Class pay_api
	var $API = false;
	// DB methods
	var $Model = false;
	// Float user balance
	var $BalanceUser = false;
	
	var $PluginsArray = array();
	var $PaysysArray = array();
	
	protected $section_num = 0;
	protected $section = array();

	protected $list_table_num = 0;
	protected $list_table = array();
	
	protected $str_table_num = 0;
	protected $str_table = array();
	
	/*
		Main()
	*/
	function Loader()
	{
		if( $this->Loader ) return;
		
		global $user_group, $billing_config, $config, $member_id, $billing_lang, $_TIME, $db, $dle_login_hash;

		$this->Loader = true;
		$this->Model = new dbActions();
		$this->Model->db = $db;
		$this->Model->BalanceField = $billing_config['fname'];
		$this->Model->_TIME = $_TIME;
		
		$this->API = new PAY_API;
		$this->API->db = $db;
		$this->API->_TIME = $_TIME;
		$this->API->member_id = $member_id;
		$this->API->config = $billing_config;
		
		$this->config = $billing_config;
		$this->config_dle = $config;
		$this->member_id = $member_id;
		$this->lang = $billing_lang;
		$this->_TIME = $_TIME;
		$this->hash = $dle_login_hash;
		
		$this->BalanceUser = $this->API->Convert( $this->member_id[$billing_config['fname']] );
	
		/* Pointer controller */
		$c = ( $_GET['c'] ) ? preg_replace("/[^a-zA-Z0-9\s]/", "", trim( $_GET['c'] ) ) : "main";
		$m = ( $_GET['m'] ) ? preg_replace("/[^a-zA-Z0-9\s]/", "", trim( $_GET['m'] ) ) : "main";

		/* Parser params */ 
		$arrParams = array();
		
		$getParams = explode('/', $_GET['p']);

		for( $i = 0; $i < count( $getParams ); $i++ )
		{
			$arrParams[$getParams[$i]] = preg_replace("/[^-_рРА-Яа-яa-zA-Z0-9\s]/", "", $getParams[$i+1]);
			$i++;
		}
		
		/* Load controller - Core */ 
		if( file_exists( MODULE_PATH."/controllers/adm." . mb_strtolower( $c ) . ".php" ) )
			require_once MODULE_PATH . '/controllers/adm.' . mb_strtolower( $c ) . '.php';

		/* Load controller - Plugins */ 
		elseif( file_exists( MODULE_PATH."/plugins/" . ucfirst( $c ) . "/adm.main.php" ) )
			require_once MODULE_PATH . '/plugins/' . ucfirst( $c ) . '/adm.main.php';
			
		else
			return $this->ThemeMsg( $this->lang['error'], $this->lang['main_error_controller'], $PHP_SELF . "?mod=billing" );
		
		/* Load controller function */  	
		$AdmPanel = new ADMIN;

		$AdmPanel->DevTools = $this;
		$AdmPanel->user_group = $user_group;

		if( in_array($m, get_class_methods($AdmPanel) ) )
			echo $AdmPanel->$m( $arrParams );
		else
			return $this->ThemeMsg( $this->lang['error'], $this->lang['main_error_metod'], $PHP_SELF . "?mod=billing" );
	}
	
	/* 
		Generating line ( size ) 
	*/
	function genCode( $length = 8 ) 
	{	
		$chars = 'abdefhiknrstyzABDEFGHKNQRSTYZ23456789';
		$numChars = strlen($chars);
		$string = '';
		  
		for ($i = 0; $i < $length; $i++) {
			$string .= substr($chars, rand(1, $numChars) - 1, 1);
		}
	  
		return $string;
	}
	
	/* 
		HTML elements 
	*/
	function MakeCheckBox($name, $selected, $value = 1, $class = true ) 
	{
		$selected = $selected ? "checked" : "";
		$class = $class ? "iButton-icons-tab" : "";

		return "<input class=\"$class\" type=\"checkbox\" name=\"$name\" value=\"$value\" {$selected}>";
	}

	function MakeButton($name, $title, $color, $hash = true) 
	{
		$hash = $hash ? "<input type=\"hidden\" name=\"user_hash\" value=\"" . $this->hash . "\" />" : "";
		
		return "<input class=\"btn btn-" . $color . "\" style=\"margin:7px;\" name=\"" . $name . "\" " . $id . " type=\"submit\" value=\"" . $title . "\" onClick=\"ShowLoading()\">" . $hash;
	}
	
	function MakeMsgInfo($text, $icon, $color) 
	{
		$hash = $hash ? "<input type=\"hidden\" name=\"user_hash\" value=\"" . $this->hash . "\" />" : "";
		$id = $id ? "id='$id'" : "";
		
		return "<div class=\"well relative\"><span class=\"triangle-button " . $color . "\"><i class=\"" . $icon . "\"></i></span>" . $text . "</div>";
	}
	
	function MakeCalendar($name, $value) 
	{
		$style = $style ? "style='$style'" : "";
	
		return "<input data-rel=\"calendardate\" type=\"text\" name=\"" . $name . "\" id=\"" . $name . "\" value=\"" . $value . "\" class=\"edit bk\" " . $style . ">";
	}
	
	function MakeICheck($name, $selected) 
	{
		$selected = $selected ? "checked" : "";
		
		return "<input class=\"icheck\" type=\"checkbox\" name=\"" . $name . "\" id=\"" . $name . "\" value=\"1\" " . $selected . ">";
	}

    function MakeDropDown($options, $name, $selected, $multiple = false) 
	{
		$selected = is_array( $selected ) ? $selected : array( $selected );
		
        $output = "<select class=\"uniform\" style=\"min-width:100px\" name=\"$name\" " . ( $multiple ? "multiple" : "" ) . ">\r\n";
		
        foreach ( $options as $value => $description )
		{
            $output .= "<option value=\"$value\"";
			
            if( in_array( $value, $selected) )
			{
                $output .= " selected ";
            }
            $output .= ">$description </option>\n";
        }
        $output .= "</select> ";

        return $output;
    }
	
	/* 
		Show msg
	*/
	function ThemeMsg( $title, $text, $link = '' ) 
	{
		$this->ThemeEchoHeader();
		
		$linkText = $link ? $this->lang['main_next'] : $this->lang['main_back'];
		
		echo <<<HTML
	<div class="box">
	  <div class="box-header">
		<div class="title">{$title}</div>
	  </div>
	  <div class="box-content">
		<div class="row box-section">
			<table width="100%">
				<tr>
					<td height="100" class="text-center settingstd">{$text}</td>
				</tr>
			</table>
		</div>
		<div class="row box-section"><div class="col-md-12 text-center"><a class="btn btn btn-default" href="{$link}">{$linkText}</a></div></div>
	  </div>
	</div>
HTML;
		
		echo $this->ThemeEchoFoother();
		die();
	}
	
	/* 
		Item catalog
	*/
	function ThemeCatalogItem( $Info, $Version = 0 )
	{
		if( $Version )
		{
			$status = ( $Version==$Info['version'] ) ? "<a href=\"#\" class=\"tip\" data-placement=\"left\" data-original-title=\"{$this->lang['catalog_verplug_ok']}\"><span class=\"status-success\"><i class=\"icon-ok-sign icon-2x\" style=\"margin-right: 10px; margin-top: 3px\"></i></span></a>" : "<a href=\"{$Info['link']}\" target=\"_blank\" class=\"tip\" data-placement=\"left\" data-original-title=\"{$this->lang['catalog_verplug_update']} {$Info['version']}\"><span class=\"status-warning\"><i class=\"icon-warning-sign icon-2x\" style=\"margin-right: 10px; margin-top: 3px\"></i></span></a>";
		}
		else $status = "<a href=\"{$Info['page']}\" target=\"_blank\" class=\"btn btn-".( $Info['price'] ? "blue": "green" )."\">".( $Info['price'] ? "{$Info['price']} RUR": $this->lang['catalog_free'] )."</a>";
		
			return "<div class=\"bt_catalog\">
										<b><a href=\"{$Info['link']}\" target=\"_blank\">{$Info['title']} v.".( $Version ? $Version : $Info['version'] )."</a><span style=\"margin-top: 10px; float: right\">{$status}</span></b> 
										".( $Info['doc'] ? "<a href=\"{$Info['doc']}\" target=\"_blank\" data-original-title=\"{$this->lang['catalog_doc']}\" class=\"status-info tip\"><i class=\"icon-book\" style=\"margin-left: 2px; vertical-align: middle\"></i></a>" : "" )."
										".( $Info['forum'] ? "<a href=\"{$Info['forum']}\" target=\"_blank\" data-original-title=\"{$this->lang['catalog_forum']}\" class=\"status-info tip\"><i class=\"icon-comments-alt\" style=\"margin-left: 2px; vertical-align: middle\"></i></a>" : "" )."
										".( $Info['autor_link'] ? "<a href=\"{$Info['autor_link']}\" target=\"_blank\" data-original-title=\"{$this->lang['catalog_autor']}\" class=\"status-info tip\"><i class=\"icon-user\" style=\"margin-left: 2px; vertical-align: middle\"></i></a>" : "" )."
										<p class=\"bt_catalog_desc\">{$Info['desc']}</p>
								</div>";
	}
	
	/* 
		Save config array file
	*/
	function SaveConfig( $file, $save_con, $title_arr ) 
	{
		$save_con = is_array( $save_con ) ? $save_con : array( $save_con );

		$handler = fopen( MODULE_DATA . '/'.$file.'.php', "w" );

		fwrite( $handler, "<?PHP \n\n//Settings \n\n\${$title_arr} = array (\n\n" );
		
		foreach ( $save_con as $name => $value )
		{
				$value = str_replace( "{", "&#123;", $value );
				$value = str_replace( "}", "&#125;", $value );
				$value = str_replace( "$", "&#036;", $value );
				$value = str_replace( '"', '&quot;', $value );
				
				
				$name = str_replace( "$", "&#036;", $name );
				$name = str_replace( "{", "&#123;", $name );
				$name = str_replace( "}", "&#125;", $name );
				$name = str_replace( '"', '&quot;', $name );

			fwrite( $handler, "'{$name}' => \"{$value}\",\n\n" );
		}

		fwrite( $handler, ");\n\n?>" );
		fclose( $handler );
		
		$this->ClearUIcache();
		
		return true;
	}

	/* 
		Show string
	*/
	function ThemeParserStr() 
	{
		if( !$this->str_table_num ) return false;
	
		$answer = "<table width=\"100%\" class=\"table table-normal\">";
	
		for( $i = 1; $i <= $this->str_table_num; $i++ )
		{
			$answer .= "<tr>
							<td class=\"col-xs-10 col-sm-6 col-md-7\"><h6>" . $this->str_table[$i]['title'] . "</h6><span class=\"note large\">" . $this->str_table[$i]['desc'] . "</span></td>
							<td class=\"col-xs-2 col-md-5 settingstd\">" . $this->str_table[$i]['field'] . "</td>
						</tr>";
		}
	
		$this->str_table = array();
		$this->str_table_num = 0;
	
		$answer .= "</table>";
	
		return $answer;
	}

	/* 
		Show table
	*/
	function ThemeParserTable( $id = '', $other_tr = '' ) 
	{		
		if( !$this->list_table_num ) return false;
		
		$answer = "<table width=\"100%\" class=\"table table-normal table-hover\" ".( ( $id ) ? 'id="'.$id.'"':'' ).">";
		
		for( $i = 1; $i <= $this->list_table_num; $i++ )
		{
			$answer .= "<tr>";
			if( $i == 1 ) $answer .= "<thead>";
			
			foreach( $this->list_table[$i] as $width=>$td )	$answer .= ( $i==1 ) ? $td: "<td>" . $td . "</td>";
			
			if( $i == 1 ) $answer .= "</thead>";
			$answer .= "</tr>";
		}

		$answer .= $other_tr;
		$answer .= "</table>";
		
		return $answer;
	}
	

	/* 
		Add table
	*/
	function ThemeAddTR( $array ) 
	{
		$this->list_table_num++;
	
		$this->list_table[$this->list_table_num] = $array;
	 
		return TRUE;
	}
	
	/* 
		Add string
	*/
	function ThemeAddStr($title, $desc, $field) 
	{
		$this->str_table_num++;
	
		$this->str_table[$this->str_table_num] = array(
									'title' => $title, 
									'desc' => $desc, 
									'field' => $field
								);
	 
	}
	
	/* 
		Show menu items
	*/
	function ThemeMenuSectionParser( $block = 'main', $str = 2 ) 
	{
		if( !$this->section_num ) return false;
	
		$more = false;

		for( $i = 1; $i <= $this->section_num; $i++ )
		{
			if( $i > $str and !$more )
			{
				$answer .= "</div>
								<div onClick=\"ShowOrHideCookie('more_".$block."'); \" class=\"bt_more\">". $this->lang['more'] ."</div>
									
										<div id=\"more_".$block."\" " . ( $_COOKIE["cookie_more_$block"] != "show" ? "style=\"display:none\"" : "" ) . ">
											<div class=\"row box-section\">";
				$more = true;
			}
			
			if( $i%2 != 0 ) $answer .= "<div class=\"row box-section\">";

			$answer .= "<div class=\"col-md-6\">
						  <div class=\"news with-icons\">
								<div class=\"avatar\"><img src=\"". $this->section[$i]['icon'] ."\"></div>
								<div class=\"news-content\">
									<div class=\"news-title\"><a href=\"". $this->section[$i]['link'] ."\">". $this->section[$i]['title'] ."</a></div>
									<div class=\"news-text\">
									  <a href=\"". $this->section[$i]['link'] ."\">". $this->section[$i]['desc'] ."</a>
									</div>
								</div>
						  </div>
						</div>";

			if( $i%2==0 OR $i == $this->section_num ) $answer .= "</div>";
		}
	
		if( $more ) $answer .= "</div>";
		
		$this->section_num = 0;
		$this->section = array();
	
		return $answer;
	}
	
	/* 
		Show footer
	*/
	function ThemePadded( $text, $box = 'box-footer', $position = 'center' ) 
	{
		return "<div class=\"". $box ." padded\" style=\"text-align: ". $position ."\">". $text ."</div>";
	}

	/* 
		Add menu item
	*/
	function ThemeMenuSection( $title, $desc, $icon, $link ) 
	{
		$this->section_num++;
	
		$this->section[$this->section_num] = array(
									'title' => $title, 
									'desc' => $desc, 
									'icon' => $icon, 
									'link' => $link
								);
	
		return true;
	}
	
	/* 
		Show plugins
	*/
	function MenuPlugins() 
	{
		foreach( $this->GetPluginsArray() as $name => $config ) 
		{
			$this->ThemeMenuSection( $config['title'], $config['desc'], "engine/modules/billing/plugins/". ucfirst( $name ) ."/icon/icon.png", $PHP_SELF."?mod=billing&c=".$name );	
		}
	
		if( !$this->section_num )
			return "<div style=\"text-align: center; padding: 30px\">".$this->lang['no_plugin']."</div>";
	
		return $this->ThemeMenuSectionParser( "plugins" );
	}


	/* 
		Show billings
	*/
	function ThemeMenuPaysys() 
	{
		foreach( $this->GetPaysysArray() as $name => $config )
		{
			$title = ( $config['title'] ) ? $config['title']: $name;
			
			$status = ( $config['status'] ) ? "<br /><font color=\"green\">".$this->lang['on']."</font>": "<br /><font color=\"red\">".$this->lang['off']."</font>";
			
			$this->ThemeMenuSection( $title, $this->lang['go_paysys'] . "&laquo;" . $title ."&raquo;" .  $status, "engine/modules/billing/paysys/". ucfirst( $name ) ."/icon/icon.png", $PHP_SELF."?mod=billing&c=paysys&p=billing/".$name );	
		}
	
		if( !$this->section_num )
			return "<div style=\"text-align: center; padding: 30px\">".$this->lang['no_paysys']."</div>";
	
		return $this->ThemeMenuSectionParser( "paysys" );
	}
	
	/* 
		Show plugins
	*/
	function GetPluginsArray() 
	{		
		if( $this->PluginsArray ) return $this->PluginsArray;

		$List = opendir( MODULE_PATH . "/plugins/" );
	
		while ( $name = readdir($List) )
		{
			if ( in_array($name, array(".", "..", "/", "index.php", ".htaccess")) ) continue;

			$plugin_config = parse_ini_file( MODULE_PATH . "/plugins/" . $name . "/info.ini" );

			if( !$plugin_config ) continue;

			$this->PluginsArray[mb_strtolower($name)] = $plugin_config;
		}
		
		return $this->PluginsArray;
	}	

	/* 
		Get billings
	*/
	function GetPaysysArray() 
	{
		if( $this->PaysysArray ) return $this->PaysysArray;
		
		$List = opendir( MODULE_PATH . "/paysys/" );
	
		while ( $name = readdir($List) )
		{
			if ( in_array($name, array(".", "..", "/", "index.php", ".htaccess")) ) continue;
		
			/* Config */
			if( file_exists( MODULE_DATA."/pasys." . $name . ".php" ) )
				require_once MODULE_DATA."/pasys." . $name . ".php";
			else
				$paysys_config = array();
		
			$this->PaysysArray[$name] = $paysys_config;
			$this->PaysysArray[$name]['name'] = $name;
			
			if( !$this->PaysysArray[$name]['title'] ) $this->PaysysArray[$name]['title'] = $name;
		}
		
		return $this->PaysysArray;
	}

	/* 
		Show billing version
	*/
	function PaysysVersion( $name )
	{
		$GetIni = parse_ini_file( MODULE_PATH . "/paysys/" . ucfirst( $name ) . "/info.ini" );
		
		return $GetIni['version'];
	}

	/* 
		Creat cache
	*/
	function CreatCache( $file, $data ) 
	{
		file_put_contents (ENGINE_DIR . "/cache/" . $file . ".tmp", $data, LOCK_EX);
		
		@chmod( ENGINE_DIR . "/cache/" . $file . ".tmp", 0666 );
		
		return true;
	}

	/* 
		Get cache
	*/
	function GetCache( $file ) 
	{
		$buffer = @file_get_contents( ENGINE_DIR . "/cache/" . $file . ".tmp" );

		if ( $buffer !== false AND $this->config_dle['clear_cache'] ) {

			$file_date = @filemtime( ENGINE_DIR . "/cache/" . $file . ".tmp" );
			$file_date = time()-$file_date;

			if ( $file_date > ( $this->config_dle['clear_cache'] * 60 ) ) {
				$buffer = false;
				@unlink( ENGINE_DIR . "/cache/" . $file . ".tmp" );
			}

			return $buffer;

		} else return $buffer;	
		
		return true;
	}
	
	/* 
		Show user info
	*/
	function ThemeInfoUser( $login ) 
	{
		return "<div class=\"btn-group\">
	
					<a href=\"". $this->config_dle['http_home_url'] ."user/".urldecode( $login )."\" target=\"_blank\"><i class=\"icon-user\" style=\"margin-left: 10px; margin-right: 5px; vertical-align: middle\"></i></a>
	
					<a href=\"#\" target=\"_blank\" data-toggle=\"dropdown\" data-original-title=\"". $this->lang['history_user'] ."\" class=\"status-info tip\"><b>{$login}</b></a>
								<ul class=\"dropdown-menu text-left\">
									<li><a href=\"". $this->config_dle['http_home_url'] ."user/".urldecode( $login )."\" target=\"_blank\"><i class=\"icon-user\"></i> ". $this->lang['user_profily'] ."</a></li>
									<li><a href=\"". $PHP_SELF ."?mod=billing&c=users&login=".urldecode( $login )."\"><i class=\"icon-edit\"></i> ". $this->lang['user_balance'] ."</a></li>
									<li class=\"divider\"></li>
									<li>
										<div style=\"white-space: nowrap; text-align: center\">
											<a href=\"". $PHP_SELF ."?mod=billing&c=statistics&m=users&p=user/".urldecode( $login )."\" class=\"tip\" data-original-title=\"". $this->lang['user_stats'] ."\"><i class=\"icon-bar-chart icon-2x\"></i></a>
											<a href=\"". $PHP_SELF ."?mod=billing&c=history&p=user/".urldecode( $login )."\" class=\"tip\" data-original-title=\"". $this->lang['user_history'] ."\"><i class=\"icon-money icon-2x\"></i></a>
											<a href=\"". $PHP_SELF ."?mod=billing&c=refund&p=user/".urldecode( $login )."\" class=\"tip\" data-original-title=\"". $this->lang['user_refund'] ."\"><i class=\"icon-credit-card icon-2x\"></i></a>
											<a href=\"". $PHP_SELF ."?mod=billing&c=invoice&p=user/".urldecode( $login )."\" class=\"tip\" data-original-title=\"". $this->lang['user_invoice'] ."\"><i class=\"icon-folder-open-alt icon-2x\"></i></a>
										</div>
									</li>
								</ul>
				</div>";
	}
	
	/* 
		Pars xfields
	*/
	function ThemeInfoUserXfields() 
	{
		$answer = array(''=>"");
		
		$xprofile = file("engine/data/xprofile.txt");
		
		foreach($xprofile as $line)
		{
			$xfield = explode("|", $line);

			$answer[$xfield[0]] = $xfield[1];
		}
		
		return $answer;
	}

	/* 
		Show billing status
	*/
	function ThemeInfoBilling( $info = array() ) 
	{
		if( !$info['title'] ) return false;
		
		$status = ( $info['status'] ) ? "<a style=\"cursor: default; color: green\"> ". $this->lang['pay_status_on'] ."</a>": "<a style=\"cursor: default; color: red\"> ". $this->lang['pay_status_off'] ."</a>";
		
		return "<div class=\"btn-group\">
	
					".( $info['status'] ? "<span class=\"status-success\"><i class=\"icon-info-sign\" style=\"margin-left: 10px; margin-right: 5px; vertical-align: middle\"></i></span>" : "<i class=\"icon-info-sign\" style=\"margin-left: 10px; margin-right: 5px; vertical-align: middle\"></i>" )."
	
					<a href=\"#\" target=\"_blank\" data-toggle=\"dropdown\" data-original-title=\"". $this->lang['pay_name'] ."\" class=\"status-info tip\"><b>{$info['title']}</b></a>
					
								  <ul class=\"dropdown-menu text-left\">
								   <li>{$status}</li>
								   <li><a style=\"cursor: default\"> 1 ".$this->API->Declension( 1 )." = ".$info['convert']." ".$info['currency']."</a></li>
								 </ul>
				</div>";
	}

	/* 
		Show time and date
	*/
	function ThemeChangeTime( $time )
	{		
		date_default_timezone_set( $this->config_dle['date_adjust'] );
		 
		$ndate = date('j.m.Y', $time);
		$ndate_time = date('H:i', $time);

		if( $ndate == date('j.m.Y') ) 
			return $this->lang['main_now'] . $ndate_time;
		
		elseif($ndate == date('j.m.Y', strtotime('-1 day'))) 
			return $this->lang['main_rnow'] . $ndate_time;
			
		else
			return langdate( "j F Y  G:i", $time );
	}
	
	/* 
		Show theme header
	*/
	function ThemeEchoHeader() 
	{
		$JSmenu = "";
	
		foreach( array( 'history', 'statistics', 'invoice', 'users') as $name )
			$JSmenu .= ( $_GET['c']==$name ) ? '<li class="active"><a href="'.$PHP_SELF.'?mod=billing&c='.$name.'"> &raquo; '.$this->lang[$name.'_title'].'</a></li>' : '<li><a href="'.$PHP_SELF.'?mod=billing&c='.$name.'"> &raquo; '.$this->lang[$name.'_title'].'</a></li>';
	
		foreach( $this->GetPluginsArray() as $name => $config ) 
			$JSmenu .= ( $_GET['c']==$name ) ? '<li class="active"><a href="'.$PHP_SELF.'?mod=billing&c='.$name.'"> &raquo; '.$config['title'].'</a></li>' : '<li><a href="'.$PHP_SELF.'?mod=billing&c='.$name.'"> &raquo; '.$config['title'].'</a></li>';
			
		$JSmenu = "$('li .active').after('{$JSmenu}');";

		$Informers = $this->TopInformer();
		
		if( $Informers ) 
			$JSback .= '$(".padding-right").html(\''.$Informers.'\');';
	
		$JSreport = $_COOKIE["report_panel"]=="close" ? '': '$("body").prepend("<span id=\"report_panel\"><a href=\''.$PHP_SELF.'?mod=billing&m=report\' target=\'_blank\' class=\'bt_bag\'>' . $this->lang['main_report'] . '</a><i class=\"icon-remove bt_bag_cl\" onClick=\"ReportClose()\" title=\"' . $this->lang['main_report_close'] . '\"></i></span>")';
	
		echoheader( $this->lang['title'], $this->lang['desc'] . " v." . $this->config['version'] );

			echo "<link href=\"engine/modules/billing/theme/styles.css\" media=\"screen\" rel=\"stylesheet\" type=\"text/css\" />";
		
			echo '<script src="engine/modules/billing/theme/highcharts.js"></script>
				  <script src="engine/modules/billing/theme/exporting.js"></script>
				  <script src="engine/modules/billing/theme/jquery.cookie.js"></script>
				  <script src="engine/modules/billing/theme/core.js"></script>
				  <script type="text/javascript">'.$JSback.$JSmenu.$JSreport.' </script>';
	
		return;
	}
	
	/* 
		Clear UI cache
	*/
	private function ClearUIcache()
	{
		@unlink( ENGINE_DIR . "/cache/system/billing.php" );
	}
		
	/* 
		Show informers
	*/
	private function TopInformer()
	{
		$strInformers = "";
		$arrInformers = explode(",", $this->config['informers'] );
		$arrInformers = array_filter( $arrInformers );
		
		if( !count( $arrInformers ) ) return false;

		/* Invoice */
		if( in_array( 'invoice', $arrInformers ) )
		{
			$strInformers = $this->TopInformerView( "?mod=billing&c=statistics", $this->lang['main_news'], $this->Model->DbNewInvoiceSumm() ? $this->API->Convert( $this->Model->DbNewInvoiceSumm() ) : 0, $this->lang['statistics_0_title'], "icon-bar-chart", "green" );
			
			unset( $arrInformers[0] );
		}

		/* Plugins */
		foreach( $arrInformers as $strInformer )
		{ 
			$arrParsInformer = explode(".", $strInformer ); 
			
			if( file_exists( MODULE_PATH . '/plugins/' . $arrParsInformer[0] . '/' . $arrParsInformer[1] . '.php' ) )	require_once MODULE_PATH . '/plugins/' . $arrParsInformer[0] . '/' . $arrParsInformer[1] . '.php';
		}

		return "<div class=\"pull-right padding-right newsbutton\">" . $strInformers . "</div>";
	}
	
	/* 
		Theme informers
	*/
	private function TopInformerView( $strLink, $strTitle, $intCount, $strText, $icon = 'icon-add', $iconBground = 'blue' )
	{
		return "<div class=\"action-nav-normal action-nav-line\" style=\"display: inline-block;\"><div class=\"action-nav-button nav-small\" style=\"width:125px;\"><a href=\"" . $strLink . "\" class=\"tip\" title=\"" . $strTitle . "\"><span class=\"bt_informer\">" . $intCount . "</span><span>" . $strText . "</span> </a><span class=\"triangle-button " . $iconBground . "\"><i class=\"" . $icon . "\"></i></span></div></div>";
	}

	/* 
		Show groups (select)
	*/
	function GetGroups( $id = false, $none = false )
	{
		global $user_group;
		
		$returnstring = "";
		
		foreach ( $user_group as $group )
		{
			if( ( is_array( $none ) and in_array( $group['id'], $none ) )
				or ( !is_array( $none ) and $group['id'] == $none ) ) continue;
			
			$returnstring .= '<option value="' . $group['id'] . '" ';
			
			if( is_array( $id ) )
			{
				foreach ( $id as $element )
				{
					if( $element == $group['id'] ) $returnstring .= 'SELECTED';
				}
			}
			elseif( $id and $id == $group['id'] ) $returnstring .= 'SELECTED';
			
			$returnstring .= ">" . $group['group_name'] . "</option>\n";
		}
		
		return $returnstring;
	}

	/* 
		Load or ( create and load ) config file
	*/
	function LoadConfig( $file, $name, $creat = false, $setStarting = array() )
	{
		if( !file_exists( MODULE_DATA . "/plugin." . $file . ".php" ) )
		{
			if( $creat ) 
			{
				$this->SaveConfig( "plugin.". $file, array( $setStarting ), "plugin_config" );
				
				require MODULE_DATA . "/plugin." . $file . ".php";
			}
		}
		else
			require MODULE_DATA . "/plugin.". $file .".php";
		
		return $$name;
	}
	
	/* 
		Show theme panel header
	*/
	function ThemeHeadStart( $title, $toolbar = '' ) 
	{
		return "<div id=\"general\" class=\"box\">
					<div class=\"box-header\">
						<div class=\"title\">{$title}</div>
						<ul class=\"box-toolbar\">
						  <li class=\"toolbar-link\">
							{$toolbar}
						  </li>
						</ul>
					</div>
					
					<div class=\"box-content\">

						<form action=\"\" enctype=\"multipart/form-data\" method=\"post\" name=\"frm_billing\" >";

	}
	
	/* 
		Show theme panel footer
	*/
	function ThemeHeadClose() 
	{
		return "		</form>
					</div>
				</div>";
	}
	
	/* 
		Show theme footer
	*/
	function ThemeEchoFoother() 
	{
		return "<p style=\"text-align:center\">
					[ <a href=\"http://dle-billing.ru/\" target=\"_blank\">" . $this->lang['support'] . "</a> ]
					<br />
					&copy 2012 - 2016 <a href=\"javascript: DLEalert('" . $this->lang['dev'] . "', '" . $this->lang['dev_title'] . "')\">mr_Evgen</a>
				</p>";
		
		echofooter();
	}
	
}
?>