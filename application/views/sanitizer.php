<div style="width: 500px;">
    <h1>SANITIZER</h1>
    <div id="content">
    	<textarea style="width: 400px; height: 300px" id="input"><?php echo html_entity_decode('<SOAP-ENV:Envelope xmlns:SOAP-ENC="http://schemas.xmlsoap.org/soap/encoding/" xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">

<SOAP-ENV:Body>

<ns:AccountRegistration xmlns:ns="http://121.96.88.20:19010">

<Message Type="0200">

<PAN>123456789012345678</PAN>

<ProcessingCode>961004</ProcessingCode>

<HostCIFNo>123456789012345678</HostCIFNo>

<AccountNo>123456789012345678</AccountNo>

<AccountType>SA</AccountType>

<TransactionTime>99/99/99:99:99:99</TransactionTime>

<TransactionDate>99/99/99</TransactionDate>

<HostTraceNumber>123456</HostTraceNumber>

<AcquirerCode>AUB</AcquirerCode>

</Message>

</ns:AccountRegistration>

</SOAP-ENV:Body>

</SOAP-ENV:Envelope>'); ?></textarea>  
    </div>
</div>
<div id="bottom">
	<span class="buttons floatRight">
    	<button id="sanitizeBtn">Sanitize!</button
        ><button class="closebtn">Close</button>
    </span>
</div>
<script>
$(function () {
	$('#sanitizeBtn').click(function (e) {
		
		var unclean = $('#input').val();
		var clean = unclean.replace(/\n/g, "")
				.replace(/[\t ]+\<"/)
				.replace(/\>[\t ]+\<"/)
				.replace(/\>[\t ]+$/g, ">")
		
		$('#input').val(clean);
	});
});
</script>
