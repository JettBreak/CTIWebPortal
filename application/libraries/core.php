<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Core Class
 *
 * @package   Application
 * @subpackage  Libraries
 * @category  Libraries
 * @author    system
 * @lastupdate  October 3, 2011
 */
class Core {
  private $session;
  private $userData;
  
  /**
   * Constructor
   *
   */
  function __construct()
  {
    //$CI =& get_instance();
    //$CI->load->library('session');
    //$this->session = $CI->session;
    session_start();
    
    
    if (isset($_SESSION['userData'. session_id()])) {
      $this->userData = $_SESSION['userData' . session_id()];
    }
  }
  
  // --------------------------------------------------------------------

  /**
   * Charge Types For Service Codes
   *
   */
  function getChargeTypes()
  {
    return array(
      'SFEE' => 'Debit to Source Account',
      'DFEE' => 'Debit to Destination Account'
    );
  }
  
  // --------------------------------------------------------------------

  /**
   * Get Header Layout
   *
   * @access  public
   * @param int
   * @return  string
   */
  function getHeaderLayout($themeID)
  {
    
  }
  
  // --------------------------------------------------------------------

  /**
   * Encrypt
   *
   * @access  public
   * @param string
   * @param string
   * @return  string
   */
  function encrypt($userID, $pwd)
  {
    //return sha1('coreware:'. $pwd);
    return sha1($userID .':'. $pwd .':coreware');
  }
  
  // --------------------------------------------------------------------

  /**
   * Zero Padder (deprecated)
   *
   * Pads $str with zeros on the left, $n is minimum length of output
   * padZeros(5, 3); returns "005"
   * padZeros(500, 3); returns "500"
   *
   * @access  public
   * @param string
   * @param int
   * @return  string
   */
  function padZeros($str, $n)
  {
    //old
    /*$x = $n - strlen($str);
    if ($x < 0) {
      $x = $n;
    }
    $zeros = str_repeat('0', $x);*/
    return str_pad($str, $n, '0', STR_PAD_LEFT);
  }
  
  // --------------------------------------------------------------------

  /**
   * Get Forbidden Modules For Branch User
   *
   * @access  public
   * @return  array
   */
  /*function getForbiddenModules()
  {
    //user entry, access template
    return array(37, 38);
  }*/
  
  // --------------------------------------------------------------------

  /**
   * Get Web User Group
   *
   * @access  public
   * @return  array
   */
  function getWebUserGroup()
  {
    return array(
      '1' => 'Administrator',
      '2' => 'Branch Manager',
      '4' => 'Branch User',
      '3' => 'Operator'
    );
  }
  
  // --------------------------------------------------------------------

  /**
   * Get ONUSCODE
   *
   * @access  public
   * @return  string
   */
  function isSuperUser()
  {
    return $this->userData['superUser'];
  }
  
  // --------------------------------------------------------------------

  /**
   * Get ONUSCODE
   *
   * @access  public
   * @return  string
   */
  function getONUSCODE()
  {
    return $this->userData['onusCode'];
  }
  
  // --------------------------------------------------------------------

  /**
   * Get FINSWITCH
   *
   * @access  public
   * @return  string
   */
  function getFINSWITCH()
  {
    return $this->userData['finswitch'];
  }
  
  // --------------------------------------------------------------------

  /**
   * Get BANKCODE
   *
   * @access  public
   * @return  string
   */
  function getBANKCODE()
  {
    return strval($this->userData['bnkCode']);
  }
  
  // --------------------------------------------------------------------
  
  /**
   * Get BANKMNEM
   *
   * @access  public
   * @return  string
   */
  function getBANKMNEM()
  {
    return $this->userData['bnkMnem'];
  }
  
  // --------------------------------------------------------------------

  /**
   * Check User Rights
   *
   * @access  public
   * @return  void
   */
  function checkUserAllows($menuPos)
  {
    $allows = $this->getUserAllows();
    if (substr($allows, $menuPos - 1, 1) !== '1') {
      echo json_encode(array(
        'auth' => FALSE,
        'message' => 'You are not allowed to access this module'
      ));
      exit();
    }
  } 

  // --------------------------------------------------------------------

  /**
   * Check User Session
   *
   * @access  public
   * @return  void
   */
  function invalidSession()
  {
    $allows = $this->getUserAllows();
    if (substr($allows, $menuPos - 1, 1) !== '1') {
      echo json_encode(array(
        'auth' => FALSE,
        'message' => 'You are not allowed to access this module'
      ));
      exit();
    }
  }
  
  // --------------------------------------------------------------------
  
  /**
   * Get if SSL
   *
   * @access  public
   * @return  boolean
   */
  function isSSL()
  {
    return (isset($_SESSION['inst']['https']) &&
      $_SESSION['inst']['https']);
  }
  
  // --------------------------------------------------------------------
  
  /**
   * Get if bgs
   *
   * @access  public
   * @return  boolean
   */
  function iscardnogen()
  {
    return (isset($_SESSION['inst']['iscardnogen']) &&
      $_SESSION['inst']['iscardnogen']);
  }
  

  // --------------------------------------------------------------------

  /**
   * Check User Rights
   *
   * @access  public
   * @return  Boolean
   */
  function checkUserAllowsBtn($menuPos)
  {
    $allows = $this->getUserAllows();
    $isAllowed = TRUE;
    if (substr($allows, $menuPos - 1, 1) !== '1') {
      $isAllowed = FALSE;
    }

    return $isAllowed;
  }
  
  // --------------------------------------------------------------------

  /**
   * Compress Output
   *
   * @access  public
   * @param string
   * @return  string
   */
  function compressOutput($str)
  {
    $search = array(
      '/\>[^\S ]+/s', //strip whitespaces after tags, except space
      '/[^\S ]+\</s', //strip whitespaces before tags, except space
      '/(\s)+/s', // shorten multiple whitespace sequences
      '/<!--(.|\s)*?-->/' //strip HTML comments
    );
    $replace = array(
      '>',
      '<',
      '\\1',
      ''
    );
    return preg_replace($search, $replace, $str);
  }
  
  // --------------------------------------------------------------------
  
  /**
   * Display Last Login
   *
   * @access  public
   * @return  boolean
   */
  function displayLastLogIn()
  {
    $secuOpt = $this->userData['secuOpts'];
    
    return substr($secuOpt, 0, 1) === '1' ? TRUE : FALSE;
  }
  
  // --------------------------------------------------------------------

  /**
   * Is User a Teller?
   *
   * @access  public
   * @return  boolean
   */
  function isTeller()
  {
    return $this->userData['isTeller'];
  }
  
  // --------------------------------------------------------------------
  
  /**
   * Is Current INST card has product code?
   *
   * @access  public
   * @return  boolean
   */
  function hasProductCode()
  {
    return $_SESSION['inst']['prCode'];
  }
  
  // --------------------------------------------------------------------
  
  /**
   * Is Current Institution use ISO Customer?
   *
   * @access  public
   * @return  boolean
   */
  function isISOCustomer()
  {
    return (isset($_SESSION['inst']['isoCust']) &&
      $_SESSION['inst']['isoCust']);
  }

  // --------------------------------------------------------------------
  
  /**
   * Is Current Institution use ISO Customer?
   *
   * @access  public
   * @return  boolean
   */
  function isCYBL()
  {
    return (isset($_SESSION['inst']['isCYBL']) &&
      $_SESSION['inst']['isCYBL']);
  }
  
  // --------------------------------------------------------------------
  
  /**
   * Is Current Institution use Reports Version II?
   *
   * @access  public
   * @return  boolean
   */
  function isRepV2()
  {
    return (isset($_SESSION['inst']['reportv2']) &&
      $_SESSION['inst']['reportv2']);
  }
  
  // --------------------------------------------------------------------
  
  /**
   * Is Inst has Batch Card Upload Module
   *
   * @access  public
   * @return  boolean
   */
  function hasBatchCardUpload()
  {
    return (isset($_SESSION['inst']['batchCardUpload']) &&
      $_SESSION['inst']['batchCardUpload']);
  }
  
  // --------------------------------------------------------------------
  
  /**
   * Is Inst has POS Cashout Module
   *
   * @access  public
   * @return  boolean
   */
  function hasPOSCashOut()
  {
    return (isset($_SESSION['inst']['POSCashOut']) &&
      $_SESSION['inst']['POSCashOut']);
  }
  
  // --------------------------------------------------------------------
  
  /**
   * Get Session Expiry
   *
   * @access  public
   * @return  string
   */
  function getSessionExp()
  {
    return $this->userData['sessionExp'];
  }
  
  // --------------------------------------------------------------------

  /**
   * Get Password Mininum Character
   *
   * @access  public
   * @return  string
   */
  function getPasswordMinChar()
  {
    return $this->userData['minChar'];
  }
  
  // --------------------------------------------------------------------

  /**
   * Get IP Address
   *
   * Returns the IP address from which the user is viewing the current page. 
   *
   * @access  public
   * @return  string
   */
  function getIPAddress()
  {
    return $_SERVER['REMOTE_ADDR'];
  }
  
