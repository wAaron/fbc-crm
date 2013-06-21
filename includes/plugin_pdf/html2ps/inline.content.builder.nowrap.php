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

require_once(HTML2PS_DIR . 'inline.content.builder.php');

class InlineContentBuilderNowrap extends InlineContentBuilder {
  function InlineContentBuilderNowrap() {
    $this->InlineContentBuilder();
  }

  /**
   * CSS 2.1, p 16.6
   * white-space: nowrap
   * This value collapses whitespace as for 'normal', but suppresses line breaks within text
   */
  function build(&$box, $raw_content, &$pipeline) {
    $raw_content = $this->remove_leading_linefeeds($raw_content);
    $raw_content = $this->remove_trailing_linefeeds($raw_content);
    $box->process_word($this->collapse_whitespace($raw_content), $pipeline);
  }
}

?>