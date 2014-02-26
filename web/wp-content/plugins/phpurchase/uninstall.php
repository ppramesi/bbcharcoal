<?php
global $wpdb;

if( !defined('ABSPATH') && !defined('WP_UNINSTALL_PLUGIN') )
    exit();

require_once(WP_PLUGIN_DIR. "/" . basename(dirname(__FILE__)) . "/models/common.php");

global $wpdb;
$prefix = PHPurchaseCommon::getTablePrefix();
$sqlFile = WP_PLUGIN_DIR. "/" . basename(dirname(__FILE__)) . "/sql/uninstall.sql";
$sql = str_replace('[prefix]', $prefix, file_get_contents($sqlFile));
$queries = explode(";\n", $sql);
foreach($queries as $sql) {
  if(strlen($sql) > 5) {
    $wpdb->query($sql);
  }
}