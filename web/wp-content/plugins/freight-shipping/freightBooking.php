<?php
class bookFreightShipment{
	private $testing = false;
	private $dictionary = array("use" => false);
	private $note = "";
	private $sid = -1;
	private $auth = array(
		"customerId" => 48976,
		"username" => "priya@joebotweb.com",
		"password" => 943886
	);
	private $dLoc = array(
		"Id" => 52558,
		"LocationName" => "Casa de Aria",
		"LocationType" => "Residential",
		"Address1" => "5430 Birdwood Road",
		"City" => "Houston",
		"State" => "TX",
		"Zip" => "77096",
		"ContactPerson" => "Aria Pramesi",
		"MainPhone" => "8325227833",
		"Email" => "maekhamin@gmail.com",
		"HasLoadingDock" => false,
		"Note" => "This is just a test for TenderShipmentWithAuth request."
	);
	private $pLoc = array(
		"Id" => 52532,
		"LocationName" => "BB Charcoal",
		"LocationType" => "Commercial",
		"Address1" => "4710 Jeddo Road",
		"City" => "Flatonia",
		"State" => "TX",
		"Zip" => "78941",
		"ContactPerson" => "Priya Pramesi",
		"MainPhone" => "8326338944",
		"Email" => "priya@joebotweb.com",
		"HasLoadingDock" => true,
		"Note" => "This is just a test for TenderShipmentWithAuth request."
	);
	
	public function __construct($sid, $newDLoc = array(), $note = "", $newPLoc = array(), $newAuth = array()){
		if(!empty($newDLoc) && !$this->testing){
			if(empty($newDLoc["Id"])){
				$addId = $this->freightAddAddress($newDLoc);
				$newDLoc["Id"] = $addId;
				//writeLog("Adding Address to the Book on Construction\n");
			}
			$this->dLoc = $newDLoc;
		}	
		if(!empty($newPLoc) && !$this->testing){
			$this->pLoc = $newPLoc;
		}
		if(!empty($newAuth) && !$this->testing){
			$this->auth = $newAuth;
		}
		$this->sid = $sid;
	}
	
	public function setAsTest(){
		$this->testing = true;
		$this->note = "This is just a test for TenderShipmentWithAuth request.";
	}
	
	public function setTestNote(){
		$this->note = "This is just a test for TenderShipmentWithAuth request.";
	}
	
	public function startTheEngine(){
		$this->dLoc = array();
	}
	
	public function setAuth($newAuth){
		$this->auth = $newAuth;
	}
	
	public function setSheetId($sid){
		$this->sid = $sid;
	}
	
	public function setKeyDictionary($dictionary){
		$this->dictionary["use"] = true;
		$this->dictionary = $dictionary;
	}
	
	public function setShipmentArgs($argsArray, $whichOne = "delivery"){
		foreach($argsArray as $arrKey => $arrValue){
			if( isset($this->pLoc[$arrKey]) || isset($this->dLoc[$arrKey])  ){
				if($this->testing){
					$something = $this->dLoc[$arrKey];
					//writeLog("$whichOne : $something $arrKey => $arrValue");
				}else{
					$this->setShipmentArg($arrKey, $arrValue, $whichOne);
				}
			}
		}
		if($this->dictionary["use"]){
			foreach($this->dictionary as $dicKey => $dicWord){
				if($this->testing){
					$something = $this->dLoc[$dicKey];
					//writeLog("$whichOne $something $dicKey = $dicWord");
				}else{
					$this->setShipmentArg($dicKey, $argsArray[$dicWord], $whichOne);
				}
			}
		}
		
		$suffixesFixes = array(
			"/ street$/",
			"/ st$/", 
			"/ str$/", 
			"/ strt$/",
			"/ drive$/",
			"/ dr$/",
			"/ drv$/", 
			"/ driv$/",
			"/ lane$/", 
			"/ ln$/", 
			"/ la$/",
			"/ road$/", 
			"/ rd$/",
			"/ boulevard$/", 
			"/ blvd$/", 
			"/ boul$/", 
			"/ boulv$/",
			"/ park$/", 
			"/ pk$/", 
			"/ prk$/",
			"/ parkway$/", 
			"/ parkwy$/", 
			"/ pkway$/", 
			"/ pkwy$/", 
			"/ pky$/"
		);
		
		$addrSql = ucwords(preg_replace($suffixesFixes, '', strtolower($this->dLoc['Address1']))); 
		/*
		foreach($suffixesFixes as $toBe => $zeArray){
			$this->dLoc['Address1'] = str_replace($zeArray, $toBe, $this->dLoc['Address1']);
		}
		*/
		$deliveryDBExist = freightHelper::checkAddressOnDB($addrSql, $this->dLoc['City'], $this->dLoc['State'], $this->dLoc['Zip']);
		if(is_null($deliveryDBExist) && strcasecmp($whichOne, "delivery") == 0){
			$addDelId = $this->freightAddAddress($this->dLoc);
			$this->dLoc["Id"] = $addDelId;
			freightHelper::insertAddressToDB($this->dLoc["Id"], $addrSql, $this->dLoc['City'], $this->dLoc['State'], $this->dLoc['Zip']);
		}else{
			$this->dLoc["Id"] = $deliveryDBExist;
		}
		/*if(!$this->testing && empty($this->dLoc['Id'])){
			$deliveryDBExist = freightHelper::checkAddressOnDB($this->dLoc['Address1'], $this->dLoc['City'], $this->dLoc['State'], $this->dLoc['Zip']);
			if(is_null($deliveryDBExist)  && strcasecmp($whichOne, "delivery") == 0){
				$addDelId = $this->freightAddAddress($this->dLoc);
				$this->dLoc["Id"] = $addDelId;
			}
			if(empty($this->pLoc['Id']) && strcasecmp($whichOne, "pickup") == 0){
				$addPickId = $this->freightAddAddress($this->pLoc);
				$this->pLoc["Id"] = $addPickId;
				//writeLog("Adding Address to the Book on setShipmentArgs (Pickup)\n");
			}
		}*/
	}
	
