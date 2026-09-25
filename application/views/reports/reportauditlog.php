<div id="branchList" style="width: 800px">
    <h1>Audit Trail</h1>
    <table class="dataTable">
        <thead>
            <tr>
            	<th>Request ID</th>
            	<th width="100">Date Requested</th>
                <th width="100">Report ID</th>
                <th>Report Name</th>
                <th>Process Type</th>
                <th width="100">Status</th>
                <th>userseqno</th>
                <th>brseqno</th>
                <th>reportreqid</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
</div>
<div id="bottom">
    <span class="buttons floatLeft">
        <button id="newBtn">New</button
        ><button id="removeBtn" disabled>Remove</button
        >
    </span>
    <span class="buttons floatRight">
    	<input type="checkbox" id="autoRefresh" checked="checked"/>
        <label for="autoRefresh">Refresh every </label><input type="number" id="secs" style="width:30px" class="numbersOnly" step="1" min="5" max="99" value="5" disabled><label for="autoRefresh"> secs.</label>
        &nbsp;&nbsp;
        <button id="refreshBtn">Refresh</button
        ><button class="closebtn">Close</button>
    </span>
</div>
<style>
.dataTables_scrollBody{min-height:150px !important;max-height:300px !important;}
</style>
<script>
$(function() {
	//init vars
	var finished = true;
    var newBtn = '#newBtn',
        refreshBtn = '#refreshBtn',
		autoRefresh = '#autoRefresh',
		refTimer = '#secs',
		branch = {};
	var timer = $('#secs')
	var status = 'init';
	//end
	
    oTable = $(DATATABLE).dataTable({
        bRetrieve: true,
        bJQueryUI: true,
		aLengthMenu: DTLENGTHMENU,
		/*'aoColumns': [
			{ sWidth: '25%' },
			{ sWidth: '30%' },
			{ sWidth: '45%' }
		],*/
		aoColumns: ([ //hide columns   
			{ sClass: 'centerAlign' }, // Date
			{ sClass: 'centerAlign' }, // Date
			{ sClass: 'centerAlign' }, // Report ID
			{ sClass: 'centerAlign' }, // Report Name
			{ sClass: 'centerAlign' }, // Report Process Type
			{ sClass: 'centerAlign' }, // Link
			{ bVisible: false },// userseqno
			{ bVisible: false },// brseqno,
			{ bVisible: false },// reportreqid
		]),
        aaSorting: [[1, 'desc']], //sort by brcode
        sScrollY: '100%',
        sScrollX: '100%',
        sPaginationType: 'full_numbers',
        fnInitComplete: function () {
			//when DT InitComplete, load data
			//getData('init');
			autoreftimer();
        },
        fnRowCallback: function (nRow, aData, iDisplayIndex) {	
            $(nRow).attr('id', aData[0]).unbind('click dblclick').click(function () {
				//close dialog boxes
				if ($(MSGBOX).length > 0) {
					$(MSGBOX).dialog('close');
				}
				
				//highlight rows
				$('tbody tr').removeClass('rowSelected');
                $(this).addClass('rowSelected');

                if ($(DATATABLE).find('input:checked').length > 0) {
					$(removeBtn).removeAttr('disabled');
				} else {
					$(removeBtn).attr('disabled', true);
				}
				
				//set temp var
				branch = {
					date: aData[0],
					repid: aData[1],
					repname: aData[2],
					proctype: aData[3],
					link: aData[4],
					userseqno: aData[5],
					brseqno: aData[6],
					reportreqid: aData[7]
				}

				if (aData[5] == 'Not Found' || aData[5] == 'Failed') {
                	$(removeBtn).removeAttr('disabled');
				}
            });
			$('tbody tr').removeClass('rowSelected');
            $(removeBtn).attr('disabled', 'disabled');
			
			//unset temp obj
			branch = {};
            return nRow
        },
		fnDrawCallback: function () {
			$('tbody tr').removeClass('rowSelected');
		}
    });

	function autoreftimer()
	{
		clearInterval(ref);
		if ($(autoRefresh).attr('checked')) {
			timer.removeAttr('disabled');
			getData();
			//$(refreshBtn).trigger()
			ref = setInterval(function () {
				getData('autoRef');
			}, timer.val() * 1000);
		} else {
			timer.attr('disabled', true);
			$('#refreshBtn').removeAttr('disabled');
			clearInterval(ref);
		}
	}

	$(autoRefresh + ',' + refTimer).change(function() {
		autoreftimer();
	});
	
    $(newBtn).click(function () {
        window.location.hash = 'reports/reportauditlogrequest';
        return false
    });
	
    $('#removeBtn').click(function (e) {
		if (branch) {
		var buttons = {
			Remove: function (f) {

				var chkarr1 = 0,
		    		chkarr2 = 0;

		    	if ($('#checkArray :checkbox:checked').length > 0) {
		    		chkarr1 = 1;
		    	}

		    	if ($('#checkArray2 :checkbox:checked').length > 0) {
		    		chkarr2 = 1;
		    	}

				requests.push(
					$.ajax({
						type: 'POST',
						url: 'reports/remove/submit',
						data: {
							increpid: branch.date,
							incnotfound: chkarr1,
							incfailed: chkarr2
						},
						dataType: 'json',
						beforeSend: function() {
							waitMessage('Removing transaction...');
						},
						success: function(data) {
							if (data.success) {
								messageBox('Reports successfully removed');
								$(MSGBOX).one('dialogbeforeclose', function () {
				                 	$(refreshBtn).trigger('click');
									$(DIALOG).dialog('close');
				                 });
							} else {
								messageBox(data.message);
							}
						},
						complete: function() {
							//$(DIALOG).dialog('close');
						}
					})
				);
				//return false;
			},
			Cancel: function () {
				$(this).dialog('close');
				//return false;
			}
		}

		var params = {
			increpid: branch.date
		}
		params = $.param(params, true);
		
		modalDialog('reports/remove?'+ params, 'Remove Reports', buttons, 200);
		
		$(DIALOG).bind('dialogbeforeclose, dialogfocus', function () {
			if ($(MSGBOX).length > 0) {
				$(MSGBOX).dialog('close');
			}
		});
		}
		e.preventDefault();
    });
	
    $(refreshBtn).click(function () {
        getData();
		return false
    });
	

	function getData() {
		if (finished) {
			//serialize AJAX request
			requests.push(
				jqxhr = $.ajax({
					type: 'GET',
					url: 'reports/reportauditlog/getdata',
					dataType: 'json',
					beforeSend: function() {
						finished = false;
						abortAJAXRequests();
						//waitMessage('Retrieving report job list...');
					},
					error: function(jqXHR, textStatus, errorThrown) {

					},
					success: function(data) {
						if (data.success) {
							//populate table
							$.fn.dataTableExt.iApiIndex = 0;
							oTable.fnClearTable(0);
							oTable.fnAddData(data.details);
							oTable.fnDraw();
							//oTable.fnAdjustColumnSizing();
						}
					},
					complete: function() {
						finished = true;
						$(MSGBOX).dialog('close');

						if ($(autoRefresh).attr('checked')) {
							$(refreshBtn).removeAttr('disabled');
						} else {
							$(refreshBtn).attr('disabled',true);
						}
					}
				})
			);
		}
	}
});
</script>