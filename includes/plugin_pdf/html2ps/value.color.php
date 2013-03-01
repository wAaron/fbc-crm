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

require_once(HTML2PS_DIR . 'value.generic.php');

class Color extends CSSValue {
  function Color($rgb = array(0,0,0), $transparent = true) {
    // We need this 'max' hack, as somethimes we can get values below zero due 
    // the rounding errors... it will cause PDFLIB to die with error message
    // that is not what we want
    $this->r = max($rgb[0] / 255.0, 0);
    $this->g = max($rgb[1] / 255.0, 0);
    $this->b = max($rgb[2] / 255.0, 0);

    $this->transparent = $transparent;
  }

  function apply(&$viewport) {
    $viewport->setrgbcolor($this->r, $this->g, $this->b);
  }

  function blend($color, $alpha) {
    $this->r += ($color->r - $this->r)*$alpha;
    $this->g += ($color->g - $this->g)*$alpha;
    $this->b += ($color->b - $this->b)*$alpha;
  }

  function &copy() {
    $color =& new Color();

    $color->r = $this->r;
    $color->g = $this->g;
    $color->b = $this->b;
    $color->transparent = $this->transparent;

    return $color;
  }

  function equals($rgb) {
    return 
      $this->r == $rgb->r &&
      $this->g == $rgb->g &&
      $this->b == $rgb->b;
  }

  function isTransparent() {
    return $this->transparent;
  }
}

?>