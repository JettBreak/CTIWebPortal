<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class ChangeStatus extends CI_Controller {
	
	private $fileVersion = '1.10.00';
	
	function __construct()
	{
		parent::__construct();
		$this->load->library('core');
		
		$this->core->checkUserAllows(ACCNTCHNGESTAT_NO);
		
		$this->load->library('version');

		$file = basename(__DIR__) . '/' . basename(__FILE__);

		$verified = $this->version->validate($file, $this->fileVersion);

		if (!$verified) {
			echo json_encode(array(
				'auth' => FALSE,
				'message' => 'Module is out of date. Please contact software administrator.'
			));
			exit();
		}

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
		
		$info = $_SESSION['accountInfo'];
		if (!$info) {
			$this->load->helper('url');
			redirect('welcome');
			exit();
		}
		
		$this->load->model('coreapp/card_model');
		$this->load->library('coreconverters');
		
		$data['cifseqno'] = $info['cifseqno'];
		$data['branchName'] = $info['brname'];
		$data['accountNo'] = $info['prkey'];
		$data['accountDesc'] = strtoupper($info['acctdesc']);
		$data['owner'] = $info['cifseqno'] ? $info['lastname'] .', '. $info['firstname'] .' '. $info['middlename'] : 'NONE';
		$data['authMode'] = strtoupper($info['authmode']);
		$data['accntStatus'] = strtoupper($info['statdesc']);
		$data['accntStatusOpt'] = NULL;
		
		//tran allows		
		$result = $this->card_model->getDefaultTranAllows('ACCT');
		
		$data['tranAllows'] = NULL;
		
		$result->free_result();
		$result->next_result();
		//end
		
		$result = $this->card_model->getAcctStat($info['accttype']);
		
		$cardLink = array();
		$cardsLinked = NULL;
		
		foreach ($result->result_array() as $row) {
			//add zeros to cifseqno
			/*$len = 8 - strlen($row['cifseqno']);
			$cifseqno = NULL;
			for ($i = 1; $i <= $len; $i++) {
				$cifseqno .= '0'; 
			}
			$cifseqno .= $row['cifseqno'];*/
			//end
			
			/*$cardLink[] = array(
				$row['prkey'],
				$cifseqno,
				$row['description']
			);*//*
			$cardsLinked = '<tr>'.
				'<td>'. $row['prkey'] .'</td>'.
				'<td>'. $row['cifseqno'] .'</td>'.
				'<td>'.$row['description'] .'</td>'.
			'</tr>';*/	
			if (intval($row['statcode']) === 10 && intval($info['status']) !== 10) {
				continue;	//exclude for verification status (10)
			} else {
				$selected = $info['status'] === $row['statcode'] ? ' selected' : NULL;
				$data['accntStatusOpt'] .= '<option value="'. $row['statcode'] .'"'. $selected .'>'. strtoupper($row['description']) .'</option>';	
			}
			
		}
		$isReadOnly = NULL;
		if (intval($info['status']) === 10) {
			$isReadOnly = 'disabled';
		}
		$data['accntStatus'] = '<select name="accntStatus" id="accntStatus" style="width:212px" '.$isReadOnly.'>';
		$data['accntStatus'] .= $data['accntStatusOpt'];
		$data['accntStatus'] .= '</select>';
		//$data['cardsLinked'] = $cardsLinked;
		
		//$this->output->cache(CACHE_TTL);
		$data['title'] = 'Change Account Status';
		$data['formAction'] = 'accounts/changestatus/submit';
		$data['isReadOnly'] = $isReadOnly;
		
		$data['sessionExp'] = $this->core->getSessionExp();
		$this->load->view('accounts/changestatus', $data);
	}
	
	function getFormat($formatValue, $brChar)
	{
		$branchCode = $this->core->getBranchCode();
		
		$padCnt = substr_count($formatValue, $brChar);
		$get = str_repeat($brChar, $padCnt); //BBBBBB
		$brVal = str_pad($branchCode, $padCnt, '0', STR_PAD_LEFT);
		$newFormat = str_replace($get, $brVal, $formatValue);
		
		return $newFormat;
	}
	
	function submit()
	{
		$this->load->model('coreapp/card_model');
		$this->load->library('coreconverters');
		
		$card  = $this->card_model;
		$core  = $this->core;
		$input = $this->input;
		
		$info = $_SESSION['accountInfo'];
		if (!$info) {
			echo json_encode(array(
				'success' => FALSE,
				'message' => 'An error has occured'
			));
			exit;
		}
		
		//$prseqno	 = $info['prseqno'];
		//$cifseqno	 = $input->post('cifseqno', TRUE);
		//$brseqno     = $info['brseqno'];
		$prKey       = $info['prkey'];
		//$acctType    = $info['accttype'];
		$newstatus   = $input->post('accntStatus', TRUE);
		$oldstatus	 = $info['status'];
		$ipAddress   = $core->getIPAddress();
		$workstation = $core->getWorkstation();
		$userAudit	 = $core->getUserID();
		$override	 = $this->session->userdata('userOverride');
		
		
		if ($oldstatus == $newstatus) {
			$row = array('errno' => 1, 'errmsg' => 'No changes has been made');
		} else {
			$result = $card->updateAcctStatus(
				$prKey,
				$newstatus,
				$ipAddress,
				$workstation,
				$userAudit,
				$override
			);
			
			$row = $result->row_array();
		}
		
		$this->session->unset_userdata('userOverride');
		
		if ($row['errno'] === '0') {
			$success = TRUE;
			$message = 'Account successfully updated';
		} else {
			$success = FALSE;
			$message = $row['errmsg'];
		}
		
		echo json_encode(array(
			'success' => $success,
			'message' => $message,
			'params' => $prKey.' | '. $newstatus.' | '. $oldstatus.' | '. $ipAddress.' | '. $workstation.' | '. $userAudit.' | '. $override.' | '
		));
	}
}
/* End of file newentry.php */
/* Location: ./application/contollers/accounts/newentry.php */