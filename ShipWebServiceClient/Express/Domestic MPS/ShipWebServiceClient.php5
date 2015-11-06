<?php
// Copyright 2009, FedEx Corporation. All rights reserved.
// Version 12.0.0

require_once('../../../library/fedex-common.php5');

//The WSDL is not included with the sample code.
//Please include and reference in $path_to_wsdl variable.
$path_to_wsdl = "../../../wsdl/ShipService_v17.wsdl";

define('SHIP_MASTERLABEL', 'shipmasterlabel.pdf');    // PNG label file. Change to file-extension .pdf for creating a PDF label (e.g. shiplabel.pdf)
define('SHIP_CODLABEL', 'shipcodlabel.pdf');
define('SHIP_CHILDLABEL_1', 'shipchildlabel_1.pdf');  // PNG label file. Change to file-extension .pdf for creating a PDF label (e.g. shiplabel.pdf)
define('SHIP_CHILDLABEL_2', 'shipchildlabel_2.pdf');  // PNG label file. Change to file-extension .pdf for creating a PDF label (e.g. shiplabel.pdf)

ini_set("soap.wsdl_cache_enabled", "0");

$client = new SoapClient($path_to_wsdl, array('trace' => 1)); // Refer to http://us3.php.net/manual/en/ref.soap.php for more information

