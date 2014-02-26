<?php
/**
 * Interprets WSDL documents for the purposes of PHP 5 object creation
 * 
 * The WSDLInterpreter package is used for the interpretation of a WSDL 
 * document into PHP classes that represent the messages using inheritance
 * and typing as defined by the WSDL rather than SoapClient's limited
 * interpretation.  PHP classes are also created for each service that
 * represent the methods with any appropriate overloading and strict
 * variable type checking as defined by the WSDL.
 *
 * PHP version 5 
 * 
 * LICENSE: This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * @category    WebServices 
 * @package     WSDLInterpreter  
 * @author      Kevin Vaughan kevin@kevinvaughan.com
 * @copyright   2007 Kevin Vaughan
 * @license     http://www.gnu.org/copyleft/lesser.html  LGPL License 2.1
 * 
 */

/**
 * A lightweight wrapper of Exception to provide basic package specific 
 * unrecoverable program states.
 * 
 * @category WebServices
 * @package WSDLInterpreter
 */

class WSDLInterpreterException extends Exception { } 

/**
 * The main class for handling WSDL interpretation
 * 
 * The WSDLInterpreter is utilized for the parsing of a WSDL document for rapid
 * and flexible use within the context of PHP 5 scripts.
 * 
 * Example Usage:
 * <code>
 * require_once 'WSDLInterpreter.php';
 * $myWSDLlocation = 'http://www.example.com/ExampleService?wsdl';
 * $wsdlInterpreter = new WSDLInterpreter($myWSDLlocation);
 * $wsdlInterpreter->savePHP('/example/output/directory/');
 * </code>
 * 
 * @version 1.0.0a3
 * @category WebServices
 * @package WSDLInterpreter
 */
class WSDLInterpreter 
{
    /**
     * The WSDL document's URI
     * @var string
     * @access private
     */
    private $_wsdl = null;

    /**
     * A SoapClient for loading the WSDL
     * @var SoapClient
     * @access private
     */
    private $_client = null;
    
    /**
     * DOM document representation of the wsdl and its translation
     * @var DOMDocument
     * @access private
     */
    private $_dom = null;
    
    /**
     * Array of classes and members representing the WSDL message types
     * @var array
     * @access private
     */
    private $_classmap = array();
    
    /**
     * Array of sources for WSDL message classes
     * @var array
     * @access private
     */
    private $_classPHPSources = array();
    
    /**
     * Array of sources for WSDL services
     * @var array
     * @access private
     */
    private $_servicePHPSources = array();
    
    /**
     * Parses the target wsdl and loads the interpretation into object members
     * 
     * @param string $wsdl  the URI of the wsdl to interpret
     * @throws WSDLInterpreterException Container for all WSDL interpretation problems
     * @todo Create plug in model to handle extendability of WSDL files
     */
    public function __construct($wsdl) 
    {
        try {
            $this->_wsdl = $wsdl;
            $this->_client = new SoapClient($wsdl);
        
            $this->_dom = new DOMDocument();
            $this->_dom->load($wsdl);
        } catch (Exception $e) {
            throw new WSDLInterpreterException("Error loading WSDL document (".$e->getMessage().")");
        }
        
        try {
            $xsl = new XSLTProcessor();
            $xslDom = new DOMDocument();
            $xslDom->load(dirname(__FILE__)."/wsdl2php.xsl");
            $xsl->importStyleSheet($xslDom);
            $this->_dom = $xsl->transformToDoc($this->_dom);
            $this->_dom->formatOutput = true;
        } catch (Exception $e) {
            throw new WSDLInterpreterException("Error interpreting WSDL document (".$e->getMessage().")");
        }
       
        $this->_loadClasses();
        $this->_loadServices();
    }

    /**
     * Validates a name against standard PHP naming conventions
     * 
     * @param string $name the name to validate
     * 
     * @return string the validated version of the submitted name
     * 
     * @access private
     */
    private function _validateNamingConvention($name) 
    {
        return preg_replace('#[^a-zA-Z0-9_\x7f-\xff]*#', '',
            preg_replace('#^[^a-zA-Z_\x7f-\xff]*#', '', $name));
    }
    
