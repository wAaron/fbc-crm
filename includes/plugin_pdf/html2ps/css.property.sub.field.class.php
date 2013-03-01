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

class CSSSubFieldProperty extends CSSSubProperty {
  var $_owner;
  var $_owner_field;

  function CSSSubFieldProperty(&$owner, $field) {
    $this->CSSSubProperty($owner);
    $this->_owner_field = $field;
  }

  function set_value(&$owner_value, &$value) {
    $field = $this->_owner_field;
    $owner_value->$field = $value;
  }

  function &get_value(&$owner_value) {
    $field = $this->_owner_field;
    return $owner_value->$field;
  }
}

?>