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
if(isset($_REQUEST['go'])){
    ob_end_clean();
    echo '<pre>';
    _e("Checking for bounces, please wait...");
    echo "\n\n";
    module_newsletter::check_bounces(true);
    echo "\n\n";
    _e("done.");
    echo '</pre>';

    exit;
}

$module->page_title = _l('Newsletter Bounce Checking');
print_heading('Newsletter Bounce Checking');

?>
<p><?php _e('Bounces are checked automatically using the CRON job, however if you want to check for bounces manually (ie: to see any error) please click the button below.');?></p>
<form action="" method="post">
<input type="submit" name="go" value="<?php _e('Check for bounces');?>">
</form>