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

define('PAGE_BREAK_AUTO'  ,0);
define('PAGE_BREAK_ALWAYS',1);
define('PAGE_BREAK_AVOID' ,2);
define('PAGE_BREAK_LEFT'  ,3);
define('PAGE_BREAK_RIGHT' ,4);

class CSSPageBreak extends CSSPropertyStringSet {
  function CSSPageBreak() { 
    $this->CSSPropertyStringSet(false, 
                                false,
                                array('inherit' => CSS_PROPERTY_INHERIT,
                                      'auto'    => PAGE_BREAK_AUTO,
                                      'always'  => PAGE_BREAK_ALWAYS,
                                      'avoid'   => PAGE_BREAK_AVOID,
                                      'left'    => PAGE_BREAK_LEFT,
                                      'right'   => PAGE_BREAK_RIGHT)); 
  }

  function default_value() { 
    return PAGE_BREAK_AUTO; 
  }
}
?>