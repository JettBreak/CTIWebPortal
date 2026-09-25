<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Generation extends CI_Controller {
	
	function __construct()
	{
		parent::__construct();
		$this->load->library('core');
		$this->core->checkUserAllows(CARDGENERATION_NO);
	}
	
	function index()
	{
		$this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
		
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
				if (!$this->core->isHeadOffice() && $row['brseqno'] === $this->core->getBranchID()) {
					$data['branches'] = '<option value="'. $row['brseqno'] .'">'. $row['brname'] .'</option>';
					break;
				}
				$data['branches'] .= '<option value="'. $row['brseqno'] .'">'. $row['brname'] .'</option>';
			}
		} else {
			$data['branches'] = '<option value="">No Branches Defined</option>';
		}
		
		if ($this->core->isHeadOffice()) {
			$data['uiToolbar'] = "$('.ui-toolbar:even').append($('#customToolbar .top').html());";
		} else {
			$data['uiToolbar'] = NULL;
		}
		
		$this->load->view('card/generation', $data);
	}
	
	function getData()
	{
		$this->load->model('coreapp/card_model');
		$this->load->library('shortxml');		
		
		$card = $this->card_model;
		$core = $this->core;
		$xml  = $this->shortxml;
		
		if ($core->isHeadOffice()) {
			$branchID = $this->input->get('brseqno', TRUE);
		} else {
			$branchID = $core->getBranchID();
		}
		
		$ipAddress = $core->getIPAddress();
		$workstation = $core->getWorkstation();
		$userAudit = $core->getUserID();
		$sessionID = $core->getSessionID();

		$result = $card->getCardForGenList($branchID, $ipAddress, $workstation, $userAudit, $sessionID);
		
		$details = array();
		if ($result->num_rows() > 0) {
			
			
			foreach ($result->result_array() as $row) {
				$orderNo = $row['orderno'];
				$brseqno = $row['brseqno'];
				$brName = $row['brname'];
				$dt 	= $core->formatDate('F j, Y g:i A', $row['dtrequested']);
				$type 	= $row['accttype'];
				$qty 	= $row['reqqty'];
				$genCnt = $row['gencount'];
				$status = $row['status'];
				$progress = round($genCnt/$qty * 100) . '%';
				
				$xml->setXML($row['xml1']);
				$remarks = $xml->getValue('DETAIL');
				
				$disabled = NULL;
				if ($status === 'PROCESSING') {
					$disabled = ' disabled';
				}
				
				$val = implode(',', array($orderNo, $brseqno));
				
				$details[] = array(
					'<input type="checkbox" name="order[]" value="' . $val . '"' . $disabled . '/>',
					$orderNo,
					$brName,
					$dt,
					$type,
					$qty,
					$genCnt,
					$status,
					$progress
				);
			}
		}
		
		echo json_encode(array(
			'details' => $details
		));
	}
	
	/*function _xxgenerateCards($orderNo, $brseqno)
	{
		$this->load->model('coreapp/card_model');
		$query = NULL;
		for ($i = 1; $i <= 1000; $i++) {
			
			
			$query[] = "CALL sp_generatecardseries(@series, @err)";
			$query[] = "SELECT @series as series, @err as err";
		//$this->db->trans_start();
		//$this->db->query($query, func_get_args());
		//$result = $this->db->query("SELECT @series as series, @err as err");
			
			
			
			//$prkey = 32232200100091000 + $i;
			//$query[] = "INSERT into prmaster SET prtype  = 'CARD',prkey   = ".$prkey.",cifseqno = 1,brseqno  = 1,agent    = 0,outlet   = 1,status   = 9,accttype = 80,dtlastact= NULL,dtlastmov= NULL,payseqno = 0,isbalance='N',allows   = 'DFFFFFFBFFFF0000',useraudit= 'franzadmin',wkstn = '192.168.168.67@web'";
		}
		return $this->card_model->executeQuery($query);
	}*/
	
	function _generateCards($orderNo, $brseqno)
	{
		$this->load->model('coreapp/card_model');
		$this->load->library('shortxml');

		$franz = NULL;
		$progress = 0;
		
		$card = $this->card_model;
		$core = $this->core;
		$xml = $this->shortxml;
		
		$card->db->trans_begin();
		
		$userAudit = $core->getUserID();
		$sessionID = $core->getSessionID();
		
		//validate session
		$result = $card->checkLogin($userAudit, $sessionID);
		$row = $result->row_array();
		
		$result->free_result();
		$result->next_result();
		
		if ($row['errno'] === '8') {
			$card->db->trans_commit();
			echo json_encode(array(
				'success' => FALSE,
				'message' => $row['errmsg'],
				'errorno' => $row['errno']
			));
			exit();
		}
		
		//check if orderNo exists
		$result = $card->getCardOrder($orderNo, $brseqno);	
		
		if ($result->num_rows() > 0) {
			$query = NULL;
			
			$row = $result->row_array();
			
			$status 	= $row['status'];
			$gencount 	= $row['gencount'];
			$qty 		= $row['reqqty']; 
			$acctType 	= $row['accttype']; 
			$bin 		= $row['bin']; 
			$isper 		= $row['ispersonalized'];
			$brcode 	= $row['brid'];
			$xml1 		= $row['xml1'];
			
			$result->free_result();
			$result->next_result();
			
			$result = $card->getCardFormat($bin);// $expyears, $format, $wts, $grace, $minday, $err);
			
			$row = $result->row_array();
			
			$expyears 	= $row['expyears'];
			$format 	= $row['format'];
			$wts 		= $row['wts'];
			$grace 		= $row['grace'];
			$minday 	= $row['minday'];

			if ($row['err'] === '1') {
				// Card Format not yet defined //
			} else if ($status === '1') {
				//$onlinelmt = 12;
				//$offlinelmt = 0;
				//$pinctr = 3;
				//$initAllows = 'DFFFFFFBFFFF0000';
				$dtexp = date('Y-m-d', strtotime('+'. $row['expyears'] .' year'));
				
				$result->free_result();
				$result->next_result();
				
				//get default limit
				$result = $card->getLimitSeqno($brseqno, $acctType, 'ONLN');//, $onlinelmt, $err);
				$row = $result->row_array();
				
				$onlinelmt = $row['onlinelmt'];		//12
				if ($row['err'] === '1') {
					// limits not yet defined 
				}

				$result->free_result();
				$result->next_result();
				
				//get default limit
				$result = $card->getLimitSeqno($brseqno, $acctType, 'OFLN');//, $offlinelmt, $err);
				$row = $result->row_array();
				
				$offlinelmt = $row['offlinelmt'];		//0
				if ($row['err'] === '1') {
					 //limits not yet defined 
				}

				$result->free_result();
				$result->next_result();
				
				//get limit pin counter
				$result = $card->getLimitPINCountMax($acctType);
				$row = $result->row_array();
				
				$pinctr = $row['pinctr'];				//3
				
				$result->free_result();
				$result->next_result();
				
				//get allows default
				$result = $card->getAccountAllowsDefault($acctType);
				$row = $result->row_array();
				
				$initAllows = $row['initAllows'];			//DFFFFFFBFFFF0000*/
				
				if ($isper === 'Y') {
					$xml->setXML($xml1);
					$cifseqno = $xml->getValue('CUSTNO');
					$emboss	= $xml->getValue('EMBOSSNAME');
				} else {
					$cifseqno = 0;
					$emboss = '';
				}
				
				$query = "UPDATE crdorder SET ".
					"status = 7 ".
					"WHERE ".
					"orderno = ?".
					" AND status = 1";
				
				$params = array($orderNo);
				
				$card->db->query($query, $params);
				
				$first = 0;
				
				$result->free_result();
				$result->next_result();
				
				for ($ctr = 1;  $ctr <= $qty; $ctr++) {
					
					$isProductExists = TRUE;
					while ($isProductExists) {
						$result = $card->generateCardSeries();
					
						$row = $result->row_array();
						
						$series = $row['series'];
						//$series = 0;//
						$chkDigit = 0;
						
						$result->free_result();
						$result->next_result();
							
						if (strpos($format, 'C') !== FALSE) { //strpos() Returns the position as an integer. If needle is not found, strpos() will return boolean FALSE.
							$tmp = str_replace('C','', $format);
							
							$result = $card->getCardNo($tmp, $bin, $series, $chkDigit, $brcode);
							$row = $result->row_array();
							
							$cardNo = str_replace('-', '', $row['cardno']);
							$result->free_result();
							$result->next_result();
						
							$result = $card->getCheckDigit($cardNo, $wts);
							$row = $result->row_array();
							
							$chkDigit = $row['chkdgt'];
							
							$result->free_result();
							$result->next_result();
						}
						
						$result = $card->getCardNo($format, $bin, $series, $chkDigit, $brcode);
							
						$row = $result->row_array();
						$cardNo = str_replace('-', '', $row['cardno']);// .'0';
						$isProductExists = $this->isProductExist('CARD', $cardNo);
						
						$result->free_result();
						$result->next_result();
					}
					
					$xml1 = '<BIN>'. $bin .'</>'.
						'<MBOS>'. $emboss .'</>'.
						'<CARDNO>'. trim(substr($cardNo, 9, 20)) .'</>'.
						'<ORDERNO>'. $orderNo .'</>';
					
					if ($first === 0) {
						$fcard = $cardNo;
						$first = 1;
					}
					
					$userAudit = $core->getUserID();
					$workstation = $core->getWorkstation();
					
					$query = "INSERT INTO prmaster SET ".
						"prtype  = 'CARD',".
						"prkey   = ?,".
						"cifseqno = ?,".
						"brseqno  = ?,".
						"agent    = 0,".
						"outlet   = ?,".
						"status   = 9,".
						"accttype = ?,".
						"dtlastact= NULL,".
						"dtlastmov= NULL,".
						"payseqno = 0,".
						"isbalance='N',".
						"allows   = ?,".
						"useraudit= ?,".
						"wkstn = ?";
					
					$params = array($cardNo, $cifseqno, $brseqno, $brseqno, $acctType, $initAllows, $userAudit, $workstation);
					
					$card->db->query($query, $params);
					
					$prseqno = $card->getLastInsertID();
					if ($prseqno > 0) {
						$query = "INSERT INTO prdetail SET ".
							"prseqno 	= ?,".
							"bankcode   = 0,".
							"pinoffset  = '000000',".
							"pinctr     = 0,".
							"pinctrmax  = ?,".
							"dtenroll   = NOW(),".
							"issuecnt   = 1,".
							"dtexpire   = ?,".
							"orderno    = ?,".
							"useraudit  = ?,".
							"wkstn      = ?,".
							"xml1       = ?";
						
						$params = array($prseqno, $pinctr, $dtexp, $orderNo, $userAudit, $workstation, $xml1);
						
						$card->db->query($query, $params);
						
						$query = "UPDATE crdorder SET ".
							"gencount = ?,".
							"dtgenerated = NOW() ".
							"WHERE orderno = ?";
						
						$params = array($ctr, $orderNo);
						
						$card->db->query($query, $params);
						
						if ($onlinelmt > 0) {
							$query = "DELETE FROM limitxxx WHERE prseqno = ". $prseqno;
							
							$card->db->query($query);
							
							$query = "INSERT INTO limitxxx (".
									"prseqno,".
									"limitseqno,".
									"trxcode,".
									"cycleavail,".
									"cyclemax,".
									"ctravail,".
									"ctrmax,".
									"tranmax,".
									"tranmin,".
									"duralimit,".
									"nonfeectravail,".
									"nonfeectrmax,".
									"nonfeetranavail,".
									"nonfeetranmax,".
									"useraudit,".
									"override,".
									"wkstn,".
									"cycle) ".
								"SELECT ?,".
									"?,".
									"trxcode,".
									"cycledef,".
									"cycledef,".
									"ctrdef,".
									"ctrdef,".
									"tranmaxdef,".
									"tranmin,".
									"duralimit,".
									"nonfeectrdef,".
									"nonfeectrdef,".
									"nonfeetrandef,".
									"nonfeetrandef,".
									"?,".
									"?,".
									"?,".
									"cycle ".
								"FROM limitdef ".
								"WHERE limitseqno = ?";
							
							$params = array($prseqno, $onlinelmt, $userAudit, $userAudit, $workstation, $onlinelmt);
							
							$card->db->query($query, $params);
						}
						
						if ($offlinelmt > 0) {
							$query = "INSERT INTO limitxxx (".
									"prseqno,".
									"limitseqno,".
									"trxcode,".
									"cycleavail,".
									"cyclemax,".
									"ctravail,".
									"ctrmax,".
									"tranmax,".
									"tranmin,".
									"duralimit,".
									"nonfeectravail,".
									"nonfeectrmax,".
									"nonfeetranavail,".
									"nonfeetranmax,".
									"useraudit,".
									"override,".
									"wkstn,".
									"cycle) ".
								"SELECT ?,".
									"?,".
									"trxcode,".
									"cycledef,".
									"cycledef,".
									"ctrdef,".
									"ctrdef,".
									"tranmaxdef,".
									"tranmin,".
									"duralimit,".
									"nonfeectrdef,".
									"nonfeectrdef,".
									"nonfeetrandef,".
									"nonfeetrandef,".
									"?,".
									"?,".
									"?,".
									"cycle ".
								"FROM limitdef ".
								"WHERE limitseqno = ?";
							
							$params = array($prseqno, $offlinelmt, $userAudit, $userAudit, $workstation, $offlinelmt);
							
							$card->db->query($query, $params);
						}
					}
					
					$lcard = $cardNo;

					if ($ctr % 100 === 0) {
						if ($card->db->trans_status() === FALSE) {
							$card->db->trans_rollback();
						} else {
							$card->db->trans_commit();
						}
						/*echo str_pad('<script>parent.logMe('. $ctr % 100 .');</script>'."\n", 1024);
						flush();*/
					}
					
					$progress++;
					$totalProg = round($progress/$qty * 100);
					echo str_pad('<script>parent.updateProgress("#'. $orderNo .'",'. $totalProg .');</script>'."\n", 1024);
					flush();
				}//end loop
				
				$xml->editTag('BEGINCARD', $fcard);
				$xml->editTag('ENDCARD', $lcard);
				
				$xml1 = $xml->getXML();
				
				$query = "UPDATE crdorder SET ".
					"dtgenerated = NOW(),".
					"status = 4,".
					"useraudit = ?,".
					"xml1 = ?".
					" WHERE orderno = ?".
					" AND status = 7 AND ".
					"gencount = ?";
				
				$params = array($userAudit, $xml1, $orderNo, $qty);
				
				$card->db->query($query, $params);
				
				$xml2 = '<IP>'. $workstation .'</>'.
					'<IPADDR>'. $core->getIPAddress() .'</>'.
					'<BRSEQN0>'. $brseqno .'</>'.
					'<CIFNO>'. $cifseqno .'</>'.
					'<ORDERNO>'. $orderNo .'</>'.
					'<BEGINCARD>'. $fcard .'</>'.
					'<ENDCARD>'. $lcard .'</>';
				
				$query = "CALL sp_insertlogclixx(".
					"41,".
					"990314,".
					"?,".
					"0,0,'CARD','',".
					"?,'','','','','','','',?,'','WEB','WEB',?,?)";
				
				$params = array($brseqno, $userAudit, $userAudit, $workstation, $xml2);
				
				$card->db->query($query, $params);
			}
		}
		
		if ($card->db->trans_status() === FALSE) {
			$card->db->trans_rollback();
			return FALSE;
		} else {
			//$card->db->trans_rollback();
			$card->db->trans_commit();
			return TRUE;
		}
	}	
	
	function isProductExist($prType, $prKey)
	{
		$query = "SELECT prkey FROM prmaster WHERE prtype = ? AND prkey = ?";
		$result = $this->card_model->db->query($query, func_get_args());
		
		return $result->num_rows() > 0 ? TRUE : FALSE;
	}
	
	function generate()
	{
		$this->load->model('coreapp/card_model');
		$this->load->library('core');

		$orders = $this->input->post('order', TRUE);
		$sessionID = $this->core->getSessionID();
		$generated = 0;
		$orderNos = array();
		
		// pad to force the browser to starting parsing/executing
		echo str_pad('<html><body>', 4096);
		foreach ($orders as $order) {
			
			$order = explode(',', $order);
			$orderNo = $order[0];
			$brseqno = $order[1];
			
			$result = $this->_generateCards($orderNo, $brseqno);//, $userAudit, $sessionID);
			
			//errno, count number of success
			$generated++;
			$orderNos[] = $orderNo;
			
			//$result->free_result();
			//$result->next_result();
		}
		echo '</body></html>';
			
		$json = json_encode(array(
			'success' => TRUE,
			'generated' => $generated,
			'orderNos' => $orderNos,
			//'message' => $result
			'message' => 'Card generation completed'
		));
		
		echo str_pad('<script>parent.showGenResult('. $json .');</script>'."\n", 1024);
		flush();
	}
}