<?php
$msg = '';
$product = new PHPurchaseProduct();
$setting = new PHPurchaseSetting();
$adminUrl = get_bloginfo('wpurl') . '/wp-admin/admin.php';
$pages = get_pages('post_type=page&orderby=title');
$args = array('post_type' => 'attachment', 'numberposts' => -1, 'post_status' => null, 'post_parent' => null, 'orderby' => 'title', 'order'=>'ASC'); 
$attachments = get_posts($args);

if($_SERVER['REQUEST_METHOD'] == "POST" && $_POST['phpurchase-action'] == 'save product') {
  // Check for file upload
  if(strlen($_FILES['product']['tmp_name']['upload']) > 2) {
    $dir = $setting->lookupValue('product_folder');
    if($dir) {
      $filename = preg_replace('/\s/', '_', $_FILES['product']['name']['upload']);
      $path = $dir . DIRECTORY_SEPARATOR . $filename;
      $src = $_FILES['product']['tmp_name']['upload'];
      if(move_uploaded_file($src, $path)) {
        $_POST['product']['download_path'] = $filename;
      }
      else {
        $msg = "<p><font color='red'>Could not upload file from $src to $path</font></p>";
        $msg .= "<pre>" . print_r($_FILES, true) . "</pre>";
      }
    }
  }
  
  // Configure free trial settings
  $freeTrial = '0 Days';
  if(isset($_POST['product']['start_number']) && is_numeric($_POST['product']['start_number']) && $_POST['product']['start_number'] > 0) {
    $freeTrial = $_POST['product']['start_number'] . ' ' . $_POST['product']['start_unit'];
  }
  $_POST['product']['free_trial'] = $freeTrial;
  
  $product->setData($_POST['product']);
  $errors = $product->validate();
  if(empty($errors)) {
    $product->save();
    // Check for problems with the digital file before clearing.
    $dlOk = true;
    if(!empty($product->download_path)) {
      $dir = $setting->lookupValue('product_folder');
      $dlOk = file_exists($dir . DIRECTORY_SEPARATOR . $product->download_path);
    }

    if($dlOk) {
      $product->clear();
    }
  }
  else {
    echo "<div style='margin: 0px 50px 10px 5px;'>";
    echo PHPurchaseCommon::showErrors($errors, "<p><b>The product coud not be saved for the following reasons:</b></p>");
    echo "</div>";
  }
  
}
elseif(isset($_GET['task']) && $_GET['task'] == 'edit' && isset($_GET['id']) && $_GET['id'] > 0) {
  $id = PHPurchaseCommon::getVal('id');
  $product->load($id);
}
elseif(isset($_GET['task']) && $_GET['task'] == 'delete' && isset($_GET['id']) && $_GET['id'] > 0) {
  $id = PHPurchaseCommon::getVal('id');
  $product->load($id);
  $product->deleteMe();
  $product->clear();
}
elseif(isset($_GET['task']) && $_GET['task'] == 'xdownload' && isset($_GET['id']) && $_GET['id'] > 0) {
  // Load the product
  $id = PHPurchaseCommon::getVal('id');
  $product->load($id);
  
  // Delete the download file
  $setting = new PHPurchaseSetting();
  $dir = $setting->lookupValue('product_folder');
  $path = $dir . DIRECTORY_SEPARATOR . $product->download_path;
  unlink($path);
  
  // Clear the name of the download file from the object and database
  $product->download_path = '';
  $product->save();
}

if(!empty($msg)) {echo $msg;}
?>

