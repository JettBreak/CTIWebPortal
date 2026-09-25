<form id="accountAddForm" method="post">
    <input type="hidden" name="prptr" id="prptr" value="<?php echo $prptr; ?>"/>
    <input type="hidden" name="primary" id="primary" value="<?php echo $primary; ?>"/>
    <input type="hidden" name="prseqno" value="<?php echo $prseqno; ?>"/>
    <input type="hidden" name="pseqnoLink" id="pseqnoLink"/>
    <input type="hidden" name="prKey" id="prKey"/>
    <input type="hidden" name="accntType" id="accntType"/>
    <input type="hidden" name="accntCode" id="accntCode"/>
    <input type="hidden" name="accntDesc" id="accntDesc"/>
    <input type="hidden" name="issuer" id="issuer"/>
    <div id="accountAdd">
        <h1>Add Account to Card</h1>
        <div id="content"><span class="hint"> Enter Account Number then press Verify to Search <span style="margin-left:60px"><span class="red">*</span> - Required Fields</span></span>
            <div class="divider">
                <table width="100%">
                    <tr>
                        <td width="180">Card Number:</td>
                        <td><input type="text" name="cardNo" style="width:250px" value="<?php echo $cardBIN; ?>" readonly/></td>
                    </tr>
                    <tr>
                        <td>Customer Name:</td>
                        <td><input type="text" style="width:250px" value="<?php echo $custName; ?>" readonly/></td>
                    </tr>
                    <tr>
                        <td>Card Status:</td>
                        <td><input type="text"style="width:250px" value="<?php echo $cardStatus; ?>" readonly/></td>
                    </tr>
                    <tr>
                        <td>Card Type:</td>
                        <td><input type="text" style="width:250px" value="<?php echo $cardType; ?>" readonly/></td>
                    </tr>
                </table>
            </div>
            <div class="divider">
                <table width="100%">
                   	<tr>
                        <td width="180"><label for="accntNo">Account Number: <span class="red">*</span></label></td>
                        <td><input type="text" name="accntNo" id="accntNo" style="width:150px" placeholder="<?php echo $acctNoPlaceholder; ?>" class="validate[required]"/><button id="verifyBtn" value="card/accountadd/verify">Verify</button></td>
    
                    </tr>
                    <tr>
                        <td><label for="acctType">Account Type:</label></td>
                        <td><select name="acctType" id="acctType" style="width: 162px">
                                <?php echo html_entity_decode($accountTypes); ?>
                            </select></td>
                    </tr>
                    <tr>
                        <td><label for="authMode">Authorization Mode:</label></td>
                        <td><input type="text" name="authMode" id="authMode" style="width:250px" readonly/></td>
                    </tr>
                    <tr>
                        <td><label for="accntStatus">Account Status:</label></td>
                        <td><input type="text" id="accntStatus" style="width:250px" readonly/></td>
                    </tr>
                    <tr>
                        <td><label for="ship">Account Owner:</label></td>
                        <td><input type="text" id="ship" style="width:250px" readonly/></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    <div id="bottom">
    	<span class="buttons floatLeft">
            <button id="linkBtn" value="card/accountadd/link" disabled>Link Account to Card</button
            ><button id="backBtn">Back to Card Info</button>
        </span>
        <span class="buttons floatRight">
            <button type="reset">Reset</button
            ><button class="closebtn">Close</button>
        </span>
	</div>
</form>
<script>
$(function () {
    var verifyBtn = '#verifyBtn',
        linkBtn = '#linkBtn',
        backBtn = '#backBtn',
        resetBtn = 'button:reset',
		accountNo = '#accntNo',
        form = $('form');
	
    form.validationEngine({
        ajaxFormValidation: true,
        onBeforeAjaxFormValidation: function () {
            if ($(verifyBtn).attr('disabled') === false) {
                var msg = 'Verifying account number...'
            } else {
                var msg = 'Sending transaction...'
            }
            waitMessage(msg);
        },
        onAjaxFormComplete: function (a, b, data, d) {
            if (data.verified === true) {
				
				//populate fields
				function populateFields() {
					$(MSGBOX).dialog('close');
					
					var info = data.details;
					
					$('#accntType').val(info.accntType);
					$('#authMode').val(info.authMode);
					$('#accntStatus').val(info.accntStats);
					$('#ship').val(info.accntOwn);
					$('#prKey').val(info.prKey);
					$('#pseqnoLink').val(info.pseqnoLink);
					$('#accntDesc').val(info.accntDesc);
					$('#accntCode').val(info.accntCode);
					$('#issuer').val(info.issuer);
					$('#primary').val(info.primary);
					//end
					
					$(verifyBtn + ',#acctType').attr('disabled', true);
					$(linkBtn).removeAttr('disabled').focus();
				}
				
				if (data.isMaxAccount === true) {
					messageBoxV2(data.message, 'Warning', {
						OK: function () {
							populateFields();
						},
						Cancel: function () {
							$(resetBtn).trigger('click');
							$(this).dialog('close');
						}
					});
				} else {
					populateFields();
				}

				
            } else if (data.verified === false) {
                messageBox(data.message);
				$(MSGBOX).one('dialogbeforeclose', function () {
					$(accountNo).val('').focus();
				});
            }
            if (data.success === true) {
                messageBox(data.message);
				$(MSGBOX).one('dialogbeforeclose', function () {
					window.location.hash = 'card/accountlinking';
				});
            } else if (data.success === false) {
                messageBox(data.message);
				$(MSGBOX).one('dialogbeforeclose', function () {
					$(resetBtn).trigger('click');
				});
            }
        },
        scroll: false
    });
    form.validationEngine('attach');
	
    $(resetBtn).click(function () {
        $(verifyBtn + ',#acctType').removeAttr('disabled');
        $(linkBtn).attr('disabled', 'disabled');
		$(accountNo).focus();
    });
	
    $(backBtn).click(function () {
        window.location.hash = 'card/accountlinking';
        return false;
    });
	
    $(linkBtn).click(function (e) {
        e.preventDefault();
        showUserOverride();
    });
	
	/*$(account).focusout(function () {
		if (this.value !== '') {
			this.value = zeroPad($(this).attr('maxlength'), this.value);
		}
	});*/
	
	$.mask.definitions = {
		'P': '[0-9]',
		'S': '[0-9]',
		'C': '[0-9]',
		'X': '<?php echo $XFormat; ?>'
	};
	
	$(accountNo).focus().mask('<?php echo $mask; ?>');
	
	$('#acctType').change(function () {
		var selected = $(this).find('option:selected');
		var mask = selected.attr('mask');
		var xChar = selected.attr('xchar');
		var placeholder = selected.attr('inputph');
		
		$(accountNo)
			.mask(mask)
			.attr('placeholder', placeholder);
		$.mask.definitions['X'] = xChar;
	});
});
</script>