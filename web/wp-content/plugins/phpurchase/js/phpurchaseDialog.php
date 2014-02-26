<?php

global $wp_db_version;

if(file_exists("../abspath.php")){
  include('../abspath.php');
  require_once( ABS_PATH . "wp-load.php");
}
else{
  require_once dirname(__FILE__) . '/../../../../wp-load.php';
}

require_once(dirname(__FILE__) .  '/../models/product.php');
$product= new PHPurchaseProduct;

$tinyURI = get_bloginfo('wpurl')."/wp-includes/js/tinymce";

?>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>PHPurchase</title>
	<link type="text/css" rel="stylesheet" href="<?php echo plugins_url()."/phpurchase"; ?>/js/phpurchase.css" />
	<link type="text/css" rel="stylesheet" href="<?php echo plugins_url()."/phpurchase"; ?>/views/buttons.css" />
	<script language="javascript" type="text/javascript" src="<?php echo $tinyURI; ?>/tiny_mce_popup.js"></script>
	<script language="javascript" type="text/javascript" src="<?php echo $tinyURI; ?>/utils/mctabs.js"></script>
	<script language="javascript" type="text/javascript" src="<?php echo $tinyURI; ?>/utils/form_utils.js"></script>
	
  <script type="text/javascript" src="<?= get_bloginfo('wpurl')?>/wp-includes/js/jquery/jquery.js"></script>
	<script language="javascript" type="text/javascript">
	<!--
  var $jq = jQuery.noConflict();
	tinyMCEPopup.onInit.add( function(){window.setTimeout(function(){$jq('#productName').focus();},500);} );

	<?php
	$types=''; $options='';
	$products = $product->getModels("where id>0", "order by name");
	if(count($products)):
	  $i=0;
	  foreach($products as $p){
      if($p->itemNumber==""){
        $id=$p->id;
        $type='id';
        $description = "";
      }
      else{
        $id=$p->itemNumber;
        $type='item';
        $description = '(# '.$p->itemNumber.')';
      }
	    
	    $types.= '"'.$type.'", ';
	    $prices.= '"'.$p->price.'", ';
	    $options .= '<option value="'.$id.'">'.$p->name.' '.$description.'</option>';
	    $i++;
	  }
	
	else:
	  $options .= '<option value="">No products</option>';
	endif;
	//$types = substr($types,0,-1);
	echo 'var prodtype=new Array('.$types.'"");';
	echo 'var prodprices=new Array('.$prices.'"");';
	?>

	function init() {
		mcTabs.displayTab('tab', 'panel');
	}
	
	function preview(){
	  var productIndex = $jq("#productName").attr('selectedIndex');
	  
	  var price = "<p style='margin-top:2px;'><label id='priceLabel'>$"+prodprices[productIndex]+"</label></p>";
	  if($jq("input[@name='showPrice']:checked").val()=="no"){
	    price = "";
	  }
	  
	  var style = "";
	  if($jq("#productStyle").val()!="") {
	    style = $jq("#productStyle").val();
	  }
	  
    <?php 
      $setting = new PHPurchaseSetting();
      $cartImgPath = $setting->lookupValue('cart_images_url');
      if($cartImgPath) {
        if(strpos(strrev($cartImgPath), '/') !== 0) {
          $cartImgPath .= '/';
        }
        $buttonPath = $cartImgPath . 'add-to-cart.png';
      }
    ?>

    var button = '';

    <?php if($cartImgPath): ?>
      var buttonPath = '<?php echo $buttonPath ?>';
      button = "<img src='"+buttonPath+"' title='Add to Cart' alt='PHPurchase Add To Cart Button'>";
    <?php else: ?>
      button = "<input type='button' class='PHPurchaseButtonPrimary' value='Add To Cart' />";
    <?php endif; ?>

	  if($jq("#buttonImage").val()!=""){
	    button = "<img src='"+$jq("#buttonImage").val()+"' title='Add to Cart' alt='PHPurchase Add To Cart Button'>";
	  } 
    
    if($jq("input[@name='showPrice']:checked").val()=="only"){
      button= "";
    }
    
    var prevBox = "<div style='"+style+"'>"+price+button+"</div>";
	  
	  $jq("#buttonPreview").html(prevBox);
	}

	function insertProductCode() {
		prod  = $jq("#productName").val();

    showPrice = $jq("input[@name='showPrice']:checked").val();
    if(showPrice == 'no') {
      showPrice = 'showprice="no"';
    }
    else if(showPrice == 'only'){
      showPrice = 'showprice="only"';
    }
    else {
      showPrice = '';
    }

    buttonImage = '';
		if($jq("#buttonImage").val() != "") {
      buttonImage = 'img="' + $jq("#buttonImage").val() + '"';
    }

		type =  prodtype[$jq("#productName").attr("selectedIndex")];
		if($jq("#productStyle").val()!=""){
		  style  = 'style="'+$jq("#productStyle").val()+'"';
		}
		else {
		  style = '';
		}
		html = '&nbsp;[cart-button '+type+'="'+prod+'" '+style+' ' +showPrice+' '+buttonImage+' ]&nbsp;';

		tinyMCEPopup.execCommand("mceBeginUndoLevel");
		tinyMCEPopup.execCommand('mceInsertContent', false, html);
	 	tinyMCEPopup.execCommand("mceEndUndoLevel");
	  tinyMCEPopup.close();
	}
	
	function toggleInsert(){
	  if($jq("#panel2").is(":visible")){
	    $jq("#insertProductButton").hide();
	    
	  }
	  else{
	    $jq("#insertProductButton").show();
	  }
	}
	
	function shortcode(code){
	  html = '&nbsp;['+code+']&nbsp;';

		tinyMCEPopup.execCommand("mceBeginUndoLevel");
		tinyMCEPopup.execCommand('mceInsertContent', false, html);
	 	tinyMCEPopup.execCommand("mceEndUndoLevel");
	  tinyMCEPopup.close();
	}

	function shortcode_wrap(open, close){
	  html = '&nbsp;['+open+"]&nbsp;<br/>[/"+close+']';

		tinyMCEPopup.execCommand("mceBeginUndoLevel");
		tinyMCEPopup.execCommand('mceInsertContent', false, html);
	 	tinyMCEPopup.execCommand("mceEndUndoLevel");
	  tinyMCEPopup.close();
	}
	
	$jq(document).ready(function(){
	  preview();
	  $jq("input").change(function(){preview();});
	  $jq("input").click(function(){preview();});
	})
	
	//-->
	</script>
	<style type="text/css" media="screen">
	 #buttonPreview{
	   padding:5px;
	 }
	</style>
	<base target="_self" />