  // --------------------------------------------------------------------

  /**
   * Get Workstation
   *
   * Returns the IP address from which the user is viewing the current page. (with "@web")
   *
   * @access  public
   * @return  string
   */
  function getWorkstation()
  {
    return $_SERVER['REMOTE_ADDR'];// .'@web';
  }
  
  // --------------------------------------------------------------------

  /**
   * Is Logged In?
   *
   * @access  public
   * @return  boolean
   */
  function isLoggedIn()
  {
    return isset($this->userData['loggedIn']) && $this->userData['loggedIn'] === TRUE;
  }
  
  // --------------------------------------------------------------------

  /**
   * Get Last Login Date
   *
   * @access  public
   * @return  string
   */
  function getLastLogIn()
  {
    return $this->userData['lastLogin'];
  }
  
  // --------------------------------------------------------------------

  /**
   * Get User ID
   *
   * Returns the ID of the the current logged in user.
   *
   * @access  public
   * @return  string
   */
  function getUserID()
  {
    //return $this->session->userdata('userID');
    return $this->userData['userID'];
  }
  
  // --------------------------------------------------------------------
  
  /**
   * Get User Name
   *
   * Returns the name of the the current logged in user.
   *
   * @access  public
   * @return  string
   */
  function getUserName()
  {
    //return $this->session->userdata('userName');
    return $this->userData['userName'];
  }
  
  // --------------------------------------------------------------------
  
  /**
   * Get User Password
   *
   * Returns the encrypted password of the the current logged in user.
   *
   * @access  public
   * @return  string
   */
  function getUserPW()
  {
    //return $this->session->userdata('userPW');
    return $this->userData['userPW'];
  }
  
  // --------------------------------------------------------------------
  
  /**
   * Get User Rights
   *
   * @access  public
   * @return  string
   */
  function getUserAllows()
  {
    //return $this->session->userdata('userAllows');
    return $this->userData['userAllows'];
  }
  
  // --------------------------------------------------------------------
  
  /**
   * Get User Secret Question
   *
   * @access  public
   * @return  string
   */
  function getSecretQuestion()
  {
    //return $this->session->userdata('secretQ');
    return $this->userData['secretQ'];
  }
  
  // --------------------------------------------------------------------
  
  /**
   * Get Session ID
   *
   * @access  public
   * @return  string
   */
  function getSessionID()
  {
    //return $this->session->userdata('sessionID');
    return $this->userData['sessionID'];
  }
  
  // --------------------------------------------------------------------
  
  /**
   * Get Institution Name
   *
   * @access  public
   * @return  string
   */
  function getInstName()
  {
    //return $this->session->userdata('instName');
    return $this->userData['instName'];
  }
  
  // --------------------------------------------------------------------
  
  /**
   * Get User Group Name
   *
   * @access  public
   * @return  string
   */
  function getUserGroup()
  {
    //return $this->session->userdata('userGroup');
    return $this->userData['userGroup'];
  }
  
  // --------------------------------------------------------------------
  
  /**
   * Get User Address
   *
   * @access  public
   * @return  string
   */
  function getAddress()
  {
    //return $this->session->userdata('address');
    return $this->userData['address'];
  }
  
  // --------------------------------------------------------------------
  
  /**
   * Get User Branch ID
   *
   * @access  public
   * @return  int
   */
  function getBranchID()
  {
    //return $this->session->userdata('branchID');
    return $this->userData['branchID'];
  }
  
  // --------------------------------------------------------------------
  
  /**
   * Get User Branch Code
   *
   * @access  public
   * @return  string
   */
  function getBranchCode()
  {
    //return $this->session->userdata('branchCode');
    return $this->userData['branchCode'];
  }
  
  // --------------------------------------------------------------------
  
  /**
   * Get User Branch Name
   *
   * @access  public
   * @return  string
   */
  function getBranchName()
  {   
    //return $this->session->userdata('branchName');
    return $this->userData['branchName'];
  }
  
  // --------------------------------------------------------------------
  
  /**
   * Get User Branch Name
   *
   * @access  public
   * @return  string
   */
  function isCoreEncrypt()
  {   
    //return $this->session->userdata('branchName');
    return isset($this->userData['coreencrypt']) && $this->userData['coreencrypt'];
  }
  
  // --------------------------------------------------------------------
   
  /**
   * Get User Region Code
   *
   * @access  public
   * @return  string
   */
  function getRegionCode()
  {   
    //return $this->session->userdata('regionCode');
    return $this->userData['regionCode'];
  }
  
  // --------------------------------------------------------------------
  
  /**
   * Get User Area Name
   *
   * @access  public
   * @return  string
   */
  function getAreaName()
  {   
    //return $this->session->userdata('areaName');
    return $this->userData['areaName'];
  }
  
  // --------------------------------------------------------------------
  
  /**
   * Checks whether the user is an Administrator
   *
   * @access  public
   * @return  boolean
   */
  function isAdmin()
  {
    if ($this->getUserGroup() === '1') {
      return TRUE;
    }
    return FALSE;
  }
  
  // --------------------------------------------------------------------
  
  /**
   * Checks whether the user is from Head Office
   *
   * @access  public
   * @return  boolean
   */
  function isHeadOffice()
  {
    //return $this->session->userdata('isHead');
    return $this->userData['isHead'];
  }
  
  // --------------------------------------------------------------------
  
  /**
   * Checks whether the user can monitor all branches
   *
   * @access  public
   * @return  boolean
   */
  function canMon()
  {
    //return $this->session->userdata('isMon');
    return $this->userData['isMon'];
  }
  
  // --------------------------------------------------------------------
  
  /**
   * Checks whether the user can generate reports from all branches
   *
   * @access  public
   * @return  boolean
   */
  function canRep()
  {
    //return $this->session->userdata('isRep');
    return $this->userData['isRep'];
  }
  
  // --------------------------------------------------------------------
  
  /**
   * Checks if BytePerByte
   *
   * @access  public
   * @return  boolean
   */
  function byteperbyte()
  {
    //return $this->session->userdata('isRep');
    return (isset($_SESSION['inst']['bpb']) &&
      $_SESSION['inst']['bpb']);
  }
  
  // --------------------------------------------------------------------
  
  /**
   * check if allowed to dl perso file
   *
   * @access  public
   * @return  boolean
   */
  function allowPersoDL()
  {
    return (isset($_SESSION['inst']['allowPersoDL']) &&
      $_SESSION['inst']['allowPersoDL']);
  }
  
  // --------------------------------------------------------------------
  
  /**
   * check if perso/emboss file is sent thru sftp
   *
   * @access  public
   * @return  boolean
   */
  function sftp()
  {
    return (isset($_SESSION['inst']['sftp']) &&
      $_SESSION['inst']['sftp']);
  }
  
  // --------------------------------------------------------------------
  
  /**
   * Checks whether the user can modify users from all branches
   *
   * @access  public
   * @return  boolean
   */
  function canUser()
  {
    //return $this->session->userdata('isUser');
    return $this->userData['isUser'];
  }
  
  // --------------------------------------------------------------------
  
  /**
   * Get Address Types
   *
   * @access  public
   * @return  array
   */
  function getAddressTypes()
  {
    return array(
      'Home', 'Office'
    );
  }
  
  // --------------------------------------------------------------------
  
  /**
   * Get Gender
   *
   * @access  public
   * @return  array
   */
  function getGender()
  {
    return array(
      'Male', 'Female'
    );
  }
  
  // --------------------------------------------------------------------
  
  /**
   * Get Name Prefixes
   *
   * @access  public
   * @return  array
   */
  function getNamePrefixes()
  {
    return array(
      'Male' => array(
        'Mr.', 'Dr.', 'Fr.', 'Rev.', 'Atty.', 'Prof.', 'Hon.'
      ),
      'Female' => array(
        'Mrs.', 'Ms.', 'Miss', 'Dr.', 'Atty.', 'Prof.', 'Hon.'
      )
    );
  }
  
  // --------------------------------------------------------------------
  
  /**
   * Get Civil Status
   *
   * @access  public
   * @return  array
   */
  function getCivilStats()
  {
    return array(
      'Single', 'Married', 'Separated', 'Divorced', 'Widowed'
    );
  }
  
  // --------------------------------------------------------------------
  
  /**
   * Get Card Status List
   *
   * @access  public
   * @return  array
   */
  function getCardStatusList() {
    return array(
      '4'  => 'Active',
      '12' => 'Hot Card/Stolen',
      '8'  => 'Closed',
      '3'  => 'Suspended',
      '6'  => 'Blocked'
    );
  }
  
  // --------------------------------------------------------------------
  
  /**
   * Get Secret Questions
   *
   * @access  public
   * @return  array
   */
  function getSecretQuestions()
  {
    return array(
      "What is your mother's middle name?",
      'What was the name of your first school?',
      'Who was your childhood hero?',
      'Where did you first meet your spouse?',
      "What is your pet's name?"
    );
  }
  
  // --------------------------------------------------------------------
  
