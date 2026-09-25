<div id="userGroups">
    <h1>User Groups</h1>
    <table class="dataTable">
        <thead>
            <tr>
                <th width="100">Code</th>
                <th>Group Name</th>
                <th>maxretry</th>
                <th>userexpiry</th>
                <th>minchar</th>
                <th>passexpiry</th>
                <th>sessionexp</th>
                <th>secuoption</th>
                <th>passoption</th>
                <th>pwcycle</th>
                <th>userInactive</th>
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
        ><button class="closebtn">Close</button
        >
    </span>
</div>
<style>
.dataTables_scrollBody{min-height:150px !important;max-height:300px !important;}
</style>
<script>
$(function () {
	var newBtn = '#newBtn',
        modifyBtn = '#modifyBtn',
        removeBtn = '#removeBtn',
        refreshBtn = '#refreshBtn',
		group = {}
	
	var buttons = modifyBtn + ',' + removeBtn;
    initSession('<?php echo $sessionExp; ?>');
		
	oTable = $(DATATABLE).dataTable({
        bRetrieve: true,
        bJQueryUI: true,
		aLengthMenu: DTLENGTHMENU,
		aoColumns: [
			{ sClass: 'centerAlign' }, //group id
			null, //Group Name
			{ bVisible: false }, //maxretry
			{ bVisible: false }, //userexpiry
			{ bVisible: false }, //minchar
			{ bVisible: false }, //passexpiry
			{ bVisible: false }, //sessionexp
			{ bVisible: false }, //secuoption
			{ bVisible: false }, //passoption
			{ bVisible: false }, //pwcycle
			{ bVisible: false }  //userInactive
		],
        aaSorting: [],
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

				group = {
					seqno: 		aData[0],
					desc: 		aData[1],
					maxretry: 	aData[2],
					userexpiry: aData[3],
					minchar: 	aData[4],
					passexpiry:	aData[5],
					sessionexp: aData[6],
					secuoption: aData[7],
					passoption: aData[8],
					passCycle: 	aData[9],
					userInactive: 	aData[10]
				};
				
                $(buttons).removeAttr('disabled')
            }).dblclick(function () {
                $(modifyBtn).triggerHandler('click');
            });
			
			return nRow;
        },
		fnDrawCallback: function () {
			$('tbody tr').removeClass('rowSelected');
			$(buttons).attr('disabled', true);
		}
    });
	
	$(newBtn).click(function () {
        window.location.hash = 'security/usergroupnew';
        return false;
    });
	
	$(modifyBtn).click(function (e) {
		if (group) {
			requests.push(
				$.ajax({
					type: 'POST',
					url: 'security/usergroups/cache',
					data: group,
					dataType: 'json',
					success: function(data) {
						if (data.success === true) {
							window.location.hash = 'security/usergroupedit';
						}
					}
				})
			)
		} else {
			messageBox('Please select an item from the list');
		}
		e.preventDefault();
	});
	
	$(removeBtn).click(function (e) {
		if (group) {
			messageBox('Delete User Group <strong>['+ group.desc +']</strong>?','Confirm','confirm',function() {
				requests.push(
					$.ajax({
						type: 'POST',
						url: 'security/usergroups/delete',
						data: {
							grpseqno: group.seqno
						},
						dataType: 'json',
						beforeSend: function () {
							waitMessage('Deleting User Group <strong>['+ group.desc +']</strong>...');
						},
						success: function(data) {
							if (data.success === true) {
								messageBox('User Group <strong>['+ group.desc +']</strong> deleted successfully');
								oTable.fnDeleteRow(oTable.fnGetPosition($('#' + group.seqno)[0]));
							} else {
								messageBox(data.message);
							}
						}
					})
				);
			});
		} else {
			messageBox('Please select an item from the list');
		}
		e.preventDefault();
	});
	
	$(refreshBtn).click(function (e) {
		getData();
		e.preventDefault();
	});
	
	function getData() {
		//serialize AJAX request
		requests.push(
			jqxhr = $.ajax({
				type: 'GET',
				url: 'security/usergroups/getdata',
				dataType: 'json',
				beforeSend: function() {
					abortAJAXRequests();
					waitMessage('Retrieving User Group list...');
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