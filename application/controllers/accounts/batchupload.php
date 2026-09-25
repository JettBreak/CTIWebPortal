<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class BatchUpload extends CI_Controller {
	
	private $fileVersion = '1.10.00';
	
	function __construct()
	{
		parent::__construct();
		$this->load->library('core');
		$this->core->checkUserAllows(ACCNTUPLOAD_NO);

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
	}

	function index()
	{
		$this->load->view('accounts/batchupload');
	}
	
	function submit()
	{
		$success = TRUE;
		$message = '';
		$errors = array();
		$data = array();
		try {
		
			$this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
			$this->load->model('coreapp/card_model');
			$this->load->model('coreapp/customer_model');
			$this->load->library('coreconverters');


			$core = $this->core;
			$card = $this->card_model;
			$customer = $this->customer_model;
			$input = $this->input;

			$csv = $input->post('csv', TRUE);

			$lines = explode( "\n", $csv );

			$userAudit 	 = $core->getUserID();
			$ipAddress	 = $core->getIPAddress();
			$workstation = $core->getWorkstation();
			$brseqno	 = $core->getBranchID();
			$sessionID	 = $core->getSessionID();

			//save accttypes
			if (!$acctType = $this->cache->get($this->core->getSessionID() . 'acctType')) {
				$result = $card->getAccountType();
				$acctType = $result->result_array();

				$result->free_result();
				$result->next_result();

				$this->cache->save($this->core->getSessionID() .'acctType', $acctType, CACHE_TTL);
			}
		
			$validTypes = array();
			foreach ($acctType as $row) {
				$validTypes[] = intval($row['accttype']);
			}
			//end

			//save cardBIN
			/*if (!$cardBIN = $this->cache->get($this->core->getSessionID() . 'cardBIN')) {
				$this->load->model('coreapp/card_model');
				
				$result = $this->card_model->getCardBIN();
				$cardBIN = $result->result_array();
				$this->cache->save($this->core->getSessionID() . 'cardBIN', $cardBIN, CACHE_TTL);
				
				$result->free_result();
				$result->next_result();
			}*/
			
			/*$validCardBINs = array();
			foreach ($cardBIN as $row)
			{
				$validCardBINs[] = $row['codevalue'];
			}*/
			//end


			$uploaded = 0;
			foreach ($lines as $number => $line) {
				$row = explode(',', $line);

				$acctType = intval($row[0]);
				$prKey = $row[1];
				$cifnumber = $row[2];


				$lineError = array();

				//validate account type
				if (!in_array($acctType, $validTypes))
				{
					$lineError[] = 'Invalid account type';
				}

				$result = $this->card_model->getDefaultAllows('ACCT', $acctType, 'ACCT');
				$r = $result->row_array();
				
				if ($result->num_rows() > 0) {
					$defAllows = $r['allows'];
				} else {
					$defAllows = 0;
				}

				$hex = $defAllows;

				$result->free_result();
				$result->next_result();


				//validate cifseqno
				if ($cifnumber != '') {
					$result = $customer->getCustomerById($cifnumber);

					if ($result->num_rows() < 1) {
						$lineError[] = 'CIF number is not yet enrolled';
					}

					$result->free_result();
					$result->next_result();

				} else {
					$lineError[] = 'Invalid CIF number';
				}
 
				//validate account type
				/*if (!in_array($cardBIN, $validCardBINs))
				{
					$lineError[] = 'Invalid card BIN';
				}*/

				//validate count
				/*if (intval($count) > MAX_CARD_REQ)
				{
					$lineError[] = 'Card requests must not be greater than '. MAX_CARD_REQ;
				}*/

				if (count($lineError) > 0) {
					$errors[] = '[Line '. ($number + 1) .'] ' . implode(', ', $lineError);
					continue;
				} else {
					$uploaded++;
				}

				$result = $card->insertAccount(
					$cifnumber,
					$brseqno,
					$prKey,
					$acctType,
					10, //status
					$hex,
					$ipAddress,
					$workstation,
					$userAudit,
					'',
					$sessionID
				);

				$result->free_result();
				$result->next_result();
			}

		} catch (Exception $e) {
			$errors[] = $e->getMessage();
		}

		if (count($errors) > 0) {
			$success = FALSE;
			$message = implode("\n", $errors) . "\n\n";
		}

		$message .= 'Processed: ' . count($lines) . "\n" .
			'Errors: ' . count($errors);

		echo json_encode(array(
			'success' => TRUE,
			'message' => $message,
			'data' => $data
		));
	}
}