<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Request extends CI_Controller {

	private $fileVersion = '1.10.00';
	private $coreencrypt = FALSE;

	function __construct()
  {
    parent::__construct();
    $this->load->library('core');
    $this->core->checkUserAllows(IPAY_REQ);
    $this->db     = $this->load->database(DB1, TRUE);
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
		$data = array();
    $data['isHeadOffice'] = $this->core->isHeadOffice();

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
    $this->load->model('coreapp/ipay_model');
    $this->load->model('coreapp/branch_model');
    $this->load->library('coreconverters');

    //declare loaded model
    $branch = $this->branch_model;
		$ipay   = $this->ipay_model;
		$cache  = $this->cache;

    $result = $ipay->getFitListXX();
    $fitListXX = $result->result_array();

    $result->free_result();
    $result->next_result();

    $data['recptBankList'] = NULL;

    foreach ($fitListXX as $row)
    {
      $data['recptBankList'] .= '<option value="'. $row['isocode'] .'">'. $row['description'] .'</option>';
    }

		$auditXML    = '';
    $brseqno     = $this->core->getBranchID();
    $userAudit   = $this->core->getUserID();
    $workstation = $this->core->getWorkstation();

		$auditXML .= '<old></><new></><field>Instapay Request</><details>Request</>';
    $this->ipay_model->insertAuditLogclixx(41,'990317',$brseqno,0,0,'IPAY','',$userAudit,'','','','','','','',$userAudit,'','WEB','WEB',$workstation,$auditXML);

		$data['sessionExp'] = $this->core->getSessionExp();

		$this->load->view('instapay/request', $data);
	}

  function submit()
  {
    $success = TRUE;
    $message = '';
    $errors  = array();
    $errno = 0;
    $errstr = '';
    try{

      $input = $this->input;

      $senderAcctNo   = $input->post('senderAcctNo', TRUE);
      $senderAcctType = $input->post('senderAcctType', TRUE);
      $senderAcctName = $input->post('senderAcctName', TRUE);
      $senderAddress  = $input->post('senderAddress', TRUE);

      $recptAcctNo    = $input->post('recptAcctNo', TRUE);
      $recptPurpose   = $input->post('recptPurpose', TRUE);

      $recptAcctName      = $input->post('recptAcctName', TRUE);
      $recptAcctBankCode  = $input->post('recptBankCode', TRUE);

      $tranAmount     = str_replace(array(',','.'), '', $input->post('tranAmount', TRUE));

      $this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
      $this->load->library('shortxml');
      $xmldata = $this->shortxml;

      $this->load->model('coreapp/ipay_model');
      $ipay  = $this->ipay_model;

      $userid      = $this->core->getUserID();
      $brseqno     = $this->core->getBranchID();
      $userAudit   = $this->core->getUserID();
      $workstation = $this->core->getWorkstation();
      $ipAddress   = $this->core->getIPAddress();
      $override    = $this->session->userdata('userOverride');

      $result    = $ipay->getIPayCardParameters();
      $ipayParams = $result->row_array();
      $result->free_result();
      $result->next_result();

      if($ipayParams['errorno'] > 0){

        if($ipayParams['errorno'] == '6500'){
          throw new Exception('IPay card not yet configured');
        }

        if($ipayParams['errorno'] == '6531'){
          throw new Exception('Other Params not found (MallID/MerchantID)');
        }

      }

      $prKey      = $ipayParams['prkey'];
      $prType     = $ipayParams['prtype'];
      $merchantID = $ipayParams['merchantid'];
      $mallID     = $ipayParams['mallid'];
      $userID     = $ipayParams['userid'];
      $userPwd    = $ipayParams['userpass'];

      $result   = $ipay->getIPAYHost();
      $hostInfo = $result->row_array();

      $result   = $ipay->getIPAYPort();
      $portInfo = $result->row_array();

      //configxx - coreashost, ipayport
      $host = $hostInfo['host'];
      $port = $portInfo['port'];

      // for testing
      // $host = '192.168.168.24';
      // $port = 3002;

      $fp    = NULL;
      if (@fsockopen($host, $port, $errno, $errstr, 10)) {
        $fp = fsockopen($host, $port, $errno, $errstr, 10);
      } else {
        throw new Exception("Warning: Unable to connect to port [".$port."]");
      }

      if (!$fp) {
        $success = FALSE;
      } else {

        $out =  '<MSGTYPE>50</>'.
                '<TRXCODE>'.TRXCODE_IPAYREQ.'</>'.
                '<PRTYPE1>'.$prType.'</>'.
                '<PRKEY1>'.$prKey.'</>'.
                '<TPNODE>WEB</>'.
                '<TERMTYPE>WEB</>'.
                '<AMTREQ>'.$tranAmount.'</>'.
                '<ISSCODE>0147</>'.
                '<ACCT1>'.$senderAcctNo.'</>'.
                '<ACCTTYPE1>'.$senderAcctType.'</>';

        $out .= '<XML>'.
                '<CARDPIN>111111</>'.
                '<MERCID>'.$merchantID.'</>'.
                '<MALLID>'.$mallID.'</>'.
                '<USERID>'.$userID.'</>'.
                '<USERPASS>'.$userPwd.'</>'.
                '<SENDERNAME>'.$senderAcctName.'</>'.
                '<ADDR>'.$senderAddress.'</>'.
                '<IP>'.$ipAddress.'</>'.
                '<CXUSER>'.$userid.'</>'.
                '<USEROVERRIDE>'.$override.'</>'.
                '<ACCT2>'.$recptAcctNo.'</>'.
                '<RECPTNAME>'.$recptAcctName.'</>'.
                '<BANKCODE>'.$recptAcctBankCode.'</>'.
                '<PURPOSE>'.$recptPurpose.'</></>';

        $out .= "\x00";
        
        fwrite($fp, $out);
      
        $msg = '';

        $starttime = time();

        while (TRUE) {
          $x = fgets($fp, 2);
          $time = time() - $starttime;
          if ($x === "\x00" ) {
            break;
          } elseif ($time > 300) {
            throw new Exception("Connection Timeout (timelapse: ".$time." Starttime: ".$starttime." Endtime: ".time().")");
            break;
          }

          $msg .= $x;
        }

        $xmlstring = $msg;
        $xmldata->setXML($msg);
        
        fclose($fp);
      }

      if ($success) {
        $msgtype = intval($xmldata->getValue('MSGTYPE'));
        $sysvdesc = $xmldata->getValue('SYSVDESC');
        $sysvcode = intval($xmldata->getValue('SYSVCODE'));

        if ( in_array($msgtype, array(53, 73)) ) {

          if($sysvdesc != ''){
            throw new Exception($sysvdesc);
          }

          if($sysvcode != NULL){
            log_message('error',$xmldata );
            throw new Exception('Unknown error occured: ['.$sysvcode.']');
          }

        }

      }else{
        throw new Exception('Unable to save data.');
      }

      $message = 'Instapay Transaction Complete';

    }catch (Exception $e){
      
      if($errno !== 0 && $errstr != ''){
        $errors[] =  $errstr;
      }else{
        $errors[] = $e->getMessage();
      }

    }

    if(count($errors) > 0){
      $success = FALSE;
      $message = implode(',', $errors);
    }
    
    echo json_encode(array(
      'success' => $success,
      'message' => $message
    ));

  }

}
