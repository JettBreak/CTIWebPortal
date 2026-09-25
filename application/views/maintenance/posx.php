<form id="posForm" method="post">
    <div id="posNew" style="width:600px;">
        <h1><?php echo $header; ?></h1>
        <div id="content">
            <table width="100%">
                <tr>
                    <td width="120"><label for="posCode" class="idName">POS Code:</label></td>
                    <td width="250"><input type="text" name="posCode" id="posCode" style="width:200px" <?php echo html_entity_decode($termCodeParams); ?> autofocus/></td>
                    <td colspan="2"><span class="floatRight">All fields are required</span></td>
                </tr>
                <tr>
                    <td><label for="posID">POS ID:</label></td>
                    <td><input type="text" name="posID" id="posID" style="width:200px" class="alphaNum" maxlength="20" value="<?php echo $termID; ?>" <?php echo $termIDParams; ?>/></td>
                    <td width="90"><label for="luno">LUNO:</label></td>
                    <td><input type="text" name="luno" id="luno" style="width:110px" value="<?php echo $luno; ?>" class="validate[required] numbersOnly" maxlength="4"/></td>
                </tr>
                <tr>
                    <td><label for="description">Description:</label></td>
                    <td colspan="3"><input name="description" id="description" type="text" style="width:200px" maxlength="50" class="validate[required]" value="<?php echo $desc; ?>"/></td>
                </tr>
                <tr>
                    <td><label for="posLang">POS Language:</label></td>
                    <td colspan="3"><select name="posLang" id="posLang" style="width:212px">
                            <?php echo html_entity_decode($posLang); ?>
                        </select></td>
                </tr>
                <tr>
                    <td><label for="description">Merchant ID:</label></td>
                    <td colspan="3"><input name="mercID" id="mercID" type="text" style="width:200px" class="validate[required] numbersOnly" maxlength="18" value="<?php echo $mercID; ?>"/></td>
                </tr>
                <tr>
                    <td><label for="location">Location:</label></td>
                    <td colspan="3"><select name="location" id="location" style="width:212px" class="validate[required]">
                            <?php echo html_entity_decode($location); ?>
                        </select></td>
                </tr>
                <tr>
                    <td><label for="areaName">Area:</label></td>
                    <td colspan="3"><input type="text" name="areaName" id="areaName" style="width:200px" value="<?php echo $areaName; ?>" readonly/></td>
                </tr>
                <tr>
                    <td><label for="branchName">Branch:</label></td>
                    <td colspan="3"><input type="text" name="branchName" id="branchName" style="width:200px" value="<?php echo $branchName; ?>" readonly/></td>
                </tr>
                <?php echo (isset($POSCashOut) ? $POSnbsp : NULL); ?>
                <tr>
                    <td><label for="institutions" id="lblinstname">Partner Inst Name:</label></td>
                    <td colspan="3"><select name="institutions" id="institutions" style="width:212px" class="validate[required]" >
                            <?php echo html_entity_decode($institutions); ?>
                        </select></td>
                </tr>   
                <tr>
                    <td><label for="instid" id="lblinstid">Partner Inst ID:</label></td>
                    <td><input type="text" name="instid" id="instid" style="width:200px" class="alphaNum" maxlength="20" value="<?php echo isset($instID)  ? $instID : NULL; ?>" 
                            <?php echo isset($instIDParams) ? $instIDParams : NULL; ?> readOnly/>
                        </td>
                </tr>
                <tr>
                    <td><label for="outlet" id="lbloutlet">Outlet Name:</label></td>
                    <td colspan="3"><select name="outlet" id="outlet" style="width:212px" class="validate[required]" >
                            <?php echo html_entity_decode($outlets); ?>
                        </select></td>
                </tr>   
                <tr>
                    <td><label for="outletID" id="lbloutletid">Outlet ID:</label></td>
                    <td><input type="text" name="outletID" id="outletID" style="width:200px" class="alphaNum" maxlength="20" value="<?php echo isset($outletID)  ? $outletID : NULL; ?>" 
                            <?php echo isset($outletIDParams)  ? $outletIDParams : NULL; ?> readOnly/>
                        </td>

                </tr>
                <?php echo (isset($POSCashOut) ? $POSnbsp : NULL); ?>
                <tr>
                    <td><label for="statusx">Status:</label></td>
                    <td colspan="3"><input type="text" name="statusx" id="statusx" style="width:200px" value="<?php echo $status; ?>" readonly/></td>
                </tr>
            </table>
        </div>
    </div>
    <div id="bottom">
        <span class="buttons floatLeft">
            <button id="submitBtn" value="<?php echo $submitBtnVal; ?>">Submit</button
            ><button type="reset">Reset</button>
		</span><span class="buttons floatRight">
            <button id="backBtn">Back</button
            ><button class="closebtn">Close</button>
		</span>
	</div>
</form>
<script>
$(function () {
    var submitBtn = '#submitBtn',
        backBtn = '#backBtn',
        form = $('form');
		
    initSession('<?php echo $sessionExp; ?>');
    form.validationEngine({
        ajaxFormValidation: true,
        onBeforeAjaxFormValidation: function () {
            waitMessage('<?php echo $waitMsg; ?>')
        },
        onAjaxFormComplete: function (a, b, data, d) {
			messageBox(data.message);
			$(MSGBOX).one('dialogbeforeclose', function () {
				if (data.success === true) {
					window.location.hash = 'maintenance/pos'
				}
			});
        },
        scroll: false
    });
    form.validationEngine('attach');
	
    $(submitBtn).click(function (e) {
        e.preventDefault();
        if (form.validationEngine('validate') === true) {
            messageBox('<?php echo $submitBtnMsg; ?>', 'Warning', 'confirm', function () {
                form.submit()
            })
        }
    });
	
    $(backBtn).click(function () {
        window.location.hash = 'maintenance/pos';
        return false
    });
	
	$('#location').change(function () {
		var selected = $(this).find('option:selected');
		
		$('#areaName').val(selected.attr('areaname'));
		$('#branchName').val(selected.attr('brname'));
	});

    //console.log(<?php echo $POSCashOut; ?>);

    if ('<?php echo $POSCashOut; ?>' == '') {
        $('#instid').hide();
        $('#lblinstid').hide();
        $('#institutions').hide();
        $('#lblinstname').hide();
        $('#outlet').hide();
        $('#lbloutlet').hide();
        $('#outletID').hide();
        $('#lbloutletid').hide();
    } else {
        $('#instid').show();
        $('#lblinstid').show();
        $('#institutions').show();
        $('#lblinstname').show();
        $('#outlet').show();
        $('#lbloutlet').show();
        $('#outletID').show();
        $('#lbloutletid').show();
    }

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
</script>