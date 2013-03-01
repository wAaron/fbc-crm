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

class ButtonResetBox extends ButtonBox {
  function ButtonResetBox($text) {
    $this->ButtonBox($text);
  }

  function &create(&$root, &$pipeline) {
    if ($root->has_attribute("value")) {
      $text = $root->get_attribute("value");
    } else {
      $text = DEFAULT_RESET_TEXT;
    };

    $box =& new ButtonResetBox($text);
    $box->readCSS($pipeline->get_current_css_state());

    return $box;
  }

  function readCSS(&$state) {
    parent::readCSS($state);
    
    $this->_readCSS($state, 
                    array(CSS_HTML2PS_FORM_ACTION));
  }

  function _render_field(&$driver) {
    $driver->field_pushbuttonreset($this->get_left_padding(), 
                                   $this->get_top_padding(), 
                                   $this->get_width() + $this->get_padding_left() + $this->get_padding_right(), 
                                   $this->get_height() + $this->get_padding_top() + $this->get_padding_bottom());
  }
}

?>