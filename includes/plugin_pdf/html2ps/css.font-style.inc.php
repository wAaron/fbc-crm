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

class CSSFontStyle extends CSSSubFieldProperty {
  function default_value() {
    return FS_NORMAL;
  }

  function parse($value) {
    $value = trim(strtolower($value));
    switch ($value) {
    case 'inherit':
      return CSS_PROPERTY_INHERIT;
    case 'normal':
      return FS_NORMAL;
    case 'italic':
      return FS_ITALIC;
    case 'oblique':
      return FS_OBLIQUE;
    };
  }

  function get_property_code() {
    return CSS_FONT_STYLE;
  }

  function get_property_name() {
    return 'font-style';
  }

}

?>