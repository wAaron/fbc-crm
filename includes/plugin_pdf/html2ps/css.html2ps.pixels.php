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

class CSSHTML2PSPixels extends CSSPropertyHandler {
  function CSSHTML2PSPixels() { 
    $this->CSSPropertyHandler(false, false); 
  }

  function &default_value() { 
    $value = 800;
    return $value;
  }

  function &parse($value) {
    $value_data = (int)$value;
    return $value_data;
  }

  function get_property_code() {
    return CSS_HTML2PS_PIXELS;
  }

  function get_property_name() {
    return '-html2ps-pixels';
  }
}

CSS::register_css_property(new CSSHTML2PSPixels);

?>