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

class LayoutVertical {
  // Calculate the vertical offset of current box due the 'clear' CSS property
  // 
  // @param $y initial Y coordinate to begin offset from
  // @param $context flow context containing the list of floats to interact with
  // @return updated value of Y coordinate
  //
  function apply_clear($box, $y, &$context) {
    $clear = $box->get_css_property(CSS_CLEAR);

    // Check if we need to offset box vertically due the 'clear' property
    if ($clear == CLEAR_BOTH || $clear == CLEAR_LEFT) {
      $floats =& $context->current_floats();
      for ($cf = 0; $cf < count($floats); $cf++) {
        $current_float =& $floats[$cf];
        if ($current_float->get_css_property(CSS_FLOAT) == FLOAT_LEFT) {
          // Float vertical margins are never collapsed
          //
          $margin = $box->get_css_property(CSS_MARGIN);
          $y = min($y, $current_float->get_bottom_margin() - $margin->top->value);
        };
      }
    };
    
    if ($clear == CLEAR_BOTH || $clear == CLEAR_RIGHT) {
      $floats =& $context->current_floats();
      for ($cf = 0; $cf < count($floats); $cf++) {
        $current_float =& $floats[$cf];
        if ($current_float->get_css_property(CSS_FLOAT) == FLOAT_RIGHT) {
          // Float vertical margins are never collapsed
          $margin = $box->get_css_property(CSS_MARGIN);
          $y = min($y, $current_float->get_bottom_margin() - $margin->top->value);
        };
      }
    };
    
    return $y;
  }
}

?>