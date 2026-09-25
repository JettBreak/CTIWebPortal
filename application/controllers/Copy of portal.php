<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Portal extends CI_Controller {
	
	function index($inst = 'coreware')
	{
		$this->load->library('session');
		$this->load->library('core');
		$this->load->helper('html');
		$this->load->helper('url');
		
		$session = $this->session;
		$core	 = $this->core;
		
		$data['sessionID'] = NULL;
		$data['isTeller'] = NULL;
		$data['sessionExp'] = 0;
		$data['navMenu'] = '';
		$data['header'] = '<tr><td>&nbsp;</td></tr>';
		$data['js']	= '';
		
		$loggedIn = $core->isLoggedIn();
		
		$instList = array(
			'coreware' => array(
				'bankName' => 'Coreware',
				'siteURL' => 'http://www.corewaretech.com/',
				'css' => 'coreware' . DIRECTORY_SEPARATOR,
				'theme' => 1,
				'appType' => 'ISS',
				'host' => '192.168.168.50',
				'db1' => 'coreapp_dev',
				'db2' => 'coresys_dev',
				'prCode' => FALSE,
				'isoCust'=> TRUE,
				'https' => FALSE 
				/*'host' => '10.252.239.244',
				'db1' => 'coreapp_ebi',
				'db2' => 'coresys_ebi'*/
			),
			'tysb' => array(
				'bankName' => 'TONGYANG Savings Bank, Inc.',
				'siteURL' => 'http://www.tongyang.com.ph/en/',
				'css' => 'tyb' . DIRECTORY_SEPARATOR,
				'theme' => 1,
				'appType' => 'ISS',
				'host' => '192.168.168.50',
				'db1' => 'coreapp_dev',
				'db2' => 'coresys_dev',
				'prCode' => FALSE,
				'https' => FALSE
			),
			'koop' => array(
				'bankName' => 'Koop Cash',
				'siteURL' => 'http://www.corewaretech.com/',
				'css' => 'koop' . DIRECTORY_SEPARATOR,
				'theme' => 2,
				'appType' => 'ISS',
				'host' => '192.168.168.50',
				'db1' => 'coreapp_dev',
				'db2' => 'coresys_dev',
				'prCode' => FALSE,
				'https' => TRUE
			),
			'pbcom' => array(
				'bankName' => 'PBCOM',
				'siteURL' => 'http://www.pbcom.com.ph/',
				'css' => 'pbcom' . DIRECTORY_SEPARATOR,
				'theme' => 1,
				'appType' => 'ISS',
				'host' => '192.168.168.50',
				'db1' => 'coreapp_dev',
				'db2' => 'coresys_dev',
				'prCode' => FALSE,
				'https' => TRUE
			),
			'ebank' => array(
				'bankName' => 'Enterprise Bank, Inc.',
				'siteURL' => 'http://ebi.ph/',
				'css' => 'ebank' . DIRECTORY_SEPARATOR,
				'theme' => 1,
				/*'appType' => 'ISS',
				'host' => '192.168.168.23',
				'db1' => 'coreapp_ebank',
				'db2' => 'coresys_ebank'*/
				'appType' => 'ISS',
				/*'host' => '192.168.168.50',
				'db1' => 'coreapp_demo',
				'db2' => 'coresys_demo'*/
				'host' => '192.168.168.50',
				'db1' => 'coreapp_dev',
				'db2' => 'coresys_dev',
				'prCode' => FALSE,
				'https' => FALSE
			),
			'skyy' => array(
				'bankName' => 'Skyy Services',
				'siteURL' => 'http://skyyservices.com/',
				'css' => 'skyy' . DIRECTORY_SEPARATOR,
				'theme' => 1,
				'appType' => 'ISS',
				'host' => '192.168.168.50',
				'db1' => 'coreapp_fusion',
				'db2' => 'coresys_fusion',
				'prCode' => FALSE,
				'https' => TRUE
				//'host' => '192.168.168.23',
				//'db1' => 'coreapp_ebank',
				//'db2' => 'coresys_ebank'
			),
			'qcrb' => array(
				'bankName' => 'QCRB',
				'siteURL' => 'http://www.qcrblive.com/',
				'css' => 'qcrb' . DIRECTORY_SEPARATOR,
				'theme' => 1,
				'appType' => 'ISS',
				'host' => 'localhost',
				'db1' => 'coreapp_dev',
				'db2' => 'coresys_dev',
				'prCode' => TRUE,
				'https' => TRUE
			),
			'postal' => array(
				'bankName' => 'Postal Bank',
				'siteURL' => 'http://www.postalbank.gov.ph/',
				'css' => 'postal' . DIRECTORY_SEPARATOR,
				'theme' => 1,
				'appType' => 'ISS',
				'host' => '192.168.168.50',
				'db1' => 'coreapp_demo',
				'db2' => 'coresys_demo',
				'prCode' => FALSE,
				'https' => TRUE
			),
			'ahb' => array(
				'bankName' => "D' Asian Hills Bank",
				'siteURL' => 'http://www.asianhillsbank.com/',
				'css' => 'ahb' . DIRECTORY_SEPARATOR,
				'theme' => 1,
				'appType' => 'ISS',
				'host' => '192.168.168.50',
				'db1' => 'coreapp_dev',
				'db2' => 'coresys_dev',
				'prCode' => FALSE,
				'https' => TRUE
			),
			'bm' => array(
				'bankName' => 'Banko Mabuhay',
				'siteURL' => 'http://www.bangkomabuhay.com/',
				'css' => 'bm' . DIRECTORY_SEPARATOR,
				'theme' => 1,
				'appType' => 'ISS',
				'host' => '192.168.168.50',
				'db1' => 'coreapp_dev',
				'db2' => 'coresys_dev',
				'prCode' => FALSE,
				'https' => TRUE
			),
			'isla' => array(
				'bankName' => 'ISLA Bank',
				'siteURL' => 'http://www.islabank.com/',
				'css' => 'isla' . DIRECTORY_SEPARATOR,
				'theme' => 1,
				'appType' => 'ISS',
				'host' => '192.168.168.50',
				'db1' => 'coreapp_dev',
				'db2' => 'coresys_dev',
				'prCode' => FALSE,
				'https' => TRUE
			),
			'nwtf' => array(
				'bankName' => 'NWTF, Inc.',
				'siteURL' => 'http://www.nwtf.ph/',
				'css' => 'nwtf' . DIRECTORY_SEPARATOR,
				'theme' => 2,
				'appType' => 'ISS',
				'host' => '192.168.168.50',
				'db1' => 'coreapp_dev',
				'db2' => 'coresys_dev',
				'prCode' => FALSE,
				'https' => TRUE
			),
			'ucpb' => array(
				'bankName' => 'UCPB',
				'siteURL' => 'http://www.ucpb.com/',
				'css' => 'ucpb' . DIRECTORY_SEPARATOR,
				'theme' => 1,
				'appType' => 'ISS',
				'host' => '192.168.168.50',
				'db1' => 'coreapp_dev',
				'db2' => 'coresys_dev',
				'prCode' => FALSE,
				'https' => TRUE
				//'host' => '192.168.168.23',
				//'db1' => 'coreapp_ebank',
				//'db2' => 'coresys_ebank'
			),
			'ucpbsavings' => array(
				'bankName' => 'UCPB Savings',
				'siteURL' => 'http://www.ucpb.com/',
				'css' => 'ucpbsavings' . DIRECTORY_SEPARATOR,
				'theme' => 1,
				/*'appType' => 'ISS',
				'host' => '192.168.168.23',
				'db1' => 'coreapp_ebank',
				'db2' => 'coresys_ebank'*/
				'appType' => 'ISS',
				/*'host' => '192.168.168.50',
				'db1' => 'coreapp_demo',
				'db2' => 'coresys_demo'*/
				'host' => '192.168.168.50',
				'db1' => 'coreapp_dev',
				'db2' => 'coresys_dev',
				'prCode' => FALSE,
				'https' => FALSE
			),
			'dipolog' => array(
				'bankName' => 'Banco Dipolog, Inc.',
				'siteURL' => 'http://www.bancodipolog.com/',
				'css' => 'dipolog' . DIRECTORY_SEPARATOR,
				'theme' => 1,
				'appType' => 'ISS',
				'prCode' => FALSE,
				'https' => TRUE,
				'host' => '192.168.168.50',
				'db1' => 'coreapp_dev',
				'db2' => 'coresys_dev',
			),
			'planbank' => array(
				'bankName' => 'PLANBANK',
				'siteURL' => 'http://www.planbank.org/',
				'css' => 'planbank' . DIRECTORY_SEPARATOR,
				'theme' => 1,
				'appType' => 'ISS',
				'prCode' => FALSE,
				'https' => TRUE,
				'host' => '192.168.168.50',
				'db1' => 'coreapp_dev',
				'db2' => 'coresys_dev',
			),
			'prsavings' => array(
				'bankName' => 'PR Savings Bank',
				'siteURL' => 'http://prsavingsbank.com.ph/',
				'css' => 'prsb' . DIRECTORY_SEPARATOR,
				'theme' => 1,
				'appType' => 'ISS',
				'prCode' => FALSE,
				'https' => FALSE,
				'host' => '192.168.168.50',
				'db1' => 'coreapp_dev',
				'db2' => 'coresys_dev',
			),
			'dcdb' => array(
				'bankName' => 'Dumaguete City Development Bank, Inc.',
				'siteURL' => 'http://dumaguetebank.com/',
				'css' => 'dcdb' . DIRECTORY_SEPARATOR,
				'theme' => 1,
				'appType' => 'ISS',
				'prCode' => FALSE,
				'https' => FALSE,
				'host' => '192.168.168.50',
				'db1' => 'coreapp_dev',
				'db2' => 'coresys_dev',
			),
			'erbi' => array(
				'bankName' => 'Entrepreneur Bank',
				'siteURL' => '',
				'css' => 'erbi' . DIRECTORY_SEPARATOR,
				'theme' => 1,
				'appType' => 'ISS',
				'prCode' => FALSE,
				'https' => FALSE,
				'host' => '192.168.168.50',
				'db1' => 'coreapp_dev',
				'db2' => 'coresys_dev',
			),
			'wealthbank' => array(
				'bankName' => 'Wealth Bank',
				'siteURL' => '',
				'css' => 'wealthbank' . DIRECTORY_SEPARATOR,
				'theme' => 1,
				'appType' => 'ISS',
				'prCode' => FALSE,
				'https' => FALSE,
				'host' => '192.168.168.50',
				'db1' => 'coreapp_dev',
				'db2' => 'coresys_dev',
			),
			'keb' => array(
				'bankName' => 'Korea Exchange Bank',
				'siteURL' => 'http://www.keb.co.kr',
				'css' => 'keb' . DIRECTORY_SEPARATOR,
				'theme' => 1,
				'appType' => 'ISS',
				'prCode' => FALSE,
				'https' => FALSE,
				'host' => '192.168.168.50',
				'db1' => 'coreapp_dev',
				'db2' => 'coresys_dev',
			)
		);
		
		$inst = $instList[$inst];
		
		$_SESSION['inst'] = $inst;
		
		//force https
		if ($inst['https'] === TRUE) {
			if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on') {
				if(!headers_sent()) {
					header("Status: 301 Moved Permanently");
					header(sprintf(
						'Location: https://%s%s',
						$_SERVER['HTTP_HOST'],
						$_SERVER['REQUEST_URI']
					));
					//exit();
				}
			}
		}
		
		if ($loggedIn === TRUE) {
			$data['sessionID'] 	= TRUE;
			$data['isTeller'] = $core->isTeller();
			$data['sessionExp'] = $core->getSessionExp();
			$branchCode = $core->getBranchCode();
			$workstation = $core->getWorkstation();
			$instName 	= $core->getInstName();
			$userName 	= '<strong>Username: </strong>'. $core->getUserName();
			$topRight 	= '<span><a href="#" id="logout">Logout</a></span>';
			$branch 	= '<strong>Branch: </strong>'. $core->getBranchName();
			$address 	= '<strong>Address: </strong>'. $core->getAddress();
			
			if ($core->displayLastLogIn()) {
				$lastLogin = $core->getLastLogIn();
			} else {
				$lastLogin = '';
			}
			
			$currentDT = '<strong>Current Date: </strong>'.date('l, F j, Y');
			
			switch ($inst['theme']) {
				case 1:
					$header = '<tr>
								<td id="instName" rowspan="2" style="vertical-align:middle !important">'. $inst['bankName'] .'</td>
								<td id="branch">'. $branch .'</td>
								<td id="userName">'. $userName .'</td>
								<td id="topRight">'. $topRight .'</td>
							</tr>
							<tr>
								<td id="currentDT">'. $currentDT .'</td>
								<td id="lastLogin">'. $lastLogin .'</td>
								<td></td>
								<td></td>
							</tr>';
					
					$navMenu = $core->getFloatingMenu($core->getUserAllows(), $core->getUserGroup());
					
					$js = 'initFloatMenu();';
					
					break;
				case 2:
					$header = '<tr>
								<td id="instName" rowspan="2" style="vertical-align:middle !important">'. $inst['bankName'] .'</td>
								<td colspan="2">
									'. $core->getFloatingMenux($core->getUserAllows(), $core->getUserGroup()) .'     
								</td>
								<td id="topRight">'. $topRight .'</td>
							</tr>';
						
					$navMenu = '';
					
					$js = '';
					
					break;
			}
			
			if ($core->isTeller()) {
				$header = '<tr>
					<td id="instName" rowspan="2" style="vertical-align:middle !important; font-size: 30px;">'. $inst['bankName'] .'</td>
					<td style="font-size: 15px;"><strong>Teller Name:</strong> '.$core->getUserName().'</td>
					<td id="topRight" colspan="2">'. $topRight .'</td>
				</tr>';
			}
			
			$data['navMenu'] = $navMenu;
			$data['header'] = $core->compressOutput($header);
			$data['js'] = $js;
		}

		$data['pageTitle'] 	= APPNAME;
		$data['pageFooter'] = 'Copyright &copy; 2005-2012 '. anchor('http://www.corewaretech.com', 'Coreware Technologies, Inc.', 'target="_blank"') .' All rights reserved.<br />'. APPNAME ." ". CURRENTVER;
		
		/*$data['isSuper'] = 'false';
		if ($core->getUserGroup() === '0') {
			$data['isSuper'] = 'true';
		}*/
		
		$data['folder'] = $inst['css'];
		$data['menucss'] = '';
		
		if ($inst['theme'] == 2) {
			$data['menucss'] = '@import "css/<?php echo $folder; ?>menu.css";';
		}

		$this->load->view('portal', $data);
	}
}