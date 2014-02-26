<?php
class PayPalGateway {
  
  /**
   * @var The URL for connecting to PayPal
   */
  protected $_paypalUrl;
  protected $_log;
  
  public function __construct($paypalUrl='https://www.paypal.com/cgi-bin/webscr') {
    $this->_paypalUrl = $paypalUrl;
    PHPurchaseCommon::log("Constructing PayPal Gateway for IPN");
  }
  
  public function validate($rawPost) {
    PHPurchaseCommon::log("Validate PayPal Post Data: \n" . print_r($rawPost, true));
    $postdata = '';
    foreach($rawPost as $i => $v) {
    	$postdata .= $i.'='.urlencode(stripslashes($v)).'&';
    }
    $postdata .= 'cmd=_notify-validate';
    PHPurchaseCommon::log("PayPal Validation Post Back: $postdata");

    $web = parse_url($this->_paypalUrl);
    if ($web['scheme'] == 'https') { 
    	$web['port'] = 443;  
    	$ssl = 'ssl://'; 
    } else { 
    	$web['port'] = 80;
    	$ssl = ''; 
    }
    $fp = @fsockopen($ssl.$web['host'], $web['port'], $errnum, $errstr, 30);

    if (!$fp) { 
    	echo "Socket error -- " . $errnum.': '.$errstr;
    } 
    else {
    	fputs($fp, "POST ".$web['path']." HTTP/1.1\r\n");
    	fputs($fp, "Host: ".$web['host']."\r\n");
    	fputs($fp, "Content-type: application/x-www-form-urlencoded\r\n");
    	fputs($fp, "Content-length: ".strlen($postdata)."\r\n");
    	fputs($fp, "Connection: close\r\n\r\n");
    	fputs($fp, $postdata . "\r\n\r\n");

    	while(!feof($fp)) { 
    		$info[] = @fgets($fp, 1024); 
    	}
    	fclose($fp);
    	$infoString = implode(',', $info);
      
    	if (eregi('VERIFIED', $infoString)) {
    		// PayPal Verification Success'
    		if($rawPost['mc_gross'] >= 0) {
    		  foreach($rawPost as $key => $val) {
    		    $decodedPost[$key] = stripslashes(urldecode($val));
    		  }
    		  $this->saveOrder($decodedPost);
    		}
    	} 
    	else {
    		// PayPal Verification Failed
    		PHPurchaseCommon::log("PayPal Validation failed: $infoString\n\n");
    	}
    }
  }
  
