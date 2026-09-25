<form id="accountRequestForm" method="post">
<div style="width:700px">
	<h1>Account Verification</h1>
    <table class="dataTable">
        <thead>
            <tr>
                <th width="10">&nbsp;</th>
                <th width="100">Account No.</th>
                <th width="100">Account Type</th>
                <th>prseqno</th>
            </tr>
        </thead>
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
    	<button id="verifyBtn" disabled>Verify</button>
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
			null, //account no.
			//hide columns
			//{ 
			//	sClass: 'centerAlign',
			//	bSortable: false
			//},//checkboxes
			null, //account type
			{ bVisible: false } //prkey
		]),
        aaSorting: [],
        sScrollY: '100%',
        sScrollX: '100%',
        sPaginationType: 'full_numbers',
        fnInitComplete: function () {
			getData();
        },
		fnRowCallback: function (nRow, aData, iDisplayIndex) {
            $(nRow).attr('id', aData[3]).unbind('click dblclick').click(function () {
				//close dialog boxes
				if ($(MSGBOX).length > 0) {
					$(MSGBOX).dialog('close');
				}
				
				//highlight rows
				//$('tbody tr').removeClass('rowSelected');
                //$(this).addClass('rowSelected');
				
               	//$(verifyBtn).removeAttr('disabled')
				if ($(DATATABLE).find('input:checked').length > 0) {
					$(verifyBtn).removeAttr('disabled');
				} else {
					$(verifyBtn).attr('disabled', true);
				}
            });
			//$('tbody tr').removeClass('rowSelected');
            $(verifyBtn).attr('disabled', true);
            return nRow;
        }
	});
	
	<?php if (!empty($showToolbarFilters)): ?>
	$('.ui-toolbar:even').append($('#customToolbar .top').html());
	<?php endif; ?>
	
	$(verifyBtn).click(function (e) {
		messageBox('Verify selected accounts?', 'Confirm', 'confirm', function () {
			//serialize AJAX request
			requests.push(
				jqxhr = $.ajax({
					type: 'POST',
					url: 'accounts/accountresultlist/verify',
					dataType: 'json',
					data: form.serialize(),
					beforeSend: function() {
						waitMessage('Verifying selected account/s...');
					},
					error: function(jqXHR, textStatus, errorThrown) {
	
					},
					success: function(data) {
						if (data.success === true) {
							messageBox(data.message);
							$(MSGBOX).one('dialogafterclose', function () {
			                    window.location.hash = 'accounts/accountrequest';
			                });
							//delete rows
							//$.each(data.accounts, function(index, value) {
							//	oTable.fnDeleteRow(oTable.fnGetPosition($('#' + value)[0]));
							//});
						} else {
							messageBox(data.message);
							$(MSGBOX).one('dialogafterclose', function () {
			                    window.location.hash = 'accounts/accountrequest';
			                });
						}
					},
					complete: function() {
						//window.location.hash = 'customer/search/delete';
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
				url: 'accounts/accountresultlist/getdata',
				dataType: 'json',
				data: {
					brseqno: $('#branchList').val()
				},
				beforeSend: function() {
					abortAJAXRequests();
					waitMessage('Retrieving Accounts List...');
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
</script>
