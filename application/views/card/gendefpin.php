<form id="generateDefPINform">
<div id="genDefPIN" style="width:600px;">
	<h1>Generate Default PIN</h1>
    <table class="dataTable">
        <thead>
            <tr>
                <th>&nbsp;</th>
                <th>Card No.</th>
                <th>Card Type</th>
                <th>Remarks</th>
                <th>msgtype</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
</div>
<div id="bottom">
	<span class="buttons floatLeft">
    	<button id="generateBtn" value="card/generation/generate">Process</button
        >
	</span>
    <div id="checkBoxControl" style="margin: 6px 0 0 15px; float: left">
        <div class="controls">
            <a title="Checks all rows above" href="#" id="checkAll">Check All</a> |
            <a title="Unchecks  all rows above" href="#" id="unCheckAll">Uncheck All</a> |
            <a title="Toggle the checkboxes above" href="#" id="toggleCheck">Toggle Check</a><!-- |
            <a title="Inverts the current selected checkboxes" href="#" id="invert">Invert Selection</a>-->
        </div>
    </div>
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
        		Card BIN:
                <select id="cardBIN" style="width:100px">
					<?php echo html_entity_decode($cardBIN); ?>
                </select>
			</span>
		</div>
    </div>
</div>
</form>
<style>
.dataTables_scrollBody {
	min-height: 250px !important;
	max-height: 250px !important;
}
</style>
<script>
$(function() {
	var cardBIN = '#cardBIN';
	var generateBtn = '#generateBtn';
	var	refreshBtn = '#refreshBtn';
	var cards = [];
	var limit = 100;
	var form = $('#generateDefPINform');
	
	$(DATATABLE).find('tbody tr').die('dblclick');
	
	oTable = $(DATATABLE).dataTable({
		bRetrieve: true,
        bJQueryUI: true,
		aLengthMenu: DTLENGTHMENU,
		aoColumns: [
			{ bSortable: false }, //checkbox
			null, //card no
			null, //card type
			null, //remarks
			{ bVisible: false }
		],
		aaSorting: [[1,'desc']],
		sScrollY: '100%',
		sScrollX: '100%', // Required for viewing tables with lots of columns at low resolution - otherwise columns are mis-aligned
		sPaginationType: 'full_numbers',
		fnInitComplete: function () {
			getData();
		},
		fnRowCallback: function(nRow, aData, iDisplayIndex) {
			$('td:eq(0)', nRow).attr('align', 'center');
			
			$(nRow).unbind('click dblclick').click(function () {
				//close dialog boxes
				if ($(MSGBOX).length > 0) {
					$(MSGBOX).dialog('close');
				}
            });
			
			$('input:checkbox', nRow).unbind('click').click(function (e) {
				if ($(this).is(':checked')) {
					if (cards.length < limit) {
						cards.push([this.id, this.value, $(this).attr('xml')]);
					} else {
						messageBox('Only ' + limit + ' cards allowed per batch');
						return false;
					}
					
				} else {
					//console.log(getArrayIndex(cards, this.id));
					cards.splice(getArrayIndex(cards, this.id), 1);
				}
				//console.log(e.currentTarget.checked);
				console.log(cards);
			});
			
			var msgType = parseInt(aData[4]);
			var rowColor = getRowColorx(msgType);
			$(nRow).addClass(rowColor);

			//$(generateBtn).attr('disabled', true);
            return nRow;
		}
	});
	
	function getRowColorx(msgType) {
		var color = null;
		//greater than -1 means true.. check http://api.jquery.com/jQuery.inArray/
		if (msgType === 23) {
			color = 'redRow';
		}
		
		return color;
	}
	
	function getArrayIndex(arr, elem)
	{
		for (var i = 0; i <= arr.length; i++) {
			
			if (arr[i].indexOf(elem) !== -1) {
				return i;
			}
		}
	}
	
	$('.ui-toolbar:even').append($('#customToolbar .top').html());
	
	function getData()
	{
		requests.push(
			$.ajax({
				url: 'card/gendefpin/getdata',
				type: 'GET',
				dataType: 'json',
				data: {
					cardBIN: $(cardBIN).val()
				},
				beforeSend: function () {
					cards = [];
					abortAJAXRequests();
					waitMessage('Retrieving card list...');
				},
				error: function () {
		
				},
				success: function (data) {
					if (data.success) {
						$.fn.dataTableExt.iApiIndex = 0;
						oTable.fnClearTable(0);
						oTable.fnAddData(data.details);
						oTable.fnDraw();
						//oTable.fnAdjustColumnSizing();
					}
				},
				complete: function () {
					$(MSGBOX).dialog('close');
				}
			})
		);
	}
	
	$(cardBIN).change(function () {
		getData();
	});
	
	$(refreshBtn).click(function (e) {
		
		$(generateBtn + ', input:checkbox').attr('disabled', false);
		$(DIALOG).dialog('close');
		
		getData();
		e.preventDefault();
	});
	
	form.submit(function (e) {
		
		$(MSGBOX).dialog('close');
		
		var selected = $(cardBIN + ' option:selected');
		//serialize AJAX request
		
		$(generateBtn + ', input:checkbox').attr('disabled', true);
		
		//cache
		requests.push(
			$.ajax({
				type: 'POST',
				url: 'card/defaultpin/cache',
				data: {
					minPIN: selected.attr('minpin'),
					maxPIN: selected.attr('maxpin')
				},
				dataType: 'json',
				success: function (data) {
					if (data.success === true) {
						
						var buttons = {
							
							//submit function
							Submit: function () {
								var form = $('#genDefPINForm');
								
								//validate
								if (form.validationEngine('validate') === true) {
									//confirm
									messageBoxV2('Proceed to default PIN generation?', 'Confirm', {
										OK: function () {
											//generate
											requests.push(
												$.ajax({
													url: form.attr('action'),
													type: 'POST',
													dataType: 'json',
													data: {
														batchNo: $('#batchNo').val(),
														defaultPIN: $('#defaultPIN').val(),
														cards: cards
													},
													beforeSend: function () {
														abortAJAXRequests();
														$(DIALOG).dialog('close');
														$('#generateBtn, input:checkbox').attr('disabled', true);
														waitMessage('Generating...');
													},
													error: function () {
											
													},
													success: function (data) {
														getData();
													},
													complete: function () {
														$(MSGBOX).dialog('close');
													}
												})
											);
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
	
						modalDialog('card/defaultpin', 'GENERATE DEFAULT PIN', buttons, 250);
	
						$(DIALOG).bind('dialogbeforeclose, dialogfocus', function () {
							if ($(MSGBOX).length > 0) {
								$(MSGBOX).dialog('close');
							}
						});
						$(DIALOG).bind('dialogbeforeclose', function () {
							$(generateBtn + ', input:not([readonly])').attr('disabled', false);
						});
					} else {
						messageBox('An error has occured');
					}
				}
			})
		);
		e.preventDefault();
	});
	
	$(generateBtn).click(function (e) {
		if (cards.length > 0) {
			if (cards.length <= limit) {
				$(MSGBOX).dialog('close');
                showUserOverride();
			} else {
				messageBox('Only ' + limit + ' cards allowed per batch');
			}
		} else {
			messageBox('Please select an item from the list');
		}
		
		e.preventDefault();
	});
	
	
	$('#checkAll').click(function(e) {
		var chk = $(DATATABLE).find(':checkbox:not(:disabled)');
		
		chk.each(function() {
			if (cards.length < limit) {
				if ($(this).is(':checked') === false) {
					$(this).attr('checked', true);
					cards.push([this.id, this.value, $(this).attr('xml')]);
				}
			} else {
				messageBox('Only ' + limit + ' cards allowed per batch');
				return false;
			}
		});
		//console.log(cards);
		e.preventDefault();
	});
	
	$('#unCheckAll').click(function(e) {
		var chk = $(DATATABLE).find(':checkbox:checked:not(:disabled)');
		chk.removeAttr('checked');
		
		chk.each(function() {
			cards.splice(getArrayIndex(cards, this.id), 1);
			//console.log(cards);
		});
		
		e.preventDefault();
	});
	
	$('#toggleCheck').click(function(e) {
		var chk = $(DATATABLE).find(':checkbox:not(:disabled)');
		chk.attr('checked', !chk.attr('checked'));
		e.preventDefault();
	});
	
	$('#invert').click(function(e) {
		var chk = $(DATATABLE).find(':checkbox:not(:disabled)');
		chk.each(function() {
			$(this).attr('checked', !$(this).attr('checked'));
		});
		e.preventDefault();
	});
});
</script>