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

if(isset($access_requirements['feature']) && $access_requirements['feature'] == 'menu'){
	switch($access_requirements['module']){
		case 'people':
		case 'inventory':
		case 'invoice':
		case 'config':
		case 'sales_order':
		case 'quote':
		case 'task':
            $access = true; //($_SERVER['SERVER_ADDR'] == '192.168.0.54');
	        break;
		default:
			$access = true;
	}
}else if(isset($access_requirements['feature']) && $access_requirements['feature'] == 'submenu'){
	switch($access_requirements['module']){
		case 'user':
		case 'supplier_product':
		case 'customer':
		case 'supplier':
			$access = true;
	        break;
		default:
			$access = true; //($_SERVER['SERVER_ADDR'] == '192.168.0.54');
	}
}else{

	if(false){ //self::getlevel() == 1){
		// can access any part or feature of the system!
		$access = true;
	}else{
		$access = true;
		// check based on selected user permissions
        if(isset($access_requirements['feature'])){
            switch($access_requirements['feature']){
                case 'page':
                    break;
                case 'add_link':
                case 'edit_link':
                case 'delete_link':
                case 'edit_field':
                    break;
                case 'view_link':
                    switch($access_requirements['database_table']){
                        case 'inventory_test':
                        case 'inventory':
                            $access = true; // users only allowed to view inventory
                            break;
                    }
                    break;
            }
		}
	}
}
?>