<?php
global $wpdb;

$customer = null;
$closed = 'closed';

if(PHPURCHASEPRO) {
  $gw = PHPurchaseCommon::gatewayName();
  if($gw == 'quantum') {
    require_once dirname(__FILE__) . '/../pro/Quantum/vault-customer.php';
    $customer = new Quantum_VaultCustomer($data['id']);
  }
  elseif($gw == 'authnet') {
    require_once dirname(__FILE__) . '/../pro/Authnet/cim.php';
    $customer = new CIM($data['id']);
  }
}

$manualError = null;

if(isset($_REQUEST['phpurchase-task']) && $_REQUEST['phpurchase-task'] == 'update-sub-stats') {
  $ri = new PHPurchaseRecurringItem();
  foreach($_REQUEST as $key => $value) {
    if(strstr($key, 'sub_')) {
      list($dummy, $id) = explode('_', $key);
      if($id > 0 && $ri->load($id)) {
        $ri->status = $value;
        $ri->save();
      }
    }
  }
}
elseif(isset($_POST['phpurchase-task']) && $_POST['phpurchase-task'] == 'update-account-billing') {
  $account = PHPurchaseCommon::postVal('account');
  if(empty($account['CreditCardNumber'])) {
    unset($account['CreditCardNumber']);
    PHPurchaseCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] CreditCardNumber has been unset");
  }
  $customer = new Quantum_VaultCustomer($account);
  if(!empty($account['password'])) {
    if($account['password'] != $account['password2']) {
      $errors[] = "Passwords do not match";
    }
  }
  else {
    $errors = $customer->validate(true);
  }
  
  if(empty($errors)) {
    $errors = $customer->update();
    if(empty($errors)) {
      echo "<div id='PHPurchaseSuccessBox' style='width: 300px;'><p class='PHPurchaseSuccess'>Account updated</p></div>";
    }
  }
  else {
    $closed = ''; // NTS: The account information box should not be closed if there are errors.
  }
}
elseif(isset($_POST['phpurchase-task']) && $_POST['phpurchase-task'] == 'update-cim-admin') {
  $customer = new CIM();
  $errors = $customer->loadFromPost();
  if(count($errors) == 0) {
    $errors = $customer->update();
  }
  
  if(count($errors)) {
    $closed = ''; // NTS: The account information box should not be closed if there are errors.
  }
  else {
    echo "<div id='PHPurchaseSuccessBox' style='width: 300px;'><p class='PHPurchaseSuccess'>Account updated</p></div>";
  }
}
elseif(isset($_POST['phpurchase-task']) && $_POST['phpurchase-task'] == 'establish-subscription') {
  $productId = PHPurchaseCommon::postVal('product_id');
  $customerId = PHPurchaseCommon::postVal('customer_id');
  $startDate = strip_tags($_POST['start_date']);
  $paymentType = PHPurchaseCommon::postVal('payment_type');
  
  if($paymentType == 'cc' || $paymentType == 'manual') {
    if(PHPurchaseCommon::isValidDate($startDate)) {
      $startDate = date('Y-m-d', strtotime($startDate));
      if(is_numeric($customerId) && $customerId >= 1) {
        if(is_numeric($productId) & $productId >= 1) {
          $product = new PHPurchaseProduct($productId);
          if($product->isSubscription()) {
            $orderItems = PHPurchaseCommon::getTableName('order_items');
            $description = $product->name . ' (' . CURRENCY_SYMBOL . $product->price . ' / ' . $product->getRecurringIntervalDisplay() . ')';
            $data = array (
              'order_id' => 0,
              'product_id' => $product->id,
              'item_number' => $product->item_number,
              'product_price' => $product->price,
              'description' => $description,
              'quantity' => 1
            );
            $wpdb->insert($orderItems, $data);
            $orderItemId = $wpdb->insert_id;
            
            $recurringItems = PHPurchaseCommon::getTableName('recurring_items');
            $data = array(
              'account_id' => $customerId,
              'order_item_id' => $orderItemId,
              'amount' => $product->price,
              'recurring_interval' => $product->recurring_interval,
              'recurring_unit' => $product->recurring_unit,
              'recurring_occurrences' => $product->recurring_occurrences,
              'start_date' => $startDate,
              'status' => 'active',
              'created_at' => date('Y-m-d H:i:s'),
              'updated_at' => date('Y-m-d H:i:s'),
              'payment_type' => $paymentType,
            );
            $wpdb->insert($recurringItems, $data);
          }
          else {
            PHPurchaseCommon::log("This product is not a subscription product: $productId");
          }
        }
        else {
          $manualError = "Invalid product id";
          PHPurchaseCommon::log("Invalid product id: $productId");
        }
      }
      else {
        $manualError = "Invalid customer id";
        PHPurchaseCommon::log("Invalid customer id: $customerId");
      }
    }
    else {
      $manualError = "Invalid start date";
      PHPurchaseCommon::log("Invalid date: $startDate");
    }
  }

}
elseif(isset($_GET['phpurchase-task']) && $_GET['phpurchase-task'] == 'delete-subscription') {
  $subId = PHPurchaseCommon::getVal('subid');

  if(is_numeric($subId) && $subId > 0) {
    $orderItems = PHPurchaseCommon::getTableName('order_items');
    $sql = "DELETE from $orderItems where id = %d";
    $wpdb->query($wpdb->prepare($sql, $subId));

    $recurringItems = PHPurchaseCommon::getTableName('recurring_items');
    $sql = "DELETE from $recurringItems where order_item_id = %d";
    $wpdb->query($wpdb->prepare($sql, $subId));
    
    $recurringTransactions = PHPurchaseCommon::getTableName('recurring_transactions');
    $sql = "DELETE from $recurringTransactions where order_item_id=%d";
    $wpdb->query($wpdb->prepare($sql, $subId));
  }
}
?>

