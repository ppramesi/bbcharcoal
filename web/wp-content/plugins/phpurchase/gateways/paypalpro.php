<?php
require_once(WP_PLUGIN_DIR. "/phpurchase/gateways/abstract.php");

/**
 * Failure:
 * Array (
 *     [TIMESTAMP] => 2009-12-09T02:13:43Z
 *     [CORRELATIONID] => cade14d44146b
 *     [ACK] => Failure
 *     [VERSION] => 60
 *     [BUILD] => 1073465
 *     [L_ERRORCODE0] => 10759
 *     [L_SHORTMESSAGE0] => Gateway Decline
 *     [L_LONGMESSAGE0] => This transaction cannot be processed. Please enter a valid credit card number and type.
 *     [L_SEVERITYCODE0] => Error
 *     [AMT] => 10.00
 *     [CURRENCYCODE] => USD
 * )
 * 
 * Success:
 * Array (
 *     [TIMESTAMP] => 2009-12-09T02:20:10Z
 *     [CORRELATIONID] => 3b777d1b6490e
 *     [ACK] => Success
 *     [VERSION] => 60
 *     [BUILD] => 1113251
 *     [AMT] => 10.00
 *     [CURRENCYCODE] => USD
 *     [AVSCODE] => X
 *     [CVV2MATCH] => M
 *     [TRANSACTIONID] => 3C577405EM5115349
 * )
 *
 */
class PHPayPal extends PHPurchaseGatewayAbstract {
  
  protected $_apiData;
  protected $_apiEndPoint;
  protected $_apiExpressCheckoutUrl;
  protected $_creditCardData;
  protected $_payerInfo;
  protected $_payerName;
  protected $_payerAddress;
  protected $_payerShipToAddress;
  protected $_paymentDetails;
  protected $_requestFields;
  protected $_ecUrls;
  protected $_items = array();

  public function __construct($mode="TEST") {
    $mode = strtoupper($mode);
    $this->clearErrors();
    // Set end point
    $apiEndPoint = 'https://api-3t.paypal.com/nvp';
    if("TEST" == $mode) {
      $apiEndPoint = 'https://api-3t.sandbox.paypal.com/nvp';
    }
    $this->_apiEndPoint = $apiEndPoint;
    
    // Set express checkout url
    $expressCheckoutUrl = 'https://www.paypal.com/webscr&cmd=_express-checkout&token=';
    if("TEST" == $mode) {
      $expressCheckoutUrl = 'https://www.sandbox.paypal.com/webscr&cmd=_express-checkout&token=';
    }
    $this->_apiExpressCheckoutUrl = $expressCheckoutUrl;
    
    // Set api data
    $setting = new PHPurchaseSetting();
    $this->_apiData['USER'] = $setting->lookupValue('paypalpro_api_username');
    $this->_apiData['PWD'] = $setting->lookupValue('paypalpro_api_password');
    $this->_apiData['SIGNATURE'] = $setting->lookupValue('paypalpro_api_signature');
    $this->_apiData['VERSION'] = '62';

    if(!($this->_apiData['USER'] && $this->_apiData['PWD'] && $this->_apiData['SIGNATURE'])) {
      throw new Exception('Invalid paypal configuration');
    }
  }

  
  /**
   * Set the credit card data.
   * 
   * The passed in array must contain the following keys
   *  -- CREDITCARDTYPE: (Visa,Mastercard,Discover,Amex)
   *  -- ACCT: Credit card number
   *  -- EXPDATE: format MMYYYY
   *  -- CVV2: Card verification value. Character length for Visa, MasterCard, and Discover: exactly three digits.
   *     Character length for American Express: exactly four digits.To comply with credit card processing regulations, 
   *     you must not store this value after a transaction has been completed.
   */
  public function setCreditCardData($data=null) {
    if(!is_array($data)) {
      $p = $this->getPayment();
      $data = array(
        'CREDITCARDTYPE' => $p['cardType'],
        'ACCT' => $p['cardNumber'], //'4532497022010364',
        'EXPDATE' => $p['cardExpirationMonth'] . $p['cardExpirationYear'],
        'CVV2' => $p['securityId']
      );
    }
    $this->_creditCardData = $data;
  }
  
