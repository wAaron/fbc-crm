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

class OutputFilterGZip extends OutputFilter {
  function content_type() {
    return null;
    //    return ContentType::gz();
  }

  function process($tmp_filename) {
    $output_file = $tmp_filename.'.gz';

    $file = gzopen($output_file, "wb");
    gzwrite($file, file_get_contents($tmp_filename));
    gzclose($file);

    unlink($tmp_filename);
    return $output_file;
  }
}
?>