<?php
require_once(WP_PLUGIN_DIR. "/phpurchase/gateways/abstract.php");

class FreeGateway extends PHPurchaseGatewayAbstract {

  public function setPayment($p) {
    $this->_payment = $p;
    if($p['email'] == '') {
      $this->_errors['Email address'] = "Email address is required";
      $this->_jqErrors[] = "payment-email";
    }

    if($p['phone'] == '') {
      $this->_errors['Phone'] = "Phone number is required";
      $this->_jqErrors[] = "payment-phone";
    }
  }

}
