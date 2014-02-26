<?php
class PHPurchaseCommon {

  /**
   * Return the string to use as the input id while keeping track of 
   * how many times a product is rendered to make sure there are no 
   * conflicting input ids.
   *
   * @param int $id - The databse id for the product
   * @return string
   */
  public static function getButtonId($id) {
    global $purCartButtons;

    $idSuffix = '';

    if(!is_array($purCartButtons)) {
      $purCartButtons = array();
    }

    if(in_array($id, array_keys($purCartButtons))) {
      $purCartButtons[$id] += 1;
    }
    else {
      $purCartButtons[$id] = 1;
    }

    if($purCartButtons[$id] > 1) {
      $idSuffix = '_' . $purCartButtons[$id];
    }

    $id .= $idSuffix;

    return $id;
  }
 
  /**
   * Strip all non numeric characters, then format the phone number.
   * 
   * Phone numbers are formatted as follows:
   *  7 digit phone numbers: 266-1789
   *  10 digit phone numbers: (804) 266-1789
   * 
   * @return string
   */
  public static function formatPhone($phone) {
  	$phone = preg_replace("/[^0-9]/", "", $phone);
  	if(strlen($phone) == 7)
  		return preg_replace("/([0-9]{3})([0-9]{4})/", "$1-$2", $phone);
  	elseif(strlen($phone) == 10)
  		return preg_replace("/([0-9]{3})([0-9]{3})([0-9]{4})/", "($1) $2-$3", $phone);
  	else
  		return $phone;
  }

  public function isRegistered() {
    $setting = new PHPurchaseSetting();
    $orderNumber = $setting->lookupValue('order_number');
    $isRegistered = ($orderNumber !== false) ? true : false;
    return $isRegistered;
  }
  
  public static function activePromotions() {
    $active = false;
    $promo = new PHPurchasePromotion();
    $promos = $promo->getModels();
    if(count($promos)) {
      $active = true;
    }
    return $active;
  }
  
  public static function showValue($value) {
    echo isset($value)? $value : '';
  }
  
  public static function getView($filename, $data=null) {

    $unregistered = '';
    if(strpos($filename, 'admin') !== false) {
      if(!self::isRegistered()) {
        $settingsUrl = get_bloginfo('wpurl') . '/wp-admin/admin.php?page=phpurchase-settings';
        $unregistered = '
          <div class="unregistered">
            This is not a registered copy of PHPurchase.<br/>
            Please <a href="' . $settingsUrl . '">enter your order number</a> or
            <a href="http://www.phpurchase.com/pricing">buy a license for your site.</a>
          </div>
        ';
      }
    }

    $filename = WP_PLUGIN_DIR . "/phpurchase/$filename"; 
    ob_start();
    include $filename;
    $contents = ob_get_contents();
    ob_end_clean();

    return $unregistered . $contents;
  }
  
  public static function getTableName($name, $prefix='pur_'){
      global $wpdb;
      return $wpdb->prefix . $prefix . $name;
  }
  
  public static function getTablePrefix(){
      global $wpdb;
      return $wpdb->prefix . "pur_";
  }
  
  /**
   * If PHPURCHASE_DEBUG is defined as true and a log file exists in the root of the PHPurchase plugin directory, log the $data
   */
	public static function log($data, $customLog = FALSE, $sendEmail = FALSE) {
		//if (is_user_logged_in() ) return;
    $date = date('m/d/Y g:i:s a');
    $header = "\n[$date] - ";
    if(PHPURCHASE_DEBUG) {
      $filename = WP_PLUGIN_DIR . "/phpurchase/log.txt";
	  $filenameX = WP_PLUGIN_DIR . "/phpurchase/log-new.txt";
	  $fd = fopen($filenameX, "a");
	  fwrite($fd, $header . $data);
	  file_put_contents($filename, $header . $data, FILE_APPEND);
      if(file_exists($filename) && is_writable($filename)) {
        
      }
    }
	if($customLog) {
		$filename = LOG_BASE . "user-log.txt"; 
		if(file_exists($filename) && is_writable($filename)) {
			file_put_contents($filename, $header . $data, FILE_APPEND);
		}
		
		if($sendEmail) {
			wp_mail('scotschroeder@gmail.com', 'Log Message for bbcharcoal.com', $data);
		}
	}
  }

