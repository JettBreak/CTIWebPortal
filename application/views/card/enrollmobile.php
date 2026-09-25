<form id="enrollMobileForm" method="post">
    <div id="enrollMobile">
        <h1>Enroll Mobile</h1>
        <div id="content"><span class="hint">Enter Mobile Phone Number then press Link Mobile<span style="margin-left:30px"><span class="red">*</span> - Required Fields</span></span><br/>
            <br/>
            <table width="100%">
                <tr>
                    <td width="35%"><label for="cardBIN">Card Number:</label></td>
                    <td><input type="text" name="cardBIN" id="cardBIN" style="width:250px" value="<?php echo $cardBIN; ?>" readonly/></td>
                </tr>
                <tr>
                    <td><label for="custName">Customer Name:</label></td>
                    <td><input type="text" name="custName" id="custName" style="width:250px" value="<?php echo $custName; ?>" readonly/></td>
                </tr>
                <tr>
                    <td><label for="cardStatus">Card Status:</label></td>
                    <td><input type="text" name="cardStatus" id="cardStatus" style="width:250px" value="<?php echo $cardStatus; ?>" readonly/></td>
                </tr>
                <tr>
                    <td><label for="cardType">Card Type:</label></td>
                    <td><input type="text" name="cardType" id="cardType" style="width:250px" value="<?php echo $cardType; ?>" readonly/></td>
                </tr>
            </table>
            <table width="100%" class="divider">
                <tr>
                    <td width="35%"><label for="custMobile">Mobile Number: <span class="red">*</span></label></td>
                    <td><select name="custAreaCodeMobile" id="custAreaCodeMobile" style="width:65px">
                            <?php echo html_entity_decode($cellBIN); ?>
                        </select>-<input type="text" name="custMobile" id="custMobile" style="width:120px" maxlength="7" class="validate[required,custom[onlyNumberSp]] numbersOnly" autofocus/></td>
                </tr>
                <tr>
                	<td colspan="2">             
                    <input type="checkbox" id="notifyUser" name="notifyUser" checked/> <label for="notifyUser">Notify User</label>
                    </td>
                </tr>
                <tr>
                	<td style="vertical-align:top !important" width="120"><label for="notifyMsg">Notification Message:</label></td>
                    <td><textarea id="notifyMsg" name="notifyMsg" style="width:250px; height:60px"><?php echo $defaultMsg; ?></textarea></td>
                </tr>
                <tr>
                	<td colspan="2">
                     	Allowed Transactions:
                        <div style="height:126px;" class="box" id="allowedTrx">
                            <?php echo html_entity_decode($tranAllows); ?>
                        </div>
                     </td>
                </tr>
            </table>
        </div>
    </div>
    <div id="bottom">
    	<span class="buttons floatLeft">
            <button id="linkBtn" value="card/enrollmobile/link">Link Mobile</button
            ><button id="backBtn">Back to Card Info</button>
        </span><span class="buttons floatRight">
            <button type="reset">Clear</button
            ><button class="closebtn">Close</button>
        </span>
	</div>
</form>
<style>
label[disabled] {
	color: #aaa;
}
input[disabled], textarea[disabled] {
	border: 1px solid #000;
}
input[type="checkbox"] {
	position: relative;
	top: 2px;
}
.box {
	overflow:auto;
	border:1px solid #333;
}
</style>
<script>
$(function () {
    var linkBtn = '#linkBtn',
        backBtn = '#backBtn',
        resetBtn = 'button:reset',
        form = $('form');
		
    form.validationEngine({
        ajaxFormValidation: true,
        onBeforeAjaxFormValidation: function () {
            waitMessage('Sending transaction...')
        },
        onAjaxFormComplete: function (a, b, data, d) {
			messageBox(data.message);
			$(MSGBOX).one('dialogbeforeclose', function () {
				
				var url = '';
				
				switch (data.errno) {
					case 1:
						url = 'card/mobiledetail';
						break;
					case 2:
						url = 'card/mobileenrollment';
						break;
					default:
						url = 'card/mobileenrollment';
						break;
				}
				
				window.location.hash = url;
			});
        },
        scroll: false
    });
    form.validationEngine('attach');
	
    $(resetBtn).click(function () {
        $('#custMobile').focus()
    });
	
    $(backBtn).click(function (e) {
        window.location.hash = 'card/mobileenrollment';
		e.preventDefault();
    });
	
    $(linkBtn).click(function (e) {
        if (form.validationEngine('validate') === true) {
            messageBox('Are all entries correct?', 'Confirm', 'confirm', function () {
                $(MSGBOX).dialog('close');
                showUserOverride();
            });
        }
		e.preventDefault();
    });
	
	$('#notifyUser').change(function () {
		
		var val = $(this).is(':checked');
		
		if (val) {
			val = false;
		} else {
			val = true;
		}
		
		$('#notifyMsg, label[for="notifyMsg"]').attr({
			disabled: val
		});
	});
});
</script>