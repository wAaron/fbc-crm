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

function &parse_css_property($string, &$pipeline) {
  $collection =& parse_css_properties($string, $pipeline);
  return $collection;
}

function &parse_css_properties($string, &$pipeline) {
  $property_collection =& new CSSPropertyCollection();

  while ($string != '') {
    $string = parse_css_properties_property($string, $code);

    if (preg_match('/^\s*:\s*(.*?)$/si', $string, $matches)) {
      $string = $matches[1];
    };

    $string = parse_css_properties_value($string, $value);

    if (preg_match('/^\s*;\s*(.*)$/si', $string, $matches)) {
      $string = $matches[1];
    };

    $property =& CSSPropertyDeclaration::create($code, $value, $pipeline);
    if (!is_null($property)) {
      $property_collection->add_property($property);
    };
  };
  
  return $property_collection;
}

function parse_css_properties_property($string, &$code) {
  $identifier_regexp = CSS::get_identifier_regexp();

  if (!preg_match(sprintf('/^\s*(%s)(.*)/si', $identifier_regexp), $string, $matches)) {
    $code = null;
    return '';
  };

  $name = strtolower(trim($matches[1]));
  $rest = $matches[2];
  $code = CSS::name2code($name);
  return $rest;
}

function parse_css_properties_value($string, &$value) {
  $string1_regexp = CSS_STRING1_REGEXP;
  $string2_regexp = CSS_STRING2_REGEXP;

  $value = '';

  do {
    $matched = false;
    
    list($new_value, $string) = CSS::parse_string($string);   
    if (!is_null($new_value)) {
      $value .= $new_value;
      $matched = true;
    };

    if (preg_match('/^('.CSS_FUNCTION_REGEXP.CSS_IDENT_REGEXP.'\))\s*(.*)$/si', $string, $matches)) {
      $value .= $matches[1];
      $string = $matches[2];
      $matched = true;
    };
  } while ($matched);

  $value_regexp = '[^;]*?';
  if (preg_match(sprintf('/^(%s)(\s*;.*)/si', $value_regexp), $string, $matches)) {
    $value .= trim($matches[1]);
    $rest = $matches[2];

    return $rest;
  };

  $value = $value.trim($string);
  return '';
}

?>