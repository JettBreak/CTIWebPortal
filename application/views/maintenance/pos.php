<form id="posMaintenanceForm" method="post">
    <input type="hidden" name="termCode" id="termCode"/>
    <div id="posMaintenance">
        <h1>POS List</h1>
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
            ><button id="removeBtn" value="maintenance/pos/remove" disabled>Remove</button
            ><!-- <button id="dupeBtn" disabled>Duplicate</button> -->
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
        refreshBtn = '#refreshBtn',
		branch = '#branchList',
		tCode = '#termCode',
		lastVal = null,
        form = $('form');
		
	var buttons = modifyBtn + ',' + removeBtn + ',' + dupeBtn;
	
    initSession('<?php echo $sessionExp; ?>');
    //$(DATATABLE).find('tbody tr').die('dblclick');
    oTable = $(DATATABLE).dataTable({
        bRetrieve: true,
        bJQueryUI: true,
		aLengthMenu: DTLENGTHMENU,
        aaSorting: [],
        sScrollY: '100%',
        sScrollX: '100%',
        sPaginationType: 'full_numbers',
        fnInitComplete: function () {
			getData();
        },
        fnRowCallback: function (nRow, aData, iDisplayIndex) {
            $('td:eq(0)', nRow).attr('align', 'center');
			
			var termCode = oTable.fnGetData(oTable.fnGetPosition(nRow))[0];
			
            $(nRow).attr('id', aData[0]).unbind('click dblclick').click(function () {
				if ($(MSGBOX).length > 0) {
					$(MSGBOX).dialog('close');
				}
                $('tbody tr').removeClass('rowSelected');
                $(this).addClass('rowSelected');
                
                $(tCode).val(termCode);
                $(buttons).removeAttr('disabled')
            }).dblclick(function () {
                window.location.hash = 'maintenance/posedit/' + termCode
            });
			$(buttons).attr('disabled', 'disabled');
			$(tCode).val('');
            return nRow
        }
    });
	form.validationEngine({
        ajaxFormValidation: true,
        onBeforeAjaxFormValidation: function () {
            waitMessage('Removing POS entry...')
        },
        onAjaxFormComplete: function (form, status, data, options) {
			if (data['removed'] === true) {
				messageBox('POS Terminal <strong>['+ data['termCode'] +']</strong> removed successfully');
				oTable.fnDeleteRow(oTable.fnGetPosition($('#' + data['termCode'])[0]));
			}
        },
        scroll: false
    });
    form.validationEngine('attach');
	
	<?php echo $uiToolbar; ?>
	
    $(newBtn).click(function () {
        window.location.hash = 'maintenance/posnew';
        return false
    });
    $(modifyBtn).click(function () {
		var selected = $(tCode).val();
		if (selected !== '') {
            window.location.hash = 'maintenance/posedit/' + selected;
        } else {
            messageBox('Please select a POS from the list')
        }
        return false
    });
    $(removeBtn).click(function (e) {
		var selected = $(tCode).val();
		if (selected !== '') {
            messageBox('Remove POS Terminal <strong>[' + selected + ']</strong>?', 'Confirm', 'confirm', function () {
                form.submit();
            })
        } else {
            messageBox('Please select a POS from the list')
        }
        e.preventDefault();
    });
	
    $(refreshBtn).click(getData);
	
    $(dupeBtn).click(function () {
		var selected = $(tCode).val();
		if (selected !== '') {
			window.location.hash = 'maintenance/posdup/' + selected;
		} else {
			messageBox('Please select a POS from the list');
		}
		return false
    });
	
	$(branch).change(getData)
	.focus(function () {
		lastVal = this.value;
	});
	
	function getData() {
		var brCode = $(branch).val();
		
		//serialize AJAX request
		requests.push(
			jqxhr = $.ajax({
				type: 'GET',
				url: 'maintenance/pos/getdata',
				dataType: 'json',
				data: {
					brcode: brCode
				},
				beforeSend: function() {
					abortAJAXRequests();
					waitMessage('Retrieving POS list...');
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
				},
				complete: function() {
					$(MSGBOX).dialog('close');
				}
			})
		);
		
		return false;
	}
});

</script>