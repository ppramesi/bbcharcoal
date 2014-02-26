<?php
require_once dirname(__FILE__) .  '/../gateways/paypal.php';

$paypal = new PayPalGateway();
if(SANDBOX) {
  $paypal = new PayPalGateway('https://www.sandbox.paypal.com/cgi-bin/webscr');
}

$paypal->validate($_POST);

/*
$data = array(
  'first_name' => 'Local',
  'las_name' => '(Address Status: XXX)',
  'address_street' => '1234 Test Street',
  'address_city' => 'Richmond',
  'state' => 'VA',
  'zip' => '23227',
  'country' => 'USA',
  'payer_email' => 'test@test.com',
  'shipping' => '2.00',
  'tax' => 1.00,
  'subtotal' => 5,
  'total' => 8,
  'trans_id' => 'test1234',
  'num_cart_items' => 1,
  'item_number1' => 'EX-29',
  'mc_gross_1' => 3,
  'item_name1' => 'Test Item Name',
  'quantity1' => 1,
);

$paypal->saveOrder($data);
*/
?>
