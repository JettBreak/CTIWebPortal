<form id="onlineLimitsForm" method="post" action="card/onlinelimits/submit">
<input type="hidden" name="prseqno" value="<?php echo $prseqno; ?>"/>
<input type="hidden" name="limitseqno" value="<?php echo $limitseqno; ?>"/>
<input type="hidden" name="trxcode" value="<?php echo $trxcode; ?>"/>
<input type="hidden" name="cycle" value="<?php echo $cycle; ?>"/>
<input type="hidden" name="duralimit" value="<?php echo $duralimit; ?>"/>
    <table>
        <tr>
            <td width="250"><label for="cycleavail">Cycle Amount (Available):</label></td>
            <td><input type="text" name="cycleavail" id="cycleavail" maxlength="9" value="<?php echo $cycleavail; ?>" readonly/></td>
        </tr>
        <tr>
            <td><label for="cyclemax">Maximum Cycle Amount:</label></td>
            <td><input type="text" name="cyclemax" id="cyclemax" maxlength="12" value="<?php echo $cyclemax; ?>" class="currencyOnly validate[required,funcCall[checkCyclemax]]"<?php echo $cyclemaxAttr; ?>/></td>
        </tr>
        <tr>
            <td><label for="ctravail">Available Counter:</label></td>
            <td><input type="text" name="ctravail" id="ctravail" maxlength="9" value="<?php echo $ctravail; ?>" readonly/></td>
        </tr>
        <tr>
            <td><label for="ctrmax">Maximum Counter:</label></td>
            <td><input type="number" name="ctrmax" id="ctrmax" maxlength="4" value="<?php echo $ctrmax; ?>" class="numbersOnly" step="1" min="0" max="9999"/></td>
        </tr>
        <tr>
            <td><label for="tranmin">Minimum Transaction Amount:</label></td>
            <td><input type="text" name="tranmin" id="tranmin" maxlength="12" value="<?php echo $tranmin; ?>" class="currencyOnly validate[required,funcCall[checkInput]]"<?php echo $tranminAttr; ?>/></td>
        </tr>
        <tr>
            <td><label for="tranmax">Maximum Transaction Amount:</label></td>
            <td><input type="text" name="tranmax" id="tranmax" maxlength="12" value="<?php echo $tranmax; ?>" class="currencyOnly validate[required,funcCall[checkInput]]"<?php echo $tranmaxAttr; ?>/></td>
        </tr>
        <tr>
            <td><label for="nonfeectravail">No Fee Counter (Available):</label></td>
            <td><input type="text" id="nonfeectravail" maxlength="9" value="<?php echo $nonfeectravail; ?>" readonly/></td>
        </tr>
        <tr>
            <td><label for="nonfeectrmax">Maximum No Fee Counter:</label></td>
            <td><input type="number" name="nonfeectrmax" id="nonfeectrmax" maxlength="4" value="<?php echo $nonfeectrmax; ?>" class="numbersOnly" step="1" min="0" max="9999"/></td>
        </tr>
        <tr>
            <td><label for="nonfeetranavail">No Fee Amount (Available):</label></td>
            <td><input type="text" id="nonfeetranavail" maxlength="9" value="<?php echo $nonfeetranavail; ?>" readonly/></td>
        </tr>
        <tr>
            <td><label for="nonfeetranmax">Maximum No Fee Amount:</label></td>
            <td><input type="text" name="nonfeetranmax" id="nonfeetranmax" maxlength="12" value="<?php echo $nonfeetranmax; ?>" class="currencyOnly"<?php echo $nonfeetranmaxAttr; ?>/></td>
        </tr>
        <tr>
            <td><label for="nonfeecycle">No Fee Cycle Period:</label></td>
            <td><input type="text" id="nonfeecycle" maxlength="9" value="<?php echo $nonfeecycle; ?>" readonly/></td>
        </tr>
    </table>
</form>
<style>
.formError, .formErrorArrow {
	z-index: 9999;
}
#modalDialog td {
	padding:2px 0 !important;
}
#onlineLimitsForm input {
	width: 150px;
	text-align: right;
}
td {
	vertical-align: middle;
}
</style>
<script>
$(function () {
	var form = $('#onlineLimitsForm');
	
	form.validationEngine({
        ajaxFormValidation: true,
        onBeforeAjaxFormValidation: function () {
            waitMessage('Saving...');
        },
        onAjaxFormComplete: function (a, b, data, d) {
            if (data.success === true) {
				//update datatable
				var columns = {
					4: '#cyclemax',
					6: '#ctrmax',
					7: '#tranmin',
					8: '#tranmax',
					12: '#nonfeectrmax',
					14: '#nonfeetranmax'
				};		
				$.each(columns, function(index, value) { 
					oTable[2].fnUpdate($(value).val(), oTable[2].fnGetPosition($('#<?php echo $trxcode; ?>')[0]), index, false); //max counter
				});
				
				oTable[2].fnAdjustColumnSizing();
				
				messageBoxV2(data.message, 'Information', {
					'OK': function() {
						$(DIALOG).dialog('close');
					}
				});
            } else {
				messageBox(data.message);
			}
        },
        scroll: false
    });
    form.validationEngine('attach');
});

function checkInput() {
	var tranmin = $('#tranmin').val();
	var tranmax = $('#tranmax').val();
	var cyclemax = $('#cyclemax').val();
		
	if ( tranmax !== 'N/A' ) {
		
		tranmin = parseFloat(tranmin.replace(/,/gi, ''));
		tranmax = parseFloat(tranmax.replace(/,/gi, ''));
		cyclemax = parseFloat(cyclemax.replace(/,/gi, ''));
		
		if ( !(tranmax >= tranmin) ) {
			return 'Maximum Transaction Amount must be greather than <br />or equal to Minimum Transaction Amount';
		} else {
			$('#tranmin').validationEngine('hidePrompt');
			$('#tranmax').validationEngine('hidePrompt');
		}
	
	}
}

function checkCyclemax() {
	var cyclemax = $('#cyclemax').val();
	var tranmax = $('#tranmax').val();
	
	if ( tranmax !== 'N/A' ) {
		
		cyclemax = parseFloat(cyclemax.replace(/,/gi, ''));
		tranmax = parseFloat(tranmax.replace(/,/gi, ''));
	
		if ( !(tranmax <= cyclemax) ) {
			return 'Maximum Transaction Amount must be less than <br />or equal to Maximum Cycle Amount';
		} else {
			$('#cyclemax').validationEngine('hidePrompt');
		}
		
	}
}
</script>