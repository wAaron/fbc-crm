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

define('BACKGROUND_ATTACHMENT_SCROLL', 1);
define('BACKGROUND_ATTACHMENT_FIXED', 2);

class CSSBackgroundAttachment extends CSSSubFieldProperty {
  function get_property_code() {
    return CSS_BACKGROUND_ATTACHMENT;
  }

  function get_property_name() {
    return 'background-attachment';
  }

  function default_value() {
    return BACKGROUND_ATTACHMENT_SCROLL;
  }

  function &parse($value_string) {
    if ($value_string === 'inherit') {
      return CSS_PROPERTY_INHERIT;
    };

    if (preg_match('/\bscroll\b/', $value_string)) {
      $value = BACKGROUND_ATTACHMENT_SCROLL;
    } elseif (preg_match('/\bfixed\b/', $value_string)) {
      $value = BACKGROUND_ATTACHMENT_FIXED;
    } else {
      $value = BACKGROUND_ATTACHMENT_SCROLL;
    };

    return $value;
  }
}
?>