<html>
<body>
<?php
/*************************
Handle The UPS Shipping:
This file is called in the phppurchase/views/auth-checkout.php
*************************/

// Require the main ups class and upsRate
require_once(UPS_BASE.'config.conf.php');
require_once(UPS_BASE.'ups.class.php');
require_once(UPS_BASE.'upsRate.class.php');
require_once(UPS_BASE.'upsShip.class.php');
require_once(UPS_BASE.'upsVoid.class.php');
require_once(UPS_BASE.'address_validator.class.php');

function scheduleShipment($validAddress, $date = NULL, $requestCount = 0) {
	GLOBAL $SHIPPING;
	// Get credentials from a form
	$accessNumber = UPS_API_KEY;
	$username = UPS_USERID;
	$password = UPS_PASSWORD;

	// If the form is filled out go get a rate from UPS 
	if ($accessNumber != '' && $username != '' && $password != '') {
		/*if($ShoppingCart->isPallet()){
			$freightBooking = new bookFreightShipment();
		}else{
			
			$upsConnect = new ups("$accessNumber","$username","$password");
			$upsConnect->setTemplatePath(UPS_BASE.'xml/');
			$upsConnect->setTestingMode(0); // Change this to 0 for production
			
		}*/

		$shippingInfo = $_POST['billing'];
		if(!isset($_POST['sameAsBilling'])) {
			$shippingInfo = $_POST['shipping'];
		}
		
		$ShoppingCart = $_SESSION['PHPurchaseCart'];
		
		if(isset($_SESSION['PHPurchaseLiveRates'])) {
			$LiveRates = $_SESSION['PHPurchaseLiveRates'];
			$rate = $LiveRates->getSelected();
			$weight = $LiveRates->weight;
		} else {
			return FALSE;	
		}

		$subTotal = $ShoppingCart->getSubTotal();
		$currentDay = date('D');
		if(empty($date)) {
			$date = date('Ymd');
		}
		$date = $date+1;
		if($currentDay == 'Fri') {
			$date = $date+2;
		} elseif($currentDay == 'Sat') {
			$date = $date+1;
		}
		
		if($weight <= $SHIPPING["SMALL_WEIGHTPERBOX"]) {
			$ship = "SMALL";
		} elseif($weight <= $SHIPPING["MID_WEIGHTPERBOX"])  {
			$ship = "MID";
		} else {
			$ship = "LARGE";
		}
		//var_dump($ship);
		$boxes = (int) ceil($weight/$SHIPPING[$ship."_WEIGHTPERBOX"]);
		$box = array();
		$existingWeight = NULL;
		for($count = 0; $count < $boxes; $count++) {
			if(!empty($existingWeight)) {
				$existingWeight = (int) $existingWeight-$SHIPPING[$ship."_WEIGHTPERBOX"];
			} else {
				$existingWeight = (int) $weight-$SHIPPING[$ship."_WEIGHTPERBOX"];
			}
			if($existingWeight >= 0) {
				$box[$count] = (int) $SHIPPING[$ship."_WEIGHTPERBOX"];
			} elseif($count == 0) {
				$box[$count] = (int) $weight;
			} else {
				$box[$count] = (int) $SHIPPING[$ship."_WEIGHTPERBOX"]+$existingWeight;
			}
		}

		$shipmentArgs = array();
		$shipmentArgs['CompanyName'] = $shippingInfo['firstName'].' '.$shippingInfo['lastName'];
		$shipmentArgs['AttentionName'] = '';
		$shipmentArgs['PhoneAreaCode'] = '';
		$shipmentArgs['PhoneNumber'] = '';
		$shipmentArgs['AddressLine1'] = $validAddress['address'];
		$shipmentArgs['City'] = $validAddress['city'];
		$shipmentArgs['StateProvinceCode'] = $validAddress['state'];
		$shipmentArgs['CountryCode'] = $validAddress['country'];
		$shipmentArgs['PostalCode'] = $validAddress['zip'];
		$shipmentArgs['ResidentialAddress'] = '';
		$shipmentArgs['ShippingCode'] = $rate->code;
		$shipmentArgs['ShippingDescription'] = $rate->service;
		$shipmentArgs['PickupDate'] = strval($date);

		$shipmentArgs['Packages'] = array();
		for($count = 0; $count < $boxes; $count++) {
			$args = array();
			$args['PackageTypeCode'] = '02';
			$args['PackageDimensionType'] = 'IN';
			$args['PackageLength'] = $SHIPPING[$ship."_BOXLENGTH"];
			$args['PackageWidth'] = $SHIPPING[$ship."_BOXWIDTH"];
			$args['PackageHeight'] = $SHIPPING[$ship."_BOXHEIGHT"];
			$args['PackageWeight'] = strval($box[$count]);
			$args['ReferenceCode'] = '02';
			$args['ReferenceValue'] = '1234567';
			$args['OrderCurrency'] = 'USD';
			$args['OrderPrice'] = strval($subTotal);
			//$args['OrderPrice'] = '2000';
			//$args['OrderPrice'] = strval($box[$count]);
			
			$shipmentArgs['Packages'][] = $args;
		}
		$useUPS = true;
		if($ShoppingCart->isPallet()){
			PHPurchaseCommon::log("Scheduling Shipment With Freight");
			$kaboom = explode(" - ", $rate->service);
			$name = $kaboom[0];
			$frid = $kaboom[2];
			$sictransitgloria = $kaboom[1];
			$weekendAdjuster = 0;
			if((date('N') + 1) == 6){
				$weekendAdjuster = 2;
			}else if((date('N') + 1)== 7){
				$weekendAdjuster = 1;
			}
			$pickup = date('l jS \of F Y h:i:s A', mktime($hour, 0, 0, date('n'), date('j') + 1 + $weekendAdjuster));
			//$pickup = date('l jS \of F Y h:i:s A', $tomorrow);
			$price = $rate->rate;
			$useUPS = false;
			//$freightBooking = new bookFreightShipment($rate->code);
			//$freightBooking->setAsTest();
			$moreInfo = $_POST['payment'];
			$freightShipmentArgs = array();
			$freightShipmentArgs['ContactPerson'] = $shippingInfo['firstName'] . ' ' . $shippingInfo['lastName'];
			$freightShipmentArgs['LocationName'] = $_POST['companyName'];
			$freightShipmentArgs['LocationType'] = $_SESSION['freight_loc_type'];
			$freightShipmentArgs['MainPhone'] = $moreInfo['phone'];
			$freightShipmentArgs['Email'] = $moreInfo['email'];
			$freightShipmentArgs['Address1'] = $validAddress['address'];
			$freightShipmentArgs['City'] = $validAddress['city'];
			$freightShipmentArgs['State'] = $validAddress['state'];
			$freightShipmentArgs['Zip'] = $validAddress['zip'];
			$freightShipmentArgs['HasLoadingDock'] = strcmp($_POST['loadingDock'], 'exists') == 0 ? true : false;
			$freightBooking = new bookFreightShipment($rate->code);
			//$freightBooking->setAsTest();
			$freightBooking->setTestNote();
			$freightBooking->setShipmentArgs($freightShipmentArgs);
			$statusArray = $freightBooking->goBookShipment(true);
			$shippedID = $statusArray['ShipmentId'];
			unset($_SESSION['freight_sinfo']);
			$_SESSION['freight_sinfo'] = "SHIPPED WITH\n--------------------\nCarrier: $name\nTransit: $sictransitgloria\nPickup Date: $pickup\nTotal Shipping: $price\nShipment ID: $shippedID\n\n";
			return 'pallet-' . $name . "-" . $sictransitgloria . "-" . $pickup . "-" . $price . "-" . $statusArray['ShipmentId'] . "-" . $statusArray['Status'];
		}else{
			$upsConnect = new ups("$accessNumber","$username","$password");
			$upsConnect->setTemplatePath(UPS_BASE.'xml/');
			$upsConnect->setTestingMode(0); // Change this to 0 for production
			
			$upsShip = new upsShip($upsConnect);
			$upsShip->buildRequestXML($shipmentArgs);
			$responseArray1 = $upsShip->responseArray();
			$responseStatusCode1 = $responseArray1['ShipmentConfirmResponse']['Response']['ResponseStatusCode']['VALUE'];
		}
		if($useUPS){
			if($responseStatusCode1 == 1) {
				$shipmentXml = $upsShip->buildShipmentAcceptXML($responseArray1['ShipmentConfirmResponse']['ShipmentDigest']['VALUE']);
				$responseArray2 = $upsShip->responseArray();
				
				//var_dump($responseArray2);die;
				$responseStatusCode2 = $responseArray2['ShipmentAcceptResponse']['Response']['ResponseStatusCode']['VALUE'];
				PHPurchaseCommon::log("Shipment Response Array: " . print_r($responseArray2, true), TRUE);
				if($responseStatusCode2 == 1) {
					$shipmentResults = $responseArray2['ShipmentAcceptResponse']['ShipmentResults'];
					storeLabel($responseArray2, UPS_BASE."labels/");
					storeLabel($responseArray2, UPS_LABELS_BASE);

					//echo base64_decode($responseArray2['ShipmentAcceptResponse']['ShipmentResults']['PackageResults']['LabelImage']['HTMLImage']['VALUE']);die;
					
					PHPurchaseCommon::log("Scheduled Shipment: " . print_r($shipmentArgs, true), TRUE);
					/*if(empty($responseArray2['ShipmentAcceptResponse']['ShipmentResults']['PackageResults'][0])) {
						$htmlImage = $responseArray2['ShipmentAcceptResponse']['ShipmentResults']['PackageResults']['LabelImage']['GraphicImage']['VALUE'];
						//echo '<img src="data:image/gif;base64,'.$htmlImage. '"/>';
					} else {
						$htmlImages = array();
						foreach($responseArray2['ShipmentAcceptResponse']['ShipmentResults']['PackageResults'] as $pkgResult) {
							$htmlImages[] = $pkgResult['LabelImage']['GraphicImage']['VALUE'];
							//echo '<img src="data:image/gif;base64,'.$pkgResult['LabelImage']['GraphicImage']['VALUE']. '"/>';
						}
						$htmlImage = implode('---', $htmlImages);
					}*/
					
					return $shipmentResults;
				} else {
					$errorArgs = array('Shippment Args'=>$shipmentArgs, 'UPS Requested'=>$responseArray1, 'UPS Results'=>$responseArray2);
					PHPurchaseCommon::log("Failed to Schedule Shipment 2nd Request: " . print_r($errorArgs, true));
					handleError($errorArgs);
				}
			} else {
				if($requestCount < 3) {
					$requestCount++;
					$result = scheduleShipment($validAddress, ($date+1), $requestCount);
					if($result !== FALSE) {
						return $result;
					}
				} else {
					$errorArgs = array('Shippment Args'=>$shipmentArgs, 'UPS Results'=>$responseArray1);
					PHPurchaseCommon::log("Failed to Schedule Shipment 1st Request: " . print_r($errorArgs, true));
					handleError($errorArgs);
				}
			}
		}else{
			return;
		}
	} else {
		handleError(array('One or more parts of the form are not filled out.  You must provide your UPS credentials in order to get an accurate rate.'));
	}
	return FALSE;
}

