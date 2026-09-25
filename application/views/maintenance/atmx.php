<form id="atmForm" method="post">
    <input type="hidden" name="progCode" id="progCode" value="<?php echo isset($progCodex) ? $progCodex : NULL ; ?>"/>
    <input type="hidden" name="progLang" id="progLang" value="<?php echo isset($progLangx) ? $progLangx : NULL ; ?>"/>
    <div id="atmNew">
        <h1><?php echo $header; ?></h1>
        <ul class="tabs fullTabs">
            <li><a href="#tab1">Information</a></li>
            <li><a href="#tab2">Program Parameters</a></li>
            <li><a href="#tab3">Denomination</a></li>
            <li><a href="#tab4">Contacts</a></li>
            <?php echo html_entity_decode($POSCashOut); ?>
        </ul>
        <div class="tab_container">
            <div id="tab1" class="tab_content">
                <table width="100%">
                    <tr>
                        <td width="120"><label for="termCode" class="idName">Terminal Code:</label></td>
                        <td width="210"><input type="text" name="termCode" id="termCode" style="width:180px" <?php echo html_entity_decode($termCodeParams); ?>/></td>
                        <td width="110"></td>
                        <td class="floatRight">All fields are required</td>
                    </tr>
                    <tr>
                        <td><label for="termID">Terminal ID:</label></td>
                        <td><input type="text" name="termID" id="termID" style="width:180px" class="validate[required] alphaNum" maxlength="20" value="<?php echo $termID; ?>" <?php echo $termIDParams; ?>/></td>
                        <td width="110"><label for="luno">LUNO:</label></td>
                        <td><input type="text" name="luno" id="luno" style="width:140px" value="<?php echo $luno; ?>" class="validate[required,custom[onlyNumberSp]] integersOnly" maxlength="4"/></td>
                    </tr>
                    <tr>
                        <td><label for="description">Description:</label></td>
                        <td colspan="3"><input name="description" id="description" type="text" style="width:180px" maxlength="50" class="validate[required]" value="<?php echo $desc; ?>" /></td>
                    </tr>
                    <tr>
                        <td><label for="prodName">Product Name:</label></td>
                        <td><select name="prodName" id="prodName" style="width:192px">
                                <?php echo html_entity_decode($prodName); ?>
                            </select></td>
                        <td><label for="location">Location:</label></td>
                        <td><select name="location" id="location" style="width:152px" class="validate[required]">
                                <?php echo html_entity_decode($location); ?>
                            </select></td>
                    </tr>
                    <tr>
                        <td><label for="termLang">Terminal Language:</label></td>
                        <td><select name="termLang" id="termLang" style="width:192px">
                                <?php echo html_entity_decode($termLang); ?>
                            </select></td>
                        <td><label for="areaName">Area:</label></td>
                        <td><input type="text" name="areaName" id="areaName" style="width:140px" value="<?php echo $areaName; ?>" readonly/></td>
                    </tr>
                    <tr>
                        <td><label for="emulation">Emulation:</label></td>
                        <td><select name="emulation" id="emulation" style="width:192px">
                                <?php echo html_entity_decode($emulation); ?>
                            </select></td>
                        <td><label for="branchName">Branch:</label></td>
                        <td><input type="text" name="branchName" id="branchName" style="width:140px" value="<?php echo $branchName; ?>" readonly/></td>
                    </tr>
                    <tr>
                        <td><label for="progName">Program Name:</label></td>
                        <td><select name="progName" id="progName" style="width:192px">
                                <?php echo html_entity_decode($progName); ?>
                            </select></td>
                        <td><label for="dtProd">Production Date:</label></td>
                        <td><input type="text" name="dtProd" id="dtProd" style="width:140px" class="datePicker" value="<?php echo $dtProd; ?>" readonly/></td>
                    </tr>
                    <tr>
                        <td><label for="instType">Installation Type:</label></td>
                        <td><select name="instType" id="instType" style="width:192px">
                                <?php echo html_entity_decode($instType); ?>
                            </select></td>
                        <td><label for="decimal">Default Decimal:</label></td>
                        <td><input type="text" name="decimal" id="decimal" style="width:140px" value="<?php echo $decimal; ?>" class="validate[required,custom[integer]] numbersOnly" maxlength="2"/></td>
