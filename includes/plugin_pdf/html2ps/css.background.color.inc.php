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
// $Header: /cvsroot/html2ps/css.background.color.inc.php,v 1.16 2007/01/24 18:55:50 Konstantin Exp $

// 'background-color' and color part of 'background' CSS properies handler

class CSSBackgroundColor extends CSSSubFieldProperty {
  function get_property_code() {
    return CSS_BACKGROUND_COLOR;
  }

  function get_property_name() {
    return 'background-color';
  }

  function default_value() {
    // Transparent color
    return new Color(array(0,0,0), true);
  }
  
  // Note: we cannot use parse_color_declaration here directly, as at won't process composite 'background' values
  // containing, say, both background image url and background color; on the other side, 
  // parse_color_declaration slow down if we'll put this composite-value processing there
  function parse($value) {
    // We should not split terms at whitespaces immediately preceeded by ( or , symbols, as 
    // it would break "rgb( xxx, yyy, zzz)" notation
    // 
    // As whitespace could be preceeded by another whitespace, we should prevent breaking 
    // value in the middle of long whitespace too
    $terms = preg_split('/(?<![,(\s])\s+(?![,)\s])/ ',$value);

    // Note that color declaration always will contain only one word; 
    // thus, we can split out value into words and try to parse each one as color
    // if parse_color_declaration returns transparent value, it is possible not 
    // a color part of background declaration
    foreach ($terms as $term) {
      if ($term === 'inherit') {
        return CSS_PROPERTY_INHERIT;
      }

      $color =& parse_color_declaration($term);

      if (!$color->isTransparent()) {
        return $color;
      }
    }

    return CSSBackgroundColor::default_value();
  }

  function get_visible_background_color() {
    $owner =& $this->owner();
    
    for ($i=0, $size = count($owner->_stack); $i<$size; $i++) {
      if ($owner->_stack[$i][0]->color[0] >= 0) {
        return $owner->_stack[$i][0]->color;
      };
    };
    return array(255,255,255);
  }
}

?>