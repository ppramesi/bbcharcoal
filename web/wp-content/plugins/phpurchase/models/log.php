<?php
class PHPurchaseLog {
  
  public static function getLogFilePath() {
    $logFilePath = WP_PLUGIN_DIR . '/phpurchase/log.txt';
    return $logFilePath;
  }
  
  /**
   * Attempt to create a log file in the plugins/phpurchase directory
   * Returns the path to the log file. If the file could not be created a PHPurchaseException is thrown.
   *
   * @return string
   * @throws PHPurchaseException on failure to create log file
   */
  public static function createLogFile() {
    $logDirPath = WP_PLUGIN_DIR . '/phpurchase';
    $logFilePath = self::getLogFilePath();
    
    PHPurchaseCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Trying to create log file.");
    if(file_exists($logDirPath)) {
      if(is_writable($logDirPath)) {
        @fclose(fopen($logFilePath, 'a'));
        if(!is_writable($logFilePath)) {
          PHPurchaseCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Unable to create log file. $logFilePath");
          throw new PHPurchaseException("Unable to create log file. $logFilePath");
        }
      }
      else {
        throw new PHPurchaseException("Log file directory is not writable. $logDirPath");
      }
    }
    else {
      throw new PHPurchaseException("Log file directory does not exist. $logDirPath");
    }
    
    
    return $logFilePath;
  }
  
  public static function exists() {
    $exists = false;
    $logFilePath = self::getLogFilePath();
    if(file_exists($logFilePath) && filesize($logFilePath) > 0) {
      $exists = true;
    }
    return $exists;
  }
  
  public static function getCartSettings() {
    global $wpdb;
    $out = "\n=====================\nCART SETTINGS\n=====================\n\n";
    $cartTable = PHPurchaseCommon::getTableName('cart_settings');
    $sql = "SELECT * from $cartTable order by `key`";
    $results = $wpdb->get_results($sql, OBJECT);
    foreach($results as $row) {
      $out .= $row->key . ' = ' . $row->value . "\n";
    }
    return $out;
  }
  
}