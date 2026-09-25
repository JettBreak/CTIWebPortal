<div id="branchList">
    <h1>Branch List</h1>
    <table class="dataTable">
        <thead>
            <tr>
            	<th width="100">Branch Code</th>
                <th>Branch Name</th>
                <th width="100">Branch ID</th>
                <th>brseqno</th>
                <th>address</th>
                <th>telno</th>
                <th>regioncode</th>
                <th>ishead</th>
                <th>isrep</th>
                <th>ismon</th>
                <th>isuser</th>
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
		branch = {};
		
	var buttons = modifyBtn + ',' + removeBtn;
	//end
	
    initSession('<?php echo $sessionExp; ?>');
    oTable = $(DATATABLE).dataTable({
        bRetrieve: true,
        bJQueryUI: true,
		aLengthMenu: DTLENGTHMENU,
		/*'aoColumns': [
			{ sWidth: '25%' },
			{ sWidth: '30%' },
			{ sWidth: '45%' }
		],*/
		aoColumns: ([ //hide columns   
			{ sClass: 'centerAlign' },//{ bVisible: false },//Branch Code
			null ,//Branch Name
			{ sClass: 'centerAlign' }, //Branch ID
			{ bVisible: false },// Branch Seqno
			{ bVisible: false },// address
			{ bVisible: false },// telno
			{ bVisible: false },// region code
			{ bVisible: false },// ishead Office
			{ bVisible: false },// isrep
			{ bVisible: false },// ismon
			{ bVisible: false } // isuser
		]),
        aaSorting: [[0, 'asc']], //sort by brcode
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
				branch = {
					code: aData[0],
					name: aData[1],
					id: aData[2],
					seq: aData[3],
					addr: aData[4],
					tel: aData[5],
					area: aData[6],
					head: aData[7],
					isRep: aData[8],
					isMon: aData[9]
				}
				
                $(buttons).removeAttr('disabled')
            }).dblclick(function () {
                $(modifyBtn).trigger('click');
            });
			
			//unset temp obj
			key = {};
            return nRow
        },
		fnDrawCallback: function () {
			$('tbody tr').removeClass('rowSelected');
            $(buttons).attr('disabled', true);
		}
    });
	
    $(newBtn).click(function () {
        window.location.hash = 'maintenance/brchnew';
        return false
    });
	
    $(modifyBtn).click(function (e) {     // edit branch
		if (branch) {
			//serialize AJAX request
			requests.push(
				$.ajax({
					type: 'POST',
					url: 'maintenance/brchlist/cache',
					data: branch,
					dataType: 'json',
					success: function(data) {
						if (data.success === true) {
							window.location.hash = 'maintenance/brchedit/';
						}
					}
				})
			);
        } else {
            messageBox('Please select an item from the list')
        }
        e.preventDefault();
    });
	
    $(removeBtn).click(function (e) {    // remove branch
		if (location) {
            messageBox('Delete branch entry <strong>[' + branch.name + ']</strong>?', 'Confirm', 'confirm', function () {
				//serialize AJAX request
				requests.push(
					$.ajax({
						type: 'POST',
						url: 'maintenance/brchlist/delete',
						data: {
							brchSeq: branch.seq,
							brchCode: branch.code 
						},
						dataType: 'json',
						beforeSend: function() {
							waitMessage('Removing branch entry...')
						},
						success: function(data) {
							if (data['removed'] === true) {
								messageBox('Branch <strong>['+ branch.name +']</strong> removed successfully');
								oTable.fnDeleteRow(oTable.fnGetPosition($('#' + branch.code)[0]));
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
	
    $(refreshBtn).click(function () {
        getData();
		return false
    });
	
	function getData() {
		//serialize AJAX request
		requests.push(
			jqxhr = $.ajax({
				type: 'GET',
				url: 'maintenance/brchlist/getdata',
				dataType: 'json',
				beforeSend: function() {
					abortAJAXRequests();
					waitMessage('Retrieving branch list...');
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