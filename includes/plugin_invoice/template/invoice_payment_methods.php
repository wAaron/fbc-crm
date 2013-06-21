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

if($invoice_data['total_amount_due']>0){


module_template::init_template('invoice_payment_methods_online','<strong>Option #1: Pay Online</strong>
<br/>
We support the following secure payment methods:
<br/>
','Displayed on the external invoice.','code');

module_template::init_template('invoice_payment_methods_offline','<strong>Option #2: Pay Offline</strong>
<br/>
We support the following offline payment methods:
<br/>
','Displayed on the external invoice.','code');

module_template::init_template('invoice_payment_in_full','Invoice has been paid in full. <br/><br/>
Thank you for your business.
','Displayed on the external invoice.','code');

module_template::init_template('invoice_payment_methods_title','','Displayed as the title on invoice for payment methods area','code');


// find all payment methods that are available for invoice payment.
$payment_methods = handle_hook('get_payment_methods');
$methods_count = count($payment_methods);

$template_print = module_template::get_template_by_key('invoice_payment_methods_title');
if(strlen($template_print->content)){
    echo $template_print->content;
}else if(!isset($mode) || $mode=='html'){
    ?>
    <h3><?php _e('Payment Methods:');?></h3>
    <?php
}else{ ?>
    <strong><?php _e('Payment Methods:');?></strong><br/>
<?php } ?>

    <?php
    // work out the payment methods that are allowed for this invoice.
    $payment_methods_online=array();
    $payment_methods_offline=array();
    $default_payment_method = module_config::c('invoice_default_payment_method','paymethod_paypal');
    foreach($payment_methods as $payment_method_id => $payment_method){
        if($payment_methods[$payment_method_id]->is_enabled() && $payment_methods[$payment_method_id]->is_allowed_for_invoice($invoice_id) ){
            if($payment_methods[$payment_method_id]->is_method('online') ){
                $payment_methods_online[] = array(
                    'name' => $payment_methods[$payment_method_id]->get_payment_method_name(),
                    'key' => $payment_methods[$payment_method_id]->module_name,
                    'description' => $payment_methods[$payment_method_id]->get_invoice_payment_description($invoice_id),
                );
            }else{
                $payment_methods_offline[] = array(
                    'name' => $payment_methods[$payment_method_id]->get_payment_method_name(),
                    'key' => $payment_methods[$payment_method_id]->module_name,
                    'description' => $payment_methods[$payment_method_id]->get_invoice_payment_description($invoice_id),
                );
            }
        }
    }
    ?>
<table width="100%" class="tableclass">
    <tbody>
    <tr>
        <td valign="top" width="50%">

            <?php if(count($payment_methods_online)){
                $template_print = module_template::get_template_by_key('invoice_payment_methods_online');
                echo $template_print->content;
                ?>


                <?php if(!isset($mode) || $mode=='html'){ ?>

                    <form action="" method="post">
                    <input type="hidden" name="payment" value="go">
                    <input type="hidden" name="invoice_id" value="<?php echo $invoice_id;?>">
                    <table class="" cellpadding="0" cellspacing="0">
                        <tbody>
                        <tr>
                            <th class="width1">
                                <?php _e('Payment Method'); ?>
                            </th>
                            <td>
                                <?php
                                // find out all the payment methods.
                                $x=1;
                                //todo
                                $default_payment_method = module_config::c('invoice_default_payment_method','paymethod_paypal');
                                foreach($payment_methods_online as $payment_methods_on){
                                    ?>
                                    <input type="radio" name="payment_method" value="<?php echo $payment_methods_on['key'];?>" id="paymethod<?php echo $x;?>" <?php echo $default_payment_method==$payment_methods_on['key'] ? 'checked':'';?>>
                                    <label for="paymethod<?php echo $x;?>"><?php echo $payment_methods_on['name']; ?></label> <br/>
                                    <?php
                                    $x++;
                                }

                                ?>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <?php _e('Payment Amount'); ?>
                            </th>
                            <td>
                                <?php echo currency('<input type="text" name="payment_amount" value="'.number_format($invoice['total_amount_due'],2,'.','').'" class="currency">',true,$invoice['currency_id']);?>
                            </td>
                        </tr>
                        <tr>
                            <td>&nbsp;</td>
                            <td>
                                <input type="submit" name="pay" value="<?php _e('Make Payment');?>" class="submit_button save_button">
                            </td>
                        </tr>
                        </tbody>
                    </table>
            </form>


                

            <?php }else{ ?>

                <ul>
                <?php
                foreach($payment_methods_online as $payment_methods_on){
                    ?>
                    <li>
                        <strong><?php echo $payment_methods_on['name']; ?></strong><br/>
                        <?php echo $payment_methods_on['description']; ?>
                    </li>
                    <?php
                }
                ?>
                </ul>
                <br/>
                <?php _e('Please <a href="%s">click here</a> to pay online.',module_invoice::link_public($invoice_id));?>
            <?php } ?>
        <?php } ?>

        </td>
        <td valign="top" width="50%">

            <?php if(count($payment_methods_offline)){

            $template_print = module_template::get_template_by_key('invoice_payment_methods_offline');
            echo $template_print->content;
            ?>

            <ul>
            <?php
            foreach($payment_methods_offline as $payment_methods_of){
                ?>
                <li>
                    <strong><?php echo $payment_methods_of['name']; ?></strong><br/>
                    <?php echo $payment_methods_of['description']; ?>
                </li>
                <?php
            }
            ?>
            </ul>
            <?php } ?>

        </td>
    </tr>
    </tbody>
</table>

    <?php }else{ ?>

        <p align="center">
        <?php
    $template_print = module_template::get_template_by_key('invoice_payment_in_full');
    echo $template_print->content; ?>
</p>
    
<?php } ?>