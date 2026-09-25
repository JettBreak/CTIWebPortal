<form id="custNewForm" method="post" enctype="multipart/form-data" target="uploadTarget">
    <div id="custNew">
        <h1>New Customer Entry</h1>
        <div id="content"><span class="floatRight hint">Enter customer details then press submit to send<br /><span class="floatRight"><span class="red">*</span> - Required Fields</span></span></span>
            <div id="custPreview" class="imgWrapper floatLeft"><img src="images/nullphoto.jpg" width="100" height="100" alt="Customer picture"/>
                <div class="uploader"><span>Upload Photo</span>
                    <input type="file" id="custImage" name="custImage"/>
                </div>
            </div>
            <strong>Customer Information:</strong>
            <table width="80%" style="margin-top:10px">
                <tr>
                    <td><label>Prefix:</label></td>
                    <td><label for="custLastName">Last Name: <span class="red">*</span></label></td>
                    <td><label for="custFirstName">First Name: <span class="red">*</span></label></td>
                    <td><label for="custMiddleName">Middle Name:</label></td>
                    <td><label for="custSuffix">Suffix:</label></td>
                </tr>
                <tr>
                    <td><select name="custPrefix" id="custPrefix" style="width:70px">
                            <?php echo html_entity_decode($prefixes); ?>
                        </select></td>
                    <td><input type="text" name="custLastName" id="custLastName" style="width:122px" maxlength="30" class="validate[required,custom[onlyLetterSpCustom]] alphaNumCustom"/></td>
                    <td><input type="text" name="custFirstName" id="custFirstName" style="width:122px" maxlength="30" class="validate[required,custom[onlyLetterSpCustom]] alphaNumCustom"/></td>
                    <td><input type="text" name="custMiddleName" id="custMiddleName" style="width:122px" maxlength="30" class="validate[custom[onlyLetterSpCustom]] alphaNumCustom"/></td>
                    <td><input type="text" name="custSuffix" id="custSuffix" style="width:40px" class="lettersOnly" maxlength="4"/></td>
                </tr>
            </table>
            <table width="100%" class="divider">
                <tr>
                    <td width="110"><label>Gender:</label></td>
                    <td><?php echo html_entity_decode($gender); ?></td>
                    <td><label for="custNationality">Nationality:</label></td>
                    <td><input type="text" name="custNationality" id="custNationality" style="width:150px" class="lettersOnly" maxlength="20"/></td>
                </tr>
                <tr>
                    <td><label for="custCivilStatus">Civil Status:</label></td>
                    <td><select name="custCivilStatus" id="custCivilStatus" style="width:152px">
                            <?php echo html_entity_decode($civilStats); ?>
                        </select></td>
                    <td><label for="custOccupation">Occupation:</label></td>
                    <td><input type="text" name="custOccupation" id="custOccupation" style="width:150px" class="lettersOnly" maxlength="30"/></td>
                </tr>
                <tr>
                    <td><label for="custBDate">Date of birth:</label></td>
                    <td><input type="text" name="custBDate" id="custBDate" style="width:140px" class="datePicker" readonly/></td>
                    <td><label for="custSSS">SSS:</label></td>
                    <td><input type="text" name="custSSS" id="custSSS" style="width:150px" class="numbersOnly" maxlength="30"/></td>
                </tr>
                <tr>
                    <td><label for="custBPlace">Place of birth:</label></td>
                    <td><input type="text" name="custBPlace" id="custBPlace" style="width:140px" class="alphaNum" maxlength="30"/></td>
                    <td><label for="custTIN">TIN:</label></td>
                    <td><input type="text" name="custTIN" id="custTIN" style="width:150px" class="numbersOnly" maxlength="30"/></td>
                </tr>
            </table>
            <div class="divider customer-address-contact">
                <table class="customer-address-table">
                    <tr>
                        <td width="110"><label for="custCountry">Country of Origin:</label></td>
                        <td colspan="3"><select name="custCountry" id="custCountry" style="width:232px">
                                <?php echo html_entity_decode($countries); ?>
                            </select></td>
                    </tr>
                    <tr>
                        <td><label for="custAddressType">Address Type:</label></td>
                        <td colspan="3"><select name="custAddressType" id="custAddressType" style="width:120px">
                                <?php echo html_entity_decode($addrTypes); ?>
                            </select></td>
                    </tr>
                    <tr>
                        <td><label for="custStreet1">Street 1: <span class="red">*</span></label></td>
                        <td colspan="3"><input type="text" name="custStreet1" id="custStreet1" style="width:220px" maxlength="50" class="validate[required]"/></td>
                    </tr>
                    <tr>
                        <td><label for="custStreet2">Street 2:</label></td>
                        <td colspan="3"><input type="text" name="custStreet2" id="custStreet2" style="width:220px" maxlength="50"/></td>
                    </tr>
                    <tr>
                        <td><label for="custCity">Town / City: <span class="red">*</span></label></td>
                        <td width="110"><input type="text" name="custCity" id="custCity" style="width:85px" class="validate[required]" maxlength="30"/></td>
                        <td width="70"><label for="custZipCode">ZIP Code: <span class="red">*</span></label></td>
                        <td><input type="text" name="custZipCode" id="custZipCode" class="validate[required,custom[onlyNumberSp]] numbersOnly" style="width:32px" maxlength="4"/></td>
                    </tr>
                    <tr>
                        <td><label for="custProvince">Province: <span class="red">*</span></label></td>
                        <td colspan="3"><input type="text" name="custProvince" id="custProvince" style="width:220px" class="validate[required]" maxlength="30"/></td>
                    </tr>
                    <tr>
                    	<td colspan="4">
                            	<span class="hint" style="white-space:nowrap;clear:both"><strong>Note:</strong> At least one contact number is required for registration. </span>
                        </td>
                    </tr>
                </table>
                <div class="customer-contact-section">
                <strong>Contact Numbers:</strong>
                <table>
                    <tr>
                        <td>&nbsp;</td>
                        <td>Area Code &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Phone No.</td>
                    </tr>
                    <tr>
                        <td width="10%"><label for="custHomePhone">Home:</label></td>
                        <td><input type="text" name="custHomePhone" id="custHomePhone" style="width:160px" maxlength="12" class="validate[custom[onlyNumberSp],minSize[7]] numbersOnly"/></td>
                        <td><input type="checkbox" name="custHomeNotify" id="custHomeNotify" value="Y" disabled/>Notify</td>
                    </tr>
                    <tr>
                        <td><label for="custOfficePhone">Office:</label></td>
                        <td><input type="text" name="custOfficePhone" id="custOfficePhone" style="width:160px" maxlength="12" class="validate[custom[onlyNumberSp],minSize[7]] numbersOnly"/></td>
                        <td><input type="checkbox" name="custOfficeNotify" id="custOfficeNotify" value="Y" disabled/>Notify</td>
                    </tr>
                    <tr>
                        <td><label for="custMobile">Mobile:</label></td>
                        <td><!-- <select name="custAreaCodeMobile" id="custAreaCodeMobile" style="width:65px">
                                <?php //echo html_entity_decode($cellBIN); ?>
                            </select> -->
                            <input type="text" name="custAreaCodeMobile" id="custAreaCodeMobile" style="width:50px" value="<?php echo $cellBIN; ?>" maxlength="4" class="validate[custom[onlyNumberSp]] numbersOnly"/>
                            -<input type="text" name="custMobile" id="custMobile" style="width:90px" maxlength="7" class="validate[custom[onlyNumberSp]] numbersOnly"/></td>
                        <td><input type="checkbox" name="custMobileNotify" id="custMobileNotify" value="Y" disabled/>Notify</td>
                    </tr>
                    <tr>
                        <td><label for="custEmail">E-mail:</label></td>
                        <td><input type="text" name="custEmail" id="custEmail" style="width: 160px" maxlength="30" class="validate[custom[email]]"/></td>
                        <td><input type="checkbox" name="custEmailNotify" id="custEmailNotify" value="Y" disabled/>Notify</td>
                    </tr>
                    
                </table>
                </div>
                </div>
        </div>
    </div>
    <div id="bottom">
    	<span class="buttons floatLeft">
        	<button id="submitBtn" value="customer/enroll/submit">Submit</button>
            <?php echo html_entity_decode($uploadBtn); ?>
        </span>
        <span class="buttons floatRight">
            <button type="reset">Clear</button
            ><button class="closebtn">Close</button>
        </span>
	</div>
