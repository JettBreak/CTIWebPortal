<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Embossing extends CI_Controller {
	private $progressFile, $queryStr;
	
	function __construct()
	{
		parent::__construct();
		$this->load->library('core');
		
		$this->queue = array();
		//$this->progressFile = realpath(APPPATH .'../emboss/data.txt');
	}
	
	function index()
	{
		$this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
		$this->load->model('coreapp/card_model');
		$this->load->helper('file');

		$this->cache->delete($this->core->getSessionID() .'processed');
		//get last batch no.
		$result = $this->card_model->getLastBatchNo();
		
		if ($result->num_rows() > 0) {
			$row = $result->row_array();
			$data['batchNo'] = intval($row['batchno']) + intval($row['stepcount']);
		} else {
			$data['batchNo'] = 0;
		}
		
		$result->free_result();
		$result->next_result();
		//end
		
		//card types
		if (!$cardType = $this->cache->get($this->core->getSessionID() . 'cardType')) {
			$result = $this->card_model->getCardType('N');
			$cardType = $result->result_array();
			
			$result->free_result();
			$result->next_result();
			$this->cache->save($this->core->getSessionID() .'cardType', $cardType, CACHE_TTL);
		}
		
		if (!$cardTypeP = $this->cache->get($this->core->getSessionID() . 'cardTypeP')) {
			$result = $this->card_model->getCardType('Y');
			$cardTypeP = $result->result_array();
			
			$result->free_result();
			$result->next_result();
			$this->cache->save($this->core->getSessionID() .'cardTypeP', $cardTypeP, CACHE_TTL);
		}
		
		$data['cardType'] = '<option value="0">ALL</option>' .
			'<optgroup id="generic">';
		
		foreach ($cardType as $row) {
			$val  = $row['accttype'];
			$desc = $row['description'];
			$data['cardType'] .= '<option value="'. $val .'">'. $desc .'</option>';
		}
		
		$data['cardType'] .= '</optgroup>';
		
		$data['cardType'] .= '<optgroup id="personalized" class="hidden">';
		
		foreach ($cardTypeP as $row) {
			$val  = $row['accttype'];
			$desc = $row['description'];
			$data['cardType'] .= '<option value="'. $val .'">'. $desc .'</option>';
		}
		$data['cardType'] .= '</optgroup>';
		//end
		
		//branches combobox
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
				$data['branches'] .= '<option value="'. $row['brseqno'] .'">'. $row['brcode'] . '-' . $row['brname'] .'</option>';
			}
		} else {
			$data['branches'] = '<option value="">No Branches Defined</option>';
		}
		
		$data['date'] = date('m/d/Y');
		$this->load->view('card/embossing', $data);
	}
	
	function process()
	{
		$this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
		$this->load->model('coreapp/card_model');
		$this->load->helper('file');
		$this->load->library('shortxml');
			
		$xml = $this->shortxml;	
		$card = $this->card_model;
		$input = $this->input;
		$core = $this->core;
		
		$batchNo	= intval($input->post('batchNo', TRUE));
		$batchNox	= $input->post('batchNox', TRUE);
		$acctType 	= intval($input->post('cardType', TRUE));
		$cardType 	= intval($input->post('generationType', TRUE));
		$date 		= $input->post('date', TRUE);
		$dtEnroll 	= $core->formatDate('Y-m-d', $date);
		
		if ($core->isHeadOffice()) {
			$brseqno = intval($input->post('brseqno', TRUE));
		} else {
			$brseqno = intval($core->getBranchID());
		}
		
		$userAudit  = $core->getUserID();
		$sessionID 	= $core->getSessionID();
		
		//validate session
		/*$result = $card->checkLogin($userAudit, $sessionID);
		$row = $result->row_array();
		
		$result->free_result();
		$result->next_result();
		
		unset($result);*/
		
		$isGenerate = NULL;
		
		if ($batchNox === '') {
			//$result = $card->getCardForEmbossing($acctType, $cardType, $dtEnroll, $brseqno, $userAudit, $sessionID);
			
			$params = array($brseqno);
			$filter = '';
			
			if ($cardType === 0) {
				$filter .= ' AND a.cifseqno > 0 ';
			} else {
				$filter .= ' AND a.cifseqno = 0 ';
			}
			
			if ($acctType !== 0) {
				$filter .= ' AND a.accttype = ? ';
				array_push($params, $acctType);
			}
			
			$query = "SELECT a.prseqno,a.cifseqno,a.prkey,a.issuer,a.accttype,b.dtexpire,b.dtenroll,b.xml1,c.brid,c.brcode
				FROM prmaster a, prdetail b, branches c
				WHERE b.prseqno = a.prseqno AND
				a.brseqno = ? AND
				c.brseqno = a.brseqno AND
				a.status = 9 AND
				DATE(b.dtenroll) < '". $dtEnroll ." 23:59:59'" . $filter;
			
			$result = $card->db->query($query, $params);
			
			$total = $result->num_rows();
			if (intval($total) > 0) {
				$resultArr = $result->result_array();
				
				$result->free_result();
				$result->next_result();
			
				$result = $card->generateSeq('EMBS');
				$row = $result->row_array();
				
				$batchNo = $row['embossno'];
			}
		} else {
			//$result = $card->getCardEmbossingByBatch($batchNo, $brseqno, $userAudit, $sessionID);
			$query = "SELECT a.prseqno,a.prkey,a.issuer,a.accttype,b.dtexpire,b.dtenroll,b.xml1,a.brseqno,c.brid,c.brcode
				FROM prmaster a, prdetail b, branches c
				WHERE b.prseqno = a.prseqno AND
				a.brseqno = ? AND
				c.brseqno = a.brseqno
				AND b.embosno = ?";
				
			$params = array($brseqno, $batchNo);
			
			$result = $card->db->query($query, $params);
			
			$total = $result->num_rows();
			$resultArr = $result->result_array();
		}
		
		$embType = $input->post('embossingType', TRUE); //text, csv, xml

		$content = '';
		$progress = 0;
		$limit = 100;
		$varTotal = 0;
		$totalProg = NULL;
		
		if ($total === 0) {
			$embType = NULL;
		}
		
		$result->free_result();
		$result->next_result();
		
		//save emb card status per accttype
		$result = $card->db->query("SELECT accttype, sf_getXMLValue(xml1,'EMBNSTAT') as embstat FROM accttype WHERE prtype = 'CARD'");
		
		$embStatList = array();
		
		foreach ($result->result_array() as $row) {
			$embStatList['x' . $row['accttype']] = intval($row['embstat']);
		}
		//end
		
		// pad to force the browser to starting parsing/executing
		echo str_pad('<html><body>', 4096);
		
		switch ($embType)
		{
			case 'text':
				$contentType = 'text/plain; charset="utf-8"';
				$fileName = 'EMB'. date('mdY') .'.txt';
				
				$content = '';
				
				for ($i = 0; $i < $total; $i += $limit) {
					$sql = $query;
					$sql .= " LIMIT ?,?";
					
					$params[] = $i;
					$params[] = ($i + $limit);
					
					$result = $card->db->query($sql, $params);
					$lastResult = $result->result_array();
					//echo $i . " - " . ($i + $limit) . "<br/>";
					
					$result->free_result();
					$result->next_result();
					
					$this->queue = array();
					
					foreach ($lastResult as $row) {
						$xml->setXML($row['xml1']);
						
						$prseqno = intval($row['prseqno']);
						$acctType = $row['accttype'];
						$brcode = $row['brcode'];
						$prkey = $row['prkey'];
						$cardNo = $xml->getValue('CARDNO');
						
						if (in_array('', array($prseqno, $acctType, $brcode, $prkey, $cardNo))) {
							continue;
						}
						
						$time = date('Y-M-d H:i:s');
						$name = $xml->getValue('MBOS');
						
						if (strlen(trim($name)) === 0) {
							$name = '#' . substr(' ', 17);
						}
						$content .= $brcode . $cardNo . $prkey . '=0000770000000' . $time . $name ."\r\n";

						$embStatus = $embStatList['x'. $acctType];
				
						$queueSuccess = $this->_buildQuery($embStatus, $batchNo, $prseqno);
						
						if ($queueSuccess) {
							$progress++;
							if ($totalProg !== round($progress/$total * 100)) {
								$totalProg = round($progress/$total * 100);
												
								echo str_pad('<script>parent.updateProgress('. $totalProg .');</script>'."\n", 1024);
								flush();
							}
							$queueSuccess = FALSE;
							//usleep(1000);
						}
					}
					
					$queue = $this->_getQuery();
		
					$card->db->trans_begin();
					
					if (count($queue) > 0) {
						foreach ($queue as $x) {
							$sql = $x[0];
							$params = $x[1];
							$card->db->query($sql, $params);
						}
					}
					
					if ($card->db->trans_status() === FALSE) {
						$card->db->trans_rollback();
					} else {
						$card->db->trans_commit();
					}
				}
				
				$content .= "Total: " . count($lastResult);
				break;
			
			case 'csv':
				$contentType = 'application/csv';
				$fileName = 'EMB'. date('mdY') .'.csv';
				
				$header = array(
					"BRNCH_CODE,C,10",
					"CARDNO,C,18",
					"TRACK2,C,31",
					"DATE,C,17",
					"NAME,C,25"
				);
				$content = implode("\t", $header) . "\r\n";
				
				for ($i = 0; $i < $total; $i += $limit) {
					$sql = $query;
					$sql .= " LIMIT ?,?";
					
					$params[] = $i;
					$params[] = ($i + $limit);
					
					$result = $card->db->query($sql, $params);
					$lastResult = $result->result_array();
					//echo $i . " - " . ($i + $limit) . "<br/>";
					
					$result->free_result();
					$result->next_result();
					
					$this->queue = array();
					
					foreach ($lastResult as $row) {
						$xml->setXML($row['xml1']);
						
						$prseqno = $row['prseqno'];
						$acctType = $row['accttype'];
						$brCode = $row['brcode'];
						$prkey = $row['prkey'];
						$dtEnroll = $this->core->formatDate('m/d/Y', $row['dtenroll']);
						$name = $xml->getValue('MBOS');
						
						if (in_array('', array($prseqno, $acctType, $brCode, $prkey))) {
							continue;
						}
						
						/*$cn1 = substr($prkey, 0, 6);
						$cn2 = substr($prkey, 6, 3);
						$cn3 = substr($prkey, 9);
						$cardNo = $cn1 .' '. $cn2 .' '. $cn3;*/
						
						//$cn1 = substr($prkey, 0, 6);
						$BIN = $xml->getValue('BIN');
						//$cn2 = substr($prkey, 6, 3);
						$number = substr($prkey, strlen($BIN) + strlen($brCode));
						$cardNo = implode(',', array($BIN, $brCode, $number));
						$trackNo = '0000770000000';
						
						$data = array(
							$brCode,
							$cardNo,
							$prkey .'='. $trackNo,
							$dtEnroll,
							$name
						);
						
						$content .= implode("\t", $data) . "\r\n";
						
						//$embStatus = 11;
						//$result = $card->getStatusForEmboss($acctType);
						//$row = $result->row_array();
						$embStatus = $embStatList['x'. $acctType];
	
						$queueSuccess = $this->_buildQuery($embStatus, $batchNo, $prseqno);
						
						if ($queueSuccess) {
							$progress++;
							if ($totalProg !== round($progress/$total * 100)) {
								$totalProg = round($progress/$total * 100);
												
								echo str_pad('<script>parent.updateProgress('. $totalProg .');</script>'."\n", 1024);
								flush();
							}
							$queueSuccess = FALSE;
							//usleep(1000);
						}
					}
				
					$queue = $this->_getQuery();
		
					$card->db->trans_begin();
					
					if (count($queue) > 0) {
						foreach ($queue as $x) {
							$sql = $x[0];
							$params = $x[1];
							$card->db->query($sql, $params);
						}
					}
					
					if ($card->db->trans_status() === FALSE) {
						$card->db->trans_rollback();
					} else {
						$card->db->trans_commit();
					}
				}
				$content .= "Total: " . count($lastResult);
				//$content = base64_encode($content);
				break;
				
			case 'xml':
				$contentType = 'text/xml"';
				$fileName = 'EMB'. date('mdY') .'.xml';
				
				$content = '<?xml version="1.0" encoding="utf-8"?>';
				$content .= "\n<processed>";
				
				for ($i = 0; $i < $total; $i += $limit) {
					$sql = $query;
					$sql .= " LIMIT ?,?";
					
					$params[] = $i;
					$params[] = ($i + $limit);
					
					$result = $card->db->query($sql, $params);
					$lastResult = $result->result_array();
					//echo $i . " - " . ($i + $limit) . "<br/>";
					
					$result->free_result();
					$result->next_result();
					
					$this->queue = array();
					
					foreach ($lastResult as $row) {
						$xml->setXML($row['xml1']);
						
						$prseqno = $row['prseqno'];
						$acctType = $row['accttype'];
						$brCode = $row['brcode'];
						$prkey = $row['prkey'];
						$dtExpire = $this->core->formatDate('m/d/Y', $row['dtexpire']);
						$name = $xml->getValue('MBOS');
						
						if (in_array('', array($prseqno, $acctType, $brCode, $prkey))) {
							continue;
						}
						
						$mbos = NULL;
						if ($name !== '') {
							$mbos = "\n\t\t<name>". $name ."</name>";
						}
						
						$cn1 = substr($prkey, 0, 6);
						$cn2 = substr($prkey, 6, 3);
						$cn3 = substr($prkey, 9);
						$cardNo = $cn1 .' '. $cn2 .' '. $cn3;
						$content .= "\n\t<card>".
							 "\n\t\t<branchcode>".$brCode."</branchcode>".
							 "\n\t\t<cardno>".$cardNo."</cardno>".
							 "\n\t\t<track2>". $prkey ."=0000770000000</track2>".
							 "\n\t\t<dtexpire>". $dtExpire ."</dtexpire>".
							 $mbos .
							 "\n\t</card>";
						
						//$result = $card->getStatusForEmboss($acctType);
						//$row = $result->row_array();
						$embStatus = $embStatList['x'. $acctType];
						
						$queueSuccess = $this->_buildQuery($embStatus, $batchNo, $prseqno);
						
						if ($queueSuccess) {
							$progress++;
							if ($totalProg !== round($progress/$total * 100)) {
								$totalProg = round($progress/$total * 100);
												
								echo str_pad('<script>parent.updateProgress('. $totalProg .');</script>'."\n", 1024);
								flush();
							}
							$queueSuccess = FALSE;
							//usleep(1000);
						}
					}
				
					$queue = $this->_getQuery();
		
					$card->db->trans_begin();
					
					if (count($queue) > 0) {
						foreach ($queue as $x) {
							$sql = $x[0];
							$params = $x[1];
							$card->db->query($sql, $params);
						}
					}
					
					if ($card->db->trans_status() === FALSE) {
						$card->db->trans_rollback();
					} else {
						$card->db->trans_commit();
					}
				}
				
				$content .= "Total: " . count($lastResult);
				
				$content .= "\n</processed>";
				break;
			
			default:
				echo str_pad('<script>parent.noData()</script>'."\n", 1024);
				flush();
						
				exit();
				break;
		}
		
		echo '</body></html>';
		
		$content = base64_encode($content);
			
		//cache processed cards
		$cacheProcessed = array(
			'contentType' => $contentType,
			'content' => $content,
			'fileName' => $fileName
		);
		$this->cache->save($this->core->getSessionID() .'processed', $cacheProcessed, CACHE_TTL);
		
		/*header('Content-type: '. $contentType);
		header('Content-Disposition: attachment; filename='. $fileName);
		header('Pragma: no-cache');
		header('Expires: 0');
		
		echo $content;*/
	}
	
	function _buildQuery($embStatus, $batchNo, $prseqno)
	{
		$sql = "UPDATE prmaster SET ".
				"status = ? ".
				"WHERE prseqno = ? AND status = 9 LIMIT 1";
		
		$params = array($embStatus, $prseqno);
			
		$this->queue[] = array(
			$sql, $params
		);
		
		$sql = "UPDATE prdetail SET ".
				"embosno = ?, dtemboss = NOW() ".
				"WHERE prseqno = ? LIMIT 1";
		
		$params = array($batchNo, $prseqno);
		
		$this->queue[] = array(
			$sql, $params
		);
		
		return TRUE;
	}
	
	function _getQuery()
	{
		return $this->queue;
	}
	
	function verify()
	{
		$this->load->model('coreapp/card_model');
		
		$batchNo = intval($this->input->post('batchNo', TRUE));
		
		if ($this->core->isHeadOffice()) {
			$brseqno = intval($this->input->post('brseqno', TRUE));
		} else {
			$brseqno = intval($this->core->getBranchID());
		}
		
		$userAudit = $this->core->getUserID();
		$sessionID = $this->core->getSessionID();
		
		/*$result = $this->card_model->getCardEmbossingByBatch(
			$batchNo, $brseqno, $userAudit, $sessionID
		);*/
		
		$query = "SELECT b.dtenroll
			FROM prmaster a, prdetail b
			WHERE b.prseqno = a.prseqno AND
			a.brseqno = ?
			AND b.embosno = ? LIMIT 1";
			
		$params = array($brseqno, $batchNo);
		
		$result = $this->card_model->db->query($query, $params);
		
		$row = $result->row_array();
		
		if ($result->num_rows() > 0) {
			$success = TRUE;
			$message = $this->core->formatDate('m/d/Y', $row['dtenroll']);
		} else {
			$success = FALSE;
			$message = 'Batch No. not found';
		}
		
		echo json_encode(array(
			'success' => $success,
			'message' => $message
		));
	}
	
	/*function getProgress()
	{
		$this->load->helper('file');
		$time = time();
		while((time() - $time) < 20) {
			// query memcache, database, etc. for new data
			$data = file_get_contents($this->progressFile);
		 	
			if ($data === 'x') {
				echo json_encode(array(
					'success' => FALSE,
					'message' => 'No data found for embossing'
				));
				exit();
			}
			
			// if we have new data return it
			if ($data !== '') {
				if (intval($data) === 100) {
					write_file($this->progressFile, '');
				}
				echo json_encode(array(
					'success' => TRUE,
					'progress' => intval($data)
				));
				break;
			}
		 
			usleep(25000);
		}
	}*/
	
	function getProcessed()
	{
		$this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
		
		//$this->cache->clean();
		if ($processed = $this->cache->get($this->core->getSessionID() . 'processed')) {
			header('Content-type: '. $processed['contentType']);
			header('Content-Disposition: attachment; filename='. $processed['fileName']);
			header('Pragma: no-cache');
			header('Expires: 0');
			
			echo $processed['content'];
		} else {
			$this->load->helper('url');
			redirect('#welcome');
		}
	}
}