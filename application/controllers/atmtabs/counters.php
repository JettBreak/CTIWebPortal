<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Counters extends CI_Controller {
	
	function __construct()
	{
		parent::__construct();
		$this->load->library('core');
		$this->core->checkUserAllows(MONATM_NO);
	}
	
	function index()
	{
		//$this->load->library('session', 'core');
		$this->load->model('coresys/atm_model');
		$this->load->library('shortxml');
		
		$atm   = $this->atm_model;
		$core  = $this->core;
		$input = $this->input;
		$xml   = $this->shortxml;
			
		//for ATM list
		if ($input->get('list', TRUE) === '1') {
			if ($core->canMon()) {
				$branchCode = $input->get('brcode');
				$locCode = $input->get('loccode');
			} else {
				$branchCode = $core->getBranchCode();
				$locCode = 0;
			}
			$status = $input->get('status');
			
			//get ATM list
			$result  = $atm->getATMList($branchCode, $locCode, $status);	
			$atmList = $core->showATMList($result);
			$atmList = $core->compressOutput($atmList);
			
			$result->free_result();
			$result->next_result();
		} else {
			$atmList = NULL;
		}
		//end
		
		//get ATM Counters
		$terminalCode = $input->get('terminalcode');
		$result	= $atm->getATMCounters($terminalCode);
		$row 	= $result->row_array();
		
		if ($row['dtlastcommand'] !== '0000-00-00 00:00:00') {
			$dtLastCmd = $core->formatDate('m/d/Y h:i:s A', $row['dtlastcommand']);
		} else {
			$dtLastCmd = 'None';
		}
		
		if ($row['dtlastupdated'] !== '0000-00-00 00:00:00') {
			$dtLastUpdated = $core->formatDate('m/d/Y h:i:s A', $row['dtlastupdated']);
		} else {
			$dtLastUpdated = 'None';
		}
			
		$serial 		  = $row['serial'];
		$transCount		  = $row['trxcount'];
		$cardsCaptured 	  = $row['cardscaptured'];
		$cameraFilm 	  = $row['camerafilm'];
		$envelopDeposited = $row['envelopdeposited'];
		$envelopSerial 	  = $row['envelopserial'];
		
		$denomination = $row['deno'];
		$dispensed	  = $row['dispense'];
		$rejected	  = $row['rejected'];
		$amtAvailable = $core->currency($row['remainingcash']);
		
		$xml->setXML($row['xml']);
		$threshold = $xml->getValue('THRES');
				
		$details = '<table>
			<tr>
				<td width="150" class="label">Date Last Command:</td>
				<td width="200">'. $dtLastCmd .'</td>
			</tr>
			<tr>
				<td class="label">Date Last Updated:</td>
				<td>'. $dtLastUpdated .'</td>
			</tr>
			<tr>
				<td class="label">Serial:</td>
				<td>'. $serial .'</td>
			</tr>
			<tr>
				<td class="label">Transaction Count:</td>
				<td>'. $transCount .'</td>
			</tr>
			<tr>
				<td class="label">Card Captured:</td>
				<td>'. $cardsCaptured .'</td>
			</tr>
			<tr>
				<td class="label">Camera Film:</td>
				<td>'. $cameraFilm .'</td>
			</tr>
			<tr>
				<td class="label">Envelope Deposit:</td>
				<td>'. $envelopDeposited .'</td>
			</tr>
			<tr>
				<td class="label">Envelope Serial:</td>
				<td>'. $envelopSerial .'</td>
			</tr>
		</table>';
		
		$header = array(
			'Denomination',
			'Notes in Cassette',
			'Notes Dispensed',
			'Notes Rejected',
			'Last Notes Dispensed',
			'Amount Available'
		);
		
		//type header
		$cntH = 1;
		$type = NULL;
		foreach ($result->result_array() as $row) {
			$type .= '<th>Type '. $cntH .'</th>';
			$cntH++;
		}
		
		$details .= '<div style="position: absolute; left: 400px; top: 15px;">
			<div id="countersDT">
			<table>
				<thead>
					<tr>
						<th>&nbsp;</th>'. $type .'
					</tr>
				</thead>
				<tbody>';
		
		$cnt = 0;
		
		foreach ($header as $row) {
			$details .= "<tr>\n";
			$details .= '<td>'. $row ."</td>\n";
		
			$result->_data_seek();
			
			$grandTotal = 0;
			
			foreach ($result->result_array() as $row) {
				$data = array(
					0 => $core->currency($row['deno']),
					1 => $row['remaining'],
					2 => $row['dispense'],
					3 => $row['rejected'],
					4 => $row['lastnotes'],
					5 => $core->currency($row['remainingcash'])
				);

				$details .= '<td align="right">'. $data[$cnt] ."</td>\n";
				
				$grandTotal += intval($row['remainingcash']);
				
			}
			//echo "<td>". $bbb['deno'] ."</td>";
			
			$details .= "</tr>\n";
			$cnt++;
		}
		
		$colspan = $cntH - 1;
		
		$details .= '<tr>
			<td>Threshold:</td>
			<td colspan="'. $colspan .'" align="right">'. $core->currency($threshold) .'</td>
		</tr>
		<tr>
			<td class="label">Total Amount Available:</td>
			<td colspan="'. $colspan .'" align="right">'. $core->currency($grandTotal) .'</td>
		</tr>';
		
		
		$details .= '</tbody>
		</table></div>';

		//Grand Total / Threshold
		/*$details .= '<table>
			<tr>
				<td class="label">Total Amount Available:</td>
				<td>'. $core->currency($grandTotal) .'</td>
				<td>&nbsp;</td>
				<td class="label">Threshold:</td>
				<td>'. $core->currency($threshold) .'</td>
			</tr>
		</table></div>';*/
		
		
		echo json_encode(array(
			'success' => TRUE,
			'atm' 	  => $atmList,
			'details' => $core->compressOutput($details)
		));
	}
}
/* End of file counters.php */
/* Location: ./application/contollers/atmtabs/counters.php */