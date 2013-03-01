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


define('_INVOICE_PAYMENT_TYPE_NORMAL',0);
define('_INVOICE_PAYMENT_TYPE_DEPOSIT',1);
define('_INVOICE_PAYMENT_TYPE_CREDIT',2);

class module_invoice extends module_base{
	
	public $links;
	public $invoice_types;

    public static function can_i($actions,$name=false,$category=false,$module=false){
        if(!$module)$module=__CLASS__;
        return parent::can_i($actions,$name,$category,$module);
    }
	public static function get_class() {
        return __CLASS__;
    }
	public function init(){
		$this->links = array();
		$this->invoice_types = array();
		$this->module_name = "invoice";
		$this->module_position = 18;

        $this->version = 2.588;
        //2.421 - fix for invoice currency in printout.
        //2.422 - fix for assigning credit
        //2.423 - assigning contacts to invoices.
        //2.424 - fix for invoice prefix number.
        //2.5 - recurring invoices. task hourly rates. payment methods per invoice. payment methods text moved to template area.
        //2.51 - date fix for recurring invoices.
        //2.52 - fix for saving extra fields on renewd invoices.
        //2.521 - added currency to invoice hourly amount.
        //2.522 - blank extra fields come through as N/A in invoices now.
        //2.523 - paid date not clearing properly when renewing invoice.
        //2.524 - added member_id for better subscription integration (eg: sending an email).
        //2.525 - multiple currency fixes
        //2.53 - new theme layout
        //2.531 - date done moved into invoice layout.
        //2.532 - bug fix in invoice task list - hourly rate
        //2.533 - permission fix for viewing invoices without customer access.
        //2.534 - customise the Hours column header
        //2.535 - upgrade fix
        //2.536 - replace fields in email template
        //2.537 - CUSTOMER_GROUP, WEBSITE_GROUP and JOB_GROUP added to invoice templates
        //2.538 - testing non-taxable items in invoices
        //2.539 - perm fix
        //2.54 - invoice empty items
        //2.541 - printing from mobile
        //2.542 - invoice qty/amount fix
        //2.543 - another invoice qty/amount fix
        //2.544 - send to primary contact
        //2.545 - discount type
        //2.546 - tax fix. calculate individually on each item.
        //2.547 - date renewl on invoices, -1 day
        //2.548 - mobile fix
        //2.549 - external invoice fix
        //2.55 - fix for invoice re-generation and dates.
        //2.551 - fix for 100% discounted invoices
        //2.552 - extra fields in invoice print from customer section
        //2.553 - custom details in invoice payment area.
        //2.554 - invoice numbers A to Z, then AA to AZ, etc..
        //2.555 - support for incrementing invoice numbers - see advanced invoice_incrementing settings
        //2.556 - better support for multi-job and multi-website invoice prints
        //2.557 - before tax/after tax invoice fix
        //2.558 - 'invoice_send_alerts' advanced setting added
        //2.559 - invoice line numbers are now editable
        //2.56 - quick search based on invoice amount or invoice payment
        //2.561 - remove discount on renewed invoices
        //2.562 - support for negative invoice line items
        //2.563 - invoice bug, possible duplication fix?
        //2.564 - bug fix for incrementing invoice numbers
        //2.565 - option to use invoice name as job name (see invoice_name_match_job option)
        //2.566 - task "long description" added to invoice items like it is in job tasks
        //2.567 - quicker way to print multiple pdf's
        //2.568 - starting work on handling job deposits and customer credit
        //2.569 - added 'contact_first_name' and 'contact_last_name' to template fields.
        //2.570 - speed improvements.
        //2.571 - currency fixes and email features
        //2.572 - currency fixes and email features
        //2.573 - invoice email bug fix
        //2.574 - {INVOICE_DATE_RANGE} template tag added to invoice emails.
        //2.575 - job/invoice deposits made easier.
        //2.576 - deposits and customer credits working nicely now.
        //2.577 - choose different templates when sending an invoice to customer.
        //2.578 - cancel invoice so no more payment reminder
        //2.579 - fix for subscription in finance upcoming items
        //2.58 - invoice credit fixing.
        //2.581 - customer subscription fixes
        //2.582 - support for public invoices notes
        //2.583 - support for multiple invoice pdf print templates
        //2.584 - search by customer group
        //2.585 - speed improvements
        //2.586 - item hourly rate/qty improvements
        //2.587 - bug fix for invoice subscription renewals
        //2.588 - date fix on dashboard invoice alerts


        // todo: add completed date as a configurable column
        // todo: move invoice layout to a template system.

        module_config::register_css('invoice','invoice.css');

        hook_add('finance_recurring_list','module_invoice::get_finance_recurring_items');
		
	}

    public function pre_menu(){

        if($this->can_i('view','Invoices')){
            // only display if a customer has been created.
            if(isset($_REQUEST['customer_id']) && $_REQUEST['customer_id'] && $_REQUEST['customer_id']!='new'){
                // how many invoices?
                $invoices = $this->get_invoices(array('customer_id'=>$_REQUEST['customer_id']));
                $name = _l('Invoices');
                if(count($invoices)){
                    $name .= " <span class='menu_label'>".count($invoices)."</span> ";
                }
                $this->links[] = array(
                    "name"=>$name,
                    "p"=>"invoice_admin",
                    'args'=>array('invoice_id'=>false),
                    'holder_module' => 'customer', // which parent module this link will sit under.
                    'holder_module_page' => 'customer_admin_open',  // which page this link will be automatically added to.
                    'menu_include_parent' => 0,
                );
            }
            $this->links[] = array(
                "name"=>"Invoices",
                "p"=>"invoice_admin",
                'args'=>array('invoice_id'=>false),
            );

            if(module_config::can_i('view','Settings')){
                $this->links[] = array(
                    "name"=>"Currency",
                    "p"=>"currency",
                    'args'=>array('currency_id'=>false),
                    'holder_module' => 'config', // which parent module this link will sit under.
                    'holder_module_page' => 'config_admin',  // which page this link will be automatically added to.
                    'menu_include_parent' => 0,
                );
            }
        }
        /*else{
            if(module_security::is_contact()){
                // find out how many for this contact.
                $customer_ids = module_security::get_customer_restrictions();
                if($customer_ids){
                    $invoices = array();
                    foreach($customer_ids as $customer_id){
                        $invoices = $invoices + $this->get_invoices(array('customer_id'=>$customer_id));
                    }
                    $name = _l('Invoices');
                    if(count($invoices)){
                        $name .= " <span class='menu_label'>".count($invoices)."</span> ";
                    }
                    $this->links[] = array(
                        "name"=>$name,
                        "p"=>"invoice_admin",
                        'args'=>array('invoice_id'=>false),
                    );
                }
            }
        }*/
    }


    public function ajax_search($search_key){
        // return results based on an ajax search.
        $ajax_results = array();
        $search_key = trim($search_key);
        if(strlen($search_key) > 3){
            $results = $this->get_invoices(array('generic'=>$search_key));
            if(count($results)){
                foreach($results as $result){
                    $match_string = _l('Invoice: ');
                    $match_string .= _shl($result['name'],$search_key);
                    $match_string .= ' for ';
                    $match_string .= dollar($result['cached_total'],true,$result['currency_id']);
                    $match_string .= ' ('.($result['date_paid']&&$result['date_paid']!='0000-00-00'?_l('Paid'):_l('Unpaid')).')';
                    $ajax_results [] = '<a href="'.$this->link_open($result['invoice_id']) . '">' . $match_string . '</a>';
                }
            }
        }
        if(strlen($search_key) >= 2 && is_numeric($search_key)){
            $sql = "SELECT * FROM `"._DB_PREFIX."invoice_payment` WHERE `amount` = '".mysql_real_escape_string($search_key)."' ORDER BY date_paid DESC LIMIT 5";
            $results = qa($sql);
            if(count($results)){
                foreach($results as $result){
                    $match_string = _l('Invoice Payment: ');
                    $match_string .= dollar($result['amount'],true,$result['currency_id']) .' on '. print_date($result['date_paid']);
                    $ajax_results [] = '<a href="'.$this->link_open($result['invoice_id']) . '">' . $match_string . '</a>';
                }
            }
            $sql = "SELECT * FROM `"._DB_PREFIX."invoice` WHERE `cached_total` = '".mysql_real_escape_string($search_key)."' ORDER BY date_create DESC LIMIT 5";
            $results = qa($sql);
            if(count($results)){
                foreach($results as $result){
                    $match_string = _l('Invoice: ');
                    $match_string .= htmlspecialchars($result['name']);
                    $match_string .= ' for ';
                    $match_string .= dollar($result['cached_total'],true,$result['currency_id']);
                    $match_string .= ' ('.($result['date_paid']&&$result['date_paid']!='0000-00-00'?_l('Paid'):_l('Unpaid')).')';
                    $ajax_results [] = '<a href="'.$this->link_open($result['invoice_id']) . '">' . $match_string . '</a>';
                }
            }
        }
        return $ajax_results;
    }
	public function handle_hook($hook,&$calling_module=false){
		switch($hook){
			case "home_alerts":
				$alerts = array();
                if($this->can_i('edit','Invoices') && module_config::c('invoice_alerts',1)){
                    // find any invoices that are past the due date and dont have a paid date.
                    $sql = "SELECT * FROM `"._DB_PREFIX."invoice` p ";
                    $sql .= " WHERE p.date_due != '0000-00-00' AND p.date_due <= '".date('Y-m-d',strtotime('+'.module_config::c('alert_days_in_future',5).' days'))."' AND p.date_paid = '0000-00-00'";
                    $invoice_items = qa($sql);
                    
                    foreach($invoice_items as $invoice_item){
                        $invoice = self::get_invoice($invoice_item['invoice_id']);
                        if(!$invoice||$invoice['invoice_id']!=$invoice_item['invoice_id'])continue;
                        if(isset($invoice['date_cancel'])&&$invoice['date_cancel']!='0000-00-00')continue;
                        $alert_res = process_alert($invoice_item['date_due'], _l('Invoice Payment Due'));
                        if($alert_res){
                            $alert_res['link'] = $this->link_open($invoice_item['invoice_id']);
                            $alert_res['name'] = $invoice_item['name'];
                            if($invoice['date_sent'] && $invoice['date_sent']!='0000-00-00'){
                                $secs = date("U") - date("U",strtotime($invoice['date_sent']));
                                $days = $secs/86400;
                                $days = floor($days);
                                $alert_res['name'] .= ' ('._l('last sent %s days ago',$days).')';
                            }
                            $alerts[] = $alert_res;
                        }
                    }
                }
                if(module_config::c('invoice_send_alerts',1)){
                    if($this->can_i('edit','Invoices')){
                        // find any invoices that haven't been sent
                        $sql = "SELECT * FROM `"._DB_PREFIX."invoice` p ";
                        $sql .= " WHERE p.date_sent = '0000-00-00' AND p.date_paid = '0000-00-00'";
                        $invoice_items = qa($sql);
                        foreach($invoice_items as $invoice_item){
                            $invoice = self::get_invoice($invoice_item['invoice_id']);
                            if(!$invoice||$invoice['invoice_id']!=$invoice_item['invoice_id'])continue;
                            $alert_res = process_alert($invoice['date_create'] != '0000-00-00' ? $invoice['date_create'] : date('Y-m-d'), _l('Invoice Not Sent'));
                            if($alert_res){
                                $alert_res['link'] = $this->link_open($invoice_item['invoice_id']);
                                $alert_res['name'] = $invoice_item['name'];
                                $alerts[] = $alert_res;
                            }
                        }
                    }
				}

                if($this->can_i('edit','Invoices') && module_config::c('invoice_renew_alerts',1)){
                    // find any invoices that have a renew date soon and have not been renewed.
                    $sql = "SELECT p.* FROM `"._DB_PREFIX."invoice` p ";
                    $sql .= " WHERE p.date_renew != '0000-00-00'";
                    $sql .= " AND p.date_renew <= '".date('Y-m-d',strtotime('+'.module_config::c('alert_days_in_future',5).' days'))."'";
                    $sql .= " AND (p.renew_invoice_id IS NULL OR p.renew_invoice_id = 0)";
                    $res = qa($sql);
                    foreach($res as $r){
                        $invoice = self::get_invoice($r['invoice_id']);
                        if(!$invoice||$invoice['invoice_id']!=$r['invoice_id'])continue;
                        if(isset($invoice['date_cancel'])&&$invoice['date_cancel']!='0000-00-00')continue;
                        $alert_res = process_alert($r['date_renew'], _l('Invoice Renewal Pending'));
                        if($alert_res){
                            $alert_res['link'] = $this->link_open($r['invoice_id']);
                            $alert_res['name'] = $r['name'];
                            // work out renewal period
                            if($r['date_create'] && $r['date_create'] != '0000-00-00'){
                                $time_diff = strtotime($r['date_renew']) - strtotime($r['date_create']);
                                if($time_diff > 0){
                                    $diff_type = 'day';
                                    $days = round($time_diff / 86400);
                                    if($days >= 365){
                                        $time_diff = round($days/365,1);
                                        $diff_type = 'year';
                                    }else{
                                        $time_diff = $days;
                                    }
                                    $alert_res['name'] .= ' '._l('(%s %s renewal)',$time_diff,$diff_type);
                                }
                            }
                            $alerts[] = $alert_res;
                        }
                    }
                }
				return $alerts;
				break;
        }
        return false;
    }


