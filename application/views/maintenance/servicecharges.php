<div id="serviceCharges" class="constrained">
    <h1>Service Charges</h1>
    <table class="dataTable">
        <thead>
            <tr>
            	<th>feeseqno</th>
                <th>brseqno</th>
                <th>servtype</th>
                <th>feetype</th>
                <th>authname</th>
                <th>nettype</th>
                <th>chargetype</th>
                <th>trxcode2</th>
                <th width="100">Transaction</th>
                <th>Branch</th>
                <th>Service Fee</th>
                <th>Terminal Type</th>
                <th>Service Type</th>
                <th>Network Type</th>
                <th>Description</th>
                <th>Fee Type</th>
                <th>Charge Type</th>
                <th>Min Range</th>
                <th>Max Range</th>
                <th>Remarks</th>
                <th>cardtype</th>
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
        ><!--<button id="createBtn" disabled>Create Fees From Other Terminal</button
        ><button id="copyBtn" disabled>Copy Fees From Other Terminal</button
        >-->
    </span>
    <span class="buttons floatRight">
        <button id="refreshBtn">Refresh</button
        ><button class="closebtn">Close</button>
    </span>
</div>
<div id="customToolbar" class="hidden">
    <div class="top">
        <!--<div class="dataTables_custom floatLeft"><span class="info"></span></div>-->
        <div class="dataTables_custom floatLeft">
        	<span class="info">
        		<label for="terminals">Terminal:</label>
                <select id="terminals" style="width:200px"><?php echo html_entity_decode($termList); ?></select>
			</span>
        	<span class="info">
        		<label for="type">Type:</label>
                <select id="type" style="width:150px">
                    <?php echo html_entity_decode($accountTypes); ?>
                </select>
			</span>
		</div>
    </div>
</div>
<style>
.dataTables_scrollBody {
	min-height:300px !important;
	max-height:400px !important;
}
.dataTables_custom {
	padding: 0 !important;
}
</style>
<script>
$(function () {
    var newBtn = '#newBtn',
        modifyBtn = '#modifyBtn',
        removeBtn = '#removeBtn',
        refreshBtn = '#refreshBtn',
		

		accttype = '#type',
		terminals = '#terminals',
		
		fee = null;
		
	var buttons = modifyBtn + ',' + removeBtn;
	
    initSession('<?php echo $sessionExp; ?>');
    //$(DATATABLE).find('tbody tr').die('dblclick');
    oTable = $(DATATABLE).dataTable({
        bRetrieve: true,
        bJQueryUI: true,
		iDisplayLength: 25,
		aLengthMenu: DTLENGTHMENU,
		aoColumns: [
			{ bVisible: false }, //feeseqno 0
			{ bVisible: false }, //brseqno 1
			{ bVisible: false }, //servtype 2
			{ bVisible: false }, //fee type 3
			{ bVisible: false }, //authname 4
			{ bVisible: false }, //nettype 5
			{ bVisible: false }, //chargetype 6
			{ bVisible: false }, //trxcode2 7
			{ sClass: 'centerAlign' }, //transaction 8
			null, //branch 9
			{ sClass: 'rightAlign' }, //service fee 10
			null, //terminal type 11
			null, //service type 12
			null, //network type 13
			null, //description 14
			null, //fee type desc 15
			null, //charge desc 16
			{ sClass: 'rightAlign' }, //min range 17
			{ sClass: 'rightAlign' }, //max range 18
			null, //remarks 19,
			{ bVisible: false } //cardtype 20
		],
        aaSorting: [],
        sScrollY: '100%',
        sScrollX: '100%',
        sPaginationType: 'full_numbers',
        fnInitComplete: function () {
			getData('zzzz',$(accttype).val());
        },
        fnRowCallback: function (nRow, aData, iDisplayIndex) {			
            $(nRow).attr('id', aData[0]).unbind('click dblclick').click(function () {
				if ($(MSGBOX).length > 0) {
					$(MSGBOX).dialog('close');
				}
                $('tbody tr').removeClass('rowSelected');
                $(this).addClass('rowSelected');
				
				var termCode = $(terminals).val();
                fee = {
					feeseqno: aData[0],
					termCode: termCode,
					brseqno: aData[1],
					servType: aData[2],
					termType: aData[11],
					feeType: aData[3],
					minRange: aData[17],
					maxRange: aData[18],
					serviceFee: aData[10],
					authName: aData[4],
					netType: aData[5],
					tranDesc: aData[14],
					chargeType: aData[6],
					chargeDesc: aData[16],
					trxcode2: aData[7],
					remarks: aData[19],
                	accttype: aData[20]
				}
				
                $(buttons).removeAttr('disabled')
            }).dblclick(function () {
                $(modifyBtn).trigger('click');
            });
            $(buttons).attr('disabled', 'disabled');
            return nRow;
        }
    });
	
	$('.ui-toolbar:first').append($('#customToolbar .top').html());
	
	$(terminals).change(function () {
		getData(this.value, $(accttype).val());
	});
	
	$(newBtn).click(function (e) {

		requests.push(
				$.ajax({
					type: 'POST',
					url: 'maintenance/servicecharges/cache2',
					data: {
						accttype: $(accttype).val()
					},
					dataType: 'json',
					beforeSend: function() {

					},
					success: function(data) {
						if (data.success === true) {
							var terminal = $(terminals).val();
							window.location.hash = 'maintenance/servicechargenew/' + terminal;
						}
					}
				})
			);
		e.preventDefault();
	});
	
	$(modifyBtn).click(function (e) {
		if (fee) {
			//serialize AJAX request
			requests.push(
				$.ajax({
					type: 'POST',
					url: 'maintenance/servicecharges/cache',
					data: fee,
					dataType: 'json',
					beforeSend: function() {

					},
					success: function(data) {
						if (data.success === true) {
							window.location.hash = 'maintenance/servicechargeedit';
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
		var feeseqno = fee.feeseqno;
		if (fee) {
            messageBox('Remove selected fee?', 'Confirm', 'confirm', function () {
				//serialize AJAX request
				requests.push(
					$.ajax({
						type: 'POST',
						url: 'maintenance/servicecharges/delete',
						data: {
							feeseqno: feeseqno
						},
						dataType: 'json',
						beforeSend: function() {
							waitMessage('Removing entry...')
						},
						success: function(data) {
							messageBox(data.message);
							if (data.success === true) {
								oTable.fnDeleteRow(oTable.fnGetPosition($('#' + feeseqno)[0]));
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
	
    $(refreshBtn).click(function (e) {
		var termCode = $(terminals).val();
        getData(termCode, $(accttype).val());
		e.preventDefault();
    });

    $(accttype).change(function () {
		getData($(terminals).val(), this.value);
	}).click(function () {
		$(DIALOG).dialog('close');
	});
	
	function getData(termCode, accttype) {
		//serialize AJAX request
		requests.push(
			jqxhr = $.ajax({
				type: 'GET',
				url: 'maintenance/servicecharges/getdata',
				data: {
					termCode: termCode,
					accttype: accttype
				},
				dataType: 'json',
				beforeSend: function() {
					abortAJAXRequests();
					waitMessage('Retrieving Fee List...');
				},
				error: function(jqXHR, textStatus, errorThrown) {

				},
				success: function(data) {
					$.fn.dataTableExt.iApiIndex = 0;
					oTable.fnClearTable(0);
					oTable.fnAddData(data['details']);
					oTable.fnDraw();
					//oTable.fnAdjustColumnSizing();
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