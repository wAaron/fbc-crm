<?php 
/** 
  * Copyright: dtbaker 2012
  * Licence: Please check CodeCanyon.net for licence details. 
  * More licence clarification available here:  http://codecanyon.net/wiki/support/legal-terms/licensing-terms/ 
  * Deploy: 3053 c28b7e0e323fd2039bb168d857c941ee
  * Envato: 6b31bbe6-ead4-44a3-96e1-d5479d29505b
  * Package Date: 2013-02-27 19:23:35 
  * IP Address: 210.14.75.228
  */

class HTMLPurifier_Printer_CSSDefinition extends HTMLPurifier_Printer
{

    protected $def;

    public function render($config) {
        $this->def = $config->getCSSDefinition();
        $ret = '';

        $ret .= $this->start('div', array('class' => 'HTMLPurifier_Printer'));
        $ret .= $this->start('table');

        $ret .= $this->element('caption', 'Properties ($info)');

        $ret .= $this->start('thead');
        $ret .= $this->start('tr');
        $ret .= $this->element('th', 'Property', array('class' => 'heavy'));
        $ret .= $this->element('th', 'Definition', array('class' => 'heavy', 'style' => 'width:auto;'));
        $ret .= $this->end('tr');
        $ret .= $this->end('thead');

        ksort($this->def->info);
        foreach ($this->def->info as $property => $obj) {
            $name = $this->getClass($obj, 'AttrDef_');
            $ret .= $this->row($property, $name);
        }

        $ret .= $this->end('table');
        $ret .= $this->end('div');

        return $ret;
    }

}

// vim: et sw=4 sts=4