<form action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>" method='post' enctype="multipart/form-data">
  <input type='hidden' name='phpurchase-action' value='save product' />
  <input type='hidden' name='product[id]' value='<?php echo $product->id ?>' />
  <div id="widgets-left" style="margin-right: 50px;">
    <div id="available-widgets">
    
      <div class="widgets-holder-wrap">
        <div class="sidebar-name">
          <div class="sidebar-name-arrow"><br/></div>
          <h3>Product <span><img class="ajax-feedback" alt="" title="" src="images/wpspin_light.gif"/></span></h3>
        </div>
        <div class="widget-holder">
          <div>
            <ul>
              <li>
                <label class="med" for='product[name]'>Product name:</label>
                <input class="long" type='text' name='product[name]' id='product_name' value='<?php echo $product->name ?>' />
              </li>
			  <li>
                <label class="med" style="vertical-align:top;" for='product[description]'>Product description:</label>
                <textarea class="long" type='text' name='product[description]' id='product_description' cols="36" rows="6" /><?php echo $product->description ?></textarea>
              </li>
			  <li>
				<label class="med" for='product[attachment_id]'>Image:</label>
				<select name='product[attachment_id]' id="product-image">
				<?php
					echo '<option value="" selected></option>';
					foreach($attachments AS $attachment) {
						$listAttachment[$attachment->ID] = $attachment->post_title;
						$selected = '';
						if($attachment->ID == $product->attachment_id) {
							$selected = 'selected';
						}
						echo '<option value="'.$attachment->ID.'" '.$selected.'>'.$attachment->post_title.'</option>';
					}
				?>
				</select>
			  </li>
			  <li>
				<label class="med" for='product[description]'>Page:</label>
				<select name='product[post_id]' id="product-page">
				<?php
					echo '<option value="" selected></option>';
					foreach($pages AS $page) {
						$listPage[$page->ID] = $page->post_title;
						$selected = '';
						if($page->ID == $product->post_id) {
							$selected = 'selected';
						}
						echo '<option value="'.$page->ID.'" '.$selected.'>'.$page->post_title.'</option>';
					}
				?>
				</select>
			  </li>
			  <li>
				<label class="med" for='product[ordinal]'>List order:</label>
                <input type='text' name='product[ordinal]' id='product_ordinal' value='<?php echo $product->ordinal ?>' />
			  </li>
              <li>
                <label class="med" for='product[item_number]'>Item number:</label>
                <input type='text' name='product[item_number]' id='product_model_number' value='<?php echo $product->itemNumber ?>' />
                <span class="label_desc">Unique item number required.</span>
              </li>
              <li>
                <label class="med" for='product[price]'>Price:</label>
                <?php echo CURRENCY_SYMBOL ?><input type='text' style="width: 75px;" name='product[price]' value='<?php echo $product->price ?>'>
              </li>
              <li>
                <label class="med" for='product[taxable]'>Taxed:</label>
                <select name='product[taxable]'>
                  <option value='1' <?php echo ($product->taxable == 1)? 'selected' : '' ?>>Yes</option>
                  <option value='0' <?php echo ($product->taxable == 0)? 'selected' : '' ?>>No</option>
                </select>
                <span class="label_desc">Do you want to collect sales tax when this item is purchased?</span>
              </li>
              <li>
                <label class="med" for='product[shipped]'>Shipped:</label>
                <select name='product[shipped]'>
                  <option value='1' <?php echo ($product->shipped === '1')? 'selected' : '' ?>>Yes</option>
                  <option value='0' <?php echo ($product->shipped === '0')? 'selected' : '' ?>>No</option>
                </select>
                <span class="label_desc">Does this product require shipping?</span>
              </li>
              <li>
                <label class="med" for="product[weight]">Weight:</label>
                <input type="text" name="product[weight]" value="<?php echo $product->weight ?>" size="6" id="product_weight" /> lbs 
                <p class="label_desc">Shipping weight in pounds. Used for live rates calculations. Weightless items ship free.<br/>If using live rates and you want an item to have free shipping you can enter 0 for the weight.</p>
              </li>
              <li>
                <label class="med" for='product[max_qty]'>Max quantity:</label>
                <input type="text" style="width: 50px;" name='product[max_quantity]' value='<?php echo $product->maxQuantity ?>' />
                <p class="label_desc">Limit the quantity that can be added to the cart. Set to 0 for unlimited.<br/>If you are selling digital products or subscriptions you may want to limit the quantity of the product to 1.</p>
              </li>
              <?php if(class_exists('RGForms') && PHPURCHASEPRO): ?>
                <li>
                  <label class="med">Attach Gravity Form:</label>
                  <select name='product[gravity_form_id]' id="gravity_form_id">
                    <option value='0'>None</option>
                    <?php
                      global $wpdb;
                      $forms = PHPurchaseCommon::getTableName('rg_form', '');
                      $sql = "SELECT id, title from $forms where is_active=1 order by title";
                      $results = $wpdb->get_results($sql);
                      foreach($results as $r) {
                        $selected = ($product->gravityFormId == $r->id) ? 'selected="selected"' : '';
                        ?>
                        <option value="<?php echo $r->id ?>" <?php echo $selected ?>><?php echo $r->title ?></option>
                        <?php
                      }
                    ?>
                  </select>
                </li>
                <?php if($product->gravityFormId > 0 || 1): ?>
                  <li>
                    <label class="med">Quantity field:</label>
                    <select name="product[gravity_form_qty_id]" id="gravity_form_qty_id">
                      <option value='0'>None</option>
                      <?php
                        $gr = new GravityReader($product->gravityFormId);
                        $fields = $gr->getStandardFields();
                        foreach($fields as $id => $label) {
                          $selected = ($product->gravityFormQtyId == $id) ? 'selected="selected"' : '';
                          echo "<option value='$id' $selected>$label</option>\n";
                        }
                      ?>
                    </select>
                    <span class="label_desc">Use one of the Gravity Form fields as they quantity for your product.</span>
                  </li>
                <?php endif; ?>
                
              <?php endif; ?>
            </ul>
          </div>
        </div>
      </div>
    
      <div class="widgets-holder-wrap <?php echo strlen($product->download_path) ? '' : 'closed'; ?>">
        <div class="sidebar-name">
          <div class="sidebar-name-arrow"><br/></div>
          <h3>Digital Product Options <span><img class="ajax-feedback" alt="" title="" src="images/wpspin_light.gif"/></span></h3>
        </div>
        <div class="widget-holder">
          <div>
            <?php
              $setting = new PHPurchaseSetting();
              $dir = $setting->lookupValue('product_folder');
              if($dir) {
                if(!file_exists($dir)) echo "<p style='color: red;'><strong>WARNING:</strong> The digital products folder does not exist. 
                Please update your <strong>Digital Product Settings</strong> on the 
                <a href='?page=phpurchase-settings'>settings page</a>.<br/>$dir</p>";
                elseif(!is_writable($dir)) echo "<p style='color: red;'><strong>WARNING:</strong> WordPress cannot write to your digital products folder.
                  Please make your digital products file writeable or change your digital products folder in the <strong>Digital Product Settings</strong> on the 
                  <a href='?page=phpurchase-settings'>settings page</a>.<br/>$dir</p>";
              }
              else {
                echo "<p style='color: red;'>
                Before you can upload your digital product, please specify a folder for your digital products in the<br/>
                <strong>Digital Product Settings</strong> on the 
                <a href='?page=phpurchase-settings'>settings page</a>.</p>";
              }
            ?>
            <ul>
              <li>
                <label class="med" for='product[upload]'>Upload product:</label>
                <input class="long" type='file' name='product[upload]' id='product_upload' value='' />
                <p class="label_desc">If you FTP your product to your product folder, enter the name of the file you uploaded in the field below.</p>
              </li>
              <li>
                <label class="med" for='product[download_path]'><em>or</em> File name:</label>
                <input style="width: 80%" type='text' name='product[download_path]' id='product_download_path' value='<?php echo $product->download_path ?>' />
                <?php
                  if(!empty($product->download_path)) {
                    $file = $dir . DIRECTORY_SEPARATOR . $product->download_path;
                    if(file_exists($file)) {
                      echo "<p class='label_desc'><a href='?page=phpurchase-products&task=xdownload&id=" . $product->id . "'>Delete this file from the server</a></p>";
                    }
                    else {
                      echo "<p class='label_desc' style='color: red;'><strong>WARNING:</strong> This file is not in your products folder";
                    }
                  }
                  
                ?>
              </li>
              <li>
                <label class="med" for='product[download_limit]'>Download limit:</label>
                <input style="width: 35px;" type='text' name='product[download_limit]' id='product_download_limit' value='<?php echo $product->download_limit ?>' />
                <span class="label_desc">Max number of times customer may download product. Enter 0 for no limit.</span>
              </li>
            </ul>
            
            <div class="description">
            <p><strong>NOTE:</strong> There are several settings built into PHP that affect the size of the files you can upload. 
              These settings are set by your web host and can usually be configured for your specific needs. 
              Please contact your web hosting company if you need help change any of the settings below.</p>
            <p>If you need to upload a file larger than what is allowed via this form, you can FTP the file to the products folder 
              <?php echo $dir ?> then enter the name of the file in the "File name" field above.</p>
            <p>Max Upload Filesize: <?php echo ini_get('upload_max_filesize');?>B<br/>Max Postsize: <?php echo ini_get('post_max_size');?>B</p>
            </div>
          </div>
        </div>
      </div>
    
      <div class="widgets-holder-wrap <?php echo strlen($product->options_1) ? '' : 'closed'; ?>">
        <div class="sidebar-name">
          <div class="sidebar-name-arrow"><br/></div>
          <h3>Product Variations <span><img class="ajax-feedback" alt="" title="" src="images/wpspin_light.gif"/></span></h3>
        </div>
        <div class="widget-holder">
          <div>
            <ul>
              <li>
                <label class="med" for='product[options_1]'>Option Group 1:</label>
                <input style="width: 80%" type='text' name='product[options_1]' id='product_options_1' value="<?php echo $product->options_1 ?>" />
                <p class="label_desc">Small, Medium +$2.00, Large +$4.00</p>
              </li>
              <li>
                <label class="med" for='product[options_2]'>Option Group 2:</label>
                <input style="width: 80%" type='text' name='product[options_2]' id='product_options_1' value="<?php echo $product->options_2 ?>" />
                <p class="label_desc">Red, White, Blue</p>
              </li>
              <li>
                <label class="med" for='product[custom]'>Custom field:</label>
                <select name='product[custom]' id='product_custom'>
                  <option value="none">No custom field</option>
                  <option value="single" <?php echo ($product->custom == 'single')? 'selected' : '' ?>>Single line text field</option>
                  <option value="multi" <?php echo ($product->custom == 'multi')? 'selected' : '' ?>>Multi line text field</option>
                </select>
                <p class="label_desc">Include a free form text area so your buyer can provide custom information such as a name to engrave on the product.</p>
              </li>
              <li>
                <label class="med" for='product[custom_desc]'>Instructions:</label>
                <input style="width: 80%" type='text' name='product[custom_desc]' id='product_custom_desc' value='<?php echo $product->custom_desc ?>' />
                <p class="label_desc">Tell your buyer what to enter into the custom text field. (Ex. Please enter the name you want to engrave)</p>
              </li>
            </ul>
          </div>
        </div>
      </div>
      
      <?php if(PHPURCHASEPRO): ?>
      <div class="widgets-holder-wrap <?php echo $product->recurring_interval > 0 ? '' : 'closed'; ?>">
        <div class="sidebar-name">
          <div class="sidebar-name-arrow"><br/></div>
          <h3>Subscriptions<span><img class="ajax-feedback" alt="" title="" src="images/wpspin_light.gif"/></span></h3>
        </div>
        <div class="widget-holder">
          <div>
            <?php
              $subscriptionsAvailable = false;
              $url = $setting->lookupValue('auth_url');
              if($url): ?>
                  <ul>
                    <li>
                      <label class="med" for='product[recurring_interval]'>Charge every:</label>
                      <input style="width: 30px;" type='text' name='product[recurring_interval]' value='<?php echo $product->recurring_interval; ?>' />
                      <select name='product[recurring_unit]'>
                        <option value='days' <?php echo ($product->recurring_unit == 'days')? 'selected' : '' ?>>Days</option>
                        <option value='weeks' <?php echo ($product->recurring_unit == 'weeks')? 'selected' : '' ?>>Weeks</option>
                        <option value='months' <?php echo ($product->recurring_unit == 'months')? 'selected' : '' ?>>Months</option>
                      </select>
                      <p class="label_desc">How often do you want to bill your customer?</p>
                    </li>
                    <li>
                      <label class="med" for='product[recurring_occurrences]'>Stop charging after:</label>
                      <input style="width: 30px;" type='text' name='product[recurring_occurrences]' 
                        value='<?php echo $product->recurring_occurrences; ?>' /> charges
                      <p class="label_desc">Enter <strong>0</strong> for continuous billing</p>
                    </li>
                    <li>
                      <label class="med" for='product[start_date]'>Start charging after:</label>
                      <input style="width: 30px;" type='text' name='product[start_number]' value='<?php echo $product->getFreeTrialNumber(); ?>' />
                      <select name='product[start_unit]'>
                        <option value='days'>Days</option>
                        <option value='weeks' <?php echo ($product->getFreeTrialUnit() == 'weeks')? 'selected' : '' ?>>Weeks</option>
                        <option value='months' <?php echo ($product->getFreeTrialUnit() == 'months')? 'selected' : '' ?>>Months</option>
                      </select>
                      <p class="label_desc">Set to <strong>0 Days</strong> to start charging on the date of purchase with no free trial.<br/>
                        Or offer your customers a free trial period by not starting to charge them right away.<br/></p> 
                    </li>
                    <li>
                      <label class="med" for='product[allow_cancel]'>Let buyer cancel:</label>
                      <input type="radio" name="product[allow_cancel]" value="1" <?php echo ($product->allow_cancel == '1')? 'checked="checked"' : '' ?>> Yes
                      <input type="radio" name="product[allow_cancel]" value="0" <?php echo ($product->allow_cancel == '0')? 'checked="checked"' : '' ?>> No
                      <p class="label_desc">Select "yes" if you want to allow your buyer to cancel their own account.<br/>
                        If you are selling a payment plan, you probably want to select "no".</p>
                    </li>
                  </ul>
            <?php else: ?>
              <p>For maximum security, PHPurchase keeps subscription data on either the 
                <a href="https://ems.authorize.net/oap/home.aspx?SalesRepID=98&ResellerID=15642">Authorize.net</a> Customer Information Manager or in 
                <a href="http://www.cdgcommerce.com/index.php?R=1794">Quantum Gateway</a> Secure Vault. You must first 
                <a href="<?php echo $adminUrl ?>?page=phpurchase-settings#gateway">configure your gateway settings</a> before the subscription features are available.</p>
            <?php endif; ?>
          </div>
        </div>
      </div>
      <?php endif; ?>
      
      <div style="padding: 0px;">
        <input type='submit' name='submit' class="button-primary" style='width: 60px;' value='Save' />

        <?php if($product->id > 0): ?>
        <a href='?page=phpurchase-products' class='button-secondary linkButton' style="">Cancel</a>
        <?php endif; ?>
      </div>
  
    </div>
  </div>

