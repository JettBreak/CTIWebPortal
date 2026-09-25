<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class ATMCmd extends CI_Controller {

	function action()
	{
		$this->load->model('coreapp/user_model');
		$this->load->library('core');
		
		$core  = $this->core;
		$input = $this->input;
		
		$core->checkUserAllows(MONATM_NO);
		
		$userAudit    = $core->getUserID();
		$sessionID 	  = $core->getSessionID();
			
		$row = $this
			->user_model
			->checkLogin($userAudit, $sessionID)
			->row_array();
			
		if ($row['errno'] !== '8') { //if session valid
			$this->load->model('coresys/atmcmd_model');
			$atmCmd = $this->atmcmd_model;
			
			$brseqno      = $core->getBranchID();
			$action 	  = $input->post('action', TRUE);
			$luno 		  = $input->post('luno', TRUE);
			$terminalCode = $input->post('termcode', TRUE);
			$ipAddress	  = $core->getIPAddress();
			$workstation  = $core->getWorkstation();
			
			switch ($action) {
				case 'termUp':
					$atmCmd->terminalUp($luno, $brseqno, $userAudit, $ipAddress, $workstation);
					break;
				case 'termDown':
					$atmCmd->terminalDown($luno, $brseqno, $userAudit, $ipAddress, $workstation);
					break;
				case 'termLoad':
					$atmCmd->terminalLoad($luno, $brseqno, $userAudit, $ipAddress, $workstation);
					break;
				case 'termReset':
					$atmCmd->terminalReset($luno, $terminalCode, $brseqno, $userAudit, $ipAddress, $workstation);
					break;
				case 'getTermInfo':
					$atmCmd->getTerminalInformation($luno, $brseqno, $userAudit, $ipAddress, $workstation);
					break;
				case 'getSupplyCounters':
					$atmCmd->getSupplyCounters($luno, $brseqno, $userAudit, $ipAddress, $workstation);
					break;
				case 'synchronize':
					$atmCmd->syncDateTime($luno, $brseqno, $userAudit, $ipAddress, $workstation);
					break;
				case 'generateKey':
					$atmCmd->generateNewKey($luno, $brseqno, $userAudit, $ipAddress, $workstation);
					break;
				case 'tagForDevt':
					$atmCmd->tagTerminalForDeployment($luno, $brseqno, $userAudit, $ipAddress, $workstation);
					break;
				case 'tagUnderMaintenance':
					$atmCmd->tagTerminalUnderMaintenance($luno, $brseqno, $userAudit, $ipAddress, $workstation);
					break;
			}
			
			$success = TRUE;
		} else {
			$success = FALSE;
		}
		
		echo json_encode(array(
			'success' => $success,
			'message' => $row['errmsg'],
			'errorno' => $row['errno']
		));
	}
}
/* End of file atmcmd.php */
/* Location: ./application/contollers/monitoring/atmatmcmd.php */