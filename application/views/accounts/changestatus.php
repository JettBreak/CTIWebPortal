<style>
.box {
	overflow:auto;
	border:1px solid #333;
}
</style>
<form id="newEntryForm" method="post">
<input type="hidden" name="cifseqno" id="cifseqno" value="<?php echo $cifseqno; ?>"/>
    <div style="width:450px">
        <h1><?php echo $title; ?></h1>
        <div id="content">
            <table width="100%">
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
                    <td><input type="text" id="branch" style="width:200px" value="<?php echo $branchName; ?>" readonly/></td>
                </tr>
                <tr>
                    <td><label for="custName">Customer Name:</label></td>
                    <td>
                    	<input type="text" id="custName" style="width:250px;" value="<?php echo $owner; ?>" readonly/>
                    </td>
                </tr>
                <tr>
                    <td><label for="authModex">Authorization Mode:</label></td>
                    <td><input type="text" id="authModex" style="width:200px" value="<?php echo $authMode; ?>" readonly/></td>
                </tr>
                <tr>
                    <td><label for="accntStatus">Account Status:</label></td>
                    <td><?php echo html_entity_decode($accntStatus); ?></td>
                </tr>
            </table>
        </div>
    </div>
    <div id="bottom">
        <span class="buttons floatLeft">
            <button id="submitBtn" value="<?php echo $formAction; ?>" <?php echo $isReadOnly; ?>>Update</button
            ><button id="searchBtn">Search</button>
		</span>
		<span class="buttons floatRight">
        	<button type="reset">Reset</button
            ><button class="closebtn">Close</button>
		</span>
	</div>
</form>
<script>
$(function () {
    var submitBtn = '#submitBtn',
        resetBtn = 'button:reset',
		accountNo = '#accntNo',
        form = $('form');
    initSession('<?php echo $sessionExp; ?>');
	
    form.validationEngine({
        ajaxFormValidation: true,
        onBeforeAjaxFormValidation: function () {
            waitMessage('Creating Account...')
        },
        onAjaxFormComplete: function (a, b, data, d) {
			messageBox(data.message);
			$(MSGBOX).one('dialogbeforeclose', function () {
				if (data.success) {
                	window.location.hash = 'accounts/search2';
				} else {
					$(resetBtn).trigger('click');
				}
			});
        },
        scroll: false
    });
    form.validationEngine('attach');
	
    $(submitBtn).click(function (e) {
        if (form.validationEngine('validate') === true) {
            messageBox('Are all entries correct?', 'Confirm', 'confirm', function () {
				$(MSGBOX).dialog('close');
                showUserOverride();
            })
        }
        e.preventDefault();
    });
	
    $(resetBtn).click(function () {
        $(accountNo).focus().val('');
    });
	
	/*$('#custName').bind('click', function () {
		$(this).attr('disabled');
		$(this).validationEngine('hidePrompt');
		searchCustomer();
		return false;
	});*/
	
	$('#searchBtn').click(function (e) {
		window.location.hash = 'accounts/search2';
		e.preventDefault();
	});
});
</script>