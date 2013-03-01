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

class ListStyleImage {
  var $_url;
  var $_image;

  function ListStyleImage($url, $image) {
    $this->_url = $url;
    $this->_image = $image;
  }

  function &copy() {
    $value =& new ListStyleImage($this->_url, $this->_image);
    return $value;
  }

  function is_default() { 
    return is_null($this->_url); 
  }
}

?>