</form>
<iframe id="uploadTarget" name="uploadTarget" src="#" onLoad="uploadDone()" class="hidden"></iframe>
<script>
$(function () {
	
	$('#uploadTarget').bind('load', uploadDone);
	
    filterPrefixes($('input[name="custGender"]').val());
	
    initSession('<?php echo $sessionExp; ?>');
    var submitBtn = '#submitBtn',
        resetBtn = 'button:reset',
        custImg = '#custImage',
        custPrev = '#custPreview',
        uploader = '.uploader',
        form = $('form');
    var batchBtn = '#uploadBtn';
		
    form.validationEngine('attach');
	
    $(DTPICKER).datepicker({
		defaultDate: '-80y',
        changeMonth: true,
        changeYear: true,
        yearRange: '-80y:-1y'
    });
	
    var custEmail = '#custEmail',
        chkEmail = '#custEmailNotify',
        txtHPhone = '#custHomePhone',
        chkHPhone = '#custHomeNotify',
        txtOPhone = '#custOfficePhone',
        chkOPhone = '#custOfficeNotify',
        txtMPhone = '#custMobile',
        chkMPhone = '#custMobileNotify';
		
    $('input[name="custGender"]').change(function () {
        filterPrefixes(this.value);
    });
	
    $(custEmail).change(function () {
        if ($(custEmail).val() != '') {
          	$(chkEmail).removeAttr('disabled');
        } else {
           	$(chkEmail).removeAttr('checked');
            $(chkEmail).attr('disabled', true);
        }
    });

    $(batchBtn).click(function () {
        window.location.hash = 'customer/batchupload';
        return false;
    });
	
    $(txtHPhone).change(function () {
        if ($(txtHPhone).val() != '') {
            $(chkHPhone).removeAttr('disabled');
        } else {
            $(chkHPhone).removeAttr('checked');
            $(chkHPhone).attr('disabled', true);
        }
    });
	
    $(txtOPhone).change(function () {
        if ($(txtOPhone).val() != '') {
            $(chkOPhone).removeAttr('disabled');
        } else {
            $(chkOPhone).removeAttr('checked');
            $(chkOPhone).attr('disabled', true);
        }
    });
	
    $(txtMPhone).change(function () {
        if ($(txtMPhone).val() != '') {
            $(chkMPhone).removeAttr('disabled');
        } else {
            $(chkMPhone).removeAttr('checked');
            $(chkMPhone).attr('disabled', true);
        }
    });
	
    $(custPrev).mouseenter(function () {
        $(uploader).show();
    }).mouseleave(function () {
        $(uploader).hide();
    });
	
    $(custImg).change(function () {
		//alert('wasd');
        form.attr('action', 'customer/enroll/upload');
		form.validationEngine('detach');
		waitMessage('Uploading image...');
		form.submit();
    });
	
    $(resetBtn).click(function () {
        $('#custLastName').focus();
        form.validationEngine('hideAll');
    });
	
    $(submitBtn).click(function (e) {
        if (form.validationEngine('validate') === true) {
            messageBox('Are all entries correct?', 'Confirm', 'confirm', function () {
                waitMessage('Validating customer registration...');
                form.submit();
            });
        }
        e.preventDefault();
    });
	
    var addr = '#custCity, #custProvince, #custZipCode';
    $(addr).focus(function () {
        $(this).attr('disabled', true);
        showZones();
    });
});

function uploadDone() {
    var iframeContent = $('#uploadTarget').contents().find('body').html();
    var data = eval("(" + iframeContent + ")");
	//var data = $.parseJSON(iframeContent);
	
	if (data) {
		var form = $('form');
		var img = $('#custPreview img');
		
		if (data.uploaded === true) {
			
			$(MSGBOX).dialog('close');
			var cacheKiller = "?timestamp=" + new Date().getTime();
			img.attr('src', 'uploads/' + data.file_name + cacheKiller);
			
		} else if (data.uploaded === false) {
			messageBox(data.message);
		}
		
		if (data.success === true) {
			
			messageBox('Customer entry successfully enrolled');
			form[0].reset();
			img.attr('src', 'images/nullphoto.jpg');
			
		} else if (data.success === false) {
			messageBox(data.message);
			
			if (data.errorno == 8) {
				logOut();
			}
		}
	}
}
</script>
