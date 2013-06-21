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
// $Header: /cvsroot/html2ps/css.rules.inc.php,v 1.10 2007/03/23 18:33:34 Konstantin Exp $

class CSSRule {
  var $selector;
  var $body;
  var $baseurl;
  var $order;

  var $specificity;
  var $pseudoelement;

  function apply(&$root, &$state, &$pipeline) {
    $pipeline->push_base_url($this->baseurl);
    $this->body->apply($state);
    $pipeline->pop_base_url();
  }

  function add_property($property) {
    $this->body->add_property($property);
  }

  function CSSRule($rule, &$pipeline) {
    $this->selector = $rule[0];
    $this->body     = $rule[1]->copy();
    $this->baseurl  = $rule[2];
    $this->order    = $rule[3];

    $this->specificity   = css_selector_specificity($this->selector);
    $this->pseudoelement = css_find_pseudoelement($this->selector);
  }

  function set_property($key, $value, &$pipeline) {
    $this->body->set_property_value($key, $value);
  }

  function &get_property($key) {
    return $this->body->get_property_value($key);
  }

  function get_order() { return $this->order; }
  function get_pseudoelement() { return $this->pseudoelement; }
  function get_selector() { return $this->selector; }
  function get_specificity() { return $this->specificity; }

  function match($root) {
    return match_selector($this->selector, $root);
  }
}

function rule_get_selector(&$rule) { return $rule[0]; };

function cmp_rules($r1, $r2) {
  $a = css_selector_specificity($r1[0]);
  $b = css_selector_specificity($r2[0]);

  for ($i=0; $i<=2; $i++) {
    if ($a[$i] != $b[$i]) { return ($a[$i] < $b[$i]) ? -1 : 1; };
  };

  // If specificity of selectors is equal, use rules natural order in stylesheet

  return $r1[3] < $r2[3] ? -1 : 1;
}

function cmp_rule_objs($r1, $r2) {
  $a = $r1->get_specificity();
  $b = $r2->get_specificity();

  for ($i=0; $i<=2; $i++) {
    if ($a[$i] != $b[$i]) { return ($a[$i] < $b[$i]) ? -1 : 1; };
  };

  // If specificity of selectors is equal, use rules natural order in stylesheet

  return $r1->get_order() < $r2->get_order() ? -1 : 1;
}

?>