	public function setShipmentArg($key, $value, $whichOne){
		switch($whichOne){
			case "delivery":
			case "Delivery":
				$this->dLoc[$key] = $value;
				break;
			case "pickup":
			case "Pickup":
				$this->pLoc[$key] = $value;
				break;
		}
	}
	
	public function goBookShipment($debug = false){
		$rVar = array();
		//if(!empty($this->shipmentArgs['Id'])){
		$response = $this->tenderTheLoinWithSoap($debug);
		/*}else{
		
		}*/
		$rVar['Status'] = $response->Status;
		$rVar['ShipmentId'] = $response->ShipmentId;
		return $rVar;
	}

	private function tenderTheLoinWithSoap($dbug = false){	
		$client = new SoapClient(
			"http://www.efreightline.com/EFLWebServices/Public/ShipperService.asmx?WSDL",
			array(
				"exceptions" => 0,
				"trace" => 1,
				"cache_wsdl" => WSDL_CACHE_NONE
			)
		);

		$sheetId = s_wrap($this->sid, "int");
		$pickup = new stdClass();
		if(!empty($this->pLoc['Id'])){
			$pickup->Id = s_wrap($this->pLoc["Id"], "int");//, "int");
		}else{
			$pickup->LocationName = s_wrap($this->pLoc["LocationName"]);
			$pickup->LocationType = s_wrap($this->pLoc["LocationType"]);
			$pickup->Address1 = s_wrap($this->pLoc["Address1"]);
			//$pickup->Address2 = "";
			$pickup->City = s_wrap($this->pLoc["City"]);
			$pickup->State = s_wrap($this->pLoc["State"]);
			$pickup->Zip = s_wrap($this->pLoc["Zip"]);
			$pickup->ContactPerson = s_wrap($this->pLoc["ContactPerson"]);
			$pickup->MainPhone = s_wrap($this->pLoc["MainPhone"]);
			$pickup->Email = s_wrap($this->pLoc["Email"]);
			$pickup->HasLoadingDock = s_wrap(true, "bool");
			$pickup->TWICRequired = s_wrap(false, "bool");
			$pickup->SeaLinkRequired = s_wrap(false, "bool");
			$pickup->ApptRequired = s_wrap(false, "bool");
			if(!empty($this->pLoc["Note"])){
				$pickup->Note = s_wrap($this->pLoc["Note"]);
			}
		}
		
		$delivery = new stdClass();
		if(!empty($this->dLoc['Id'])){
			$delivery->Id = s_wrap($this->dLoc["Id"], "int");//, "int");
		}else{
			$delivery->LocationName = s_wrap($this->dLoc["LocationName"]);
			$delivery->LocationType = s_wrap($this->dLoc["LocationType"]);
			$delivery->Address1 = s_wrap($this->dLoc["Address1"]);
			//$delivery->Address2 = "";
			$delivery->City = s_wrap($this->dLoc["City"]);
			$delivery->State = s_wrap($this->dLoc["State"]);
			$delivery->Zip = s_wrap($this->dLoc["Zip"]);
			$delivery->ContactPerson = s_wrap($this->dLoc["ContactPerson"]);
			$delivery->MainPhone = s_wrap($this->dLoc["MainPhone"]);
			//$delivery->MainPhoneExt = "MainPhoneExt";
			//$delivery->AltPhone = "AltPhone";
			//$delivery->AltPhoneExt = "AltPhoneExt";
			//$delivery->Fax = "Fax";
			$delivery->Email = s_wrap($this->dLoc["Email"]);
			//$delivery->Hours = "";
			$delivery->HasLoadingDock = s_wrap($this->dLoc["HasLoadingDock"], "bool");
			$delivery->TWICRequired = s_wrap(false, "bool");
			$delivery->SeaLinkRequired = s_wrap(false, "bool");
			$delivery->ApptRequired = s_wrap(false, "bool");
			if(!empty($this->dLoc["Note"])){
				$pickup->Note = s_wrap($this->dLoc["Note"]);
			}
		}

		$instructions = $this->note;
		$pickupHour->StartTime = s_wrap(getTomorrow(11), "datetime");
		//$pickupHour->EndTime = s_wrap(getTomorrow(14), "datetime");
		$pickupHour->IsFirm = s_wrap(true, "bool");
		$pickupTime = s_wrap($pickupHour, "encase");
		$pickupLocation = s_wrap($pickup, "encase");
		$deliveryLocation = s_wrap($delivery, "encase");
		$specialInstructions = s_wrap($instructions);
		//$requestParams = s_wrap($request, "encase");
		$wrapped_false = s_wrap(false, "bool");
		$customerId = s_wrap(48976, "int");
		$username = s_wrap("priya@joebotweb.com", "string");
		$password = s_wrap(943886, "string");
		$freightDetails = s_wrap("", "encase");

		$paramFin = s_wrap(
			array(
				"pickupTime" => $pickupTime,
				"priceSheetId" => $sheetId,
				"deliveryLocation" => $deliveryLocation,
				"specialInstructions" => $specialInstructions, 
				"customerId" => $customerId, 
				"username" => $username, 
				"password" => $password,
				"pickupLocation" => $pickupLocation,
				"updatePickupAddress" => $wrapped_false,
				"updateDeliveryAddress" => $wrapped_false,
				"freightDetails" => $freightDetails
			),
			"encase"
		);

		$xmlresults = $client->__soapCall("TenderShipmentWithAuth", array($paramFin));
		if($dbug || $this->testing){
			//print_d($xmlresults);
			//print_d(htmlentities($client->__getLastRequestHeaders() .  $client->__getLastRequest()));
			//writeLog($xmlresults);
			//writeLog($client->__getLastRequestHeaders() .  $client->__getLastRequest());
		}
		return $xmlresults->TenderShipmentWithAuthResult;
	}
	
