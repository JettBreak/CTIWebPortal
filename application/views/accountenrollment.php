<form id="accountEnrollmentForm" method="post">
    <input type="hidden" name="prseqno" value="<?php echo $prseqno; ?>"/>
    <input type="hidden" name="prptr" id="prptr"/>
    <input type="hidden" name="pseqnoLink" id="pseqnoLink"/>
    
    <input type="hidden" name="accntDesc" id="accntDesc"/>
    <input type="hidden" name="prKey" id="prKey"/>
    <div id="accountEnrollment">
        <h1>Account Enrollment</h1>
        <div id="content">
            <table width="100%">
                <tr>
                    <td width="35%">Card Number:</td>
                    <td><input type="text" name="cardBIN" style="width:250px" value="<?php echo $cardBIN; ?>" readonly/></td>
                </tr>
                <tr>
                    <td>Customer Name:</td>
                    <td><input type="text" style="width:250px" value="<?php echo $custName; ?>" readonly/></td>
                </tr>
                <tr>
                    <td>Card Status:</td>
                    <td><input type="text" style="width:250px" value="<?php echo $cardStatus; ?>" readonly/></td>
                </tr>
                <tr>
                    <td>Card Type:</td>
                    <td><input type="text" style="width:250px" value="<?php echo $cardType; ?>" readonly/></td>
                </tr>
            </table>
        </div>
        <table class="dataTable">
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Account Type</th>
                    <th>Account No.</th>
                    <th>Authorization Type</th>
                    <th>Primary</th>
                    <th>pseqnoLink</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
    <div id="bottom">
    	<span class="buttons floatLeft">
            <button id="addBtn" disabled>Add Account</button
            ><button id="removeBtn" value="card/accountenrollment/remove" disabled>Remove Account</button>
        </span>
        <span class="buttons floatRight">
            <button id="refreshBtn">Refresh</button
            ><button class="closebtn">Close</button>
        </span>
	</div>
</form>
<style>
.dataTables_scrollBody{min-height:50px !important;max-height:150px !important;}
</style>
<script>
$(function () {
    var $addBtn = '#addBtn',
        $removeBtn = '#removeBtn',
        $refreshBtn = '#refreshBtn',
		$accntDesc = '#accntDesc',
		$prKey = '#prKey',
        $form = $('form');
    $(DATATABLE).find('tbody tr').die('dblclick');
    oTable = $(DATATABLE).dataTable({
        'bRetrieve': true,
        'bJQueryUI': true,
        'aaSorting': [],
        'sScrollY': '100%',
        'sScrollX': '100%',
        'sPaginationType': 'full_numbers',
        'fnInitComplete': function () {
			//this.fnAdjustColumnSizing(); //fix misaligned columns
            $($addBtn).removeAttr('disabled')
        },
        'fnRowCallback': function (a, b, c) {
            $('td:eq(0), td:eq(4)', a).attr('align', 'center');
			$(a).unbind('click').click(function() {			
				var aPos = oTable.fnGetPosition(a);
				var $tblIndex = oTable.fnGetData(aPos);
				$($prKey).val($tblIndex[2]); //account number
				$($accntDesc).val($tblIndex[1]); //account desc
			});
            return a
        },
        'fnDrawCallback': function () {
            $(DATATABLE).find('tbody tr').click(function () {
                var a = $(this).find('td:first').text(),
                    $link = oTable.fnGetData($(this).index())[5];
                $('tbody tr').removeClass('rowSelected');
                $(this).addClass('rowSelected');
                $('#prptr').val(a);
                $('#pseqnoLink').val($link);
                $($removeBtn).removeAttr('disabled')
            });
            $($removeBtn).attr('disabled', 'disabled')
        }
    });
    $('.dataTables_length').html('<strong>Accounts Linked:</strong>').css('font-size', '12px');
	
    $.fn.dataTableExt.iApiIndex = 0;
    oTable.fnSetColumnVis(5, false);
	
    $form.validationEngine({
        ajaxFormValidation: true,
        onBeforeAjaxFormValidation: function () {
            waitMessage('Removing account link...')
        },
        onAjaxFormComplete: function (a, b, c, d) {
			if (c['removed'] === true) {
				messageBox('Account number: <strong>[' + $($prKey).val() + ']</strong> successfully removed');
				$($prKey).val('');
				$($accntDesc).val('');
			}
			getData(true);
        },
        scroll: false
    });
    $form.validationEngine('attach');
    $($addBtn).click(function () {
        window.location.hash = 'card/accountadd';
        return false
    });
    $($removeBtn).click(function (e) {
        if (!$('#prptr').val() || !$('#pseqnoLink').val()) {
            messageBox('Please select an account.', 'Error')
        } else {
            messageBox('Do you want to remove account <strong>[' + $($prKey).val() + ']</strong> from this card?', 'Confirm link removal', 'confirm', function () {
                $(MSGBOX).dialog('close');
                showUserOverride()
            })
        }
        e.preventDefault()
    });
    $($refreshBtn).click(function (e) {
		getData();
        e.preventDefault();
    })
	getData();
});
function getData(isRemove)
{
	abortAJAXRequests();
	requests.push(
		$.ajax({
			url: 'card/accountenrollment/getdata',
			type: 'GET',
			dataType: 'json',
			beforeSend: function () {
				if (isRemove !== true) {
					waitMessage('Retrieving accounts linked...');
				}
			},
			error: function () {
	
			},
			success: function (a) {	
				oTable.fnClearTable(0);
				oTable.fnAddData(a['aaData']);
				oTable.fnDraw();
				oTable.fnAdjustColumnSizing();
			},
			complete: function () {
				if (isRemove !== true) {
					$(MSGBOX).dialog('close');
				}
			}
		})
	)
}
</script>