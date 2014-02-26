<?php
class GravityReader {
  
  /**
   * An array holding the Gravity Forms field array
   * @var array
   * @access private
   */
  private $_fields;
  
  public function __construct($formId=null) {
    $this->_fields = array();
    if(is_numeric($formId) && $formId > 0) {
      $this->load($formId);
    }
  }
  
  /**
   * Load the form fields for the given form id into the private $_fields array
   */
  public function load($formId) {
    global $wpdb;
    $metaTable = PHPurchaseCommon::getTableName('rg_form_meta', '');
    $sql = "select display_meta from $metaTable where form_id = $formId";
    $meta = unserialize($wpdb->get_var($sql));
    if(count($meta['fields'])) {
      $this->_fields = $meta['fields'];
    }
    else {
      throw new Exception("Unalbe to load Gravity Form: $formId");
    }
  }
  
  /**
   * Return an array of id/label combos for standard fields
   * 
   * @return array
   * @access public
   */
  public function getStandardFields() {
    $standardFields = array();
    if(is_array($this->_fields) && count($this->_fields)) {
      foreach($this->_fields as $field) {
        if(!is_array($field['inputs'])) {
          $standardFields[$field['id']] = $field['label'];
        }
      }
    }
    return $standardFields;
  }
  
  public function updateQuantity($entryId, $qtyFieldId, $qty) {
    global $wpdb;
    $entryTable = PHPurchaseCommon::getTableName('rg_lead_detail', '');
    $wpdb->update($entryTable, array('value' => $qty), array('lead_id' => $entryId, 'field_number' => $qtyFieldId));
  }
  
}

/*
class GravityEntry {
  
  private $_entryId;
  
  public function __construct($id=null) {
    $this->_entryId = $id;
    if($id > 0) {
      $this->loadEntry();
    }
  }
  
  public function setEntryId($id) {
    $this->_entryId = $id;
  }
  
  public function loadEntry($id=null) {
    if($id > 0) {
      $this->setEntryId($id);
    }
    
    global $wpdb;
    
  }
  
  
}
*/