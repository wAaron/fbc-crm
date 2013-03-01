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
class ContentType {
  var $default_extension;
  var $mime_type;

  function ContentType($extension, $mime) {
    $this->default_extension = $extension;
    $this->mime_type = $mime;
  }

  function png() {
    return new ContentType('png', 'image/png');
  }

  function gz() {
    return new ContentType('gz', 'application/gzip');
  }

  function pdf() {
    return new ContentType('pdf', 'application/pdf');
  }

  function ps() {
    return new ContentType('ps', 'application/postscript');
  }
}
?>