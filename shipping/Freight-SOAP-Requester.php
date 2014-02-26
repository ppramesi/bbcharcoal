<?php

/**
 * Plugin Name: Freight SOAP Requester
 * Plugin URI: http://pramesi.net
 * Description: This thing gets stuff from an eBay store and do things to 'em
 * Version: 1.0.0
 * Author: Priya X. Pramesi
 * Author URI: http://pramesi.net
 * License: GPL2
 */
 
 /*  Copyright 2013  Priya X. Pramesi  (email : ppramesi@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/* Questions:
define ltl
customerID, hows it given?
NMFC number
error on the docs (GetFreightQuotes->GetFreightQuote)
*/

function print_d($array){    echo "<pre>\n";    print_r($array);    echo "</pre>\n";}//debug tool

function call_the_train(/*$itemDesc, $itemPrice, $freightClass, $length, $width, $height, $weight, $NMFC = "12345-1", $count, $refNumber, $originCity, $originState, $originZip, $originType, $load*/){
	//echo 1;
	$client = new SoapClient(
		"http://www.efreightline.com/EFLWebServices/Public/ShipperService.asmx?WSDL",
		array(
			"exceptions" => 0,
			"trace" => 1,
		)
	);
	//echo 2;

	//begin deployable codes. delete comments when testing/deployment

	/* Using placeholder values for now. Next step is to get the username + login and test with that.
	$shipperService = $client->ShipperService;
	// Send the request and get the results
	$authParams->CustomerId = 12355;
	$authParams->Username = "something@email.com";
	$authParams->Password = "SomePassword";
	$itemParams->Commodity = $itemDesc; // required - brief description of the item / freight 
	$itemParams->CommodityType = "New"; // required - either “New”, “Used” or “HHG” 
	$itemParams->DeclaredValue = $itemPrice; // must be supplied if you wish to insure the full value of the shipment     via the “purchase_ins” accessorial code and vice versa. 
	$itemParams->FreightClass = (float) $freightClass; // required for “ltl” ServiceMode only 
	$itemParams->Length = $length; // optional – in inches 
	$itemParams->Width = $width; // options – in inches 
	$itemParams->Height = $height; // optional – in inches 
	$itemParams->Weight = $weight; // required - in pounds 
	$itemParams->IsHazardous = false; // optional – only required if item is in fact hazardous, otherwise    can be left unassigned and will default to false for rating purposes 
	$itemParams->NMFCNumber = "12345-1"; // optional but preferred for “ltl” ServiceMode, irrelevant for all      other ServiceMode values 
	$itemParams->PackagingType = "Crate"; // optional – one of the valid PackagingType values if set 
	$itemParams->PieceCount = $count; // optional – only refers to this particular instance of ShippingItem – in     most cases it is more appropriate to create another ShippingItem 
	$requestParams->ShippingItems = $itemParams;
	$requestParams->RefNumber = $refNumber;  
	// Must declare either City & State combination or Zip
	// It is OK to supply all three
	$requestParams->OriginCity = $originCity; 
	$requestParams->OriginState = $originState; 
	// valid 2 character US State or Canadian Province abbreviation 
	$requestParams->OriginZip = '' . $originZip;
	$requestParams->OriginType = $originType;
	$requestParams->ServiceMode = strcasecmp($load, 'ftl') == 0 ? "ftl" : "ltl";
	$shipperService->AuthHeaderValue = $authParams;*/

	//begin placeholder. delete this shit before testing/deployment

	//echo 3;
	$shipperService = $client->ShipperService;
	// Send the request and get the results
	$authParams->CustomerId = 12355;
	$authParams->Username = "something@email.com";
	$authParams->Password = "SomePassword";
	$itemParams->Commodity = "Crated Automobile Engine"; // required - brief description of the item / freight 
	$itemParams->CommodityType = "New"; // required - either “New”, “Used” or “HHG” 
	$itemParams->DeclaredValue = 2500; // must be supplied if you wish to insure the full value of the shipment     via the “purchase_ins” accessorial code and vice versa. 
	$itemParams->FreightClass = (float) 125; // required for “ltl” ServiceMode only 
	$itemParams->Length = 52; // optional – in inches 
	$itemParams->Width = 48; // options – in inches 
	$itemParams->Height = 48; // optional – in inches 
	$itemParams->Weight = 650; // required - in pounds 
	$itemParams->IsHazardous = false; // optional – only required if item is in fact hazardous, otherwise    can be left unassigned and will default to false for rating purposes 
	$itemParams->NMFCNumber = "12345-1"; // optional but preferred for “ltl” ServiceMode, irrelevant for all      other ServiceMode values 
	$itemParams->PackagingType = "Crate"; // optional – one of the valid PackagingType values if set 
	$itemParams->PieceCount = 1; // optional – only refers to this particular instance of ShippingItem – in     most cases it is more appropriate to create another ShippingItem 
	$requestParams->ShippingItems = $itemParams;  
	$requestParams->RefNumber = "YourReference";  
	// Must declare either City & State combination or Zip
	// It is OK to supply all three
	$requestParams->OriginCity = "Cleveland"; 
	$requestParams->OriginState = "OH"; 
	// valid 2 character US State or Canadian Province abbreviation 
	$requestParams->OriginZip = "44114";
	$requestParams->OriginType = "Residential";
	$requestParams->ServiceMode = "ltl";
	$client->ShipperService->AuthHeaderValue = $authParams;
	//echo 4;
	//$xmlresults = $shipperService->GetFreightQuote(array($requestParams));
	//echo 5;
	$xmlresults = $client->__soapCall("GetFreightQuote", array($requestParams));
	print_d($xmlresults);
	return $xmlresults;
}

add_shortcode('priya-ebay', 'priya_ebay');
function priya_ebay($atts){
	$r_var = "";
	call_the_train();
	return $r_var;
}
?>