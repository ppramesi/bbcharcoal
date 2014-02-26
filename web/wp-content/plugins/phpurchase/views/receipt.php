<?php
global $wpdb;

$product = new PHPurchaseProduct();

$order = false;
if(isset($_GET['ouid'])) {
  $order = new PHPurchaseOrder();
  $order->loadByOuid($_GET['ouid']);
  if(empty($order->id)) {
    echo "<h2>This order is no longer in the system</h2>";
    exit();
  }
  $palletMethod = explode(' - ', $order->shipping_method);
  if(strcmp($palletMethod[3], "Pallet") == 0){
    
  }
}elseif(isset($_SESSION['order_id'])) {
  $order = new PHPurchaseOrder($_SESSION['order_id']);
  $palletMethod = explode(' - ', $order->shipping_method);
  // Begin processing affiliate information
  if(!empty($_SESSION['ap_id'])) {
    $referrer = $_SESSION['ap_id'];
  }
  elseif(isset($_COOKIE['ap_id'])) {
    $referrer = $_COOKIE['ap_id'];
  }

  if (!empty($referrer)) {
    PHPurchaseCommon::awardCommission($order->id, $referrer);
  }
  // End processing affiliate information
}

if($_COOKIE['ap_id']) {
  setcookie('ap_id',$referrer, time() - 3600, "/");
  unset($_COOKIE['ap_id']);
}

if($_SESSION['app_id']) {
  unset($_SESSION['app_id']);
}

if(isset($_GET['duid'])) {
  $duid = $_GET['duid'];
  $product = new PHPurchaseProduct();
  if($product->loadByDuid($duid)) {
    $okToDownload = true;
    if($product->download_limit > 0) {
      // Check if download limit has been exceeded
      if($product->countDownloadsForDuid($duid) >= $product->download_limit) {
        $okToDownload = false;
      }
    }
    
    if($okToDownload) {
      $data = array(
        'duid' => $duid,
        'downloaded_on' => date('Y-m-d H:i:s'),
        'ip' => $_SERVER['REMOTE_ADDR']
      );
      $downloadsTable = PHPurchaseCommon::getTableName('downloads');
      $wpdb->insert($downloadsTable, $data, array('%s', '%s', '%s'));
      
      $setting = new PHPurchaseSetting();
      $dir = $setting->lookupValue('product_folder');
      $path = $dir . DIRECTORY_SEPARATOR . $product->download_path;
      downloadFile($path);
    }
    else {
      echo "You have exceeded the maximum number of downloads for this product";
    }
    exit();
  }
}
  

function downloadFile($path) {
  // Erase and close all output buffers
  while (@ob_end_clean());
  
  // Get the name of the file to be downloaded
  $fileName = basename($path);
  
  // This is required for IE, otherwise Content-disposition is ignored
  if(ini_get('zlib.output_compression')) {
    ini_set('zlib.output_compression', 'Off');
  }
  
  $bytes = 'unknown';
  if(substr($path, 0, 4) == 'http') {
    $bytes = remoteFileSize($path);
  }
  else {
    $bytes = filesize($path);
  }
  
  ob_start();
  header("Pragma: public"); // required
  header("Expires: 0");
  header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
  header("Cache-Control: private",false); // required for certain browsers 
  //header("Content-Type: $ctype");
  header("Content-Type: application/octet-stream;");
  header("Content-Disposition: attachment; filename=\"".$fileName."\";" );
  header("Content-Transfer-Encoding: binary");
  header("Content-Length: $bytes");
  
  
  //open the file and stream download
  if($fp = fopen($path, 'rb')) {
    while(!feof($fp)) {
      //reset time limit for big files
      @set_time_limit(0);
      echo fread($fp, 1024*8);
      flush();
      ob_flush();
    }
    fclose($fp);
  }
  
}

function remoteFileSize($remoteFile) {
  $ch = curl_init($remoteFile);
  curl_setopt($ch, CURLOPT_NOBODY, true);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_HEADER, true);
  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); 
  $data = curl_exec($ch);
  curl_close($ch);
  $contentLength = 'unknown';
  if ($data !== false) {
    if (preg_match('/Content-Length: (\d+)/', $data, $matches)) {
      $contentLength = (int)$matches[1];
    }
  }
  return $contentLength;
}
?>

