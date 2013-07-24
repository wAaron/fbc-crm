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
define("_REWRITE_LINKS",false);

$noredirect = true;
$external=true;
include('init.php');


if($load_modules){
    $m = current($load_modules);
}else{
    $m = false;
}
//$m = (isset($_REQUEST['m'])) ? trim(basename($_REQUEST['m'])) : false;
$h = (isset($_REQUEST['h'])) ? trim(basename($_REQUEST['h'])) : false;

if($m && isset($plugins[$m])){
    if(method_exists($plugins[$m],'external_hook')){
        $plugins[$m] -> external_hook($h);
    }
}