</head>
<body id="phpurchase" onLoad="tinyMCEPopup.executeOnLoad('init();');" style="display: none">
	<form onSubmit="insertSomething();" action="#">
	<div class="tabs">
		<ul>
			<li id="tab"><span><a href="javascript:mcTabs.displayTab('tab','panel');toggleInsert();"><?php  _e('Pick a product'); ?></a></span></li>
			<li id="tab2"><span><a href="javascript:mcTabs.displayTab('tab2','panel2');toggleInsert();"><?php  _e('Shortcode Reference'); ?></a></span></li>
		</ul>
	</div>
	<div class="panel_wrapper">
		<div id="panel" class="panel current">
			<table border="0" cellspacing="0" cellpadding="2">
				<tr>
					<td class="phplabel"><label for="productName"><?php  _e('Your products:'); ?></label></td>
					<td class="phpinput"><select id="productName" name="productName" onchange="preview();"><?php  echo $options; ?></select>
				</tr>
				<tr>
				  <td class="phplabel"><label for="productStyle"><?php  _e('CSS style:'); ?></label></td>
				  <td class="phpinput"><input id="productStyle" name="productStyle" size="34"></td>
				</tr>
				<tr>
				  <td class="phplabel"><label for="showPrice"><?php  _e('Show price:'); ?></label></td>
          <td class="phpinput">
            <input type='radio' style="border: none;" id="showPrice" name="showPrice" value='yes' checked> Yes
            <input type='radio' style="border: none;" id="showPrice" name="showPrice" value='no'> No
            <input type='radio' style="border: none;" id="showPrice" name="showPrice" value='only'> Price Only
          </td>
				</tr>
				<tr>
				  <td class="phplabel"><label for="buttonImage"><?php  _e('Button path:'); ?></label></td>
				  <td class="phpinput"><input id="buttonImage" name="buttonImage" size="34"></td>
				</tr>
				<tr>
				  <td class="phplabel" valign="top"><label for="buttonImage"><?php  _e('Preview:'); ?></label></td>
				  <td class="" valign="top" id="buttonPreview"> 
				  </td>
				</tr>
			</table>
		</div>
    
    <div id="panel2" class="panel">
      Shortcode Quick Reference:
      
      <table cellpadding="2">
        <tr>
          <td><div class="shortcode" onclick="shortcode('cart');"><a title="Insert [cart]">[cart]</a></div></td>
          <td>The shopping cart</td>
        </tr>
        <tr>
          <td><div class="shortcode" onclick="shortcode('cart mode=&quot;read&quot;');"><a title="Insert [cart mode=&quot;read&quot;]">[cart mode="read"]</a></div></td>
          <td>The shopping cart in read-only mode</td>
        </tr>
        <tr>
          <td><div class="shortcode" onclick="shortcode('clearcart');"><a title="Insert [clearcart]">[clearcart]</a></div></td>
          <td>Clear the contents of the shopping cart</td>
        </tr>
        <tr>
          <td><div class="shortcode" onclick="shortcode('paypalcheckout');"><a title="Insert [paypalcheckout]">[paypalcheckout]</a></div></td>
          <td>PayPal checkout button</td>
        </tr>
        <tr>
          <td><div class="shortcode" onclick="shortcode('paypalprocheckout');"><a title="Insert [paypalprocheckout]">[paypalprocheckout]</a></div></td>
          <td>PayPal Pro checkout form</td>
        </tr>
        <tr>
          <td><div class="shortcode" onclick="shortcode('expresscheckout');"><a title="Insert [expresscheckout]">[expresscheckout]</a></div></td>
          <td>PayPal Express checkout button</td>
        </tr>
        <tr>
          <td><div class="shortcode" onclick="shortcode('authcheckout');"><a title="Insert [authcheckout]">[authcheckout]</a></div></td>
          <td>Authorize.net or Quantum Gateway checkout form</td>
        </tr>
        <tr>
          <td><div class="shortcode" onclick="shortcode('manualcheckout');"><a title="Insert [manualcheckout]">[manualcheckout]</a></div></td>
          <td>Checkout without processing financial transaction</td>
        </tr>
        <tr>
          <td><div class="shortcode" onclick="shortcode('receipt');"><a title="Insert [receipt]">[receipt]</a></div></td>
          <td>Receipt</td>
        </tr>
        <tr>
          <td><div class="shortcode" onclick="shortcode('ipn');"><a title="Insert [ipn]">[ipn]</a></div></td>
          <td>PayPal Instant Notification page</td>
        </tr>
        <tr>
          <td><div class="shortcode" onclick="shortcode('shoppingcart');"><a title="Insert [shoppingcart]">[shoppingcart]</a></div></td>
          <td>Shopping cart widget</td>
        </tr>
        <tr>
          <td colspan="2">&nbsp;</td>
        </tr>
        
        <?php if(PHPURCHASEPRO): ?>
        <!-- Professinal -->
        <tr>
          <td colspan="2"><strong>PHPurchase Professional Shortcodes</strong></td>
        </tr>
        <tr>
          <td><div class="shortcode" onclick="shortcode('account-create');"><a title="Insert [account-create]">[account-create]</a></div></td>
          <td>Create membership account</td>
        </tr>
        <tr>
          <td><div class="shortcode" onclick="shortcode('account-login');"><a title="Insert [account-login]">[account-login]</a></div></td>
          <td>Member login form</td>
        </tr>
        <tr>
          <td><div class="shortcode" onclick="shortcode('account-billing');"><a title="Insert [account-billing]">[account-billing]</a></div></td>
          <td>Member billing form</td>
        </tr>
        <tr>
          <td><div class="shortcode" onclick="shortcode('account-subscriptions');"><a title="Insert [account-subscriptions]">[account-subscriptions]</a></div></td>
          <td>List logged in member's subscriptions</td>
        </tr>
        <tr>
          <td><div class="shortcode" onclick="shortcode_wrap('phprotect item_numbers=&quot;&quot;', 'phprotect');"><a title="Insert [phprotect]">[phprotect]</a></div></td>
          <td>Visitor must subscribe to listed item numbers to see content</td>
        </tr>
        <tr>
          <td><div class="shortcode" onclick="shortcode_wrap('phpromote item_numbers=&quot;&quot;', 'phpromote');"><a title="Insert [phpromote]">[phpromote]</a></div></td>
          <td>Visitor must NOT subscribe to item numbers to see content</td>
        </tr>
        <tr>
          <td><div class="shortcode" onclick="shortcode('phpurchase-logout');"><a title="Insert [phpurchase=logout]">[phpurchase-logout]</a></div></td>
          <td>Log out of PHPurchase membership</td>
        </tr>
        <?php endif; ?>
        
        
      </table>
      
    </div>
	</div>
	<div class="mceActionPanel">
		<div id="insertProductButton" style="float: left">
				<input type="button" id="insert" name="insert" value="<?php  _e('Insert'); ?>" onClick="insertProductCode();" />
		</div>
		<div style="float: right">
			<input type="button" id="cancel" name="cancel" value="<?php  _e('Cancel'); ?>" onClick="tinyMCEPopup.close();" />
		</div>
	</div>
</form>
</body>
</html>
