<?php
class PHPurchaseProduct extends PHPurchaseModelAbstract {
  
  public function __construct($id=null) {
    $this->_tableName = PHPurchaseCommon::getTableName('products');
    parent::__construct($id);
  }
  
  public function getOptions() {
    $opt1 = $this->_buildOptionList(1);
    $opt2 = $this->_buildOptionList(2);
    return $opt1 . $opt2;
  }
  
  public function loadByDuid($duid) {
    $itemsTable = PHPurchaseCommon::getTableName('order_items');
    $sql = "SELECT product_id from $itemsTable where duid = '$duid'";
    $id = $this->_db->get_var($sql);
    $this->load($id);
    return $this->id;
  }
  
  public function loadByItemNumber($itemNumber) {
    $itemNumber = $this->_db->escape($itemNumber);
    $sql = "SELECT id from $this->_tableName where item_number = '$itemNumber'";
    $id = $this->_db->get_var($sql);
    $this->load($id);
    return $this->id;
  }

  public function loadFromShortcode($attrs) {
    if(is_array($attrs)) {
      if(isset($attrs['item'])) {
        $this->loadByItemNumber($attrs['item']);
      }
      else {
        $id = $attrs['id'];
        $this->load($id);
      }
    }
    return $this->id;
  }

  public function countDownloadsForDuid($duid) {
    $downloadsTable = PHPurchaseCommon::getTableName('downloads');
    $sql = "SELECT count(*) from $downloadsTable where duid='$duid'";
    return $this->_db->get_var($sql);
  }
  
  /**
   * Return the quantity of inventory in stock for the product with the given id and variation description.
   * 
   * The variation descriptins is a ~ separated string of options. The price info may be in the variation string but
   * will be stripped out before calculating the iKey.
   * 
   * @param int $id
   * @param string $variation
   * @return int Quantity of inventory in stock
   */
  public static function checkInventoryLevelForProduct($id, $variation='') {
    // Build varation ikey string component
    if(!empty($variation)) {
      $variation = self::scrubVaritationsForIkey($variation);
    }
    
    $p = new PHPurchaseProduct($id);
    $ikey = $p->getInventoryKey($variation);
    $count = $p->getInventoryCount($ikey);
    //PHPurchaseCommon::log("Check Inventory Level For Product: $ikey = $count");
    return $count;
  }
  
  public static function decrementInventory($id, $variation='', $qty=1) {
    PHPurchaseCommon::log("Decrementing Inventory: line " . __LINE__);
    // Build varation ikey string component
    if(!empty($variation)) {
      $variation = self::scrubVaritationsForIkey($variation);
    }
    
    $p = new PHPurchaseProduct($id);
    $ikey = $p->getInventoryKey($variation);
    $count = $p->getInventoryCount($ikey);
    $newCount = $count - $qty;
    if($newCount < 0) {
      $newCount = 0;
    }
    
    $p->setInventoryLevel($ikey, $newCount);
  }
  
  public static function scrubVaritationsForIkey($variation='') {
    if(!empty($variation)) {
      $variations = explode('~', $variation);
      $options = array();
      foreach($variations as $opt) {
        $options[] = trim(preg_replace('/\s*([+-])[^$]*\$.*$/', '', $opt));
      }
      $variation = strtolower(str_replace('~', ',', str_replace(' ', '', implode(',', $options))));
    }
    return $variation;
  }
  
  public static function confirmInventory($id, $variation='', $desiredQty=1) {
    PHPurchaseCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Confirming Inventory:\n$id | $variation | $desiredQty");
    $ok = true;
    $setting = new PHPurchaseSetting();
    $trackInventory = $setting->lookupValue('track_inventory');
    if($trackInventory == 1) {
      $p = new PHPurchaseProduct($id);
      $variation = self::scrubVaritationsForIkey($variation);
      $ikey = $p->getInventoryKey($variation);
      if($p->isInventoryTracked($ikey)) {
        $qty = self::checkInventoryLevelForProduct($id, $variation);
        if($qty < $desiredQty) {
          $ok = false;
        }
      }
      else {
        PHPurchaseCommon::log("Inventory not tracked: $ikey");
      }
    }
    return $ok;
  }
  
