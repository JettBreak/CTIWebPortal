<div id="custInfo">
    <h1>Customer Information <span class="floatRight"><?php echo html_entity_decode($custnum); ?></span></h1>
    <ul class="tabs fullTabs">
        <li><a href="#personalInfo">Personal Information</a></li>
        <li><a href="#contactInfo">Contact Information</a></li>
        <?php echo html_entity_decode($hidden); ?>
    </ul>
    <div class="tab_container">
        <div id="personalInfo" class="tab_content">
            <div class="customer-personal-overview">
            <div id="custPreview" class="imgWrapper customer-personal-photo"><img src="<?php echo $srcImg; ?>" width="100" height="100" alt="Customer picture"/></div>
            <div class="customer-personal-main">
            <strong>Customer Name:</strong>
            <table width="80%">
                <tr>
                    <td>Prefix:</td>
                    <td>Last Name:</td>
                    <td>First Name:</td>
                    <td>Middle Name:</td>
                    <td>Suffix:</td>
                </tr>
                <tr>
                    <td><input type="text" style="width:40px" value="<?php echo $prefix; ?>" readonly/></td>
                    <td><input type="text" style="width:122px" value="<?php echo $lastName; ?>" readonly/></td>
                    <td><input type="text" style="width:122px" value="<?php echo $firstName; ?>" readonly/></td>
                    <td><input type="text" style="width:122px" value="<?php echo $middleName; ?>" readonly/></td>
                    <td><input type="text" style="width:40px" value="<?php echo $suffix; ?>" readonly/></td>
                </tr>
            </table>
            <div class="divider customer-personal-columns">
                <table class="customer-personal-column">
                    <tr>
                        <td width="40%">Gender:</td>
                        <td colspan="3"><input type="text" style="width:140px" value="<?php echo $gender; ?>" readonly/></td>
                    </tr>
                    <tr>
                        <td>Civil Status:</td>
                        <td colspan="3"><input type="text" style="width:140px" value="<?php echo $civil; ?>" readonly/></td>
                    </tr>
                    <tr>
                        <td>Date of birth:</td>
                        <td><input type="text" style="width:140px" value="<?php echo $bDay; ?>" readonly/></td>
                    </tr>
                    <tr>
                        <td>Place of birth:</td>
                        <td><input type="text" style="width:140px" value="<?php echo $bPlace; ?>" readonly/></td>
                    </tr>
                </table>
                <table class="customer-personal-column">
                    <tr>
                        <td>Nationality:</td>
                        <td><input type="text" style="width:150px" value="<?php echo $nationality; ?>" readonly/></td>
                    </tr>
                    <tr>
                        <td>Occupation:</td>
                        <td><input type="text" style="width:150px" value="<?php echo $occupation; ?>" readonly/></td>
                    </tr>
                    <tr>
                        <td>SSS:</td>
                        <td><input type="text" style="width:150px" value="<?php echo $sss; ?>" readonly/></td>
                    </tr>
                    <tr>
                        <td>TIN:</td>
                        <td><input type="text" style="width:150px" value="<?php echo $tin; ?>" readonly/></td>
                    </tr>
                </table>
            </div>
            </div>
        </div>
    </div>
        <div id="contactInfo" class="tab_content">
            <div class="customer-address-contact">
            <table class="customer-address-table">
                <tr>
                    <td width="30%">Country of Origin:</td>
                    <td colspan="3"><input type="text" style="width:250px" value="<?php echo $country; ?>" readonly/></td>
                </tr>
                <tr>
                    <td>Address Type:</td>
                    <td colspan="3"><input type="text" style="width:250px" value="<?php echo $addrType; ?>" readonly/></td>
                </tr>
                <tr>
                    <td>Street 1:</td>
                    <td colspan="3"><input type="text" style="width:250px" value="<?php echo $address1; ?>" readonly/></td>
                </tr>
                <tr>
                    <td>Street 2:</td>
                    <td colspan="3"><input type="text" style="width:250px" value="<?php echo $address2; ?>" readonly/></td>
                </tr>
                <tr>
                    <td>City:</td>
                    <td width="140"><input type="text" style="width:120px" value="<?php echo $city; ?>" readonly/></td>
                    <td width="70">ZIP Code:</td>
                    <td><input type="text" style="width:32px" value="<?php echo $zipCode; ?>" readonly/></td>
                </tr>
                <tr>
                    <td>Province:</td>
                    <td colspan="3"><input type="text" style="width:250px" value="<?php echo $province; ?>" readonly/></td>
                </tr>
            </table>
            <div class="customer-contact-section">
            <strong>Contact Numbers:</strong>
            <table>
                <tr>
                    <td>&nbsp;</td>
                    <td>Area Code &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Phone No.</td>
                </tr>
                <tr>
                    <td width="10%">Home:</td>
                    <td><input type="text" style="width:200px" value="<?php echo $hPhone; ?>" readonly/></td>
                </tr>
                <tr>
                    <td>Office:</td>
                    <td><input type="text" style="width:200px" value="<?php echo $oPhone; ?>" readonly/></td>
                </tr>
                <tr>
                    <td>Mobile:</td>
                    <td><input type="text" style="width:200px" value="<?php echo $mPhone; ?>" readonly/></td>
                </tr>
                <tr>
                    <td>E-mail:</td>
                    <td><input type="text" style="width:200px" value="<?php echo $email; ?>" readonly/></td>
                </tr>
            </table>
            </div>
            </div>
        </div>
        <div id="cardsLinked" class="tab_content nopadding">
            <table class="dataTable">
                <thead>
                    <tr>
                        <th>Card Number</th>
                        <th>Status</th>
                        <th>Last Activity</th>
                        <th>Last Movement</th>
                    </tr>
                </thead>
                <tbody>
                    <?php echo html_entity_decode($cardLink); ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<div id="bottom">
	<span class="buttons floatLeft hidden">
    	<button id="removeBtn">Remove</button
        >
    </span>
	<span class="buttons floatRight">
        <button id="searchBtn">Search</button
        ><button id="closebtn" class="closebtn" value="<?php echo $onclose; ?>" >Close</button>
    </span>
