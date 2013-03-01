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

class RenderQueue {
  var $_root_context;

  function RenderQueue() {
    $this->set_root_context(null);
  }

  function get_root_context() {
    return $this->_root_context;
  }

  function set_root_context(&$context) {
    $this->_root_context =& $context;
  }
}

?>