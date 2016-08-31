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

function genCode() 
{	
	$chars = 'abdefhiknrstyzABDEFGHKNQRSTYZ23456789';
	$numChars = strlen($chars);
	$string = '';
		  
	for ($i = 0; $i < 10; $i++) {
		$string .= substr($chars, rand(1, $numChars) - 1, 1);
	}
	  
	return $string;
}

	/* Start Config */
	$blank = array
	(
		'status' => "0",
		'page' => "billing",
		'currency' => "",
		'sum' => "100",
		'paging' => "10",
		'admin' => "",
		'secret' => "",
		'fname' => "user_balance",
		'start' => "log/main/page:1",
		'format' => "0.00",
		'version' => "0.6",
		'url_catalog' => "http://dle-billing.ru/engine/ajax/extras/plugins.php"
	);
	
	$blank['currency'] = $billing_lang['currency'];
	$blank['admin'] = $member_id['name'];
	$blank['secret'] = genCode();
	
	$htaccess_set = "# billing
RewriteRule ^([^/]+).html/([^/]*)(/?)+$ index.php?do=static&page=$1&seourl=$1&c=$2 [L]
RewriteRule ^([^/]+).html/([^/]*)/([^/]*)(/?)+$ index.php?do=static&page=$1&seourl=$1&c=$2&m=$3 [L]
RewriteRule ^([^/]+).html/([^/]*)/([^/]*)/([^/]*)(/?)+$ index.php?do=static&page=$1&seourl=$1&c=$2&m=$3&p=$4 [L]
RewriteRule ^pay/([^/]*)/([^/]*).html$ index.php?do=static&page=$1&seourl=$1&c=pay&m=get&p=$2 [L,QSA]";
						