<style type='text/css'>
label {
  display: inline-block; 
  width: 120px; 
  text-align: right;
}

</style>

<?php if($gw == 'quantum'): ?>
  <?php if(!empty($customer->LastName)): ?>
    <h2><?php echo $customer->FirstName ?> <?php echo $customer->LastName ?></h2>
  <?php else: ?>
    <h2>No Name Provided</h2>
  <?php endif;?>

  <p style='float:left; margin: 0px 20px 0px 0px;'>
  <?php echo $customer->Address?><br/>
  <?php echo $customer->City?> <?php echo $customer->State ?> <?php echo $customer->ZipCode ?>
  </p>

  <p style='float:left; margin: 0px;'>
  Email: <a href='mailto:<?php echo $customer->EmailAddress ?>'><?php echo $customer->EmailAddress ?></a><br/>
  <?php if(!empty($customer->PhoneNumber)): ?>
    Phone: <?php echo $customer->PhoneNumber ?>
  <?php endif; ?>
  </p>
<?php elseif($gw == 'authnet'): ?>
  <?php if(!empty($customer->firstName)): ?>
    <h2><?php echo $customer->getFirstName() ?> <?php echo $customer->getLastName() ?></h2>
  <?php else: ?>
    <h2>Name not set</h2>
  <?php endif; ?>
  <p style='float:left; margin: 0px 20px 0px 0px;'>
    <?php echo $customer->getBillingAddress(); ?><br/>
    <?php echo $customer->getBillingCity() ?> <?php echo $customer->getBillingState() ?> <?php echo $customer->getBillingZip() ?>
  </p>

  <p style='float:left; margin: 0px;'>
  Email: <a href='mailto:<?php echo $customer->EmailAddress ?>'><?php echo $customer->EmailAddress ?></a><br/>
  <?php if(!empty($customer->phone)): ?>
    Phone: <?php echo $customer->phone ?>
  <?php endif; ?>
  </p>
<?php endif; ?>

<br style="clear:both;"/>

<div id="widgets-left" style='clear: both; margin-top: 20px; margin-left: 0px;'>
  <div id="available-widgets">
    
    <div class="widgets-holder-wrap <?php echo $closed ?>">
      <div class="sidebar-name">
        <div class="sidebar-name-arrow"><br/></div>
        <h3>Edit Account Information <span><img class="ajax-feedback" alt="" title="" src="images/wpspin_light.gif"/></span></h3>
      </div>
      <div class="widget-holder">
        <div>
          <?php
            if(count($errors)) {
              echo "\n<script type='text/javascript'>\n";
              echo 'var $j = jQuery.noConflict();' . "\n";
              echo '$j(function() { ' . "\n";
              foreach($errors as $key => $value) {
                echo '$j("#' . $key . '").addClass("PHPurchaseErrorField");' . "\n";
              }
              echo '});';
              echo "</script>\n";

              echo '<div class="PHPurchaseError"><h3>There Were Problems Saving This Form</h3><ul>';
              foreach($errors as $key => $value) {
                echo "<li>$value</li>";
              }
              echo '</ul></div>';
            }
            $gw = PHPurchaseCommon::gatewayName();
            if($gw == 'quantum') {
              include(dirname(__FILE__) . '/../pro/Quantum/member-update-form.php');
            }
            elseif($gw == 'authnet') {
              $b = $customer->getBilling();
              $s = $customer->getShipping();
              $p = $customer->getPayment();
              include(dirname(__FILE__) . '/../pro/Authnet/member-update-form.php');
            }
            else {
              echo "<p style='color: red;'>No gateway is configured</p>";
            }
          ?>
        </div>
      </div>
    </div>
    
  </div>
