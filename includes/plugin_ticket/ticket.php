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


define('_TICKET_ACCESS_ALL','All support tickets');
define('_TICKET_ACCESS_ASSIGNED','Only assigned tickets');
define('_TICKET_ACCESS_CREATED','Only tickets I created');
define('_TICKET_ACCESS_CUSTOMER','Only tickets from my customer account');

define('_TICKET_MESSAGE_TYPE_CREATOR',1);
define('_TICKET_MESSAGE_TYPE_ADMIN',0);



define('_TICKET_PRIORITY_STATUS_ID',5);

define('_TICKET_STATUS_RESOLVED_ID',6);

class module_ticket extends module_base{

	public $links;
	public $ticket_types;

    public $version = 2.401;
    // 2.351 - ability to change the assigned contact / customer in the ticket.
    // 2.352 - added novalidate-cert to ticket IMAP connection
    // 2.353 - fix for get_user() to get_contact()
    // 2.354 - new option (ticket_allow_priority_selection) that allows user to select priority on ticket creation. also we allow extra fields on ticket creation.
    // 2.355 - delete a ticket from group
    // 2.36 - extra field encryption support.
    // 2.361 - bug fix for encryption
    // 2.362 - ajax customer drop down list, try not to sned autoreply to customer if admin created message.
    // 2.363 - fix for ticket cron
    // 2.37 - moved list/edit to support themeable pages.
    // 2.371 - hiding previosu messages.
    // 2.372 - mobile layout fixes
    // 2.373 - 20 out of 19 in email footer bug
    // 2.374 - fix pop3 connection string
    // 2.375 - ticket_from_creators_email config variable added
    // 2.376 - permissions fix
    // 2.377 - priority bug fix
    // 2.378 - notify staff member
    // 2.379 - priority invoice fix
    // 2.38 - bulk actions (beta) and public status change
    // 2.381 - replace ticket status links in auto-reply and
    // 2.382 - fix for importing support tickets without a default customer selected
    // 2.383 - improve ticket layout for large ticket threads
    // 2.384 - new jquery version
    // 2.385 - integration with new FAQ feature
    // 2.386 - config option added: ticket_reply_status_id
    // 2.387 - fix to import support emails without subjects
    // 2.388 - from email address as full name of user
    // 2.389 - create customer from ticket
    // 2.39 - fix for faq + product selection
    // 2.391 - ability to reject new support tickets via email, only allow replies
    // 2.392 - ability to create a support ticket from staff to customer.
    // 2.393 - support for multiple ticket queues based on "products" (set advanced 'ticket_separate_product_queue' to 1)
    // 2.394 - priority support fix
    // 2.395 - dropdown support in ticket extra fields
    // 2.396 - better moving customer contacts between customers
    // 2.397 - bug fix, sending customer ticket message alerts
    // 2.398 - extra fields update - show in main listing option
    // 2.399 - speed improvements
    // 2.401 - bug fix with ticket creation on staff accounts