<!--						<td><label for="contactNo">Contact No.</label></td>
                        <td><input type="text" name="contactNo" id="contactNo" style="width:140px" value="<?php //echo $contactNo; ?>" class="validate[custom[integer]] numbersOnly" maxlength="18"></td>-->
					</tr>
                    <tr>
                    	<td><label for="threshold">Threshold Amount:</label></td>
                        <td><input type="text" name="threshold" id="threshold" style="width:180px" value="<?php echo $threshold; ?>" class="validate[required] currencyOnly" maxlength="9"/></td>
                        <td><label for="currency">Default Currency:</label></td>
                        <td><select name="currency" id="currency" style="width:152px">
                                <?php echo html_entity_decode($currency); ?>
                            </select></td>
                    </tr>
                    <tr>
                        <td><label for="statusx">Status:</label></td>
                        <td><input type="text" name="statusx" id="statusx" style="width:180px" value="<?php echo $status; ?>" readonly/></td>
                        <td><label for="isEMV">EMV Enabled:</label></td>
                        <td><input type="checkbox" name="isEMV" id="isEMV" <?php echo isset($isEMV) ? $isEMV : NULL; ?> /></td>
                    </tr>
                </table>
            </div>
            <div id="tab2" class="tab_content">
                <table width="100%">
                    <tr>
                        <td><label for="screenLoadSize">Screen Load Size:</label></td>
                        <td><input type="number" name="screenLoadSize" id="screenLoadSize" style="width:120px" value="<?php echo $screenLoadSize; ?>" class="validate[required,funcCall[checkInput1]] integersOnly rule1" maxlength="4" step="1" min="500" max="2000"/></td>
                        <td><label for="otherLoadSize">Other Load Size:</label></td>
                        <td><input type="number" name="otherLoadSize" id="otherLoadSize" style="width:120px" value="<?php echo $otherLoadSize; ?>" class="validate[required,funcCall[checkInput1]] integersOnly rule1" maxlength="4" step="1" min="500" max="2000"/></td>
                    </tr>
                    <tr>
                        <td><label for="stateLoadSize">State Load Size:</label></td>
                        <td><input type="number" name="stateLoadSize" id="stateLoadSize" style="width:120px" value="<?php echo $stateLoadSize; ?>" class="validate[required,funcCall[checkInput1]] integersOnly rule1" maxlength="4" step="1" min="500" max="2000"/></td>
                        <td><label for="maxNotes">Maximum Notes:</label></td>
                        <td><input type="number" name="maxNotes" id="maxNotes" style="width:120px" value="<?php echo $maxNotes; ?>" class="validate[required,funcCall[checkInput2]] integersOnly rule2" maxlength="2" step="1" min="20" max="99"/></td>
                    </tr>
                    <tr>
                        <td><label for="fitLoadSize">Fit Load Size:</label></td>
                        <td><input type="number" name="fitLoadSize" id="fitLoadSize" style="width:120px" value="<?php echo $fitLoadSize; ?>" class="validate[required,funcCall[checkInput1]] integersOnly rule1" maxlength="4" step="1" min="500" max="2000"/></td>
                        <td><label for="dispenseLogic">Dispense Logic:</label></td>
                        <td><select name="dispenseLogic" id="dispenseLogic" style="width:134px">
                                <?php echo html_entity_decode($dispLogic); ?>
                            </select></td>
                    </tr>
                    <tr>
                        <td><label for="optLoadSize">Option Load Size:</label></td>
                        <td><input type="number" name="optLoadSize" id="optLoadSize" style="width:120px" value="<?php echo $optLoadSize; ?>" class="validate[required,funcCall[checkInput1]] integersOnly rule1" maxlength="4" step="1" min="500" max="2000"/></td>
                        <td><input type="checkbox" name="loadNewKey" id="loadNewKey" <?php echo $loadNewKey; ?>/>
                            <label for="loadNewKey">Load New Key</label></td>
                        <td><input type="checkbox" name="loadPower" id="loadPower" <?php echo $loadPower; ?>/>
                            <label for="loadPower">Load on Power-up</label></td>
                    </tr>
                </table>
            </div>
            <div id="tab3" class="tab_content">
                <table width="100%">
                    <tr>
                        <td><label for="progCodeDeno">Program Code:</label></td>
                        <td><select name="progCodeDeno" id="progCodeDeno" style="width:100px">
                                <?php echo html_entity_decode($progCodeDeno); ?>
                            </select></td>
                        <td><label for="progLangDeno">Program Language:</label></td>
                        <td><input type="text" name="progLangDeno" id="progLangDeno" style="width:100px" value="<?php echo $progLangDeno; ?>" readonly/></td>
                    </tr>
                </table>
                <table class="dataTable">
                    <thead>
                        <tr>
                            <th>Currency</th>
                            <th>Denomination</th>
                            <th>Cassette Type</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php echo html_entity_decode($denominations); ?>
                    </tbody>
                </table>
            </div>
            <div id="tab4" class="tab_content">
            	<table id="contactPersons">
                	<thead>
                    	<tr>
                        	<th width="100">Concern</th>
                            <th>Contact Person</th>
                            <th>Contact No.</th>
                        </tr>
                    </thead>
                	<tbody>
                        <tr>
                            <td><label for="hName">Hardware:</label></td>
                            <td><input type="text" name="hName" id="hName" style="width:150px" class="<?php echo $hNameAttr; ?>lettersOnly" value="<?php echo $hName; ?>" maxlength="50"/></td>
                            <td><input type="text" name="hTel" id="hTel" style="width:150px" class="<?php echo $hTelAttr; ?>numbersOnly" value="<?php echo $hTel; ?>" maxlength="15"/></td>
                        </tr>
                        <tr>
                            <td><label for="nName">Network:</label></td>
                            <td><input type="text" name="nName" id="nName" style="width:150px" class="<?php echo $nNameAttr; ?>lettersOnly" value="<?php echo $nName; ?>" maxlength="50"/></td>
                            <td><input type="text" name="nTel" id="nTel" style="width:150px" class="<?php echo $nTelAttr; ?>numbersOnly" value="<?php echo $nTel; ?>" maxlength="15"/></td>
                        </tr>
                        <tr>
                            <td><label for="tName">Threshold:</label></td>
                            <td><input type="text" name="tName" id="tName" style="width:150px" class="<?php echo $tNameAttr; ?>lettersOnly" value="<?php echo $tName; ?>" maxlength="50"/></td>
                            <td><input type="text" name="tTel" id="tTel" style="width:150px" class="<?php echo $tTelAttr; ?>numbersOnly" value="<?php echo $tTel; ?>" maxlength="15"/></td>
                        </tr>
                        <tr>
                            <td><label for="oName">Others:</label></td>
                            <td><input type="text" name="oName" id="oName" style="width:150px" class="<?php echo $oNameAttr; ?>lettersOnly" value="<?php echo $oName; ?>" maxlength="50"/></td>
                            <td><input type="text" name="oTel" id="oTel" style="width:150px" class="<?php echo $oTelAttr; ?>numbersOnly" value="<?php echo $oTel; ?>" maxlength="15"/></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div id="tab5" class="tab_content">
                <table width="100%">
                    <tr>
                        <td><label for="institutions">Partner Inst Name:</label></td>
                        <td colspan="3"><select name="institutions" id="institutions" style="width:212px" class="validate[required]" >
                                <?php echo html_entity_decode($institutions); ?>
                            </select></td>
                    </tr>   
                    <tr>
                        <td><label for="instid">Partner Inst ID:</label></td>
                        <td><input type="text" name="instid" id="instid" style="width:200px" class="alphaNum" maxlength="20" value="<?php echo isset($instID)  ? $instID : NULL; ?>" 
                                <?php echo isset($instIDParams) ? $instIDParams : NULL; ?> readOnly/>
                            </td>
                    </tr>
                    <tr>
                        <td><label for="outlet">Outlet Name:</label></td>
                        <td colspan="3"><select name="outlet" id="outlet" style="width:212px" class="validate[required]" >
                                <?php echo html_entity_decode($outlets); ?>
                            </select></td>
                    </tr>   
                    <tr>
                        <td><label for="outletID">Outlet ID:</label></td>
                        <td><input type="text" name="outletID" id="outletID" style="width:200px" class="alphaNum" maxlength="20" value="<?php echo isset($outletID)  ? $outletID : NULL; ?>" 
                                <?php echo isset($outletIDParams)  ? $outletIDParams : NULL; ?> readOnly/>
                            </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    <div id="bottom">
    	<span class="buttons floatLeft">
            <button id="submitBtn" value="<?php echo $submitBtnVal; ?>">Submit</button
            ><button type="reset">Reset</button>
        </span>
        <span class="buttons floatRight">
            <button id="backBtn">Back</button
            ><button class="closebtn">Close</button>
        </span>
	</div>
