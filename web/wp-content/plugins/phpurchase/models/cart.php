<?php
class PHPurchaseCartItem {
  private $_productId;
  private $_quantity;
  private $_optionInfo;
  private $_priceDifference;
  private $_customFieldInfo;
  private $_formEntryIds;
  
  public function __construct($productId=0, $qty=1, $optionInfo='', $priceDifference=0) {
    $this->_productId = $productId;
    $this->_quantity = $qty;
    $this->_optionInfo = $optionInfo;
    $this->_priceDifference = $priceDifference;
    $this->_formEntryIds = array();
    // PHPurchaseCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] New Cart Item Option Info: $optionInfo");
  }
  
  public function setProductId($id) {
    if(is_numeric($id) && $id > 0) {
      $this->_productId = $id;
    }
  }
  
  public function getProductId() {
    return $this->_productId;
  }
  
  public function setOptionInfo($value) {
    $this->_optionInfo = $value;
  }
  
  public function getOptionInfo() {
    $options = $this->_optionInfo;
    if($this->isSubscription()) {
      $freeTrial = $this->getFreeTrial();
      $options .= CURRENCY_SYMBOL . $this->getRecurringPrice() . ' / ' . $this->getRecurringIntervalDisplay();
      if($freeTrial[0] > 0) {
        $options .= " Free Trial: $freeTrial";
      }
      if($this->getRecurringOccurrences() > 0) {
        $options .= ' x ' . $this->getRecurringOccurrences();
      }
      //PHPurchaseCommon::log("This is a subscription product: $options " . $this->getItemNumber());
    }
    return $options;
  }
  
  public function setQuantity($qty) {
    if(is_numeric($qty) && $qty >= 0) {
      $qty = ceil($qty);
      $product = new PHPurchaseProduct($this->_productId);
      if($product->maxQuantity > 0) {
        // Only limit quantity when max is set to a value greater than zero
        if($product->maxQuantity < $qty) {
          $qty = $product->maxQuantity;
        }
      }
      
      
      if($product->gravity_form_id > 0) {
        // Set quantity to zero because this is a gravity forms product with no entries
        if(count($this->_formEntryIds) == 0) {
          $qty = 0;
        }
        else {
          if($product->gravity_form_qty_id > 0) {
            // update gravity form entry for quanity to keep cart and gform in sync
            $gr = new GravityReader();
            $entryId = $this->_formEntryIds[0];
            $qtyFieldId = $product->gravity_form_qty_id;
            $gr->updateQuantity($entryId, $qtyFieldId, $qty);
          }
        }
      }
      
      
      $this->_quantity = $qty;
    }
  }
  
  public function setCustomFieldInfo($info) {
    $info = stripslashes($info);
    $this->_customFieldInfo = $info;
  }
  
  public function getQuantity() {
    return $this->_quantity;
  }
  
