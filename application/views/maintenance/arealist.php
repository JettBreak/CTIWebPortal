<div id="areaList">
    <h1>Area List</h1>
    <table class="dataTable">
        <thead>
            <tr>
            	<th width="100">Area Code</th>
                <th>Area Name</th>
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
		area = {};
		
	var buttons = modifyBtn + ',' + removeBtn;
	//end
	
    initSession('<?php echo $sessionExp; ?>');
    oTable = $(DATATABLE).dataTable({
        bRetrieve: true,
        bJQueryUI: true,
		aLengthMenu: DTLENGTHMENU,
		aoColumns: ([ //hide columns   
			{ sClass: 'centerAlign' },//{'bVisible': false },//Area Code
			null //Area Name
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
				//if ($(MSGBOX).length > 0) {
					$(MSGBOX).dialog('close');
				//}
				
				//highlight rows
				$('tbody tr').removeClass('rowSelected');
                $(this).addClass('rowSelected');
				
				//set temp var
				area = {
					code: aData[0],
					name: aData[1]
				}
				
                $(buttons).removeAttr('disabled')
            }).dblclick(function () {
                $(modifyBtn).trigger('click');
            });
			
			//unset temp obj
			//key = {};
            return nRow;
        },
		fnDrawCallback: function () {
			$('tbody tr').removeClass('rowSelected');
            $(buttons).attr('disabled', true);
		}
    });
	
    $(newBtn).click(function () {
        window.location.hash = 'maintenance/areanew';
        return false
    });
	
    $(modifyBtn).click(function (e) {     // edit area
		if (area) {
			//serialize AJAX request
			requests.push(
				$.ajax({
					type: 'POST',
					url: 'maintenance/arealist/cache',
					data: area,
					dataType: 'json',
					success: function(data) {
						if (data.success === true) {
							window.location.hash = 'maintenance/areaedit/';
						}
					}
				})
			);
        } else {
            messageBox('Please select an item from the list')
        }
        e.preventDefault();
    });
	
    $(removeBtn).click(function (e) {    // remove area
		if (area) {
            messageBox('Delete area entry <strong>[' + area.name + ']</strong>?', 'Confirm', 'confirm', function () {
				//serialize AJAX request
				requests.push(
					$.ajax({
						type: 'POST',
						url: 'maintenance/arealist/delete',
						data: {
							areaCode: area.code
						},
						dataType: 'json',
						beforeSend: function() {
							waitMessage('Removing area entry...')
						},
						success: function(data) {
							if (data.success === true) {
								messageBox('Area <strong>['+ area.name +']</strong> removed successfully');
								oTable.fnDeleteRow(oTable.fnGetPosition($('#' + area.code)[0]));
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
				url: 'maintenance/arealist/getdata',
				dataType: 'json',
				beforeSend: function() {
					abortAJAXRequests();
					waitMessage('Retrieving area list...');
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