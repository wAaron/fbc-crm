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
// $Header: /cvsroot/html2ps/css.line-height.inc.php,v 1.15 2006/11/11 13:43:52 Konstantin Exp $

require_once(HTML2PS_DIR . 'value.line-height.class.php');

/**
 * We'll treat 'line-height' as a subproperty of 'font', as it can be set using 
 * 'font' value
 */
class CSSLineHeight extends CSSSubFieldProperty {
  var $_defaultValue;

  function CSSLineHeight(&$owner, $field) {
    $this->CSSSubFieldProperty($owner, $field);

    $this->_defaultValue = new LineHeight_Relative(1.1);
  }

  function default_value() {
    return $this->_defaultValue;
  }

  function parse($value) {
    if ($value === 'inherit') {
      return CSS_PROPERTY_INHERIT;
    };

    // <Number>
    // The used value of the property is this number multiplied by the element's font size. 
    // Negative values are illegal. The computed value is the same as the specified value.
    if (preg_match("/^\d+(\.\d+)?$/",$value)) { 
      return new LineHeight_Relative((float)$value);
    };

    // <percentage>
    // The computed value of the property is this percentage multiplied by the element's 
    // computed font size. Negative values are illegal.  
    if (preg_match("/^\d+%$/",$value)) { 
      return new LineHeight_Relative(((float)$value)/100);
    };

    // normal
    // Tells user agents to set the used value to a "reasonable" value based on the font of the element. 
    // The value has the same meaning as <number>. We recommend a used value for 'normal' between 1.0 to 1.2. 
    // The computed value is 'normal'.
    if (trim($value) === "normal") { 
      return $this->default_value();
    };
  
    // <length>
    // The specified length is used in the calculation of the line box height. 
    // Negative values are illegal.  
    return new LineHeight_Absolute($value);
  }

  function get_property_code() {
    return CSS_LINE_HEIGHT;
  }

  function get_property_name() {
    return 'line-height';
  }
}

?>
