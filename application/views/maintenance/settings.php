<div id="genSettings">
    <h1>General Settings</h1>
    <ul class="tabs fullTabs">
        <li><a href="#cardFormat">Card Format</a></li>
        <li><a href="#accountFormat">Account Format</a></li>
    </ul>
    <div class="tab_container">
        <div id="cardFormat" class="tab_content">
        	<form id="cardFormatForm" method="post">
            	<input type="hidden" name="formatType" value="CARD"/>
            	<input type="hidden" name="description2" id="description2" value="CARD"/>
                <table width="100%">
                    <tr>
                    	<?php echo html_entity_decode($p1); ?>
                        <td><strong>P</strong> - Product</td>
                        <td><strong>I</strong> - Institution</td>
                        <td><strong>N</strong> - Number</td>
                        <td><strong>C</strong> - Check Digit</td>
                        <td><strong>B</strong> - Branch Code</td>
                    </tr>
                </table>
                <table width="100%" class="divider">
                	<tr>
                        <td width="170"><label for="formatCode1">Card Type:</label></td>
                        <td>
                            <select name="formatCode" id="formatCode1" style="width:200px">
                                <?php echo html_entity_decode($cardTypes); ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td width="170"><label for="formatBIN">Card BIN:</label></td>
                        <td>
                            <select name="formatBIN" id="formatBIN" style="width:90px">
                                <?php echo html_entity_decode($cardBIN); ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td><label for="formatValue1">Format Layout:</label></td>
                        <td><input type="text" name="formatValue" id="formatValue1" style="width:250px" class="validate[required] upperCase" value="<?php echo $formatValue1; ?>" maxlength="30"/></td>
                    </tr>
                        <td><label for="weights1">Weights:</label></td>
                        <td><input type="text" name="weights" id="weights1" style="width:250px" class="validate[required,minSize[<?php echo $weights1MaxLength; ?>],maxSize[<?php echo $weights1MaxLength; ?>]] numbersOnly" value="<?php echo $weights1; ?>" maxlength="<?php echo $weights1MaxLength; ?>"/></td>
                    </tr>
                        <td><label for="expYears1">Years to Expire:</label></td>
                        <td><input type="number" name="expYears" id="expYears1" style="width:80px" class="validate[required] numbersOnly" value="<?php echo $expYears1; ?>" maxlength="2" min="1" max="99" step="1"/></td>
                    </tr>
                        <td><label for="gracePeriod1">Grace Period:</label></td>
                        <td><input type="number" name="gracePeriod" id="gracePeriod1" style="width:80px" class="validate[required] numbersOnly" value="<?php echo $gracePeriod1; ?>" maxlength="3" min="1" max="999" step="1"/></td>
                    </tr>
                    </tr>
                        <td><label for="minDays1">Min. No. of Days Inactive:</label></td>
                        <td><input type="number" name="minDays" id="minDays1" style="width:80px" class="validate[required] numbersOnly" value="<?php echo $minDays1; ?>" maxlength="3" min="1" max="999" step="1"/></td>
                    </tr>  	
                </table>
                <br>
		        <fieldset>
		        	<legend>EMV Setup</legend>
		        	<table>
		        		<tr>
		        			<td width=250>
		        				<input type="checkbox" name="trnTrack2" id="trnTrack2" value="1"  /><label for="trnTrack2">Use Track2</label>
		        			</td>
		        			<td>
		        				<input type="checkbox" name="trnExpr" id="trnExpr" value="1"  ><label for="trnExpr">Check Expiration</label>
		        			</td>
		        		</tr>
		        		<tr>
		        			<td>
		        				<input type="checkbox" name="trnICCT2" id="trnICCT2" value="1"  /><label for="trnICCT2">Use ICC</label>
		        			</td>
		        			<td>
		        				<input type="checkbox" name="trnCVV" id="trnCVV" value="1"  /><label for="trnCVV">Check CVV</label>
		        				<span style="margin-left:20px;"><input type="number" name="servcCVV" id="servcCVV" max="999" min="0" maxlength="3" step="1" style="width:50px; text-align:center"/></span>
		        			</td>
		        		</tr>
		        		<tr>
		        			<td>
		        				<input type="checkbox" name="trnARQC" id="trnARQC" value="1"  /><label for="trnARQC">Verify ARQC</label>
		        			</td>
		        			<td>
		        				<input type="checkbox" name="trnICVV" id="trnICVV" value="1"  /><label for="trnICVV">Check iCVV</label>
		        				<span style="margin-left:18px;"><input type="number" name="servcICVV" id="servcICVV" max="999" min="0" maxlength="3" step="1" style="width:50px; text-align:center"/></span>
		        			</td>
		        		</tr>
		        		<tr>
		        			<td>
		        				<input type="checkbox" name="trnARPC" id="trnARPC" value="1"  /><label for="trnARPC">Generate ARPC</label>
		        			</td>
		        			<td>
		        				<input type="checkbox" name="trnCntr" id="trnCntr" value="1"  /><label for="trnCntr">Send Transaction Counters</label>
		        			</td>
		        		</tr>
		        	</table>
		        </fieldset>
            </form>
        </div>
        <div id="accountFormat" class="tab_content">
        	<form id="accountFormatForm" method="post">
            	<input type="hidden" name="formatType" value="ACCT"/>
                <table width="100%">
                    <tr>
                        <td><strong>P</strong> - Product</td>
                        <td><strong>S</strong> - Sequence</td>
                        <td><strong>C</strong> - Check Digit</td>
                        <td><strong>B</strong> - Branch Code</td>
                        <td><strong>X</strong> - Character</td>
                    </tr>
                </table>
                <table width="100%" class="divider">
                    <tr>
                        <td width="170"><label for="formatCode2">Account Type:</label></td>
                        <td>
                            <select name="formatCode" id="formatCode2" style="width:200px">
                                <?php echo html_entity_decode($accountTypes); ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td><label for="formatValue2">Format Layout:</label></td>
                        <td><input type="text" name="formatValue" id="formatValue2" style="width:250px" class="validate[required] upperCase" value="<?php echo $formatValue2; ?>" maxlength="30"/></td>
                    </tr>
                        <td><label for="weights2">Weights:</label></td>
                        <td><input type="text" name="weights" id="weights2" style="width:250px" class="validate[required,minSize[<?php echo $weights2MaxLength; ?>],maxSize[<?php echo $weights2MaxLength; ?>]] numbersOnly" value="<?php echo $weights2; ?>" maxlength="<?php echo $weights2MaxLength; ?>"/></td>
                    </tr>
                        <td><label for="prodCode">Product Code:</label></td>
                        <td><input type="number" name="prodCode" id="prodCode" style="width:80px" class="validate[required] numbersOnly" value="<?php echo $prodCode; ?>" maxlength="3" min="1" max="999" step="1"/></td>
                    </tr>
                        <td><label for="gracePeriod2">Grace Period:</label></td>
                        <td><input type="number" name="gracePeriod" id="gracePeriod2" style="width:80px" class="validate[required] numbersOnly" value="<?php echo $gracePeriod2; ?>" maxlength="3" min="1" max="999" step="1"/></td>
                    </tr>
                    </tr>
                        <td><label for="minDays2">Min. No. of Days Inactive:</label></td>
                        <td><input type="number" name="minDays" id="minDays2" style="width:80px" class="validate[required] numbersOnly" value="<?php echo $minDays2; ?>" maxlength="3" min="1" max="999" step="1"/></td>
                    </tr>
                    <tr>
                    	<td><label for="charFormat">Character Format:</label></td>
                        <td>
                            <select name="charFormat" id="charFormat" style="width:120px">
                                <?php echo html_entity_decode($charFormat); ?>
                            </select>
                        </td>
                    </tr>
                </table>
            </form>
        </div>
	</div>