  /**
   * Set the payer information data.
   * 
   * The passed in array must contain the following keys
   *  -- EMAIL: Email address of the payer
   *  -- PAYERID: Unique PayPal customer account identification number. 13 alphanumeric chars
   *  -- PAYERSTATUS: verified or unverified
   *  -- COUNTRYCODE: Payer's country of residence. Two chars
   *  -- BUSINESS: Payer's business name. 127 chars.
   */
  public function setPayerInfo($data=null) {
    if(!is_array($data)) {
      $p = $this->getPayment();
      $b = $this->getBilling();
      $data = array(
        'EMAIL' => $p['email']
      );
      // 'COUNTRYCODE' => $b['country']
    }
    $this->_payerInfo = $data;
  }
  
  /**
   * Set the payer name information
   * 
   * The passed in data must contain the following keys.
   *  -- SALUTATION: Payer's salutation. 20 chars.
   *  -- FIRSTNAME: Payer's first name
   *  -- MIDDLENAME: Payer's middle name
   *  -- LASTNAME: Payer's last name
   *  -- SUFFIX: Payer's suffix
   */
  public function setPayerName($data=null) {
    if(!is_array($data)) {
      $b = $this->getBilling();
      $data = array(
        'FIRSTNAME' => $b['firstName'],
        'LASTNAME' => $b['lastName']
      );
    }
    $this->_payerName = $data;
  }
  
  /**
   * Set the address of the payer.
   * 
   * The passed in array must contain the following keys.
   *  -- STREET: First street address (required)
   *  -- STREET2: Second Street address
   *  -- CITY (required)
   *  -- STATE (required)
   *  -- COUNTRYCODE (required)
   *  -- ZIP (required)
   *  -- SHIPTOPHONENUM
   */
  public function setAddress($data=null) {
    if(!is_array($data)) {
      $b = $this->getBilling();
      $p = $this->getPayment();
      $data = array(
        'STREET' => $b['address'],
        'STREET2' => $b['address2'],
        'CITY' => $b['city'],
        'STATE' => $b['state'],
        'COUNTRYCODE' => $b['country'],
        'ZIP' => $b['zip'],
        'SHIPTOPHONENUM' => $p['phone']
      );
    }
    $this->_payerAddress = $data;
  }
  
  /**
   * Set the ship to address of the payer.
   * 
   * The passed in array must contain the following keys.
   *  -- SHIPTONAME: First Name and Last Name
   *  -- SHIPTOSTREET: First street address (required)
   *  -- SHIPTOSTREET2: Second Street address
   *  -- SHIPTOCITY (required)
   *  -- SHIPTOSTATE (required)
   *  -- SHIPTOCOUNTRYCODE (required)
   *  -- SHIPTOZIP (required)
   *  -- SHIPTOPHONENUM
   */
  public function setShipToAddress($data=null) {
    if(!is_array($data)) {
      $s = $this->getShipping();
      $data = array(
        'SHIPTONAME' => $s['firstName'] . ' ' . $s['lastName'],
        'SHIPTOSTREET' => $s['address'],
        'SHIPTOSTREET2' => $s['address2'],
        'SHIPTOCITY' => $s['city'],
        'SHIPTOSTATE' => $s['state'],
        'SHIPTOCOUNTRYCODE' => $s['country'],
        'SHIPTOZIP' => $s['zip']
      );
    }
    $this->_payerShipToAddress = $data;
  }
  
