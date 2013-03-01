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

require_once(HTML2PS_DIR . 'value.generic.php');

/**
 * @version 1.0
 * @created 14-���-2006 17:49:11
 */
class WidthConstraint extends CSSValue {
  var $_min_width;

  function WidthConstraint() {
    $this->_min_width = Value::fromData(0, UNIT_PT);
  }

  function apply($w, $pw) {
    $width = $this->_apply($w, $pw);
    $width = max($this->_min_width->getPoints(), $width);
    return $width;
  }

  function &copy() {
    $copy =& $this->_copy();

    if ($this->_min_width == CSS_PROPERTY_INHERIT) {
      $copy->_min_width = CSS_PROPERTY_INHERIT;
    } else {
      $copy->_min_width = $this->_min_width->copy();
    };

    return $copy;
  }

  function units2pt($base) {
    $this->_units2pt($base);
    $this->_min_width->units2pt($base);
  }

  function isNull() { 
    return false; 
  }

  function isFraction() {
    return false;
  }

  function isConstant() {
    return false;
  }
}
?>