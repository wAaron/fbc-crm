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

require_once(HTML2PS_DIR . 'box.generic.formatted.php');

class SimpleInlineBox extends GenericBox {
  function SimpleInlineBox() {
    $this->GenericBox();
  }

  function readCSS(&$state) {
    parent::readCSS($state);

    $this->_readCSS($state,
                    array(CSS_TEXT_DECORATION,
                          CSS_TEXT_TRANSFORM));
    
    // '-html2ps-link-target'
    global $g_config;
    if ($g_config["renderlinks"]) {
      $this->_readCSS($state, 
                      array(CSS_HTML2PS_LINK_TARGET));
    };
  }

  function get_extra_left() {
    return 0;
  }

  function get_extra_top() {
    return 0;
  }

  function get_extra_right() {
    return 0;
  }

  function get_extra_bottom() {
    return 0;
  }

  function show(&$driver) {
    parent::show($driver);

    $strategy =& new StrategyLinkRenderingNormal();
    $strategy->apply($this, $driver);
  }
}
?>