</div>
<style>
.dataTables_scrollBody{height:195px !important;max-height:195px !important;}
</style>
<script>
$(function () {
    var b = '#searchBtn',
        c = '#closebtn',
        $form = $('form'),
        $customerInfo = $('#custInfo'),
        $tabs = $customerInfo.find('ul.tabs li'),
        $tabContent = $customerInfo.find('.tab_content');
    initSession('<?php echo $sessionExp; ?>');
    $(DATATABLE).find('tbody tr').die('dblclick');
    oTable = $(DATATABLE).dataTable({
        'bRetrieve': true,
        'bJQueryUI': true,
        'aaSorting': [],
        'sScrollY': '100%',
        'sScrollX': '100%',
        'sPaginationType': 'full_numbers'
    });
    $tabContent.hide();
    $tabs.removeClass('active').first().addClass('active');
    $tabContent.first().show();
    $tabs.click(function () {
        if (!$(this).hasClass('active')) {
            $form.validationEngine('hideAll');
            $tabs.removeClass('active');
            $(this).addClass('active');
            $tabContent.hide();
            var tab = $(this).find('a').attr('href');
            $customerInfo.find(tab).show();
			
			if (tab === '#cardsLinked') {
				$('#removeBtn').hide();
			} else {
				$('#removeBtn').hide();
			}
			
            $(WRAPPER).height('auto');
            $.fn.dataTableExt.iApiIndex = 0;
            oTable.fnAdjustColumnSizing();
        }
        return false
    });
    $(b).click(function () {
        if ($(c).val() === 'verify') {
            window.location.hash = 'customer/verification';
        } else if ($(c).val() === 'editcust') {
            window.location.hash = 'customer/editapproval';
        } else {
            window.location.hash = 'customer/search/info';
        }
        return false
    });
    $(c).click(function () {
        if ($(c).val() === 'verify') {
            window.location.hash = 'customer/verification';
        } else if ($(c).val() === 'editcust') {
            window.location.hash = 'customer/editapproval';
        } else {
            window.location.hash = 'customer/search/info';
        }
        return false
    });
    $('#removeBtn').hide();
	$('#removeBtn').click(function () {
		messageBoxV2('Are you sure you want to delete this customer?', 'Confirm', {
			'OK': function () {
				//serialize AJAX request
				requests.push(
					jqxhr = $.ajax({
						type: 'POST',
						url: 'customer/info/delete',
						dataType: 'json',
						data: {
							cifseqno: <?php echo $cifseqno; ?>
						},
						beforeSend: function() {
							abortAJAXRequests();
							waitMessage('Deleting customer entry...');
						},
						error: function(jqXHR, textStatus, errorThrown) {
		
						},
						success: function(data) {
							messageBoxV2(data.message, 'Information', {
								'OK': function () {
									window.location.hash = 'customer/search/info';
									$(this).dialog('close');
								}
							});
						}
					})
				);
			},
			'Cancel': function () {
				$(this).dialog('close');
			}
		});
	});
});
</script>
