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

class Rectangle {
  var $ur;
  var $ll;
  
  function Rectangle($ll, $ur) {
    $this->ll = $ll;
    $this->ur = $ur;
  }

  function getWidth() {
    return $this->ur->x - $this->ll->x;
  }

  function getHeight() {
    return $this->ur->y - $this->ll->y;
  }

  function normalize() {
    if ($this->ur->x < $this->ll->x) {
      $x = $this->ur->x;
      $this->ur->x = $this->ll->x;
      $this->ll->x = $x;
    };

    if ($this->ur->y < $this->ll->y) {
      $y = $this->ur->y;
      $this->ur->y = $this->ll->y;
      $this->ll->y = $y;
    };
  }
}

?>