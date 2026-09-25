<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class UserGroupEdit extends CI_Controller {
	
	function __construct()
	{
		parent::__construct();
		$this->load->library('core');
		$this->core->checkUserAllows(USERGROUPS_NO);
		
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
		$group = $_SESSION['userGroupx'];
		if (!$group) {
			$this->load->helper('url');
			redirect('welcome');
			exit();
		}
		
		$data['groupName'] = $group['desc'];

		$data['secOpt0Chk'] = substr($group['secuoption'], 0, 1) === '1' ? 'checked' : NULL;
		$data['secOpt1Chk'] = substr($group['secuoption'], 1, 1) === '1' ? 'checked' : NULL;
		
		if (substr($group['secuoption'], 2, 1) === '1') {
			$data['secOpt2Chk'] = 'checked';
			$data['secOpt2Attr'] = $group['seqno'] == 1 ? NULL : NULL;
		} else {
			$data['secOpt2Chk'] = NULL;
			$data['secOpt2Attr'] = 'disabled';
		}
		
		$data['userExpiry'] = $group['userexpiry'] ? $group['userexpiry'] : 90;
		
		if (substr($group['secuoption'], 7, 1) === '1') {
			$data['secOpt7Chk'] = 'checked';
			$data['secOpt7Attr'] = NULL;
		} else {
			$data['secOpt7Chk'] = NULL;
			$data['secOpt7Attr'] = 'disabled';
		}

		$data['userInactive'] = $group['userInactive'] ? $group['userInactive'] : 0;

		if (substr($group['secuoption'], 3, 1) === '1') {
			$data['secOpt3Chk'] = 'checked';
			$data['secOpt3Attr'] = $group['seqno'] == 1 ? NULL : NULL;
		} else {
			$data['secOpt3Chk'] = NULL;
			$data['secOpt3Attr'] = 'disabled';
		}
		
		$data['sessionExpx'] = $group['sessionexp'] ? $group['sessionexp'] : 15;
		
		$data['maxRety'] = $group['maxretry'];
		
		if (substr($group['passoption'], 0, 1) === '1') {
			$data['passOpt0Chk'] = 'checked';
			$data['passOpt0Attr'] = NULL;
		} else {
			$data['passOpt0Chk'] = NULL;
			$data['passOpt0Attr'] = 'disabled';
		}
		
		$data['minChar'] = $group['minchar'] ? $group['minchar'] : 3;
		
		if (substr($group['passoption'], 1, 1) === '1') {
			$data['passOpt1Chk'] = 'checked';
			$data['passOpt1Attr'] = $group['seqno'] == 1 ? NULL : NULL;
		} else {
			$data['passOpt1Chk'] = NULL;
			$data['passOpt1Attr'] = 'disabled';
		}
		
		$data['passExpiry'] = $group['passexpiry'] ? $group['passexpiry'] : ($group['seqno'] == 1 ? 30 : 30);
		$data['passExpiryMin'] = $group['seqno'] == 1 ? 30 : 30;
		
		$data['passOpt2Chk'] = substr($group['passoption'], 2, 1) === '1' ? 'checked' : NULL;
		$data['passOpt3Chk'] = substr($group['passoption'], 3, 1) === '1' ? 'checked' : NULL;
		$data['passOpt4Chk'] = substr($group['passoption'], 4, 1) === '1' ? 'checked' : NULL;
		$data['passOpt5Chk'] = substr($group['passoption'], 5, 1) === '1' ? 'checked' : NULL;
		$data['passOpt6Chk'] = substr($group['passoption'], 6, 1) === '1' ? 'checked' : NULL;


		$data['passRecycle'] = isset($group['passCycle']) ? $group['passCycle'] : 5;

		if (substr($group['passoption'], 6, 1) === '1') {
			$data['passOpt6Chk'] = 'checked';
			$data['passOpt6Attr'] = NULL;
			//$data['passRecycle'] = 0;
		} else {
			$data['passOpt6Chk'] = NULL;
			$data['passOpt6Attr'] = 'disabled';
			$data['passRecycle'] = 0;
		}
		
		$data['title'] = 'Update User Group';
		$data['waitMsg'] = 'Updating User Group...';
		$data['formAction'] = 'security/usergroupedit/submit';
		
		$data['sessionExp'] = $this->core->getSessionExp() != '' ? $this->core->getSessionExp() : $group['sessionexp'];
		$this->load->view('security/usergroup', $data);
	}
	
	function submit()
	{
		$group = $_SESSION['userGroupx'];
		if (!$group) {
			echo json_encode(array(
				'success' => FALSE,
				'message' => 'An error has occcured'
			));
			exit();
		}
		
		$this->load->model('coreapp/user_model');
		
		$securityOpt = NULL;
		$passwordOpt = NULL;
		
		//password options binary
		$secOptArr = $this->input->post('securityOpt');	
		$secOptCnt = 7;
		
		if ($secOptArr) {
			for ($i = 0; $i <= $secOptCnt; $i++) {
				if (array_key_exists($i, $secOptArr)) {
					$securityOpt .= '1';
				} else {
					$securityOpt .= '0';
				}
			}
		} else {
			$securityOpt = str_repeat(0, $secOptCnt + 1);
		}
		//end
		
		//password options binary
		$passOptArr = $this->input->post('passOpt');
		$passOptCnt = 6;
		
		if ($passOptArr) {
			for ($i = 0; $i <= $passOptCnt; $i++) {
				if (array_key_exists($i, $passOptArr)) {
					$passwordOpt .= '1';
				} else {
					$passwordOpt .= '0';
				}
			}
		} else {
			$passwordOpt = str_repeat(0, $passOptCnt + 1);
		}
		//end
		
		$grpseqno 	= $group['seqno'];
		$groupName	= $this->input->post('groupName', TRUE);
		$userAudit 	= $this->core->getUserID();
		$override 	= '';
		$workstation = $this->core->getWorkstation();
		
		$maxRetry 	= $this->input->post('maxRetry', TRUE);
		$userExpiry = $this->input->post('userExpiry', TRUE);
		$userInactive = $this->input->post('userInactive', TRUE);
		$minChar 	= $this->input->post('minChar', TRUE);
		$passExpiry = $this->input->post('passExpiry', TRUE);
		$passCycle = $this->input->post('passCycle', TRUE);
		$sessionExp = $this->input->post('sessionExp', TRUE);
		$xml = 
			//'<INDAY></>'.
			'<MAXRETRY>'. $maxRetry .'</>'.
			'<USEREXPIRY>'. ($userExpiry == '' ? 0 : $userExpiry) .'</>'.
			'<MINCHAR>'. $minChar .'</>'.
			'<MENUCAPTION>'. $groupName .'</>'.
			'<SESSIONEXP>'. ($sessionExp == '' ? 0 : $sessionExp) .'</>'.
			'<PASSEXPIRY>'. ($passExpiry == '' ? 0 : $passExpiry) .'</>'.
			'<SECUOPTION>'. $securityOpt .'</>'.
			'<PASSOPTION>'. $passwordOpt .'</>'.
			'<PWCYCLE>'. $passCycle .'</>'.
			'<USERINACTIVE>'. ($userInactive == '' ? 0 : $userInactive) .'</>';
		
		$result = $this->user_model->updateWebUserGroup(
			$grpseqno,
			$groupName,
			$userAudit,
			$override,
			$workstation,
			$xml
		);
		
		$row = $result->row_array();
		if ($row['errno'] == 0) {
			$success = TRUE;
			$message = 'User Group updated successfully';
		} else {
			$success = FALSE;
			$message = 'User Group already exists';
		}
		
		$result->free_result();
		$result->next_result();
		
		unset($_SESSION['newGroupID']);
		
		echo json_encode(array(
			'success' => $success,
			'message' => $message
		));
	}
}