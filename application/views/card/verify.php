<form id="cardVerify" method="post">
    <div id="cardVerify">
        <h1>Search Card</h1>
        <div id="content"><span class="hint">Enter Card Number then press Search<span style="margin-left:100px"><span class="red">*</span> - Required Fields</span></span>
            <table width="100%">
            	<tr>
                	<td width="120"><label for="brseqno">Branch:</label></td>
                    <td colspan="3">
                    	<select id="brseqno" style="width:212px">
                            <?php echo html_entity_decode($branches); ?>
                            
                        </select>
                    </td>
                </tr>
                <tr>
                    <td width="120"><label for="cardtype">Card Type:</label></td>
                    <td colspan="3">
                        <select id="cardtype" style="width:212px">
                            <?php echo html_entity_decode($cardtype); ?>
                            
                        </select>
                    </td>
                </tr>
                <tr>
                    <td><label for="cardNo">Card Number: <span class="red">*</span></label></td>
                    <td colspan="3"><select name="cardBIN" id="cardBIN" style="width:80px">
                            <?php echo html_entity_decode($cardBIN); ?>
                        </select
                        ><input type="text" name="cardNo" id="cardNo" style="width:120px" class="validate[required,custom[onlyNumberSp]]" placeholder="<?php echo $cardNoPlaceholder; ?>"/>
                        </td>
                </tr>
            </table>
        </div>
    </div>
    <div id="bottom">
    	<span class="buttons floatLeft">
        	<button type="submit" value="<?php echo $formAction; ?>">Search</button
            ><?php echo html_entity_decode($buttons); ?>
        </span>
        <span class="buttons floatRight">
        	<button id="resetBtn" type="reset">Clear</button
            ><button class="closebtn">Close</button>
        </span>
	</div>
</form>
<script>
$(function () {
	var browseBtn = '#browseBtn';
    var form = $('form');
	var branch = '#brseqno';
	var cardBIN = '#cardBIN';
	var cardNo = '#cardNo';
    initSession('<?php echo $sessionExp; ?>');
	
    form.validationEngine({
        ajaxFormValidation: true,
        onBeforeAjaxFormValidation: function () {
            waitMessage('Verifying card number...');
        },
        onAjaxFormComplete: function (a, b, data, d) {
            if (data.success === true) {
				if ($(MSGBOX).length > 0) {
                	$(MSGBOX).dialog('close');
				}
                window.location.hash = data['page'];
            } else {
				messageBox(data.message);
				$(MSGBOX).one('dialogbeforeclose', function () {
					$(cardNo).val('').focus();
				});
            }
        },
        scroll: false
    });
    form.validationEngine('attach');
	
	$(browseBtn).click(function () {
		window.location.hash = 'card/browse';
		return false;
	});
	
	$(branch + ',' + cardBIN + ',#cardtype').change(function () {		
		var selected = $('#cardtype').find('option:selected');
        var format = selected.attr('format');
        
        var brChar = 'B';
        var cnt = format.split(brChar).length - 1;
        var rep = brChar.repeat(cnt);
        var brCode = $(branch).find('option:selected').attr('value');
        var br = '';
        var mask = '';
        if (cnt > 0) {
            br = zeroPad(cnt, String(brCode));
        } else {
            br = zeroPad(cnt, '');
        }
        mask = format.replace(rep, br);

        var prcdChar = 'P';
        cnt = format.split(prcdChar).length - 1;
        rep = prcdChar.repeat(cnt);
        var repU = 'N';
        if (cnt > 0) {
            pr = zeroPad(cnt, $('#cardtype').val());
        } else {
            pr = zeroPad(cnt, '');
        }
        mask = mask.replace(rep, pr);

       // alert('cntr:'+format);
      //  alert('rep:'+mask);

		var placeholder = mask.replace(/N/g, '_');
		
		$(cardNo)
			.val('')
			.mask(mask)
			.attr('placeholder', placeholder);
	});
	
	$.mask.definitions = {
		'N': '[0-9]'
	};
	
	$(cardNo).mask('<?php echo $cardNoMask; ?>');
});
</script>