function validateStreetAddress() {
// Get credentials from a form
	$accessNumber = UPS_API_KEY;
	$username = UPS_USERID;
	$password = UPS_PASSWORD;

	// If the form is filled out go get a rate from UPS 
	if ($accessNumber != '' && $username != '' && $password != '') {
		$av = new c_address_validator();
		$upsConnect = $av->setUpsAccount("$accessNumber","$username","$password");

		$shippingInfo = $_POST['billing'];
		if(!isset($_POST['sameAsBilling'])) {
			$shippingInfo = $_POST['shipping'];
		}

		$addressArgs = array();
		$addressArgs['line1'] = $shippingInfo['address'];
		$addressArgs['line2'] = $shippingInfo['address2'];
		$addressArgs['city'] = $shippingInfo['city'];
		$addressArgs['state'] = $shippingInfo['state'];
		$addressArgs['zip'] = $shippingInfo['zip'];
		$addressArgs['country'] = $shippingInfo['country'];

		$av->setVerifyTarget($addressArgs);
        $av->commit();
		$responseArray = $av->getResult();
		
		/*$result = $av->getIsSucces();
        $q = $av->getQuality();
		var_dump($av->response);*/
		//echo $av->getHtmlout();die;
		
		if($av->getTotalReturnedAddresses() == 1 && $av->getIsSucces() == 1) {
			$validAddress = $av->getValidAddress();
			$addressArgs['results'] = $validAddress;
			PHPurchaseCommon::log("Validated Address: " . print_r($addressArgs, true), TRUE);
			return array(TRUE, $validAddress);
		} else {
			$errorArgs = array('Shippment Args'=>$p, 'UPS Results'=>$responseArray);
			PHPurchaseCommon::log("failed to Validate Address: " . print_r($errorArgs, true), TRUE);
			if($_SESSION['shippingerrors'] > 2) {
				foreach($GLOBALS['INVALID_ADDESS_EMAILS'] as $email) {
					PHPurchaseCommon::log('bbcharcoal.com FAILED TO VALIDATE ADDRESS FOR A 3rd TIME', var_export($errorArgs, TRUE), TRUE);
					$_SESSION['shippingerrors'] = 0;
					return array(TRUE, $validAddress);
				}
			}
			$_SESSION['shippingerrors']++;
			handleError($errorArgs);
			return array(FALSE, array());
		}
	}
}

