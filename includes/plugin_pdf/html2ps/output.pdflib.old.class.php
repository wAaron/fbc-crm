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
// $Header: /cvsroot/html2ps/output.pdflib.old.class.php,v 1.2 2006/11/11 13:43:53 Konstantin Exp $

require_once(HTML2PS_DIR . 'output.pdflib.class.php');

class OutputDriverPdflibOld extends OutputDriverPdflib {
  function field_multiline_text($x, $y, $w, $h, $value, $name) { 
  }

  function field_text($x, $y, $w, $h, $value, $name) {
  }

  function field_password($x, $y, $w, $h, $value, $name) {
  }

  function field_pushbutton($x, $y, $w, $h) {
  }

  function field_pushbuttonimage($x, $y, $w, $h, $field_name, $value, $actionURL) {
  }

  function field_pushbuttonreset($x, $y, $w, $h) {
  }

  function field_pushbuttonsubmit($x, $y, $w, $h, $field_name, $value, $actionURL) {
  }

  function field_checkbox($x, $y, $w, $h, $name, $value, $checked) {
  }

  function field_radio($x, $y, $w, $h, $groupname, $value, $checked) {
  }

  function field_select($x, $y, $w, $h, $name, $value, $options) { 
  }

  function new_form($name) {
  }
}
?>