/* Install */
if( isset( $_POST['agree'] ) )
{	
	// - htaccess
	if( is_writable( ".htaccess" ) )
	{
		if ( !strpos( file_get_contents(".htaccess"), "# billing" ) )
		{
			$new_htaccess = fopen(".htaccess", "a");
			fwrite($new_htaccess, "\n".$htaccess_set);
			fclose($new_htaccess); 
		} 		
	}
	elseif ( !strpos( file_get_contents(".htaccess"), "# billing" ) )
	{	
		msg( "error", $billing_lang['install_bad'], "<div style=\"text-align: left\">".$billing_lang['install_error']."<pre><code>".$htaccess_set."</code></pre></div><hr /><a href=\"\" class=\"btn btn-blue\" style=\"margin:7px;\" type=\"submit\">{$billing_lang['main_re']}</a>" );
	}
	
	// - Config
	$save_file = fopen( ENGINE_DIR . '/data/billing/config.php', "w" );
	fwrite( $save_file, "<?PHP \n\n//Settings \n\n\$billing_config = array (\n\n" );
	foreach ( $blank as $name => $value ) fwrite( $save_file, "'{$name}' => \"{$value}\",\n\n" );
	fwrite( $save_file, ");\n\n?>" );
	fclose( $save_file );

	// - sql
	$tableSchema[] = "DROP TABLE IF EXISTS " . PREFIX . "_billing_history";
	$tableSchema[] = "DROP TABLE IF EXISTS " . PREFIX . "_billing_invoice";
	$tableSchema[] = "DROP TABLE IF EXISTS " . PREFIX . "_billing_refund";
		
	if( !isset( $member_id[$blank['fname']] ) ) 
		$tableSchema[] = "ALTER TABLE `" . PREFIX . "_users` ADD {$blank['fname']} float NOT NULL";

	$_admin_sections = $db->super_query( "SELECT name FROM " . USERPREFIX . "_admin_sections WHERE name='billing'" );
			
	if( !isset( $_admin_sections['name'] ) ) 
		$tableSchema[] = "INSERT INTO `" . PREFIX . "_admin_sections` (`name`, `title`, `descr`, `icon`, `allow_groups`) VALUES ('billing', '".$billing_lang['title']."', '".$billing_lang['desc']."', 'billing.png', '1')";
			
	$_static = $db->super_query( "SELECT name FROM " . USERPREFIX . "_static WHERE name='billing'" );
				
	if( !isset( $_static['name'] ) ) 
		$tableSchema[] = "INSERT INTO `" . PREFIX . "_static` (`name`, `descr`, `template`, `allow_br`, `allow_template`, `grouplevel`, `tpl`, `metadescr`, `metakeys`, `views`, `template_folder`, `date`, `metatitle`, `allow_count`, `sitemap`, `disable_index`) VALUES ('billing', '".$billing_lang['cabinet']."', 'billing/cabinet', 1, 1, 'all', 'billing', 'billing/cabinet', 'cabinet, billing', 0, '', ".$_TIME.", '', 1, 1, 1);";
	
			$tableSchema[] = "CREATE TABLE `" . PREFIX . "_billing_history` (
								  `history_id` int(11) NOT NULL AUTO_INCREMENT,
								  `history_plugin` varchar(100) NOT NULL,
								  `history_plugin_id` int(11) NOT NULL,
								  `history_user_name` varchar(100) NOT NULL,
								  `history_plus` text NOT NULL,
								  `history_minus` text NOT NULL,
								  `history_balance` text NOT NULL,
								  `history_currency` varchar(100) NOT NULL,
								  `history_text` text NOT NULL,
								  `history_date` int(11) NOT NULL,
								  PRIMARY KEY (`history_id`)
								) ENGINE=InnoDB  DEFAULT CHARSET=" . COLLATE . " AUTO_INCREMENT=1 ;";
	
			$tableSchema[] = "CREATE TABLE `" . PREFIX . "_billing_invoice` (
								  `invoice_id` int(11) NOT NULL AUTO_INCREMENT,
								  `invoice_paysys` varchar(100) NOT NULL,
								  `invoice_user_name` varchar(100) NOT NULL,
								  `invoice_get` text NOT NULL,
								  `invoice_pay` text NOT NULL,
								  `invoice_date_creat` int(11) NOT NULL,
								  `invoice_date_pay` int(11) NOT NULL,
								  PRIMARY KEY (`invoice_id`)
								) ENGINE=InnoDB  DEFAULT CHARSET=" . COLLATE . " AUTO_INCREMENT=1 ;";

			$tableSchema[] = "CREATE TABLE `" . PREFIX . "_billing_refund` (
								  `refund_id` int(11) NOT NULL AUTO_INCREMENT,
								  `refund_date` int(11) NOT NULL,
								  `refund_user` varchar(100) NOT NULL,
								  `refund_summa` text NOT NULL,
								  `refund_commission` text NOT NULL,
								  `refund_requisites` text NOT NULL,
								  `refund_date_return` int(11) NOT NULL,
								  PRIMARY KEY (`refund_id`)
								) ENGINE=InnoDB DEFAULT CHARSET=" . COLLATE . " AUTO_INCREMENT=1 ;";
								
			foreach($tableSchema as $table)
			{
				$db->super_query($table);
			}	
	
	msg( "success", $billing_lang['install_ok'], str_replace( '\\', "", $billing_lang['dev']) . "<hr /><a href=\"\" class=\"btn btn-green\" style=\"margin:7px;\" type=\"submit\">{$billing_lang['main_next']}</a>" );
}
	
/* Page */
echoheader( $billing_lang['title']." ".$blank['version'], $billing_lang['install'] );

		echo "<div id=\"general\" class=\"box\">
					<div class=\"box-header\">
						<div class=\"title\">{$billing_lang['install']}</div>
						<ul class=\"box-toolbar\">
						  <li class=\"toolbar-link\">
							{$toolbar}
						  </li>
						</ul>
					</div>
					
					<div class=\"box-content\">

						<form action=\"\" enctype=\"multipart/form-data\" method=\"post\" name=\"frm_billing\" >
							<form action=\"{$PHP_SELF}\" method=\"post\">
								<div style=\"margin: 10px; height: 200px; border: 1px solid #76774C; background-color: #FDFDD3; padding: 5px; overflow: auto;\">
								{$billing_lang['license']}
								</div>
								<div class=\"row box-section\">	
									<input class=\"btn btn-green\" name=\"agree\" type=\"submit\" value=\"{$billing_lang['install_button']}\">
								</div>
							</form>
						</form>
					</div>
				</div>";

echofooter();
?>