    /**
     * Validates a class name against PHP naming conventions and already defined
     * classes, and optionally stores the class as a member of the interpreted classmap.
     * 
     * @param string $className the name of the class to test
     * @param boolean $addToClassMap whether to add this class name to the classmap
     * 
     * @return string the validated version of the submitted class name
     * 
     * @access private
     * @todo Add reserved keyword checks
     */
    private function _validateClassName($className, $addToClassMap = true) 
    {
        $validClassName = $this->_validateNamingConvention($className);
        
        if (class_exists($validClassName)) {
            throw new Exception("Class ".$validClassName." already defined.".
                " Cannot redefine class with class loaded.");
        }
        
        if ($addToClassMap) {
            $this->_classmap[$className] = $validClassName;
        }
        
        return $validClassName;
    }

    
    /**
     * Validates a wsdl type against known PHP primitive types, or otherwise
     * validates the namespace of the type to PHP naming conventions
     * 
     * @param string $type the type to test
     * 
     * @return string the validated version of the submitted type
     * 
     * @access private
     * @todo Extend type handling to gracefully manage extendability of wsdl definitions, add reserved keyword checking
     */    
    private function _validateType($type) 
    {
        $array = false;
        if (substr($type, -2) == "[]") {
            $array = true;
            $type = substr($type, 0, -2);
        }
        switch (strtolower($type)) {
        case "int": case "integer": case "long": case "byte": case "short":
        case "negativeInteger": case "nonNegativeInteger": 
        case "nonPositiveInteger": case "positiveInteger":
        case "unsignedByte": case "unsignedInt": case "unsignedLong": case "unsignedShort":
            $validType = "integer";
            break;
            
        case "float": case "long": case "double": case "decimal":
            $validType = "double";
            break;
            
        case "string": case "token": case "normalizedString": case "hexBinary":
            $validType = "string";
            break;
            
        default:
            $validType = $this->_validateNamingConvention($type);
            break;
        }
        if ($array) {
            $validType .= "[]";
        }
        return $validType;
    }        
    
    /**
     * Loads classes from the translated wsdl document's message types 
     * 
     * @access private
     */
    private function _loadClasses() 
    {
        $classes = $this->_dom->getElementsByTagName("class");
        foreach ($classes as $class) {
            $class->setAttribute("validatedName", 
                $this->_validateClassName($class->getAttribute("name")));
            $extends = $class->getElementsByTagName("extends");
            if ($extends->length > 0) {
                $extends->item(0)->nodeValue = 
                    $this->_validateClassName($extends->item(0)->nodeValue);
            }
            $properties = $class->getElementsByTagName("property");
            foreach ($properties as $property) {
                $property->setAttribute("validatedName", 
                    $this->_validateNamingConvention($property->getAttribute("name")));
                $property->setAttribute("type", 
                    $this->_validateType($property->getAttribute("type")));
            }
            
            $this->_classPHPSources[$class->getAttribute("validatedName")] = 
                $this->_generateClassPHP($class);
        }
    }
    
