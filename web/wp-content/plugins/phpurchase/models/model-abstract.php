<?php
abstract class PHPurchaseModelAbstract {
  
  protected $_tableName;
  protected $_db;
  protected $_data;

  public function __construct($id=null) {
    global $wpdb;
    $this->_db = $wpdb;
    $this->_data = array();
    $this->_initProperties();
    if($id > 0 && is_numeric($id)) {
      $this->load($id);
    }
  }
  
  public function getDb() {
    return $this->_db;
  }
  
  public function load($id) {
    if(is_numeric($id) && $id > 0) {
      $sql = 'SELECT * from ' . $this->_tableName . " WHERE id='$id'";
      if($data = $this->_db->get_row($sql, ARRAY_A)) {
        $this->setData($data);
        return true;
      }
    }
    return false;
  }
  
  public function deleteMe() {
    if($this->id > 0) {
      $sql = "DELETE from " . $this->_tableName . " WHERE id='" . $this->id . "'";
      $this->_db->query($sql);
    }
  }
  
  /**
   * Overwrite any common keys with the values from the passed in array
   */
  public function setData(array $data) {
    foreach($data as $key => $value) {
      if(array_key_exists($key, $this->_data)) {
        if($key == 'id') {
          if(is_numeric($value) && $value > 0) {
            $this->_data[$key] = $value;
          }
          else {
            $this->_data[$key] = null;
          }
        }
        $this->_data[$key] = $value;
      }
    }
  }
    
  public function getData() {
    return $this->_data;
  }
  
  public function dumpData() {
    echo '<pre>';
    print_r($this->_data);
    echo '</pre>';
  }
  
  /**
   * Return an array of models
   */
  public function getModels($where=null, $orderBy=null) {
    $models = array();
    global $wpdb;
    if(isset($where)) {
      $where = ' ' . $where;
    }
    if(isset($orderBy)) {
      $orderBy = ' ' . $orderBy;
    }
    $sql = 'SELECT id FROM ' . $this->_tableName . $where . $orderBy;
    $ids = $wpdb->get_col($sql);
    foreach($ids as $id) {
      $className = get_class($this);
      $models[] = new $className($id);
    }
    return $models;
  }
  
  /**
   * Return the first matching model or false if no model could be found.
   */
  public function getOne($where, $orderBy=null) {
    $model = false;
    $models = $this->getModels($where, $orderBy);
    if(count($models)) {
      $model = $models[0];
    }
    return $model;
  }
  
  public function __get($key) {
    $key = $this->_camelToSnake($key);
    $value = false;
    $funcName = "_get" . $this->_snakeToCamel($key, false);
    if(method_exists($this, $funcName)) {
      $value = $this->{$funcName}();
    }
    elseif(array_key_exists($key, $this->_data)) {
      $value = $this->_data[$key];
    }
    
    return $value;
  }
  
  public function __set($key, $value) {
    $key = $this->_camelToSnake($key);
    if(array_key_exists($key, $this->_data)) {
      // Check for hook to override setting incoming data using _setKeyName() as the expected function
      $funcName = "_set" . $this->_snakeToCamel($key);
      if(method_exists($this, $funcName)) {
        $value = $this->{$funcName}($value);
      }
      else {
        $this->_data[$key] = $value;
      }
    }
  }
  
  public function __isset($key) {
    return isset($this->_data[$key]);
  }
  
  
  public function save() {
    foreach($this->_data as $key => $value) {
      if(is_scalar($value)) {
        $this->_data[$key] = stripslashes($value);
      }
    }
    return $this->_data['id'] >= 1 ? $this->_update() : $this->_insert();
  }
  
  public function clear() {
    foreach($this->_data as $key => $value) {
      $this->_data[$key] = '';
    }
    if(isset($this->_data['id'])) {
      $this->_data['id'] = null;
    }
  }
  
  protected function _insert() {
    if(isset($this->_data['created_at'])) {
      $this->_data['created_at'] = date('Y-m-d H:i:s');
    }
    if(isset($this->_data['updated_at'])) {
      $this->_data['updated_at'] = date('Y-m-d H:i:s');
    }
    $this->_db->insert($this->_tableName, $this->_data);
    $this->id = $this->_db->insert_id;
    return $this->id;
  }
  
  protected function _update() {
    if(isset($this->_data['updated_at'])) {
      $this->_data['updated_at'] = date('Y-m-d H:i:s');
    }
    $this->_db->update($this->_tableName, $this->_data, array('id' => $this->_data['id']));
    return $this->id;
  }
  

  protected function _initProperties() {
  	$query = 'describe ' . $this->_tableName;
  	$metadata = $this->_db->get_results($query);
  	foreach($metadata as $row) {
  	  $colName = $row->Field;
  	  if($colName == 'id') {
  	    $this->_data[$colName] = null;
  	  }
  	  else {
  	    $this->_data[$colName] = '';
  	  }
  	}
  }
  
  protected function _camelToSnake($name) {
		$pattern = "/([A-Z])/";
		$replace = "_$1";
		$name = strtolower(preg_replace($pattern, $replace, $name));
		return $name;
	}
	
	/**
   * Return a camelCase string based on the given snake_case value.
   *
   * If the optional $lcFirst parameter is true, the first letter of 
   * the returned value is lower case like this lowerCase otherwise 
   * the first letter is upper case like this UpperCase.
   *
   * @param string $val
   * @param boolean $lcFirst
   * @return string
   */
  protected function _snakeToCamel($val, $lcFirst=true) {
    $val = str_replace(' ', '', ucwords(str_replace('_', ' ', $val)));
    if($lcFirst) {
      $val = strtolower(substr($val,0,1)).substr($val,1); 
    }
    return $val;
  }
  
  public function getLastQuery() {
    return $this->_db->last_query;
  }
  
}