    public static function link_generate($invoice_id=false,$options=array(),$link_options=array()){

        $key = 'invoice_id';
        if($invoice_id === false && $link_options){
            foreach($link_options as $link_option){
                if(isset($link_option['data']) && isset($link_option['data'][$key])){
                    ${$key} = $link_option['data'][$key];
                    break;
                }
            }
            if(!${$key} && isset($_REQUEST[$key])){
                ${$key} = $_REQUEST[$key];
            }
        }
        $bubble_to_module = false;
        if(!isset($options['type']))$options['type']='invoice';
        if(!isset($options['page']))$options['page'] = 'invoice_admin';
        if(!isset($options['arguments'])){
            $options['arguments'] = array();
        }
        $options['arguments']['invoice_id'] = $invoice_id;
        $options['module'] = 'invoice';
        if(!isset($options['data']) || !$options['data']){
            if((int)$invoice_id > 0){
                $data = self::get_invoice($invoice_id,2);
            }else{
                $data = array(

                );
            }
            $options['data'] = $data;
        }else{
            $data = $options['data'];
        }
        if(!isset($data['total_amount_due'])){

        }else if(isset($data['date_cancel']) && $data['date_cancel'] != '0000-00-00'){
            $link_options['class'] = 'invoice_cancel';
        }else if($data['total_amount_due'] <= 0){
            $link_options['class'] = 'success_text';
        }else{
            $link_options['class'] = 'error_text';
        }
        // what text should we display in this link?
        $options['text'] = (!isset($data['name'])||!trim($data['name'])) ? 'N/A' : $data['name'];
        if(
            // only bubble for admins:
            self::can_i('edit','Invoices') &&
            (
                isset($data['customer_id']) && $data['customer_id']>0 ||
                isset($_REQUEST['customer_id']) && $_REQUEST['customer_id']>0
            )
        ){
            $bubble_to_module = array(
                'module' => 'customer',
                'argument' => 'customer_id',
            );
        }
        array_unshift($link_options,$options);


        if(!module_security::has_feature_access(array(
            'name' => 'Customers',
            'module' => 'customer',
            'category' => 'Customer',
            'view' => 1,
            'description' => 'view',
        ))
           // only apply this restriction to administrators, not contacts.
           //&& self::can_i('edit','Invoices')

        ){
            $bubble_to_module = false;
            /*
            if(!isset($options['full']) || !$options['full']){
                return '#';
            }else{
                return isset($options['text']) ? $options['text'] : 'N/A';
            }*/

        }
        if($bubble_to_module){
            global $plugins;
            return $plugins[$bubble_to_module['module']]->link_generate(false,array(),$link_options);
        }else{
            // return the link as-is, no more bubbling or anything.
            // pass this off to the global link_generate() function
            //print_r($link_options);
            return link_generate($link_options);

        }
    }

	public static function link_open($invoice_id,$full=false,$data=array()){
        return self::link_generate($invoice_id,array('full'=>$full,'data'=>$data));
    }


    public static function link_receipt($invoice_payment_id,$h=false){
        if($h){
            return md5('s3cret7hash '._UCM_FOLDER.' '.$invoice_payment_id);
        }
        return full_link(_EXTERNAL_TUNNEL.'?m=invoice&h=receipt&i='.$invoice_payment_id.'&hash='.self::link_receipt($invoice_payment_id,true));
    }
    

    public static function link_public($invoice_id,$h=false){
        if($h){
            return md5('s3cret7hash for invoice '._UCM_FOLDER.' '.$invoice_id);
        }
        return full_link(_EXTERNAL_TUNNEL_REWRITE.'m.invoice/h.public/i.'.$invoice_id.'/hash.'.self::link_public($invoice_id,true));
    }
    public static function link_public_print($invoice_id,$h=false){
        if($h){
            return md5('s3cret7hash for invoice '._UCM_FOLDER.' '.$invoice_id);
        }
        return full_link(_EXTERNAL_TUNNEL_REWRITE.'m.invoice/h.public_print/i.'.$invoice_id.'/hash.'.self::link_public($invoice_id,true));
    }


    public function external_hook($hook){
        
        switch($hook){
            case 'public_print':
                ob_start();

                $invoice_id = (isset($_REQUEST['i'])) ? (int)$_REQUEST['i'] : false;
                $hash = (isset($_REQUEST['hash'])) ? trim($_REQUEST['hash']) : false;
                if($invoice_id && $hash){
                    $correct_hash = $this->link_public_print($invoice_id,true);
                    if($correct_hash == $hash){
                        // check invoice still exists.
                        $invoice_data = $this->get_invoice($invoice_id);
                        if(!$invoice_data || $invoice_data['invoice_id'] != $invoice_id){
                            echo 'Invoice no longer exists';
                            exit;
                        }
                        ini_set('display_errors',false);
                        $pdf_file = $this->generate_pdf($invoice_id);

                        if($pdf_file && is_file($pdf_file)){
                            ob_end_clean();
                            ob_end_clean();

                            // send pdf headers and prompt the user to download the PDF

                            header("Pragma: public");
                            header("Expires: 0");
                            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
                            header("Cache-Control: private",false);
                            header("Content-Type: application/pdf");
                            header("Content-Disposition: attachment; filename=\"".basename($pdf_file)."\";");
                            header("Content-Transfer-Encoding: binary");
                            header("Content-Length: ".filesize($pdf_file));
                            readfile($pdf_file);

                        }else{
                            echo _l('Sorry PDF is not currently available.');
                        }
                    }
                }

                exit;

                break;
            case 'public':
                $invoice_id = (isset($_REQUEST['i'])) ? (int)$_REQUEST['i'] : false;
                $hash = (isset($_REQUEST['hash'])) ? trim($_REQUEST['hash']) : false;
                if($invoice_id && $hash){
                    $correct_hash = $this->link_public($invoice_id,true);
                    if($correct_hash == $hash){


                        // check invoice still exists.
                        $invoice_data = $this->get_invoice($invoice_id);
                        if(!$invoice_data || $invoice_data['invoice_id'] != $invoice_id){
                            echo 'Invoice no longer exists';
                            exit;
                        }

                        // are we processing this payment?
                        if(isset($_REQUEST['payment'])&&$_REQUEST['payment']=='go'){
                            $this->handle_payment();
                        }

                        // all good to print a receipt for this payment.
                        $invoice = $invoice_data = $this->get_invoice($invoice_id);
                        
                        module_template::init_template('external_invoice','<h2>Invoice</h2>
Invoice Number: <strong>{INVOICE_NUMBER}</strong> <br/>
Due Date: <strong>{DUE_DATE}</strong> <br/>
Customer: <strong>{CUSTOMER_NAME}</strong> <br/>
Address: <strong>{CUSTOMER_ADDRESS}</strong> <br/>
Contact: <strong>{CONTACT_NAME} {CONTACT_EMAIL}</strong> <br/>
{PROJECT_TYPE} Name: <strong>{PROJECT_NAME}</strong> <br/>
Job: <strong>{JOB_NAME}</strong> <br/>
<a href="{PRINT_LINK}">Print PDF Invoice</a> <br/>
<br/>
{TASK_LIST}
{PAYMENT_METHODS}
{PAYMENT_HISTORY}
','Used when displaying the external view of an invoice.','code');
                        // correct!
                        // load up the receipt template.
                        $template = module_template::get_template_by_key('external_invoice');



                        ob_start();
                        include('template/invoice_task_list.php');
                        $task_list_html = ob_get_clean();
                        ob_start();
                        include('template/invoice_payment_history.php');
                        $invoice_payment_history = ob_get_clean();
                        ob_start();
                        include('template/invoice_payment_methods.php');
                        $invoice_payment_methods = ob_get_clean();

                        $data = $this->get_replace_fields($invoice_id,$invoice_data);
                        $data['task_list'] = $task_list_html;
                        $data['payment_methods'] = $invoice_payment_methods;
                        $data['payment_history'] = $invoice_payment_history;

                        $template->page_title = htmlspecialchars($invoice_data['name']);

                        $template->assign_values($data);
                        echo $template->render('pretty_html');
                        exit;
                    }
                }
                break;
            case 'receipt':
                $invoice_payment_id = (isset($_REQUEST['i'])) ? (int)$_REQUEST['i'] : false;
                $hash = (isset($_REQUEST['hash'])) ? trim($_REQUEST['hash']) : false;
                if($invoice_payment_id && $hash){
                    $correct_hash = $this->link_receipt($invoice_payment_id,true);
                    if($correct_hash == $hash){
                        // all good to print a receipt for this payment.
                        $invoice_payment_data = $this->get_invoice_payment($invoice_payment_id);
                        if($invoice_payment_data){
                            $invoice_data = $this->get_invoice($invoice_payment_data['invoice_id']);
                            if($invoice_payment_data && $invoice_data){
                                // correct!
                                 module_template::init_template('invoice_payment_receipt','Payment Receipt for Invoice # {NAME}

    Receipt Number: <strong>{RECEIPT_NUMBER}</strong>
    Payment status: <strong>{PAY_STATUS}</strong>
    Payment made on: <strong>{PAYMENT_DATE}</strong>
    Payment amount: <strong>{AMOUNT}</strong>
    Payment method: <strong>{METHOD}</strong>
    ','Receipts for invoice payments.',array(
                                       'NAME' => 'Invoice Number',
                                       'DATE_SENT' => 'Date invoice was sent',
                                       'DATE_DUE' => 'Date invoice was due',
                                       'DATE_PAID' => 'Date invoice was paid',
                                       'HOURLY_RATE' => 'Hourly rate of the invoice',
                                       'TOTAL_AMOUNT' => 'Total amount of invoice',
                                       'TOTAL_AMOUNT_DUE' => 'Total due on invoice',
                                       'TOTAL_AMOUNT_PAID' => 'Total paid on invoice',
                                       'RECEIPT_NUMBER' => 'Our Receipt Number',
                                       'PAY_STATUS' => 'Paid or not',
                                       'PAYMENT_DATE' => 'Date payment was made',
                                       'AMOUNT' => 'Amount that was paid',
                                       'METHOD' => 'What payment method was used',
                                       ));
                                // load up the receipt template.
                                if($invoice_payment_data['date_paid']=='0000-00-00'){
                                    $custom_data = array(
                                        'receipt_number' => 'N/A',
                                        'pay_status' => _l('Not Paid Yet'),
                                        'payment_date' => 'Not Yet',
                                    );
                                }else{
                                    $custom_data = array(
                                        'receipt_number' => $invoice_payment_data['invoice_payment_id'],
                                        'pay_status' => _l('Payment Completed'),
                                        'payment_date' => print_date($invoice_payment_data['date_paid']),
                                    );
                                }
                                $invoice_payment_data['amount'] = dollar($invoice_payment_data['amount'],true,$invoice_payment_data['currency_id']);
                                $template = module_template::get_template_by_key('invoice_payment_receipt');


                                $data = $this->get_replace_fields($invoice_payment_data['invoice_id'],$invoice_data);


                                $template->assign_values($data+$invoice_payment_data+$invoice_data+$custom_data);
                                echo $template->render('pretty_html');
                            }
                        }
                    }
                }
                break;
        }
    }

