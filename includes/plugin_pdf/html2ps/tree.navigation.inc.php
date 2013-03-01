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
// $Header: /cvsroot/html2ps/tree.navigation.inc.php,v 1.13 2007/05/06 18:49:29 Konstantin Exp $

class TreeWalkerDepthFirst {
  var $_callback;

  function TreeWalkerDepthFirst($callback) {
    $this->_callback = $callback;
  }

  function run(&$node) {
    call_user_func($this->_callback, array('node' => &$node));
    $this->walk_element($node);
  }

  function walk_element(&$node) {
    if (!isset($node->content)) {
      return;
    };

    for ($i = 0, $size = count($node->content); $i < $size; $i++) {
      $child =& $node->content[$i];
      $this->run($child);
    };
  }
}

function &traverse_dom_tree_pdf(&$root) {
  switch ($root->node_type()) {
  case XML_DOCUMENT_NODE:
    $child =& $root->first_child();
    while($child) {
      $body =& traverse_dom_tree_pdf($child);
      if ($body) { 
        return $body; 
      }
      $child =& $child->next_sibling();
    };

    $null = null;
    return $null;
  case XML_ELEMENT_NODE:    
    if (strtolower($root->tagname()) == "body") { 
      return $root; 
    }

    $child =& $root->first_child(); 
    while ($child) {
      $body =& traverse_dom_tree_pdf($child);
      if ($body) { 
        return $body; 
      }
      $child =& $child->next_sibling();
    };
    
    $null = null;
    return $null;
  default:
    $null = null;
    return $null;
  }
};

function dump_tree(&$box, $level) {
  print(str_repeat(" ", $level));
  if (is_a($box, 'TextBox')) {
    print(get_class($box).":".$box->uid.":".join('/', $box->words)."\n");
  } else {
    print(get_class($box).":".$box->uid."\n");
  };

  if (isset($box->content)) {
    for ($i=0; $i<count($box->content); $i++) {
      dump_tree($box->content[$i], $level+1);
    };
  };
};

?>