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

/* Need install */
if( !file_exists( MODULE_DATA . '/config.php' ) )
{
	header( 'Refresh: 0; url='.$config['http_home_url'] );
	exit();
}

/* Helpers classes */
require_once MODULE_PATH . '/helpers/dbActions.php';
require_once MODULE_PATH . '/helpers/API.php';
require_once MODULE_PATH . '/helpers/DevTools.php';

DevTools::getInstance()->Loader();
?>