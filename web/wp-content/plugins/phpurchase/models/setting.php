<?php
class PHPurchaseSetting extends PHPurchaseModelAbstract {
  
  public function __construct($id=null) {
    $this->_tableName = PHPurchaseCommon::getTableName('cart_settings');
    parent::__construct($id);
  }
  
  public function save() {
    if(!empty($this->key)) {
      $dbKey = $this->_db->get_var("SELECT `key` from $this->_tableName where `key`='$this->key'");
      if($dbKey) {
        if(!empty($this->value)) {
          $this->_db->update($this->_tableName, 
            array('key'=>$this->key, 'value'=>$this->value),
            array('key'=>$this->key),
            array('%s', '%s'),
            array('%s')
          );
        }
        else {
          $this->_db->query("DELETE from $this->_tableName where `key`='$this->key'");
        }
      }
      else {
        if(!empty($this->value)) {
          $this->_db->insert($this->_tableName, 
            array('key'=>$this->key, 'value'=>$this->value),
            array('%s', '%s')
          );
        }
      }
    }// end if key is not empty
  }// end save
  
  public function lookupValue($key) {
    $value = $this->_db->get_var("SELECT `value` from $this->_tableName where `key`='$key'");
    return empty($value) ? false : $value;
  }
  
}