	private function freightAddAddress($param){
		$addressClient = new SoapClient(
			"http://www.efreightline.com/EFLWebServices/Public/ShipperService.asmx?WSDL",
			array(
				"exceptions" => 0,
				"trace" => 1,
				"cache_wsdl" => WSDL_CACHE_NONE
			)
		);
		if(!empty($param["LocationName"])){
			$addressEntryObj->LocationName = s_wrap($param["LocationName"]);
		}
		$addressEntryObj->LocationType = s_wrap($param["LocationType"]);
		$addressEntryObj->Address1 = s_wrap($param["Address1"]);
		//$addressEntryObj->Address2 = "";
		$addressEntryObj->City = s_wrap($param["City"]);
		$addressEntryObj->State = s_wrap($param["State"]);
		$addressEntryObj->Zip = s_wrap($param["Zip"]);
		$addressEntryObj->ContactPerson = s_wrap($param["ContactPerson"]);
		if(!empty($param["MainPhone"])){
			$addressEntryObj->MainPhone = s_wrap($param["MainPhone"]);
		}
		if(!empty($param["Email"])){
			$addressEntryObj->Email = s_wrap($param["Email"]);
		}
		//$addressEntryObj->Note = s_wrap($param["Note"]);
		$addressEntryObj->HasLoadingDock = s_wrap($param["HasLoadingDock"], "bool");
		$addressEntryObj->TWICRequired = s_wrap(false, "bool");
		$addressEntryObj->SeaLinkRequired = s_wrap(false, "bool");
		$addressEntryObj->ApptRequired = s_wrap(false, "bool");
		
		$addressEntry = s_wrap($addressEntryObj, "encase");
		$customerId = s_wrap(48976, "int");
		$username = s_wrap("priya@joebotweb.com", "string");
		$password = s_wrap(943886, "string");
		$paramFin = s_wrap(
			array(
				"addressBookEntryToAdd" => $addressEntry,
				"customerId" => $customerId, 
				"username" => $username, 
				"password" => $password,
			),
			"encase"
		);
		$xmlresults = $addressClient->__soapCall("AddAddressWithAuth", array($paramFin));
		//print_d($xmlresults);
		//print_d(htmlentities($addressClient->__getLastRequestHeaders() .  $addressClient->__getLastRequest()));
		if($this->testing){
			//writeLog($xmlresults);
			//writeLog($client->__getLastRequestHeaders() .  $client->__getLastRequest());
		}
		return $xmlresults->AddAddressWithAuthResult->AddressBookEntries->AddressBookEntry->Id;
	}
}

function getToday($hour = 0, $min = 0, $sec = 0){
	$tstamp = date('c', mktime($hour, $min, $sec, date('n'), date('j')));
	return $tstamp;
}

function writeLog($word){
	if(is_bool($word)){
		$word = $word ? "true" : "false";
	}
	$freightLog = fopen(LOG_BASE . "freightShipping.log", "a");
	$today = getToday();
	$freightLogWrite = fwrite($freightLog, $today . ":" . $word . "\n");
	return $freightLogWrite;
}

function getTomorrow($hour){
	$today = getdate();
	$weekendAdjuster = 0;
	if((date('N') + 1) == 6){
		$weekendAdjuster = 2;
	}else if((date('N') + 1)== 7){
		$weekendAdjuster = 1;
	}
	$tstamp = date('c', mktime($hour, 0, 0, date('n'), date('j') + 1 + $weekendAdjuster));
	return $tstamp;
}