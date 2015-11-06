<?php
// Copyright 2009, FedEx Corporation. All rights reserved.
// Version 12.0.0

require_once('../../../library/fedex-common.php5');

//The WSDL is not included with the sample code.
//Please include and reference in $path_to_wsdl variable.
$path_to_wsdl = "../../../wsdl/ShipService_v17.wsdl";

define('SHIP_MASTERLABEL', 'shipmasterlabel.pdf');    // PNG label file. Change to file-extension .pdf for creating a PDF label (e.g. shiplabel.pdf)
define('SHIP_CHILDLABEL_1', 'shipchildlabel_1.pdf');  // PNG label file. Change to file-extension .pdf for creating a PDF label (e.g. shiplabel.pdf)
define('SHIP_CODMASTERLABEL', 'CODmasterlabel.pdf');    // PNG label file. Change to file-extension .pdf for creating a PDF label (e.g. CODmasterlabel.pdf)
define('SHIP_CODCHILDLABEL_1', 'CODchildlabel_1.pdf');  // PNG label file. Change to file-extension .pdf for creating a PDF label (e.g. CODchildlabel_2.pdf)

ini_set("soap.wsdl_cache_enabled", "0");

$client = new SoapClient($path_to_wsdl, array('trace' => 1)); // Refer to http://us3.php.net/manual/en/ref.soap.php for more information

$masterRequest['WebAuthenticationDetail'] = array(
	'ParentCredential' => array(
		'Key' => getProperty('parentkey'), 
		'Password' => getProperty('parentpassword')
	),
	'UserCredential' => array(
		'Key' => getProperty('key'), 
		'Password' => getProperty('password')
	)
);

$masterRequest['ClientDetail'] = array(
	'AccountNumber' => getProperty('shipaccount'), 
	'MeterNumber' => getProperty('meter')
);
$masterRequest['TransactionDetail'] = array('CustomerTransactionId' => '*** Ground Domestic MPS Shipping Request - Master using PHP ***');
$masterRequest['Version'] = array(
	'ServiceId' => 'ship', 
	'Major' => '17', 
	'Intermediate' => '0', 
	'Minor' => '0'
);
$masterRequest['RequestedShipment'] = array(
	'ShipTimestamp' => date('c'),
	'DropoffType' => 'REGULAR_PICKUP', // valid values REGULAR_PICKUP, REQUEST_COURIER, DROP_BOX, BUSINESS_SERVICE_CENTER and STATION
	'ServiceType' => 'FEDEX_GROUND', // valid values STANDARD_OVERNIGHT, PRIORITY_OVERNIGHT, FEDEX_GROUND, ...
	'PackagingType' => 'YOUR_PACKAGING', // valid values FEDEX_BOX, FEDEX_PAK, FEDEX_TUBE, YOUR_PACKAGING, ...
	'Shipper' => addShipper(),
	'Recipient' => addRecipient(),
	'ShippingChargesPayment' => addShippingChargesPayment(),
	'LabelSpecification' => addLabelSpecification(), 
	'PackageCount' => 2,
	'RequestedPackageLineItems' => array(
		'0' => addPackageLineItem1()
	)
);



