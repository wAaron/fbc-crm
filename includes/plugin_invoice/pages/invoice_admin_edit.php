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

if(!$invoice_safe)die('failed');

$invoice_id = (int)$_REQUEST['invoice_id'];
$invoice = module_invoice::get_invoice($invoice_id);
if($invoice_id>0 && $invoice && $invoice['invoice_id']==$invoice_id){
    $module->page_title = _l('Invoice: #%s',htmlspecialchars($invoice['name']));
	if(class_exists('module_security',false)){

        // make sure current customer can access this invoice
        if(!module_security::can_access_data('invoice',$invoice,$invoice_id)){
            echo 'Data access denied. Sorry.';
            exit;
        }

        module_security::check_page(array(
            'category' => 'Invoice',
            'page_name' => 'Invoices',
            'module' => 'invoice',
            'feature' => 'edit',
		));
	}
}else{
    $invoice_id = 0;
    $invoice = module_invoice::get_invoice($invoice_id);
	if(class_exists('module_security',false)){
		module_security::check_page(array(
            'category' => 'Invoice',
            'page_name' => 'Invoices',
            'module' => 'invoice',
            'feature' => 'create',
		));
	}
	module_security::sanatise_data('invoice',$invoice);
}
$invoice_items = module_invoice::get_invoice_items($invoice_id);
$invoice_locked = ($invoice['date_sent'] && $invoice['date_sent'] != '0000-00-00') || ($invoice['date_paid'] && $invoice['date_paid'] != '0000-00-00');

$customer_data = array();
if($invoice['customer_id']){
    $customer_data = module_customer::get_customer($invoice['customer_id']);
}