  public static function getRandNum($num_chars = 7) {
    $id = '';
		mt_srand((double)microtime()*1000000);
		for ($i = 0; $i < $num_chars; $i++) { 
			$id .= chr(mt_rand(ord(0), ord(9)));
		}
		return $id;
	}
  
  public static function camel2human($val) {
    $val = strtolower(preg_replace('/([A-Z])/', ' $1', $val));
    return $val;
  }
  

  public static function checkUpdate(){
    if(IS_ADMIN) {
      $pluginName = "phpurchase/phpurchase.php";
      $option = function_exists('get_transient') ? get_transient("update_plugins") : get_option("update_plugins");
      $option = self::getUpdatePluginsOption($option);
      
      if(function_exists('set_transient')) {
        self::log('Setting Transient Value: ' . print_r($option->response[$pluginName], true));
        set_transient("update_plugins", $option);
      }
    }
  }
  
  public static function getUpdatePluginsOption($option) {
    $pluginName = "phpurchase/phpurchase.php";
    $versionInfo = PHPurchaseCommon::getVersionInfo();
    if(is_array($versionInfo)) {

      $phpurchaseOption = $option->response[$pluginName];
      if(empty($phpurchaseOption)) {
        $option->response[$pluginName] = new stdClass();
      }

      $setting = new PHPurchaseSetting();
      $orderNumber = $setting->lookupValue('order_number');
      $currentVersion = $setting->lookupValue('version');
      if(version_compare($currentVersion, $versionInfo['version'], '<')) {
        $newVersion = $versionInfo['version'];
        self::log("New Version Available: $currentVersion < $newVersion");
        $option->response[$pluginName]->url = "http://www.cart66.com";
        $option->response[$pluginName]->slug = "phpurchase";
        $option->response[$pluginName]->package = str_replace("{KEY}", $orderNumber, $versionInfo["url"]);
        $option->response[$pluginName]->new_version = $versionInfo["version"];
        $option->response[$pluginName]->id = "0";
      }
      else {
        unset($option->response[$pluginName]);
      }
    }
    return $option;
  }
  
  public static function getVersionInfo() {
    $versionInfo = false;
    $setting = new PHPurchaseSetting();
    $orderNumber = $setting->lookupValue('order_number');
    if($orderNumber) {
      $body = 'key=$orderNumber';
      $options = array('method' => 'POST', 'timeout' => 3, 'body' => $body);
      $options['headers'] = array(
          'Content-Type' => 'application/x-www-form-urlencoded; charset=' . get_option('blog_charset'),
          'Content-Length' => strlen($body),
          'User-Agent' => 'WordPress/' . get_bloginfo("version"),
          'Referer' => get_bloginfo("url")
      );
      $callBackLink = CALLBACK_URL . "/phpurchase-version.php?" . self::getRemoteRequestParams();
      self::log("Callback link: $callBackLink");
      $raw = wp_remote_request($callBackLink, $options);
      if (!is_wp_error($raw) && 200 == $raw['response']['code']) {
        $info = explode("~", $raw['body']);
        $versionInfo = array("isValidKey" => $info[0], "version" => $info[1], "url" => $info[2]);
      }
    }
    return $versionInfo;      
  }
  
  public static function getRemoteRequestParams() {
    $params = false;
    $setting = new PHPurchaseSetting();
    $orderNumber = $setting->lookupValue('order_number');
    if(!$orderNumber) {
      self::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Order number not available");
    }
    $version = $setting->lookupValue('version');
    if(!$version) {
      self::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Version number not available");
    }
    if($orderNumber && $version) {
      global $wpdb;
      $versionName = PHPURCHASEPRO ? 'pro' : 'standard';
      $params = sprintf("task=getLatestVersion&pn=PHPurchase&key=%s&v=%s&vnm=%s&wp=%s&php=%s&mysql=%s&ws=%s", 
        urlencode($orderNumber), 
        urlencode($version), 
        urlencode($versionName),
        urlencode(get_bloginfo("version")), 
        urlencode(phpversion()), 
        urlencode($wpdb->db_version()),
        urlencode(get_bloginfo("url"))
      );
    }
    return $params;
  }
  
