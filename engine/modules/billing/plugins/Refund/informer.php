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

$this->Model->DbWhere( array( "refund_date_return = '0' " => 1 ) );

$strInformers .= $this->TopInformerView( "?mod=billing&c=Refund", $this->lang['refund_informer_title'], $this->Model->DbGetRefundNum(), $this->lang['refund_informer'], "icon-credit-card", "red" );
?>