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

define( 'MODULE_DATA', ENGINE_DIR . "/data/billing" );

$login = $db->safesql( $login );

if( ! isset( $billing_config['fname'] ) ) include MODULE_DATA . '/config.php';

if( ( $member_id['name'] and $login == $member_id['name'] ) OR (!$login and $member_id['name']) )
	echo $member_id[$billing_config['fname']] ? $member_id[$billing_config['fname']] : $billing_config['format'];

else if ( $login )
{
	$search = $db->super_query( "SELECT ".$billing_config['fname']." FROM " . USERPREFIX . "_users WHERE name='$login'" );

	echo $search[$billing_config['fname']] ? $search[$billing_config['fname']] : $billing_config['format'];
}
?>