  /**
   * Get Country List
   *
   * @access  public
   * @return  array
   */
  function getCountries()
  {
    return simplexml_load_file('countries.xml');
  }
  
  // --------------------------------------------------------------------
  
  /**
   * Unused
   *
   */
  function getAreaCode($num)
  {
    return substr($num, 0, strrpos($num, "-"));
  }
  
  // --------------------------------------------------------------------
  
  /**
   * Unused
   *
   */
  function getPhoneNumber($num)
  {
    $bin = substr($num, 0, 4);
    $num = substr($num, 4);
    return array(
      'bin' => $bin,
      'num' => $num
    );
  }
  
  // --------------------------------------------------------------------
  
  /**
   * Currency Formatter
   *
   * This function formats the string to currency e.g "1000" => "1,000.00"
   *
   * @param string
   * @return  string
   */
  function currency($str)
  {
    return number_format(doubleval($str), 2, '.', ',');
  }
  
  // --------------------------------------------------------------------
  
  /**
   * Date Formatter
   *
   * @param string
   * @param string
   * @return  string
   */
  function formatDate($format, $str)
  {
    if ($str !== NULL) {
      return date($format, strtotime($str));
    } else {
      return $str;
    }
  }
  
  // --------------------------------------------------------------------
  
  /**
   * Get ATM Image
   *
   * returns the filename (ATM image) according to status code
   *
   * @param int
   * @return  string
   */
  function getATMImage($status)
  {
    switch ($status) {
      case 1:
      case 0:
      case 5:
        return 'atm-40-2.gif'; //green
        break;
      case 2:
      case 8:
        return 'atm-40-3.gif'; //red
        break;
      case 3:
        return 'atm-40-6b.gif'; //yellow wd down arrow
        break;
      case 4:
        return 'atm-40-7b.gif'; //yellow wd key
        break;
      case 6:
        return 'atm-40-5.gif'; //black
        break;
      case 7:
        return 'atm-40-9B.gif'; //yellow wd x 
        break;
      case 9:
        return 'atm-40-4.gif'; //yellow
        break;
      case 10:
        return 'animated.gif'; //processing
        break;
      case 14:
        return 'atm-40.gif'; //blue
        break;
      case 15:
        return 'atm-40-23.gif'; //pink
        break;
      case 127:
        return 'atm-40-14.gif'; //brown
        break;
      default:
        return 'atm-40-8.gif'; //unknown
        break;
    }
  }
  
  // --------------------------------------------------------------------
  
  /**
   * Get ATM Critical Status
   *
   * @param string
   * @return  string
   */
  function getATMCritStat($code)
  {
    switch ($code) {
      case '4':
        return '<span class="termCritStats toggle hidden">(*Overfilled*)</span>';
        break;
      case '3':
        return '<span class="termCritStats toggle hidden">(*Media Out*)</span>';
        break;
      case '2':
        return '<span class="termCritStats toggle hidden">(*Media Low*)</span>';
        break;
      default:
        return NULL;
        break;
    }
  }

  // --------------------------------------------------------------------
  
  /**
   * Decrypt subscriber number
   *
   * Decrypt subscriber number
   *
   * @param string
   * @return  string
   */

  function subsnoDecrypt( $s, $c ) 
  {
    $out = "";
    $r = 0;

    for ( $x=0 ; $x < strlen($s); $x++) {

      $r = strpos($c, $s[$x]);

      if ( $r >= 0) {
        $out .= strpos($c, $s[$x]);
      } else {
        $out .= $s[$x];
      }

    }
    return $out;
  }
  
  // --------------------------------------------------------------------
  
  /**
   * Generate Random Password
   *
   * populates ATM list view
   *
   * @param string
   * @return  string
   */
  
    function randomPassword() {
        
        $pass = array(); //remember to declare $pass as an array
        $new = array();
        $four = 0;

        for ($i = 0; $i < 8; $i++) {

            $random = rand(0,4);
            
            if (count($new) < 5) {
              while (in_array($random, $new)) {
                $random = rand(0,4);
              }
            }

            switch ($random) {
              case 0:
                $alphabet = "abcdefghijklmnopqrstuvwxyz";
                $new[] = $random;
                break;
              case 1:
                $alphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
                $new[] = $random;
                break;
              case 2:
                $alphabet = "!@#$%^&*()_+-={}[]|:;<>?/.,";
                $new[] = $random;
                break;
              case 3:
                $alphabet = "0123456789";
                $new[] = $random;
                break;
              default:
                $alphabet = "abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ0123456789!@#$%^&*()_+-={}[]|:;<>?/.,";
                $new[] = $random;
                # code...
                break;
            }

            $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }
        return implode($pass); //turn the array into a string
    }
  
  // --------------------------------------------------------------------
  
  /**
   * Get ATM List
   *
   * populates ATM list view
   *
   * @param string
   * @return  string
   */
  function showATMList($result)
  {
    $CI =& get_instance();
    $CI->load->library('shortxml');
    
    $xml = $CI->shortxml;
    
    $atm = NULL;
    
    if ($result->num_rows() > 0) {
      foreach ($result->result_array() as $row) {
        $termCode   = $row['termcode'];
        $status   = intval($row['status']);
        $statDesc   = ($row['statdesc'] ? $row['statdesc'] : $row['status'] .'-Unknown');
        $luno   = $row['luno'];
        $location = ucwords(strtolower($row['location']));
        $progCode = $row['progcode'];
        $progLang = $row['proglang'];
        
        //check threshold
        if (intval($status) === 1) {
          $xml->setXML($row['xml']);
          $threshold = floatval($xml->getValue('THRES'));
          $remaining = floatval($row['remainingcash']);
          
          if ($threshold > $remaining) {
            $status = 127;
          }
        }
        //end
        
        $icon = $this->getATMImage($status);
        
        //get critical status
        $critStat = $this->getATMCritStat(max(array(
          $row['cardcapturebin'],
          $row['cashhandler'],
          $row['depositbin'],
          $row['receiptprinter'],
          $row['journalprinter'],
          $row['nightsafedepository'],
          $row['cassette1'],
          $row['cassette2'],
          $row['cassette3'],
          $row['cassette4'],
          $row['statementribbon'],
          $row['statementprinter'],
          $row['envelopedispenser']
        )));
        
        //adds "toggle" class
        if ($critStat !== NULL) {
          $toggle = ' toggle';
        } else {
          $toggle = NULL;
        }
        
        $atm .= '<div id="'. $termCode .'" class="terminal" '.
          'status="'. $status .'" '.
          'luno="'. $luno .'" '.
          'location="'. $location .'" '.
          'progcode="'. $progCode .'" '.
          'proglang="'. $progLang .'">'.
          '<img src="images/atm/'. $icon .'" title="Click to get ATM info" class="atmimg" /><br />'.
          '<span>'. $termCode .'</span><br />'.
          '<span class="'. $toggle .'">('. $statDesc .')</span>'.
          $critStat .
        '</div>';
      }
      
      $atm = $this->compressOutput($atm);
    }
    
    return $atm;
  }
  
  // --------------------------------------------------------------------
  
  /**
   * Validate required fields
   *
   * returns boolean
   *
   * @param array
   * @return  boolean
   */
  function validateParams($required, $params)
  {
    $missing = array();
    $success = TRUE;
    $message = '';
    
    if (count($params) > 0) {
      
      foreach ($required as $param) {
        if (!array_key_exists($param, $params)) {
          $missing[] = $param;
        }
      }
      
      if (count($missing) > 0) {
        $success = FALSE;
        // $message = 'Must provide the following parameters: '. implode(', ', $missing);
        $message = 'Must provide all required fields.';
      }
    } else {
      $success = FALSE;
      $message = 'No args provided';
    }
    
    return array(
      'success' => $success,
      'params' => $params,
      'message' => $message,
    );
  } 
  
  // --------------------------------------------------------------------
  
  /**
   * Get POS Image
   *
   * returns the filename (POS image) according to status code
   *
   * @param string
   * @return  string
   */
  function getPOSImage($status)
  {
    switch ($status) {
      case '60':
        return 'pos-proc.gif';//processing
        break;
      case '61':
        return 'pos-procoff.gif';//processing off
        break;
      case '62':
        return 'pos-online.gif';//online
        break;
      case '63':
        return 'pos-idle.gif';//idle
        break;
      case '64':
        return 'pos-offline.gif';//offline
        break;
      case '6':
      case '65':
        return 'pos-oos.gif';//out of service
        break;
      default:
        return 'pos-unknown.gif';//unknown
        break;
    }
  }
  
  // --------------------------------------------------------------------
  
