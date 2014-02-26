<?php
class PHPurchaseAccessManager {

  /**
   * Return link to the page with the custom field
   *   phpurchase_member = denied
   * If no such page exists, return link to homepage of site
   */
  public static function getDeniedLink() {
    $deniedLink = get_bloginfo('url');
    $pgs = get_posts('numberposts=1&post_type=any&meta_key=phpurchase_member&meta_value=denied');
    if(count($pgs)) {
      $deniedLink = get_permalink($pgs[0]->ID);
    }
    return $deniedLink;
  }

  /**
   * Return an array of page ids with the custom field 
   *   phpurchase_access = private
   */
  public static function getPrivatePageIds() {
    $privatePages = array();
    $pages = get_pages(array('meta_key'=>'phpurchase_access', 'meta_value' => 'private', 'hierarchical' => 0));
    foreach($pages as $pg) {
      $privatePages[] = $pg->ID;
    }
    return $privatePages;
  }

  /**
   * If private pages should be blocked because the visitor is not a logged in member,
   * check if the page that is being accessed is private. If so, redirect to the login 
   * page or the access denied page. 
   */
  public static function verifyPageAccessRights($pageId) {
    if(self::blockPrivatePages()) {
      $privatePages = self::getPrivatePageIds();
      $deniedLink = self::getDeniedLink();
      if(in_array($pageId, $privatePages)) {
        wp_redirect($deniedLink);
        exit;
      }
    }
  }
  
  /**
   * If PHPurchase is the PHPURCHASEPRO version and the visitor is a logged in member,
   * do not block access to private pages. Otherwise, private pages should be blocked.
   *
   * @return boolean
   */
  public function blockPrivatePages() {
    $blockPrivate = true;
    if(PHPURCHASEPRO && isset($_SESSION['PHPurchaseMember']) && $_SESSION['PHPurchaseMember'] > 0) {
      $blockPrivate = false;
    }
    return $blockPrivate;
  }

  public static function getRequiredItemNumbersForPage($pageId) {
    $requiredIds = array();
    $custom = get_post_custom_values('phpurchase_subscription', $pageId);
    if(is_array($custom)) {
      $requiredIds = explode(',', str_replace(' ', '', $custom[0]));
    }
    return $requiredIds;
  }

  /**
   * Loop through array of required ids. If the required id is found in the $mySubscriptionIds array then return true.
   */
  public static function allowSubscriptionAccess(array $mySubscriptionIds, array $requiredIds) {
    foreach($requiredIds as $rId) {
      $rId = trim($rId);
      if(in_array($rId, $mySubscriptionIds)) {
        return true;
      }
    }
    return false;
  }

  /**
   * Return an array of page ids where the custom field is set to 
   *   phpurchase_access = guest only
   * If no such pages exist, return an empty array
   *
   * @return array
   */
  public static function getGuestOnlyPageIds() {
    $guestPageIds = array();
    $pages = get_pages(array('meta_key'=>'phpurchase_access', 'meta_value' => 'guest only', 'hierarchical' => 0));
    foreach($pages as $pg) {
      $guestPageIds[] = $pg->ID;
    }
    return $guestPageIds;
  }

  public static function hideSubscriptionPages($mySubItemNums) {
    global $wpdb;
    $hiddenPages = array();
    $posts = PHPurchaseCommon::getTableName('posts', '');
    $meta = PHPurchaseCommon::getTableName('postmeta', '');
    $sql = "SELECT post_id, meta_value from $meta where meta_key='phpurchase_subscription'";
    $results = $wpdb->get_results($sql);
    if(count($results)) {
      foreach($results as $m) {
        $requiredIds = explode(',', $m->meta_value);
        if(!PHPurchaseAccessManager::allowSubscriptionAccess($mySubItemNums, $requiredIds)) {
          $hiddenPages[] = $m->post_id;
          // PHPurchaseCommon::log("Excluding based on not having subscription: $m->post_id");
        }
      }
    }
    return $hiddenPages;
  }

}