  public static function showChangelog() {
    if($_REQUEST["plugin"] == "phpurchase") {
      $setting = new PHPurchaseSetting();
      $orderNumber = $setting->lookupValue('order_number');

      if($orderNumber) {
        $raw = file_get_contents('http://www.cart66.com/latest-phpurchase');
        $raw = str_replace("\n", '', $raw);
        $matches = array();
        preg_match('/<div class="entry">(.+?)<\/div>/m', $raw, $matches);
        $raw = "<h1>PHPurchase</h1>$matches[1]";
        echo $raw;
      }
      
      exit;
    }
  }
  
  public static function awardCommission($orderId, $referrer) {
    global $wpdb;
    if (!empty($referrer)) {
      $order = new PHPurchaseOrder($orderId);
      if($order->id > 0) {
        $subtractAmount = 0;
        $discount = $order->discountAmount;
        foreach($order->getItems() as $item) {
          $price = $item->product_price * $item->quantity;

          if($price > $discount) {
            $subtractAmount = $discount;
            $discount = 0;
          }
          else {
            $subtractAmount = $price;
            $discount = $discount - $price;
          }

          if($subtractAmount > 0) {
            $price = $price - $subtractAmount;
          }
          
          // Transaction if for commission is the id in th order items table
          $txn_id = $item->id;
          $sale_amount = $price;
          $item_id = $item->item_number;
          $buyer_email = $order->email;

          // Make sure commission has not already been granted for this transaction
          $aff_sales_table = $wpdb->prefix . "affiliates_sales_tbl";
          $txnCount = $wpdb->get_var($wpdb->prepare("SELECT count(*) FROM $aff_sales_table where txn_id = %s", $txn_id));
          if($txnCount < 1) {
            wp_aff_award_commission($referrer,$sale_amount,$txn_id,$item_id,$buyer_email);
          }
        }
        
      }
    }
  }
  
  /**
   * Return true if the email address is not empty and has a valid format
   * 
   * @param string $email The email address to validate
   * @return boolean Empty or invalid email addresses return false, otherwise true
   */
  public static function isValidEmail($email) {
    $pattern = '/\b[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,4}\b/';
    $isValid = false;
    if(!empty($email)) {
      if(preg_match($pattern, $email)) {
        $isValid = true;
      }
    }
    return $isValid;
  }
  
  public static function isEmailUnique($email, $exceptId=0) {
    global $wpdb;
    $accounts = self::getTableName('accounts');
    $sql = "SELECT count(*) as c from $accounts where email = %s and id != %d";
    $sql = $wpdb->prepare($sql, $email, $exceptId);
    $count = $wpdb->get_var($sql);
    $isUnique = $count == 0;
    return $isUnique;
  }
  
  
  /**
   * Configure mail for use with either standard wp_mail or when using the WP Mail SMTP plugin
   */
  public static function mail($to, $subject, $msg, $headers=null) {
    //Disable mail headers if the WP Mail SMTP plugin is in use.
    if(function_exists('wp_mail_smtp_activate')) { $headers = null; }
    return wp_mail($to, $subject, $msg, $headers);
  }
  
  public static function randomString($numChars = 7) {
		$letters = "";
		mt_srand((double)microtime()*1000000);
		for ($i = 0; $i < $numChars; $i++) { 
			$randval = chr(mt_rand(ord("a"), ord("z")));
			$letters .= $randval;
		}
		return $letters;
	}
	
	public static function isValidDate($val) {
	  $isValid = false;
		if(preg_match("/\d{1,2}\/\d{1,2}\/\d{4}/", $val)) {
			list($month, $day, $year) = split("/", $val);
			if(is_numeric($month) && is_numeric($day) && is_numeric($year) ) {
				if($month > 12 || $month < 1) {
					$isValid = false;
				}
				elseif($day > 31 || $day < 1) {
					$isValid = false;
				}
				elseif($year < 1900) {
					$isValid = false;
				}
				else {
					$isValid = true;
				}
			}
		}
		return $isValid;
	}

  public static function postVal($key) {
    $value = $_POST[$key];
    if(is_scalar($value)) {
      $value = strip_tags($value);
      $value = preg_replace('/[<>\\\\\/]/', '', $value);
    }
    return $value;
  }

  public static function getVal($key) {
    $value = strip_tags($_GET[$key]);
    $value = preg_replace('/[<>\\\\\/]/', '', $value);
    return $value;
  }
  
  public static function getCountryName($code) {
    $countries = self::getCountries(true);
    return $countries[$code];
  }

