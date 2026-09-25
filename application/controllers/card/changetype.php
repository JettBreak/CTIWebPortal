<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class ChangeType extends CI_Controller {
	
	function index()
	{
		$this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
		$this->load->model('coreapp/card_model');
		$this->load->library('core');
		
		$cardNo = '3223220010000079';
		$branchID = $this->core->getBranchID();
		$ipAddress = $this->core->getIPAddress();
		$workstation = $this->core->getWorkstation();
		$userAudit = $this->core->getUserID();
		
		$result = $this->card_model->getCardInfo($cardNo, $branchID, $ipAddress, $workstation, $userAudit);
		
		//if ($result->num_rows() > 0) {
			$row = $result->row_array();
		//}
		$acctType = $row['accttype'];
		$data['prseqno'] = $row['prseqno'];
		$data['dtEnrolled'] = $this->core->formatDate('F j, Y g:i A', $row['dtenroll']);
		$data['cardNo'] = $cardNo;
		$data['status'] = strtoupper($row['statdesc']);
		$data['acctdesc'] = $row['acctdesc'];
		//$data['embossName'] = '';
		
		$prefix = $row['prefix'];
		$fName = $row['fname'];
		$mName = $row['mname'];
		$lName = $row['lname'];
		$suffix = $row['suffix'];
		
		$data['custName'] = $prefix .' '. $lName .', '. $fName .' '. $mName .' '. $suffix;
		
		$data['allows'] = NULL;
		for ($i = 1; $i <= 20; $i ++) {
			$data['allows'] .= '<input type="checkbox" checked/>AAAAA<br />';
		}
		
		if (!$cardType = $this->cache->get($this->core->getSessionID() . 'cardType')) {
			$result->free_result();
			$result->next_result();
				
			$result = $this->card_model->getCardType('N');
			$cardType = $result->result_array();
			$this->cache->save($this->core->getSessionID() .'cardType', $cardType, CACHE_TTL);
		}
		
		$data['cardType'] = NULL;
		foreach ($cardType as $row)
		{
			$val  = $row['accttype'];
			$desc = $row['description'];
			
			if ($acctType !== $val) {
				$data['cardType'] .= '<option value="'. $val .'">'. $desc .'</option>';
			}
		}
		
		$this->load->view('card/changetype', $data);
	}
	
	function submit()
	{
		$this->load->model('coreapp/card_model');
		$this->load->library('core');
		
		$core = $this->core;
		
		$prseqno = $this->input->post('prseqno', TRUE);
		$accttype = $this->input->post('newCardType', TRUE);
		$userAudit = $core->getUserID();
		$sessionID = $core->getSessionID();
			
		$result = $this->card_model->setNewCardType($prseqno, $accttype, $userAudit, $sessionID);
		
		$row = $result->row_array();
		
		if ($row['errno'] > 0) {
			$success = FALSE;
			$message = $row['errmsg'];
		} else {
			$success = TRUE;
			$message = 'Card type successfully changed';
		}
		
		echo json_encode(array(
			'success' => $success,
			'message' => $message
		));
	}
}