<?php  if($order !== false): ?>
<div class="receip-wrapper">
<h2>Order Number: <?php echo $order->trans_id ?></h2>

<table border="0" cellpadding="0" cellspacing="0">
  <tr>
    <td valign="top">
      <p>
        <strong>Billing Information</strong><br/>
      <?php echo $order->bill_first_name ?> <?php echo $order->bill_last_name ?><br/>
      <?php echo $order->bill_address ?><br/>
      <?php if(!empty($order->bill_address2)): ?>
        <?php echo $order->bill_address2 ?><br/>
      <?php endif; ?>

      <?php if(!empty($order->bill_city)): ?>
        <?php echo $order->bill_city ?> <?php echo $order->bill_state ?>, <?php echo $order->bill_zip ?><br/>
      <?php endif; ?>
      
      <?php if(!empty($order->bill_country)): ?>
        <?php echo $order->bill_country ?><br/>
      <?php endif; ?>
      </p>
    </td>
    <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
    <td valign="top">
      <p><strong>Contact Information</strong><br/>
      <?php if(!empty($order->phone)): ?>
        Phone: <?php echo PHPurchaseCommon::formatPhone($order->phone) ?><br/>
      <?php endif; ?>
      Email: <?php echo $order->email ?><br/>
      Date: <?php echo date('m/d/Y g:i a', strtotime($order->ordered_on)) ?>
      </p>
    </td>
  </tr>
  <tr>
    <td>
      <?php if($order->shipping_method != 'None'): ?>
      <br/><p>
        <strong>Shipping Information</strong><br/>
      <?php echo $order->ship_first_name ?> <?php echo $order->ship_last_name ?><br/>
      <?php echo $order->ship_address ?><br/>
      
      <?php if(!empty($order->ship_address2)): ?>
        <?php echo $order->ship_address2 ?><br/>
      <?php endif; ?>
      
      <?php if($order->ship_city != ''): ?>
        <?php echo $order->ship_city ?> <?php echo $order->ship_state ?>, <?php echo $order->ship_zip ?><br/>
      <?php endif; ?>
      <?php if(strcmp($palletMethod[3], "Pallet") == 0){?>
		</p>
	</td><td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
		<td><p>
		<strong>Shipped With</strong><br/>
		<?php
			$stuff = freightHelper::getFrOrderFromDB($order->id);
			$stuffstr = "<br>";
			if(is_null($stuff)){
				$stuffstr .= "Can't find the shipping info for this order<br>";
			}else{
				$stuffstr .= "Carrier: " . $stuff["Carrier"] . "<br>";
				$stuffstr .= "Transit: " . $stuff["Transit"] . "<br>";
				$stuffstr .= "Pickup Time: " . $stuff["Pickup"] . "<br>";
				$stuffstr .= "Total Shipping Price: " . $stuff["Price"] . "<br>";
				$stuffstr .= "Shipping Id: " . $stuff["Sid"] . "<br>";
			}
			echo $stuffstr; 
		?>
		<?php }else{ ?>
        <br/><em>Delivery via: <?php echo $order->shipping_method;?></em><br/><?php } ?>
        </p>
        <?php endif; ?>
    </td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
  </tr>
</table>
</div>


