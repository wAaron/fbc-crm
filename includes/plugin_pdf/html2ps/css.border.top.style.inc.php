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
// $Header: /cvsroot/html2ps/css.border.top.style.inc.php,v 1.1 2006/09/07 18:38:13 Konstantin Exp $

class CSSBorderTopStyle extends CSSSubProperty {
  function CSSBorderTopStyle(&$owner) {
    $this->CSSSubProperty($owner);
  }

  function set_value(&$owner_value, &$value) {
    $owner_value->top->style = $value;
  }

  function get_value(&$owner_value) {
    return $owner_value->top->style;
  }

  function get_property_code() {
    return CSS_BORDER_TOP_STYLE;
  }

  function get_property_name() {
    return 'border-top-style';
  }

  function parse($value) {
    if ($value == 'inherit') {
      return CSS_PROPERTY_INHERIT;
    }

    return CSSBorderStyle::parse_style($value);
  }
}

?>