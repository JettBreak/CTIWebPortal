<style>
.box {
	overflow:auto;
	border:1px solid #333;
}
</style>
<form id="newEntryForm" method="post">
<input type="hidden" name="cifseqno" id="cifseqno" value="<?php echo $cifseqno; ?>"/>
    <div style="width:750px">
        <h1><?php echo $title; ?></h1>
        <div id="content">
            <table width="100%">
                <tr>
                	<td colspan="3">&nbsp;</td>
                	<td rowspan="7" width="300">
                    	Allowed Transactions:
                        <div style="height:190px;" class="box" id="allowedTrx">
                            <?php echo html_entity_decode($tranAllows); ?>
                        </div>
                    </td>
                </tr>
                <tr>
                	<td width="140"><label for="accntNo">Account Number:</label></td>
                    <td><input type="text" id="accntNo" style="width:200px" value="<?php echo $accountNo; ?>" readonly/></td>
                </tr>
                <tr>
                    <td><label for="accntType">Account Type:</label></td>
                    <td><input type="text" id="accntType" style="width:200px" value="<?php echo $accountDesc; ?>" readonly/></td>
                </tr>
                <tr>
                	<td width="140"><label for="branch">Branch:</label></td>
                    <td><!-- <input type="text" id="branch" style="width:200px" value="<?php //echo $branchName; ?>" readonly/> -->
                        <select id="branch" name="branch" style="width:212px">
                            <?php echo html_entity_decode($branches); ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td><label for="custName">Customer Name:</label></td>
                    <td>
                    	<input type="text" id="custName" style="width:250px;cursor:pointer" value="<?php echo $owner; ?>" readonly/>
                    </td>
                </tr>
                <tr>
                    <td><label for="authModex">Authorization Mode:</label></td>
                    <td><input type="text" id="authModex" style="width:200px" value="<?php echo $authMode; ?>" readonly/></td>
                </tr>
                <tr>
                    <td><label for="accntStatus">Account Status:</label></td>
                    <td><input type="text" id="accntStatus" style="width:200px" value="<?php echo $accountStat; ?>" readonly/></td>
                </tr>
                <tr>
                	<td colspan="2">&nbsp;</td>
                </tr>
            </table>
            <table class="dataTable">
            <thead>
                <tr>
                    <th>Card No.</th>
                    <th>CIF No.</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
            	<?php echo html_entity_decode($cardsLinked); ?>
            </tbody>
        </table>
        </div>
    </div>
    <div id="bottom">
        <span class="buttons floatLeft">
            <button id="submitBtn" value="<?php echo $formAction; ?>" <?php echo $allowUpdate; ?> >Update</button
            ><button id="searchBtn">Search</button
            ><button id="browseBtn">Browse</button
            ><button id="removeBtn" <?php echo $status; ?> value="<?php echo $accntDel; ?>">Remove</button>
		</span>
		<span class="buttons floatRight">
        	<button type="reset">Reset</button
            ><button class="closebtn">Close</button>
		</span>
	</div>
</form>
<style>
.dataTables_scrollBody {
	max-height: 200px !important;
}
</style>
<script>
$(function () {
    var submitBtn = '#submitBtn',
        resetBtn = 'button:reset',
        removeBtn = '#removeBtn',
		accountNo = '#accntNo',
        form = $('form');
	
    initSession('<?php echo $sessionExp; ?>');
    form.validationEngine({
        ajaxFormValidation: true,
        onBeforeAjaxFormValidation: function () {
            waitMessage('Updating Account...')
        },
        onAjaxFormComplete: function (a, b, data, d) {
			messageBox(data.message);
			$(MSGBOX).one('dialogbeforeclose', function () {
				if (data.success) {
                	window.location.hash = 'accounts/search';
					
				} else {
					$(resetBtn).trigger('click');
				}
			});
        },
        scroll: false
    });
    form.validationEngine('attach');
	
	oTable = $(DATATABLE).dataTable({
        bRetrieve: true,
        bJQueryUI: true,
        aaSorting: [],
        sScrollY: '100%',
        sScrollX: '100%',
        sPaginationType: 'full_numbers'
    });
    $('.dataTables_length').html('<strong>Cards Linked:</strong>').css({
		'font-size': '12px',
		'margin-top': '4px'
	});
	
	$('#browseBtn').click(function (e) {
		window.location.hash = 'accounts/browse';
		e.preventDefault();
	});

    if ($(submitBtn).attr('disabled')) {
        $(submitBtn).hide();
    }
	
    $(submitBtn).click(function (e) {
        if (form.validationEngine('validate') === true) {
            messageBox('Are all entries correct?', 'Confirm', 'confirm', function () {
				$(MSGBOX).dialog('close');
                showUserOverride();
            })
        }
        e.preventDefault();
    });

    $(removeBtn).hide();
	
    $(resetBtn).click(function () {
        $(accountNo).focus().val('');
    });
	
	
	$(removeBtn).click(function (e) {
		if (form.validationEngine('validate') === true) {
            messageBox('Delete account number <strong>[' + $(accntNo).val() + ']</strong>?', 'Confirm', 'confirm', function () {
				$(MSGBOX).dialog('close');
                showUserOverride();
            })
        }
		e.preventDefault();
	});
	
	$('#custName').bind('click', function () {
		$(this).attr('disabled');
		$(this).validationEngine('hidePrompt');
		searchCustomer();
		return false;
	});
	
	$('#searchBtn').click(function (e) {
		window.location.hash = 'accounts/search';
		e.preventDefault();
	});
});
</script>