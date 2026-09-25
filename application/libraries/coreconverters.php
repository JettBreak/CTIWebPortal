<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * CoreConverters Class
 *
 * @package		Application
 * @subpackage	Libraries
 * @category	Libraries
 * @author		Franz S. Evangelista
 * @lastupdate	October 3, 2011
 */
class CoreConverters {
	
	/**
	 * ASCII Binary to Hex Converter
	 *
	 * @access	public
	 * @param	string
	 * @return	string
	 */
	function asciiBinToHex($str)
	{
		//delete some probably spaces
		$str = str_replace(' ', '', $str);
		
		//Binary to HEX list
		/*$bin['0000'] = '0';
		$bin['0001'] = '1';
		$bin['0010'] = '2';
		$bin['0011'] = '3';
		$bin['0100'] = '4';
		$bin['0101'] = '5';
		$bin['0110'] = '6';
		$bin['0111'] = '7';
		$bin['1000'] = '8';
		$bin['1001'] = '9';
		$bin['1010'] = 'A';
		$bin['1011'] = 'B';
		$bin['1100'] = 'C';
		$bin['1101'] = 'D';
		$bin['1110'] = 'E';
		$bin['1111'] = 'F';*/
		$bin = array(
			'0000' => '0',
			'0001' => '1',
			'0010' => '2',
			'0011' => '3',
			'0100' => '4',
			'0101' => '5',
			'0110' => '6',
			'0111' => '7',
			'1000' => '8',
			'1001' => '9',
			'1010' => 'A',
			'1011' => 'B',
			'1100' => 'C',
			'1101' => 'D',
			'1110' => 'E',
			'1111' => 'F'
		);
		
		//make sets of 4
		$set = 4;
		for ( ; ; ) {
			$calc = strlen($str) / $set;
			
			if (is_numeric($calc) && (intval($calc) == floatval($calc))) {
				break;
			} else {
				$str .= 0;
			}
		}
		
		//translate binary to hex
		$hex = NULL;
		for($i = 0; $i < strlen($str); $i = $i + $set) {
			$s = substr($str, $i, $set);
			$hex .= $bin[$s];
		}
		
		return $hex;
	}
	
	// --------------------------------------------------------------------

	/**
	 * ASCII Hex to Binary Converter
	 *
	 * @access	public
	 * @param	string
	 * @return	string
	 */
	function asciiHexToBin($str)
	{
		//delete some probably spaces
		$str = str_replace(' ', '', $str);
		
		//HEX to Binary list
		/*$hex['0'] = '0000';
		$hex['1'] = '0001';
		$hex['2'] = '0010';
		$hex['3'] = '0011';
		$hex['4'] = '0100';
		$hex['5'] = '0101';
		$hex['6'] = '0110';
		$hex['7'] = '0111';
		$hex['8'] = '1000';
		$hex['9'] = '1001';
		$hex['A'] = '1010';
		$hex['B'] = '1011';
		$hex['C'] = '1100';
		$hex['D'] = '1101';
		$hex['E'] = '1110';
		$hex['F'] = '1111';*/
		$hex = array(
			'0' => '0000',
			'1' => '0001',
			'2' => '0010',
			'3' => '0011',
			'4' => '0100',
			'5' => '0101',
			'6' => '0110',
			'7' => '0111',
			'8' => '1000',
			'9' => '1001',
			'A' => '1010',
			'B' => '1011',
			'C' => '1100',
			'D' => '1101',
			'E' => '1110',
			'F' => '1111'
		);
		
		//make sets of 1
		$set = 1;
		for ( ; ; ) {
			$calc = strlen($str) / $set;
			
			if (is_numeric($calc) && (intval($calc) == floatval($calc))) {
				break;
			} else {
				$str .= 0;
			}
		}
		
		//translate binary to hex
		$bin = NULL;
		for($i = 0; $i < strlen($str); $i = $i + $set) {
			$s = substr($str, $i, $set);
			$bin .= $hex[$s];
		}
		
		return $bin;
	}
}