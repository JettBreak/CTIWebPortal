<form id="cardGenerationForm" method="post" target="hidden">
<div id="cardGeneration">
	<h1>Card Generation</h1>
    <table class="dataTable">
        <thead>
            <tr>
            	<th>&nbsp;</th>
                <th>Order No.</th>
                <th>Branch</th>
                <th>Date/Time Requested</th>
                <th>Card Type</th>
                <th>Req. Qty.</th>
                <th>Generated</th>
                <th>Status</th>
                <th>Progress</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
</div>
<div id="bottom">
	<span class="buttons floatLeft">
    	<?php echo html_entity_decode($generationbtn); ?>
	</span>
    <span class="buttons floatRight">
    	<button id="refreshBtn">Refresh</button
        ><button class="closebtn">Close</button>
	</span>
</div>
</form>
<div id="customToolbar" class="hidden">
    <div class="top">
        <!--<div class="dataTables_custom floatLeft"><span class="info"></span></div>-->
        <div class="dataTables_custom floatLeft" style="padding:0 !important">
        	<span class="info">
        		Branch:
                <select id="branchList" style="width:150px">
                	<option value="-1">ALL</option>
					<?php echo html_entity_decode($branches); ?>
                </select>
			</span>
		</div>
    </div>
</div>
<iframe id="hidden" name="hidden" class="hidden"></iframe>
<style>
.info a:hover {
	color: #ccf !important;
}
.dataTables_scrollBody {
	height: 220px !important;
	max-height: 220px !important;
}
.ui-progressbar-value {
	background: #fff;
}
#progressBar {
	float: right;
	width: 200px;
	height: 10px;
	margin-top: 5px;
}
</style>

