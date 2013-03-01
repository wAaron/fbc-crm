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

class InlineContentBuilderPre extends InlineContentBuilder {
  function InlineContentBuilderPre() {
    $this->InlineContentBuilder();
  }

  /**
   * CSS 2.1 16.6 Whitespace: the 'white-space' property
   *
   * pre
   *
   * This  value prevents  user  agents from  collapsing sequences  of
   * whitespace. Lines are  only broken at newlines in  the source, or
   * at occurrences of "\A" in generated content.
   */
  function build(&$box, $text, &$pipeline) {
    $text = $this->remove_trailing_linefeeds($text);
    $lines = $this->break_into_lines($text);

    $parent =& $box->get_parent_node();

    for ($i=0, $size = count($lines); $i<$size; $i++) {
      $line = $lines[$i];
      $box->process_word($line, $pipeline);

      if ((!$parent || $parent->isBlockLevel()) && $i < $size - 1) {
        $this->add_line_break($box, $pipeline);
      };
    };
  }
}

?>