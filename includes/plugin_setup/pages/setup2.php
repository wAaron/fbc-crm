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
db_connect(); // connection isn't auto done until setup complete.


?>

<?php print_heading('Step #2: Database Installation');?>

<?php
// check for existing database tables.
// upgrade if neccessary.
// check with each plugin to get a list of SQL to install / upgrade.

//$current_db_version = _UCM_VERSION;

// check if db is installed
//$sql = "SHOW TABLES LIKE '"._DB_PREFIX."config'";
//$res = qa1($sql);
/*
if(count($res)){
    // something is installed, find out what version.
    $sql = "SELECT * FROM `"._DB_PREFIX."config` WHERE `key` = 'db_version'";
    $res = qa1($sql);
    if(count($res)){
        // found a version.
        $current_db_version = $res['val'];
    }
    $do_upgrade = true;
}else{
    $do_upgrade = false;
}*/

// start running all the hooks to install plugins.
$fail = false;
$set_versions = array();
foreach($plugins as $plugin_name => &$p){
    echo "Installing <span style='text-decoration:underline;'>$plugin_name</span> plugin version ".$p->get_plugin_version().".... ";
    if($version = $p->install_upgrade()){ //$do_upgrade,$current_db_version
        echo '<span class="success_text">success</span>';
        $set_versions[$plugin_name] = $version;
    }else{
        $fail = true;
        echo '<span class="error_text">fail</span> ';
    }
    echo '<br>';
}
// all done?

if(isset($set_versions['config'])){
    // config db worked.
    foreach($plugins as $plugin_name => &$p){
        if(isset($set_versions[$plugin_name])){
            $p->init();
            // lol typo - oh well. 
            $p->set_insatlled_plugin_version($set_versions[$plugin_name]);
        }
    }
}


if($fail){
    print_header_message();
    ?>
        <br/>
        Some things failed. Would you like to retry? <br/>
    <a href="?m=setup&amp;step=2" class="uibutton">Retry</a>
    <?php
}else{
    ?>

    <p>Database Installation Success!</p>

    <?php
    if(!module_security::is_logged_in()){
        $_REQUEST['auto_login'] = module_security::get_auto_login_string(1);
        if(!module_security::auto_login(false)){
            echo 'Failed to login automatically...';
        }else{
            ?>
            <p>We have successully logged you in as the Administrator. Welcome!</p>
            <?php
        }
    }else{
        ?>
        <p>We have successully logged you in as the Administrator. Welcome!</p>
        <?php
    }
    ?>


    <p><a href="?m=setup&amp;step=3" class="uibutton">Continue to Step 3</a></p>

    <?php
}

?>