    public static function get_replace_fields($invoice_id,$invoice_data){

        $customer_data = module_customer::get_customer($invoice_data['customer_id']);
        $address_combined = array();
        if(isset($customer_data['customer_address'])){
            foreach($customer_data['customer_address'] as $key=>$val){
                if(strlen(trim($val)))$address_combined[$key] = $val;
            }
        }
        // do we use the primary contact or a specified contact on the invoice.
        if(isset($invoice_data['user_id']) && $invoice_data['user_id']){
            $contact_data = module_user::get_user($invoice_data['user_id']);
        }else{
            $contact_data = module_user::get_user($customer_data['primary_user_id']);
        }


        // todo - put this out in a "replace" method - so we can use the same replace for PDF and ONLINE view.
        $data = array(
            'invoice_number' => htmlspecialchars($invoice_data['name']),
            'project_type' => _l(module_config::c('project_name_single','Website')),
            'print_link' => self::link_public_print($invoice_id),

            'title' => module_config::s('admin_system_name'),
            'invoice_paid' => ($invoice_data['total_amount_due'] <= 0) ? '<p> <font style="font-size: 1.6em;"><strong>'._l('INVOICE PAID') .'</strong></font> </p>' : '',
            'date_create' => print_date($invoice_data['date_create']),
            'due_date' => print_date($invoice_data['date_due']),
            'customer_details' => ' - todo - ',
            'customer_name' => $customer_data['customer_name'] ? htmlspecialchars($customer_data['customer_name']) : _l('N/A'),
            'customer_address' => htmlspecialchars(implode(', ',$address_combined)),
            'contact_name' => ($contact_data['name'] != $contact_data['email']) ? htmlspecialchars($contact_data['name'].' '.$contact_data['last_name']) : '',
            'contact_first_name' => htmlspecialchars($contact_data['name']),
            'contact_last_name' => htmlspecialchars($contact_data['last_name']),
            'contact_email' => htmlspecialchars($contact_data['email']),
            'contact_phone' => htmlspecialchars($contact_data['phone']),
            'contact_mobile' => htmlspecialchars($contact_data['mobile']),

            //'project_name' => htmlspecialchars( isset($website_data['name']) && $website_data['name'] ? $website_data['name'] : _l('N/A')),
            //'job_name' => htmlspecialchars( isset($job_data['name']) && $job_data['name'] ? $job_data['name'] : _l('N/A')),
        );
        $data['invoice_date_range'] = '';
        if($invoice_data['date_renew']!='0000-00-00'){
            $data['invoice_date_range'] = _l('%s to %s',print_date($invoice_data['date_create']),print_date(strtotime("-1 day",strtotime($invoice_data['date_renew']))));
        }

        $data['invoice_notes'] = '';
        // grab any public notes
        $notes = module_note::get_notes(array('public'=>1,'owner_table'=>'invoice','owner_id'=>$invoice_id));
        if(count($notes)>1){
            $data['invoice_notes'] .= '<ul>';
            foreach($notes as $note){
                if($note['public']){
                    $data['invoice_notes'] .= '<li>';
                    $data['invoice_notes'] .= htmlspecialchars($note['note']);
                    $data['invoice_notes'] .= '</li>';
                }
            }
            $data['invoice_notes'] .= '</ul>';
        }else{
            $note = array_shift($notes);
            $data['invoice_notes'] .= htmlspecialchars($note['note']);
        }

        $job_names = $website_url = $project_names = array();
        foreach($invoice_data['job_ids'] as $job_id){
            $job_data = module_job::get_job($job_id);
            if($job_data && $job_data['job_id']==$job_id){
                $job_names[$job_data['job_id']] = $job_data['name'];
                if(module_config::c('job_invoice_show_date_range',1)){
                    // check if this job is a renewable job.
                    if($job_data['date_renew']!='0000-00-00'){
                        $data['invoice_date_range'] = _l('%s to %s',print_date($job_data['date_start']),print_date(strtotime("-1 day",strtotime($job_data['date_renew']))));
                    }
                }
                if($job_data['website_id']){
                    $website_data = module_website::get_website($job_data['website_id']);
                    if($website_data && $website_data['website_id']==$job_data['website_id']){
                        if(isset($website_data['url']) && $website_data['url']){
                            $website_url[$website_data['website_id']] = module_website::urlify($website_data['url']);
                            $website_data['name'] .= ' ('.module_website::urlify($website_data['url']).')';
                        }
                        $project_names[$website_data['website_id']] = $website_data['name'];
                    }
                }
            }
        }
        $data['project_name'] = forum_text(count($project_names) ? implode(', ',$project_names) : _l('N/A'));
        $data['website_name'] = $data['project_name'];
        $data['website_url'] = forum_text(count($website_url) ? implode(', ',$website_url) : _l('N/A'));
        $data['job_name'] = forum_text($job_names ? implode(', ',$job_names) : _l('N/A'));

        foreach($customer_data['customer_address'] as $key=>$val){
            $data['address_'.$key] = $val;
        }


        if(class_exists('module_group',false)){
            // get the customer groups
            $g = array();
            if((int)$invoice_data['customer_id']>0){
                foreach(module_group::get_groups_search(array(
                    'owner_table' => 'customer',
                    'owner_id' => $invoice_data['customer_id'],
                )) as $group){
                    $g[] = $group['name'];
                }
            }
            $data['customer_group'] = implode(', ',$g);
            // get the job groups
            $wg = array();
            $g = array();
            foreach($invoice_data['job_ids'] as $group_job_id){
                $group_job_id = (int)trim($group_job_id);
                if($group_job_id>0){
                    $job_data = module_job::get_job($group_job_id);
                    foreach(module_group::get_groups_search(array(
                        'owner_table' => 'job',
                        'owner_id' => $group_job_id,
                    )) as $group){
                        $g[$group['group_id']] = $group['name'];
                    }
                    // get the website groups
                    foreach(module_group::get_groups_search(array(
                        'owner_table' => 'website',
                        'owner_id' => $job_data['website_id'],
                    )) as $group){
                        $wg[$group['group_id']] = $group['name'];
                    }
                }
            }
            $data['job_group'] = implode(', ',$g);
            $data['website_group'] = implode(', ',$wg);
        }

        // addition. find all extra keys for this invoice and add them in.
        // we also have to find any EMPTY extra fields, and add those in as well.
        $all_extra_fields = module_extra::get_defaults('invoice');
        foreach($all_extra_fields as $e){
            $data[$e['key']] = _l('N/A');
        }
        // and find the ones with values:
        $extras = module_extra::get_extras(array('owner_table'=>'invoice','owner_id'=>$invoice_id));
        foreach($extras as $e){
            $data[$e['extra_key']] = $e['extra'];
        }
        // also do this for customer fields
        if($invoice_data['customer_id']){
            $all_extra_fields = module_extra::get_defaults('customer');
            foreach($all_extra_fields as $e){
                $data[$e['key']] = _l('N/A');
            }
            $extras = module_extra::get_extras(array('owner_table'=>'customer','owner_id'=>$invoice_data['customer_id']));
            foreach($extras as $e){
                $data[$e['extra_key']] = $e['extra'];
            }
        }


        return $data;
    }

	
	public function process(){
		$errors=array();
        if($_REQUEST['_process'] == 'make_payment'){
            $this->handle_payment();
        }else if(isset($_REQUEST['butt_del']) && $_REQUEST['butt_del'] && $_REQUEST['invoice_id']){
            $data = self::get_invoice($_REQUEST['invoice_id']);
            if(module_form::confirm_delete('invoice_id',"Really delete invoice: ".$data['name'],self::link_open($_REQUEST['invoice_id']))){
                $invoice_data = self::get_invoice($_REQUEST['invoice_id'],true);
                $this->delete_invoice($_REQUEST['invoice_id']);
                set_message("Invoice deleted successfully");
                if(isset($invoice_data['job_ids']) && $invoice_data['job_ids']){
                    redirect_browser(module_job::link_open(current($invoice_data['job_ids'])));
                }else{
                    redirect_browser(self::link_open(false));
                }
            }
		}else if("assign_credit_to_customer" == $_REQUEST['_process']){
            $invoice_id = (int)$_REQUEST['invoice_id'];
            if($invoice_id>0){
                $invoice_data = $this->get_invoice($invoice_id);
                $credit = $invoice_data['total_amount_credit'];
                if($credit > 0){
                    if($invoice_data['customer_id']){
                        // assign to customer.
                        module_customer::add_credit($invoice_data['customer_id'],$credit);
                        // assign this as a negative payment, and also give it to the customer account.
                        $this->add_history($invoice_id,'Added '.dollar($credit).' credit to customers account from this invoice overpayment');
                        update_insert('invoice_payment_id','new','invoice_payment',array(
                                                              'invoice_id'=>$invoice_id,
                                                              'amount' => -$credit,
                                                              'method' => 'Assigning Credit',
                                                              'date_paid' => date('Y-m-d'),
                                               ));
                    }
                }
                redirect_browser($this->link_open($invoice_id));
            }
		}else if("save_invoice" == $_REQUEST['_process']){

            $invoice_id = isset($_REQUEST['invoice_id']) ? (int)$_REQUEST['invoice_id'] : false;
            // check the user has permissions to edit this page.
            if($invoice_id>0){
                $invoice = $this->get_invoice($invoice_id);
                if(!module_security::can_access_data('invoice',$invoice,$invoice_id)){
                    echo 'Data access denied. Sorry.';
                    exit;
                }

            }

            if($this->can_i('edit','Invoices')){
                $data = $_POST;

                if(isset($data['customer_id']) && $data['customer_id'] && (!isset($data['user_id']) || !$data['user_id'])){
                    // find the primary contact for this invoice and set that there?
                    $customer_data = module_customer::get_customer($data['customer_id']);
                    if($customer_data && $customer_data['customer_id'] == $data['customer_id']){
                        if($customer_data['primary_user_id']){
                            $data['user_id'] = $customer_data['primary_user_id'];
                        }else{
                            $customer_contacts = module_user::get_contacts(array('customer_id'=>$data['customer_id']));
                            foreach($customer_contacts as $contact){
                                // todo - search roles or something to find the accountant.
                                $data['user_id'] = $contact['user_id'];
                                break;
                            }
                        }
                    }

                }


                // check for credit assessment.
                if(isset($_POST['apply_credit_from_customer']) && $_POST['apply_credit_from_customer'] == 'do'){
                    $invoice_data = $this->get_invoice($invoice_id);
                    $customer_data = module_customer::get_customer($invoice_data['customer_id']);
                    if($customer_data['credit'] > 0){
                        $apply_credit = min($invoice_data['total_amount_due'],$customer_data['credit']);
                        //$invoice_data['discount_amount'] += $customer_data['credit'];
                        //$this->save_invoice($invoice_id,array('discount_amount'=>$invoice_data['discount_amount'],'discount_description'=>_l('Credit:')));
                        update_insert('invoice_payment_id',false,'invoice_payment',array(
                            'invoice_id' => $invoice_id,
                            'payment_type'=>_INVOICE_PAYMENT_TYPE_CREDIT,
                            'method' => 'Credit',
                            'amount' => $apply_credit,
                            'currency_id' => $invoice_data['currency_id'],
                            'other_id' => $invoice_data['customer_id'],
                            'date_paid' => date('Y-m-d'),
                        ));
                        $this->add_history($invoice_id,_l('Applying %s customer credit to this invoice.',dollar($apply_credit)));
                        module_customer::remove_credit($customer_data['customer_id'],$apply_credit);
                    }
                }

                $invoice_id = $this->save_invoice($invoice_id,$data);

                if(isset($_REQUEST['allowed_payment_method']) && is_array($_REQUEST['allowed_payment_method'])){
                    // todo - ability to disable ALL payment methods. - array wont be set if none are ticked
                    $payment_methods = handle_hook('get_payment_methods');
                    foreach($payment_methods as &$payment_method){
                        if($payment_method->is_enabled()){
                            if(isset($_REQUEST['allowed_payment_method'][$payment_method->module_name])){
                                $payment_method->set_allowed_for_invoice($invoice_id,1);
                            }else{
                                $payment_method->set_allowed_for_invoice($invoice_id,0);
                            }
                        }
                    }
                }

                // check if we are generating any renewals
                if(isset($_REQUEST['generate_renewal']) && $_REQUEST['generate_renewal'] > 0){
                    $invoice = $this->get_invoice($invoice_id);
                    if(strtotime($invoice['date_renew']) <= strtotime('+'.module_config::c('alert_days_in_future',5).' days')){
                        // /we are allowed to renew.
                        unset($invoice['invoice_id']);
                        // work out the difference in start date and end date and add that new renewl date to the new order.
                        $time_diff = strtotime($invoice['date_renew']) - strtotime($invoice['date_create']);
                        if($time_diff > 0){
                            // our renewal date is something in the future.
                            if(!$invoice['date_create'] || $invoice['date_create'] == '0000-00-00'){
                                set_message('Please set a invoice create date before renewing');
                                redirect_browser($this->link_open($invoice_id));
                            }
                            // work out the next renewal date.
                            $new_renewal_date = date('Y-m-d',strtotime($invoice['date_renew'])+$time_diff);

                            $invoice['name'] = self::new_invoice_number($invoice['customer_id']);
                            $invoice['date_create'] = $invoice['date_renew'];
                            $invoice['date_renew'] = $new_renewal_date;
                            $invoice['date_sent'] = false;
                            $invoice['date_paid'] = false;
                            $invoice['discount_amount'] = 0;
                            $invoice['discount_description'] = _l('Discount:');
                            $invoice['discount_type'] = module_config::c('invoice_discount_type',1); // 1 = After Tax
                            $invoice['date_due'] = date('Y-m-d',strtotime('+'.module_config::c('invoice_due_days',30).' days',strtotime($invoice['date_create'])));
                            $invoice['status'] = module_config::s('invoice_status_default','New');
                            // todo: copy the "more" listings over to the new invoice
                            // todo: copy any notes across to the new listing.

                            // hack to copy the 'extra' fields across to the new invoice.
                            // save_invoice() does the extra handling, and if we don't do this
                            // then it will move the extra fields from the original invoice to this new invoice.
                            $owner_table = 'invoice';
                            if(isset($_REQUEST['extra_'.$owner_table.'_field']) && is_array($_REQUEST['extra_'.$owner_table.'_field'])){
                                $x=1;
                                foreach($_REQUEST['extra_'.$owner_table.'_field'] as $extra_id => $extra_data){
                                    $_REQUEST['extra_'.$owner_table.'_field']['new'.$x] = $extra_data;
                                    unset($_REQUEST['extra_'.$owner_table.'_field'][$extra_id]);
                                }
                            }
                            $new_invoice_id = $this->save_invoice('new',$invoice);
                            if($new_invoice_id){
                                // now we create the tasks
                                $tasks = $this->get_invoice_items($invoice_id);
                                foreach($tasks as $task){
                                    unset($task['invoice_item_id']);
                                    if($task['custom_description'])$task['description']=$task['custom_description'];
                                    if($task['custom_long_description'])$task['long_description']=$task['custom_long_description'];
                                    $task['invoice_id'] = $new_invoice_id;
                                    $task['date_done'] = $invoice['date_create'];
                                    update_insert('invoice_item_id','new','invoice_item',$task);
                                }
                                // link this up with the old one.
                                update_insert('invoice_id',$invoice_id,'invoice',array('renew_invoice_id'=>$new_invoice_id));
                            }
                            set_message("Invoice renewed successfully");
                            redirect_browser($this->link_open($new_invoice_id));
                        }
                    }
                }
            }




            if(isset($_REQUEST['butt_makepayment']) && $_REQUEST['butt_makepayment'] == 'yes'){
                self::handle_payment();
            }else if(isset($_REQUEST['butt_print']) && $_REQUEST['butt_print']){
                $_REQUEST['_redirect'] = self::link_generate($invoice_id,array('arguments'=>array('print'=>1)));;
            }else if(isset($_REQUEST['butt_merge']) && $_REQUEST['butt_merge'] && isset($_REQUEST['merge_invoice']) && is_array($_REQUEST['merge_invoice'])){
                $merge_invoice_ids = self::check_invoice_merge($invoice_id);
                foreach($merge_invoice_ids as $merge_invoice){
                    if(isset($_REQUEST['merge_invoice'][$merge_invoice['invoice_id']])){
                        // copy all the tasks from that invoice over to this invoice.
                        $sql = "UPDATE `"._DB_PREFIX."invoice_item` SET invoice_id = ".(int)$invoice_id." WHERE invoice_id = ".(int)$merge_invoice['invoice_id']." ";
                        query($sql);
                        $this->delete_invoice($merge_invoice['invoice_id']);
                    }
                }
                $_REQUEST['_redirect'] = $this->link_open($invoice_id);
                set_message('Invoices merged successfully');
            }else if(isset($_REQUEST['butt_email']) && $_REQUEST['butt_email']){
                $_REQUEST['_redirect'] = self::link_generate($invoice_id,array('arguments'=>array('email'=>1)));;
            }else{
                $_REQUEST['_redirect'] = $this->link_open($invoice_id);
                set_message("Invoice saved successfully");
            }
		}
		if(!count($errors)){
			redirect_browser($_REQUEST['_redirect']);
			exit;
		}
		print_error($errors,true);
	}


