<?php
// Copyright 2009, FedEx Corporation. All rights reserved.
// Version 12.0.0

require_once('../../../library/fedex-common.php5');

//The WSDL is not included with the sample code.
//Please include and reference in $path_to_wsdl variable.
$path_to_wsdl = "../../../wsdl/ShipService_v17.wsdl";

define('SHIP_LABEL', 'shipexpresslabel.pdf');  // PNG label file. Change to file-extension .pdf for creating a PDF label (e.g. shiplabel.pdf)
define('SHIP_CODLABEL', 'CODexpressreturnlabel.pdf');  // PNG label file. Change to file-extension .pdf for creating a PDF label (e.g. CODexpressreturnlabel.pdf)

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
$request['TransactionDetail'] = array('CustomerTransactionId' => '*** Express Domestic Shipping Request using PHP ***');
$request['Version'] = array(
	'ServiceId' => 'ship', 
	'Major' => '17', 
	'Intermediate' => '0', 
	'Minor' => '0'
);
$request['RequestedShipment'] = array(
	'ShipTimestamp' => date('c'),
	'DropoffType' => 'REGULAR_PICKUP', // valid values REGULAR_PICKUP, REQUEST_COURIER, DROP_BOX, BUSINESS_SERVICE_CENTER and STATION
	'ServiceType' => 'PRIORITY_OVERNIGHT', // valid values STANDARD_OVERNIGHT, PRIORITY_OVERNIGHT, FEDEX_GROUND, ...
	'PackagingType' => 'YOUR_PACKAGING', // valid values FEDEX_BOX, FEDEX_PAK, FEDEX_TUBE, YOUR_PACKAGING, ...
	'TotalWeight' => array(
		'Value' => 50.0, 
		'Units' => 'LB' // valid values LB and KG
	), 
	'Shipper' => addShipper(),
	'Recipient' => addRecipient(),
	'ShippingChargesPayment' => addShippingChargesPayment(),
	'SpecialServicesRequested' => addSpecialServices(),
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
	
	$response = $client->processShipment($request);  // FedEx web service invocation

    if ($response->HighestSeverity != 'FAILURE' && $response->HighestSeverity != 'ERROR'){    	
    	printSuccess($client, $response);
        
        $fp = fopen(SHIP_CODLABEL, 'wb');   
        fwrite($fp, $response->CompletedShipmentDetail->AssociatedShipments->Label->Parts->Image); //Create COD Return PNG or PDF file
        fclose($fp);
        echo '<a href="./'.SHIP_CODLABEL.'">'.SHIP_CODLABEL.'</a> was generated.'.Newline;

        // Create PNG or PDF label
        // Set LabelSpecification.ImageType to 'PDF' or 'PNG for generating a PDF or a PNG label       
        $fp = fopen(SHIP_LABEL, 'wb');   
        fwrite($fp, $response->CompletedShipmentDetail->CompletedPackageDetails->Label->Parts->Image); //Create PNG or PDF file
        fclose($fp);
        echo '<a href="./'.SHIP_LABEL.'">'.SHIP_LABEL.'</a> was generated.';
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
				'CountryCode' => 'US')
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
function addSpecialServices(){
	$specialServices = array(
		'SpecialServiceTypes' => array('COD'),
		'CodDetail' => array(
			'CodCollectionAmount' => array(
				'Currency' => 'USD', 
				'Amount' => 150
			),
			'CollectionType' => 'ANY' // ANY, GUARANTEED_FUNDS
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
			'Length' => 20,
			'Width' => 20,
			'Height' => 10,
			'Units' => 'IN'
		)
	);
	return $packageLineItem;
}
?>