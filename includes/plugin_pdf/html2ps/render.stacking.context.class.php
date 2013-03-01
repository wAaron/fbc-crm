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

class RenderStackingContext {
  var $_stacking_levels;

  function RenderStackingContext() {
    $this->set_stacking_levels(array());

    $level =& new StackingLevel('in-flow-non-inline');
    $this->add_stacking_level($level);

    $level =& new StackingLevel('in-flow-floats');
    $this->add_stacking_level($level);

    $level =& new StackingLevel('in-flow-inline');
    $this->add_stacking_level($level);
  }

  function get_stacking_levels() {
    return $this->_stacking_levels;
  }

  function set_stacking_levels($levels) {
    $this->_stacking_levels = $levels;
  }
}

?>