  public function getCustomField($itemIndex, $fullMode=true) {
    $out = '';
    if($this->_productId > 0) {
      $p = new PHPurchaseProduct();
      $p->load($this->_productId);
      
      if($p->custom == 'single') {
        $desc = $p->custom_desc;
        $value = $this->_customFieldInfo;
        if($fullMode) {
          $buttonValue = empty($value) ? 'Save' : 'Update';
          $showCustomForm = empty($value) ? '' : 'none';
          $change = empty($value) ? '' : "<a href='' onclick='' id='change_$itemIndex'>Change</a>";
          $out = "
          <script type='text/javascript'>
          	jQuery(document).ready(function($){
          		$('#change_$itemIndex').click(function() {
          		  $('#customForm_$itemIndex').toggle();
          		  return false;
          		});
            });
          </script>
          <br/><p class=\"PHPurchaseCustomFieldDesc\">$desc:<br/><strong>$value</strong> $change</p>
          <div id='customForm_$itemIndex' style='display: $showCustomForm;'>
          <input type=\"text\" name=\"customFieldInfo[$itemIndex]\" value=\"$value\" class=\"PHPurchaseCustomTextField\" id=\"custom_field_info_$itemIndex\" />
          <input type=\"submit\" value=\"$buttonValue\" /></div>";
        }
        else {
          if(empty($value)) {
            $cartPage = get_page_by_path('store/cart');
            $viewCartLink = get_permalink($cartPage->ID);
            $value = "<a href='$viewCartLink'>Click here to enter your information</a>";
          }
          $out = "<br/><p class=\"PHPurchaseCustomFieldDesc\">$desc:<br/><strong>$value</strong></p>";
          
        }
      }
      elseif($p->custom == 'multi') {
        $desc = $p->custom_desc;
        $value = $this->_customFieldInfo;
        if($fullMode) {
          $buttonValue = empty($value) ? 'Save' : 'Update';
          $showCustomForm = empty($value) ? '' : 'none';
          $change = empty($value) ? '' : "<a href='' onclick='' id='change_$itemIndex'>Change</a>";
          $brValue = nl2br($value);
          $out = "
          <script type='text/javascript'>
          	jQuery(document).ready(function($){
          		$('#change_$itemIndex').click(function() {
          		  $('#customForm_$itemIndex').toggle();
          		  return false;
          		});
            });
          </script>
          <br/><p class=\"PHPurchaseCustomFieldDesc\">$desc:<br/><strong>$brValue</strong><br/>$change</p>
          <div id='customForm_$itemIndex' style='display: $showCustomForm;'>
          <textarea name=\"customFieldInfo[$itemIndex]\" class=\"PHPurchaseCustomTextarea\" id=\"custom_field_info_$itemIndex\" />$value</textarea>
          <br/><input type=\"submit\" value=\"$buttonValue\" /></div>";
        }
        else {
          if(empty($value)) {
            $cartPage = get_page_by_path('store/cart');
            $viewCartLink = get_permalink($cartPage->ID);
            $value = "<a href='$viewCartLink'>Click here to enter your information</a>";
          }
          $value = nl2br($value);
          $out = "<br/><p class=\"PHPurchaseCustomFieldDesc\">$desc:<br/><strong>$value</strong></p>";
        }
      }
    }
    return $out;
  }
  
  /**
   * Return the value of the custom field info or false if the value is empty
   */
  public function getCustomFieldInfo() {
    $info = false;
    if(!empty($this->_customFieldInfo)) {
      $info = $this->_customFieldInfo;
    }
    return $info;
  }
  
  /**
   * Return the value of the custom field description or false if the value is empty
   */
  public function getCustomFieldDesc() {
    $desc = false;
    if($this->_productId > 0) {
      $p = new PHPurchaseProduct();
      $p->load($this->_productId);
      if(strlen($p->custom_desc) > 0) {
        $desc = $p->custom_desc;
      }
    }
    return $desc;
  }
  
  /**
   * Return the price for the product + the price difference applied by selected product options.
   */
  public function getProductPrice() {
    if($this->_productId > 0) {
      $product = new PHPurchaseProduct($this->_productId);
      $price = $product->price + $this->_priceDifference;
      if($this->isSubscription()) {
        $trial = $product->free_trial;
        if($trial[0] > 0) {
          // Set the price to zero if the product has a free trial period
          $price = 0;
        }
      }
      return $price;
    }
    return false;
  }
  
  public function getRecurringPrice() {
    $price = false;
    if($this->_productId > 0) {
      if($this->isSubscription()) {
        $product = new PHPurchaseProduct($this->_productId);
        $price = $product->price + $this->_priceDifference;
      }
    }
    return number_format($price, 2);
  }
  
  public function getItemNumber() {
    if($this->_productId > 0) {
      $p = new PHPurchaseProduct();
      $p->load($this->_productId);
      return $p->item_number;
    }
    return false;
  }
  
  public function getWeight() {
    if($this->_productId > 0) {
      $p = new PHPurchaseProduct();
      $p->load($this->_productId);
      return $p->weight;
    }
    return false;
  }
  
  public function getFormEntryIds() {
    return $this->_formEntryIds;
  }
  
  public function getFullDisplayName() {
    $product = new PHPurchaseProduct($this->_productId);
    $fullName = $product->name;
    $optionInfo = $this->getOptionInfo();
    if(strlen($optionInfo) >= 1) {
      $options = split(',', $optionInfo);
      $options = implode(', ', $options);
      $fullName .= " ($options)";
    }
    return $fullName;
  }
  
  public function isEqual(PHPurchaseCartItem $item) {
    $isEqual = true;
    if($this->_productId != $item->getProductId()) {
      $isEqual = false;
    }
    if($this->_optionInfo != $item->getOptionInfo()) {
      $isEqual = false;
    }
    return $isEqual;
  }
  
