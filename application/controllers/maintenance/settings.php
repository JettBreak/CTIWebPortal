<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Settings extends CI_Controller {
  
  function __construct()
  {
    parent::__construct();
    $this->load->library('core');
    $this->core->checkUserAllows(GENERALSETTINGS_NO);
    
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
    $this->load->library('shortxml');
    
    $this->card_model->db->trans_begin();
    
    $core = $this->core;
    $xml = $this->shortxml;
    
    $brseqno = $core->getBranchID();
    $userAudit = $core->getUserID();
    $ipAddress = $core->getIPAddress();
    $workstation = $core->getWorkstation();
    $override = '';
    $sessionID = $core->getSessionID();
    
    //card BIN
    if (!$cardBIN = $this->cache->get($this->core->getSessionID() . 'cardBIN')) {
      $result = $this->card_model->getCardBIN();
      
      $cardBIN = $result->result_array();
      
      $result->free_result();
      $result->next_result();
      $this->cache->save($this->core->getSessionID() .'cardBIN', $cardBIN, CACHE_TTL);
    }
    
    $first = current($cardBIN);
    
    $data['cardBIN'] = NULL;
    $data['formatValue1'] = NULL;
    $data['weights1'] = NULL;
    $data['weights1MaxLength'] = NULL;
    $data['expYears1'] = NULL;
    $data['gracePeriod1'] = NULL;
    $data['minDays1'] = NULL;
    
    foreach ($cardBIN as $row)
    {
      $codevalue = $row['codevalue'];
      
      $data['cardBIN'] .= '<option >'. $codevalue .'</option>';
      
    }
    //end

    //card type
    $result = $this->card_model->getCardTypeGeneralSettings();
    
    $cardTypes = '';//<option value="0">ALL</option>';
    
    //if ($this->core->isISOCustomer()) {
    $first = NULL;
    if ($result->num_rows() > 0) {
      foreach ($result->result_array() as $row) {

        if ($first === NULL) {
          $first = $row;
        }

        $codevalue = $row['accttype'];

        $accttype = $row['accttype'];
        $description = $row['description'];

        $result = $this->card_model->getAccountFormat(
          $codevalue,
          $brseqno,
          $ipAddress,
          $workstation,
          $userAudit,
          $override,
          $sessionID
        );
        
        $row = $result->row_array();
        
        //$formatValue = NULL;
        //$weights = $xml->getValue('WEIGHTS');
        //$expYears = $xml->getValue('EXPYEARS') ? $xml->getValue('EXPYEARS') : 0;
        //$gracePeriod = $xml->getValue('GRACEPERIOD') ? $xml->getValue('GRACEPERIOD') : 0;
        //$minDays = $xml->getValue('MINDAYS') ? $xml->getValue('MINDAYS') : 0;
        
        if ($result->num_rows() > 0) {
        
          $xml->setXML($row['xml1']);
          $formatValue = $row['formatvalue'];
          $weights = $xml->getValue('WEIGHTS');
          $expYears = $xml->getValue('EXPYEARS') ? $xml->getValue('EXPYEARS') : 0;
          $gracePeriod = $xml->getValue('GRACEPERIOD') ? $xml->getValue('GRACEPERIOD') : 0;
          $minDays = $xml->getValue('MINDAYS') ? $xml->getValue('MINDAYS') : 0;

          $emvresult = $this->card_model->getEMVFormat(
            $codevalue,
            $brseqno,
            $ipAddress,
            $workstation,
            $userAudit,
            $override,
            $sessionID
          );

          $emvrow = $emvresult->row_array();

          $emvresult->free_result();
          $emvresult->next_result();

          $trnExpr = isset($emvrow['isExpireDate']) ? $emvrow['isExpireDate'] : 0;
          $trnICVV = isset($emvrow['isCheckICVV']) ? $emvrow['isCheckICVV'] : 0;
          $servICVV = isset($emvrow['ICVV']) ? $emvrow['ICVV'] : 0;
          $trnCVV = isset($emvrow['isCheckCVV']) ? $emvrow['isCheckCVV'] : 0;
          $servCVV = isset($emvrow['CVV']) ? $emvrow['CVV'] : 0;
          $trnARQC = isset($emvrow['isVerifyARQC']) ? $emvrow['isVerifyARQC'] : 0;
          $trnARPC = isset($emvrow['isGenerateARPC']) ? $emvrow['isGenerateARPC'] : 0;
          $trnCntr = isset($emvrow['isSendTransactionCounter']) ? $emvrow['isSendTransactionCounter'] : 0;

          $trnTrack2 = isset($emvrow['isUseTrack2']) ? $emvrow['isUseTrack2'] : 0;
          $trnICCT2 = isset($emvrow['isUseICCTrack2']) ? $emvrow['isUseICCTrack2'] : 0;


          $result->free_result();
          $result->next_result();
        
        } else {
          $xml->setXML(NULL);
          $formatValue = NULL;
          $weights = $xml->getValue('WEIGHTS');
          $expYears = $xml->getValue('EXPYEARS') ? $xml->getValue('EXPYEARS') : 0;
          $gracePeriod = $xml->getValue('GRACEPERIOD') ? $xml->getValue('GRACEPERIOD') : 0;
          $minDays = $xml->getValue('MINDAYS') ? $xml->getValue('MINDAYS') : 0;

          $trnExpr = 0;
          $trnICVV = 0;
          $servICVV = 0;
          $trnCVV = 0;
          $servCVV = 0;
          $trnARQC = 0;
          $trnARPC = 0;
          $trnCntr = 0;          

          $trnTrack2 = 0;
          $trnICCT2 = 0;
        }
        
        if ($codevalue === $first['accttype']) {
          $data['formatValue1'] = $formatValue;
          $data['weights1'] = $weights;
            
          $charLen = strlen(str_replace(array('-', 'C'), '', $formatValue));
            
          $data['weights1MaxLength'] = $charLen;
          $data['expYears1'] = $expYears;
          $data['gracePeriod1'] = $gracePeriod;
          $data['minDays1'] = $minDays;
        }
      
        $result->free_result();
        $result->next_result();

        $cardTypes .= '<option formatvalue="'. $formatValue .'" weights="'. $weights .'" expyears="'. $expYears .'" graceperiod="'. $gracePeriod .'" mindays="'. $minDays .'" charlen="'. $charLen .'" value="'. $accttype .'" trnexpr="'.$trnExpr.'" trnicvv="'.$trnICVV.'" trnarqc="'.$trnARQC.'" trnarpc="'.$trnARPC.'" trncntr="'.$trnCntr.'" trntrack2="'.$trnTrack2.'" trnicct2="'.$trnICCT2.'" trncvv="'.$trnCVV.'" servcvv="'.$servCVV.'" servicvv="'.$servICVV.'">'. $description .'</option>';
      }
    } else {
      $cardTypes = '<option value="">No Data Defined</option>';
    }
    //}
    $data['cardTypes'] = $cardTypes;
    //end

    //account types
    //if (!$accountTypes = $this->cache->get($this->core->getSessionID() . 'accountTypes')) {
    $result = $this->card_model->getAccountType();
    
    $accountTypes = $result->result_array();
    
    $result->free_result();
    $result->next_result();
      //$this->cache->save($this->core->getSessionID() .'accountTypes', $accountTypes, CACHE_TTL);
    //}
    
    $first = current($accountTypes);
    
    $data['accountTypes'] = NULL;
    $data['formatValue2'] = NULL;
    $data['weights2'] = NULL;
    $data['weights2MaxLength'] = NULL;
    $data['prodCode'] = NULL;
    $data['gracePeriod2'] = NULL;
    $data['minDays2'] = NULL;
    $charFormat = NULL;
    
    foreach ($accountTypes as $row) {     
      $acctType = $row['accttype'];
      $description = $row['description'];
      
      $xml->setXML($row['xml1']);
      $formatValue = $row['formatvalue'];
      $weights = $xml->getValue('WEIGHTS');
      $prodCode = $row['prodcode'];
      $gracePeriod = $xml->getValue('GRACEPERIOD') ? $xml->getValue('GRACEPERIOD') : 0;
      $minDays = $xml->getValue('MINDAYS') ? $xml->getValue('MINDAYS') : 0;
      
      if ($acctType === $first['accttype']) {
        $data['formatValue2'] = $formatValue;
        $data['weights2'] = $weights;
        
        $charLen = strlen(str_replace(array('-', 'C'), '', $formatValue));
        
        $data['weights2MaxLength'] = $charLen;
        $data['prodCode'] = $prodCode;
        $data['gracePeriod2'] = $gracePeriod;
        $data['minDays2'] = $minDays;
        $charFormat = $row['acctchar'];
      }
      
      $data['accountTypes'] .= '<option value="'. $acctType .'" formatvalue="'. $formatValue .'" weights="'. $weights .'" prodcode="'. $prodCode .'" graceperiod="'. $gracePeriod .'" mindays="'. $minDays .'" charlen="'. $charLen .'">'. $description .'</option>';
    }
    //end
    
    //character format
    $charFormatList = array(
      'A' => 'Alpha',
      'N' => 'Numeric',
      'X' => 'Alphanumeric'
    );
    
    $data['charFormat'] = NULL;
    foreach ($charFormatList as $val => $desc) {
      $selected = $charFormat === $val ? ' selected' : NULL;
      $data['charFormat'] .= '<option value="'. $val .'"'. $selected .'>'. $desc .'</option>';
    }
    //end
    
    //$this->card_model->db->trans_commit();
    
    //if INST has card product code
    $data['p1'] = NULL;
    $data['p2'] = NULL;
    if ($core->hasProductCode()) {
      $data['p1'] = '<td><strong>P</strong> - Product Code</td>';
      $data['p2'] = 'case (n === 112):
        case (n === 80):';
    }

    $auditXML = '';
    $brseqno = $this->core->getBranchID();
    $userAudit = $this->core->getUserID();
    $workstation = $this->core->getWorkstation();

    $auditXML .= '<old></><new></><field>General Settings</><details>Open Module</>';
    $this->card_model->insertAuditLogclixx(41,'990317',$brseqno,0,0,'GSET','',$userAudit,'','','','','','','',$userAudit,'','WEB','WEB',$workstation,$auditXML);

    $this->cache->save($this->core->getSessionID() .'formatdata', $data, CACHE_TTL);
    
    $data['sessionExp'] = $this->core->getSessionExp();
    $this->load->view('maintenance/settings', $data);
  }
  
  function submit()
  {
    $this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
    $this->load->model('coreapp/card_model');
    $this->load->library('shortxml');
    
    $input = $this->input;
    $xml2 = $this->shortxml;
    $core = $this->core;
    
    $formatCode = $input->post('formatCode', TRUE);
    $formatType = $input->post('formatType', TRUE);
    $formatValue = strtoupper($input->post('formatValue', TRUE));
    $formatDesc = $formatCode . ' Format';
    $weights = $input->post('weights', TRUE);
    $prodCode = $input->post('prodCode', TRUE);
    $expYears = $input->post('expYears', TRUE);
    $gracePeriod = $input->post('gracePeriod', TRUE);
    $minDays = $input->post('minDays', TRUE);
    $charFormat = $input->post('charFormat', TRUE);


    $description2 = $input->post('description2', TRUE);

    $trnTrack2 = $input->post('trnTrack2', TRUE);
    $trnICCT2  = $input->post('trnICCT2', TRUE);
    $trnExpr   = $input->post('trnExpr', TRUE);
    $trnCVV    = $input->post('trnCVV', TRUE);
    $servCVV   = $input->post('servcCVV', TRUE);
    $trnICVV   = $input->post('trnICVV', TRUE);
    $servICVV  = $input->post('servcICVV', TRUE);
    $trnARQC   = $input->post('trnARQC', TRUE);
    $trnARPC   = $input->post('trnARPC', TRUE);
    $trnCntr   = $input->post('trnCntr', TRUE);

    //var_dump(intval($trnTrack2));
    
    $xml = '<WEIGHTS>'. $weights .'</>'.
      '<EXPYEARS>'. $expYears .'</>'.
      '<GRACEPERIOD>'. $gracePeriod .'</>'.
      '<MINDAYS>'. $minDays .'</>'.
      '<CHKEXP>'. $trnExpr .'</>'.
      '<CHKCVV>'. $trnICVV .'</>'.
      '<ISARQC>'. $trnARQC .'</>'.
      '<ISARPC>'. $trnARPC .'</>'.
      '<SNDCNT>'. $trnCntr .'</>';
      
    $brseqno = $core->getBranchID();
    $userAudit = $core->getUserID();
    $ipAddress = $core->getIPAddress();
    $workstation = $core->getWorkstation();
    $override = '';
    $sessionID = $core->getSessionID();

    $result = $this->card_model->getAccountFormat($formatCode,$brseqno,$ipAddress,$workstation,$userAudit,$override,$sessionID);

    $row = $result->row_array();

    $result->free_result();
    $result->next_result();

    if (!$formatdata = $this->cache->get($this->core->getSessionID() . 'formatdata')) {
      echo json_encode(array(
        'success' => FALSE,
        'message' => 'Failed to retrieve format data.'
      ));
    } else {
    
      $result = $this->card_model->setAccountFormat(
        $formatCode,
        $formatType,
        $formatValue,
        $formatDesc,
        $prodCode,
        $charFormat,
        $xml,
        $brseqno,
        $ipAddress,
        $workstation,
        $userAudit,
        $override,
        $sessionID
      );

      $result = $this->card_model->setEMVFormat(
        $formatCode,
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
      );

      //var_dump($result);

      $formatdata = $row;
      $xml2->setXML(isset($formatdata['xml1']) ? $formatdata['xml1'] : '');

      if (isset($formatdata['formatvalue'])) {
        if ($formatdata['formatvalue'] != $formatValue) {

          $auditXML = '';
          
          $auditXML .= '<old>'.$formatdata['formatvalue'].'</><new>'.$formatValue.'</><field>'.($formatType == 'ACCT' ? 'Account' : 'Card').' Settings</><details>Format Layout: '.$description2.'</>';
          $this->card_model->insertAuditLogclixx(41,'990317',$brseqno,0,0,'SETU','',$userAudit,'','','','','','','',$userAudit,'','WEB','WEB',$workstation,$auditXML);
        }
      } else {

        $auditXML = '';
        
        $auditXML .= '<old></><new>'.$formatValue.'</><field>'.($formatType == 'ACCT' ? 'Account' : 'Card').' Settings</><details>Format Layout: '.$description2.'</>';
        $this->card_model->insertAuditLogclixx(41,'990317',$brseqno,0,0,'SETU','',$userAudit,'','','','','','','',$userAudit,'','WEB','WEB',$workstation,$auditXML);
      }

      //formatvalue
      //if (isset($xml2->getValue('WEIGHTS'))) {
        if ($xml2->getValue('WEIGHTS') != $weights) {

          $auditXML = '';
          
          $auditXML .= '<old>'.$xml2->getValue('WEIGHTS').'</><new>'.$weights.'</><field>'.($formatType == 'ACCT' ? 'Account' : 'Card').' Settings</><details>Weights: '.$description2.'</>';
          $this->card_model->insertAuditLogclixx(41,'990317',$brseqno,0,0,'SETU','',$userAudit,'','','','','','','',$userAudit,'','WEB','WEB',$workstation,$auditXML);
        //}
      } else {
        $auditXML = '';
          
        $auditXML .= '<old></><new>'.$weights.'</><field>'.($formatType == 'ACCT' ? 'Account' : 'Card').' Settings</><details>Weights: '.$description2.'</>';
        $this->card_model->insertAuditLogclixx(41,'990317',$brseqno,0,0,'SETU','',$userAudit,'','','','','','','',$userAudit,'','WEB','WEB',$workstation,$auditXML);
      }

      //formatType
      if ($formatType == 'ACCT') {
         //Prodcode
        if ($formatdata['prodcode'] != $prodCode) {

          $auditXML = '';
          
          $auditXML .= '<old>'.$formatdata['prodcode'].'</><new>'.$prodCode.'</><field>'.($formatType == 'ACCT' ? 'Account' : 'Card').' Settings</><details>Product Code: '.$description2.'</>';
          $this->card_model->insertAuditLogclixx(41,'990317',$brseqno,0,0,'SETU','',$userAudit,'','','','','','','',$userAudit,'','WEB','WEB',$workstation,$auditXML);
        }
        //Character Format
        if ($formatdata['acctchar'] != $charFormat) {

          $auditXML = '';
          
          $auditXML .= '<old>'.$formatdata['acctchar'].'</><new>'.$charFormat.'</><field>'.($formatType == 'ACCT' ? 'Account' : 'Card').' Settings</><details>Character Format: '.$description2.'</>';
          $this->card_model->insertAuditLogclixx(41,'990317',$brseqno,0,0,'SETU','',$userAudit,'','','','','','','',$userAudit,'','WEB','WEB',$workstation,$auditXML);
        }
      } else {
        //Years to expire
        //if (isset($xml2->getValue('EXPYEARS'))) {
          if ($xml2->getValue('EXPYEARS') != $expYears) {

            $auditXML = '';
            
            $auditXML .= '<old>'.$xml2->getValue('EXPYEARS').'</><new>'.$expYears.'</><field>'.($formatType == 'ACCT' ? 'Account' : 'Card').' Settings</><details>Years to Expire: '.$description2.'</>';
            $this->card_model->insertAuditLogclixx(41,'990317',$brseqno,0,0,'SETU','',$userAudit,'','','','','','','',$userAudit,'','WEB','WEB',$workstation,$auditXML);
          //}
        } else {
          $auditXML = '';
          
          $auditXML .= '<old></><new>'.$expYears.'</><field>'.($formatType == 'ACCT' ? 'Account' : 'Card').' Settings</><details>Years to Expire: '.$description2.'</>';
          $this->card_model->insertAuditLogclixx(41,'990317',$brseqno,0,0,'SETU','',$userAudit,'','','','','','','',$userAudit,'','WEB','WEB',$workstation,$auditXML);
        }
      }

      //Grace period
      //if (isset($xml2->getValue('GRACEPERIOD'))) {
        if ($xml2->getValue('GRACEPERIOD') != $gracePeriod) {

          $auditXML = '';
          
          $auditXML .= '<old>'.$xml2->getValue('GRACEPERIOD').'</><new>'.$gracePeriod.'</><field>'.($formatType == 'ACCT' ? 'Account' : 'Card').' Settings</><details>Grace Period: '.$description2.'</>';
          $this->card_model->insertAuditLogclixx(41,'990317',$brseqno,0,0,'SETU','',$userAudit,'','','','','','','',$userAudit,'','WEB','WEB',$workstation,$auditXML);
        //}
      } else {
        $auditXML = '';
        
        $auditXML .= '<old></><new>'.$gracePeriod.'</><field>'.($formatType == 'ACCT' ? 'Account' : 'Card').' Settings</><details>Grace Period: '.$description2.'</>';
        $this->card_model->insertAuditLogclixx(41,'990317',$brseqno,0,0,'SETU','',$userAudit,'','','','','','','',$userAudit,'','WEB','WEB',$workstation,$auditXML);
      }

      //Minimum no of days inactive      
      //if (isset($xml2->getValue('MINDAYS'))) {
        if ($xml2->getValue('MINDAYS') != $minDays) {

          $auditXML = '';
          
          $auditXML .= '<old>'.$xml2->getValue('MINDAYS').'</><new>'.$minDays.'</><field>'.($formatType == 'ACCT' ? 'Account' : 'Card').' Settings</><details>Min Days Inactive: '.$description2.'</>';
          $this->card_model->insertAuditLogclixx(41,'990317',$brseqno,0,0,'SETU','',$userAudit,'','','','','','','',$userAudit,'','WEB','WEB',$workstation,$auditXML);
        //}
      } else {

        $auditXML = '';
        
        $auditXML .= '<old></><new>'.$minDays.'</><field>'.($formatType == 'ACCT' ? 'Account' : 'Card').' Settings</><details>Min Days Inactive: '.$description2.'</>';
        $this->card_model->insertAuditLogclixx(41,'990317',$brseqno,0,0,'SETU','',$userAudit,'','','','','','','',$userAudit,'','WEB','WEB',$workstation,$auditXML);
      }

      echo json_encode(array(
        'success' => TRUE,
        'message' => 'Format successfully updated',
        'formatdata' => (isset($formatdata) ? $formatdata : NULL)/*,
        'formatnew' => $formatValue*/
      ));
    }
  }

  function updateemvcards()
  {
    $this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
    $this->load->model('coreapp/card_model');
    $this->load->library('shortxml');
    
    $input = $this->input;
    $xml2 = $this->shortxml;
    $core = $this->core;
    
    $formatCode = $input->post('id', TRUE);
    $userAudit = $core->getUserID();
    $ipAddress = $core->getIPAddress();
    $workstation = $core->getWorkstation();
    $override = '';
    $sessionID = $core->getSessionID();

    $result = $this->card_model->updateEMVcards(
      $formatCode,
      $ipAddress,
      $workstation,
      $userAudit,
      $override,
      $sessionID
    );

    echo json_encode(array(
      'success' => TRUE,
      'message' => $formatCode
    ));
  }
}