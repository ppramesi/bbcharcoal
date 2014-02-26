<?php
require_once(WP_PLUGIN_DIR. "/phpurchase/gateways/abstract.php");

class AuthorizeNet extends PHPurchaseGatewayAbstract {

  var $field_string;
  var $fields = array();
  var $response_string;
  var $response = array();
  var $gateway_url;
   
  function __construct($url = 'https://secure.quantumgateway.com/cgi/authnet_aim.php') {
    // initialize error arrays
    $this->_errors = array();
    $this->_jqErrors = array();
    $this->_payment = array();
    $this->_billing = array();
    $this->_shipping = array();

    // some default values
    $this->gateway_url = $url;
    $this->addField('x_version', '3.1');
    $this->addField('x_delim_data', 'TRUE');
    $this->addField('x_delim_char', '|');  
    $this->addField('x_url', 'FALSE');
    $this->addField('x_type', 'AUTH_CAPTURE');
    $this->addField('x_method', 'CC');
    $this->addField('x_relay_response', 'FALSE');
  }
   
   function addField($field, $value) {
      $this->fields["$field"] = $value;   
   }

  public function initCheckout($total) {
    $setting = new PHPurchaseSetting();
    $p = $this->getPayment();
    $b = $this->getBilling();
	$pCopy = $p;
	unset($pCopy['cardNumber']);
    PHPurchaseCommon::log("Payment info for checkout: " . print_r($pCopy, true));
    $expDate = $p['cardExpirationMonth'] . '/' . $p['cardExpirationYear'];
    $this->addField('x_login', $setting->lookupValue('auth_username'));
    $this->addField('x_tran_key', $setting->lookupValue('auth_trans_key'));
    $this->addField('x_card_num', $p['cardNumber']);
    $this->addField('x_exp_date', $expDate);
    $this->addField('x_card_code', $p['securityId']);
    $this->addField('x_first_name', $b['firstName']);
    $this->addField('x_last_name', $b['lastName']);
    $this->addField('x_address', $b['address']);
    $this->addField('x_city', $b['city']);
    $this->addField('x_state', $b['state']);
    
    if(isset($b['country'])) {
      $this->addField('x_country', $b['country']);
      PHPurchaseCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Added country code to auth.net:  " . print_r($b, true));
    }
    
    $this->addField('x_zip', $b['zip']);
    $this->addField('x_phone', $p['phone']);
    $this->addField('x_email', $p['email']);
    $this->addField('x_amount', $total);
  }

   function process() {
      // This function actually processes the payment.  This function will 
      // load the $response array with all the returned information.  The return
      // values for the function are:
      // 1 - Approved
      // 2 - Declined
      // 3 - Error
      
      $responseCode = 3;
      
      if($this->fields['x_amount'] > 0) {
        // construct the fields string to pass to authorize.net
        foreach( $this->fields as $key => $value ) 
           $this->field_string .= "$key=" . urlencode( $value ) . "&";

        // execute the HTTPS post via CURL
        $ch = curl_init($this->gateway_url); 
        curl_setopt($ch, CURLOPT_HEADER, 0); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
        curl_setopt($ch, CURLOPT_POSTFIELDS, rtrim( $this->field_string, "& " )); 
        
        // do not worry about checking for SSL certs
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); 
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
            
            
        $this->response_string = urldecode(curl_exec($ch)); 

        if (curl_errno($ch)) {
           $this->response['Response Reason Text'] = curl_error($ch);
           return 3;
        }
        else curl_close ($ch);


        // load a temporary array with the values returned from authorize.net
        $temp_values = explode('|', $this->response_string);

        // load a temporary array with the keys corresponding to the values 
        // returned from authorize.net (taken from AIM documentation)
        $temp_keys= array ( 
             "Response Code", "Response Subcode", "Response Reason Code", "Response Reason Text",
             "Approval Code", "AVS Result Code", "Transaction ID", "Invoice Number", "Description",
             "Amount", "Method", "Transaction Type", "Customer ID", "Cardholder First Name",
             "Cardholder Last Name", "Company", "Billing Address", "City", "State",
             "Zip", "Country", "Phone", "Fax", "Email", "Ship to First Name", "Ship to Last Name",
             "Ship to Company", "Ship to Address", "Ship to City", "Ship to State",
             "Ship to Zip", "Ship to Country", "Tax Amount", "Duty Amount", "Freight Amount",
             "Tax Exempt Flag", "PO Number", "MD5 Hash", "Card Code (CVV2/CVC2/CID) Response Code",
             "Cardholder Authentication Verification Value (CAVV) Response Code"
        );

        // add additional keys for reserved fields and merchant defined fields
        for ($i=0; $i<=27; $i++) {
           array_push($temp_keys, 'Reserved Field '.$i);
        }
        $i=0;
        while (sizeof($temp_keys) < sizeof($temp_values)) {
           array_push($temp_keys, 'Merchant Defined Field '.$i);
           $i++;
        }

        // combine the keys and values arrays into the $response array.  This
        // can be done with the array_combine() function instead if you are using
        // php 5.
        for ($i=0; $i<sizeof($temp_values);$i++) {
           $this->response["$temp_keys[$i]"] = $temp_values[$i];
        }
        // $this->dump_response();
        // Return the response code.
        $responseCode = $this->response['Response Code'];
      }
      else {
        // Process free orders without sending to the Auth.net gateway
        $responseCode = 1;
        $this->response['Transaction ID'] = 'MR' . PHPurchaseCommon::getRandNum();
      }
      
      return $responseCode;
   }
   
   function getResponseReasonText() {
      return $this->response['Response Reason Text'];
   }
   
   function getTransactionId() {
     return $this->response['Transaction ID'];
   }

   function dumpFields() {
 
      // Used for debugging, this function will output all the field/value pairs
      // that are currently defined in the instance of the class using the
      // add_field() function.
      
      echo "<h3>authorizenet_class->dump_fields() Output:</h3>";
      echo "<table width=\"95%\" border=\"1\" cellpadding=\"2\" cellspacing=\"0\">
            <tr>
               <td bgcolor=\"black\"><b><font color=\"white\">Field Name</font></b></td>
               <td bgcolor=\"black\"><b><font color=\"white\">Value</font></b></td>
            </tr>"; 
            
      foreach ($this->fields as $key => $value) {
         echo "<tr><td>$key</td><td>".urldecode($value)."&nbsp;</td></tr>";
      }
 
      echo "</table><br>"; 
   }

   function dumpResponse() {
 
      // Used for debugging, this function will output all the response field
      // names and the values returned for the payment submission.  This should
      // be called AFTER the process() function has been called to view details
      // about authorize.net's response.
      
      echo "<h3>authorizenet_class->dump_response() Output:</h3>";
      echo "<table width=\"95%\" border=\"1\" cellpadding=\"2\" cellspacing=\"0\">
            <tr>
               <td bgcolor=\"black\"><b><font color=\"white\">Index&nbsp;</font></b></td>
               <td bgcolor=\"black\"><b><font color=\"white\">Field Name</font></b></td>
               <td bgcolor=\"black\"><b><font color=\"white\">Value</font></b></td>
            </tr>";
            
      $i = 0;
      foreach ($this->response as $key => $value) {
         echo "<tr>
                  <td valign=\"top\" align=\"center\">$i</td>
                  <td valign=\"top\">$key</td>
                  <td valign=\"top\">$value&nbsp;</td>
               </tr>";
         $i++;
      } 
      echo "</table><br>";
   }    



}
