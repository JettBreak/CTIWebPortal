<form id="userListForm" method="post">
<div id="userList">
	<h1>Administrators List</h1>
    <table class="dataTable">
        <thead>
            <tr>
                <th width="80">User ID</th>
                <th width="140">Name</th>
                <th>Status</th>
                <th width="150">Last Login Date</th>
                <th width="150">Last Password Change</th>
                <th width="150">Date Created</th>
                <th>grpseqno</th>
                <th>position</th>
                <th>xml1</th>
                <th>department</th>
                <th>tmseqno</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
</div>
<div id="bottom">
	<span class="buttons floatLeft">
    	<button id="addBtn">Add</button
        ><button id="modifyBtn" disabled>Modify</button
        ><button id="deleteBtn" disabled>Delete</button
        ><button id="disableBtn" disabled>Disable</button
        ><button id="enableBtn" disabled class="hidden">Enable</button
        ><button id="resetSettingsBtn" disabled>Reset Settings</button
        ><button id="resetPwdBtn" disabled>Reset Password</button>
	</span>
    <span class="buttons floatRight">
    	<button id="refreshBtn">Refresh</button
        >
	</span>
</div>
</form>
<style>
.dataTables_scrollBody {
	height: 220px !important;
	max-height: 220px !important;
}
</style>