</div>


<br style="clear:both;"/>
<br style="clear:both;"/>

<?php 
  $subs = $customer->getMySubscriptions(null, 'cc'); 
  $wpurl = get_bloginfo('wpurl');
  $formAction = "$wpurl/wp-admin/admin.php?page=phpurchase-members&phpurchase-task=viewMember&id=$customer->id";
?>
<?php if(count($subs)): ?>
  <form action='<?php echo $formAction ?>' method='post'>
    <h3>Credit Card Subscriptions</h3>
    <input type='hidden' name='phpurchase-task' value='update-sub-stats' />
    <input type='hidden' name='id' value='<?php echo $customer->id ?>' />
    <input type='hidden' name='page' value='phpurchase-members' />
    <table class="PHPurchaseTableMed">
      <tr>
        <th>Description</th>
        <th>Start Date</th>
        <th>Last Payment</th>
        <th>Next Payment</th>
        <th>Status</th>
        <th>&nbsp;</th>
      </tr>
      <?php foreach($subs as $s): ?>
        <tr>
          <td class='PHPurchase<?php echo ucwords($s->status) ?>'><?php echo $s->description ?></td>
          <td class='PHPurchase<?php echo ucwords($s->status) ?>'><?php echo date("n/j/Y", strtotime($s->start_date)) ?></td>
          <td class='PHPurchase<?php echo ucwords($s->status) ?>'><?php echo PHPurchaseRecurringItem::getLastPaymentFor($s->id) ?></td>
          <td class='PHPurchase<?php echo ucwords($s->status) ?>'><?php echo PHPurchaseRecurringItem::getNextPaymentFor($s->id) ?></td>
          <td class="" colspan="2">
            <select name='sub_<?php echo $s->id ?>'>
              <option value='active'    <?php echo $s->status == 'active' ? 'selected="selected"' : '' ?>>active</option>
              <option value='complete'  <?php echo $s->status == 'complete' ? 'selected="selected"' : '' ?>>complete</option>
              <option value='overdue'   <?php echo $s->status == 'overdue' ? 'selected="selected"' : '' ?>>overdue</option>
              <option value='suspended' <?php echo $s->status == 'suspended' ? 'selected="selected"' : '' ?>>suspended</option>
              <option value='canceled' <?php echo $s->status == 'canceled' ? 'selected="selected"' : '' ?>>canceled</option>
            </select>
            &nbsp;&nbsp;
            <a class="deleteLink" 
            href='?page=phpurchase-members&phpurchase-task=delete-subscription&id=<?php echo $customer->id ?>&subid=<?php echo $s->order_item_id ?>'>delete</a>
          </td>
        </tr>
      <?php endforeach; ?>
   
      <tr>
        <td colspan="4">&nbsp;</td>
        <td style="padding-top: 10px;"><input type="submit" class="button-secondary" value="Update Status" /></td>
      </tr>
    </table>
  </form>
<?php endif; ?>

