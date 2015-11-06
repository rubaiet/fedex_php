<?php
// Copyright 2009, FedEx Corporation. All rights reserved.
// Version 12.0.0

require_once('../library/fedex-common.php');

//The WSDL is not included with the sample code.
//Please include and reference in $path_to_wsdl variable.
$path_to_wsdl = "../wsdl/beta/ShipService_v17.wsdl";

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
$request['TransactionDetail'] = array('CustomerTransactionId' => ' *** Cancel Shipment Request using PHP ***');
$request['Version'] = array(
	'ServiceId' => 'ship', 
	'Major' => '17', 
	'Intermediate' => '0', 
	'Minor' => '0'
);
$request['ShipTimestamp'] = date('c');
$request['TrackingId'] = array(
	'TrackingIdType' =>'GROUND', // valid values EXPRESS, GROUND, USPS, etc
   	'TrackingNumber'=>getProperty('trackingnumber')
);  
$request['DeletionControl'] = 'DELETE_ONE_PACKAGE'; // Package/Shipment



try {
	if(setEndpoint('changeEndpoint')){
		$newLocation = $client->__setLocation(setEndpoint('endpoint'));
	}
	
	$response = $client ->deleteShipment($request);
    
    if ($response -> HighestSeverity != 'FAILURE' && $response -> HighestSeverity != 'ERROR'){
        printSuccess($client, $response);
    }else{
        printError($client, $response);
    } 
    
    writeToLog($client);    // Write to log file   
} catch (SoapFault $exception) {
    printFault($exception, $client);
}
?>