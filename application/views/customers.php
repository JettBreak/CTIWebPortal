<form id="customersForm" method="post" action="customer/search/submit">
    <div>
        <table width="100%" style="margin:10px" id="custSearchTable">
            <tr>
                <td width="120"><label for="custNo">Customer No.:</label></td>
                <td><input type="text" name="custNo" id="custNo" style="width:100px" maxlength="11" class="validate[custom[onlyNumberSp]] numbersOnly"/></td>
            </tr>
            <tr>
                <td><label for="custLastName">Last Name:</label></td>
                <td><input type="text" name="custLastName" id="custLastName" style="width:260px" maxlength="30" class="validate[custom[onlyLetterSp]] lettersOnly"/></td>
            </tr>
            <tr>
                <td><label for="custFirstName">First Name:</label></td>
                <td><input type="text" name="custFirstName" id="custFirstName" style="width:260px" maxlength="30" class="validate[custom[onlyLetterSp]] lettersOnly"/></td>
            </tr>
            <tr>
                <td><label for="custMiddleName">Middle Name:</label></td>
                <td><input type="text" name="custMiddleName" id="custMiddleName" style="width:260px" maxlength="30" class="validate[custom[onlyLetterSp]] lettersOnly"/></td>
            </tr>
        </table>
    </div>
</form>
<div id="dtContainer" class="hidden">
<table id="custDT">
    <thead>
        <tr>
            <th width="50">CIFNo.</th>
            <th>Customer Name</th>
            <th>Address</th>
        </tr>
    </thead>
    <tbody>
    </tbody>
</table>
</div>
<style>
#custSearchTable td {
	vertical-align: middle;
	padding: 2px;
}
.dataTables_scrollBody{max-height:150px !important;}#modalDialog{padding:0;}
</style>
<script>
$(function () {
    var form = $('#customersForm'),
		dtContainer = '#dtContainer',
        searchBtn = '#searchBtn';
	
	$('#custNo').focus();
	
    oTable.customer = $('#custDT').dataTable({
        bRetrieve: true,
        bJQueryUI: true,
		aoColumns: ([
			{ sClass: 'centerAlign' }, //cifseqno
			null, //customer name
			null //address
		]),
		oLanguage: {
			sSearch: 'Filter: '
		},
        sScrollY: '100%',
        sPaginationType: 'full_numbers',
		fnRowCallback: function (nRow, aData, iDisplayIndex) {	
            $(nRow).attr('id', aData[0]).unbind('click dblclick').click(function () {
				//close dialog boxes
				if ($(MSGBOX).length > 0) {
					$(MSGBOX).dialog('close');
				}
				
				//highlight rows
				$('tbody tr').removeClass('rowSelected');
                $(this).addClass('rowSelected');
				
            }).dblclick(function () {
                retrieveCustomer();
            });
			$('tbody tr').removeClass('rowSelected');

            return nRow;
        }
    });
	
    form.validationEngine({
        ajaxFormValidation: true,
        onBeforeAjaxFormValidation: function () {
			if (($('#custNo').val() === '') && ($('#custLastName').val() === '') && ($('#custFirstName').val() === '') && ($('#custMiddleName').val() === '')) {
				messageBox('At least one search parameter must have a value');
				return false;
			} else {
				waitMessage('Sending search transaction...');
			}
        },
        onAjaxFormComplete: function (a, b, data, d) {
			if (data.pass === true) {
				//populate hidden cifseqno field and custname
                $(MSGBOX).dialog('close');
				$(DIALOG).dialog('close');
				
				$('#cifseqno').val(data['id']);
				$('#custName').val(data['name']);
            } else {
				if (data.success === true) {
					$(MSGBOX).dialog('close');
					
					//display result table
					$(dtContainer).show();
					$(WRAPPER).height('auto');
					
					$.fn.dataTableExt.iApiIndex = 0;
					oTable.customer.fnClearTable(0);
					oTable.customer.fnAddData(data.result);
					oTable.customer.fnDraw();
					oTable.customer.fnAdjustColumnSizing();
					
					$(DIALOG).dialog('option', 'position', 'center');
				} else if (data.success === false) {
					messageBox(data.result);
					form[0].reset();
				}
			}
			$('#custName').validationEngine('hidePrompt');
        },
        scroll: false
    });
    form.validationEngine('attach');
	
    $(DIALOG).dialog({
        close: function () {
            $('#custName').removeAttr('disabled');
            $(this).remove();
        }
    });
	
	//clears input
	$('#custNo').bind('change keyup', function() {
		$('#custLastName, #custFirstName, #custMiddleName').val('');
	});
	
	$('#custLastName, #custFirstName, #custMiddleName').bind('change keyup', function() {
		$('#custNo').val('');
	});
	
	$('#custNo, #custLastName, #custFirstName, #custMiddleName').keypress(function (e) {
		if (e.which === 13) {
			form.submit();
		}
	});
	
	function retrieveCustomer() {
		var selected = $('.rowSelected');
		if (selected.length > 0) {
			//populate hidden cifseqno field and custname
			var cifseqno = selected.find('td').eq(0).text();
			var customer = selected.find('td').eq(1).text();
			
			$('#cifseqno').val(cifseqno);
			$('#custName').val(customer);
			
			$(DIALOG).dialog('close');
		} else {
			messageBox('Please select a customer from the list');
		}
	}
});
</script>