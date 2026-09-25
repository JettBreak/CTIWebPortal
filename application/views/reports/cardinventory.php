<form id="reportViewerForm" method="post" target="formTarget">
<div id="cardInventory">
    <h1>Card Inventory Report</h1>
    <?php echo html_entity_decode($branches); ?>
    <table id="cardInv" cellpadding="0" cellspacing="0">
        <thead>
            <tr>
                <th width="100">Description</th>
                <th width="80">Count</th>
            </tr>
        </thead>
        <tbody id="tBody">
            <?php echo html_entity_decode($cardInv); ?>
        </tbody>
    </table>
</div>
<div id="bottom">
    <span class="buttons floatLeft">
        <button id="prevBtn" value="reports/cardinventory/preview">Preview</button>
    </span>
    <span class="buttons floatRight">
        <button class="closebtn">Close</button>
    </span>
</div>
</form>
<style>
/*.dataTables_scrollBody {
	min-height:300px !important;
	max-height:380px !important;
}*/
#cardInv {
	width: 100%;
	border-collapse: collapse !important;
    border-spacing: 0;
}
#cardInv thead {
	font-size: 14px;
	text-transform: uppercase;
	background: #222;
}
#cardInv thead th {
	border: 1px solid #777;
	padding: 5px;
}
#cardInv tbody tr:nth-child(even) {
	background: #303030;
}
#cardInv tbody tr:nth-child(odd) {
	background: #333;
}
#cardInv td {
	border: 1px solid #222;
	padding: 4px;
}
.fg-toolbar  {
	display: none;
}
</style>
<script>
$(function () {
    var prevBtn = '#prevBtn',
       	refreshBtn = '#refreshBtn',
		form = $('form');
		
    //$(DATATABLE).find('tbody tr').die('dblclick');

   /* oTable = $(DATATABLE).dataTable({
        bRetrieve: true,
        bJQueryUI: true,
		bLengthChange: false,
		bSort: false,
        iDisplayLength: -1,
        aaSorting: [],
        sScrollY: '100%',
        sScrollX: '100%',
        sPaginationType: 'full_numbers'/*,
        'fnInitComplete': function () {
            //this.fnAdjustColumnSizing()
        }*/
    //});
	//$.fn.dataTableExt.iApiIndex = 0;*/
	
    $(prevBtn).click(function (e) {
        pdfViewer('pdfviewer/preview', 'Preview');
		//
		e.preventDefault();
		
		setTimeout(function () {
			form.submit();
		}, 500);
    });
	
	$('#branchList').change(function () {
		requests.push(
			$.ajax({
				type: 'GET',
				url: 'reports/cardinventory/getdata',
				data: {
					brseqno: this.value
				},
				dataType: 'json',
				beforeSend: function () {
					waitMessage('Retrieving data...');
				},
				success: function(data) {
					if (data.success === true) {
						$('#tBody').html(data.details);
					}
					$(MSGBOX).dialog('close');
				}
			})
		);
	});
});
</script>