	public static function get_invoices($search=array()){
		// limit based on customer id
		/*if(!isset($_REQUEST['customer_id']) || !(int)$_REQUEST['customer_id']){
			return array();
		}*/
		// build up a custom search sql query based on the provided search fields
		$sql = "SELECT u.*,u.invoice_id AS id ";
        $sql .= ", u.name AS name ";
        $sql .= ", c.customer_name ";
        $from = " FROM `"._DB_PREFIX."invoice` u ";
        $from .= " LEFT JOIN `"._DB_PREFIX."customer` c USING (customer_id)";
        $from .= " LEFT JOIN `"._DB_PREFIX."invoice_item` ii ON u.invoice_id = ii.invoice_id ";

        $from .= " LEFT JOIN `"._DB_PREFIX."task` t ON ii.task_id = t.task_id";
        /*if(isset($search['job_id']) && (int)$search['job_id']>0){
            $from .= " AND t.`job_id` = ".(int)$search['job_id'];
        }*/

		$where = " WHERE 1 ";
		if(isset($search['generic']) && $search['generic']){
			$str = mysql_real_escape_string($search['generic']);
			$where .= " AND ( ";
			$where .= " u.name LIKE '%$str%' ";
			//$where .= "OR  u.url LIKE '%$str%'  ";
			$where .= ' ) ';
		}
        foreach(array('customer_id','status','name','date_paid','date_due','renew_invoice_id') as $key){
            if(isset($search[$key]) && $search[$key] !== ''&& $search[$key] !== false){
                $str = mysql_real_escape_string($search[$key]);
                $where .= " AND u.`$key` = '$str'";
            }
        }
        if(isset($search['date_from']) && $search['date_from']){
            $str = mysql_real_escape_string(input_date($search['date_from']));
            $where .= " AND ( ";
            $where .= " u.date_create >= '$str' ";
            $where .= ' ) ';
        }
        if(isset($search['date_to']) && $search['date_to']){
            $str = mysql_real_escape_string(input_date($search['date_to']));
            $where .= " AND ( ";
            $where .= " u.date_create <= '$str' ";
            $where .= ' ) ';
        }
        if(isset($search['job_id']) && (int)$search['job_id']>0){
            $where .= " AND ( t.`job_id` = ".(int)$search['job_id'] .' OR ';
            $where .= "  u.deposit_job_id = ".(int)$search['job_id'];
            $where .= ' ) ';
        }
        if(isset($search['deposit_job_id']) && (int)$search['deposit_job_id']>0){
            $where .= " AND ( u.deposit_job_id = ".(int)$search['deposit_job_id'];
            $where .= ' ) ';
        }
        if(isset($search['customer_group_id']) && (int)$search['customer_group_id']>0){
			$from .= " LEFT JOIN `"._DB_PREFIX."group_member` gm ON (c.customer_id = gm.owner_id)";
			$where .= " AND (gm.group_id = '".(int)$search['customer_group_id']."' AND gm.owner_table = 'customer')";
        }

        // permissions from job module.
        switch(module_job::get_job_access_permissions()){
            case _JOB_ACCESS_ALL:

                break;
            case _JOB_ACCESS_ASSIGNED:
                // only assigned jobs!
                //$from .= " LEFT JOIN `"._DB_PREFIX."task` t ON u.job_id = t.job_id ";
                //u.user_id = ".(int)module_security::get_loggedin_id()." OR
                $where .= " AND (t.user_id = ".(int)module_security::get_loggedin_id().")";
                break;
            case _JOB_ACCESS_CUSTOMER:
                break;
        }

        // permissions from customer module.
        // tie in with customer permissions to only get jobs from customers we can access.
        switch(module_customer::get_customer_data_access()){
            case _CUSTOMER_ACCESS_ALL:
                // all customers! so this means all jobs!
                break;
            case _CUSTOMER_ACCESS_CONTACTS:
                // we only want customers that are directly linked with the currently logged in user contact.
                $valid_customer_ids = module_security::get_customer_restrictions();
                if(count($valid_customer_ids)){
                    $where .= " AND ( ";
                    foreach($valid_customer_ids as $valid_customer_id){
                        $where .= " u.customer_id = '".(int)$valid_customer_id."' OR ";
                    }
                    $where = rtrim($where,'OR ');
                    $where .= " )";
                }

                /*if(isset($_SESSION['_restrict_customer_id']) && (int)$_SESSION['_restrict_customer_id']> 0){
                    // this session variable is set upon login, it holds their customer id.
                    // todo - share a user account between multiple customers!
                    //$where .= " AND c.customer_id IN (SELECT customer_id FROM )";
                    $where .= " AND u.customer_id = '".(int)$_SESSION['_restrict_customer_id']."'";
                }*/
                break;
            case _CUSTOMER_ACCESS_TASKS:
                // only customers who have a job that I have a task under.
                // this is different to "assigned jobs" Above
                // this will return all jobs for a customer even if we're only assigned a single job for that customer
                // tricky!
                // copied from customer.php
                $where .= " AND u.customer_id IN ";
                $where .= " ( SELECT cc.customer_id FROM `"._DB_PREFIX."customer` cc ";
                $where .= " LEFT JOIN `"._DB_PREFIX."job` jj ON cc.customer_id = jj.customer_id ";
                $where .= " LEFT JOIN `"._DB_PREFIX."task` tt ON jj.job_id = tt.job_id ";
                $where .= " WHERE (jj.user_id = ".(int)module_security::get_loggedin_id()." OR tt.user_id = ".(int)module_security::get_loggedin_id().")";
                $where .= " )";

                break;
        }


        $group_order = ' GROUP BY u.invoice_id ORDER BY u.date_create DESC'; // stop when multiple company sites have same region
		$sql = $sql . $from . $where . $group_order;
		$result = qa($sql);
		//module_security::filter_data_set("invoice",$result);
		return $result;
//		return get_multiple("invoice",$search,"invoice_id","fuzzy","name");

	}
    public static function get_invoice_items($invoice_id){
        $invoice_id = (int)$invoice_id;
        if(!$invoice_id && isset($_REQUEST['job_id']) && (int)$_REQUEST['job_id'] > 0){

            // hack for half completed invoices
            if(isset($_REQUEST['amount_due']) && $_REQUEST['amount_due'] > 0){

                $amount = (float)$_REQUEST['amount_due'];
                

                $new_tasks = array(
                    'new0' => array(
                        'description' => isset($_REQUEST['description'])?$_REQUEST['description']:_l('Invoice Item'),
                        'custom_description' => '',
                        'long_description' => '',
                        'custom_long_description' => '',
                        'amount' => $amount,
                        'hours' => 0,
                        'taxable' => false,
                        'task_id' => 0,
                    ),
                );
                

            }else{

                $job_id = (int)$_REQUEST['job_id'];
                if($job_id>0){
                    // we return the items from the job rather than the items from the invoice.
                    // for new invoice creation.
                    $tasks = module_job::get_invoicable_tasks($job_id);
                    $new_tasks = array();
                    $x=0;
                    $job = module_job::get_job($job_id,false);
                    foreach($tasks as $task){
                        if(!isset($task['custom_description']))$task['custom_description'] = '';
                        if(!isset($task['custom_long_description']))$task['custom_long_description'] = '';
                        //$task['task_id'] = 'new'.$x;
                        $task['hourly_rate'] = $job['hourly_rate'];
                        $new_tasks['new'.$x] = $task;
                        $x++;
                    }
                }
            }
            return $new_tasks;
        }
        if($invoice_id){
            $sql = "SELECT ii.invoice_item_id AS id, ii.*, t.job_id, t.description AS description, ii.description as custom_description, ii.long_description as custom_long_description, t.task_order, ii.task_order AS custom_task_order "; // , j.hourly_rate
            $sql .= " FROM `"._DB_PREFIX."invoice_item` ii ";
            $sql .= " LEFT JOIN `"._DB_PREFIX."task` t ON ii.task_id = t.task_id ";
            $sql .= " LEFT JOIN `"._DB_PREFIX."job` j ON t.job_id = j.job_id ";
            $sql .= " WHERE ii.invoice_id = $invoice_id";
            $sql .= " ORDER BY t.task_order ";
            return qa($sql);
        }
		//return get_multiple("invoice_item",array('invoice_id'=>$invoice_id),"invoice_item_id","exact","invoice_item_id");
        return array();
	}
    public static function get_invoice_payments($invoice_id){
        $invoice_id = (int)$invoice_id;
		return get_multiple("invoice_payment",array('invoice_id'=>$invoice_id),"invoice_payment_id","exact","invoice_payment_id",true);
	}
    public static function get_invoice_payment($invoice_payment_id){
        $invoice_payment_id = (int)$invoice_payment_id;
		return get_single('invoice_payment','invoice_payment_id',$invoice_payment_id,true);
	}
    private static function new_invoice_number($customer_id){

        $invoice_number = '';

        if(function_exists('custom_invoice_number')){
            $invoice_number = custom_invoice_number();
        }

        $invoice_prefix = '';
        if($customer_id>0){
            $customer_data = module_customer::get_customer($customer_id);
            if($customer_data && isset($customer_data['default_invoice_prefix'])){
                $invoice_prefix = $customer_data['default_invoice_prefix'];
            }
        }

        if(!$invoice_number){

            if(module_config::c('invoice_name_match_job',0) && isset($_REQUEST['job_id']) && (int)$_REQUEST['job_id']>0){
                $job = module_job::get_job($_REQUEST['job_id']);
                // todo: confirm tis isn't a data leak risk oh well.
                $invoice_number = $invoice_prefix.$job['name'];
            }else if(module_config::c('invoice_incrementing',0)){
                $invoice_number = module_config::c('invoice_incrementing_next',1);
                // see if there is an invoice number matching this one.
                $this_invoice_number = $invoice_number;
                do{
                    $invoices = self::get_invoices(array('name'=>$this_invoice_number)); //'customer_id'=>$customer_id,
                    if(!count($invoices)){
                        $invoice_number = $this_invoice_number;
                    }else{
                        $this_invoice_number++;
                    }
                }while(count($invoices)); //90 is Z
                module_config::save_config('invoice_incrementing_next',$invoice_number);
                $invoice_number = $invoice_prefix.$invoice_number;
            }else{
                $invoice_number = $invoice_prefix . date('ymd');

                //$invoice_number = $invoice_prefix . date('ymd');
                // check if this invoice number exists for this customer
                // if it does exist we create a suffix a, b, c, d etc..
                // this isn't atomic - if two invoices are created for the same customer at the same time then
                // this probably wont work. but for this system it's fine.
                $this_invoice_number = $invoice_number;
                $suffix_ascii = 65; // 65 is A
                $suffix_ascii2 = 0; // 65 is A
                do{
                    if($suffix_ascii==91){
                        // we've exhausted all invoices for today.
                        $suffix_ascii=65; // reset to A
                        if(!$suffix_ascii2){
                            // first loop, start with A
                            $suffix_ascii2=65; // set 2nd suffix to A, work with this.
                        }else{
                            $suffix_ascii2++; // move from A to B
                        }

                    }
                    $invoices = self::get_invoices(array('name'=>$this_invoice_number)); //'customer_id'=>$customer_id,
                    if(!count($invoices)){
                        $invoice_number = $this_invoice_number;
                    }else{
                        $this_invoice_number = $invoice_number.($suffix_ascii2?chr($suffix_ascii2):'').chr($suffix_ascii);
                    }
                    $suffix_ascii++;
                }while(count($invoices) && $suffix_ascii <= 91 && $suffix_ascii2 <= 90); //90 is Z
            }
        }
        return $invoice_number;

    }
	public static function get_invoice($invoice_id,$basic=false){
        $invoice = array();
        if((int)$invoice_id>0){

            if($basic===2){ // used in links. just want the invoice name really.
                // todo - cache. meh
                return get_single('invoice','invoice_id',$invoice_id);
            }
            $sql = "SELECT i.*";
            $sql .= ", c.primary_user_id  "; // AS user_id // DONE - change this to the invoice table. drop down to select invoice contact. auto select based on contacts role?
            $sql .= ", c.customer_name AS customer_name ";
            $sql .= ", GROUP_CONCAT(DISTINCT j.`website_id` SEPARATOR ',') AS website_ids"; // the website id(s)
            $sql .= ", GROUP_CONCAT(DISTINCT j.`job_id` SEPARATOR ',') AS job_ids"; // the website id(s)
            $sql .= ", j.customer_id AS new_customer_id ";
            $sql .= " FROM `"._DB_PREFIX."invoice` i ";
            $sql .= " LEFT JOIN `"._DB_PREFIX."invoice_item` ii USING (invoice_id) ";
            $sql .= " LEFT JOIN `"._DB_PREFIX."task` t ON ii.task_id = t.task_id";
            $sql .= " LEFT JOIN `"._DB_PREFIX."job` j ON t.job_id = j.job_id";
            $sql .= " LEFT JOIN `"._DB_PREFIX."customer` c ON i.customer_id = c.customer_id ";
            //$sql .= " LEFT JOIN `"._DB_PREFIX."user` u ON c.primary_user_id = u.user_id ";
            $sql .= " WHERE i.invoice_id = ".(int)$invoice_id;
            $sql .= " GROUP BY i.invoice_id";
            $invoice = qa1($sql,false);
//            print_r($invoice);exit;
            if(!$invoice)return array();
            // set the job id of the first job just for kicks
            if(strlen(trim($invoice['job_ids']))>0){
                $invoice['job_ids'] = explode(',',$invoice['job_ids']);
            }else{
                $invoice['job_ids'] = array();
            }
            if(isset($invoice['deposit_job_id']) && (int)$invoice['deposit_job_id']>0){
                $invoice['job_ids'][] = $invoice['deposit_job_id'];
            }
            if(isset($invoice['website_ids'])){
                $invoice['website_ids'] = explode(',',$invoice['website_ids']);
            }else{
                $invoice['website_ids'] = array();
            }
            // incase teh customer id on this invoice changes:
            if(isset($invoice['new_customer_id']) && $invoice['new_customer_id'] > 0 && $invoice['new_customer_id'] != $invoice['customer_id']){
                $invoice['customer_id'] = $invoice['new_customer_id'];
                update_insert('invoice_id',$invoice_id,'invoice',array('customer_id'=>$invoice['new_customer_id']));
            }
            if($basic===true){
                return $invoice;
            }
        }
        // not sure why this code was here, commenting it out for now until we need it.
        /*if(isset($invoice['customer_id']) && isset($invoice['job_id']) && $invoice['customer_id'] <= 0 && $invoice['job_id'] > 0){
            $job_data = module_job::get_job($invoice['job_id'],false);
            $invoice['customer_id'] = $job_data['customer_id'];
        }*/
        if(!$invoice){
            $customer_id = (isset($_REQUEST['customer_id'])? $_REQUEST['customer_id'] : 0);
            $job_id = (isset($_REQUEST['job_id'])? $_REQUEST['job_id'] : 0);
            $currency_id = module_config::c('default_currency_id',1);
            if($customer_id > 0){
                // find a default website to use ?
            }else if($job_id > 0){
                // only a job, no customer. set the customer id.
                $job_data = module_job::get_job($job_id,false);
                $customer_id = $job_data['customer_id'];
                $currency_id = $job_data['currency_id'];
            }
            // work out an invoice number

            $invoice_number = self::new_invoice_number($customer_id);
            $invoice = array(
                'invoice_id' => 'new',
                'customer_id' => $customer_id,
                'job_id' => $job_id, // this is  needed as a once off for creating new invoices.
                'job_ids' => $job_id > 0 ? array($job_id) : array(),
                'currency_id' => $currency_id,
                'name' => $invoice_number,
                'cached_total' => 0,
                'discount_description' => _l('Discount:'),
                'discount_amount' => 0,
                'discount_type' => module_config::c('invoice_discount_type',1), // 1 = After Tax
                'date_create' => date('Y-m-d'),
                'date_sent' => '',
                'date_due' => date('Y-m-d',strtotime('+'.module_config::c('invoice_due_days',30).' days')),
                'date_paid' => '',
                'hourly_rate' => module_config::c('hourly_rate',60),
                'status'  => module_config::s('invoice_status_default','New'),
                'user_id' => '',
                'date_renew' => '',
                'renew_invoice_id' => '',
                'total_tax' => 0,
                'deposit_job_id' => 0,
                'date_cancel' => '0000-00-00',
            );
            $invoice['total_tax_rate'] = module_config::c('tax_percent',10);
            $invoice['total_tax_name'] = module_config::c('tax_name','TAX');

            $customer_data = false;
            if($customer_id>0){
                $customer_data = module_customer::get_customer($customer_id);
            }

            if($customer_data && isset($customer_data['default_tax']) && $customer_data['default_tax'] >= 0){
                $invoice['total_tax_rate'] = $customer_data['default_tax'];
                $invoice['total_tax_name'] = $customer_data['default_tax_name'];
            }

        }
        if($invoice){


            // drag some details from the related job
            if(!(int)$invoice_id){
                if(isset($invoice['job_ids']) && $invoice['job_ids']){
                    $first_job_id = current($invoice['job_ids']);
                }else if(isset($invoice['job_id']) && $invoice['job_id']){
                    $first_job_id = $invoice['job_id']; // abckwards compatibility
                }else{
                    $first_job_id = 0;
                }
                if($first_job_id>0){
                    $job_data = module_job::get_job($first_job_id,false);
                    $invoice['hourly_rate'] = $job_data['hourly_rate'];
                    $invoice['total_tax_rate'] = $job_data['total_tax_rate'];
                    $invoice['total_tax_name'] = $job_data['total_tax_name'];
                }
            }
            // work out total hours etc..
            //$invoice['total_hours'] = 0;
            //$invoice['total_hours_completed'] = 0;
            //$invoice['total_hours_overworked'] = 0;
            $invoice['total_tax'] = 0;
            $invoice['total_sub_amount'] = 0;
            $invoice['total_sub_amount_taxable'] = 0;
            $invoice_items = self::get_invoice_items((int)$invoice['invoice_id']);
            foreach($invoice_items as $invoice_item){
                if($invoice_item['amount'] != 0){
                    // we have a custom amount for this invoice_item
                    $invoice['total_sub_amount'] += $invoice_item['amount'];
                    if($invoice_item['taxable']){
                        $invoice['total_sub_amount_taxable'] += $invoice_item['amount'];
                        if(module_config::c('tax_calculate_mode',0)==1){
                            // tax calculated along the way.
                            $invoice['total_tax'] += round(($invoice_item['amount'] * ($invoice['total_tax_rate'] / 100)),module_config::c('currency_decimal_places',2));
                        }
                    }
                }
                if($invoice_item['hours'] > 0){
                    /*$invoice['total_hours'] += $invoice_item['hours'];
                    $invoice['total_hours_completed'] += min($invoice_item['hours'],$invoice_item['completed']);
                    if($invoice_item['completed'] > $invoice_item['hours']){
                        $invoice['total_hours_overworked'] = $invoice_item['completed'] - $invoice_item['hours'];
                    }*/
                    if($invoice_item['amount'] == 0){
                        $task_hourly = isset($invoice_item['hourly_rate']) && $invoice_item['hourly_rate']!=0 ? $invoice_item['hourly_rate'] : $invoice['hourly_rate'];
                        $item_amount = $invoice_item['hours'] * $task_hourly;
                        $invoice['total_sub_amount'] += $item_amount;
                        if($invoice_item['taxable']){
                            $invoice['total_sub_amount_taxable'] += $item_amount;
                            if(module_config::c('tax_calculate_mode',0)==1){
                                // tax calculated along the way.
                                $invoice['total_tax'] += round(($item_amount * ($invoice['total_tax_rate'] / 100)),module_config::c('currency_decimal_places',2));
                            }
                        }
                    }
                }
            }

            $invoice['final_modification'] = 0; // hack for discount modes

            // add any discounts.
            if($invoice['discount_amount'] != 0){
                if($invoice['discount_type']==1){ // after tax discount
                    $invoice['final_modification'] = -$invoice['discount_amount'];
                    if(module_config::c('tax_calculate_mode',0)==1){
                        // tax calculated along the way.
                        //$invoice['total_tax'] -= round(($invoice['discount_amount'] * ($invoice['total_tax_rate'] / 100)),module_config::c('currency_decimal_places',2));
                    }
                }else{
                    $invoice['final_modification'] = -$invoice['discount_amount'];
                    //$invoice['total_sub_amount']-=$invoice['discount_amount'];
                    // before tax discount.
                    $invoice['total_sub_amount_taxable']-=$invoice['discount_amount'];
                    if(module_config::c('tax_calculate_mode',0)==1){
                        // tax calculated along the way.
                        $invoice['total_tax'] -= round(($invoice['discount_amount'] * ($invoice['total_tax_rate'] / 100)),module_config::c('currency_decimal_places',2));
                    }
                }
            }

            //$invoice['total_hours_remain'] = $invoice['total_hours'] - $invoice['total_hours_completed'];
            //$invoice['total_percent_complete'] = $invoice['total_hours'] > 0 ? round($invoice['total_hours_remain'] / $invoice['total_hours'],2) : 0;
            //if(isset($invoice['total_tax_rate'])){
            if(module_config::c('tax_calculate_mode',0)==1 && isset($invoice['total_tax']) && $invoice['total_tax'] > 0){
                // tax calculated along the way. don't calclate now.
                //$invoice['total_tax'] = ($invoice['total_sub_amount_taxable'] * ($invoice['total_tax_rate'] / 100));
                //$invoice['total_amount'] = round($invoice['total_sub_amount'] + $invoice['total_tax'],2);
            }else if(module_config::c('tax_calculate_mode',0)==0){
                $invoice['total_tax'] = round(($invoice['total_sub_amount_taxable'] * ($invoice['total_tax_rate'] / 100)),module_config::c('currency_decimal_places',2));
                //$invoice['total_amount'] = $invoice['total_sub_amount'];
            }else{
                $invoice['total_tax'] = 0;
                //$invoice['total_amount'] = $invoice['total_sub_amount'];
            }
            $invoice['total_amount'] = round($invoice['total_sub_amount'] + $invoice['total_tax'] + $invoice['final_modification'],module_config::c('currency_decimal_places',2));

            if($basic===1){
                // so we don't go clearning cache and working out how much has been paid.
                // used in the finance module while displaying dashboard summary.
                return $invoice;
            }

            // find the user id if none exists.
            if($invoice['customer_id'] && !$invoice['user_id']){
                $customer_data = module_customer::get_customer($invoice['customer_id']);
                if($customer_data && $customer_data['customer_id'] == $invoice['customer_id']){
                    if($customer_data['primary_user_id']){
                        $invoice['user_id'] = $customer_data['primary_user_id'];
                    }else{
                        $customer_contacts = module_user::get_contacts(array('customer_id'=>$invoice['customer_id']));
                        foreach($customer_contacts as $contact){
                            // todo - search roles or something to find the accountant.
                            $invoice['user_id'] = $contact['user_id'];
                            break;
                        }
                    }
                }
            }

            $paid = 0;
            //module_cache::clear_cache(); // no longer clearnig cache, it does it in get_invoice_payments.
            foreach(self::get_invoice_payments($invoice_id) as $payment){
                if($payment['date_paid'] && $payment['date_paid']!='0000-00-00'){
                    $paid += $payment['amount'];
                }
            }
            // dont go negative on payments:
            $invoice['total_amount_paid'] = max(0,min($invoice['total_amount'],$paid));
            $invoice['total_amount_credit'] = 0;
            if($invoice['total_amount'] > 0 && $paid > $invoice['total_amount']){
                // raise a credit against this customer for the difference.
                $invoice['total_amount_credit'] = round($paid - $invoice['total_amount'],2);
                //echo $invoice['total_amount_overpaid'];exit;
            }
            if($invoice['total_amount'] != $invoice['cached_total']){
                if((int)$invoice_id>0){
                    update_insert('invoice_id',$invoice_id,'invoice',array('cached_total'=>$invoice['total_amount']));
                }
                $invoice['cached_total'] = $invoice['total_amount'];
            }
            $invoice['total_amount_due'] = round($invoice['total_amount'] - $invoice['total_amount_paid'],module_config::c('currency_decimal_places',2));

            // a special addition for deposit invoices.
            if(isset($invoice['deposit_job_id']) && $invoice['deposit_job_id']){
                // we find out how much deposit has actually been paid
                // and how much is remaining that hasn't been allocated to any other invoices
                $invoice['deposit_remaining'] = 0;
                if($invoice['total_amount_paid']>0){
                    $invoice['deposit_remaining'] = $invoice['total_amount_paid'];
                    $payments = get_multiple('invoice_payment',array(
                        'payment_type' => _INVOICE_PAYMENT_TYPE_DEPOSIT,
                        'other_id' => $invoice['invoice_id'],
                    ));
                    foreach($payments as $payment){
                        $invoice['deposit_remaining'] = $invoice['deposit_remaining'] - $payment['amount'];
                    }
                }
            }
        }
		return $invoice;
	}
	public static function save_invoice($invoice_id,$data){
        if(!(int)$invoice_id && isset($data['job_id']) && $data['job_id']){
            $linkedjob = module_job::get_job($data['job_id']);
            $data['currency_id'] = $linkedjob['currency_id'];
            $data['customer_id'] = $linkedjob['customer_id'];
        }
		$invoice_id = update_insert("invoice_id",$invoice_id,"invoice",$data);
        if($invoice_id){
            $invoice_data = self::get_invoice($invoice_id);
            // check for new invoice_items or changed invoice_items.
            $invoice_items = self::get_invoice_items($invoice_id);
            if(isset($data['invoice_invoice_item']) && is_array($data['invoice_invoice_item'])){
                foreach($data['invoice_invoice_item'] as $invoice_item_id => $invoice_item_data){
                    $invoice_item_id = (int)$invoice_item_id;
                    if(!is_array($invoice_item_data))continue;
                    if($invoice_item_id > 0 && !isset($invoice_items[$invoice_item_id]))continue; // wrong invoice_item save - will never happen.
                    if(!isset($invoice_item_data['description']) || $invoice_item_data['description'] == ''){
                        if($invoice_item_id>0){
                            // remove invoice_item.
                            $sql = "DELETE FROM `"._DB_PREFIX."invoice_item` WHERE invoice_item_id = '$invoice_item_id' AND invoice_id = $invoice_id LIMIT 1";
                            query($sql);
                        }
                        continue;
                    }
                    // add / save this invoice_item.
                    $invoice_item_data['invoice_id'] = $invoice_id;
                    // remove the amount of it equals the hourly rate.
                    if(isset($invoice_item_data['amount']) && isset($invoice_item_data['hours']) && $invoice_item_data['amount'] > 0 && $invoice_item_data['hours'] > 0){
                        if($invoice_item_data['amount'] - ($invoice_item_data['hours'] * $data['hourly_rate']) == 0){
                            unset($invoice_item_data['amount']);
                        }
                    }
                    // check if we haven't unticked a non-hourly invoice_item
                    if(isset($invoice_item_data['completed_t']) && $invoice_item_data['completed_t'] && !isset($invoice_item_data['completed'])){
                        $invoice_item_data['completed'] = 0;
                    }
                    if(isset($invoice_item_data['taxable_t']) && $invoice_item_data['taxable_t'] && !isset($invoice_item_data['taxable'])){
                        $invoice_item_data['taxable'] = 0;
                    }
                    update_insert('invoice_item_id',$invoice_item_id,'invoice_item',$invoice_item_data); 
                }
            }
            $last_payment_date = date('Y-m-d');
            if(isset($data['invoice_invoice_payment']) && is_array($data['invoice_invoice_payment'])){
                foreach($data['invoice_invoice_payment'] as $invoice_payment_id => $invoice_payment_data){
                    $invoice_payment_id = (int)$invoice_payment_id;
                    if(!is_array($invoice_payment_data))continue;
                    // check this invoice payment actually matches this invoice.
                    $invoice_payment_data_existing=false;
                    if($invoice_payment_id>0){
                        $invoice_payment_data_existing = get_single('invoice_payment',array('invoice_payment_id','invoice_id'),array($invoice_payment_id,$invoice_id));
                        if(!$invoice_payment_data_existing || $invoice_payment_data_existing['invoice_payment_id'] != $invoice_payment_id){
                            $invoice_payment_id = 0;
                            $invoice_payment_data_existing = false;
                        }
                    }
                    if(!isset($invoice_payment_data['amount']) || $invoice_payment_data['amount'] == '' || $invoice_payment_data['amount'] == 0){ // || $invoice_payment_data['amount'] <= 0
                        if($invoice_payment_id>0){
                            // remove invoice_payment.
                            $sql = "DELETE FROM `"._DB_PREFIX."invoice_payment` WHERE invoice_payment_id = '$invoice_payment_id' AND invoice_id = $invoice_id LIMIT 1";
                            query($sql);
                            // delete any existing transactions from the system as well.
                            // todo: is this right???
                            $sql = "DELETE FROM `"._DB_PREFIX."finance` WHERE invoice_payment_id = '$invoice_payment_id' LIMIT 1";
                            query($sql);

                        }
                        continue;
                    }
                    if(!$invoice_payment_id && (!isset($_REQUEST['add_payment']) || $_REQUEST['add_payment'] != 'go')){
                        continue; // not saving a new one.
                    }
                    // add / save this invoice_payment.
                    $invoice_payment_data['invoice_id'] = $invoice_id;
                   // $invoice_payment_data['currency_id'] = $invoice_data['currency_id'];
                    $last_payment_date = input_date($invoice_payment_data['date_paid']);
                    if($invoice_payment_data_existing && isset($invoice_payment_data['custom_notes'])){
                        $details = @unserialize($invoice_payment_data['data']);
                        $details['custom_notes'] = $invoice_payment_data['custom_notes'];
                        $invoice_payment_data['data'] = serialize($details);
                    }
                    update_insert('invoice_payment_id',$invoice_payment_id,'invoice_payment',$invoice_payment_data);
                }
            }
            // check if the invoice has been paid
            module_cache::clear_cache(); // this helps fix the bug where part payments are not caulcated a correct paid date.
            $invoice_data = self::get_invoice($invoice_id);
            if(((!$invoice_data['date_paid']||$invoice_data['date_paid']=='0000-00-00')) && $invoice_data['total_amount_due'] <= 0 && ($invoice_data['total_amount_paid'] > 0||$invoice_data['discount_amount']>0)){
                // find the date of the last payment history.

                // if the sent date is null also update that.
                $date_sent = $invoice_data['date_sent'];
                if(!$date_sent||$date_sent=='0000-00-00'){
                    $date_sent = $last_payment_date;
                }
                update_insert("invoice_id",$invoice_id,"invoice",array(
                                              'date_paid' => $last_payment_date,
                                              'date_sent' => $date_sent,
                                              'status' => _l('Paid'),
                 ));
                // hook for our ticketing plugin to mark a priority support ticket as paid.
                // or anything else down the track.
                handle_hook('invoice_paid',$invoice_id);
            }
            if($invoice_data['total_amount_due']>0){
                // update the status to unpaid.
                update_insert("invoice_id",$invoice_id,"invoice",array(
                                              'date_paid' => '',
                                              'status' => $invoice_data['status'] == _l('Paid') ? module_config::s('invoice_status_default','New') : $invoice_data['status'],
                 ));
            }
            module_extra::save_extras('invoice','invoice_id',$invoice_id);
        }
		return $invoice_id;
	}

