<?php 
require_once('/home5/joebotwe/public_html/bbcharcoal/web/wp-config.php');
$orderId = $_GET['orderId'];
$showLabel = $_GET['showlabel'];
$controlLog = $_GET['showcontrollog'];

$order = new PHPurchaseOrder($orderId);
$shipmentInfo = unserialize($order->shipping_results);
//var_dump($shipmentInfo);die;
if(!empty($shipmentInfo)) {
	if(!empty($controlLog)) {
		if(!empty($shipmentInfo['ControlLogReceipt']['GraphicImage']['VALUE'])) {
			echo base64_decode($shipmentInfo['ControlLogReceipt']['GraphicImage']['VALUE']);
		}
		exit();
	}

	$count = 1;
	if(!empty($shipmentInfo['PackageResults'][0])) {
		foreach($shipmentInfo['PackageResults'] as $label) {
			if(!empty($showLabel)) {
				if($showLabel == $count) {
					echo preg_replace('/®/', '&#174;', base64_decode($label['LabelImage']['HTMLImage']['VALUE']));
				} 
			} else {
				echo '<a href="'.URL_BASE.'ups-labels/printLabel.php?orderId='.$orderId.'&showlabel='.$count.'" target="_blank">Label '.$count.'</a><br/>';
			}
			$count++;
		}	
	} else {
		echo preg_replace('/®/', '&#174;', base64_decode($shipmentInfo['PackageResults']['LabelImage']['HTMLImage']['VALUE']));
	}
} else {
	echo 'ERROR: No Label Was Created For This Order.';
}

?>