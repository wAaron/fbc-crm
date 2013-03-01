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

class CSSFontFamily extends CSSSubFieldProperty {
  function default_value() {
    return 'times';
  }

  function parse($value) {
    if ($value == 'inherit') {
      return CSS_PROPERTY_INHERIT;
    }

    $subvalues = preg_split("/\s*,\s*/",$value);

    foreach ($subvalues as $subvalue) {
      $subvalue = trim(strtolower($subvalue));   
    
      // Check if current subvalue is not empty (say, in case of 'font-family:;' or 'font-family:family1,,family2;')
      if ($subvalue !== "") {

        // Some multi-word font family names can be enclosed in quotes; remove them
        if ($subvalue{0} == "'") {
          $subvalue = substr($subvalue,1,strlen($subvalue)-2);
        } elseif ($subvalue{0} == '"') {
          $subvalue = substr($subvalue,1,strlen($subvalue)-2);
        };
      
        global $g_font_resolver;
        if ($g_font_resolver->have_font_family($subvalue)) { return $subvalue; };

        global $g_font_resolver_pdf;
        if ($g_font_resolver_pdf->have_font_family($subvalue)) { return $subvalue; };
      };
    };

    // Unknown family type
    return "times";
  }

  function get_property_code() {
    return CSS_FONT_FAMILY;
  }

  function get_property_name() {
    return 'font-family';
  }

}

?>