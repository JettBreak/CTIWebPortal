<div id="hostMonitoring">
    <h1>Host Monitoring</h1>
    <table id="nodeView">
        <thead>
            <tr>
                <td width="200"><label for="chStatus">Status: </label>
                    <select id="chStatus" style="width:150px">
                        <option value="-1">All</option>
                        <?php echo html_entity_decode($statusList); ?>
                    </select></td>
                <td><strong>Node Name:</strong> <span id="hNodeName"><?php //echo $nodeName; ?></span></td>
                <td width="160"><span id="status"></span></td>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td colspan="3"><div id="nodeList"><?php //echo html_entity_decode($posList); ?></div></td>
            </tr>
        </tbody>
    </table>
    <ul class="tabs fullTabs">
        <li><a href="#tabInfo">Information</a></li>
        <li><a href="#tabTranhistory">Transaction History</a></li>
        <li><a href="#tabCmdHistory">Command History</a></li>
        <li><a href="#tabStoreAndForward">Store and Forward</a></li>
        <li><a href="#tabJournal">Journal</a></li>
    </ul>
    <div class="tab_container">
        <div id="tabInfo" class="tab_content">
            <table>
                <tr>
                	<td width="150" class="idName">Node Name:</td>
                    <td width="200" id="tNodeName">&nbsp;</td>
                    <td width="120" class="label">Status:</td>
                    <td id="tStatDesc">&nbsp;</td>
                </tr>
                <tr>
                	<td class="label">Node Description:</td>
                    <td colspan="3" id="tNodeDesc">&nbsp;</td>
                </tr>
                <tr>
                	<td class="label">System Description:</td>
                    <td colspan="3" id="tSysDesc">&nbsp;</td>
                </tr>
                <tr>
                	<td class="label">Protocol Description:</td>
                    <td colspan="3" id="tPrDesc">&nbsp;</td>
                </tr>
                <tr>
                	<td class="label">Address:</td>
                    <td colspan="3" id="tAddress">&nbsp;</td>
                </tr>
                <tr>
                	<td class="label">Contact No.:</td>
                    <td colspan="3" id="tContact">&nbsp;</td>
                </tr>
                <tr>
                	<td class="label">Last Command:</td>
                    <td id="tLastCmd">&nbsp;</td>
                    <td class="label">Command Status:</td>
                    <td id="tCmdStatus">&nbsp;</td>
                </tr>
            </table>
        </div>
        <div id="tabTranhistory" class="tab_content nopadding">
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
        <div id="tabCmdHistory" class="tab_content nopadding">
            <table class="dataTable">
                <thead>
                    <tr>
                        <th>Date Processed</th>
                        <th>Command</th>
                        <th>Ref. No.</th>
                        <th>Status</th>
                        <th>Processed By</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
        <div id="tabStoreAndForward" class="tab_content nopadding">
            <table class="dataTable">
                <thead>
                    <tr>
                        <th width="150">Transaction Date/Time</th>
                        <th>Log Seq. No.</th>
                        <th>Transaction Code</th>
                        <th>Transaction Description</th>
                        <th>Message Type</th>
                        <th>Amount Request</th>
                        <th>Void Code</th>
                        <th>Void Description</th>
                        <th>Product Key 1</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
        <div id="tabJournal" class="tab_content nopadding">
            <table class="dataTable">
                <thead>
                    <tr>
                        <th width="150">Transaction Date/Time</th>
                        <th>Information</th>
                        <th width="150">Status Code</th>
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
                <label for="toggleView">Toggle Host View</label>
            </span>
            <span>
                <input type="checkbox" id="autoRefresh"/>
                <label for="autoRefresh">Refresh every </label><input type="number" id="secs" style="width:30px" class="numbersOnly" step="1" min="5" max="99" value="5" disabled><label for="autoRefresh"> secs.</label>
            </span>
        </span>
        <button id="refreshBtn">Refresh</button
        ><button class="closebtn">Close</button>
    </span>
