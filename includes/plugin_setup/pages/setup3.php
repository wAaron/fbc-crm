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

if(_UCM_INSTALLED && !module_security::is_logged_in()){
    ob_end_clean();
    echo 'Sorry the system is already installed. You need to be logged in to run the setup again.';
    exit;
}

print_heading('Step #3: Initial system update');

if(
    isset($_REQUEST['run_upgrade']) ||
    (
        isset($_REQUEST['install_upgrade']) &&
        isset($_REQUEST['save_license_codes']) &&
        isset($_REQUEST['license_codes']) &&
        trim($_REQUEST['license_codes'][0])
    )
){
    $setup_upgrade_hack = true;
    include('includes/plugin_config/pages/config_upgrade.php');

}else{
    ?>

    <p>This will automatically install the latest version of Ultimate Client Manager for you. <br> To proceed you will need to enter your <strong>license purchase code</strong>. This is available in the "license" file in your downloads page on CodeCanyon.net (<a href="http://ultimateclientmanager.com/webimages/licence_cert_location.jpg" target="_blank">click here for help</a>). <br>
        The license code will look something like this: 30d91230-a8df-4545-1237-467abcd5b920
    </p>

    <h3>Please enter your license purchase code:</h3>
    <div style="padding:10px;">
    <form action="" method="post">

        <input type="hidden" name="install_upgrade" value="true">
        <input type="hidden" name="save_license_codes" value="true">
        <input type="text" name="license_codes[0]" value="<?php echo module_config::c('_installation_code','');?>" style="width:400px; padding:5px; border:1px solid #CCC;">

        <input type="submit" name="go" value="<?php _e('Check for Updates');?>" class="submit_button" onclick="this.value='Checking... this may take a few minutes.'">

    </form>
    </div>

<?php } ?>