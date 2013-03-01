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

class StrategyLinkRenderingNormal {
  function StrategyLinkRenderingNormal() {
  }

  function apply(&$box, &$driver) {
    $link_target = $box->get_css_property(CSS_HTML2PS_LINK_TARGET);

    if (CSSPseudoLinkTarget::is_external_link($link_target)) {
      $driver->add_link($box->get_left(), 
                        $box->get_top(), 
                        $box->get_width(), 
                        $box->get_height(), 
                        $link_target);
    } elseif (CSSPseudoLinkTarget::is_local_link($link_target)) {
      if (isset($driver->anchors[substr($link_target,1)])) {
        $anchor = $driver->anchors[substr($link_target,1)];
        $driver->add_local_link($box->get_left(), 
                                $box->get_top(), 
                                $box->get_width(), 
                                $box->get_height(), 
                                $anchor);
      };
    };
  }
}
