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
// $Header: /cvsroot/html2ps/css.white-space.inc.php,v 1.9 2007/01/24 18:55:52 Konstantin Exp $

define('WHITESPACE_NORMAL',   0);
define('WHITESPACE_PRE',      1);
define('WHITESPACE_NOWRAP',   2);
define('WHITESPACE_PRE_WRAP', 3);
define('WHITESPACE_PRE_LINE', 4);

class CSSWhiteSpace extends CSSPropertyStringSet {
  function CSSWhiteSpace() { 
    $this->CSSPropertyStringSet(true, 
                                true,
                                array('normal'   => WHITESPACE_NORMAL,
                                      'pre'      => WHITESPACE_PRE,
                                      'pre-wrap' => WHITESPACE_PRE_WRAP,
                                      'nowrap'   => WHITESPACE_NOWRAP,
                                      'pre-line' => WHITESPACE_PRE_LINE)); 
  }

  function default_value() { 
    return WHITESPACE_NORMAL; 
  }

  function get_property_code() {
    return CSS_WHITE_SPACE;
  }

  function get_property_name() {
    return 'white-space';
  }
}

CSS::register_css_property(new CSSWhiteSpace);
  
?>