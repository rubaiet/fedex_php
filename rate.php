<?php
	if(isset($_POST['submit']))
	{
		require_once('library/menu.php');
		echo "<h1>Rate</h1>";
		
		require_once('library/fedex-common.php');
		
		function addShipper(){
			$shipper = array(
				'Contact' => array(
					'PersonName' => 'Rubaiet M',
					'CompanyName' => 'ELS',
					'PhoneNumber' => '019130191355'
				),
				'Address' => getProperty('address1')
			);
			return $shipper;
		}
		function addRecipient($input){
			$recipient = array(
				'Contact' => array(
					'PersonName' => $input['PersonName'],
					'CompanyName' => $input['CompanyName'],
					'PhoneNumber' => $input['PhoneNumber']
				),
				'Address' => array(
					'StreetLines' => array($input['StreetLines']),
					'City' => $input['City'],
					'StateOrProvinceCode' => $input['StateOrProvinceCode'],
					'PostalCode' => $input['PostalCode'],
					'CountryCode' => $input['CountryCode'],
					'Residential' => 1
				)
			);
			return $recipient;	                                    
		}
		function addShippingChargesPayment($input){
			$shippingChargesPayment = array(
				'PaymentType' => 'SENDER', // valid values RECIPIENT, SENDER and THIRD_PARTY
				'Payor' => array(
					'ResponsibleParty' => array(
						'AccountNumber' => getProperty('billaccount'),
						'CountryCode' => 'US'
					)
				)
			);
			return $shippingChargesPayment;
		}
		function addSmartPostDetail($input){
			$smartPostDetail = array( 
				'Indicia' => 'PARCEL_SELECT',
				'AncillaryEndorsement' => 'CARRIER_LEAVE_IF_NO_RESPONSE',
				'SpecialServices' => 'USPS_DELIVERY_CONFIRMATION',
				'HubId' => getProperty('hubid'),
				'CustomerManifestId' => 'XXX'
			);
			return $smartPostDetail;
		}
		function addPackageLineItem($input){
			$packageLineItem = array( 
				'SequenceNumber' => 1,
				'GroupPackageCount' => 1,
				'Weight' => array(
					'Value' => $input['Weight'],
					'Units' => 'LB'
				),
				'Dimensions' => array(
					'Length' => $input['Length'],
					'Width' => $input['Width'],
					'Height' => $input['Height'],
					'Units' => 'IN'
				)
			);
			return $packageLineItem;
		}

		$newline = "<br />";
		//The WSDL is not included with the sample code.
		//Please include and reference in $path_to_wsdl variable.
		$path_to_wsdl = "wsdl/RateService_v18.wsdl";

		ini_set("soap.wsdl_cache_enabled", "0");
		 
		$client = new SoapClient($path_to_wsdl, array('trace' => 1)); // Refer to http://us3.php.net/manual/en/ref.soap.php for more information

		$request['WebAuthenticationDetail'] = array(
			'ParentCredential' => array(
				'Key' => getProperty('parentkey'),
				'Password' => getProperty('parentpassword')
			),
			'UserCredential' =>array(
				'Key' => getProperty('key'), 
				'Password' => getProperty('password')
			)
		); 
		$request['ClientDetail'] = array(
			'AccountNumber' => getProperty('shipaccount'), 
			'MeterNumber' => getProperty('meter')
		);
		$request['TransactionDetail'] = array('CustomerTransactionId' => ' *** SmartPost Rate Request using PHP ***');
		$request['Version'] = array(
			'ServiceId' => 'crs', 
			'Major' => '18', 
			'Intermediate' => '0', 
			'Minor' => '0'
		);
		$request['ReturnTransitAndCommit'] = true;
		$request['RequestedShipment']['DropoffType'] = 'REGULAR_PICKUP'; // valid values REGULAR_PICKUP, REQUEST_COURIER, ...
		$request['RequestedShipment']['ShipTimestamp'] = date('c');
		$request['RequestedShipment']['ServiceType'] = $_POST['ServiceType']; // valid values STANDARD_OVERNIGHT, PRIORITY_OVERNIGHT, FEDEX_GROUND, ...
		$request['RequestedShipment']['PackagingType'] = 'YOUR_PACKAGING'; // valid values FEDEX_BOX, FEDEX_PAK, FEDEX_TUBE, YOUR_PACKAGING, ...
		$request['RequestedShipment']['Shipper'] = addShipper($_POST);
		$request['RequestedShipment']['Recipient'] = addRecipient($_POST);
		$request['RequestedShipment']['ShippingChargesPayment'] = addShippingChargesPayment($_POST);														 
		$request['RequestedShipment']['SmartPostDetail'] = addSmartPostDetail($_POST);
		$request['RequestedShipment']['PackageCount'] = '1';
		$request['RequestedShipment']['RequestedPackageLineItems'] = addPackageLineItem($_POST);



		try {
			if(setEndpoint('changeEndpoint')){
				$newLocation = $client->__setLocation(setEndpoint('endpoint'));
			}
			
			$response = $client -> getRates($request);

			if ($response -> HighestSeverity != 'FAILURE' && $response -> HighestSeverity != 'ERROR'){
				$rateReply = $response -> RateReplyDetails;
				echo '<table border="1">';
				echo '<tr><td>Service Type</td><td>Amount</td><td>Transit Time</td><td>Max Transit Time</td><td>Ship</td></tr><tr>';
				$serviceType = '<td>'.$rateReply -> ServiceType . '</td>';
				if($rateReply->RatedShipmentDetails && is_array($rateReply->RatedShipmentDetails)){
					$amount = '<td>$' . number_format($rateReply->RatedShipmentDetails[0]->ShipmentRateDetail->TotalNetCharge->Amount,2,".",",") . '</td>';
				}elseif($rateReply->RatedShipmentDetails && ! is_array($rateReply->RatedShipmentDetails)){
					$amount = '<td>$' . number_format($rateReply->RatedShipmentDetails->ShipmentRateDetail->TotalNetCharge->Amount,2,".",",") . '</td>';
				} 
				if(array_key_exists('TransitTime',$rateReply->CommitDetails)){
					$transitTime= '<td>' . $rateReply->CommitDetails->TransitTime . '</td>';
				}elseif(array_key_exists('DeliveryTimestamp',$rateReply)){
					$transitTime= '<td>' . $rateReply->DeliveryTimestamp . '</td>';
				}
				else
				{
					$transitTime= '<td>' . 'N/A' . '</td>';
				}
				
				if(array_key_exists('MaximumTransitTime',$rateReply->CommitDetails)){
					$maxTransitTime= '<td>' . $rateReply->CommitDetails->MaximumTransitTime . '</td>';
				}else{
					$maxTransitTime= '<td>' . 'not working' . '</td>';
				}
				
				echo $serviceType . $amount. $transitTime. $maxTransitTime;
?>
				<td>
					<form action="ship.php" method="post">
						<input type="hidden" name="ServiceType" value="<?php echo $_POST['ServiceType']?>">
						<input type="hidden" name="PersonName" value="<?php echo $_POST['PersonName']?>">
						<input type="hidden" name="CompanyName" value="<?php echo $_POST['CompanyName']?>">
						<input type="hidden" name="PhoneNumber" value="<?php echo $_POST['PhoneNumber']?>">
						<input type="hidden" name="StreetLines" value="<?php echo $_POST['StreetLines']?>">
						<input type="hidden" name="City" value="<?php echo $_POST['City']?>">
						<input type="hidden" name="StateOrProvinceCode" value="<?php echo $_POST['StateOrProvinceCode']?>">
						<input type="hidden" name="PostalCode" value="<?php echo $_POST['PostalCode']?>">
						<input type="hidden" name="CountryCode" value="<?php echo $_POST['CountryCode']?>">
						<input type="hidden" name="Length" value="<?php echo $_POST['Length']?>">
						<input type="hidden" name="Width" value="<?php echo $_POST['Width']?>">
						<input type="hidden" name="Height" value="<?php echo $_POST['Height']?>">
						<input type="hidden" name="Weight" value="<?php echo $_POST['Weight']?>">
						<input type="submit" name="submit" name="Finalize Shipment">
					</form>
					
				</td>
<?php
				echo '</tr>';
				echo '</table>';
				
				// printSuccess($client, $response);
			}else{
				printError($client, $response);
			} 
			
			writeToLog($client);    // Write to log file   
		} catch (SoapFault $exception) {
		   printFault($exception, $client);        
		}
	}
	else
	{
		header('Location: index.php');
	}

?>