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

require_once(HTML2PS_DIR . 'value.border.class.php');
require_once(HTML2PS_DIR . 'value.border.edge.class.php');

class CSSPseudoTableBorder extends CSSPropertyHandler {
  var $_defaultValue;

  function CSSPseudoTableBorder() {
    $this->CSSPropertyHandler(true, false);

    $this->_defaultValue = BorderPDF::create(array('top'    => array('width' => Value::fromString('2px'), 
                                                                     'color' => array(0,0,0), 
                                                                     'style' => BS_NONE),
                                                   'right'  => array('width' => Value::fromString('2px'), 
                                                                     'color' => array(0,0,0), 
                                                                     'style' => BS_NONE),
                                                   'bottom' => array('width' => Value::fromString('2px'), 
                                                                     'color' => array(0,0,0), 
                                                                     'style' => BS_NONE),
                                                   'left'   => array('width' => Value::fromString('2px'), 
                                                                     'color' => array(0,0,0), 
                                                                     'style' => BS_NONE)));
  }

  function default_value() {
    return $this->_defaultValue->copy();
  }

  function get_property_code() {
    return CSS_HTML2PS_TABLE_BORDER;
  }

  function get_property_name() {
    return '-html2ps-table-border';
  }

  function inherit($old_state, &$new_state) { 
    // Determine parent 'display' value
    $parent_display = $old_state[CSS_DISPLAY];

    // Inherit from table rows and tables
    $inherit_from = array('table-row', 'table', 'table-row-group', 'table-header-group', 'table-footer-group');
    if (array_search($parent_display, $inherit_from) !== FALSE) {
      $this->replace_array($this->get($old_state),
                           $new_state);
      return;
    }
       
    $this->replace_array($this->default_value(), $new_state);
    return;
  }
}

CSS::register_css_property(new CSSPseudoTableBorder());

?>