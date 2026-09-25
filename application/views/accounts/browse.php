<div style="width:900px">
	<h1>Accounts List</h1>
    <table class="dataTable">
        <thead>
            <tr>
                <th width="120">Account No.</th>
                <th>Customer Name</th>
                <th width="100">Account Type</th>
                <th width="120">Last Activity</th>
                <th width="150">Status</th>
                <th>accttype</th>
                <th>acctstat</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
</div>
<div id="bottom">
	<span class="buttons floatLeft">
    	<button id="enrollBtn">New Account</button
        ><button id="viewBtn" disabled>View/Update Account</button
        ><!--<button id="removeBtn" disabled>Remove</button
        >-->
	</span>
    <span class="buttons floatRight">
    	<button id="refreshBtn">Refresh</button
        ><button class="closebtn">Close</button>
	</span>
</div>
<style>
.dataTables_scrollBody {
	min-height: 200px !important;
	max-height: 250px !important;
}
.dataTables_filter input {
	width: 150px;
}
.dataTables_custom {
	padding: 4px 0;
}
</style>

<script>
$(function() {
	//waitMessage('Retrieving Card List...');
	
	var viewBtn = '#viewBtn',
		enrollBtn = '#enrollBtn',
        /*removeBtn = '#removeBtn',*/
		refreshBtn	= '#refreshBtn',
		acct = {};
	
	var buttons = viewBtn;
    initSession('<?php echo $sessionExp; ?>');
	
	var oTable = $(DATATABLE).dataTable({
		bRetrieve:true,
		bJQueryUI: true,
		//'bProcessing': true,
		bServerSide: true,
		sAjaxSource: 'accounts/browse/getdata',
		sScrollY: '100%',
		sScrollX: '100%', // Required for viewing tables with lots of columns at low resolution - otherwise columns are mis-aligned
		sPaginationType: 'full_numbers', 
		aoColumns: ([
			null,//user.ID
			null,//userName
			null,//branch
			null,//group
			null,//status
			{'bVisible': false }, //accttype
			{'bVisible': false } //acctstat
			//null,//last pwd change
			//null,//dtcreated
			//{'bVisible': false },//grpseqno
			//{'bVisible': false },//position
			//{'bVisible': false },//xml1
			//{'bVisible': false },//department
			//{'bVisible': false },//tmseqno
			//{'bVisible': false },//allows
			//{'bVisible': false },//brseqno
			//{'bVisible': false } //enable disable
		]),
		fnInitComplete: function() {	
			//getData();
			//$(MSGBOX).dialog('close');
		},
		fnRowCallback: function(nRow, aData, iDisplayIndex) {
			$('td:eq(0)', nRow).attr('align', 'center');
			$(nRow).attr('id', aData[0]).unbind('click dblclick').click(function () {
				//close dialog boxes
				if ($(MSGBOX).length > 0) {
					$(MSGBOX).dialog('close');
				}
				
				//highlight rows
				$('tbody tr').removeClass('rowSelected');
                $(this).addClass('rowSelected');
				
				//set temp var
				acct = {
					number: aData[0],
					accntType: aData[5]
				}
				
                $(buttons).removeAttr('disabled')
				/*if(aData[6] == 8) {
                	$(removeBtn).removeAttr('disabled')
				} else {
            		$(removeBtn).attr('disabled', 'disabled')
				}*/
            }).dblclick(function () {
                $(viewBtn).trigger('click');
            });
			//$('tbody tr').removeClass('rowSelected');
            
			$(buttons).attr('disabled', 'disabled');
            /*$(removeBtn).attr('disabled', 'disabled')*/
			
			//unset temp obj
			//acct = {};
			return nRow;
		},
		fnDrawCallback: function () {
			$('.dataTables_filter input').focus();
			//$('tbody tr').removeClass('rowSelected');
		},
		fnServerData: function ( sSource, aoData, fnCallback ) {
			/* Add some extra data to the sender */
			aoData.push( { 
				name: 'acctType',
				value: $('#acctType').val() ? $('#acctType').val() : 0
			} );
			
			requests.push(
				jqxhr = $.ajax( {
					url: sSource,
					data: aoData,
					dataType: 'json',
					cache: false,
					beforeSend: function () {
						abortAJAXRequests();
						waitMessage('Retrieving data...');
					},
					success: function(data) {
						fnCallback(data)
					},
					error: function (xhr, error, thrown) {
						if ( error == "parsererror" ) {
							alert( "DataTables warning: JSON data from server could not be parsed. "+
								"This is caused by a JSON formatting error." );
						}
					},
					complete: function () {
						$(MSGBOX).dialog('close');
					}
				} )
			);
		}
	});
	$.fn.dataTableExt.iApiIndex = 0;
	
	$('.ui-toolbar:first').append('<div class="dataTables_custom floatLeft" style="margin: 0 0 0 20px">'+
		'Account Type: ' +
		'<select id="acctType" style="width:170px">'+
			'<option value="0">ALL</option>' +
			<?php echo html_entity_decode($accountTypeList); ?>
		'</select>' +
	'</div>');
	
	$('#acctType').change(function () {
		oTable.fnDraw();
	})
	/*jqxhr.always(function () {
		
	}).done(function () {
		$(MSGBOX).dialog('close');
	});*/
	
	$(viewBtn).click(function (e) {
		if (acct) {
			//serialize AJAX request
			requests.push(
				$.ajax({
					type: 'POST',
					url: 'accounts/browse/cache',
					data: acct,
					dataType: 'json',
					success: function(data) {
						if (data.success === true) {
							window.location.hash = 'accounts/editinfo';
						}
					}
				})
			);
        } else {
            messageBox('Please select an item from the list')
        }
        e.preventDefault();
	});
	
	/*$(removeBtn).click(function (e) {    // remove area
		if (acct) {
			messageBox('Delete account number <strong>[' + $(accntNo).val() + ']</strong>?', 'Confirm', 'confirm', function () {
				$(MSGBOX).dialog('close');
                showUserOverride();
            })
           messageBox('Delete account number <strong>[' + acct.number + ']</strong>?', 'Confirm', 'confirm', function () {
				//serialize AJAX request
				requests.push(
					$.ajax({
						type: 'POST',
						url: 'accounts/browse/delete',
						data: acct,
						dataType: 'json',
						beforeSend: function() {
							waitMessage('Removing account entry...')
						},
						success: function(data) {
							if (data.success === true) {
								oTable.fnDeleteRow(oTable.fnGetPosition($('#' + acct.number)[0]));
								messageBox('Account <strong>['+ acct.number +']</strong> removed successfully');
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
    });*/
	
	/*$(removeBtn).click(function (e) {
		if (form.validationEngine('validate') === true) {
            messageBox('Delete account number <strong>[' + $(accntNo).val() + ']</strong>?', 'Confirm', 'confirm', function () {
				$(MSGBOX).dialog('close');
                showUserOverride();
            })
        }
		e.preventDefault();
	});*/
	
	$(enrollBtn).click(function (e) {
		window.location.hash = 'accounts/newentry';
		e.preventDefault();
	});
	
	$(refreshBtn).click(function (e) {
		oTable.fnDraw();
		e.preventDefault();
		//getData();
		//return false
	});
});
</script>