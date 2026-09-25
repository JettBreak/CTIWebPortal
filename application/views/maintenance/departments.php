<div id="deptList">
    <h1>Department List</h1>
    <table class="dataTable">
        <thead>
            <tr>
            	<th width="100">Department Code</th>
                <th>Department Name</th>
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
		department = {};
		
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
			{ sClass: 'centerAlign' },//{'bVisible': false },//location ID
			null //location
		]),
        aaSorting: [[0, 'desc']], //sort by locCode
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
				department = {
					code: aData[0],
					name: aData[1]
				}
				
                $(buttons).removeAttr('disabled')
            }).dblclick(function () {
                $(modifyBtn).trigger('click');
            });
            return nRow;
        },
		fnDrawCallback: function () {
			$('tbody tr').removeClass('rowSelected');
			$(buttons).attr('disabled', true);
			
			//unset temp obj
			department = {};
		}
    });
	
    $(newBtn).click(function () {
        window.location.hash = 'maintenance/deptnew';
        return false
    });
	
    $(modifyBtn).click(function (e) {
		if (department) {
			//serialize AJAX request
			requests.push(
				$.ajax({
					type: 'POST',
					url: 'maintenance/departments/cache',
					data: department,
					dataType: 'json',
					success: function(data) {
						if (data.success === true) {
							window.location.hash = 'maintenance/deptedit/';
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
		if (department) {
            messageBox('Delete <strong>[' + department.name + ']</strong> department?', 'Confirm', 'confirm', function () {
				//serialize AJAX request
				requests.push(
					$.ajax({
						type: 'POST',
						url: 'maintenance/departments/delete',
						data: {
							deptCode: department.code
						},
						dataType: 'json',
						beforeSend: function() {
							waitMessage('Removing department entry...')
						},
						success: function(data) {
							if (data.success === true) {
								messageBox('<strong>['+ department.name +']</strong> department removed successfully');
								oTable.fnDeleteRow(oTable.fnGetPosition($('#' + department.code)[0]));
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
	
    $(refreshBtn).click(function () {
        getData();
		return false
    });
	
	function getData() {
		//serialize AJAX request
		requests.push(
			jqxhr = $.ajax({
				type: 'GET',
				url: 'maintenance/departments/getdata',
				dataType: 'json',
				beforeSend: function() {
					abortAJAXRequests();
					waitMessage('Retrieving department list...');
				},
				error: function(jqXHR, textStatus, errorThrown) {

				},
				success: function(data) {
					if (data.success) {
						//populate table
						$.fn.dataTableExt.iApiIndex = 0;
						oTable.fnClearTable(0);
						oTable.fnAddData(data['details']);
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