  public static function getCountries($all=false) {
    $countries = array(
       'AR'=>'Argentina',
       'AU'=>'Australia',
       'AT'=>'Austria',
       'BS'=>'Bahamas',
       'BE'=>'Belgium',
       'BR'=>'Brazil',
       'BG'=>'Bulgaria',
       'CA'=>'Canada',
       'CL'=>'Chile',
       'CN'=>'China',
       'CO'=>'Colombia',
       'CR'=>'Costa Rica',
       'HR'=>'Croatia',
       'CY'=>'Cyprus',
       'CZ'=>'Czech Republic',
       'DK'=>'Denmark',
       'EC'=>'Ecuador',
       'EE'=>'Estonia',
       'FI'=>'Finland',
       'FR'=>'France',
       'DE'=>'Germany',
       'GR'=>'Greece',
       'GP'=>'Guadeloupe',
       'HK'=>'Hong Kong',
       'HU'=>'Hungary',
       'IS'=>'Iceland',
       'IN'=>'India',
       'ID'=>'Indonesia',
       'IE'=>'Ireland',
       'IL'=>'Israel',
       'IT'=>'Italy',
       'JM'=>'Jamaica',
       'JP'=>'Japan',
       'LV'=>'Latvia',
       'LT'=>'Lithuania',
       'LU'=>'Luxembourg',
       'MY'=>'Malaysia',
       'MT'=>'Malta',
       'MX'=>'Mexico',
       'NL'=>'Netherlands',
       'NZ'=>'New Zealand',
       'NO'=>'Norway',
       'PE'=>'Peru',
       'PH'=>'Philippines',
       'PL'=>'Poland',
       'PT'=>'Portugal',
       'PR'=>'Puerto Rico',
       'RO'=>'Romania',
       'RU'=>'Russia',
       'SG'=>'Singapore',
       'SK'=>'Slovakia',
       'SI'=>'Slovenia',
       'ZA'=>'South Africa',
       'KR'=>'South Korea',
       'ES'=>'Spain',
       'VC'=>'St. Vincent',
       'SE'=>'Sweden',
       'CH'=>'Switzerland',
       'SY'=>'Syria',
       'TW'=>'Taiwan',
       'TH'=>'Thailand',
       'TT'=>'Trinidad and Tobago',
       'TR'=>'Turkey',
       'AE'=>'United Arab Emirates',
       'GB'=>'United Kingdom',
       'US'=>'United States',
       'UY'=>'Uruguay',
       'VE'=>'Venezuela');
    
    // Put home country at the top of the list
    $setting = new PHPurchaseSetting();
    $home_country = $setting->lookupValue('home_country');
    if($home_country) {
      list($code, $name) = explode('~', $home_country);
      $countries = array_merge(array($code => $name), $countries);
    }
    else {
      $countries = array_merge(array('US' => 'United States'), $countries);
    }

    $customCountries = self::getCustomCountries();
    
    if($all) {
      if(is_array($customCountries)) {
        foreach($customCountries as $code => $name) {
          unset($countries[$code]);
        }
        foreach($countries as $code => $name) {
          $customCountries[$code] = $name;
        }
        $countries = $customCountries;
      }
    }
    else {
      $international = $setting->lookupValue('international_sales');
      if($international) {
        if($customCountries) {
          //PHPurchaseCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Got some custom countries: " . print_r($customCountries, true));
          $countries = $customCountries;
        }
      }
      else {
        $countries = array_slice($countries, 0, 1, true); 
      }
    }
    
    
    
    return $countries;
  }

  public static function getCustomCountries() {
    $list = false;
    $setting = new PHPurchaseSetting();
    $countries = $setting->lookupValue('countries');
    if($countries) {
      $countries = explode(',', $countries);
      foreach($countries as $c) {
        list($code, $name) = explode('~', $c);
        $list[$code] = $name;
      }
    }
    return $list;
  }
  
