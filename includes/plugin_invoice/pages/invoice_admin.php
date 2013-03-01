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

$invoice_safe = true;

if(isset($_REQUEST['invoice_id'])){

    if(isset($_REQUEST['print'])){
        include(module_theme::include_ucm("includes/plugin_invoice/pages/invoice_admin_print.php"));
        //include('invoice_admin_print.php');
    }else if(isset($_REQUEST['email'])){
        include(module_theme::include_ucm("includes/plugin_invoice/pages/invoice_admin_email.php"));
        //include('invoice_admin_email.php');
    }else{
        /*if(module_security::getlevel() > 1){
            include('invoice_customer_view.php');
        }else{*/
            include(module_theme::include_ucm("includes/plugin_invoice/pages/invoice_admin_edit.php"));
            //include("invoice_admin_edit.php");
        /*}*/
    }

}else{

    include(module_theme::include_ucm("includes/plugin_invoice/pages/invoice_admin_list.php"));
	//include("invoice_admin_list.php");
	
} 