  /**
   * Return an array of option names having stripped off any price variations
   * 
   * @param int $optNumber The option group number
   * @return array
   */
  public function getOptionNames($optNumber=1) {
    $names = array();
    $optionName = "options_$optNumber";
    $opts = split(',', $this->$optionName);
    foreach($opts as $opt) {
      $name = $opt;
      if(strpos($opt, '$')) {
        $name = trim(preg_replace('/\s*([+-])[^$]*\$.*$/', '', $opt));
      }
      
      if(!empty($name)) {
        $names[] = $name;
      }
    }
    return $names;
  }
  
  public function getAllOptionCombinations() {
    $combos = array();
    $opt1 = $this->getOptionNames(1);
    $opt2 = $this->getOptionNames(2);
    if(count($opt1)) {
      foreach($opt1 as $first) {
        if(count($opt2)) {
          foreach($opt2 as $second) {
            $combos[] = "$first, $second";
          }
        }
        else {
          $combos[] = "$first";
        }
      }
    }
    return $combos;
  }
  
  /**
   * Return the primary key used in the ikey table. 
   * This is the product name + variation name without price difference information in all lowercase with no spaces.
   * Only letters and numbers are used.
   * 
   * @param string The variation name without the price difference
   * @return string
   */
  public function getInventoryKey($variationName='') {
    $key = strtolower($this->id . $this->name . $variationName);
    $key = str_replace(' ', '', $key);
    $key = preg_replace('/\W/', '', $key);
    return $key;
  }
  
  public function insertInventoryData() {
    $keys = array();
    $combos = $this->getAllOptionCombinations();
    if(count($combos)) {
      foreach($combos as $c) {
        $key = $this->getInventoryKey($c);
        $keys[] = $key;
      }
    }
    else {
      // There are no product variations
      $key = $this->getInventoryKey();
      $keys[] = $key;
    }
    
    foreach($keys as $key) {
      $inventory = PHPurchaseCommon::getTableName('inventory');
      
      // Only insert new rows
      $sql = "SELECT ikey from $inventory where ikey = %s";
      $stmt = $this->_db->prepare($sql, $key);
      $foundKey = $this->_db->get_var($stmt);
      if(!$foundKey) {
        $sql = "INSERT into $inventory (ikey, track, product_id, quantity) VALUES (%s,%d,%d,%d)";
        $stmt = $this->_db->prepare($sql, $key, 0, $this->id, 0);
        $this->_db->query($stmt);
      }
      
    }
    
    // Delete obsolete inventory rows
    $keyList = implode(',', $keys);
    $sql = "DELETE from $inventory where product_id=$this->id and ikey not in ($keyList)";
    $this->_db->query($sql);
  }
  
  public function updateInventoryFromPost($ikey) {
    $inventory = PHPurchaseCommon::getTableName('inventory');
    $track = PHPurchaseCommon::postVal("track_$ikey");
    $qty = PHPurchaseCommon::postVal("qty_$ikey");
    $sql = "UPDATE $inventory set track=%d, quantity=%d where ikey=%s";
    $sql = $this->_db->prepare($sql, $track, $qty, $ikey);
    $this->_db->query($sql);
  }
  
  public function setInventoryLevel($ikey, $qty) {
    $inventory = PHPurchaseCommon::getTableName('inventory');
    $sql = "UPDATE $inventory set quantity=%d where ikey=%s";
    $sql = $this->_db->prepare($sql, $qty, $ikey);
    $this->_db->query($sql);
  }
  
  public function getInventoryCount($ikey) {
    $inventory = PHPurchaseCommon::getTableName('inventory');
    $sql = "SELECT quantity from $inventory where ikey=%s";
    $sql = $this->_db->prepare($sql, $ikey);
    $count = $this->_db->get_var($sql);
    return $count;
  }
  
  public function getInventoryNamesAndCounts() {
    $counts = array();
    $ikeyList = $this->getInventoryKeyList();
    foreach($ikeyList as $comboName => $ikey) {
      if($this->isInventoryTracked($ikey)) {
        $counts[$comboName] = $this->getInventoryCount($ikey);
      }
      else {
        $counts[$comboName] = 'in stock';
      }
    }
    return $counts;
  }
  
