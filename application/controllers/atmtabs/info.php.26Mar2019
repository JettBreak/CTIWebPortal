<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Info extends CI_Controller {
	
	function __construct()
	{
		parent::__construct();
		$this->load->library('core');
		$this->core->checkUserAllows(MONATM_NO);
		
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
		$this->load->model('coreapp/area_model');
		$this->load->model('coresys/atm_model');
		$this->load->model('coresys/pos_model');

		$this->load->library('shortxml');
		
		$atm   = $this->atm_model;
		$core  = $this->core;
		$input = $this->input;
		$xml   = $this->shortxml;
		
		//for ATM list
		if ($input->get('list', TRUE) === '1') {
			if ($core->canMon()) {
				$branchCode = $input->get('brcode');
				$locCode = $input->get('loccode');
			} else {
				$branchCode = $core->getBranchCode();
				$locCode = 0;
			}
			$status = $input->get('status');
			
			//get ATM list
			$result  = $atm->getATMList($branchCode, $locCode, $status);	
			$atmList = $core->showATMList($result);
			$atmList = $core->compressOutput($atmList);
			
			$result->free_result();
			$result->next_result();
		} else {
			$atmList = NULL;
		}
		//end
		
		//get ATM Info
		
		$terminalCode = $input->get('terminalcode');
		
		$result	= $atm->getATMInfoByCode($terminalCode);
		$row 	= $result->row_array();
		$xml->setXML($row['xml']);
		
		
		$terminalCode  = $row['termcode'];
		$terminalID    = $row['termid'];
		$terminalLuno  = $row['luno'];
		$terminalStats = $row['statdesc'] ? $row['statdesc'] : $row['status'] .'-Unknown';
		$terminalDesc  = $row['description'];
		$installType   = $row['insttype'];
		$terminalLoc   = ucwords(strtolower($row['location']));
		//$contactNo	   = $xml->getValue('CONTACT') ? $xml->getValue('CONTACT') : 'None';
		$brCode		   = $row['brcode'];
		$instid = $xml->getValue('INSTID') ? $xml->getValue('INSTID') : NULL;
		$outlet = $xml->getValue('OUTLET') ? $xml->getValue('OUTLET') : NULL;
        $isEMV  = $xml->getValue('EMVENABLED') ? $xml->getValue('EMVENABLED') : NULL;

		//Partner Institution
		IF ($instid != NULL && $core->hasPOSCashOut()) {
			$inst = $this->pos_model->getInstitutionInfo($instid);
			$instname = isset($inst['instname']) ? $inst['instname'] : 'N/A';
		} else {
			$instname = 'N/A';
		}
		//end

		//Outlet
		IF ($outlet != NULL && $core->hasPOSCashOut()) {
			$outl = $this->pos_model->getOutletInfo($outlet);
			$outletname = isset($outl['outletname']) ? $outl['outletname'] : 'N/A';
		} else {
			$outletname = 'N/A';
		}
		//end

		$result->free_result();
		$result->next_result();

		$result	= $atm->getATMLastCommandStatus($terminalLuno);
		$row 	= $result->row_array();

		if (count($row)) {
			$lastCmd	   = $row['description'] ? str_replace('Atm', 'ATM', ucwords(strtolower($row['description']))) : 'Unknown';
			$cmdStatus	   = $row['statdesc'];
		} else {
			$lastCmd	   = 'Unknown';
			$cmdStatus	   = 'Unknown';
		}
		
		
		//get area name
		$res = $this->area_model->getAreaByBranch($brCode);
		if ($res->num_rows() > 0) {
			$rw  = $res->row_array();
		
			$areaName = ucwords(strtolower($rw['areaname']));//$core->getAreaName();
		} else {
			$areaName = 'Unknown';
		}
		
		
		$res->free_result();
		$res->next_result();
		//end
		
		if ($core->canMon()) {
			if (!$branches = $this->cache->get($this->core->getSessionID() . 'branches')) {
				$this->load->model('coreapp/branch_model');
				$result = $this->branch_model->getBranchList();
			
				$branches = $result->result_array();
				
				$result->free_result();
				$result->next_result();
				$this->cache->save($this->core->getSessionID() .'branches', $branches, CACHE_TTL);
			}
			
			$branchName = 'Unknown';
			foreach ($branches as $row) {
				if ($brCode == $row['brcode']) {
					$branchName = ucwords(strtolower($row['brname']));
					break;
				}
			}
		} else {
			$branchName = $core->getBranchName();
		}
		
		$details = $core->compressOutput('<table>
			<tr>
				<td class="idName" width="150">Terminal Code:</td>
				<td id="infoTerminalCode" class="idNumber" width="200">'. $terminalCode .'</td>
				<td width="150" class="label">LUNO:</td>
				<td id="infoTerminalLuno" width="200">'. $terminalLuno .'</td>
			</tr>
			<tr>
				<td class="label">Terminal ID:</td>
				<td id="infoTerminalID">'. $terminalID .'</td>
				<td class="label">Terminal Status:</td>
				<td id="infoTerminalStats">'. $terminalStats .'</td>
			</tr>
			<tr>
				<td class="label">Description:</td>
				<td id="infoTerminalDesc">'. $terminalDesc .'</td>
				<td class="label">Installation Type:</td>
				<td id="infoInstallType">'. $installType .'</td>
			</tr>
			<tr>
				<td class="label">Location:</td>
				<td id="infoTerminalLoc">'. $terminalLoc .'</td>
                <td class="label">Partner Institution:</td>
                <td id="inPartnetInst">'. $instname .'</td>
			</tr>
			<tr>
				<td class="label">Area:</td>
				<td id="infoTerminalArea">'. $areaName .'</td>
                <td class="label">Outlet:</td>
                <td id="inOutlet">'. $outletname .'</td>
			</tr>
			<tr>
				<td class="label">Branch:</td>
				<td id="infoTerminalBranch">'. $branchName .'</td>
                <td class="label">EMV Enabled:</td>
                <td id="isEMV">'. ($isEMV == "Y" ? "Yes" : "No") .'</td>
			</tr>
			<tr>
				<td class="label">Last Command:</td>
				<td id="infoLastCommand">'. $lastCmd .'</td>
				<td class="label">Command Status:</td>
				<td id="infoCommandStatus">'. $cmdStatus .'</td>
			</tr>
		</table>');
		
		//<td class="label">Branch:</td>
				//<td id="infoTerminalBranch">'. $branchName .'</td>
				
       	echo json_encode(array(
			'success' => TRUE,
			'atm' 	  => $atmList,
			'details' => $details
		));
	}
}
/* End of file info.php */
/* Location: ./application/contollers/atmtabs/info.php */