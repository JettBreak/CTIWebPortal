<div style="width:800px">
    <h1>POS Monitoring</h1>
    <table id="termView">
        <thead>
            <tr>
                <td width="200"><label for="posStatus">Status: </label>
                    <select id="posStatus" style="width:150px">
                        <option value="-1">All</option>
                        <?php echo html_entity_decode($statusList); ?>
                    </select></td>
                <?php echo html_entity_decode($branches); ?>
                <td><strong>POS Code: </strong><span id="hposCode"><?php echo $infoCode; ?></span></td>
                <td width="160"><span id="status"></span></td>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td colspan="4"><div id="terminalList"><?php echo html_entity_decode($posList); ?></div></td>
            </tr>
        </tbody>
    </table>
    <ul class="tabs fullTabs">
        <li><a href="#tabInfo">Information</a></li>
        <li><a href="#tabTranshistory">Transaction History</a></li>
        <li><a href="#tabJournal">Journal</a></li>
    </ul>
    <div class="tab_container">
        <div id="tabInfo" class="tab_content">
            <table>
                <tr>
                    <td class="idName" width="90">POS Code:</td>
                    <td id="infoCode" class="idNumber" width="200"><?php echo $infoCode ?></td>
                    <td width="90" class="label">LUNO:</td>
                    <td id="infoLuno" width="200"><?php echo $infoLuno ?></td>
                </tr>
                <tr>
                    <td class="label">POS ID:</td>
                    <td id="infoID"><?php echo $infoID ?></td>
                    <td class="label">POS Status:</td>
                    <td id="infoStats"><?php echo $infoStats ?></td>
                </tr>
                <tr>
                    <td class="label">Description:</td>
                    <td id="infoDesc" colspan="3"><?php echo $infoDesc ?></td>
                </tr>
                <tr>
                    <td class="label">Area:</td>
                    <td id="infoArea" colspan="3"><?php echo $infoArea ?></td>
                </tr>
                <tr>
                	<td class="label">Branch:</td>
                    <td id="infoBranch" colspan="3"><?php echo $infoBranch ?></td>
                </tr>
                <tr>
                    <td class="label">Location:</td>
                    <td id="infoLocation" colspan="3"><?php echo $infoLocation ?></td>
                </tr>
            </table>
        </div>
        <div id="tabTranshistory" class="tab_content nopadding">
            <table class="dataTable">
                <thead>
                    <tr>
                        <th>Transaction Date/Time</th>
                        <th>Transaction Code</th>
                        <th>Transaction Description</th>
                        <th>Void Code</th>
                        <th>Void Description</th>
                        <th>Log Seq. No.</th>
                        <th>Trace No.</th>
                        <th>TP Seq. No.</th>
                        <th>Message Type</th>
                        <th>Amt. Req</th>
                        <th>Amt. Ath</th>
                        <th>Fee 1</th>
                        <th>Fee 2</th>
                        <th>Product Key 1</th>
                        <th>Product Key 2</th>
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
                <label for="toggleView">Toggle POS View</label>
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
</style>
<script>
$(function () {
	//init vars
	var loading = '<img src="images/loader.gif" width="16" height="18" alt="loading" title="loading" />';
	var	error = '<img src="images/error.gif" width="7" height="19" alt="error" title="error" /> Error: ';
	var	status = '#status';
	var	cboStatus = '#posStatus';
	var	posList = '#terminalList';
	var	terminal = '.terminal';
	var	hposCode = '#hposCode';
	var cboLocation = '#location';
	var	toggleView = '#toggleView';
	var	autoRefresh = '#autoRefresh';
	var refTimer = '#secs';
	var	refreshBtn = '#refreshBtn';
	var	jqxhr = null;
	
	var selected = $(terminal).first();
	var pos = {
		code: 		selected.attr('id'),
		termid: 	selected.attr('termid'),
		luno:		selected.attr('luno'),
		desc:		selected.attr('desc'),
		status: 	selected.attr('status'),
		statdesc: 	selected.attr('statdesc'),
		location:	selected.attr('location')			
	};
	
	var lastVal = -1;
	//end
	
	var oTable = [];	
	var aLengthMenu = [
		[10, 25, 50, 100, -1], [10, 25, 50, 100, 'All'] // -1 = 'All'
	];
	
	$.fn.dataTableExt.iApiIndex = 0;
	
	oTable[0] = $('#tabTranshistory table').dataTable({
		bRetrieve: true,
		bJQueryUI: true,
		aLengthMenu: aLengthMenu,
		aoColumns: [
			null, //Transaction Date/Time,
			{ sClass: 'centerAlign' }, //Transaction Code
			null, //Transaction Description
			{ sClass: 'centerAlign' }, //Void Code
			null, //Void Description
			{ sClass: 'centerAlign' }, //Log Seq. No.
			null, //Trace No.
			{ sClass: 'centerAlign' }, //TP Seq. No.
			null, //Message Type
			{ sClass: 'rightAlign' }, //Amt. Requested
			{ sClass: 'rightAlign' }, //Amt. Authorized
			{ sClass: 'rightAlign' }, //Fee 1
			{ sClass: 'rightAlign' }, //Fee 2
			null, //Product Key 1
			null //Product Key 2
		],
		aaSorting: [],
		sScrollY: '100%',
		sScrollX: '100%',
		sPaginationType: 'full_numbers',
		fnRowCallback: function(nRow, aData, iDisplayIndex) {
						
			var msgDesc = aData[7];
			var msgType = parseInt(msgDesc.substring(0, msgDesc.indexOf(':')));

			var rowColor = getRowColor(msgType);
			$(nRow).addClass(rowColor);
			return nRow;
		}
	});
	
	oTable[1] = $('#tabJournal table').dataTable({
		bRetrieve: true,
		bJQueryUI: true,
		aLengthMenu: aLengthMenu,
		aaSorting: [],
		sScrollY: '100%',
		sScrollX: '100%',
		sPaginationType: 'full_numbers'
	});
		
    $(terminal).first().addClass('selected');
	
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
			
            posRefresh();
			
            jqxhr.success(function (data) {
                $(TABS).removeClass('active');
                tab.addClass('active');
                $(TABCONTENT).hide();
                $(activeTab).show();
				
				if (data.dataTables === true) {
					var i = data.tableIndex;
					oTable[i].fnAdjustColumnSizing();
				}
            })
        }
        return false
    });
	
	preload([
		'images/connect.gif',
		'images/loader.gif',
		'images/error.gif',		
		'images/pos/pos-proc.gif',
		'images/pos/pos-procoff.gif',
		'images/pos/pos-online.gif',
		'images/pos/pos-idle.gif',
		'images/pos/pos-offline.gif',
		'images/pos/pos-oos.gif',
		'images/pos/pos-unknown.gif'
	]);
	
    $(terminal).die('click').live('click', function () {		
		pos = {
			code: 		this.id,
			termid: 	$(this).attr('termid'),
			luno:		$(this).attr('luno'),
			desc:		$(this).attr('desc'),
			status: 	$(this).attr('status'),
			statdesc: 	$(this).attr('statdesc'),
			location:	$(this).attr('location')			
		};	
        posRefresh();
    });
	
    $(cboStatus).change(function () {
        posRefresh(true);
    }).click(function () {
        lastVal = this.value;
    }).focus(function () {
		if ($(MSGBOX).length > 0) {
			$(MSGBOX).dialog('close');
		}
	});
	
    $(refreshBtn).click(function (e) {
        posRefresh();
		e.preventDefault();
    });
	
	$(toggleView).change(function() {
		if ($(this).attr('checked')) {
			$('ul.tabs, div.tab_container').hide();
			$('#terminalList').css('max-height','360px');
		} else {
			$('ul.tabs, div.tab_container').show();
			$('#terminalList').css('max-height','169px');
			
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
			posRefresh();
			ref = setInterval(function () {
				posRefresh();
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
		
		//var status = $(cboStatus).val();
        posRefresh(true);
	});
	
    /*function filterPOS(lastVal) {
		var posStatus = $(cboStatus).val();
        var selected = $(cboStatus + ' option:selected').text();
		
		var brCode 	= $(cboLocation).val();
		var locCode = $(cboLocation).find('option:selected').attr('loccode');
		
        $.ajax({
            url: 'monitoring/pos/status',
            type: 'GET',
            cache: false,
			//async: false,
            data: {
				status: posStatus,
				brcode: brCode,
				loccode: locCode
			},
            dataType: 'json',
            beforeSend: function () {
				//disable POS status filter
                $(cboStatus +','+ cboLocation).attr('disabled', 'disabled');
				
				//show status indicator (loading...)
                $(status).html(loading + 'Retrieving POS info...').show();
            },
            error: function (jqXHR, textStatus, errorThrown) {
                //display status indicator
                if (jqXHR.status > 0) {
					$(status).html(error + '(' + textStatus + ')').show()
				}
            },
            success: function (data) {
                if (data.success === true) {
					//populate POS list view
                    $(posList).html(data.pos);

                } else {
					//no POS found with status x
                    $(cboStatus).val(lastVal);
                    messageBox('There are no "' + selected + '" terminals at the moment')
                }
				
				//enable POS status filter
                $(cboStatus +','+ cboLocation).removeAttr('disabled');
            },
            complete: function () {
				//hide status indicator
                $(status).html('').hide();
            }
        });
    }*/
	
    function posRefresh(isFilter) {
		if (isFilter === undefined) {
			isFilter = 0;
		}
		
		var tab = $(TABSActive).find('a').attr('href').substring(4).toLowerCase();
		var statcode = $(cboStatus).val();
		var page = 'postabs/' + tab;
		
		var brCode 	= $(cboLocation).val();
		var locCode = $(cboLocation).find('option:selected').attr('loccode');
		var selected = $(cboStatus + ' option:selected').text();
		
		requests.push(
			jqxhr = $.ajax({
				url: page,
				type: 'GET',
				cache: false,
				data: {
					poscode: pos.code,
					status: statcode,
					brcode: brCode,
					loccode: locCode,
					tab: tab,
					isFilter: isFilter
				},
				dataType: 'json',
				timeout: TIMEOUT,
				beforeSend: function () {
					abortAJAXRequests();
					
					//disable POS status filter
					$(cboStatus +','+ cboLocation).attr('disabled', true);
					
					//show status indicator (loading...)
					$(status).html(loading + 'Retrieving POS info...').show();
				},
				error: function (jqXHR, textStatus, errorThrown) {
					//display status indicator
					if (jqXHR.status > 0) {
						$(status).html(error + '(' + textStatus + ')').show()
					}
				},
				success: function (data) {
					if (data.pos !== '') {
						//populate POS list view
						$(posList).html(data.pos);
						
						//if DT tabs opened
						if (data.dataTables === true) {
							//populate DTs
							//$.fn.dataTableExt.iApiIndex = data['tableIndex'];
							var i = data.tableIndex;
							oTable[i].fnClearTable(0);
							oTable[i].fnAddData(data.details);			
							oTable[i].fnDraw();
							//oTable[i].fnAdjustColumnSizing();
						} else {
							
							if (data.info !== null) {
								pos.areaname = data.info.areaname;
								pos.brname = data.info.brname;
							}
						}
					} else {
						//no POS found with status x
						$(cboStatus).val(lastVal);
						messageBox('There are no "' + selected + '" terminals at the moment')
					}
					
					if (data.selected !== null) {						
						pos = {
							code: 		data.selected.termcode,
							termid: 	data.selected.termid,
							luno:		data.selected.luno,
							desc:		data.selected.description,
							status: 	data.selected.status,
							statdesc: 	data.selected.statdesc,
							location:	data.selected.location
						};
					}
					
					
					$(terminal).removeClass('selected');
					$('#' + pos.code).addClass('selected');
					
					$('#infoCode, #hposCode').text(pos.code);
					$('#infoLuno').text(pos.luno);
					$('#infoID').text(pos.termid);
					$('#infoStats').text(pos.statdesc);
					$('#infoDesc').text(pos.desc);
					$('#infoArea').text(pos.areaname);
					$('#infoBranch').text(pos.brname);
					$('#infoLocation').text(pos.location);
				},
				complete: function () {
					//enable POS status filter
					$(cboStatus +','+ cboLocation).removeAttr('disabled');

					//hide status indicator
					$(status).html('').hide();
				}
			})
		);
    }
});
</script>