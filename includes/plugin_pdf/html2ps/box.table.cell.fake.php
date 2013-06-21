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

class FakeTableCellBox extends TableCellBox {
  var $colspan;
  var $rowspan;

  function create(&$pipeline) {
    $box =& new FakeTableCellBox;
    
    $css_state =& $pipeline->get_current_css_state();
    $css_state->pushDefaultState();

    $box->readCSS($css_state);

    $nullbox =& new NullBox;
    $nullbox->readCSS($css_state);
    $box->add_child($nullbox);

    $box->readCSS($css_state);

    $css_state->popState();

    return $box;
  }

  function FakeTableCellBox() {
    // Required to reset any constraints initiated by CSS properties
    $this->colspan = 1;
    $this->rowspan = 1;
    $this->GenericContainerBox();

    $this->setCSSProperty(CSS_DISPLAY, 'table-cell');
    $this->setCSSProperty(CSS_VERTICAL_ALIGN, VA_MIDDLE);
  }

  function show(&$viewport) {
    return true;
  }
  
  function is_fake() {
    return true;
  }

  function get_width_constraint() {
    return new WCNone();
  }

  function get_height_constraint() {
    return new HCConstraint(null, null, null);
  }

  function get_height() {
    return 0;
  }

  function get_top_margin() {
    return 0;
  }

  function get_full_height() {
    return 0;
  }

  function get_max_width() {
    return 0;
  }

  function get_min_width() {
    return 0;
  }
}

?>