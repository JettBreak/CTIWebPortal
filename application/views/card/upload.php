<div id="cardUpload">
	<h1>Card Order via File Upload</h1>
    <div id="content">
    	<span class="hint">
        	Please upload a valid *.csv file
        </span>
        <embed 
        	src="uploader.swf"
            quality="best"
            pluginspage="http://get.adobe.com/flashplayer/"
            type="application/x-shockwave-flash"
            width="360"
            height="60"
            wmode="transparent"
            FlashVars="s=<?php echo $sessionID; ?>"
		/>
        <textarea id="uploadStatus" style="width: 330px; height: 200px" readonly>
        	
        </textarea>
    </div>
</div>
<div id="bottom">
    <span class="buttons floatRight">
    	<button class="closebtn">Close</button>
	</span>
</div>

<script>
function wait() {
	messageBox(waitMessage('Uploading card request...'), 'Please wait');
}

function showStatus(json) {
	$(MSGBOX).dialog('close');
	
	var data     = $.parseJSON(json),
		$result  = null;
		$errDesc = data['errorDesc'];
		$reqCnt  = data['total'];
		$goodCnt = data['successful'];
		$badCnt  = data['errorCount'];
		$errors  = data['errorDesc'];
		$good    = null;
		$bad     = null;
		
	$good  = 'Successful request(s): ' + $goodCnt;
	$bad   = '\nNumber of errors: ' + $badCnt;
	$count = '\nTotal requests: ' + $reqCnt;
	
	$result  = $errDesc + $good + $bad + $count;
	$('#uploadStatus').html($result);
}
</script>