<table id='viewCartTable' cellspacing="0" cellpadding="0">
  <tr>
    <th style='text-align: left;'>Product</th>
    <th style='text-align: center;'>Quantity</th>
    <th style='text-align: left;'>Item&nbsp;Price</th>
    <th style='text-align: left;'>Item&nbsp;Total</th>
  </tr>

  <?php foreach($order->getItems() as $item): ?>
    <?php 
      $product->load($item->product_id);
      $price = $item->product_price * $item->quantity;
    ?>
    <tr>
      <td>
        <?php echo nl2br($item->description) ?>
        <?php
          $product->load($item->product_id);
          if($product->isDigital()) {
            $receiptPage = get_page_by_path('store/receipt');
            $receiptPageLink = get_permalink($receiptPage);
            $receiptPageLink .= (strstr($receiptPageLink, '?')) ? '&duid=' . $item->duid : '?duid=' . $item->duid;
            echo "<br/><a href='$receiptPageLink'>Download</a>";
          }
        ?>
        
      </td>
      <td style='text-align: center;'><?php echo $item->quantity ?></td>
      <td><?php echo CURRENCY_SYMBOL ?><?php echo number_format($item->product_price, 2) ?></td>
      <td><?php echo CURRENCY_SYMBOL ?><?php echo number_format($item->product_price * $item->quantity, 2) ?></td>
    </tr>
    <?php
      if(!empty($item->form_entry_ids)) {
        $entries = explode(',', $item->form_entry_ids);
        foreach($entries as $entryId) {
          if(class_exists('RGFormsModel')) {
            if(RGFormsModel::get_lead($entryId)) {
              echo "<tr><td colspan='4'><div class='PHPurchaseGravityFormDisplay'>" . displayGravityForm($entryId) . "</div></td></tr>";
            }
          }
          else {
            echo "<tr><td colspan='5' style='color: #955;'>This order requires Gravity Forms in order to view all of the order information</td></tr>";
          }
        }
      }
    ?>
  <?php endforeach; ?>

  <tr>
    <td class='noBorder' colspan='1'>&nbsp;</td>
    <td class='noBorder' colspan="1" style='text-align: center;'>&nbsp;</td>
    <td class='noBorder' colspan="1" style='text-align: right; font-weight: bold;'>Subtotal:</td>
    <td class='noBorder' colspan="1" style="text-align: left; font-weight: bold;"><?php echo CURRENCY_SYMBOL ?><?php echo $order->subtotal; ?></td>
  </tr>
  
  <?php if($order->shipping_method != 'None' && $order->shipping_method != 'Download'): ?>
  <tr>
    <td class='noBorder' colspan='1'>&nbsp;</td>
    <td class='noBorder' colspan="1" style='text-align: center;'>&nbsp;</td>
    <td class='noBorder' colspan="1" style='text-align: right; font-weight: bold;'>Shipping:</td>
    <td class='noBorder' colspan="1" style="text-align: left; font-weight: bold;"><?php echo CURRENCY_SYMBOL ?><?php echo $order->shipping; ?></td>
  </tr>
  <?php endif; ?>
  
  <?php if($order->discount_amount > 0): ?>
    <tr>
      <td class='noBorder' colspan='2'>&nbsp;</td>
      <td class='noBorder' colspan="1" style='text-align: right; font-weight: bold;'>Discount:</td>
      <td class='noBorder' colspan="1" style="text-align: left; font-weight: bold;">-<?php echo CURRENCY_SYMBOL ?><?php echo number_format($order->discount_amount, 2); ?></td>
    </tr>
  <?php endif; ?>
  
  <?php if($order->tax > 0): ?>
    <tr>
      <td class='noBorder' colspan='2'>&nbsp;</td>
      <td class='noBorder' colspan="1" style='text-align: right; font-weight: bold;'>Tax:</td>
      <td class='noBorder' colspan="1" style="text-align: left; font-weight: bold;"><?php echo CURRENCY_SYMBOL ?><?php echo number_format($order->tax, 2); ?></td>
    </tr>
  <?php endif; ?>
  
  <tr>
    <td class='noBorder' colspan='2' style='text-align: center;'>&nbsp;</td>
    <td class='noBorder' colspan="1" style='text-align: right; font-weight: bold;'>Total:</td>
    <td class='noBorder' colspan="1" style="text-align: left; font-weight: bold;"><?php echo CURRENCY_SYMBOL ?><?php echo number_format($order->total, 2); ?></td>
  </tr>
</table>

<p><a href='#' id="print_version">Printer Friendly Receipt</a></p>

