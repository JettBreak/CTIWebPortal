<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class ATMEdit extends CI_Controller { 

  function __construct()
  {
    parent::__construct();
    $this->load->library('core');
    $this->core->checkUserAllows(MAINTENANCEATM_NO);
    
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
  
  function index($termCode)
  {
    $this->load->model('coresys/atm_model');
    $this->load->model('coreapp/area_model');
    $this->load->model('coreapp/branch_model');
    $this->load->library('shortxml');
    $this->load->helper('url');
    
    $atm = $this->atm_model;
    $xml = $this->shortxml;
    $core = $this->core;
    
    $result = $atm->getTerminalInfo($termCode);
    
    if (!$termCode || ($result->num_rows() === 0)) {
      $this->load->helper('url');
      redirect('maintenance/atm');
      exit();
    }
    
    $row = $result->row_array();
  
    $progCode = $row['progcode'];
    $prodCode = $row['productcode'];
    $termLang = $row['termlang'];
    $location = $row['loccode'];
    $emulation = $row['emulation'];
    $dispLogic = $row['dispenselogic'];
    $instType = $row['installationtype'];
    $data['dtProd'] = $row['dtprod'] ? $core->formatDate('m/d/Y', $row['dtprod']) : 'Undefined';
    
    //denomination
    $progCodeDeno = $row['progcode_denomination'];
    $progLangDeno = $row['proglang_denomination'];
    
    $xml->setXML($row['xml'] . $row['xml2']);
    $data['luno']     = $row['luno'];
    $data['termID']   = $row['termid'];
    $data['desc']     = $row['description'];
    $currency = $xml->getValue('CURRENCY');
    $data['contactNo'] = $xml->getValue('CONTACT');
    $data['screenLoadSize'] = $row['screenloadsize'];
    $data['otherLoadSize']  = $row['otherloadsize'];
    $data['stateLoadSize']  = $row['stateloadsize'];
    $data['maxNotes']     = $xml->getValue('MAXNOTES');
    $data['fitLoadSize']  = $row['fitloadsize'];
    $data['optLoadSize']  = $row['optionloadsize'];
    
    $data['loadNewKey'] = ($row['isloadkeynew'] === '1' ? 'checked' : NULL);
    $data['loadPower']  = ($row['isloadpower'] === '1' ? 'checked' : NULL);
    
    $data['threshold'] = $xml->getValue('THRES') ? $core->currency($xml->getValue('THRES')) : '0.00';
    $data['decimal'] = $row['amtdec'];
    $data['status'] = $row['statdesc'] ? $row['statdesc'] : $row['status'] .'-Unknown';

    $InstID = $row['xml'] ? $xml->getValue('INSTID') : '';
    $Outlet = $row['xml'] ? $xml->getValue('OUTLET') : '';

    $errcnt = 0;
    $data['success'] = TRUE;
    $data['message'] = '';
    
    //product codes
    $result->free_result();
    $result->next_result();
    
    $result = $atm->getProductCodes();
    
    $data['prodName'] = NULL;
    foreach ($result->result_array() as $row) {
      $selected = ($row['productcode'] === $prodCode ? ' selected' : NULL);
      $data['prodName'] .= '<option value="'. $row['productcode'] .'"'. $selected .'>'. $row['productname'] .'</option>';
    }
    
    //terminal language
    $result->free_result();
    $result->next_result();
      
    $result = $atm->getTerminalLanguage();
    
    $data['termLang'] = NULL;
    foreach ($result->result_array() as $row) {
      $selected = ($row['termlang'] === $termLang ? ' selected' : NULL);
      $data['termLang'] .= '<option value="'. $row['termlang'] .'"'. $selected .'>'. $row['description'] .'</option>';
    }
    
    //location
    $result->free_result();
    $result->next_result();
      
    if ($core->isHeadOffice()) {
      $result = $atm->getAllLocations();
    } else {
      $result = $atm->getLocations($core->getBranchCode());
    }
    
    $data['location'] = NULL;
    $areaName = NULL;
    $brchName = NULL;
    
    $lastResult = $result->result_array();
    
    $result->free_result();
    $result->next_result();
      
    $locationFound = FALSE; //location indicator
    
    foreach ($lastResult as $row) {
      $result = $this->area_model->getAreaByBranch($row['brcode']);
      $r = $result->row_array();
      
      if ($location === $row['loccode']) {
        $selected = ' selected';
        $areaName = $r['areaname'];
        $brchName = $r['brname'];
        
        $locationFound = TRUE; //
      } else {
        $selected = NULL;
      }
      
      if ($result->num_rows() === 0) {
        $area = 'undefined';
        $branch = 'undefined';  
      } else {
        $area = $r['areaname'] ? $r['areaname'] : 'Undefined';
        $branch = $r['brname'] ? $r['brname'] : 'Undefined';
      }
      
      
      $data['location'] .= '<option value="'. $row['loccode'] .'" areaname="'. $area .'" brname="'. $branch .'"'. $selected .'>'. substr($row['location'], 0 ,25) .'</option>';
      
      $result->free_result();
      $result->next_result();
    
    }
    
    //something is wrong
    if ($locationFound === FALSE) {
      $data['location'] = NULL;
    }
    
    //Area and Branch
    $data['areaName'] = $areaName;
    $data['branchName'] = $brchName;
    //end
    
    //emulation
      
    $result = $atm->getCodeList('TERMEMUL');
    
    $data['emulation'] = NULL;
    foreach ($result->result_array() as $row) {
      $selected = ($row['codevalue'] === $emulation ? ' selected' : NULL);
      $data['emulation'] .= '<option value="'. $row['codevalue'] .'"'. $selected .'>'. $row['xml1'] .'</option>';
    }
      
    //program codes
    $result->free_result();
    $result->next_result();
      
    $result = $atm->getProgramCodes();
    
    $row = $result->row_array();

    $programcodes = FALSE;
    if (COUNT($row) > 0) {
      $programcodes = TRUE;
      $data['progCodex'] = $row['progcode'];
      $data['progLangx'] = $row['proglang'];
    } else {
      $errcnt++;
      $data['success'] = FALSE;
      $data['message'] .= '<li> Program Name</li>';
    }
    
    $data['progName'] = NULL;
    foreach ($result->result_array() as $row) {
      $selected = ($row['progcode'] === $progCode ? ' selected' : NULL);
      $data['progName'] .= '<option proglang="'. $row['proglang'] .'" value="'. $row['progcode'] .'"'. $selected .'>'. $row['tpfile'] .'</option>';
    }
    
    $result->free_result();
    $result->next_result();
    //end
    
    //installation type
    $result = $atm->getCodeList('INSTTYPE');
    
    $data['instType'] = NULL;
    foreach ($result->result_array() as $row) {
      $selected = ($row['codeseqno'] === $instType ? ' selected' : NULL);
      $data['instType'] .= '<option value="'. $row['codeseqno'] .'"'. $selected .'>'. $row['codevalue'] .'</option>';
    }
    
    //dispense logic
    $result->free_result();
    $result->next_result();
      
    $result = $atm->getCodeList('DISPLOGIC');
    
    $data['dispLogic'] = NULL;
    foreach ($result->result_array() as $row) {
      $selected = ($row['codeseqno'] === $dispLogic ? ' selected' : NULL);
      $data['dispLogic'] .= '<option value="'. $row['codeseqno'] .'"'. $selected .'>'. $row['codevalue'] .'</option>';
    }
    
    //denominations
    $result->free_result();
    $result->next_result();
      
    $result = $atm->getDenomination();
    
    $data['progCodeDeno'] = NULL;
    foreach ($result->result_array() as $row) {
      $selected = ($row['progcode'] === $progCodeDeno ? ' selected' : NULL);
      $data['progCodeDeno'] .= '<option value="'. $row['progcode'] .'"'. $selected .'>'. $row['progcode'] .'</option>';
    }
    $data['progLangDeno'] = $progLangDeno;
    
    $result->free_result();
    $result->next_result();
    
    if ($progCodeDeno === '0') {
      $progCodeDeno = $data['progCodex'];
      $progLangDeno = $data['progLangx'];
    }
    
    $result = $atm->getTerminalDenomination($progCodeDeno, $progLangDeno);
    
    $data['denominations'] = NULL;
    foreach ($result->result_array() as $row) {
      $parmData = $row['parmdata'];
      
      $curr = substr($parmData, 0, 3);
      $deno = $core->currency(substr($parmData, 6, 12) / 100);
      $cass = substr($parmData, 4, 1);
      $data['denominations'] .= '<tr>
        <td>'. $curr .'</td>
        <td>'. $deno .'</td>
        <td>'. $cass .'</td>
      </tr>';
    }
        
    //default currency
    $result->free_result();
    $result->next_result();
      
    $result = $atm->getCurrency();
    
    $data['currency'] = NULL;
    foreach ($result->result_array() as $row) {
      $selected = ($row['currency'] === $currency ? ' selected' : NULL);
      $data['currency'] .= '<option value="'. $row['currency'] .'"'. $selected .'>'. $row['currency'] .'</option>';
    }

    $result->free_result();
    $result->next_result();

    $data['institutions'] = NULL;
    $data['institutions'] = '<option value="XXX">Select</option>';
    $data['outlets'] = NULL;
    $data['outlets'] = '<option value="XXX">Select</option>';
    
    if ($this->core->hasPOSCashOut()) {
      //institutions

      $result = $this->branch_model->getInstitutions();
      $institutions = $result->result_array();
      
      if (count($institutions) > 0) {
        foreach ($institutions as $row) {

          if ($row['instseqno'] == $InstID) {
            $selected = ' selected';
            $data['instID'] = $row['instid'];
          } else {
            $selected = NULL;
          }

          $data['institutions'] .= '<option instid="'.$row['instid'].'" value="'. $row['instseqno'] .'"'.$selected.'>'. $row['instname'] .'</option>';
        }
      } else {
        $data['institutions'] = '<option value="">No Institution Defined</option>';
      }
      //end

      $result->free_result();
      $result->next_result();

      //institutions

      $result = $this->branch_model->getOutlets();
      $outlets = $result->result_array();
      
      if (count($outlets) > 0) {
        foreach ($outlets as $row) {

          if ($row['outletseqno'] == $Outlet) {
            $selected = ' selected';
            $data['outletID'] = $row['outletid'];
          } else {
            $selected = NULL;
          }

          $data['outlets'] .= '<option inst="'.$row['instseqno'].'" outletid="'.$row['outletid'].'" value="'. $row['outletseqno'] .'"'.$selected.'>'. $row['outletname'] .'</option>';
        }
      } else {
        $data['outlets'] = '<option value="">No Outlet Defined</option>';
      }
      //end 
    }
    
    //contact persons
    $data['hName'] = $xml->getValue('HNAME');
    $data['hNameAttr'] = $xml->getValue('HTEL') !== '' ? 'validate[required] ' : NULL;
    $data['hTel'] = $xml->getValue('HTEL');
    $data['hTelAttr'] = $xml->getValue('HNAME') !== '' ? 'validate[required] ' : NULL;
    $data['nName'] = $xml->getValue('NNAME');
    $data['nNameAttr'] = $xml->getValue('NTEL') !== '' ? 'validate[required] ' : NULL;
    $data['nTel'] = $xml->getValue('NTEL');
    $data['nTelAttr'] = $xml->getValue('NNAME') !== '' ? 'validate[required] ' : NULL;
    $data['tName'] = $xml->getValue('TNAME');
    $data['tNameAttr'] = $xml->getValue('TTEL') !== '' ? 'validate[required] ' : NULL;
    $data['tTel'] = $xml->getValue('TTEL');
    $data['tTelAttr'] = $xml->getValue('TNAME') !== '' ? 'validate[required] ' : NULL;
    $data['oName'] = $xml->getValue('ONAME');
    $data['oNameAttr'] = $xml->getValue('OTEL') !== '' ? 'validate[required] ' : NULL;
    $data['oTel'] = $xml->getValue('OTEL');
    $data['oTelAttr'] = $xml->getValue('ONAME') !== '' ? 'validate[required] ' : NULL;
    $data['isEMV'] = $xml->getValue('EMVENABLED') == 'Y' ? 'checked' : NULL;
    
    $data['header'] = 'Update ATM Information';
    $data['termCodeParams'] = 'value="'. $termCode .'" class="validate[required] numbersOnly" maxlength="8" readonly';
    $data['termIDParams'] = 'readonly';
    $data['submitBtnVal'] = 'maintenance/atmedit/submit';
    $data['waitMsg'] = 'Updating ATM entry...';
    $data['submitBtnMsg'] = 'The modification will alter the terminal behavior.<br />Do you want to continue?';
    $data['POSCashOut'] = $this->core->hasPOSCashOut() ? '<li><a href="#tab5">Others</a></li>' : NULL;

    if ($errcnt > 0) {
      $data['message'] = /*$errcnt.*/'Missing Parameters: <br>'.$data['message'];
    }
    
    $data['sessionExp'] = $this->core->getSessionExp();
    $this->load->view('maintenance/atmx', $data);
  }
  
  function submit()
  {
    $this->load->model('coreapp/user_model');
    $this->load->library('core');
    
    $core  = $this->core;
    $input = $this->input;
    
    $brseqno     = $this->core->getBranchID();
    $ipaddress   = $this->core->getIPAddress();
    $workstation = $this->core->getWorkstation();
    $userAudit = $core->getUserID();
    $sessionID = $core->getSessionID();
    
    $row = $this
      ->user_model
      ->checkLogin($userAudit, $sessionID)
      ->row_array();
    
    if ($row['errno'] !== '8') { //if session valid
      $termCode     = $input->post('termCode', TRUE);
      $termID     = $input->post('termID', TRUE);
      $luno       = $input->post('luno', TRUE);
      $instType     = $input->post('instType', TRUE);
      $codeDeno     = $input->post('progCodeDeno', TRUE);
      $langDeno     = $input->post('progLangDeno', TRUE);
      $emulation    = $input->post('emulation', TRUE);
      $prodCode     = $input->post('prodName', TRUE);
      $locCode    = $input->post('location', TRUE);
      $description  = $input->post('description', TRUE);
      $amtdec     = $input->post('decimal', TRUE);
      $progCode     = $input->post('progCode', TRUE);
      $progLang     = $input->post('progLang', TRUE);
      $termLang     = $input->post('termLang', TRUE);
      $screenLoadSize = $input->post('screenLoadSize', TRUE);
      $stateLoadSize  = $input->post('stateLoadSize', TRUE);
      $fitLoadSize  = $input->post('fitLoadSize', TRUE);
      $optLoadSize  = $input->post('optLoadSize', TRUE);
      $otherLoadSize  = $input->post('otherLoadSize', TRUE);
      $isLoadKeyNew   = $input->post('loadNewKey') ? '1' : '0';
      $dispLogic    = $input->post('dispenseLogic', TRUE);
      $isLoadPower  = $input->post('loadPower') ? '1' : '0';
      $dtProd     = $input->post('dtProd', TRUE) === 'Undefined' ? NULL : $core->formatDate('Y-m-d', $input->post('dtProd', TRUE));
      $instID       = $input->post('institutions', TRUE);
      $outlet       = $input->post('outlet', TRUE);
      $isEMV       = $input->post('isEMV', TRUE) ? 'Y' : 'N';
      
      $xml1 = '<CURRENCY>'. $input->post('currency', TRUE) .'</>'.
         '<THRES>'. str_replace(',', '', $input->post('threshold', TRUE)) .'</>'.
         '<HNAME>'. $input->post('hName', TRUE) .'</>'.
         '<HTEL>'. $input->post('hTel', TRUE) .'</>'.
         '<NNAME>'. $input->post('nName', TRUE) .'</>'.
         '<NTEL>'. $input->post('nTel', TRUE) .'</>'.
         '<TNAME>'. $input->post('tName', TRUE) .'</>'.
         '<TTEL>'. $input->post('tTel', TRUE) .'</>'.
         '<ONAME>'. $input->post('oName', TRUE) .'</>'.
         '<OTEL>'. $input->post('oTel', TRUE) .'</>'.
         '<EMVENABLED>'. $isEMV .'</>';
        //'<CONTACT>'. $input->post('contactNo', TRUE) .'</>';
                
      $xml2 = '<MAXNOTES>'. $input->post('maxNotes', TRUE) .'</>';
      $xml1 .= $instID != 'xxx' ? '<INSTID>'. $instID .'</>' : '' ;
      $xml1 .= $outlet != 'xxx' ? '<OUTLET>'. $outlet .'</>' : '' ;
      
      $this->load->model('coresys/atm_model');

      $result = $this->atm_model->getTerminalInfo($termCode);

      $lastResult = $result->row_array();

      $result->free_result();
      $result->next_result();
      
      $result = $this->atm_model->updateATMTerminal(
        $termCode, $termID, $luno, $instType, $codeDeno, $langDeno, $emulation, $prodCode,
        $locCode, $description, $amtdec, $progCode, $progLang, $termLang, $screenLoadSize, $stateLoadSize,
        $fitLoadSize, $optLoadSize, $otherLoadSize, $isLoadKeyNew, $dispLogic, $isLoadPower, $dtProd, $xml1, $xml2
      );
      
      $row = $result->row_array();
      
      if (isset($row['errno']) && (int) $row['errno'] === 0) {
        $success = TRUE;
        $message = 'ATM entry updated successfully';

        
        $result->free_result();
        $result->next_result();

/*$progCode = $row['progcode'];
$prodCode = $row['productcode'];
$termLang = $row['termlang'];
$location = $row['loccode'];
$emulation = $row['emulation'];
$dispLogic = $row['dispenselogic'];
$instType = $row['installationtype'];
$data['dtProd'] = $row['dtprod'] ? $core->formatDate('m/d/Y', $row['dtprod']) : 'Undefined';

//denomination
$progCodeDeno = $row['progcode_denomination'];
$progLangDeno = $row['proglang_denomination'];

$xml->setXML($row['xml'] . $row['xml2']);
$data['luno']     = $row['luno'];
$data['termID']   = $row['termid'];
$data['desc']     = $row['description'];
$currency = $xml->getValue('CURRENCY');
$data['contactNo'] = $xml->getValue('CONTACT');
$data['screenLoadSize'] = $row['screenloadsize'];
$data['otherLoadSize']  = $row['otherloadsize'];
$data['stateLoadSize']  = $row['stateloadsize'];
$data['maxNotes']     = $xml->getValue('MAXNOTES');
$data['fitLoadSize']  = $row['fitloadsize'];
$data['optLoadSize']  = $row['optionloadsize'];

$data['loadNewKey'] = ($row['isloadkeynew'] === '1' ? 'checked' : NULL);
$data['loadPower']  = ($row['isloadpower'] === '1' ? 'checked' : NULL);

$data['threshold'] = $xml->getValue('THRES') ? $core->currency($xml->getValue('THRES')) : '0.00';
$data['decimal'] = $row['amtdec'];
$data['status'] = $row['statdesc'] ? $row['statdesc'] : $row['status'] .'-Unknown';

$InstID = $row['xml'] ? $xml->getValue('INSTID') : '';
$Outlet = $row['xml'] ? $xml->getValue('OUTLET') : '';*/

      if ($lastResult['termid'] != $termID) {
        $auditXML = '<old>'.$lastResult['termid'].'</><new>'.$termID.'</><field>Term ID</><details>Termcode: '.$termCode.'</>';
        $this->atm_model->insertAuditLogclixx(41,'990317',$brseqno,0,0,'ATM','',$userAudit,'','','','','','','',$userAudit,'','WEB','WEB',$workstation,$auditXML);
      }

      if ($lastResult['description'] != $description) {
        $auditXML = '<old>'.$lastResult['description'].'</><new>'.$description.'</><field>Description</><details>Termcode: '.$termCode.'</>';
        $this->atm_model->insertAuditLogclixx(41,'990317',$brseqno,0,0,'ATM','',$userAudit,'','','','','','','',$userAudit,'','WEB','WEB',$workstation,$auditXML);
      }

      if ($lastResult['progcode'] != $progCode) {

        //program codes
        $result->free_result();
        $result->next_result();
          
        $result = $this->atm_model->getProgramCodes();
        
        $progcoderesult = $result->row_array();
        
        $progNameOld = NULL;
        $progNameNew = NULL;
        foreach ($result->result_array() as $rowx) {
          if ($rowx['progcode'] == $progCode) {
            $progNameNew = $rowx['tpfile'];
          }

          if ($rowx['progcode'] == $lastResult['progcode']) {
            $progNameOld = $rowx['tpfile'];
          }
        }

        $result->free_result();
        $result->next_result();

        $auditXML = '<old>'.$progNameOld.'</><new>'.$progNameNew.'</><field>Program Name</><details>Termcode: '.$termCode.'</>';
        $this->atm_model->insertAuditLogclixx(41,'990317',$brseqno,0,0,'ATM','',$userAudit,'','','','','','','',$userAudit,'','WEB','WEB',$workstation,$auditXML);
      }

      if ($lastResult['productcode'] != $progCode) {

        //product codes
        $result->free_result();
        $result->next_result();
        
        $result = $this->atm_model->getProductCodes();
        
        $prodNameOld = NULL;
        $prodNameNew = NULL;
        foreach ($result->result_array() as $rowx) {
          if ($rowx['productcode'] == $progCode) {
            $prodNameNew = $rowx['productname'];
          }

          if ($rowx['productcode'] == $lastResult['productcode']) {
            $prodNameOld = $rowx['productname'];
          }
        }

        $result->free_result();
        $result->next_result();

        $auditXML = '<old>'.$prodNameOld.'</><new>'.$prodNameNew.'</><field>Product Name</><details>Termcode: '.$termCode.'</>';
        $this->atm_model->insertAuditLogclixx(41,'990317',$brseqno,0,0,'ATM','',$userAudit,'','','','','','','',$userAudit,'','WEB','WEB',$workstation,$auditXML);
      }

      } else {
        $success = FALSE;
        $message = $row['errmsg'];
      }
    } else { //if invalid userID / session then logout
      $success = FALSE;
      $message = $row['errmsg'];
    }
    
    echo json_encode(array(
      'success' => $success,
      'message' => $message,
      'errorno' => $row['errno']
    ));
  }
}
/* End of file atmedit.php */
/* Location: ./application/contollers/maintenance/atmedit.php */
