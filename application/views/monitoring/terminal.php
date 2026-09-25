<div id="atmMonitoring">
    <h1>ATM Monitoring</h1>
    <table id="termView">
        <thead>
            <tr>
                <td width="220"><label for="atmStatus">Status: </label>
                    <select id="atmStatus" style="width: 160px">
                        <option value="-1">All</option>
                        <?php echo html_entity_decode($statusList); ?>
                    </select></td>
				<?php echo html_entity_decode($branches); ?>
                <td width="170"><strong>TERMINAL CODE: </strong><span id="hTerminalCode"><?php echo $infoTerminalCode; ?></span></td>
                <td width="80"><strong>LUNO: </strong><span id="hTerminalLuno"><?php echo $infoTerminalLuno; ?></span></td>
                <td id="hTerminalProgCode" class="hidden"><?php echo $infoProgCode; ?></td>
                <td id="hTerminalProglang" class="hidden"><?php echo $infoProgLang; ?></td>
                <td width="150"><span id="status"></span></td>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td colspan="6"><div id="terminalList"><?php echo html_entity_decode($atmList); ?></div></td>
            </tr>
        </tbody>
    </table>
    <ul class="tabs fullTabs">
        <li><a href="#tabInfo">Information</a></li>
        <li><a href="#tabContacts">Contacts</a></li>
        <li><a href="#tabCounters">Counters</a></li>
        <li><a href="#tabPrograms">Programs</a></li>
        <li><a href="#tabConfig">Configurations</a></li>
        <li><a href="#tabFitness">Fitness</a></li>
        <li><a href="#tabSupplies">Supplies</a></li>
        <li><a href="#tabCmdHistory">Command History</a></li>
        <li><a href="#tabStatHistory">Status History</a></li>
        <li><a href="#tabTransHistory">Transaction History</a></li>
        <li><a href="#tabJournal">Journal</a></li>
    </ul>
    <div class="tab_container">
        <div id="tabInfo" class="tab_content">
            <table>
                <tr>
                    <td class="idName" width="150">Terminal Code:</td>
                    <td id="infoTerminalCode" class="idNumber" width="200"><?php echo $infoTerminalCode ?></td>
                    <td width="150" class="label">LUNO:</td>
                    <td id="infoTerminalLuno" width="200"><?php echo $infoTerminalLuno ?></td>
                </tr>
                <tr>
                    <td class="label">Terminal ID:</td>
                    <td id="infoTerminalID"><?php echo $infoTerminalID ?></td>
                    <td class="label">Terminal Status:</td>
                    <td id="infoTerminalStats"><?php echo $infoTerminalStats ?></td>
                </tr>
                <tr>
                    <td class="label">Description:</td>
                    <td id="infoTerminalDesc"><?php echo $infoTerminalDesc ?></td>
                    <td class="label">Installation Type:</td>
                    <td id="infoInstallType"><?php echo $infoInstallType ?></td>
                </tr>
                <tr>
                    <td class="label">Location:</td>
                    <td id="infoTerminalLoc"><?php echo $infoTerminalLoc ?></td>
                    <td class="label">Partner Institution:</td>
                    <td id="inPartnetInst"><?php echo $instname ?></td>
                </tr>
                <tr>
                    <td class="label">Area:</td>
                    <td id="infoTerminalArea"><?php echo $infoAreaName ?></td>
                    <td class="label">Outlet:</td>
                    <td id="inOutlet"><?php echo $outletname ?></td>
                </tr>
                <tr>
                	<td class="label">Branch:</td>
                    <td id="infoTerminalBranch"><?php echo $infoBranchName ?></td>
                    <td class="label">EMV Enabled:</td>
                    <td id="isEMV"><?php echo ($isEMV == "Y" ? "Yes" : "No"); ?></td>
                </tr>
                <tr>
                    <td class="label">Last Command:</td>
                    <td id="infoLastCommand"><?php echo $infoLastCommand ?></td>
                    <td class="label">Command Status:</td>
                    <td id="infoCommandStatus"><?php echo $infoCommandStatus ?></td>
                </tr>
            </table>
        </div>
        <div id="tabContacts" class="tab_content">
        </div>
        <div id="tabCounters" class="tab_content">
        </div>
        <div id="tabPrograms" class="tab_content nopadding">
            <table class="dataTable">
                <thead>
                    <tr>
                        <th>TP File</th>
                        <th>TP Type</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
        <div id="tabConfig" class="tab_content nopadding">
            <table class="dataTable">
                <thead>
                    <tr>
                        <th>Information</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
        <div id="tabFitness" class="tab_content nopadding">
            <table class="dataTable">
                <thead>
                    <tr>
                        <th>Information</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
        <div id="tabSupplies" class="tab_content nopadding">
            <table class="dataTable">
                <thead>
                    <tr>
                        <th>Information</th>
                        <th>Status</th>
                        <th>Status Code</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
        <div id="tabCmdHistory" class="tab_content nopadding">
            <table class="dataTable">
                <thead>
                    <tr>
                        <th>Command</th>
                        <th>Date Log</th>
                        <th>Date Processed</th>
                        <th>Ref. No.</th>
                        <th>Status</th>
                        <th>Processed By</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
        <div id="tabStatHistory" class="tab_content nopadding">
            <table class="dataTable">
                <thead>
                    <tr>
                        <th>Transaction Date/Time</th>
                        <th>Device ID</th>
                        <th>Status</th>
                        <th>Additional Status</th>
                        <th>Severity</th>
                        <th>Ref. No.</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
        <div id="tabTransHistory" class="tab_content nopadding">
            <table class="dataTable">
                <thead>
                    <tr>
                        <th>Transaction Date/Time</th>
                        <th>Log Key</th>
                        <th>Trace No.</th>
                        <th>Transaction Code</th>
                        <th>Transaction Description</th>
                        <th>Mnemonic</th>
                        <th>Message Type</th>
                        <th>Authorizer</th>
                        <th>Void Code</th>
                        <th>Void Description</th>
                        <th>Product Key</th>
                        <th>Amt. Requested</th>
                        <th>Amt. Authorized</th>
                        <th>TP Seq. No.</th>
                        <th>Fee 1</th>
                        <th>Fee 2</th>  
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
        <div id="tabJournal" class="tab_content nopadding">
            <table id="dt_journal" class="dataTable">
                <thead>
                    <tr>
                        <th>Transaction Date/Time</th>
                        <th>Information</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
