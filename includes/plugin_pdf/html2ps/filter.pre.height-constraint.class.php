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

/**
 * This is an internal HTML2PS filter; you never need to use it.
 */

class PreTreeFilterHeightConstraint extends PreTreeFilter {
  function process(&$tree, $data, &$pipeline) {
    if (!is_a($tree, 'GenericFormattedBox')) {
      return;
    };

    /**
     * In non-quirks mode, percentage height should be ignored for children of boxes having
     * non-constrained height
     */
    global $g_config;
    if ($g_config['mode'] != 'quirks') {
      if (!is_null($tree->parent)) {
        $parent_hc = $tree->parent->get_height_constraint();
        $hc        = $tree->get_height_constraint();

        if (is_null($parent_hc->constant) &&
            $hc->constant[1]) {
          $hc->constant = null;
          $tree->put_height_constraint($hc);
        };
      };
    };

    /**
     * Set box height to constrained value
     */
    $hc     = $tree->get_height_constraint();
    $height = $tree->get_height();

    $tree->height = $hc->apply($height, $tree);

    /**
     * Proceed to this box children
     */
    if (is_a($tree, 'GenericContainerBox')) {
      for ($i=0, $size = count($tree->content); $i<$size; $i++) {
        $this->process($tree->content[$i], $data, $pipeline);
      };
    };
  }
}
?>