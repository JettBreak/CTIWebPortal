<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class AccountLinking extends CI_Controller {
	
	function __construct()
	{
		parent::__construct();
		$this->load->library('core');
		$this->core->checkUserAllows(ACCNTLINKING_NO);
		
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
		$this->load->library('session');
		
		$session = $this->session;
		
		/*if (!$info = $session->userdata('cardInfo')) {
			$this->load->helper('url');
			redirect('welcome');
			exit();
		};*/
		$info = $_SESSION['cardInfo'];
		if (!$info) {
			$this->load->helper('url');
			redirect('welcome');
			exit();
		}
		$data['prseqno'] 	= $info['prseqno'];
		$data['cardNo']		= $this->core->isCoreEncrypt() ? $info['cardNo'] : $info['cardBIN'];
		$data['custName'] 	= utf8_decode($info['custName']);
		$data['cardStatus'] = $info['cardStatDesc'];
		$data['cardType'] 	= $info['cardType'];
		
		$session->set_flashdata('prseqno', $data['prseqno']);
		$data['sessionExp'] = $this->core->getSessionExp();
		
		
		$this->load->view('card/accountlinking', $data);
	}
	
	function getData()
	{
		$this->load->model('coreapp/card_model');
		$this->load->library('session');
		$this->load->library('shortxml');
		
		$card	   = $this->card_model;
		$session   = $this->session;
		$core      = $this->core;
		$xml 	   = $this->shortxml;
		
		
		$session->keep_flashdata('prseqno');
		$prseqno   = $session->flashdata('prseqno');
		
		$userAudit = $core->getUserID();
		$sessionID = $core->getSessionID();
		
		$result    = $card->getCardAccountLink($prseqno, $userAudit, $sessionID);
		
		unset($_SESSION['accounts']);
		
		$aaData = array();
		$prptr = NULL;
		
		$_SESSION['accounts'] = $result->result_array();
		
		if ($result->num_rows() > 0) {

			$sacntr = 0;
			$cacntr = 0;
			foreach ($result->result_array() as $rowcntr) {
				$xml->setXML($rowcntr['xml1']);
				$accType	= $xml->getValue('ACCT');

				if ($accType === 'SA') {
					$sacntr++;
				} elseif ($accType === 'CA') {
					$cacntr++;
				}
			}

			$eachsa = $sacntr;
			$eachca = $cacntr;
			foreach ($result->result_array() as $row)
			{
				$xml->setXML($row['xml1']);
				
				$prptr 		= $row['prptr'];
				$accntType 	= $xml->getValue('ACCTTYPE');
				$accntNo 	= $xml->getValue('ACCTNO');
				$authType 	= $xml->getValue('AUTHTYPE');
				$primary 	= $xml->getValue('PRIMARY');
				$pseqnoLink	= $row['pseqnolink'];
				$accType	= $xml->getValue('ACCT');

				if ($accType === 'SA') {
					//if ($eachsa === $sacntr) {
					$eachsa--;
					$prptr = $sacntr - $eachsa;
					//}
				} elseif ($accType === 'CA') {
					$eachca--;
					$prptr = ($cacntr - $eachca) + $sacntr;
				}
				
				
				$aaData[] = array(
					$prptr,
					$accntType,
					$accntNo,
					$authType,
					$primary,
					$pseqnoLink,
					$accType
				);
				
				$prptr++; 
			}
		}
		
		if ($result->num_rows === 0) {
			$isPrimary = 'Y';
		} else {
			$isPrimary = 'N';
		}
		
		$data = array(
			'prptr'   => $prptr,
			'primary' => $isPrimary
		);
		
		$session->set_userdata($data);
		
		echo json_encode(array(
			'aaData' => $aaData
		));
	}
	
	function remove()
	{
		$this->load->model('coreapp/card_model');
		$this->load->library('session');
		$this->load->library('shortxml');
		
		$core		= $this->core;
		$input 		= $this->input;
		$xml 	   = $this->shortxml;
		
		$prseqno 	= $input->post('prseqno', TRUE);
		$pseqnoLink = $input->post('pseqnoLink', TRUE);
		$prptr 		= $input->post('prptr', TRUE);
		$acctNo		= $input->post('prKey', TRUE);
		$acctDesc 	= $input->post('accntDesc', TRUE);
		$prKey 	    = $input->post('cardNo', TRUE);
		$primary 	= $input->post('primary', TRUE);
		$acctType 	= $input->post('acctType', TRUE);
		$branchID   = $core->getBranchID();
		$ipAddress  = $core->getIPAddress();
		$workstation = $core->getWorkstation();
		$userAudit	= $core->getUserID();
		$userOverride = $this->session->userdata('userOverride');
		$sessionID  = $core->getSessionID();

		$totalAccnts = count($_SESSION['accounts']);

		$xmlX = '';

		
		$result = $this->card_model->deleteCardAccountLink(
			$prseqno,
			$pseqnoLink,
			$prptr,
			$acctNo,
			$acctDesc,
			$prKey,
			$branchID,
			$ipAddress,
			$workstation,
			$userAudit,
			$userOverride,
			$sessionID
		);

		$result->free_result();
		$result->next_result();

		$replaceprimary = FALSE;
		
		foreach ($_SESSION['accounts'] as $trow) {

			# code...
			$xmlX = '';
			$xml->setXML($trow['xml1']);

			$pseqnoLinkX = $trow['pseqnolink'];
			$prptrX = $trow['prptr'];

			$accType = $xml->getValue('ACCT');

			if (intval($prptrX) > intval($prptr)) {
				$prptrX--;

				$isSetprimary = 0;
				if ($primary == 'Y') {
					if ($acctType == $accType) {

						//if ($replaceprimary === FALSE) {
							$xmlX = '<ACCTNO>'. $xml->getValue('ACCTNO') .'</>'.
								'<ACCTTYPE>'. $xml->getValue('ACCTTYPE') .'</>'.
								'<ACCTCODE>'. $xml->getValue('ACCTCODE') .'</>'.
								'<AUTHTYPE>'. $xml->getValue('AUTHTYPE') .'</>'.
								'<ACCT>'. $xml->getValue('ACCT') .'</>'.
								'<AUTHCODE>'. $xml->getValue('AUTHCODE') .'</>'.
								'<PRIMARY>Y</>';
							$primary = 'N';
							//$replaceprimary = TRUE;
						//}

						$isSetprimary = 1;
					}
				}
				$result = $this->card_model->updateCardAccountLink(
					$prseqno,
					$pseqnoLinkX,
					$prptrX,
					$xmlX,
					$isSetprimary,
					$acctNo,
					$acctDesc,
					$prKey,
					$branchID,
					$ipAddress,
					$workstation,
					$userAudit,
					$userOverride,
					$sessionID
				);
				$result->free_result();
				$result->next_result();
			}
		}
		
		$this->session->set_flashdata('prseqno', $prseqno);
		
		echo json_encode(array(
			'success' => TRUE,
			'message' => 'Account number: <strong>['. $acctNo .']</strong><br />successfully removed'
		));
	}
	
	function setprimary()
	{
		$this->load->model('coreapp/card_model');
		$this->load->library('session');
		$this->load->library('shortxml');
		
		$core		= $this->core;
		$input 		= $this->input;
		$xml 	   = $this->shortxml;
		
		$prseqno 	= $input->post('prseqno', TRUE);
		$pseqnoLink = $input->post('pseqnoLink', TRUE);
		$acctNo		= $input->post('prKey', TRUE);
		$acctDesc 	= $input->post('accntDesc', TRUE);
		$prKey 	    = $input->post('cardNo', TRUE);
		$primary 	= $input->post('primary', TRUE);
		$acctType 	= $input->post('acctType', TRUE);
		$prptr 		= $input->post('prptr', TRUE);
		$branchID   = $core->getBranchID();
		$ipAddress  = $core->getIPAddress();
		$workstation = $core->getWorkstation();
		$userAudit	= $core->getUserID();
		$userOverride = $this->session->userdata('userOverride');
		$sessionID  = $core->getSessionID();


		$sacntr = 0;
		$cacntr = 0;
		foreach ($_SESSION['accounts'] as $trow) {

			$xmlX = '';
			$xml->setXML($trow['xml1']);

			$pseqnoLinkX = $trow['pseqnolink'];
			$prptrX = $trow['prptr'];

			$accType = $xml->getValue('ACCT');


			if ($accType === 'SA') {
				$sacntr++;
			} else {
				$cacntr++;
			}

			if (intval($prptrX) < intval($prptr)) {
				$prptrX++;
				//if ($primary == 'Y') {
				if ($acctType == $accType) {

					$xmlX = '<ACCTNO>'. $xml->getValue('ACCTNO') .'</>'.
						'<ACCTTYPE>'. $xml->getValue('ACCTTYPE') .'</>'.
						'<ACCTCODE>'. $xml->getValue('ACCTCODE') .'</>'.
						'<AUTHTYPE>'. $xml->getValue('AUTHTYPE') .'</>'.
						'<ACCT>'. $xml->getValue('ACCT') .'</>'.
						'<AUTHCODE>'. $xml->getValue('AUTHCODE') .'</>'.
						'<PRIMARY>N</>';

					$isSetprimary = 1;

					$result = $this->card_model->updateCardAccountLink(
						$prseqno,
						$pseqnoLinkX,
						$prptrX,
						$xmlX,
						$isSetprimary,
						$acctNo,
						$acctDesc,
						$prKey,
						$branchID,
						$ipAddress,
						$workstation,
						$userAudit,
						$userOverride,
						$sessionID
					);
					$result->free_result();
					$result->next_result();
				}
			}
		}

		if ($acctType === 'SA') {
			$prptr = 1;
		} else {
			$prptr = $sacntr + 1;
		}
		
		$result = $this->card_model->setCardPrimaryAccountLink(
			$prseqno,
			$pseqnoLink,
			$acctType,
			$prptr,
			$branchID,
			$ipAddress,
			$workstation,
			$userAudit,
			$userOverride,
			$sessionID
		);


		$result->free_result();
		$result->next_result();
		
		$this->session->set_flashdata('prseqno', $prseqno);
		
		echo json_encode(array(
			'success' => TRUE,
			'message' => 'Account successfully changed'
		));
	}
}
/* End of file accountlinking.php */
/* Location: ./application/controllers/card/accountlinking.php */