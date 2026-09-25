<form id="productEditForm" method="post">
    <div id="department">
        <h1><?php echo $title; ?></h1>
        <div id="content"><span class="hint floatRight"><span class="red">*</span> - All fields required</span>
            <table width="100%">
                <tr>
                    <td width="150"><label for="editProductDesc">Description: <span class="red">*</span></label></td>
                    <td><input name="editProductDesc" id="editProductDesc" style="width:150px" class="validate[required,custom[onlyLetterNumberSp]] alphaNum" maxlength="30" value="<?php echo $prodDesc; ?>"/></td>

                </tr>
                <tr>
                    <td width="150"><label for="editProductType">Type: <span class="red">*</span></label></td>
                    <td><input name="editProductType" id="editProductType" style="width: 100px" min=50 max=89 value="<?php echo $prodCode; ?>"  class="validate[required,custom[onlyLetterNumberSp]]" maxlength="2" class="validate[required, custom[onlyNumberSp]]" /></td>

                </tr>
                <tr>
                    <td width="150"><label for="editProductCode">Code: <span class="red">*</span></label></td>
                    <td><input name="editProductCode" id="editProductCode" style="width:100px" class="validate[required,custom[onlyLetterSp]] lettersOnly" maxlength="2" value="<?php echo $prodType; ?>"/></td>
                </tr>
                <tr>
                    <td width="150"><label for="editEmbnstat" id="embnstatLbl">EMBNSTAT: <span class="red">*</span></label></td>
                    <td><select name="editEmbnstat" id="editEmbnstat" style="width:170px">
                        <?php echo html_entity_decode($embnstat); ?>
                    </select></td>

                </tr>
                <tr>
                    <td width="150"><label for="editPmlnstat" id="pmlnstatLbl">PMLNSTAT: <span class="red">*</span></label></td>
                    <td><select name="editPmlnstat" id="editPmlnstat" style="width:170px">
                        <?php echo html_entity_decode($pmlnstat); ?>
                    </select></td>

                </tr>
                <tr>
                    <td width="150"><label for="editActnstat" id="actnstatLbl">ACTNSTAT: <span class="red">*</span></label></td>
                    <td><select name="editActnstat" id="editActnstat" style="width:170px">
                        <?php echo html_entity_decode($actnstat); ?>
                    </select></td>

                </tr>
            </table>
        </div>
    </div>
    <div id="bottom">
    	<span class="buttons floatLeft">
        	<button id="submitBtn" value="<?php echo $formAction; ?>">Submit</button
            ><button id="showConfigBtn" value="0">Show Config</button
            ><button type="reset">Reset</button>
        </span>
        <span class="buttons floatRight">
        	<button id="closeBtn">Close</button>
        </span>
	</div>
</form>
<script>
$(function () {
    var submitBtn = '#submitBtn',
		productCode = '#editProductCode',
		productDesc = '#editProductDesc',
        productType = '#editProductType',
        embn = 0,
        pmln = 0,
        actn = 0,
        form = $('form');
		
	//var oldDeptName = $(deptName).val();
		
    initSession('<?php echo $sessionExp; ?>');
    form.validationEngine({
        ajaxFormValidation: true,
        onBeforeAjaxFormValidation: function () {
            waitMessage('Submitting...');
        },
        onAjaxFormComplete: function (a, b, data, d) {
			messageBox(data.message);
			$(MSGBOX).one('dialogbeforeclose', function () {
				if (data.success === true) {
					window.location.hash = 'maintenance/cardproductlist';
				} else {
					form[0].reset();
					//$('#isEdited').val('0');
				}
			});
        },
        scroll: false
    });
    form.validationEngine('attach');
	
	$(productType).focus().live('focusout', function (e){
       /* var value = parseInt(this.value, 5);
        if (isNaN(value)) {
            this.value = 50;
        }
        this.value = this.value.replace(/^0+/, ''); //remove leading zeros*/
        //if (eval(this.value) < 50) {
          //  this.value = 50;
        //}

        //if (eval(this.value) > 89) {
          //  this.value = 89;
        //}

    });

    $(submitBtn).focus();
	
	$(productCode).live('focusout', function (e) {
		
    });

    if(<?php echo $disabled; ?>) {
        $(productType).attr('disabled','disabled');
    }
	
	$(submitBtn).click(function (e) {
        e.preventDefault();
        if (form.validationEngine('validate') === true) {
            messageBox('<?php echo $submitBtnMsg; ?>', 'Confirm', 'confirm', function () {
                $(productType).removeAttr('disabled');
                form.submit();
            })
        }
    });

    $('#closeBtn').click(function (e) {
        window.location.hash = 'maintenance/cardproductlist';
        e.preventDefault();
    });


    $('#editEmbnstat').hide();
    $('#embnstatLbl').hide();

    $('#editPmlnstat').hide();
    $('#pmlnstatLbl').hide();

    $('#editActnstat').hide();
    $('#actnstatLbl').hide();

    $(showConfigBtn).click(function (e) {


        if (this.value == 0) {
            this.value = 1;
            $('#editEmbnstat').show();
            $('#embnstatLbl').show();

            $('#editPmlnstat').show();
            $('#pmlnstatLbl').show();

            $('#editActnstat').show();
            $('#actnstatLbl').show();

            $(showConfigBtn).text('Hide Config');


            embn = $('#editEmbnstat').val();
            pmln = $('#editPmlnstat').val();
            actn = $('#editActnstat').val();

        } else {
            //messageBox('Do you want to save config?', 'Confirm', 'confirm', function () {
                
           // });

            //$(productDesc).val(embn);
            if (embn != $('#editEmbnstat').val() ||
                pmln != $('#editPmlnstat').val() ||
                actn != $('#editActnstat').val()) {
                messageBoxV2('Do you want to save config?', 'Confirm', {
                    YES: function () {
                        
                        $(showConfigBtn).val(0);

                        $('#editEmbnstat').hide();
                        $('#embnstatLbl').hide();

                        $('#editPmlnstat').hide();
                        $('#pmlnstatLbl').hide();

                        $('#editActnstat').hide();
                        $('#actnstatLbl').hide();

                        $(showConfigBtn).text('Show Config');

                        $(MSGBOX).dialog('close');
                    },
                    NO: function () {

                        $('#editEmbnstat').val(embn);
                        $('#editPmlnstat').val(pmln);
                        $('#editActnstat').val(actn);

                        $('#editEmbnstat').hide();
                        $('#embnstatLbl').hide();

                        $('#editPmlnstat').hide();
                        $('#pmlnstatLbl').hide();

                        $('#editActnstat').hide();
                        $('#actnstatLbl').hide();

                        $(showConfigBtn).text('Show Config');

                        $(MSGBOX).dialog('close');
                        $(showConfigBtn).val(0);
                    }
                });
            } else {
                $(showConfigBtn).val(0);

                $('#editEmbnstat').hide();
                $('#embnstatLbl').hide();

                $('#editPmlnstat').hide();
                $('#pmlnstatLbl').hide();

                $('#editActnstat').hide();
                $('#actnstatLbl').hide();

                $(showConfigBtn).text('Show Config');
            }


        }


        e.preventDefault();
    });
	
	//$(backBtn).click(function () {
	//	window.location.hash = 'maintenance/departments';
	//	return false;
	//});
	
	//$(deptName).change(function() {
	//	if (oldDeptName !== this.value) {
	//		$('#isEdited').val('1');
	//	} else {
	//		$('#isEdited').val('0');
	//	}
	//});
});
</script>