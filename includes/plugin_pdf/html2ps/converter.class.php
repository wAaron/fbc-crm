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
// $Header: /cvsroot/html2ps/converter.class.php,v 1.6 2006/06/25 13:55:35 Konstantin Exp $

class Converter {
  function create() {
//     if (function_exists('iconv')) {
//       return new IconvConverter;
//     } else {
      return new PurePHPConverter;
//     }
  }
}

class IconvConverter {
  function to_utf8($string, $encoding) {
    return iconv(strtoupper($encoding), "UTF-8", $string);
  }
}

class PurePHPConverter {
  function apply_aliases($encoding) {
    global $g_encoding_aliases;

    if (isset($g_encoding_aliases[$encoding])) {
      return $g_encoding_aliases[$encoding];
    }

    return $encoding;
  }

  function to_utf8($html, $encoding) {
    global $g_utf8_converters;

    $encoding = $this->apply_aliases($encoding);

    if ($encoding === 'iso-8859-1') {
      return utf8_encode($html);
    } elseif ($encoding === 'utf-8') {
      return $html;
    } elseif(isset($g_utf8_converters[$encoding])) {
      return $this->something_to_utf8($html, $g_utf8_converters[$encoding][0]);
    } else {
      die("Unsupported encoding detected: '$encoding'");
    };
  }

  function something_to_utf8($html, &$mapping) {
    for ($i=0; $i < strlen($html); $i++) {
      $replacement = code_to_utf8($mapping[$html{$i}]);
      if ($replacement != $html{$i}) {
        $html = substr_replace($html, $replacement, $i, 1);
        $i += strlen($replacement) - 1;
      };
    };
    return $html;
  }
}
?>