    /**
     * Generates the PHP code for a WSDL message type class representation
     * 
     * This gets a little bit fancy as the magic methods __get and __set in
     * the generated classes are used for properties that are not named 
     * according to PHP naming conventions (e.g., "MY-VARIABLE").  These
     * variables are set directly by SoapClient within the target class,
     * and could normally be retrieved by $myClass->{"MY-VARIABLE"}.  For
     * convenience, however, this will be available as $myClass->MYVARIABLE.
     * 
     * @param DOMElement $class the interpreted WSDL message type node
     * @return string the php source code for the message type class
     * 
     * @access private
     * @todo Include any applicable annotation from WSDL
     */
    private function _generateClassPHP($class) 
    {
        $return = "";
        $return .= 'if (!class_exists("'.$class->getAttribute("validatedName").'")) {'."\n";
        $return .= '/**'."\n";
        $return .= ' * '.$class->getAttribute("validatedName")."\n";
        $return .= ' */'."\n";
        $return .= "class ".$class->getAttribute("validatedName");
        $extends = $class->getElementsByTagName("extends");
        if ($extends->length > 0) {
            $return .= " extends ".$extends->item(0)->nodeValue;
        }
        $return .= " {\n";
    
        $properties = $class->getElementsByTagName("property");
        foreach ($properties as $property) {
            $return .= "\t/**\n"
                     . "\t * @access public\n"
                     . "\t * @var ".$property->getAttribute("type")."\n"
                     . "\t */\n"
                     . "\t".'public $'.$property->getAttribute("validatedName").";\n";
        }
    
        $extraParams = false;
        $paramMapReturn = "\t".'private $_parameterMap = array ('."\n";
        $properties = $class->getElementsByTagName("property");
        foreach ($properties as $property) {
            if ($property->getAttribute("name") != $property->getAttribute("validatedName")) {
                $extraParams = true;
                $paramMapReturn .= "\t\t".'"'.$property->getAttribute("name").
                    '" => "'.$property->getAttribute("validatedName").'",'."\n";
            }
        }
        $paramMapReturn .= "\t".');'."\n";
        $paramMapReturn .= "\t".'/**'."\n";
        $paramMapReturn .= "\t".' * Provided for setting non-php-standard named variables'."\n";
        $paramMapReturn .= "\t".' * @param $var Variable name to set'."\n";
        $paramMapReturn .= "\t".' * @param $value Value to set'."\n";
        $paramMapReturn .= "\t".' */'."\n";
        $paramMapReturn .= "\t".'public function __set($var, $value) '.
            '{ $this->{$this->_parameterMap[$var]} = $value; }'."\n";
        $paramMapReturn .= "\t".'/**'."\n";
        $paramMapReturn .= "\t".' * Provided for getting non-php-standard named variables'."\n";
        $paramMapReturn .= "\t".' * @param $var Variable name to get'."\n";
        $paramMapReturn .= "\t".' * @return mixed Variable value'."\n";
        $paramMapReturn .= "\t".' */'."\n";
        $paramMapReturn .= "\t".'public function __get($var) '.
            '{ return $this->{$this->_parameterMap[$var]}; }'."\n";
        
        if ($extraParams) {
            $return .= $paramMapReturn;
        }
    
        $return .= "}}";
        return $return;
    }
    
    /**
     * Loads services from the translated wsdl document
     * 
     * @access private
     */
    private function _loadServices() 
    {
        $services = $this->_dom->getElementsByTagName("service");
        foreach ($services as $service) {
            $service->setAttribute("validatedName", 
                $this->_validateClassName($service->getAttribute("name"), false));
            $functions = $service->getElementsByTagName("function");
            foreach ($functions as $function) {
                $function->setAttribute("validatedName", 
                    $this->_validateNamingConvention($function->getAttribute("name")));
                $parameters = $function->getElementsByTagName("parameters");
                if ($parameters->length > 0) {
                    $parameterList = $parameters->item(0)->getElementsByTagName("variable");
                    foreach ($parameterList as $variable) {
                        $variable->setAttribute("validatedName", 
                            $this->_validateNamingConvention($variable->getAttribute("name")));
                        $variable->setAttribute("type", 
                            $this->_validateType($variable->getAttribute("type")));
                    }
                }
            }
            
            $this->_servicePHPSources[$service->getAttribute("validatedName")] = 
                $this->_generateServicePHP($service);
        }
    }
    