</div>
<div id="bottom">
    <span class="buttons floatRight">
    	<span style="margin-right:50px">
            <span style="margin-right:50px">
                <input type="checkbox" id="toggleView"/>
                <label for="toggleView">Toggle ATM View</label>
            </span>
            <span>
                <input type="checkbox" id="autoRefresh"/>
                <label for="autoRefresh">Refresh every </label><input type="number" id="secs" style="width:30px" class="numbersOnly" step="1" min="5" max="99" value="5" disabled><label for="autoRefresh"> secs.</label>
            </span>
        </span><button id="email" value="monitoring/transaction/testemail">Email</button
        ><button id="refreshBtn">Refresh</button
        ><button class="closebtn">Close</button>
    </span>
</div>
<style>
.dataTables_scrollBody {
	max-height: 200px !important;
}
.dataTables_custom {
	padding: 4px 0;
}
#countersDT table {
	border-collapse: collapse;
	border-spacing: 0px;
	font-size: 11px;
}
#countersDT th {
	background-color: #444;
	border: 1px solid #333;
}
#countersDT td {
	margin: 0;
	padding: 0px 4px;
	border: 1px solid #333;
}
input[type="checkbox"] {
	position: relative;
	top: 2px;
}
input[step] {
	font-size:11px;
	padding:2px;
}
</style>
<script>
$(function () {
	var loading 	= '<img src="images/loader.gif" width="16" height="18" alt="loading" title="loading"/>';
	var	error 		= '<img src="images/error.gif" width="7" height="19" alt="error" title="error" /> Error: ';
	var	status 		= '#status';
	var	cboStatus 	= '#atmStatus';
	var cboLocation = '#location';
	var	atmList 	= '#terminalList';
	var	atm 		= '.terminal';
	var	htCode 		= '#hTerminalCode';
	var	htLuno	 	= '#hTerminalLuno';
	var	htProgCode 	= '#hTerminalProgCode';
	var	htProgLang 	= '#hTerminalProglang';
	var	thisPage 	= 'monitoring/atm/';
	var	toggleView 	= '#toggleView';
	var	autoRefresh = '#autoRefresh';
	var refTimer 	= '#secs';
	var	jqxhr 		= null;
	var	lastval 	= null;
    var autologout  = null;
	
	var terminal = {
		termcode: $(htCode).text(),
		luno:	  $(htLuno).text(),
		progcode: $(htProgCode).text(),
		proglang: $(htProgLang).text()
	};
	
	var oTable = $(DATATABLE).dataTable({
		bRetrieve: true,
		bJQueryUI: true,
		aLengthMenu: DTLENGTHMENU,
		aaSorting: [],
		sScrollY: '100%',
        sScrollX: '100%',
		sPaginationType: 'full_numbers',
		fnRowCallback: function(nRow, aData, iDisplayIndex) {
			var tableIndex = $.fn.dataTableExt.iApiIndex;
			
			//if in TransactionHistory Tab get row color
			if (tableIndex === 6) {				
				var msgDesc = aData[6];
				var msgType = parseInt(msgDesc.substring(0, msgDesc.indexOf(':')));
				var vCode = parseInt(aData[8]);
				var rowColor = getRowColor(msgType, vCode);
				$(nRow).addClass(rowColor);
			}
			
			//if in Supplies Tab get row color
			if (tableIndex === 3) {
				oTable.fnSetColumnVis(2, false);
				var statCode = parseInt(aData[2]);
				var rowColor = getRowColorSupplies(statCode);
				$(nRow).addClass(statCode);
			}
			return nRow;
		}
	});
	
	preload([
		'images/connect.gif',
		'images/loader.gif',
		'images/error.gif',
		'images/atm/atm-40-2.gif',
		'images/atm/atm-40-3.gif',
		'images/atm/atm-40-6b.gif',
		'images/atm/atm-40-7b.gif',
		'images/atm/atm-40-5.gif',
		'images/atm/atm-40-9B.gif',
		'images/atm/atm-40-4.gif',
		'images/atm/animated.gif',
		'images/atm/atm-40.gif',
		'images/atm/atm-40-23.gif',
		'images/atm/atm-40-8.gif'
	]);
	
	//for monitoring forms
	/*var windowID = '#atmMonitoring';
	$(windowID).css({
		width: 'auto'
	});*/
		
	/*$(window).resize(function () {
		$(windowID).css({
			width: '100%'
		});
		
		$('#wrapper').css({
			width: 'auto'
		});
	});*/
	//end
	
    initContextMenu();
	
    $(atm).first().addClass('selected');
    $(TABCONTENT).hide();
    $(TABS).first().addClass('active').show();
    $(TABCONTENT).first().show();
    $(TABS).click(function () {
		
		//hide messageBox
		if ($(MSGBOX).length) {
			$(MSGBOX).dialog('close');
		}
		
		//hide context menu
        $('#atmCommands').hide();
		
        if (!$(this).hasClass('active')) {
			
            $(WRAPPER).height('auto');
            $(TABS).removeClass('active');
			
			//adds reference to var "tab"
            var tab = $(this);
            tab.addClass('active');
				
            atmRefresh();
			
            jqxhr.success(function (data) {

                if (data.auth !== undefined && !data.auth) {
                    messageBox(data.message);
                } else {
                    $(TABS).removeClass('active');
                    tab.addClass('active');
                    $(TABCONTENT).hide();               

                    var tabID = $(TABSActive).find('a').attr('href');

                    $(tabID).show();
                    oTable.fnAdjustColumnSizing();
                }
                
            });	
        }
        return false;
    });
	
    $(atm).die('click').live('click', function () {
		terminal = {
			termcode: this.id,
			luno:	  $(this).attr('luno'),
			progcode: $(this).attr('progcode'),
			proglang: $(this).attr('proglang')
		};
        atmRefresh();
    });
	
    $(cboStatus).change(function () {
        filterATM(this.value, lastval, $(cboLocation).val());
    }).click(function () {
        lastval = this.value
    }).focus(function () {
		if ($(MSGBOX).length > 0) {
			$(MSGBOX).dialog('close');
		}
	});
	
    $('#refreshBtn').click(function (e) {
		atmRefresh();
		e.preventDefault();
	});
	
    flashATMStats();
	
    //$('#atmCommands').live('isVisible', isVisible);
	
	$(toggleView).change(function() {
		if ($(this).attr('checked')) {
			$('ul.tabs, div.tab_container').hide();
			$('#terminalList').css('max-height','360px');
		} else {
			$('ul.tabs, div.tab_container').show();
			$('#terminalList').css('max-height','170px');
			oTable.fnAdjustColumnSizing();
		}
	});
	
	$(autoRefresh + ',' + refTimer).change(function() {
		var timer = $('#secs');
		clearInterval(ref);
		if ($(autoRefresh).attr('checked')) {
			timer.removeAttr('disabled');
			atmRefresh();
			ref = setInterval(function () {
				atmRefresh();
			}, timer.val() * 1000);
		} else {
			timer.attr('disabled', true);
			clearInterval(ref);
		}
	});
	
	$(cboLocation).change(function (e) {
		//create dummy option
		var selected = $(this).find('option:selected');
		var desc = selected.text();
		desc = $.trim(desc.replace(/●/gi, ''));
		var loccode = selected.attr('loccode');
		$(this).prepend('<option class="fake" selected value="'+ this.value +'" loccode="'+ loccode +'">'+ desc +'</option>');
		$('.fake:not(:first)').remove();
		$('.fake').hide();
		//end
		
		var status = $(cboStatus).val();
        filterATM(status, status);
	});
	
    function flashATMStats() {
        clearInterval(intervals);
        intervals = setInterval(function () {
            $(atm + ' .toggle').toggle()
        }, 1000)
    }
	
    function filterATM(statusCode, statDesc) {
        var desc;
		var brCode = $(cboLocation).val();
		var locCode = $(cboLocation).find('option:selected').attr('loccode');
		
		requests.push(
			$.ajax({
				url: thisPage + 'get',
				type: 'GET',
				cache: false,
				//async: false,
				data: {
					status: statusCode,
					brcode: brCode,
					loccode: locCode
				},
				dataType: 'json',
				beforeSend: function () {
					$(cboStatus +','+ cboLocation).attr('disabled', true);
					$(status).html(loading + 'Retrieving ATM info...').show();
					desc = $(cboStatus + ' option:selected').text();
				},
				error: function (jqXHR, textStatus, errorThrown) {
					if (jqXHR.status > 0) {
						$(status).html(error + '(' + textStatus + ')').show();
					}
				},
				success: function (data) {
					if (data.success === true) {
						$(atmList).html(data.atm);
						
						//select first ATM
						var term = $(atm).first();
						terminal = {
							termcode: term.attr('id'),
							luno:	  term.attr('luno'),
							progcode: term.attr('progcode'),
							proglang: term.attr('proglang')
						};
						atmRefresh(false);
						//end
					} else {
						$(cboStatus).val(statDesc);
						messageBox('There are no "' + desc + '" terminals at the moment');
					}
					$(cboStatus +','+ cboLocation).removeAttr('disabled');
					flashATMStats();
				},
				complete: function () {
					$(status).html('').hide();
					initContextMenu();
				}
			})
		);
    }
	
    var finished = true;

	//retrieveATMList bool
    function atmRefresh(retrieveATMList) {
        if (finished) {
    		if (retrieveATMList === undefined) {
    			retrieveATMList = 1;
    		}
    		
            var toolbar = $(TABSActive).find('a').attr('href');
    		var tab 	= toolbar.substring(4).toLowerCase();
    		var page 	= 'atmtabs/' + tab;
    		var stat 	= $(cboStatus).val();
    		var brCode 	= $(cboLocation).val();
    		var locCode = $(cboLocation).find('option:selected').attr('loccode');
    		
            requests.push(
    			jqxhr = $.ajax({
    				url: page,
    				type: 'GET',
    				cache: false,
    				data: {
    					terminalcode: 	terminal.termcode,
    					luno: 			terminal.luno,
    					progcode: 		terminal.progcode,
    					proglang: 		terminal.proglang,
    					status: 		stat,
    					brcode:			brCode,
    					loccode:		locCode,
    					tab: 			tab,
    					list:			retrieveATMList
    				},
    				dataType: 'json',
    				beforeSend: function () {
    					abortAJAXRequests();
    					
    					$(cboStatus).attr('disabled', true);
    					
    					//show status indicator
    					$(status).html(loading + 'Retrieving ATM info...').show();
    				},
    				error: function (jqXHR, textStatus, errorThrown) {
    					//show error msg (upper right corner)
    					if (jqXHR.status > 0) {
    						$(status).html(error + '(' + textStatus + ')').show();
    					}
    				},
    				success: function (data) {
    					$(cboStatus +','+ cboLocation).removeAttr('disabled');
    					if (data.atm !== null) {
    						$(atmList).html(data.atm);
    					}
                        if (data.auth == false) {
                            clearInterval(ref);
                            messageBox(data.message);
                            
                            setTimeout(logOut, 10000);

                            $(MSGBOX).one('dialogbeforeclose', function () {
                                logOut();
                            });
                        }
    					if (data.dataTables === true) {
    						//populate datatables
    						$.fn.dataTableExt.iApiIndex = data.tableIndex;
    						oTable.fnClearTable(0);
    						oTable.fnAddData(data.details);
    						oTable.fnDraw();
    						oTable.fnAdjustColumnSizing();
    						
    						if (data.toolbar !== null) {
    							$('.dataTables_custom').remove();
    							$(toolbar).find('.ui-toolbar:first').append(data.toolbar);
    						}
    						
    					} else {
    						$(toolbar).html(data.details);
    					}
    					
    					//populate header
    					$(htCode).text(terminal.termcode);
    					$(htLuno).text(terminal.luno);
    					$(htProgCode).text(terminal.progcode);
    					$(htProgLang).text(terminal.proglang);
    					//end
    					
    					flashATMStats();
    					$(status).html('').hide();
    					$(atm).removeClass('selected');
    					$('#' + terminal.termcode + atm).addClass('selected');
    				},
    				complete: function () {
                        finished = true;
    					initContextMenu();
    				}
            	})
    		);
        }
    }
	
    function initContextMenu() {		
		$.contextMenu({
			selector: atm, 
			callback: function(action, opt) {
				
				var termCode = opt.$trigger.attr('id');
				var luno = opt.$trigger.attr('luno');
				var msg = opt.items[action].msg + ' <strong>[' + termCode + ']</strong>?';
				
				messageBox(msg, 'Confirm', 'confirm', function () {
					
					//execute terminal command
					var param = {
						action: 	action,
						termcode: 	termCode,
						luno: 		luno
					};
					//console.log(param);
					
					$.ajax({
						url: 'monitoring/atmcmd/action',
						type: 'POST',
						data: param,
						dataType: 'json'
					});
					
					atmRefresh();
			
					$(MSGBOX).dialog('close');
				});
			},
			items: {
				termUp:	{
					name: 'Terminal Up',
					msg: 'Continue terminal up',
					disabled: function (key, opt) {
						var arrUp = [0, 1, 2, 6, 8, 14, 15]; //ready, online, offline, out of service, temporarily down, for deployment, under maintenance
						var status = parseInt(opt.$trigger.attr('status'));
						
						if ($.inArray(status, arrUp) === -1) {
							return true;
						} else {
							return false;
						}
					}
				},
				termDown: {
					name: 'Terminal Down',
					msg: 'Continue terminal down',
					disabled: function (key, opt) {
						var arrDn = [0, 1, 3, 4, 5, 7]; //ready. online, loading, supervisor mode, in service, comm error
						var status = parseInt(opt.$trigger.attr('status'));
						
						if ($.inArray(status, arrDn) === -1) {
							return true;
						} else {
							return false;
						}
					}
				},
				sep1: '---------',
				termReset: {
					name: 'Reset Terminal',
					msg: 'Reset terminal'
				},
				sep2: '---------',
				termLoad: {
					name: 'Terminal Load',
					msg: 'Continue loading terminal'
				},
				sep3: '---------',
				getTermInfo: {
					name: 'Get Terminal Information',
					msg: 'Get ATM information'
				},
				getSupplyCounters: {
					name: 'Get Supply Counters',
					msg: 'Get ATM supply counters'
				},
				sep4: '---------',
				synchronize: {
					name: 'Synchronize Date/Time',
					msg: 'Synchronize ATM date/time'
				},
				sep5: '---------',
				generateKey: {
					name: 'Generate New Key',
					msg: 'Generate new ATM key'
				},
				sep6: '---------',
				tagForDevt: {
					name: 'Tag Terminal For Deployment',
					msg: 'Set terminal as "For Deployment"'
				},
				tagUnderMaintenance: {
					name: 'Tag Terminal Under Maintenance',
					msg: 'Set terminal as "Under Maintenance"'
				}
			}
		});
    }
});</script>