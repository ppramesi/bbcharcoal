<?php
/*
Plugin Name: PHPurchase eFreight Add-On
Plugin URI: http://www.bbcharcoal.com
Description: add-on for eFreight
Version: 0.0.1
Author: JoeBot Web
Author URI: http://www.bbcharcoal.com
*/
define("FXLMNS", "http://www.eFreightLine.com/EFLWebServices/Public/");

require_once(WP_PLUGIN_DIR . "/" . basename(dirname(__FILE__)) . "/freightBooking.php");
require_once(WP_PLUGIN_DIR . "/" . basename(dirname(__FILE__)) . "/freightHelper.php");
//require_once(WP_PLUGIN_DIR . "/" . basename(dirname(__FILE__)) . "/freightAddAddress.php");

function print_d($array){    echo "<pre>\n";    print_r($array);    echo "</pre>\n";}//debug tool

function freightSqlConstructor(){
	global $wpdb;
	$addrTableName = $wpdb->prefix . "pur_fr_addresses";
	$frOrderTableName = $wpdb->prefix . "pur_fr_orders";
	$addrQuery = "CREATE TABLE IF NOT EXISTS $addrTableName (
		Id INT(7) NOT NULL,
		Address1 VARCHAR(50) NOT NULL,
		City VARCHAR(20) NOT NULL,
		State VARCHAR(3) NOT NULL,
		Zip VARCHAR(5) NOT NULL,
		PRIMARY KEY(Id)
	);";
	$frOrderQuery = "CREATE TABLE IF NOT EXISTS $frOrderTableName (
		Id INT(7) NOT NULL,
		Carrier VARCHAR(25) NOT NULL,
		Transit INT(3) NOT NULL,
		Pickup VARCHAR(50) NOT NULL,
		Price DECIMAL(7,2) NOT NULL,
		Sid INT(7) NOT NULL,
		PRIMARY KEY(Id)
	);";
	/*$authQuery = "CREATE TABLE IF NOT EXISTS $authTableName (
		Name VARCHAR(10) NOT NULL,
		Value VARCHAR(30) NOT NULL,
		PRIMARY KEY(Name)
	);";*/
	$wpdb->query($addrQuery);
	$wpdb->query($frOrderQuery);
	//$wpdb->query($authQuery);
}
register_activation_hook( __FILE__, 'freightSqlConstructor' );

function s_wrap($sval, $stype = "string", $usenm = true){
	$type = XSD_STRING;
	$false_type = false;
	//echo 71;
	switch($stype){
		case "integer":
		case "int":
			//echo ' int:' . $sval . ' ';
			$type = XSD_INTEGER;
			break;
		case "string":
			//echo "b";
			$type = XSD_STRING;
			break;
		case "bool":
			//echo "c";
			$type = XSD_BOOLEAN;
			break;
		case "float":
			//echo "d";
			$type = XSD_FLOAT;
			break;
		case "encase":
			//echo "e";
			$type = SOAP_ENC_OBJECT;
			break;
		case "datetime":
			//echo "e";
			$type = XSD_DATETIME;
			break;
		default:
			//echo "fuck";
			$false_type = true;
			break;
	}
	if($false_type){
		return new SoapFault("SOAP Var fault", "Soap Var type not recognized");
	}
	if($usenm){
		return new SoapVar($sval, $type, NULL, NULL, NULL, FXLMNS);
	}else{
		return new SoapVar($sval, $type);
	}
}

class freightShipping{
	private $param = array();

	public function __construct($param){
		$this->param = $param;
	}
	
	public function getFreightRates(){
		return $this->price_parser($this->call_the_freight());
	}
	
