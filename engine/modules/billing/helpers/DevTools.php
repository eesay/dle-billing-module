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
	Tools for the development: cabinet
*/
Class DevTools
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
	// Config - array module
	var $config = array();
	// Config - array DLE
	var $config_dle = array();
	// Info User array
	var $member_id = array();
	// Lang cabinet.php
	var $lang = array();
	
	// Int time dle
	var $_TIME = false;
	// Class pay_api
	var $API = false;
	// DB methods
	var $Model = false;
	// Float user balance
	var $BalanceUser = false;

	protected $elements = array();
	protected $element_block = array();
	protected $plugins = array();

	/*
		Main()
	*/
	function Loader()
	{
		if( $this->Loader ) return;
		
		global $user_group, $config, $member_id, $billing_lang, $_TIME, $db;

		require MODULE_DATA . '/config.php';
		require MODULE_PATH . '/lang/cabinet.php';

		// - off
		if( !$billing_config['status'] )
		{
			// - for billings
			if( $_GET['c'] == "pay" and $_GET['m'] == "get" ) exit("Off");
			
			// - for users
			if( $member_id['user_group']!=1 )
			{
				echo $billing_lang['cabinet_off'];
				return;
			}
			else
				// - for admins
				echo $billing_lang['off'];
		}
		
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
		
		$this->BalanceUser = $this->API->Convert( $this->member_id[$billing_config['fname']] );
	
		/* Pointer controller */
		$start = explode("/", $this->config['start'] );

		$c = ( preg_replace ("/[^a-zA-Z0-9\s]/", "", trim( $_GET['c'] ) ) ) ? trim( $_GET['c'] ) : $start[0];
		$m = ( preg_replace ("/[^a-zA-Z0-9\s]/", "", trim( $_GET['m'] ) ) ) ? trim( $_GET['m'] ) : $start[1];

		$arrParams = array();
			
		$getParams = explode(':', $_GET['p'] ? $_GET['p'] : $start[2] );

		for( $i = 0; $i < count( $getParams ); $i++ )
		{
			$arrParams[$getParams[$i]] = preg_replace("/[^-_рРА-Яа-яa-zA-Z0-9.\s]/", "", $getParams[$i+1]);
			$i++;
		}

		if( file_exists( MODULE_PATH . '/controllers/user.' . mb_strtolower( $c ) . '.php' ) )
			require_once MODULE_PATH . '/controllers/user.' . mb_strtolower( $c ) . '.php';

		elseif( file_exists( MODULE_PATH . '/plugins/' . ucfirst( $c ) . '/user.main.php' ) )
			require_once MODULE_PATH . '/plugins/' . ucfirst( $c ) . '/user.main.php';
			
		else
		{
			echo str_replace( "{c}", $c, $billing_lang['cabinet_controller_error'] );
			return;
		}
		
		$Cabinet = new USER;

		$Cabinet->DevTools = $this;
		$Cabinet->user_group = $user_group;

		if( in_array($m, get_class_methods($Cabinet) ) )
		{
			echo $Cabinet->$m( $arrParams );
		}
		else
		{
			echo str_replace("{c}", $c, str_replace("{m}", $m, $billing_lang['cabinet_metod_error']));
			return;
		}
	}
	
	/*
		Search and replace tags
	*/
	function ThemePregReplace( $tag, &$data, $update = '' )
	{
		$data = preg_replace("'\\[$tag\\].*?\\[/$tag\\]'si", $update, $data);
		
		return;
	}
	
	/*
		Save value tags, before load
	*/
	function ThemeSetElement( $field, $value )
	{
		$this->elements[$field] = $value;
	
		return;
	}

	/*
		Save value in tags, before load
	*/
	function ThemeSetElementBlock( $fields, $value )
	{
		$this->element_block[$fields] = $value;
	
		return;
	}
	
	/*
		Search in tags
	*/
	function ThemePregMatch( $theme, $tag )
	{
		$answer = array();
		
		preg_match($tag, $theme, $answer);
		
		return $answer[1];
	}

	/*
		Show lang time
	*/
	function ThemeChangeTime( $time, $format )
	{
		date_default_timezone_set( $this->config_dle['date_adjust'] );
		 
		$ndate = date('j.m.Y', $time);
		$ndate_time = date('H:i', $time);

		if( $ndate == date('j.m.Y') ) 
			return $this->lang['cabinet_now'] . $ndate_time;
		
		elseif($ndate == date('j.m.Y', strtotime('-1 day'))) 
			return $this->lang['cabinet_rnow'] . $ndate_time;
			
		else
			return langdate( $format, $time );
	}
	
	/*
		Array plugins
	*/
	function PluginsArray()
	{
		if( count( $this->plugins ) ) return $this->plugins;
	
		$load_list = opendir( MODULE_PATH . "/plugins/" );
	
		while ( $name = readdir($load_list) )
		{
			if ( in_array($name, array(".", "..", "/", "index.php", ".htaccess")) ) continue;

			/* Config */
			if( file_exists( MODULE_DATA . "/plugin." . mb_strtolower( $name ) . ".php" ) )
				include MODULE_DATA . "/plugin." . mb_strtolower( $name ) . ".php";
			
			else
				continue;
						
			if( !$plugin_config['status'] or !$plugin_config['name'] ) continue;
		
			$this->plugins[$name] = $plugin_config;
		}
	
		return $this->plugins;
	}
	
	/*
		Load .tpl
	*/
	function ThemeLoad( $TplPath )
	{
		$Content = @file_get_contents( ROOT_DIR . "/templates/". $this->config_dle['skin'] ."/billing/". $TplPath . ".tpl" ) or die( $this->lang['cabinet_theme_error'] . "$TplName.tpl" );	

		return $Content;
	}
	
	/*
		Show page
	*/
	function Show( $Content, $Menu = 'log' )
	{
		// get
		$Cabinet = file_get_contents( ENGINE_DIR . "/cache/system/billing.php" );	

		// not cache
		if( $Cabinet == FALSE ) 
		{
			$Cabinet = file_get_contents( ROOT_DIR . "/templates/". $this->config_dle['skin'] ."/billing/cabinet.tpl" );

			$TplPlugin = $this->ThemePregMatch( $Cabinet, '~\[plugin\](.*?)\[/plugin\]~is' );
			
			$PluginsList = "";
			
			if( count( $this->PluginsArray() ) ) 
				foreach( $this->PluginsArray() as $name => $pl_config )
				{
					$TimeLine = $TplPlugin;
					
					$TimeLine = str_replace("{plugin_link}", mb_strtolower( $name ), $TimeLine);
					$TimeLine = str_replace("{plugin_name}", $pl_config['name'], $TimeLine);
					$TimeLine = str_replace("{plugin_active}", "bt_menu_lisel[active]" . mb_strtolower( $name ) . "[/active]", $TimeLine);
					
					$PluginsList .= $TimeLine;
				}
				
			$Cabinet = preg_replace("'\\[plugin\\].*?\\[/plugin\\]'si", $PluginsList, $Cabinet);

			$save_file = fopen( ENGINE_DIR . "/cache/system/billing.php", "w" );
			fwrite( $save_file, $Cabinet );
			fclose( $save_file );
		}
		
		// sys tags
		$Cabinet = str_replace( "{content}", $Content, $Cabinet);

		$Cabinet = str_replace( "{CABINET}", $this->config['page'], $Cabinet);
		$Cabinet = str_replace( "{THEME}", $this->config_dle['skin'], $Cabinet);
		$Cabinet = str_replace( "{BALANCE}", $this->BalanceUser . $this->API->Declension( $this->BalanceUser ), $Cabinet);
			
		$Cabinet = str_replace( "[active]" . mb_strtolower( $Menu ) . "[/active]", "_active", $Cabinet);
		$Cabinet = preg_replace("'\\[active\\].*?\\[/active\\]'si", '', $Cabinet);

		// user tags
		foreach( $this->elements as $key=>$value ) 
		{
			$Cabinet = str_replace( $key, $value, $Cabinet);
		}
		
		foreach( $this->element_block as $key=>$value ) 
		{
			$Cabinet = preg_replace("'\\[".$key."\\].*?\\[/".$key."\\]'si", $value, $Cabinet);
		}
		
		// show
		return $Cabinet;
	}
	
	/*
		Show Msg
	*/
	function ThemeMsg( $title, $errors, $plugin = 'log' )
	{
		$this->ThemeSetElement( "{msg}", $errors );
		$this->ThemeSetElement( "{title}", $title );
				
		return $this->Show( $this->ThemeLoad( "msg" ), $plugin );
	}	
	
	/*
		Get Icon
	*/
	function ThemeIconPlugin( $plugin )
	{
		if( @getimagesize( $this->config_dle['http_home_url'] . "engine/modules/billing/plugins/" . ucfirst( $plugin ) . "/icon/icon.png" ) )
			return $this->config_dle['http_home_url'] . "engine/modules/billing/plugins/" . ucfirst( $plugin ) . "/icon/icon.png";
		else 
			return $this->config_dle['http_home_url'] . "templates/" . $this->config_dle['skin'] . "/billing/icons/pay_icon.png";
	}
	
	/*
		Get xfields user
	*/
	function ParsUserXFields( $xfields_str )
	{
		$arrUserfields = array();
		
		foreach( explode("||", $xfields_str) as $xfield_str )
		{
			$value = explode("|", $xfield_str);
				
			$arrUserfields[$value[0]] = $value[1];
		}
		
		return $arrUserfields;
	}
	
	/*
		Clear dir
	*/
	function ClearUrlDir($var)
	{
		if ( is_array($var) ) return "";
	
		$var = str_ireplace( ".php", "", $var );
		$var = str_ireplace( ".php", ".ppp", $var );
		$var = trim( strip_tags( $var ) );
		$var = str_replace( "\\", "/", $var );
		$var = preg_replace( "/[^a-z0-9\/\_\-]+/mi", "", $var );
		$var = preg_replace( '#[\/]+#i', '/', $var );

		return $var;
	}
	
	/*
		Get hash page
	*/
	function hash()
	{
		return base64_encode( $this->member_id['email'] .'/*\/'. date("H") );
	}

}
?>