<?php
if(isset($_GET['phpurchase-task']) && $_GET['phpurchase-task'] == 'mask') {
  $memberId = PHPurchaseCommon::getVal('id');

  if(is_numeric($memberId)) {
    $_SESSION['PHPurchaseMember'] = $memberId;
    $pgs = get_posts('numberposts=1&post_type=any&meta_key=phpurchase_member&meta_value=home');
    if(count($pgs)) {
      $link = get_permalink($pgs[0]->ID);
      wp_redirect($link);
      exit();
    }
  }
}
?>
<h2>PHPurchase Members</h2>

<p><a href='#' id="createNewMemberLink">Create New Member</a></p>

<?php
$display = 'none';
if(isset($_POST['phpurchase-task']) && $_POST['phpurchase-task'] == 'account-create') {
  $display = "block";
}
?>
<div id='createMemberBlock' class="adminHeaderBlock" style="display: <?php echo $display ?>;">
  <?php 
    include(dirname(__FILE__) . '/../pro/account-create.php'); 
    if(empty($errors) && isset($_POST['phpurchase-task']) && $_POST['phpurchase-task'] == 'account-create') {
      unset($_SESSION['PHPurchaseMember']);
      wp_redirect('?page=phpurchase-members&phpurchase-task=viewMember&id=' . $member->id);
    }
  ?>
  
</div>

<script type='text/javascript'>
var $jq = jQuery.noConflict();
$jq(document).ready(function() {
  $jq('#createNewMemberLink').click(function() {
    $jq('#createMemberBlock').toggle('slow');
    return false;
  });

  $jq('#phpurchaseMemberSearch').keyup(function() {
    var data = {action: 'member_search', search: $jq('#phpurchaseMemberSearch').val()};
    $jq.ajax({
        type: "POST",
        url: ajaxurl,
        data: data,
        dataType: 'json',
        success: function(result) {
          $jq('#memberList').html(result);
        }
    });
  });
  
  
  <?php if( (isset($_POST['phpurchase-task']) && $_POST['phpurchase-task'] == 'account-create') && empty($error)): ?>
    $jq('#createMemberBlock').hide();
  <?php endif; ?>
});
</script>
  
<form action='' style="clear: both; display: block; margin-bottom: 10px;">
  <input type="hidden" name="action" value="member_search">
  <label>Search by email:</label>
  <input type="text" name="search" id='phpurchaseMemberSearch' value="" />
</form>

<div id="memberList">
<?php
  global $wpdb;
  $gw = PHPurchaseCommon::gatewayName();
  $customer = ($gw == 'quantum') ? new Quantum_VaultCustomer() : new CIM();
  $accounts = PHPurchaseCommon::getTableName('accounts');
  $sql = "SELECT * from $accounts order by email";
  $members = $wpdb->get_results($sql);
  if(count($members)) {
    foreach($members as $m) {
      ?>
      <div class="PHPurchaseMemberInfoBlock">
        <p style='float: right;'><a class="PHPurchaseLinkButton" href='?page=phpurchase-members&phpurchase-task=mask&id=<?php echo $m->id ?>'>Login</a></p>
        <p>
          <strong><a href='?page=phpurchase-members&phpurchase-task=viewMember&id=<?php echo $m->id ?>' 
          title='click to manage member'><?php echo $m->email ?></a></strong>
        </p>
        <?php
          $customer->load($m->id, false);
          $subs = $customer->getMySubscriptions();
          if(count($subs)) {
            foreach($subs as $s) {
              echo "<p class='PHPurchase" . ucwords($s->status) . "'>$s->description</p>";
            }
          }
        ?>
        
      </div>
      <?php
    }
  }
  else {
    echo "<p>No members</p>";
  }
?>
</div>
