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

// dynamically generate a css stylesheet based on the users theme preferences.

chdir('../../../');
require_once('init.php');

header('Content-type: text/css');

$styles = module_theme::get_theme_styles(module_theme::$current_theme);
?>

/** css stylesheet */

<?php foreach($styles as $style){

    echo $style['r'].'{';
    foreach($style['v'] as $s=>$v){
        echo $s.':'.$v[0].'; ';
    }
    echo "}\n";

} ?>