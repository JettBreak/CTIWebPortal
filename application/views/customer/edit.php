<form id="custEditForm" method="post" action="customer/edit/upload" enctype="multipart/form-data" target="uploadTarget">
    <input type="hidden" name="custCifseqno" value="<?php echo $cifseqno; ?>"/>
    <input type="hidden" name="custkey" value="<?php echo $custkey; ?>"/>
    <input type="hidden" name="custBlobPic" value="<?php echo $blobPic; ?>"/>
    <div id="custEdit">
        <h1>Update Customer Entry <span class="floatRight">CIF Number: <?php echo $cifseqno; ?></span></h1>
        <div id="content"><span class="floatRight hint" style="position:absolute;top:-5px;right:15px;text-align:right">Enter customer details then press submit to send<br /><span class="floatRight"><span class="red">*</span> - Required Fields</span></span></span>
            <div class="customer-entry-overview" style="display:flex;align-items:flex-start;gap:15px;padding-top:30px">
            <div id="custPreview" class="imgWrapper customer-entry-photo" style="flex:0 0 100px;margin:0"><img src="<?php echo $srcImg; ?>" width="100" height="100" alt="Customer picture"/>
                <div class="uploader"><span><?php echo $upMsg; ?></span>
                    <input type="file" id="custImage" name="custImage"/>
                </div>
            </div>
            <div class="customer-entry-main" style="flex:1 1 0;min-width:0">
            <strong>Customer Information:</strong>
            <table width="100%" style="margin-top:10px">
                <tr>
                    <td><label for="custPrefix">Prefix:</label></td>
                    <td><label for="custLastName">Last Name: <span class="red">*</span></label></td>
                    <td><label for="custFirstName">First Name: <span class="red">*</span></label></td>
                    <td><label for="custMiddlename">Middle Name:</label></td>
                    <td><label for="custSuffix">Suffix:</label></td>
                </tr>
                <tr>
                    <td><select name="custPrefix" id="custPrefix" style="width:70px">
                            <?php echo html_entity_decode($prefixes); ?>
                        </select></td>
                    <td><input type="text" name="custLastName" id="custLastName" style="width:122px" maxlength="50" value="<?php echo $lastName; ?>" class="validate[required,custom[onlyLetterSpCustom]] alphaNumCustom"/></td>
                    <td><input type="text" name="custFirstName" id="custFirstName" style="width:122px" maxlength="50" value="<?php echo $firstName; ?>" class="validate[required,custom[onlyLetterSpCustom]] alphaNumCustom"/></td>
                    <td><input type="text" name="custMiddleName" id="custMiddleName" style="width:122px" maxlength="30" value="<?php echo $middleName; ?>" class="validate[custom[onlyLetterSpCustom]] alphaNumCustom"/></td>
                    <td><input type="text" name="custSuffix" id="custSuffix" style="width:40px" class="lettersOnly" maxlength="4" value="<?php echo $suffix; ?>"/></td>
                </tr>
            </table>
            </div>
            </div>
            <table width="100%" class="divider">
                <tr>
                    <td width="110"><label>Gender:</label></td>
                    <td><?php echo html_entity_decode($gender); ?></td>
                    <td><label for="custNationality">Nationality:</label></td>
                    <td><input type="text" name="custNationality" id="custNationality" class="lettersOnly" style="width:150px" maxlength="20" value="<?php echo $nationality; ?>"/></td>
                </tr>
                <tr>
                    <td><label for="custCivilStatus">Civil Status:</label></td>
                    <td><select name="custCivilStatus" id="custCivilStatus" style="width:152px">
                            <?php echo html_entity_decode($civilStats); ?>
                        </select></td>
                    <td><label for="custOccupation">Occupation:</label></td>
                    <td><input type="text" name="custOccupation" id="custOccupation" style="width:150px" class="lettersOnly" maxlength="30" value="<?php echo $occupation; ?>"/></td>
                </tr>
                <tr>
                    <td><label for="custBDate">Date of birth:</label></td>
                    <td><input type="text" name="custBDate" id="custBDate" style="width:140px" value="<?php echo $bDay; ?>" class="datePicker" readonly/></td>
                    <td><label for="custSSS">SSS:</label></td>
                    <td><input type="text" name="custSSS" id="custSSS" style="width:150px" class="numbersOnly" maxlength="30" value="<?php echo $sss; ?>"/></td>
                </tr>
                <tr>
                    <td><label for="custBPlace">Place of birth:</label></td>
                    <td><input type="text" name="custBPlace" id="custBPlace" style="width:140px" class="alphaNum" maxlength="30" value="<?php echo $bPlace; ?>"/></td>
                    <td><label for="custTIN">TIN:</label></td>
                    <td><input type="text" name="custTIN" id="custTIN" style="width:150px" class="numbersOnly" maxlength="30" value="<?php echo $tin; ?>"/></td>
                </tr>
            </table>
            <div class="divider customer-address-contact">
                <table class="customer-address-table">
                    <tr>
                        <td width="30%"><label for="custCountry">Country of Origin:</label></td>
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
                        <td colspan="3"><input type="text" name="custStreet1" id="custStreet1" style="width:220px" maxlength="50" value="<?php echo $address1; ?>" class="validate[required]"/></td>
                    </tr>
                    <tr>
                        <td><label for="custStreet2">Street 2:</label></td>
                        <td colspan="3"><input type="text" name="custStreet2" id="custStreet2" style="width:220px" maxlength="50" value="<?php echo $address2; ?>"/></td>
                    </tr>
                    <tr>
                        <td><label for="custCity">Town / City: <span class="red">*</span></label></td>
                        <td width="110"><input type="text" name="custCity" id="custCity" style="width:80px" maxlength="30" value="<?php echo $city; ?>" class="validate[required]"/></td>
                        <td width="70"><label for="custZipCode">ZIP Code: <span class="red">*</span></label></td>
                        <td><input type="text" name="custZipCode" id="custZipCode" style="width:32px" maxlength="4" value="<?php echo $zipCode; ?>" class="validate[required,custom[onlyNumberSp]] numbersOnly"/></td>
                    </tr>
                    <tr>
                        <td><label for="custProvince">Province: <span class="red">*</span></label></td>
                        <td colspan="3"><input type="text" name="custProvince" id="custProvince" style="width:220px" maxlength="30" value="<?php echo $province; ?>" class="validate[required]"/></td>
                    </tr>
                </table>
                <div class="customer-contact-section">
                <strong>Contact Numbers:</strong>
                <table>
                    <tr>
                        <td>&nbsp;</td>
                        <td colspan="3">Area Code &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Phone No.</td>
                    </tr>
                    <tr>
                        <td width="9%"><label for="custHomePhone">Home:</label></td>
                        <td><input type="text" name="custHomePhone" id="custHomePhone" style="width:160px" maxlength="12" value="<?php echo $hPhone; ?>" class="validate[custom[onlyNumberSp],minSize[7]] numbersOnly"/></td>
                        <td><input type="checkbox" name="custHomeNotify" id="custHomeNotify" value="Y" <?php echo $hNotify; ?>/>
                            Notify</td>
                    </tr>
                    <tr>
                        <td><label for="custOfficePhone">Office:</label></td>
                        <td><input type="text" name="custOfficePhone" id="custOfficePhone" style="width:160px" maxlength="12" value="<?php echo $oPhone; ?>" class="validate[custom[onlyNumberSp],minSize[7]] numbersOnly"/></td>
                        <td><input type="checkbox" name="custOfficeNotify" id="custOfficeNotify" value="Y" <?php echo $oNotify; ?>/>
                            Notify</td>
                    </tr>
                    <tr>
                        <td><label for="custOfficeMobile">Mobile:</label></td>
                        <td><!-- <select name="custMobileBIN" id="custMobileBIN" style="width:65px">
                                <?php //echo html_entity_decode($cellBIN); ?>
                            </select> -->
                            <input type="text" name="custMobileBIN" id="custMobileBIN" style="width:50px" value="<?php echo $cellBIN; ?>" maxlength="4" class="validate[custom[onlyNumberSp]] numbersOnly"/>
                            -<input type="text" name="custMobile" id="custMobile" style="width:90px" value="<?php echo $mPhone; ?>" maxlength="7" class="validate[custom[onlyNumberSp]] numbersOnly"/></td>
                        <td><input type="checkbox" name="custMobileNotify" id="custMobileNotify" value="Y" <?php echo $mNotify; ?>/>
                            Notify</td>
                    </tr>
                    <tr>
                        <td><label for="custEmail">E-mail:</label></td>
                        <td><input type="text" name="custEmail" id="custEmail" class="validate[custom[email]]" style="width:160px" maxlength="30" value="<?php echo $email; ?>"/></td>
                        <td><input type="checkbox" name="custEmailNotify" id="custEmailNotify" value="Y" <?php echo $eNotify; ?>/>
                            Notify</td>
                    </tr>
                </table>
                </div>
        </div>
    </div>
    <div id="bottom"><span class="buttons floatLeft">
        <button id="submitBtn" value="customer/edit/submit">Submit</button>
        </span><span class="buttons floatRight">
        <button type="reset">Reset</button>
        <button class="closebtn">Close</button>
        </span></div></div>
