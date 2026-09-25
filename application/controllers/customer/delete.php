<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Delete extends CI_Controller {
	
	function __construct()
	{
		parent::__construct();
		$this->load->library('core');
		$this->core->checkUserAllows(CUSTDELETE_NO);
	}
	
    function index($id = NULL)
	{
		$this->load->model('coreapp/customer_model');
		
		$core		= $this->core;
		$customer	= $this->customer_model;
		
		$userAudit	= $core->getUserID();
		$sessionID	= $core->getSessionID();

		$custappr = '';
		if ($id === NULL || $core->isISOCustomer()) { //if no param set then fetch from cache
			$this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
			if ($cust = $this->cache->get($this->core->getSessionID() . 'cust')) {
				$id = $cust['cifseqno'];
				$custappr = $cust['custappr'];
			} else {
				$this->load->helper('url');
				redirect('customer/search/delete');
				exit();
			}
		}

		if ($custappr === '') {
			$data['onclose'] = 'view';
			$result 	= $customer->searchCustomer($id, '', '', '', $userAudit, $sessionID);
		} else {
			$data['onclose'] = 'verify';
			$result 	= $customer->searchCustomerApproval($id, '', '', '', 0, $userAudit, $sessionID);
		}

		
		if ($result->num_rows() > 0) {
			$this->load->library('shortxml');
			$xml = $this->shortxml;
			
			$row = $result->row_array();
			
			$data['cifseqno'] 	= $core->isISOCustomer() ? $row['cifseqno'] : $row['cifseqno'];
			$data['blobPic'] 	= $row['blobpic'];
			
			$data['prefix'] 	= $row['prefix'];
			$data['lastName'] 	= $row['lastname'];
			$data['firstName'] 	= $row['firstname'];
			$data['middleName'] = $row['middlename'];
			$data['suffix'] 	= $row['suffix'];
			
			$xml->setXML($row['xml1']);
			
			$data['email'] 		= $xml->getValue('EMAIL');
			
			$xml->setXML($row['xml2']);
			
			$data['bDay'] 		= $xml->getValue('BIRTHDAY');
			$data['bPlace'] 	= $xml->getValue('BIRTHPLACE');
			$data['sss'] 		= $xml->getValue('SSS');
			$data['tin'] 		= $xml->getValue('TIN');
			$data['occupation'] = $xml->getValue('OCCUPATION');
			$data['race'] 		= $xml->getValue('RACE');
			$data['gender'] 	= $xml->getValue('SEX');
			$data['civil'] 		= $xml->getValue('CIVIL');
			$data['nationality'] = $xml->getValue('NATIONALITY');
			
			$xml->setXML($row['xml3']);
			
			$data['hPhone'] 	= $xml->getValue('HPHONE');
			$data['hFax'] 		= $xml->getValue('HFAX');
			$data['hOther'] 	= $xml->getValue('HOTHER');
			$data['bPhone'] 	= $xml->getValue('BPHONE');
			$data['bFax'] 		= $xml->getValue('BFAX');
			$data['bOther'] 	= $xml->getValue('BOTHER');
			$data['oPhone'] 	= $xml->getValue('OPHONE');
			$data['oFax'] 		= $xml->getValue('OFAX');
			$data['mPhone'] 	= $xml->getValue('MPHONE');
			
			$data['address1'] = NULL;
			$data['address2'] = NULL;
			$data['city'] 	  = NULL;
			$data['province'] = NULL;
			$data['zipCode']  = NULL;
			$data['country']  = NULL;
			$data['addrType'] = NULL;
			
			if ($row['xml4'] !== '') {
				$xml->setXML($row['xml4']);
				if ($xml->getValue('HADDRESS') !== '') {
					$data['address1'] 	= $xml->getValue('HADDRESS');
					$data['address2'] 	= $xml->getValue('HADDRESS2');
					$data['city']		= $xml->getValue('HCITY');
					$data['province'] 	= $xml->getValue('HPROV');
					$data['zipCode'] 	= $xml->getValue('HZIPCODE');
					$data['country'] 	= $xml->getValue('HCOUNTRY');
					$data['addrType']	= 'Home';
				}
			} elseif ($row['xml5'] !== '') {
				$xml->setXML($row['xml5']);
				if ($xml->getValue('BADDRESS') !== '') {
					$data['address1'] 	= $xml->getValue('BADDRESS');
					$data['address2'] 	= $xml->getValue('BADDRESS2');
					$data['city']		= $xml->getValue('BCITY');
					$data['province'] 	= $xml->getValue('BPROV');
					$data['zipCode'] 	= $xml->getValue('BZIPCODE');
					$data['country'] 	= $xml->getValue('BCOUNTRY');
					$data['addrType']	= 'Office';
				}
			} 
			
			$errNo 	= $row['errno'];
			$errMsg = $row['errmsg'];
			
			if ($errNo != 0) {
				echo json_encode(array(
					'success' => FALSE,
					'message' => $errMsg
				));
				exit();
			}
			
			if ($data['blobPic'] == 0) {
				$data['srcImg'] = 'images/nullphoto.jpg';
			} else {
				$data['srcImg'] = 'customer/photo/'. $data['cifseqno'] .'/'. $data['blobPic'] .'?'. time();
			}
			
			$result->free_result();
			$result->next_result();
			
			$result = $customer->getCustomerCardLink($id, $userAudit, $sessionID);
			
			$data['cardLink'] = NULL;
			
			foreach ($result->result_array() as $row)
			{
				$cardNo		 = $row['prkey'];
				$cardStats 	 = $row['description'];	
				$cardLastAct = $core->formatDate('F j, Y g:i A', $row['dtactive']);
				$cardLastMov = $core->formatDate('F j, Y g:i A', $row['dtlastmov']);
			
				$data['cardLink'] .= '<tr>';
				$data['cardLink'] .= '<td>'. $cardNo .'</td>';
				$data['cardLink'] .= '<td>'. $cardStats .'</td>';
				$data['cardLink'] .= '<td>'. $cardLastAct .'</td>';
				$data['cardLink'] .= '<td>'. $cardLastMov .'</td>';
				$data['cardLink'] .= '</tr>';
			}
			$data['hidden'] = $custappr !== '' ? '' : '<li><a href="#cardsLinked">Cards Owned</a></li>';

			$this->load->view('customer/delete', $data);
		} else {
			$data['type'] = 'delete';
			
			$this->output->cache(CACHE_TTL);
			$this->load->view('customer/search', $data);
		}
	}
	
	function remove()
	{
		$this->load->model('coreapp/customer_model');
		$input		= $this->input;
		
		$cifseqno = $this->input->post('cifseqno', TRUE);
		$useraudit = $this->core->getUserID();
		$override = '';
		$ipAddress = $this->core->getIPAddress();
		$workstation = $this->core->getWorkstation();
		$brseqno = $this->core->getBranchID();
		$sessionID = $this->core->getSessionID();
		
		//cifseqno, useraudit, override, ipAddress, workstation, brseqno, sessionID
		$result = $this->customer_model->deleteCustomer(
			$cifseqno,
			$useraudit,
			$override,
			$ipAddress,
			$workstation,
			$brseqno,
			$sessionID
		);
		
		$row = $result->row_array();
		
		if ($row['errno'] > 0) {
			$success = FALSE;
			$message = $row['errmsg'];
		} else {
			$success = TRUE;
			$message = 'Customer entry deleted successfully';
		}
		
		echo json_encode(array(
			'success' => $success,
			'message' => $message
		));
	}
}
/* End of file info.php */
/* Location: ./application/contollers/customer/info.php */