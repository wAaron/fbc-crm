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
class PreTreeFilterHTML2PSFields extends PreTreeFilter {
  var $filename;
  var $filesize;
  var $_timestamp;

  function PreTreeFilterHTML2PSFields($filename=null, $filesize=null, $timestamp=null) {
    $this->filename  = $filename;
    $this->filesize  = $filesize;

    if (is_null($timestamp)) {
      $this->_timestamp = date("Y-m-d H:s");
    } else {
      $this->_timestamp = $timestamp;
    };
  }

  function process(&$tree, $data, &$pipeline) {
    if (is_a($tree, 'TextBox')) {
      // Ignore completely empty text boxes
      if (count($tree->words) == 0) { return; };

      switch ($tree->words[0]) {
      case '##PAGE##':
        $parent =& $tree->parent;
        $field  = BoxTextFieldPageNo::from_box($tree);

        $parent->insert_before($field, $tree);

        $parent->remove($tree);
        break;
      case '##PAGES##':
        $parent =& $tree->parent;
        $field  = BoxTextFieldPages::from_box($tree);
        $parent->insert_before($field, $tree);
        $parent->remove($tree);
        break;
      case '##FILENAME##':
        if (is_null($this->filename)) {
          $tree->words[0] = $data->get_uri();
        } else {
          $tree->words[0] = $this->filename;
        };
        break;
      case '##FILESIZE##':
        if (is_null($this->filesize)) {
          $tree->words[0] = strlen($data->get_content());
        } else {
          $tree->words[0] = $this->filesize;
        };
        break;
      case '##TIMESTAMP##':
        $tree->words[0] = $this->_timestamp;
        break;
      };
    } elseif (is_a($tree, 'GenericContainerBox')) {
      for ($i=0; $i<count($tree->content); $i++) {
        $this->process($tree->content[$i], $data, $pipeline);
      };
    };
  }
}
?>