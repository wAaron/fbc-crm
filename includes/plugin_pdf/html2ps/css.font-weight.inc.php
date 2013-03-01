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

class CSSFontWeight extends CSSSubFieldProperty {
  function default_value() {
    return WEIGHT_NORMAL;
  }

  function parse($value) {
    switch (trim(strtolower($value))) {
    case 'inherit':
      return CSS_PROPERTY_INHERIT;
    case 'bold':
    case '700':
    case '800':
    case '900':
    case 'bolder':
      return WEIGHT_BOLD;
    case 'lighter':
    case 'normal':
    case '100':
    case '200':
    case '300':
    case '400':
    case '500':
    case '600':
    default:
      return WEIGHT_NORMAL;
    };
  }

  function get_property_code() {
    return CSS_FONT_WEIGHT;
  }

  function get_property_name() {
    return 'font-weight';
  }
}

?>