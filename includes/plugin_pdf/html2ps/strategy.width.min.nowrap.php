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

class StrategyWidthMinNowrap {
  var $_maxw;
  var $_cmaxw;

  function StrategyWidthMinNowrap() {
  }

  function add_width($delta) {
    $this->_cmaxw += $delta;
  }

  function line_break() {
    $this->_maxw  = max($this->_maxw, $this->_cmaxw);
    $this->_cmaxw = 0;
  }

  function apply(&$box, &$context) {
    $this->_maxw = 0;
    
    // We need to add text indent to the width
    $ti = $box->get_css_property(CSS_TEXT_INDENT);
    $this->add_width($ti->calculate($box));

    for ($i=0, $size = count($box->content); $i<$size; $i++) {
      $child =& $box->content[$i];
      if ($child->isLineBreak()) {
        $this->line_break();
      } elseif (!$child->out_of_flow()) {
        if (is_inline($child)) {
          // Inline boxes content will not be wrapped, so we may calculate its max width
          $this->add_width($child->get_max_width($context));
        } else {
          // Non-inline boxes cause line break
          $this->line_break();
          $this->add_width($child->get_min_width($context));
          $this->line_break();
        }
      };
    }

    // Check if last line have maximal width
    $this->line_break();

    // Apply width constraint to min width. Return maximal value
    $wc = $box->get_css_property(CSS_WIDTH);
    return max($this->_maxw, $wc->apply($this->_maxw, $box->parent->get_width())) + $box->_get_hor_extra();
  }
}

?>