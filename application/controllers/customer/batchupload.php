<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class BatchUpload extends CI_Controller {
	
	private $fileVersion = '1.10.00';
	
	function __construct()
	{
		parent::__construct();
		$this->load->library('core');
		$this->core->checkUserAllows(CUSTBATCHUPLOAD_NO);
		
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
		$this->load->view('customer/batchupload');
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
			//end

			$uploaded = 0;
			$report = '';
			foreach ($lines as $number => $line) {
				$row = explode(',', $line);

				//$acctType = intval($row[0]);
				//$prKey = $row[1];
				//$cifnumber = $row[2];

				$cardno  = strval(trim($row[0]));
				$prefix  = strtoupper(strval(trim($row[1])));
				$fname   = strtoupper(strval(trim($row[2])));
				$lname   = strtoupper(strval(trim($row[3])));
				$mname   = strtoupper(strval(trim($row[4])));
				$nickname  = strval(trim($row[5]));
				$birthday  = date('m/d/Y', strtotime($row[6]));
				$nickno  = strval(trim($row[7]));
				$passport  = strval(trim($row[8]));
				$permitno  = strval(trim($row[9]));
				$gender  = $row[10] === 'F' ? 'Female':'Male';
				$cvlstat = strval(trim($row[11]));
				$street1 = strval(trim($row[12]));
				$city 	 = strval(trim($row[13]));
				$zipcode = strval(trim($row[14]));
				$prvince = strval(trim($row[15]));


				$xml = array(
					'4' => '',
					'5' => '',
					'6' => '',
					'7' => '',
					'8' => ''
				);

				$xml1 = '<EMAIL></>';
				$xml1 .= '<ENOT></>';

				$xml2 = '<BIRTHDAY>'.$birthday.'</>';
				$xml2 .= '<BIRTHPLACE></>';
				$xml2 .= '<SSS></>';
				$xml2 .= '<TIN></>';
				$xml2 .= '<OCCUPATION></>';
				$xml2 .= '<RACE></>';
				$xml2 .= '<SEX>'. $gender .'</>';
				$xml2 .= '<CIVIL>'. $cvlstat .'</>';
				$xml2 .= '<NATIONALITY></>';

				$xml3 = '<HPHONE></>';
				$xml3 .= '<OPHONE></>';
				$xml3 .= '<MPHONE></>';
				$xml3 .= '<HNOT></>';
				$xml3 .= '<ONOT></>';
				$xml3 .= '<MNOT></>';

				$xml['4'] = '<HADDRESS>'. $street1 .'</>';
				$xml['4'] .= '<HADDRESS2></>';
				$xml['4'] .= '<HCITY>'. $city .'</>';
				$xml['4'] .= '<HPROV>'. $prvince .'</>';
				$xml['4'] .= '<HZIPCODE>'. $zipcode .'</>';
				$xml['4'] .= '<HCOUNTRY>Philippines</>';
				$xml['4'] .= '<HMAIL></>';


				$lineError = array();

				//validate cifseqno
				/*if ($cardno != '') {
					$result = $card->getCardInfo($cardno, $brseqno, $ipAddress, $workstation, $userAudit);

					$cRow = $result->row_array();
					if ($cRow['prseqno'] === NULL || $cRow['prseqno'] === 0) {
						$lineError[] = 'Card number not found.'.$cardno.$brseqno.$ipAddress.$workstation.$userAudit;
					} else {
						$prseqno = $cRow['prseqno'];
					}

					$result->free_result();
					$result->next_result();

				} else {
					$lineError[] = 'Invalid Card number';
				}*/

				//validate cifseqno
				/*if ($cifnumber != '') {
					$result = $customer->getCustomerById($cifnumber);

					if ($result->num_rows() < 1) {
						$lineError[] = 'CIF number is not yet enrolled';
					}

					$result->free_result();
					$result->next_result();

				} else {
					$lineError[] = 'Invalid CIF number';
				}*/
 
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

				$result = $customer->insertbatchcustomer(
					2,			//cifgrpseqno
					$cardno,			//custkey
					'INDV',		//ciftype
					$prefix,
					$fname,
					$mname,
					$lname,
					'',
					1,			//uniqtype
					'',			//uniqval
					$userAudit,
					'',			//override
					$ipAddress,
					$workstation,	//wkstn
					$xml1,
					$xml2,
					$xml3,
					$xml['4'],
					$xml['5'],
					$xml['6'],
					$xml['7'],
					$xml['8'],
					'',
					0,			//blobsgn
					$brseqno,
					$sessionID
				);

				$row 	= $result->row_array();	
				
				$errNo 	= $row['errno'];
				$errMsg	= $row['errmsg'];

				$cifno	= $row['cifseqno'];
				$data[] = array($errNo,$errMsg);

				$result->free_result();
				$result->next_result();

				if ($errNo > 0) {
					$errors[] = $errMsg.'[Line '.($number + 1).']';
				} else {
					$result = $card->batchcifacctupdate($cardno,$cifno,$brseqno,$ipAddress,$workstation,$userAudit,'',$sessionID);
					$row = $result->row_array();

					$result->free_result();
					$result->next_result();

					if (intval($row['errno']) > 0) {
						$errors[] = '[Line '.($number + 1).'] '.$row['errmsg'];
					} else {
						$report .= $row['errmsg'];
					}
				}
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
			'report' => $report,
			'data' => $data
		));
	}
}