  /**
   * Return an array of all inventory keys for this product
   */
  public function getInventoryKeyList() {
    $ikeyList = array();
    $combos = $this->getAllOptionCombinations();
    if(count($combos)) {
      foreach($combos as $c) {
        $k = $this->getInventoryKey($c);
        $n = $this->name . ': ' . $c;
        $ikeyList[$n] = $k;
      }
    }
    else {
      $ikeyList[$p->name] = $p->getInventoryKey();
    }
    
    return $ikeyList;
  }
  
  /**
   * Return true if this product is available in any variation for purchase.
   * 
   * If inventory is not tracked or if any variations of the product are in stock, true is returned.
   * Otherwise, false is returned.
   * 
   * @return boolean
   */
  public function isAvailable() {
    $isAvailable = false;
    $inventory = PHPurchaseCommon::getTableName('inventory');
    $sql = "SELECT count(*) from $inventory where product_id=$this->id";
    $found = $this->_db->get_var($sql);
    if($found) {
      $sql = "SELECT sum(quantity) from $inventory where track=1 and product_id=$this->id";
      $qty = $this->_db->get_var($sql);
      if(is_numeric($qty) && $qty > 0) {
        $isAvailable = true;
      }
      else {
        $sql = "SELECT count(*) as c from $inventory where track=0 and product_id=$this->id";
        $notTracked = $this->_db->get_var($sql);
        if($notTracked > 0) {
          $isAvailable = true;
        }
      }
    }
    else {
      // Inventory table hasn't been refreshed so ignore inventory tracking for this product
      $isAvailable = true;
    }
    return $isAvailable;
  }
  
  public function isInventoryTracked($ikey) {
    $inventory = PHPurchaseCommon::getTableName('inventory');
    $sql = "SELECT track from $inventory where ikey=%s";
    $sql = $this->_db->prepare($sql, $ikey);
    $track = $this->_db->get_var($sql);
    //PHPurchaseCommon::log("Is inventory tracked query: $sql");
    $isTracked = ($track == 1) ? true : false;
    return $isTracked;
  }
  
  public function pruneInventory(array $ikeyList) {
    $inventory = PHPurchaseCommon::getTableName('inventory');
    $list = "'" . implode("','", $ikeyList) . "'";
    $sql = "DELETE from $inventory where ikey not in ($list)";
    $this->_db->query($sql);
    //PHPurchaseCommon::log("Prune Inventory: $sql");
  }
  
  private function _buildOptionList($optNumber) {
    $select = '';
    $optionName = "options_$optNumber";
    if(strlen($this->$optionName) > 1) {
      $select = "\n<select name=\"options_$optNumber\" id=\"options_$optNumber\">";
      $opts = split(',', $this->$optionName);
      foreach($opts as $opt) {
        $opt = str_replace('+$', '+ $', $opt);
        $opt = trim($opt);
        $optDisplay = str_replace('$', CURRENCY_SYMBOL, $opt);
        $select .= "\n\t<option value=\"$opt\">$optDisplay</option>";
      }
      $select .= "\n</select>";
    }
    return $select;
  }

  public function isDigital() {
    $isDigital = false;
    if(strlen($this->downloadPath) > 2) {
      $isDigital = true;
    }
    return $isDigital;
  }
  
  public function isShipped() {
    $isShipped = false;
    if($this->shipped > 0) {
      $isShipped = true;
    }
    return $isShipped;
  }

  /**
   * Return the shipping rate for this product for the given shipping method
   */
  public function getShippingPrice($methodId) {
    // Look to see if there is a specific setting for this product and the given shipping method
    $ratesTable = PHPurchaseCommon::getTableName('shipping_rates');
    $sql = "SELECT shipping_rate from $ratesTable where product_id = " . $this->id . " and shipping_method_id = $methodId";
    $rate = $this->_db->get_var($sql);
    if($rate === NULL) {
      // If no specific rate is set, return the default rate for the given shipping method
      $shippingMethods = PHPurchaseCommon::getTableName('shipping_methods');
      $sql = "SELECT default_rate from $shippingMethods where id=$methodId";
      $rate = $this->_db->get_var($sql);
    }
    return $rate;
  }
  