  public function isDigital() {
    $product = new PHPurchaseProduct($this->_productId);
    return $product->isDigital();
  }
  
  public function isShipped() {
    $product = new PHPurchaseProduct($this->_productId);
    return $product->isShipped();
  }
  
  public function isSubscription() {
    $product = new PHPurchaseProduct($this->_productId);
    return $product->recurring_interval > 0;
  }
  
  public function getRecurringInterval() {
    $product = new PHPurchaseProduct($this->_productId);
    return $product->recurring_interval;
  }
  
  public function getRecurringIntervalDisplay() {
    $product = new PHPurchaseProduct($this->_productId);
    return $product->getRecurringIntervalDisplay();
  }
  
  public function getRecurringUnit() {
    $product = new PHPurchaseProduct($this->_productId);
    $interval = $product->recurring_interval;
    $unit = $product->recurring_unit;
    if($interval == 1) {
      $unit = str_replace('s', '', $unit);
    }
    return $unit;
  }
  
  public function getRecurringOccurrences() {
    $product = new PHPurchaseProduct($this->_productId);
    $occurrences = $product->recurring_occurrences;
    return $occurrences;
  }
  
  public function getStartDate() {
    $product = new PHPurchaseProduct($this->_productId);
    $trial = $product->free_trial;
    //$startDate = date('Y-m-d H:i:s', strtotime("+$trial"));
    $startDate = date('Y-m-d', strtotime("+$trial"));
    return $startDate;
  }
  
  public function getFreeTrial() {
    $product = new PHPurchaseProduct($this->_productId);
    $trial = $product->free_trial;
    if($trial[0] == 1) {
      $trial = str_replace('s', '', $trial);
    }
    return $trial;
  }
  
  public function addFormEntryId($id) {
    if(!is_array($this->_formEntryIds)) {
      $this->_formEntryIds = array();
    }
    if(!in_array($id, $this->_formEntryIds)) {
      $this->_formEntryIds[] = $id;
    }
  }
  
  public function showAttachedForms($fullMode) {
    $out = '';
    if(is_array($this->_formEntryIds)) {
      foreach($this->_formEntryIds as $entryId) {
        /*
        $removeLink = '';
        if($fullMode) {
          $removeLink = str_replace( '%7E', '~', $_SERVER['REQUEST_URI']);
          $removeLink .= strpos($removeLink, '?') ? '&' : '?';
          $removeLink .= 'phpurchase-task=remove-attached-form&entry=' . $entryId;
          $removeLink = '<a class="PHPurchaseRemoveFormLink" href="' . $removeLink . '">remove</a>';
        }
        */
        $out .= "<div class='PHPurchaseGravityFormDisplay'>" . displayGravityForm($entryId) . "</div>";
      }
    }
    return $out;
  }
  
  public function detachFormEntry($lead_id) {
    $entries = $this->getFormEntryIds();
    if(in_array($lead_id, $entries)) {
      if(class_exists('RGFormsModel')) {
        global $wpdb;
        $lead_table = RGFormsModel::get_lead_table_name();
        $lead_notes_table = RGFormsModel::get_lead_notes_table_name();
        $lead_detail_table = RGFormsModel::get_lead_details_table_name();
        $lead_detail_long_table = RGFormsModel::get_lead_details_long_table_name();

        //Delete from detail long
        $sql = $wpdb->prepare(" DELETE FROM $lead_detail_long_table
                                WHERE lead_detail_id IN(
                                    SELECT id FROM $lead_detail_table WHERE lead_id=%d
                                )", $lead_id);
        $wpdb->query($sql);

        //Delete from lead details
        $sql = $wpdb->prepare("DELETE FROM $lead_detail_table WHERE lead_id=%d", $lead_id);
        $wpdb->query($sql);

        //Delete from lead notes
        $sql = $wpdb->prepare("DELETE FROM $lead_notes_table WHERE lead_id=%d", $lead_id);
        $wpdb->query($sql);

        //Delete from lead
        $sql = $wpdb->prepare("DELETE FROM $lead_table WHERE id=%d", $lead_id);
        $wpdb->query($sql);
        PHPurchaseCommon::log("Deleting lead from lead table: $sql");

        // Remove entry from array
        $entries = array_values(array_diff($entries, array($lead_id))); 
        $this->_formEntryIds = $entries;
        $qty = $this->getQuantity();
        $this->setQuantity($qty - 1);
      }
    }
  }
  
  public function detachAllForms() {
    $entries = $this->getFormEntryIds();
    if(is_array($entries)) {
      foreach($entries as $id) {
        $this->detachFormEntry($id);
      }
    }
  }
  
  public function hasAttachedForms() {
    $hasForms = false;
    if(is_array($this->_formEntryIds) && count($this->_formEntryIds) > 0) {
      $hasForms = true;
    }
    return $hasForms;
  }
  
}

class PHPurchaseCart {
  