  /**
   * Get POS List
   *
   * populates POS list view
   *
   * @param string
   * @return  string
   */
  function showPOSList($result)
  {
    $CI =& get_instance();
    $CI->load->model('coresys/pos_model');
    $CI->load->library('shortxml');
    
    $xml = $CI->shortxml;
    $pos = NULL;
    
    if ($result->num_rows() > 0) {
      
      $posArr = array();
      foreach ($result->result_array() as $row) {
        $posCode  = $row['termcode'];
        
        //if NULL set statdesc to Unknown
        $posStatDesc = $row['statdesc'] ? $row['statdesc'] : $row['status'] .'-Unknown';
        $posImg   = $this->getPOSImage($row['status']);
        
        $posID    = $row['termid'];
        $posDesc  = $row['description'];
        $posStatus  = $row['status'];
        $posLoc   = ucwords(strtolower($row['location']));
        $posLuno  = $row['luno'];

        $xml->setXML($row['xml']);

        $instid = $xml->getValue('INSTID') && $xml->getValue('INSTID') != 'XXX' ? $xml->getValue('INSTID') : NULL;
        $outlet = $xml->getValue('OUTLET') && $xml->getValue('OUTLET') != 'XXX' ? $xml->getValue('OUTLET') : NULL;

        $result->free_result();
        $result->next_result();

        //Partner Institution
        IF ($instid != NULL) {
          $inst = $CI->pos_model->getInstitutionInfo($instid);
          $instname = isset($inst['instname']) ? $inst['instname'] : 'N/A';
        } else {
          $instname = 'N/A';
        }
        //end
        
        $result->free_result();
        $result->next_result();

        //Outlet
        IF ($outlet != NULL) {
          $outl = $CI->pos_model->getOutletInfo($outlet);
          $outletname = isset($outl['outletname']) ? $outl['outletname'] : 'N/A';
        } else {
          $outletname = 'N/A';
        }
        //end
        
        $pos .= '<div id="'. $posCode .'" class="terminal" termid="'. $posID .'" desc="'. $posDesc .'" location="'. $posLoc .'" luno="'. $posLuno .'" status="'. $posStatus .'" statdesc="'. $posStatDesc .'" instname="'. $instname .'" outletname="'. $outletname .'">
          <img src="images/pos/'. $posImg .'" title="Click to get POS info" class="posimg" /><br />
          <span>'. $posCode .'</span><br />
          <span>('. $posStatDesc .')</span>
        </div>';
      }
      
      $pos = $this->compressOutput($pos);
    }
    return $pos;
  }
  
  // --------------------------------------------------------------------
  
  /**
   * Get Host Image
   *
   * returns the filename (Host image) according to status code
   *
   * @param string
   * @return  string
   */
  function getHostImage($status)
  {
    switch ($status) {
      case '0':
        return 'host4.png';//unavailable
        break;
      case '1'://online
      case '3'://connected
        return 'host2.png';//processing off
        break;
      case '2'://offline
      case '4'://disconnected
        return 'host3.png';//processing off
        break;  
      default:
        return 'host0.png';//unknown
        break;
    }
  }
  
  // --------------------------------------------------------------------
  