	private function call_the_freight($load = "ltl", $debug = false, $originCity = "Flatonia", $originState = "TX", $originZip = "78941", $originType = "Commercial"){//change to array  4710 Jeddo Rd, Flatonia, TX 78941
		$itemDesc = $this->param['itemDesc'];
		$itemPrice = $this->param['itemPrice'];
		//$height = $this->param['height'];
		$weight = $this->param['weight'];
		$count = $this->param['count'];
		$refNumber = $this->param['refNumber'];

		$destinationCity = $this->param['destinationCity'];
		$destinationState = $this->param['destinationState'];
		$destinationZip = $this->param['destinationZip'];
		$destinationType = $this->param['destinationType'];

		$client = new SoapClient(
			"http://www.efreightline.com/EFLWebServices/Public/ShipperService.asmx?WSDL",
			array(
				"exceptions" => 0,
				"trace" => 1//,
				//"cache_wsdl" => WSDL_CACHE_NONE
			)
		);

		// Send the request and get the results
		$authParams = array(
			'CustomerId' => 48976,
			'Username' => "priya@joebotweb.com",
			'Password' => "943886"
		);
		$itemParams = new stdClass();
		$itemParams->Commodity = s_wrap($itemDesc); // required - brief description of the item / freight 
		$itemParams->CommodityType = s_wrap("New"); // required - either “New”, “Used” or “HHG” 
		$itemParams->DeclaredValue = s_wrap($itemPrice, "int"); // must be supplied if you wish to insure the full value of the shipment     via the “purchase_ins” accessorial code and vice versa. 
		$itemParams->FreightClass = s_wrap((float) 85, "float"); // required for “ltl” ServiceMode only 
		//$itemParams->Length = s_wrap(40, "int"); // optional – in inches 
		//$itemParams->Width = s_wrap(48, "int"); // options – in inches 
		//$itemParams->Height = s_wrap($height, "int"); // optional – in inches 
		$itemParams->Weight = s_wrap($weight, "int"); // required - in pounds 
		//$itemParams->Height = s_wrap(48, "int"); // required - in pounds 
		$itemParams->IsHazardous = s_wrap(false, "bool"); // optional – only required if item is in fact hazardous, otherwise    can be left unassigned and will default to false for rating purposes 
		$itemParams->NMFCNumber = s_wrap("42480"); // optional but preferred for “ltl” ServiceMode, irrelevant for all      other ServiceMode values 
		$itemParams->PackagingType = s_wrap("Pallet"); // optional – one of the valid PackagingType values if set 
		$itemParams->PieceCount = s_wrap($count, "int"); // optional – only refers to this particular instance of ShippingItem – in     most cases it is more appropriate to create another ShippingItem
		$itemParams->Equipment = s_wrap("", "int");
		$request = new stdClass();
		$request->ShippingItems = s_wrap(array("ShippingItem" => s_wrap($itemParams, "encase")), "encase");  
		//$request->RefNumber = s_wrap($refNumber);  
		// Must declare either City & State combination or Zip
		// It is OK to supply all three
		// valid 2 character US State or Canadian Province abbreviation 
		$request->OriginZip = s_wrap($originZip);
		$request->OriginCity = s_wrap($originCity); 
		$request->OriginState = s_wrap($originState);
		$request->OriginType = s_wrap($originType);
	
		if(empty($destinationZip)){
			$request->DestinationCity = s_wrap($destinationCity);
			$request->DestinationState = s_wrap($destinationState);
		}else{
			$request->DestinationZip = s_wrap($destinationZip);
		}
		$request->DestinationType = s_wrap($destinationType);

		$request->ServiceMode = s_wrap("ltl");

		$requestParams = s_wrap($request, "encase");
		$customerId = s_wrap(48976, "int");
		$username = s_wrap("priya@joebotweb.com", "string");
		$password = s_wrap(943886, "string");
		$paramFin = s_wrap(
			array(
				"request" => $requestParams, 
				"customerId" => $customerId, 
				"username" => $username, 
				"password" => $password
			),
			"encase"
		);
		$xmlresults = $client->__soapCall("GetLTLFreightQuoteWithAuth", array($paramFin));
		$last_request = htmlentities($client->__getLastRequestHeaders() .  $client->__getLastRequest());
		if($debug){
			print_d($xmlresults);
			print_d($last_request);
		}
		return $xmlresults->GetLTLFreightQuoteWithAuthResult;
	}

	private function price_parser($data){
		$prices = array();
		
		foreach($data->PriceSheets->PriceSheet as $key => $priceSheet){
			/* old way, might be used later on
			$item = array();
			$item['totalPrice'] = round((float) $priceSheet->TotalPrice, 2);
			$item['Id'] = $priceSheet->Id;
			$item['name'] = $priceSheet->CarrierName;
			$item['transit'] = (int) $priceSheet->TransitDays;
			$item['note'] = $priceSheet->Note;
			$prices[] = $item;
			*/
			$sicTransitGloria = (int) $priceSheet->TransitDays;
			$serviceName = $priceSheet->CarrierName;
			$serviceId = $priceSheet->Id;
			$service = $serviceName . ' - ' . $sicTransitGloria . ' day - ' . $serviceId;
			$prices[$service] = round((float) $priceSheet->TotalPrice, 2);
		}
		
		return $prices;
	}
}
?>