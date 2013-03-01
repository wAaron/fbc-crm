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

print_heading('PayPal Settings');?>


<?php module_config::print_settings_form(
    array(
         array(
            'key'=>'payment_method_paypal_enabled',
            'default'=>1,
             'type'=>'checkbox',
             'description'=>'Enable PayPal Checkout',
         ),
         array(
            'key'=>'payment_method_paypal_email',
            'default'=>_ERROR_EMAIL,
             'type'=>'text',
             'description'=>'Your PayPal registered email address',
         ),
         array(
            'key'=>'payment_method_paypal_sandbox',
            'default'=>0,
             'type'=>'checkbox',
             'description'=>'Use PayPal Sandbox Mode (for testing payments)',
         ),
    )
); ?>

<?php print_heading('PayPal setup instructions:');?>

<p>Please signup for a PayPal business account here: http://www.paypal.com - please enter your paypal email address above.</p>
