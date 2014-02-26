<?php
/*
Plugin Name: one time run
Plugin URI: $wp->wpurl
Description: just once
Version: 0.0.0
Author: LUAAR LMRPAE
Author URI: $wp->wpurl
*/
function qweasd(){
	global $wpdb;
	$addrTableName = $wpdb->prefix . "pur_fr_addresses";
	$frOrderTableName = $wpdb->prefix . "pur_fr_orders";
	$addrQuery = "CREATE TABLE IF NOT EXISTS $addrTableName (
		Id INT(7) not null,
		Address1 VARCHAR(50) not null,
		City VARCHAR(20) not null,
		State VARCHAR(3) not null,
		Zip VARCHAR(5) not null,
		PRIMARY KEY(Id)
	);";
	$frOrderQuery = "CREATE TABLE IF NOT EXISTS $frOrderTableName (
		Id INT(7) NOT NULL,
		Carrier VARCHAR(25) NOT NULL,
		Transit INT(3) NOT NULL,
		Pickup VARCHAR(50) NOT NULL,
		Price DECIMAL(7,2) NOT NULL,
		Sid INT(7) NOT NULL,
		PRIMARY KEY(Id)
	);";
	/*$authQuery = "CREATE TABLE IF NOT EXISTS $authTableName (
		Name VARCHAR(10) NOT NULL,
		Value VARCHAR(30),
		CONSTRAINT NameValuePair PRIMARY KEY(Name)
	);";*/
	$wpdb->query($addrQuery);
	$wpdb->query($frOrderQuery);
}

register_activation_hook( __FILE__, 'qweasd' );
/*if(function_exists('mcrypt_encrypt')){ 
	class fuckingEncryptDecrypt{
		private $hashUno = md5("dojo");
		private $hashDos = hash('sha256', "b1289");
		private $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC); 
		private $iv = mcrypt_create_iv($this->iv_size, MCRYPT_RAND);
		
		public static function fuckingEncrypt($word){
			$key = pack('H*', $this->hashUno . $this->hashDos);
			
			$ciphertext = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $word, MCRYPT_MODE_CBC, $this->iv_size);

			# prepend the IV for it to be available for decryption
			$ciphertext = $this->iv . $ciphertext;
				
			# encode the resulting cipher text so it can be represented by a string
			$ciphertext_base64 = base64_encode($ciphertext);
			return $ciphertext_base64;
		}
		
		public static function fuckingDecrypt($cipher){
			$key = pack('H*', $this->hashUno . $this->hashDos);
			$ciphertext_dec = base64_decode($cipher);
			# retrieves the IV, iv_size should be created using mcrypt_get_iv_size()
			$iv_dec = substr($ciphertext_dec, 0, $this->iv_size);
		
			# retrieves the cipher text (everything except the $iv_size in the front)
			$ciphertext_dec = substr($ciphertext_dec, $this->iv_size);

			# may remove 00h valued characters from end of plain text
			$plaintext_dec = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key, $ciphertext_dec, MCRYPT_MODE_CBC, $iv_dec);
		
			echo  $plaintext_dec . "\n";
		}
	}
}

class holyShitAuth{
	public static function getAuth(){
		global $wpdb;
		$retVal = array();
		$authTableName = $wpdb->prefix . "pur_fr_auth";
		$passRaw = "";
		for($count = 0; $count < 4; $count++){
			$passRaw .= $wpdb->get_var("SELECT Value FROM $authTableName WHERE Name='password$count';");
		}
		$retVal['username'] = $wpdb->get_var("SELECT Value FROM $authTableName WHERE Name='username';");
		$retVal['customerId'] = $wpdb->get_var("SELECT Value FROM $authTableName WHERE Name='customerId';");
		$retVal['password'] = class_exists("fuckingEncryptDecrypt") ? fuckingEncryptDecrypt::fuckingDecrypt($authParam['password']) : base64_decode($authParam['password']);
		return $retVal;
	}
	
	public static function setAuth($authParam){
		global $wpdb;
		$authTableName = $wpdb->prefix . "pur_fr_auth";
		$cryptPass = class_exists("fuckingEncryptDecrypt") ? fuckingEncryptDecrypt::fuckingEncrypt($authParam['password']) : base64_encode($authParam['password']);
		$divisor = ceil(floatval(strlen($cryptPass)) / 4.00);
		$arrCryptPass = str_split($cryptPass, intval($divisor));
		$wpdb->update(
			"$authTableName",
			array(
				'Value' => $authParam['username']
			),
			array('Name' => 'username')
		);
		
		$wpdb->update(
			"$authTableName",
			array(
				'Value' => $authParam['customerId']
			),
			array('Name' => 'customerId')
		);
		for($count = 0; $count < 4; $count++){
			$wpdb->update(
				"$authTableName",
				array(
					'Value' => $arrCryptPass[$count]
				),
				array('Name' => "password$count")
			);
		}
	}
}*/