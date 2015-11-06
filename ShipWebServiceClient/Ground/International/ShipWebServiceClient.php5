<?php
// Copyright 2009, FedEx Corporation. All rights reserved.
// Version 12.0.0

require_once('../../../library/fedex-common.php5');

//The WSDL is not included with the sample code.
//Please include and reference in $path_to_wsdl variable.
$path_to_wsdl = "../../../wsdl/ShipService_v17.wsdl";

define('SHIP_LABEL', 'shipgroundlabel.pdf');  // PNG label file. Change to file-extension .pdf for creating a PDF label (e.g. shiplabel.pdf)

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
$request['TransactionDetail'] = array('CustomerTransactionId' => '*** Ground International Shipping Request using PHP ***');
$request['Version'] = array(
	'ServiceId' => 'ship', 
	'Major' => '17', 
	'Intermediate' => '0', 
	'Minor' => '0'
);
$request['RequestedShipment'] = array(
	'ShipTimestamp' => date('c'),
	'DropoffType' => 'REGULAR_PICKUP', // valid values REGULAR_PICKUP, REQUEST_COURIER, DROP_BOX, BUSINESS_SERVICE_CENTER and STATION
	'ServiceType' => 'FEDEX_GROUND', // valid values STANDARD_OVERNIGHT, PRIORITY_OVERNIGHT, FEDEX_GROUND, ...
	'PackagingType' => 'YOUR_PACKAGING', // valid values FEDEX_BOX, FEDEX_PAK, FEDEX_TUBE, YOUR_PACKAGING, ...
	'Shipper' => addShipper(),
	'Recipient' => addRecipient(),
	'ShippingChargesPayment' => addShippingChargesPayment(),
	'CustomsClearanceDetail' => addCustomClearanceDetail(),                                                                                                      
	'LabelSpecification' => addLabelSpecification(),
	'CustomerSpecifiedDetail' => array('MaskedData'=> 'SHIPPER_ACCOUNT_NUMBER'), 
	'PackageCount' => 1,                                       
	'RequestedPackageLineItems' => array(
		'0' => addPackageLineItem1()
	)
);



try{
	if(setEndpoint('changeEndpoint')){
		$newLocation = $client->__setLocation(setEndpoint('endpoint'));
	}
	
	$response = $client->processShipment($request); // FedEx web service invocation

    if ($response->HighestSeverity != 'FAILURE' && $response->HighestSeverity != 'ERROR'){
        printSuccess($client, $response);

        // Create PNG or PDF label
        // Set LabelSpecification.ImageType to 'PDF' for generating a PDF label
        $fp = fopen(SHIP_LABEL, 'wb');   
        fwrite($fp, ($response->CompletedShipmentDetail->CompletedPackageDetails->Label->Parts->Image));
        fclose($fp);
        echo 'Label <a href="./'.SHIP_LABEL.'">'.SHIP_LABEL.'</a> was generated.';            
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
			'City' => 'Richmond',
			'StateOrProvinceCode' => 'BC',
			'PostalCode' => 'V7C4V4',
			'CountryCode' => 'CA',
			'Residential' => false
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
			'CollectionType' => 'ANY' // ANY, GUARANTEED_FUNDS
		)
	);
	return $specialServices; 
}
function addCustomClearanceDetail(){
	$customerClearanceDetail = array(
		'DutiesPayment' => array(
			'PaymentType' => 'SENDER', // valid values RECIPIENT, SENDER and THIRD_PARTY
			'Payor' => array(
				'ResponsibleParty' => array(
					'AccountNumber' => getProperty('dutyaccount'),
					'Contact' => null,
					'Address' => array('CountryCode' => 'US')
					)
				)
			),
			'DocumentContent' => 'NON_DOCUMENTS',                                                                                            
			'CustomsValue' => array(
				'Currency' => 'USD', 
				'Amount' => 400.0
			),
		'Commodities' => array(
			'0' => array(
				'NumberOfPieces' => 1,
				'Description' => 'Books',
				'CountryOfManufacture' => 'US',
				'Weight' => array(
					'Units' => 'LB', 
					'Value' => 1.0
				),
				'Quantity' => 4,
				'QuantityUnits' => 'EA',
				'UnitPrice' => array(
					'Currency' => 'USD', 
					'Amount' => 100.000000
				),
				'CustomsValue' => array(
					'Currency' => 'USD', 
					'Amount' => 400.000000
				)
			)
		),
		'ExportDetail' => array(
			'B13AFilingOption' => 'NOT_REQUIRED'
		)
	);
	return $customerClearanceDetail;
}
function addPackageLineItem1(){
	$packageLineItem = array(
		'SequenceNumber'=>1,
		'GroupPackageCount'=>1,
		'InsuredValue' => array(
			'Amount' => 400.00, 
			'Currency' => 'USD'
		),
		'Weight' => array(
			'Value' => 20.0,
			'Units' => 'LB'
		),
		'Dimensions' => array(
			'Length' => 20,
			'Width' => 10,
			'Height' => 10,
			'Units' => 'IN'
		),
		'CustomerReferences' => array(
			'0' => array(
				'CustomerReferenceType' => 'CUSTOMER_REFERENCE', // valid values CUSTOMER_REFERENCE, INVOICE_NUMBER, P_O_NUMBER and SHIPMENT_INTEGRITY
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
?>