<?php 
/** 
  * Copyright: dtbaker 2012
  * Licence: Please check CodeCanyon.net for licence details. 
  * More licence clarification available here:  http://codecanyon.net/wiki/support/legal-terms/licensing-terms/ 
  * Deploy: 3053 c28b7e0e323fd2039bb168d857c941ee
  * Envato: 6b31bbe6-ead4-44a3-96e1-d5479d29505b
  * Package Date: 2013-02-27 19:23:35 
  * IP Address: 210.14.75.228
  */
/**
 * A simple wrapper for the SOAP API as well as a helper function
 * to generate a documentation file from the WSDL.
 *
 * @package    AuthorizeNet
 * @subpackage AuthorizeNetSoap
 */

/**
 * A simple wrapper for the SOAP API as well as a helper function
 * to generate a documentation file from the WSDL.
 *
 * @package    AuthorizeNet
 * @subpackage AuthorizeNetSoap
 * @todo       Make the doc file a usable class.
 */
class AuthorizeNetSOAP extends SoapClient
{
    const WSDL_URL = "https://api.authorize.net/soap/v1/Service.asmx?WSDL";
    const LIVE_URL = "https://api.authorize.net/soap/v1/Service.asmx";
    const SANDBOX_URL = "https://apitest.authorize.net/soap/v1/Service.asmx";
    
    public $sandbox;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct(self::WSDL_URL);
        $this->__setLocation(self::SANDBOX_URL);
    }
    
    /**
     * Switch between the sandbox or production gateway.
     *
     * @param bool
     */
    public function setSandbox($bool)
    {
        $this->__setLocation(($bool ? self::SANDBOX_URL : self::LIVE_URL));
    }

    /**
     * Get all types as PHP Code.
     * @return string
     */
    public function getSoapTypes()
    {
        $string = "";
        $types = $this->__getTypes();
        foreach ($types as $type) {
            if (preg_match("/struct /",$type)) {
                $type = preg_replace("/struct /","class ",$type);
                $type = preg_replace("/ (\w+) (\w+);/","    // $1\n    public \$$2;",$type);
                $string .= $type ."\n";
            }
        }
        return $string;
    }
    
    /**
     * Get all methods as PHP Code.
     * @return string
     */
    public function getSoapMethods()
    {
        $string = "";
        $functions = array();
        $methods = $this->__getFunctions();
        foreach ($methods as $index => $method) {
            $sig = explode(" ", $method, 2);
            if (!isset($functions[$sig[1]])) {
                $string .= "    /**\n     * @return {$sig[0]}\n    */\n    public function {$sig[1]} {}\n\n";
                $functions[$sig[1]] = true;
            }
        }
        return $string;
    }
    
    /**
     * Create a file from the WSDL for reference.
     */
    public function saveSoapDocumentation($path)
    {
        $string =  "<?php\n";
        $string .= "/**\n";
        $string .= " * Auto generated documentation for the AuthorizeNetSOAP API.\n";
        $string .= " * Generated " . date("m/d/Y") . "\n";
        $string .= " */\n";
        $string .= "class AuthorizeNetSOAP\n";
        $string .= "{\n" . $this->getSoapMethods() . "\n}\n\n" . $this->getSoapTypes() ."\n\n ?>";
        return file_put_contents($path, $string);
    }
    
    
    
}