  public static function getPayPalCurrencyCodes() {
    $currencies = array(
      'United States Dollar' => 'USD',
      'Australian Dollar' => 'AUD',
      'Canadian Dollar' => 'CAD',
      'Czech Koruna' => 'CZK',
      'Danish Krone' => 'DKK',
      'Euro' => 'EUR',
      'Hong Kong Dollar' => 'HKD',
      'Hungarian Forint' => 'HUF',
      'Israeli New Sheqel' => 'ILS',
      'Japanese Yen' => 'JPY',
      'Mexican Peso' => 'MXN',
      'Norwegian Krone' => 'NOK',
      'New Zealand Dollar' => 'NZD',
      'Philippine Peso' => 'PHP',
      'Polish Zloty' => 'PLN',
      'Pound Sterling' => 'GBP',
      'Singapore Dollar' => 'SGD',
      'Swedish Krona' => 'SEK',
      'Swiss Franc' => 'CHF',
      'Taiwan New Dollar' => 'TWD',
      'Thai Baht' => 'THB'
    );
    return $currencies;
  }

  
  public static function getZones($code) {
    $zones = array('0' => '&nbsp;');
    switch ($code) {
      case 'AU':
        $zones['NSW'] = 'New South Wales';
        $zones['NT'] = 'Northern Territory';
        $zones['QLD'] = 'Queensland';
        $zones['SA'] = 'South Australia';
        $zones['TAS'] = 'Tasmania';
        $zones['VIC'] = 'Victoria';
        $zones['WA'] = 'Western Australia';
        break;
      case 'CA':
        $zones['AB'] = 'Alberta';
        $zones['BC'] = 'British Columbia';
        $zones['MB'] = 'Manitoba';
        $zones['NB'] = 'New Brunswick';
        $zones['NF'] = 'Newfoundland';
        $zones['NT'] = 'Northwest Territories';
        $zones['NS'] = 'Nova Scotia';
        $zones['NU'] = 'Nunavut';
        $zones['ON'] = 'Ontario';
        $zones['PE'] = 'Prince Edward Island';
        $zones['PQ'] = 'Quebec';
        $zones['SK'] = 'Saskatchewan';
        $zones['YT'] = 'Yukon Territory';
        break;
      case 'US':
        $zones['AL'] = 'Alabama';
        $zones['AK'] = 'Alaska ';
        $zones['AZ'] = 'Arizona';
        $zones['AR'] = 'Arkansas';
        $zones['CA'] = 'California ';
        $zones['CO'] = 'Colorado';
        $zones['CT'] = 'Connecticut';
        $zones['DE'] = 'Delaware';
        $zones['DC'] = 'D. C.';
        $zones['FL'] = 'Florida';
        $zones['GA'] = 'Georgia ';
        $zones['HI'] = 'Hawaii';
        $zones['ID'] = 'Idaho';
        $zones['IL'] = 'Illinois';
        $zones['IN'] = 'Indiana';
        $zones['IA'] = 'Iowa';
        $zones['KS'] = 'Kansas';
        $zones['KY'] = 'Kentucky';
        $zones['LA'] = 'Louisiana';
        $zones['ME'] = 'Maine';
        $zones['MD'] = 'Maryland';
        $zones['MA'] = 'Massachusetts';
        $zones['MI'] = 'Michigan';
        $zones['MN'] = 'Minnesota';
        $zones['MS'] = 'Mississippi';
        $zones['MO'] = 'Missouri';
        $zones['MT'] = 'Montana';
        $zones['NE'] = 'Nebraska';
        $zones['NV'] = 'Nevada';
        $zones['NH'] = 'New Hampshire';
        $zones['NJ'] = 'New Jersey';
        $zones['NM'] = 'New Mexico';
        $zones['NY'] = 'New York';
        $zones['NC'] = 'North Carolina';
        $zones['ND'] = 'North Dakota';
        $zones['OH'] = 'Ohio';
        $zones['OK'] = 'Oklahoma';
        $zones['OR'] = 'Oregon';
        $zones['PA'] = 'Pennsylvania';
        $zones['RI'] = 'Rhode Island';
        $zones['SC'] = 'South Carolina';
        $zones['SD'] = 'South Dakota';
        $zones['TN'] = 'Tennessee';
        $zones['TX'] = 'Texas';
        $zones['UT'] = 'Utah';
        $zones['VT'] = 'Vermont';
        $zones['VA'] = 'Virginia';
        $zones['WA'] = 'Washington';
        $zones['WV'] = 'West Virginia';
        $zones['WI'] = 'Wisconsin';
        $zones['WY'] = 'Wyoming';
        $zones['AE'] = 'Armed Forces';
        break;
    }
    return $zones;
  }
  
