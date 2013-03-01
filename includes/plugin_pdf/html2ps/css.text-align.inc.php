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
// $Header: /cvsroot/html2ps/css.text-align.inc.php,v 1.10 2006/09/07 18:38:14 Konstantin Exp $

define('TA_LEFT',0);
define('TA_RIGHT',1);
define('TA_CENTER',2);
define('TA_JUSTIFY',3);

class CSSTextAlign extends CSSPropertyStringSet {
  function CSSTextAlign() { 
    $this->CSSPropertyStringSet(true, 
                                true,
                                array('inherit' => CSS_PROPERTY_INHERIT,
                                      'left'    => TA_LEFT,
                                      'right'   => TA_RIGHT,
                                      'center'  => TA_CENTER,
                                      'middle'  => TA_CENTER,
                                      'justify' => TA_JUSTIFY)); 
  }
  
  function default_value() { return TA_LEFT; }

  function value2pdf($value) { 
    switch ($value) {
    case TA_LEFT:
      return "ta_left";
    case TA_RIGHT:
      return "ta_right";
    case TA_CENTER:
      return "ta_center";
    case TA_JUSTIFY:
      return "ta_justify";
    default:
      return "ta_left";
    }
  }

  function get_property_code() {
    return CSS_TEXT_ALIGN;
  }

  function get_property_name() {
    return 'text-align';
  }
}

CSS::register_css_property(new CSSTextAlign);

?>