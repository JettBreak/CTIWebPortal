<form id="customerApprovalForm" method="post">
<div style="width:550px">
	<h1>Customer Approval</h1>
    <table class="dataTable">
        <thead>
            <tr>
                <th width="10">&nbsp;</th>
                <th width="80">CIF No.</th>
                <th>Customer</th>
            </tr>
        </thead>
        <tbody></tbody>
        <tfoot><tr>
                    <td colspan="3">
                        &nbsp;
                        <a title="Checks all the checkboxes above" href="#" id="checkall">Check All</a> | 
                        <a title="Unchecks all the checkboxes above" href="#" id="uncheckall">Uncheck All</a>
                    </td>
                </tr></tfoot>
    </table>
    <!--<div id="content" class="hidden">
    	<table width="100%">
        	<tr>
            	<td>Customer Name:</td>
            </tr>
            <tr>
            	<td>Primary Identifier:</td>
            </tr>
            <tr>
            	<td>Birthday:</td>
            </tr>
            <tr>
            	<td>Home Address:</td>
            </tr>
            <tr>
            	<td>Home Phone:</td>
            </tr>
            <tr>
            	<td>Business Address:</td>
            </tr>
            <tr>
            	<td>Business Phone:</td>
            </tr>
            <tr>
            	<td>E-mail:</td>
            </tr>
        </table>
    </div>-->
</div>
<div id="bottom">
	<span class="buttons floatLeft">
    	<button id="verifyBtn" disabled>Approve</button>
    	<button id="rejectBtn" disabled>Reject</button>
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
<style>
.dataTables_scrollBody{min-height:150px !important;max-height:300px !important;}
</style>

<script>
$(function() {
	var verifyBtn = '#verifyBtn',
		rejectBtn = '#rejectBtn',
		refreshBtn = '#refreshBtn',
		form 	  = $('form');
	
    initSession('<?php echo $sessionExp; ?>');
	oTable = $(DATATABLE).dataTable({
		bRetrieve: true,
        bJQueryUI: true,
		aLengthMenu: DTLENGTHMENU,
		aoColumns: ([ //hide columns
			{ 
				sClass: 'centerAlign',
				bSortable: false
			},//checkboxes
			null, //cif no.
			null, //customer
		]),
        aaSorting: [],
        sScrollY: '100%',
        sScrollX: '100%',
        sPaginationType: 'full_numbers',
        fnInitComplete: function () {
			getData();
        },
		fnRowCallback: function (nRow, aData, iDisplayIndex) {
			//$(nRow).attr('id', aData[2])
			//	.dblclick(function () {
					//hack
			//		searchRedirect(aData[1]);
			//	});
            $(nRow).attr('id', aData[2]).unbind('click dblclick').click(function () {
				//close dialog boxes
				if ($(MSGBOX).length > 0) {
					$(MSGBOX).dialog('close');
				}
				
				//highlight rows
				$('tbody tr').removeClass('rowSelected');
                $(this).addClass('rowSelected');
				
               	$(verifyBtn).removeAttr('disabled')
				if ($(DATATABLE).find('input:checked').length > 0) {
					$(verifyBtn).removeAttr('disabled');
					$(rejectBtn).removeAttr('disabled');
				} else {
					$(verifyBtn).attr('disabled', true);
					$(rejectBtn).attr('disabled', true);
				}
            }).dblclick(function () {
					//hack
					searchRedirect(aData[1]);
			});
			//$('tbody tr').removeClass('rowSelected');
            //$(verifyBtn).attr('disabled', true);
            return nRow;
        }
	});
	
	<?php if (!empty($showToolbarFilters)): ?>
	.ui-toolbar:even.append(#customToolbar .top.html());
	<?php endif; ?>
	
	$(verifyBtn).click(function (e) {
		messageBox('Approve selected customers?', 'Confirm', 'confirm', function () {
			//serialize AJAX request
			requests.push(
				jqxhr = $.ajax({
					type: 'POST',
					url: 'customer/verification/verify',
					dataType: 'json',
					data: form.serialize(),
					beforeSend: function() {
						waitMessage('Verifying selected customer/s...');
					},
					error: function(jqXHR, textStatus, errorThrown) {
	
					},
					success: function(data) {
						if (data.success === true) {
					
							//delete rows
							/*$.each(data.customers, function(index, value) {
								oTable.fnDeleteRow(oTable.fnGetPosition($('#' + value)[0]));
							});*/

						}
						messageBox(data.message);
						$(MSGBOX).one('dialogbeforeclose', function () {
                            $(refreshBtn).trigger('click');
                		});
					},
					complete: function() {
	
					}
				})
			);
		});
		e.preventDefault();
	});

	
    $('#checkall').click( function() {
        $('input', oTable.fnGetNodes()).each( function() {
            $('input', oTable.fnGetNodes()).attr('checked','checked');
        });
    });


    $('#uncheckall').click( function() {
        $('input', oTable.fnGetNodes()).each( function() {
            $('input', oTable.fnGetNodes()).removeAttr('checked','checked');
        });
    });
	
	$(rejectBtn).click(function (e) {
		messageBox('Reject selected customers?', 'Confirm', 'confirm', function () {
			//serialize AJAX request
			requests.push(
				jqxhr = $.ajax({
					type: 'POST',
					url: 'customer/verification/reject',
					dataType: 'json',
					data: form.serialize(),
					beforeSend: function() {
						waitMessage('Removing selected customer/s...');
					},
					error: function(jqXHR, textStatus, errorThrown) {
	
					},
					success: function(data) {
						if (data.success === true) {
					
							//delete rows
							/*$.each(data.customers, function(index, value) {
								oTable.fnDeleteRow(oTable.fnGetPosition($('#' + value)[0]));
							});*/

						}
						messageBox(data.message);
						$(MSGBOX).one('dialogbeforeclose', function () {
                            $(refreshBtn).trigger('click');
                		});
					},
					complete: function() {
	
					}
				})
			);
		});
		e.preventDefault();
	});

	$(refreshBtn).click(function (e) {
		getData();
		e.preventDefault();
	});
	
	$('#branchList').change(getData);
	
	function getData() {
		//serialize AJAX request
		requests.push(
			jqxhr = $.ajax({
				type: 'GET',
				url: 'customer/verification/getdata',
				dataType: 'json',
				data: {
					brseqno: $('#branchList').val()
				},
				beforeSend: function() {
					abortAJAXRequests();
					waitMessage('Retrieving Customer List...');
				},
				error: function(jqXHR, textStatus, errorThrown) {

				},
				success: function(data) {
					if (data.success) {
						$.fn.dataTableExt.iApiIndex = 0;
						oTable.fnClearTable(0);
						oTable.fnAddData(data.details);
						oTable.fnDraw();
						//oTable.fnAdjustColumnSizing();
					}
				},
				complete: function() {
					if ($(MSGBOX).length > 0) {
						$(MSGBOX).dialog('close');
					}
				}
			})
		);
	}

});
function searchRedirect(custkey) {
    $.ajax({
        type: 'POST',
        url: 'customer/verification/cache',
        data: {
            'cifseqno': custkey,
            'custappr': true
        },
        dataType: 'json',
        success: function (data) {
            if (data.success === true) {
                window.location.hash = 'customer/info/'+custkey;
            } else {
                messageBox('An error has occured');
            }
        }
    })
}
</script>