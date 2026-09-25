<form id="cardResetPINForm" method="post">
    <input type="hidden" name="prseqno" value="<?php echo $prseqno; ?>"/>
    <div style="width:480px">
        <h1>Card Reset PIN</h1>
        <div id="content">
            <table>
                <tr>
                    <td width="170">Card Number:</td>
                    <td><input type="text" name="prkey" style="width:290px" value="<?php echo $cardNo; ?>" readonly/></td>
                </tr>
                <tr>
                    <td>Customer Name:</td>
                    <td><input type="text" style="width:290px" value="<?php echo $custName; ?>" readonly/></td>
                </tr>
                <tr>
                    <td>Card Status:</td>
                    <td><input type="text" style="width:290px" value="<?php echo $cardStatus; ?>" readonly/></td>
                </tr>
                <tr>
                    <td>Card Type:</td>
                    <td><input type="text" style="width:290px" value="<?php echo $cardType; ?>" readonly/></td>
                </tr>
                <tr>
                    <td>Primary Account:</td>
                    <td><input type="text" style="width:290px" value="<?php echo $primary; ?>" readonly/></td>
                </tr>
            </table>
        </div>
    </div>
    <div id="bottom">
        <span class="buttons floatLeft">
            <button id="submitBtn" value="card/resetpin/process">Reset PIN</button>
        </span>
        <span class="buttons floatRight">
            <button class="closebtn">Close</button>
        </span>
    </div>
</form>
<script>
$(function () {
    var submitBtn = '#submitBtn';
    var form = $('form');
	
    $(submitBtn).click(function (e) {
        messageBox('Reset current PIN?', 'Confirm', 'confirm', function () {
			$(MSGBOX).dialog('close');
            showUserOverride();
        });
        e.preventDefault()
    });
	
	form.submit(function (e) {
		requests.push(
			$.ajax({
				url: form.attr('action'),
				type: form.attr('method'),
				dataType: 'json',
				data: form.serialize(),
				beforeSend: function () {
					abortAJAXRequests();
					waitMessage('Processing...');
				},
				error: function () {
		
				},
				success: function (data) {
					messageBox(data.message);
				},
				complete: function () {
				}
			})
		);
		e.preventDefault();
	});
});
</script>