  /**
   * Get Navigation Menu
   *
   * @return  array
   */
  function getNavMenu()
  {
    $newCustomerName = 'Customer Enrollment';
    $newCustomerLink = '#customer/enroll';

    $newAccountName = 'Account Enrollment';
    $newAccountLink = '#accounts/newentry';

    if ($this->isCYBL()) {
      $newAccountName = 'Account Request';
      $newAccountLink = '#accounts/accountget';
    }
    
    if ($this->isISOCustomer()) {
      $newCustomerLink = '#customer/search2';
      $newCustomerName = 'Customer Request';
      
      $custSubmenu = array(
          CUSTNEW_NO => array(
            'name' => $newCustomerName,
            'link' => $newCustomerLink,
            'type' => 'ISS'
          ),
          CUSTINFO_NO => array(
            'name' => 'Customer Information',
            'link' => '#customer/search/info',
            'type' => 'ISS'
          ),
          CUSTAPPROVAL_NO => array(
            'name' => 'Customer Approval',
            'link' => '#customer/verification',
            'type' => 'ISS'
          ),
          CUSTEDIT_NO => array(
            'name' => 'Update Customer Information',
            'link' => '#customer/search/edit',
            'type' => 'ISS'
          ),
          CUSTEDITAPPROVAL_NO => array(
            'name' => 'Update Customer Approval',
            'link' => '#customer/editapproval',
            'type' => 'ISS'
          ),
          CUSTDELETE_NO => array(
            'name' => 'Delete Customer Information',
            'link' => '#customer/search/delete',
            'type' => 'ISS'
          )
        );

      $newAccountName = 'Account Request';
      $newAccountLink = '#accounts/accountrequest';

      $cardSubmenu = array(
          CARDINFO => array(
            'name' => 'View Card',
            'link' => '#card/verify/info',
            'type' => 'ISS'
          ),
          CARDUPDATE_NO => array(
            'name' => 'Update Card',
            'link' => '#card/verify/info',
            'type' => 'ISS'
          ),
          CARDENROLLMENT_NO => array(
            'name' => 'Card Enrollment',
            'link' => '#card/enrollment',
            'type' => 'ISS'
          ),
          CARDVERIFICATION_NO => array(
            'name' => 'Card Verification',
            'link' => '#card/verification',
            'type' => 'ISS'
          ),
          CARDORDER_NO => array(
            'name' => 'Card Order Request',
            'link' => '#card/orderrequest',
            'type' => 'ISS'
          ),
          CARDGENERATION_NO => array(
            'name' => 'Card Generation',
            'link' => '#card/generation',
            'type' => 'ISS'
          ),
          CARDEMBOSSING_NO => array(
            'name' => 'Card Embossing',
            'link' => '#card/embossing',
            'type' => 'ISS'
          ),
          /*GENDEFPIN => array(
            'name' => 'Generate Default PIN',
            'link' => '#card/gendefpin',
            'type' => 'ISS'
          ),*/
          //BATCHACCOUNTLINK_NO => array(
          //  'name' => 'Card Linking via File Upload',
         //   'link' => '#card/batchaccountlink'
          //),
          CARDISSUANCE_NO => array(
            'name' => 'Card Issuance',
            'link' => '#customer/search/issuance',
            'type' => 'ISS'
          ),
          /*CARDISSUANCEAPPROVAL_NO => array(
            'name' => 'Card Issuance Approval',
            'link' => '#card/cardissuanceapproval',
            'type' => 'ISS'
          ),*/
          ACCNTLINKING_NO => array(
            'name' => 'Account Linking',
            'link' => '#card/verify/account',
            'type' => 'ISS'
          ),/*
          BILLSPAYMENT_NO => array(
            'name' => 'Bills Payment',
            'link' => '#card/verify/billspayment',
            'type' => 'ISS'
          ),
          MOBILEENROLL_NO => array(
            'name' => 'Mobile Enrollment',
            'link' => '#card/verify/mobile',
            'type' => 'ISS'
          ),*/
          /*MOBILEENROLL_NO => array(
            'name' => 'Mobile Enrollment',
            'link' => '#card/verify/mobile',
            'type' => 'ISS'
          ),*/
          //MOBILESEARCH_NO => array(
          //  'name' => 'Search Mobile',
          //  'link' => '#card/mobile'
          //),
          REPLACEMENTREQ_NO => array(
            'name' => 'Replacement Request',
            'link' => '#card/verify/replace',
            'type' => 'ISS'
          ),
          /*CHANGECARDSTAT_NO => array(
            'name' => 'Change Card Status',
            'link' => '#card/verify/change',
            'type' => 'ISS'
          )
          RESETPIN => array(
            'name' => 'Reset PIN',
            'link' => '#card/verify/resetpin',
            'type' => 'ISS'
          )*/
        );

    } else {
      $custSubmenu = array(
          CUSTNEW_NO => array(
            'name' => $newCustomerName,
            'link' => $newCustomerLink,
            'type' => 'ISS'
          ),
          CUSTBATCHUPLOAD_NO => array(
            'name' => 'Customer Batch Upload',
            'link' => '#customer/batchupload',
            'type' => 'ISS'
          ),
          CUSTINFO_NO => array(
            'name' => 'Customer Information',
            'link' => '#customer/search/info',
            'type' => 'ISS'
          ),
          CUSTEDIT_NO => array(
            'name' => 'Edit Customer Information',
            'link' => '#customer/search/edit',
            'type' => 'ISS'
          )
        );
      if (!$this->hasBatchCardUpload()) {
        unset($custSubmenu[CUSTBATCHUPLOAD_NO]);
      }
      $cardSubmenu = array(
          CARDINFO => array(
            'name' => 'View Card',
            'link' => '#card/verify/info',
            'type' => 'ISS'
          ),
          CARDUPDATE_NO => array(
            'name' => 'Update Card',
            'link' => '#card/verify/info',
            'type' => 'ISS'
          ),
          CARDENROLLMENT_NO => array(
            'name' => 'Card Enrollment',
            'link' => '#card/enrollment',
            'type' => 'ISS'
          ),
          CARDVERIFICATION_NO => array(
            'name' => 'Card Verification',
            'link' => '#card/verification',
            'type' => 'ISS'
          ),
          CARDORDER_NO => array(
            'name' => 'Card Order Request',
            'link' => '#card/orderrequest',
            'type' => 'ISS'
          ),
          CARDBATCHUPLOAD_NO => array(
            'name' => 'Card Order Batch Upload',
            'link' => '#card/batchupload',
            'type' => 'ISS'
          ),          
          //BATCHACCOUNTLINK_NO => array(
          //  'name' => 'Card Linking via File Upload',
          //  'link' => '#card/batchaccountlink'
        //  ),
          CARDGENERATION_NO => array(
            'name' => 'Card Generation',
            'link' => '#card/generation',
            'type' => 'ISS'
          ),
          CARDEMBOSSING_NO => array(
            'name' => 'Card Embossing',
            'link' => '#card/embossing',
            'type' => 'ISS'
          ),
          /*GENDEFPIN => array(
            'name' => 'Generate Default PIN',
            'link' => '#card/gendefpin',
            'type' => 'ISS'
          ),*/
          //CARDUPLOAD_NO => array(
          //  'name' => 'Card Order via File Upload',
          //  'link' => '#card/upload'
          //),
          CARDISSUANCE_NO => array(
            'name' => 'Card Issuance',
            'link' => '#customer/search/issuance',
            'type' => 'ISS'
          ),
          /*CARDISSUANCEAPPROVAL_NO => array(
            'name' => 'Card Issuance Approval',
            'link' => '#card/cardissuanceapproval',
            'type' => 'ISS'
          ),*/
          ACCNTLINKING_NO => array(
            'name' => 'Account Linking',
            'link' => '#card/verify/account',
            'type' => 'ISS'
          ),
          /*BILLSPAYMENT_NO => array(
            'name' => 'Bills Payment',
            'link' => '#card/verify/billspayment',
            'type' => 'ISS'
          ),*/
          /*MOBILEENROLL_NO => array(
            'name' => 'Mobile Enrollment',
            'link' => '#card/verify/mobile',
            'type' => 'ISS'
          ),*/
          /*MOBILEENROLL_NO => array(
            'name' => 'Mobile Enrollment',
            'link' => '#card/verify/mobile',
            'type' => 'ISS'
          ),*/
          //MOBILESEARCH_NO => array(
          //  'name' => 'Search Mobile',
          //  'link' => '#card/mobile'
          //),
          REPLACEMENTREQ_NO => array(
            'name' => 'Replacement Request',
            'link' => '#card/verify/replace',
            'type' => 'ISS'
          ),
          /*CHANGECARDSTAT_NO => array(
            'name' => 'Change Card Status',
            'link' => '#card/verify/change',
            'type' => 'ISS'
          )*/
          /*RESETPIN => array(
            'name' => 'Reset PIN',
            'link' => '#card/verify/resetpin',
            'type' => 'ISS'
          )*/
        );

      if (!$this->hasBatchCardUpload()) {
        unset($cardSubmenu[CARDBATCHUPLOAD_NO]);
      }
    }



    if ($this->isRepV2()) {
      $reportsno = array(
          REPORTPROCLIST_NO => array(
            'name' => 'Reports',
            'link' => '#reports/reportprocesslist',
            'type' => 'ISS',
            'isChannel' => 0
          ),
          
          BARTSFILE_NO => array(
            'name' => 'BARTS File',
            'link' => '#reports/barts',
            'type' => 'ACQ',
            'isChannel' => 0
          ),
          BILLSPAYMENTREP_NO => array(
            'name' => 'Bills Payment Report',
            'link' => '#reports/billspaymentrep',
            'type' => 'ACQ',
            'isChannel' => 0
          ),
          INPUTFILESREP_NO => array(
            'name' => 'Input Files',
            'link' => '#reports/filereports',
            'type' => 'ACQ',
            'isChannel' => 0
          ),
          PINMAILERBATCHREP_NO => array(
            'name' => 'PIN Mailer Batch Report',
            'link' => '#reports/pinmailer',
            'type' => 'ISS',
            'isChannel' => 0
          ),
          REPBRANCHLOG_NO => array(
            'name' => 'Audit Trail',
            'link' => '#reports/reportauditlog',
            'type' => 'ACQ',
            'isChannel' => 1
          )
        );
    } else {
      $reportsno = array(
          PDFGEN_NO => array(
            'name' => 'PDF Generator',
            'link' => '#reports/pdfgenerator',
            'type' => 'ACQ'
          ),
          ATMAVAILABILITY_NO => array(
            'name' => 'ATM Availability Report',
            'link' => 'reports/atmavailability/preview',
            'type' => 'ACQ',
            'isChannel' => 0
          ),
          DAILYCARDACT_NO => array(
            'name' => 'Card Inventory Report',
            'link' => 'reports/cardinventory/preview',
            'type' => 'ISS',
            'isChannel' => 0
          ),
          APPROVEDTRXACQPERTERM_NO => array(
            'name' => 'Approved Transactions, ACQ Per Terminal Summary ',
            'link' => 'reports/approvedtrxacqperterm/preview',
            'type' => 'ACQ',
            'isChannel' => 0
          ),
          REJECTEDTRXACQPERTERM_NO => array(
            'name' => 'Rejected Transactions, ACQ Per Terminal Summary ',
            'link' => 'reports/rejectedtrxacqperterm/preview',
            'type' => 'ACQ',
            'isChannel' => 0
          ),
          TRXPERTERM_NO => array(
            'name' => 'Transactions Per Terminal Summary ',
            'link' => 'reports/trxperterm/preview',
            'type' => 'ACQ',
            'isChannel' => 0
          ),
          APPROVEDTRXISSPERBRANCH_NO => array(
            'name' => 'Approved Transactions, Issuer Per Branch Summary ',
            'link' => 'reports/approvedtrxissperbranch/preview',
            'type' => 'ISS',
            'isChannel' => 1
          ),
          REJECTEDTRXISSPERBRANCH_NO => array(
            'name' => 'Rejected Transactions, Issuer Per Branch Summary',
            'link' => 'reports/rejectedtrxissperbranch/preview',
            'type' => 'ISS',
            'isChannel' => 1
          ),
          APPROVEDTRXONUSPERBRANCH_NO => array(
            'name' => 'Approved Transactions, ONUS Per Branch Summary',
            'link' => 'reports/approvedtrxonusperbranch/preview',
            'type' => 'ISS',
            'isChannel' => 1
          ),
          REJECTEDTRXONUSPERBRANCH_NO => array(
            'name' => 'Rejected Transactions, ONUS Per Branch Summary',
            'link' => 'reports/rejectedtrxonusperbranch/preview',
            'type' => 'ISS',
            'isChannel' => 1
          ),
          APPROVEDTRXONUSPERTERM_NO => array(
            'name' => 'Approved Transactions, ONUS Per Terminal Summary',
            'link' => 'reports/approvedtrxonusperterm/preview',
            'type' => 'ISS',
            'isChannel' => 0
          ),
          REJECTEDTRXONUSPERTERM_NO => array(
            'name' => 'Rejected Transactions, ONUS Per Terminal Summary',
            'link' => 'reports/rejectedtrxonusperterm/preview',
            'type' => 'ISS',
            'isChannel' => 0
          ),
          APPROVEDOURCHATOURTERM_NO => array(
            'name' => 'Approved Our Cardholder at Our Bank Terminal',
            'link' => 'reports/approvedcardholderatourbankterm/preview',
            'type' => 'ISS',
            'isChannel' => 1
          ),
          REJECTEDOURCHATOURTERM_NO => array(
            'name' => 'Rejected Our Cardholder at Our Bank Terminal',
            'link' => 'reports/rejectedcardholderatourbankterm/preview',
            'type' => 'ISS',
            'isChannel' => 1
          ),
          APPROVEDOURCHATOTHERBRANCH_NO => array(
            'name' => 'Approved Our Cardholder at Other Branch',
            'link' => 'reports/approvedcardholderatotherbranch/preview',
            'type' => 'ISS',
            'isChannel' => 1
          ),
          REJECTEDOURCHATOTHERBRANCH_NO => array(
            'name' => 'Rejected Our Cardholder at Other Branch',
            'link' => 'reports/rejectedcardholderatotherbranch/preview',
            'type' => 'ISS',
            'isChannel' => 1
          ),
          DAILYSETTLEMENTPERBANK_NO => array(
            'name' => 'Daily Settlement Per Bank Summary',
            'link' => 'reports/dailysettlementperbank/preview',
            'type' => 'ISS',
            'isChannel' => 1
          ),
          DAILYSETTLEMENTPERBRANCH_NO => array(
            'name' => 'Daily Settlement Per Branch Summary',
            'link' => 'reports/dailysettlementperbranch/preview',
            'type' => 'ISS',
            'isChannel' => 1
          ),
          DAILYBILLSTRANSACTIONS_NO => array(
            'name' => 'Debit Bills Transactions',
            'link' => 'reports/debitbillstrx/preview',
            'type' => 'ISS',
            'isChannel' => 0
          ),
          APPROVEDOURCHATOTHERTERM_NO => array(
            'name' => 'Approved Our Cardholder at Other Terminal',
            'link' => 'reports/approvedcardholderatotherterm/preview',
            'type' => 'ISS',
            'isChannel' => 1
          ),
          REJECTEDOURCHATOTHERTERM_NO => array(
            'name' => 'Rejected Our Cardholder at Other Terminal',
            'link' => 'reports/rejectedcardholderatotherterm/preview',
            'type' => 'ISS',
            'isChannel' => 1
          ),
          ISSIBFTTRX_NO => array(
            'name' => 'Issuer IBFT Transaction',
            'link' => 'reports/issibfttrx/preview',
            'type' => 'ISS',
            'isChannel' => 1
          ),
          TRANSFEREEIBFTTRX_NO => array(
            'name' => 'Transferee IBFT Transaction',
            'link' => 'reports/transfereeibfttrx/preview',
            'type' => 'ISS',
            'isChannel' => 1
          ),
          ALLIBFTTRX_NO => array(
            'name' => 'All IBFT Transaction',
            'link' => 'reports/allibfttrx/preview',
            'type' => 'ISS',
            'isChannel' => 1
          ),
          POSTRX_NO => array(
            'name' => 'POS Transaction Report',
            'link' => 'reports/postrxreport/preview',
            'type' => 'ISS',
            'isChannel' => 1
          ),
          POSLOANPAY_NO => array(
            'name' => 'POS Loan Payment, ONUS Report',
            'link' => 'reports/posloanpayreport/preview',
            'type' => 'ISS',
            'isChannel' => 1
          ),
          POSLOANPAYACQ_NO => array(
            'name' => 'POS Loan Payment, Acquirer Report',
            'link' => 'reports/posloanpayacqreport/preview',
            'type' => 'ACQ',
            'isChannel' => 1
          ),
          AUTOLOADTRX_NO => array(
            'name' => 'Autoload Transaction Report',
            'link' => 'reports/autoloadtrxreport/preview',
            'type' => 'ISS',
            'isChannel' => 1
          ),
          APPROVEDMOBILEBNKNGREP_NO => array(
            'name' => 'Approved Mobile Banking Transactions',
            'link' => 'reports/approvedmobilebnkng/preview',
            'type' => 'ISS',
            'isChannel' => 1
          ),
          REJECTEDMOBILEBNKNGREP_NO => array(
            'name' => 'Rejected Mobile Banking Transactions',
            'link' => 'reports/rejectedmobilebnkng/preview',
            'type' => 'ISS',
            'isChannel' => 1
          ),
          OTHERCARDHOLDERATOURTERM_NO => array(
            'name' => 'Other Cardholder at Our Terminal',
            'link' => 'reports/otherchourterm/preview',
            'type' => 'ACQ',
            'isChannel' => 1
          ),
          
          
          /*ATMAVAILABILITY_NO => array(
            'name' => 'ATM Availability Report',
            'link' => '#reports/atmavailability',
            'type' => 'ACQ'
          ),
          APPROVEDTRXACQPERTERM_NO => array(
            'name' => 'Approved Transactions, ACQ Per Terminal Summary',
            'link' => '#reports/approvedtrxacqperterm',
            'type' => 'ACQ'
          ),
          REJECTEDTRXACQPERTERM_NO => array(
            'name' => 'Rejected Transactions, ACQ Per Terminal Summary',
            'link' => '#reports/rejectedtrxacqperterm',
            'type' => 'ACQ'
          ),
          TRXPERTERM_NO => array(
            'name' => 'Transactions Per Terminal',
            'link' => '#reports/trxperterm',
            'type' => 'ACQ'
          ),
          OTHERCARDHOLDERATOURTERM_NO => array(
            'name' => 'Other Cardholder at Our Terminal',
            'link' => '#reports/otherchourterm',
            'type' => 'ACQ'
          ),
          
          
          REPDAILYCARDACT_NO => array(
            'name' => 'Daily Card Activity Report',
            'link' => '#reports/dailycardact',
            'type' => 'ISS'
          ),
          REPCARDINV_NO => array(
            'name' => 'Card Inventory Report',
            'link' => '#reports/cardinventory',
            'type' => 'ISS'
          ),*/
          
          REPBRANCHLOG_NO => array(
            'name' => 'Audit Trail',
            'link' => '#reports/auditlog',
            'type' => 'ACQ',
            'isChannel' => 1
          ),
          BARTSFILE_NO => array(
            'name' => 'BARTS File',
            'link' => '#reports/barts',
            'type' => 'ACQ',
            'isChannel' => 0
          ),
          BILLSPAYMENTREP_NO => array(
            'name' => 'Bills Payment Report',
            'link' => '#reports/billspaymentrep',
            'type' => 'ACQ',
            'isChannel' => 0
          ),
          PINMAILERBATCHREP_NO => array(
            'name' => 'PIN Mailer Batch Report',
            'link' => '#reports/pinmailer',
            'type' => 'ISS',
            'isChannel' => 0
          )
        );
      }
    
    
    $navMenu = array(
      CUSTMGMT_NO => array(
        'name' => 'Customer Management',
        'img' => 'images/custmgmt.png',
        'subMenu' => $custSubmenu,
        'type' => 'ISS'
      ),
      ACCNTMGMT_NO => array(
        'name' => 'Account Management',
        'img' => 'images/accountmgmt.png',
        'subMenu' => array(
          /*ACCNTINFO_NO => array(
            'name' => 'Account Information',
            'link' => '#accounts/info',
            'type' => 'ISS'
          ),*/
          ACCNTEDIT_NO => array(
            'name' => 'View Account',
            'link' => '#accounts/search',
            'type' => 'ISS'
          ),
          ACCTUPDATE_NO => array(
            'name' => 'Update Account',
            'link' => '#accounts/search',
            'type' => 'ISS'
          ),
          ACCNTNEW_NO => array(
            'name' => $newAccountName,
            'link' => $newAccountLink,
            'type' => 'ISS'
          ),
          ACCNTUPLOAD_NO => array(
            'name' => 'Account Batch Upload',
            'link' => '#accounts/batchupload',
            'type' => 'ISS'
          ),
          ACCNTVERIFY_NO => array(
            'name' => 'Account Verification',
            'link' => '#accounts/verification',
            'type' => 'ISS'
          ),
          ACCNTCHNGESTAT_NO => array(
            'name' => 'Account Change Status',
            'link' => '#accounts/search2',
            'type' => 'ISS'
          )
        ),
        'type' => 'ISS'
      ),
      CARDMGMT_NO => array(
        'name' => 'Card Management',
        'img' => 'images/cardmgmt.png',
        'subMenu' => $cardSubmenu,
        'type' => 'ISS'
      ),
      /*MOBILEMGMT_NO => array(
        'name' => 'Mobile Management',
        'img' => 'images/mobile.png',
        'subMenu' => array(
          MOBILEENROLL_NO => array(
            'name' => 'Mobile Enrollment',
            'link' => '#card/verify/mobile',
            'type' => 'ACQ'
          )
        ),
        'type' => 'ACQ'
      ),*/
      MONITORING_NO => array(
        'name' => 'Monitoring',
        'img' => 'images/monitoring.png',
        'subMenu' => array(
          MONATM_NO => array(
            'name' => 'ATM',
            'link' => '#monitoring/terminal',
            'type' => 'ISS'
          ),
          MONPOS_NO => array(
            'name' => 'POS',
            'link' => '#monitoring/pos',
            'type' => 'ISS'
          ),
          MONHOST_NO => array(
            'name' => 'Host',
            'link' => '#monitoring/host',
            'type' => 'ACQ'
          ),
          MONTRANS_NO => array(
            'name' => 'Transactions',
            'link' => '#monitoring/transactions',
            'type' => 'ACQ'
          ),
        ),
        'type' => 'ACQ'
      ),
      REPORTS_NO => array(
        'name' => 'Reports and Files',
        'img' => NULL,//'images/reports.png',
        'subMenu' => $reportsno,
        'type' => 'ACQ'
      ),
      MAINTENANCE_NO => array(
        'name' => 'Maintenance',
        'img' => 'images/maintenance.png',
        'subMenu' => array(
          GENERALSETTINGS_NO => array(
            'name' => 'General Settings',
            'link' => '#maintenance/settings',
            'type' => 'ISS'
          ),
          CARDPRODUCTLIST_NO => array(
            'name' => 'Card Product',
            'link' => '#maintenance/cardproductlist',
            'type' => 'ISS'
          ),
          USERTEMP_NO => array(
            'name' => 'User Access Templates',
            'link' => '#security/templates',
            'type' => 'ACQ'
          ),
          USERENTRY_NO => array(
            'name' => 'User Entry',
            'link' => '#security/users',
            'type' => 'ACQ'
          ),
          USERGROUPS_NO => array(
            'name' => 'User Groups',
            'link' => '#security/usergroups',
            'type' => 'ACQ'
          ),
          SVCCHARGES_NO => array(
            'name' => 'Service Charges',
            'link' => '#maintenance/servicecharges',
            'type' => 'ACQ'
          ),
          ALLOWSSETUP_NO => array(
            'name' => 'Allows Setup',
            'link' => '#maintenance/allowssetup',
            'type' => 'ISS'
          ),
          ALLOWSDEFAULT_NO => array(
            'name' => 'Allows Default',
            'link' => '#maintenance/allowsdefault',
            'type' => 'ISS'
          ),
          SRVCCODES_NO => array(
            'name' => 'Service Codes',
            'link' => '#maintenance/servicecodes',
            'type' => 'ISS'
          ),
          LIMITSDEFONLN_NO => array(
            'name' => 'Default Online Limits',
            'link' => '#maintenance/limitsdefonln',
            'type' => 'ISS'
          ),
          PRRULES_NO => array(
            'name' => 'Product Rules',
            'link' => '#maintenance/prrules',
            'type' => 'ACQ'
          ),
          CARDHOLDER_NO => array(
            'name' => 'Cardholders',
            'link' => '#maintenance/cardholders',
            'type' => 'ACQ'
          ),
          MAINTENANCEATM_NO => array(
            'name' => 'ATM',
            'link' => '#maintenance/atm',
            'type' => 'ACQ'
          ),
          MAINTENANCEPOS_NO => array(
            'name' => 'POS',
            'link' => '#maintenance/pos',
            'type' => 'ACQ'
          ),
          POSINST_NO => array(
            'name' => 'Institution',
            'link' => '#maintenance/xpartnerlist',
            'type' => 'ACQ'
          ),
          MOBILE_NO => array(
            'name' => 'Mobile',
            'link' => '#maintenance/mobilelist',
            'type' => 'ACQ'
          ),
          POSOUTLET_NO => array(
            'name' => 'Outlet',
            'link' => '#maintenance/xposoutletlist',
            'type' => 'ACQ'
          ),
          ATMKEYMGMT_NO => array(
            'name' => 'Security Key Management',
            'link' => '#maintenance/securitykey',
            'type' => 'ACQ'
          ),
          AREA_NO => array(
            'name' => 'Area List',
            'link' => '#maintenance/arealist',
            'type' => 'ACQ'
          ),
          DEPT_NO => array(
            'name' => 'Department List',
            'link' => '#maintenance/departments',
            'type' => 'ACQ'
          ),
          BRANCH_NO => array(
            'name' => 'Branch List',
            'link' => '#maintenance/brchlist',
            'type' => 'ACQ'
          ),
          LOCATION_NO => array(
            'name' => 'Location List',
            'link' => '#maintenance/locations',
            'type' => 'ACQ'
          ),
          CHANGEPW_NO => array(
            'name' => 'Change Password',
            'link' => '#maintenance/changepw',
            'type' => 'ACQ'
          )
        ),
        'type' => 'ACQ'
      ),
      PINMAILER_NO => array(
        'name' => 'PIN Mailer Management',
        'img' => 'images/monitoring.png',
        'subMenu' => array(
          PROCESSPINMAILER_NO => array(
            'name' => 'Process PIN Mailer (GUI)',
            'link' => '#',
            'type' => 'ACQ'
          ),
          REPRINTPINMAILER_NO => array(
            'name' => 'Reprint PIN Mailer (GUI)',
            'link' => '#',
            'type' => 'ACQ'
          ),
          LAYOUTSETTINGS_NO => array(
            'name' => 'Layout Settings (GUI)',
            'link' => '#',
            'type' => 'ACQ'
          )
        ),
        'type' => 'ACQ'
      ),
    );

    if (!$this->hasBatchCardUpload()) {
      unset($navMenu[ACCNTMGMT_NO]['subMenu'][ACCNTUPLOAD_NO]);
    }

    if (!$this->hasPOSCashOut()) {
      unset($navMenu[MAINTENANCE_NO]['subMenu'][POSOUTLET_NO]);
      unset($navMenu[MAINTENANCE_NO]['subMenu'][POSINST_NO]);
    }
    
    //if ( in_array($this->getUserGroup(), array(3) )) {
    /*if ($this->getUserGroup() === 3) {
      unset($navMenu[USERENTRY_NO]);
    }*/

    //filter ISS modules
    if(!$_SESSION['inst'])
    {
      // nothing  
    } else {
      if ($_SESSION['inst']['appType'] === 'ACQ') {
        
        foreach ($navMenu as $pos => $sub) {
          if ($sub['type'] === 'ISS') {
            unset($navMenu[$pos]);
          }
          foreach ($sub['subMenu'] as $posx => $submenu) {
            if ($submenu['type'] === 'ISS') {
              unset($navMenu[$pos]['subMenu'][$posx]);
            }
          }
        }
      }
    }
    return $navMenu;
  }
  