<?php
  $receiptLog = fopen(LOG_BASE . "receiptShipping.log", "a");
  $count = 0;
  //fwrite($receiptLog, $count . "\n");
  $retUpsMsg = isset($_SESSION['freight_sinfo']) ? false : true;
  $msg = PHPurchaseCommon::getEmailReceiptMessage($order, $retUpsMsg);
  $setting = new PHPurchaseSetting();
  $to = $order->email;
  //fwrite($receiptLog, $msg . "\n");
  $count += 1;
  $subject = $setting->lookupValue('receipt_subject');
  $headers = 'From: '. $setting->lookupValue('receipt_from_name') .' <' . $setting->lookupValue('receipt_from_address') . '>' . "\r\n\\";
  $msgIntro = $setting->lookupValue('receipt_intro');
  //fwrite($receiptLog, $count);
  $count += 1;
  $testingMsg = $msg[0];
  $freightShippingInfo = isset($_SESSION['freight_sinfo']) ? $_SESSION['freight_sinfo'] : "";
  $finMsg = str_replace("BILLING INFORMATION", $freightShippingInfo . "BILLING INFORMATION", $testingMsg);
  $msg[0] = $finMsg;
  //fwrite($receiptLog, $count);
  $count += 1;
  //unset($_SESSION['freight_sinfo']);
  
  //Disable mail headers if the WP Mail SMTP plugin is in use.
  if(function_exists('wp_mail_smtp_activate')) { $headers = null; }
  
  if(!isset($_GET['ouid'])) {
    //fwrite($receiptLog, $count);
    $count += 1;
	$freightToAdd = $_SESSION['freight_sinfo'];
    $isSent = PHPurchaseCommon::mail($to, $subject, $msg[0], $headers);
    if(!$isSent) {
      PHPurchaseCommon::log("Mail not sent to: $to");
    } else {
	  PHPurchaseCommon::log("Mail sent to : $to");
	}	
    
    $others = $setting->lookupValue('receipt_copy');
    if($others) {
      $list = explode(',', $others);
	$noShipping = '';
	  if(empty($order->shipping_results)) {
		$noShipping = '(FAILED TO SHIP)';
	  }
      $msg = "THIS IS A COPY OF THE RECEIPT ".$noShipping."\n\n$msg[0]\n\n$msg[1]";
	  //fwrite($receiptLog, $count);
    $count += 1;
      foreach($list as $e) {
        $e = trim($e);
        $isSent = wp_mail($e, $subject, $msg, $headers);
		//fwrite($receiptLog, $count);
    $count += 1;
        if(!$isSent) {
          PHPurchaseCommon::log("Mail not sent to: $e");
        }
      }
    } 
  }
  // Erase the shopping cart from the session at the end of viewing the receipt
  unset($_SESSION['PHPurchaseCart']);
  echo '<script type="text/javascript">document.getElementById(\'shopping-cart-button-float\').innerHTML = \'\';</script>';
?>
<?php else: ?>
  <p>Receipt not available</p>
<?php endif; ?>


<?php
  if($order !== false) {
    $printView = PHPurchaseCommon::getView('views/receipt_print_version.php', array('order' => $order));
    $printView = str_replace("\n", '', $printView);
    $printView = str_replace("'", '"', $printView);
  }
?>

<script type="text/javascript">
//<![CDATA[
jQuery(document).ready(function($) {
  $('#print_version').click(function() {
    myWindow = window.open('','Your_Receipt','resizable=yes,scrollbars=yes,width=550,height=700');
    //myWindow.document.body.innerHTML = '<?php echo $printView; ?>';
    myWindow.document.write('<?php echo $printView; ?>');
    return false;
  });
  
});
//]]>
</script>
<!-- Google Code for Purchase/ Sale Conversion Page -->
<script type="text/javascript">
/* <![CDATA[ */
var google_conversion_id = 1009375175;
var google_conversion_language = "en";
var google_conversion_format = "2";
var google_conversion_color = "ffffff";
var google_conversion_label = "s8D0CJnJ9gIQx6-n4QM";
var google_conversion_value = 0;
/* ]]> */
</script>
<script type="text/javascript" src="http://www.googleadservices.com/pagead/conversion.js">
</script>
<noscript>
<div style="display:inline;">
<img height="1" width="1" style="border-style:none;" alt="" src="http://www.googleadservices.com/pagead/conversion/1009375175/?label=s8D0CJnJ9gIQx6-n4QM&amp;guid=ON&amp;script=0"/>
</div>
</noscript>