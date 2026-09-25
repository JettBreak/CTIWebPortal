<div style="width:600px">
    <h1>Allows Setup</h1>
    <table class="dataTable">
        <thead>
            <tr>
                <th width="100">Bit No.</th>
                <th>Transaction</th>
                <th>trxcode</th>
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
        		<label for="prTypes">Product Types:</label>
                <select id="prTypes" style="width:150px">
					<?php echo html_entity_decode($prTypes); ?>
                </select>
			</span>
		</div>
    </div>
</div>
<script>
$(function() {
	var prTypes = '#prTypes';
	var modifyBtn = '#modifyBtn';
	var removeBtn = '#removeBtn';
	var allows = {};
	
    initSession('<?php echo $sessionExp; ?>');
	var buttons = modifyBtn + ',' + removeBtn;
	
    oTable = $(DATATABLE).dataTable({
        bRetrieve: true,
        bJQueryUI: true,
		aLengthMenu: DTLENGTHMENU,
        aaSorting: [],
		aoColumns: ([ //hide columns   
			{ sClass: 'centerAlign' },//bit no.
			null, //transaction
			{ bVisible: false }  //trxcode
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
				
                allows = {
					bitNo: aData[0],
					trx: aData[1],
					trxcode: aData[2],
					prType: $(prTypes).val()
				};
				
                $(buttons).removeAttr('disabled')
            }).dblclick(function () {
                $(modifyBtn).triggerHandler('click');
            });
            $(buttons).attr('disabled', true);
			
			allows = {};
            return nRow;
        }
    });
	
	$('.ui-toolbar:even').append($('#customToolbar .top').html());
	
	$('#newBtn').click(function (e) {
		window.location.hash = 'maintenance/allowsnew';
		e.preventDefault();
	});
	
	$(modifyBtn).click(function (e) {
		if (allows) {
			//serialize AJAX request
			requests.push(
				$.ajax({
					type: 'POST',
					url: 'maintenance/allowssetup/cache',
					data: allows,
					dataType: 'json',
					success: function(data) {
						if (data.success) {
							window.location.hash = 'maintenance/allowsedit';
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
		if (allows) {
            messageBox('Delete <strong>[' + allows.trx + ']</strong> transaction?', 'Confirm', 'confirm', function () {
				//serialize AJAX request
				requests.push(
					$.ajax({
						type: 'POST',
						url: 'maintenance/allowssetup/delete',
						data: allows,
						dataType: 'json',
						beforeSend: function() {
							waitMessage('Removing entry...')
						},
						success: function(data) {
							if (data.success === true) {
								messageBox('<strong>['+ allows.trx +']</strong><br />transaction removed successfully');
								oTable.fnDeleteRow(oTable.fnGetPosition($('#' + allows.bitNo)[0]));
							} else {
								messageBox(data.message);
							}
						}
					})
				);
            });
        } else {
            messageBox('Please select an item from the list')
        }
        e.preventDefault();
    });
	
	$(prTypes).change(getData);
	
	$('#refreshBtn').click(function (e) {
		getData();
		e.preventDefault();	
	});
	
	function getData() {		
		//serialize AJAX request
		requests.push(
			jqxhr = $.ajax({
				type: 'GET',
				url: 'maintenance/allowssetup/getdata',
				data: {
					prType: $(prTypes).val()
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