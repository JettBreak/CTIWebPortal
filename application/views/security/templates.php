<div id="userTemplates">
    <h1>User Templates</h1>
    <table class="dataTable">
        <thead>
            <tr>
            	<th width="100">Template ID</th>
                <th>Description</th>
                <th>User Group</th>
                <th>grpseqno</th>
                <th>allows</th>
                <th width="80">Predefined</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
</div>
<div id="bottom">
    <span class="buttons floatLeft">
        <button id="newBtn">New</button
        ><button id="editBtn">Edit</button
        ><button id="deleteBtn">Delete</button
        ><!--<button id="previewBtn" disabled>Preview</button
        >--><button id="updateBtn">Update Users</button>
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
$(function () {
    var newBtn = '#newBtn',
        editBtn = '#editBtn',
        deleteBtn = '#deleteBtn',
        refreshBtn = '#refreshBtn',
		previewBtn = '#previewBtn',
		updateBtn = '#updateBtn',
		template = {};
		
	var buttons = editBtn + ',' + deleteBtn + ',' + previewBtn + ',' + updateBtn;
	
    initSession('<?php echo $sessionExp; ?>');
    oTable = $(DATATABLE).dataTable({
        bRetrieve: true,
        bJQueryUI: true,
		aLengthMenu: DTLENGTHMENU,
		aoColumns: [
			{ sClass: 'centerAlign' }, //template ID
			null, //template Name
			null, //user group
			{ bVisible: false }, //grpseqno
			{ bVisible: false }, //allows
			{ sClass: 'centerAlign' }  //predefined
		],
        aaSorting: [[0, 'desc']], //sort by template ID
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

				template = {
					ID: aData[0],
					description: aData[1],
					grpseqno: aData[3],
					allows: aData[4],
					predefined: aData[5]
				};
				
				//uncomment this to enable checking of predefined templates
				//var disabled = aData[5] === 'Y' ? true : false; //check if predefined
				$(buttons).attr('disabled', false);
            }).dblclick(function () {
                $(editBtn).triggerHandler('click');
            });
			
            return nRow;
        },
		fnDrawCallback: function () {
			$('tbody tr').removeClass('rowSelected');
			$(buttons).attr('disabled', true);
		}
    });
	
    $(newBtn).click(function (e) {
        window.location.hash = 'security/tmnew';
        e.preventDefault();
    });
	
    $(editBtn).click(function (e) {
		if (template) {
			/*if (template.predefined === 'Y') {
				
				messageBox('You cannot modify/delete predefined templates');
			
			}*/
			/*else if (template.grpseqno !== '<?php //echo $grpseqno; ?>') {
				
				messageBox('You cannot modify/delete templates of other user groups');
				
			}*/
			/*else {
				
			}*/
			
			requests.push(
				$.ajax({
					type: 'POST',
					url: 'security/templates/cache',
					data: template,
					dataType: 'json',
					success: function(data) {
						if (data.success === true) {
							window.location.hash = 'security/tmedit';
						}
					}
				})
			);
		} else {
			messageBox('Please select a template from the list');
		}
		e.preventDefault();
    });
	
    $(deleteBtn).click(function (e) {
		if (template) {
			/*if (template.predefined === 'Y') {
				
				messageBox('You cannot modify/delete predefined templates');
				
			}*/
			/*else if (template.grpseqno !== '<?php //echo $grpseqno; ?>') {
				
				messageBox('You cannot modify/delete templates of other user groups');
				
			}*/ 
			/*else {
				
			}*/
			messageBox('Delete User Template <strong>['+ template.description +']</strong>?','Confirm','confirm',function() {
				requests.push(
					$.ajax({
						type: 'POST',
						url: 'security/templates/delete',
						data: {
							description: template.description,
							tmseqno: template.ID
						},
						dataType: 'json',
						beforeSend: function() {
							waitMessage('Deleting User Template<br /><strong>['+ template.description +']</strong>...');
						},
						success: function(data) {
							if (data.success === true) {
								oTable.fnDeleteRow(oTable.fnGetPosition($('#' + template.ID)[0]));
							}
							messageBox(data.message);
						}
					})
				);
			});
		} else {
			messageBox('Please select a template from the list');
		}
		e.preventDefault();
    });
	
	$(updateBtn).click(function (e) {
		if (template) {
			messageBox('Update users using <strong>['+ template.description +']</strong> template?','Confirm','confirm',function() {
				requests.push(
					$.ajax({
						type: 'POST',
						url: 'security/templates/updateusers',
						data: {
							tmseqno: template.ID,
							grpseqno: template.grpseqno
						},
						dataType: 'json',
						beforeSend: function() {
							waitMessage('Updating Users...');
						},
						success: function(data) {
							if (data.success === true) {
								messageBox(data.message);
							}
						}
					})
				);
			});
		} else {
			messageBox('Please select a template from the list');
		}
		e.preventDefault();
    });
	
	$(previewBtn).click(function (e) {
		var templateID = template.ID;
		if (templateID) {
			pdfViewer('security/templates/previewx/' + template.ID, 'Preview');
		} else {
			messageBox('Please select a template from the list');
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
				url: 'security/templates/getdata',
				dataType: 'json',
				beforeSend: function() {
					abortAJAXRequests();
					waitMessage('Retrieving User Templates...');
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