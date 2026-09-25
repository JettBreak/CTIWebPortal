<div style="width:1024px">
    <h1>Default Online Limits</h1>
    <table class="dataTable">
        <thead>
            <th>Transaction</th>
            <th>limitseqno</th>
            <th>trxcode</th>
            <th>Default Cycle Amt.</th>
            <th>Max Cycle Amt.</th>
            <th>Default Counter</th>
            <th>Max Counter</th>
            <th>Default Min Tran Amt.</th>
            <th>Min Tran Amt.</th>
            <th>Default Max Tran Amt.</th>
            <th>Max Tran Amt.</th>
            <th>Cycle Period</th>
            <th>Time Limit</th>
            <th>No Fee Ctr.</th>
            <th>Max No Fee Ctr.</th>
            <th>No Fee Amt. Limit</th>
            <th>Max No Fee Amt. Limit</th>
            <th>No Fee Cycle Period</th>
        </thead>
    </table>
</div>
<div id="bottom">
	<span class="buttons floatLeft">
        <button id="newBtn">Add</button
        ><button id="modifyBtn" disabled>Modify</button
        ><button id="removeBtn" disabled>Remove</button
        >
        <span style="margin-left:20px;"></span>
        <button id="newLimitBtn">New Limits</button
        ><button id="deleteLimitBtn">Delete Limit</button
        ><button id="updateAllLimitsBtn" disabled>Update All Limits</button
        ><!--<button id="resetAllCycleBal">Reset All Cycle Balance</button>-->
    </span>
    <span class="buttons floatRight">
        <button id="refreshBtn">Refresh</button
        ><button class="closebtn">Close</button>
    </span>
</div>
<div id="customToolbar" class="hidden">
    <div class="top">
        <!--<div class="dataTables_custom floatLeft"><span class="info"></span></div>-->
        <div class="dataTables_custom floatLeft" style="padding:0 !important">
        	<span class="info">
        		<label for="type">Type:</label>
                <select id="type" style="width:150px">
                    <?php echo html_entity_decode($accountTypes); ?>
                </select>
			</span>
            <span class="info">
            	<label for="limitseqno">Limit Name:</label>
                <select name="limitseqno" id="limitseqno" style="width:150px">
                	<?php echo html_entity_decode($limitNames); ?>
                </select>
            </span>
		</div>
    </div>
    <div class="bottom">
        <div class="dataTables_custom floatLeft" style="padding:3px !important">
        	<span class="info">
            	<span class="label">PIN Retry Count Default:</span> <span id="pinctrdef"><?php echo $pinctrdef; ?></span>
			</span>
        	<span class="info">
            	<span class="label">PIN Maximum Retry Count:</span> <span id="pinctrmax"><?php echo $pinctrmax; ?></span>
			</span>
		</div>
    </div>
