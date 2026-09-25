<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class OrderRequest extends CI_Controller {
	
	function __construct()
	{
		parent::__construct();
		$this->load->library('core');
		$this->core->checkUserAllows(CARDORDER_NO);
		
		$this->load->model('coreapp/user_model');

		$result = $this->user_model->checkLogin($this->core->getUserID(), $this->core->getSessionID());

		$row = $result->row_array();

		if (intval($row['errno']) > 0) {
			echo json_encode(array(
				'auth' => FALSE,
				'message' => 'Invalid Login Session. Please relogin'
			));
			exit();
		}
	}
	
	function index()
	{
		$this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
		
		//branches combobox
		if (!$branches = $this->cache->get($this->core->getSessionID() . 'branches')) {
			$this->load->model('coreapp/branch_model');
			$result = $this->branch_model->getBranchList();
		
			$branches = $result->result_array();
			
			$result->free_result();
			$result->next_result();
			$this->cache->save($this->core->getSessionID() .'branches', $branches, CACHE_TTL);
		}
		
		$data['branches'] = NULL;
		
		if (count($branches) > 0) {
			foreach ($branches as $row) {
				//if user branch is not allowed to monitor users from other branches
				if (!$this->core->isHeadOffice() && $row['brseqno'] === $this->core->getBranchID()) {
					$data['branches'] = '<option value="'. $row['brseqno'] .'">'. $row['brname'] .'</option>';
					break;
				}
				$data['branches'] .= '<option value="'. $row['brseqno'] .'">'. $row['brname'] .'</option>';
			}
		} else {
			$data['branches'] = '<option value="">No Branches Defined</option>';
		}
		
		if ($this->core->isHeadOffice()) {
			$data['uiToolbar'] = "$('.ui-toolbar:even').append($('#customToolbar .top').html());";
		} else {
			$data['uiToolbar'] = NULL;
		}
		
		//if INST has card product code
		$data['p1'] = NULL;
		$data['p2'] = NULL;
		$data['p3'] = NULL;
		if ($this->core->hasProductCode()) {
			$data['p1'] = '<th>Product Code</th>';
			$data['p2'] = ',{ bVisible: false }';
			$data['p3'] = ',prcdcode: aData[11]';
		}

		$data['batchUploadBtn'] = '';
		if ($this->core->hasBatchCardUpload()) {
			$data['batchUploadBtn'] = NULL;//'<button id="batchUploadBtn">Batch Upload</button>';
		}
		$data['sessionExp'] = $this->core->getSessionExp();
		
		$this->load->view('card/orderrequest', $data);
	}
	
	function getOrders()
	{
		$this->load->model('coreapp/card_model');
		$this->load->library('shortxml');		
		
		$card = $this->card_model;
		$core = $this->core;
		$xml  = $this->shortxml;

		if ($core->isHeadOffice()) {
			$branchID = $this->input->get('brseqno', TRUE);
		} else {
			$branchID = $core->getBranchID();
		}
		
		//$branchID  = $core->getBranchID();
		$ipAddress = $core->getIPAddress();
		$workstation = $core->getWorkstation();
		$userAudit = $core->getUserID();
		$sessionID = $core->getSessionID();

		$result = $card->getCardOrderList(
			$branchID, $ipAddress, $workstation, $userAudit, $sessionID
		);
		
		$numRows = $result->num_rows();
		$resultArr = $result->result_array();
		
		$result->free_result();
		$result->next_result();
						
		$aaData = array();
		if ($numRows > 0) {
			foreach ($resultArr as $row) {
				$id 	= $row['orderno'];
				$brName = $row['brname'];
				$dt 	= $core->formatDate('F j, Y g:i A', $row['dtrequested']);
				$type 	= $row['accttype'];
				$qty 	= $row['reqqty'];
				$status = $row['status'];
				
				$xml->setXML($row['xml1']);
				$cifseqno = $xml->getValue('CUSTNO');
				$detail = $xml->getValue('DETAIL') ? $cifseqno .': '. $xml->getValue('DETAIL') : 'N/A';
				$isPersonalized = $row['ispersonalized'];
				$embossName = $xml->getValue('EMBOSSNAME');
				$brseqno = $row['brseqno'];
				
				//hidden columns
				$bin = $row['bin'];
				
				$data = array(
					$id,
					$brName,
					$dt,
					$type,
					$qty,
					$bin,
					$status,
					$detail,
					$isPersonalized,
					$embossName,
					$brseqno
				);
				
				//if INST has card product code
				if ($this->core->hasProductCode()) {
			
					$inserted = array(
						$xml->getValue('PRCDCODE')
					);
					
					array_splice($data, 11, 0, $inserted);
				}
				
				$aaData[] = $data;
			}
		}
		
		echo json_encode(array(
			'success' => TRUE,
			'aaData' => $aaData
		));
	}
	
	function getHistory()
	{
		$this->load->model('coreapp/card_model');
		$this->load->library('shortxml');		
		
		$card = $this->card_model;
		$core = $this->core;
		$xml  = $this->shortxml;
		
		if ($core->isHeadOffice()) {
			$branchID = $this->input->get('brseqno', TRUE);
		} else {
			$branchID = $core->getBranchID();
		}
		$ipAddress = $core->getIPAddress();
		$workstation = $core->getWorkstation();
		$userAudit = $core->getUserID();
		$sessionID = $core->getSessionID();

		$result = $card->getCardOrderHistory($branchID, $ipAddress, $workstation, $userAudit, $sessionID);
		
		$aaData = array();
		if ($result->num_rows() > 0) {
			foreach ($result->result_array() as $row) {
				$id 	= $row['orderno'];
				$orderBy = $row['brfrom'];
				$date 	= $core->formatDate('F j, Y', $row['dtdate']);
				$time 	= $core->formatDate('g:i A', $row['dttime']);
				$dtRequested = $date .' '. $time;
				$dtGenerated = $row['dtgenerated'] ? $core->formatDate('F j, Y g:i A', $row['dtgenerated']) : 'Never';
				$cardType = $row['acctdesc'];
				$reqQty = $row['reqqty'];
				$genQty = $row['gencount'];
				$cardBIN = $row['bin'];
				$userAudit = $row['useraudit'] ? $row['useraudit'] : 'N/A';
				$statDesc = $row['statdesc'];
				$xml->setXML($row['xml1']);
				$detail = $xml->getValue('DETAIL') ? $xml->getValue('DETAIL') : 'N/A';
				
				$data = array(
					$id,
					$orderBy,
					$dtRequested,
					$dtGenerated,
					$cardType,
					$reqQty,
					$genQty,
					$cardBIN,
					$userAudit,
					$statDesc,
					$detail
				);
				
				//if INST has card product code
				if ($this->core->hasProductCode()) {
			
					$inserted = array(
						$xml->getValue('PRCDCODE')
					);
					
					array_splice($data, 11, 0, $inserted);
				}
				
				$aaData[] = $data;
			}
		}
		
		echo json_encode(array(
			'success' => TRUE,
			'aaData' => $aaData
		));
	}
	
	function remove()
	{
		$this->load->model('coreapp/card_model');
		
		$orderNo = $this->input->post('orderNo', TRUE);
		$brseqno = $this->core->getBranchID();
		$ipAddress = $this->core->getIPAddress();
		$workstation = $this->core->getWorkstation();
		$userAudit = $this->core->getUserID();
		$sessionID = $this->core->getSessionID();
		
		$this->card_model->deleteCardOrder($orderNo, $brseqno, $ipAddress, $workstation, $userAudit, $sessionID);
		
		echo json_encode(array(
			'success' => TRUE,
			'message' => 'Card Order <strong>['. $orderNo .']</strong> has been removed',
			'orderNo' => $orderNo
		));
	}
	
	function cancel()
	{
		$this->load->model('coreapp/card_model');
		
		$orderNo = $this->input->post('orderNo', TRUE);
		$brseqno = $this->core->getBranchID();
		$ipAddress = $this->core->getIPAddress();
		$workstation = $this->core->getWorkstation();
		$userAudit = $this->core->getUserID();
		$sessionID = $this->core->getSessionID();
		
		$this->card_model->cancelCardOrder($orderNo, $brseqno, $ipAddress, $workstation, $userAudit, $sessionID);
		
		echo json_encode(array(
			'success' => TRUE,
			'message' => 'Card Order <strong>['. $orderNo .']</strong> has been cancelled',
			'orderNo' => $orderNo
		));
	}
	
	//cache card order
	function cache()
	{
		$_SESSION['cardOrder'] = $_POST;
		
		if ($this->input->post('isPersonalized', TRUE) === 'Y') {
			$page = 'card/editpersonalized';
		} else {
			$page = 'card/editbatchorder';
		}
		
		echo json_encode(array(
			'success' => TRUE,
			'page' => $page
		));
	}
}
/* End of file orderrequest.php */
/* Location: ./application/controllers/card/orderrequest.php */