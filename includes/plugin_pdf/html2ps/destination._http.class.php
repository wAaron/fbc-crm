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
class DestinationHTTP extends Destination {  
  function DestinationHTTP($filename) {
    $this->Destination($filename);
  }

  function headers($content_type) {
    die("Unoverridden 'header' method called in ".get_class($this));
  }

  function process($tmp_filename, $content_type) {
    header("Content-Type: ".$content_type->mime_type);
    
    $headers = $this->headers($content_type);
    foreach ($headers as $header) {
      header($header);
    };

    // NOTE: readfile does not work well with some Windows machines
    // echo(file_get_contents($tmp_filename));
    readfile($tmp_filename);
  }
}
?>