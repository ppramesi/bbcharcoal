<?php
class freightHelper{
	public static function checkAddressOnDB($address, $city, $state, $zip){
		global $wpdb;
		$addrTableName = $wpdb->prefix . "pur_fr_addresses";
		$addrQuery = "SELECT Id FROM $addrTableName WHERE Address1=%s AND City=%s AND State=%s AND Zip=%s;";
		$preexistingId = $wpdb->get_var(
			$wpdb->prepare(
				$addrQuery,
				$address,
				$city,
				$state,
				$zip
			)
		);
		return $preexistingId;
	}
	public static function insertAddressToDB($id, $address, $city, $state, $zip){
		global $wpdb;
		$addrTableName = $wpdb->prefix . "pur_fr_addresses";
		$addrInsertData = array(
			"Id" => $id,
			"Address1" => $address,
			"City" => $city,
			"State" => $state,
			"Zip" => $zip
		);
		$formatting = array('%d', '%s', '%s', '%s', '%s');
		$wpdb->insert($addrTableName, $addrInsertData, $formatting );
	}
	
	public static function insertFrOrderToDB($id, $carrier, $transit, $pickup, $price, $sid){
		global $wpdb;
		$frOrderTableName = $wpdb->prefix . "pur_fr_orders";
		$frOrderInsertData = array(
			"Id" => $id,
			"Carrier" => $carrier,
			"Transit" => $transit,
			"Pickup" => $pickup,
			"Price" => $price,
			"Sid" => $sid
		);
		$formatting = array('%d', '%s', '%s', '%s', '%s', '%d');
		$wpdb->insert($frOrderTableName, $frOrderInsertData, $formatting );
	}
	public static function getFrOrderFromDB($id){
		global $wpdb;
		$frOrderTableName = $wpdb->prefix . "pur_fr_orders";
		$frOrderQuery = "SELECT * FROM $frOrderTableName WHERE Id=$id;";
		$orderId = $wpdb->get_row($frOrderQuery, ARRAY_A);
		return $orderId;
	}
}

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