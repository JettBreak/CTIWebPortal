<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Upload extends CI_Controller {
	
	private $path, $cardFile;
	
	function __construct()
	{
		parent::__construct();
		$this->load->helper('file');
		$this->load->library('encrypt');
		$this->cardFile = TEMP_PATH .'cardreq.csv';
	}
	
	function index()
	{
		$this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
		$this->load->library('core');
		
		$cache 	 = $this->cache;
		$core    = $this->core;
		$encrypt = $this->encrypt;
		
		//$core->checkUserAllows(CARDUPLOAD_NO);
		
		$data['branchID']  = $core->getBranchID();
		$data['userAudit'] = $core->getUserID();
		$data['sessionID'] = $core->getSessionID();
				
		$authFile = TEMP_PATH . $this->core->getSessionID() .'.csv';
		
		//set session csv
		$fileData = '"USERAUDIT","BRANCHID","SESSIONID","CARDBIN"';
		$fileData .= "\n". 
					'"'. $encrypt->encode($data['userAudit']) .'",'. 
					$encrypt->encode($data['branchID']) 
					.',"'. $encrypt->encode($data['sessionID']) .'",';
		
		if (!$cardBIN = $cache->get($this->core->getSessionID() . 'cardBIN')) {
			$this->load->model('coreapp/card_model');
			// Save into the cache for 5 minutes
			$cardBIN = $this->card_model->getCardBIN()->result_array();
			$cache->save($this->core->getSessionID() . 'cardBIN', $cardBIN, CACHE_TTL);
		}
		
		foreach ($cardBIN as $key => $row) {
			$fileData .= $encrypt->encode($row['codevalue']);
			if ($key !== (count($row['codevalue']) - 1)) {
				$fileData .= "\n,,,";
			}
		}
		
		write_file($authFile, $fileData);
		$this->load->view('card/upload', $data);
	}
	
	function submit()
	{	
		$this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
		$this->load->model('coreapp/card_model');
		$this->load->library('csvreader');
		
		$card      = $this->card_model;
		$csv       = $this->csvreader;
		$encrypt   = $this->encrypt;
		$input     = $this->input;
		$cardFile  = $this->cardFile;
		$sessionID = $input->get('s', TRUE);
		$authFile  = TEMP_PATH . $sessionID .'.csv';
		$img       = 'Filedata';
		
		//init upload config
		$config = array(
			'file_name'		=> 'cardreq',
			'upload_path' 	=> TEMP_PATH,
			'allowed_types' => VALID_CSV_FORMATS,
			'overwrite'		=> TRUE
		);
		
		$this->load->library('upload', $config);

		if ($this->upload->do_upload($img)) {
			//if invalid csv file
			if (!$cardReq = $csv->parse_file($cardFile)) {
				echo json_encode(array(
					'total'		 => 'NA',
					'uploaded' 	 => TRUE,
					'errorDesc'  => 'Invalid CSV file. Please follow the correct format:\n\n&quot;ACCTYPE&quot;,&quot;COUNT&quot;,&quot;CARDBIN&quot;\n00,00,000000\n\n',
					'errorCount' => 'NA',
					'successful' => 'NA'	
				));
				exit();
			}
			$auth    = $csv->parse_file($authFile);
			
			//decode session csv file
			$userAudit = $encrypt->decode($auth[0]['USERAUDIT']);
			$branchID  = $encrypt->decode($auth[0]['BRANCHID']);
			$sessionID = $encrypt->decode($auth[0]['SESSIONID']);
			
			if (in_array('CUSTID', $auth[0])) {
			}
			
			$cardBIN = NULL;
			foreach ($auth as $a) {
				$cardBIN[] = $encrypt->decode($a['CARDBIN']);
			}
			//end
			
			if (!$cardType = $this->cache->get($this->core->getSessionID() . 'cardType')) {
				$result = $card->getCardType('N');
				$cardType = $result->result_array();
				$this->cache->save($this->core->getSessionID() .'cardType', $cardType, CACHE_TTL);
			}
		
			$validTypes = NULL;
			foreach ($cardType as $row) {
				$validTypes[] = $row['accttype'];
			}
			
			$result->free_result();
			$result->next_result();
					
			$errorCount = 0;
			$errorDescx = NULL;
			
			//check all requests
			foreach ($cardReq as $key => $req) {			
				$line	 = $key + 1;
				$accType = $req['ACCTYPE'];
				$count   = $req['COUNT'];
				$bin	 = $req['CARDBIN'];
				
				$validType  = TRUE;
				$validCount = TRUE;
				$validBIN	= TRUE;
				$errorDesc  = NULL;
				
				//validate account type
				if (in_array($accType, $validTypes) === FALSE)
				{
					$validType = FALSE;
					$errorDesc .= '\nInvalid account type';
				}
				//validate card(s) requested
				if (intval($count) > MAX_CARD_REQ)
				{
					$validCount = FALSE;
					$errorDesc .= '\nCard requests must not be greater than '. MAX_CARD_REQ;
				}
				//validate card BIN
				if (in_array($bin, $cardBIN) === FALSE)
				{
					$validBIN = FALSE;
					$errorDesc .= '\nInvalid card BIN';
				}
				//if errors found
				if (($validType === FALSE) || ($validCount === FALSE) || ($validBIN === FALSE)) {
					$errorCount++;
					//set error heading
					$errorHeading = 'Error: Line number ['. $line .']';
					$errorHeading .= '\nACCTYPE: ['. $accType .']';
					$errorHeading .= '\nCOUNT: ['. $count .']';
					$errorHeading .= '\nCARDBIN: ['. $bin .']';
					
					//concat all errors
					$errorDescx .= $errorHeading . $errorDesc .'\n\n';	
				} else {
					$result = $card->insertCardOrder(
						$accType, $branchID, $branchID, 
						$count, $bin, 'N', '', 
						$userAudit, 'override', $sessionID
					);
					$result->free_result();
					$result->next_result();
				}
			}
			
			$totalReq	= count($cardReq);
			$uploaded 	= TRUE;
			$successful	= $totalReq - $errorCount;
		} else {
			$totalReq	= NULL;
			$uploaded   = FALSE;
			$errorDescx = $this->upload->display_errors('', '');	
			$errorCount = NULL;	
			$successful = NULL;
		}
		
		echo json_encode(array(
			'total'		 => $totalReq,
			'uploaded' 	 => $uploaded,
			'errorDesc'  => $errorDescx,
			'errorCount' => $errorCount,
			'successful' => $successful	
		));
	}
}
/* End of file upload.php */
/* Location: ./application/controllers/card/upload.php */