    public static $ticket_statuses = array();
    public static function can_i($actions,$name=false,$category=false,$module=false){
        if(!$module)$module=__CLASS__;
        return parent::can_i($actions,$name,$category,$module);
    }
	public static function get_class() {
        return __CLASS__;
    }
	public function init(){
		$this->links = array();
		$this->ticket_types = array();
		$this->module_name = "ticket";
		$this->module_position = 25;

        self::$ticket_statuses = array(
            1 => _l('Unassigned'),
            2 => _l('New'),
            3 => _l('Replied'),
            5 => _l('In Progress'),
            _TICKET_STATUS_RESOLVED_ID => _l('Resolved'),
            7 => _l('Canceled'),
        );


        $this->ajax_search_keys = array(
            _DB_PREFIX.'ticket' => array(
                'plugin' => 'ticket',
                'search_fields' => array(
                    'ticket_id',
                    'subject',
                ),
                'key' => 'ticket_id',
                'title' => _l('Ticket: '),
            ),
        );

        module_config::register_css('ticket','tickets.css');
        module_config::register_js('ticket','tickets.js');

        hook_add('invoice_admin_list_job','module_ticket::hook_invoice_admin_list_job');
        hook_add('invoice_sidebar','module_ticket::hook_invoice_sidebar');
        hook_add('customer_contact_moved','module_ticket::hook_customer_contact_moved');


        module_template::init_template('ticket_container', '<span style="font-size:10px; color:#666666;">{REPLY_LINE}</span>
<span style="font-size:10px; color:#666666;">Your ticket has been updated, please see the message below:</span>


{MESSAGE}


<span style="font-size:10px; color:#666666;">Ticket Number: <strong>{TICKET_NUMBER}</strong></span>
<span style="font-size:10px; color:#666666;">Ticket Status: <strong>{TICKET_STATUS}</strong></span>
<span style="font-size:10px; color:#666666;">Your position in the support queue: <strong>{POSITION_CURRENT} out of {POSITION_ALL}</strong>.</span>
<span style="font-size:10px; color:#666666;">Estimated time for a reply: <strong>within {DAYS} days</strong></span>
<span style="font-size:10px; color:#666666;">You can view the status of your support query at any time by following this link:</span>
<span style="font-size:10px; color:#666666;"><a href="{URL}" style="color:#666666;">View Ticket {TICKET_NUMBER} History Online</a></span>

','The email sent along with all ticket replies.','text');

        module_template::init_template('ticket_admin_email','{MESSAGE}


<span style="font-size:12px; color:#666666; font-weight: bold;">Ticket Details:</span>
<span style="font-size:10px; color:#666666;">Number of messages: <strong>{MESSAGE_COUNT}</strong></span>
<span style="font-size:10px; color:#666666;">Ticket Number: <strong>{TICKET_NUMBER}</strong></span>
<span style="font-size:10px; color:#666666;">Ticket Status: <strong>{TICKET_STATUS}</strong></span>
<span style="font-size:10px; color:#666666;">Position in the support queue: <strong>{POSITION_CURRENT} out of {POSITION_ALL}</strong>.</span>
<span style="font-size:10px; color:#666666;">Estimated time for a reply: <strong>within {DAYS} days</strong></span>
<span style="font-size:10px; color:#666666;">View the ticket: <strong>{URL_ADMIN}</strong></span>
        ','Sent as an email to the administrator when a new ticket is created.','text');

        module_template::init_template('ticket_autoreply','Hello,

Thank you for your email. We will reply shortly.

        ','Sent as an email after a support ticket is received.','text');

 module_template::init_template('ticket_rejection','Hello,

Please submit all NEW support tickets via our website by following this link:
{TICKET_URL}

New support tickets are no longer accepted via email due to high levels of spam causing delays for everyone.

Thanks,

        ','Email Bounced: {SUBJECT}.','text');



	}

    public function pre_menu(){


        if($this->is_installed() && $this->can_i('view','Tickets')){

            /* module_security::has_feature_access(array(
                    'name' => 'Settings',
                    'module' => 'config',
                    'category' => 'Config',
                    'view' => 1,
                    'description' => 'view',
            ))*/
            if($this->can_i('edit','Ticket Settings')){
                $this->links['ticket_settings'] = array(
                    "name"=>"Ticket",
                    "p"=>"ticket_settings",
                    'args'=>array('ticket_account_id'=>false,'ticket_id'=>false),
                    "icon"=>"icon.png",
                    'holder_module' => 'config', // which parent module this link will sit under.
                    'holder_module_page' => 'config_admin',  // which page this link will be automatically added to.
                    'menu_include_parent' => 0,
                );
            }

            // only display if a customer has been created.
            if(isset($_REQUEST['customer_id']) && (int)$_REQUEST['customer_id']>0){
                $link_name = _l('Tickets');
                if(module_config::c('ticket_show_summary',1) && self::can_edit_tickets()){
                    // how many tickets?
                    // cache results for 30 seconds.
                    $ticket_count = self::get_total_ticket_count($_REQUEST['customer_id']);

                    if($ticket_count>0){
                        $link_name .= " <span class='menu_label'>".$ticket_count."</span> ";
                    }
                }

                $this->links['ticket_customer'] = array(
                    "name"=>$link_name,
                    "p"=>"ticket_admin",
                    'args'=>array('ticket_id'=>false),
                    'holder_module' => 'customer', // which parent module this link will sit under.
                    'holder_module_page' => 'customer_admin_open',  // which page this link will be automatically added to.
                    'menu_include_parent' => 0,
                );
            }


            $link_name = _l('Tickets');
            if(module_config::c('ticket_show_summary',1) && self::can_edit_tickets()){
                switch(module_config::c('ticket_show_summary_type','unread')){
                    case 'unread':
                        $ticket_count = self::get_unread_ticket_count();
                        break;
                    case 'total':
                    default:
                        $ticket_count = self::get_total_ticket_count();
                        break;
                }

                if($ticket_count > 0){
                    $link_name .= " <span class='menu_label'>".$ticket_count."</span> ";
                }
                $ticket_count = self::get_priority_ticket_count();
                if($ticket_count && $ticket_count[0] > 0){
                    $link_name .= " <span class='menu_label".($ticket_count[1]>0 ? ' important':'')."'>".$ticket_count[0]."</span> ";
                }
            }
            $this->links['ticket_main'] = array(
                "name"=>$link_name,
                "p"=>"ticket_admin",
                'args'=>array('ticket_id'=>false),
            );
        }
    }

    public static function can_edit_tickets(){
        return self::can_i('edit','Tickets');
    }
    public static function creator_hash($creator_id){
        return md5('secret key! '._UCM_FOLDER.$creator_id);
    }
	public function handle_hook($hook){
		switch($hook){
			case "invoice_paid":


                $foo = func_get_args();
                $invoice_id = (int)$foo[1];
                if($invoice_id>0){
                    // see if any tickets match this invoice.
                    $ticket = get_single('ticket','invoice_id',$invoice_id);
                    if($ticket){
                        // check it's status and make it priority if it isn't already
                        if($ticket['priority'] != _TICKET_PRIORITY_STATUS_ID){
                            update_insert('ticket_id',$ticket['ticket_id'],'ticket',array(
                                'priority' => _TICKET_PRIORITY_STATUS_ID,
                            ));
                            // todo - send email to admin?
                            //send_email('dtbaker@gmail.com','priority ticket',var_export($ticket,true));
                        }
                    }
                }

                break;
			case "home_alerts":
				$alerts = array();
                if(module_ticket::can_edit_tickets()){
                    if(module_config::c('ticket_alerts',1)){
                        // find any tickets that are past the due date and dont have a finished date.
                        $sql = "SELECT * FROM `"._DB_PREFIX."ticket` p ";
                        $sql .= " WHERE p.status_id <= 2 AND p.date_updated <= '".date('Y-m-d',strtotime('-'.module_config::c('ticket_turn_around_days',5).' days'))."'";
                        $tickets = qa($sql);
                        foreach($tickets as $ticket){
                            $alert_res = process_alert($ticket['date_updated'], _l('Ticket Not Completed'), module_config::c('ticket_turn_around_days',5));
                            if($alert_res){
                                $alert_res['link'] = $this->link_open($ticket['ticket_id']);
                                $alert_res['name'] = $ticket['subject'];
                                $alerts[] = $alert_res;
                            }
                        }
                    }
				}
				return $alerts;
				break;
        }
    }

    public static function link_generate($ticket_id=false,$options=array(),$link_options=array()){

        $key = 'ticket_id';
        if($ticket_id === false && $link_options){
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
        if(!isset($options['type']))$options['type']='ticket';
        if(!isset($options['page']))$options['page']='ticket_admin';
        if(!isset($options['arguments'])){
            $options['arguments'] = array();
        }
        $options['arguments']['ticket_id'] = $ticket_id;
        $options['module'] = 'ticket';
        // what text should we display in this link?
        if($options['page']=='ticket_settings_fields'){
            if(isset($options['data']) && $options['data']){
                //$options['data'] = $options['data'];
            }else{
                $data = self::get_ticket_extras_key($ticket_id);
                $options['data'] = $data;
            }
            $options['text'] = isset($options['data']['key']) ? $options['data']['key'] : '';
            array_unshift($link_options,$options);
            $options['page']='ticket_settings';
            // bubble back onto ourselves for the link.
            return self::link_generate($ticket_id,$options,$link_options);
        }else if($options['page']=='ticket_settings_types'){
            if(isset($options['data']) && $options['data']){
                //$options['data'] = $options['data'];
            }else{
                $data = self::get_ticket_type($ticket_id);
                $options['data'] = $data;
            }
            $options['text'] = isset($options['data']['name']) ? $options['data']['name'] : '';
            array_unshift($link_options,$options);
            $options['page']='ticket_settings';
            // bubble back onto ourselves for the link.
            return self::link_generate($ticket_id,$options,$link_options);
        }elseif($options['page']=='ticket_settings_accounts'){
            if(isset($options['data']) && $options['data']){
                //$options['data'] = $options['data'];
            }else{
                $data = self::get_ticket_account($ticket_id);
                $options['data'] = $data;
            }
            $options['text'] = $options['data']['name'];
            array_unshift($link_options,$options);
            $options['page']='ticket_settings';
            // bubble back onto ourselves for the link.
            return self::link_generate($ticket_id,$options,$link_options);
        }else{
            if(isset($options['data']) && $options['data']){
                //$options['data'] = $options['data'];
            }else{
                $data = self::get_ticket($ticket_id);
                $options['data'] = $data;
                $options['class'] = 'error';
            }
            $options['text'] = $ticket_id ? self::ticket_number($ticket_id) : 'N/A';
        }
        array_unshift($link_options,$options);
        if($options['page']=='ticket_admin' && $options['data'] && isset($options['data']['status_id'])){
            // pick the class name for the error. or ticket status
            $link_options['class'] = 'ticket_status_'.$options['data']['status_id'];
        }
        if(self::can_i('edit','Ticket Settings') && $options['page']=='ticket_settings'){
            $bubble_to_module = array(
                'module' => 'config',
            );
        }else if((!isset($_GET['customer_id'])||!$_GET['customer_id']) && class_exists('module_faq',false) && module_config::c('ticket_separate_product_queue',0)){

        }else if($options['data']['customer_id']>0){
            if(!module_security::has_feature_access(array(
                'name' => 'Customers',
                'module' => 'customer',
                'category' => 'Customer',
                'view' => 1,
                'description' => 'view',
            ))){
                /*if(!isset($options['full']) || !$options['full']){
                    return '#';
                }else{
                    return isset($options['text']) ? $options['text'] : 'N/A';
                }*/
            }else{
                $bubble_to_module = array(
                    'module' => 'customer',
                    'argument' => 'customer_id',
                );
            }
        }
        if($bubble_to_module){
            global $plugins;
            return $plugins[$bubble_to_module['module']]->link_generate(false,array(),$link_options);
        }else{
            // return the link as-is, no more bubbling or anything.
            // pass this off to the global link_generate() function
            return link_generate($link_options);

        }
    }

	public static function link_open($ticket_id,$full=false,$ticket_data=array()){
        return self::link_generate($ticket_id,array('full'=>$full,'data'=>$ticket_data));
    }
	public static function link_open_notify($ticket_id,$full=false,$ticket_data=array()){
        return self::link_generate($ticket_id,array('data'=>$ticket_data,'full'=>$full,'arguments'=>array('notify'=>1)));
    }
	public static function link_open_account($ticket_account_id,$full=false){
        return self::link_generate($ticket_account_id,array('page'=>'ticket_settings_accounts','full'=>$full,'arguments'=>array('ticket_account_id'=>$ticket_account_id)));
    }
	public static function link_open_field($ticket_data_key_id,$full=false){
        return self::link_generate($ticket_data_key_id,array('page'=>'ticket_settings_fields','full'=>$full,'arguments'=>array('ticket_data_key_id'=>$ticket_data_key_id)));
    }

	public static function link_open_type($ticket_type_id,$full=false){
        return self::link_generate($ticket_type_id,array('page'=>'ticket_settings_types','full'=>$full,'arguments'=>array('ticket_type_id'=>$ticket_type_id)));
    }


    public static function link_public_status($ticket_id,$new_status_id,$h=false){
        if($h){
            return md5('s3cret7hash for tickets '.$new_status_id.'statuses '._UCM_FOLDER.' '.$ticket_id);
        }
        return full_link(_EXTERNAL_TUNNEL_REWRITE.'m.ticket/h.public_status/i.'.$ticket_id.'/s.'.$new_status_id.'/hash.'.self::link_public_status($ticket_id,$new_status_id,true));

    }
    public static function link_public($ticket_id,$h=false){
        if($h){
            return md5('s3cret7hash for tickets '._UCM_FOLDER.' '.$ticket_id);
        }
        return full_link(_EXTERNAL_TUNNEL_REWRITE.'m.ticket/h.public/i.'.$ticket_id.'/hash.'.self::link_public($ticket_id,true));
        //return full_link(_EXTERNAL_TUNNEL.'?m=ticket&h=public&i='.$ticket_id.'&hash='.self::link_public($ticket_id,true));
        /*
        // return an auto login link for the end user.
        $ticket_data = self::get_ticket($ticket_id);
        if($ticket_data['user_id']){
            $auto_login_link = 'auto_login='.module_security::get_auto_login_string($ticket_data['user_id']);
        }else{
            $auto_login_link = '';
        }
        $link_options = array();
        $options['page'] = 'ticket_admin';
        $options['arguments'] = array();
        $options['arguments']['ticket_id'] = $ticket_id;
        $options['module'] = 'ticket';
        $options['data'] = $ticket_data;
        array_unshift($link_options,$options);
        $link = link_generate($link_options);
        $link .= strpos($link,'?') === false ? '?' : '&';
        $link .= $auto_login_link;
        return $link;
        */
    }
    public static function link_open_attachment($ticket_id,$ticket_message_attachment_id,$h=false){
        if($h){
            return md5('s3cret7hash for ticket attacments '._UCM_FOLDER.' '.$ticket_id.'-'.$ticket_message_attachment_id);
        }
        return full_link(_EXTERNAL_TUNNEL_REWRITE.'m.ticket/h.attachment/t.'.$ticket_id.'/tma.'.$ticket_message_attachment_id.'/hash.'.self::link_open_attachment($ticket_id,$ticket_message_attachment_id,true));
        //return full_link(_EXTERNAL_TUNNEL.'?m=ticket&h=attachment&t='.$ticket_id.'&tma='.$ticket_message_attachment_id.'&hash='.self::link_open_attachment($ticket_id,$ticket_message_attachment_id,true));
    }
    public static function link_public_new(){
        return full_link(_EXTERNAL_TUNNEL.'?m=ticket&h=public_new');
    }

    public function external_hook($hook){
            switch($hook){
                case 'attachment':

                    $ticket_id = (isset($_REQUEST['t'])) ? (int)$_REQUEST['t'] : false;
                    $ticket_message_attachment_id = (isset($_REQUEST['tma'])) ? (int)$_REQUEST['tma'] : false;
                    $hash = (isset($_REQUEST['hash'])) ? trim($_REQUEST['hash']) : false;
                    if($ticket_id && $ticket_message_attachment_id && $hash){
                        $correct_hash = $this->link_open_attachment($ticket_id,$ticket_message_attachment_id,true);
                        if($correct_hash == $hash){
                            $attach = get_single('ticket_message_attachment','ticket_message_attachment_id',$ticket_message_attachment_id);
                            if(file_exists('includes/plugin_ticket/attachments/'.$attach['ticket_message_attachment_id'])){
                                header("Content-type: application/octet-stream");
                                header('Content-Disposition: attachment; filename="'.$attach['file_name'].'";');
                                readfile('includes/plugin_ticket/attachments/'.$attach['ticket_message_attachment_id']);
                            }else{
                                echo 'File no longer exists';
                            }
                        }
                    }
                    exit;
                    break;
                case 'status':
                    ob_start();
                    ?>

                    <table class="wpetss wpetss_status">
                        <tbody>
                        <tr>
                            <th><?php _e('New/Pending Tickets');?></th>
                            <td>
                                <?php
                                $sql = "SELECT COUNT(ticket_id) AS c FROM `"._DB_PREFIX."ticket` WHERE status_id = 1 OR status_id = 2";
                                $res = array_shift(qa($sql));
                                echo $res['c'];
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <th><?php _e('In Progress Tickets');?></th>
                            <td>
                                <?php
                                $sql = "SELECT COUNT(ticket_id) AS c FROM `"._DB_PREFIX."ticket` WHERE status_id = 3 OR status_id = 5";
                                $res = array_shift(qa($sql));
                                echo $res['c'];
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <th><?php _e('Resolved Tickets');?></th>
                            <td>
                                <?php
                                $sql = "SELECT COUNT(ticket_id) AS c FROM `"._DB_PREFIX."ticket` WHERE status_id >= "._TICKET_STATUS_RESOLVED_ID;
                                $res = array_shift(qa($sql));
                                echo $res['c'];
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <th><?php _e('Estimated Turn Around');?></th>
                            <td>
                                <?php echo _l('We will reply within %s and %s days',module_config::c('ticket_turn_around_days_min',2),module_config::c('ticket_turn_around_days',5)); ?>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                    <?php
                    echo preg_replace('/\s+/',' ',ob_get_clean());
                    exit;
                    break;
                case 'public_new':

                    $ticket_id = 'new';
                    $ticket_account_id = module_config::c('ticket_default_account_id',0); //todo: set from a hashed variable in GET string.
                    if($ticket_account_id){
                        $ticket_account = self::get_ticket_account($ticket_account_id);
                    }else{
                        $ticket_account_id = 0;
                        $ticket_account = array();
                    }
                    if(!$ticket_account || $ticket_account['ticket_account_id']!=$ticket_account_id){
                        // dont support accounts yet. work out the default customer id etc.. from settings.
                        $ticket_account = array(
                            'ticket_account_id' => 0,
                            'default_customer_id' => module_config::c('ticket_default_customer_id',1),
                            'default_user_id' => module_config::c('ticket_default_user_id',1),
                            'default_type' => module_config::c('ticket_type_id_default',0),
                        );
                    }

                    // hack to better support recaptcha errors.
                    $save_public_ticket = false;
                    $errors = array();

                    if(isset($_REQUEST['_process']) && $_REQUEST['_process'] == 'save_public_ticket'){
                        // user is saving the ticket.
                        // process it!

                        $save_public_ticket = true;

                        if(module_config::c('ticket_recaptcha',1)){
                            if(!module_captcha::check_captcha_form()){
                                // captcha was wrong.
                                $errors [] = _l('Sorry the captcha code you entered was incorrect. Please try again.');
                                if(isset($_FILES['attachment']) && isset($_FILES['attachment']['tmp_name']) && is_array($_FILES['attachment']['tmp_name'])){
                                    foreach($_FILES['attachment']['tmp_name'] as $key => $val){
                                        if(is_uploaded_file($val)){
                                            $errors [] = _l('Please select your file attachments again as well.');
                                            break;
                                        }
                                    }
                                }
                                $save_public_ticket = false;
                            }
                        }
                    }
                    if($save_public_ticket && isset($_POST['new_ticket_message']) && strlen($_POST['new_ticket_message']) > 1){

                            // this allows input variables to be added to our $_POST
                            // like extra fields etc.. from envato module.
                            handle_hook('ticket_create_post',$ticket_id);

                            // we're posting from a public account.
                            // check required fields.
                            if(!trim($_POST['subject'])){
                                return false;
                            }
                            // check this user has a valid email address, find/create a user in the ticket user table.
                            // see if this email address exists in the wp user table, and link that user there.
                            $email = trim(strtolower($_POST['email']));
                            $name = trim($_POST['name']);
                            if(strpos($email,'@')){ //todo - validate email.
                                $sql = "SELECT * FROM `"._DB_PREFIX."user` u WHERE u.`email` LIKE '".mysql_real_escape_string($email)."'";
                                $from_user = qa1($sql);
                                if($from_user){
                                    $from_user_id = $from_user['user_id'];
                                    // woo!! found a user. assign this customer to the ticket.
                                    if($from_user['customer_id']){
                                        $ticket_account['default_customer_id'] = $from_user['customer_id'];
                                    }
                                }else{
                                    // create a user under this account customer.
                                    $default_customer_id = 0;
                                    if($ticket_account && $ticket_account['default_customer_id']){
                                        $default_customer_id = $ticket_account['default_customer_id'];
                                    }
                                    // create a new support user! go go!
                                    if(strlen($name)){
                                        $bits = explode(' ',$name);
                                        $first_name = array_shift($bits);
                                        $last_name = implode(' ',$bits);
                                    }else{
                                        $first_name = $email;
                                        $last_name = '';
                                    }
                                    $from_user = array(
                                        'name' => $first_name,
                                        'last_name' => $last_name,
                                        'customer_id' => $default_customer_id,
                                        'email' => $email,
                                        'status_id' => 1,
                                        'password' => substr(md5(time().mt_rand(0,600)),3,7),
                                    );
                                    global $plugins;
                                    $from_user_id = $plugins['user']->create_user($from_user);
                                    // todo: set the default role for this user
                                    // based on the settings
                                    /*}else{
                                        echo 'Failed - no from accoutn set';
                                        return;
                                    }*/
                                }

                                if(!$from_user_id){
                                    echo 'Failed - cannot find the from user id';
                                    echo $email . ' to support<hr>';
                                    return;
                                }

                                // what type of ticket is this?
                                $public_types = $this->get_types(true);
                                $ticket_type_id = $ticket_account['default_type'];
                                if(isset($_POST['ticket_type_id'])&&isset($public_types[$_POST['ticket_type_id']])){
                                    $ticket_type_id = $_POST['ticket_type_id'];
                                }
//                                echo $ticket_type_id;exit;

                                $ticket_data = array(
                                    'user_id' => $from_user_id,
                                    'force_logged_in_user_id' => $from_user_id,
                                    'assigned_user_id' => $ticket_account['default_user_id'] ? $ticket_account['default_user_id'] : module_config::c('ticket_default_user_id',1),
                                    'ticket_type_id' => $ticket_type_id,
                                    'customer_id' => $ticket_account['default_customer_id'],
                                    'status_id' => 2,
                                    'ticket_account_id' => $ticket_account_id,
                                    'unread' => 1,
                                    'subject' => $_POST['subject'],
                                    'new_ticket_message' => $_POST['new_ticket_message'],
                                    'ticket_extra' => isset($_POST['ticket_extra']) && is_array($_POST['ticket_extra']) ? $_POST['ticket_extra'] : array(),
                                    'faq_product_id' => isset($_POST['faq_product_id']) ? (int)$_POST['faq_product_id'] : 0,
                                );
                                if(module_config::c('ticket_allow_priority_selection',0) && isset($_POST['priority'])){
                                    $priorities = $this->get_ticket_priorities();
                                    if(isset($priorities[$_POST['priority']])){
                                        $ticket_data['priority'] = $_POST['priority'];
                                    }
                                }
                                $ticket_id = $this->save_ticket('new',$ticket_data);

                                // check if they want a priority support
                                if(isset($_POST['do_priority']) && $_POST['do_priority']){
                                    // generate a "priority invoice" against this support ticket using the invoice module.
                                    // this will display the invoice in the sidebar and the user can pay.
                                    $this->generate_priority_invoice($ticket_id);
                                }

                                handle_hook('ticket_public_created',$ticket_id);

                                // where to redirect?
                                $url = module_config::c('ticket_public_new_redirect','');
                                if(!$url){
                                    $url = $this->link_public($ticket_id);
                                }

                                redirect_browser($url);

                            }
                    }

                    $ticket = self::get_ticket($ticket_id);
                    include('public/ticket_customer_new.php');

                    break;
                case 'public_status':

                    $ticket_id = (isset($_REQUEST['i'])) ? (int)$_REQUEST['i'] : false;
                    $new_status_id = (isset($_REQUEST['s'])) ? (int)$_REQUEST['s'] : false;
                    $hash = (isset($_REQUEST['hash'])) ? trim($_REQUEST['hash']) : false;
                    if($ticket_id && $new_status_id && $hash){
                        $correct_hash = $this->link_public_status($ticket_id,$new_status_id,true);
                        if($correct_hash == $hash){
                            // change the status.
                            update_insert('ticket_id',$ticket_id,'ticket',array('status_id'=>$new_status_id));
                            module_template::init_template('ticket_status_change','<h2>Ticket</h2>
<p>Thank you. Your support ticket status has been adjusted.</p>
<p>Please <a href="{TICKET_URL}">click here</a> to view your ticket.</p>
','Displayed after an external ticket status is changed.','code');
                            // correct!
                            // load up the receipt template.
                            $template = module_template::get_template_by_key('ticket_status_change');

                            $data = $this->get_ticket($ticket_id);
                            $data['ticket_url'] = $this->link_public($ticket_id);
                            $template->page_title = _l("Ticket");

                            $template->assign_values($data);
                            echo $template->render('pretty_html');
                        }
                    }
                    exit;
                    break;
                case 'public':

                    $ticket_id = (isset($_REQUEST['i'])) ? (int)$_REQUEST['i'] : false;
                    $hash = (isset($_REQUEST['hash'])) ? trim($_REQUEST['hash']) : false;
                    if($ticket_id && $hash){
                        $correct_hash = $this->link_public($ticket_id,true);
                        if($correct_hash == $hash){
                            // all good to print a receipt for this payment.
                            $ticket = $this->get_ticket($ticket_id);

                            if(isset($_POST['_process']) && $_POST['_process'] == 'send_public_ticket'){
                                // user is saving the ticket.
                                // process it!
                                if(isset($_POST['new_ticket_message']) && strlen($_POST['new_ticket_message']) > 1){
                                    // post a new reply to this message.
                                    // who are we replying to?
                                    // it's either a reply from the admin, or from the user via the web interface.
                                    $ticket_creator = $ticket['user_id'];
                                    $to_user_id = $ticket['assigned_user_id'] ? $ticket['assigned_user_id'] : module_config::c('ticket_default_user_id',1);
                                    $this->send_reply($ticket_id,$_POST['new_ticket_message'], $ticket_creator, $to_user_id, 'end_user');

                                    /*$new_status_id = $ticket['status_id'];
                                    if($ticket['status_id']>=6){
                                        // it's cancelled or resolved.
                                    }*/
                                    $new_status_id = 5;
                                    update_insert("ticket_id",$ticket_id,"ticket",array('unread'=>1,'status_id'=>$new_status_id));
                                }

                                if(isset($_REQUEST['generate_priority_invoice'])){
                                    $invoice_id = $this->generate_priority_invoice($ticket_id);
                                    redirect_browser(module_invoice::link_public($invoice_id));
                                }

                                // where to redirect?
                                $url = module_config::c('ticket_public_reply_redirect','');
                                if(!$url){
                                    $url = $this->link_public($ticket_id);
                                }

                                redirect_browser($url);

                            }


                            if($ticket&& $ticket['ticket_id'] == $ticket_id){


                                $admins_rel = self::get_ticket_staff_rel();
                                /*if(!isset($logged_in_user) || !$logged_in_user){
                                    // we assume the user is on the public side.
                                    // use the creator id as the logged in id.
                                    $logged_in_user = module_security::get_loggedin_id();
                                }*/
                                // public hack, we are the ticket responder.
                                $logged_in_user = $ticket['user_id'];

                                $ticket_creator = $ticket['user_id'];
                                if($ticket_creator == $logged_in_user){
                                    // we are sending a reply back to the admin, from the end user.
                                    $to_user_id = $ticket['assigned_user_id'] ? $ticket['assigned_user_id'] : module_config::c('ticket_default_user_id',1);
                                    $from_user_id = $logged_in_user;
                                }else{
                                    // we are sending a reply back to the ticket user.
                                    $to_user_id = $ticket['user_id'];
                                    $from_user_id = $logged_in_user;
                                }
                                $to_user_a = module_user::get_user($to_user_id,false);
                                $from_user_a = module_user::get_user($from_user_id,false);

                                if(isset($ticket['ticket_account_id']) && $ticket['ticket_account_id']){
                                    $ticket_account = module_ticket::get_ticket_account($ticket['ticket_account_id']);
                                }else{
                                    $ticket_account = false;
                                }

                                if($ticket_account && $ticket_account['email']){
                                    $reply_to_address = $ticket_account['email'];
                                    $reply_to_name = $ticket_account['name'];
                                }else{
                                    // reply to creator.
                                    $reply_to_address = $from_user_a['email'];
                                    $reply_to_name = $from_user_a['name'];
                                }


                                if($ticket_creator == $logged_in_user){
                                    $send_as_name = $from_user_a['name'];
                                    $send_as_address = $from_user_a['email'];
                                }else{
                                    $send_as_address = $reply_to_address;
                                    $send_as_name = $reply_to_name;
                                }

                                $admins_rel = self::get_ticket_staff_rel();

                                ob_start();
                                include('public/ticket_customer_view.php');
                                $html = ob_get_clean();

                                module_template::init_template('external_ticket_public_view','{TICKET_HTML}', 'Used when displaying the external view of a ticket to the customer.','code');
                                $template = module_template::get_template_by_key('external_ticket_public_view');
                                $template->assign_values(array(
                                                             'ticket_html' => $html,
                                                         ));
                                $template->page_title = _l('Ticket: %s',module_ticket::ticket_number($ticket['ticket_id']));

                                echo $template->render('pretty_html');
                                exit;

                            }else{
                                _e('Permission Denied. Please logout and try again.');
                            }
                        }
                    }
                    break;
            }
        }



    public static function ticket_number($id){
        $id=(int)$id;
        if(!$id)return _l('New');
        return str_pad($id,6,'0',STR_PAD_LEFT);
    }

    // will return the total ticket count if given no parameters.
    // if given a ticket id then faq_product_id is ignored.
    //if given a faq_product_id and no ticket id it will show only for that product
    // if given a ticket_id then faq_product_id is pulled from ticket read, and priority is pulled as well.
    public static function ticket_position($ticket_id=false,$faq_product_id=false){
        $ordering = module_config::c('ticket_ordering','latest_message_last');
        if($ticket_id){
            // want a count of all tickets above this one.
            $ticket_data = self::get_ticket($ticket_id,2); // this gets basic data
            if($ticket_data&&$ticket_data['ticket_id']==$ticket_id){
                $faq_product_id = !$faq_product_id ? $ticket_data['faq_product_id'] : $faq_product_id;
                $sql = "SELECT COUNT(ticket_id) AS c FROM `"._DB_PREFIX."ticket` t WHERE t.status_id < "._TICKET_STATUS_RESOLVED_ID."";
                // find tickets that are above or equal to this priority
                if($faq_product_id && module_config::c('ticket_separate_product_queue',0)){
                    $sql .= "  AND ( t.faq_product_id = ".(int)$faq_product_id."";
                    /*if(module_config::c('ticket_separate_product_queue_incempty',1)){
                        $sql .= " OR t.faq_product_id = 0 ";
                    }*/
                    $sql .= " ) ";
                }
                $sql .= "  AND ( t.priority > ".(int)$ticket_data['priority']." OR ( t.priority = ".(int)$ticket_data['priority'] ." ";
                switch($ordering){
                    case 'unread_first':
                    case 'ticket_id':
                        $sql .= " AND t.ticket_id <= ".(int)$ticket_id."";
                        break;
                    case 'latest_message_last':
                    default:
                        $sql .= " AND t.last_message_timestamp <= ".(int)$ticket_data['last_message_timestamp']."";
                        break;
                }
                $sql .= " )";
                $sql .= " )";
                $current = qa1($sql,false);
                $sql = "SELECT COUNT(ticket_id) AS c FROM `"._DB_PREFIX."ticket` t WHERE (t.status_id < "._TICKET_STATUS_RESOLVED_ID."";
                if($faq_product_id && module_config::c('ticket_separate_product_queue',0)){
                    $sql .= "  AND t.faq_product_id = ".(int)$faq_product_id."";
                }
                $sql .= " )";
                $total = qa1($sql,false);
                return array(
                    'current' => $current['c'],
                    'total' => $total['c']
                );
            }
        }else if($faq_product_id){
            // want a count of all tickets that have this faq_product_id
            $sql = "SELECT COUNT(ticket_id) AS c FROM `"._DB_PREFIX."ticket` t WHERE (t.status_id < "._TICKET_STATUS_RESOLVED_ID."";
            if($faq_product_id && module_config::c('ticket_separate_product_queue',0)){
                $sql .= "  AND ( t.faq_product_id = ".(int)$faq_product_id."";
                /*if(module_config::c('ticket_separate_product_queue_incempty',1)){
                    $sql .= " OR t.faq_product_id = 0 ";
                }*/
                $sql .= " ) ";
            }
            $sql .= " )";
            $current = qa1($sql,false);
            return array(
                'current' => $current['c'],
                'total' => $current['c']
            );
        }else{
            // just a count on all tickets.
            $x = self::get_total_ticket_count();
            return array(
                'current' => $x,
                'total' => $x,
            );
        }
    }

    /** old ticket_count method, we're slowly moving to the new one (above) that will better handle our new features (eg: different queue per product) */
    public static function ticket_count($type, $time = false, $ticket_id = false, $ticket_priority = false){
        switch($type){
            case 'paid':
            case 'priority':
                $sql = "SELECT COUNT(ticket_id) AS c FROM `"._DB_PREFIX."ticket` t WHERE (t.status_id < "._TICKET_STATUS_RESOLVED_ID." AND t.priority = "._TICKET_PRIORITY_STATUS_ID;
                switch(module_config::c('ticket_ordering','latest_message_last')){
                    case 'unread_first':
                    case 'ticket_id':
                        if($ticket_id){
                            $sql .= " AND t.ticket_id <= ".(int)$ticket_id."";
                        }
                        break;
                    case 'latest_message_last':
                    default:
                        if($time){
                            $sql .= " AND t.last_message_timestamp <= ".(int)$time."";
                        }
                        break;
                }

                $sql .= " )";
                $res = qa1($sql);
                return $res['c'];
            default:
                $sql = "SELECT COUNT(ticket_id) AS c FROM `"._DB_PREFIX."ticket` t WHERE (t.status_id < "._TICKET_STATUS_RESOLVED_ID;
                // we filter by the priority id too.
                if($ticket_priority){
                    $sql .= " AND ( t.priority > ".(int)$ticket_priority ." OR ( t.priority = ".(int)$ticket_priority ." ";
                }
                switch(module_config::c('ticket_ordering','latest_message_last')){
                    case 'unread_first':
                    case 'ticket_id':
                        if($ticket_id){
                            $sql .= " AND t.ticket_id <= ".(int)$ticket_id."";
                        }
                        break;
                    case 'latest_message_last':
                    default:
                        if($time){
                            $sql .= " AND t.last_message_timestamp <= ".(int)$time."";
                        }
                        break;
                }
                if($ticket_priority){
                    $sql .= " ) ) ";
                }
                $sql .= " )";
                $res = qa1($sql,false); // fix bug with 20 out of 19.
                return $res['c'];
        }
    }



	public function process(){
		$errors=array();
        if('save_saved_response' == $_REQUEST['_process']){

            $data = array(
                'value' => $_REQUEST['value'],
            );
            $saved_response_id = (int)$_REQUEST['saved_response_id'];
            if((string)$saved_response_id != (string)$_REQUEST['saved_response_id']){
                // we are saving a new response, not overwriting an old one.
                $data['name'] = $_REQUEST['saved_response_id'];
                $saved_response_id = 'new';
            }else{
                // overwriting an old one.
            }
            $this->save_saved_response($saved_response_id,$data);
            // saved via ajax
            exit;

        }else if('insert_saved_response' == $_REQUEST['_process']){

            ob_end_clean();
            $response = $this->get_saved_response($_REQUEST['saved_response_id']);
            echo json_encode($response);
            exit;

        }else if('save_ticket_type' == $_REQUEST['_process']){

            if(!module_config::can_i('edit','Settings')){
                die('No perms to save ticket settings.');
            }

            $ticket_type_id = update_insert('ticket_type_id',$_REQUEST['ticket_type_id'],'ticket_type',$_POST);
            if(isset($_REQUEST['butt_del'])){
                // deleting ticket type all together
                delete_from_db('ticket_type','ticket_type_id',$_REQUEST['ticket_type_id']);
                set_message('Ticket type deleted successfully.');
                redirect_browser($this->link_open_type(false));
            }
            set_message('Ticket type saved successfully');
            redirect_browser($this->link_open_type($ticket_type_id));
            
            
        }else if('save_ticket_data_key' == $_REQUEST['_process']){

            if(!module_config::can_i('edit','Settings')){
                die('No perms to save ticket settings.');
            }

            $data = $_POST;
            if(isset($data['options'])){
                $options = array();
                foreach(explode("\n",$data['options']) as $line){
                    $line = trim($line);
                    if(strlen($line)>0){
                        $bits = explode('|',$line);
                        $key = $bits[0];
                        if(count($bits)==2){
                            $val = $bits[1];
                        }else{
                            $val = $bits[0];
                        }
                        $options[$key] = $val;
                    }
                }
                $data['options'] = serialize($options);
            }

            $ticket_data_key_id = update_insert('ticket_data_key_id',$_REQUEST['ticket_data_key_id'],'ticket_data_key',$data);
            if(isset($_REQUEST['butt_del'])){
                // deleting ticket data_key all together
                delete_from_db('ticket_data_key','ticket_data_key_id',$_REQUEST['ticket_data_key_id']);
                set_message('Ticket field deleted successfully.');
                redirect_browser($this->link_open_field(false));
            }
            set_message('Ticket field saved successfully');
            redirect_browser($this->link_open_field($ticket_data_key_id));
            
            
        }else if('save_ticket_account' == $_REQUEST['_process']){

            if(!module_config::can_i('edit','Settings')){
                die('No perms to save ticket settings.');
            }
            $ticket_account_id = update_insert('ticket_account_id',$_REQUEST['ticket_account_id'],'ticket_account',$_POST);
            if(isset($_REQUEST['butt_save_test'])){
                ?> <a href="<?php echo $this->link_open_account($ticket_account_id);?>">Return to account settings</a><br><br> <?php
                self::import_email($ticket_account_id,false,true);
                exit;
            }else if(isset($_REQUEST['butt_del'])){
                // deleting ticket account all together
                delete_from_db('ticket_account','ticket_account_id',$_REQUEST['ticket_account_id']);
                set_message('Ticket account deleted successfully.');
                redirect_browser($this->link_open_account(false));
            }
            set_message('Ticket account saved successfully');
            redirect_browser($this->link_open_account($ticket_account_id));

        }else if(isset($_REQUEST['butt_del']) && $_REQUEST['butt_del'] && $_REQUEST['ticket_id']){
            $data = self::get_ticket($_REQUEST['ticket_id']);
            if(module_form::confirm_delete('ticket_id',"Really delete ticket: ".$this->ticket_number($data['ticket_id']),self::link_open($_REQUEST['ticket_id']))){
                $this->delete_ticket($_REQUEST['ticket_id']);
                set_message("Ticket deleted successfully");
                redirect_browser($this->link_open(false));
            }
		}else if("save_ticket" == $_REQUEST['_process']){
            $this->_handle_save_ticket();


		}
		if(!count($errors)){
			redirect_browser($_REQUEST['_redirect']);
			exit;
		}
		print_error($errors,true);
	}


	public static function get_tickets($search=array(),$message_count=false){


        // work out what customers this user can access?
        $ticket_access = self::get_ticket_data_access();

        $sql = "SELECT t.* ";
        if($message_count){
            $sql .= ", COUNT(tm.ticket_message_id) AS message_count ";
        }
        $sql .= ", tt.`name` AS `type`";
        $from = " FROM `"._DB_PREFIX."ticket` t ";
        if($message_count){
            $from .= " LEFT JOIN `"._DB_PREFIX."ticket_message` tm ON t.ticket_id = tm.ticket_id ";
        }
        $from .= " LEFT JOIN `"._DB_PREFIX."ticket_type` tt ON t.ticket_type_id = tt.ticket_type_id";
		$where = " WHERE 1 ";
		if(isset($search['generic']) && $search['generic']){
			$str = mysql_real_escape_string($search['generic']);
			$where .= " AND ( ";
			$where .= " t.subject LIKE '%$str%' ";
			$where .= ' ) ';
		}
		if(isset($search['date_from']) && $search['date_from']){
			$str = strtotime(input_date($search['date_from']));
			$where .= " AND ( ";
			$where .= " t.last_message_timestamp >= '$str' ";
			$where .= ' ) ';
		}
		if(isset($search['date_to']) && $search['date_to']){
			$str = strtotime(input_date($search['date_to'].' 23:59:59',true));
			$where .= " AND ( ";
			$where .= " t.last_message_timestamp <= '$str' ";
			$where .= ' ) ';
		}
        if(isset($search['ticket_id'])){
            $search['ticket_id'] = trim(ltrim($search['ticket_id'],'0'));
        }
        /*if(isset($search['status_id']) && $search['status_id'] == -1){
            $where .= ' AND ( t.`status_id` = 2 OR t.`status_id` = 3 OR t.`status_id` = 5 ) ';
            unset($search['status_id']);
        }*/

        if(isset($search['status_id']) && strpos($search['status_id'],',') !== false){
            $where .= ' AND ( ';
            foreach(explode(',',$search['status_id']) as $s){
                $s = (int)trim($s);
                if(!$s)continue;
                $where .= ' t.`status_id` = '.$s.' OR ';
            }
            $where = rtrim($where,' OR ');
            $where .= ' ) ';
            unset($search['status_id']);
        }else if(isset($search['status_id']) && strpos($search['status_id'],'<') !== false){
            $search['status_id'] = ltrim($search['status_id'],'<');
            if((int)$search['status_id']>0){
                $where .= ' AND t.`status_id` < '.(int)$search['status_id'].' ';
            }
            unset($search['status_id']);
        }
        if(isset($search['contact']) && strlen(trim($search['contact']))){
            $search['contact'] = trim($search['contact']);
            $from .= " LEFT JOIN `"._DB_PREFIX."user` u ON t.user_id = u.user_id ";
            if(class_exists('module_envato',false)){
                $from .= " LEFT JOIN `"._DB_PREFIX."envato_ticket` et ON t.ticket_id = et.ticket_id ";
                $from .= " LEFT JOIN `"._DB_PREFIX."envato_author` ea ON et.envato_author_id = ea.envato_author_id ";
            }
            $where .= " AND ( ";
            $where .= " u.email LIKE '%".mysql_real_escape_string($search['contact'])."%' ";
            $where .= " OR u.name LIKE '%".mysql_real_escape_string($search['contact'])."%' ";
            if(class_exists('module_envato',false)){
                $where .= " OR ea.envato_username LIKE '%".mysql_real_escape_string($search['contact'])."%' ";
            }
            $where .= " )";
        }
        if(isset($search['envato_item_id']) && is_array($search['envato_item_id'])){
            // the new multi-select envato item id serach.
            $from .= " LEFT JOIN `"._DB_PREFIX."envato_ticket` et ON t.ticket_id = et.ticket_id ";
            $envato_item_where = '';
            foreach($search['envato_item_id'] as $envato_item_id){
                $envato_item_id = (int)$envato_item_id;
                if($envato_item_id>0){
                    $envato_item_where .= " et.envato_item_id = ".(int)$envato_item_id." OR ";
                }else if($envato_item_id==-1){
                    $envato_item_where .= " et.envato_item_id IS NULL OR ";
                }
            }
            if(strlen($envato_item_where)){
                $envato_item_where = rtrim($envato_item_where,' OR');
                $where .= " AND (".$envato_item_where.")";
            }
        }else if(isset($search['envato_item_id']) && strlen(trim($search['envato_item_id']))){
            $search['envato_item_id'] = (int)$search['envato_item_id'];
            $from .= " LEFT JOIN `"._DB_PREFIX."envato_ticket` et ON t.ticket_id = et.ticket_id ";
            $where .= " AND ( ";
            $where .= " et.envato_item_id = '".$search['envato_item_id']."'";
            $where .= " )";
        }
        if(isset($search['status_id']) && !$search['status_id']){
            unset($search['status_id']);//hack
        }
		foreach(array('user_id','assigned_user_id','customer_id','website_id','ticket_id','status_id','unread','ticket_type_id','priority','faq_product_id') as $key){
            if(isset($search[$key]) && $search[$key] !== ''&& $search[$key] !== false){
                $str = mysql_real_escape_string($search[$key]);
                $where .= " AND t.`$key` = '$str'";
            }
        }
        switch($ticket_access){
            case _TICKET_ACCESS_ALL:

                break;
            case _TICKET_ACCESS_ASSIGNED:
                // we only want tickets assigned to me.
                $where .= " AND t.assigned_user_id = '".(int)module_security::get_loggedin_id()."'";
                break;
            case _TICKET_ACCESS_CREATED:
                // we only want tickets i created.
                $where .= " AND t.user_id = '".(int)module_security::get_loggedin_id()."'";
                break;
            case _TICKET_ACCESS_CUSTOMER:
                $valid_customer_ids = module_security::get_customer_restrictions();
                if(is_array($valid_customer_ids) && count($valid_customer_ids)){
                    $where .= " AND ( ";
                    foreach($valid_customer_ids as $valid_customer_id){
                        $where .= " t.customer_id = '".(int)$valid_customer_id."' OR ";
                    }
                    $where = rtrim($where,'OR ');
                    $where .= " )";
                }
                break;
        }
        // want multiple options for ordering.
        switch(module_config::c('ticket_ordering','latest_message_last')){
            case 'unread_first':
                $group_order = ' GROUP BY t.ticket_id ORDER BY t.priority DESC, t.unread DESC, t.ticket_id ASC';
                break;
            case 'ticket_id':
                $group_order = ' GROUP BY t.ticket_id ORDER BY t.priority DESC, t.ticket_id ASC';
                break;
            case 'latest_message_last':
            default:
                $group_order = ' GROUP BY t.ticket_id ORDER BY t.priority DESC, t.last_message_timestamp ASC'; // t.unread DESC,
                break;
        }

		$sql = $sql . $from . $where . $group_order;
//        echo $sql;
		$result = query($sql);
		//module_security::filter_data_set("ticket",$result);
		return $result;
		//return get_multiple("ticket",$search,"ticket_id","fuzzy","last_message_timestamp DESC");

	}
    public static function get_ticket_messages($ticket_id){
		return get_multiple("ticket_message",array('ticket_id'=>$ticket_id),"ticket_message_id","exact","ticket_message_id",true);

	}
    public static function get_ticket_message($ticket_message_id){
		return get_single('ticket_message','ticket_message_id',$ticket_message_id);
	}
    public static function get_ticket_message_attachments($ticket_message_id){
		return get_multiple("ticket_message_attachment",array('ticket_message_id'=>$ticket_message_id),"ticket_message_attachment_id","exact","ticket_message_attachment_id");

	}
    public static function get_accounts(){
		return get_multiple("ticket_account",false,"ticket_account_id");

	}
    public static function get_accounts_rel(){
		$res = array();
        foreach(self::get_accounts() as $row){
            $res[$row['ticket_account_id']] = $row['name'];
        }
        return $res;
	}
    public static function get_ticket_staff(){
        $admins = module_user::get_users_by_permission(
            array(
                'category' => 'Ticket',
                'name' => 'Tickets',
                'module' => 'ticket',
                'edit' => 1,
            )

        );
        return $admins;
    }
    public static function get_ticket_staff_rel(){
        $admins = self::get_ticket_staff();
        $admins_rel = array();
        foreach($admins as $admin){
            $admins_rel[$admin['user_id']] = $admin['name'];
        }
        return $admins_rel;
    }
    public static function get_ticket_account($ticket_account_id){
		$ticket_account_id = (int)$ticket_account_id;
        $ticket_account = false;
        if($ticket_account_id>0){
		    $ticket_account = get_single("ticket_account","ticket_account_id",$ticket_account_id);
        }
        return $ticket_account;
	}
    private static $_ticket_cache=array();
	public static function get_ticket($ticket_id,$full=true){

        if(isset(self::$_ticket_cache[$ticket_id]))return self::$_ticket_cache[$ticket_id];
        $ticket_access = self::get_ticket_data_access();

        $ticket_id = (int)$ticket_id;
        $ticket = false;
        if($ticket_id>0){
		    //$ticket = get_single("ticket","ticket_id",$ticket_id);
            $sql = "SELECT * FROM `"._DB_PREFIX."ticket` t WHERE t.ticket_id = $ticket_id ";
            switch($ticket_access){
                case _TICKET_ACCESS_ALL:

                    break;
                case _TICKET_ACCESS_ASSIGNED:
                    // we only want tickets assigned to me.
                    $sql .= " AND t.assigned_user_id = '".(int)module_security::get_loggedin_id()."'";
                    break;
                case _TICKET_ACCESS_CREATED:
                    // we only want tickets I created.
                    $sql .= " AND t.user_id = '".(int)module_security::get_loggedin_id()."'";
                    break;
                case _TICKET_ACCESS_CUSTOMER:
                    $valid_customer_ids = module_security::get_customer_restrictions();
                    if(is_array($valid_customer_ids) && count($valid_customer_ids)){
                        $sql .= " AND ( ";
                        foreach($valid_customer_ids as $valid_customer_id){
                            $sql .= " t.customer_id = '".(int)$valid_customer_id."' OR ";
                        }
                        $sql = rtrim($sql,'OR ');
                        $sql .= " )";
                    }
                    break;
            }
            $ticket = qa1($sql, false);
        }
        if($full===2){
            return $ticket;
        }

        if(!$ticket){
            $customer_id = $website_id = 0;
            $user_id = module_security::get_loggedin_id();
            if(isset($_REQUEST['customer_id']) && $_REQUEST['customer_id']){
                //
                $customer_id = (int)$_REQUEST['customer_id'];
                $customer = module_customer::get_customer($customer_id);
                if(!$customer || $customer['customer_id']!=$customer_id){
                    $customer_id = 0;
                }else{
                    $user_id = $customer['primary_user_id'];
                }
                // find default website id to use.
                if(isset($_REQUEST['website_id'])){
                    $website_id = (int)$_REQUEST['website_id'];
                    $website = module_website::get_website($website_id);
                    if(!$website || $website['website_id']!=$website_id || $website['customer_id']!=$customer_id)$website_id = 0;
                }else{
                    $website_id = 0;
                }
            }
            $position = self::ticket_position();
            $ticket = array(
                'ticket_id' => 'new',
                'customer_id' => $customer_id,
                'website_id' => $website_id,
                'subject' => '',
                'date_completed' => '',
                'status_id'  => 2, // new
                'user_id'  => $user_id,
                'assigned_user_id'  => module_config::c('ticket_default_user_id',1), // who is the default assigned user?
                'ticket_account_id'  => module_config::c('ticket_default_account_id',0), // default pop3 account for pro users.
                'last_message_timestamp'  => 0,
                'last_ticket_message_id'  => 0,
                'message_count'  => 0,
                'position'  => $position['current'] + 1,
                'priority'  => 0, // 0, 1, 2, etc...
                'ticket_type_id'  => module_config::c('ticket_type_id_default',0),
                'total_pending' => $position['total'] + 1,
                'extra_data' => array(),
                'invoice_id' => false,
                'faq_product_id' => false,
            );

        }else{
            // find the position of this ticket
            // the position is determined by the number of pending tickets
            // that have a last_message_timestamp earlier than this ticket.

            $position = self::ticket_position($ticket_id);
            $ticket['position'] = $position['current'];
            $ticket['total_pending'] = $position['total'];

            /*if($ticket['priority'] == _TICKET_PRIORITY_STATUS_ID){
                $ticket['position'] = self::ticket_count('priority',$ticket['last_message_timestamp'],$ticket['ticket_id'],$ticket['priority']);
            }else{
                $ticket['position'] = self::ticket_count('pending',$ticket['last_message_timestamp'],$ticket['ticket_id'],$ticket['priority']);
            }
            $ticket['total_pending'] = self::ticket_count('pending');*/
            $messages = self::get_ticket_messages($ticket_id);
            $ticket['message_count'] = count($messages);
            end($messages);
            $last_message = current($messages);
            $ticket['last_ticket_message_id'] = $last_message['ticket_message_id'];
            // for passwords and website addresses..
            $ticket['extra_data'] = self::get_ticket_extras($ticket_id);

            // hook into the envato module.
            // link any missing envato/faqproduct items together.
            if(class_exists('module_envato',false) && isset($_REQUEST['faq_product_envato_hack']) && (!$ticket['faq_product_id'] || $ticket['faq_product_id'] == $_REQUEST['faq_product_envato_hack'])){
                $items = module_envato::get_items_by_ticket($ticket['ticket_id']);
                foreach($items as $envato_item_id => $item){
                    // see if this item is linked to a product.
                    if($item['envato_item_id']){
                        $sql = "SELECT * FROM `"._DB_PREFIX."faq_product` WHERE envato_item_ids REGEXP '[|]*".$envato_item_id."[|]*'";
                        $res = qa1($sql);
                        if($res&&$res['faq_product_id']){
                            // found a product matching this one. link her up.
                            update_insert('ticket_id',$ticket_id,'ticket',array('faq_product_id'=>$res['faq_product_id']));
                            break;
                        }
                    }
                }
            }

            self::$_ticket_cache[$ticket_id] = $ticket;
        }
		return $ticket;
	}


    public static function get_ticket_type($ticket_type_id=0){
        return get_single('ticket_type','ticket_type_id',$ticket_type_id);
    }
    public static function get_ticket_extras_key($ticket_data_key_id=0){
        return get_single('ticket_data_key','ticket_data_key_id',$ticket_data_key_id);
    }
    public static function get_ticket_extras_keys($ticket_account_id=0){
        //array('ticket_account_id'=>$ticket_account_id)
        return get_multiple('ticket_data_key',array(),'ticket_data_key_id','exact','order');
    }
    public static function get_ticket_extras($ticket_id){
        return get_multiple('ticket_data',array('ticket_id'=>$ticket_id),'ticket_data_key_id');
    }

    public static function mark_as_read($ticket_id,$credential_check=false){
        $ticket_id=(int)$ticket_id;
        if($ticket_id>0){
            /*if($credential_check){
                $admins_rel = self::get_ticket_staff_rel();
                // we check what the last message is.
                $messages = self::get_ticket_messages($ticket_id);
                end($messages);
                $last_message = current($messages);
                // if the last message is from an admin:
                if($last_message['']);
                // FUCK. this isn't going to work.
                // will do it later.
            }*/
            update_insert("ticket_id",$ticket_id,"ticket",array('unread'=>0));
        }
    }
    public static function mark_as_unread($ticket_id){
        $ticket_id=(int)$ticket_id;
        if($ticket_id>0){
            update_insert("ticket_id",$ticket_id,"ticket",array('unread'=>1));
        }
    }
	public function save_ticket($ticket_id,$data){
        if(isset($data['website_id']) && $data['website_id']){
            $website = module_website::get_website($data['website_id']);
            $data['customer_id'] = $website['customer_id'];
        }
        if(isset($data['user_id']) && $data['user_id']){
            $user = module_user::get_user($data['user_id'],false);
            if(!isset($data['customer_id'])||!$data['customer_id'])$data['customer_id'] = $user['customer_id'];
        }
        if(isset($data['change_assigned_user_id']) && (int)$data['change_assigned_user_id']>0){
            // check if we're realling changing the user.
            if($ticket_id>0){
                $existing_ticket_data = $this->get_ticket($ticket_id);
                if($existing_ticket_data['assigned_user_id'] != $data['change_assigned_user_id']){
                    // they are really changing the user
                    $data['assigned_user_id'] = $data['change_assigned_user_id'];
                }
            }else{
                $data['assigned_user_id'] = $data['change_assigned_user_id'];
            }
        }
		$ticket_id = update_insert("ticket_id",$ticket_id,"ticket",$data);
        if($ticket_id){

            // save any extra data
            if(isset($data['ticket_extra']) && is_array($data['ticket_extra'])){
                $available_extra_fields = $this->get_ticket_extras_keys();
                foreach($data['ticket_extra'] as $ticket_data_key_id => $ticket_data_key_value){
                    if(strlen($ticket_data_key_value)>1 && isset($available_extra_fields[$ticket_data_key_id])){
                        // save this one!
                        // hack: addition for encryption module.
                        // bit nasty, but it works.
                        if(class_exists('module_encrypt',false) && isset($available_extra_fields[$ticket_data_key_id]['encrypt_key_id']) && $available_extra_fields[$ticket_data_key_id]['encrypt_key_id'] && strpos($ticket_data_key_value,'encrypt:')===false
                         &&
                            ($available_extra_fields[$ticket_data_key_id]['type'] == 'text' || $available_extra_fields[$ticket_data_key_id]['type']=='textarea')
                        ){
                            // encrypt this value using this key.
                            $page_name = 'ticket_extras'; // match the page_name we have in ticket_extra_sidebar.php
                            $input_id = 'ticket_extras_'.$ticket_data_key_id; // match the input id we have in ticket_extra_sidebar.php
                            $ticket_data_key_value = module_encrypt::save_encrypt_value($available_extra_fields[$ticket_data_key_id]['encrypt_key_id'],$ticket_data_key_value,$page_name,$input_id);
                        }

                        // check for existing
                        $existing = get_single('ticket_data',array('ticket_id','ticket_data_key_id'),array($ticket_id,$ticket_data_key_id));
                        if($existing){
                            update_insert('ticket_data_id',$existing['ticket_data_id'],'ticket_data',array(
                                'value' => $ticket_data_key_value,
                            ));
                        }else{
                            update_insert('ticket_data_id','new','ticket_data',array(
                                'ticket_data_key_id' => $ticket_data_key_id,
                                'ticket_id' => $ticket_id,
                                'value' => $ticket_data_key_value,
                            ));
                        }
                    }
                }
            }

            if(isset($data['new_ticket_message']) && strlen($data['new_ticket_message']) > 1){
                // post a new reply to this message.
                // who are we replying to?


                $ticket_data = $this->get_ticket($ticket_id);

                if(isset($data['change_status_id']) && $data['change_status_id']){
                    update_insert("ticket_id",$ticket_id,"ticket",array('status_id'=>$data['change_status_id']));
                }else if ($ticket_data['status_id']==_TICKET_STATUS_RESOLVED_ID || $ticket_data['status_id'] == 7){
                    $data['change_status_id'] = 5; // change to in progress.
                }


                // it's either a reply from the admin, or from the user via the web interface.
                $ticket_data = $this->get_ticket($ticket_id);


                $logged_in_user = isset($data['force_logged_in_user_id']) ? $data['force_logged_in_user_id'] : false;
                if(!$logged_in_user){
                    $logged_in_user = module_security::get_loggedin_id();
                    if(!$logged_in_user){
                        $logged_in_user = $ticket_data['user_id'];
                    }
                }

                if(!$ticket_data['user_id'] && module_security::get_loggedin_id()){
                    update_insert('ticket_id',$ticket_id,'ticket',array('user_id' => module_security::get_loggedin_id()));
                    $ticket_data['user_id'] = module_security::get_loggedin_id();
                }
                $ticket_creator = $ticket_data['user_id'];
               // echo "creator: $ticket_creator logged in: $logged_in_user"; print_r($ticket_data);exit;
                //echo "Creator: ".$ticket_data['user_id'] . " logged in ".$logged_in_user;exit;
                if($ticket_creator == $logged_in_user){
                    // we are sending a reply back to the admin, from the end user.
                    self::mark_as_unread($ticket_id);
                    $ticket_message_id = $this->send_reply($ticket_id,$data['new_ticket_message'],$ticket_creator, $ticket_data['assigned_user_id'] ? $ticket_data['assigned_user_id'] : module_config::c('ticket_default_user_id',1), 'end_user');
                }else{
                    // we are sending a reply back to the ticket user.
                    // admin is allowed to change the status of a message.
                    $from_user_id = $ticket_data['assigned_user_id'] ? $ticket_data['assigned_user_id'] : module_security::get_loggedin_id();
                    //echo "From $from_user_id to $ticket_creator ";exit;
                    $ticket_message_id = $this->send_reply($ticket_id,$data['new_ticket_message'], $from_user_id, $ticket_creator, 'admin');
                }
            }

            if(isset($data['change_status_id']) && $data['change_status_id']){
                // we only update this status if the sent reply or send reply and next buttons are clicked.
                if(isset($_REQUEST['newmsg']) || isset($_REQUEST['newmsg_next'])){
                    update_insert("ticket_id",$ticket_id,"ticket",array('status_id'=>$data['change_status_id']));
                }
            }

        }
        module_extra::save_extras('ticket','ticket_id',$ticket_id);
		return $ticket_id;
	}

	public static function delete_ticket($ticket_id){
		$ticket_id=(int)$ticket_id;
		$sql = "DELETE FROM "._DB_PREFIX."ticket WHERE ticket_id = '".$ticket_id."' LIMIT 1";
		$res = query($sql);
		$sql = "DELETE FROM "._DB_PREFIX."ticket_message WHERE ticket_id = '".$ticket_id."'";
		$res = query($sql);
		$sql = "DELETE FROM "._DB_PREFIX."ticket_message_attachment WHERE ticket_id = '".$ticket_id."'";
		$res = query($sql);
        if(class_exists('module_group',false)){
            module_group::delete_member($ticket_id,'ticket');
        }

//		module_note::note_delete("ticket",$ticket_id);
//        module_extra::delete_extras('ticket','ticket_id',$ticket_id);
	}
    public function login_link($ticket_id){
        return module_security::generate_auto_login_link($ticket_id);
    }

    public function generate_priority_invoice($ticket_id){
        // call the invoice module and create an invoice for this ticket.
        // once this invoice is paid it will do a callback to the ticket.
        $ticket_data = $this->get_ticket($ticket_id);
        // check if no invoice exists.
        if(!$ticket_data['invoice_id']){
            $task_name = module_config::c('ticket_priority_invoice_task','Priority Support Ticket');
            $task_cost = module_config::c('ticket_priority_cost',10);
            $task_currency = module_config::c('ticket_priority_currency',1);

            $invoice_data = module_invoice::get_invoice('new',true);
            // todo - if the ticket customer_id changes (a feature for later on) then we have to update any of these invoices.
            // maybe it's best we don't have a customer_id here? hmmmmmmmmmmmmmmmmmm
            // the user will have to enter their own invoice details anyway.
            // maybe we can read the customer_id from the user table if there is no customer_id in the invoice table? that might fix some things.
            $invoice_data['customer_id'] = $ticket_data['customer_id'];
            $invoice_data['user_id'] = $ticket_data['user_id'];
            $invoice_data['currency_id'] = $task_currency;
            $invoice_data['date_sent'] = date('Y-m-d');
            $invoice_data['name'] = 'T'.$this->ticket_number($ticket_id);
            // pick a tax rate for this automatic invoice.
            //if(module_config::c('ticket_priority_tax_name','')){
                $invoice_data['total_tax_name'] = module_config::c('ticket_priority_tax_name','');
            //}
            //if(module_config::c('ticket_priority_tax_rate','')){
                $invoice_data['total_tax_rate'] = module_config::c('ticket_priority_tax_rate','');
            //}

            $invoice_data['invoice_invoice_item']=array(
                'new' => array(
                    'description' => $task_name. ' - '._l('Ticket #'.$this->ticket_number($ticket_id)),
                    'amount' => $task_cost,
                    'completed' => 1, // not needed?
                )
            );
            $invoice_id = module_invoice::save_invoice('new',$invoice_data);
            update_insert('ticket_id',$ticket_id,'ticket',array(
                'invoice_id'=>$invoice_id,
            ));
            module_invoice::add_history($invoice_id,'Created invoice from support ticket #'.$this->ticket_number($ticket_id));

            return $invoice_id;
        }

        return $ticket_data['invoice_id'];
    }

    public static function get_statuses(){
        return self::$ticket_statuses;
    }
    public static function get_types($only_public=false){

        //$sql = "SELECT `type` FROM `"._DB_PREFIX."ticket` GROUP BY `type` ORDER BY `type`";
        $sql = "SELECT * FROM `"._DB_PREFIX."ticket_type` tt";
        if($only_public){
            $sql.=" WHERE tt.`public` = 1 ";
        }
        $sql .= " ORDER BY tt.`name`";
        $statuses = array();
        foreach(qa($sql) as $r){
            $statuses[$r['ticket_type_id']] = $r['name'];
        }
        return $statuses;
    }


    public static function send_reply($ticket_id,$message,$from_user_id,$to_user_id, $reply_type = 'admin' , $internal_from = ''){


        // we also check if this message contains anything, or anything above the "reply line"
        // this is a hack to stop the autoreply loop that seems to happen when sending an email as yourself from  your envato profile.

        // stip out the text before our "--reply above this line-- bit.
        // copied code from ticket_admin_edit.php
        /*$reply__ine_default = '----- (Please reply above this line) -----'; // incase they change it
        $reply__ine =   module_config::s('ticket_reply_line',$reply__ine_default);
        $text = preg_replace("#<br[^>]*>#",'',$message);
        // convert to single text.
        $text = preg_replace('#\s+#imsU',' ',$text);
        if(
            preg_match('#^\s*'.preg_quote($reply__ine,'#').'.*#ims',$text) ||
            preg_match('#^\s*'.preg_quote($reply__ine_default,'#').'.*#ims',$text)
        ){
            // no content. don't send email
            //mail('dtbaker@gmail.com','ticket reply '.$ticket_id,'sending reply for text:\''.$text."' \n\n\n Original:\n".$message);
            return false;
        }*/



        // $message is in text format, need to nl2br it before printing.

        $ticket_number = self::ticket_number($ticket_id);
        $ticket_details = self::get_ticket($ticket_id);


        $to_user_a = module_user::get_user($to_user_id,false);
        $from_user_a = module_user::get_user($from_user_id,false);


        // we have to replace some special text within these messages. this is just a hack to support text in my autoreply.
        $replace = array(
            'name' => $to_user_a['name'],
            'ticket_url' => module_ticket::link_public($ticket_id),
            'ticket_url_cancel' => module_ticket::link_public_status($ticket_id,7),
            'ticket_url_resolved' => module_ticket::link_public_status($ticket_id,_TICKET_STATUS_RESOLVED_ID),
            'ticket_url_inprogress' => module_ticket::link_public_status($ticket_id,5),
            'faq_product_id' => $ticket_details['faq_product_id'],
        );
        foreach($replace as $key=>$val){
            $message = str_replace('{'.strtoupper($key).'}',$val,$message);
        }

        // the from details need to match the ticket account details.
        if($ticket_details['ticket_account_id']){
            $ticket_account = self::get_ticket_account($ticket_details['ticket_account_id']);
        }else{
            $ticket_account = false;
        }
        if($ticket_account && $ticket_account['email']){
            // want the user to reply to our ticketing system.
            $reply_to_address = $ticket_account['email'];
            $reply_to_name = $ticket_account['name'];
        }else{
            // reply to creator of the email.
            $reply_to_address = $from_user_a['email'];
            $reply_to_name = $from_user_a['name'];
        }

        $htmlmessage = '';
        if(self::is_text_html($message)){
            $htmlmessage = $message;
            $message = strip_tags($message);
        }


        $ticket_message_id = update_insert('ticket_message_id','new','ticket_message',array(
                                             'ticket_id' => $ticket_id,
                                             'content' => $message,
                                             'htmlcontent' => $htmlmessage,
                                             'message_time' => time(),
                                             'from_user_id' => $from_user_id,
                                             'to_user_id' => $to_user_id,
                                            'message_type_id' => ($reply_type == 'admin' ? _TICKET_MESSAGE_TYPE_ADMIN : _TICKET_MESSAGE_TYPE_CREATOR),
                                     ));
        if(!$ticket_message_id)return false;

        // handle any attachemnts.

        // are there any attachments?
        if($ticket_message_id && isset($_FILES['attachment']) && isset($_FILES['attachment']['tmp_name']) && is_array($_FILES['attachment']['tmp_name'])){
            foreach($_FILES['attachment']['tmp_name'] as $key => $val){
                if(is_uploaded_file($val)){
                    // save attachments against ticket!

                    $mime = dtbaker_mime_type($_FILES['attachment']['name'][$key], $val);

                    $attachment_id = update_insert('ticket_message_attachment_id','new','ticket_message_attachment',array(
                                             'ticket_id' => $ticket_id,
                                             'ticket_message_id' => $ticket_message_id,
                                             'file_name' => $_FILES['attachment']['name'][$key],
                                             'content_type' => $mime,
                                    ));
                    //echo getcwd();exit;
                    //ini_set('display_errors',true);
                    if(!move_uploaded_file($val, 'includes/plugin_ticket/attachments/'.$attachment_id.'')){
                        //echo 'error uploading file';exit;
                    }
                }
            }
        }


        if($internal_from != 'autoreply'){
            // stops them all having the same timestamp on a big import.
            update_insert('ticket_id',$ticket_id,'ticket',array(
                                         'last_message_timestamp' => time(),
                              ));
        }else{
            // we are sending an auto reply, flag this in the special cache field.
            // hacky!
            update_insert('ticket_message_id',$ticket_message_id,'ticket_message',array(
                     'cache'=>'autoreply',
             ));
        }
        //$reply_line = module_config::s('ticket_reply_line','----- (Please reply above this line) -----');

        $s = self::get_statuses();


        if($to_user_id == $ticket_details['user_id']){
            // WE ARE emailing the "User" from support.
            // so the support is emailing a response back to the customer.
            module_ticket::send_customer_alert($ticket_id,strlen($htmlmessage) ? $htmlmessage : $message,$ticket_message_id);

        }else{
            if(!self::is_text_html($message)){
                $message = nl2br(htmlspecialchars($message)); // because message is in text format, before we send admin notification do this.
            }
            module_ticket::send_admin_alert($ticket_id,$message);
        }


        if($reply_type == 'end_user' && (!$ticket_details['message_count'] || module_config::c('ticket_autoreply_every_message',0))){
            // this is the first message!
            // send an email back to the user confirming this submissions via the web interface.
            self::send_autoreply($ticket_id,$message);
        }

        return $ticket_message_id;

    }

    /**
     * Sends the customer an email telling them to use the online form to submit support tickets.
     *
     * @static
     * @param array $from_user
     * @param string $subject
     */
    public static function send_customer_rejection_alert($from_user, $subject){

        $template = module_template::get_template_by_key('ticket_rejection');
        $data = array(
            'subject' => $subject,
            'ticket_url' => module_config::c('ticket_public_submit_url','http://yoursite.com/support-tickets.html'),
        ) + $from_user;
        $template->assign_values($data);
        $content = $template->replace_content();

        $email = module_email::new_email();
        $email->set_to_manual($from_user['email'],$from_user['name']);
        $email->set_subject($template->description);
        foreach($data as $key=>$val){
            $email->replace($key,$val);
        }
        $email->send();
    }
    /**
     * Sends the customer an email letting them know the administrator has updated
     * their ticket with a new message.
     *
     * @static
     * @param $ticket_id
     * @param string $message
     */
    public static function send_customer_alert($ticket_id,$message='',$ticket_message_id=false){

        $ticket_details = self::get_ticket($ticket_id);
        $ticket_account_data = self::get_ticket_account($ticket_details['ticket_account_id']);
        $ticket_number = self::ticket_number($ticket_id);
        $s = self::get_statuses();
        $reply_line = module_config::s('ticket_reply_line','----- (Please reply above this line) -----');
        if(!$ticket_message_id){
            $ticket_message_id = $ticket_details['last_ticket_message_id'];
        }
        $last_ticket_message = self::get_ticket_message($ticket_message_id);
        if(!self::is_text_html($message)){
            $message = nl2br(htmlspecialchars($message));
        }
        if(!$message && $last_ticket_message){
            if($last_ticket_message['htmlcontent']){
                $message = trim($last_ticket_message['htmlcontent']);
            }else if($last_ticket_message['content']){
                $message = nl2br(htmlspecialchars($last_ticket_message['content']));
            }
        }

        $to_user_id = $last_ticket_message['to_user_id'];
        if(!$to_user_id)$to_user_id = $ticket_details['user_id']; // default to assigned user

        // bug fix! don't send a customer alert back to a staff member account.
        $staff_members = self::get_ticket_staff_rel();
        $ticket_accounts = self::get_accounts();
        $to_user_a = module_user::get_user($to_user_id,false);
        $sending_to_ticket_account = false;
        foreach($ticket_accounts as $ta){
            if(strlen($ta['email'])>0 && strtolower($ta['email']==strtolower($to_user_a['email']))){
                $sending_to_ticket_account = true;
            }
        }
        if($sending_to_ticket_account){
            send_error('Ticket '.$ticket_id.' error! Attempted to send a customer alert back to a ticket account email address. This would probably create a new ticket based on the customer auto-reply when the system sends it back. Please report this error to us if you believe it is wrong.');
            return false;
        }
        /*if(isset($staff_members[$to_user_id])){
            // we send 1 customer alert back to the staff member, but we check the last ticket message and don't send it if it's going to the same user again.
            if($last_ticket_message && isset($last_ticket_message['to_user_id']) && $last_ticket_message['to_user_id'] == $to_user_id){
                send_error('Ticket '.$ticket_id.' error! Attempted to send a customer alert back to a staff member. This could cause all sorts of problems. Please check this Customer Contact permissions, if they have TICKET EDIT permissions please turn this off and try again to see if that fixes the problem.');
                return false;
            }
        }*/


        $from_user_id = $last_ticket_message['from_user_id'];
        if(!$from_user_id)$from_user_id = $ticket_details['assigned_user_id']; // default to assigned staff member
        $from_user_a = module_user::get_user($from_user_id,false);

        if($ticket_details['ticket_account_id']){
            $ticket_account = self::get_ticket_account($ticket_details['ticket_account_id']);
        }else{
            $ticket_account = false;
        }
        if($ticket_account && $ticket_account['email']){
            // want the user to reply to our ticketing system.
            $reply_to_address = $ticket_account['email'];
            $reply_to_name = $ticket_account['name'];
        }else{
            // reply to creator of the email.
            $reply_to_address = $from_user_a['email'];
            $reply_to_name = $from_user_a['name'].(isset($from_user_a['last_name']) && $from_user_a['last_name']) ? ' '.$from_user_a['last_name'] : '';
        }

        $template = module_template::get_template_by_key('ticket_container');
        $template->assign_values(array(
            'ticket_number' => self::ticket_number($ticket_id),
            'ticket_status' => $s[$ticket_details['status_id']],
            'message' => $message,
            'subject' => $ticket_details['subject'],
            'position_current' => $ticket_details['position'],
            'position_all' => $ticket_details['total_pending'],
            'reply_line' => $reply_line,
            'days' => module_config::c('ticket_turn_around_days',5),
            'url' => self::link_public($ticket_id),
            'message_count' => $ticket_details['message_count'],
            'message_date_time' => date('l jS \of F Y h:i A'),

            'ticket_url_cancel' => module_ticket::link_public_status($ticket_id,7),
            'ticket_url_resolved' => module_ticket::link_public_status($ticket_id,_TICKET_STATUS_RESOLVED_ID),
            'ticket_url_inprogress' => module_ticket::link_public_status($ticket_id,5),

            'faq_product_id' => $ticket_details['faq_product_id'],
        ));
        $content = $template->replace_content();

        $email = module_email::new_email();
        $email->set_to('user',$to_user_id);
        if(module_config::c('ticket_from_creators_email',1)==1){
            $email->set_from('user',$from_user_id);
        }else{
            $email->set_from_manual($reply_to_address,$reply_to_name);
        }
        $email->set_reply_to($reply_to_address,$reply_to_name);
        $email->set_subject('[TICKET:'.$ticket_number.'] Re: '.$ticket_details['subject']);
        $email->set_html($content);
        // check attachments:
        $attachments = self::get_ticket_message_attachments($ticket_message_id);
        foreach($attachments as $attachment){
            $file_path = 'includes/plugin_ticket/attachments/'.$attachment['ticket_message_attachment_id'];
            $file_name = $attachment['file_name'];
            $email->AddAttachment($file_path,$file_name);
        }
        $email->send();
    }


    // send an alert to the admin letting them know there's a new ticket.
    public static function send_admin_alert($ticket_id,$message='') {
        $ticket_data = self::get_ticket($ticket_id);
        $ticket_account_data = self::get_ticket_account($ticket_data['ticket_account_id']);
        $ticket_number = self::ticket_number($ticket_id);
        if(!$message && $ticket_data['last_ticket_message_id']){
            $last_message = self::get_ticket_message($ticket_data['last_ticket_message_id']);
            $htmlmessage = trim($last_message['htmlcontent']);
            if($htmlmessage){
                $message = $htmlmessage;
            }else{
                $message = nl2br(htmlspecialchars(trim($last_message['content'])));
            }
        }
        $to = module_config::c('ticket_admin_email_alert',_ERROR_EMAIL);
        if(strlen($to)<4)return;
        // do we only send this on first emails or not ?
        $first_only = module_config::c('ticket_admin_alert_first_only',0);
        if($first_only && $ticket_data['message_count'] > 1)return;
        $s = self::get_statuses();
        $reply_line = module_config::s('ticket_reply_line','----- (Please reply above this line) -----');
        // autoreplies go back to the user - not our admin system:
        $from_user_a = module_user::get_user($ticket_data['user_id'],false);
        $reply_to_address = $from_user_a['email'];
        $reply_to_name = $from_user_a['name'];

        $template = module_template::get_template_by_key('ticket_admin_email');
        $template->assign_values(array(
            'ticket_number' => self::ticket_number($ticket_id),
            'ticket_status' => $s[$ticket_data['status_id']],
            'message' => $message,
            'subject' => $ticket_data['subject'],
            'position_current' => $ticket_data['position'],
            'position_all' => $ticket_data['total_pending'],
            'reply_line' => $reply_line,
            'days' => module_config::c('ticket_turn_around_days',5),
            'url' => self::link_public($ticket_id),
            'url_admin' => self::link_open($ticket_id),
            'message_count' => $ticket_data['message_count'],

            'ticket_url_cancel' => module_ticket::link_public_status($ticket_id,7),
            'ticket_url_resolved' => module_ticket::link_public_status($ticket_id,_TICKET_STATUS_RESOLVED_ID),
            'ticket_url_inprogress' => module_ticket::link_public_status($ticket_id,5),

            'faq_product_id' => $ticket_data['faq_product_id'],
        ));
        $content = $template->replace_content();

        $email = module_email::new_email();
        $email->set_to_manual($to);
        if($ticket_account_data && $ticket_account_data['email']){
            $email->set_from_manual($ticket_account_data['email'],$ticket_account_data['name']);
            $email->set_bounce_address($ticket_account_data['email']);
        }else{
            $email->set_from_manual($to, module_config::s('admin_system_name'));
            $email->set_bounce_address($to);
        }
        //$email->set_from('user',$from_user_id);
        //$email->set_from('foo','foo',$to,'Admin');
        // do we reply to the user who created this, or to our ticketing system?
        if(module_config::c('ticket_admin_alert_postback',1) && $ticket_account_data && $ticket_account_data['email']){
            $email->set_reply_to($ticket_account_data['email'],$ticket_account_data['name']);
        }else{
            $email->set_reply_to($reply_to_address,$reply_to_name);
        }
        $email->set_subject(sprintf(module_config::c('ticket_admin_alert_subject','Support Ticket Updated: [TICKET:%s]'),$ticket_number));
        $email->set_html($content);
        // check attachments:
        $attachments = self::get_ticket_message_attachments($ticket_data['last_ticket_message_id']);
        foreach($attachments as $attachment){
            $file_path = 'includes/plugin_ticket/attachments/'.$attachment['ticket_message_attachment_id'];
            $file_name = $attachment['file_name'];
            $email->AddAttachment($file_path,$file_name);
        }
        $email->send();
    }



    public static function send_autoreply($ticket_id,$userse_message='') {
        // send back an auto responder letting them know where they are in the queue.
        $ticket_data = self::get_ticket($ticket_id);

        $template = module_template::get_template_by_key('ticket_autoreply');
        $auto_reply_message = $template->content;
        $from_user_id = $ticket_data['assigned_user_id'] ? $ticket_data['assigned_user_id'] : module_config::c('ticket_default_user_id',1);
        //if($ticket_data['user_id'] != $from_user_id){
        // check if we have sent an autoreply to this address in the past 5 minutes, if we have we dont send another one.
        // this stops autoresponder spam messages.
        $time = time() - 300; // 5 mins
        $sql = "SELECT * FROM `"._DB_PREFIX."ticket_message` tm WHERE to_user_id = '".(int)$ticket_data['user_id']."' AND message_time > '".$time."' AND `cache` = 'autoreply'";
        $res = qa($sql);
        if(!count($res)){

            $send_autoreply = true;

            // other logic to check here???

            if($send_autoreply){
                self::send_reply($ticket_id,$auto_reply_message, $from_user_id, $ticket_data['user_id'], 'admin', 'autoreply');
            }
        }
        //}
    }

    public static function run_cron(){

        if(!function_exists('imap_open')){
            set_error('Please contact hosting provider and enable IMAP for PHP');
            echo 'Imap extension not available for php';
            return false;
        }

        include('cron/read_emails.php');
    }


    private function _subject_decode($str, $mode=0, $charset="UTF-8") {

        return iconv_mime_decode($str,ICONV_MIME_DECODE_CONTINUE_ON_ERROR,"UTF-8");

        $data = imap_mime_header_decode($str);
        if (count($data) > 0) {
          // because iconv doesn't like the 'default' for charset
          $charset = ($data[0]->charset == 'default') ? 'ASCII' : $data[0]->charset;
          return(iconv($charset, $charset, $data[0]->text));
        }
        return("");
     }


    public static function import_email($ticket_account_id,$import=true,$debug=false){


        require_once('includes/plugin_ticket/cron/rfc822_addresses.php');
        require_once('includes/plugin_ticket/cron/mime_parser.php');

        $admins_rel = self::get_ticket_staff_rel();
        $created_tickets = array();
        $ticket_account_id=(int)$ticket_account_id;
        $account = self::get_ticket_account($ticket_account_id);
        if(!$account)return false;
        $email_username = $account['username'];
        $email_password = $account['password'];
        $email_host = $account['host'];
        $email_port = $account['port'];
        $reply_from_user_id = $account['default_user_id'];
        $support_type = (int)$account['default_type'];
        $subject_regex = $account['subject_regex'];
        $body_regex = $account['body_regex'];
        $to_regex = $account['to_regex'];
        $search_string = $account['search_string'];
        $mailbox = $account['mailbox'];
        $imap = (int)$account['imap'];
        $secure = (int)$account['secure'];
        $start_date = ($account['start_date'] && $account['start_date'] != '0000-00-00') ? $account['start_date'] : false;


        if(!$email_host || !$email_username)return false;

        // try to connect with ssl first:
        $ssl = ($secure) ? '/ssl' : '';
        if($imap){
            $host = '{'.$email_host.':'.$email_port.'/imap'.$ssl.'/novalidate-cert}'.$mailbox;
            if($debug)echo "Connecting to $host <br>\n";
            $mbox = imap_open ($host, $email_username, $email_password);
        }else{
            $host = '{'.$email_host.':'.$email_port.'/pop3'.$ssl.'/novalidate-cert}'.$mailbox;
            if($debug)echo "Connecting to $host <br>\n";
            $mbox = imap_open ($host, $email_username, $email_password);
        }
        if(!$mbox){
            // todo: send email letting them know bounce checking failed?
            echo 'Failed to connect when checking for support ticket emails.'.imap_last_error();
            imap_errors();
            return false;
        }



        update_insert('ticket_account_id',$account['ticket_account_id'],'ticket_account',array(
                                             'last_checked' => time(),
                                         ));

        $MC = imap_check($mbox);
        //echo 'Connected'.$MC->Nmsgs;
        // do a search if
        $search_results = array(-1);
        if($imap && $search_string){
            //imap_sort($mbox,SORTARRIVAL,0);
            // we do a hack to support multiple searches in the imap string.
            if(strpos($search_string,'||')){
                $search_strings = explode('||',$search_string);
            }else{
                $search_strings = array($search_string);
            }
            $search_results = array();
            foreach($search_strings as $this_search_string){
                $this_search_string = trim($this_search_string);
                if(!$this_search_string){
                    return false;
                }
                if($debug)echo "Searching for $this_search_string <br>\n";
                $this_search_results = imap_search($mbox,$this_search_string);
                if($debug){
                    echo " -- found ".count($this_search_results)." results <br>\n";
                }
                $search_results = array_merge($search_results,$this_search_results);
            }
            if(!$search_results){
                echo "No search results for $search_string ";
                return false;
            }else{
                sort($search_results);
            }
        }
        imap_errors();
        //print_r($search_results);//imap_close($mbox);return false;
        $sorted_emails = array();
        foreach($search_results as $search_result){

            if($search_result>=0){
                $result = imap_fetch_overview($mbox,$search_result,0);
            }else{
                //$result = imap_fetch_overview($mbox,"1:100",0);
                $result = imap_fetch_overview($mbox,"1:". min(100,$MC->Nmsgs),0);
            }
            foreach ($result as $overview) {


                if(!isset($overview->subject) && !$overview->date)continue;
                $overview->subject = self::_subject_decode(isset($overview->subject) ? (string)$overview->subject : '');

                if($subject_regex && !preg_match($subject_regex,$overview->subject)){
                    continue;
                }
                if(!isset($overview->date))$overview->date = date('Y-m-d H:i:s');
                if($start_date > 1000){
                    if(strtotime($overview->date) < strtotime($start_date)){
                        continue;
                    }
                }

                $message_id = isset($overview->message_id) ? (string)$overview->message_id : false;
                if(!$message_id){
                    $overview->message_id = $message_id = md5($overview->subject . $overview->date);
                }

                //echo "#{$overview->msgno} ({$overview->date}) - From: {$overview->from} <br> {$this_subject} <br>\n";
                // check this email hasn't been processed before.
                // check this message hasn't been processed yet.
                $ticket = get_single('ticket_message','message_id',$message_id);
                if($ticket){
                    continue;
                }

                // get ready to sort them.
                $overview->time = strtotime($overview->date);
                $sorted_emails [] = $overview;
            }
        }
        if(!function_exists('dtbaker_ticket_import_sort')){
            function dtbaker_ticket_import_sort($a,$b){
                return $a->time > $b->time;
            }
        }
        uasort($sorted_emails,'dtbaker_ticket_import_sort');
        $message_number = 0;
        foreach($sorted_emails as $overview){
                $message_number++;

                $message_id = (string)$overview->message_id;

                if($debug){
                    ?>
                        <div style="padding:5px; border:1px solid #EFEFEF; margin:4px;">
                            <div>
                                <strong><?php echo $message_number;?></strong>
                                Date: <strong><?php echo $overview->date;?></strong> <br/>
                                Subject: <strong><?php echo htmlspecialchars($overview->subject);?></strong> <br/>
                                From: <strong><?php echo htmlspecialchars($overview->from);?></strong>
                                To: <strong><?php echo htmlspecialchars($overview->to);?></strong>
                                <!-- <a href="#" onclick="document.getElementById('msg_<?php echo $message_number;?>').style.display='block'; return false;">view body</a>
                            </div>
                            <div style="display:none; padding:10px; border:1px solid #CCC;" id="msg_<?php echo $message_number;?>">
                                <?php
                                // echo htmlspecialchars($results['Data']);
                                ?> -->
                            </div>
                        </div>
                    <?php
                }
                if(!$import){
                    continue;
                }

                $tmp_file = tempnam(_UCM_FOLDER.'/temp/','ticket');
                imap_savebody  ($mbox, $tmp_file, $overview->msgno);
                $mail_content = file_get_contents($tmp_file);


                $mime=new mime_parser_class();
                $mime->mbox = 0;
                $mime->decode_bodies = 1;
                $mime->ignore_syntax_errors = 1;
                $parameters=array(
                    //'File'=>$mailfile,
                    'Data'=>$mail_content,
                    //'SaveBody'=>'/tmp',
                    //'SkipBody'=>0,
                );

                $parse_success = false;
                if(!$mime->Decode($parameters, $decoded)){
                    echo 'MIME message decoding error: '.$mime->error.' at position '.$mime->error_position."\n";
                    // TODO - send warning email to admin.
                    send_error("Failed to decode this email: ".$mail_content);
                    $parse_success = false;
                }else{
                    for($message = 0; $message < count($decoded); $message++){
                        if($mime->Analyze($decoded[$message], $results)){

                            if(isset($results['From'][0]['address'])){
                                $from_address = $results['From'][0]['address'];
                            }else{
                                continue;
                            }


                            if($to_regex){
                                $to_match = false;
                                foreach($results['To'] as $possible_to_address){
                                    if(preg_match($to_regex,$possible_to_address['address'])){
                                        $to_match = true;
                                    }
                                }
                                if(!$to_match){
                                    continue;
                                }
                            }

                            // find out which accout this sender is from.
                            if(preg_match('/@(.*)$/',$from_address,$matches)){



                                // run a hook now to parse the from address.


                                $domain = $matches[1];

                                // find this sender in the database.
                                // if we cant find this sender/customer in the database
                                // then we add this customer as a "support user" to the default customer for this ticketing system.
                                // based on the "to" address of this message.



                                //store this as an eamil
                                $email_to = '';
                                $email_to_first = current($results['To']);
                                if($email_to_first){
                                    $email_to = $email_to_first['address'];
                                }

                                // work out the from and to users.
                                $from_user_id = 0; // this becomes the "user_id" field in the ticket table.
                                $to_user_id = 0; // this is admin. leave blank for now i guess.
                                // try to find a user based on this from email address.
                                $sql = "SELECT * FROM `"._DB_PREFIX."user` u WHERE u.`email` LIKE '".mysql_real_escape_string($from_address)."' ORDER BY `date_created` DESC";
                                $from_user = qa1($sql);
                                // todo! this user may be in the system twice!
                                // eg: once from submitting a ticket - then again when creating that user as a contact under a different customer.
                                // so we find the latest entry and use that... ^^ done! updated the above to sort by date updated.
                                if($from_user){
                                    $from_user_id = $from_user['user_id'];
                                    // woo!!found a user. assign this customer to the ticket.
                                    if($from_user['customer_id']){
                                        $account['default_customer_id'] = $from_user['customer_id'];
                                    }

                                }else{
                                    // create a user under this account customer.
                                    if($account['default_customer_id']){
                                        // create a new support user! go go!
                                        $from_user = array(
                                            'name' => isset($results['From'][0]['name']) ? $results['From'][0]['name'] : $from_address,
                                            'customer_id' => $account['default_customer_id'],
                                            'email' => $from_address,
                                            'status_id' => 1,
                                            'password' => substr(md5(time().mt_rand(0,600)),3),
                                        );
                                        global $plugins;
                                        $from_user_id = $plugins['user']->create_user($from_user,'support');
                                    }else{
                                        $from_user = array(
                                            'name' => isset($results['From'][0]['name']) ? $results['From'][0]['name'] : $from_address,
                                            'customer_id' => -1, // instead of 0, use -1.
                                            'email' => $from_address,
                                            'status_id' => 1,
                                            'password' => substr(md5(time().mt_rand(0,600)),3),
                                        );
                                        global $plugins;
                                        $from_user_id = $plugins['user']->create_user($from_user,'support');
                                        //echo 'Failed - no from account set';
                                        //continue;
                                    }
                                }

                                if(!$from_user_id){
                                    echo 'Failed - cannot find the from user id';
                                    echo $from_address . ' to '.var_export($results['To'],true).' : subject: '.$overview->subject.'<hr>';
                                    continue;
                                }
                                $sql = "SELECT * FROM `"._DB_PREFIX."user` u WHERE u.`email` LIKE '".mysql_real_escape_string($email_to)."'";
                                $to_user_temp = qa1($sql);
                                if($to_user_temp){
                                    $to_user_id = $to_user_temp['user_id'];
                                    // woo!!
                                }

                                $message_type_id = _TICKET_MESSAGE_TYPE_CREATOR; // from an end user.
                                if(isset($admins_rel[$from_user_id])){
                                    $message_type_id = _TICKET_MESSAGE_TYPE_ADMIN; // from an admin replying via email.
                                }
                                $ticket_id = false;
                                $new_message = true;
                                // check if the subject matches an existing ticket subject.
                                if(preg_match('#\[TICKET:(\d+)\]#i',$overview->subject,$subject_matches)){
                                    // found an existing ticket.
                                    // find this ticket in the system.
                                    $ticket_id = ltrim($subject_matches[1],'0');
                                    // see if it exists.
                                    $existing_ticket = get_single('ticket','ticket_id',$ticket_id);
                                    if($existing_ticket){
                                        // woot!
                                        // todo - check the from/to email address is correct as well.
                                        // meh.
                                        update_insert('ticket_id',$ticket_id,'ticket',array(
                                                      'status_id' => 5,// change status to in progress.
                                                      'last_message_timestamp' => strtotime($overview->date),
                                                   ));
                                        $new_message = false;
                                    }else{
                                        // fail..
                                        $ticket_id = false;
                                    }
                                }else{
                                    // we search for this subject, and this sender, to see if they have sent a follow up
                                    // before we started the ticketing system.
                                    // handy for importing an existing inbox with replies etc..

                                    // check to see if the subject matches any existing subjects.
                                    $search_subject1 = trim(preg_replace('#^Re:?\s*#i','',$overview->subject));
                                    $search_subject2 = trim(preg_replace('#^Fwd?:?\s*#i','',$overview->subject));
                                    $search_subject3 = trim($overview->subject);
                                    // find any threads that match this subject, from this user id.
                                    $sql = "SELECT * FROM `"._DB_PREFIX."ticket` t ";
                                    $sql .= " WHERE t.`user_id` = ".(int)$from_user_id." ";
                                    $sql .= " AND ( t.`subject` LIKE '%".mysql_real_escape_string($search_subject1)."%' OR ";
                                    $sql .= " t.`subject` LIKE '%".mysql_real_escape_string($search_subject2)."%' OR ";
                                    $sql .= " t.`subject` LIKE '%".mysql_real_escape_string($search_subject3)."%') ";
                                    $sql .= " ORDER BY ticket_id DESC;";
                                    $match = qa1($sql);
                                    if(count($match) && (int)$match['ticket_id'] > 0){
                                        // found a matching email. stoked!
                                        // add it in as a reply from the end user.
                                        $ticket_id = $match['ticket_id'];
                                        update_insert('ticket_id',$ticket_id,'ticket',array(
                                                      'status_id' => 5,// change status to in progress.
                                                      'last_message_timestamp' => strtotime($overview->date),
                                                   ));
                                        $new_message = false;

                                    }

                                    if(!$ticket_id){
                                        // now we see if any match the "TO" address, ie: it's us replying to the user.
                                        // handly from a gmail import.
                                        if($email_to){
                                            $sql = "SELECT * FROM `"._DB_PREFIX."user` u WHERE u.`email` LIKE '".mysql_real_escape_string($email_to)."'";
                                            $temp_to_user = qa1($sql);
                                            if($temp_to_user && $temp_to_user['user_id']){
                                                // we have sent emails to this user before...
                                                // check to see if the subject matches any existing subjects.

                                                $sql = "SELECT * FROM `"._DB_PREFIX."ticket` t ";
                                                $sql .= " WHERE t.`user_id` = ".(int)$temp_to_user['user_id']." ";
                                                $sql .= " AND ( t.`subject` LIKE '%".mysql_real_escape_string($search_subject1)."%' OR ";
                                                $sql .= " t.`subject` LIKE '%".mysql_real_escape_string($search_subject2)."%' OR ";
                                                $sql .= " t.`subject` LIKE '%".mysql_real_escape_string($search_subject3)."%') ";
                                                $sql .= " ORDER BY ticket_id DESC;";
                                                $match = qa1($sql);
                                                if(count($match) && (int)$match['ticket_id'] > 0){
                                                    // found a matching email. stoked!
                                                    // add it in as a reply from the end user.
                                                    $ticket_id = $match['ticket_id'];
                                                    update_insert('ticket_id',$ticket_id,'ticket',array(
                                                                  'status_id' => 5,// change status to in progress.
                                                                  'last_message_timestamp' => strtotime($overview->date),
                                                               ));
                                                    $new_message = false;

                                                }
                                            }
                                        }
                                    }
                                }


                                if(!$ticket_id){
                                    // creating a new ticket for this email.
                                    // new option to ignore these emails and force people to submit new tickets via the web interface
                                    if(!module_config::c('ticket_allow_new_from_email',1)){
                                        // todo: do the same as this above when we're creating new user accounts etc...
                                        // dont create a new user account if this option is disabled
                                        // send an autoreply to this user saying that their ticket was not created.

                                        module_ticket::send_customer_rejection_alert($from_user,$overview->subject);
                                        echo 'Rejecting new tickets';
                                        $parse_success = true;
                                        continue;
                                    }
                                    $ticket_id = update_insert('ticket_id','new','ticket',array(
                                                      'subject' => $overview->subject,
                                                      'ticket_account_id' => $account['ticket_account_id'],
                                                      'status_id' => 2, // new !
                                                      'user_id' => $from_user_id,
                                                      'assigned_user_id'=>$reply_from_user_id,
                                                      'customer_id' => $from_user['customer_id'],
                                                      'ticket_type_id' => $support_type,
                                                      'last_message_timestamp' => strtotime($overview->date),
                                                   ));
                                }

                                if(!$ticket_id){
                                    echo 'Error creating ticket';
                                    continue;
                                }
                                module_ticket::mark_as_unread($ticket_id);

                                $cache = array(
                                    'from_email' =>  $from_address,
                                    'to_email' => $email_to,
                                );

                                // pull otu the email bodyu.
                                $body = $results['Data'];
                                if($results['Type']=="html"){
                                    $is_html = true;
                                }else{
                                    // convert body to html, so we can do wrap.
                                    $body = nl2br($body);
                                    $is_html = true;
                                }
                                // find the alt body.
                                $altbody = '';
                                if(isset($results['Alternative']) && is_array($results['Alternative'])){
                                    foreach($results['Alternative'] as $alt_id => $alt){
                                        if($alt['Type']=="text"){
                                            $altbody = $alt['Data'];
                                            break;
                                        }
                                    }
                                }

                                if(!$altbody){
                                    // should really never happen, but who knows.
                                    // edit - i think this happens with godaddy webmailer.
                                    $altbody = $body; // todo: strip any html.
                                    $altbody = preg_replace('#<br[^>]*>\n*#imsU',"\n",$altbody);
                                    $altbody = strip_tags($altbody);
                                }



                                // pass the body and altbody through a hook so we can modify it if needed.
                                // eg: for envato tickets we strip the header/footer out and check the link to see if the buyer really bought anything.
                                // run_hook(...

                                //echo "<hr>$body<hr>$altbody<hr><br><br><br>";
                                // save the message!
                                $ticket_message_id = update_insert('ticket_message_id','new','ticket_message',array(
                                                                     'ticket_id' => $ticket_id,
                                                                      'message_id' => $message_id,
                                                                     'content' => $altbody,
                                                                     // save html content later on.
                                                                     'htmlcontent' => $body,
                                                                     'message_time' => strtotime($overview->date),
                                                                     'message_type_id' => $message_type_id, // from a support user.
                                                                     'from_user_id' => $from_user_id,
                                                                     'to_user_id' => $to_user_id,
                                                                      'cache' => serialize($cache),
                                ));

                                if(isset($results['Related'])){
                                    foreach($results['Related'] as $related){
                                        if(isset($related['FileName']) && $related['FileName']){
                                            // save as attachment against this email.
                                            $attachment_id = update_insert('ticket_message_attachment_id','new','ticket_message_attachment',array(
                                                                                                             'ticket_id' => $ticket_id,
                                                                                                             'ticket_message_id' => $ticket_message_id,
                                                                                                             'file_name' => $related['FileName'],
                                                                                                             'content_type' => $related['Type'].(isset($related['SubType']) ? '/'.$related['SubType'] : ''),
                                                                                                                                            ));
                                            file_put_contents('includes/plugin_ticket/attachments/'.$attachment_id.'',$related['Data']);
                                        }
                                    }
                                }
                                if(isset($results['Attachments'])){
                                    foreach($results['Attachments'] as $related){
                                        if(isset($related['FileName']) && $related['FileName']){
                                            // save as attachment against this email.
                                            $attachment_id = update_insert('ticket_message_attachment_id','new','ticket_message_attachment',array(
                                                                                                             'ticket_id' => $ticket_id,
                                                                                                             'ticket_message_id' => $ticket_message_id,
                                                                                                             'file_name' => $related['FileName'],
                                                                                                             'content_type' => $related['Type'].(isset($related['SubType']) ? '/'.$related['SubType'] : ''),
                                                                                                                                            ));
                                            file_put_contents('includes/plugin_ticket/attachments/'.$attachment_id.'',$related['Data']);
                                        }
                                    }
                                }

                                //$new_message &&
                                if(!preg_match('#failure notice#i',$overview->subject)){

                                    // we don't sent ticket autoresponders when the from user and to user are teh same
                                    if($from_user_id && $to_user_id && $from_user_id == $to_user_id){

                                    }else{
                                        $created_tickets [] = $ticket_id;
                                    }

                                }

                                $parse_success = true;

                            }
                        }
                    }
                }

                if($parse_success && $account['delete']){
                    // remove email from inbox if needed.
                    imap_delete($mbox, $overview->msgno);
                }

                unlink($tmp_file);
            }

        imap_errors();
        //}
        imap_expunge($mbox);
        imap_close($mbox);
        imap_errors();

        return $created_tickets;

    }

    public static function get_saved_responses() {
        // we use the extra module for saving canned responses for now.
        // why not? meh - use a new table later when we start with a FAQ system.
        $extra_fields = module_extra::get_extras(array('owner_table'=>'ticket_responses','owner_id'=>1));

        $responses = array();
        foreach($extra_fields as $extra){
            $responses[$extra['extra_id']] = $extra['extra_key'];
        }
        return $responses;
    }
    public static function get_saved_response($saved_response_id) {
        // we use the extra module for saving canned responses for now.
        // why not? meh - use a new table later when we start with a FAQ system.
        $extra = module_extra::get_extra($saved_response_id);
        return array(
            'saved_response_id' => $extra['extra_id'],
            'name' => $extra['extra_key'],
            'value' => $extra['extra'],
        );
    }
    public static function save_saved_response($saved_response_id,$data) {
        // we use the extra module for saving canned responses for now.
        // why not? meh - use a new table later when we start with a FAQ system.
        $extra_db = array(
            'extra' => $data['value'],
            'owner_table' => 'ticket_responses',
            'owner_id' => 1,
        );
        if(isset($data['name'])&&$data['name']){
            $extra_db['extra_key'] = $data['name'];
        }else if(!(int)$saved_response_id){
            return; // not saving correctly.
        }
        $extra_id = update_insert('extra_id',$saved_response_id,'extra',$extra_db);
        return $extra_id;
    }

    public static function get_ticket_data_access() {
        if(class_exists('module_security',false)){
            return module_security::can_user_with_options(module_security::get_loggedin_id(),'Ticket Access',array(
                                                                                                   _TICKET_ACCESS_ALL,
                                                                                                   _TICKET_ACCESS_ASSIGNED,
                                                                                                   _TICKET_ACCESS_CREATED,
                                                                                                   _TICKET_ACCESS_CUSTOMER,
                                                                                                                       ));
        }else{
            return _TICKET_ACCESS_ALL;
        }
    }

    public static function get_ticket_priorities(){
        $s = array(
            0 => _l('Normal'),
            1 => _l('Medium'),
            2 => _l('High'),
        );
        if(module_config::c('ticket_allow_priority',0)){
            $s[_TICKET_PRIORITY_STATUS_ID] = _l('Paid');
        }
        return $s;
    }

    /**
     * @static
     * @param $ticket_id
     * @return array
     *
     * return a ticket recipient ready for sending a newsletter based on the ticket id.
     *
     */
    public static function get_newsletter_recipient($ticket_id) {
        $ticket = self::get_ticket($ticket_id);
        if(!$ticket || !(int)$ticket['ticket_id'])return false; // doesn't exist any more
        // some other details the newsletter system might need.
        $contact = module_user::get_user($ticket['user_id'],false);
        $name_parts = explode(" ",preg_replace('/\s+/',' ',$contact['name']));
        $ticket['first_name'] = array_shift($name_parts);
        $ticket['last_name'] = implode(' ',$name_parts);
        $ticket['email'] = $contact['email'];
        $ticket['public_link'] = self::link_public($ticket_id);
        $ticket['ticket_number'] = self::ticket_number($ticket_id);
        $ticket['ticket_subject'] = $ticket['subject'];
        unset($ticket['subject']);
        if($ticket['status_id'] == 2 || $ticket['status_id'] == 3 || $ticket['status_id'] == 5){
            $ticket['pending_status'] = _l('%s out of %s tickets',ordinal($ticket['position']),$ticket['total_pending']);
        }else{
            $ticket['pending_status'] = 'ticket completed';
        }
        $ticket['_edit_link'] = self::link_open($ticket_id,false,$ticket);
        return $ticket;
    }

    private function _handle_save_ticket() {

        $ticket_data = $_POST;
        $ticket_id = (int)$_REQUEST['ticket_id'];
        // check security can user edit this ticket
        if($ticket_id>0){
            $test = self::get_ticket($ticket_id);
            if(!$test || $test['ticket_id'] != $ticket_id){
                $ticket_id = 0;
            }
        }
        // handle some security before passing if off to the save
        if(!self::can_edit_tickets()){
            // dont allow new "types" to be created
            /*if(isset($ticket_data['type']) && $ticket_data['type']){
                $types = self::get_types();
                $existing=false;
                foreach($types as $type){
                    if($type==$ticket_data['type']){
                        $existing=true;
                    }
                }
                if(!$existing){
                    unset($ticket_data['type']);
                }
            }*/
            if(isset($ticket_data['change_customer_id']))unset($ticket_data['change_customer_id']);
            if(isset($ticket_data['change_user_id']))unset($ticket_data['change_user_id']);
            if(isset($ticket_data['ticket_account_id']))unset($ticket_data['ticket_account_id']);
            if(isset($ticket_data['assigned_user_id']))unset($ticket_data['assigned_user_id']);
            if(isset($ticket_data['change_status_id']))unset($ticket_data['change_status_id']);
            if(isset($ticket_data['change_assigned_user_id']))unset($ticket_data['change_assigned_user_id']);
            if(isset($ticket_data['priority']))unset($ticket_data['priority']);
            if($ticket_id>0 && isset($ticket_data['status_id']))unset($ticket_data['status_id']);
            if($ticket_id>0 && isset($ticket_data['user_id']))unset($ticket_data['user_id']);
        }
        $ticket_data = array_merge(self::get_ticket($ticket_id), $ticket_data);
        if(isset($_REQUEST['mark_as_unread']) && $_REQUEST['mark_as_unread']){
            $ticket_data['unread'] = 1;
        }
        if(isset($ticket_data['change_customer_id']) && (int)$ticket_data['change_customer_id']>0 && $ticket_data['change_customer_id'] != $ticket_data['customer_id']){
            // we are changing customer ids
            // todo - some extra logic in here to swap the user contact over to this new customer or something?
            $ticket_data['customer_id'] = $ticket_data['change_customer_id'];
        }
        if(isset($ticket_data['change_user_id']) && (int)$ticket_data['change_user_id']>0 && $ticket_data['change_user_id'] != $ticket_data['user_id']){
            // we are changing customer ids
            // todo - some extra logic in here to swap the user contact over to this new customer or something?
            $ticket_data['user_id'] = $ticket_data['change_user_id'];
        }
        $ticket_id = $this->save_ticket($ticket_id,$ticket_data);

        // run the envato hook incase we're posting data to our sidebar bit.
        ob_start();
        handle_hook('ticket_sidebar',$ticket_id);
        ob_end_clean();

        if(isset($_REQUEST['generate_priority_invoice'])){
            $invoice_id = $this->generate_priority_invoice($ticket_id);
            redirect_browser(module_invoice::link_public($invoice_id));
        }

        set_message("Ticket saved successfully");
        if(isset($_REQUEST['butt_notify_staff']) && $_REQUEST['butt_notify_staff']){
            redirect_browser($this->link_open_notify($ticket_id,false,$ticket_data));
        }else if(isset($_REQUEST['mark_as_unread']) && $_REQUEST['mark_as_unread']){
            redirect_browser($this->link_open(false));
        }else{
            if(isset($_REQUEST['newmsg_next']) && isset($_REQUEST['next_ticket_id']) && (int)$_REQUEST['next_ticket_id']>0){
                redirect_browser($this->link_open($_REQUEST['next_ticket_id']));
            }
            redirect_browser($this->link_open($ticket_id));
        }
    }

    public static function get_total_ticket_count($customer_id=false) {
        $ticket_count = module_cache::time_get('ticket_total_count'.$customer_id);
        if($ticket_count===false){
            if($customer_id>0){
                $tickets = self::get_tickets(array('customer_id'=>$customer_id,'status_id' => '2,3,5')); //,'status_id'=>-1
            }else{
                $tickets = self::get_tickets(array('status_id' => '2,3,5'));
            }
            $ticket_count = mysql_num_rows($tickets);
            module_cache::time_save('ticket_total_count'.$customer_id,$ticket_count);
        }
        return $ticket_count;
    }
    public static function get_unread_ticket_count() {
        $ticket_count = module_cache::time_get('ticket_unread_count');
        if($ticket_count===false){
            $res = self::get_tickets(array(
                'unread'=>1,
                'status_id'=>'<'._TICKET_STATUS_RESOLVED_ID,
            ));

            /*$sql = "SELECT * FROM `"._DB_PREFIX."ticket` t WHERE t.unread = 1 AND t.status_id < 6 ";
            // work out what customers this user can access?
            $ticket_access = self::get_ticket_data_access();
            switch($ticket_access){
                case _TICKET_ACCESS_ALL:

                    break;
                case _TICKET_ACCESS_ASSIGNED:
                    // we only want tickets assigned to me.
                    $sql .= " AND t.assigned_user_id = '".(int)module_security::get_loggedin_id()."'";
                    break;
                case _TICKET_ACCESS_CREATED:
                    // we only want tickets I created.
                    $sql .= " AND t.user_id = '".(int)module_security::get_loggedin_id()."'";
                    break;
            }
            $res = query($sql);*/
            $ticket_count = mysql_num_rows($res);
            module_cache::time_save('ticket_unread_count',$ticket_count);
        }
        return $ticket_count;
    }
    public static function get_priority_ticket_count($faq_product_id = false) {
        $foo = module_cache::time_get('ticket_priority_count_ur'.($faq_product_id!==false?$faq_product_id:''));
        if($foo===false){
            $search = array(
                'priority'=>_TICKET_PRIORITY_STATUS_ID,
                'status_id'=>'<'._TICKET_STATUS_RESOLVED_ID,
            );
            if($faq_product_id !== false && module_config::c('ticket_separate_product_queue',0)){
                $search['faq_product_id'] = $faq_product_id;
            }
            $res = self::get_tickets($search);
            /*
            $sql = "SELECT * FROM `"._DB_PREFIX."ticket` t WHERE t.priority = "._TICKET_PRIORITY_STATUS_ID." AND t.status_id < 6 ";
            // work out what customers this user can access?
            $ticket_access = self::get_ticket_data_access();
            switch($ticket_access){
                case _TICKET_ACCESS_ALL:

                    break;
                case _TICKET_ACCESS_ASSIGNED:
                    // we only want tickets assigned to me.
                    $sql .= " AND t.assigned_user_id = '".(int)module_security::get_loggedin_id()."'";
                    break;
                case _TICKET_ACCESS_CREATED:
                    // we only want tickets I created.
                    $sql .= " AND t.user_id = '".(int)module_security::get_loggedin_id()."'";
                    break;
            }
            $res = query($sql);*/
            $ticket_count = mysql_num_rows($res);
            $unread = 0;
            while($ticket = mysql_fetch_assoc($res)){
                if($ticket['unread'])$unread++;
            }
            $foo = array($ticket_count,$unread);
            module_cache::time_save('ticket_priority_count_ur'.($faq_product_id!==false?$faq_product_id:''),$foo);
        }
        return $foo;
    }

    public static function hook_customer_contact_moved($callback,$user_id,$old_customer_id,$customer_id){
        // $user_id has been moved from $old_customer_id to $customer_id
        // find all support tickets with a user_id / old_customer_id and update them to new customer id
        $sql = "UPDATE `"._DB_PREFIX."ticket` SET `customer_id` = ".(int)$customer_id." WHERE `customer_id` = ".(int)$old_customer_id.' AND `user_id` = '.(int)$user_id;
        query($sql);
    }
    public static function hook_invoice_admin_list_job($callback,$invoice_id){
        // see if any tickets match this  invoice.
        $tickets = get_multiple('ticket',array('invoice_id'=>$invoice_id));
        if($tickets){
            foreach($tickets as $ticket){
                _e('Ticket: %s',module_ticket::link_open($ticket['ticket_id'],true,$ticket));
            }
        }
    }
    public static function hook_invoice_sidebar($callback,$invoice_id){
        // see if any tickets match this  invoice.
        $tickets = get_multiple('ticket',array('invoice_id'=>$invoice_id));
        if($tickets){
            foreach($tickets as $ticket){
                ?>
                <h3><?php _e('Priority Support Ticket');?></h3>
                <table border="0" cellspacing="0" cellpadding="2" class="tableclass tableclass_form tableclass_full">
                    <tbody>
                    <tr>
                        <th class="width1">
                            <?php _e('Ticket');?>
                        </th>
                        <td>
                            <?php echo module_ticket::link_open($ticket['ticket_id'],true,$ticket); ?>
                        </td>
                    </tr>
                    <tr>
                        <th>
                            <?php _e('Subject');?>
                        </th>
                        <td>
                            <?php echo htmlspecialchars($ticket['subject']); ?>
                        </td>
                    </tr>
                    <tr>
                        <th>
                            <?php _e('Status');?>
                        </th>
                        <td>
                            <?php
                            $s = module_ticket::get_statuses();
                            echo $s[$ticket['status_id']];
                            ?>
                        </td>
                    </tr>
                    </tbody>
                </table>
                <?php
            }
        }
    }

    public static function is_text_html($text){
        return stripos($text,'<br')!==false;
    }



    public function get_upgrade_sql(){
        $sql = '';


        $res = qa1("SHOW TABLES LIKE '"._DB_PREFIX."ticket_data'");
        if(!$res || !count($res)){
            $sql .= "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX."ticket_data` (
    `ticket_data_id` int(11) NOT NULL AUTO_INCREMENT,
    `ticket_data_key_id` int(11) NOT NULL,
    `ticket_id` int(11) NOT NULL,
    `value` text NOT NULL,
    `create_user_id` int(11) NOT NULL,
    `update_user_id` int(11) NOT NULL,
    `date_updated` date NOT NULL,
    `date_created` int(11) NOT NULL,
    PRIMARY KEY (`ticket_data_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;";
        }
        $res = qa1("SHOW TABLES LIKE '"._DB_PREFIX."ticket_data_key'");
        if(!$res || !count($res)){
            $sql .= "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX."ticket_data_key` (
              `ticket_data_key_id` int(11) NOT NULL AUTO_INCREMENT,
              `ticket_account_id` int(11) NOT NULL,
              `key` varchar(255) NOT NULL,
              `type` varchar(50) NOT NULL,
              `options` text NOT NULL,
              `order` int(11) NOT NULL DEFAULT '0',
                `encrypt_key_id` int(11) NOT NULL DEFAULT '0',
              `create_user_id` int(11) NOT NULL,
              `update_user_id` int(11) NOT NULL,
              `date_updated` date NOT NULL,
              `date_created` int(11) NOT NULL,
              PRIMARY KEY (`ticket_data_key_id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;";
        }

        $res = qa1("SHOW TABLES LIKE '"._DB_PREFIX."ticket_message_attachment'");
        if(!$res || !count($res)){
            $sql_create = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX.'ticket_message_attachment` (
              `ticket_message_attachment_id` int(11) NOT NULL AUTO_INCREMENT,
              `ticket_id` int(11) DEFAULT NULL,
              `ticket_message_id` int(11) DEFAULT NULL,
              `file_name` varchar(255) NOT NULL,
              `content_type` varchar(60) NOT NULL,
              `create_user_id` int(11) NOT NULL,
              `update_user_id` int(11) NULL,
              `date_created` date NOT NULL,
              `date_updated` date NULL,
              PRIMARY KEY (`ticket_message_attachment_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;';
            query($sql_create);
        }
        $res = qa1("SHOW TABLES LIKE '"._DB_PREFIX."ticket_type'");
        if(!$res || !count($res)){
            $sql_create = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX.'ticket_type` (
              `ticket_type_id` int(11) NOT NULL AUTO_INCREMENT,
              `name` varchar(255) NOT NULL,
              `public` tinyint(1) NOT NULL DEFAULT \'0\',
              `create_user_id` int(11) NOT NULL,
              `update_user_id` int(11) NOT NULL,
              `date_updated` date NOT NULL,
              `date_created` int(11) NOT NULL,
              PRIMARY KEY (`ticket_type_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
            ';
            query($sql_create);
        }

        $fields = get_fields('ticket_data_key');
        if(!isset($fields['encrypt_key_id'])){
            $sql .= 'ALTER TABLE `'._DB_PREFIX.'ticket_data_key` ADD `encrypt_key_id` int(11) NOT NULL DEFAULT \'0\' AFTER  `order`;';
        }
        $fields = get_fields('ticket');
        if(!isset($fields['priority'])){
            $sql .= 'ALTER TABLE `'._DB_PREFIX.'ticket` ADD `priority` INT NOT NULL DEFAULT  \'0\' AFTER  `user_id`;';
        }
        if(!isset($fields['invoice_id'])){
            $sql .= 'ALTER TABLE `'._DB_PREFIX.'ticket` ADD `invoice_id` INT NOT NULL DEFAULT  \'0\' AFTER  `user_id`;';
        }
        if(!isset($fields['faq_product_id'])){
            $sql .= 'ALTER TABLE `'._DB_PREFIX.'ticket` ADD `faq_product_id` INT NOT NULL DEFAULT  \'0\' AFTER  `ticket_account_id`;';
        }


        $fields = get_fields('ticket');
        if(!isset($fields['ticket_type_id'])){
            $ticket_type_sql = 'ALTER TABLE `'._DB_PREFIX.'ticket` ADD `ticket_type_id` INT NOT NULL DEFAULT  \'0\' AFTER  `type`;';
            query($ticket_type_sql);
            // upgrade our ticket types into this new table.
            $sql_old_types = "SELECT `type` FROM `"._DB_PREFIX."ticket` GROUP BY `type` ORDER BY `type`";
            $statuses = array();
            foreach(qa($sql_old_types) as $r){
                if(strlen(trim($r['type']))>0){
                    $ticket_type_id = update_insert('ticket_type_id','new','ticket_type',array('name'=>$r['type']));
                    $sql_ticket_type_id = "UPDATE `"._DB_PREFIX."ticket` SET ticket_type_id = '".(int)$ticket_type_id."' WHERE `type` = '".mysql_real_escape_string($r['type'])."'";
                    query($sql_ticket_type_id);
                }
            }

        }
        // todo - other tables.

        self::add_table_index('ticket','assigned_user_id');
        self::add_table_index('ticket','ticket_account_id');
        self::add_table_index('ticket','last_message_timestamp');
        self::add_table_index('ticket','status_id');
        self::add_table_index('ticket','user_id');
        self::add_table_index('ticket','customer_id');
        self::add_table_index('ticket','faq_product_id');
        return $sql;
    }
    public function get_install_sql(){
        ob_start();
        ?>


    CREATE TABLE IF NOT EXISTS `<?php echo _DB_PREFIX; ?>ticket` (
    `ticket_id` int(11) NOT NULL AUTO_INCREMENT,
    `ticket_account_id` int(11) NOT NULL,
    `faq_product_id` int(11) NOT NULL DEFAULT '0',
    `customer_id` int(11) DEFAULT NULL,
    `website_id` int(11) DEFAULT NULL,
    `user_id` int(11) NOT NULL,
    `invoice_id` int(11) NOT NULL,
    `priority` int(11) NOT NULL,
    `assigned_user_id` int(11) NOT NULL,
    `last_message_timestamp` int(11) NOT NULL,
    `status_id` int(11) NOT NULL,
    `subject` varchar(255) NOT NULL DEFAULT '',
    `type` varchar(255) NOT NULL DEFAULT '',
    `ticket_type_id` INT NOT NULL DEFAULT  '0',
    `unread` tinyint(1) NOT NULL DEFAULT '1',
    `date_completed` date NOT NULL,
    `create_user_id` int(11) NOT NULL,
    `update_user_id` int(11) NULL,
    `date_created` datetime NOT NULL,
    `date_updated` datetime NULL,
    PRIMARY KEY (`ticket_id`),
        KEY `assigned_user_id` (`assigned_user_id`),
        KEY `ticket_account_id` (`ticket_account_id`),
        KEY `last_message_timestamp` (`last_message_timestamp`),
        KEY `status_id` (`status_id`),
        KEY `user_id` (`user_id`),
        KEY `customer_id` (`customer_id`),
        KEY `faq_product_id` (`faq_product_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    CREATE TABLE IF NOT EXISTS `<?php echo _DB_PREFIX; ?>ticket_account` (
    `ticket_account_id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(255) NOT NULL,
    `email` varchar(255) NOT NULL,
    `username` varchar(255) NOT NULL,
    `password` varchar(255) NOT NULL,
    `host` varchar(255) NOT NULL,
    `port` int(11) NOT NULL DEFAULT '110',
    `delete` tinyint(4) NOT NULL DEFAULT '0',
    `default_user_id` int(11) NOT NULL DEFAULT '0',
    `default_customer_id` int(11) NOT NULL DEFAULT '0',
    `default_type` int(11) NOT NULL,
    `subject_regex` varchar(255) NOT NULL,
    `body_regex` varchar(255) NOT NULL,
    `to_regex` varchar(255) NOT NULL,
    `start_date` datetime NOT NULL,
    `secure` tinyint(4) NOT NULL DEFAULT '0',
    `imap` tinyint(4) NOT NULL DEFAULT '0',
    `search_string` varchar(255) NOT NULL,
    `mailbox` varchar(255) NOT NULL,
    `last_checked` int(11) NOT NULL DEFAULT '0',
    `create_user_id` int(11) NOT NULL,
    `update_user_id` int(11) NULL,
    `date_created` datetime NOT NULL,
    `date_updated` datetime NULL,
    PRIMARY KEY (`ticket_account_id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

    CREATE TABLE IF NOT EXISTS `<?php echo _DB_PREFIX; ?>ticket_message` (
    `ticket_message_id` int(11) NOT NULL AUTO_INCREMENT,
    `ticket_id` int(11) DEFAULT NULL,
    `message_id` varchar(255) NOT NULL,
    `content` text NOT NULL,
    `htmlcontent` text NOT NULL,
    `message_time` int(11) NOT NULL,
    `message_type_id` int(11) NOT NULL,
    `from_user_id` int(11) NOT NULL,
    `to_user_id` int(11) NOT NULL,
    `cache` text NOT NULL,
    `status_id` int(11) NOT NULL,
    `create_user_id` int(11) NOT NULL,
    `update_user_id` int(11) NULL,
    `date_created` date NOT NULL,
    `date_updated` date NULL,
    PRIMARY KEY (`ticket_message_id`),
    KEY `message_id` (`message_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

    ALTER TABLE  `<?php echo _DB_PREFIX; ?>ticket_message` ADD INDEX (  `ticket_id` );


    CREATE TABLE IF NOT EXISTS `<?php echo _DB_PREFIX; ?>ticket_message_attachment` (
    `ticket_message_attachment_id` int(11) NOT NULL AUTO_INCREMENT,
    `ticket_id` int(11) DEFAULT NULL,
    `ticket_message_id` int(11) DEFAULT NULL,
    `file_name` varchar(255) NOT NULL,
    `content_type` varchar(60) NOT NULL,
    `create_user_id` int(11) NOT NULL,
    `update_user_id` int(11) NULL,
    `date_created` date NOT NULL,
    `date_updated` date NULL,
    PRIMARY KEY (`ticket_message_attachment_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

    CREATE TABLE IF NOT EXISTS `<?php echo _DB_PREFIX; ?>ticket_type` (
    `ticket_type_id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(255) NOT NULL,
    `public` tinyint(1) NOT NULL DEFAULT '0',
    `create_user_id` int(11) NOT NULL,
    `update_user_id` int(11) NOT NULL,
    `date_updated` date NOT NULL,
    `date_created` int(11) NOT NULL,
    PRIMARY KEY (`ticket_type_id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;


    CREATE TABLE IF NOT EXISTS `<?php echo _DB_PREFIX; ?>ticket_data` (
    `ticket_data_id` int(11) NOT NULL AUTO_INCREMENT,
    `ticket_data_key_id` int(11) NOT NULL,
    `ticket_id` int(11) NOT NULL,
    `value` text NOT NULL,
    `create_user_id` int(11) NOT NULL,
    `update_user_id` int(11) NOT NULL,
    `date_updated` date NOT NULL,
    `date_created` int(11) NOT NULL,
    PRIMARY KEY (`ticket_data_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

    CREATE TABLE IF NOT EXISTS `<?php echo _DB_PREFIX; ?>ticket_data_key` (
    `ticket_data_key_id` int(11) NOT NULL AUTO_INCREMENT,
    `ticket_account_id` int(11) NOT NULL,
    `key` varchar(255) NOT NULL,
    `type` varchar(50) NOT NULL,
    `options` text NOT NULL,
    `order` int(11) NOT NULL DEFAULT '0',
    `encrypt_key_id` int(11) NOT NULL DEFAULT '0',
    `create_user_id` int(11) NOT NULL,
    `update_user_id` int(11) NOT NULL,
    `date_updated` date NOT NULL,
    `date_created` int(11) NOT NULL,
    PRIMARY KEY (`ticket_data_key_id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

    <?php
// todo: add default admin permissions.

        return ob_get_clean();
    }


}