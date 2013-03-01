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
class FlowViewport {
  var $left;
  var $top;
  var $width;
  var $height;

  function FlowViewport() {
    $this->left = 0;
    $this->top = 0;
    $this->width = 0;
    $this->height = 0;
  }

  function &create(&$box) {
    $viewport = new FlowViewport;
    $viewport->left   = $box->get_left_padding();
    $viewport->top    = $box->get_top_padding();
    
    $padding = $box->get_css_property(CSS_PADDING);
    
    $viewport->width  = $box->get_width() + $padding->left->value + $padding->right->value;
    $viewport->height = $box->get_height() + $padding->top->value + $padding->bottom->value;

    return $viewport;
  }

  function get_left() { return $this->left; }
  function get_top() { return $this->top; }
  function get_height() { return $this->height; }
  function get_width() { return $this->width; }
}
?>