  /**
   * An array of PHPurchaseCartItem objects
   */
  private $_items = array();
  
  private $_promotion;
  private $_promoStatus;
  private $_shippingMethodId;
  
  public function __construct($items=null) {
    if(is_array($items)) {
      $this->_items = $items;
    }
    else {
      $this->_items = array();
    }
    $this->_promoStatus = 0;
    $this->_setDefaultShippingMethodId();
  }
  
  public function addItem($id, $qty=1, $optionInfo='', $formEntryId=0) {
    // Look for price difference information
    $optionInfo = trim($optionInfo);
    // PHPurchaseCommon::log("Raw options => $optionInfo");
    $priceDiff = 0;
    $options = explode('~', $optionInfo);
    $optionList = array();
    foreach($options as $opt) {
      PHPurchaseCommon::log("Working with option: $opt");
      if(preg_match('/\+\s*\$/', $opt)) {
        $opt = preg_replace('/\+\s*\$/', '+$', $opt);
        list($opt, $pd) = explode('+$', $opt);
        $optionList[] = trim($opt);
        $priceDiff += $pd;
      }
      elseif(preg_match('/-\s*\$/', $opt)) {
        $opt = preg_replace('/-\s*\$/', '-$', $opt);
        list($opt, $pd) = explode('-$', $opt);
        $optionList[] = trim($opt);
        $pd = trim($pd);
        $priceDiff -= $pd;
      }
      else {
        $optionList[] = trim($opt);
      }
    }
    $option = implode(', ', $optionList);
    
    PHPurchaseCommon::log("Option: $option");
    
    $product = new PHPurchaseProduct($id);
    if($product->id > 0) {
      
      $newItem = new PHPurchaseCartItem($product->id, $qty, $option, $priceDiff);
      
      if($product->isGravityProduct()) {
        PHPurchaseCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Product being added is a Gravity Product: $formEntryId");
        if($formEntryId > 0) {
          $newItem->addFormEntryId($formEntryId);
          $this->_items[] = $newItem;
        }
      }
      else {
        PHPurchaseCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Product being added is NOT a Gravity Product");
        $isNew = true;
        foreach($this->_items as $item) {
          if($item->isEqual($newItem)) {
            $isNew = false;
            $newQuantity = $item->getQuantity() + $qty;
            $item->setQuantity($newQuantity);
            if($formEntryId > 0) {
              $item->addFormEntryId($formEntryId);
            }
            break;
          }
        }
        if($isNew) {
          $this->_items[] = $newItem;
        }
      }
      
    }
  }
  
  public function removeItem($itemIndex) {
    if(isset($this->_items[$itemIndex])) {
      $this->_items[$itemIndex]->detachAllForms();
      unset($this->_items[$itemIndex]);
    }
  }
  
  public function setPriceDifference($amt) {
    if(is_numeric($amt)) {
      $this->_priceDifference = $amt;
    }
  }
  
  public function setItemQuantity($itemIndex, $qty) {
    if(is_numeric($qty)) {
      if(isset($this->_items[$itemIndex])) {
        if($qty == 0) {
          unset($this->_items[$itemIndex]);
        }
        else {
          $this->_items[$itemIndex]->setQuantity($qty);
        }
      }
    }
  }
  
  public function setCustomFieldInfo($itemIndex, $info) {
    if(isset($this->_items[$itemIndex])) {
      $this->_items[$itemIndex]->setCustomFieldInfo($info);
    }
  }
  
  /**
   * Return the number of items in the shopping cart.
   * This count includes multiples of the same product so the returned value is the sum 
   * of all the item quantities for all the items in the cart.
   */
  public function countItems() {
    $count = 0;
    foreach($this->_items as $item) {
      $count += $item->getQuantity();
    }
    return $count;
  }
  
