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

Class dbActions
{
	var $where = "";
	var $db = false;
	var $BalanceField = false;
	var $_TIME = false;

	function DbSearchUsers( $limit = 100 ) 
	{
		$limit = intval( $limit );
		
		$answer = array();
		
		$this->db->query( "SELECT * FROM " . USERPREFIX . "_users " . $this->where . " order by " . $this->BalanceField . " desc limit " . $limit );

		while ( $row = $this->db->get_row() ) $answer[] = $row;
		
		return $answer;
	}

	function DbSearchUserByName( $name ) 
	{
		$name = $this->db->safesql( $name );
		
		$user = $this->db->super_query( "SELECT * FROM " . USERPREFIX . "_users WHERE name='" . $name . "'" );

		return $user;
	}

	function DbSearchUserById( $id ) 
	{
		$id = intval( $id );
		
		$user = $this->db->super_query( "SELECT * FROM " . USERPREFIX . "_users WHERE user_id='" . $id . "'" );

		return $user;
	}
	
	function DbGetRefundById( $refund_id ) 
	{
		$refund_id = intval( $refund_id );
	
		$refund = $this->db->super_query( "SELECT * FROM " . USERPREFIX . "_billing_refund WHERE refund_id='" . $refund_id . "'" );

		return $refund;
	}

	function DbRefundStatus( $refund_id, $new_status = 0 ) 
	{
		$refund_id = intval( $refund_id );
		
		$new_status = $new_status ? intval( $new_status ) : 0;
		
		$this->db->super_query( "UPDATE " . USERPREFIX . "_billing_refund SET refund_date_return='" . $new_status . "' where refund_id='" . $refund_id . "'" );

		return true;
	}

	function DbRefundRemore( $refund_id ) 
	{
		$refund_id = intval( $refund_id );
		
		$this->db->super_query( "DELETE FROM " . USERPREFIX . "_billing_refund WHERE refund_id='" . $refund_id . "'" );

		return true;
	}

	function DbGetRefundNum() 
	{
		$result_count = $this->db->super_query( "SELECT COUNT(*) as count FROM " . USERPREFIX . "_billing_refund " . $this->where );
		
        return $result_count['count'];
	}

	function DbGetRefund( $intFrom = 1, $intPer = 30 ) 
	{
		$this->parsPage( $intFrom, $intPer );

		$answer = array();

		$this->db->query( "SELECT * FROM " . USERPREFIX . "_billing_refund " . $this->where . " ORDER BY refund_id desc LIMIT {$intFrom},{$intPer}" );

		while ( $row = $this->db->get_row() ) $answer[] = $row;
		
		return $answer;
	}
	
	function DbGetInvoiceNum() 
	{
		$result_count = $this->db->super_query( "SELECT COUNT(*) as count FROM " . USERPREFIX . "_billing_invoice " . $this->where );
		
        return $result_count['count'];
	}

	function DbNewInvoiceSumm() 
	{
		$sqlInvoice = $this->db->super_query( "SELECT SUM(invoice_get) as summa FROM " . USERPREFIX . "_billing_invoice where invoice_get>'0' and invoice_date_pay>" . mktime(0,0,0) );
		
		return $sqlInvoice['summa'] ? $sqlInvoice['summa'] : 0;
	}
	
	function DbGetInvoice( $intFrom = 1, $intPer = 30 ) 
	{
		$this->parsPage( $intFrom, $intPer );
		
		$answer = array();

		$this->db->query( "SELECT * FROM " . USERPREFIX . "_billing_invoice " . $this->where . " ORDER BY invoice_id desc LIMIT {$intFrom},{$intPer}" );

		while ( $row = $this->db->get_row() ) $answer[] = $row;
		
		return $answer;
	}
	
	function DbGetHistoryNum() 
	{
		$result_count = $this->db->super_query( "SELECT COUNT(*) as count FROM " . USERPREFIX . "_billing_history " . $this->where );

        return $result_count['count'];
	}

	function DbGetHistory( $intFrom = 1, $intPer = 30 ) 
	{
		$this->parsPage( $intFrom, $intPer );
		
		$answer = array();

		$this->db->query( "SELECT * FROM " . USERPREFIX . "_billing_history " . $this->where . " ORDER BY history_id desc LIMIT {$intFrom},{$intPer}" );

		while ( $row = $this->db->get_row() ) $answer[] = $row;
		
		return $answer;
	}
	
	function DbCreatInvoice( $strPaySys, $strUser, $floatGet, $floatPay ) 
	{
		$this->parsVar( $strUser );
		$this->parsVar( $strPaySys, "/[^a-zA-Z0-9\s]/" );
		$this->parsVar( $floatGet, "/[^.0-9\s]/" );
		$this->parsVar( $floatPay, "/[^.0-9\s]/" );

		$this->db->query( "INSERT INTO " . USERPREFIX . "_billing_invoice	(invoice_paysys, invoice_user_name, invoice_get, invoice_pay, invoice_date_creat) values 
																			('" . $strPaySys . "', '" . $strUser . "', '" . $floatGet . "', '" . $floatPay . "', '" . $this->_TIME . "')" );
				
		return $this->db->insert_id();
	}

	function DbGetInvoiceByID( $id ) 
	{
		$id = intval( $id );
	
		if( !$id ) return false;
	
		$Invoice = $this->db->super_query( "SELECT * FROM " . USERPREFIX . "_billing_invoice WHERE invoice_id='" . $id . "'" );

		return $Invoice;
	}
	
	function DbInvoiceUpdate( $invoice_id, $wait = false ) 
	{
		$invoice_id = intval( $invoice_id );
	
		$time = ( !$wait ) ? $this->_TIME : 0;

		$this->db->super_query( "UPDATE " . USERPREFIX . "_billing_invoice SET invoice_date_pay='" . $time . "' where invoice_id='" . $invoice_id . "'" );

		return true;
	}
	
	function DbInvoiceRemove( $invoice_id ) 
	{
		$invoice_id = intval( $invoice_id );
	
		$this->db->super_query( "DELETE FROM " . USERPREFIX . "_billing_invoice WHERE invoice_id='" . $invoice_id . "'" );

		return true;
	}
	
	function DbCreatRefund( $strUser, $floatSum, $floatComm, $strReq ) 
	{
		$this->parsVar( $strUser );
		$this->parsVar( $strReq );
		$this->parsVar( $floatSum, "/[^.0-9\s]/" );
		$this->parsVar( $floatComm, "/[^.0-9\s]/" );
		
		$this->db->query( "INSERT INTO " . USERPREFIX . "_billing_refund 	(refund_date, refund_user, refund_summa, refund_commission, refund_requisites) values 
																			('" . $this->_TIME . "', '" . $strUser . "', '" . $floatSum . "', '" . $floatComm . "', '" . $strReq . "')" );
				
		return $this->db->insert_id();
	}
		
	function DbWhere( $where_array ) 
	{
		$this->where = "";

		foreach( $where_array as $key => $value )
		{
			$this->parsVar( $value );

			if( empty( $value ) ) continue;
			
			$this->where .= !$this->where ? "where " . str_replace("{s}", $value, $key) : "and " . str_replace("{s}", $value, $key);
		}
	
	}
	
	/*
		Filter examples:
			/[^-_рРА-Яа-яa-zA-Z0-9\s]/
			/[^a-zA-Z0-9\s]/
			/[^.0-9\s]/
	*/
	function parsVar( &$str, $filter = '' )
	{
		$str = trim( $str );
		
		if( function_exists( "get_magic_quotes_gpc" ) && get_magic_quotes_gpc() ) $str = stripslashes( $str );
		
		$str = htmlspecialchars( trim( $str ), ENT_COMPAT );
		
		if( $filter )
		{
			$str = preg_replace( $filter, "", $str);
		}
		
		$str = preg_replace('#\s+#i', ' ', $str);
		$str = $this->db->safesql( $str );
	
		return $str;
	}
		
	private function parsPage( &$intFrom, &$intPer )
	{
		$intFrom = intval( $intFrom );
		$intPer = intval( $intPer );
	
		if( $intFrom < 1 ) $intFrom = 1;
		if( $intPer < 1 ) $intPer = 30;
	
		$intFrom = ( $intFrom * $intPer ) - $intPer;
		
		return true;
	}
	
}
?>