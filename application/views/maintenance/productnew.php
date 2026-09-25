<form id="productNewForm" method="post">
    <div id="department">
        <h1><?php echo $title; ?></h1>
        <div id="content"><span class="hint floatRight"><span class="red">*</span> - All fields required</span>
            <table width="100%">
                <tr>
                    <td width="150"><label for="newProductDesc">Description: <span class="red">*</span></label></td>
                    <td><input name="newProductDesc" id="newProductDesc" style="width:160px" class="validate[required,custom[onlyLetterNumberSp]] alphaNum" maxlength="30" value="<?php echo $prodDesc; ?>"/></td>

                </tr>
                <tr>
                    <td width="150"><label for="newProductType">Type: <span class="red">*</span></label></td>
                    <td><select name="newProductType" id="newProductType" style="width:110px">
                        <?php echo html_entity_decode($prodCode); ?>
                    </select></td>
                </tr>
                <tr>
                    <td width="150"><label for="newProductCode">Code: <span class="red">*</span></label></td>
                    <td><input name="newProductCode" id="newProductCode" style="width:100px" class="validate[required,custom[onlyLetterSp]] lettersOnly" maxlength="2" value="<?php echo $prodType; ?>"/></td>

                </tr>
                <tr>
                    <td width="150"><label for="newEmbnstat" id="embnstatLbl">EMBNSTAT: <span class="red">*</span></label></td>
                    <td><select name="newEmbnstat" id="newEmbnstat" style="width:170px">
                        <?php echo html_entity_decode($embnstat); ?>
                    </select></td>

                </tr>
                <tr>
                    <td width="150"><label for="newPmlnstat" id="pmlnstatLbl">PMLNSTAT: <span class="red">*</span></label></td>
                    <td><select name="newPmlnstat" id="newPmlnstat" style="width:170px">
                        <?php echo html_entity_decode($pmlnstat); ?>
                    </select></td>

                </tr>
                <tr>
                    <td width="150"><label for="newActnstat" id="actnstatLbl">ACTNSTAT: <span class="red">*</span></label></td>
                    <td><select name="newActnstat" id="newActnstat" style="width:170px">
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
        showConfigBtn = '#showConfigBtn',
		productCode = '#newProductCode',
		productDesc = '#newProductDesc',
        productType = '#newProductType',
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
	
	$(productType).focus();

    $('#closeBtn').click(function (e) {
        window.location.hash = 'maintenance/cardproductlist';
        e.preventDefault();
    });
	
	$(productCode).live('focusout', function (e) {
		/*var value = parseInt(this.value, 5);
		if (isNaN(value)) {
			value = '';
		}*/
		this.value = this.value.replace(/^0+/, ''); //remove leading zeros


    });

    $(productType).focus().live('focusout', function (e){
       /* var value = parseInt(this.value, 5);
        if (isNaN(value)) {
            this.value = 50;
        }
        this.value = this.value.replace(/^0+/, ''); //remove leading zeros*/
        //if (eval(this.value) < 50) {
          //  this.value = 50;
       // }

        //if (eval(this.value) > 89) {
        //    this.value = 89;
       // }

    });
	
	$(submitBtn).click(function (e) {
        e.preventDefault();
        if (form.validationEngine('validate') === true) {
            messageBox('<?php echo $submitBtnMsg; ?>', 'Confirm', 'confirm', function () {
                form.submit();
            })
        }
    });

    $('#newEmbnstat').hide();
    $('#embnstatLbl').hide();

    $('#newPmlnstat').hide();
    $('#pmlnstatLbl').hide();

    $('#newActnstat').hide();
    $('#actnstatLbl').hide();

    $(showConfigBtn).click(function (e) {


        if (this.value == 0) {
            this.value = 1;
            $('#newEmbnstat').show();
            $('#embnstatLbl').show();

            $('#newPmlnstat').show();
            $('#pmlnstatLbl').show();

            $('#newActnstat').show();
            $('#actnstatLbl').show();

            $(showConfigBtn).text('Hide Config');


            embn = $('#newEmbnstat').val();
            pmln = $('#newPmlnstat').val();
            actn = $('#newActnstat').val();

        } else {
            //messageBox('Do you want to save config?', 'Confirm', 'confirm', function () {
                
           // });

            //$(productDesc).val(embn);
            if (embn != $('#newEmbnstat').val() ||
                pmln != $('#newPmlnstat').val() ||
                actn != $('#newActnstat').val()) {
                messageBoxV2('Do you want to save config?', 'Confirm', {
                    YES: function () {
                        
                        $(showConfigBtn).val(0);

                        $('#newEmbnstat').hide();
                        $('#embnstatLbl').hide();

                        $('#newPmlnstat').hide();
                        $('#pmlnstatLbl').hide();

                        $('#newActnstat').hide();
                        $('#actnstatLbl').hide();

                        $(showConfigBtn).text('Show Config');

                        $(MSGBOX).dialog('close');
                    },
                    NO: function () {

                        $('#newEmbnstat').val(embn);
                        $('#newPmlnstat').val(pmln);
                        $('#newActnstat').val(actn);

                        $('#newEmbnstat').hide();
                        $('#embnstatLbl').hide();

                        $('#newPmlnstat').hide();
                        $('#pmlnstatLbl').hide();

                        $('#newActnstat').hide();
                        $('#actnstatLbl').hide();

                        $(showConfigBtn).text('Show Config');

                        $(MSGBOX).dialog('close');
                        $(showConfigBtn).val(0);
                    }
                });
            } else {
                $(showConfigBtn).val(0);

                $('#newEmbnstat').hide();
                $('#embnstatLbl').hide();

                $('#newPmlnstat').hide();
                $('#pmlnstatLbl').hide();

                $('#newActnstat').hide();
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