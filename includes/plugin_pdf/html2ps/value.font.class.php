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

class ValueFont {
  var $style;
  var $weight;
  var $size;
  var $family;
  var $line_height;

  function ValueFont() {
  }

  function &copy() {
    $font =& new ValueFont;
    $font->style  = $this->style;
    $font->weight = $this->weight;

    if ($this->size === CSS_PROPERTY_INHERIT) {
      $font->size = CSS_PROPERTY_INHERIT;
    } else {
      $font->size = $this->size->copy();
    };

    $font->family = $this->family;

    if ($this->line_height === CSS_PROPERTY_INHERIT) {
      $font->line_height = CSS_PROPERTY_INHERIT;
    } else {
      $font->line_height = $this->line_height->copy();
    };

    return $font;
  }

  function units2pt($base_font_size) {
    $this->size->units2pt($base_font_size);
    $this->line_height->units2pt($base_font_size);
  }

  function doInherit(&$state) {
    if ($state->get_propertyDefaultFlag(CSS_FONT_SIZE)) {
      $this->size = Value::fromData(1, UNIT_EM);
    };

    if ($this->style === CSS_PROPERTY_INHERIT) {
      $this->style = $state->getInheritedProperty(CSS_FONT_STYLE);
    };

    if ($this->weight === CSS_PROPERTY_INHERIT) {
      $this->weight = $state->getInheritedProperty(CSS_FONT_WEIGHT);
    };
    
    if ($this->size === CSS_PROPERTY_INHERIT) {
      $size = $state->getInheritedProperty(CSS_FONT_SIZE);
      $this->size = $size->copy();
    };

    if ($this->family === CSS_PROPERTY_INHERIT) {
      $this->family = $state->getInheritedProperty(CSS_FONT_FAMILY);
    };

    if ($this->line_height === CSS_PROPERTY_INHERIT) {
      $this->line_height = $state->getInheritedProperty(CSS_LINE_HEIGHT);
    };
  }
}

?>