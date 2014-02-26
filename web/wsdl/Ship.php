<?php

if (!class_exists("NewOperation")) {
/**
 * NewOperation
 */
class NewOperation {
	/**
	 * @access public
	 * @var string
	 */
	public $in;
}}

if (!class_exists("NewOperationResponse")) {
/**
 * NewOperationResponse
 */
class NewOperationResponse {
	/**
	 * @access public
	 * @var string
	 */
	public $out;
}}

if (!class_exists("Ship")) {
/**
 * Ship
 * @author WSDLInterpreter
 */
class Ship extends SoapClient {
	/**
	 * Default class map for wsdl=>php
	 * @access private
	 * @var array
	 */
	private static $classmap = array(
		"NewOperation" => "NewOperation",
		"NewOperationResponse" => "NewOperationResponse",
	);

	/**
	 * Constructor using wsdl location and options array
	 * @param string $wsdl WSDL location for this service
	 * @param array $options Options for the SoapClient
	 */
	public function __construct($wsdl="http://joebot.bbcharcoal.com/FreightShip.wsdl", $options=array()) {
		foreach(self::$classmap as $wsdlClassName => $phpClassName) {
		    if(!isset($options['classmap'][$wsdlClassName])) {
		        $options['classmap'][$wsdlClassName] = $phpClassName;
		    }
		}
		parent::__construct($wsdl, $options);
	}

	/**
	 * Checks if an argument list matches against a valid argument type list
	 * @param array $arguments The argument list to check
	 * @param array $validParameters A list of valid argument types
	 * @return boolean true if arguments match against validParameters
	 * @throws Exception invalid function signature message
	 */
	public function _checkArguments($arguments, $validParameters) {
		$variables = "";
		foreach ($arguments as $arg) {
		    $type = gettype($arg);
		    if ($type == "object") {
		        $type = get_class($arg);
		    }
		    $variables .= "(".$type.")";
		}
		if (!in_array($variables, $validParameters)) {
		    throw new Exception("Invalid parameter types: ".str_replace(")(", ", ", $variables));
		}
		return true;
	}

	/**
	 * Service Call: NewOperation
	 * @return 
	 * @throws Exception invalid function signature message
	 */
	public function NewOperation() {
		return $this->__soapCall("NewOperation", array());
	}


}}

?>