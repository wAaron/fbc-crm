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

class FeatureAutomargins {
  var $_top_margin;
  var $_bottom_margin;

  function FeatureAutomargins() {
    $this->_top_margin = 0;
    $this->_bottom_margin = 0;
  }

  function handle_before_page_heights($params) {
    $pipeline =& $params['pipeline'];
    $document =& $params['document'];
    $media =& $params['media'];

    $boxes = $pipeline->reflow_margin_boxes(0, $media);

    $this->_top_margin = max($boxes[CSS_MARGIN_BOX_SELECTOR_TOP]->get_real_full_height(),
                             $boxes[CSS_MARGIN_BOX_SELECTOR_TOP_LEFT_CORNER]->get_real_full_height(),
                             $boxes[CSS_MARGIN_BOX_SELECTOR_TOP_LEFT]->get_real_full_height(),
                             $boxes[CSS_MARGIN_BOX_SELECTOR_TOP_CENTER]->get_real_full_height(),
                             $boxes[CSS_MARGIN_BOX_SELECTOR_TOP_RIGHT]->get_real_full_height(),
                             $boxes[CSS_MARGIN_BOX_SELECTOR_TOP_RIGHT_CORNER]->get_real_full_height());
    $this->_bottom_margin = max($boxes[CSS_MARGIN_BOX_SELECTOR_BOTTOM]->get_real_full_height(),
                                $boxes[CSS_MARGIN_BOX_SELECTOR_BOTTOM_LEFT_CORNER]->get_real_full_height(),
                                $boxes[CSS_MARGIN_BOX_SELECTOR_BOTTOM_LEFT]->get_real_full_height(),
                                $boxes[CSS_MARGIN_BOX_SELECTOR_BOTTOM_CENTER]->get_real_full_height(),
                                $boxes[CSS_MARGIN_BOX_SELECTOR_BOTTOM_RIGHT]->get_real_full_height(),
                                $boxes[CSS_MARGIN_BOX_SELECTOR_BOTTOM_RIGHT_CORNER]->get_real_full_height());
    
    $media->margins['top'] = $this->_top_margin / mm2pt(1);
    $media->margins['bottom'] = $this->_bottom_margin / mm2pt(1);
    
    $pipeline->output_driver->update_media($media);
    $pipeline->_setupScales($media);
  }

  function install(&$pipeline, $params) {
    $dispatcher =& $pipeline->get_dispatcher();
    $dispatcher->add_observer('before-page-heights', array(&$this, 'handle_before_page_heights'));
  }
}

?>