</div>
<div id="bottom">
	<span class="buttons floatLeft">
    	<button id="saveBtn" value="maintenance/settings/submit" >Save</button
        ><button id="updateEMV">Update EMV Cards</button
        >
	</span>
    <span class="buttons floatRight">
    	<!--<button id="resetBtn" type="reset">Reset</button
        >--><button class="closebtn">Close</button>
	</span>
</div>
<style>
fieldset {
	padding: 3px 5px 10px 5px;
	border: 1px solid #aaa;
}
legend {
	padding: 0 5px;
}
input[type="checkbox"], input[type="radio"] {
	position: relative;
	top: 2px;
}
</style>
<script>
$(function () {
	var saveBtn = '#saveBtn';
	var updateBtn = '#updateEMV';
	var form = $('#cardFormatForm');
	
    initSession('<?php echo $sessionExp; ?>');
	var option = {
        ajaxFormValidation: true,
        onBeforeAjaxFormValidation: function () {
            waitMessage('Saving...');
        },
        onAjaxFormComplete: function (a, b, data, d) {
			messageBox(data.message);
            if (data.success === true) {
				$(MSGBOX).one('dialogbeforeclose', function () {
					window.location.hash = 'welcome';
				});
            }
        },
        scroll: false
    };
	
	form.validationEngine(option).validationEngine('attach');
	
    $(TABCONTENT).hide();
    $(TABS).first().addClass('active').show();
    $(TABCONTENT).first().show();
    $(TABS).click(function () {
        if (!$(this).hasClass('active')) {
            //form.validationEngine('hideAll');
            $(TABS).removeClass('active');
            $(this).addClass('active');
            $(TABCONTENT).hide();
			
            var tab = $(this).find('a').attr('href');
            $(tab).show();
			
			form.validationEngine('detach');
			form = $(tab + ' form');
			form.validationEngine(option).validationEngine('attach');
        }
        return false;
    });
	
	$('#formatCode1').change(function () {
		var selected = $(this).find('option:selected');
		var formatValue = selected.attr('formatvalue');
		var cardType = selected.text();
		var weights = selected.attr('weights');
		var expYears = selected.attr('expyears');
		var gracePeriod = selected.attr('graceperiod');
		var minDays = selected.attr('mindays');
		var len = selected.attr('charlen');
		var trnexpr = selected.attr('trnexpr') == 1;
		var trnicvv = selected.attr('trnicvv') == 1;
		var trnarqc = selected.attr('trnarqc') == 1;
		var trnarpc = selected.attr('trnarpc') == 1;
		var trncntr = selected.attr('trncntr') == 1;

		var trntrack2 	 = selected.attr('trntrack2') == 1;
		var trnicctrack2 = selected.attr('trnicct2') == 1;
		var trncvv 		 = selected.attr('trncvv') == 1;
		var servicvv 	 = selected.attr('servicvv');
		var servcvv 	 = selected.attr('servcvv');
		
		$('#formatValue1').val(formatValue);
		$('#description2').val(cardType);

		$('#weights1')
			.attr({
				maxlength: len,
				class: 'validate[required,minSize['+ len +'],maxSize['+ len +']] numbersOnly'
			})
			.val(weights);
		$('#expYears1').val(expYears);
		$('#gracePeriod1').val(gracePeriod);
		$('#minDays1').val(minDays);
		$('#trnExpr').attr('checked', trnexpr);
		$('#trnICVV').attr('checked', trnicvv);
		$('#trnARQC').attr('checked', trnarqc);
		$('#trnARPC').attr('checked', trnarpc);
		$('#trnCntr').attr('checked', trncntr);

		$('#trnTrack2').attr('checked', trntrack2);
		$('#trnICCT2').attr('checked', trnicctrack2);
		$('#trnCVV').attr('checked', trncvv);
		$('#servcICVV').attr('value', servicvv);
		$('#servcCVV').attr('value', servcvv);

	});

	$('#formatCode1').trigger('change');	
	
	$('#formatCode2').change(function () {
		var selected = $(this).find('option:selected');
		var formatValue = selected.attr('formatvalue');
		var weights = selected.attr('weights');
		var prodCode = selected.attr('prodcode');
		var gracePeriod = selected.attr('graceperiod');
		var minDays = selected.attr('mindays');
		var len = selected.attr('charlen');
		
		$('#formatValue2').val(formatValue);
		$('#weights2')
			.attr({
				maxlength: len,
				class: 'validate[required,minSize['+ len +'],maxSize['+ len +']] numbersOnly'
			})
			.val(weights);
		$('#prodCode').val(prodCode);
		$('#gracePeriod2').val(gracePeriod);
		$('#minDays2').val(minDays);
	});
	
	$('#formatValue1').bind('keyup keypress', function(e) {
        var n = e.which;

		switch (true) {
			case (n === 0): //delete
			case (n === 8): //backspace
			case (n === 9): //tab
			case (n === 13): //enter
			case (n === 45): //-
			case (n === 112):
			case (n === 80): //P
			<?php echo $p2; ?>
			case (n === 105): //i
			case (n === 73): //I
			case (n === 110): //n
			case (n === 78): //N
			case (n === 99): //c
			case (n === 67): //C
			case (n === 98): //b
			case (n === 66): //B
				return true;
				break;
			default:
				return false;
				break
		}
    });
	
	$('#formatValue2').bind('keyup keypress', function(e) {
        var n = e.which;
		
		switch (true) {
			case (n === 0): //delete
			case (n === 8): //backspace
			case (n === 9): //tab
			case (n === 13): //enter
			case (n === 45): //-
			case (n === 112): //p
			case (n === 80): //P
			case (n === 115): //s
			case (n === 83): //S
			case (n === 99): //c
			case (n === 67): //C
			case (n === 98): //b
			case (n === 66): //B
			case (n === 120): //x
			case (n === 88): //X
				return true;
				break;
			default:
				return false;
				break
		}
    });
	
	$('#formatValue1, #formatValue2').focusout(function () {
		var str = this.value.replace(/C|-/gi, '');
		var len = str.length;
		
		if (this.id === 'formatValue1') {
			var instance = $('#weights1');
		} else {
			var instance = $('#weights2');
		}
		
		//change maxlength and trim
		instance.attr({
			maxlength: len,
			class: 'validate[required,minSize['+ len +'],maxSize['+ len +']] numbersOnly'
		})
			.val(instance.val().substr(0, len));
			
		form.validationEngine('hideAll');
		
		this.value = this.value.replace(/^-+|-+$/g, ''); //trim "-"
	});

	$(updateBtn).click(function(e) {	
		messageBox('Update existing EMV cards?','Confirm','confirm',function() {

			var type = $('#formatCode1').find('option:selected');
			var id = type.val();

			requests.push(
				$.ajax({
					type: 'POST',
					url: 'maintenance/settings/updateemvcards',
					data: {
						id: id
					},
					dataType: 'json',
					beforeSend: function() {
						waitMessage('Updating EMV cards...');
					},
					success: function(data) {
						if (data['success'] === true) {
							messageBox(data.message);
						}
					}
				})
			);
		});
		e.preventDefault();
	});
	
	$(saveBtn).click(function (e) {
		if (form.validationEngine('validate') === true) {
			messageBoxV2('Are all entries correct?', 'Confirm', {
				OK: function () {
					form.submit();
				},
				Cancel: function () {
					$(this).dialog('close');
				}
			});
		}
		e.preventDefault();
	});
});
</script>