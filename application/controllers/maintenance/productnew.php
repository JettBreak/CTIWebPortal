<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class ProductNew extends CI_Controller {
	
	function __construct()
	{
		parent::__construct();
		$this->load->library('core');
		$this->load->model('coreapp/dept_model');
		$this->core->checkUserAllows(CARDPRODUCTLIST_NO);
		
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
		$this->load->model('coreapp/card_model');
		
		$card	 = $this->card_model;
		$cache	 = $this->cache;

		$data['title'] = 'Card Product Entry';
		$data['submitBtnMsg'] = 'Are all entries correct?';
		$data['formAction'] = 'maintenance/productnew/submit';
		$data['prodType'] = '';
		$data['prodDesc'] = '';
		$data['prodCode'] = '';

		$result = $card->getavailableaccttype();
		$availacct = $result->result_array();

		$result->free_result();
		$result->next_result();


		$availtypes = '';
		if (count($availacct) > 0) {
			//foreach ($availacct as $row) {
			//	$acct = $row['accttype'];

			for ($i = 0; $i < 99; $i++) {

				$cntr = 0;
				foreach ($availacct as $row) {
					$acct = $row['accttype'];

					if ($i == $acct) {
						$cntr++;
					}

				}
				if ($cntr > 0) {
					continue;
				}

				$availtypes .= '<option value="'. str_pad($i, 2, 0, STR_PAD_LEFT) .'">'. str_pad($i, 2, 0, STR_PAD_LEFT) .'</option>';
				
				//if (!in_array($i, $availacct)) {
				//}
			}
			//}
		}

		//$data['prodType'] = $availtypes;
		$data['prodCode'] = $availtypes;


		//card status
		//if (!$cardStatus = $cache->get($this->core->getSessionID() . 'cardStatus')) {			
		$result = $card->getavailablecardstat();
		$cardStatus = $result->result_array();
		
		$result->free_result();
		$result->next_result();
			
			//$cache->save($this->core->getSessionID() . 'cardStatus', $cardStatus, CACHE_TTL);
		//}

		$statlist  = '';
		$statlist2 = '';

		if (count($cardStatus) > 0) {
			foreach ($cardStatus as $row) {
				$status = $row['status'];
				$desc = $row['description'];

				$selected = $status == 11 ? ' selected' : NULL;
				$statlist .= '<option value="'. $status .'"'. $selected .'>'. strtoupper($desc) .'</option>';

				$selected2 = $status == 4 ? ' selected' : NULL;
				$statlist2 .= '<option value="'. $status .'"'. $selected2 .'>'. strtoupper($desc) .'</option>';
			}
		} else {
			echo json_encode(array(
				'success' => FALSE,
				'message' => 'No card status defined. Please contact software administrator.'
			));
			exit();
		}


		$data['embnstat'] = $statlist;
		$data['pmlnstat'] = $statlist;
		$data['actnstat'] = $statlist2;

		//$data['deptCode'] = $department['deptCode'];
		//$data['deptName'] = $department['deptName'];
		//$data['hiddenInput'] = '<input type="hidden" name="deptCode" id="deptCode" value="'. $department['deptCode'] .'"/>'.
		//	'<input type="hidden" name="isEdited" id="isEdited" value="0"/>';
		
		$data['sessionExp'] = $this->core->getSessionExp();
		$this->load->view('maintenance/productnew', $data);
	}
	
	function submit()
	{
		$this->load->model('coreapp/card_model');
		
		$success  = TRUE;
		$core	  = $this->core;
		$input	  = $this->input;
		$card 	  = $this->card_model;

		$cardProdType = $input->post('newProductType', TRUE);
		$cardProdDesc = strtoupper($input->post('newProductDesc', TRUE));
		$cardProdCode = strtoupper($input->post('newProductCode', TRUE));
		$embnstat = strtoupper($input->post('newEmbnstat', TRUE));
		$pmlnstat = strtoupper($input->post('newPmlnstat', TRUE));
		$actnstat = strtoupper($input->post('newActnstat', TRUE));

		$prtype 		= 'CARD';
		$currency 		= 'PHP'; //temporary ();
		$xrate 			= 1;
		$groupno		= 0;
		$groupname		= NULL;
		$subgroupno		= 0;
		$subgroupname	= 0;
		$aclass			= NULL;
		$isdefault		= NULL;
		$useraudit		= $core->getUserID();
		$override		= NULL;
		$wkstn 			= $core->getWorkstation();
		$islimit		= 'Y';
		$isfee			= 'Y';
		$ispersonalized = 'Y';
		$sessionID 		= $core->getSessionID();
		$isgeneric		= 'Y';

		$result = $card->getCardBIN();

		$row = $result->row_array();

		$result->free_result();
		$result->next_result();

		$xml1 = '<DEFBIN>'.$row['codevalue'].'</>'.
				'<EMBNSTAT>'.$embnstat.'</>'.
				'<PMLNSTAT>'.$pmlnstat.'</>'.
				'<ACTNSTAT>'.$actnstat.'</>';

		if (str_pad($core->getBANKCODE(), 3, '0', STR_PAD_LEFT) == '002') {
			$xml1 .= '<GENNSTAT>17</>';
		}

		$result = $card->insertCardType(
			$cardProdType,
			$prtype,
			$prtype,
			$cardProdCode,
			$cardProdDesc,
			$currency,
			$xrate,
			$groupno,
			$groupname,
			$subgroupno,
			$subgroupname,
			$aclass,
			$isdefault,
			$useraudit,
			$override,
			$wkstn,
			$xml1,
			$islimit,
			$isfee,
			$ispersonalized,
			$sessionID,
			$isgeneric);

		$row = $result->row_array();

		if ($row['errno'] > 0) {
			$success = FALSE;
			$message = $row['errmsg'];
		} else {
			$success = TRUE;
			$message = 'Card type successfully added.';
		}

		/*
		$row = $this
			->user_model
			->checkLogin(
				$this->core->getUserID(),
				$this->core->getSessionID()
			)
			->row_array();
			*/
		
		//if ($row['errno'] !== '8') { //if session valid
		
			/*$result = $this->dept_model->updateDepartment(
				$this->input->post('deptCode', TRUE),
				$this->input->post('newDeptCode', TRUE),
				$this->input->post('deptName', TRUE),
				$this->core->getBranchID(),
				$this->core->getIPAddress(),
				$this->core->getWorkstation(),
				$this->core->getUserID(),
				$this->core->getSessionID(),
				$this->input->post('isEdited', TRUE)
			);*/
			
			/*$row = $result->row_array();
			
			if ($row['errno'] > 0) {
				$success = FALSE;
			} else {
				$success = TRUE;
			}*/
			
		//} else {
		//	$success = FALSE;
			//$message = $row['errmsg'];
		//}
		
		echo json_encode(array(
			'success' => $success,
			'message' => $message
			//'errorno' => $row['errno']
		));
	}
}