  // --------------------------------------------------------------------
  
  /**
   * Get Floating Menu
   *
   * if User Group is Branch User, disable User Entry Module
   *
   * @return  array
   */
  function getFloatingMenu($userAllows, $grpseqno, $userName = NULL, $userID = NULL, $isSuper = FALSE) {
    //$userAllows = '11111011111011111111111110101011001111111111111111100000000000000000000000000000000000000000000000000000000000000000000000000000';
    $menu = $this->getNavMenu();
    
    $menux = '<nav id="floatMenu"><div class="panel" title="Hide Panel"></div><ul>';
    
    $pdfGen = NULL;
    
    foreach ($menu as $pos => $sub) {     
      if (substr($userAllows, $pos - 1, 1) === '1') {
        
        if (in_array($pos, array(PINMAILER_NO))) {
          continue;
        }
        $menux .= '<li><span class="menu">'. $sub['name'] .'</span><ul>';

        
        //if menu is report
        if ( $pos === REPORTS_NO ) {
          foreach ( $sub['subMenu'] as $PDFRep => $rep ) {
            // exclude reports that is not in the PDF Generator.
            if ( !in_array($PDFRep, array(REPBRANCHLOG_NO, PINMAILERBATCHREP_NO, REPORTPROCLIST_NO, INPUTFILESREP_NO)) ) {
              // add to array if report is allowed.
              if (substr($userAllows, $PDFRep - 1, 1) === '1') {
                $pdfGen[] = $PDFRep;
              }
            } 
          }
        }
        
        $viewAcct = FALSE;
        $updateAcct = FALSE;
        $viewCard = FALSE;
        $updateCard = FALSE;
        foreach ($sub['subMenu'] as $posx => $submenu) {
          if ($posx === ACCNTEDIT_NO && substr($userAllows, $posx - 1, 1) === '1') {
            $viewAcct = TRUE;
          } elseif ($posx === ACCTUPDATE_NO && substr($userAllows, $posx - 1, 1) === '1') {
            $updateAcct = TRUE;
          } elseif ($posx === CARDUPDATE_NO && substr($userAllows, $posx - 1, 1) === '1') {
            $updateCard = TRUE;
          } elseif ($posx === CARDINFO && substr($userAllows, $posx - 1, 1) === '1') {
            $viewCard = TRUE;
          }
        }

        foreach ($sub['subMenu'] as $posx => $submenu) {
          
          // check if PDF Generator has zero report. if true then hide pdf generator from reports view.
          if ( $pos === REPORTS_NO ) {
            if ($posx === PDFGEN_NO && (count($pdfGen) < 1)) {
              continue;
            }
          } 
          
          // check if not report and allowed or if it is a report and allowed and not in pdfgenerator reports or report and pdfgenerator.
          if (
            ( !in_array($pos, array(REPORTS_NO,ACCNTMGMT_NO,CARDMGMT_NO)) && substr($userAllows, $posx - 1, 1) === '1' ) || 
            ( $pos === REPORTS_NO && substr($userAllows, $posx - 1, 1) === '1' 
            && in_array($posx, array(REPBRANCHLOG_NO, PINMAILERBATCHREP_NO, REPORTPROCLIST_NO, INPUTFILESREP_NO)) ) || 
            ( $pos === REPORTS_NO && in_array($posx, array(PDFGEN_NO)) ) ||
            (   $pos === ACCNTMGMT_NO && 
              substr($userAllows, $posx - 1, 1) === '1' &&
              !in_array($posx, array(ACCTUPDATE_NO)) 
            ) ||
            ( $pos === ACCNTMGMT_NO &&
              substr($userAllows, $posx - 1, 1) === '0' &&
              in_array($posx, array(ACCNTEDIT_NO)) && $updateAcct === TRUE
            ) ||
            (   $pos === CARDMGMT_NO && 
              substr($userAllows, $posx - 1, 1) === '1' &&
              !in_array($posx, array(CARDUPDATE_NO)) 
            ) ||
            (   $pos === CARDMGMT_NO && 
              substr($userAllows, $posx - 1, 1) === '0' &&
              !in_array($posx, array(CARDINFO)) && $updateCard === TRUE 
            )
          ) 
          { 
            if ($posx === ACCNTEDIT_NO) {
              if ($updateAcct === TRUE && $viewAcct === TRUE) {
                $menuname = 'View/Update Account';  
              } elseif ($updateAcct === TRUE && $viewAcct === FALSE) {
                $menuname = 'Update Account';
              } elseif ($updateAcct === FALSE && $viewAcct === TRUE) {
                $menuname = 'View Account';
              }
            } elseif ($posx === CARDINFO) {
              if ($updateCard === TRUE && $viewCard === TRUE) {
                $menuname = 'View/Update Card'; 
              } elseif ($updateCard === TRUE && $viewCard === FALSE) {
                $menuname = 'Update Card';
              } elseif ($updateCard === FALSE && $viewCard === TRUE) {
                $menuname = 'View Card';
              }
            } else {
              $menuname = $submenu['name'];
            }

            if (in_array($posx, array(PROCESSPINMAILER_NO,REPRINTPINMAILER_NO,LAYOUTSETTINGS_NO))) {
              continue;
            }
            $menux .= '<li><a href="'. $submenu['link'] .'">'. $menuname .'</a></li>';
          }
        }
        if (in_array($userID,array('core','core1')) && $userName == 'Super User' && $pos == MAINTENANCE_NO) {
          $menux .= '<li><a href="maintenance/brchlist/getcustomcardlist"><span>Export Cards</span></a></li>';/*
          $menux .= '<li><a href="maintenance/card/batchaccountlink"><span></span></a></li>';*/
        }
        /*if (($isSuper || !$_SESSION['isSuper']) && $pos == MAINTENANCE_NO) {
          $menux .= '<li><a href="#maintenance/loader"><span>Loader</span></a></li>';
        }*/
        $menux .= '</ul>';
      }
      //return $pdfGen;
    }
    
    /*$menux .= '<li><span class="menu">Dev</span>
      <ul>
        <li><a href="#maintenance/allowssetup">Allows Setup</a></li>
        <li><a href="#card/verify/billspayment">Bills Payment</a></li>
        <li><a href="#reports/pdfgenerator">PDF Generator</a></li>
        <li><a href="#monitoring/host">Host Monitoring</a></li>
      </ul>
    </li>';*/
    
    $menux .= '</ul></nav>';
    return $this->compressOutput($menux);
  }
  
