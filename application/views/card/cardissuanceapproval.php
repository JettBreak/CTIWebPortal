<form id="issuanceapprovalForm" method="post">
<div style="width:700px">
	<h1>Card Issuance Approval</h1>
    <table class="dataTable">
        <thead>
            <tr>
                <th width="10">&nbsp;</th>
                <th width="100">Card No.</th>
                <th width="100">Card Type</th>
                <th>Customer</th>
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
		card = {},
		form 	  = $('form');
	
	oTable = $(DATATABLE).dataTable({
		bRetrieve: true,
        bJQueryUI: true,
		aLengthMenu: DTLENGTHMENU,
		aoColumns: ([ //hide columns
			{ 
				sClass: 'centerAlign',
				bSortable: false
			},//checkboxes
			null, //card no.
			//hide columns
			//{ 
			//	sClass: 'centerAlign',
			//	bSortable: false
			//},//checkboxes
			null, //card type
			null, //customer
			{ bVisible: false } //tokenid
		]),
        aaSorting: [],
        sScrollY: '100%',
        sScrollX: '100%',
        sPaginationType: 'full_numbers',
        fnInitComplete: function () {
			getData();
        },
		fnRowCallback: function (nRow, aData, iDisplayIndex) {
            $(nRow).attr('id', aData[4]).unbind('click dblclick').click(function () {
				//close dialog boxes
				if ($(MSGBOX).length > 0) {
					$(MSGBOX).dialog('close');
				}
				
				//highlight rows
				$('tbody tr').removeClass('rowSelected');
                $(this).addClass('rowSelected');
				
               	//$(verifyBtn).removeAttr('disabled')
				if ($(DATATABLE).find('input:checked').length > 0) {
					$(verifyBtn).removeAttr('disabled');
				} else {
					$(verifyBtn).attr('disabled', true);
				}


            }).dblclick(function () {

				//set temp var
				card = {
					number: aData[1],
					readonly: true,
					module: 'cardissuanceapproval',
					tokenid: aData[4]
				};

                if (card) {
			//serialize AJAX request
					requests.push(
						$.ajax({
							type: 'POST',
							url: 'card/browse/cache',
							data: card,
							dataType: 'json',
							success: function(data) {
								if (data.success === true) {
									window.location.hash = 'card/info';
								}
							}
						})
					);
		        }
            });
			//$('tbody tr').removeClass('rowSelected');
            $(verifyBtn).attr('disabled', true);
            return nRow;
        }
	});
	
	<?php if (!empty($showToolbarFilters)): ?>
	.ui-toolbar:even.append(#customToolbar .top.html());
	<?php endif; ?>
	
	$(verifyBtn).click(function (e) {
		messageBox('Approve selected cards?', 'Confirm', 'confirm', function () {
			//serialize AJAX request
			requests.push(
				jqxhr = $.ajax({
					type: 'POST',
					url: 'card/cardissuanceapproval/verify',
					dataType: 'json',
					data: form.serialize(),
					beforeSend: function() {
						waitMessage('Verifying selected card/s...');
					},
					error: function(jqXHR, textStatus, errorThrown) {
	
					},
					success: function(data) {

						if (data.success === true) {
					
							//delete rows
							$.each(data.cards, function(index, value) {
								oTable.fnDeleteRow(oTable.fnGetPosition($('#' + value)[0]));
							});

						}
						messageBox(data.message);
						$(MSGBOX).one('dialogbeforeclose', function () {
                            $(refreshBtn).trigger('click');
                		});

					},
					complete: function(data) {
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
				url: 'card/cardissuanceapproval/getdata',
				dataType: 'json',
				data: {
					brseqno: $('#branchList').val()
				},
				beforeSend: function() {
					abortAJAXRequests();
					waitMessage('Retrieving Card List...');
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