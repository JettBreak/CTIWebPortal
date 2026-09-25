<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class ResetLimits extends CI_Controller {
	
	function __construct()
	{
		parent::__construct();
		$this->load->library('core');
		$this->core->checkUserAllows(CARDINFO);
	}
	
	function index()
	{
		$this->load->model('coreapp/card_model');
		
		$prseqno = intval($this->input->get('prseqno', TRUE));
		
		$result = $this->card_model->getCardLimitList($prseqno);
		
		$data['trx'] = NULL;		
		
		foreach ($result->result_array() as $row)
		{			
			$data['trx'] .= '<tr>'.
				'<td>'. $row['description'] .'</td>'.
				'<td align="center"><input type="checkbox"/></td>'.
				'<td align="center"><input type="checkbox"/></td>'.
			'</tr>';
		}
		
		$result->free_result();
		$result->next_result();
		//end
		
		$data['prseqno'] = $prseqno;
		
		$this->load->view('card/resetlimits', $data);
	}
	
	function submit()
	{
		$this->load->model('coreapp/card_model');
		
		$prseqno = $this->input->post('prseqno', TRUE);
		$brseqno = $this->core->getBranchID();
		$ipAddress = $this->core->getIPAddress();
		$workstation = $this->core->getWorkstation();
		$userAudit = $this->core->getUserID();
		$override = '';
		$sessionID = $this->core->getSessionID();
		
		if (isset($_POST['pinRetry'])) {
			$result = $this->card_model->resetPINRetryCount(
				$prseqno,
				$brseqno,
				$ipAddress,
				$workstation,
				$userAudit,
				$override,
				$sessionID
			);
			
			$row = $result->row_array();
			
			if (intval($row['errno']) > 0) {
				$success = FALSE;
				$message = 'An error has occured';
			} else {
				$success = TRUE;
				$message = 'PIN Retry Counter was reset';
			}
			
			$result->free_result();
			$result->next_result();
		}
		
		echo json_encode(array(
			'success' => $success,
			'message' => $message
		));
	}
}