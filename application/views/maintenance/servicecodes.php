<div style="width:620px">
    <h1>Service Codes</h1>
    <table class="dataTable">
        <thead>
            <tr>
                <th width="100">Code</th>
                <th>Description</th>
                <th width="100">Mnemonic</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
</div>
<div id="bottom">
	<span class="buttons floatLeft">
        <button id="newBtn">New</button
        ><button id="modifyBtn" disabled>Modify</button
        ><button id="removeBtn" disabled>Remove</button
        >
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
        		<label for="chargeType">Charge Type:</label>
                <select id="chargeType" style="width:200px">
					<?php echo html_entity_decode($chargeTypes); ?>
                </select>
			</span>
		</div>
    </div>
</div>
<script>
$(function() {
	var chargeType = '#chargeType';
	var modifyBtn = '#modifyBtn';
	var removeBtn = '#removeBtn';
	var service = {};
	
	var buttons = modifyBtn + ',' + removeBtn;
	
    initSession('<?php echo $sessionExp; ?>');
    oTable = $(DATATABLE).dataTable({
        bRetrieve: true,
        bJQueryUI: true,
		aLengthMenu: DTLENGTHMENU,
        aaSorting: [],
		aoColumns: ([ //hide columns   
			{ sClass: 'centerAlign' },//code
			null, //description
			null  //mnemonic
		]),
        sScrollY: '100%',
        sScrollX: '100%',
        sPaginationType: 'full_numbers',
        fnInitComplete: function () {
			getData();
        },
        fnRowCallback: function (nRow, aData, iDisplayIndex) {
			$(nRow).attr('id', aData[0]).unbind('click dblclick').click(function () {
				if ($(MSGBOX).length > 0) {
					$(MSGBOX).dialog('close');
				}
                $('tbody tr').removeClass('rowSelected');
                $(this).addClass('rowSelected');
				
                service = {
					trxcode: aData[0],
					desc: aData[1],
					mnemonic: aData[2],
					type: $('#chargeType').val()
				};
				
                $(buttons).removeAttr('disabled')
            }).dblclick(function () {
                $(modifyBtn).triggerHandler('click');
            });
            $(buttons).attr('disabled', true);
			
			service = {};
            return nRow;
        }
    });
	
	$('.ui-toolbar:even').append($('#customToolbar .top').html());
	
	$('#newBtn').click(function (e) {
		window.location.hash = 'maintenance/svccodenew';
		e.preventDefault();
	});
	
	$(modifyBtn).click(function (e) {
		if (service) {
			//serialize AJAX request
			requests.push(
				$.ajax({
					type: 'POST',
					url: 'maintenance/servicecodes/cache',
					data: service,
					dataType: 'json',
					success: function(data) {
						if (data.success) {
							window.location.hash = 'maintenance/svccodeedit';
						}
					}
				})
			);
        } else {
            messageBox('Please select an item from the list');
        }
		e.preventDefault();	
	});
	
	$(removeBtn).click(function (e) {
		if (service) {
            messageBox('Delete <strong>[' + service.desc + ']</strong> service code?', 'Confirm', 'confirm', function () {
				//serialize AJAX request
				requests.push(
					$.ajax({
						type: 'POST',
						url: 'maintenance/servicecodes/delete',
						data: {
							trxcode: service.trxcode
						},
						dataType: 'json',
						beforeSend: function() {
							waitMessage('Removing entry...');
						},
						success: function(data) {
							if (data.success === true) {
								messageBox('<strong>['+ service.desc +']</strong><br />service code removed successfully');
								oTable.fnDeleteRow(oTable.fnGetPosition($('#' + service.trxcode)[0]));
							} else {
								messageBox(data.message);
							}
						}
					})
				);
            })
        } else {
            messageBox('Please select an item from the list')
        }
        e.preventDefault();
    });
	
	$(chargeType).change(getData);
	
	$('#refreshBtn').click(function (e) {
		getData();
		e.preventDefault();	
	});
	
	function getData() {		
		//serialize AJAX request
		requests.push(
			jqxhr = $.ajax({
				type: 'GET',
				url: 'maintenance/servicecodes/getdata',
				data: {
					chargeType: $(chargeType).val()
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
						$.fn.dataTableExt.iApiIndex = 0;
						oTable.fnClearTable(0);
						oTable.fnAddData(data.details);
						oTable.fnDraw();
						//oTable.fnAdjustColumnSizing();
					}
				},
				complete: function() {
					$(MSGBOX).dialog('close');
				}
			})
		);
	}
});
</script>