  /**
   * Set the payment details
   * 
   * The passed in array must contain the following keys.
   *  -- AMT: The total cost to the customer (required)
   *  -- CURRENCYCODE: default USD
   *  -- ITEMAMT: Sum of cost of all items in this order.
   *  -- SHIPPINGAMT: Total shipping costs for this order.
   *  -- INSURANCEAMT: Total shipping insurance costs for this order.
   *  -- SHIPPINGDISCOUNT: Shipping discount for this order, specified as a negative number.
   *  -- INSURANCEOPTIONOFFERED: true or false
   *  -- HANDLINGAMT: Total handling costs for this order.
   *  -- TAXAMT: Sum of tax for all items in this order.
   *  -- DESC: Description of items the customer is purchasing. (127 chars)
   *  -- CUSTOM: A free-form field for your own use. (256 alphanumeric chars)
   *  -- INVNUM: Your own invoice or tracking number.
   *  -- BUTTONSOURCE: An identification code for use by third-party applications to identify transactions.
   *  -- NOTIFYURL: Your URL for receiving Instant Payment Notification (IPN) about this transaction.
   *  -- NOTETEXT: Note to seller
   *  -- TRANSACTIONID: Transaction identification number of the transaction that was created.
   *  -- ALLOWEDPAYMENTMETHOD: InstantPaymentOnly
   */
  public function setPaymentDetails(array $data) {
    $this->_paymentDetails = $data;
  }
  
  /**
   * Add an item from the shopping cart
   * 
   * The passed in array should contain the following keys.
   *  -- NAME: Item name
   *  -- AMT: The price of the item
   *  -- NUMBER: Item number
   *  -- QTY: Item quantity
   *  -- TAXAMT: Item sales tax
   */
  public function addItem(array $data) {
    $this->_items[] = $data;
  }
 
  /**
   * Set the Express Checkout required URLs.
   * 
   * The passed in array must contain the following to keys.
   *  -- RETURNURL: URL to which the customer’s browser is returned after choosing to pay with PayPal.
   *  -- CANCELURL: URL to which the customer is returned if he does not approve the use of PayPal to pay you.
   */
  public function setEcUrls(array $data) {
    $this->_ecUrls = $data;
  }
  
  public function DoDirectPayment() {
    $this->_requestFields = array(
      'METHOD' => 'DoDirectPayment',
      'PAYMENTACTION' => 'Sale',
      'IPADDRESS' => $_SERVER['REMOTE_ADDR']
    );
    $nvp = $this->_buildNvpStr();
    
    $nvpLog = str_replace('&', "\n", $nvp);
    PHPurchaseCommon::log("API END POINT: $this->_apiEndPoint \nNVP: $nvpLog");
    
    $result = $this->_decodeNvp($this->_sendRequest($this->_apiEndPoint, $nvp));
    return $result;
  }
  
  public function SetExpressCheckout() {
    $this->_requestFields = array(
      'METHOD' => 'SetExpressCheckout',
      'PAYMENTACTION' => 'Sale',
      'LANDINGPAGE' => 'Login'
    );
    $nvp = $this->_buildNvpStr();
    $result = $this->_decodeNvp($this->_sendRequest($this->_apiEndPoint, $nvp));
    return $result;
  }
  
  public function getExpressCheckoutUrl($token) {
    return $this->_apiExpressCheckoutUrl . urlencode($token);
  }
  
  public function GetExpressCheckoutDetails($token) {
    $token = urlencode($token);
    $params = array();
    foreach($this->_apiData as $key => $value) {
      $valuey = urlencode($value);
      $params[] = "$key=$value";
    }
    $nvp = implode('&', $params);
    $nvp .= "&METHOD=GetExpressCheckoutDetails&TOKEN=$token";
    $result = $this->_decodeNvp($this->_sendRequest($this->_apiEndPoint, $nvp));
    return $result;
  }
  