	public static function delete_invoice($invoice_id){
		$invoice_id=(int)$invoice_id;
		$sql = "DELETE FROM "._DB_PREFIX."invoice WHERE invoice_id = '".$invoice_id."' LIMIT 1";
		$res = query($sql);
		$sql = "DELETE FROM "._DB_PREFIX."invoice_item WHERE invoice_id = '".$invoice_id."'";
		$res = query($sql);
		$sql = "DELETE FROM "._DB_PREFIX."invoice_payment WHERE invoice_id = '".$invoice_id."'";
		$res = query($sql);
        $sql = "UPDATE "._DB_PREFIX."invoice SET renew_invoice_id = 0 WHERE renew_invoice_id = '".$invoice_id."'";
        $res = query($sql);
		module_note::note_delete("invoice",$invoice_id);
        module_extra::delete_extras('invoice','invoice_id',$invoice_id);
        hook_handle_callback('invoice_deleted',$invoice_id);
	}
    public function login_link($invoice_id){
        return module_security::generate_auto_login_link($invoice_id);
    }

    public static function get_statuses(){
        $sql = "SELECT `status` FROM `"._DB_PREFIX."invoice` GROUP BY `status` ORDER BY `status`";
        $statuses = array();
        foreach(qa($sql) as $r){
            $statuses[$r['status']] = $r['status'];
        }
        return $statuses;
    }
    public static function get_payment_methods(){
        $sql = "SELECT `method` FROM `"._DB_PREFIX."invoice_payment` GROUP BY `method` ORDER BY `method`";
        $statuses = array();
        foreach(qa($sql) as $r){
            $statuses[$r['method']] = $r['method'];
        }
        return $statuses;
    }
    public static function get_types(){
        $sql = "SELECT `type` FROM `"._DB_PREFIX."invoice` GROUP BY `type` ORDER BY `type`";
        $statuses = array();
        foreach(qa($sql) as $r){
            $statuses[$r['type']] = $r['type'];
        }
        return $statuses;
    }