  public function getItems() {
    return $this->_items;
  }
  
  public function getItem($itemIndex) {
    return $this->_items[$itemIndex];
  }
  
  public function setItems($items) {
    if(is_array($items)) {
      $this->_items = $items;
    }
  }
  
  public function getSubTotal() {
    $total = 0;
    foreach($this->_items as $item) {
      $total += $item->getProductPrice() * $item->getQuantity();
    }
    return $total;
  }
  
  public function getTaxableAmount() {
    $total = 0;
    $p = new PHPurchaseProduct();
    foreach($this->_items as $item) {
      $p->load($item->getProductId());
      if($p->taxable == 1) {
        $total += $item->getProductPrice() * $item->getQuantity();
      }
    }
    $discount = $this->getDiscountAmount();
    if($discount > $total) {
      $total = 0;
    }
    else {
      $total = $total - $discount;
    }
    return $total;
  }
  
  public function getTax($state='All Sales', $zip=null) {
    $tax = 0;
    $taxRate = new PHPurchaseTaxRate();
    
    $isTaxed = $taxRate->loadByZip($zip);
    if($isTaxed == false) {
      $isTaxed = $taxRate->loadByState($state);
    }
    
    if($isTaxed) {
      $taxable = $this->getTaxableAmount();
      if($taxRate->tax_shipping == 1) {
        $taxable += $this->getShippingCost();
      }
      $tax = number_format($taxable * ($taxRate->rate/100), 2);
    }
    
    return $tax;
  }
  
  /**
   * Return an array of the shipping methods where the keys are names and the values are ids
   * 
   * @return array of shipping names and ids
   */
  public function getShippingMethods() {
    $method = new PHPurchaseShippingMethod();
    $methods = $method->getModels("where code IS NULL or code = ''", 'order by default_rate asc');
    $ship = array();
    foreach($methods as $m) {
      $ship[$m->name] = $m->id;
    }
    return $ship;
  }

  public function getCartWeight() {
    $weight = 0;
    foreach($this->_items as $item) {
      $weight += $item->getWeight()  * $item->getQuantity();
    }
    return $weight;
  }
  
  public function getShippingCost($methodId=null) {
    $setting = new PHPurchaseSetting();

    if(!$this->requireShipping()) { 
      $shipping = 0; 
    }
    // Check to see if Live Rates are enabled and available
    elseif(isset($_SESSION['PHPurchaseLiveRates']) && $setting->lookupValue('use_live_rates')) {
      $liveRate = $_SESSION['PHPurchaseLiveRates']->getSelected();
      if(is_numeric($liveRate->rate)) {
        return number_format($liveRate->rate, 2);
      }
    }
    // Live Rates are not in use
    else {
      if($methodId > 0) {
        $this->_shippingMethodId = $methodId;
      }
      
      if($this->_shippingMethodId < 1) {
        $this->_setDefaultShippingMethodId();
      }
      else {
        // make sure shipping method exists otherwise reset to the default shipping method
        $method = new PHPurchaseShippingMethod();
        if(!$method->load($this->_shippingMethodId) || !empty($method->code)) {
          PHPurchaseCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Resetting the default shipping method id");
          $this->_setDefaultShippingMethodId();
        }
      }
      
      $methodId = $this->_shippingMethodId;

      // Check for shipping rules first
      $shipping = 0;
      $isRuleSet = false;
      $rule = new PHPurchaseShippingRule();
      $rules = $rule->getModels("where shipping_method_id = $methodId", 'order by min_amount desc');
      if(count($rules)) {
        $cartTotal = $this->getSubTotal();
        foreach($rules as $rule) {
          if($cartTotal > $rule->minAmount) {
            $shipping = $rule->shippingCost;
            $isRuleSet = true; 
            break;
          }
        }
      }
      
      if(!$isRuleSet) {
        $product = new PHPurchaseProduct();
        $shipping = 0;
        $highestShipping = 0;
        $bundleShipping = 0;
        $highestId = 0;
        foreach($this->_items as $item) {
          $product->load($item->getProductId());
          
          if($highestId < 1) {
            $highestId = $product->id;
          }
          
          if($product->isShipped()) {
            $shippingPrice = $product->getShippingPrice($methodId);
            $bundleShippingPrice = $product->getBundleShippingPrice($methodId);
            if($shippingPrice > $highestShipping) {
              $highestShipping = $shippingPrice;
              $highestId = $product->id;
            }
            $bundleShipping += $bundleShippingPrice * $item->getQuantity();
          }
        }

        if($highestId > 0) {
          $product->load($highestId);
          $shippingPrice = $product->getShippingPrice($methodId);
          $bundleShippingPrice = $product->getBundleShippingPrice($methodId);
          $shipping = $shippingPrice + ($bundleShipping - $bundleShippingPrice);
        }
      }
    }
    
    return number_format($shipping, 2);
  }
  