  public static function getUpsServices() {
    
    $usaServices = array(
      'UPS Next Day Air' => '01',
      'UPS Second Day Air' => '02',
      'UPS Ground' => '03',
      'UPS Worldwide Express' => '07',
      'UPS Worldwide Expedited' => '08',
      'UPS Standard' => '11',
      'UPS Three-Day Select' => '12',
      'UPS Next Day Air Early A.M.' => '14',
      'UPS Worldwide Express Plus' => '54',
      'UPS Second Day Air A.M.' => '59',
      'UPS Saver' => '65'
    );
    
    $internationalServices = array(
      'UPS Express' =>	'01',
      'UPS Expedited' =>	'02',
      'UPS Worldwide Express' =>	'07',
      'UPS Worldwide Expedited' =>	'08',
      'UPS Standard' =>	'11',
      'UPS Three-Day Select' =>	'12',
      'UPS Saver' =>	'13',
      'UPS Express Early A.M.' =>	'14',
      'UPS Worldwide Express Plus' =>	'54',
      'UPS Saver' =>	'65'
    );
    
    $homeCountryCode = 'US';
    $setting = new PHPurchaseSetting();
    $home = $setting->lookupValue('home_country');
    if($home) {
      list($homeCountryCode, $name) = explode('~', $home);
    }
    
    $services = $homeCountryCode == 'US' ? $usaServices : $internationalServices;
    
    return $services;
  }

  /**
   * Return a link to the "view cart" page
   */
  public static function getPageLink($path) {
    $page = get_page_by_path($path);
    $link = get_permalink($page->ID);
    if($_SERVER['SERVER_PORT'] == '443') {
      $link = str_replace('http://', 'https://', $link);
    }
    return $link;
  }

  /**
   * Make sure path ends in a trailing slash by looking for trailing slash and add if necessary
   */
  public static function scrubPath($path) {
    if(stripos(strrev($path), '/') !== 0) {
      $path .= '/';
    }
    return $path;
  }

  /**
   * Return an array of order status options
   * If no options have been set by the user, 'new' is the only returned option
   */
  public static function getOrderStatusOptions() {
    $statuses = array();
    $setting = new PHPurchaseSetting();
    $opts = $setting->lookupValue('status_options');
    if(!empty($opts)) {
      $opts = explode(',', $opts);
      foreach($opts as $o) {
        $statuses[] = trim($o);
      }
    }
    if(count($statuses) == 0) {
      $statuses[] = 'new';
    }
    return $statuses;
  }

  public function getPromoMessage() {
    $promo = $_SESSION['PHPurchaseCart']->getPromotion();
    $promoMsg = "none";
    if($promo) {
      $promoMsg = $promo->code . ' (-' . CURRENCY_SYMBOL . number_format($_SESSION['PHPurchaseCart']->getDiscountAmount(), 2) . ')';
    }
    return $promoMsg;
  }

  public function showErrors($errors, $message=null) {
    $out = "<div id='phpurchaseErrors' class='PHPurchaseError'>";
    if(empty($message)) {
      $out .= "<p><b>We're sorry.<br/>Your order could not be completed for the following reasons:</b></p>";
    }
    else {
      $out .= $message;
    }
    $out .= '<ul>';
    if(is_array($errors)) {
      foreach($errors as $key => $value) {
        $value = strtolower($value);
        $out .= "<li>$value</li>";
      }
    }
    else {
      $out .= "<li>$errors</li>";
    }
    $out .= "</ul></div>";
    return $out;
  }

  public function getCardTypes() {
    $cardTypes = array();
    $setting = new PHPurchaseSetting();
    $cards = $setting->lookupValue('auth_card_types');
    if($cards) {
      $cards = explode('~', $cards);
      if(in_array('mastercard', $cards)) {
        $cardTypes['MasterCard'] = 'mastercard';
      }
      if(in_array('visa', $cards)) {
        $cardTypes['Visa'] = 'visa';
      }
      if(in_array('amex', $cards)) {
        $cardTypes['American Express'] = 'amex';
      }
      if(in_array('discover', $cards)) {
        $cardTypes['Discover'] = 'discover';
      }
    }
    return $cardTypes;
  }


  /**
   * Return the name of the configured gateway or false if neither authnet or quantum are configured.
   * This function doesn't care about paypal.
   * 
   * @return string (authnet, quauntum)
   */
  public function gatewayName() {
    $gateway = false;
    $setting = new PHPurchaseSetting();
    $authUrl = $setting->lookupValue('auth_url');
    if(strpos($authUrl, 'authorize.net') > 0) {
      $gateway = 'authnet';
    }
    else {
      $gateway = 'quantum';
    }
    return $gateway;
  }
  