  public function getBundleShippingPrice($methodId) {
    $ratesTable = PHPurchaseCommon::getTableName('shipping_rates');
    $shippingMethods = PHPurchaseCommon::getTableName('shipping_methods');
    
    // Look to see if there is a specific bundle rate for this product and the given shipping method
    $sql = "SELECT shipping_bundle_rate from $ratesTable where product_id = " . $this->id . " and shipping_method_id = $methodId";
    $rate = $this->_db->get_var($sql);
    if($rate === NULL) {
      // If no specific rate is set, return the default bundle rate for the given shipping method
      $sql = "SELECT default_bundle_rate from $shippingMethods where id=$methodId";
      $rate = $this->_db->get_var($sql);
      return $rate;
    }
    return $rate;
  }
  
  public function getFreeTrialNumber() {
    $number = '0';
    if(strlen($this->free_trial) > 2) {
      list($number, $unit) = explode(' ', $this->free_trial);
      $number = trim($number);
    }
    return $number;
  }
  
  public function getFreeTrialUnit() {
    $unit = 'days';
    if(strlen($this->free_trial) > 2) {
      list($num, $unit) = explode(' ', $this->free_trial);
    }
    return $unit;
  }
  
  /*
  public function getRecurringIntervalDisplay() {
    $out = '';
    if($this->recurring_interval > 0) {
      if($this->recurring_interval > 1) {
        $out = $this->recurring_interval . ' ' . $this->recurring_unit;
      }
      else {
        $out = str_replace('s', '', $this->recurring_unit);
      }
    }
    return $out;
  }
  */
  
  public function getRecurringIntervalDisplay() {
    $out = '';
    $interval = $this->recurring_interval;
    $unit = $this->recurring_unit;
    if($interval == 1) {
      $out = str_replace('s', '', $unit);
    }
    elseif($interval == 12 && $unit = 'months') {
      $out = 'year';
    }
    else {
      $out = $interval . ' ' . $unit;
    }
    return $out;
  }
  
  public function isSubscription() {
    $isSub = false;
    if($this->recurring_interval > 0) {
      $isSub = true;
    }
    return $isSub;
  }
  
  public static function getProductIdByGravityFormId($id) {
    global $wpdb;
    $products = PHPurchaseCommon::getTableName('products');
    $sql = "SELECT id from $products where gravity_form_id = %d";
    $query = $wpdb->prepare($sql, $id);
    $productId = $wpdb->get_var($query);
    return $productId;
  }
  
  public static function getNonSubscriptionProducts() {
    global $wpdb;
    $subscriptions = array();
    $products = PHPurchaseCommon::getTableName('products');
    $sql = "SELECT id from $products where recurring_interval < 1 order by name";
    $results = $wpdb->get_results($sql);
    foreach($results as $row) {
      $subscriptions[] = new PHPurchaseProduct($row->id);
    }
    return $subscriptions;
  }
  
  public static function getSubscriptionProducts() {
    global $wpdb;
    $subscriptions = array();
    $products = PHPurchaseCommon::getTableName('products');
    $sql = "SELECT id from $products where recurring_interval > 0 order by name";
    $results = $wpdb->get_results($sql);
    foreach($results as $row) {
      $subscriptions[] = new PHPurchaseProduct($row->id);
    }
    return $subscriptions;
  }
  
  public function countByStatus($status) {
    $recurringItems = PHPurchaseCommon::getTableName('recurring_items');
    $orderItems = PHPurchaseCommon::getTableName('order_items');
    $sql = "SELECT count(*) as num from 
      $recurringItems as r, 
      $orderItems as o 
      where 
        r.order_item_id = o.id and
        r.status = %s and 
        o.product_id = %d";
    $query = $this->_db->prepare($sql, $status, $this->id);
    $num = $this->_db->get_var($query);
    return $num;
  }
  
