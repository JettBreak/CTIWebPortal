<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Info extends CI_Controller {
  
  function __construct()
  {
    parent::__construct();
    $this->load->library('core');
    if ($this->core->checkUserAllowsBtn(CARDINFO)) {
      $this->core->checkUserAllows(CARDINFO);
    } elseif ($this->core->checkUserAllowsBtn(CARDUPDATE_NO)) {
      $this->core->checkUserAllows(CARDUPDATE_NO);
    }


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
    //$this->load->library('session');
    $this->load->library('shortxml');
    $this->load->library('coreconverters');
    
    $card  = $this->card_model;
    $cache   = $this->cache;
    //$session = $this->session;
    $core    = $this->core;
    $xml   = $this->shortxml;
    
    //$info    = $session->userdata('cardInfo');
    $info = $_SESSION['cardInfo'];
    if (!$info) {
      $this->load->helper('url');
      redirect('welcome');
      exit();
    }
    //$data['prseqno'] = $info['prseqno'];
    $data['cardNo'] = $info['cardNo'];
    //$data['cardNox'] = substr($info['cardNo'], -7);
    
    $xml->setXML($info['xml1']);
      
    $data['cardType'] = $info['cardType'];
    $data['prseqno'] = $info['prseqno'];
    $data['defFastCashVal'] = $xml->getValue('FCASH') ? $core->currency($xml->getValue('FCASH')) : '0.00';
    $data['defFastCashAttr'] = NULL;
    $data['branchID']   = str_pad($core->getBranchID(), 3, '0', STR_PAD_LEFT);
    $data['brseqno']    = $info['brseqno'];
    
    $data['cifseqno']   = $info['cifseqno'] ? $info['cifseqno'] : 0;
    $data['custName']     = utf8_decode($info['custName']);
    $data['embossName']   = $xml->getValue('MBOS');
    $cardStatusDesc     = $info['cardStatDesc'];
    $data['cardStatus']   = $cardStatusDesc;
    $data['dtInitIssue']  = $core->formatDate('F j, Y', $info['dateInitIssue']);
    $data['issueCount']   = $info['issueCount'];
    
    $data['dtActivated']  = $info['dateActivated'] ? $core->formatDate('F j, Y', $info['dateActivated']) : 'NOT YET ACTIVATED';
    
    $data['dtExpiry']     = $info['dateExpiry'];
    $data['lastActivity']   = $info['lastActivity'] ? $info['lastActivity'] : 'NONE';
    
    $data['branch']     = $info['brname'] ? $info['brname'] : 'Unknown';
    
    //card type
    if (!$cardType = $cache->get($this->core->getSessionID() . 'cardType')) {     
      $result = $card->getCardType('N');
      $cardType = $result->result_array();
      
      $result->free_result();
      $result->next_result();
        
      $cache->save($this->core->getSessionID() . 'cardType', $cardType, CACHE_TTL);
    }
    
    $data['cardType'] = NULL;
    foreach ($cardType as $row)
    {
      $selected = intval($info['acctType']) === intval($row['accttype']) ? ' selected' : ' disabled';
      $data['cardType'] .= '<option value="'. $row['accttype'] .'"'. $selected .'>'. $row['description'] .'</option>';
    }
    //end
    
    $data['p1'] = NULL;
    $data['productCode'] = NULL;
    $data['ronly'] = $info['readonly'] == TRUE ? '0' : '0';
    
    if ($this->core->hasProductCode()) {
      
      if (!$productCodes = $cache->get($this->core->getSessionID() . 'productCodes')) {
        $this->load->model('coreapp/card_model');
        
        $result = $this->card_model->getProductCodes();
        $productCodes = $result->result_array();
        $cache->save($this->core->getSessionID() . 'productCodes', $productCodes, CACHE_TTL);
        
        $result->free_result();
        $result->next_result();
      }
      
      $prcdcode = $xml->getValue('PRCDCODE');
      $prcddesc = NULL;
      $data['productCode'] = $prcdcode;
      
      foreach ($productCodes as $row)
      {
        if ($prcdcode === $row['codeseqno']) {
          $prcddesc = strtoupper($row['codevalue']);
          break;
        }
      }
      
      $prcddesc = $prcddesc ? $prcddesc : 'Unknown';
       
      $data['p1'] = '<tr>
        <td>Product Code:</td>
        <td colspan="3">
          <input type="text" style="width:200px" value="'. $prcddesc .'" readonly/>
        </td>
      </tr>';
    }
    
    //tran allows
    $result = $card->getDefaultTranAllows('CARD');
    
    $allows = $this->coreconverters->asciiHexToBin($info['allows']);
    $data['tranAllows'] = NULL;
    foreach ($result->result_array() as $row) {
      $checked = substr($allows, $row['bitno'] - 1, 1) === '1' ? ' checked' : NULL;
      $data['tranAllows'] .= '<input type="checkbox" id="bit'. $row['bitno'] .'" name="allows[]" value="'. $row['bitno'] .'"'. $checked .' disabled/>'. $row['description'] .'<br />';
    }

    $result->free_result();
    $result->next_result();
    //end
    
    //channel locking   
    $result = $card->getChannelLocks();
    
    $data['allows'] = NULL;
    foreach ($result->result_array() as $row) {
      $checked = $xml->getValue($row['xml1']) === 'Y' ? ' checked' : NULL;
      $data['allows'] .= '<input type="checkbox" name="ch'. $row['xml1'] .'"'. $checked .' disabled />'. $row['codevalue'] .'<br />';
    }
    
    $result->free_result();
    $result->next_result();
    //end
    
    //def fast cash
    $result = $card->getDefFastCash();
    $row = $result->row_array();
    
    $data['defFastCash'] = NULL;
    $data['defFastCashAttr'] = ' disabled';
      
    if ($row['dfc'] === 'Y') {
      $data['defFastCashAttr'] = NULL;
    }
    
    foreach ($this->_getDefFastCash() as $fType => $fDesc) {
      if ($row['dfc'] === 'Y' && $fType == 2) {
        break;
      }
      $selected = intval($xml->getValue('FTYPE')) === $fType ? ' selected' : NULL;
      $data['defFastCash'] .= '<option value="'. $fType .'"'. $selected .'>'. $fDesc .'</option>';
    }
    
    $result->free_result();
    $result->next_result();
    //end
    
    //card status
    if (!$cardStatus = $cache->get($this->core->getSessionID() . 'cardStatus')) {     
      $result = $card->getCardStatus();
      $cardStatus = $result->result_array();
      
      $result->free_result();
      $result->next_result();
      
      $cache->save($this->core->getSessionID() . 'cardStatus', $cardStatus, CACHE_TTL);
    }
    $cardStatusx = array();
    
    foreach ($cardStatus as $key => $row) {
      $status = $row['status'];
      $desc = $row['description'];
      $acctType = $row['accttype'];
      $isEditable = $row['iseditable'];
      
      if (intval($info['acctType']) === intval($acctType) && $isEditable === 'Y') {
        $cardStatusx[$status]['description'] = $desc;
        $cardStatusx[$status]['acctTypes'][$acctType] = array('isEditable' => $isEditable);
      }
    }
    
    $cardStatus = $info['cardStatus'];
    if ( in_array($cardStatus, array_keys($cardStatusx)) ) {
      $data['cardStatus'] = '<select name="cardStatus" id="cardStatus" style="width:212px">';
      foreach ($cardStatusx as $status => $row) {
        $desc = $row['description'];
        $selected = intval($cardStatus) === intval($status) ? ' selected' : NULL;
        $data['cardStatus'] .= '<option value="'. $status .'"'. $selected .'>'. strtoupper($desc) .'</option>';
      }
      $data['cardStatus'] .= '</select>';
    } else {
      $data['cardStatus'] = '<input type="hidden" name="cardStatus" value="'. $cardStatus .'" readonly/>';
      $data['cardStatus'] .= '<input type="text" id="cardStatus" style="width: 200px" value="'. $cardStatusDesc .'" readonly/>';
    }
    //end
    
    //$data['cardStatus'] = array_key_exists('10', $cardStatusx) ? 'Y' : 'N';
    
    //accounts linked
    $userAudit = $core->getUserID();
    $sessionID = $core->getSessionID();
    
    $result = $card->getCardAccountLink($info['prseqno'], $userAudit, $sessionID);
    
    $data['accountLink'] = NULL;
    
    foreach ($result->result_array() as $row)
    {
      $xml->setXML($row['xml1']);
      
      $num    = $row['prptr'];
      $accntType  = $xml->getValue('ACCTTYPE');
      $accntNo  = $xml->getValue('ACCTNO');
      $authType   = $xml->getValue('AUTHTYPE');
      $primary  = $xml->getValue('PRIMARY');
      
      $data['accountLink'] .= '<tr>'.
        '<td>'. $num .'</td>'.
        '<td>'. $accntType .'</td>'.
        '<td>'. $accntNo .'</td>'.
        '<td>'. $authType .'</td>'.
        '<td>'. $primary .'</td>'.
      '</tr>';
    }
    
    $result->free_result();
    $result->next_result();
    //end
    
    //bills payment
    $result = $card->getCardBillsLink($info['prseqno'], $userAudit, $sessionID);
    
    $data['billsLink'] = NULL;    
    $parentx = NULL;  
    
    foreach ($result->result_array() as $row)
    {
      $xml->setXML($row['xml1']);
      
      $inst     = $row['parent'];
      $ptr    = $row['bpayptr'];
      $subsNo   = $row['subscriberno'];
      $subsName   = $row['subscribername'];
      $parent   = $row['parentseqno'];
      
      if ($parent != $parentx) {
        $parentx  = $parent;
        $data['billsLink'] .= '<tr id="'. $parent .'">'.
          '<td>'. $inst.'</td>'.
          '<td></td>'.
          '<td></td>'.
          '<td></td>'.
          '<td></td>'.
        '</tr>';
      }
        
      $data['billsLink'] .= '<tr idref="'. $parent .'">'.
        '<td>'. $inst.'</td>'.
        '<td>'. $ptr .'</td>'.
        '<td>'. $subsNo .'</td>'.
        '<td>'. $subsName .'</td>'.
        '<td>'. $parent .'</td>'.
      '</tr>';
    }
    
    $result->free_result();
    $result->next_result();
    //end
    
    //online limits
    $result = $card->getCardLimitList($info['prseqno']);
    
    $data['onlineLimits'] = NULL;   
    
    $limitseqno = NULL;
    foreach ($result->result_array() as $row)
    {
      if ($row['isamtlimit'] === 'N') {
        $cycleAvail = 'N/A';
        $cycleMax = 'N/A';
        $tranMin = 'N/A';
        $tranMax = 'N/A';
        $nonFeeTranAvail = 'N/A';
        $nonFeeTranMax = 'N/A';
      } else {
        $cycleAvail = $core->currency($row['cycleavail']);
        $cycleMax = $core->currency($row['cyclemax']);
        $tranMin = $core->currency($row['tranmin']);
        $tranMax = $core->currency($row['tranmax']);
        $nonFeeTranAvail = $core->currency($row['nonfeetranavail']);
        $nonFeeTranMax = $core->currency($row['nonfeetranmax']);
      }
      
      if ($limitseqno === NULL) {
        $limitseqno = $row['limitseqno'];
      }
      
      $data['onlineLimits'] .= '<tr>'.
        '<td>'. $row['description'] .'</td>'.
        '<td>'. $row['limitseqno'] .'</td>'.
        '<td>'. $row['trxcode'] .'</td>'.
        '<td>'. $cycleAvail .'</td>'.
        '<td>'. $cycleMax .'</td>'.
        '<td>'. ($row['ctravail'] ? $row['ctravail'] : 0) .'</td>'.
        '<td>'. ($row['ctrmax'] ? $row['ctrmax'] : 0) .'</td>'.
        '<td>'. $tranMin .'</td>'.
        '<td>'. $tranMax .'</td>'.
        '<td>'. ($row['cycle'] ? $row['cycle'] : 0) .'</td>'.
        '<td>'. ($row['duralimit'] ? $row['duralimit'] : 0) .'</td>'.
        '<td>'. ($row['nonfeectravail'] ? $row['nonfeectravail'] : 0) .'</td>'.
        '<td>'. ($row['nonfeectrmax'] ? $row['nonfeectrmax'] : 0) .'</td>'.
        '<td>'. $nonFeeTranAvail .'</td>'.
        '<td>'. $nonFeeTranMax .'</td>'.
        '<td>'. ($row['nonfeecycle'] ? $row['nonfeecycle'] : 0) .'</td>'.
      '</tr>';
    }
    
    $result->free_result();
    $result->next_result();
    //end
    
    //card limits
    $result = $card->getCardLimits($info['acctType'], 'ONLN', $info['brseqno']);
    
    $data['limitID'] = NULL;
    
    $data['pinRetryCnt'] = $info['pinctr'];
    $data['pinMaxRetryCnt'] = $info['pinctrmax'];
    
    $data['limitDesc'] = 'No Limits Defined';
    
    $data['limitID'] .= '<option value="" pinmaxretry="0">Select Limits</option>';
      
    foreach ($result->result_array() as $row)
    {
      if ($row['limitseqno'] === $limitseqno) {
        $data['limitID'] = NULL;
      }
    }
    
    foreach ($result->result_array() as $row)
    {
      if ($row['limitseqno'] === $limitseqno) {
        //$data['limitDesc'] = 'Online Limits ['. $row['description'] .']';
        $data['limitDesc'] = 'Online Limits';
        $selected = ' selected';
        $attrib = NULL;
      } else {
        $selected = NULL;
        $attrib = " disabled";
      }
      
      $data['limitID'] .= '<option value="'. $row['limitseqno'] .'" pinmaxretry="'. $row['pinctrdef'] .'"'. $selected . '>'. $row['description'] .'</option>';
    }
    
    
    $result->free_result();
    $result->next_result();
    //end
    
    //reset limits button

    $data['readonly'] = 'disabled';
    if ($this->core->checkUserAllowsBtn(CARDUPDATE_NO) && $info['module'] != 'cardissuanceapproval') {
      $info['readonly'] = FALSE;
      $data['readonly'] = '';
    } elseif ($info['readonly'] == FALSE && !$this->core->checkUserAllowsBtn(CARDUPDATE_NO)) {
      $info['readonly'] = TRUE;
      $data['readonly'] = 'disabled';
    }

    $updatebtn = '<button id="updateBtn" value="card/info/submit">Update</button>';
    $searchbtn = '<button id="searchBtn">Search</button>';
    $browsebtn = '<button id="browseBtn">Browse</button>';
    $modifybtn = '<button id="modifyBtn" class="hidden" disabled>Modify</button>';
    $resetbtn = '<button id="resetBtn" type="reset">Reset</button>';
    $emvButton = '<button id="emvButton" class="hidden">Update EMV</button>';
    $closebtn = '<button id="clsebtn" class="closebtn">Close</button>';
    $cardhist = '<button id="cardhist">Export History</button>';

    if ($info['module'] == 'cardissuanceapproval') {
      $closebtn2 = '<button id="clbtn">Close</button>';
    } else {
      $closebtn2 = '<button id="clsebtn" class="closebtn">Close</button>';
    }

    $grpseqno = $core->getUserGroup();
    $hiddenBtn = '<button id="resetLimitsBtn" class="hidden">Reset PIN Settings</button>';

    $data['updatebtn'] = $info['readonly'] == FALSE ? $updatebtn : NULL;
    $data['searchbtn'] = $info['readonly'] == FALSE ? $searchbtn : NULL;
    $data['browsebtn'] = $info['readonly'] == FALSE ? $browsebtn : NULL;
    $data['modifybtn'] = $info['readonly'] == FALSE ? $modifybtn : NULL;
    $data['resetbtn'] = $info['readonly'] == FALSE ? $resetbtn : NULL;
    $data['closebtn'] = $info['readonly'] == FALSE ? $closebtn : $closebtn2;
    $data['cardhist'] = $info['readonly'] == FALSE ? $cardhist : $cardhist;
    $data['emvButton'] = $info['readonly'] == FALSE ? $emvButton : NULL;

    if ($info['readonly'] == FALSE) {
      $data['resetLimitsBtn'] = in_array($grpseqno, array(1,2)) ? $hiddenBtn : NULL;
    } else {
      $data['resetLimitsBtn'] = NULL;
    }

    $brseqno = $this->core->getBranchID();
    $userAudit = $this->core->getUserID();
    $ipAddress = $this->core->getIPAddress();
    $workstation = $this->core->getWorkstation();
    $override = '';

    $emvresult = $this->card_model->getEMVFormatPerCard(
      $info['prseqno'],
      $info['brseqno'],
      $ipAddress,
      $workstation,
      $userAudit,
      $override,
      $sessionID
    );

    $emvrow = $emvresult->row_array();

    $data['trnExpr'] = isset($emvrow['isExpireDate']) ? ($emvrow['isExpireDate'] == 1 ? 'checked' : NULL) : NULL;
    $data['trnICVV'] = isset($emvrow['isCheckICVV']) ? ($emvrow['isCheckICVV'] == 1 ? 'checked' : NULL) : NULL; 
    $data['servICVV'] = isset($emvrow['ICVV']) ? $emvrow['ICVV'] : 0;
    $data['trnCVV'] = isset($emvrow['isCheckCVV']) ? ($emvrow['isCheckCVV'] == 1 ? 'checked' : NULL) : NULL; 
    $data['servCVV'] = isset($emvrow['CVV']) ? $emvrow['CVV'] : 0;
    $data['trnARQC'] = isset($emvrow['isVerifyARQC']) ? ($emvrow['isVerifyARQC'] == 1 ? 'checked' : NULL) : NULL; 
    $data['trnARPC'] = isset($emvrow['isGenerateARPC']) ? ($emvrow['isGenerateARPC'] == 1 ? 'checked' : NULL) : NULL; 
    $data['trnCntr'] = isset($emvrow['isSendTransactionCounter']) ? ($emvrow['isSendTransactionCounter'] == 1 ? 'checked' : NULL) : NULL; 

    $data['trnTrack2'] = isset($emvrow['isUseTrack2']) ? ($emvrow['isUseTrack2'] == 1 ? 'checked' : NULL) : NULL;
    $data['trnICCT2'] = isset($emvrow['isUseICCTrack2']) ? ($emvrow['isUseICCTrack2'] == 1 ? 'checked' : NULL) : NULL;

    $auditXML = '';

    $auditXML .= '<old></><new></><field>Card View|Update</><details>Open Module</>';
    $this->card_model->insertAuditLogclixx(41,'990317',$brseqno,0,0,'CARD','',$userAudit,'','','','','','','',$userAudit,'','WEB','WEB',$workstation,$auditXML);


    //
    $this->load->view('card/info', $data);
  }
  
  function _getDefFastCash()
  {
    return array(
      0 => 'SAVINGS',
      1 => 'CURRENT',
      2 => 'BY ACCT PTR.'
    );
  }
  
  function submit()
  {
    $this->load->model('coreapp/card_model');
    //$this->load->model('coresys/reports_model');
    $this->load->library('coreconverters');
    $this->load->library('shortxml');
    //$this->load->library('session');
    
    //$info = $this->session->userdata('cardInfo');
    $core = $this->core;
    $input = $this->input;
    $xml2 = $this->shortxml;
    $branchID = $core->getBranchID();
    
    $prseqno = $input->post('prseqno', TRUE);
    $cifseqno = $input->post('cifseqno', TRUE);
    $acctType = $input->post('acctType', TRUE);
    $emboss = $input->post('embossName', TRUE);
    
    $status = $input->post('cardStatus', TRUE);
    
    $allows = $this->input->post('allows');

    //$info    = $session->userdata('cardInfo');
    $info = $_SESSION['cardInfo'];
    if (!$info) {
      $this->load->helper('url');
      redirect('welcome');
      exit();
    }
    
    if ($allows) {
      $max = max($allows);
      $bin = '';
      for ($i = 1; $i <= $max; $i++) {  
        if (in_array($i, $allows)) {
          $bin .= '1';
        } else {
          $bin .= '0';
        }
      }
    } else {
      $bin = '0';
    }

    //tran allows
    $result = $this->card_model->getDefaultTranAllows('CARD');
    
    $oldbin = $this->coreconverters->asciiHexToBin($info['allows']);
    
    $hex = $this->coreconverters->asciiBinToHex($bin);

    $xml1 = NULL;
    foreach ($result->result_array() as $row) {
      if (substr($bin, $row['bitno'] - 1, 1) !== substr($oldbin, $row['bitno'] - 1, 1)) {
        $checked = substr($bin, $row['bitno'] - 1, 1) === '1' ? 'Y' : 'N';
        $xml1 .= '<'.str_replace(' ', '', $row['description']).'>'.$checked.'</>';
      }
    }

    $result->free_result();
    $result->next_result();

    $result = $this->card_model->getCurrentCardInfo($prseqno);

    $oldcardinfo = $result->row_array();

    if (count($oldcardinfo) == 0) {
      $oldcardinfo = $info;
    } 


    $result->free_result();
    $result->next_result();
    
    //$initAllows = $hex;//'DFFFFFFBFFFF0000';//$input->post('initAllows', TRUE);
    $fCash = str_replace(',', '', $core->currency($input->post('fCash', TRUE))); //format 0.00
    $fType = $input->post('fType', TRUE);
    $fPtr = 0;
    
    $atmLock = $input->post('chATMLOCK', TRUE) ? 'Y' : 'N';
    $posLock = $input->post('chPOSLOCK', TRUE) ? 'Y' : 'N';
    $webLock = $input->post('chWEBLOCK', TRUE) ? 'Y' : 'N';
    $cellLck = $input->post('chCELLLOCK', TRUE) ? 'Y' : 'N';
    
    $xml = '<ATMLOCK>'. $atmLock .'</>'.
      '<POSLOCK>'. $posLock .'</>'.
      '<WEBLOCK>'. $webLock .'</>'.
      '<CELLLOCK>'. $cellLck .'</>';

/*    $xml2->setXML($info['xml1']);

    if ($atmLock != $xml->getValue('ATMLOCK')) {

    }*/
    
    //if INST has card product code
    if ($this->core->hasProductCode()) {
      $prcdcode = $input->post('productCode', TRUE);
      
      $xml .= '<PRCDCODE>'. $prcdcode .'</>';
    }
    
    $brseqno = $core->getBranchID();
    $ipAddress = $core->getIPAddress();
    $workstation = $core->getWorkstation();
    $userAudit = $core->getUserID();
    $override = '';
    $sessionID = $core->getSessionID();
    
    $result = $this->card_model->updateCard(
      $prseqno,
      $cifseqno,
      $acctType,
      $emboss,
      $status,
      $info['allows'],//$hex,
      $fCash,
      $fType,
      $fPtr,
      $xml,
      $brseqno,
      $ipAddress,
      $workstation,
      $userAudit,
      $override,
      $sessionID
    );
    
    $row = $result->row_array();
    
    if ($row['errno'] > 0) {
      $success = FALSE;
      $message = $row['errmsg'];
    } else {
      $success = TRUE;
      $message = 'Card successfully updated';

      $item = $oldcardinfo;
      $auditXML = '';

      $item['accttype'] = isset($item['accttype']) ? $item['accttype'] : NULL; 

      if ($item['accttype'] != $acctType) {
        $auditXML .= '<old>'.$item['accttype'].'</><new>'.$acctType.'</><field>Card type</><prseqno>'.$prseqno.'</><BRSEQNO>'.$brseqno.'</>';
        $this->card_model->insertAuditLogclixx(41,'990317',$brseqno,0,0,'CARD','',$info['cardNo'],'','','','','','','',$userAudit,'','WEB','WEB',$workstation,$auditXML);
      }

      $item = $oldcardinfo;
      $auditXML = '';

      $item['MBOS'] = isset($item['MBOS']) ? $item['MBOS'] : NULL; 

      if ($item['MBOS'] != $emboss) {
        $auditXML .= '<old>'.$item['MBOS'].'</><new>'.$emboss.'</><field>Display Name</><BRSEQNO>'.$brseqno.'</>';
        $this->card_model->insertAuditLogclixx(41,'990317',$brseqno,0,0,'CARD','',$info['cardNo'],'','','','','','','',$userAudit,'','WEB','WEB',$workstation,$auditXML);
      }

      $item = $oldcardinfo;
      $auditXML = '';
      
      $item['cifseqno'] = isset($item['cifseqno']) ? $item['cifseqno'] : NULL; 

      if ($item['cifseqno'] != $cifseqno) {
        $auditXML .= '<old>'.$item['cifseqno'].'</><new>'.$cifseqno.'</><field>Customer Link</><cif>1</><BRSEQNO>'.$brseqno.'</>';
        $this->card_model->insertAuditLogclixx(41,'990317',$brseqno,0,0,'CARD','',$info['cardNo'],'','','','','','','',$userAudit,'','WEB','WEB',$workstation,$auditXML);
      }

      $item = $oldcardinfo;
      $auditXML = '';
      
      $item['status'] = isset($item['status']) ? $item['status'] : NULL; 
      
      if ($item['status'] != $status) {

        $stat = $this->card_model->getcardstatusdesc($acctType,$status);
        $desc = $stat->row_array();

        $stat->free_result();
        $stat->next_result();

        $item['description'] = isset($item['description']) ? $item['description'] : NULL; 

        $auditXML .= '<old>'.$item['description'].'</><new>'.$desc['description'].'</><field>Card status</><BRSEQNO>'.$brseqno.'</>';
        $this->card_model->insertAuditLogclixx(41,'990317',$brseqno,0,0,'CARD','',$info['cardNo'],'','','','','','','',$userAudit,'','WEB','WEB',$workstation,$auditXML);
      }

      unset($_SESSION['cardInfo']);
    }
    
    echo json_encode(array(
      'success' => $success,
      'message' => $message,/*,
      'errorno' => $row['errno'].'|'.$item['status'].'|'.$status
      'hex' => $hex,
      'xml' => $xm
      ,'prseqno' => $prseqnol*/
    ));
  }
  
  function resetPIN()
  {
    $this->load->model('coreapp/card_model');
    
    $prseqno = $this->input->post('prseqno', TRUE);
    $brseqno = $this->core->getBranchID();
    $ipAddress = $this->core->getIPAddress();
    $workstation = $this->core->getWorkstation();
    $userAudit = $this->core->getUserID();
    $override = '';
    $sessionID = $this->core->getSessionID();
    
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
    
    echo json_encode(array(
      'success' => $success,
      'message' => $message
    ));
  }

  function getCardHistory()
  {
    $this->load->model('coreapp/reports_model');
    $this->load->library('zip');
    
    $prseqno = $this->input->get('pr', TRUE);
    $brseqno = $this->input->get('br', TRUE);
    $cardxxx = $this->input->get('cr', TRUE);
    $ipAddress = $this->core->getIPAddress();
    $workstation = $this->core->getWorkstation();
    $userAudit = $this->core->getUserID();
    $override = '';
    $sessionID = $this->core->getSessionID();

    $result = $this->reports_model->getCardLink($cardxxx);
    $lastresult = $result->result_array(); 

    $result->free_result();
    $result->next_result();   

    $files = NULL;

    foreach($lastresult AS $rowx) {
      $tokenid = $rowx['TokenID'];
      $file = $rowx['prkey'];

      if (intval($prseqno) == 0 && intval($brseqno) == 0 ) {
        $result = $this->reports_model->getCardHistory(
          $prseqno,
          $brseqno,
          $tokenid,
          $workstation,
          $userAudit,
          $cardxxx,
          $sessionID
        );
      }/* else {
        $result = $this->reports_model->getCardHistory(
          $prseqno,
          $brseqno,
          $tokenid,
          $workstation,
          $userAudit,
          $cardxxx,
          $sessionID
        );
      }*/
      
      $cardresult = $result->result_array();

      $data = ''."\r\n";
      $cardno = 'unknown';
      if (count($cardresult) > 0) {
        $header = 'Tran Date,Tran Time,Acquirer Code,Terminal Number,Transaction Type,Account Type,Account Number,Amount,Transferee Account,'.
            'Sequence Number,Trace Number,Response Code'."\r\n";

        $data = $header;
        foreach ($cardresult AS $row) {
          $logseqno   = $row['logseqno'];
          $trandate   = date('mdY', strtotime($row['dtlog']));
          $trantime   = date('His', strtotime($row['dtlog']));
          $acqcode  = intval($row['acqcode']);
          $termcode = $row['termcode'];
          $trxtype1 = $row['trxtype1'];
          $accttype   = $row['accttype1'];
          $acctno1  = $row['acct1'];
          $amtath   = str_replace(',', '', $row['amtath']);
          $acctno2  = $row['acct2'];
          $tpseqno  = $row['tpseqno'];
          $chseqno  = $row['chseqno'];
          $msgtype  = $row['msgtype'];
          $cardno   = $row['cardno'];

          $data .= $trandate.','.$trantime.','.$acqcode.','.$termcode.','.$trxtype1.','.$accttype.','.$acctno1.','.$amtath.','.$acctno2.','.$tpseqno.','.$chseqno.','.$msgtype."\r\n";
        }
        $data .= 'End of Report';

      }

      $result->free_result();
      $result->next_result();

      $files = array(
        $file .'_'. date('mdYHis') . '.csv' => $data
      );
    
      $this->zip->add_data($files);
    }


    /*if (intval($row['errno']) > 0) {
      $success = FALSE;
      $message = 'An error has occured';
    } else {
      $success = TRUE;
      $message = 'PIN Retry Counter was reset';
    }*/
    /*
    $files = array(
      'CARD_HISTORY_'. date('mdYHis') . '.csv' => $data
    );*/

    //$this->zip->add_data($files);
    $this->zip->download('CARD_HISTORY_'. date('mdYHis')  . '.zip'); 




    /*header('Content-type: application/csv';
    header('Content-Disposition: attachment; filename='. 'CARD_HISTORY_'.$cardno.'.zip';
    header('Pragma: no-cache');
    header('Expires: 0');

    echo $data;*/
    /*
    echo json_encode(array(
      'success' => $success,
      'message' => $message,
      'params' => $brseqno
    ));*/
  }

  function demo()
  {
    echo json_encode(array(
      'success' => TRUE,
      'message' => 'This is a demo process.'
    ));
  }

  function updateemvcard()
  {
    $this->load->model('coreapp/card_model');
    
    $brseqno = $this->core->getBranchID();
    $ipAddress = $this->core->getIPAddress();
    $workstation = $this->core->getWorkstation();
    $userAudit = $this->core->getUserID();
    $override = '';
    $sessionID = $this->core->getSessionID();

    $prseqno   = $this->input->post('prq', TRUE);
    $trnTrack2 = $this->input->post('tk2', TRUE);
    $trnICCT2  = $this->input->post('icc', TRUE);
    $trnExpr   = $this->input->post('exp', TRUE);
    $trnCVV    = $this->input->post('cvv', TRUE);
    $servCVV   = $this->input->post('cvd', TRUE);
    $trnICVV   = $this->input->post('icv', TRUE);
    $servICVV  = $this->input->post('icd', TRUE);
    $trnARQC   = $this->input->post('arq', TRUE);
    $trnARPC   = $this->input->post('arp', TRUE);
    $trnCntr   = $this->input->post('txc', TRUE);
    
    $result = $this->card_model->updateCardAsEMV(
      $prseqno,
      $trnTrack2,
      $trnICCT2,
      $trnICVV,
      $servICVV,
      $trnCVV,
      $servCVV,
      $trnARQC,
      $trnARPC,
      $trnExpr,
      $trnCntr,
      $ipAddress,
      $workstation,
      $userAudit,
      $override,
      $sessionID
    );/*

    var_dump(implode(',',array($prseqno,
      $trnTrack2,
      $trnICCT2,
      $trnICVV,
      $servICVV,
      $trnCVV,
      $servCVV,
      $trnARQC,
      $trnARPC,
      $trnExpr,
      $trnCntr,
      $ipAddress,
      $workstation,
      $userAudit,
      $override,
      $sessionID)));*/
    
    $row = $result->row_array();
    
    if ($row['ERRNO'] > 0) {
      $success = FALSE;
      $message = 'An error has occured';
    } else {
      $success = TRUE;
      $message = 'Card successfully updated';
    }
    
    echo json_encode(array(
      'success' => $success,
      'message' => $message
    ));
  }
}
/* End of file info.php */
/* Location: ./application/controllers/card/info.php */