function voidShipment($shipmentIdNumber) {
	// Get credentials from a form
	$accessNumber = UPS_API_KEY;
	$username = UPS_USERID;
	$password = UPS_PASSWORD;

	// If the form is filled out go get a rate from UPS 
	if ($accessNumber != '' && $username != '' && $password != '') {
		$upsConnect = new ups("$accessNumber","$username","$password");
		$upsConnect->setTemplatePath(UPS_BASE.'xml/');
		$upsConnect->setTestingMode(0); // Change this to 0 for production
	
		$upsVoid = new upsVoid($upsConnect);
		$upsVoid->buildRequestXML($shipmentIdNumber);
		$responseArray1 = $upsVoid->responseArray();
		
		//var_dump($responseArray1);die;
	}
}

function storeLabel($responseArray2, $path) {
	$packageResults = $responseArray2['ShipmentAcceptResponse']['ShipmentResults']['PackageResults'];
		if(empty($packageResults[0])) {
			$oldPack = $packageResults;
			$packageResults = array();
			$packageResults[0] = $oldPack;
		}
		//$responseArray2['ShipmentAcceptResponse']['ShipmentResults']['PackageResults']['LabelImage']['GraphicImage']['VALUE']
		foreach($packageResults as $package) {
			if(!empty($package['LabelImage']['GraphicImage']['VALUE'])) {
				$myFile = $path."label".$package['TrackingNumber']['VALUE'].".gif";
				$fh = fopen($myFile, 'w');
				$stringData = base64_decode($package['LabelImage']['GraphicImage']['VALUE']);
				fwrite($fh, $stringData);
				fclose($fh);
			} else {
				handleError(array("Failed to find a GIF for storage", $responseArray2));
			}
		}
}

function notifyShipment($shipmentInfo) {
}

function handleError($msg) {
	foreach($GLOBALS['INVALID_ADDESS_EMAILS'] as $email) {
		mail($email, 'bbcharcoal.com part of the shipment failed', var_export($msg, TRUE));
	}
}	
?></body>
</html>
