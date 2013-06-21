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
// $Header: /cvsroot/html2ps/xhtml.p.inc.php,v 1.6 2005/09/04 08:03:21 Konstantin Exp $

function process_p($sample_html) { 
  $open_regexp = implode("|",
    array(
      "p","dl","div","noscript","blockquote","form","hr","table","fieldset","address",
      "ul","ol","li",
      "h1","h2","h3","h4","h5","h6",
      "pre", "frameset", "noframes"
    )
  );  
  $close_regexp = implode("|",
    array(
      "dl","div","noscript","blockquote","form","hr","table","fieldset","address",
      "ul","ol","li",
      "h1","h2","h3","h4","h5","h6",
      "pre", "frameset", "noframes", "body"
    )
  );  
  $open = mk_open_tag_regexp("(".$open_regexp.")");
  $close = mk_close_tag_regexp("(".$close_regexp.")");

  $offset = 0;
  while (preg_match("#^(.*?)(<\s*p(\s+[^>]*?)?>)(.*?)($open|$close)#is",substr($sample_html, $offset), $matches)) {
    if (!preg_match("#<\s*/\s*p\s*>#is",$matches[3])) {
      $cutpos = $offset + strlen($matches[1]) + strlen($matches[2]) + strlen($matches[4]);
      $sample_html = substr_replace($sample_html, "</p>", $cutpos, 0);
      $offset = $cutpos+4;
    } else {
      $offset += strlen($matches[1])+1;
    };
  };

  return $sample_html;
};

?>