    /**
     * Generates the PHP code for a WSDL service class representation
     * 
     * This method, in combination with generateServiceFunctionPHP, create a PHP class
     * representation capable of handling overloaded methods with strict parameter
     * type checking.
     * 
     * @param DOMElement $service the interpreted WSDL service node
     * @return string the php source code for the service class
     * 
     * @access private
     * @todo Include any applicable annotation from WSDL
     */
    private function _generateServicePHP($service) 
    {
        $return = "";
        $return .= 'if (!class_exists("'.$service->getAttribute("validatedName").'")) {'."\n";
        $return .= '/**'."\n";
        $return .= ' * '.$service->getAttribute("validatedName")."\n";
        $return .= ' * @author WSDLInterpreter'."\n";
        $return .= ' */'."\n";
        $return .= "class ".$service->getAttribute("validatedName")." extends SoapClient {\n";

        if (sizeof($this->_classmap) > 0) {
            $return .= "\t".'/**'."\n";
            $return .= "\t".' * Default class map for wsdl=>php'."\n";
            $return .= "\t".' * @access private'."\n";
            $return .= "\t".' * @var array'."\n";
            $return .= "\t".' */'."\n";
            $return .= "\t".'private static $classmap = array('."\n";
            foreach ($this->_classmap as $className => $validClassName)    {
                $return .= "\t\t".'"'.$className.'" => "'.$validClassName.'",'."\n";
            }
            $return .= "\t);\n\n";
        }
        
        $return .= "\t".'/**'."\n";
        $return .= "\t".' * Constructor using wsdl location and options array'."\n";
        $return .= "\t".' * @param string $wsdl WSDL location for this service'."\n";
        $return .= "\t".' * @param array $options Options for the SoapClient'."\n";
        $return .= "\t".' */'."\n";
        $return .= "\t".'public function __construct($wsdl="'.
            $this->_wsdl.'", $options=array()) {'."\n";
        if (sizeof($this->_classmap) > 0) {
            $return .= "\t\t".'foreach(self::$classmap as $wsdlClassName => $phpClassName) {'."\n";
            $return .= "\t\t".'    if(!isset($options[\'classmap\'][$wsdlClassName])) {'."\n";
            $return .= "\t\t".'        $options[\'classmap\'][$wsdlClassName] = $phpClassName;'."\n";
            $return .= "\t\t".'    }'."\n";
            $return .= "\t\t".'}'."\n";
        }
        $return .= "\t\t".'parent::__construct($wsdl, $options);'."\n";
        $return .= "\t}\n\n";
        $return .= "\t".'/**'."\n";
        $return .= "\t".' * Checks if an argument list matches against a valid '.
            'argument type list'."\n";
        $return .= "\t".' * @param array $arguments The argument list to check'."\n";
        $return .= "\t".' * @param array $validParameters A list of valid argument '.
            'types'."\n";
        $return .= "\t".' * @return boolean true if arguments match against '.
            'validParameters'."\n";
        $return .= "\t".' * @throws Exception invalid function signature message'."\n"; 
        $return .= "\t".' */'."\n";
        $return .= "\t".'public function _checkArguments($arguments, $validParameters) {'."\n";
        $return .= "\t\t".'$variables = "";'."\n";
        $return .= "\t\t".'foreach ($arguments as $arg) {'."\n";
        $return .= "\t\t".'    $type = gettype($arg);'."\n";
        $return .= "\t\t".'    if ($type == "object") {'."\n";
        $return .= "\t\t".'        $type = get_class($arg);'."\n";
        $return .= "\t\t".'    }'."\n";
        $return .= "\t\t".'    $variables .= "(".$type.")";'."\n";
        $return .= "\t\t".'}'."\n";
        $return .= "\t\t".'if (!in_array($variables, $validParameters)) {'."\n";
        $return .= "\t\t".'    throw new Exception("Invalid parameter types: '.
            '".str_replace(")(", ", ", $variables));'."\n";
        $return .= "\t\t".'}'."\n";
        $return .= "\t\t".'return true;'."\n";
        $return .= "\t}\n\n";

        $functionMap = array();        
        $functions = $service->getElementsByTagName("function");
        foreach ($functions as $function) {
            if (!isset($functionMap[$function->getAttribute("validatedName")])) {
                $functionMap[$function->getAttribute("validatedName")] = array();
            }
            $functionMap[$function->getAttribute("validatedName")][] = $function;
        }    
        foreach ($functionMap as $functionName => $functionNodeList) {
            $return .= $this->_generateServiceFunctionPHP($functionName, $functionNodeList)."\n\n";
        }
    
        $return .= "}}";
        return $return;
    }

