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

if(!module_config::can_i('view','Settings')){
    redirect_browser(_BASE_HREF);
}

if(class_exists('module_security',false)){
    // if they are not allowed to "edit" a page, but the "view" permission exists
    // then we automatically grab the page and regex all the crap out of it that they are not allowed to change
    // eg: form elements, submit buttons, etc..
    module_security::check_page(array(
        'category' => 'Config',
        'page_name' => 'Settings',
        'module' => 'config',
        'feature' => 'Edit',
    ));
}
$module->page_title = 'Settings';

?>


<form action="#" method="post">

    <?php
module_form::prevent_exit(array(
    'valid_exits' => array(
        // selectors for the valid ways to exit this form.
        '.submit_button',
    ))
);
?>
    <input type="hidden" name="_process" value="save_config">

    <style type="text/css">
        .config_variable{
            border:1px solid #EFEFEF;
            padding:2px;
            min-width: 50px;
            display: inline-block;
            cursor: pointer;
        }
    </style>
       
        <p><?php _e('Advanced Configuration area below. This contains every configurable value in the system. Change at own risk :)');?>
        <br/>Please <strong>click</strong> on an item to change it.</p>

    <table class="tableclass tableclass_rows">
        <thead>
        <tr>
            <th>
                <?php echo _l('Configuration Key');?>
            </th>
            <th>
                <?php echo _l('Configuration Value');?>
            </th>
        </tr>
        </thead>
        <tbody>
        <?php foreach(get_multiple('config',false,'key','exact','`key`') as $config){
            if($config['key'][0]=='_')continue;
            ?>
        <tr>
            <th>
                <?php echo $config['key']; ?>
            </th>
            <td>
                <span data-name="config[<?php echo htmlspecialchars($config['key']);?>]" class="config_variable"><?php echo htmlspecialchars($config['val']);?></span>
            </td>
        </tr>
        <?php } ?>
        <tr>
            <th></th>
            <td>
                <input type="submit" name="save" value="Save" class="submit_button">
            </td>
        </tr>
        </tbody>
    </table>
</form>

<script type="text/javascript">
    $(function(){
        $('.config_variable').click(function(){
            var txt = $('<input type="text" name="'+$(this).attr('data-name')+'" value="">');
            $(this).after(txt.val($(this).html()));
            $(this).remove();
            txt[0].focus();
            txt[0].select();
        });
    });
</script>
