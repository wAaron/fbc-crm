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

class BodyBox extends BlockBox {
  function BodyBox() {
    $this->BlockBox();
  }

  function &create(&$root, &$pipeline) {
    $box = new BodyBox();
    $box->readCSS($pipeline->get_current_css_state());
    $box->create_content($root, $pipeline);
    return $box;
  }

  function get_bottom_background() { 
    return $this->get_bottom_margin(); 
  }

  function get_left_background()   { 
    return $this->get_left_margin();   
  }

  function get_right_background()  { 
    return $this->get_right_margin();  
  }

  function get_top_background()    { 
    return $this->get_top_margin();    
  }

  function reflow(&$parent, &$context) {
    parent::reflow($parent, $context);
    
    // Extend the body height to fit all contained floats
    $float_bottom = $context->float_bottom();
    if (!is_null($float_bottom)) {
      $this->extend_height($float_bottom);
    };
  }
}

?>