  public function applyPromotion($code) {
    $code = strtoupper($code);
    $promotion = new PHPurchasePromotion();
    if($promotion->loadByCode($code)) {
      if($this->_promotion->minOrder > $this->getSubTotal()) {
        // Order total not high enough for promotion to apply
        $this->_promoStatus = -1;
        $this->_promotion = null;
      }
      else {
        $this->_promotion = $promotion;
        $this->_promoStatus = 1;
      }
    }
    else {
      $this->_promoStatus = -1;
      $this->_promotion = null;
    }
  }
  
  public function getPromotion() {
    $promotion = false;
    if(is_a($this->_promotion, 'PHPurchasePromotion')) {
      $promotion = $this->_promotion;
    }
    return $promotion;
  }
  
  public function getPromoMessage() {
    $message = '&nbsp;';
    if($this->_promoStatus == -1) {
      $message = 'Invalid coupon code';
    }
    elseif($this->_promoStatus == -2) {
      $message = 'Order total not high enough for promotion to apply';
    }
    if($this->_promoStatus < 0) {
      $this->_promoStatus = 0;
    }
    return $message;
  }
  
  public function resetPromotionStatus() {
    if(is_a($this->_promotion, 'PHPurchasePromotion')) {
      if($this->_promotion->minOrder > $this->getSubTotal()) {
        // Order total not high enough for promotion to apply
        $this->_promoStatus = -2;
        $this->_promotion = null;
      }
      else {
        $this->_promoStatus = 1;
      }
    }
  }
  
  public function clearPromotion() {
    $this->_promotion = '';
    $this->_promoStatus = 0;
  }
  
  public function getPromoStatus() {
    return $this->_promoStatus;
  }
  
  public function getDiscountAmount() {
    $discount = 0;
    if(is_a($this->_promotion, 'PHPurchasePromotion')) {
      $total = $this->getSubTotal();
      $discountedTotal = $this->_promotion->discountTotal($total);
      $discount = number_format($total - $discountedTotal, 2, '.', '');
      PHPurchaseCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Getting discount Total: $total -- Discounted Total: $discountedTotal -- Discount: $discount");
    }
    return $discount;
  }
  
  public function getGrandTotal() {
    $total = $this->getSubTotal() + $this->getShippingCost() - $this->getDiscountAmount();
    return $total; 
  }
  
  public function storeOrder($orderInfo) {
    $order = new PHPurchaseOrder();
    $orderInfo['trans_id'] = (empty($orderInfo['trans_id'])) ? 'MT-' . PHPurchaseCommon::getRandNum() : $orderInfo['trans_id'];
    $orderInfo['ip'] = $_SERVER['REMOTE_ADDR'];
    $orderInfo['discount_amount'] = $this->getDiscountAmount();
    $order->setInfo($orderInfo);
    $order->setItems($this->getItems());
    return $order->save();
  }
  
  /**
   * Return true if all products are digital
   */
  public function isAllDigital() {
    $allDigital = true;
    foreach($this->getItems() as $item) {
      if(!$item->isDigital()) {
        $allDigital = false;
        break;
      }
    }
    return $allDigital;
  }
  
  public function hasSubscriptionProducts() {
    foreach($this->getItems() as $item) {
      if($item->isSubscription()) {
        return true;
      }
    }
    return false;
  }
  
  /**
   * Return false if none of the items in the cart are shipped
   */
  public function requireShipping() {
    $ship = false;
		foreach($this->getItems() as $item) {
		  if($item->isShipped()) {
			$ship = true;
			break;
		  }
		}
    return $ship;
  }

  public function setShippingMethod($id) {
    $method = new PHPurchaseShippingMethod();
    if($method->load($id)) {
      $this->_shippingMethodId = $id;
      PHPurchaseCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Set shipping method id to: $id");
    }
  }