  /**
   * A successful return result looks like this:
   * Array
   * (
   *     [TOKEN] => EC-2K187552EW5520044
   *     [SUCCESSPAGEREDIRECTREQUESTED] => false
   *     [TIMESTAMP] => 2009-12-13T22:56:18Z
   *     [CORRELATIONID] => f05cc1a35c955
   *     [ACK] => Success
   *     [VERSION] => 60
   *     [BUILD] => 1077585
   *     [TRANSACTIONID] => 7PD84087WY2993410
   *     [TRANSACTIONTYPE] => expresscheckout
   *     [PAYMENTTYPE] => instant
   *     [ORDERTIME] => 2009-12-13T22:56:17Z
   *     [AMT] => 18.00
   *     [FEEAMT] => 0.82
   *     [TAXAMT] => 0.00
   *     [CURRENCYCODE] => USD
   *     [PAYMENTSTATUS] => Completed
   *     [PENDINGREASON] => None
   *     [REASONCODE] => None
   *     [PROTECTIONELIGIBILITY] => Eligible
   * )
   */
  public function DoExpressCheckout($token, $payerId, $itemAmount, $shipping, $tax=0) {
    $amount = $itemAmount + $shipping + $tax;
  
    $this->_requestFields = array(
      'METHOD' => 'DoExpressCheckoutPayment',
      'PAYMENTACTION' => 'Sale',
      'TOKEN' => $token,
      'PAYERID' => $payerId,
      'AMT' => number_format($amount, 2),
      'ITEMAMT' => number_format($itemAmount, 2),
      'SHIPPINGAMT' => number_format($shipping, 2),
      'TAXAMT' => number_format($tax, 2),
      'CURRENCYCODE' => CURRENCY_CODE
    );
    $nvp = $this->_buildNvpStr();
    
    PHPurchaseCommon::log("Do Express Checkout Request NVP: " . str_replace('&', "\n", $nvp));
    
    $result = $this->_decodeNvp($this->_sendRequest($this->_apiEndPoint, $nvp));
    return $result;
  }
  
  protected function _buildNvpStr() {
    $nvp = false;
    $dataSources = array(
      '_apiData',
      '_requestFields',
      '_ecUrls',
      '_creditCardData',
      '_payerInfo',
      '_payerName',
      '_payerAddress',
      '_paymentDetails',
      '_payerShipToAddress'
    );
    
    $params = array();
    foreach($dataSources as $source) {
      if(is_array($this->$source) && count($this->$source) > 0) {
        foreach($this->$source as $key => $value) {
          // Only add values that contain a value
          if(isset($value) && strlen($value) > 0) {
            $value = urlencode($value);
            $params[] = "$key=$value";
          }
        }
      }
    }
    
    // Add information about individual items
    if(is_array($this->_items) && count($this->_items) > 0) {
      $counter = 0;
      foreach($this->_items as $itemInfo) {
        $params[] = "L_NAME" . $counter . '=' . urlencode($itemInfo['NAME']);
        $params[] = "L_AMT" . $counter . '=' . urlencode(number_format($itemInfo['AMT'], 2));
        $params[] = "L_NUMBER" . $counter . '=' . urlencode($itemInfo['NUMBER']);
        $params[] = "L_QTY" . $counter . '=' . urlencode($itemInfo['QTY']);
        //$params[] = "L_TAXAMT" . $counter . '=' . urlencode($itemInfo['TAXAMT']);
        $counter++;
      }
    }
    
    $nvp = implode('&', $params);
    
    return $nvp;
  }
  
  protected function _sendRequest($url, $data) {
    $numParams = substr_count($data, '&') + 1;
    
    //open connection
    $ch = curl_init();
    
    //set the url, number of POST vars, POST data
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch,CURLOPT_URL,$url);
    curl_setopt($ch,CURLOPT_POST, $numParams);
    curl_setopt($ch,CURLOPT_POSTFIELDS,$data);

    //execute post
    $response = curl_exec($ch);

    //close connection
    curl_close($ch);
    
    return $response;
  }
  
  /**
   * Return an array of decoded NVP data
   */
  protected function _decodeNvp($nvpstr) {
		$intial=0;
		$nvpArray = array();
		
		while(strlen($nvpstr)) {
			// postion of Key
			$keypos= strpos($nvpstr,'=');
			
			// position of value
			$valuepos = strpos($nvpstr,'&') ? strpos($nvpstr,'&'): strlen($nvpstr);
	
			// getting the Key and Value values and storing in a Associative Array
			$keyval=substr($nvpstr,$intial,$keypos);
			$valval=substr($nvpstr,$keypos+1,$valuepos-$keypos-1);
			
			// decoding the respose
			$nvpArray[urldecode($keyval)] = urldecode( $valval);
			$nvpstr=substr($nvpstr,$valuepos+1,strlen($nvpstr));
		}
			
		return $nvpArray;
  }

 
}
