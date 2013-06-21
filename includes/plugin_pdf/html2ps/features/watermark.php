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

class FeatureWatermark {
  var $_text;

  function FeatureWatermark() {
    $this->set_text('');
  }

  function get_text() {
    return $this->_text;
  }

  function handle_after_page($params) {
    $pipeline =& $params['pipeline'];
    $document =& $params['document'];
    $pageno =& $params['pageno'];

    $pipeline->output_driver->_show_watermark($this->get_text());
  }

  function install(&$pipeline, $params) {
    $dispatcher =& $pipeline->get_dispatcher();
    $dispatcher->add_observer('after-page', array(&$this, 'handle_after_page'));

    $this->set_text($params['text']);
  }

  function set_text($text) {
    $this->_text = $text;
  }
}

?>