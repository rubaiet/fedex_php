<?php
// Copyright 2009, FedEx Corporation. All rights reserved.
// Version 12.0.0

require_once('../../../library/fedex-common.php5');

$newline = "<br />";
//The WSDL is not included with the sample code.
//Please include and reference in $path_to_wsdl variable.
$path_to_wsdl = "../../../wsdl/ShipService_v17.wsdl";

define('SMARTPOST_LABEL', 'smartpostlabel.pdf');  // PDF label file. Change to file-extension .png for creating a PNG label (e.g. shiplabel.png)

ini_set("soap.wsdl_cache_enabled", "0");

$client = new SoapClient($path_to_wsdl, array('trace' => 1)); // Refer to http://us3.php.net/manual/en/ref.soap.php for more information

$request['WebAuthenticationDetail'] = array(
	'ParentCredential' => array(
		'Key' => getProperty('parentkey'), 
		'Password' => getProperty('parentpassword')
	),
	'UserCredential' => array(
		'Key' => getProperty('key'), 
		'Password' => getProperty('password')
	)
);

$request['ClientDetail'] = array(
	'AccountNumber' => getProperty('shipaccount'), 
	'MeterNumber' => getProperty('meter')
);
$request['TransactionDetail'] = array('CustomerTransactionId' => '*** Ground Domestic Shipping Request using PHP ***');
$request['Version'] = array(
	'ServiceId' => 'ship', 
	'Major' => '17', 
	'Intermediate' => '0', 
	'Minor' => '0'
);
$request['RequestedShipment'] = array(
	'ShipTimestamp' => date('c'),
	'DropoffType' => 'REGULAR_PICKUP', // valid values REGULAR_PICKUP, REQUEST_COURIER, DROP_BOX, BUSINESS_SERVICE_CENTER and STATION
	'ServiceType' => 'SMART_POST', // valid values STANDARD_OVERNIGHT, PRIORITY_OVERNIGHT, FEDEX_GROUND, ...
	'PackagingType' => 'YOUR_PACKAGING', // valid values FEDEX_BOX, FEDEX_PAK, FEDEX_TUBE, YOUR_PACKAGING, ...
	'Shipper' => addShipper(),
	'Recipient' => addRecipient(),
	'ShippingChargesPayment' => addShippingChargesPayment(),
	'SmartPostDetail' => addSmartPostDetail(), 
	'LabelSpecification' => addLabelSpecification(), 
	'PackageCount' => 1,
	'RequestedPackageLineItems' => array(
		'0' => addPackageLineItem1()
	)
); 

   
                                                                                                                           
try {
	if(setEndpoint('changeEndpoint')){
		$newLocation = $client->__setLocation(setEndpoint('endpoint'));
	}
	
	$response = $client->processShipment($request); // FedEx web service invocation  

    if ($response->HighestSeverity != 'FAILURE' && $response->HighestSeverity != 'ERROR'){
        printSuccess($client, $response);
        
        // Create PNG or PDF label
        // Set LabelSpecification.ImageType to 'PNG' for generating a PNG label
    
        $fp = fopen(SMARTPOST_LABEL, 'wb');   
        fwrite($fp, ($response->CompletedShipmentDetail->CompletedPackageDetails->Label->Parts->Image));
        fclose($fp);
        echo '<a href="./'.SMARTPOST_LABEL.'">'.SMARTPOST_LABEL.'</a> was generated.'; 
    }else{
        printError($client, $response);
    }

	writeToLog($client);    // Write to log file
} catch (SoapFault $exception) {
    printFault($exception, $client);
}



function addShipper(){
	$shipper = array(
		'Contact' => array(
			'PersonName' => 'Sender Name',
			'CompanyName' => 'Sender Company Name',
			'PhoneNumber' => '1234567890'
		),
		'Address' => array(
			'StreetLines' => array('Address Line 1'),
			'City' => 'Austin',
			'StateOrProvinceCode' => 'TX',
			'PostalCode' => '73301',
			'CountryCode' => 'US'
		)
	);
	return $shipper;
}
function addRecipient(){
	$recipient = array(
		'Contact' => array(
			'PersonName' => 'Recipient Name',
			'CompanyName' => 'Recipient Company Name',
			'PhoneNumber' => '1234567890'
		),
		'Address' => array(
			'StreetLines' => array('Address Line 1'),
			'City' => 'Herndon',
			'StateOrProvinceCode' => 'VA',
			'PostalCode' => '20171',
			
			//'City' => 'Toledo',
			//'StateOrProvinceCode' => 'OH',
			//'PostalCode' => '43606',
			
			'CountryCode' => 'US',
			'Residential' => true
		)
	);
	return $recipient;	                                    
}
function addShippingChargesPayment(){
	$shippingChargesPayment = array('PaymentType' => 'SENDER',
        'Payor' => array(
			'ResponsibleParty' => array(
				'AccountNumber' => getProperty('billaccount'),
				'Contact' => null,
				'Address' => array(
					'CountryCode' => 'US'
				)
			)
		)
	);
	return $shippingChargesPayment;
}
function addLabelSpecification(){
	$labelSpecification = array(
		'LabelFormatType' => 'COMMON2D', // valid values COMMON2D, LABEL_DATA_ONLY
		'ImageType' => 'PDF',  // valid values DPL, EPL2, PDF, ZPLII and PNG
		'LabelStockType' => 'PAPER_7X4.75'
	);
	return $labelSpecification;
}
function addSpecialServices1(){
	$specialServices = array(
		'SpecialServiceTypes' => array('COD'),
		'CodDetail' => array(
			'CodCollectionAmount' => array(
				'Currency' => 'USD', 
				'Amount' => 80
			),
			'CollectionType' => 'ANY' //ANY, GUARANTEED_FUNDS
		)
	);
	return $specialServices; 
}
function addPackageLineItem1(){
	$packageLineItem = array(
		'SequenceNumber'=>1,
		'GroupPackageCount'=>1,
		'Weight' => array(
			'Value' => 5.0,
			'Units' => 'LB'
		),
		'Dimensions' => array(
			'Length' => 6,
			'Width' => 4,
			'Height' => 1,
			'Units' => 'IN'
		),
		'CustomerReferences' => array(
			'0' => array(
				'CustomerReferenceType' => 'CUSTOMER_REFERENCE',  // valid values CUSTOMER_REFERENCE, INVOICE_NUMBER, P_O_NUMBER and SHIPMENT_INTEGRITY
				'Value' => 'GR4567892'
			), 
			'1' => array(
				'CustomerReferenceType' => 'INVOICE_NUMBER', 
				'Value' => 'INV4567892'
			),
			'2' => array(
				'CustomerReferenceType' => 'P_O_NUMBER', 
				'Value' => 'PO4567892'
			)
		)
	);
	return $packageLineItem;
}
function addSmartPostDetail(){
	$smartPostDetail = array(
		'Indicia' => 'PARCEL_SELECT',
		'AncillaryEndorsement' => 'CARRIER_LEAVE_IF_NO_RESPONSE',
		'SpecialServices' => 'USPS_DELIVERY_CONFIRMATION',
		'HubId' => getProperty('hubid')
		//'CustomerManifestId' => 'XXX'
	);
	return $smartPostDetail;
}
?>