$show_task_dates = module_config::c('invoice_task_list_show_date',1);
$colspan = 2;
if($show_task_dates)$colspan++;
?>


	
<form action="#" method="post" id="invoice_form">
	<input type="hidden" name="_process" value="save_invoice" class="no_permissions" />
    <input type="hidden" name="invoice_id" value="<?php echo $invoice_id; ?>" />
    <?php if($invoice['customer_id'] && !isset($_REQUEST['change_customer'])){ ?>
    <input type="hidden" name="customer_id" value="<?php echo $invoice['customer_id']; ?>" />
    <?php } ?>
    <input type="hidden" name="job_id" value="<?php echo isset($invoice['job_id']) ? (int)$invoice['job_id'] : 0; ?>" />
    <?php if(isset($_REQUEST['as_deposit'])){ ?>
    <input type="hidden" name="deposit_job_id" value="<?php echo isset($invoice['job_id']) ? (int)$invoice['job_id'] : 0; ?>" />
    <?php } ?>
    <input type="hidden" name="total_tax_rate" value="<?php echo $invoice['total_tax_rate']; ?>" />
    <input type="hidden" name="total_tax_name" value="<?php echo htmlspecialchars($invoice['total_tax_name']); ?>" />
    <input type="hidden" name="hourly_rate" value="<?php echo htmlspecialchars($invoice['hourly_rate']); ?>" />


    <?php

    $fields = array(
    'fields' => array(
        'name' => 'Name',
    ));
    module_form::set_required(
        $fields
    );
    module_form::prevent_exit(array(
        'valid_exits' => array(
            // selectors for the valid ways to exit this form.
            '.submit_button',
            '.save_invoice_item',
            '.save_invoice_payment',
            '.delete',
            '.apply_discount',
        ))
    );
    

    ?>

	<table cellpadding="10" width="100%">
		<tbody>
			<tr>
				<td valign="top" width="35%">
					<h3><?php echo _l('%sInvoice Details',(!$invoice_id?_l('New '):'')); ?></h3>



					<table border="0" cellspacing="0" cellpadding="2" class="tableclass tableclass_form tableclass_full">
						<tbody>
							<tr>
								<th class="width1">
									<?php echo _l('Invoice #'); ?>
								</th>
								<td>
                                    <input type="text" name="name" id="name" value="<?php echo htmlspecialchars($invoice['name']); ?>" />
								</td>
							</tr>
							<tr>
								<th>
									<?php echo _l('Status'); ?>
								</th>
								<td>
									<?php echo print_select_box(module_invoice::get_statuses(),'status',$invoice['status'],'',true,false,true); ?>
								</td>
							</tr>
							<tr>
								<th>
									<?php echo _l('Created Date'); ?>
								</th>
								<td>
									<input type="text" name="date_create" class="date_field" value="<?php echo print_date($invoice['date_create']);?>">
								</td>
							</tr>
							<tr>
								<th>
									<?php echo _l('Due Date'); ?>
								</th>
								<td>
									<input type="text" name="date_due" class="date_field" value="<?php echo print_date($invoice['date_due']);?>">
								</td>
							</tr>
                            <?php if((int)$invoice_id){ ?>
							<tr>
								<th>
									<?php echo _l('Sent Date'); ?>
								</th>
								<td>
									<input type="text" name="date_sent" id="date_sent" class="date_field" value="<?php echo print_date($invoice['date_sent']);?>">
								</td>
							</tr>
							<tr>
								<th>
									<?php echo _l('Paid Date'); ?>
								</th>
								<td>
									<input type="text" name="date_paid" class="date_field" value="<?php echo print_date($invoice['date_paid']);?>">
                                    <?php _h('To mark an invoice as paid please record a full payment against this invoice. Once that is done you can adjust the date here.'); ?>
								</td>
							</tr>
                            <?php } ?>

                            <tr>
                                <th>
                                    <?php echo _l('Tax'); ?>
                                </th>
                                <td>
                                    <input type="text" name="total_tax_name" value="<?php echo htmlspecialchars($invoice['total_tax_name']);?>" style="width:30px;">
                                    @
                                    <input type="text" name="total_tax_rate" value="<?php echo htmlspecialchars($invoice['total_tax_rate']);?>" style="width:35px;">%

                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <?php echo _l('Currency'); ?>
                                </th>
                                <td>
                                    <?php echo print_select_box(get_multiple('currency','','currency_id'),'currency_id',$invoice['currency_id'],'',false,'code'); ?>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <?php echo _l('Hourly Rate'); ?>
                                </th>
                                <td>
                                    <?php echo currency('<input type="text" name="hourly_rate" class="currency" value="'.$invoice['hourly_rate'].'">',true,$invoice['currency_id']);
                                    echo _h('This hourly rate will be applied to all manual tasks (tasks that did not come from jobs) in this invoice');
                                    ?>
                                    <?php
                                    $other_rates = array();
                                    foreach($invoice_items as $invoice_item){
                                        if(isset($invoice_item['hourly_rate']) && $invoice_item['hourly_rate'] != $invoice['hourly_rate'] && $invoice_item['hourly_rate']>0){
                                            $other_rates[dollar($invoice_item['hourly_rate'])] = true;
                                        }
                                    }
                                    if(count($other_rates)){
                                        _e("(and %s)",implode(', ',array_keys($other_rates)));
                                    }
                                    ?>
                                </td>
                            </tr>

                            <?php if(count($invoice['job_ids'])){ ?>
							<tr>
								<th>
									<?php echo _l('Linked Job'); ?>
								</th>
								<td>
									<?php
                                    foreach($invoice['job_ids'] as $job_id){
                                        if((int)$job_id>0){
                                            echo module_job::link_open($job_id,true);
                                            echo "<br/>\n";
                                        }
                                    } ?>
								</td>
							</tr>
                            <?php } ?>
                            <tr>
                                <th>
                                    <?php echo _l('Customer'); ?>
                                </th>
                                <td>
                                    <?php
                                    if(!$invoice['customer_id'] || isset($_REQUEST['change_customer'])){
                                        // allow them to pick customer.
                                        $c = array();
                                        $customers = module_customer::get_customers();
                                        foreach($customers as $customer){
                                            $c[$customer['customer_id']] = $customer['customer_name'];
                                        }
                                        echo print_select_box($c,'customer_id',$invoice['customer_id']);
                                        if($invoice['customer_id'] && module_customer::can_i('view','Customers')){ ?>
                                            <a href="<?php echo module_customer::link_open($invoice['customer_id'],false);?>"><?php _e('Open');?></a>
                                            <?php
                                        }
                                    }else{
                                        echo module_customer::link_open($invoice['customer_id'],true);
                                    }
                                     ?>
                                </td>
                            </tr>
                            <?php if($invoice['customer_id']){ ?>
                            <tr>
                                <th>
                                    <?php echo _l('Contact'); ?>
                                </th>
                                <td>
                                    <?php
                                        $c = array();
                                        $res = module_user::get_contacts(array('customer_id'=>$invoice['customer_id']));
                                        while($row = array_shift($res)){
                                            $c[$row['user_id']] = $row['name'].' '.$row['last_name'];
                                        }
                                        if($invoice['user_id'] && !isset($c[$invoice['user_id']])){
                                            // this option isn't in the listing. add it in.
                                            $user_temp = module_user::get_user($invoice['user_id'],false);
                                            $c[$invoice['user_id']] = $user_temp['name'].' '.$user_temp['last_name'] . ' '._l('(under different customer)');
                                        }
                                        echo print_select_box($c,'user_id',$invoice['user_id']);
                                    ?>
                                </td>
                            </tr>
                            <?php } ?>
                            <?php
                            // not sure what I was going to put in this hook.
                            hook_handle_callback('invoice_details_footer',$invoice_id); ?>

						</tbody>
                        <?php
                         module_extra::display_extras(array(
                            'owner_table' => 'invoice',
                            'owner_key' => 'invoice_id',
                            'owner_id' => $invoice['invoice_id'],
                            'layout' => 'table_row',
                                 'allow_new' => module_job::can_i('create','Invoices'),
                                 'allow_edit' => module_job::can_i('create','Invoices'),
                            )
                        );
                        ?>
					</table>

                    
                    <?php if((int)$invoice_id>0){ ?>

                        <?php hook_handle_callback('invoice_sidebar',$invoice_id); ?>

                    <?php } ?>
                    
                    <?php
                    if($invoice_id && $invoice_id!='new'){
                        $note_summary_owners = array();
                        // generate a list of all possible notes we can display for this invoice.
                        // display all the notes which are owned by all the sites we have access to

                        module_note::display_notes(array(
                            'title' => 'Invoice Notes',
                            'owner_table' => 'invoice',
                            'owner_id' => $invoice_id,
                            'view_link' => module_invoice::link_open($invoice_id),
                            'public' => array(
                                    'enabled'=>true,
                                    'title'=>'Public',
                                    'text'=>'Yes, show this note in invoice',
                                    'help'=>'If this is ticked then this note will be available to the customer and will be included in the {INVOICE_NOTES} shortcode in the invoice template.',
                                )
                            )
                        );
                        if(module_job::can_i('edit','Invoices')){
                            module_email::display_emails(array(
                                'title' => 'Invoice Emails',
                                'search' => array(
                                    'invoice_id' => $invoice_id,
                                )
                            ));
                        }
                    }
                    ?>

                    <?php if ((int)$invoice_id > 0 && (!$invoice['date_sent'] || $invoice['date_sent'] == '0000-00-00') && module_security::is_page_editable()){ ?>

                    <h3 class="error_text"><?php _e('Send Invoice');?></h3>
                        <div class="tableclass_form content">

                            <p style="text-align: center;">
                                <input type="submit" name="butt_email" id="butt_email2" value="<?php echo _l('Email Invoice'); ?>" class="submit_button" />
                                <?php _h('Click this button to email the invoice to the customer from the system'); ?>
                            </p>
                            <p style="text-align: center;">
                                <a href="#" onclick="$('#date_sent').val('<?php echo print_date(date('Y-m-d'));?>'); $('#invoice_form')[0].submit(); return false;" class="uibutton"><?php _e('Mark invoice as sent');?></a>
                                <?php _h('This invoice has not been sent yet. When this invoice has been sent to the customer please click this button or enter a "sent date" into the form above.'); ?>
                            </p>
                        </div>

                    <?php } ?>



                    <?php if((int)$invoice_id > 0){


                        // find out all the payment methods.
                        $payment_methods = handle_hook('get_payment_methods',$module);
                        $x=1;
                        $default_payment_method = module_config::c('invoice_default_payment_method','paymethod_paypal');

                        ?>
                        <h3><?php _e('Make a Payment');?></h3>
                        <table class="tableclass tableclass_form tableclass_full" cellpadding="0" cellspacing="0">
                            <tbody>
                            <tr>
                                <th class="width1">
                                    <?php _e('Payment Method'); ?>
                                </th>
                                <td>
                                    <?php
                                    foreach($payment_methods as &$payment_method){
                                        if($payment_method->is_enabled() && $payment_method->is_method('online')){ ?>
                                            <input type="radio" name="payment_method" value="<?php echo $payment_method->module_name;?>" id="paymethod<?php echo $x;?>" class="no_permissions" <?php echo $default_payment_method==$payment_method->module_name ? 'checked':'';?>>
                                            <label for="paymethod<?php echo $x;?>"><?php echo $payment_method->get_payment_method_name(); ?></label> <br/>
                                            <?php
                                            $x++;
                                        }
                                    }
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <?php _e('Payment Amount'); ?>
                                </th>
                                <td>
                                    <?php echo currency('<input type="text" name="payment_amount" value="'.number_format($invoice['total_amount_due'],2,'.','').'" class="currency no_permissions">',true,$invoice['currency_id']);?>
                                </td>
                            </tr>
                            <tr>
                                <td>&nbsp;</td>
                                <td>
                                    <input type="hidden" name="butt_makepayment" id="butt_makepayment" value="" class="no_permissions">
                                    <input type="button" name="buttpay" value="<?php _e('Make Payment');?>" class="submit_button no_permissions" onclick="$('#butt_makepayment').val('yes'); this.form.submit();">
                                </td>
                            </tr>
                            </tbody>
                        </table>

                        <?php if(module_invoice::can_i('edit','Invoices')){ ?>

                        <h3><?php _e('Advanced');?></h3>
                        <table class="tableclass tableclass_form tableclass_full" cellpadding="0" cellspacing="0">
                            <tbody>
                            <tr>
                                <th class="width1">
                                    <?php _e('Customer Link');?>
                                </th>
                                <td>
                                    <a href="<?php echo module_invoice::link_public($invoice_id);?>" target="_blank"><?php echo _l('Click to view external link');?></a> <?php _h('You can send this link to your customer and they can preview the invoice, pay for the invoice as well as optionally download the invoice as a PDF'); ?>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <?php _e('Allowed Payments');?>
                                </th>
                                <td>
                                    <?php
                                    $x=0;
                                    foreach($payment_methods as &$payment_method){
                                        if($payment_method->is_enabled()){
                                            ?>
                                            <input type="checkbox" name="allowed_payment_method[<?php echo $payment_method->module_name;?>]" value="1" id="paymethodallowed<?php echo $x;?>" <?php echo $payment_method->is_allowed_for_invoice($invoice_id) ? 'checked':'';?>>
                                            <label for="paymethodallowed<?php echo $x;?>"><?php echo $payment_method->get_payment_method_name(); ?></label> <br/>
                                            <?php
                                            $x++;
                                        }
                                    } ?>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <?php _e('Discount Type'); ?>
                                </th>
                                <td>
                                    <?php echo print_select_box(array('0'=>_l('Before Tax'),1=>_l('After Tax')),'discount_type',$invoice['discount_type']);?>
                                </td>
                            </tr>
                            <?php // see if this invoice was renewed from anywhere
                            $invoice_history = module_invoice::get_invoices(array('renew_invoice_id'=>$invoice_id));
                            if(count($invoice_history)){
                                foreach($invoice_history as $invoice_h){
                                    ?>
                                    <tr>
                                        <th class="width1">
                                            <?php echo _l('Renewal History'); ?>
                                        </th>
                                        <td>
                                            <?php echo _l('This invoice was renewed from %s on %s',module_invoice::link_open($invoice_h['invoice_id'],true),print_date($invoice_h['date_renew'])); ?>
                                        </td>
                                    </tr>
                                    <?php
                                }
                            } ?>

                            <tr>
                                <th>
                                    <?php echo _l('Renewal Date'); ?>
                                </th>
                                <td>
                                <?php if($invoice['renew_invoice_id']){
                                    echo _l('This invoice was renewed on %s.',print_date($invoice['date_renew']));
                                    echo '<br/>';
                                    echo _l('A new invoice was created, please click <a href="%s">here</a> to view it.',module_invoice::link_open($invoice['renew_invoice_id']));
                                }else{
                                    $has_renewal = false;
                                    foreach($invoice['job_ids'] as $job_id){
                                        if((int)$job_id>0){
                                            $job_data = module_job::get_job($job_id,false);
                                            if($job_data['date_renew']&&$job_data['date_renew']!='0000-00-00'){
                                                $has_renewal = true;
                                                _e('This invoice will renew as part of job %s on %s',module_job::link_open($job_id,true),print_date($job_data['date_renew']));
                                            }
                                        }
                                    }
                                    if($has_renewal){

                                    }else{
                                        ?>
                                        <input type="text" name="date_renew" class="date_field" value="<?php echo print_date($invoice['date_renew']);?>">
                                        <?php
                                        if($invoice['date_renew'] && $invoice['date_renew'] != '0000-00-00' && strtotime($invoice['date_renew']) <= strtotime('+'.module_config::c('alert_days_in_future',5).' days')){
                                            // we are allowed to generate this renewal.
                                            ?>
                                            <input type="button" name="generate_renewal_btn" value="<?php echo _l('Generate Renewal');?>" class="submit_button" onclick="$('#generate_renewal_gogo').val(1); this.form.submit();">
                                            <input type="hidden" name="generate_renewal" id="generate_renewal_gogo" value="0">

                                            <?php
                                            _h('A renewal is available for this invoice. Clicking this button will create a new invoice based on this invoice, and set the renewal reminder up again for the next date.');
                                        }else{
                                            _h('You will be reminded to renew this invoice on this date. You will be given the option to renew this invoice closer to the renewal date (a new button will appear).');
                                        }
                                    }
                                } ?>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <?php _e('Cancel Date');?>
                                </th>
                                <td>
                                    <input type="text" name="date_cancel" value="<?php echo print_date($invoice['date_cancel']);?>" class="date_field">
                                    <?php _h('If the invoice has been cancelled set the date here. Payment reminders for this invoice will no longer be generated.'); ?>
                                </td>
                            </tr>
                            <?php // check if there are multiple invoice templates available
                            $find_other_templates = 'invoice_print';
                            $current_template = isset($invoice['invoice_template_print']) && strlen($invoice['invoice_template_print']) ? $invoice['invoice_template_print'] : module_config::c('invoice_template_print_default','invoice_print');
                            if(function_exists('convert_html2pdf') && isset($find_other_templates) && strlen($find_other_templates) && isset($current_template) && strlen($current_template)){
                                $other_templates = array();
                                foreach(module_template::get_templates() as $possible_template){
                                    if(strpos($possible_template['template_key'],$find_other_templates)!==false){
                                        // found another one!
                                        $other_templates[$possible_template['template_key']] = $possible_template['description'];
                                    }
                                }
                                if(count($other_templates)>1){
                                    ?>
                                    <tr>
                                        <th>
                                            <?php _e('PDF Template');?>
                                        </th>
                                        <td>
                                            <select name="invoice_template_print" id="invoice_template_print">
                                                <?php foreach($other_templates as $other_template_key => $other_template_name){ ?>
                                                    <option value="<?php echo htmlspecialchars($other_template_key);?>"<?php echo $current_template==$other_template_key ? ' selected':'';?>><?php echo htmlspecialchars($other_template_key);?></option>
                                                <?php } ?>
                                            </select>
                                            <?php _h('Choose the default template for PDF printing and PDF emailing. Name your custom templates invoice_print_SOMETHING for them to appear in this listing.'); ?>
                                        </td>
                                    </tr>
                                    <?php
                                }
                            }
                            ?>

                            </tbody>
                        </table>

                        <?php } ?>
                        <?php
                    } ?>


				</td>
                <td valign="top">
                 <?php if (($invoice['date_cancel'] && $invoice['date_cancel']!='0000-00-00')){ ?>
                    <h3 class="error_text"><?php _e('Invoice Cancelled');?></h3>
                        <div class="tableclass_form content">
                            <p align="center"><?php echo _l('This invoice has been cancelled!'); ?></p>
                        </div>
                    <?php }else if (($invoice['date_due'] && $invoice['date_due']!='0000-00-00') && (!$invoice['date_paid'] || $invoice['date_paid'] == '0000-00-00') && strtotime($invoice['date_due']) < time()){ ?>
                    <h3 class="error_text"><?php _e('Invoice Overdue');?></h3>
                        <div class="tableclass_form content">
                            <p align="center"><?php echo _l('This invoice has not been paid by the due date and %s is now overdue.',dollar($invoice['total_amount_due'],true,$invoice['currency_id'])); ?></p>
                            <?php if($invoice['date_sent'] && $invoice['date_sent']!='0000-00-00'){
                            $secs = date("U") - date("U",strtotime($invoice['date_sent']));
                            $days = $secs/86400;
                            $days = floor($days);
                            ?>
                            <p align="center"><?php echo _l('This invoice was last sent %s days ago on %s.',$days,print_date($invoice['date_sent'])); ?></p>
                            <?php } ?>
                            <p align="center">
					        <input type="submit" name="butt_email" id="butt_email_overdue" value="<?php echo _l('Email Overdue Notice'); ?>" class="submit_button" />
                            </p>
                        </div>
                    <?php } ?>



                    <?php if (isset($invoice['deposit_job_id']) && $invoice['deposit_job_id'] > 0){ ?>
                    <h3><?php _e('Deposit Invoice');?></h3>
                        <div class="tableclass_form content">
                            <p align="center">
                                <?php echo _l('This invoice is a deposit for the job: %s.',module_job::link_open($invoice['deposit_job_id'],true)); ?><br/>
                                <?php _e('The deposit will apply to this job once this invoice is paid.'); ?>
                            </p>
                        </div>
                    <?php }else if($invoice['total_amount_due']>0 && module_security::is_page_editable() && module_invoice::can_i('create','Invoice Payments')){
                        foreach($invoice['job_ids'] as $possible_job_id){
                            // find any deposit invoices matching this job.
                            $possible_invoices = module_invoice::get_invoices(array('deposit_job_id'=>$possible_job_id));
                            if($possible_invoices){
                                foreach($possible_invoices as $possible_invoice){
                                    if($possible_invoice['invoice_id']==$invoice_id )continue; // skip me
                                    // see if this deposit invoice has been paid.
                                    $possible_invoice = module_invoice::get_invoice($possible_invoice['invoice_id']);
                                    if(isset($possible_invoice['deposit_remaining']) && $possible_invoice['deposit_remaining'] >0){
                                        // we have some cash that can be applied to this invoice from the deposit! woo!
                                        $this_take = min($invoice['total_amount_due'],$possible_invoice['deposit_remaining']);
                                        if($this_take>0){
                                            ?>
                                            <h3><?php _e('Apply Previous Deposit');?></h3>
                                            <div class="tableclass_form content">
                                                <p align="center">
                                                    <?php _e('The customer has paid a deposit on this Job: %s.',module_job::link_open($possible_job_id,true)); ?>
                                                    <br/>
                                                    <?php _e('Deposit Invoice: %s paid on %s',module_invoice::link_open($possible_invoice['invoice_id'],true,$possible_invoice),print_date($possible_invoice['date_paid']));?><br/>
                                                    <?php _e('Please click the button below to apply this deposit.'); ?>
                                                    <br/>
                                                    <a href="#" onclick="$('#newinvoice_payment_type').val(<?php echo _INVOICE_PAYMENT_TYPE_DEPOSIT;?>); $('#newinvoice_payment_other_id').val('<?php echo $possible_invoice['invoice_id'];?>'); $('#newinvoice_paymentamount').val('<?php echo $this_take;?>'); $('#newinvoice_payment_date').val('<?php echo print_date($possible_invoice['date_paid']);?>'); $('#add_payment_btn').click(); $('#add_payment').val('go'); $('#invoice_form')[0].submit(); return false;" class="uibutton"><?php _e('Apply %s deposit to this invoice',dollar($this_take,true,$invoice['currency_id']));?></a>
                                                </p>
                                            </div>
                                            <?php
                                        }
                                    }
                                }
                            }
                        }
                    }
                    ?>

                <?php
                if((int)$invoice_id>0 && !$invoice_locked && $customer_data && $customer_data['credit']>0){

                    $apply_credit = min($invoice['total_amount_due'],$customer_data['credit']);
                    ?>
                    <h3><?php _e('Customer Credit Available');?></h3>
                    <div class="tableclass_form content">
                        <p align="center">
                            <?php _e('The customer has a %s credit on thier account.',dollar($customer_data['credit'],true,$invoice['currency_id'])); ?>
                            <br/>
                            <?php _e('Please click the button below to apply the credit to this invoice.'); ?>
                            <br/>
                            <!-- <a href="#" onclick="$('#newinvoice_payment_type').val(<?php echo _INVOICE_PAYMENT_TYPE_CREDIT;?>); $('#newinvoice_paymentamount').val('<?php echo $apply_credit;?>'); $('#newinvoice_payment_date').val('<?php echo print_date(time());?>'); $('#add_payment_btn').click(); $('#add_payment').val('go'); $('#invoice_form')[0].submit(); return false;" class="uibutton"><?php _e('Apply %s credit to this invoice',dollar($apply_credit,true,$invoice['currency_id']));?></a> -->
                            <a href="#"  onclick="$('#apply_credit_from_customer').val('do'); $('#invoice_form')[0].submit(); return false;" class="uibutton"><?php _e('Apply %s credit to this invoice',dollar($apply_credit,true,$invoice['currency_id']));?></a>
                        </p>
                        <input type="hidden" name="apply_credit_from_customer" id="apply_credit_from_customer" value="0">
                    </div>
                    <?php } ?>

                <script type="text/javascript">
                    function setamount(a,invoice_item_id,rate){
                        var ee = parseFloat(a);
                        if(!rate)rate = $('#'+invoice_item_id+'invoice_itemrate').val(); //<?php echo $invoice['hourly_rate'];?>;
                        if(ee>0){
                            $('#'+invoice_item_id+'invoice_itemamount').val(ee * rate);
                        }
                    }
                    function editinvoice_item(invoice_item_id,hours){
                        $('#invoice_item_preview_'+invoice_item_id).hide();
                        $('#invoice_item_edit_'+invoice_item_id).show();
                        if(hours>0){
                            $('#complete_'+invoice_item_id).val(hours);
                            if(typeof $('#complete_t_'+invoice_item_id)[0] != 'undefined'){
                                $('#complete_t_'+invoice_item_id)[0].checked = true;
                            }
                        }else{
                            $('#invoice_item_desc_'+invoice_item_id)[0].focus();
                        }
                    }
                    $(function(){
                        $('.task_toggle_long_description').live('click',function(event){
                            event.preventDefault();
                            $(this).parent().find('.task_long_description').slideToggle(function(){
                                if($('textarea.edit_task_long_description').length>0){
                                    $('textarea.edit_task_long_description')[0].focus();
                                }
                            });
                            return false;
                        });
                    });
                </script>

                <?php
                // here we check if this invoice can be merged with any other invoices.
                if($invoice_id>0&&!$invoice_locked && module_invoice::can_i('edit','Invoices') && (!isset($invoice['deposit_job_id']) || !$invoice['deposit_job_id'])){
                    $merge_invoice_ids = module_invoice::check_invoice_merge($invoice_id);
                    if($merge_invoice_ids){
                        ?>
                        <h3><?php _e('Merge Customer Invoices');?></h3>
                        <div class="content_box_wheader" style="padding-bottom: 20px">
                            <p>
                                <?php _e('We found %s other invoices from this customer that can be merged.',count($merge_invoice_ids));?>
                                <?php _h('You can generate invoices from multiple jobs (eg: a Hosting Setup job and a Web Development job) then you can combine them together here and send them as a single invoice to the customer, rather than sending multiple invoices.');?>
                            </p>
                            <ul>
                                <?php foreach($merge_invoice_ids as $merge_invoice){
                                    $merge_invoice = module_invoice::get_invoice($merge_invoice['invoice_id']);
                                    ?>
                                    <li>
                                        <input type="checkbox" name="merge_invoice[<?php echo $merge_invoice['invoice_id'];?>]" value="1">
                                        <?php echo module_invoice::link_open($merge_invoice['invoice_id'],true);?>
                                        <?php echo dollar($merge_invoice['total_amount'],true,$invoice['currency_id']);?>
                                        <?php if($merge_invoice['discount_amount']>0){
                                            _e('(You will have to apply the %s discount to this invoice again manually.)',dollar($merge_invoice['discount_amount'],true,$invoice['currency_id']));
                                        } ?>
                                    </li>
                                <?php } ?>
                            </ul>
                                <input type="hidden" name="butt_merge" value="" id="butt_merge">
                            <input type="button" name="butt_merge_do" value="<?php _e('Merge selected invoices into this invoice');?>" class="submit_button" onclick="$('#butt_merge').val(1); this.form.submit();">
                        </div>
                        <?php
                    }
                }
                ?>

					<h3><?php echo _l('Invoice items'); ?></h3>
                    <div class="content_box_wheader">

                    <table border="0" cellspacing="0" cellpadding="2" class="tableclass tableclass_rows tableclass_full">
                        <thead>
                        <tr>
                            <?php if(module_config::c('invoice_task_numbers',1)){ ?>
                            <th width="10">#</th>
                            <?php } ?>
                            <th class="invoice_item_column"><?php _e('Description');?></th>
                            <?php if($show_task_dates){ ?>
                            <th width="10%"><?php _e('Date');?></th>
                            <?php } ?>
                            <th width="10%"><?php echo module_config::c('task_hours_name',_l('Hours'));?></th>
                            <th width="10%"><?php _e('Price');?></th>
                            <th width="10%"><?php _e('Sub-Total');?></th>
                            <th width="80"> </th>
                        </tr>
                        </thead>
                        <?php if(!$invoice_locked && module_invoice::can_i('edit','Invoices')){ ?>
						<tbody>
                        <tr>
                            <?php if(module_config::c('invoice_task_numbers',1)){ ?>
                            <td>
                                <input type="text" name="invoice_invoice_item[new][task_order]" value="" id="next_task_number" size="3" class="edit_task_order">
                            </td>
                            <?php } ?>
                            <td>
                                <input type="text" name="invoice_invoice_item[new][description]" value="" style="width:90%;">
                                <a href="#" class="task_toggle_long_description">&raquo;</a>
                                <div class="task_long_description">
                                    <textarea name="invoice_invoice_item[new][long_description]" id="new_task_long_description" class="edit_task_long_description no_permissions"></textarea>
                                </div>
                            </td>
                            <?php if($show_task_dates){ ?>
                            <td>
                                <input type="text" name="invoice_invoice_item[new][date_done]" value="" class="date_field">
                            </td>
                            <?php } ?>
                            <td>
                                <input type="text" name="invoice_invoice_item[new][hours]" value="" id="newinvoice_itemqty" size="3" style="width:25px;" onchange="setamount(this.value,'new');" onkeyup="setamount(this.value,'new');">
                            </td>
                            <td>
                                <input type="text" name="invoice_invoice_item[new][hourly_rate]" value="<?php echo $invoice['hourly_rate'];?>" id="newinvoice_itemrate"  size="3" style="width:35px;" onchange="setamount($('#newinvoice_itemqty').val(),'new');" onkeyup="setamount($('#newinvoice_itemqty').val(),'new');">
                            </td>
                            <td nowrap="">
                                <?php echo currency('<input type="text" name="invoice_invoice_item[new][amount]" value="" id="newinvoice_itemamount" class="currency">',true,$invoice['currency_id']);?>
                            </td>
                            <td align="center">
                                <input type="submit" name="save" value="<?php _e('Add Item');?>" class="save_invoice_item small_button">
                            </td>
                        </tr>
						</tbody>
                        <?php }
                        ?>
                        <?php
                        $c=0;
                        /*[new3] => Array
                        (
                            [task_id] => 46
                            [job_id] => 15
                            [hours] => 0.00    ***********
                            [amount] => 20.00    ***********
                            [hourly_rate] => 60.00    ***********
                            [taxable] => 1
                            [billable] => 1
                            [fully_completed] => 1
                            [description] => test with fixed price ($20 one) sdgsdfg
                            [long_description] =>
                            [date_due] => 2012-05-18
                            [date_done] => 2012-07-16
                            [invoice_id] =>
                            [user_id] => 1
                            [approval_required] => 0
                            [task_order] => 8
                            [create_user_id] => 1
                            [update_user_id] => 1
                            [date_created] => 2012-07-16
                            [date_updated] => 2012-07-28
                            [id] => 46
                            [completed] =>
                            [custom_description] =>
                        )*/
                        foreach($invoice_items as $invoice_item_id => $invoice_item_data){

                            // copy any changes here to template/invoice_task_list.php
                            $task_hourly_rate = isset($invoice_item_data['hourly_rate']) && $invoice_item_data['hourly_rate']!=0 ? $invoice_item_data['hourly_rate'] : $invoice['hourly_rate'];
                            // if there are no hours logged against this task
                            if(!$invoice_item_data['hours']){
                                //$task_hourly_rate=0;
                            }
                            // if we have a custom price for this task
                            if($invoice_item_data['amount']!=0 && $invoice_item_data['amount'] != ($invoice_item_data['hours']*$task_hourly_rate)){
                                $invoice_item_amount = $invoice_item_data['amount'];
                                if(module_config::c('invoice_calculate_item_price_auto',1) && $invoice_item_data['hours'] > 0){
                                    $task_hourly_rate = round($invoice_item_amount/$invoice_item_data['hours'],module_config::c('currency_decimal_places',2));
                                }else{
                                    $task_hourly_rate = false;
                                }
                            }else if($invoice_item_data['hours']>0){
                                $invoice_item_amount = $invoice_item_data['hours']*$task_hourly_rate;
                            }else{
                                $invoice_item_amount = 0;
                                $task_hourly_rate = false;
                            }
                            /*$invoice_item_amount = $invoice_item_data['amount'] > 0 ? $invoice_item_data['amount'] : $invoice_item_data['hours']*$task_hourly_rate;
                            if($invoice_item_data['amount']>0 && !$invoice_item_data['hours']){
                                $invoice_item_amount = $invoice_item_data['amount'];
                                $invoice_item_data['hours'] = 1;
                                $task_hourly_rate = $invoice_item_data['amount']; // not sure if this will be buggy
                            }else{
                                $invoice_item_amount = $invoice_item_data['hours']*$task_hourly_rate;
                            }*/

                            // new feature, date done.
                            if(isset($invoice_item_data['date_done']) && $invoice_item_data['date_done'] != '0000-00-00'){
                                // $invoice_item_data['date_done'] is ok to print!
                            }else{
                                $invoice_item_data['date_done'] = '0000-00-00';
                                // check if this is linked to a task.
                                if($invoice_item_data['task_id']){
                                    $task = get_single('task','task_id',$invoice_item_data['task_id']);
                                    if($task && isset($task['date_done']) && $task['date_done'] != '0000-00-00'){
                                        $invoice_item_data['date_done'] = $task['date_done']; // move it over ready for printing below
                                    }else{
                                        if(isset($invoice['date_create']) && $invoice['date_create'] != '0000-00-00'){
                                            $invoice_item_data['date_done'] = $invoice['date_create'];
                                        }
                                    }
                                }
                            }

                            ?>
                                <?php if(!$invoice_locked){ ?>
                                <tbody id="invoice_item_edit_<?php echo $invoice_item_id;?>" style="display:none;">
                                <tr>
                                    <?php if(module_config::c('invoice_task_numbers',1)){ ?>
                                    <td>
                                        <input type="text" name="invoice_invoice_item[<?php echo $invoice_item_id;?>][task_order]" value="<?php
                                            if(isset($invoice_item_data['custom_task_order']) && (int)$invoice_item_data['custom_task_order']>0){
                                                echo $invoice_item_data['custom_task_order'];
                                            }else if(isset($invoice_item_data['task_order']) && $invoice_item_data['task_order']>0){
                                                echo $invoice_item_data['task_order'];
                                            }
                                            ?>" size="3" class="edit_task_order">
                                    </td>
                                    <?php } ?>
                                    <td>
                                        <input type="hidden" name="invoice_invoice_item[<?php echo $invoice_item_id;?>][task_id]" value="<?php echo htmlspecialchars($invoice_item_data['task_id']);?>">

                                        <input type="text" name="invoice_invoice_item[<?php echo $invoice_item_id;?>][description]" value="<?php echo htmlspecialchars($invoice_item_data['custom_description'] ? $invoice_item_data['custom_description'] : $invoice_item_data['description']);?>" style="width:90%;" id="invoice_item_desc_<?php echo $invoice_item_id;?>">
                                        <br/>
                                        <textarea name="invoice_invoice_item[<?php echo $invoice_item_id;?>][long_description]" style="width:90%;"><?php echo htmlspecialchars($invoice_item_data['custom_long_description'] ? $invoice_item_data['custom_long_description'] : $invoice_item_data['long_description']);?></textarea>
                                        <?php if($invoice_item_data['task_id']){
                                            // echo htmlspecialchars($invoice_item_data['custom_description'] ? $invoice_item_data['custom_description'] : $invoice_item_data['description']);
                                            echo '<br/>';
                                            echo _l('(linked to job: %s)',module_job::link_open($invoice_item_data['job_id'],true));
                                        } else {
                                        } ?>
                                        <a href="#" onclick="if(confirm('<?php _e('Delete invoice item?');?>')){$(this).parent().find('input').val(''); $('#invoice_form')[0].submit();} return false;" class="delete ui-state-default ui-corner-all ui-icon ui-icon-trash" style="display:inline-block; float:right;">[x]</a>
                                    </td>
                                    <?php if($show_task_dates){ ?>
                                    <td>
                                        <input type="text" name="invoice_invoice_item[<?php echo $invoice_item_id;?>][date_done]" value="<?php echo print_date($invoice_item_data['date_done']);?>" class="date_field">
                                    </td>
                                    <?php } ?>
                                    <td>
                                        <input type="text" name="invoice_invoice_item[<?php echo $invoice_item_id;?>][hours]" value="<?php echo $invoice_item_data['hours'];?>" size="3" style="width:25px;"  onchange="setamount(this.value,'<?php echo $invoice_item_id;?>',<?php echo $task_hourly_rate;?>);" id="<?php echo $invoice_item_id;?>invoice_itemqty" onkeyup="setamount(this.value,'<?php echo $invoice_item_id;?>',<?php echo $task_hourly_rate;?>);">
                                    </td>
                                    <td>
                                        <input type="text" name="invoice_invoice_item[<?php echo $invoice_item_id;?>][hourly_rate]" value="<?php echo $task_hourly_rate;?>" id="<?php echo $invoice_item_id;?>invoice_itemrate"  size="3" style="width:35px;" onchange="setamount($('#<?php echo $invoice_item_id;?>invoice_itemqty').val(),<?php echo $invoice_item_id;?>);" onkeyup="setamount($('#<?php echo $invoice_item_id;?>invoice_itemqty').val(),<?php echo $invoice_item_id;?>);">
                                    </td>
                                    <td nowrap="">
                                        <?php echo currency('<input type="text" name="invoice_invoice_item['.$invoice_item_id.'][amount]" value="'.$invoice_item_amount.'" id="'.$invoice_item_id.'invoice_itemamount" class="currency">',true,$invoice['currency_id']);?>
                                    </td>
                                    <td nowrap="nowrap" align="center">
                                        <input type="submit" name="ts" class="save_invoice_item small_button" value="<?php _e('Save');?>">
                                    </td>
                                </tr>
                                <tr>
                                    <?php if(module_config::c('invoice_task_numbers',1)){ ?>
                                    <td>
                                    </td>
                                    <?php } ?>
                                    <td>
                                    </td>
                                    <?php if($show_task_dates){ ?>
                                    <td>
                                    </td>
                                    <?php } ?>
                                    <td>
                                    </td>
                                    <td>
                                    </td>
                                    <td colspan="2">
                                        <input type="hidden" name="invoice_invoice_item[<?php echo $invoice_item_id;?>][taxable_t]" value="1">
                                        <input type="checkbox" name="invoice_invoice_item[<?php echo $invoice_item_id;?>][taxable]" id="invoice_taxable_item_<?php echo $invoice_item_id;?>" value="1" <?php echo $invoice_item_data['taxable'] ? ' checked':'';?> tabindex="17"> <label for="invoice_taxable_item_<?php echo $invoice_item_id;?>"><?php _e('Item is taxable');?></label>
                                    </td>

                                </tr>
                                </tbody>
                                <?php } ?>
                                <tbody id="invoice_item_preview_<?php echo $invoice_item_id;?>">
                                <tr class="<?php echo $c++%2 ? 'odd':'even';?>">
                                    <?php if(module_config::c('invoice_task_numbers',1)){ ?>
                                    <td>
                                        <?php
                                        if(isset($invoice_item_data['custom_task_order']) && (int)$invoice_item_data['custom_task_order']>0){
                                            echo $invoice_item_data['custom_task_order'];
                                        }else if(isset($invoice_item_data['task_order']) && $invoice_item_data['task_order']>0){
                                            echo $invoice_item_data['task_order'];
                                        }
                                        ?>
                                    </td>
                                    <?php } ?>
                                    <td>
                                        <?php
                                        $desc = $invoice_item_data['custom_description'] ? htmlspecialchars($invoice_item_data['custom_description']) : htmlspecialchars($invoice_item_data['description']);
                                        if($invoice_locked){
                                            echo $desc;
                                        }else{ ?>
                                            <a href="#" onclick="editinvoice_item('<?php echo $invoice_item_id;?>',0); return false;"><?php echo (!trim($desc)) ? 'N/A' : $desc;?></a>
                                        <?php }
                                        $long_description = trim($invoice_item_data['custom_long_description'] ? htmlspecialchars($invoice_item_data['custom_long_description']) : htmlspecialchars($invoice_item_data['long_description']));
                                        if($long_description != ''){ ?>
                            <a href="#" class="task_toggle_long_description">&raquo;</a>
                            <div class="task_long_description" <?php if(module_config::c('invoice_show_long_desc',1)){ ?> style="display:block;" <?php } ?>><?php echo forum_text($long_description);?></div>
                            <?php }else{ ?>
    &nbsp;
    <?php } ?>
                                    </td>
                                    <?php if($show_task_dates){ ?>
                                    <td>
                                        <?php echo print_date($invoice_item_data['date_done']);?>
                                    </td>
                                    <?php } ?>
                                    <td>
                                        <?php echo $invoice_item_data['hours']>0 ? $invoice_item_data['hours'] : '-';?>
                                    </td>
                                    <td>

                                        <?php
                                        if($task_hourly_rate!=0){
                                            echo dollar($task_hourly_rate,true,$invoice['currency_id']);
                                        }else{
                                            echo '-';
                                        }
                                            ?>
                                    </td>
                                    <td>
                                        <span class="currency">
                                        <?php
                                            echo dollar($invoice_item_amount,true,$invoice['currency_id']);
                                            ?>
                                        </span>
                                    </td>
                                    <td align="center">
                                        &nbsp;
                                    </td>
                                </tr>
                            </tbody>
                        <?php } ?>
                        <?php if(true){ //(int)$invoice_id>0 ?>
                        <tfoot style="border-top:1px solid #CCC;">
                        <tr>
                            <td colspan="<?php echo $colspan;?>">
                                &nbsp;
                            </td>
                            <td>
                                <?php _e('Sub:');?>
                            </td>
                            <td>
                                <span class="currency">
                                <?php echo dollar($invoice['total_sub_amount'],true,$invoice['currency_id']);?>
                                </span>
                            </td>
                            <td>
                                &nbsp;
                            </td>
                        </tr>


                        <?php if($invoice['discount_type']==1){ // after tax discount ?>

                            <tr>
                                <td colspan="<?php echo $colspan;?>">
                                    &nbsp;
                                </td>
                                <td>
                                    <?php _e('Tax:');?>
                                </td>
                                <td>
                                    <span class="currency">
                                    <?php echo dollar($invoice['total_tax'],true,$invoice['currency_id']);?>
                                    </span>
                                </td>
                                <td>
                                    <?php echo $invoice['total_tax_name'] ;?> =
                                    <?php echo $invoice['total_tax_rate'] . '%' ;?>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="<?php echo $colspan;?>">
                                    &nbsp;
                                </td>
                                <td>
                                    <?php _e('Sub:');?>
                                </td>
                                <td>
                                    <span class="currency">
                                    <?php echo dollar($invoice['total_sub_amount']+$invoice['total_tax'],true,$invoice['currency_id']);?>
                                    </span>
                                </td>
                                <td>
                                    &nbsp;
                                </td>
                            </tr>
                            <?php if(!($invoice_locked && module_security::is_page_editable()) || $invoice['discount_amount']>0){ ?>
                            <tr>
                                <td colspan="<?php echo $colspan-1;?>">
                                    &nbsp;
                                </td>
                                <?php if($invoice_locked){ ?>
                                <td>
                                    &nbsp;
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($invoice['discount_description']);?>
                                </td>
                                <?php }else{ ?>
                                <td colspan="2" align="right">
                                    <input type="text" name="discount_description" value="<?php echo htmlspecialchars($invoice['discount_description']);?>" style="width:80px;">
                                </td>
                                <?php } ?>
                                <td>
                                    <?php if($invoice_locked || !module_security::is_page_editable()){ ?>
                                    <span class="currency">
                                            <?php echo dollar($invoice['discount_amount'],true,$invoice['currency_id']);?>
                                        </span>
                                    <?php }else{ ?>
                                    <input type="text" name="discount_amount" value="<?php echo $invoice['discount_amount'];?>" class="currency">

                                    <?php } ?>
                                </td>
                                <td>
                                    <?php if(!$invoice_locked){ ?>
                                    <input type="submit" name="apply" value="<?php _e('Apply');?>" class="apply_discount small_button">
                                    <?php } ?>
                                    <?php _h('Here you can apply a before tax discount to this invoice. You can name this anything, eg: DISCOUNT, CREDIT, REFUND, etc..'); ?>
                                </td>
                            </tr>
                                <?php } ?>

                    <?php }else{
                    // before tax discount
                                ?>


                            <?php if(!($invoice_locked && module_security::is_page_editable()) || $invoice['discount_amount']>0){ ?>
                            <tr>
                                <td colspan="<?php echo $colspan-1;?>">
                                    &nbsp;
                                </td>
                                <?php if($invoice_locked){ ?>
                                <td>
                                    &nbsp;
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($invoice['discount_description']);?>
                                </td>
                                <?php }else{ ?>
                                <td colspan="2" align="right">
                                    <input type="text" name="discount_description" value="<?php echo htmlspecialchars($invoice['discount_description']);?>" style="width:80px;">
                                </td>
                                <?php } ?>
                                <td>
                                    <?php if($invoice_locked || !module_security::is_page_editable()){ ?>
                                    <span class="currency">
                                                                <?php echo dollar($invoice['discount_amount'],true,$invoice['currency_id']);?>
                                                            </span>
                                    <?php }else{ ?>
                                    <input type="text" name="discount_amount" value="<?php echo $invoice['discount_amount'];?>" class="currency">

                                    <?php } ?>
                                </td>
                                <td>
                                    <?php if(!$invoice_locked){ ?>
                                    <input type="submit" name="apply" value="<?php _e('Apply');?>" class="apply_discount">
                                    <?php } ?>
                                    <?php _h('Here you can apply a before tax discount to this invoice. You can name this anything, eg: DISCOUNT, CREDIT, REFUND, etc..'); ?>
                                </td>
                            </tr>
                                <?php } ?>
                            <?php if($invoice['discount_amount'] != 0){ ?>
                            <tr>
                                <td colspan="<?php echo $colspan;?>">
                                    &nbsp;
                                </td>
                                <td>
                                    <?php _e('Sub:');?>
                                </td>
                                <td>
                                        <span class="currency">
                                        <?php echo dollar($invoice['total_sub_amount']-$invoice['discount_amount'],true,$invoice['currency_id']);?>
                                        </span>
                                </td>
                                <td>
                                    &nbsp;
                                </td>
                            </tr>
                                <?php } ?>
                        <tr>
                            <td colspan="<?php echo $colspan;?>">
                                &nbsp;
                            </td>
                            <td>
                                <?php _e('Tax:');?>
                            </td>
                            <td>
                                <span class="currency">
                                <?php echo dollar($invoice['total_tax'],true,$invoice['currency_id']);?>
                                </span>
                            </td>
                            <td>
                                <?php echo $invoice['total_tax_name'] ;?> =
                                <?php echo $invoice['total_tax_rate'] . '%' ;?>
                            </td>
                        </tr>
                        <?php } ?>
                        <tr>
                            <td colspan="<?php echo $colspan;?>">
                                &nbsp;
                            </td>
                            <td>
                                <?php _e('Total:');?>
                            </td>
                            <td>
                                <span class="currency" style="text-decoration: underline; font-weight: bold;">
                                    <?php echo dollar($invoice['total_amount'],true,$invoice['currency_id']);?>
                                </span>
                            </td>
                            <td>
                                &nbsp;
                            </td>
                        </tr>
                        <tr>
                            <td colspan="<?php echo $colspan+3;?>">&nbsp;</td>
                        </tr>
                        <tr>
                            <td colspan="<?php echo $colspan;?>" align="right">

                            </td>
                            <td>
                                <?php _e('Paid:');?>
                            </td>
                            <td>
                                <span class="currency success_text">
                                    <?php echo dollar($invoice['total_amount_paid'],true,$invoice['currency_id']);?>
                                </span>
                            </td>
                            <td>
                                <?php _h('This is how much the customer has paid against the invoice. When they have paid the due amount the invoice will be marked as paid.');?>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="<?php echo $colspan;?>" align="right">

                            </td>
                            <td>
                                <?php _e('Due:');?>
                            </td>
                            <td>
                                <span class="currency error_text">
                                    <?php echo dollar($invoice['total_amount_due'],true,$invoice['currency_id']);?>
                                </span>
                            </td>
                            <td >
                                &nbsp;
                            </td>
                        </tr>
                        <?php if($invoice['total_amount_credit']>0){ ?>
                        <tr>
                            <td colspan="<?php echo $colspan;?>" align="center">
                                <a href="?_process=assign_credit_to_customer&invoice_id=<?php echo $invoice_id;?>"><?php _e('This customer has overpaid this invoice. Click here to assign this as credit to their account for a future invoice.');?></a>
                            </td>
                            <td>
                                <?php _e('Credit:');?>
                            </td>
                            <td>
                                <span class="currency success_text">
                                    <?php echo dollar($invoice['total_amount_credit'],true,$invoice['currency_id']);?>
                                </span>
                            </td>
                            <td>

                            </td>
                        </tr>
                        <?php } ?>
                        </tfoot>
<?php } ?>
					</table>
                    </div> <!-- content box -->
                    <?php if($invoice_id){ ?>

                <script type="text/javascript">
                    function editinvoice_payment(invoice_payment_id,hours){
                        $('#invoice_payment_preview_'+invoice_payment_id).hide();
                        $('#invoice_payment_edit_'+invoice_payment_id).show();

                    }
                </script>

					<h3><?php echo _l('Invoice payment history'); ?></h3>

                        <div class="content_box_wheader">

                    <table border="0" cellspacing="0" cellpadding="2" class="tableclass tableclass_rows tableclass_full">
                        <thead>
                        <tr>
                            <th><?php _e('Payment Date');?></th>
                            <th><?php _e('Payment Method');?></th>
                            <th><?php _e('Amount');?></th>
                            <th><?php _e('Details');?></th>
                            <th width="80"> </th>
                        </tr>
                        </thead>
                        <?php if(module_security::is_page_editable() && module_invoice::can_i('create','Invoice Payments')){ //

                        $payment_date = time(); // today.
                        if(isset($invoice['date_due']) && $invoice['date_due'] != '' && $invoice['date_due'] != '0000-00-00' && strtotime($invoice['date_due'])<$payment_date){
                            $payment_date = strtotime($invoice['date_due']);
                        }

                        ?>
						<tbody>
                        <tr>
                            <td>
                                <input type="text" name="invoice_invoice_payment[new][date_paid]" id="newinvoice_payment_date" value="<?php echo print_date($payment_date);?>" class="date_field">
                                <input type="hidden" name="invoice_invoice_payment[new][payment_type]" id="newinvoice_payment_type" value="<?php echo _INVOICE_PAYMENT_TYPE_NORMAL;?>">
                                <input type="hidden" name="invoice_invoice_payment[new][other_id]" id="newinvoice_payment_other_id" value="">

                            </td>
                            <td>
                                
                                <?php echo print_select_box(module_invoice::get_payment_methods(),'invoice_invoice_payment[new][method]',module_config::s('invoice_payment_default_method','Bank'),'',true,false,true); ?>
                                <!-- <input type="text" name="invoice_invoice_payment[new][method]" value="<?php echo module_config::s('invoice_payment_default_method','Bank');?>" size="20">-->
                            </td>
                            <td nowrap="">
                                <?php echo '<input type="text" name="invoice_invoice_payment[new][amount]" value="'.number_format($invoice['total_amount_due'],2,'.','').'" id="newinvoice_paymentamount" class="currency">';?>
                                <?php echo print_select_box(get_multiple('currency','','currency_id'),'invoice_invoice_payment[new][currency_id]',$invoice['currency_id'],'',false,'code'); ?>
                            </td>
                            <td>&nbsp;</td>
                            <td align="center">
                                <input type="hidden" name="add_payment" value="0" id="add_payment">
                                <input type="button" name="add_payment_btn" value="<?php _e('Add payment');?>" class="save_invoice_payment small_button" onclick="$('#add_payment').val('go'); $('#invoice_form')[0].submit(); return false;">
                            </td>
                        </tr>
						</tbody>
                        <?php } ?>
                        <tbody>
                        <?php foreach(module_invoice::get_invoice_payments($invoice_id) as $invoice_payment_id => $invoice_payment_data){

                            if(module_invoice::can_i('edit','Invoice Payments') && module_security::is_page_editable()){
                            ?>
                                <tr id="invoice_payment_edit_<?php echo $invoice_payment_id;?>" style="display:none;">
                                    <td>
                                        <input type="text" name="invoice_invoice_payment[<?php echo $invoice_payment_id;?>][date_paid]" value="<?php echo print_date($invoice_payment_data['date_paid']);?>" class="date_field" id="invoice_payment_desc_<?php echo $invoice_payment_id;?>">
                                        <?php if(module_invoice::can_i('delete','Invoice Payments')){ ?>
                                        <a href="#" onclick="if(confirm('<?php _e('Delete invoice payment?');?>')){$('#<?php echo $invoice_payment_id;?>invoice_paymentamount').val(''); $('#invoice_form')[0].submit();} return false;"  class="delete ui-state-default ui-corner-all ui-icon ui-icon-trash" style="display:inline-block;">[x]</a>
                                        <?php } ?>
                                    </td><td>
                                        <input type="text" name="invoice_invoice_payment[<?php echo $invoice_payment_id;?>][method]" value="<?php echo htmlspecialchars($invoice_payment_data['method']);?>" size="20">
                                    </td>
                                    <td nowrap="">
                                        <?php echo '<input type="text" name="invoice_invoice_payment['.$invoice_payment_id.'][amount]" value="'.$invoice_payment_data['amount'].'" id="'.$invoice_payment_id.'invoice_paymentamount" class="currency">';?>
                                        <?php echo print_select_box(get_multiple('currency','','currency_id'),'invoice_invoice_payment['.$invoice_payment_id.'][currency_id]',$invoice_payment_data['currency_id'],'',false,'code'); ?>
                                    </td>
                                    <td>
                                        <?php
                                        $notes = '';
                                        if(isset($invoice_payment_data['data'])&&$invoice_payment_data['data']){
                                            $details = unserialize($invoice_payment_data['data']);
                                            if(isset($details['custom_notes'])){
                                                $notes = $details['custom_notes'];
                                            }
                                        }
                                        ?>
                                        <input type="text" name="invoice_invoice_payment[<?php echo $invoice_payment_id;?>][custom_notes]" value="<?php echo htmlspecialchars($notes);?>" size="20">
                                        <?php if(isset($invoice_payment_data['data'])&&$invoice_payment_data['data']){
                                            $details = unserialize($invoice_payment_data['data']);
                                            if(isset($details['log'])){
                                                ?>
                                                <ul>
                                                    <?php foreach($details['log'] as $log){
                                                    echo '<li>'.$log.'</li>';
                                                } ?>
                                                </ul>
                                                <?php
                                            }
                                        } ?>
                                    </td>
                                    <td nowrap="nowrap">
                                        <input type="submit" name="ts" class="save_invoice_payment small_button" value="<?php _e('Save');?>">
                                    </td>
                                </tr>
                            <?php } ?>
                                <tr id="invoice_payment_preview_<?php echo $invoice_payment_id;?>">
                                    <td>
                                        <?php if(module_invoice::can_i('edit','Invoice Payments') && module_security::is_page_editable()){ ?>
                                        <a href="#" onclick="editinvoice_payment('<?php echo $invoice_payment_id;?>',0); return false;"><?php echo (!trim($invoice_payment_data['date_paid']) || $invoice_payment_data['date_paid'] == '0000-00-00') ? _l('Pending on %s',print_date($invoice_payment_data['date_created'])) : print_date($invoice_payment_data['date_paid']);?></a>
                                        <?php }else{ ?>
                                            <?php echo print_date($invoice_payment_data['date_paid']);?>
                                        <?php } ?>
                                    </td>
                                    <td>
                                        <?php
                                        switch($invoice_payment_data['payment_type']){
                                            case _INVOICE_PAYMENT_TYPE_NORMAL:
                                                echo htmlspecialchars($invoice_payment_data['method']);
                                                break;
                                            case _INVOICE_PAYMENT_TYPE_CREDIT:
                                                _e('Credit from customer %s',module_customer::link_open($invoice_payment_data['other_id'],true));
                                                break;
                                            case _INVOICE_PAYMENT_TYPE_DEPOSIT:
                                                _e('Deposit from invoice %s',module_invoice::link_open($invoice_payment_data['other_id'],true));
                                                break;

                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <span class="currency">
                                        <?php /* echo $invoice_payment_data['amount']>0 ? dollar($invoice_payment_data['amount'],true,$invoice['currency_id']) : dollar($invoice_payment_data['hours']*$invoice['hourly_rate'],true,$invoice['currency_id']); */?>
                                        <?php echo dollar($invoice_payment_data['amount'],true,$invoice_payment_data['currency_id']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if(isset($invoice_payment_data['data'])&&$invoice_payment_data['data']){
                                            $details = unserialize($invoice_payment_data['data']);
                                            if(isset($details['custom_notes'])){
                                                echo htmlspecialchars($details['custom_notes']).'<br>';
                                            }
                                            if(isset($details['log'])){
                                                ?>
                                                <a href="#" onclick="$('#details_<?php echo $invoice_payment_data['invoice_payment_id'];?>').show(); $(this).hide(); return false;"><?php _e('Show...');?></a>
                                                <div id="details_<?php echo $invoice_payment_data['invoice_payment_id'];?>" style="display:none;">
                                                    <ul>
                                                        <?php foreach($details['log'] as $log){
                                                            echo '<li>'.$log.'</li>';
                                                        } ?>
                                                    </ul>
                                                </div>
                                                <?php
                                            }
                                        } ?>
                                    </td>
                                    <td align="center">
                                        <a href="<?php echo module_invoice::link_receipt($invoice_payment_data['invoice_payment_id']);?>" target="_blank"><?php _e('View Receipt');?></a>
                                    </td>
                                </tr>
                        <?php } ?>
                        </tbody>
					</table>
                        </div>

    <?php } ?>
                    
                </td>
			</tr>
			<tr>
				<td colspan="2" style="text-align: center">
					<input type="submit" name="butt_save" id="butt_save" value="<?php echo _l('Save invoice'); ?>" class="submit_button save_button" />
					<?php if((int)$invoice_id){ ?>
                        <?php if($invoice['date_paid'] && $invoice['date_paid']!='0000-00-00'){ ?>
                            <input type="submit" name="butt_email" id="butt_email" value="<?php echo _l('Email Receipt'); ?>" class="submit_button save_button" />
                        <?php }else{ ?>
					        <input type="submit" name="butt_email" id="butt_email" value="<?php echo _l('Email Invoice'); ?>" class="submit_button" />
                        <?php } ?>
                        <?php if(function_exists('convert_html2pdf')){
                            if(!module_invoice::can_i('edit','Invoices')){ ?>
                                <input type="button" name="butt_print" id="butt_print" value="<?php echo _l('Print PDF'); ?>" class="submit_button no_permissions" onclick="window.location.href='<?php echo module_invoice::link_public_print($invoice_id);?>';" />
                            <?php }else{ ?>
                                <input type="submit" name="butt_print" id="butt_print" value="<?php echo _l('Print PDF'); ?>" class="submit_button" />
                            <?php } ?>
                        <?php } ?>
					<?php } ?>
					<?php if((int)$invoice_id && module_invoice::can_i('delete','Invoices')){ ?>
					<input type="submit" name="butt_del" id="butt_del" value="<?php echo _l('Delete'); ?>" class="submit_button delete_button" />
					<?php } ?>
					<input type="button" name="cancel" value="<?php echo _l('Cancel'); ?>" onclick="window.location.href='<?php echo module_invoice::link_open(false); ?>';" class="submit_button" />
				</td>
			</tr>
		</tbody>
	</table>


</form>
