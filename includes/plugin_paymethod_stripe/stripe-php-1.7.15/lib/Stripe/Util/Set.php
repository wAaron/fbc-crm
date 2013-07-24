<?php 
/** 
  * Copyright: dtbaker 2012
  * Licence: Please check CodeCanyon.net for licence details. 
  * More licence clarification available here:  http://codecanyon.net/wiki/support/legal-terms/licensing-terms/ 
  * Deploy: 3053 c28b7e0e323fd2039bb168d857c941ee
  * Envato: 6b31bbe6-ead4-44a3-96e1-d5479d29505b
  * Package Date: 2013-02-27 19:23:35 
  * IP Address: 210.14.75.228
  */

class Stripe_Util_Set
{
  private $_elts;

  public function __construct($members=array())
  {
    $this->_elts = array();
    foreach ($members as $item)
      $this->_elts[$item] = true;
  }

  public function includes($elt)
  {
    return isset($this->_elts[$elt]);
  }

  public function add($elt)
  {
    $this->_elts[$elt] = true;
  }

  public function discard($elt)
  {
    unset($this->_elts[$elt]);
  }

  // TODO: make Set support foreach
  public function toArray()
  {
    return array_keys($this->_elts);
  }
}
