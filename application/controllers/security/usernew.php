<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class UserNew extends CI_Controller {
	
	function __construct()
	{
		parent::__construct();
		$this->load->library('core');
		$this->core->checkUserAllows(USERENTRY_NO);
		
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
	
	//$g = User Group
	function index()
	{
		$this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
		$this->load->model('coreapp/user_model');
		$this->load->library('shortxml');
		
		$xml = $this->shortxml;
		
		//get user groups
		$result = $this->user_model->getWebUserGroupList();
		
		//init vars !important
		$data['userGroups'] = '<select id="grpseqno" name="grpseqno" style="width:262px" class="validate[required]">';
		$data['minChar'] = NULL;
		$data['setPassAttr'] = NULL;
		$currentGroup = NULL;
		//end
		
		if ($result->num_rows() > 0) {			
			$current = $result->row_array();
			$xml->setXML($current['xml1']);
			
			$currentGroup = $current['grpseqno'];
			$data['minChar'] = $xml->getValue('MINCHAR') ? $xml->getValue('MINCHAR') : 0;
			$data['setPassAttr'] = ' class="hidden"';//substr($xml->getValue('PASSOPTION'), 4, 1) === '1' ? ' class="hidden"' : NULL;
			
			foreach ($result->result_array() as $row) {
				$xml->setXML($row['xml1']);
				$minChar = $xml->getValue('MINCHAR') ? $xml->getValue('MINCHAR') : 0;
				//$selected = $group === $row['grpseqno'] ? ' selected' : NULL;
				
				$setPass = substr($xml->getValue('PASSOPTION'), 4, 1) !== '1' ? ' setpass' : NULL;
				$data['userGroups'] .= '<option value="'. $row['grpseqno'] .'" minchar="'. $minChar .'"'. $setPass .'>'. $row['description'] .'</option>';		
			}
		} else {
			$data['userGroups'] = '<option value="">No User Groups Defined</option>';
		}
		$data['userGroups'] .= '</select>';
		
		$result->free_result();
		$result->next_result();
		//end
		
		//get branches
		if (!$branches = $this->cache->get($this->core->getSessionID() . 'branches')) {
			$this->load->model('coreapp/branch_model');
			$result = $this->branch_model->getBranchList();
		
			$branches = $result->result_array();
			
			$result->free_result();
			$result->next_result();
			$this->cache->save($this->core->getSessionID() .'branches', $branches, CACHE_TTL);
		}
		
		$data['branches'] = NULL;
		
		if (count($branches) > 0) {
			foreach ($branches as $row) {
				//if user branch is not allowed to monitor users from other branches
				if (!$this->core->canUser() && $row['brseqno'] === $this->core->getBranchID()) {
					$data['branches'] = '<option value="'. $row['brseqno'] .'">'. $row['brname'] .'</option>';
					break;
				}
				$data['branches'] .= '<option value="'. $row['brseqno'] .'">'. $row['brname'] .'</option>';
			}
		} else {
			$data['branches'] = '<option value="">No Branches Defined</option>';
		}
		//end
		
		//departments
		$result = $this->user_model->getDepartments();
		
		$data['departments'] = NULL;
		if ($result->num_rows() > 0) {	
			foreach ($result->result_array() as $row) {
				$data['departments'] .= '<option value="'. $row['codeseqno'] .'">'. $row['codevalue'] .'</option>';
			}
		} else {
			$data['departments'] = '<option value="">No Departments Defined</option>';
		}
		
		$result->free_result();
		$result->next_result();
		//end
		
		$grpseqno = $this->core->getUserGroup();
		$result = $this->user_model->getUserTemplates($grpseqno);
		
		$data['templates'] = NULL;
		$currentAllows = NULL;
		$data['tmseqno'] = NULL;
		$data['grpseqno'] = NULL;
		$data['emailAddr'] = NULL;
		
		if ($result->num_rows() > 0) {
			$this->load->library('coreconverters');
			
			foreach ($result->result_array() as $row) {
				$xml->setXML($row['xml1']);
				$tmseqno = $row['tmseqno'];
				$desc = $row['description'];
				$grpseqno = $row['grpseqno'];
				$grpdesc = $row['grpdesc'];
				$allows = $row['allows'] ? $this->coreconverters->asciiHexToBin($row['allows']) : '';
				
				if ($xml->getValue('PREDEFINED') === 'Y') {
					$predefined = ' predefined ';
				} else {
					$predefined = NULL;
				}
				
				if ($currentGroup === $grpseqno) {// && $xml->getValue('PREDEFINED') === 'Y'
					$selected = ' selected ';
					$currentAllows = $allows;
				} else {
					$selected = NULL;
				}
				
				$data['templates'] .= '<option 
					id="x'. $tmseqno .'"
					value="'. $tmseqno .'"
					grpseqno="'. $grpseqno .'"
					grpdesc="'. $grpdesc .'"
					allows="'. $allows .'"
					'. $predefined . $selected .'>'. $desc .'</option>';
			}
		}
		
		//treeview
		$menu = $this->core->getNavMenu();
		//$forbidden = $this->core->getForbiddenModules();
		
		$data['menuList'] = '<ul id="navMenu">';
		foreach ($menu as $pos => $sub) {
			$checked = substr($currentAllows, $pos - 1, 1) === '1' ? ' checked' : NULL;

			$data['menuList'] .= '<li><input type="checkbox" id="'.$pos.'" name="allows[]" value="'.$pos.'" class="category"'.$checked.'/><span class="category">'. $sub['name'] .'</span><ul>';
			foreach ($sub['subMenu'] as $posx => $submenu) {
				if ( in_array($posx, array(PDFGEN_NO)) ) {
					continue;
				}
				$checked = substr($currentAllows, $posx - 1, 1) === '1' ? ' checked' : NULL;
							
				$data['menuList'] .= '<li><input type="checkbox" id="'.$posx.'" name="allows[]" value="'.$posx.'" class="'.$pos.'sub"'.$checked.'/>'. $submenu['name'] .'</li>';
			}
			$data['menuList'] .= '</ul></li>';
		}
		$data['menuList'] .= '</ul>';
		//treeview
		
		$data['title'] = 'User Enrollment';
		$data['btnLabel'] = 'Submit';
		$data['userIDValue'] = NULL;
		$data['userIDAttr'] = NULL;
		$data['userNameValue'] = NULL;
		$data['dtCreatedVisibility'] = ' style="display:none"';
		$data['dtCreated'] = NULL;
		$data['userStatus'] = 'New';
		$data['userPosition'] = NULL;
		$data['waitMsg'] = 'Sending new user entry...';
		$data['confirmMsg'] = 'Are all entries correct?';
		$data['branch'] = $this->core->getBranchName();
		$data['isUpdate'] = ' disabled';
		$data['sessionExp'] = $this->core->getSessionExp();
		$this->load->view('security/userx', $data);
	}
}