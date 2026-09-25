<form id="allowsForm" method="post">
<div style="width:500px">
    <h1><?php echo $title; ?></h1>
    <div id="content">
    	<span class="hint floatRight"><span class="red">*</span> - All fields required</span>
    	<table>
        	<tr>
            	<td width="120"><label for="prtype">Product Type:</label></td>
                <td>
                    <select name="prtype" id="prtype" style="width:300px" class="validate[required]"<?php echo $disabled; ?>>
                        <?php echo html_entity_decode($prTypes); ?>
                    </select>
                </td>
            </tr>
            <tr>
            	<td><label for="bitNo">Bit No.</label></td>
                <td><input type="number" name="bitNo" id="bitNo" style="width:100px" class="validate[required,custom[integer],min[0],max[64]] numbersOnly" value="<?php echo $bitNo; ?>" step="1" min="0" max="64"/></td>
            </tr>
            <tr>
            	<td><label for="transaction">Transaction:</label></td>
                <td>
                    <select name="transaction" id="transaction" style="width:300px" class="validate[required]">
                        <?php echo html_entity_decode($transaction); ?>
                    </select>
                </td>
            </tr>
        </table>
    </div>
</div>
<div id="bottom">
	<span class="buttons floatLeft">
    	<button id="submitBtn" value="<?php echo $formAction; ?>">Submit</button
        >
    </span>
	<span class="buttons floatRight">
    	<button id="backBtn">Back</button
        ><button class="closebtn">Close</button>
    </span>
</div>
</form>
<script>
$(function() {
	var form = $('form');
	var prtype = {
		'CARD': '',
		'ACCT': '',
		'CELL': ''
	};
	<?php echo html_entity_decode($js); ?>
	
    initSession('<?php echo $sessionExp; ?>');
	form.validationEngine({
        ajaxFormValidation: true,
        onBeforeAjaxFormValidation: function () {
            waitMessage('<?php echo $waitMsg; ?>');
        },
        onAjaxFormComplete: function (a, b, data, d) {
			//redirect
			messageBox(data.message);
            if (data.success === true) {
				$(MSGBOX).one('dialogbeforeclose', function () {
					window.location.hash = 'maintenance/allowssetup';
				});
            }
        },
        scroll: false
    });
	
	$('#prtype').change(function () {
		$('#transaction').html(prtype[this.value]);
	});
	
	$('#backBtn').click(function (e) {
		window.location.hash = 'maintenance/allowssetup';
		e.preventDefault();
	});
	
	$('#submitBtn').click(function (e) {
		e.preventDefault();
		if (form.validationEngine('validate')) {
            messageBox('<?php echo $submitBtnMsg; ?>', 'Confirm', 'confirm', function () {
                form.submit();
            })
        }
	});
});
</script>