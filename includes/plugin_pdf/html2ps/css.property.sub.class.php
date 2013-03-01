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

class CSSSubProperty extends CSSPropertyHandler {
  var $_owner;

  function CSSSubProperty(&$owner) {
    $this->_owner =& $owner;
  }

  function &get(&$state) {
    $owner =& $this->owner();
    $value =& $owner->get($state);
    $subvalue =& $this->get_value($value);
    return $subvalue;
  }

  function is_subproperty() { 
    return true; 
  }

  function &owner() { 
    return $this->_owner; 
  }
 
  function default_value() { 
  }

  function inherit($old_state, &$new_state) { 
  }

  function inherit_text($old_state, &$new_state) { 
  }

  function replace_array($value, &$state_array) {
    $owner =& $this->owner();

    $owner_value = $state_array[$owner->get_property_code()];

    if (is_object($owner_value)) {
      $owner_value = $owner_value->copy();
    };

    if (is_object($value)) {
      $this->set_value($owner_value, $value->copy());
    } else {
      $this->set_value($owner_value, $value);
    };

    $state_array[$owner->get_property_code()] = $owner_value;
  }

  function replace($value, &$state) { 
    $owner =& $this->owner();
    $owner_value = $owner->get($state->getState());

    if (is_object($owner_value)) {
      $owner_value =& $owner_value->copy();
    };

    if (is_object($value)) {
      $value_copy =& $value->copy();
      $this->set_value($owner_value, $value_copy);
    } else {
      $this->set_value($owner_value, $value);
    };

    $owner->replaceDefault($owner_value, $state);
    $state->set_propertyDefaultFlag($this->get_property_code(), false);
  }

  function set_value(&$owner_value, &$value) {
    error_no_method('set_value', get_class($this));
  }

  function &get_value(&$owner_value) {
    error_no_method('get_value', get_class($this));
  }
}

?>