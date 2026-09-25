<div style="width:500px">
    <h1>Withdrawal Transaction</h1>
    <div id="content">
    	<div style="font-size:15px; padding-bottom: 10px">Customer Card Information</div>
    	<table>
        	<tr>
            	<td width="150"><label for="cardNo" class="idName">Card Number: <span class="red">*</span></label></td>
                <td><input type="text" name="cardNo" id="cardNo" style="width: 200px"/></td>
			</tr>
            <tr>
                <td><label>Customer Name:</label></td>
                <td><input type="text" style="width: 200px"/></td>
			</tr>
			<tr>
                <td><label>Card Status:</label></td>
                <td><input type="text" style="width: 200px"/></td>
            </tr>
            <tr>
                <td><label>Passbook Status:</label></td>
                <td><input type="text" style="width: 200px"/></td>
            </tr>
        </table>
        
        <div class="divider"></div>
        <div style="font-size:15px; padding-bottom: 10px">Cash Transaction Details</div>
        
        <table>
        	<tr>
                <td><label>Currency Used:</label></td>
                <td><input type="text" style="width: 200px" value="Philippine Peso"/></td>
			</tr>
        	<tr>
            	<td width="150"><label><strong>Withdrawal Amount:</strong></label></td>
                <td><input type="text" style="width: 200px" value="PHP 0.00"/></td>
			</tr>
            <tr>
                <td><label>Service Charge Value:</label></td>
                <td><input type="text" style="width: 200px" value="PHP 0.00"/></td>
			</tr>
            <tr>
                <td><label>Total Amount Due:</label></td>
                <td><input type="text" style="width: 200px" value="PHP 0.00"/></td>
            </tr>
			<tr>
                <td><label>Teller Drawer ID:</label></td>
                <td><input type="text" style="width: 200px"/></td>
            </tr>
            <tr>
                <td><label>Drawer Balance:</label></td>
                <td><input type="text" style="width: 200px" value="PHP 0.00"/></td>
            </tr>
        </table>
    </div>
</div>
<div id="bottom">
	<span class="buttons floatLeft">
    	<button id="submitBtn">Submit</button>
    </span>
	<span class="buttons floatRight">
    	<button class="closebtn">Close</button>
    </span>
</div>