<?php	if( !defined( 'DATALIFEENGINE' ) OR !LOGED_IN ) die( "Hacking attempt!" );
/*
=====================================================
 Billing
-----------------------------------------------------
 evgeny.tc@gmail.com
-----------------------------------------------------
 This code is copyrighted
=====================================================
*/

if( $member_id['user_group'] != 1 )
{
	msg( "error", $lang['index_denied'], $lang['index_denied'] );
}

define( 'BILLING_MODULE', TRUE );
define( 'MODULE_PATH', ENGINE_DIR . "/modules/billing" );
define( 'MODULE_DATA', ENGINE_DIR . "/data/billing" );

require_once MODULE_PATH . '/lang/admin.php';

/* Need install */
if( !file_exists( MODULE_DATA . '/config.php' ) )
{
	require_once MODULE_PATH . '/helpers/install.php';
	
	exit();
}

/* Helpers classes */
require_once MODULE_DATA . '/config.php';
require_once MODULE_PATH . '/helpers/dbActions.php';
require_once MODULE_PATH . '/helpers/API.php';
require_once MODULE_PATH . '/helpers/Dashboard.php';

Dashboard::getInstance()->Loader();
?>