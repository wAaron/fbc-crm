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

class CSSPropertyDeclaration {
  var $_code;
  var $_value;
  var $_important;

  function CSSPropertyDeclaration() {
    $this->_code      = 0;
    $this->_value     = null;
    $this->_important = false;
  }

  function &get_value() {
    return $this->_value;
  }

  function set_code($code) {
    $this->_code = $code;
  }

  function set_important($value) {
    $this->_important = $value;
  }

  function set_value(&$value) {
    $this->_value =& $value;
  }

  function &create($code, $value, $pipeline) {
    $handler =& CSS::get_handler($code);
    if (is_null($handler)) {
      $null = null;
      return $null;
    };

    $declaration =& new CSSPropertyDeclaration();
    $declaration->_code = $code;

    if (preg_match("/^(.*)!\s*important\s*$/", $value, $matches)) {
      $value     = $matches[1];
      $declaration->_important = true;
    } else {
      $declaration->_important = false;
    };

    $declaration->_value = $handler->parse($value, $pipeline);
    return $declaration;
  }

  function get_code() {
    return $this->_code;
  }

  function &copy() {
    $declaration =& new CSSPropertyDeclaration();
    $declaration->_code = $this->_code;

    if (is_object($this->_value)) {
      $declaration->_value =& $this->_value->copy();
    } else {
      $declaration->_value =& $this->_value;
    };

    $declaration->_important = $this->_important;

    return $declaration;
  }

  function is_important() {
    return $this->_important;
  }
}

?>