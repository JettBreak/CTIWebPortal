<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class BillsAdd extends CI_Controller {
	
	function __construct()
	{
		parent::__construct();
		$this->load->library('core');
		$this->core->checkUserAllows(BILLSPAYMENT_NO);
	}
	
	function index()
	{
		$this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));	
		$this->load->model('coreapp/card_model');
		$this->load->library('shortxml');
		
		$xml = $this->shortxml;
		
		$info = $_SESSION['cardInfo'];
		if (!$info) {
			$this->load->helper('url');
			redirect('welcome');
			exit();
		}
		$data['prseqno'] = $info['prseqno'];
		
		//bills inst list
		$result = $this->card_model->getBillsInstList();
		
		$billsInstList = NULL;
		$billNumFormat = NULL;
		$subsNoMask = NULL;
		
		if ($result->num_rows() > 0) {
			foreach ($result->result_array() as $row)
			{
				$xml->setXML($row['xml1']);
				$snFormat = $xml->getValue('SNFORMAT');
				
				if ($snFormat === '') {
					$snFormat = 'None';
					$subsNoMask = '?*******************************';
				} else {
					$subsNoMask = $snFormat;
				}
				
				if ($billNumFormat === NULL) {
					$billNumFormat = $snFormat;
					$data['instID'] = $row['instid'];
					$data['subsNoMask'] = $subsNoMask;
				}
				
				$billsInstList .= '<option value="'. $row['bpayseqno'] .'" snformat="'. $snFormat .'" instid="'. $row['instid'] .'">'. $row['description'] .'</option>';
			}
		} else {
			$billsInstList = '<option value="">No data defined</option>';
		}
		
		$result->free_result();
		$result->next_result();
		//end
		
		//get max bills
		if (isset($_SESSION['maxBills'])) {
			$maxBills = $_SESSION['maxBills'];
		} else {				
			$result = $this->card_model->getMaxBills();
			$row = $result->row_array();
			
			$maxBills = $_SESSION['maxBills'] = $row['maxbills'];
			
			$result->free_result();
			$result->next_result();
		}
		
		$billsNo = NULL;
		for ($i = 1; $i <= $maxBills; $i++) {
			$billsNo .= '<option value="'. $i .'">'. $i .'</option>';
		}
		//end
		
		//get bpay status
		if (!$bpayStatus = $this->cache->get($this->core->getSessionID() . 'bpayStatus')) {			
			$result = $this->card_model->getBPayStat();
			$bpayStatus = $result->result_array();
			
			$result->free_result();
			$result->next_result();
			
			$this->cache->save($this->core->getSessionID() .'bpayStatus', $bpayStatus, CACHE_TTL);
		}

		$bpayStatOptions = NULL;
		
		if (count($bpayStatus) > 0) {
			foreach ($bpayStatus as $row)
			{				
				$bpayStatOptions .= '<option value="'. $row['codeseqno'] .'">'. $row['codevalue'] .'</option>';
			}
		} else {
			$bpayStatOptions = '<option value="">No data defined</option>';
		}
		//end
		
		$data['billsInstList'] = $billsInstList;
		$data['billsNo'] = $billsNo;
		$data['subsNo'] = NULL;
		$data['subsName'] = NULL;
		$data['billNumFormat'] = $billNumFormat;
		$data['bpayStatOptions'] = $bpayStatOptions;
		
		$data['title'] = 'Bills Payment Enrollment';
		$data['formAction'] = 'card/billsadd/submit';
		
		$this->load->view('card/billsx', $data);
	}
	
	function submit()
	{
		$this->load->model('coresys/billspay_model');
		$this->load->model('coreapp/card_model');
		
		$instID = $this->input->post('instID', TRUE);
		$subsNo = $this->input->post('subsNo', TRUE);
		
		$result = $this->billspay_model->validateSubscriber($instID, $subsNo);	
		$row = $result->row_array();
		
		$seqno = $row['seqno'];
		
		$result->free_result();
		$result->next_result();
		
		$msgType = 40;
		
		$ctr = 0;
		while ($msgType === 40) {
			$ctr++;
			$result = $this->billspay_model->getSubNoValidation($seqno);
			$row = $result->row_array();
			
			$msgType = intval($row['msgtype']);
			
			$result->free_result();
			$result->next_result();
			
			if ($ctr > 100) {
				echo json_encode(array(
					'success' => FALSE,
					'message' => 'An error has occured while validating subscriber no. Please try again',
					'msgType' => $msgType
				));
				exit;
			}
			
			usleep(100000);
		}
		
		switch($msgType) {
			case 41:
			case 42:
				$success = TRUE;
				//$message = 'Valid Subscriber No.';
				break;
			case 43:
				$success = FALSE;
				$message = 'Voided/Invalid Subscriber No.';
				break;
			default:
				$success = FALSE;
				$message = 'An error has occured';
				break;
		}
		
		if ($success) {
			$info = $_SESSION['cardInfo'];
			if (!$info) {
				$this->load->helper('url');
				redirect('welcome');
				exit();
			}
			
			$prseqno = $info['prseqno'];
			$payptr = $this->input->post('billNo', TRUE);
			$bpayseqno = $this->input->post('institution', TRUE);
			$subsNo = $this->input->post('subsNo', TRUE);
			$subsName = $this->input->post('subsName', TRUE);
			$status = $this->input->post('statusx', TRUE);
			$brseqno = $this->core->getBranchID();
			$ipAddress = $this->core->getIPAddress();
			$workstation = $this->core->getWorkstation();
			$userAudit = $this->core->getUserID();
			$sessionID = $this->core->getSessionID();
				
			$result = $this->card_model->insertBillsPayment(
				$prseqno,
				$bpayseqno,
				$payptr,
				$subsNo,
				$subsName,
				$status,
				$brseqno,
				$ipAddress,
				$workstation,
				$userAudit,
				$sessionID
			);
			
			$row = $result->row_array();
			
			$result->free_result();
			$result->next_result();
			
			$message = $row['errmsg'];
		}
		
		echo json_encode(array(
			'success' => $success,
			'message' => $message,
			'msgType' => $msgType
		));
	}
}
/* End of file billsadd.php */
/* Location: ./application/controllers/card/billsadd.php */