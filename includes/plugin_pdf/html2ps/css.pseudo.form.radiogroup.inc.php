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

class CSSPseudoFormRadioGroup extends CSSPropertyHandler {
  function CSSPseudoFormRadioGroup() { 
    $this->CSSPropertyHandler(true, true); 
  }

  function default_value() { 
    return null; 
  }

  function parse($value) { 
    return $value;
  }

  function get_property_code() {
    return CSS_HTML2PS_FORM_RADIOGROUP;
  }

  function get_property_name() {
    return '-html2ps-form-radiogroup';
  }
}

CSS::register_css_property(new CSSPseudoFormRadioGroup);

?>