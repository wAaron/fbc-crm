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

class FetcherMemory extends Fetcher {
  var $base_path;
  var $base_url;
  var $content;

  function FetcherMemory($content, $base_path) {
    $this->content   = $content;
    $this->base_path = $base_path;
    $this->base_url  = $base_path;
  }

  function get_base_url() {
    return $this->base_path;
  }

  function &get_data($url) {
    if ($url != $this->base_path) {
      $null = null;
      return $null;
    };

    $data =& new FetchedDataFile($this->content, $this->base_path);
    return $data;
  }

  function set_base_url($base_url) {
    $this->base_url = $base_url;
  }
}


?>