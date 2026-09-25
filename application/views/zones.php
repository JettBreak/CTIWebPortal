<form id="zonesForm" method="post" action="zones/getdata">
    <div style="padding-bottom:1em;">
        <table width="100%" style="margin:10px">
            <tr>
                <td><label for="zoneCity">Town / City:</label></td>
                <td><label for="zoneProv">Province:</label></td>
                <td><label for="zoneZip">ZIP Code:</label></td>
                <td rowspan="2" style="padding-top:10px;"><button id="searchBtn">Search</button></td>
            </tr>
            <tr>
                <td><input type="text" name="zoneCity" id="zoneCity" style="width:130px" maxlength="30" class="alphaNum"/></td>
                <td><input type="text" name="zoneProv" id="zoneProv" style="width:130px" maxlength="30" class="alphaNum"/></td>
                <td><input type="text" name="zoneZip" id="zoneZip" style="width:70px" maxlength="4" class="validate[custom[onlyNumberSp]] numbersOnly"/></td>
        </table>
    </div>
</form>
<table class="dataTable">
    <thead>
        <tr>
            <th>Town / City</th>
            <th>Province</th>
            <th width="60">ZIP Code</th>
        </tr>
    </thead>
    <tbody>
    </tbody>
</table>
<style>
.dataTables_scrollBody{max-height:150px !important;}#modalDialog{padding:0;}
</style>
<script>
$(function () {
    var form = $('#zonesForm'),
        searchBtn = '#searchBtn';
    //$(DATATABLE).find('tbody tr').die('dblclick');
    oTable = $(DATATABLE).dataTable({
        'bRetrieve': true,
        'bJQueryUI': true,
		'oLanguage': {
			sSearch: 'Filter: '
		},
        'sScrollY': '100%',
        'sPaginationType': 'full_numbers',
		'fnRowCallback': function (nRow, aData, iDisplayIndex) {	
            $(nRow).attr('id', aData[0]).unbind('click dblclick').click(function () {
				//close dialog boxes
				if ($(MSGBOX).length > 0) {
					$(MSGBOX).dialog('close');
				}
				
				//highlight rows
				$('tbody tr').removeClass('rowSelected');
                $(this).addClass('rowSelected');
				
            }).dblclick(function () {
                retrieveZone();
            });
			$('tbody tr').removeClass('rowSelected');

            return nRow;
        }
    });
    form.validationEngine({
        ajaxFormValidation: true,
        onBeforeAjaxFormValidation: function () {
            waitMessage('Sending search transaction...')
        },
        onAjaxFormComplete: function (a, b, data, d) {
            if (data.success === true) {
                $(MSGBOX).dialog('close');
                $.fn.dataTableExt.iApiIndex = 0;
                oTable.fnClearTable(0);
                oTable.fnAddData(data['result']);
                oTable.fnDraw();
                oTable.fnAdjustColumnSizing();
                $(DIALOG).dialog('option', 'position', 'center')
            } else if (data.success === false) {
                messageBox(data['result']);
                form[0].reset()
            }
        },
        scroll: false
    });
    form.validationEngine('attach');
	
    $(DIALOG).dialog({
        close: function () {
            $('#custCity, #custProvince, #custZipCode').removeAttr('disabled');
            $(this).remove();
        }
    });
    $(searchBtn).click(function (e) {
        if ($(MSGBOX).length === 0) {
            if (($('#zoneCity').val() === '') && ($('#zoneProv').val() === '') && ($('#zoneZip').val() === '')) {
                messageBox('At least one search parameter must have a value');
                e.preventDefault();
            }
        } else {
            $(MSGBOX).dialog('close');
            return false;
        }
    })
});

function retrieveZone() {
	var selected = $('.rowSelected');
    if (selected.length > 0) {
		
        var city = selected.find('td').eq(0).text(),
            prov = selected.find('td').eq(1).text(),
            zip = selected.find('td').eq(2).text();
			
        $('#custCity').val(city);
        $('#custProvince').val(prov);
        $('#custZipCode').val(zip);
        $(DIALOG).dialog('close');
    } else {
		messageBox('Please select an item from the list');
	}
}
</script>