  /**
   * Return the number of sales for the given month
   * 
   * @param int $month An integer between 1 and 12 inclusive
   * @param int $year The four digit year
   * @return int
   */
  public function getSalesForMonth($month, $year) {
    $orders = PHPurchaseCommon::getTableName('orders');
    $orderItems = PHPurchaseCommon::getTableName('order_items');
    $start = date('Y-m-d 00:00:00', strtotime($month . '/1/' . $year));
    $end = date('Y-m-d 00:00:00', strtotime($month . '/1/' . $year . ' +1 month'));
    $sql = "SELECT sum(oi.quantity) as num 
      from 
        $orders as o, 
        $orderItems as oi 
      where
        oi.product_id = %s and
        oi.order_id = o.id and
        o.ordered_on >= '$start' and 
        o.ordered_on < '$end'
      ";
    $query = $this->_db->prepare($sql, $this->id);
    $num = $this->_db->get_var($query);
    return $num;
  }
  
  public function getSalesTotal() {
    $orders = PHPurchaseCommon::getTableName('orders');
    $orderItems = PHPurchaseCommon::getTableName('order_items');
    $sql = "SELECT sum(oi.quantity) as num 
      from 
        $orders as o, 
        $orderItems as oi 
      where
        oi.product_id = %s and
        oi.order_id = o.id
      ";
    $query = $this->_db->prepare($sql, $this->id);
    $num = $this->_db->get_var($query);
    return $num;
  }
  
  public function getIncomeTotal() {
    $orders = PHPurchaseCommon::getTableName('orders');
    $orderItems = PHPurchaseCommon::getTableName('order_items');
    $sql = "SELECT sum(oi.product_price * oi.quantity) as num 
      from 
        $orders as o, 
        $orderItems as oi 
      where
        oi.product_id = %s and
        oi.order_id = o.id
      ";
    $query = $this->_db->prepare($sql, $this->id);
    $num = $this->_db->get_var($query);
    return $num;
  }
  
  public function getIncomeForMonth($month, $year) {
    $orders = PHPurchaseCommon::getTableName('orders');
    $orderItems = PHPurchaseCommon::getTableName('order_items');
    $start = date('Y-m-d 00:00:00', strtotime($month . '/1/' . $year));
    $end = date('Y-m-d 00:00:00', strtotime($month . '/1/' . $year . ' +1 month'));
    
    $sql = "SELECT sum(oi.product_price * oi.quantity) as total
      FROM
        $orders as o,
        $orderItems as oi
      WHERE
        oi.product_id = %s and
        oi.order_id = o.id and
        o.ordered_on >= '$start' and 
        o.ordered_on < '$end'
      ";
       
    $query = $this->_db->prepare($sql, $this->id);
    $total = $this->_db->get_var($query);
    return $total;
  }
  
  public function validate() {
    $errors = array();
    
    // Verify that the item number is present
    if(empty($this->item_number)) {
      $errors['item_number'] = "Item number is required";
    }
    
    // Verify that no other products have the same item number
    if(empty($errors)) {
      $sql = "SELECT count(*) from $this->_tableName where item_number = %s and id != %d";
      $sql = $this->_db->prepare($sql, $this->item_number, $this->id);
      $count = $this->_db->get_var($sql);
      if($count > 0) {
        $errors['item_number'] = "The item number must be unique";
      }
    }

    return $errors;
  }
  
  /**
   * Check the gravity form entry for the quantity field.
   * Return the quanity in the field, or 1 if no quantity can be found.
   * 
   * @return int
   * @access public
   */
  public function gravityCheckForEntryQuantity($gfEntry) {
    $qty = 1;
    $qtyId = $this->gravity_form_qty_id;
    if($qtyId > 0) {
      if(isset($gfEntry[$qtyId]) && is_numeric($gfEntry[$qtyId])) {
        $qty = $gfEntry[$qtyId];
        unset($gfEntry[$qtyId]);
      }
    }
    return $qty;
  }
  
  public function gravityGetVariationPrices($gfEntry) {
    $options = array();
    foreach($gfEntry as $id => $value) {
      $exp = '/[+-]\s*\$\d/';
      if(preg_match($exp, $value)) {
        $options[] = $value;
      }
    }
    $options = implode('~', $options);
    return $options;
  }
  
  public function isGravityProduct() {
    $isGravity = false;
    if($this->gravity_form_id > 0) {
      $isGravity = true;
    }
    return $isGravity;
  }
  
}
