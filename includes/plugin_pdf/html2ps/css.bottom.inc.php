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
// $Header: /cvsroot/html2ps/css.bottom.inc.php,v 1.6 2006/11/11 13:43:52 Konstantin Exp $

require_once(HTML2PS_DIR . 'value.bottom.php');

/**
 * 'bottom'
 *  Value:       <length> | <percentage> | auto | inherit
 *  Initial:     auto
 *  Applies to:  positioned elements
 *  Inherited:   no
 *  Percentages: refer to height of containing block
 *  Media:       visual
 *  Computed  value:  for  'position:relative', see  section  Relative
 *  Positioning.   For   'position:static',   'auto'.  Otherwise:   if
 *  specified  as  a length,  the  corresponding  absolute length;  if
 *  specified as a percentage, the specified value; otherwise, 'auto'.
 *
 * Like 'top',  but specifies  how far a  box's bottom margin  edge is
 * offset  above  the  bottom  of  the  box's  containing  block.  For
 * relatively  positioned boxes,  the offset  is with  respect  to the
 * bottom  edge of  the box  itself. Note:  For  absolutely positioned
 * elements whose containing block  is based on a block-level element,
 * this property is an offset from the padding edge of that element.
 */

class CSSBottom extends CSSPropertyHandler {
  function CSSBottom() { 
    $this->CSSPropertyHandler(false, false); 
    $this->_autoValue = ValueBottom::fromString('auto');
  }

  function _getAutoValue() {
    return $this->_autoValue->copy();
  }

  function default_value() { 
    return $this->_getAutoValue();
  }

  function get_property_code() {
    return CSS_BOTTOM;
  }

  function get_property_name() {
    return 'bottom';
  }

  function parse($value) { 
    return ValueBottom::fromString($value);
  }
}

CSS::register_css_property(new CSSBottom);

?>