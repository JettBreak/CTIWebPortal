<form id="serviceCodeForm" method="post">
<div style="width:500px">
    <h1><?php echo $title; ?></h1>
    <div id="content">
    	<span class="hint floatRight">All fields required</span>
    	<table>
        	<tr>
            	<td><label for="chargeType">Charge Type:</label></td>
                <td>
                    <select name="chargeType" id="chargeType" style="width:212px"<?php echo $chargeTypeAttr; ?>>
                        <?php echo html_entity_decode($chargeTypes); ?>
                    </select>
                </td>
            </tr>
        	<tr>
            	<td width="120"><label for="trxcode">Code:</label></td>
                <td><input type="number" name="trxcode" id="trxcode" style="width:100px" class="validate[required]" value="<?php echo $trxcode; ?>" step="1" min="10000" max="19999" maxlength="5"<?php echo $trxcodeAttr; ?>/></td>
            </tr>
            <tr>
            	<td><label for="desc">Description:</label></td>
                <td><input type="text" name="desc" id="desc" style="width:200px" class="validate[required]" value="<?php echo $description ;?>" maxlength="50" autofocus/></td>
            </tr>
            <tr>
            	<td><label for="mnemonic">Mnemonic:</label></td>
                <td><input type="text" name="mnemonic" id="mnemonic" style="width:200px" class="validate[required] upperCase" value="<?php echo $mnemonic; ?>" maxlength="10"/></td>
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
					window.location.hash = 'maintenance/servicecodes';
				});
            }
        },
        scroll: false
    });
	
	$('#backBtn').click(function (e) {
		window.location.hash = 'maintenance/servicecodes';
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