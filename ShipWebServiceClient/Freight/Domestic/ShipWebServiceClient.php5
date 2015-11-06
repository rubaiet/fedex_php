<?php
// Copyright 2009, FedEx Corporation. All rights reserved.
// Version 12.0.0

require_once('../../../library/fedex-common.php5');

//The WSDL is not included with the sample code.
//Please include and reference in $path_to_wsdl variable.
$path_to_wsdl = "../../../wsdl/ShipService_v17.wsdl";

define('SHIP_LABEL', 'BillOfLading.pdf');  // PDF label file.
define('ADDRESS_LABEL', 'AddressLabel.pdf');  // PDF label file.

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
$request['TransactionDetail'] = array('CustomerTransactionId' => '*** Freight Domestic Shipping Request using PHP ***');
$request['Version'] = array(
	'ServiceId' => 'ship', 
	'Major' => '17', 
	'Intermediate' => '0', 
	'Minor' => '0'
);
$request['RequestedShipment'] = array(
	'ShipTimestamp' => date('c'),
	'DropoffType' => 'REGULAR_PICKUP', // valid values REGULAR_PICKUP, REQUEST_COURIER, DROP_BOX, BUSINESS_SERVICE_CENTER and STATION
	'ServiceType' => 'FEDEX_FREIGHT_PRIORITY', // valid values STANDARD_OVERNIGHT, PRIORITY_OVERNIGHT, FEDEX_GROUND, ...
	'PackagingType' => 'YOUR_PACKAGING', // valid values FEDEX_BOX, FEDEX_PAK, FEDEX_TUBE, YOUR_PACKAGING, ...
	'Shipper' => getProperty('freightbilling'),
	'Recipient' => addRecipient(),
	'ShippingChargesPayment' => addShippingChargesPayment(),
	'FreightShipmentDetail' => array(
		'FedExFreightAccountNumber' => getProperty('freightaccount'),
		'FedExFreightBillingContactAndAddress' => getProperty('freightbilling'),
		'PrintedReferences' => array(
			'Type' => 'SHIPPER_ID_NUMBER',
			'Value' => 'RBB1057'
		),
		'Role' => 'SHIPPER',
		'PaymentType' => 'PREPAID',
		'CollectTermsType' => 'STANDARD',
		'DeclaredValuePerUnit' => array(
			'Currency' => 'USD',
			'Amount' => 50
		),
		'LiabilityCoverageDetail' => array(
			'CoverageType' => 'NEW',
			'CoverageAmount' => array(
				'Currency' => 'USD',
				'Amount' => '50'
			)
		),
		'TotalHandlingUnits' => 15,
		'ClientDiscountPercent' => 0,
		'PalletWeight' => array(
			'Units' => 'LB',
			'Value' => 20
		),
		'ShipmentDimensions' => array(
			'Length' => 60,
			'Width' => 40,
			'Height' => 50,
			'Units' => 'IN'
		),
		'LineItems' => array(
			'FreightClass' => 'CLASS_050',
			'ClassProvidedByCustomer' => false,
			'HandlingUnits' => 15,
			'Packaging' => 'PALLET',
			'Pieces' => 1,
			'BillOfLaddingNumber' => 'BOL_12345',
			'PurchaseOrderNumber' => 'PO_12345',
			'Description' => 'Heavy Stuff',
			'Weight' => array(
				'Value' => 500.0,
				'Units' => 'LB'
			),
			'Dimensions' => array(
				'Length' => 60,
				'Width' => 40,
				'Height' => 50,
				'Units' => 'IN'
			),
			'Volume' => array(
				'Units' => 'CUBIC_FT',
				'Value' => 30
			)
		)
	),	
	'LabelSpecification' => addLabelSpecification(),
	'ShippingDocumentSpecification' => addShippingDocumentSpecification(),
	'PackageCount' => 1,
	'PackageDetail' => 'INDIVIDUAL_PACKAGES'                                        
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
    	
    	$shippingDocuments = $response->CompletedShipmentDetail->ShipmentDocuments;
    	foreach($shippingDocuments as $key => $value){
    		$type = $value->Type;
    		if($type == "OUTBOUND_LABEL"){
    			$bol = $value->Parts->Image;
    			$fp = fopen(SHIP_LABEL, 'wb');
    			fwrite($fp, $bol);
        		fclose($fp);
        		echo '<a href="./'.SHIP_LABEL.'">'.SHIP_LABEL.'</a> was generated.<br/>';
    		}else if($type == "FREIGHT_ADDRESS_LABEL"){
    			$addressLabel = $value->Parts->Image;
    			$fp1 = fopen(ADDRESS_LABEL, 'wb');   
        		fwrite($fp1, $addressLabel);
        		fclose($fp1);
        		echo '<a href="./'.ADDRESS_LABEL.'">'.ADDRESS_LABEL.'</a> was generated.<br/>'; 
    		}
    	}

    }else{
        printError($client, $response);
    }

	writeToLog($client);    // Write to log file
} catch (SoapFault $exception) {
    printFault($exception, $client);
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
				'AccountNumber' => getProperty('freightaccount'),
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
		'LabelFormatType' => 'VICS_BILL_OF_LADING', // valid values COMMON2D, LABEL_DATA_ONLY
		'ImageType' => 'PDF',  // valid values DPL, EPL2, PDF, ZPLII and PNG
		'LabelStockType' => 'PAPER_LETTER'
	);
	return $labelSpecification;
}
function addShippingDocumentSpecification(){
	$shippingDocumentSpecification = array(
		'ShippingDocumentTypes' => array('FREIGHT_ADDRESS_LABEL'),
		'FreightAddressLabelDetail' => array(
			'Format' => array(
				'ImageType' => 'PDF',
				'StockType' => 'PAPER_4X6',
				'ProvideInstuctions' => true
			)
		)
	);
	return $shippingDocumentSpecification;
}
?>