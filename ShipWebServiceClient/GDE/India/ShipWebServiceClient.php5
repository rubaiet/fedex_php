<?php
// Copyright 2009, FedEx Corporation. All rights reserved.
// Version 12.0.0

require_once('../../../library/fedex-common.php5');

//The WSDL is not included with the sample code.
//Please include and reference in $path_to_wsdl variable.
$path_to_wsdl = "../../../wsdl/ShipService_v17.wsdl";

// PDF label files. Change to file-extension .png for creating a PNG label (e.g. shiplabel.png)
define('SHIP_LABEL', 'shiplabel.pdf');  
define('COD_LABEL', 'codlabel.pdf'); 

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
$request['TransactionDetail'] = array('CustomerTransactionId' => '*** Intra India Shipping Request using PHP ***');
$request['Version'] = array(
	'ServiceId' => 'ship', 
	'Major' => '17', 
	'Intermediate' => '0', 
	'Minor' => '0'
);
$request['RequestedShipment'] = array(
	'ShipTimestamp' => date('c'),
	'DropoffType' => 'REGULAR_PICKUP', // valid values REGULAR_PICKUP, REQUEST_COURIER, DROP_BOX, BUSINESS_SERVICE_CENTER and STATION
	'ServiceType' => 'STANDARD_OVERNIGHT', // valid values STANDARD_OVERNIGHT, PRIORITY_OVERNIGHT, FEDEX_EXPRESS_SAVER
	'PackagingType' => 'YOUR_PACKAGING', // valid values FEDEX_BOX, FEDEX_PAK, FEDEX_TUBE, YOUR_PACKAGING, ...
	'Shipper' => addShipper(),
	'Recipient' => addRecipient(),
	'ShippingChargesPayment' => addShippingChargesPayment(),
	'SpecialServicesRequested' => addSpecialServices1(), //Used for Intra-India shipping - cannot use with PRIORITY_OVERNIGHT
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

        // Create PNG or PDF labels
        // Set LabelSpecification.ImageType to 'PNG' for generating a PNG labels
        $fp = fopen(SHIP_LABEL, 'wb');   
        fwrite($fp, ($response->CompletedShipmentDetail->CompletedPackageDetails->Label->Parts->Image));
        fclose($fp);
        echo 'Label <a href="./'.SHIP_LABEL.'">'.SHIP_LABEL.'</a> was generated.';           
        
        $fp = fopen(COD_LABEL, 'wb');   
        fwrite($fp, ($response->CompletedShipmentDetail->AssociatedShipments->Label->Parts->Image));
        fclose($fp);
        echo 'Label <a href="./'.COD_LABEL.'">'.COD_LABEL.'</a> was generated.';   
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
			'StreetLines' => '1 SENDER STREET',
			'City' => 'PUNE',
			'StateOrProvinceCode' => 'MH',
			'PostalCode' => '411011',
			'CountryCode' => 'IN',
			'CountryName' => 'INDIA'
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
			'StreetLines' => '1 RECIPIENT STREET',
			'City' => 'NEWDELHI',
			'StateOrProvinceCode' => 'DL',
			'PostalCode' => '110010',
			'CountryCode' => 'IN',
			'CountryName' => 'INDIA',
			'Residential' => false
		)
	);
	return $recipient;	                                    
}
function addShippingChargesPayment(){
	$shippingChargesPayment = array(
		'PaymentType' => 'SENDER',
        'Payor' => array(
			'ResponsibleParty' => array(
				'AccountNumber' => getProperty('billaccount'),
				'Contact' => null,
				'Address' => array('CountryCode' => 'IN')
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
		'SpecialServiceTypes' => 'COD',
		'CodDetail' => array(
			'CodCollectionAmount' => array(
				'Currency' => 'INR', 
				'Amount' => 400
			),
			'CollectionType' => 'GUARANTEED_FUNDS',// ANY, GUARANTEED_FUNDS
			'FinancialInstitutionContactAndAddress' => array(
				'Contact' => array(
					'PersonName' => 'Financial Contact',
					'CompanyName' => 'Financial Company',
					'PhoneNumber' => '8888888888'
				),
				'Address' => array(
					'StreetLines' => '1 FINANCIAL STREET',
					'City' => 'NEWDELHI',
					'StateOrProvinceCode' => 'DL',
					'PostalCode' => '110010',
					'CountryCode' => 'IN',
					'CountryName' => 'INDIA'
				)
			),
			'RemitToName' => 'Remitter'
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
					'Address' => array(
						'CountryCode' => 'IN'
					)
				)
			)
		),
		'DocumentContent' => 'NON_DOCUMENTS',                                                                                            
		'CustomsValue' => array(
			'Currency' => 'INR', 
			'Amount' => 400.0
		),
		'CommercialInvoice' => array(
			'Purpose' => 'SOLD',
			'CustomerReferences' => array(
				'CustomerReferenceType' => 'CUSTOMER_REFERENCE',
				'Value' => '1234'
			)
		),
		'Commodities' => array(
			'NumberOfPieces' => 1,
			'Description' => 'Books',
			'CountryOfManufacture' => 'IN',
			'Weight' => array(
				'Units' => 'LB', 
				'Value' => 1.0
			),
			'Quantity' => 4,
			'QuantityUnits' => 'EA',
			'UnitPrice' => array(
				'Currency' => 'INR', 
				'Amount' => 100.000000
			),
			'CustomsValue' => array(
				'Currency' => 'INR', 
				'Amount' => 400.000000
			)
		)
	);
	return $customerClearanceDetail;
}
function addPackageLineItem1(){
	$packageLineItem = array(
		'SequenceNumber'=>1,
		'GroupPackageCount'=>1,
		'InsuredValue' => array(
			'Amount' => 80.00, 
			'Currency' => 'INR'
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
			'CustomerReferenceType' => 'CUSTOMER_REFERENCE', // valid values CUSTOMER_REFERENCE, INVOICE_NUMBER, P_O_NUMBER and SHIPMENT_INTEGRITY
			'Value' => 'GR4567892'
		)
	);
	return $packageLineItem;
}
?>