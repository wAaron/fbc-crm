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

class Dispatcher {
  var $_callbacks;

  function Dispatcher() {
    $this->_callbacks = array();
  }

  /**
   * @param String $type name of the event to dispatch
   */
  function add_event($type) {
    $this->_callbacks[$type] = array();
  }

  function add_observer($type, $callback) {
    $this->_check_event_type($type);
    $this->_callbacks[$type][] = $callback;
  }

  function fire($type, $params) {
    $this->_check_event_type($type);

    foreach ($this->_callbacks[$type] as $callback) {
      call_user_func($callback, $params);
    };
  }

  function _check_event_type($type) {
    if (!isset($this->_callbacks[$type])) {
      die(sprintf("Invalid event type: %s", $type));
    };
  }
}

?>