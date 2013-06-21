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
class DataFilterEncoding extends DataFilter {
  function DataFilterEncoding($encoding) {
    $this->encoding = $encoding;
  }

  function getEncoding() {
    return $this->encoding;
  }

  function process(&$data) {
    // Remove control symbols if any
    $data->set_content(preg_replace('/[\x00-\x07]/', "", $data->get_content()));

    if (empty($this->encoding)) {
      $encoding = $data->detect_encoding();

      if (is_null($encoding)) {
        $encoding = DEFAULT_ENCODING;
      };
      $converter = Converter::create();
      $data->set_content($converter->to_utf8($data->get_content(), $encoding));
    } else {
      $converter = Converter::create();
      $data->set_content($converter->to_utf8($data->get_content(), $this->encoding));
    };

    return $data;
  }

  function _convert(&$data, $encoding) {
    error_no_method('_convert', get_class($this));
  }
}
?>