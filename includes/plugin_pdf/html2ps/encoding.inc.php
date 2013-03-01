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
// $Header: /cvsroot/html2ps/encoding.inc.php,v 1.7 2006/06/25 13:55:36 Konstantin Exp $

/**
 * Converts a hexadecimal string  representing UCS-2 character code to
 * UTF-8 encoding
 */
function hex_to_utf8($code) {
  return code_to_utf8(hexdec($code));
}

/**
 * Converts an UTF8-encoded character to UCS-2 integer code
 * TODO: handle sequence incorrect length
 */
function utf8_to_code($utf8) {
  $code = 0;

  if ((ord($utf8{0}) & 0xF0) == 0xF0) {
    // 4-byte sequence
    $code = 
      ((ord($utf8{0}) & 0x07) << 18) | 
      ((ord($utf8{1}) & 0x3F) << 12) | 
      ((ord($utf8{2}) & 0x3F) <<  6) | 
      (ord($utf8{3}) & 0x3F);
  } elseif ((ord($utf8{0}) & 0xE0) === 0xE0) {
    // 3-byte sequence
    $code = 
      ((ord($utf8{0}) & 0x0F) << 12) | 
      ((ord($utf8{1}) & 0x3F) <<  6) | 
      (ord($utf8{2}) & 0x3F);
  } elseif ((ord($utf8{0}) & 0xC0) === 0xC0) {

    // 2-byte sequence
    $code = 
      ((ord($utf8{0}) & 0x1F) << 6) | 
      (ord($utf8{1}) & 0x3F);
  } else {
    // Single-byte sequence
    $code = ord($utf8);
  };
  
  return $code;
}

/**
 * Converts an integer UCS-2 character code to UTF-8 encoding
 */
function code_to_utf8($code) {
  if ($code < 128) {
    return chr($code);
  };

  if ($code < 2048) {
    return chr(0xC0 | (($code >> 6) & 0x1F)) . chr(0x80 | ($code & 0x3F));
  };

  if ($code < 65536) {
    return chr(0xE0 | (($code >> 12) & 0x0F)) . chr(0x80 | (($code >> 6) & 0x3F)) . chr(0x80 | ($code & 0x3F));
  };

  return 
    chr(0xF0 | (($code >> 18) & 0x07)) . 
    chr(0x80 | (($code >> 12) & 0x3F)) . 
    chr(0x80 | (($code >>  6) & 0x3F)) . 
    chr(0x80 | ($code & 0x3F));
}

?>