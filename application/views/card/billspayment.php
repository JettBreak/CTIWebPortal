<div id="billsPayment">
    <h1>Bills Payment</h1>
    <table class="dataTable">
        <thead>
            <tr>
            	<th>bpayseqno</th>
                <th width="100">Institution</th>
                <th>No.</th>
                <th>Subcriber No.</th>
                <th>Subcriber Name</th>
                <th>Status</th>
                <th>statcode</th>
                <th>blistseqno</th>
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
<script>
$(function() {
	var newBtn = '#newBtn';
	var modifyBtn = '#modifyBtn';
	var removeBtn = '#removeBtn';
	var refreshBtn = '#refreshBtn';
	var bill = {};
		
	var buttons = modifyBtn + ',' + removeBtn;
	
    oTable = $(DATATABLE).dataTable({
        bRetrieve: true,
        bJQueryUI: true,
		aLengthMenu: DTLENGTHMENU,
        aaSorting: [],
		aoColumns: [
			{ bVisible: false }, //bpayseqno
			null,
			{ sClass: 'centerAlign' },
			null,
			null,
			null,
			{ bVisible: false },
			{ bVisible: false }
		],
        sScrollY: '100%',
        sScrollX: '100%',
        sPaginationType: 'full_numbers',
        fnInitComplete: function () {
			//when DT InitComplete, load data
			getData();
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
				
				//set temp var
				bill = {
					seqno: aData[0],
					inst: aData[1],
					payptr: aData[2],
					subsNo: aData[3],
					subsName: aData[4],
					status: aData[6],
					blistseqno: aData[7]
				}
				
                $(buttons).removeAttr('disabled');
            }).dblclick(function () {
                $(modifyBtn).triggerHandler('click');
            });
            return nRow;
        },
		fnDrawCallback: function () {
			$('tbody tr').removeClass('rowSelected');
			$(buttons).attr('disabled', true);
			
			//unset temp obj
			bill = {};
		}
    });
	
	$(newBtn).click(function (e) {
		window.location.hash = 'card/billsadd';
		e.preventDefault();
	});
	
	$(modifyBtn).click(function (e) {
		if (bill) {
			//serialize AJAX request
			requests.push(
				$.ajax({
					type: 'POST',
					url: 'card/billspayment/cache',
					data: bill,
					dataType: 'json',
					success: function(data) {
						if (data.success === true) {
							window.location.hash = 'card/billsedit/';
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
		if (bill) {
            messageBox('Remove <strong>[' + bill.inst + ']</strong> bills payment?', 'Confirm', 'confirm', function () {
				//serialize AJAX request
				requests.push(
					$.ajax({
						type: 'POST',
						url: 'card/billspayment/delete',
						data: {
							blistseqno: bill.blistseqno
						},
						dataType: 'json',
						beforeSend: function() {
							waitMessage('Removing bills payment...')
						},
						success: function(data) {
							if (data.success === true) {
								messageBox('<strong>['+ bill.inst +']</strong> bills payment removed successfully');
								oTable.fnDeleteRow(oTable.fnGetPosition($('#' + bill.seqno)[0]));
							} else {
								messageBox(data.message);
							}
						}
					})
				);
            })
        } else {
            messageBox('Please select an item from the list');
        }
		e.preventDefault();
	});
	
	$(refreshBtn).click(function () {
        getData();
		return false
    });
	
	function getData() {
		//serialize AJAX request
		requests.push(
			jqxhr = $.ajax({
				type: 'GET',
				url: 'card/billspayment/getData',
				dataType: 'json',
				beforeSend: function() {
					abortAJAXRequests();
					waitMessage('Retrieving bills payment list...');
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
					$(MSGBOX).dialog('close');
				}
			})
		);
	}
});
</script>