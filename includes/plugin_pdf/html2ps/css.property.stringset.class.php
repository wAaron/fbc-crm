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

class CSSPropertyStringSet extends CSSPropertyHandler {
  var $_mapping;
  var $_keys;

  function CSSPropertyStringSet($inherit, $inherit_text, $mapping) {
    $this->CSSPropertyHandler($inherit, $inherit_text);

    $this->_mapping = $mapping;

    /**
     * Unfortunately, isset($this->_mapping[$key]) will return false
     * for $_mapping[$key] = null. As CSS_PROPERTY_INHERIT value is 'null',
     * this should be avoided using the hack below
     */
    $this->_keys    = $mapping;
    foreach ($this->_keys as $key => $value) {
      $this->_keys[$key] = 1;
    };
  }

  function parse($value) {
    $value = trim(strtolower($value));

    if (isset($this->_keys[$value])) {
      return $this->_mapping[$value];
    };

    return $this->default_value();
  }
}

?>