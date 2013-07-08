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

$noredirect = true;
header( 'Content-Type: text/html; charset=UTF-8' );
require_once('init.php');

if(module_security::is_logged_in()){
    $search_text = isset($_REQUEST['ajax_search_text']) ? trim(urldecode($_REQUEST['ajax_search_text'])) : false;
	if($search_text){
		$search_results = array();
		foreach($plugins as $plugin_name => &$plugin){
			$search_results = array_merge( $search_results , $plugin->ajax_search($search_text,$db) );
		}
        if(count($search_results)){
            echo '<ul>';
            foreach($search_results as $r){
                echo '<li>' . $r . '</li>';
            }
            echo '</ul>';
        }
	}else{
		echo '';
	}
	exit;
}

