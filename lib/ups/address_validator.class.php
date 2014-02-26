<?php

class c_address_validator
{
    var $xml_acc_key;       #
    var $userid;            #
    var $passw;             #
    var $trans_description; #UPS Address Validation


	var $line1;
	var $line2;
	var $country;
    var $city;
    var $state;
    var $zip;
	var $returnedAddresses;
	
	var $ups_serv_url =     "https://onlinetools.ups.com/ups.app/xml/XAV";
    //var $ups_serv_url = "https://wwwcie.ups.com/ups.app/xml/XAV";
	// production = https://onlinetools.ups.com/ups.app/xml/XAV
    var $response;
    var $out;

    function setUpsAccount($xml_acc_key,$userid,$passw){
        $this->xml_acc_key = $xml_acc_key;
        $this->userid = $userid;
        $this->passw = $passw;
    }

    function setVerifyTarget($addressArgs){
		$this->line1 = trim($addressArgs['line1']);
		$this->line2 = trim($addressArgs['line2']);
        $this->city  = trim($addressArgs['city']);
        $this->state = $this->getStateCode($addressArgs['state']);
        $this->zip   = trim($addressArgs['zip']);
		$this->country = trim($addressArgs['country']);
        $this->trans_description = addslashes(trim($addressArgs['description']));
    }

