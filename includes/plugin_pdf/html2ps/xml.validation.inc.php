<?php 
/** 
  * Copyright: dtbaker 2012
  * Licence: Please check CodeCanyon.net for licence details. 
  * More licence clarification available here:  http://codecanyon.net/wiki/support/legal-terms/licensing-terms/ 
  * Deploy: 3053 c28b7e0e323fd2039bb168d857c941ee
  * Envato: 6b31bbe6-ead4-44a3-96e1-d5479d29505b
  * Package Date: 2013-02-27 19:09:56 
  * IP Address: 
  */
class HTML2PS_XMLUtils {
  function valid_attribute_name($name) {
    // Note that, technically, it is not correct, as XML standard treats as letters 
    // characters other than a-z too.. Nevertheless, this simple variant 
    // will do for XHTML/HTML

    return preg_match("/[a-z_:][a-z0-9._:.]*/i",$name);
  }
}
?>