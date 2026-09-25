<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Verify extends CI_Controller {

	private $fileVersion = '1.10.00';

	function __construct()
  {
    parent::__construct();
    $this->load->library('core');
    $this->core->checkUserAllows(IPAY_CARDENRL);

    $this->coreencrypt = $this->core->isCoreEncrypt();

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


		//load cache driver
		$this->load->driver('cache', array('adapter' => 'file', 'backup' => 'apc'));

		//load model
    $this->load->model('coreapp/card_model');
    $this->load->model('coreapp/branch_model');
    $this->load->model('coreapp/ipay_model');
    $this->load->library('coreconverters');

    //declare loaded model
    $branch = $this->branch_model;
		$card   = $this->card_model;
    $ipay   = $this->ipay_model;
		$cache  = $this->cache;

		$data = array();
    $data['isHeadOffice'] = $this->core->isHeadOffice();

		$data['branchCode'] = $this->core->getBranchCode();
		$data['branchName'] = $this->core->getBranchName();

    $data['cardType']   = NULL;
		$data['cardBIN'] 		= NULL;
		$data['cardNumber'] = NULL;

    $data['mallID'] = NULL;
    $data['merchantID'] = NULL;
    $data['userID'] = NULL; 

    
		//get card bin
		$BIN = '';
    if (!$cardBIN = $cache->get($this->core->getSessionID() . 'cardBINWithFormat')) {
      $result = $card->getCardBINWithFormat();
      $cardBIN = $result->result_array();
      
      $result->free_result();
      $result->next_result();
      
      $cache->save($this->core->getSessionID() . 'cardBINWithFormat', $cardBIN, CACHE_TTL);
    }

    $replace = array('-', 'I');
    
    foreach ($cardBIN as $row)
    {     
      $val = $row['codevalue'];
      $format = str_replace($replace, '', $row['formatvalue']);
      $format = str_replace('C', 'N', $format);
      
      $data['cardBIN'] .= '<option value="'. $val .'" format="'. $format .'">'. $val .'</option>';
    }

    //get product code
    if ($this->core->hasProductCode()) {
      //cache product codes 
      if (!$productCodes = $cache->get($this->core->getSessionID() . 'productCodes')) {
        $this->load->model('coreapp/card_model');
        
        $result = $this->card_model->getProductCodes();
        $productCodes = $result->result_array();
        $cache->save($this->core->getSessionID() . 'productCodes', $productCodes, CACHE_TTL);
        
        $result->free_result();
        $result->next_result();
      }
    }
    //end product code

    $result   = $ipay->getIPayCardType('N');
    $cardType = $result->result_array();
      
    $result->free_result();
    $result->next_result();

    // card type
    $allows = NULL;
    $format 		 		  = NULL;
    $firstformat 		  = NULL;

    foreach ($cardType as $row)
    {
      $val    = $row['accttype'];
      $desc   = $row['description'];
      $prtype = $row['prtype'];
      $format = str_replace($replace, '', $row['formatvalue']);

      if ($firstformat == NULL) {
        $firstformat = $format;
      }
            
      $weights = $row['weights'];
      $result = $this->card_model->getDefaultAllows('CARD', $val, 'CARD');
      $row = $result->row_array();
      
      if ($result->num_rows() > 0) {
        $defAllows = rtrim($this->coreconverters->asciiHexToBin($row['allows']), 0);
      } else {
        $defAllows = 0;
      }
      
      if ($allows === NULL) {
        $allows = $defAllows;
      }
      
      $result->free_result();
      $result->next_result(); 
        
      $data['cardType'] .= '<option value="'. $val .'" prtype="'.$prtype.'" defaultallows="'. $defAllows .'" format="'.$format.'" weights="'.$weights.'">'. $desc .'</option>';
    }

    $format = $firstformat;

    // ----------------
    $bCount = NULL;
    foreach (count_chars($format, 1) as $i => $cnt) {
      if (chr($i) === 'B') {
        $bCount = $cnt;
      }
    }

    $data['hasCheckDigit'] = strpos($format, 'C');

    $mask = str_replace(str_repeat('B', $bCount), str_pad($this->core->getBranchCode(), '0', $bCount, STR_PAD_LEFT), $format);
    
    if ($this->core->hasProductCode()) {
      $pCount = NULL;
      foreach (count_chars($format, 1) as $i => $cnt) {
        if (chr($i) === 'P') {
          $pCount = $cnt;
        }
      }
      $mask = str_replace(str_repeat('P', $pCount), str_pad($productCodes[0]['codeseqno'], '0', $pCount, STR_PAD_LEFT), $mask);
    } else {
      $pCount = NULL;
      foreach (count_chars($format, 1) as $i => $cnt) {
        if (chr($i) === 'P') {
          $pCount = $cnt;
        }
      }
      $mask = str_replace(str_repeat('P', $pCount), str_pad($cardType[0]['accttype'], '0', $pCount, STR_PAD_LEFT), $mask);  
    }

    $placeholder = $mask;
    $data['cardNoMask'] = $mask;
    $data['cardNoPlaceholder'] = $placeholder;

    // get branch list
    $result   = $branch->getBranchList();
    $branches = $result->result_array();
    $result->free_result();
    $result->next_result();
    $this->cache->save($this->core->getSessionID() .'branches', $branches, CACHE_TTL);

    $data['branches'] = '';
    if ($this->core->isHeadOffice() && count($branches) > 0) {
    	foreach ($branches as $row) {
    		//if user branch is not allowed to monitor users from other branches
    		$matched = $row['brseqno'] === $this->core->getBranchID() ? TRUE : FALSE;

    		$seqno = $row['seqno'];
    		$selected = $matched ? 'selected' : NULL;

    		if ($matched) {
          $data['branches'] = '<option value="'. $row['brseqno'] .'" brcode="'. $row['brcode'] .'" seqno="'.$seqno.'">'. $row['brname'] .'</option>';
          break;
        }
			}
		} else {
      $data['branches'] = '<option value="">No Head Office Branch Defined</option>';
    }

		$result    = $ipay->getIPayCardInfo();
		$resultArr = $result->row_array();
		$total     = $result->num_rows(); // get number of rows
		$result->free_result();
    $result->next_result();

		//check if prmaster for instapay is already available
		// $pageView = NULL;
		if(intval($total) > 0){
			
      $data['cardStatus']     = $resultArr['status'];
      $data['cardStatusDesc'] = $resultArr['statusdesc'];
			$data['cardNumber']     = $resultArr['prkey'];

      $result       = $ipay->getConfigxx('IPAYMERC');
      $merchantInfo = $result->row_array();
      $result->free_result();
      $result->next_result();

      $result   = $ipay->getConfigxx('IPAYMALL');
      $mallInfo = $result->row_array();
      $result->free_result();
      $result->next_result();

      $result   = $ipay->getConfigxx('IPAYUSER');
      $userInfo = $result->row_array();
      $result->free_result();
      $result->next_result();

      $data['merchantID'] = $merchantInfo['description'];
      $data['mallID']     = $mallInfo['description'];
      $data['userID']     = $userInfo['description'];

      $data['pageLbl'] = 'Update';
      $data['pageUrl'] = 'instapay/enrollment/updateIPayCard';

      // $pageView = 'instapay/cardinfo';
		}else{
      $data['cardStatus']     = 4;
      $data['cardStatusDesc'] = 'ACTIVE';

			// $pageView = 'instapay/enrollment';
      $data['pageLbl'] = 'Enrollment';
      $data['pageUrl'] = 'instapay/enrollment/newIPayCard';
		}

		$auditXML = '';
    $brseqno     = $this->core->getBranchID();
    $userAudit   = $this->core->getUserID();
    $workstation = $this->core->getWorkstation();

    $auditXML .= '<old></><new></><field>Instapay Card '.$data['pageLbl'].'</><details>Open Module</>';
    $this->card_model->insertAuditLogclixx(41,'990317',$brseqno,0,0,'IPAY','',$userAudit,'','','','','','','',$userAudit,'','WEB','WEB',$workstation,$auditXML);

		$data['sessionExp'] = $this->core->getSessionExp();

		$this->load->view('instapay/enrollment', $data);
	}
}