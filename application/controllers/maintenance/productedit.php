<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class ProductEdit extends CI_Controller {
	
	function __construct()
	{
		parent::__construct();
		$this->load->library('core');
		$this->load->model('coreapp/dept_model');
		$this->load->model('coreapp/card_model');
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
		$this->load->library('shortxml');

		$card = $this->card_model;
		$xml  = $this->shortxml;	
		
		//$this->cache->clean();
		if (!$cardProduct = $this->cache->get($this->core->getSessionID() . 'cardProduct')) {
			$this->load->helper('url');
			redirect('welcome');
			exit();
		}

		$data['title'] = 'Card Product Edit';
		$data['submitBtnMsg'] = 'Are all entries correct?';
		$data['formAction'] = 'maintenance/productedit/submit';
		$data['prodType'] = '';
		$data['prodDesc'] = '';
		$data['prodCode'] = '';

		$result = $this->card_model->getCardProductInfo($cardProduct['cardProdCode']);
		$row = $result->row_array();

		$result->free_result();
		$result->next_result();

		if (count($row) > 0) {

			$data['prodType'] = $row['acctcode'];
			$data['prodDesc'] = $row['description'];
			$data['prodCode'] = $row['accttype'];

			$data['disabled'] = TRUE; // prodcode disable field for edit.

			$xml->setXML($row['xml1']);

			$result = $card->getavailablecardstat();
			$cardStatus = $result->result_array();
			
			$result->free_result();
			$result->next_result();

			$statlist  = '';
			$statlist2 = '';
			$statlist3 = '';

			if (count($cardStatus) > 0) {
				foreach ($cardStatus as $row) {
					$status = $row['status'];
					$desc = $row['description'];

					$selected   = $status == $xml->getValue('EMBNSTAT') ? ' selected' : NULL;
					$statlist  .= '<option value="'. $status .'"'. $selected .'>'. strtoupper($desc) .'</option>';

					$selected2  = $status == $xml->getValue('PMLNSTAT') ? ' selected' : NULL;
					$statlist2 .= '<option value="'. $status .'"'. $selected2 .'>'. strtoupper($desc) .'</option>';

					$selected3  = $status == $xml->getValue('ACTNSTAT') ? ' selected' : NULL;
					$statlist3 .= '<option value="'. $status .'"'. $selected3 .'>'. strtoupper($desc) .'</option>';
				}

				$data['embnstat'] = $statlist;
				$data['pmlnstat'] = $statlist2;
				$data['actnstat'] = $statlist3;

			} else {
				echo json_encode(array(
					'success' => FALSE,
					'message' => 'No card status defined. Please contact software administrator.'
				));
				exit();
			}


			$data['sessionExp'] = $this->core->getSessionExp();
			$this->load->view('maintenance/productedit', $data);
		} else {
			echo json_encode(array(
				'auth' => FALSE,
				'message' => 'An error has occured. Please contact software administrator.'));
			exit();
		}
		
		//$data['deptCode'] = $department['deptCode'];
		//$data['deptName'] = $department['deptName'];
		//$data['hiddenInput'] = '<input type="hidden" name="deptCode" id="deptCode" value="'. $department['deptCode'] .'"/>'.
		//	'<input type="hidden" name="isEdited" id="isEdited" value="0"/>';
		
	}
	
	function submit()
	{
		
		$success  = TRUE;
		$core	  = $this->core;
		$input	  = $this->input;
		$card 	  = $this->card_model;

		$cardProdType = $input->post('editProductType', TRUE);
		$cardProdDesc = strtoupper($input->post('editProductDesc', TRUE));
		$cardProdCode = strtoupper($input->post('editProductCode', TRUE));
		$embnstat = strtoupper($input->post('editEmbnstat', TRUE));
		$pmlnstat = strtoupper($input->post('editPmlnstat', TRUE));
		$actnstat = strtoupper($input->post('editActnstat', TRUE));

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

		$result = $card->editCardType(
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
			$message = 'Card Type successfully updated.';
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