  public static function getEmailReceiptMessage($order, $UPSLabelLink = FALSE) {
    $product = new PHPurchaseProduct();
    
	$shipmentDetails = unserialize($order->shipping_results);
	$msgAdmin = '';
	
    $msg = "ORDER NUMBER: " . $order->trans_id . "\n\n";
    $hasDigital = false;
    foreach($order->getItems() as $item) {
      $product->load($item->product_id);
      if($hasDigital == false) {
        $hasDigital = $product->isDigital();
      }
      $price = $item->product_price * $item->quantity;
      // $msg .= "Item: " . $item->item_number . ' ' . $item->description . "\n";
      $msg .= "Item: " . $item->description . "\n";
      if($item->quantity > 1) {
        $msg .= "Quantity: " . $item->quantity . "\n";
      }
      $msg .= "Item Price: " . CURRENCY_SYMBOL_TEXT . number_format($item->product_price, 2) . "\n";
      $msg .= "Item Total: " . CURRENCY_SYMBOL_TEXT . number_format($item->product_price * $item->quantity, 2) . "\n\n";
      
      if($product->isGravityProduct()) {
        $msg .= displayGravityForm($item->form_entry_ids, true);
      }
    }

    if($order->shipping_method != 'None' && $order->shipping_method != 'Download') {
      $msg .= "Shipping: " . CURRENCY_SYMBOL_TEXT . $order->shipping . "\n";
    }

    if(!empty($order->coupon) && $order->coupon != 'none') {
      $msg .= "Coupon: " . $order->coupon . "\n";
    }

    if($order->tax > 0) {
      $msg .= "Tax: " . CURRENCY_SYMBOL_TEXT . number_format($order->tax, 2) . "\n";
    }

    $msg .= "\nTOTAL: " . CURRENCY_SYMBOL_TEXT . number_format($order->total, 2) . "\n";

    if($order->shipping_method != 'None' && $order->shipping_method != 'Download') {
      $msg .= "\n\nSHIPPING INFORMATION\n--------------------\n";

      $msg .= $order->ship_first_name . ' ' . $order->ship_last_name . "\n";
      $msg .= $order->ship_address . "\n";
      if(!empty($order->ship_address2)) {
        $msg .= $order->ship_address2 . "\n";
      }
      $msg .= $order->ship_city . ' ' . $order->ship_state . ' ' . $order->ship_zip . "\n" . $order->ship_country . "";

	  if($UPSLabelLink) {
		  $msgAdmin .= "UPS SHIPPING INFORMATION (Not Part of the Original Receipt)\n--------------------\n";
		  $msgAdmin .= "Transportation Charges: $".$shipmentDetails["ShipmentCharges"]["TransportationCharges"]["MonetaryValue"]["VALUE"]."\n";
		  $msgAdmin .= "Service Option Charges: $".$shipmentDetails["ShipmentCharges"]["ServiceOptionsCharges"]["MonetaryValue"]["VALUE"]."\n";
		  $msgAdmin .= "Total Charges: $".$shipmentDetails["ShipmentCharges"]["TotalCharges"]["MonetaryValue"]["VALUE"]."\n";
		  $msgAdmin .= "Billing Weight: ".$shipmentDetails["BillingWeight"]["Weight"]["VALUE"]." ".$shipmentDetails["BillingWeight"]["UnitOfMeasurement"]["Code"]["VALUE"]."\n";
		  $msgAdmin .= "Shipping Identification Number: ".$shipmentDetails["ShipmentIdentificationNumber"]["VALUE"]."\n";
		  $msgAdmin .= "Pickup Request Number: ".$shipmentDetails["PickupRequestNumber"]["VALUE"]."\n";
		  $msgAdmin .= "Delivery via: " . $order->shipping_method . "\n\n";
		  
		  if(!empty($shipmentDetails["PackageResults"][0])) {
			$count = 1;
			foreach($shipmentDetails["PackageResults"] as $package) {
				$msgAdmin .= "--------------------\nPACKAGE ".$count." DETAILS:";
				$msgAdmin .= "Tracking Number: ".$package["TrackingNumber"]["VALUE"]."\n";
				$msgAdmin .= "Service Charges: ".$package["ServiceOptionsCharges"]["MonetaryValue"]["VALUE"]." ".$package["ServiceOptionsCharges"]["CurrencyCode"]["VALUE"]."\n";
				$msgAdmin .= "Package Label: ".URL_BASE.'ups-labels/printLabel.php?orderId='.$order->id."&showlabel=".$count."\n";
				$count++;
			}
		  } else {
			$msgAdmin .= "PACKAGE DETAILS\n--------------------\n";
			$msgAdmin .= "Tracking Number: ".$shipmentDetails["PackageResults"]["TrackingNumber"]["VALUE"]."\n";
			$msgAdmin .= "Service Charges: ".$shipmentDetails["PackageResults"]["ServiceOptionsCharges"]["MonetaryValue"]["VALUE"]." ".$shipmentDetails["PackageResults"]["ServiceOptionsCharges"]["CurrencyCode"]["VALUE"]."\n";
			$msgAdmin .= "Package Label: ".URL_BASE.'ups-labels/printLabel.php?orderId='.$order->id."\n";
		  }
		  
		  if(!empty($shipmentDetails['ControlLogReceipt']['GraphicImage']['VALUE'])) {
			$msgAdmin .= "\nControl Log: (Must be printed twice, one copy for the driver and one for your record)\n".URL_BASE.'ups-labels/printLabel.php?orderId='.$order->id."&showcontrollog=1\n";
		  }
	  }
    }


    $msg .= "\n\nBILLING INFORMATION\n--------------------\n";

    $msg .= $order->bill_first_name . ' ' . $order->bill_last_name . "\n";
    $msg .= $order->bill_address . "\n";
    if(!empty($order->bill_address2)) {
      $msg .= $order->bill_address2 . "\n";
    }
    $msg .= $order->bill_city . ' ' . $order->bill_state . ' ' . $order->bill_zip . "\n" . $order->bill_country . "\n";

    if(!empty($order->phone)) {
      $phone = self::formatPhone($order->phone);
      $msg .= "\nPhone: $phone\n";
    }
    
    if(!empty($order->email)) {
      $msg .= 'Email: ' . $order->email . "\n";
    }

    $receiptPage = get_page_by_path('store/receipt');
    $link = get_permalink($receiptPage->ID);
    if(strstr($link,"?")){
      $link .= '&ouid=' . $order->ouid;
    }
    else{
      $link .= '?ouid=' . $order->ouid;
    }

    if($hasDigital) {
      $msg .= "\nDOWNLOAD LINK\nClick the link below to download your order.\n$link";
    }
    else {
      $msg .= "\nVIEW RECEIPT ONLINE\nClick the link below to view your receipt online.\n$link";
    }
    
    $setting = new PHPurchaseSetting();
    $msgIntro = $setting->lookupValue('receipt_intro');
    $msg = $msgIntro . " \n----------------------------------\n\n" . $msg;
    
	if($UPSLabelLink) {
		return array($msg, $msgAdmin);
	} else {
		return array($msg, "");
	}
  }
  
  /**
   * Return true if at least one product in the system is a subscriptiion product
   */
  public static function isSellingSubscriptions() {
    global $wpdb;
    $isSellingSubscriptions = false;
    $products = self::getTableName('products');
    $sql = "SELECT count(*) as num from $products where recurring_interval > 0";
    $num = $wpdb->get_var($sql);
    if($num > 0) {
      $isSellingSubscriptions = true;
    }
    return $isSellingSubscriptions;
  }
  
  /**
   * Return the WP_CONTENT_URL taking into account HTTPS and the possibility that WP_CONTENT_URL may not be defined
   * 
   * @return string
   */
  public static function getWpContentUrl() {
    $wpurl = WP_CONTENT_URL;
    if(empty($wpurl)) {
      $wpurl = get_bloginfo('wpurl') . '/wp-content';
    }
    if(!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') {
      $wpurl = str_replace('http://', 'https://', $wpurl);
    }
    return $wpurl;
  }
  
  /**
   * Return the WordPress URL taking into account HTTPS
   */
  public static function getWpUrl() {
    $wpurl = get_bloginfo('wpurl');
    if(!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') {
      $wpurl = str_replace('http://', 'https://', $wpurl);
    }
    return $wpurl;
  }
  
  
}