  // --------------------------------------------------------------------
  
  /**
   * Get Floating Menu
   *
   * if User Group is Branch User, disable User Entry Module
   *
   * @return  array
   */
  function getFloatingMenux($userAllows, $grpseqno, $userName = NULL, $userID = NULL, $isSuper = FALSE) {
    //$userAllows = '11111011111011111111111110101011001111111111111111100000000000000000000000000000000000000000000000000000000000000000000000000000';
    $menu = $this->getNavMenu();
    
    $menux = '<div id="menu"><ul class="menu">';
    
    foreach ($menu as $pos => $sub) { 
      if (in_array($pos, array(PINMAILER_NO))) {
        continue;
      }
      if (substr($userAllows, $pos - 1, 1) === '1') {//$pos == 222 for testing
        
        $menux .= '<li><a href="#" class="parent"><span class="menuimg" style="background: url('. $sub['img'] .') no-repeat top left;"></span><span>'. $sub['name'] .'</span></a>';
        $menux .= '<div><ul>';


        $viewAcct = FALSE;
        $updateAcct = FALSE;
        $viewCard = FALSE;
        $updateCard = FALSE;
        foreach ($sub['subMenu'] as $posx => $submenu) {
          if ($posx === ACCNTEDIT_NO && substr($userAllows, $posx - 1, 1) === '1') {
            $viewAcct = TRUE;
          } elseif ($posx === ACCTUPDATE_NO && substr($userAllows, $posx - 1, 1) === '1') {
            $updateAcct = TRUE;
          } elseif ($posx === CARDUPDATE_NO && substr($userAllows, $posx - 1, 1) === '1') {
            $updateCard = TRUE;
          } elseif ($posx === CARDINFO && substr($userAllows, $posx - 1, 1) === '1') {
            $viewCard = TRUE;
          }
        }

        foreach ($sub['subMenu'] as $posx => $submenu) {
          if (
            ( !in_array($pos, array(REPORTS_NO,ACCNTMGMT_NO,CARDMGMT_NO)) && substr($userAllows, $posx - 1, 1) === '1' ) || 
            ( $pos === REPORTS_NO && in_array($posx, array(PDFGEN_NO, REPBRANCHLOG_NO, PINMAILERBATCHREP_NO, REPORTPROCLIST_NO, INPUTFILESREP_NO)) ) ||
            (   $pos === ACCNTMGMT_NO && 
              substr($userAllows, $posx - 1, 1) && 
              !in_array($posx, array(ACCTUPDATE_NO)) 
            ) ||
            (   $pos === ACCNTMGMT_NO &&
              substr($userAllows, $posx - 1, 1) === '0' &&
              in_array($posx, array(ACCNTEDIT_NO)) && $updateAcct === TRUE
            ) ||
            (   $pos === CARDMGMT_NO && 
              substr($userAllows, $posx - 1, 1) &&
              !in_array($posx, array(CARDUPDATE_NO)) 
            ) ||
            (   $pos === CARDMGMT_NO && 
              substr($userAllows, $posx - 1, 1) === '0' &&
              !in_array($posx, array(CARDINFO)) && $updateCard === TRUE
            )
          )
          {       
            if ($posx === ACCNTEDIT_NO) {
              if ($updateAcct === TRUE && $viewAcct === TRUE) {
                $menuname = 'View/Update Account';  
              } elseif ($updateAcct === TRUE && $viewAcct === FALSE) {
                $menuname = 'Update Account';
              } elseif ($updateAcct === FALSE && $viewAcct === TRUE) {
                $menuname = 'View Account';
              }
            } elseif ($posx === CARDINFO) {
              if ($updateCard === TRUE && $viewCard === TRUE) {
                $menuname = 'View/Update Card'; 
              } elseif ($updateCard === TRUE && $viewCard === FALSE) {
                $menuname = 'Update Card';
              } elseif ($updateCard === FALSE && $viewCard === TRUE) {
                $menuname = 'View Card';
              }
            } else {

              if (in_array($posx, array(PROCESSPINMAILER_NO,REPRINTPINMAILER_NO,LAYOUTSETTINGS_NO))) {
                continue;
              }
              $menuname = $submenu['name'];
            }
            $menux .= '<li><a href="'. $submenu['link'] .'"><span>'. $menuname .'</span></a></li>';
          }
        }
        if (in_array($userID,array('core','core1')) && $userName == 'Super User' && $pos == MAINTENANCE_NO) {
          $menux .= '<li><a href="maintenance/brchlist/getcustomcardlist"><span>Export Cards</span></a></li>';/*
          $menux .= '<li><a href="maintenance/card/batchaccountlink"><span></span></a></li>';*/
        }
        if ($isSuper || $_SESSION['isSuper']) {
          $menux .= '<li><a href="#maintenance/loader"><span>Loader</span></a></li>';
        }
        $menux .= '</ul></div>';
      }
    }
    
    /*$menux .= '<li><a href="#" class="parent"><span class="menuimg" style="background: url(images/dev.png) no-repeat top left;"></span><span>Dev</span></a>'.
        '<div><ul>'.
        '<li><a href="#monitoring/host"><span>Host Monitoring</span></a></li>'.
        '<li><a href="#maintenance/allowssetup"><span>Allows Setup</span></a></li>'.
        '<li><a href="#maintenance/allowsdefault"><span>Allows Default</span></a></li>'.
        '<li><a href="#maintenance/servicecodes"><span>Service Codes</span></a></li>'.
        '<li><a href="#maintenance/limitsdefonln"><span>Limits Default (Online)</span></a></li>'.
        '<li><a href="#reports/pdfgenerator"><span>PDF Generator</span></a></li>'.
        '<li><a href="#tellering/deposittrx"><span>Deposit Transaction</span></a></li>'.
        '<li><a href="#tellering/withdrawaltrx"><span>Withdrawal Transaction</span></a></li>'.
        '<li><a href="#tellering/cardinquiry"><span>Card Inquiry</span></a></li>'.
        '</ul></div>';*/
    //        
    /*$menux .= '<li><span class="menu">Dev</span>
      <ul>
        <li><a href="#reports/acq">Acquirer Per Terminal</a></li>
        <li><a href="#reports/iss">Issuer Per Branch</a></li>
        <li><a href="#reports/trxperterm">Transactions Per Terminal</a></li>
        <li><a href="#maintenance/atmkey">ATM Key Management</a></li>
        <li><a href="#card/enroll">Card Enrollment</a></li>
        <li><a href="#card/search">Card Search</a></li>
        <li><a href="#card/cardlist">Card List</a></li>
        <li><a href="#card/changelink">Change Customer Link</a></li>
        <li><a href="#card/verification">Card Verification</a></li>
        <li><a href="#card/changetype">Card Account Type Change</a></li>
      </ul>
    </li>';*/
    
    /*$menux .= '<li><span class="menu">Dev</span>
      <ul>
        <li><a href="#sanitizer">Sanitizer</a></li>
      </ul>
    </li>';*/
    
    //<li><a href="#card/mobileinfo">Mobile Information</a></li>
    //<li><a href="#card/verifymobile">Search Mobile</a></li>
    
    $menux .= '</ul></div>';
    return $this->compressOutput($menux);
  }
}
/* End of file Core.php */
/* Location: ./application/libraries/Core.php */
