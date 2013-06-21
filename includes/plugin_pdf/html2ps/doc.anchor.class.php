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

/**
 * @package HTML2PS
 * @subpackage Document
 * Contains an Anchor class definition
 * 
 * @see Anchor
 */

/**
 * @package HTML2PS
 * @subpackage Document
 * Defines a position on the PDF page which could be referred by a hyperlink
 *
 * @see GenericFormattedBox::reflow_anchors
 */
class Anchor {
  /**
   * @var string Symbolic name of the location
   * @access public
   */
  var $name;

  /**
   * @var int Page number 
   * @access public
   */
  var $page;

  /**
   * @var float X-coordinate on the selected page
   * @access public
   */
  var $x;

  /**
   * @var float Y-coordinate on the selected page
   * @access public
   */
  var $y;

  /**
   * Constructs a new Anchor object
   *
   * @param string $name symbolic name of the anchor
   * @param int $page page containing this anhor
   * @param int $x X-coordinate of the anchor on the selected page
   * @param int $y Y-coordinate of the anchor on the selected page
   */
  function Anchor($name, $page, $x, $y) {
    $this->name = $name;
    $this->page = $page;
    $this->x    = $x;
    $this->y    = $y;
  }
}

?>