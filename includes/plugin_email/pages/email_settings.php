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

$settings = array(
         array(
            'key'=>'email_smtp',
            'default'=>'0',
             'type'=>'checkbox',
             'description'=>'Use SMTP when sending emails from this system',
         ),
         array(
            'key'=>'email_smtp_hostname',
            'default'=>'',
             'type'=>'text',
             'description'=>'SMTP hostname (eg: mail.yoursite.com)',
         ),
         array(
            'key'=>'email_smtp_authentication',
            'default'=>'0',
             'type'=>'checkbox',
             'description'=>'Use SMTP authentication',
         ),
         array(
            'key'=>'email_smtp_username',
            'default'=>'',
             'type'=>'text',
             'description'=>'SMTP Username',
         ),
         array(
            'key'=>'email_smtp_password',
            'default'=>'',
             'type'=>'text',
             'description'=>'SMTP Password',
         ),
        array(
            'key'=>'email_limit_amount',
            'default'=>'0',
            'type'=>'text',
            'description' => 'Limit number of emails',
            'help'=>'How many emails you can send per day, hour or minute. Set to 0 for unlimited emails.',
        ),
         array(
            'key'=>'email_limit_period',
            'default'=>'day',
             'type'=>'select',
             'options' => array(
                 'day' => _l('Per Day'),
                 'hour' => _l('Per Hour'),
                 'minute' => _l('Per Minute'),
             ),
             'description'=>'Limit per',
             'help'=>'How many emails you can send per day, hour or minute',
         ),
);

$demo_email = module_config::c('admin_email_address');
if(isset($_REQUEST['email'])){
    $demo_email = $_REQUEST['email'];
}
if(isset($_REQUEST['_email'])){
    // send a test email and report any errors.
    $email = module_email::new_email();
    $email->set_subject('Test Email from '.module_config::c('admin_system_name'));
    $email->set_to_manual($demo_email);
    $email->set_html('This is a test email from the "'.module_config::c('admin_system_name').'" setup wizard.');
    if(!$email->send()){
        ?>
        <div class="warning">
            Failed to send test email. Error message: <?php echo $email->error_text;?>
        </div>
        <?php
    }else{
        ?>
        <strong>Test email sent successfully.</strong>
        <?php
    }
}


?>

<table class="tableclass tableclass_full">
        <tr>
            <td valign="top">
                <h2>Send a test email:</h2>
                <form action="" method="post">
                    <input type="hidden" name="_email" value="true">
                    <p>Please enter your email address:</p>
                    <p><input type="text" name="email" value="<?php echo htmlspecialchars($demo_email);?>" size="40"></p>
                    <p>If sending an email does not work, please change your SMTP details on the right and try again.</p>
                    <input type="submit" name="send" value="Click here to send a test email" class="submit_button save_button">
                    <p><em>(the subject of this email will be "Test Email from <?php echo module_config::c('admin_system_name');?>")</em></p>
                </form>
            </td>
            <td width="50%" valign="top">
                <?php
                 print_heading('Email Settings (SMTP)');

                module_config::print_settings_form(
                     $settings
                );
                ?>
            </td>
        </tr>
    </table>


