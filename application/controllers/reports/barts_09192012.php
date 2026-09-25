<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Barts extends CI_Controller {	
    
    function __construct()
    {
        parent::__construct();
        $this->load->library('core');
        $this->core->checkUserAllows(BARTSFILE_NO);
    }
    
    function index()
    {
        if ($this->input->get('norecord', TRUE) === 'true') {
            $data['showMsg'] = '1';
        } else {
            $data['showMsg'] = '0';
        }
        
        $data['title'] = 'BARTS FILE';
        $data['date'] = date('m/d/Y');
        $data['formAction'] = 'reports/barts/process';
        $this->load->view('reports/reportviewer', $data);
    }
    
    function process()
    {
        $this->load->model('coresys/reports_model');
        $this->load->library('shortxml');
        $this->load->library('zip');
        
        $tracer = NULL;
        $core = $this->core;
        $xml = $this->shortxml;
		$rXML = $this->shortxml;
		
		$finswitch = $core->getFINSWITCH();
		$bnkcode  = $core->getBANKCODE();
		$onusCode  = $core->getONUSCODE();
        
        $trxDate = $this->input->post('trxDate', TRUE);
        
        $result = $this->reports_model->getTransactionForBARTS($core->formatDate('Y-m-d', $trxDate), $onusCode);
        
        $rfb = $result->result_array();
        
        $type = NULL;
        $casaData = NULL;
        $tfreeData= NULL;
        $switData = NULL;
        $isRecreate = NULL;
        foreach ($rfb as $row) {
            //init vars
            $stLogseq = substr(trim($row['logseqno']), 0, 6);
            $stNode = trim($row['chname']);
            $stType = trim($row['termtype']);
            $stMnemonic = substr(trim($row['mnemonic']), 0, 8);
            $stTrxType = substr(trim($row['trxtype1']), 0, 8);
            $stISS = intval(trim($row['isscode']));
            
            //Concatinate XMLs
            $xml->setXML($row['xml1'] . $row['xml2'] . $row['xml3'] . $row['xml4'] . $row['xml5'] . $row['xml6']);
            
            //OB Code (Acquirer / Originating Central Bank Code)
            if ($stISS === 0) {
                $stISS = '5';
            }
            
            $stISS = substr($stISS, 0, 4);
            $stISS = str_pad($stISS, 4, '0', STR_PAD_LEFT);
            
            if ($stTrxType !== 'POS') {
                $stACQ = intval(trim($row['acqcode']));
            } else {
                $stACQ = intval(trim($xml->getValue('ACQCODE')));
            }
            
			if ($stACQ === 0) {
				$stACQ = intval(trim($xml->getValue('BNET_BIT32')));	
			} elseif (strlen($stACQ) === 6) {
				$result->free_result();
				$result->next_result();
				
				$result = $this->reports_model->getIssoCode($stACQ);
				$r = $result->result_array();
				$stACQ = intval(trim($r['isocode']));
				
				$result->free_result();
				$result->next_result();
			}
			
			if (strlen($stACQ) === 10) {
				$result->free_result();
				$result->next_result();
				
				$result = $this->reports_model->getPosCode($stACQ);
				foreach ($result->result_array() as $r) {
					$rXML->setXML($r['xml1']);
					$stACQ = intval(trim($rXML->getValue('POSCODE')));
				}
				
				$result->free_result();
				$result->next_result();
			}
			
            if ($stACQ === 0) {
                $stACQ = '5';
            }
            
            $stACQ = substr($stACQ, 0, 4);
            $stACQ = str_pad($stACQ, 4, '0', STR_PAD_LEFT);
            
            if ( in_array($stType, array('ATM', 'POS', 'SAF')) && $stNode === $finswitch) {
                $stCBCode = $stISS;
                $isISS = FALSE;
            } else {
                $stCBCode = $stACQ;
                $isISS = TRUE;
            }
            
            if (! ( ($stType === 'ATM') && in_array($stNode, array('ONUS', $onusCode)) ) ) {
                
                /*if (($stType === $finswitch) && ($stNode === 'ONUS')) {
                    $stCBCode = $stISS;
                    $isISS = TRUE;
                } else {
                    $stCBCode = $stACQ;
                    $isISS = FALSE;
                }*/
                //Transaction Date / Time
                $stDate = $core->formatDate('mdY', $row['dtlog']);
                $stTime = $core->formatDate('His', $row['dtlog']);
                //Terminal Number
                $stTerm = substr(trim($row['termcode']), 4, 4);
                $stTerm = str_pad($stTerm, 4, '0', STR_PAD_LEFT);
                //Account Number
                $stAcct = substr(trim($row['acct1']), 0, 16);
                $stAcct = str_pad($stAcct, 16, '0', STR_PAD_LEFT);
                
                $stTran = substr(trim($row['mnemonic']), 0, 8);
                $stMsgType = trim($row['msgtype']);
                
				//tpseqno
				$stTPSeq = substr(trim($row['tpseqno']), 0, 6);
				 
				//chseqno
               	$stChSeq = substr(trim($row['chseqno']), 0, 6);
				 
				
                //Transaction Code
                $stTran = $this->checkData($stTrxType, $stMsgType, $stMnemonic, $xml);
                //end

                //Transaction Amount
                
                $amtath = number_format(floatval($row['amtath']), 2, '', '');
                $amtreq = number_format(floatval($row['amtreq']), 2, '', '');
                $fee = number_format(floatval($row['fee']), 2, '', '');
                
                if ($amtath > 0) {
                    $stAmtAth = str_replace('.', '', $amtath) ;
                } else {
                    $stAmtAth = str_replace('.', '', $amtreq);
                }
                $stAmtAth = str_pad($stAmtAth, 11, '0', STR_PAD_LEFT);
                
                if ($fee > 0) {
                    if ( in_array($stMsgType, array(71, 72, 171)) ) {
                    	
						                   
                        //$stAmtAth = (str_replace('.', '', ($row['fee']) * 100)) + $stAmtAth;
                        $stAmtAth = (str_replace('.', '', $fee)) + $stAmtAth;
                        $stAmtAth = str_pad($stAmtAth, 11, '0', STR_PAD_LEFT);
                        
                    }
                }
                //end
				
				//$checker = 'INIT' . $stMsgType;
				if ($stMsgType === '171') {
					$rejRep = NULL;
					foreach ($rfb as $traceIfRej) {
						
						//$checker .= $traceIfRej['msgtype'] . ' - ';
					
						$traceMsgType = trim($traceIfRej['msgtype']);
						$traceTpSeqno = trim($traceIfRej['tpseqno']);
						$traceChSeqno = trim($traceIfRej['chseqno']);
						
						if (in_array($stTPSeq, array($traceTpSeqno, $traceChSeqno)) ||
							in_array($stChSeq, array($traceTpSeqno, $traceChSeqno))		) {
							if ( !in_array($traceMsgType, array(53,171)) ) {
								$rejRep = FALSE;
								break;
							} elseif ($traceMsgType === '171') {
								continue;
							} else {
								$rejRep = TRUE;
								break;	
							}
						}
					
					}
					
					if ($rejRep === TRUE || $rejRep === NULL) {
						continue;
					}
					
				}
                
                //Trace Number
                if (in_array($stType, array($finswitch)) && ($stNode === $onusCode)) {
                    $stTrace = str_pad($stTPSeq, 6, '0', STR_PAD_LEFT);
                } else {
                    $stTrace = str_pad($stChSeq, 6, '0', STR_PAD_LEFT);
                }
                //end
                
                //sysvode
                $stVCode = substr(trim($row['sysvcode']), 0, 8);
                
                if (in_array($stMsgType, array(71, 72, 171)) ) {
                    $isRecreate = TRUE;
                    $stVCode = '0';
                };
                
                $stVCode = str_pad($stVCode, 8, '0', STR_PAD_LEFT);
                //end
                
                //BPS Code x(4)
                //BPS Subs Number 9(16)
                
                $SUBSNO = trim($xml->getValue('SUBSNO'));
                if (!$SUBSNO) {
                    $SUBSNO = trim($row['acct2']);
                }
                
                if ( in_array($stTran, array('BPS', 'BPC')) ) {
                    $pos = strpos('|', $SUBSNO);
                    if ($pos === FALSE) {
                        $stSubNo = substr($SUBSNO, -16); //subscriber no.
                    } else {
                        $stSubNo = substr($SUBSNO, 0, $pos);
                    }
                    
                    $stSubNo = str_pad($stSubNo, 16, '0', STR_PAD_LEFT);
                    
                    $stInsNo = substr(trim($row['instcode']), 0, 4);
                    $stInsNo = str_pad($stInsNo, 4, '0', STR_PAD_LEFT);
                } else {
                    $stSubNo = str_repeat('0', 16);
                    $stInsNo = str_repeat('0', 4);
                }
                //end
                
                //TB Code / TB Account
                if ( in_array($stTrxType, array('IBFT', 'TRN', 'LOAD', 'WDL')) ) {
                    
                    if ( in_array($stTran, array('TFR', 'TFC')) ) {
                        $stTBAcct = str_repeat('0', 16);
                        $stTBCode = str_repeat('0', 4);
                    } else {
                        $stTBAcct = substr(trim($row['acct2']), 0, 16);
                        
                        if (strlen($stTBAcct) === 0) {
                            //$stTBAcct = intval($xml->getValue('IBFTPTR'));
                           // if ($stTBAcct === '') {
                                //$stTBAcct = substr(trim($xml->getValue('BUFFER_C')), 0, 15);
                            //}
							$stTBAcct = str_pad($stTBAcct, 16, '0', STR_PAD_LEFT);
                        } else {
                            $stTBAcct = str_pad($stTBAcct, 16, '0', STR_PAD_LEFT);
                        }
						
                        if (strlen($stTBAcct) === 0) {
							 $stTBAcct = str_pad($stTBAcct, 16, '0', STR_PAD_LEFT);
						}
                        
                        if (in_array($stTrxType, array('IBFT', 'WDL')) ) {
                            if (intval($xml->getValue('IBFTPTR')) === IBFTPTR) {
                                $stTran = 'ELD';
                            }
							
							if ($stTrxType === 'WDL') {
                            	if (trim($row['acct2']) === '') {
									$stTBAcct = str_pad($stTBAcct, 16, '0', STR_PAD_LEFT);	
								} else {
									 $stTBAcct = str_pad($row['acct2'], 16, '0', STR_PAD_LEFT);
								};
							}
							
                            $stTBCode = substr(intval($xml->getValue('IBFTPTR')), 0, 4);
                        } else {
							
                            $stTBCode = $stISS;
                        }
                        
                        if ($stTran === 'ELD') {
                            //service provider + 0 + cellnumber
                            $stTBAcct = substr(trim($row['acct2']), -11);
                            
                            $serviceP = trim($xml->getValue('LOADPTR'));
                            
                            if ($serviceP) {
                                $serviceP = str_pad($serviceP, 4, '0', STR_PAD_LEFT) . '0';
                            
                                $stTBAcct = $serviceP . $stTBAcct;
                                $stTBAcct = str_pad($stTBAcct, 16, '0', STR_PAD_LEFT);
                            } else {
								if (trim($row['acct2']) === '') {
									$stTBAcct = str_pad($stTBAcct, 16, '0', STR_PAD_LEFT);	
								} else {
                                	$stTBAcct = str_pad($row['acct2'], 16, '0', STR_PAD_LEFT);
								}
                            }
                            
                            $stTBCode = '8882';
                        }
                        
                        if (!in_array($stTran, array('ELD', 'IBF')) ) {
                            if ($stTran === 'TFR') {
                                $stSubNo = str_repeat('0', 16);
                            }
                        }
                        
                        $stTBCode = str_pad($stTBCode, 4, '0', STR_PAD_LEFT);
                    }
                    
                } else {
                    $stTBAcct = str_repeat('0', 16);
                    $stTBCode = str_repeat('0', 4);
                }
                //end
                
                $tracer = array($stTrxType, $stTran, $stTBAcct);
                
                //Merchant ID
                if ($stTrxType === 'POS') {
                    $stMercID = substr(trim($xml->getValue('TERMIDCODE')), 0, 15);
                    $stMercID = str_pad($stMercID, 15, '0', STR_PAD_LEFT);
                } else {
                    $stMercID = str_repeat('0', 15);
                }
                //end
                
                //Branch Code
                $stBranch = substr(trim($row['termcode']), 0, 4);
                $stBranch = $core->padZeros($stBranch, 4);
                //end
                
                
                
                if ($stTran !== 'ERR') {
                    
                    if ($isISS === TRUE && !in_array($stMnemonic, array('IBFTTRCA', 'IBFTTRSA'))) {
                        //$type = 'CASA';
                        
                        if ($isRecreate === TRUE) {	
                            $isRecreate = FALSE;
                            $stMsgType = 51;						
                            $stTran = $this->checkData($stTrxType, $stMsgType, $stMnemonic, $xml);
							
                            //$stAmtAth = str_pad(intval($row['amtreq']) * 100, 11, '0', STR_PAD_LEFT);

                            
                            if ($stTran !== 'ERR') {
                                $casaData .= $stCBCode . 
                                    $stDate .
                                    $stTime .
                                    $stTerm .
                                    $stAcct .
                                    $stTran .
                                    $stAmtAth .
                                    $stTrace .
                                    $stTBCode .
                                    $stTBAcct .
                                    $stMercID .
                                    $stInsNo .
                                    $stSubNo .
                                    $stBranch .
                                    //json_encode($tracer) .
                                    "\r\n";
                            }
							
                        	$stMsgType = 71;
                            $isRecreate = TRUE;
							$stTran = $this->checkData($stTrxType, $stMsgType, $stMnemonic, $xml);	
                        }
						
						if ($stTran !== 'ERR') {
								$casaData .= $stCBCode . 
									$stDate .
									$stTime .
									$stTerm .
									$stAcct .
									$stTran .
									$stAmtAth .
									$stTrace .
									$stTBCode .
									$stTBAcct .
									$stMercID .
									$stInsNo .
									$stSubNo .
									$stBranch .
									//json_encode($tracer) .
									"\r\n";
						}
                    
                    } elseif ($isISS !== TRUE) {
                        //$type = 'SWIT';
                                                    
                        if ($isRecreate === TRUE) {
                            $isRecreate = FALSE;
                            $stMsgType = 51;
                            $stTran = $this->checkData($stTrxType, $stMsgType, $stMnemonic, $xml);
							
                            //$stAmtAth = str_pad(intval($row['amtreq']) * 100, 11, '0', STR_PAD_LEFT);

                            if ($stTran !== 'ERR') {
                                $switData .= $stCBCode . 
                                    $stDate .
                                    $stTime .
                                    $stTerm .
                                    $stAcct .
                                    $stTran .
                                    $stAmtAth .
                                    $stTrace .
                                    $stVCode .
                                    $stTime .
                                    $stTBCode .
                                    $stTBAcct .
                                    $stInsNo .
                                    $stSubNo .
                                    $stBranch .
                                    //json_encode($tracer) .
                                    "\r\n";
                            }
							
                        	$stMsgType = 71;	
							$stTran = $this->checkData($stTrxType, $stMsgType, $stMnemonic, $xml);
                        }
						
						if ($stTran !== 'ERR') {
							$switData .= $stCBCode . 
								$stDate .
								$stTime .
								$stTerm .
								$stAcct .
								$stTran .
								$stAmtAth .
								$stTrace .
								$stVCode .
								$stTime .
								$stTBCode .
								$stTBAcct .
								$stInsNo .
								$stSubNo .
								$stBranch .
								//json_encode($tracer) .
								"\r\n";
						}
					}
					if ( ($stTBCode !== $bnkcode) && in_array($stTran, array('IBF', 'IBC', 'TFR', 'TFC')) && in_array($stMnemonic, array('IBFTTRCA', 'IBFTTRSA','IBFTWDCA', 'IBFTWDSA','TRNSACA','TRNCASA')) ) {
					  //$type = 'TFREE';
														  
						if ($isRecreate === TRUE) {
								$isRecreate = FALSE;
								$stMsgType = 51;
								$stTran = $this->checkData($stTrxType, $stMsgType, $stMnemonic, $xml);
								
								//$stAmtAth = str_pad(intval($row['amtreq']) * 100, 11, '0', STR_PAD_LEFT);
		  
									  
								if ($stTran !== 'ERR') {
									$tfreeData .= $stTBCode .
										$stACQ .
										$stDate .
										$stTime .
										$stBranch .
										$stTerm .
										$stAcct .
										$stTran .
										$stAmtAth .
										$stTrace .
										$stTBAcct ."\r\n";
								}
								
								$stMsgType = 71;	
								$stTran = $this->checkData($stTrxType, $stMsgType, $stMnemonic, $xml);
								
						}
						if ($stTran !== 'ERR') {
							$tfreeData .= $stTBCode .
								$stACQ .
								$stDate .
								$stTime .
								$stBranch .
								$stTerm .
								$stAcct .
								$stTran .
								$stAmtAth .
								$stTrace .
								$stTBAcct ."\r\n";
						}
					}
				}
			}
		}
        
        $suffix = $core->formatDate('mdY', $trxDate);
        $ext = '.txt';
                
        $casa = "[Header]\r\n".
                "Type = CASA\r\n".
                "[Txns]\r\n".
                $casaData;
        
        $tfree = "[Header]\r\n".
                "Type = TFREE\r\n".
                "[Txns]\r\n".
                $tfreeData;
        
        $swit = "[Header]\r\n".
                "Type = SWIT\r\n".
                "[Txns]\r\n".
                $switData;
                
        $data = array(
            'CASA'. $suffix . $ext => $casa,
            'TFREE'. $suffix . $ext => $tfree,
            'SWIT'. $suffix . $ext => $swit
        );

        $this->zip->add_data($data);
        $this->zip->download('BARTS'. $suffix .'.zip'); 
    }
    
    function checkData($stTrxType, $stMsgType, $stMnemonic, $xml = NULL)
    {
        if ( in_array($stTrxType, array('WDL', 'ADV'))  )  {
           
		   if (in_array($stMnemonic, array('ELOADSA','ELOADCA')) || intval($xml->getValue('IBFTPTR')) === IBFTPTR) {
			   if ( in_array($stMsgType, array(51, 52)) ) {
                
					$stTran = 'ELD';
				
            	} else {
			 
                	$stTran = 'ELC';
            	}
		   } else {
		    	if ( in_array($stMsgType, array(51, 52)) ) {
                
					$stTran = 'CWD';
				
            	} else {
			 
                	$stTran = 'CWC';	
				
            	}
		   }
            /*if ( in_array($stMsgType, array(71, 72, 171)) ) {
                $stTran = 'CWC';
            }*/
        }
        
        elseif ( in_array($stMnemonic, array('TRNCASA', 'TRNSACA')) ) {
            if ( in_array($stMsgType, array(51, 52)) ) {
                $stTran = 'TFR';
            } else {
                $stTran = 'TFC';	
            }
            
            /*if ( in_array($stMsgType, array(71, 72, 171)) ) {
                $stTran = 'TFC';

            }*/
        }
        
        elseif ( in_array($stMnemonic, array('POSWDL', 'POSWDLSA', 'POSWDLCA')) ) {
            if ( in_array($stMsgType, array(51, 52)) ) {
                $stTran = 'SAL';
            } else {
                $stTran = 'SLR';
            }
            
            /*if ( in_array($stMsgType, array(71, 72, 171)) ) {
                $stTran = 'SLR';
            }*/
        }
        
        elseif ($stTrxType === 'PAY') {
            if ( in_array($stMsgType, array(51, 52)) ) {
                $stTran = 'BPS';
            } else {
                $stTran = 'BPC';
            }
            
            /*if ( in_array($stMsgType, array(71, 72, 171)) ) {
                $stTran = 'BPC';
            }*/
        }
        
        elseif ( in_array($stTrxType, array('IBFT', 'LOAD')) ) {
            if ( in_array($stMnemonic, array('LOADSA', 'LOADCA')) ) {
                
                if ( in_array($stMsgType, array(51, 52)) ) {
                    $stTran = 'ELD';
                } else {
                    $stTran = 'ELC';
                }
                
                /*if ( in_array($stMsgType, array(71, 72, 171)) ) {
                    $stTran = 'ELC';
                }*/
                
            } else {
				
                
                	if ( in_array($stMsgType, array(71, 72, 171)) ) {
                    	$stTran = 'IBC';
                	} else {
                    	$stTran = 'IBF';
                	}
				//}
            }
            
        } else {
            $stTran = 'ERR';
        }
        
        return $stTran;
    }
}