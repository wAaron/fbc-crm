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

class CSSOrphans extends CSSPropertyHandler {
  function CSSOrphans() { 
    $this->CSSPropertyHandler(true, false); 
  }

  function default_value() { 
    return 2; 
  }

  function parse($value) {
    return (int)$value;
  }

  function get_property_code() {
    return CSS_ORPHANS;
  }

  function get_property_name() {
    return 'orphans';
  }
}

CSS::register_css_property(new CSSOrphans);

?>