    /**
     * Generates the PHP code for a WSDL service operation function representation
     * 
     * The function code that is generated examines the arguments that are passed and
     * performs strict type checking against valid argument combinations for the given
     * function name, to allow for overloading.
     * 
     * @param string $functionName the php function name
     * @param array $functionNodeList array of DOMElement interpreted WSDL function nodes
     * @return string the php source code for the function
     * 
     * @access private
     * @todo Include any applicable annotation from WSDL
     */    
    private function _generateServiceFunctionPHP($functionName, $functionNodeList) 
    {
        $return = "";
        $return .= "\t".'/**'."\n";
        $return .= "\t".' * Service Call: '.$functionName."\n";
        $parameterComments = array();
        $variableTypeOptions = array();
        $returnOptions = array();
        foreach ($functionNodeList as $functionNode) {
            $parameters = $functionNode->getElementsByTagName("parameters");
            if ($parameters->length > 0) {
                $parameters = $parameters->item(0)->getElementsByTagName("variable");
                $parameterTypes = "";
                $parameterList = array();
                foreach ($parameters as $parameter) {
                    if (substr($parameter->getAttribute("type"), 0, -2) == "[]") {
                        $parameterTypes .= "(array)";
                    } else {
                        $parameterTypes .= "(".$parameter->getAttribute("type").")";
                    }
                    $parameterList[] = "(".$parameter->getAttribute("type").") ".
                        $parameter->getAttribute("validatedName");
                }
                if (sizeof($parameterList) > 0) {
                    $variableTypeOptions[] = $parameterTypes;
                    $parameterComments[] = "\t".' * '.join(", ", $parameterList);
                }
            }
            $returns = $functionNode->getElementsByTagName("returns");
            if ($returns->length > 0) {
                $returns = $returns->item(0)->getElementsByTagName("variable");
                if ($returns->length > 0) {
                    $returnOptions[] = $returns->item(0)->getAttribute("type");
                }
            }
        }
        if (sizeof($parameterComments) > 0) {
            $return .= "\t".' * Parameter options:'."\n";
            $return .= join("\n", $parameterComments)."\n";
            $return .= "\t".' * @param mixed,... See function description for parameter options'."\n";
        }
        $return .= "\t".' * @return '.join("|", array_unique($returnOptions))."\n";
        $return .= "\t".' * @throws Exception invalid function signature message'."\n"; 
        $return .= "\t".' */'."\n";
        $return .= "\t".'public function '.$functionName;
        if (sizeof($variableTypeOptions) > 0) {
            $return .= '($mixed = null) {'."\n";
            $return .= "\t\t".'$validParameters = array('."\n";
            foreach ($variableTypeOptions as $variableTypeOption) {
                $return .= "\t\t\t".'"'.$variableTypeOption.'",'."\n";
            }
            $return .= "\t\t".');'."\n";
            $return .= "\t\t".'$args = func_get_args();'."\n";
            $return .= "\t\t".'$this->_checkArguments($args, $validParameters);'."\n";
            $return .= "\t\t".'return $this->__soapCall("'.
                $functionNodeList[0]->getAttribute("name").'", $args);'."\n";
        } else {
            $return .= '() {'."\n";
            $return .= "\t\t".'return $this->__soapCall("'.
                $functionNodeList[0]->getAttribute("name").'", array());'."\n";
        }
        $return .= "\t".'}'."\n";
        
        return $return;
    }
    
    /**
     * Saves the PHP source code that has been loaded to a target directory.
     * 
     * Services will be saved by their validated name, and classes will be included
     * with each service file so that they can be utilized independently.
     * 
     * @param string $outputDirectory the destination directory for the source code
     * @return array array of source code files that were written out
     * @throws WSDLInterpreterException problem in writing out service sources
     * @access public
     * @todo Add split file options for more efficient output
     */
    public function savePHP($outputDirectory) 
    {
        if (sizeof($this->_servicePHPSources) == 0) {
            throw new WSDLInterpreterException("No services loaded");
        }
        $classSource = join("\n\n", $this->_classPHPSources);
        $outputFiles = array();
        foreach ($this->_servicePHPSources as $serviceName => $serviceCode) {
            $filename = $outputDirectory."/".$serviceName.".php";
            if (file_put_contents($filename, 
                    "<?php\n\n".$classSource."\n\n".$serviceCode."\n\n?>")) {
                $outputFiles[] = $filename;
            } else {
				echo 'Failed to place file in '.$outputDirectory;
			}
        }
        if (sizeof($outputFiles) == 0) {
            throw new WSDLInterpreterException("Error writing PHP source files.");
        }
        return $outputFiles;
    }
}

$myWSDLlocation = 'http://joebot.bbcharcoal.com/FreightShip.wsdl';
$wsdlInterpreter = new WSDLInterpreter($myWSDLlocation);
$wsdlInterpreter->savePHP('/mnt/stor9-wc2-dfw1/528353/528995/joebot.bbcharcoal.com/web/content/wsdl/');
?>
