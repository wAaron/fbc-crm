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

print_heading('FAQ Settings');
$c = array();
$customers = module_customer::get_customers();
foreach($customers as $customer){
    $c[$customer['customer_id']] = $customer['customer_name'];
}

module_config::print_settings_form(
    array(
        array(
            'key'=>'faq_ticket_show_product_selection',
            'default'=>1,
            'type'=>'checkbox',
            'description'=>'Show product selection on ticket submit form.',
        ),
    )
);

?>

<?php

print_heading('FAQ Embed');
?>
<p>
    <?php _e('Place this in an iframe on your website, or as a link on your website, and people can view FAQ tickets.'); ?>
</p>
<p><a href="<?php echo module_faq::link_open_public(-1);?>?show_search=1&show_header=1&show_product=1" target="_blank"><?php echo module_faq::link_open_public(-1);?>?show_search=1&show_header=1&show_product=1</a></p>
