<form id="issuanceForm" method="post">
    <input type="hidden" name="cifseqno" value="<?php echo $cifseqno; ?>"/>
    <input type="hidden" name="prseqno" id="prseqno" value=""/>
    <input type="hidden" name="acctType" id="acctType" value=""/>
    <div id="cardIssuance">
        <h1>Customer Card Issuance</h1>
        <div id="content"><span class="hint"> Enter Card Number then press ENTER key </span><span class="hint floatRight"><span class="red">*</span> - Required Fields </span>
            <table width="100%">
                <tr>
                    <td width="30%"><label for="custName">Customer Name:</label></td>
                    <td><input type="text" name="custName" id="custName" style="width:200px" value="<?php echo $custName; ?>" readonly/></td>
                </tr>
                <tr>
                	<td width="120"><label for="brseqno">Branch:</label></td>
                    <td colspan="3">
                    	<select name="brseqno" id="brseqno" style="width:212px">
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
                        ><input type="text" name="cardNo" id="cardNo" style="width:120px" class="validate[required,custom[onlyNumberSp]]" placeholder="<?php echo $cardNoPlaceholder; ?>"/><button id="verfiftBtn" value="card/issuance/verify">Verify</button>
                        </td>
                </tr>
                <tr>
                    <td><label for="cardStatus">Card Status:</label></td>
                    <td><input type="text" name="cardStatus" id="cardStatus" style="width:200px" readonly/></td>
                </tr>
                <?php echo html_entity_decode($hiddenInput); ?>
            </table>
        </div>
    </div>
    <div id="bottom">
    	<span class="buttons floatLeft">
        	<button id="issueBtn" value="card/issuance/submit" disabled>Issue</button>
        </span>
        <span class="buttons floatRight">
            <button id="searchBtn">Search Customer</button
            ><button type="reset">Reset</button
            ><button class="closebtn">Close</button>
        </span>
	</div>
    <div id="dtContainer" class="hidden">
        <table class="dataTable">
            <thead>
                <tr>
                    <th>&nbsp;</th>
                    <th>Account No.</th>
                    <th>Account Type</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3">
                        &nbsp;
                        <a title="Checks all the checkboxes above" href="#" id="checkall">Check All</a> | 
                        <a title="Unchecks all the checkboxes above" href="#" id="uncheckall">Uncheck All</a>
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
</form>
<style>
.dataTables_scrollBody{max-height:200px !important;}
</style>
<script>
$(function () {	
    var searchBtn = '#searchBtn',
        verifyBtn = '#verifyBtn',
        issueBtn = '#issueBtn',
        resetBtn = 'button:reset',
        form = $('form'),
        dtContainer = $('#dtContainer');
	var branch = '#brseqno';
	var cardBIN = '#cardBIN';
	var cardNo = '#cardNo';
    var showdt = false;
    var embName = '#embossName';
    initSession('<?php echo $sessionExp; ?>');

    //$(DATATABLE).find('tbody tr').die('dblclick');
    oTable = $(DATATABLE).dataTable({
        bRetrieve: true,
        bJQueryUI: true,
        aaSorting: [],
        oLanguage: {
            sSearch: 'Filter: '
        },
        sScrollY: '100%',
        sScrollX: '100%',
        sPaginationType: 'full_numbers',
        fnInitComplete: function () {
            
        },
        fnRowCallback: function (nRow, aData, iDisplayIndex) {
            $('td:eq(0)', nRow).attr('align', 'center');
            $(nRow).attr('id', aData[0])
                .unbind('click dblclick')
                .dblclick(function () {
                    //hack
           //         searchRedirect(aData[0], aData[1]);
                });
            return nRow;
        }
    });
    $.fn.dataTableExt.iApiIndex = 0;
		
    form.validationEngine({
        ajaxFormValidation: true,
        onBeforeAjaxFormValidation: function () {
            if ($('#prseqno').val() === '') {
                var msg = 'Verifying card number...'
            } else {
                var msg = 'Sending transaction...'
            }
            waitMessage(msg);
        },
        onAjaxFormComplete: function (a, b, data, d,e) {
            //e.preventDefault();
            if (data.verified === true) {
                //$(MSGBOX).dialog('close');
                $('#prseqno').val(data['prseqno']);
                $('#acctType').val(data['acctType']);
                $('#cardStatus').val(data['cardStat']);
                $('#cardType').val(data['cardType']);
                $(verifyBtn).attr('disabled', 'disabled');
                $(issueBtn).removeAttr('disabled');
				
				$('#newCardNo2').attr('readonly', 'readonly');

                if (data.hasAccount === true) {
                    //show result
                    //$(MSGBOX).dialog('close');
                    if (data.showlist === true) {
                        dtContainer.show();
                        oTable.fnClearTable(0);
                        oTable.fnAddData(data['result']);
                        oTable.fnDraw();
                        oTable.fnAdjustColumnSizing();
                        showdt = true;
                    }
                    $(WRAPPER).height('auto');
                    //end
                    $(MSGBOX).dialog('close');
                    //populate datatable
                    //$.fn.dataTableExt.iApiIndex = 0;
                    //end
                } else if (data.success === false) {
                    //$(resetBtn).trigger('click');
                    messageBox(data.message2);
                    $(MSGBOX).one('dialogbeforeclose', function () {
                        $(resetBtn).trigger('click');
                    });
                } else if (data.success === true) {
                    $(WRAPPER).height('auto');
                    $(MSGBOX).dialog('close');
                }

            } else if (data['verified'] === false) {
                messageBox(data.message);
                $(MSGBOX).one('dialogbeforeclose', function () {
                    $(resetBtn).trigger('click');
                });
            }
            
            if (data.successissuance === true) {
                messageBox(data.message);
                $(MSGBOX).one('dialogbeforeclose', function () {
                     window.location.hash = 'customer/search/issuance'
                });
                //$(resetBtn).trigger('click');
            } else if (data.successissuance === false) {
                messageBox(data.message);
                $(MSGBOX).one('dialogbeforeclose', function () {
                    $(resetBtn).trigger('click');
                });
            }

            //e.preventDefault();
        },
        scroll: false
    });
    form.validationEngine('attach');
	
    $(searchBtn).click(function () {
        window.location.hash = 'customer/search/issuance';
        return false
    });
	
    $(resetBtn).click(function (e) {
        $('#prseqno, #acctType').val('');
        $(verifyBtn).removeAttr('disabled');
        $('#issueBtn').attr('disabled', 'disabled');
		$(cardNo).removeAttr('readonly');
        $('#uncheckall').trigger('click');
        dtContainer.hide();

        //e.preventDefault();
    });
	
    $(issueBtn).click(function (e) {
        var a = $('#cardBIN option:selected').text() + $(cardNo).val();
        var chkcnt = 0;

       // $('input', oTable.fnGetNodes()).each( function() {
       //     if ($('input', oTable.fnGetNodes()).attr('checked')) {
                chkcnt++;
       //     }
        //});
        if ($(DATATABLE).find('input:checked').length > 0 || showdt === false) {
             if (form.validationEngine('validate') === true) {
                if (!$(embName)) {
                    messageBox('Activate Card No. <strong>[' + a + ']</strong> ?', 'Confirm', 'confirm', function () {
                        form.submit();
                    });
                 } else {
                    if ($(embName).val() == '') {
                        messageBox('No EMBOSS NAME entered. <br>Activate Card No. <strong>[' + a + ']</strong> ?', 'Confirm', 'confirm', function () {
                            form.submit();
                        });
                    } else {
                        messageBox('Activate Card No. <strong>[' + a + ']</strong> ?', 'Confirm', 'confirm', function () {
                            form.submit();
                        });
                    }
                 }
            }
        } else {
            messageBox('Please select atleast one account.');
        }
        
        e.preventDefault();
        //messageBox('Activate Card No. <strong>[' + a + ']</strong> ?', 'Confirm', 'confirm', function () {
            //$(MSGBOX).dialog('close');
        //    form.submit()
        //});
		//e.preventDefault();
    });
	
    $('#checkall').click( function() {
        $('input', oTable.fnGetNodes()).each( function() {
            $('input', oTable.fnGetNodes()).attr('checked','checked');
        });
    });


    $('#uncheckall').click( function() {
        $('input', oTable.fnGetNodes()).each( function() {
            $('input', oTable.fnGetNodes()).removeAttr('checked','checked');
        });
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

        //alert('cntr:'+format);
        //alert('rep:'+mask);

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