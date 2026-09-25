<form id="cardListForm" method="post">
<div id="cardList">
	<h1>Card List</h1>
    <table class="dataTable">
        <thead>
            <tr>
                <th width="120">Card No.</th>
                <th>Customer Name</th>
                <th width="100">Card Type</th>
                <th width="120">Last Activity</th>
                <th width="150">Status</th>
                <th>tokenid</th>
            </tr>
        </thead>
    </table>
</div>
<div id="bottom">
	<span class="buttons floatLeft">
    	<button id="enrollBtn">New Card</button
        ><button id="viewBtn" disabled>View/Update Card</button
        ><button id="exportbtn">Export Cards</button
        >
	</span>
    <span class="buttons floatRight">
    	<button id="refreshBtn">Refresh</button
        ><button class="closebtn">Close</button>
	</span>
</div>
</form>
<style>
.dataTables_scrollBody {
	min-height: 200px !important;
	max-height: 250px !important;
}
.dataTables_filter input {
	width: 150px;
}
.dataTables_custom {
	padding: 4px 0;
}
</style>

<script>
$(function() {
	//waitMessage('Retrieving Card List...');
	
	var viewBtn = '#viewBtn',
		enrollBtn = '#enrollBtn',
		refreshBtn	= '#refreshBtn',
		card = {};
	
	initSession('<?php echo $sessionExp; ?>');
	var buttons = viewBtn;
	
	var oTable = $(DATATABLE).dataTable({
		bRetrieve:true,
		bJQueryUI: true,
		bServerSide: true,
		aoColumns: ([ 
			null, //card type
			null, //card type
			null, //customer
			null, //customer
			null, //customer
			{ bVisible: false } //tokenid
		]),
		sAjaxSource: 'card/browse/getdata',
		sScrollY: '100%',
		sScrollX: '100%', // Required for viewing tables with lots of columns at low resolution - otherwise columns are mis-aligned
		sPaginationType: 'full_numbers',
		fnInitComplete: function() {	
			//getData();
			//$(MSGBOX).dialog('close');
		},
		fnRowCallback: function(nRow, aData, iDisplayIndex) {
			$('td:eq(0)', nRow).attr('align', 'center');
			$(nRow).attr('id', aData[0]).unbind('click dblclick').click(function () {
				//close dialog boxes
				if ($(MSGBOX).length > 0) {
					$(MSGBOX).dialog('close');
				}
				
				//highlight rows
				$('tbody tr').removeClass('rowSelected');
                $(this).addClass('rowSelected');
				
				//set temp var
				card = {
					number: aData[0],
					readonly: false,
					module: 'browse',
					tokenid: aData[5]
				}
				
                $(buttons).removeAttr('disabled')
            }).dblclick(function () {
                $(viewBtn).trigger('click');
            });
			$('tbody tr').removeClass('rowSelected');
            $(buttons).attr('disabled', 'disabled');
			
			//unset temp obj
			card = {};
			return nRow;
		},
		fnDrawCallback: function () {
			$('.dataTables_filter input').focus();
			//this.fnDraw();
		},
		fnServerData: function ( sSource, aoData, fnCallback ) {
			/* Add some extra data to the sender */
			aoData.push( { 
				name: 'cardStatus',
				value: $('#cardStatus').val() ? $('#cardStatus').val() : 0
			},{ 
				name: 'cardType',
				value: $('#cardType').val() ? $('#cardType').val() : '-1'
			} );
			
			requests.push(
				jqxhr = $.ajax( {
					url: sSource,
					data: aoData,
					dataType: 'json',
					cache: false,
					beforeSend: function () {
						abortAJAXRequests();
						waitMessage('Retrieving data...');
					},
					success: function(data) {
						fnCallback(data)
					},
					error: function (xhr, error, thrown) {
						if ( error == "parsererror" ) {
							alert( "DataTables warning: JSON data from server could not be parsed. "+
								"This is caused by a JSON formatting error." );
						}
					},
					complete: function () {
						$(MSGBOX).dialog('close');
					}
				} )
			);
		}
	});
	$.fn.dataTableExt.iApiIndex = 0;
	
	$('.ui-toolbar:first').append('<div class="dataTables_custom floatLeft" style="margin: 0 0 0 20px">'+
		'Status: ' +
		'<select id="cardStatus" style="width:170px">'+
			'<option value="0">ALL</option>' +
			<?php echo html_entity_decode($cardStatusList); ?>
		'</select>' +
	'</div>'+
	'<div class="dataTables_custom floatLeft" style="margin: 0 0 0 20px">'+
		'Card Type: ' +
		'<select id="cardType" style="width:170px">'+
			'<option value="-1">ALL</option>' +
			<?php echo html_entity_decode($cardTypeList); ?>
		'</select>' +
	'</div>');
	
	$('#cardStatus, #cardType').change(function () {
		oTable.fnDraw();
	})
	/*jqxhr.always(function () {
		
	}).done(function () {
		$(MSGBOX).dialog('close');
	});*/
	
	$(viewBtn).click(function (e) {
		if (card) {
			//serialize AJAX request
			requests.push(
				$.ajax({
					type: 'POST',
					url: 'card/browse/cache',
					data: card,
					dataType: 'json',
					success: function(data) {
						if (data.success === true) {
							window.location.hash = 'card/info';
						}
					}
				})
			);
        } else {
            messageBox('Please select an item from the list')
        }
        e.preventDefault();
	});

	$(exportbtn).click(function (e) {
		//serialize AJAX request
		requests.push(
			$.ajax({
				type: 'POST',
				url: 'card/browse/getcardlist',
				dataType: 'json',
				complete: function(data) {
					$(MSGBOX).dialog('close');
				}
			})
		);
        e.preventDefault();
	});
	
	$(enrollBtn).click(function (e) {
		window.location.hash = 'card/enrollment';
		e.preventDefault();
	});
	
	$(refreshBtn).click(function (e) {
		oTable.fnDraw();
		e.preventDefault();
	});
	
	/*function getData() {
		//serialize AJAX request
		requests.push(
			jqxhr = $.ajax({
				type: 'GET',
				url: 'card/browse/getdata',
				dataType: 'json',
				beforeSend: function() {
					abortAJAXRequests();
					waitMessage('Retrieving Card List...');
				},
				error: function(jqXHR, textStatus, errorThrown) {

				},
				success: function(data) {
					//populate table
					$.fn.dataTableExt.iApiIndex = 0;
					oTable.fnClearTable(0);
					oTable.fnAddData(data['details']);
					oTable.fnDraw();
					//oTable.fnAdjustColumnSizing();
				},
				complete: function() {
					if ($(MSGBOX).length > 0) {
						$(MSGBOX).dialog('close');
					}
				}
			})
		);
	}*/
});
</script>