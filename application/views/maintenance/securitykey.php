<div id="atmKeyMgmt">
    <h1>Security Key Management</h1>
    <table class="dataTable">
        <thead>
            <tr>
            	
                <th>Security Code</th>
                <th>Type</th>
                <th>Description</th>
                <th>encMode</th>
                <th>inVariant</th>
                <th>inMKey</th>
                <th>inWKey</th>
                <th>inEncType</th>
                <th>outVariant</th>
                <th>outMKey</th>
                <th>outWKey</th>
                <th>outEncType</th>
                <th>xml</th>
                <th>nodename</th>
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
<style>
.dataTables_scrollBody{min-height:150px !important;max-height:300px !important;}
</style>
<script>
$(function() {
	//init vars
    var newBtn = '#newBtn',
        modifyBtn = '#modifyBtn',
        removeBtn = '#removeBtn',
        refreshBtn = '#refreshBtn',
		key = {};
		
	var buttons = modifyBtn + ',' + removeBtn;
	//end
	
    initSession('<?php echo $sessionExp; ?>');
    oTable = $(DATATABLE).dataTable({
        bRetrieve: true,
        bJQueryUI: true,
		aLengthMenu: DTLENGTHMENU,
		/*aoColumns: [
			{ sWidth: '25%' },
			{ sWidth: '30%' },
			{ sWidth: '45%' }
		],*/
		aoColumns: ([
			null,//secCode
			null,//encType
			null,//desc
			{ bVisible : false },//encMode
			{ bVisible : false },//inVariant
			{ bVisible : false },//inMKey
			{ bVisible : false },//inWKey
			{ bVisible : false },//inEncType
			{ bVisible : false },//outVariant
			{ bVisible : false },//outMKey
			{ bVisible : false },//outWKey
			{ bVisible : false },//outEncType
			{ bVisible : false },//xml
			{ bVisible : false }//nodename
		]),
        aaSorting: [],
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
				key = {
					nodeName: aData[13],
					secCode: aData[0],
					encMode: aData[3],
					inVariant: aData[4],
					inMKey: aData[5],
					inWKey: aData[6],
					inEncType: aData[7],
					outVariant: aData[8],
					outMKey: aData[9],
					outWKey: aData[10],
					outEncType: aData[11],
					xml: aData[12]
				}
				
                $(buttons).removeAttr('disabled')
            }).dblclick(function () {
                $(modifyBtn).trigger('click');
            });
			$('tbody tr').removeClass('rowSelected');
            $(buttons).attr('disabled', 'disabled');
			
			//unset temp obj
			key = {};
            return nRow
        }
    });
    $(newBtn).click(function () {
        window.location.hash = 'maintenance/keynew';
        return false
    });
    $(modifyBtn).click(function (e) {
		if (key.secCode) {
			//serialize AJAX request
			requests.push(
				$.ajax({
					type: 'POST',
					url: 'maintenance/securitykey/cache',
					data: key,
					dataType: 'json',
					success: function(data) {
						if (data.success === true) {
							window.location.hash = 'maintenance/keyedit/';
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
		if (key.secCode) {
            messageBox('Remove Security Key <strong>[' + key.secCode + ']</strong>?', 'Confirm', 'confirm', function () {
				//serialize AJAX request
				requests.push(
					$.ajax({
						type: 'POST',
						url: 'maintenance/securitykey/remove',
						data: {
							'secCode': key.secCode
						},
						dataType: 'json',
						beforeSend: function() {
							waitMessage('Removing Security Key...')
						},
						success: function(data) {
							if (data['removed'] === true) {
								messageBox('Security Key <strong>['+ key.secCode +']</strong> removed successfully');
								oTable.fnDeleteRow(oTable.fnGetPosition($('#' + key.secCode)[0]));
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
    $(refreshBtn).click(function () {
        getData();
		return false
    });
	function getData() {
		//serialize AJAX request
		requests.push(
			jqxhr = $.ajax({
				type: 'GET',
				url: 'maintenance/securitykey/getdata',
				dataType: 'json',
				beforeSend: function() {
					abortAJAXRequests();
					waitMessage('Retrieving Security Key list...');
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