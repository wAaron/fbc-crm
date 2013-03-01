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

if(!module_config::can_i('view','Settings')){
    redirect_browser(_BASE_HREF);
}


print_heading('Theme settings'); ?>


<?php
$show_theme_selector = false;
$themes = array(
    'default' => _l('Default Theme'),
);
$theme_folders = glob('includes/plugin_theme/themes/*');
if(is_array($theme_folders)){
    foreach($theme_folders as $foo){
        if(is_dir($foo) && !is_file($foo.'/ucm_ignore')){
            $show_theme_selector = true;
            $themes[basename($foo)] = ucwords(str_replace('_',' ',basename($foo)));
        }
    }
}
if($show_theme_selector){
    $settings = array(
        array(
            'key'=>'theme_name',
            'default'=>'default',
            'type'=>'select',
            'options'=>$themes,
            'description'=>'Default theme to use',
        ),
    );
    if(module_security::is_logged_in() && module_config::c('theme_per_user',0)){
        $settings[] = array(
            'key' => 'theme_name_'.module_security::get_loggedin_id(),
            'default' => module_config::c('theme_name','default'),
            'options' => $themes,
            'type' => 'select',
            'description' => 'Theme to use when logged into your account'
        );
    }

    module_config::print_settings_form(
        $settings
    );
}
?>


<p><?php _e('This is just a basic CSS editor. Paste in CSS compatible rules over the top of defaults. Click the default value to return to that value.');?></p>

<form action="" method="post">

    <?php
module_form::prevent_exit(array(
    'valid_exits' => array(
        // selectors for the valid ways to exit this form.
        '.submit_button',
    ))
);
?>

    <input type="hidden" name="_config_settings_hook" value="save_config">

    <table class="tableclass tableclass_rows">
        <thead>
        <tr>
            <th>
                <?php _e('Description');?>
            </th>
            <th>
                <?php _e('CSS Property');?>
            </th>
            <th>
                <?php _e('Value');?>
            </th>
            <th>
                <?php _e('Default');?>
            </th>
        </tr>
        </thead>
        <tbody>
            <?php
            $r=1;
            $x=1;
            foreach(module_theme::get_theme_styles(module_theme::$current_theme) as $style){
                $c=0;
                foreach($style['v'] as $k=>$v){
                    $c++;
                    ?>
                    <tr class="<?php echo $x%2?'odd':'even';?>">
                        <?php if($c==1){ ?>
                        <td rowspan="<?php echo count($style['v']);?>"><?php echo $style['d'];?></td>
                        <?php } ?>
                        <td>
                            <?php echo $k;?>
                        </td>
                        <td>
                        <?php switch($k){
                            default;
                            ?>
                                <input type="text" name="config[_theme_<?php echo htmlspecialchars(module_theme::$current_theme.$style['r'] .'_'.$k);?>]" value="<?php echo htmlspecialchars($v[0]);?>" size="60" id="s<?php echo $r;?>">
                                <?php
                            break;
                    } ?>
                        </td>
                        <td<?php if($v[0]!=$v[1])echo ' style="font-weight:bold"';?>>
                            <a href="#" onclick="$('#s<?php echo $r;?>').val('<?php echo htmlspecialchars($v[1]);?>');return false;"><?php echo htmlspecialchars($v[1]);?></a>
                        </td>
                    </tr>
                <?php
                $r++;
                }
            $x++;
            } ?>

            <tr>
                <td colspan="4" align="center">
                    <input type="submit" name="save" value="<?php _e('Save settings');?>" class="submit_button save_button">
                </td>
            </tr>
        </tbody>
    </table>

</form>

    <p><?php _e('More advanced changes can be made like normal in the /css/styles.css and /css/desktop.css files. (use Chrome or Firebug to locate the styles you wish to change)');?></p>

    <?php
$settings = array(
         array(
            'key'=>_THEME_CONFIG_PREFIX.'theme_logo',
            'default'=>_BASE_HREF.'images/logo.png',
             'type'=>'text',
             'description'=>'URL for header logo',
         ),
         array(
            'key'=>_THEME_CONFIG_PREFIX.'theme_favicon',
            'default'=>'',
             'type'=>'text',
             'description'=>'URL for favicon',
             'help' => 'Please google for "How to make a favicon". It should be a small PNG or ICO image.'
         ),
);

module_config::print_settings_form(
     $settings
);

?>