</form>
<iframe id="uploadTarget" name="uploadTarget" src="#" onLoad="uploadDone()" class="hidden"></iframe>
<script>
$(function () {
    //filterPrefixes($('input[name="custGender"]').val());
	
    var $submitBtn = '#submitBtn',
        $custImg = '#custImage',
        $custPrev = '#custPreview',
        $uploader = '.uploader',
        $resetBtn = 'button[type="reset"]',
        $form = $('form');
    
    initSession('<?php echo $sessionExp; ?>');
    $form.validationEngine('attach');
    $(DATATABLE).find('tbody tr').die('dblclick');
    $(DTPICKER).datepicker({
        changeMonth: true,
        changeYear: true,
        yearRange: '-80y:-1y'
    });
    $(TABCONTENT).hide();
    $(TABS).first().addClass('active').show();
    $(TABCONTENT).first().show();
    $(TABS).click(function () {
        if (!$(this).hasClass('active')) {
            $form.validationEngine('hideAll');
            $(TABS).removeClass('active');
            $(this).addClass('active');
            $(TABCONTENT).hide();
            var a = $(this).find('a').attr('href');
            $(a).show();
            $(WRAPPER).height('auto')
        }
        return false
    });
    var $custEmail = '#custEmail',
        $chkEmail = '#custEmailNotify',
        $txtHPhone = '#custHomePhone',
        $chkHPhone = '#custHomeNotify',
        $txtOPhone = '#custOfficePhone',
        $chkOPhone = '#custOfficeNotify',
        $txtMPhone = '#custMobile',
        $chkMPhone = '#custMobileNotify';
    $('input[name="custGender"]').change(function () {
        filterPrefixes(this.value, '<?php echo $prefix; ?>')
    });
    $($custEmail).change(function () {
        if ($($custEmail).val() !== '') {
            $($chkEmail).removeAttr('disabled')
        } else {
            $($chkEmail).removeAttr('checked').attr('disabled', 'disabled')
        }
    });
    $($txtHPhone).change(function () {
        if ($($txtHPhone).val() !== '') {
            $($chkHPhone).removeAttr('disabled')
        } else {
            $($chkHPhone).removeAttr('checked').attr('disabled', 'disabled')
        }
    });
    $($txtOPhone).change(function () {
        if ($($txtOPhone).val() !== '') {
            $($chkOPhone).removeAttr('disabled')
        } else {
            $($chkOPhone).removeAttr('checked').attr('disabled', 'disabled')
        }
    });
    $($txtMPhone).change(function () {
        if ($($txtMPhone).val() !== '') {
            $($chkMPhone).removeAttr('disabled')
        } else {
            $($chkMPhone).removeAttr('checked').attr('disabled', 'disabled')
        }
    });
    $($custPrev).mouseenter(function () {
        $($uploader).show()
    }).mouseleave(function () {
        $($uploader).hide()
    });
    $($custImg).change(function () {
        $form.attr('action', 'customer/edit/upload');
        $form.validationEngine('detach');
        waitMessage('Uploading image...');
        $form.submit()
    });
    $($resetBtn).click(function () {
        $form[0].reset();
        if ($($custEmail).val() !== '') {
            $($chkEmail).removeAttr('disabled')
        } else {
            $($chkEmail).removeAttr('checked').attr('disabled', 'disabled')
        }
        if ($($txtHPhone).val() !== '') {
            $($chkHPhone).removeAttr('disabled')
        } else {
            $($chkHPhone).removeAttr('checked').attr('disabled', 'disabled')
        }
        if ($($txtOPhone).val() !== '') {
            $($chkOPhone).removeAttr('disabled')
        } else {
            $($chkOPhone).removeAttr('checked').attr('disabled', 'disabled')
        }
        if ($($txtMPhone).val() !== '') {
            $($chkMPhone).removeAttr('disabled')
        } else {
            $($chkMPhone).removeAttr('checked').attr('disabled', 'disabled')
        }
        $form.validationEngine('hideAll');
        return true
    });
    $($submitBtn).click(function (e) {
        if ($form.validationEngine('validate') == true) {
            messageBox('Are all entries correct?', 'Confirm', 'confirm', function () {
                waitMessage('Updating Customer Information...');
                $form.submit();
                //$(MSGBOX).dialog('close');
                //showUserOverride();
            })
        }
        e.preventDefault()
    });
	
	
    var selector = '#custCity, #custProvince, #custZipCode';
    $(selector).focus(function () {
        $(selector).attr('disabled', true);
        showZones();
    });
});

function uploadDone() {
    var iframeContent = $('#uploadTarget').contents().find('body').html();
    //var data = eval("(" + iframeContent + ")");
	var data = $.parseJSON(iframeContent);
	
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
			
			messageBox('Customer entry successfully updated');
			
		} else if (data.success === false) {
			messageBox(data.message);
			
			if (data.errorno == 8) {
				logOut();
			}
		}
	}
}
</script>
