<?php 
class PHPurchaseUPS {
  protected $AccessLicenseNumber;  
  protected $UserId;  
  protected $Password;
  protected $shipperNumber;
  protected $credentials;
  protected $dimensionsUnits = "IN";
  protected $weightUnits = "LBS";
  protected $fromZip;

  public function __construct() {
    $setting = new PHPurchaseSetting();
    $this->UserID = $setting->lookupValue('ups_username');
    $this->Password = $setting->lookupValue('ups_password');
    $this->AccessLicenseNumber = $setting->lookupValue('ups_apikey');
    $this->shipperNumber = $setting->lookupValue('ups_account');
    $this->fromZip = $setting->lookupValue('ups_ship_from_zip');
    $this->credentials = 1;
  }
  
  public function setDimensionsUnits($unit){
    $this->dimensionsUnits = $unit;
  }
  
  public function setWeightUnits($unit){
    $this->weightUnits = $unit;
  }

  /**
   * Return the monetary value of the shipping rate or false on failure.
   */
  public function getRate($PostalCode, $dest_zip, $dest_country_code, $service, $weight, $length=0, $width=0, $height=0) {
	GLOBAL $SHIPPING;
    $setting= new PHPurchaseSetting();
    $countryCode = array_shift(explode('~', $setting->lookupValue('home_country')));
    
    if ($this->credentials != 1) {
      print 'Please set your credentials with the setCredentials function';
      die();
    }
	
	if($weight <= $SHIPPING["SMALL_WEIGHTPERBOX"]) {
		$ship = "SMALL";
	} elseif($weight <= $SHIPPING["MID_WEIGHTPERBOX"])  {
		$ship = "MID";
	} else {
		$ship = "LARGE";
	}
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
	$args = array();
	for($count = 0; $count < $boxes; $count++) {
		$args[] = "<Package>  
            <PackagingType>  
            <Code>02</Code>  
            </PackagingType>  
            <Dimensions>  
              <UnitOfMeasurement>  
                <Code>".$this->dimensionsUnits."</Code>  
              </UnitOfMeasurement>  
              <Length>".$SHIPPING[$ship."_BOXLENGTH"]."</Length>  
              <Width>".$SHIPPING[$ship."_BOXWIDTH"]."</Width>  
              <Height>".$SHIPPING[$ship."_BOXHEIGHT"]."</Height>  
            </Dimensions>  
            <PackageWeight>  
              <UnitOfMeasurement>  
                <Code>".$this->weightUnits."</Code>  
              </UnitOfMeasurement>  
              <Weight>".strval($box[$count])."</Weight>  
            </PackageWeight>  
          </Package>";
	}

    $data ="<?xml version=\"1.0\"?>  
      <AccessRequest xml:lang=\"en-US\">  
        <AccessLicenseNumber>$this->AccessLicenseNumber</AccessLicenseNumber>  
        <UserId>$this->UserID</UserId>  
        <Password>$this->Password</Password>  
      </AccessRequest>  
      <?xml version=\"1.0\"?>  
      <RatingServiceSelectionRequest xml:lang=\"en-US\">  
        <Request>  
          <TransactionReference>  
            <CustomerContext>Rating and Service</CustomerContext>  
            <XpciVersion>1.0001</XpciVersion>  
          </TransactionReference>  
          <RequestAction>Rate</RequestAction>  
          <RequestOption>Rate</RequestOption>  
        </Request>  
        <PickupType>  
          <Code>01</Code>  
        </PickupType>  
        <Shipment>  
          <Shipper>  
            <Address>  
            <PostalCode>$PostalCode</PostalCode>  
            <CountryCode>$countryCode</CountryCode>  
            </Address>  
            <ShipperNumber>$this->shipperNumber</ShipperNumber>  
          </Shipper>  
          <ShipTo>  
            <Address>  
            <PostalCode>$dest_zip</PostalCode>  
            <CountryCode>$dest_country_code</CountryCode>  
            <ResidentialAddressIndicator/>  
            </Address>  
          </ShipTo>  
          <ShipFrom>  
            <Address>  
            <PostalCode>$PostalCode</PostalCode>  
            <CountryCode>$countryCode</CountryCode>  
            </Address>  
          </ShipFrom>  
          <Service>  
            <Code>$service</Code>  
          </Service>"; 
          /*<Package>  
            <PackagingType>  
            <Code>02</Code>  
            </PackagingType>  
            <Dimensions>  
              <UnitOfMeasurement>  
                <Code>$this->dimensionsUnits</Code>  
              </UnitOfMeasurement>  
              <Length>".UPS_BOXLENGTH."</Length>  
              <Width>".UPS_BOXWIDTH."</Width>  
              <Height>".UPS_BOXHEIGHT."</Height>  
            </Dimensions>  
            <PackageWeight>  
              <UnitOfMeasurement>  
                <Code>$this->weightUnits</Code>  
              </UnitOfMeasurement>  
              <Weight>$weight</Weight>  
            </PackageWeight>  
          </Package>*/
		  $data .= implode('', $args);
		  $data .= "
      </Shipment>  
      </RatingServiceSelectionRequest>";  
    $ch = curl_init("https://onlinetools.ups.com/ups.app/xml/Rate");  
    curl_setopt($ch, CURLOPT_HEADER, 1);  
    curl_setopt($ch,CURLOPT_POST,1);  
    curl_setopt($ch,CURLOPT_TIMEOUT, 60);  
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);  
    curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);  
    curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);  
    curl_setopt($ch,CURLOPT_POSTFIELDS,$data);  
    $result = curl_exec ($ch); 
    $xml = substr($result, strpos($result, '<RatingServiceSelectionResponse'));
    
    // PHPurchaseCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] UPS XML REQUEST: \n$data");
    // PHPurchaseCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] UPS XML RESULT: \n$xml");
    
    $xml = new SimpleXmlElement($xml);
    $responseDescription = $xml->Response->ResponseStatusDescription;
    $errorDescription = $xml->Response->Error->ErrorDescription;
    PHPurchaseCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Response Description: (Service: $service) $responseDescription $errorDescription");
    if($responseDescription == "Failure") {
      $rate = false;
    }
    else {
      //$rate = $xml->RatedShipment->RatedPackage->TotalCharges->MonetaryValue;
      $rate = $xml->RatedShipment->TotalCharges->MonetaryValue; 
    }
    
	if(!empty($rate)) {
		PHPurchaseCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] RATE ===> $rate");
	}
    return $rate;
  }  

  /**
   * Return an array where the keys are the service names and the values are the prices
   */
  public function getAllRates($toZip, $toCountryCode, $weight) {
    global $wpdb;
    $rates = array();
    $shippingMethods = PHPurchaseCommon::getTableName('shipping_methods');
    $sql = "SELECT name, code from $shippingMethods where carrier = 'ups'";
    $results = $wpdb->get_results($sql);
    foreach($results as $method) {
      $rate = $this->getRate($this->fromZip, $toZip, $toCountryCode, $method->code, $weight);
      if($rate !== FALSE) {
        $rates[$method->name] = array('code'=>$method->code, 'rate'=>number_format((float) $rate, 2));
      }
      PHPurchaseCommon::log("LIVE RATE REMOTE RESULT ==> ZIP: $toZip Service: $method->name ($method->code) Rate: $rate");
    }
    
    if(count($rates) == 0) {
      $rates['No Shipping Methods Available'] = false;
    }
    
    return $rates;
  }

}