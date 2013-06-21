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

class InlineContentBuilderFactory {
  function &get($whitespace) {
    switch ($whitespace) {
    case WHITESPACE_NORMAL:
      require_once(HTML2PS_DIR . 'inline.content.builder.normal.php');
      $builder =& new InlineContentBuilderNormal();
      break;
    case WHITESPACE_PRE:
      require_once(HTML2PS_DIR . 'inline.content.builder.pre.php');
      $builder =& new InlineContentBuilderPre();
      break;
    case WHITESPACE_NOWRAP:
      require_once(HTML2PS_DIR . 'inline.content.builder.nowrap.php');
      $builder =& new InlineContentBuilderNowrap();
      break;
    case WHITESPACE_PRE_WRAP:
      require_once(HTML2PS_DIR . 'inline.content.builder.pre.wrap.php');
      $builder =& new InlineContentBuilderPreWrap();
      break;
    case WHITESPACE_PRE_LINE:
      require_once(HTML2PS_DIR . 'inline.content.builder.pre.line.php');
      $builder =& new InlineContentBuilderPreLine();
      break;
    default:
      trigger_error('Internal error: unknown whitespace enumeration value', E_USER_ERROR);
    };

    return $builder;
  }
}

?>