</div>
<style>
.dataTables_scrollBody {
	min-height: 150px !important;
	max-height: 400px !important;
}
</style>
<script>
$(function() {
	var limitseqno = '#limitseqno';
	var modifyBtn = '#modifyBtn';
	var removeBtn = '#removeBtn';
	var accttype = '#type';
	
	var limits = {};
	
	var buttons = modifyBtn + ',' + removeBtn;
	
    initSession('<?php echo $sessionExp; ?>');
	oTable = $(DATATABLE).dataTable({
        bRetrieve: true,
        bJQueryUI: true,
		aLengthMenu: DTLENGTHMENU,
        aaSorting: [],
		aoColumns: [
			null, //Transaction
			{ bVisible: false }, //limitseqno
			{ bVisible: false }, //trxcode
			{
				sClass: 'rightAlign'//, //Default Cycle Amt.
				//sType: 'formatted-num'
			}, 
			{ sClass: 'rightAlign' },	//Maximum Cycle Amt.
			{ sClass: 'centerAlign'},	//Default Counter
			{ sClass: 'centerAlign'},	//Max Counter
			{ sClass: 'rightAlign' },	//Default Min Tran Amt.
			{ sClass: 'rightAlign' },	//Min Tran Amt.
			{ sClass: 'rightAlign' },	//Default Max Tran Amt.
			{ sClass: 'rightAlign' },	//Max Tran Amt.
			{ sClass: 'centerAlign'},	//Cycle Period
			{ sClass: 'centerAlign'},	//Time Limit
			{ sClass: 'centerAlign'},	//No Fee Ctr.
			{ sClass: 'centerAlign'},	//Max. No Fee Ctr.
			{ sClass: 'rightAlign' },	//No Fee Amt. Limit
			{ sClass: 'rightAlign' },	//Max. No Fee Amt.
			{ sClass: 'centerAlign'} 	//No Fee Cycle Period
		],
        sScrollY: '100%',
        sScrollX: '100%',
        sPaginationType: 'full_numbers',
        fnInitComplete: function () {
			getData();
        },
        fnRowCallback: function (nRow, aData, iDisplayIndex) {
			$(nRow).attr('id', aData[2]).unbind('click dblclick').click(function () {
				if ($(MSGBOX).length > 0) {
					$(MSGBOX).dialog('close');
				}
                $('tbody tr').removeClass('rowSelected');
                $(this).addClass('rowSelected');
				
                //set temp var
				limits = {
					description: 	aData[0],
					limitseqno: 	aData[1],
					trxcode: 		aData[2],
					cycledef:		aData[3],	//Default Cycle Amt.
					cyclemax: 		aData[4],	//Maximum Cycle Amt.
					ctrdef: 		aData[5],	//Default Counter
					ctrmax: 		aData[6],	//Max Counter
					tranmindef: 	aData[7],	//Default Min Tran Amt.
					tranmin: 		aData[8],	//Min Tran Amt.
					tranmaxdef: 	aData[9],	//Default Max Tran Amt.
					tranmax: 		aData[10],	//Max Tran Amt.
					cycle: 			aData[11],	//Cycle Period
					duralimit: 		aData[12],	//Time Limit
					nonfeectr: 		aData[13],	//No Fee Ctr.
					nonfeectrdef: 	aData[14],	//Max. No Fee Ctr.
					nonfeetran:		aData[15],	//No Fee Amt. Limit
					nonfeetrandef:	aData[16],	//Max. No Fee Amt.
					nonfeecycle: 	aData[17]	//No Fee Cycle Period
				}
				
                $(buttons).removeAttr('disabled');
				
				$(DIALOG).dialog('close');
            }).dblclick(function () {
                $(modifyBtn).triggerHandler('click');
            });
            $(buttons).attr('disabled', true);
			
			limits = {};
            return nRow;
        }
    });
	
	$('#bottom button').click(function () {
		$(DIALOG).dialog('close');
	});
	
	$('.ui-toolbar:even').append($('#customToolbar .top').html());
	$('.ui-toolbar:odd').append($('#customToolbar .bottom').html());
	
	$(accttype).change(function () {
		//serialize AJAX request
		requests.push(
			jqxhr = $.ajax({
				type: 'GET',
				url: 'maintenance/limitsdefonln/getlimitnames',
				data: {
					acctType: this.value
				},
				dataType: 'json',
				beforeSend: function() {
					abortAJAXRequests();
					waitMessage('Retrieving data...');
				},
				error: function(jqXHR, textStatus, errorThrown) {

				},
				success: function(data) {
					if (data.success) {
						$(limitseqno)
							.html(data.options)
							.triggerHandler('change')
						
						var selected = $(limitseqno).find('option:selected');
						var pinctrdef = selected.attr('pinctrdef');
						var pinctrmax = selected.attr('pinctrmax');
						
						$('#pinctrdef').text(pinctrdef);
						$('#pinctrmax').text(pinctrmax);
						//oTable.fnAdjustColumnSizing();
					}
					$(MSGBOX).dialog('close');
				}
			})
		);
	}).click(function () {
		$(DIALOG).dialog('close');
	});
	
	$(limitseqno).change(function () {
		getData();
		var selected = $(this).find('option:selected');
		
		var pinctrdef = selected.attr('pinctrdef');
		var pinctrmax = selected.attr('pinctrmax');
		
		$('#pinctrdef').text(pinctrdef);
		$('#pinctrmax').text(pinctrmax);
	}).click(function () {
		$(DIALOG).dialog('close');
	});
	
	$('#refreshBtn').click(getData);
	
	function getData() {
		//serialize AJAX request
		requests.push(
			jqxhr = $.ajax({
				type: 'GET',
				url: 'maintenance/limitsdefonln/getdata',
				data: {
					limitseqno: $(limitseqno).val()
				},
				dataType: 'json',
				beforeSend: function() {
					abortAJAXRequests();
					waitMessage('Retrieving data...');
				},
				error: function(jqXHR, textStatus, errorThrown) {

				},
				success: function(data) {
					
					$.fn.dataTableExt.iApiIndex = 0;
					oTable.fnClearTable(0);
						
					if (data.success) {
						oTable.fnAddData(data.details);
						//oTable.fnAdjustColumnSizing();
						$('#updateAllLimitsBtn').attr('disabled', false);
						$('#newBtn').attr('disabled', false);
					} else {
						$('#updateAllLimitsBtn').attr('disabled', true);
						if (data.limitno > 0) {
							$('#newBtn').attr('disabled', false);
						} else {
							$('#newBtn').attr('disabled', true);
						}

						$(buttons).attr('disabled', true);
					}
					
					oTable.fnDraw();
					
					$(MSGBOX).dialog('close');
				}
			})
		);
	}
	
	$('#newBtn').click(function () {
		var buttons = {
			Save: function () {
				
				var form = $('#onlineLimitsDefForm');
				
				if (form.validationEngine('validate') === true) {
				
					messageBoxV2('Are all entries correct?', 'Confirm', {
						OK: function () {
							form.submit();
						},
						Cancel: function () {
							$(this).dialog('close');
							//return false;
						}
					});
					//return false;
				
				}
			},
			Cancel: function () {
				$(this).dialog('close');
				$(MSGBOX).dialog('close');
				//return false;
			}
		}
								
		modalDialog('maintenance/limitsdefonlnnew?limitseqno=' + $(limitseqno).val(), 'NEW TRANSACTION LIMITS', buttons, 450);
	});
	
	$(modifyBtn).click(function (e) {
		if (limits) {
			//serialize AJAX request
			requests.push(
				$.ajax({
					type: 'POST',
					url: 'maintenance/limitsdefonln/cache',
					data: limits,
					dataType: 'json',
					success: function (data) {
						if (data.success === true) {
							var buttons = {
								Save: function () {
									var form = $('#onlineLimitsDefForm');
									if (form.validationEngine('validate') === true) {
										messageBoxV2('Update transaction limits?', 'Confirm', {
											OK: function () {
												form.submit();
											},
											Cancel: function () {
												$(this).dialog('close');
												//return false;
											}
										});
									}
									//return false;
								},
								Cancel: function () {
									$(this).dialog('close');
									//return false;
								}
							}
							modalDialog('maintenance/limitsdefonlnedit', 'UPDATE TRANSACTION LIMITS', buttons, 450);
							$(DIALOG).one('dialogbeforeclose', function () {
								if ($(MSGBOX).length > 0) {
									$(MSGBOX).dialog('close');
								}
							});
						} else {
							messageBox('An error has occured');
						}
					}
				})
			);
		} else {
			messageBox('Please select an item from the list')
		}
		e.preventDefault();
	});
	
	$(removeBtn).click(function (e) {
		if (limits) {
			messageBox('Remove <strong>[' + limits.description + ']</strong><br/>transaction limits entry?', 'Confirm', 'confirm', function () {
				//serialize AJAX request
				requests.push(
					$.ajax({
						type: 'POST',
						url: 'maintenance/limitsdefonln/remove',
						data: {
							limitseqno: limits.limitseqno,
							trxcode: limits.trxcode
						},
						dataType: 'json',
						beforeSend: function() {
							waitMessage('Removing transaction...');
						},
						success: function(data) {
							if (data.success) {
								messageBox('<strong>['+ limits.description +']</strong><br />transaction limits removed successfully');
								oTable.fnDeleteRow(oTable.fnGetPosition($('#' + limits.trxcode)[0]));
							} else {
								messageBox(data.message);
							}
						}
					})
				);
            });
		} else {
			messageBox('Please select an item from the list');
		}
		e.preventDefault();
	});
	
	$('#newLimitBtn').click(function (e) {
		var buttons = {
			Save: function () {
				var form = $('#onlnLimitsDefFormxx');
				
				if (form.validationEngine('validate') === true) {		
					messageBoxV2('Add online limits?', 'Confirm', {
						OK: function () {
							form.submit();
						},
						Cancel: function () {
							$(this).dialog('close');
							$(MSGBOX).dialog('close');
							//return false;
						}
					});
				}
				//return false;
			},
			Cancel: function () {
				$(this).dialog('close');
				$(MSGBOX).dialog('close');
				//return false;
			}
		}
		
		var type = $(accttype).val();
		modalDialog('maintenance/limitsdefonlnadd?type=' + type, 'NEW LIMITS ENTRY', buttons, 450);
		e.preventDefault();
	});
	
	$('#deleteLimitBtn').click(function (e) {
		var limits = $(limitseqno);
		var selected = limits.find('option:selected');
		var desc = selected.text();
		
		messageBox('Delete <strong>[' + desc + ']</strong> limits?', 'Confirm', 'confirm', function () {
			//serialize AJAX request
			requests.push(
				$.ajax({
					type: 'POST',
					url: 'maintenance/limitsdefonln/delete',
					data: {
						limitseqno: limits.val(),
					},
					dataType: 'json',
					beforeSend: function() {
						waitMessage('Removing entry...');
					},
					success: function(data) {
						if (data.success) {
							messageBox('<strong>['+ desc +']</strong> limits removed successfully');
							
							$(MSGBOX).one('dialogbeforeclose', function () {
								$(limitseqno)
									.find('option:selected')
									.remove()
									.find('option:first').attr('selected', true);
								
								if ($(limitseqno).find('option').length === 0) {
									$(limitseqno).html('<option value="">No Limits Defined</option>');
								}
								
								$(limitseqno).trigger('change');
							});
							
						} else {
							messageBox(data.message);
						}
					}
				})
			);
		});
		
		e.preventDefault();
	});
	
	$('#updateAllLimitsBtn').click(function (e) {
		var limit = $(limitseqno).val();
		
		var buttons = {
			Save: function () {
				if ($('#trxTable').find('input:checked').length > 0) {
					messageBoxV2('Warning: Updating limits will override all existing transaction limit values.<br />Some transactions might not work if required limit is removed.<br/>Continue update?', 'Confirm', {
						OK: function () {
							$('#updateAllLimitsForm').submit();
							waitMessage('Processing... <span id="pmsg"></span><br /><div id="pbar"></>');
							$('#pbar').progressbar({
								value: 0,
								complete: function () {
									setTimeout("messageBox('Update complete')", 500);
									
									$(MSGBOX).one('dialogbeforeclose', function () {
										$(MSGBOX + ',' + DIALOG).dialog('close');
									});
								}
							});
						},
						Cancel: function () {
							$(this).dialog('close');
							//return false;
						}
					});
				} else {
					messageBox('No transactions selected');
				}
				//return false;
			},
			Cancel: function () {
				$(this).dialog('close');
				//return false;
			}
		}
		
		var params = {
			limitseqno: limit,
			accttype: $(accttype).val()
		}
		params = $.param(params, true);
		modalDialog('maintenance/updatealllimits?' + params, 'UPDATE ALL LIMITS', buttons, 700);
		
		$(DIALOG).bind('dialogbeforeclose, dialogfocus', function () {
			if ($(MSGBOX).length > 0) {
				$(MSGBOX).dialog('close');
			}
		});
		
		e.preventDefault();
	});
});
</script>