<?php $subs = $customer->getMySubscriptions(null, 'manual'); ?>
<?php if(count($subs)): ?>
  <form action='<?php echo $formAction ?>' method='post'>
    <h3>Subscriptions Managed Manually</h3>
    <input type='hidden' name='phpurchase-task' value='update-sub-stats' />
    <input type='hidden' name='id' value='<?php echo $customer->id ?>' />
    <input type='hidden' name='page' value='phpurchase-members' />
    <table class="PHPurchaseTableMed">
      <tr>
        <th>Description</th>
        <th>Start Date</th>
        <th>Status</th>
        <th>&nbsp;</th>
      </tr>
      <?php foreach($subs as $s): ?>
        <tr>
          <td class='PHPurchase<?php echo ucwords($s->status) ?>'><?php echo $s->description ?></td>
          <td class='PHPurchase<?php echo ucwords($s->status) ?>'><?php echo date("n/j/Y", strtotime($s->start_date)) ?></td>
          <td class="" colspan="2">
            <select name='sub_<?php echo $s->id ?>'>
              <option value='active'    <?php echo $s->status == 'active' ? 'selected="selected"' : '' ?>>active</option>
              <option value='complete'  <?php echo $s->status == 'complete' ? 'selected="selected"' : '' ?>>complete</option>
              <option value='overdue'   <?php echo $s->status == 'overdue' ? 'selected="selected"' : '' ?>>overdue</option>
              <option value='suspended' <?php echo $s->status == 'suspended' ? 'selected="selected"' : '' ?>>suspended</option>
              <option value='canceled' <?php echo $s->status == 'canceled' ? 'selected="selected"' : '' ?>>canceled</option>
            </select>
            &nbsp;&nbsp;
            <a class="deleteLink" 
            href='?page=phpurchase-members&phpurchase-task=delete-subscription&id=<?php echo $customer->id ?>&subid=<?php echo $s->order_item_id ?>'>delete</a>
          </td>
        </tr>
      <?php endforeach; ?>
   
      <tr>
        <td colspan="2">&nbsp;</td>
        <td style="padding-top: 10px;"><input type="submit" class="button-secondary" value="Update Status" /></td>
      </tr>
    </table>
  </form>
<?php endif; ?>

<form action='<?php echo $formAction ?>' method='post'>
  <input type='hidden' name='phpurchase-task' value='establish-subscription' />
  <input type='hidden' name='customer_id' value='<?php echo $customer->id ?>' />
  
  <h3>Establish New Subscription</h3>
  
  <?php if(!empty($manualError)): ?>
  <div class="PHPurchaseError" style="width: 300px; padding: 10px; margin: 10px 0px; "><?php echo $manualError; ?></div>
  <?php endif; ?>
  
  <ul>
    <li>
      <label>Subscription:</label>
      <select name='product_id'>
        <?php
          $subs = PHPurchaseProduct::getSubscriptionProducts();
          foreach($subs as $s) {
            echo "<option value='$s->id'>$s->name \$" . $s->price . ' / ' . $s->getRecurringIntervalDisplay() . "</option>\n";
          }
        ?>
      </select>
    </li>
    <li><label>Start billing on:</label>
      <input type="text" name="start_date" style="width: 100px;" value="<?php echo date("m/d/Y"); ?>"/> <span style="color: #AAA;">(mm/dd/YYYY)</span>
    </li>
    <li><label>Payment Type:</label>
      <select name="payment_type">
        <option value="manual">Manual</option>
        <option value="cc">Credit Card</option>
      </select>
    </li>
    <li><label>&nbsp;</label><input type="submit" style='margin-top: 10px;' value="Save Subscription" class="button-secondary" /></li>
  </ul>
</form>


<br style="clear:both;"/>
<br style="clear:both;"/>

<p><a href='?page=phpurchase-members'>&lt;&lt;&nbsp;Back to members</a></p>

<p style="width: 300px; margin: 100px 0px; color: #858585; border: 1px solid #ccc; padding: 10px; background-color: #f3f3f3;">
  <strong style="color: #744;">Delete This Account</strong><br/>
  You can permanently delete this account which will remove all of this person's subscriptions and delete their entire account from your website.
  This action cannot be undone. Please delete with care.
  <a id="removeAccountLink" href='?page=phpurchase-members&phpurchase-task=remove-account&id=<?php echo $customer->id ?>'>Permanently delete this account forever</a>
</p>



<script type='text/javascript'>
  $jq = jQuery.noConflict();
  $jq(document).ready(function() {
    setTimeout("$jq('#PHPurchaseSuccessBox').hide('slow')", 2000);
    $jq('.sidebar-name').click(function() {
      $jq(this.parentNode).toggleClass("closed");
    });
    
    $jq('#removeAccountLink').click(function() {
      return confirm('Are you sure you want to permanently remove this account?');
    });
    
    $jq('.deleteLink').click(function() {
      return confirm('Are you sure you want to permanently remove this item?');
    });
  });
</script>