  public function getShippingMethodId() {
    if($this->_shippingMethodId < 1) {
      $this->_setDefaultShippingMethodId();
    }
    return $this->_shippingMethodId;
  }

  public function getShippingMethodName() {
    // Look for live rates
    if(isset($_SESSION['PHPurchaseLiveRates'])) {
      $rate = $_SESSION['PHPurchaseLiveRates']->getSelected();
      return $rate->service;
    }
    // Not using live rates
    else {
      if($this->isAllDigital()) {
        return 'Download';
      }
      elseif(!$this->requireShipping()) {
        return 'None';
      }
      else {
        if($this->_shippingMethodId < 1) {
          $this->_setDefaultShippingMethodId();
        }
        $method = new PHPurchaseShippingMethod($this->_shippingMethodId);
        return $method->name;
      }
    }
    
  }
  
  public function detachFormEntry($entryId) {
    foreach($this->_items as $index => $item) {
      $entries = $item->getFormEntryIds();
      if(in_array($entryId, $entries)) {
        $item->detachFormEntry($entryId);
        $qty = $item->getQuantity();
        if($qty == 0) {
          $this->removeItem($index);
        }
      }
    }
  }
  
  public function checkCartInventory() {
    $alert = '';
    foreach($this->_items as $itemIndex => $item) {
      if(!PHPurchaseProduct::confirmInventory($item->getProductId(), $item->getOptionInfo(), $item->getQuantity())) {
        PHPurchaseCommon::log("Unable to confirm inventory when checking cart.");
        $qtyAvailable = PHPurchaseProduct::checkInventoryLevelForProduct($item->getProductId(), $item->getOptionInfo());
        if($qtyAvailable > 0) {
          $alert .= '<p>We are not able to fulfill your order for <strong>' .  $item->getQuantity() . '</strong> ' . $item->getFullDisplayName() . "  because 
            we only have <strong>$qtyAvailable in stock</strong>.</p>";
        }
        else {
          $alert .= '<p>We are not able to fulfill your order for <strong>' .  $item->getQuantity() . '</strong> ' . $item->getFullDisplayName() . "  because 
            it is <strong>out of stock</strong>.</p>";
        }
        
        if($qtyAvailable > 0) {
          $item->setQuantity($qtyAvailable);
        }
        else {
          $this->removeItem($itemIndex);
        }
        
      }
    }
    
    if(!empty($alert)) {
      $alert = "<div class='PHPurchaseUnavailable'><h1>Inventory Restriction</h1> $alert <p>Your cart has been updated based on our available inventory.</p>";
      $alert .= '<input type="button" name="close" value="Ok" class="PHPurchaseButtonSecondary modalClose" /></div>';
    }
    
    return $alert;
  }
  
