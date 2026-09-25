<form id="atmMaintenanceForm" method="post">
    <div id="atmMaintenance">
        <h1>ATM List</h1>
        <table class="dataTable">
            <thead>
                <tr>
                    <th width="100">Terminal Code</th>
                    <th>Description</th>
                    <th width="120">Status</th>
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
            ><button id="dupeBtn" disabled>Duplicate</button
            ><button id="issueListBtn" disabled>Issue List</button>
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
        		<label for="branchList">Branch:</label>
                <select id="branchList" style="width:150px">
                	<option value="0">ALL</option>
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
$(function () {
    var newBtn = '#newBtn',
        modifyBtn = '#modifyBtn',
        removeBtn = '#removeBtn',
        dupeBtn = '#dupeBtn',
		issueListBtn = '#issueListBtn',
        refreshBtn = '#refreshBtn',
		branch = '#branchList',
		lastVal = null,
		tCode = null;
		
	var buttons = modifyBtn + ',' + removeBtn + ',' + dupeBtn + ',' + issueListBtn;
	
    initSession('<?php echo $sessionExp; ?>');
    //$(DATATABLE).find('tbody tr').die('dblclick');
    oTable = $(DATATABLE).dataTable({
        bRetrieve: true,
        bJQueryUI: true,
		aLengthMenu: DTLENGTHMENU,
		/*'aoColumns': [
			{ sWidth: '25%' },
			{ sWidth: '45%' },
			{ sWidth: '30%' }
		],*/
        aaSorting: [],
        sScrollY: '100%',
        sScrollX: '100%',
        sPaginationType: 'full_numbers',
        fnInitComplete: function () {
			getData();
        },
        fnRowCallback: function (nRow, aData, iDisplayIndex) {
            $('td:eq(0)', nRow).attr('align', 'center');
			
            $(nRow).attr('id', aData[0]).unbind('click dblclick').click(function () {
				if ($(MSGBOX).length > 0) {
					$(MSGBOX).dialog('close');
				}
                $('tbody tr').removeClass('rowSelected');
                $(this).addClass('rowSelected');
				
                tCode = aData[0];
				
                $(buttons).removeAttr('disabled')
            }).dblclick(function () {
                $(modifyBtn).trigger('click');
            });
            $(buttons).attr('disabled', true);
			
			tCode = null;
            return nRow;
        }
    });
	
	<?php echo $uiToolbar; ?>
	
    $(newBtn).click(function () {
        window.location.hash = 'maintenance/atmnew';
        return false;
    });
	
    $(modifyBtn).click(function () {
		if (tCode) {
            window.location.hash = 'maintenance/atmedit/' + tCode
        } else {
            messageBox('Please select an ATM from the list')
        }
        return false;
    });
	
    $(removeBtn).click(function (e) {
		if (tCode) {
            messageBox('Remove ATM Terminal <strong>[' + tCode + ']</strong>?', 'Confirm', 'confirm', function () {
                requests.push(
					$.ajax({
						type: 'POST',
						url: 'maintenance/atm/remove',
						data: {
							termCode: tCode
						},
						dataType: 'json',
						beforeSend: function() {
							waitMessage('Removing ATM entry...');
						},
						success: function(data) {
							if (data.removed === true) {
								messageBox('ATM Terminal <strong>['+ data.termCode +']</strong> removed successfully');
								oTable.fnDeleteRow(oTable.fnGetPosition($('#' + data.termCode)[0]));
							}
						}
					})
				);
            })
        } else {
            messageBox('Please select an ATM from the list')
        }
        e.preventDefault();
    });
	
    $(refreshBtn).click(function () {
		var brCode = $(branch).val();
        getData(brCode);
		return false;
    });
	
    $(dupeBtn).click(function () {
		if (tCode) {
			window.location.hash = 'maintenance/atmdup/' + tCode;
		} else {
			messageBox('Please select an ATM from the list');
		}
		return false;
    });
	
	$(issueListBtn).click(function () {
		if (tCode) {
            window.location.hash = 'maintenance/issuelist/' + tCode
        } else {
            messageBox('Please select an ATM from the list')
        }
        return false;
	});
	
	//filter by branch
	$(branch).change(getData)
	.focus(function () {
		lastVal = this.value;
	});
	
	function getData(brCode) {
		var brCode = $(branch).val();
		
		//serialize AJAX request
		requests.push(
			jqxhr = $.ajax({
				type: 'GET',
				url: 'maintenance/atm/getdata',
				data: {
					branch: brCode
				},
				dataType: 'json',
				beforeSend: function() {
					abortAJAXRequests();
					waitMessage('Retrieving ATM list...');
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
					}/* else {
						messageBox('There are no terminals in this branch');
						$(branch).val(lastVal);
					}*/
					$(MSGBOX).dialog('close');
				},
				complete: function() {
					
				}
			})
		);
	}
});
</script>