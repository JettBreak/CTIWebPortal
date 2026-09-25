<div id="issueList">
    <h1>Issue List. Terminal: <?php echo $termCode; ?></h1>
    <table class="dataTable">
        <thead>
            <tr>
            	<th>No.</th>
                <th width="100">Report Date</th>
                <th>Hardware Problem/Issue</th>
                <th>Date Resolved</th>
                <th>Reported By</th>
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
        ><button id="backBtn">Back</button
        ><button class="closebtn">Close</button>
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
		backBtn = '#backBtn',
        refreshBtn = '#refreshBtn',
		issue = null;
		
	var buttons = modifyBtn + ',' + removeBtn;
	
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
				
                issue = {
					seqno: aData[0]
				};
				
				//check user
				if (aData[4] === '<?php echo $userID; ?>') {
					$(buttons).removeAttr('disabled');
				}
            }).dblclick(function () {
				if (aData[4] === '<?php echo $userID; ?>') {
                	$(modifyBtn).trigger('click');
				} else {
					//message
				}
            });
            $(buttons).attr('disabled', 'disabled');
			
			tCode = null;
            return nRow;
        }
    });
	
    $(newBtn).click(function () {
        window.location.hash = 'maintenance/issuelognew/<?php echo $termCode; ?>';
        return false;
    });
	
    $(modifyBtn).click(function () {
		if (issue) {
            window.location.hash = 'maintenance/issuelogedit/' + issue.seqno;
        } else {
            messageBox('Please select an item from the list')
        }
        return false;
    });
	
    $(removeBtn).click(function (e) {
		if (issue) {
            messageBox('Remove Issue No. <strong>[' + issue.seqno + ']</strong>?', 'Confirm', 'confirm', function () {
                requests.push(
					$.ajax({
						type: 'POST',
						url: 'maintenance/issuelist/remove',
						data: {
							issueseqno: issue.seqno,
							termcode: '<?php echo $termCode; ?>'
						},
						dataType: 'json',
						beforeSend: function() {
							waitMessage('Removing Issue...');
						},
						success: function(data) {
							if (data.success === true) {
								messageBox('Issue No. <strong>['+ issue.seqno +']</strong> removed successfully');
								oTable.fnDeleteRow(oTable.fnGetPosition($('#' + issue.seqno)[0]));
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
		return false;
    });
	
	$(backBtn).click(function () {
        window.location.hash = 'maintenance/atm';
        return false;
	});
	
	function getData() {
		//serialize AJAX request
		requests.push(
			jqxhr = $.ajax({
				type: 'GET',
				url: 'maintenance/issuelist/getdata',
				dataType: 'json',
				data: {
					termCode: '<?php echo $termCode; ?>'
				},
				beforeSend: function() {
					abortAJAXRequests();
					waitMessage('Retrieving Issue list...');
				},
				error: function(jqXHR, textStatus, errorThrown) {

				},
				success: function(data) {
					if (data.success === true) {
						$.fn.dataTableExt.iApiIndex = 0;
						oTable.fnClearTable(0);
						oTable.fnAddData(data['details']);
						oTable.fnDraw();
						//oTable.fnAdjustColumnSizing();
					} else {
						messageBox('An error has occured');
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