</form>
    
  
<?php
  $product = new PHPurchaseProduct();
  $products = $product->getModels('where id>0', 'order by name');
  if(count($products)):
?>
  <table class="widefat" style="margin-top: 20px;">
  <thead>
  	<tr>
  		<th>ID</th>
  		<th>Item Number</th>
  		<th>Product Name</th>
  		<th>Price</th>
		<th>Page</th>
		<th>Image</th>
		<th>Order</th>
  		<th>Taxed</th>
  		<th>Shipped</th>
  		<th>Actions</th>
  	</tr>
  </thead>
  <tfoot>
      <tr>
    		<th>ID</th>
    		<th>Item Number</th>
    		<th>Product Name</th>
    		<th>Price</th>
			<th>Page</th>
			<th>Image</th>
			<th>Order</th>
    		<th>Taxed</th>
    		<th>Shipped</th>
    		<th>Actions</th>
    	</tr>
  </tfoot>
  <tbody>
    <?php foreach($products as $p): ?>
     <tr>
       <td><?php echo $p->id ?></td>
       <td><?php echo $p->itemNumber ?></td>
       <td><?php echo $p->name ?></td>
       
       <?php if($p->recurring_interval > 0): ?>
         <td><?php echo CURRENCY_SYMBOL ?><?php echo $p->price ?> / <?php echo $p->getRecurringIntervalDisplay() ?></td>
       <?php else: ?>
         <td><?php echo CURRENCY_SYMBOL ?><?php echo $p->price ?></td>
       <?php endif; ?>
	   <td><?php echo $listPage[$p->post_id]; ?></td>
	   <td><?php echo $listAttachment[$p->attachment_id]; ?></td>
       <td><?php echo $p->ordinal; ?></td>
       <td><?php echo $p->taxable? ' Yes' : 'No'; ?></td>
       <td><?php echo $p->shipped? ' Yes' : 'No'; ?></td>
       <td>
         <a href='?page=phpurchase-products&task=edit&id=<?php echo $p->id ?>'>Edit</a> | 
         <a class='delete' href='?page=phpurchase-products&task=delete&id=<?php echo $p->id ?>'>Delete</a>
       </td>
     </tr>
    <?php endforeach; ?>
  </tbody>
  </table>
<?php endif; ?>

<script type='text/javascript'>
  $jq = jQuery.noConflict();
  
  $jq('.sidebar-name').click(function() {
    $jq(this.parentNode).toggleClass("closed");
  });
  
  $jq('.delete').click(function() {
    return confirm('Are you sure you want to delete this item?');
  });
  
  // TODO: add ajax to populate gravity_form_qty_id when gravity_form_id changes
  $jq('#gravity_form_id').change(function() {
    var gravityFormId = $jq('#gravity_form_id').val();
    $jq.get(ajaxurl, { 'action': 'update_gravity_product_quantity_field', 'formId': gravityFormId}, function(myOptions) {
      $jq('#gravity_form_qty_id >option').remove();
      $jq('#gravity_form_qty_id').append( new Option('None', 0) );
      $jq.each(myOptions, function(val, text) {
          $jq('#gravity_form_qty_id').append( new Option(text,val) );
      });
    });
  });
  
</script>
