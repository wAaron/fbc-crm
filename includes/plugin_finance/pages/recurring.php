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


if(!module_finance::can_i('view','Finance Upcoming')){
    redirect_browser(_BASE_HREF);
}

if(isset($_REQUEST['finance_recurring_id']) && $_REQUEST['finance_recurring_id'] && isset($_REQUEST['record_new'])){
    include(module_theme::include_ucm(dirname(__FILE__).'/finance_edit.php'));
}else if(isset($_REQUEST['finance_recurring_id']) && $_REQUEST['finance_recurring_id']){
    //include("recurring_edit.php");
    include(module_theme::include_ucm(dirname(__FILE__).'/recurring_edit.php'));
}else{
    //include("recurring_list.php");
    include(module_theme::include_ucm(dirname(__FILE__).'/recurring_list.php'));
}