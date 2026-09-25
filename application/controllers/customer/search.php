<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Search extends CI_Controller {
	
	function index($type)
	{
		$this->load->library('core');

		$this->load->model('coreapp/user_model');

		$result = $this->user_model->checkLogin($this->core->getUserID(), $this->core->getSessionID());

		$row = $result->row_array();

		if (intval($row['errno']) > 0) {
			echo json_encode(array(
				'auth' => FALSE,
				'message' => 'Your session has expired. Please relogin'
			));
			exit();
		}
		
		switch ($type)
		{
			case 'info':
				$menuPos = CUSTINFO_NO;
				break;
			case 'edit':
				$menuPos = CUSTEDIT_NO;
				break;
			case 'delete':
				$menuPos = CUSTDELETE_NO;
				break;
			case 'issuance':
				$menuPos = CARDISSUANCE_NO;
				break;
			case 'cardrequest':
				$menuPos = CARDORDER_NO;
				break;
		}
		
		$this->core->checkUserAllows($menuPos);
		
		$data['type'] = $type;
		if ($this->core->isISOCustomer()) {
			$data['title'] = 'Host CIF No.';
		} else {
			$data['title'] = 'Address';
		}
		$data['sessionExp'] = $this->core->getSessionExp();
		//$this->output->cache(CACHE_TTL);
		$this->load->view('customer/search', $data);
	}
	
	function submit()
	{
		$this->load->model('coreapp/customer_model');
		$this->load->library('core');
		$this->load->library('shortxml');
		
		$xml 		= $this->shortxml;
		$core		= $this->core;
		$customer	= $this->customer_model;
		$input		= $this->input;
		
		$cifno		= $input->post('custNo', TRUE) == '' ? 0 : $input->post('custNo', TRUE) ;
		$custLName 	= $input->post('custLastName', TRUE);
		$custMName 	= $input->post('custMiddleName', TRUE);
		$custFName 	= $input->post('custFirstName', TRUE);	
		
		$userID 	= $core->getUserID();
		$sessionID	= $core->getSessionID();
			
		$result 	= $customer->searchCustomer($cifno, $custFName, $custMName, $custLName, $userID, $sessionID);
		
		$data = array();
		
		$logXML = NULL;
				
		$message = NULL;
		$id = NULL;
		$fName = NULL;
		$mName = NULL;
		$lName = NULL;
		$name = NULL;
		$pass = FALSE;
		$title = array();
		if ($result->num_rows() > 0) {
			//if only 1 record found
			if ($result->num_rows === 1) {
				$row 	= $result->row_array();
				$id 	= $row['cifseqno'];
				$lName 	= $row['lastname'];
				$mName 	= $row['middlename'];
				$fName 	= $row['firstname'];
				$name 	= $lName .', '. $fName .' '. $mName;
				
				$pass = TRUE;
			} else {
				foreach ($result->result_array() as $row) {
					$id    = $row['cifseqno'];
					$lNamex = $row['lastname'];
					$mNamex = $row['middlename'];
					$fNamex = $row['firstname'];
					$name  = $lNamex .', '. $fNamex .' '. $mNamex;
					
					$xml->setXML($row['xml4']);
					$address = $xml->getValue('HADDRESS');

					$row3 = '';
					if ($core->isISOCustomer()) {
						$title['colname'] = 'Host CIF No.';
						$custkey = $row['custkey'];
						$row3 = $custkey;
					} else {
						$title['colname'] = 'Address';
						$row3 = $address;
					}

					
					$data[] = array($id, $name, $row3);
				}
				$message = $data;
			}
			//log
			$msgType = 41;
			$sysVCode = 0;
			$success = TRUE;
		} else {
			//log
			$msgType = 43;
			$sysVCode = 9056;
			$logXML .= '<SYSVMINI>CUSTOMER ERROR</>';
			$logXML .= '<SYSVDESC>No Customer Record Found</>';
			
			$success = FALSE;
			$message = 'No record found';
		}
		
		$result->free_result();
		$result->next_result();

		//log
		$trxCode = '990100';
		$brseqno = $core->getBranchID();
		$brName	 = $core->getBranchName();
		$workstation = $core->getWorkstation();
		$ipAddress	 = $core->getIPAddress();
		
		$logXML .= '<IP>'.$workstation.'</>';
		$logXML .= '<IPADDR>'.$ipAddress.'</>';
		$logXML .= '<BRNAME>'.$brName.'</>';
		$logXML .= '<BRSEQN0>'.$brseqno.'</>';
		
		//if search by cifno
		if ($cifno !== '') {
			$custFName = $fName;
			$custMName = $mName;
			$custLName = $lName;
		}
		$logXML .= '<ID>'.$cifno.'</>';
		$logXML .= '<FN>'.$custFName.'</>';
		$logXML .= '<MN>'.$custMName.'</>';
		$logXML .= '<LN>'.$custLName.'</>';
		
		$customer->insertLogclixx($msgType, $trxCode, $brseqno, $sysVCode, $userID, $userID, $workstation, $logXML);
		
		echo json_encode(array(
			'success' => $success,
			'result'  => $message,
			'data'	=> $title,
			'pass' => $pass,
			'id'   => $id,
			'name' => $name//for personalized card order
		));
	}
	
	function cache()
	{
		$success = TRUE;
		$message = '';

		try {

			$this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
			
			$input = $this->input;
			
			$cifseqno = $input->post('cifseqno', TRUE);
			$fullName = $input->post('fullName', TRUE);
			$type 	  = $input->post('type', TRUE);
			
			$custInfo = array(
				'cifseqno' => $cifseqno,
				'fullName' => $fullName,
				'type' 	  => $type,
				'custappr' => '',
				'tabledata'=> 0
			);
			
			$success = $this->cache->save($this->core->getSessionID() .'cust', $custInfo, CACHE_TTL);

		} catch (Exception $e) {
			$message = $e->getMessage();
		}


		echo json_encode(array(
			'success' => $success,
			'message' => $message
		));
	}
}
/* End of file search.php */
/* Location: ./application/contollers/customer/search.php */