  /**
   * Return PHPurchaseLiveRates object. 
   * The shipping zip code must be in the session before calling this function.
   * 1/18/14 - modded for eFreight by Priya
   * @return PHPurchaseLiveRates
   */
  public function getUpsRates() {
    $liveRates = new PHPurchaseLiveRates();
    $cartWeight = $_SESSION['PHPurchaseCart']->getCartWeight();
    $zip = $_SESSION['phpurchase_shipping_zip'];
    $countryCode = $_SESSION['phpurchase_shipping_country_code'];
  
    if($cartWeight > 0 && isset($_SESSION['phpurchase_shipping_zip'])/* && isset($_SESSION['phpurchase_shipping_country_code'])*/) {
      PHPurchaseCommon::log("User requested shipping rates to ".$_SESSION['phpurchase_shipping_zip'], TRUE);

      // Return the live rates from the session if the zip, country code, and cart weight are the same
      /*if(isset($_SESSION['PHPurchaseLiveRates'])) {
        $cartWeight = $this->getCartWeight();
        $liveRates = $_SESSION['PHPurchaseLiveRates'];
        PHPurchaseCommon::log(  "Live Rates were found in session. Now comparing...
            $liveRates->weight --> $cartWeight
            $liveRates->toZip --> $zip
            $liveRates->toCountryCode --> $countryCode
        ");
        if($liveRates->weight == $cartWeight && $liveRates->toZip == $zip && $liveRates->toCountryCode == $countryCode) {
          PHPurchaseCommon::log("Using Live Rates from the session");
          return $liveRates; 
        }
      }*/
        // If there are no live rates in the session or the zip/weight has been changed then look up new rates
      $palletExist = false;
      $palletNames = array();
      $palletPrice = 0;
      $palletWeight = 0;
	  $count = 1;
      foreach($this->getItems() as $itemKey => $anItem){
        if(stripos($anItem->getFullDisplayName(), 'pallet') !== false){
          $palletExist = true;
		  break;
		}
	  }
	  if($palletExist){
	    foreach($this->getItems() as $itemKey => $anItem){
		  $quantString = $anItem->getQuantity() > 1 ? $anItem->getQuantity() . " x " : "";
          $palletNames[] = $quantString . $anItem->getFullDisplayName();
          $palletPrice += $anItem->getProductPrice()  * $anItem->getQuantity();
          $palletWeight += $anItem->getWeight()  * $anItem->getQuantity();
        }
		$count++;
      }

      /*if(!empty($comment)){
        $param['refNumber'] = $comment; //to be implemented
      }*/
      if($palletExist && class_exists("freightShipping")){
		$param = array();
        $param['itemDesc'] = implode(', ', $palletNames) . '.';
        $param['itemPrice'] = $palletPrice;
        $param['weight'] = $palletWeight;
        $param['count'] = $this->countItems();
        $param['destinationZip'] = $zip;
		$param['destinationType'] = $_SESSION['freight_loc_type'];
		$param['liftGate'] = strcasecmp($_SESSION['liftGate'], "yes") == 0 ? 1 : 0;
		if(isset($_SESSION['PHPurchaseLiveRates'])) {
          $cartWeight = $this->getCartWeight();
          $liveRates = $_SESSION['PHPurchaseLiveRates'];
		  if($liveRates->weight == $palletWeight && 
		  $liveRates->toZip == $zip &&
		  $liveRates->locType == $_SESSION['freight_loc_type']){
		    return $liveRates;
		  }
		}
        $freight = new freightShipping($param);
        $rates = $freight->getFreightRates();
		$liveRates->weight = $this->getCartWeight();
		$liveRates->toZip = $zip;
		$liveRates->locType = $_SESSION['freight_loc_type'];
		$liveRates->clearRates();
		foreach($rates as $service => $rate) {
		  $boom = explode(' - ', $service);
          $liveRates->addRate($service . ' - Pallet', $rate, $boom[2]);
		}
      }else{
		if(isset($_SESSION['PHPurchaseLiveRates'])) {
          $cartWeight = $this->getCartWeight();
          $liveRates = $_SESSION['PHPurchaseLiveRates'];
          PHPurchaseCommon::log(  "Live Rates were found in session. Now comparing...
              $liveRates->weight --> $cartWeight
              $liveRates->toZip --> $zip
          ");
          if($liveRates->weight == $cartWeight && $liveRates->toZip == $zip /*&& $liveRates->toCountryCode == $countryCode*/) {
            PHPurchaseCommon::log("Using Live Rates from the session");
            return $liveRates; 
          }
        }
		$ups = new PHPurchaseUPS();
        $liveRates->weight = $this->getCartWeight();
        $liveRates->toZip = $zip;
        $liveRates->toCountryCode = $countryCode;
        $rates = $ups->getAllRates($zip, $countryCode, $liveRates->weight);
		$liveRates->clearRates();
		foreach($rates as $service => $rate) {
		  $liveRates->addRate($service, $rate['rate'], $rate['code']);
		}
      }
    }else{
      $liveRates->weight = 0;
      $liveRates->toZip = $zip;
      $liveRates->toCountryCode = $countryCode;
      $liveRates->addRate('Free Shipping', '0.00', NULL);
    }
    $_SESSION['PHPurchaseLiveRates'] = $liveRates;
    return $liveRates;
  }
  
  //does the cart have a pallet that needs to be sent with freight?
  public function isPallet(){
    foreach($this->getItems() as $itemKey => $anItem){
      if(stripos($anItem->getFullDisplayName(), 'pallet') !== false){
        return true;
      }
    }
	return false;
  }
  
  protected function _setDefaultShippingMethodId() {
    // Set default shipping method to the cheapest method
    $method = new PHPurchaseShippingMethod();
    $methods = $method->getModels("where code IS NULL or code = ''", 'order by default_rate asc');
    $this->_shippingMethodId = $methods[0]->id;
  }
  
}