try {
	if(setEndpoint('changeEndpoint')){
		$newLocation = $client->__setLocation(setEndpoint('endpoint'));
	}
		
	$masterResponse = $client->processShipment($masterRequest); // FedEx web service invocation  
	
	writeToLog($client);    // Write to log file

	if ($masterResponse->HighestSeverity != 'FAILURE' && $masterResponse->HighestSeverity != 'ERROR'){
	    printSuccess($client, $masterResponse);
	
	    $fp = fopen(SHIP_CODMASTERLABEL, 'wb');   
	    fwrite($fp, $masterResponse->CompletedShipmentDetail->CompletedPackageDetails->CodReturnDetail->Label->Parts->Image); //Create COD Return PNG or PDF file
	    fclose($fp);
	    echo '<a href="./'.SHIP_CODMASTERLABEL.'">'.SHIP_CODMASTERLABEL.'</a> was generated.'.Newline;
	
	    // Create PNG or PDF label
	    // Set LabelSpecification.ImageType to 'PDF' for generating a PDF label
	    $fp = fopen(SHIP_MASTERLABEL, 'wb');   
	    fwrite($fp, ($masterResponse->CompletedShipmentDetail->CompletedPackageDetails->Label->Parts->Image));
	    fclose($fp);
	    echo '<a href="./'.SHIP_MASTERLABEL.'">'.SHIP_MASTERLABEL.'</a> was generated. Processing package# 1 ...';   
	    
	    $childRequest['WebAuthenticationDetail'] = array(
	    		'ParentCredential' => array(
	    				'Key' => getProperty('parentkey'),
	    				'Password' => getProperty('parentpassword')
	    		),
	    		'UserCredential' => array(
	    				'Key' => getProperty('key'),
	    				'Password' => getProperty('password')
	    		)
	    );
	    
	    $childRequest['ClientDetail'] = array(
	    	'AccountNumber' => getProperty('shipaccount'), 
	    	'MeterNumber' => getProperty('meter')
	    );
	    $childRequest['TransactionDetail'] = array('CustomerTransactionId' => '*** Ground Domestic MPS Shipping Request - Child using PHP ***');
	    $childRequest['Version'] = array(
	    	'ServiceId' => 'ship', 
	    	'Major' => '17', 
	    	'Intermediate' => '0', 
	    	'Minor' => '0'
	    );
	    $childRequest['RequestedShipment'] = array(
	    	'ShipTimestamp' => date('c'),
			'DropoffType' => 'REGULAR_PICKUP', // valid values REGULAR_PICKUP, REQUEST_COURIER, DROP_BOX, BUSINESS_SERVICE_CENTER and STATION
			'ServiceType' => 'FEDEX_GROUND', // valid values STANDARD_OVERNIGHT, PRIORITY_OVERNIGHT, FEDEX_GROUND, ...
			'PackagingType' => 'YOUR_PACKAGING', // valid values FEDEX_BOX, FEDEX_PAK, FEDEX_TUBE, YOUR_PACKAGING, ...
			'Shipper' => addShipper(),
			'Recipient' => addRecipient(),
			'ShippingChargesPayment' => addShippingChargesPayment(),
			'LabelSpecification' => addLabelSpecification(), 
			'MasterTrackingId' => $masterResponse->CompletedShipmentDetail->MasterTrackingId,
			'RateRequestTypes' => array('ACCOUNT', 'LIST'), // valid values ACCOUNT and LIST
			'PackageCount' => 2,
			'RequestedPackageLineItems' => array(
				'0' => addPackageLineItem2()
			)
	    );                                                                                                                                                                                                                                                                
	    $childResponse = $client->processShipment($childRequest); // FedEx web service invocation  
	    
	    writeToLog($client);    // Write to log file
	    
	    if ($childResponse->HighestSeverity != 'FAILURE' && $childResponse->HighestSeverity != 'ERROR'){
	        printSuccess($client, $childResponse);
	
	        $fp = fopen(SHIP_CODCHILDLABEL_1, 'wb');   
	        fwrite($fp, $childResponse->CompletedShipmentDetail->CompletedPackageDetails->CodReturnDetail->Label->Parts->Image); //Create COD Return PNG or PDF file
	        fclose($fp);
	        echo '<a href="./'.SHIP_CODCHILDLABEL_1.'">'.SHIP_CODCHILDLABEL_1.'</a> was generated.'.Newline;
	       
	        // Create PNG or PDF label
	        // Set LabelSpecification.ImageType to 'PDF' for generating a PDF label
	        $fp = fopen(SHIP_CHILDLABEL_1, 'wb');   
	        fwrite($fp, ($childResponse->CompletedShipmentDetail->CompletedPackageDetails->Label->Parts->Image));
	        fclose($fp);
	        echo '<a href="./'.SHIP_CHILDLABEL_1.'">'.SHIP_CHILDLABEL_1.'</a> was generated';   
	    }else{
	        echo 'Processing child master' . Newline;
	    	printError($client, $childResponse);
	    }
	}else{
	    echo 'Error in processing master transaction.'. Newline; 
	    printError($client, $masterResponse);
	}
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
function addSpecialServices2(){
	$specialServices = array(
		'SpecialServiceTypes' => array('COD'),
		'CodDetail' => array(
			'CodCollectionAmount' => array(
				'Currency' => 'USD', 
				'Amount' => 50
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
		'InsuredValue' => array(
			'Amount' => 100.00, 
			'Currency' => 'USD'
		),
		'Weight' => array(
			'Value' => 70.0,
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
				'CustomerReferenceType' => 'CUSTOMER_REFERENCE',   // valid values CUSTOMER_REFERENCE, INVOICE_NUMBER, P_O_NUMBER and SHIPMENT_INTEGRITY
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
		),
		'SpecialServicesRequested' => addSpecialServices1()
	);
	return $packageLineItem;
}
function addPackageLineItem2(){
	$packageLineItem = array(
		'SequenceNumber'=>2,
		'GroupPackageCount'=>1,
		'InsuredValue' => array(
			'Amount' => 75.00, 
			'Currency' => 'USD'
		),
		'Weight' => array(
			'Value' => 50.0,
			'Units' => 'LB'
		),
		'Dimensions' => array(
			'Length' => 15,
			'Width' => 10,
			'Height' => 10,
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
		),
		'SpecialServicesRequested' => addSpecialServices2()
	);
	return $packageLineItem;
}
?>