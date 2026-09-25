<div id="cardUpload">
	<h1>Card Order via File Upload</h1>
    <div id="content">
    	<span class="hint">
        	Please upload a valid *.csv file
        </span>

       <!--  <button id="uploadBtn">Upload</button> -->
       <form>
			<input id="uploadCSV" type="file" name="myCSV" accept=".csv"/>
       </form>

        <textarea id="uploadStatus" style="width: 330px; height: 200px" readonly>
        	
        </textarea>
    </div>
</div>
<div id="bottom">
	<span class="buttons floatLeft">
		<button id="submitBtn">Submit</button>
	</span>
    <span class="buttons floatRight">
    	<button id="backBtn">Back</button
        ><button class="closebtn">Close</button>
	</span>
</div>

<script>
$(function () {
	var content = null;
	$('#uploadCSV').change(loadFile);

	$('#uploadCSV').click(function (e) {
		$('form').reset();
		content = null;
	});

	$('#backBtn').click(function () {
		window.location.hash = 'card/orderrequest';
	});

	function loadFile(e)
	{
		var file = e.target.files[0];

		if (file.name.split(".")[1].toUpperCase() == "CSV") {
			//$('#uploadStatus').text( "File Name: "+file.name + " | "+file.size+" Bytes." );

			var fileReader = new FileReader();

			fileReader.onload = function (event) {
				content = event.target.result;

				$('#uploadStatus').text( content );
			};
			fileReader.readAsText(file);

		} else {
			alert('Invalid csv file');
			e.target.parentNode.reset();
		}
	}

	$('#submitBtn').click(function (e) {

		if (!content) {
			messageBox('Please upload a valid CSV file first.');
			return;
		}

		$.ajax({
			type: 'POST',
			url: 'card/batchupload/submit',
			dataType: 'json',
			data: {
				csv: content
			},
			beforeSend: function() {
				waitMessage('Uploading...');
			},
			error: function(jqXHR, textStatus, errorThrown) {
				messageBox(jqXHR.responseText);
			},
			success: function(data) {
				if ($(MSGBOX).length > 0) {
					$(MSGBOX).dialog('close');
				}
				$('#uploadStatus').text( data.message );

				//$('form').reset(); //not working :(
				content = null;
			}
		});
		e.preventDefault();
	});
});
</script>