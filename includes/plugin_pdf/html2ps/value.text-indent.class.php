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

// Stack value is an array containing two values:
// string value of text-indent proprty and 
// boolean flag indication of this value is relative (true) or absolute (false)

class TextIndentValuePDF extends CSSValue {
  var $raw_value;

  function calculate(&$box) {
    if ($this->raw_value[1]) {
      // Is a percentage
      return $box->get_width() * $this->raw_value[0] / 100;
    } else {
      return $this->raw_value[0];
    };
  }

  function &copy() {
    $value =& new TextIndentValuePDF($this->raw_value);
    return $value;
  }

  function is_default() {
    return $this->raw_value[0] == 0;
  }

  function TextIndentValuePDF($value) {
    $this->raw_value = $value;
  }

  function units2pt($base) {
    $this->raw_value[0] = units2pt($this->raw_value[0], $base);
  }
}

?>