<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Portal extends CI_Controller {
	
	function index()
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
		
		$inst = array(
				'bankName' => 'ISLA Bank, Inc.',
				'siteURL' => 'http://isla.ph/',
				'css' => 'isla' . DIRECTORY_SEPARATOR,
				'appType' => 'ISS',
				'theme' => 1,
				'prCode' => FALSE
			);
		
				
		$_SESSION['inst'] = $inst;
		
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
		
		$this->load->view('portal', $data);
	}
}
/* End of file portal.php */
/* Location: ./application/controllers/portal.php */
