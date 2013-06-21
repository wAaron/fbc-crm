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

class FormBox extends BlockBox {
  /**
   * @var String form name; it will be used as a prefix for field names when submitting forms
   * @access private
   */
  var $_name;

  function show(&$driver) {
    global $g_config;
    if ($g_config['renderforms']) {
      $driver->new_form($this->_name);
    };
    return parent::show($driver);
  }

  function &create(&$root, &$pipeline) {
    if ($root->has_attribute('name')) {
      $name = $root->get_attribute('name');
    } elseif ($root->has_attribute('id')) {
      $name = $root->get_attribute('id');
    } else {
      $name = "";
    };

    $box = new FormBox($name);
    $box->readCSS($pipeline->get_current_css_state());
    $box->create_content($root, $pipeline);
    return $box;
  }

  function FormBox($name) {
    $this->BlockBox();

    $this->_name = $name;
  }
}

?>