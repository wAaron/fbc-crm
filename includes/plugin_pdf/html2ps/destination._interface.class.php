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
class Destination {
  var $filename;

  function Destination($filename) {
    $this->set_filename($filename);
  }

  function filename_escape($filename) { return preg_replace("/[^a-z0-9-]/i","_",$filename); }

  function get_filename() { return empty($this->filename) ? OUTPUT_DEFAULT_NAME : $this->filename; }

  function process($filename, $content_type) {
    die("Oops. Inoverridden 'process' method called in ".get_class($this));
  }

  function set_filename($filename) { $this->filename = $filename; }
}
?>