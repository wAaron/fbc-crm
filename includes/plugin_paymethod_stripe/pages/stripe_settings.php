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


if(!module_config::can_i('edit','Settings')){
    redirect_browser(_BASE_HREF);
}

print_heading('Stripe Settings');?>


<?php module_config::print_settings_form(
    array(
         array(
            'key'=>'payment_method_stripe_enabled',
            'default'=>1,
             'type'=>'checkbox',
             'description'=>'Enable Stripe Checkout',
         ),
         array(
            'key'=>'payment_method_stripe_secret_key',
            'default'=>'',
             'type'=>'text',
             'description'=>'Your Stripe Secret Key (Test or Live)',
         ),
         array(
            'key'=>'payment_method_stripe_publishable_key',
            'default'=>'',
             'type'=>'text',
             'description'=>'Your Stripe Publishable Key (Test or Live)',
         ),
    )
); ?>

<?php print_heading('Stripe setup instructions:');?>

<p>Stripe only supports payments in USD and CAD </p>
<p>Please signup for a Strip account here: http://www.stripe.com - please enter your stripe API Keys above.</p>
<p>If you are using the TEST api keys then you can use the credit card number 4242424242424242 with any valid expiry date of CVC</p>
