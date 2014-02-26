<?php
$filename = "/mnt/stor9-wc2-dfw1/528353/528995/www.bbcharcoal.com/logs/user-log.txt"; 
$data = file_get_contents($filename);
$data2 = preg_split('/ - /', $data);
foreach($data2 AS $d) {
	$list[$d] = $d;
}
echo implode('<br/>', $list);
die;
$from = "Web Master <test@bbcharcoal.com>";
$to = "Scot <scotschroeder@gmail.com>";
$subject = "Test email using PHP SMTP\r\n\r\n";
$body = "This is a test email message";

$host = "mail.bbcharcoal.com";
$username = "test@bbcharcoal.com";
$password = "Dojob1289";

$headers = array ('From' => $from,
  'To' => $to,
  'Subject' => $subject);
$smtp = Mail::factory('smtp',
  array ('host' => $host,
    'auth' => true,
    'username' => $username,
    'password' => $password));

$mail = $smtp->send($to, $headers, $body);

if (PEAR::isError($mail)) {
  echo("<p>" . $mail->getMessage() . "</p>");
} else {
  echo("<p>Message successfully sent!</p>");
}
?>

