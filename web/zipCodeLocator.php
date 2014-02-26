<?php
if(!empty($_POST) && !is_user_logged_in()) {
	file_put_contents('/mnt/stor9-wc2-dfw1/528353/528995/www.bbcharcoal.com/logs/store-locator.txt', date('m-d-Y h:i:s A').' - '.print_r($_POST, TRUE)."\n", FILE_APPEND);	
}
?>