</div>
<style>
.dataTables_scrollBody {
	max-height:200px !important;
}
input[type="checkbox"] {
	position: relative;
	top: 2px;
}
input[step] {
	font-size:11px;
	padding:2px;
}
#nodeView {
	width: 100%;
	border-collapse: collapse !important;
	border-spacing: 0 !important;
}
#nodeView thead {
	background: #333;
}
#nodeView thead td {
	padding: 5px 10px;
}
#nodeList {
	min-height: 110px;
	max-height: 169px;
	overflow: auto;
	padding: 5px 15px 0 15px;
}
#nodeView .node, #nodeView .terminal {
	border: 1px dotted transparent;
	line-height: 12px;
	text-align: center;
	cursor: pointer;
	width: 75px;
	height: 90px;
	margin-bottom: 9px;
	display: table-column;
	vertical-align: top;
	float: left;
}
#nodeView .node.selected {
	border: 1px dotted #fff;

}
#nodeView .node {
	margin-top: 2px;
	font-size: .85em;
}
</style>
<script>
$(function () {
	//init vars
	var loading = '<img src="images/loader.gif" width="16" height="18" alt="loading" title="loading" />';
	var	error = '<img src="images/error.gif" width="7" height="19" alt="error" title="error" /> Error: ';
	var	status = '#status';
	var node = '.node';
	var	cboStatus = '#chStatus';
	var	autoRefresh = '#autoRefresh';
	var refTimer = '#secs';
	var	refreshBtn = '#refreshBtn';
	var	toggleView 	= '#toggleView';
	var host = null;
	var	jqxhr = null;
	
	var oTable = [];	
	var aLengthMenu = [
		[10, 25, 50, 100, -1], [10, 25, 50, 100, 'All'] // -1 = 'All'
	];
	//end
	
	$.fn.dataTableExt.iApiIndex = 0;
	
	oTable[0] = $('#tabTranhistory table').dataTable({
		bRetrieve: true,
		bJQueryUI: true,
		aLengthMenu: aLengthMenu,
		aaSorting: [],
		sScrollY: '100%',
		sScrollX: '100%',
		sPaginationType: 'full_numbers',
		fnRowCallback: function(nRow, aData, iDisplayIndex) {
						
			var msgDesc = aData[6];
			var msgType = parseInt(msgDesc.substring(0, msgDesc.indexOf(':')));

			var rowColor = getRowColor(msgType);
			$(nRow).addClass(rowColor);
			return nRow;
		}
	});
	
	oTable[1] = $('#tabCmdHistory table').dataTable({
		bRetrieve: true,
		bJQueryUI: true,
		aLengthMenu: aLengthMenu,
		aaSorting: [],
		sScrollY: '100%',
		sScrollX: '100%',
		sPaginationType: 'full_numbers'
	});
	
	oTable[2] = $('#tabStoreAndForward table').dataTable({
		bRetrieve: true,
		bJQueryUI: true,
		aLengthMenu: aLengthMenu,
		aaSorting: [],
		sScrollY: '100%',
		sScrollX: '100%',
		sPaginationType: 'full_numbers'
	});
	
	oTable[3] = $('#tabJournal table').dataTable({
		bRetrieve: true,
		bJQueryUI: true,
		aLengthMenu: aLengthMenu,
		aaSorting: [],
		sScrollY: '100%',
		sScrollX: '100%',
		sPaginationType: 'full_numbers'
	});
	
	preload([
		'images/connect.gif',
		'images/loader.gif',
		'images/error.gif',
		'images/host/host0.png',
		'images/host/host1.png',
		'images/host/host2.png',
		'images/host/host3.png',
		'images/host/host4.png'
	]);
	
	$(cboStatus).change(function () {
        chRefresh(true);
    }).click(function () {
        lastVal = this.value;
    }).focus(function () {
		if ($(MSGBOX).length > 0) {
			$(MSGBOX).dialog('close');
		}
	});
	
	$(refreshBtn).click(function (e) {
        chRefresh();
		e.preventDefault();
    });
	
	$(toggleView).change(function() {
		if ($(this).attr('checked')) {
			$('ul.tabs, div.tab_container').hide();
			$('#nodeList').css('max-height','360px');
		} else {
			$('ul.tabs, div.tab_container').show();
			$('#nodeList').css('max-height','169px');
			
			$.each(oTable, function(key, value) { 
			  	oTable[key].fnAdjustColumnSizing();
			});
		}
	});
	
	$(autoRefresh + ',' + refTimer).change(function() {
		var timer = $('#secs');
		clearInterval(ref);
		if ($(autoRefresh).attr('checked')) {
			timer.removeAttr('disabled');
			chRefresh();
			ref = setInterval(function () {
				chRefresh();
			}, timer.val() * 1000);
		} else {
			timer.attr('disabled', true);
			clearInterval(ref);
		}
	});
	
	$('.node').die('click').live('click', function () {
		var node = $(this);
		host = {
			nodeName: node.attr('id'),
			nodeDesc: node.attr('nodedesc'),
			statDesc: node.attr('statdesc'),
			sysDesc: node.attr('sysdesc'),
			prDesc: node.attr('prdesc'),
			address: node.attr('address'),
			contact: node.attr('contact'),
			lastCmd: node.attr('lastcmd'),
			cmdStat: node.attr('cmdstat')
		};
        chRefresh();
    });
	
	getNodeList();
	
	$(TABCONTENT).hide();
    $(TABS).first().addClass('active').show();
    $(TABCONTENT).first().show();
    $(TABS).click(function () {
		if ($(MSGBOX).length) {
			$(MSGBOX).dialog('close');
		}
        if (!$(this).hasClass('active')) {
            $(WRAPPER).height('auto');
            
            var tab = $(this);
			
			//highlight selected tab
			$(TABS).removeClass('active');
            tab.addClass('active');
			
            var activeTab = $(TABSActive).find('a').attr('href');
			
			chRefresh();
			
            jqxhr.success(function (data) {
                $(TABS).removeClass('active');
                tab.addClass('active');
                $(TABCONTENT).hide();
                $(activeTab).show();
				
				if (data.dataTables === true) {
					var i = data.tableIndex;
					oTable[i].fnAdjustColumnSizing();
				}
            });
        }
        return false;
    });
	
	function chRefresh(isFilter) {
		if (isFilter === undefined) {
			isFilter = 0;
		}
			
		var tab = $(TABSActive).find('a').attr('href').substring(4).toLowerCase();
		var statcode = $(cboStatus).val();
		var page = 'chtabs/' + tab;
		
		var selected = $(cboStatus + ' option:selected').text();	
			
		requests.push(
			jqxhr = $.ajax({
				url: page,
				type: 'GET',
				cache: false,
				data: {
					nodeName: host.nodeName,
					status: statcode,
					isFilter: isFilter
				},
				dataType: 'json',
				timeout: TIMEOUT,
				beforeSend: function () {
					abortAJAXRequests();
					
					//disable CH status filter
					$(cboStatus).attr('disabled', true);
					
					//show status indicator (loading...)
					$(status).html(loading + 'Retrieving Host info...').show();
				},
				error: function (jqXHR, textStatus, errorThrown) {
					//display status indicator
					if (jqXHR.status > 0) {
						$(status).html(error + '(' + textStatus + ')').show()
					}
				},
				success: function (data) {
					
					if (data.nodes.length > 0) {
						//if DT tabs opened
						if (data.dataTables === true) {
							//populate DTs
							//$.fn.dataTableExt.iApiIndex = data['tableIndex'];
							var i = data.tableIndex;
							oTable[i].fnClearTable(0);
							oTable[i].fnAddData(data.details);			
							oTable[i].fnDraw();
							//oTable[i].fnAdjustColumnSizing();
						}
						
						if (isFilter !== true) {
							var selectedNode = '#' + host.nodeName;
						}
						populateListView(data.nodes, selectedNode);
					} else {
						$(cboStatus).val(lastVal);
						messageBox('There are no "' + selected + '" hosts at the moment');
					}
				},
				complete: function () {
					//enable CH status filter
					$(cboStatus).removeAttr('disabled');

					//hide status indicator
					$(status).html('').hide();
				}
			})
		);
	}
	
	function getNodeList() {
		$.ajax({
			url: 'monitoring/host/getHosts',
			dataType: 'json',
			success: function(data) {
				if (data.success) {
					//populate nodeList html
					populateListView(data.nodeList);
					//end
					
				} else {
					messageBox(data.message);
				}
			}
		});
	}
	
	function populateListView(nodeList, selected) {
		var nodes = '';
		var selectedIndex = 0;
		
		if (!selected) {
			selected = 'div:first';
		}
		
		var selectedIndex = 0;
		
		$(nodeList).each(function(index) {
			if (selected) {
				if (selected.substr(1) === this.nodeName) {
					selectedIndex = index;
				}
			}
			nodes += '<div id="' + this.nodeName + '" class="node" ' +
				'status="' + this.status + '" ' +
				'nodedesc="' + this.nodeDesc + '" ' +
				'statdesc="' + this.statDesc + '" ' +
				'sysdesc="' + this.sysDesc + '" ' +
				'prdesc="' + this.prDesc + '" ' +
				'address="' + this.address + '" ' +
				'contact="' + this.contact + '" ' +
				'lastcmd="' + this.lastCmd + '" ' +
				'cmdstat="' + this.cmdStat + '">' +
				'<img src="images/host/' + this.img + '" title="Click to get Host info"/><br />' +
				'<span>' + this.nodeName + '</span><br />' +
				'<span>(' + this.statDesc + ')</span>' +
			'</div>';
			
		});

		$(node).removeClass('selected');
		
		$('#nodeList')
			.html(nodes)
			.find(selected)
			.addClass('selected');
			
		var n = nodeList[selectedIndex]; //first node reference
					
		//populate tab tds
		
		host = {
			nodeName: n.nodeName
		}
		
		$('#hNodeName, #tNodeName').text(n.nodeName);
		$('#tNodeDesc').text(n.nodeDesc);
		$('#tStatDesc').text(n.statDesc);
		$('#tSysDesc').text(n.sysDesc);
		$('#tPrDesc').text(n.prDesc);
		$('#tAddress').text(n.address);
		$('#tContact').text(n.contact);
		$('#tLastCmd').text(n.lastCmd);
		$('#tCmdStatus').text(n.cmdStat);
		//end
	}
});
</script>