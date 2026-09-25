<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class UserGroupNew extends CI_Controller {
	
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
		if (!$_SESSION['newGroupID']) {
			$this->load->helper('url');
			redirect('welcome');
			exit();
		}
		
		$data['groupName'] = NULL;
		$data['secOpt0Chk'] = 'checked';
		$data['secOpt1Chk'] = 'checked';
		$data['secOpt2Chk'] = 'checked';
		$data['secOpt2Attr'] = NULL;
		$data['userExpiry'] = 90;
		$data['secOpt3Chk'] = 'checked';
		$data['secOpt3Attr'] = NULL;
		$data['userInactive'] = 30;
		$data['secOpt7Chk'] = 'checked';
		$data['secOpt7Attr'] = NULL;
		$data['sessionExpx'] = 15;
		
		$data['maxRety'] = 6;
		
		$data['passOpt0Chk'] = 'checked';
		$data['passOpt0Attr'] = NULL;
		$data['minChar'] = 8;
		$data['passOpt1Chk'] = 'checked';
		$data['passOpt1Attr'] = NULL;
		$data['passExpiry'] = 30;
		$data['passExpiryMin'] = 30;
		$data['passOpt2Chk'] = 'checked';
		$data['passOpt3Chk'] = 'checked';
		$data['passOpt4Chk'] = 'checked';
		$data['passOpt5Chk'] = 'checked';
		$data['passOpt6Chk'] = 'checked';
		$data['passOpt6Attr'] = NULL;
		$data['passRecycle'] = 5;
		
		$data['title'] = 'New User Group';
		$data['waitMsg'] = 'Creating User Group...';
		$data['formAction'] = 'security/usergroupnew/submit';
		
		$data['sessionExp'] = $this->core->getSessionExp();
		$this->load->view('security/usergroup', $data);
	}
	
	function submit()
	{
		$grpseqno = $_SESSION['newGroupID'];
		if (!$grpseqno) {
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
		
		$result = $this->user_model->insertWebUserGroup(
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
			$message = 'New User Group created successfully';
			
			unset($_SESSION['newGroupID']);
		} else {
			$success = FALSE;
			$message = $_SESSION['newGroupID'];
		}
		
		$result->free_result();
		$result->next_result();
		
		echo json_encode(array(
			'success' => $success,
			'message' => $message
		));
	}
}