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

define( 'BILLING_MODULE', TRUE );
define( 'MODULE_PATH', ENGINE_DIR . "/modules/billing" );
define( 'MODULE_DATA', ENGINE_DIR . "/data/billing" );

require_once MODULE_PATH . '/helpers/API.php';

$PayApi = new PAY_API;

if( !$billing_config['version'] )  include (ENGINE_DIR . '/data/billing/config.php');
	
$PayApi->config = $billing_config;
	
if( !isset( $db ) )
{
	include_once (ENGINE_DIR . '/classes/mysql.php');
	include_once (ENGINE_DIR . '/data/dbconfig.php');	
}
	
$PayApi->db = $db;
$PayApi->_TIME = $_TIME;
$PayApi->member_id = $member_id;
?>