</form>
<style>
th {
	font-weight: bold;
}
</style>
<script>
$(function () {
    var $submitBtn = '#submitBtn',
        $backBtn = '#backBtn',
        form = $('form');
	
	$(DTPICKER).datepicker({
        changeMonth: true,
        changeYear: true,
		maxDate: '-0d',
        yearRange: '-10y:+0y'
    });
	
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
					window.location.hash = 'maintenance/atm';
				});
            }
        },
        scroll: false
    });
	
    form.validationEngine('attach');
	
    //$(DATATABLE).find('tbody tr').die('dblclick');
	
    initSession('<?php echo $sessionExp; ?>');
    oTable = $(DATATABLE).dataTable({
        'bRetrieve': true,
        'bJQueryUI': true,
        'aaSorting': [],
        'sScrollY': '100%',
        'sScrollX': '100%',
        'sPaginationType': 'full_numbers',
        'fnRowCallback': function (a, b, c) {
            $('td:eq(0), td:eq(2)', a).attr('align', 'center');
            $('td:eq(1)', a).attr('align', 'right');
            return a
        }
    });
	
    $.fn.dataTableExt.iApiIndex = 0;
	
    $(TABCONTENT).hide();
    $(TABS).first().addClass('active').show();
    $(TABCONTENT).first().show();
	
    $(TABS).click(function () {
        if (form.validationEngine('validate') === true) {
            if (!$(this).hasClass('active')) {
                $(TABS).removeClass('active');
                $(this).addClass('active');
                $(TABCONTENT).hide();
                var a = $(this).find('a').attr('href');
                $(a).show();
                $(WRAPPER).height('auto');
                oTable.fnAdjustColumnSizing()
            }
        }
        return false
    });

    if ('<?php echo intval($success); ?>' == 0) {
        messageBox('<?php echo html_entity_decode($message); ?>'+'<br>');
    }
	
    $($submitBtn).click(function (e) {
        if (form.validationEngine('validate') === true) {
            messageBox('<?php echo html_entity_decode($submitBtnMsg); ?>', 'Warning', 'confirm', function () {
                form.submit();
            })
        }
		e.preventDefault();
    });
	
    $('#progName').change(function () {
        var a = $('#progName option:selected').attr('proglang');
        var b = this.value;
        $('#progLang').val(a);
        $('#progCode').val(b);
        $('#progCodeDeno').val(b);
        $('#progCodeDeno').trigger('change');
    });
	
    $($backBtn).click(function () {
        window.location.hash = 'maintenance/atm';
        return false
    });
	
    $('#progCodeDeno').change(function () {
        var d = $('#progName option:selected').attr('proglang');
        if (this.value === '0') {
            var e = $('#progName').val();
            $('#progLangDeno').val('0');
        } else {
            var e = this.value;
            $('#progLangDeno').val(d);
            $('#progName').val(e);
            $('#progLang').val(d);
            $('#progCode').val(e);
        }
        requests.push($.ajax({
            url: 'maintenance/atm/getdeno',
            type: 'POST',
            cache: false,
            data: {
                'progCode': e,
                'progLang': d,
            },
            dataType: 'json',
            beforeSend: function () {
                $('#progCode').attr('disabled', 'disabled')
            },
            error: function (a, b, c) {},
            success: function (a) {
                $('#progCode').removeAttr('disabled');
                $.fn.dataTableExt.iApiIndex = 0;
                oTable.fnClearTable(0);
                oTable.fnAddData(a['aaData']);
                oTable.fnDraw()
            }
        }));
    });
	
	$('#threshold').focusout(function (e) {
		this.value = formatCurrency(this.value);
	}).focusin(function(e) {
		if (this.value === '0.00') {
			this.value = '';
		}
    });
	
	$('#decimal').focusout(function (e) {
		if (this.value !== '0') {
			this.value = this.value.replace(/^0+/, ''); //remove leading zeros
		}
	});
	
	$('#location').change(function () {
		var selected = $(this).find('option:selected');
		
		$('#areaName').val(selected.attr('areaname'));
		$('#branchName').val(selected.attr('brname'));
	});
	
	$('#contactPersons').focusout(function (e) {
		var cur = $(e.target)
			.parents('tr')
			.find('input')
			.not('#' + e.target.id);
		
		if (e.target.value !== '') {
			cur.addClass('validate[required]');
		} else {
			cur.removeClass('validate[required]')
				.validationEngine('hidePrompt');
		}
	});
    

    $('#institutions').change(function () {
        selected = $(this).find('option:selected');
        $('#instid').val(selected.attr('instid'));

        $('#outlet').val('XXX');
        $('#outletID').val('');

        $('#outlet option').hide();
        $('#outlet option[value="XXX"]').show()
        $('#outlet option[inst=' + selected.val() + ']').show()
    });

    $('#outlet').change(function () {
        selected = $(this).find('option:selected');
        $('#outletID').val(selected.attr('outletid'));
    });

    $('#outlet').focus(function () {
        selected = $('#institutions').find('option:selected');

        $('#outlet option').hide();
        $('#outlet option[value="XXX"]').show()
        $('#outlet option[inst=' + selected.val() + ']').show()
    });
});

//min 500 max 2000
function checkInput1(field, rules, i, options) {
	var maxInput = 2000;
	var minInput = 500;
	var val = parseInt(field.val());
	
	if (val < minInput || val > maxInput) {
		return '* Must be between ' + minInput + ' and ' + maxInput;
	}
}

//min 20 max 99
function checkInput2(field, rules, i, options) {
	var maxInput = 99;
	var minInput = 20;
	var val = parseInt(field.val());
	
	if (val < minInput || val > maxInput) {
		return '* Must be between ' + minInput + ' and ' + maxInput;
	}
}
</script>