  public function saveOrder($pp) {
    global $wpdb;
    
    $orderTable = PHPurchaseCommon::getTableName('orders');
    
    // Make sure the transaction id is not already in the database
    $sql = "SELECT count(*) as c from $orderTable where trans_id=%s";
    $sql = $wpdb->prepare($sql, $pp['txn_id']);
    $count = $wpdb->get_var($sql);
    if($count < 1) {
      $hasDigital = false;
      
      // Calculate subtotal
      $subtotal = 0;
      $numCartItems = ($pp['num_cart_items'] > 0) ? $pp['num_cart_items'] : 1;
      for($i=1; $i<= $numCartItems; $i++) {
        // PayPal in not consistent in the way it passes back the item amounts
        $amt = 0;
        if(isset($pp['mc_gross' . $i])) {
          $amt = $pp['mc_gross' . $i];
        }
        elseif(isset($pp['mc_gross_' . $i])) {
          $amt = $pp['mc_gross_' . $i];
        }
        $subtotal += $amt;
      }

      $statusOptions = PHPurchaseCommon::getOrderStatusOptions();
      $status = $statusOptions[0];

      $ouid = md5($pp['txn_id'] . $pp['address_street']);

      // Parse custom value
      $referrer = false;
      $deliveryMethod = $pp['custom'];
      if(strpos($deliveryMethod, '~') !== false) {
        list($deliveryMethod, $referrer) = explode('~', $deliveryMethod);
      }

      // Look for discount amount
      $discount = 0;
      if(isset($pp['discount'])) {
        $discount = $pp['discount'];
      }

      $data = array(
        'bill_first_name' => $pp['address_name'],
        'bill_address' => $pp['address_street'],
        'bill_city' => $pp['address_city'],
        'bill_state' => $pp['address_state'],
        'bill_zip' => $pp['address_zip'],
        'bill_country' => $pp['address_country'],
        'ship_first_name' => $pp['address_name'],
        'ship_address' => $pp['address_street'],
        'ship_city' => $pp['address_city'],
        'ship_state' => $pp['address_state'],
        'ship_zip' => $pp['address_zip'],
        'ship_country' => $pp['address_country'],
        'shipping_method' => $deliveryMethod,
        'email' => $pp['payer_email'],
        'phone' => $pp['contact_phone'],
        'shipping' => $pp['mc_handling'],
        'tax' => $pp['tax'],
        'subtotal' => $subtotal,
        'total' => $pp['mc_gross'],
        'discount_amount' => $discount,
        'trans_id' => $pp['txn_id'],
        'ordered_on' => date('Y-m-d H:i:s'),
        'status' => $status,
        'ouid' => $ouid
      );


      // Verify the first items in the IPN are for products managed by PHPurchase. It could be an IPN from some other type of transaction.
      $productsTable = PHPurchaseCommon::getTableName('products');
      $orderItemsTable = PHPurchaseCommon::getTableName('order_items');
      $sql = "SELECT id from $productsTable where item_number = '" . $pp['item_number1'] . "'";
      $productId = $wpdb->get_var($sql);
      if(!$productId) {
        throw new Exception("This is not an IPN that should be managed by PHPurchase");
      }
      
      $wpdb->insert($orderTable, $data);
      $orderId = $wpdb->insert_id;

      $product = new PHPurchaseProduct();
      for($i=1; $i <= $numCartItems; $i++) {
        $sql = "SELECT id from $productsTable where item_number = '" . $pp['item_number' . $i] . "'";
        $productId = $wpdb->get_var($sql);
        $product->load($productId);
        
        // Decrement inventory
        $info = $pp['item_name' . $i];
        if(strpos($info, '(') > 0) {
          $start = strpos($info, '(');
          $end = strpos($info, ')');
          $length = $end - $start;
          $variation = substr($info, $start+1, $length-1);
          PHPurchaseCommon::log("PayPal Variation Information: $variation\n$info");
        }
        $qty = $pp['quantity' . $i];
        PHPurchaseProduct::decrementInventory($productId, $variation, $qty);
        
        
        if($hasDigital == false) {
          $hasDigital = $product->isDigital();
        }

        // PayPal in not consistent in the way it passes back the item amounts
        $amt = 0;
        if(isset($pp['mc_gross' . $i])) {
          $amt = $pp['mc_gross' . $i];
        }
        elseif(isset($pp['mc_gross_' . $i])) {
          $amt = $pp['mc_gross_' . $i]/$pp['quantity' . $i];
        }

        $duid = md5($pp['txn_id'] . '-' . $orderId . '-' . $productId);
        $data = array(
          'order_id' => $orderId,
          'product_id' => $productId,
          'item_number' => $pp['item_number' . $i],
          'product_price' => $amt,
          'description' => $pp['item_name' . $i],
          'quantity' => $pp['quantity' . $i],
          'duid' => $duid
        );
        $wpdb->insert($orderItemsTable, $data);
      }
      
      // Handle email receipts
      $order = new PHPurchaseOrder($orderId);
      $msg = PHPurchaseCommon::getEmailReceiptMessage($order);

      // Send email receipts
      $setting = new PHPurchaseSetting();
      $to = $pp['payer_email'];
      $subject = $setting->lookupValue('receipt_subject');
      $headers = 'From: '. $setting->lookupValue('receipt_from_name') .' <' . $setting->lookupValue('receipt_from_address') . '>';
      PHPurchaseCommon::mail($to, $subject, $msg, $headers);

      $others = $setting->lookupValue('receipt_copy');
      if($others) {
        $list = explode(',', $others);
        $msg = "THIS IS A COPY OF THE RECEIPT\n\n$msg";
        foreach($list as $e) {
          $e = trim($e);
          $isSent = PHPurchaseCommon::mail($e, $subject, $msg, $headers);
          if(!$isSent) {
            PHPurchaseCommon::log("Mail not sent to: $e");
          }
        }
      }

      // Process affiliate reward if necessary
      if($referrer) {
        PHPurchaseCommon::awardCommission($orderId, $referrer);
      }
      
    } // end transaction id check
    
    
  }
  
}