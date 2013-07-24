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
if(!$invoice_safe)die('failed');
$invoice_id = (int)$_REQUEST['invoice_id'];
$invoice = module_invoice::get_invoice($invoice_id);

print_heading(_l('Email Invoice: %s',$invoice['name']));


module_template::init_template('invoice_email_due','Dear {CUSTOMER_NAME},<br>
<br>
Please find attached your invoice {INVOICE_NUMBER}.<br><br>
The {TOTAL_AMOUNT} is due on {DATE_DUE}.<br><br>
You can also view this invoice online by <a href="{INVOICE_URL}">clicking here</a>.<br><br>
Thank you,<br><br>
{FROM_NAME}
','Invoice Owing: {INVOICE_NUMBER}',array(
                                       'CUSTOMER_NAME' => 'Customers Name',
                                       'INVOICE_NUMBER' => 'Invoice Number',
                                       'TOTAL_AMOUNT' => 'Total amount of invoice',
                                       'DATE_DUE' => 'Due Date',
                                       'FROM_NAME' => 'Your name',
                                       'INVOICE_URL' => 'Link to invoice for customer',
                                       ));



module_template::init_template('credit_note_email','Dear {CUSTOMER_NAME},<br>
<br>
Please find attached your Credit Note {INVOICE_NUMBER} for Invoice {CREDIT_INVOICE_NUMBER}.<br><br>
Total amount: {TOTAL_AMOUNT}<br><br>
You can view this invoice online by <a href="{INVOICE_URL}">clicking here</a>.<br><br>
Thank you,<br><br>
{FROM_NAME}
','Credit Note: {INVOICE_NUMBER}',array(
                                       'CUSTOMER_NAME' => 'Customers Name',
                                       'INVOICE_NUMBER' => 'Credit Note Number',
                                       'CREDIT_INVOICE_NUMBER' => 'Original Invoice Number',
                                       'TOTAL_AMOUNT' => 'Total amount of invoice',
                                       'FROM_NAME' => 'Your name',
                                       'INVOICE_URL' => 'Link to invoice for customer',
                                       ));


module_template::init_template('invoice_email_overdue','Dear {CUSTOMER_NAME},<br>
<br>
The attached invoice {INVOICE_NUMBER} is now <span style="font-weight:bold; color:#FF0000;">overdue</span>.<br><br>
The {TOTAL_AMOUNT} was due on {DATE_DUE}.<br><br>
You can also view this invoice online by <a href="{INVOICE_URL}">clicking here</a>.<br><br>
Thank you,<br><br>
{FROM_NAME}
','Invoice Overdue: {INVOICE_NUMBER}',array(
                                       'CUSTOMER_NAME' => 'Customers Name',
                                       'INVOICE_NUMBER' => 'Invoice Number',
                                       'TOTAL_AMOUNT' => 'Total amount of invoice',
                                       'DATE_DUE' => 'Due Date',
                                       'FROM_NAME' => 'Your name',
                                       'INVOICE_URL' => 'Link to invoice for customer',
                                       ));


module_template::init_template('invoice_email_paid','Dear {CUSTOMER_NAME},<br>
<br>
Thank you for your {TOTAL_AMOUNT} payment on invoice {INVOICE_NUMBER}.<br><br>
This invoice was paid in full on {DATE_PAID}.<br><br>
Please find attached the receipt for this invoice payment. <br>
You can also view this invoice online by <a href="{INVOICE_URL}">clicking here</a>.<br><br>
Thank you,<br><br>
{FROM_NAME}
','Invoice Paid: {INVOICE_NUMBER}',array(
                                       'CUSTOMER_NAME' => 'Customers Name',
                                       'INVOICE_NUMBER' => 'Invoice Number',
                                       'TOTAL_AMOUNT' => 'Total amount of invoice',
                                       'DATE_PAID' => 'Paid date',
                                       'FROM_NAME' => 'Your name',
                                       'INVOICE_URL' => 'Link to invoice for customer',
                                       ));


// template for sending emails.
// are we sending the paid one? or the dueone.
$original_template_name = $template_name = '';
$template_name = '';
if(isset($invoice['credit_note_id']) && $invoice['credit_note_id']){
    $original_template_name = $template_name = 'credit_note_email';
}else if($invoice['date_paid'] && $invoice['date_paid']!='0000-00-00'){
    $original_template_name = $template_name = 'invoice_email_paid';
}else if(($invoice['date_due'] && $invoice['date_due']!='0000-00-00') && (!$invoice['date_paid'] || $invoice['date_paid'] == '0000-00-00') && strtotime($invoice['date_due']) < time()){
    $original_template_name = $template_name = 'invoice_email_overdue';
}else{
    $original_template_name = $template_name = 'invoice_email_due';
}
$template_name = isset($_REQUEST['template_name']) ? $_REQUEST['template_name'] : $template_name;
$template = module_template::get_template_by_key($template_name);

$replace = module_invoice::get_replace_fields($invoice_id,$invoice);

$replace['from_name'] = module_security::get_loggedin_name();

// generate the PDF ready for sending.
$pdf = module_invoice::generate_pdf($invoice_id);

// find available "to" recipients.
// customer contacts.
$to_select=false;
if($invoice['customer_id']){
    $customer = module_customer::get_customer($invoice['customer_id']);
    $replace['customer_name'] = $customer['customer_name'];
    $to = module_user::get_contacts(array('customer_id'=>$invoice['customer_id']));
    if($invoice['user_id']){
        $primary = module_user::get_user($invoice['user_id']);
        if($primary){
            $to_select = $primary['email'];
        }
    }else if($customer['primary_user_id']){
        $primary = module_user::get_user($customer['primary_user_id']);
        if($primary){
            $to_select = $primary['email'];
        }
    }
}else if($invoice['member_id']){
    $member = module_member::get_member($invoice['member_id']);
    $to = array($member);
    $replace['customer_name'] = $member['first_name'];
}else{
    $to = array();
}

$template->assign_values($replace);


module_email::print_compose(
    array(
        'find_other_templates' => $original_template_name, // find others based on this name, eg: job_email*
        'current_template' => $template_name,
        'customer_id'=>$invoice['customer_id'],
        'to'=>$to,
        'to_select'=>$to_select,
        'bcc'=>module_config::c('admin_email_address',''),
        'content' => $template->render('html'),
        'subject' => $template->replace_description(),
        'success_url'=>module_invoice::link_open($invoice_id),
        'success_callback'=>'module_invoice::email_sent', // ('.$invoice_id.',"'.$template_name.'","{SUBJECT}","{TO}");
        'success_callback_args'=>array(
            'invoice_id' => $invoice_id,
            'template_name' => $template_name,
        ),
        'invoice_id'=>$invoice_id,
        'cancel_url'=>module_invoice::link_open($invoice_id),
        'attachments' => array(
            array(
                'path'=>$pdf,
                'name'=>basename($pdf),
                'preview'=>module_invoice::link_generate($invoice_id,array('arguments'=>array('go'=>1,'print'=>1),'page'=>'invoice_admin','full'=>false)),
            ),
        ),
    )
);
?>