try {
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
	$masterRequest['TransactionDetail'] = array('CustomerTransactionId' => '*** Express Domestic Shipping Request - Master using PHP ***');
	$masterRequest['Version'] = array(
		'ServiceId' => 'ship', 
		'Major' => '17', 
		'Intermediate' => '0', 
		'Minor' => '0'
	);
	$masterRequest['RequestedShipment'] = array(
		'ShipTimestamp' => date('c'),
		'DropoffType' => 'REGULAR_PICKUP', // valid values REGULAR_PICKUP, REQUEST_COURIER, DROP_BOX, BUSINESS_SERVICE_CENTER and STATION
		'ServiceType' => 'PRIORITY_OVERNIGHT', // valid values STANDARD_OVERNIGHT, PRIORITY_OVERNIGHT, FEDEX_GROUND, ...s
		'PackagingType' => 'YOUR_PACKAGING', // valid values FEDEX_BOX, FEDEX_PAK, FEDEX_TUBE, YOUR_PACKAGING, ...
		'TotalWeight' => array('Value' => 9.0, 'Units' => 'LB'), // valid values LB and KG
		'Shipper' => addShipper(),
		'Recipient' => addRecipient(),
		'ShippingChargesPayment' => addShippingChargesPayment(),
		'SpecialServicesRequested' => addSpecialServices(),
		'LabelSpecification' => addLabelSpecification(), 
		'PackageCount' => 3,              
		'RequestedPackageLineItems' => array(
			'0' => addPackageLineItem1()
		)
	);
                                                                                        
	if(setEndpoint('changeEndpoint')){
		$newLocation = $client->__setLocation(setEndpoint('endpoint'));
	}

	$masterResponse = $client->processShipment($masterRequest);  // FedEx web service invocation for master label
	
	writeToLog($client);    // Write to log file

	if ($masterResponse->HighestSeverity != 'FAILURE' && $masterResponse->HighestSeverity != 'ERROR'){
	    printSuccess($client, $masterResponse);
	    
	    echo 'Generating label ...'. Newline;
	    // Create PNG or PDF label
	    // Set LabelSpecification.ImageType to 'PDF' for generating a PDF label
	    $fp = fopen(SHIP_MASTERLABEL, 'wb');   
	    fwrite($fp, $masterResponse->CompletedShipmentDetail->CompletedPackageDetails->Label->Parts->Image);
	    fclose($fp);
	    echo 'Label <a href="./'.SHIP_MASTERLABEL.'">'.SHIP_MASTERLABEL.'</a> was generated. Processing package#1 ...';
	 
		$childRequest1['WebAuthenticationDetail'] = array(
			'ParentCredential' => array(
				'Key' => getProperty('parentkey'), 
				'Password' => getProperty('parentpassword')
			),
			'UserCredential' => array(
				'Key' => getProperty('key'), 
				'Password' => getProperty('password')
			)
		);
	    
	    $childRequest1['ClientDetail'] = array(
	    	'AccountNumber' => getProperty('shipaccount'), 
	    	'MeterNumber' => getProperty('meter')
	    );
	    $childRequest1['TransactionDetail'] = array('CustomerTransactionId' => '*** Express Domestic Shipping Request Child 1 using PHP ***');
	    $childRequest1['Version'] = array(
	    	'ServiceId' => 'ship', 
	    	'Major' => '17', 
	    	'Intermediate' => '0', 
	    	'Minor' => '0'
	    );
	    $childRequest1['RequestedShipment'] = array(
	    	'ShipTimestamp' => date('c'),
			'DropoffType' => 'REGULAR_PICKUP', // valid values REGULAR_PICKUP, REQUEST_COURIER, DROP_BOX, BUSINESS_SERVICE_CENTER and STATION
			'ServiceType' => 'PRIORITY_OVERNIGHT', // valid values STANDARD_OVERNIGHT, PRIORITY_OVERNIGHT, FEDEX_GROUND, ...s
			'PackagingType' => 'YOUR_PACKAGING', // valid values FEDEX_BOX, FEDEX_PAK, FEDEX_TUBE, YOUR_PACKAGING, ...
			'TotalWeight' => array(
				'Value' => 50.0, 
				'Units' => 'LB' // valid values LB and KG
			), 
			'Shipper' => addShipper(),
			'Recipient' => addRecipient(),
			'ShippingChargesPayment' => addShippingChargesPayment(),
			'LabelSpecification' => addLabelSpecification(), 
			'PackageCount' => 3,
			'MasterTrackingId' => $masterResponse->CompletedShipmentDetail->MasterTrackingId,
			'RequestedPackageLineItems' => array(
				'0' => addPackageLineItem2()
	    	)
	    );
	
	    $childResponse1 = $client->processShipment($childRequest1);  // FedEx web service invocation  for child label #1
	    
	    writeToLog($client);    // Write to log file
	    
	    if ($childResponse1->HighestSeverity != 'FAILURE' && $childResponse1->HighestSeverity != 'ERROR'){
	        printSuccess($client, $childResponse1);
	        
	        // Create PNG or PDF label
	        // Set LabelSpecification.ImageType to 'PDF' for generating a PDF label
	        $fp = fopen(SHIP_CHILDLABEL_1, 'wb');   
	        fwrite($fp, $childResponse1->CompletedShipmentDetail->CompletedPackageDetails->Label->Parts->Image);
	        fclose($fp);
	        echo 'Label <a href="./'.SHIP_CHILDLABEL_1.'">'.SHIP_CHILDLABEL_1.'</a> was generated. Processing package#2 ...';   
	    }else{
	        echo 'Processing child shipment 1' . Newline;
			printError($client, $masterResponse);
	    }
	    
	    $childRequest2['WebAuthenticationDetail'] = array(
			'ParentCredential' => array(
				'Key' => getProperty('parentkey'), 
				'Password' => getProperty('parentpassword')
			),
			'UserCredential' => array(
				'Key' => getProperty('key'), 
				'Password' => getProperty('password')
			)
		);
	    		
	    $childRequest2['ClientDetail'] = array(
	    	'AccountNumber' => getProperty('shipaccount'), 
	    	'MeterNumber' => getProperty('meter')
	    );
	    $childRequest2['TransactionDetail'] = array('CustomerTransactionId' => '*** Express Domestic Shipping Request - Child 2 using PHP ***');

	    $childRequest2['Version'] = array(
	    	'ServiceId' => 'ship', 
	    	'Major' => '17', 
	    	'Intermediate' => '0', 
	    	'Minor' => '0'
	    );
	    $childRequest2['RequestedShipment'] = array(
	    	'ShipTimestamp' => date('c'),
			'DropoffType' => 'REGULAR_PICKUP', // valid values REGULAR_PICKUP, REQUEST_COURIER, DROP_BOX, BUSINESS_SERVICE_CENTER and STATION
			'ServiceType' => 'PRIORITY_OVERNIGHT', // valid values STANDARD_OVERNIGHT, PRIORITY_OVERNIGHT, FEDEX_GROUND, ...s
			'PackagingType' => 'YOUR_PACKAGING', // valid values FEDEX_BOX, FEDEX_PAK, FEDEX_TUBE, YOUR_PACKAGING, ...
			'TotalWeight' => array(
				'Value' => 50.0, 
				'Units' => 'LB' // valid values LB and KG
			), 
			'Shipper' => addShipper(),
			'Recipient' => addRecipient(),
			'ShippingChargesPayment' => addShippingChargesPayment(),
			'LabelSpecification' => addLabelSpecification(), 
			'RateRequestTypes' => array('ACCOUNT'), // valid values ACCOUNT and LIST
			'PackageCount' => 3,
			'MasterTrackingId' => $masterResponse->CompletedShipmentDetail->MasterTrackingId,                       
			'PackageDetail' => 'INDIVIDUAL_PACKAGES',
			'RequestedPackageLineItems' => array(
				'0' => array(
					'SequenceNumber' => '3',
	                'Weight' => array(
	                	'Value' => 2.0,
	                	'Units' => 'LB'
					),
					'Dimensions' => array(
						'Length' => 30,
		              	'Width' => 30,
		              	'Height' => 3,
		                'Units' => 'IN'
		        	)
		        )
			)
		);
	
	    $childResponse2 = $client->processShipment($childRequest2);  // FedEx web service invocation for child label #2
	    
	    writeToLog($client);    // Write to log file
	    
	    if ($childResponse2->HighestSeverity != 'FAILURE' && $childResponse2->HighestSeverity != 'ERROR'){
	        printSuccess($client, $childResponse2);
	        
	        // Create PNG or PDF label
	        // Set LabelSpecification.ImageType to 'PDF' for generating a PDF label
	        $fp = fopen(SHIP_CHILDLABEL_2, 'wb');   
	        fwrite($fp, $childResponse2->CompletedShipmentDetail->CompletedPackageDetails->Label->Parts->Image);
	        fclose($fp);
	        echo 'Label <a href="./'.SHIP_CHILDLABEL_2.'">'.SHIP_CHILDLABEL_2.'</a> was generated.' . Newline;
	        
	        //Printing COD label from last child shipment
	        $fp = fopen(SHIP_CODLABEL, 'wb');   
	    	fwrite($fp, $childResponse2->CompletedShipmentDetail->AssociatedShipments->Label->Parts->Image); //Create COD Return PNG or PDF file
	    	fclose($fp);
	    	echo '<a href="./'.SHIP_CODLABEL.'">'.SHIP_CODLABEL.'</a> was generated.'.Newline;
	    }else{
	        echo 'Processing child shipment 2' . Newline;
			printError($client, $masterResponse);
	    }    
	}else{
	    echo 'Processing Master shipment' . Newline;
		printError($client, $masterResponse);
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
		'GroundPackageCount'=>1,
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
function addPackageLineItem2(){
	$packageLineItem = array(
		'SequenceNumber' => '2',
		'GroundPackageCount'=>1,
		'Weight' => array(
			'Value' => 2.0,
			'Units' => 'LB'
		),
		'Dimensions' => array(
			'Length' => 20,
			'Width' => 20,
			'Height' => 3,
			'Units' => 'IN'
		)
	);
	return $packageLineItem;
}
function addPackageLineItem3(){
	$packageLineItem = array(
		'SequenceNumber' => '3',
		'GroupPackageCount'=>1,
		'Weight' => array(
			'Value' => 2.0,
			'Units' => 'LB'
		),
		'Dimensions' => array(
			'Length' => 20,
			'Width' => 20,
			'Height' => 3,
			'Units' => 'IN'
		)
	);
	return $packageLineItem;
}
?>