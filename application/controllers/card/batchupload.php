<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class BatchUpload extends CI_Controller {
	
	private $fileVersion = '1.10.00';
	
	function __construct()
	{
		parent::__construct();
		$this->load->library('core');
		$this->core->checkUserAllows(CARDBATCHUPLOAD_NO);

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
		$this->load->view('card/batchupload');
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


			$core = $this->core;
			$card = $this->card_model;
			$input = $this->input;

			$csv = $input->post('csv', TRUE);

			$lines = explode( "\n", $csv );

			$userAudit 	 = $core->getUserID();
			$ipAddress	 = $core->getIPAddress();
			$workstation = $core->getWorkstation();
			$brseqno	 = $core->getBranchID();
			$sessionID	 = $core->getSessionID();

			//save accttypes
			if (!$cardType = $this->cache->get($this->core->getSessionID() . 'cardType')) {
				$result = $card->getCardType('N');
				$cardType = $result->result_array();

				$result->free_result();
				$result->next_result();

				$this->cache->save($this->core->getSessionID() .'cardType', $cardType, CACHE_TTL);
			}
		
			$validTypes = array();
			foreach ($cardType as $row) {
				$validTypes[] = intval($row['accttype']);
			}
			//end

			//save cardBIN
			if (!$cardBIN = $this->cache->get($this->core->getSessionID() . 'cardBIN')) {
				$this->load->model('coreapp/card_model');
				
				$result = $this->card_model->getCardBIN();
				$cardBIN = $result->result_array();
				$this->cache->save($this->core->getSessionID() . 'cardBIN', $cardBIN, CACHE_TTL);
				
				$result->free_result();
				$result->next_result();
			}
			
			$validCardBINs = array();
			foreach ($cardBIN as $row)
			{
				$validCardBINs[] = $row['codevalue'];
			}
			//end

			$uploaded = 0;
			foreach ($lines as $number => $line) {
				$row = explode(',', $line);

				$acctType = intval($row[0]);
				$cardBIN = $row[1];
				$count = intval($row[2]);

				$lineError = array();

				//validate account type
				if (!in_array($acctType, $validTypes))
				{
					$lineError[] = 'Invalid account type';
				}

				//validate account type
				if (!in_array($cardBIN, $validCardBINs))
				{
					$lineError[] = 'Invalid card BIN';
				}

				//validate count
				if (intval($count) > MAX_CARD_REQ)
				{
					$lineError[] = 'Card requests must not be greater than '. MAX_CARD_REQ;
				}

				if (count($lineError) > 0) {
					$errors[] = '[Line '. ($number + 1) .'] ' . implode(', ', $lineError);
					continue;
				} else {
					$uploaded++;
				}

				$result = $this->card_model->insertCardOrder(
					$acctType,
					NULL,
					$brseqno,
					$brseqno, 
					$count,
					$cardBIN,
					'N',//is personalized?
					'',
					'',
					$ipAddress,
					$workstation, 
					$userAudit,
					NULL,
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
			'success' => $success,
			'message' => $message,
			'data' => $data
		));
	}
}