    function commit(){
        $xpciv = '1.0001';
        $upsAccessLicenseNumber = $this->xml_acc_key;
        $upsUserID = $this->userid;
        $upsPassWord = $this->passw;
        $description = $this->trans_description;

        $data = "
            <?xml version=\"1.0\" ?>
            <AccessRequest xml:lang=\"en-US\">
                <AccessLicenseNumber>".$this->xml_acc_key."</AccessLicenseNumber>
                <UserId>".$this->userid."</UserId>
                <Password>".$this->passw."</Password>
            </AccessRequest>

            <?xml version=\"1.0\" ?>
            <AddressValidationRequest xml:lang=\"en-US\">
                <Request>
                    <TransactionReference>
                        <CustomerContext>".$this->trans_description."</CustomerContext>
                        <XpciVersion>$xpciv</XpciVersion>
                    </TransactionReference>

                    <RequestAction>XAV</RequestAction>
					<RequestOption>3</RequestOption>
                </Request>
				<MaximumListSize>5</MaximumListSize>
				<AddressKeyFormat>
					<AddressLine>".$this->line1."</AddressLine>
					<AddressLine>".$this->line2."</AddressLine>
					<PoliticalDivision2>".$this->city."</PoliticalDivision2>
					<PoliticalDivision1>".$this->state."</PoliticalDivision1>
					<PostcodePrimaryLow>".$this->zip."</PostcodePrimaryLow>
					<CountryCode>".$this->country."</CountryCode>
				</AddressKeyFormat>

            </AddressValidationRequest>
            ";
            
		 PHPurchaseCommon::log($data, TRUE);
		 
         $ch = curl_init();                                     /// initialize a cURL session
         curl_setopt ($ch, CURLOPT_URL,$this->ups_serv_url);    /// set the post-to url (do not include the ?query+string here!)
         curl_setopt ($ch, CURLOPT_HEADER, 0);                  /// Header control
         curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, FALSE);      /// Use this to prevent PHP from verifying the host (later versions of PHP including 5)
         curl_setopt ($ch, CURLOPT_POST, 1);                    /// tell it to make a POST, not a GET
         curl_setopt ($ch, CURLOPT_POSTFIELDS, $data);          /// put the query string here starting with "?"
         curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);          /// This allows the output to be set into a variable $xyz
         $upsResponse = curl_exec ($ch);                        /// execute the curl session and return the output to a variable $xyz
         curl_close ($ch);                                      /// close the curl session

         $resp = $this->XMLParser($upsResponse);

		 $totalAddresses = 0;
         foreach ($resp as $v)
         {
			if($v['tag'] == 'AddressKeyFormat' && $v['type'] == 'open') {
				$totalAddresses++;
			}
            if ($v['type'] = 'complete' and '' != $v['value']){
                $all_html_resp[$v['tag']] = $v['value'];
            }
        }

        $this->response = $all_html_resp;
		$this->returnAddresses = $totalAddresses;
		
        $this->out = "";
        $this->out.= "<table cellspacing=1 bgcolor=black>";
        if(is_array($all_html_resp)){
			foreach ($all_html_resp as $k => $v){
				$this->out.= "<tr bgcolor=white>
							  <td>$k</td><td>$v</td>
							 </tr>
				   ";
			}
		}
        $this->out.= "</table>";


    }

    function getResult()
    {
        return $this->response;
    }
    function getHtmlout()
    {
        return $this->out;
    }
	function getTotalReturnedAddresses()
    {
        return $this->returnAddresses;
    }
	function getValidAddress() {
		if($this->getTotalReturnedAddresses() == 1) {
			$result = $this->getResult();
			$valid['address'] = $result['AddressLine'];
			$valid['city'] = $result['PoliticalDivision2'];
			$valid['state'] = $result['PoliticalDivision1'];
			$valid['zip'] = $result['PostcodePrimaryLow'];
			$valid['country'] = $result['CountryCode'];
			
			return $valid;
		}
		return FALSE;
	}
    function XMLParser($simple)
    {
        $vals = false;
        $index = false;
        $p = xml_parser_create();
        xml_parser_set_option($p,XML_OPTION_CASE_FOLDING,0);
        xml_parser_set_option($p,XML_OPTION_SKIP_WHITE,1);
        xml_parse_into_struct($p,$simple,$vals,$index);
        xml_parser_free($p);

        return $vals;
    }

    function getQuality(){
        $a = $this->response;
        return $a['Quality'];
    }

    function getIsSucces(){
        $a = $this->response;
        return $a['ResponseStatusCode'];
    }

    function getErrorDescription(){
        $a = $this->response;
        if($a == '' || $a = array()){
            return 'Service unavailable';  #no connection to internet / ups server offline
        }

        return  $a['ErrorDescription'];
    }


    function getStateCode($state = false){
         $state_codes = array(
        "Alabama              " => "AL",
        "Alaska               " => "AK",
        "Arizona              " => "AZ",
        "Arkansas             " => "AR",
        "California           " => "CA",
        "Colorado             " => "CO",
        "Connecticut          " => "CT",
        "Delaware             " => "DE",
        "District of Columbia " => "DC",
        "Florida              " => "FL",
        "Georgia              " => "GA",
        "Hawaii               " => "HI",
        "Idaho                " => "ID",
        "Illinois             " => "IL",
        "Indiana              " => "IN",
        "Iowa                 " => "IA",
        "Kansas               " => "KS",
        "Kentucky             " => "KY",
        "Louisiana            " => "LA",
        "Maine                " => "ME",
        "Maryland             " => "MD",
        "Massachusetts        " => "MA",
        "Michigan             " => "MI",
        "Minnesota            " => "MN",
        "Mississippi          " => "MS",
        "Missouri             " => "MO",
        "Montana              " => "MT",
        "Nebraska             " => "NE",
        "Nevada               " => "NV",
        "New Hampshire        " => "NH",
        "New Jersey           " => "NJ",
        "New Mexico           " => "NM",
        "New York             " => "NY",
        "North Carolina       " => "NC",
        "North Dakota         " => "ND",
        "Ohio                 " => "OH",
        "Oklahoma             " => "OK",
        "Oregon               " => "OR",
        "Pennsylvania         " => "PA",
        "Rhode Island         " => "RI",
        "South Carolina       " => "SC",
        "South Dakota         " => "SD",
        "Tennessee            " => "TN",
        "Texas                " => "TX",
        "Utah                 " => "UT",
        "Vermont              " => "VT",
        "Virginia             " => "VA",
        "Washington           " => "WA",
        "West Virginia        " => "WV",
        "Wisconsin            " => "WI",
        "Wyoming              " => "WY",
        "Alberta              " => "AB",
        "British Columbia     " => "BC",
        "Manitoba             " => "MB",
        "New Brunswick        " => "NB",
        "Newfoundland         " => "NF",
        "Northwest Territories" => "NT",
        "Nova Scotia          " => "NS",
        "Nunavut              " => "NU",
        "Ontario              " => "ON",
        "Prince Edward Island " => "PE",
        "Quebec               " => "QC",
        "Saskatchewan         " => "SK",
        "Yukon                " => "YT" );


        $r = $state;
        if(strlen($state) > 2){
            foreach ($state_codes as $k => $v){
                    if(trim(strtolower($state)) == trim(strtolower($k))){
                        $r = strtoupper($v);
                    }
            }
        }

        return $r;

    }

}



?>