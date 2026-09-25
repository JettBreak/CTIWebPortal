<form id="xPosMaintenanceForm" method="post">
    <input type="hidden" name="instid" id="instid"/>
    <input type="hidden" name="instcode" id="instcode"/>
    <div id="xPosMaintenance" style="width:600px">
        <h1>INSTITUTION LIST</h1>
        <table class="dataTable">
            <thead>
                <tr>
                    <th>instseqno</th>
                    <th width="100">Institution ID</th>
                    <th>Institution Name</th>
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
            ><button id="removeBtn" value="maintenance/xpartnerlist/remove" disabled>Remove</button
            >
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
        		<!-- Branch:
                <select id="branchList" style="width:150px">
                	<option value="0">ALL</option>
					<?php //echo html_entity_decode($branches); ?>
                </select> -->
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
		instid = '#instid',
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
        aoColumns: [
            { bVisible: false }, //time
            { sClass: 'leftAlign' }, //log key
            { sClass: 'leftAlign' } //log key

        ],
        sScrollY: '100%',
        sScrollX: '100%',
        sPaginationType: 'full_numbers',
        fnInitComplete: function () {
			getData();
        },
        fnRowCallback: function (nRow, aData, iDisplayIndex) {
            $('td:eq(0)', nRow).attr('align', 'center');
			
			var instidx = oTable.fnGetData(oTable.fnGetPosition(nRow))[0];
            var instcdx = oTable.fnGetData(oTable.fnGetPosition(nRow))[1];
			
            $(nRow).attr('id', aData[0]).unbind('click dblclick').click(function () {
				if ($(MSGBOX).length > 0) {
					$(MSGBOX).dialog('close');
				}
                $('tbody tr').removeClass('rowSelected');
                $(this).addClass('rowSelected');
                
                $(instid).val(instidx);
                $('#instcode').val(instcdx);
                $(buttons).removeAttr('disabled')
            }).dblclick(function () {
                window.location.hash = 'maintenance/xposinstedit/' + instidx
            });
			$(buttons).attr('disabled', 'disabled');
			$(instid).val('');
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
				messageBox('Institution <strong>['+ data['instcode'] +']</strong> removed successfully');
				oTable.fnDeleteRow(oTable.fnGetPosition($('#' + data['instid'])[0]));
			} else {
                messageBox(data['message']);
                //oTable.fnDeleteRow(oTable.fnGetPosition($('#' + data['instid'])[0]));
            }
        },
        scroll: false
    });
    form.validationEngine('attach');
	
	<?php echo $uiToolbar; ?>
	
    $(newBtn).click(function () {
        window.location.hash = 'maintenance/xposinst';
        return false
    });
    $(modifyBtn).click(function () {
		var selected = $(instid).val();
		if (selected !== '') {
            window.location.hash = 'maintenance/xposinstedit/' + selected;
        } else {
            messageBox('Please select an Institution from the list');
        }
        return false
    });
    $(removeBtn).click(function (e) {
		var selected = $('#instcode').val();
		if (selected !== '') {
            messageBox('Remove Institution <strong>[' + selected + ']</strong>?', 'Confirm', 'confirm', function () {
                form.submit();
            })
        } else {
            messageBox('Please select an Institution from the list');
        }
        e.preventDefault();
    });
	
    $(refreshBtn).click(getData);
	
    $(dupeBtn).click(function () {
		var selected = $(instid).val();
		if (selected !== '') {
			window.location.hash = 'maintenance/xposinstdup/' + selected;
		} else {
			messageBox('Please select an Institution from the list');
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
				url: 'maintenance/xpartnerlist/getdata',
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