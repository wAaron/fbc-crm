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



if(isset($_REQUEST['user_id'])){

    $user_id = (int)$_REQUEST['user_id'];

    if(class_exists('module_security',false)){
        if($user_id > 0){
            $user = module_user::get_user($user_id);

            if(!$user){
                die('Permission denied to view this user');
            }
            $user_id = (int)$user['user_id'];
        }
        if($user_id > 0){
            module_security::check_page(array(
                 'category' => 'Config',
                 'page_name' => 'Users',
                'module' => 'user',
                'feature' => 'edit',
            ));
        }else{
            module_security::check_page(array(
                 'category' => 'Config',
                 'page_name' => 'Users',
                'module' => 'user',
                'feature' => 'create',
            ));
        }
    }

    $user_safe = true;
    include(module_theme::include_ucm("includes/plugin_user/pages/user_admin_edit.php"));
	//include("user_admin_edit.php");

}else{

    if(class_exists('module_security',false)){
        module_security::check_page(array(
             'category' => 'Config',
             'page_name' => 'Users',
            'module' => 'user',
            'feature' => 'view',
        ));
    }

    include(module_theme::include_ucm("includes/plugin_user/pages/user_admin_list.php"));
	//include("user_admin_list.php");
	
} 