<script>
$(function() {
	waitMessage('Retrieving user list...');
	
	//init vars
	var loading = '<img src="images/loader.gif" width="16" height="18" alt="loading" title="loading"/>Retrieving user list...',
		error = '<img src="images/error.gif" width="7" height="19" alt="error" title="error" /> Error: ';
	
	var addBtn = '#addBtn',
		modifyBtn = '#modifyBtn',
		deleteBtn = '#deleteBtn',
		disableBtn = '#disableBtn',
		enableBtn = '#enableBtn',
		resetSettingsBtn = '#resetSettingsBtn',
		resetPwdBtn = '#resetPwdBtn',
		refreshBtn	= '#refreshBtn',
		status = '#status',
		form = $('form'),
		user = {},
		jqxhr = null;
	
	var buttons = modifyBtn + ',' + deleteBtn + ',' + enableBtn + ',' + disableBtn + ',' + resetSettingsBtn + ',' + resetPwdBtn;
	//end
	
	oTable = $(DATATABLE).dataTable({
		'bRetrieve':true,
		'bJQueryUI': true,
		'aLengthMenu': DTLENGTHMENU,
		'aaSorting': [],
		'aoColumns': ([
			null,//userID
			null,//userName
			null,//status
			null,//last login
			null,//last pwd change
			null,//dtcreated
			{'bVisible': false },//grpseqno
			{'bVisible': false },//position
			{'bVisible': false },//xml1
			{'bVisible': false },//department
			{'bVisible': false }//tmseqno
		]),
		'sScrollY': '100%',
		'sScrollX': '100%', // Required for viewing tables with lots of columns at low resolution - otherwise columns are mis-aligned
		'sPaginationType': 'full_numbers',
		'fnRowCallback': function(nRow, aData, iDisplayIndex) {
			$(nRow).attr('id', aData[0]).unbind('click dblclick').click(function () {
				//close dialog boxes		
				if ($(MSGBOX).length > 0) {
					$(MSGBOX).dialog('close');
				}
				
				//highlight rows
				$('tbody tr').removeClass('rowSelected');
                $(this).addClass('rowSelected');
				
				//set temp var
				user = {
					ID: aData[0],
					name: aData[1],
					status: aData[2],
					dtCreated: aData[5],
					grpseqno: aData[6],
					position: aData[7],
					xml1: aData[8],
					department: aData[9],
					tmseqno: aData[10]
				}
				
				//
				if (user.status === 'Disabled') {
					$(enableBtn).show();
					$(disableBtn).hide();
				} else {
					$(enableBtn).hide();
					$(disableBtn).show();
				}
				
				$(buttons).removeAttr('disabled');
			}).dblclick(function () {
                $(modifyBtn).trigger('click');
            });
			$('tbody tr').removeClass('rowSelected');
			$(buttons).attr('disabled', 'disabled');
			
			return nRow;
		}
	});
	
	//default Action
	$(TABCONTENT).hide(); //hide all content
	$(TABS).first().addClass('active').show(); //activate first tab
	$(TABCONTENT).first().show(); //show first tab content
	       			
	//onClick Event
	$(TABS).click(function() {
		if (!$(this).hasClass('active')) {
			$(WRAPPER).height('auto');
			
			$(TABS).removeClass('active'); //remove any "active" class
			var $this = $(this);
			$this.addClass('active'); //add "active" class to selected tab
			
			var $x = $(TABSActive).find('a');
			
			var $activeTab = $x.attr('href'); //find the rel attribute value to identify the active tab + content
			var $grpseqno = $x.attr('grpseqno');
			var $index = $x.attr('tableIndex');
			getData($grpseqno, $index);
			
			$(status).html(loading).show();				
			jqxhr.error(function() {

			}).success(function() {
				$(TABS).removeClass('active'); //remove any "active" class
				$this.addClass('active'); //add "active" class to selected tab
			
				$(TABCONTENT).hide(); //hide all tab content
				$($activeTab).show(); //fade in the active content
				oTable.fnAdjustColumnSizing(); //fix misaligned columns
			});
		}
		return false;
	});
	
	//hide grpseqno, position columns
	//oTable.fnSetColumnVis(6, false);
	//oTable.fnSetColumnVis(7, false);
	
	//load first dataTable (Branch Manager Tab)
	//getData(grpseqno 2, tableIndex 0);
	getData(<?php echo $initgrpseqno; ?>,0);
	//$(TABS + ' a[grpseqno="4"]').addClass('active');
			
	function getData(grpseqno, index, cache) {
		cache = typeof(cache) != 'undefined' ? cache : 1;
		abortAJAXRequests();
		//serialize AJAX request
		requests.push(
			jqxhr = $.ajax({
				type: 'POST',
				url: 'security/users/getData',
				data: 'grpseqno=' + grpseqno + '&cache=' + cache,
				dataType: 'json',
				error: function(jqXHR, textStatus, errorThrown) {
					if (jqXHR.status !== 0) {
						$(status).html(error + '(' + textStatus + ')').show();
					}
				},
				success: function(data) {
					$.fn.dataTableExt.iApiIndex = index;
					oTable.fnClearTable(0);
					oTable.fnAddData(data['details']);
					oTable.fnDraw();
							
					$(status).html('').hide();
				},
				complete: function() {
					if ($(MSGBOX).length > 0) {
						$(MSGBOX).dialog('close');
					}
				}
			})
		);
	}
	
	$(addBtn).click(function(e) {
		var grpseqno = $(TABS+'.active a').attr('grpseqno');
		window.location.hash = 'security/users/enroll/' + grpseqno;
		e.preventDefault();
	});

	$(DATATABLE).find('tbody tr').die().live('dblclick', function() {
		//if record count > 0
		if (oTable.fnSettings().fnRecordsDisplay() > 0) {
			$(modifyBtn).trigger('click');
		}
	});
	
	$(modifyBtn).click(function(e) {
		var userID = user.ID;
		if (userID) {
			requests.push(
				$.ajax({
					type: 'POST',
					url: 'security/users/cache',
					data: user,
					dataType: 'json',
					success: function(data) {
						if (data.success === true) {
							window.location.hash = 'security/users/update';
						}
					}
				})
			)
		} else {
			messageBox('Please select a user from the list');
		}
		e.preventDefault();
	});
	
	$(deleteBtn).click(function(e) {
		var userID = user.ID;
		if (userID) {
			messageBox('Delete User ID <strong>['+ userID +']</strong>?','Confirm','confirm',function() {
				requests.push(
					$.ajax({
						type: 'POST',
						url: 'security/users/delete',
						data: {
							'userID': userID
						},
						dataType: 'json',
						beforeSend: function() {
							waitMessage('Deleting User ID <strong>['+ userID +']</strong>...');
						},
						success: function(data) {
							if (data['removed'] === true) {
								messageBox('User ID <strong>['+ userID +']</strong> deleted successfully');
								oTable.fnDeleteRow(oTable.fnGetPosition($('#' + userID)[0]));
							}
						}
					})
				);
			});
		} else {
			messageBox('Please select a user from the list');
		}
		e.preventDefault();
	});
	
	$(disableBtn).click(function(e) {
		var userID = user.ID;
		if (userID) {			
			messageBox('Disable User ID <strong>['+ userID +']</strong>?','Confirm','confirm',function() {
				requests.push(
					$.ajax({
						type: 'POST',
						url: 'security/users/disable',
						data: {
							'userID': userID
						},
						dataType: 'json',
						beforeSend: function() {
							waitMessage('Disabling User ID <strong>['+ userID +']</strong>...');
						},
						success: function(data) {
							if (data['disabled'] === true) {
								messageBox('User ID <strong>['+ userID +']</strong> has been disabled');
								oTable.fnUpdate(data['newStat'], oTable.fnGetPosition($('#' + userID)[0]), 2, false);
							}
						}
					})
				);
			});
		} else {
			messageBox('Please select a user from the list');
		}
		e.preventDefault();
	});
	
	$(enableBtn).click(function(e) {
		var userID = user.ID;
		if (userID) {			
			messageBox('Enable User ID <strong>['+ userID +']</strong>?','Confirm','confirm',function() {
				requests.push(
					$.ajax({
						type: 'POST',
						url: 'security/users/enable',
						data: {
							'userID': userID
						},
						dataType: 'json',
						beforeSend: function() {
							waitMessage('Enabling User ID <strong>['+ userID +']</strong>...');
						},
						success: function(data) {
							if (data['enabled'] === true) {
								messageBox('User ID <strong>['+ userID +']</strong> has been enabled');
								oTable.fnUpdate(data['newStat'], oTable.fnGetPosition($('#' + userID)[0]), 2, false);
							}
						}
					})
				);
			});
		} else {
			messageBox('Please select a user from the list');
		}
		e.preventDefault();
	});
	
	$(resetSettingsBtn).click(function(e) {
		var userID = user.ID;
		if (userID) {			
			messageBox('Reset User ID <strong>['+ userID +']</strong>?','Confirm','confirm',function() {
				requests.push(
					$.ajax({
						type: 'POST',
						url: 'security/users/resetSettings',
						data: {
							'userID': userID
						},
						dataType: 'json',
						beforeSend: function() {
							waitMessage('Resetting User ID <strong>['+ userID +']</strong>...');
						},
						success: function(data) {
							if (data['resetSettings'] === true) {
								messageBox('User ID <strong>['+ userID +']</strong> reset');
								oTable.fnUpdate(data['newStat'], oTable.fnGetPosition($('#' + userID)[0]), 2, false);
							}
						}
					})
				);
			});
		} else {
			messageBox('Please select a user from the list');
		}
		e.preventDefault();
	});
	
	$(resetPwdBtn).click(function(e) {
		var userID = user.ID;
		if (userID) {		
			messageBox('Reset password for User ID <strong>['+ userID +']</strong>?','Confirm','confirm',function() {
				requests.push(
					$.ajax({
						type: 'POST',
						url: 'security/users/resetPwd',
						data: {
							'userID': userID
						},
						dataType: 'json',
						beforeSend: function() {
							waitMessage('Resetting <strong>['+ userID +']</strong> password...');
						},
						success: function(data) {
							if (data['resetPwd'] === true) {
								messageBox('User ID <strong>['+ userID +']</strong> password reset');
								oTable.fnUpdate(data['newStat'], oTable.fnGetPosition($('#' + userID)[0]), 2, false);
							}
						}
					})
				);
			});
		} else {
			messageBox('Please select a user from the list');
		}
		e.preventDefault();
	});
	
	$(refreshBtn).click(function(e) {
		//display status indicator
		$(status).html(loading).show();	
		
		var a = $(TABS + '.active a');
		grpseqno = a.attr('grpseqno');
		index = a.attr('tableIndex');
		
		getData(grpseqno, index, 0);
		
		e.preventDefault();
	});
});
</script>