<script>
$(function() {
	var generateBtn = '#generateBtn',
		refreshBtn	= '#refreshBtn',
		reqQty = 0,
		form = $('form');
	
	//$(DATATABLE).find('tbody tr').die('dblclick');
	initSession('<?php echo $sessionExp; ?>');
	
	oTable = $(DATATABLE).dataTable({
		bRetrieve: true,
        bJQueryUI: true,
		aLengthMenu: DTLENGTHMENU,
		aoColumns: [
			{ bSortable: false }, //checkbox
			null, //order no.
			null, //branch
			null, //dt requested
			null, //card type
			null, //req qty
			{ bVisible: false }, //generated
			null, //status
			null  //progress
		],
		aaSorting: [[1,'desc']],
		sScrollY: '100%',
		sScrollX: '100%', // Required for viewing tables with lots of columns at low resolution - otherwise columns are mis-aligned
		sPaginationType: 'full_numbers',
		fnInitComplete: function () {
			getData();
		},
		fnRowCallback: function(nRow, aData, iDisplayIndex) {
			$('td:eq(0), td:eq(1), td:eq(4), td:eq(5), td:eq(6), td:eq(7)', nRow).attr('align', 'center');
			$(nRow).attr('id', aData[1]).unbind('click dblclick').click(function () {
				//close dialog boxes
				if ($(MSGBOX).length > 0) {
					$(MSGBOX).dialog('close');
				}
				//highlight rows
				//$('tbody tr').removeClass('rowSelected');
                //$(this).addClass('rowSelected');
				
               	//$(verifyBtn).removeAttr('disabled')
				
				if ($(DATATABLE).find('input:checked').length > 0) {					
					$(generateBtn).removeAttr('disabled');
				} else {
					$(generateBtn).attr('disabled', 'disabled');
				}

            });
			
			$(nRow).find('input:checkbox').unbind('click').click(function () {
				if ($(this).is('input:checked') === true) {
					lastQty = reqQty;
					reqQty = reqQty + parseInt(aData[4]);
					
					if (reqQty > 1000) {
						reqQty = lastQty;
						messageBox('Maximum limit reached. Must not be greater than 1000');
						return false;
					}
				} else {
					reqQty = reqQty - parseInt(aData[4]);
				}
				//console.log('R: '+ reqQty);
				//console.log('L: '+ reqQty);
			});
			
			//$('tbody tr').removeClass('rowSelected');
            $(generateBtn).attr('disabled', 'disabled');
            return nRow;
		}
	});
	
	<?php if (!empty($showToolbarFilters)): ?>
	.ui-toolbar:even.append(#customToolbar .top.html());
	<?php endif; ?>
	
	/*$(DATATABLE).find('input:checkbox').die().live('change', function() {
		if ($(this).is('input:checked') === true) {
			lastQty = reqQty;
			reqQty = reqQty + parseInt(aData[4]);
			
			if (reqQty > 1000) {
				reqQty = lastQty;
				messageBox('Maximum limit reached');
				$(this).removeAttr('checked');
			}
		} else {
			reqQty = reqQty - parseInt(aData[4]);
		}
		console.log('R: '+ reqQty);
		console.log('L: '+ reqQty);
	});*/
				
	/*$('.ui-toolbar:first').append('<span class="dataTables_custom floatLeft" style="margin: 3px 0 0 50px">'+
		'<span class="info">'+
			'<a href="#" id="checkAll">Check All</a> | '+
			'<a href="#" id="unCheckAll">Uncheck All</a> | '+
			'<a href="#" id="toggleCheck">Toggle Check</a>'+
	'</span>');*/
	
	//$('.ui-toolbar:last').append('<span class="dataTables_custom floatLeft"><span class="info"><span class="label">Progress:</span><div id="progressBar"></div></span></span>');
	
	$('#progressBar').progressbar({
		value: 3
	});
	
	/*form.validationEngine({
		ajaxFormValidation: true,
		onBeforeAjaxFormValidation: function() {
			waitMessage('Card generation in progess...');
		},
		onAjaxFormComplete: function(form, status, data, options) {
			if (data.success === true) {
				
				//delete rows
				$.each(data.orderNos, function(index, value) {
					oTable.fnDeleteRow(oTable.fnGetPosition($('#' + value)[0]));
				});
				
				messageBox(data.message);
			}
		},
		scroll: false
	});
	form.validationEngine('attach');*/	
	
	/*$('#checkAll').click(function(e) {
		chk = $(DATATABLE).find(':checkbox:not(:disabled)');
		chk.attr('checked', 'checked');
		e.preventDefault();
	});
	
	$('#unCheckAll').click(function(e) {
		chk = $(DATATABLE).find(':checkbox:not(:disabled)');
		chk.removeAttr('checked');
		e.preventDefault();
	});
	
	$('#toggleCheck').click(function(e) {
		chk = $(DATATABLE).find(':checkbox:not(:disabled)');
		chk.attr('checked', !chk.attr('checked'));
		e.preventDefault();
	});*/
	
	$(generateBtn).click(function (e) {
		if ($(DATATABLE).find('input:checked').length > 0) {
			messageBox('Proceed card generation?','Confirm','confirm',function(){
				form.submit();
				waitMessage('Card generation in progess...');
			});
		} else {
			messageBox('Please select an item from the list');
		}
		e.preventDefault();
	});
	
	/*$('input').click(function () {
		console.log($(DATATABLE).find('input:checked').length);
		alert($(this).is(':checked'));
		if ($(DATATABLE).find('input:checked').length > 0) {
			if ($(this).is('input:checked') === true) {
				lastQty = reqQty;
				reqQty = reqQty + parseInt(aData[4]);
				
				if (reqQty > 1000) {
					messageBox('Maximum limit reached');
					reqQty = lastQty;
					return false;
				}
			} else {
				reqQty = reqQty - parseInt(aData[4]);
			}
			
			console.log(reqQty);
			$(generateBtn).removeAttr('disabled');
		} else {
			reqQty = 0;
			console.log(reqQty);
			$(generateBtn).attr('disabled', 'disabled');
		}
	});*/
	
	$(refreshBtn).click(function (e) {
		getData();
		e.preventDefault();
	});
	
	$('#branchList').change(getData);
});
function getData()
{
	requests.push(
		$.ajax({
			url: 'card/generation/getdata',
			type: 'GET',
			dataType: 'json',
			data: {
				brseqno: $('#branchList').val()
			},
			beforeSend: function () {
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
	)
}

function updateProgress(orderNo, progress)
{
	$(orderNo).find('td:eq(6)').text('PROCESSING'); 
	$(orderNo).find('td:eq(7)').text(progress + '%');
}

function showGenResult(data)
{
	if (data.success === true) {
		//delete rows
		$.each(data.orderNos, function(index, value) {
			oTable.fnDeleteRow(oTable.fnGetPosition($('#' + value)[0]));
		});
	}
	messageBox(data.message);
}

/*function logMe(data)
{
	console.log(data);
}*/
</script>