    public function handle_payment(){
        // handle a payment request via post data from
        if(isset($_REQUEST['payment_method']) && isset($_REQUEST['invoice_id']) && isset($_REQUEST['payment_amount'])){
            $invoice_id = (int)$_REQUEST['invoice_id'];
            $payment_method = $_REQUEST['payment_method'];
            $payment_amount = (float)$_REQUEST['payment_amount'];
            $invoice_data = $this->get_invoice($invoice_id);

             //&& module_security::can_access_data('invoice',$invoice_data,$invoice_id)
            if($invoice_id && $payment_method && $payment_amount > 0 && $invoice_data){
                // pass this off to the payment module for handling.
                global $plugins;
                if(isset($plugins[$payment_method])){

                    // delete any previously pending payment methods
                    //$sql = "DELETE FROM `"._DB_PREFIX."invoice_payment` WHERE invoice_id = $invoice_id AND method = '".mysql_real_escape_string($plugins[''.$payment_method]->get_payment_method_name())."' AND currency_id = '".$invoice_data['currency_id']."' ";
                    // insert a temp payment method here.
                    $invoice_payment_id = update_insert('invoice_payment_id','new','invoice_payment',array(
                        'invoice_id' => $invoice_id,
                        'amount' => $payment_amount,
                        'currency_id' => $invoice_data['currency_id'],
                        'method' => $plugins[''.$payment_method]->get_payment_method_name(),
                    ));


                    $plugins[''.$payment_method]->start_payment($invoice_id,$payment_amount,$invoice_payment_id);

                }
            }
        }
        // todo - better redirect with errors.
        redirect_browser($_SERVER['REQUEST_URI']);
    }


