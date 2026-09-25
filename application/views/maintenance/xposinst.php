<form id="posForm" method="post">
    <div id="posNew" style="width:450px">
        <h1><?php echo $header; ?></h1>
        <div id="content">
            <table width="100%">
                <tr>
                    <td colspan="2"><span class="floatRight">All fields are required</span></td>
                </tr>
                <tr>
                    <td width="250"><label for="instid">Partner Institution ID:</label></td>
                    <td width="210"><input type="text" name="instid" id="instid" style="width:200px" value="<?php echo isset($instid) ? $instid : NULL; ?>" class="validate[required] numbersOnly" maxlength="8" <?php echo isset($instid) ? 'readOnly' : ''; ?> autofocus/></td>
                    
                </tr>
                <tr>
                    <td width="250"><label for="instname">Partner Institution Name:</label></td>
                    <td width="210"><input type="text" name="instname" id="instname" style="width:200px" value="<?php echo isset($instname) ? $instname : NULL; ?>" class="validate[required,custom[onlyLetterNumberSp]] alphaNum" maxlength="30"/></td>
                    
                </tr>
                <tr>
                    <td width="250"><label for="insttype">Partner Institution Type:</label></td>
                    <td width="210"><select name="insttype" id="insttype" style="width:212px">
                            <?php echo html_entity_decode($insttype); ?>
                        </select></td>
                </tr>
                <tr><td>&nbsp;</td></tr>
                <tr>
                    <td width="250"><label for="contact">Contact Person:</label></td>
                    <td width="210"><input type="text" name="contact" id="contact" style="width:200px" value="<?php echo isset($contact) ? $contact : NULL; ?>" class="validate[required] alphaNumCustomSP" maxlength="30" /></td>
                    
                </tr>
                <tr>
                    <td width="250"><label for="telno">Telephone Number:</label></td>
                    <td width="210"><input type="text" name="telno" id="telno" style="width:200px" value="<?php echo isset($telno) ? $telno : NULL; ?>" class="validate[required] numbersOnly" maxlength="30" /></td>
                    
                </tr>
                <tr>
                    <td width="250"><label for="email">Email Address:</label></td>
                    <td width="210"><input type="text" name="email" id="email" style="width:200px" value="<?php echo isset($email) ? $email : NULL; ?>" class="validate[required] alphaNumCustomSP" maxlength="30" /></td>
                </tr>
            </table>
            <br><br>
            <!-- <table class="dataTable">
                <thead>
                    <tr>
                        <th width="100">Terminal Code</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table> -->
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
					window.location.hash = 'maintenance/xpartnerlist'
				}
			});
        },
        scroll: false
    });
    form.validationEngine('attach');

    /*oTable = $(DATATABLE).dataTable({
        bRetrieve: true,
        bJQueryUI: true,
        aLengthMenu: DTLENGTHMENU,
        aaSorting: [],
        sScrollY: '100%',
        sScrollX: '100%',
        sPaginationType: 'full_numbers',
        fnInitComplete: function () {
            getData();
        },
        fnRowCallback: function (nRow, aData, iDisplayIndex) {
            $('td:eq(0)', nRow).attr('align', 'center');
            
            var termCode = oTable.fnGetData(oTable.fnGetPosition(nRow))[0];
            
            $(nRow).attr('id', aData[0]).unbind('click dblclick').click(function () {
                if ($(MSGBOX).length > 0) {
                    $(MSGBOX).dialog('close');
                }
                $('tbody tr').removeClass('rowSelected');
                $(this).addClass('rowSelected');
                
                $(tCode).val(termCode);
                $(buttons).removeAttr('disabled')
            }).dblclick(function () {
                window.location.hash = 'maintenance/posedit/' + termCode
            });
            //$(buttons).attr('disabled', 'disabled');
            //$(tCode).val('');
            return nRow
        }
    });*/
	
    $(submitBtn).click(function (e) {
        e.preventDefault();
        if (form.validationEngine('validate') === true) {
            messageBox('<?php echo $submitBtnMsg; ?>', 'Warning', 'confirm', function () {
                form.submit()
            })
        }
    });
	
    $(backBtn).click(function () {
        window.location.hash = 'maintenance/xpartnerlist';
        return false
    });
	
	/*$('#location').change(function () {
		var selected = $(this).find('option:selected');
		
		$('#areaName').val(selected.attr('areaname'));
		$('#branchName').val(selected.attr('brname'));
	});*/

    /*function getData() {
        var brCode = $(branch).val();
        
        //serialize AJAX request
        requests.push(
            jqxhr = $.ajax({
                type: 'GET',
                url: 'maintenance/xpartnerlist/getdata',
                dataType: 'json',
                data: {
                    brcode: brCode
                },
                beforeSend: function() {
                    abortAJAXRequests();
                    waitMessage('Retrieving POS list...');
                },
                error: function(jqXHR, textStatus, errorThrown) {

                },
                success: function(data) {
                    if (data.success) {
                        $.fn.dataTableExt.iApiIndex = 0;
                        oTable.fnClearTable(0);
                        oTable.fnAddData(data.details);
                        oTable.fnDraw();
                        //oTable.fnAdjustColumnSizing();
                    }*//* else {
                        messageBox('There are no terminals in this branch');
                        $(branch).val(lastVal);
                    }*/
                /*},
                complete: function() {
                    $(MSGBOX).dialog('close');
                }
            })
        );
        
        return false;
    }*/
});
</script>