    /**
     * Generate a PDF for the currently load()'d quote
     * Return the path to the file name for this quote.
     * @return bool
     */

    public static function generate_pdf($invoice_id){

        if(!function_exists('convert_html2pdf'))return false;

        $invoice_id = (int)$invoice_id;
        $invoice_data = self::get_invoice($invoice_id);
        $invoice_html = self::invoice_html($invoice_id,$invoice_data,'pdf');
        if($invoice_html){
            //echo $invoice_html;exit;

            $html_file_name = _UCM_FOLDER . "/temp/".'Invoice_'.$invoice_data['name'].'.html';
            $pdf_file_name = _UCM_FOLDER . "/temp/".'Invoice_'.$invoice_data['name'].'.pdf';

            file_put_contents($html_file_name,$invoice_html);

            return convert_html2pdf($html_file_name,$pdf_file_name);


        }
        return false;
    }

    public static function invoice_html($invoice_id,$invoice_data,$mode='html'){

        if($invoice_id && $invoice_data){
            // spit out the invoice html into a file, then pass it to the pdf converter
            // to convert it into a PDF.

            module_template::init_template('invoice_print','<html>
    <head>
        <title>Invoice Print Out</title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <style type="text/css">
        body{
			font-family:arial;
			font-size:17px;
		}
        .table,
        .table2{
            border-collapse:collapse;
        }
        .table td,
        .table2 td.border{
            border:1px solid #EFEFEF;
            border-collapse:collapse;
            padding:4px;
        }
        .task_header{
            background-color: #000000;
            color:#FFFFFF;
        }
    </style>
    </head>
    <body>

<table width="100%" cellpadding="0" cellspacing="0">
    <tbody>
    <tr>
        <td width="10%">&nbsp;</td>
        <td width="80%">


    <table cellpadding="4" cellspacing="0" width="100%">
        <tbody>
            <tr>
                <td width="450" align="left" valign="top">
                    <p>
                        <font style="font-size: 1.6em;">
                            <strong>Invoice #:</strong> {INVOICE_NUMBER}<br/>
                        </font>
                        <strong>Due Date:</strong>
                        {DUE_DATE} <br/>
                    </p>
                    {INVOICE_PAID}
                </td>
                <td align="right" valign="top">
                    <p>
                        <font style="font-size: 1.6em;"><strong>{TITLE}</strong></font>
                        <br/>
                        <font style="color: #333333;">
                        [our company details]
                        </font>
                    </p>
                </td>
            </tr>
            <tr>
                <td align="left" valign="top">
                    <strong>INVOICE TO:</strong><br/>
                    {CUSTOMER_NAME} <br/>
                    {CUSTOMER_ADDRESS} <br/>
                    {CONTACT_NAME} {CONTACT_EMAIL} <br/>
                </td>
                <td align="right" valign="top">
                    &nbsp;<br/>
                    {PROJECT_TYPE}: <strong>{PROJECT_NAME}</strong> <br/>
                    Job: <strong>{JOB_NAME}</strong>
                </td>
            </tr>
        </tbody>
    </table>
    <br/>
    {TASK_LIST}
    <br/>
    
    {PAYMENT_METHODS}

    {PAYMENT_HISTORY}

        </td>
        <td width="10%">&nbsp;</td>
    </tr>
    </tbody>
</table>


</body>
</html>','Used for printing out an invoice for the customer.','html');


            $invoice = $invoice_data;

            $job_data = module_job::get_job(current($invoice_data['job_ids']));
            $website_data = module_website::get_website($job_data['website_id']);

            ob_start();
            include('template/invoice_task_list.php');
            $task_list_html = ob_get_clean();
            ob_start();
            include('template/invoice_payment_history.php');
            $payment_history = ob_get_clean();
            ob_start();
            include('template/invoice_payment_methods.php');
            $payment_methods = ob_get_clean();




            $replace = self::get_replace_fields($invoice_id,$invoice_data);
            $replace['payment_history'] = $payment_history;
            $replace['payment_methods'] = $payment_methods;
            $replace['task_list'] = $task_list_html;

            $replace['external_invoice_template_html'] = '';
                $external_invoice_template = module_template::get_template_by_key('external_invoice');
                $external_invoice_template->assign_values($replace);
            $replace['external_invoice_template_html'] = $external_invoice_template->replace_content();


            $invoice_template = isset($invoice_data['invoice_template_print']) && strlen($invoice_data['invoice_template_print']) ? $invoice_data['invoice_template_print'] : module_config::c('invoice_template_print_default','invoice_print');
            ob_start();
            $template = module_template::get_template_by_key($invoice_template);
            $template->assign_values($replace);
            echo $template->render('html');
            $invoice_html = ob_get_clean();
            return $invoice_html;
        }
        return false;
    }

    public static function add_history($invoice_id,$message){
		module_note::save_note(array(
			'owner_table' => 'invoice',
			'owner_id' => $invoice_id,
			'note' => $message,
			'rel_data' => self::link_open($invoice_id),
			'note_time' => time(),
		));
	}

    public static function customer_id_changed($old_customer_id, $new_customer_id) {
        $old_customer_id = (int)$old_customer_id;
        $new_customer_id = (int)$new_customer_id;
        if($old_customer_id>0 && $new_customer_id>0){
            $sql = "UPDATE `"._DB_PREFIX."invoice` SET customer_id = ".$new_customer_id." WHERE customer_id = ".$old_customer_id;
            query($sql);
        }
    }

    public static function check_invoice_merge($invoice_id) {
        $invoice_data = self::get_invoice($invoice_id);
        $sql = "SELECT invoice_id FROM `"._DB_PREFIX."invoice` i WHERE";
        $sql .= " invoice_id != ".(int)$invoice_id;
        $sql .= " AND total_tax_rate = '".mysql_real_escape_string($invoice_data['total_tax_rate'])."'";
        $sql .= " AND customer_id = ".(int)$invoice_data['customer_id'];
        $sql .= " AND deposit_job_id = 0";
        $sql .= " AND (date_sent IS NULL OR date_sent = '0000-00-00') ";
        return qa($sql);
    }

    public static function email_sent($args){ //$invoice_id,$template_name=''){
        $invoice_id = $args['invoice_id'];
        $template_name = $args['template_name'];
        // add sent date if it doesn't exist
        $invoice = self::get_invoice($invoice_id,true);
        //if(!$invoice['date_sent'] || $invoice['date_sent'] == '0000-00-00'){
            update_insert('invoice_id',$invoice_id,'invoice',array(
                'date_sent' => date('Y-m-d'),
            ));
        //}
        /*switch($template_name){
            case 'invoice_email_overdue':
                self::add_history($invoice_id,_l('Overdue Invoice Emailed'));
                break;
            case 'invoice_email_paid':
                self::add_history($invoice_id,_l('Receipt Emailed'));
                break;
            case 'invoice_email_due':
            default:
                self::add_history($invoice_id,_l('Invoice Emailed'));


        }*/
    }

    public static function get_finance_recurring_items(){
        /**
         * next_due_date
         * url
         * type (i or e)
         * amount
         * currency_id
         * days
         * months
         * years
         * last_transaction_finance_id
         * account_name
         * categories
         * finance_recurring_id
         */
        // find any unpaid invoices.
        $invoices = self::get_invoices(array('date_paid'=>'0000-00-00'));
        $return = array();
        foreach($invoices as $invoice){
            // filter out invoices that haven't been sent yet? probably should...
            $invoice = self::get_invoice($invoice['invoice_id']);
            if(isset($invoice['date_cancel']) && $invoice['date_cancel'] !='0000-00-00')continue;
            // check if this invoice is part of a subscription, put in some additional info for this subscriptions
            // 'recurring_text'
            $recurring_text = '';
            if(class_exists('module_subscription',false)){
                $sql = "SELECT * FROM `"._DB_PREFIX."subscription_history` sh WHERE invoice_id = ".(int)$invoice['invoice_id']."";
                $res = qa1($sql);
                $subscription_name = module_subscription::link_open($res['subscription_id'],true);
                if($invoice['member_id']){
                    $member_name = module_member::link_open($invoice['member_id'],true);
                }else if($invoice['customer_id']){
                    $member_name = module_customer::link_open($invoice['customer_id'],true);
                }else{
                    $member_name = '';
                }
                $recurring_text = _l('Payment from %s on subscription %s',$member_name,$subscription_name);
            }

            $return[] = array(
                'next_due_date' => ($invoice['date_due'] && $invoice['date_due']!='0000-00-00') ? $invoice['date_due'] : $invoice['date_created'],
                'url' => module_invoice::link_open($invoice['invoice_id'],true,$invoice),
                'type' => 'i',
                'amount' => $invoice['total_amount_due'],
                'currency_id' => $invoice['currency_id'],
                'days' => 0,
                'months' => 0,
                'years' => 0,
                'last_transaction_finance_id' => 0,
                'account_name' => '',
                'categories' => '',
                'finance_recurring_id' => 0,
                'recurring_text' => $recurring_text,
            );
        }
        return $return;
    }




    public function get_upgrade_sql(){
        $sql = '';

        $fields = get_fields('invoice');
        // member/subscription integration:
        if(!isset($fields['date_renew'])){
            $sql .= 'ALTER TABLE `'._DB_PREFIX.'invoice` ADD `date_renew` DATE NOT NULL AFTER `date_paid`;';
        }
        if(!isset($fields['renew_invoice_id'])){
            $sql .= 'ALTER TABLE `'._DB_PREFIX.'invoice` ADD `renew_invoice_id` INT(11) NOT NULL DEFAULT \'0\' AFTER `date_renew`;';
        }
        if(!isset($fields['currency_id'])){
            $sql .= 'ALTER TABLE `'._DB_PREFIX.'invoice` ADD `currency_id` int(11) NOT NULL DEFAULT \'1\' AFTER `discount_description`;';
        }
        if(!isset($fields['cached_total'])){
            $sql .= 'ALTER TABLE `'._DB_PREFIX.'invoice` ADD `cached_total` DECIMAL(10,2) NOT NULL DEFAULT \'0\' AFTER `currency_id`;';
        }
        if(!isset($fields['user_id'])){
            $sql .= 'ALTER TABLE `'._DB_PREFIX.'invoice` ADD `user_id` int(11) NOT NULL DEFAULT \'0\' AFTER `currency_id`;';
        }
        if(!isset($fields['member_id'])){
            $sql .= 'ALTER TABLE `'._DB_PREFIX.'invoice` ADD `member_id` INT NOT NULL DEFAULT \'0\' AFTER `user_id`;';
        }
        if(!isset($fields['date_create'])){
            $sql .= 'ALTER TABLE `'._DB_PREFIX.'invoice` ADD `date_create` DATE NOT NULL AFTER `total_tax_rate`;';
        }
        if(!isset($fields['discount_type'])){
            $sql .= 'ALTER TABLE `'._DB_PREFIX.'invoice` ADD `discount_type` INT NOT NULL DEFAULT \'0\' AFTER `discount_description`;';
        }
        if(!isset($fields['deposit_job_id'])){
            $sql .= 'ALTER TABLE `'._DB_PREFIX.'invoice` ADD `deposit_job_id` INT NOT NULL DEFAULT \'0\' AFTER `member_id`;';
        }
        if(!isset($fields['date_cancel'])){
            $sql .= 'ALTER TABLE `'._DB_PREFIX.'invoice` ADD `date_cancel` DATE NOT NULL AFTER `date_renew`;';
        }
        if(!isset($fields['invoice_template_print'])){
            $sql .= 'ALTER TABLE `'._DB_PREFIX.'invoice` ADD `invoice_template_print` varchar(50) NOT NULL DEFAULT \'\' AFTER `deposit_job_id`;';
        }
        if(!isset($fields['invoice_template_email'])){
            $sql .= 'ALTER TABLE `'._DB_PREFIX.'invoice` ADD `invoice_template_email` varchar(50) NOT NULL DEFAULT \'\' AFTER `invoice_template_print`;';
        }

        $fields = get_fields('invoice_payment');
        if(!isset($fields['currency_id'])){
            $sql .= 'ALTER TABLE `'._DB_PREFIX.'invoice_payment` ADD `currency_id` int(11) NOT NULL DEFAULT \'1\' AFTER `method`;';
        }
        if(!isset($fields['data'])){
            $sql .= 'ALTER TABLE `'._DB_PREFIX.'invoice_payment` ADD  `data` LONGBLOB NULL AFTER  `date_paid`;';
        }
        if(!isset($fields['other_id'])){
            $sql .= 'ALTER TABLE `'._DB_PREFIX.'invoice_payment` ADD  `other_id` VARCHAR( 255 ) NOT NULL DEFAULT \'\' AFTER  `data`;';
        }
        if(!isset($fields['payment_type'])){
            $sql .= 'ALTER TABLE `'._DB_PREFIX.'invoice_payment` ADD  `payment_type` TINYINT( 2 ) NOT NULL DEFAULT  \'0\' AFTER  `other_id`;';
        }
        $res = qa1("SHOW TABLES LIKE '"._DB_PREFIX."currency'");
        if(!$res || !count($res)){
            $sql .= 'CREATE TABLE `'._DB_PREFIX.'currency` (
  `currency_id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(4) NOT NULL,
  `symbol` varchar(8) NOT NULL,
  `location` TINYINT( 1 ) NOT NULL DEFAULT  \'1\',
  `create_user_id` int(11) NOT NULL,
  `update_user_id` int(11) NULL,
  `date_created` date NOT NULL,
  `date_updated` date NULL,
  PRIMARY KEY (`currency_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;';
            $sql .= "INSERT INTO `"._DB_PREFIX ."currency` (`currency_id`, `code`, `symbol`, `location`, `create_user_id`, `update_user_id`, `date_created`, `date_updated`) VALUES
(1, 'USD', '$', 1, 0, 1, '2011-11-10', '2011-11-10'),
(2, 'AUD', '$', 1, 1, NULL, '2011-11-10', '2011-11-10');";
        }

        $fields = get_fields('invoice_item');
        if(!isset($fields['date_done'])){
            $sql .= 'ALTER TABLE `'._DB_PREFIX.'invoice_item` ADD `date_done` DATE NOT NULL AFTER `description`;';
        }
        if(!isset($fields['hourly_rate'])){
            $sql .= 'ALTER TABLE `'._DB_PREFIX.'invoice_item` ADD `hourly_rate` DOUBLE(10,2) NOT NULL DEFAULT \'-1\' AFTER `hours`;';
        }
        if(!isset($fields['taxable'])){
            $sql .= 'ALTER TABLE  `'._DB_PREFIX.'invoice_item` ADD  `taxable` tinyint(1) NOT NULL DEFAULT \'1\' AFTER `amount`;';
        }
        if(!isset($fields['task_order'])){
            $sql .= 'ALTER TABLE  `'._DB_PREFIX.'invoice_item` ADD  `task_order` int(11) NOT NULL DEFAULT \'0\' AFTER `description`;';
        }
        if(!isset($fields['long_description'])){
            $sql .= 'ALTER TABLE  `'._DB_PREFIX.'invoice_item` ADD `long_description` LONGTEXT NULL AFTER `description`;';
        }

        // check for indexes
        self::add_table_index('invoice','customer_id');
        self::add_table_index('invoice','deposit_job_id');
        self::add_table_index('invoice_item','task_id');
        self::add_table_index('invoice_item','invoice_id');
        /*$sql_check = 'SHOW INDEX FROM `'._DB_PREFIX.'invoice_item';
        $res = qa($sql_check);
        //print_r($res);exit;
        $add_index=true;
        foreach($res as $r){
            if(isset($r['Column_name']) && $r['Column_name'] == 'task_id'){
                $add_index=false;
            }
        }
        if($add_index){
            $sql .= 'ALTER TABLE  `'._DB_PREFIX.'invoice_item` ADD INDEX ( `task_id` );';
        }

        $add_index=true;
        foreach($res as $r){
            if(isset($r['Column_name']) && $r['Column_name'] == 'invoice_id'){
                $add_index=false;
            }
        }
        if($add_index){
            $sql .= 'ALTER TABLE  `'._DB_PREFIX.'invoice_item` ADD INDEX ( `invoice_id` );';
        }*/

        return $sql;

    }
    public function get_install_sql(){
        ob_start();
        //  `job_id` INT(11) NULL, (useto be in invoice table)
        ?>

    CREATE TABLE `<?php echo _DB_PREFIX; ?>invoice` (
    `invoice_id` int(11) NOT NULL auto_increment,
    `customer_id` INT(11) NULL,
    `hourly_rate` DECIMAL(10,2) NULL,
    `name` varchar(255) NOT NULL DEFAULT  '',
    `status` varchar(255) NOT NULL DEFAULT  '',
    `total_tax_name` varchar(20) NOT NULL DEFAULT  '',
    `total_tax_rate` DECIMAL(10,2) NULL,
    `date_create` date NOT NULL,
    `date_sent` date NOT NULL,
    `date_due` date NOT NULL,
    `date_paid` date NOT NULL,
    `date_renew` date NOT NULL,
    `date_cancel` date NOT NULL,
    `renew_invoice_id` INT(11) NULL,
    `discount_amount` DECIMAL(10,2) NULL,
    `discount_description` varchar(255) NULL,
    `currency_id` int(11) NOT NULL DEFAULT '1',
    `cached_total` DECIMAL(10,2) NOT NULL DEFAULT '0',
    `user_id` int(11) NOT NULL DEFAULT '0',
    `member_id` int(11) NOT NULL DEFAULT '0',
    `deposit_job_id` int(11) NOT NULL DEFAULT '0',
    `invoice_template_print` varchar(50) NOT NULL DEFAULT '',
    `invoice_template_email` varchar(50) NOT NULL DEFAULT '',
    `create_user_id` int(11) NOT NULL,
    `update_user_id` int(11) NULL,
    `date_created` date NOT NULL,
    `date_updated` date NULL,
    PRIMARY KEY  (`invoice_id`),
        KEY `customer_id` (`customer_id`),
        KEY `deposit_job_id` (`deposit_job_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;


    CREATE TABLE `<?php echo _DB_PREFIX; ?>invoice_item` (
    `invoice_item_id` int(11) NOT NULL AUTO_INCREMENT,
    `invoice_id` int(11) NOT NULL,
    `task_id` int(11) NULL,
    `hours` decimal(10,2) NULL,
    `amount` decimal(10,2) NULL,
    `taxable` tinyint(1) NOT NULL DEFAULT '1',
    `completed` decimal(10,2) NULL,
    `description` text NOT NULL,
    `long_description` LONGTEXT NULL,
    `task_order` INT NOT NULL DEFAULT  '0',
    `date_done` date NOT NULL,
    `date_due` date NOT NULL,
    `create_user_id` int(11) NOT NULL,
    `update_user_id` int(11) NULL,
    `date_created` date NOT NULL,
    `date_updated` date NULL,
    PRIMARY KEY (`invoice_item_id`),
        KEY `invoice_id` (`invoice_id`),
        KEY `task_id` (`task_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    CREATE TABLE `<?php echo _DB_PREFIX; ?>invoice_payment` (
    `invoice_payment_id` int(11) NOT NULL AUTO_INCREMENT,
    `invoice_id` int(11) NOT NULL,
    `parent_finance_id` int(11) NULL,
    `amount` decimal(10,2) NOT NULL,
    `method` varchar(50) NOT NULL,
    `currency_id` int(11) NOT NULL DEFAULT '1',
    `date_paid` date NOT NULL,
    `data` LONGBLOB NULL,
    `other_id` VARCHAR( 255 ) NOT NULL DEFAULT '',
    `payment_type` TINYINT( 2 ) NOT NULL DEFAULT  '0',
    `create_user_id` int(11) NOT NULL,
    `update_user_id` int(11) NULL,
    `date_created` date NOT NULL,
    `date_updated` date NULL,
    PRIMARY KEY (`invoice_payment_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    CREATE TABLE `<?php echo _DB_PREFIX; ?>currency` (
    `currency_id` int(11) NOT NULL AUTO_INCREMENT,
    `code` varchar(4) NOT NULL,
    `symbol` varchar(8) NOT NULL,
    `location` TINYINT( 1 ) NOT NULL DEFAULT  '1',
    `create_user_id` int(11) NOT NULL,
    `update_user_id` int(11) NULL,
    `date_created` date NOT NULL,
    `date_updated` date NULL,
    PRIMARY KEY (`currency_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    INSERT INTO `<?php echo _DB_PREFIX; ?>currency` (`currency_id`, `code`, `symbol`, `location`, `create_user_id`, `update_user_id`, `date_created`, `date_updated`) VALUES
    (1, 'USD', '$', 1, 0, 1, '2011-11-10', '2011-11-10'),
    (2, 'AUD', '$', 1, 1, NULL, '2011-11-10', '2011-11-10');

    <?php

        return ob_get_clean();
    }


}
