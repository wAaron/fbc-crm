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


// for the email table.
define('_MAIL_STATUS_PENDING',1);
define('_MAIL_STATUS_OVER_QUOTA',5);
define('_MAIL_STATUS_SENT',2);
define('_MAIL_STATUS_FAILED',4);



class module_email extends module_base{

	public $replace_values;

    public $email_id; // queued email id in system.
    private $email_fields; // in db.


	public $to = array();
	public $cc = array();
	public $bcc = array();
    public $message_html;
    public $from;
    public $attachments;
    public $message_text;
    public $subject;
    public $sent_time;
    public $status;
    public $reply_to;
    public $bounce_address = '';
    public $error_text;
    public $message_id = '';
    public $debug_message = '';

    public $invoice_id=0;
    public $job_id=0;
    public $note_id=0;

    public static function can_i($actions,$name=false,$category=false,$module=false){
        if(!$module)$module=__CLASS__;
        return parent::can_i($actions,$name,$category,$module);
    }
	public static function get_class() {
        return __CLASS__;
    }
    function init(){
		$this->module_name = "email";
		$this->module_position = 1666;

        $this->version = 2.306;
        // 2.23 - do the email string replace twice so we catch everything.
        // 2.24 - auth
        // 2.25 - bug fix, replace with arrays.
        // 2.251 - menu change.
        // 2.252 - link rewirte
        // 2.253 - permission fix
        // 2.254 - default to address for multi contacts
        // 2.255 - custom from email address
        // 2.301 - BIG UPDATE! with internal mail queue and quota limiting
        // 2.302 - from email address set to full name of user.
        // 2.303 - ability to add multiple attachments to Job/Invoice/etc.. email
        // 2.304 - showing email history in invoice/jobs/etc..
        // 2.305 - show which user emailed invoice/job/etc..
        // 2.306 - choose different templates when sending an email


		$this->reset();
	}

    public function pre_menu(){

        // the link within Admin > Settings > Emails.
        if($this->can_i('view','Email Settings','Config')){
            $this->links[] = array(
                "name"=>"Email",
                "p"=>"email_settings",
                "icon"=>"icon.png",
                "args"=>array('email_template_id'=>false),
                'holder_module' => 'config', // which parent module this link will sit under.
                'holder_module_page' => 'config_admin',  // which page this link will be automatically added to.
                'menu_include_parent' => 0,
            );
        }
    }

	public function reset(){
		// clear all local variables.
		$this->replace_values = array();
		$this->to = array();
		$this->cc = array();
		$this->bcc = array();
		$this->error_text = '';
		$this->from = array();
		$this->attachments = array();
		$this->bounce_address = '';
		$this->reply_to= '';
		$this->subject= '';
		$this->message_html= '';
		$this->message_text= '';
		$this->sent_time= 0;

        $this->invoice_id=0;
        $this->job_id=0;
        $this->note_id=0;

        $this->email_fields = get_fields('email');
	}
	

     public function process(){
		if('send_email' == $_REQUEST['_process']){
			$this->_handle_send_email();
		}

	}

    public function get_summary($field_type,$field_id,$field_key) {
        global $plugins;
        switch($field_type){
            case 'customer':
                if($field_key=='name')$field_key = 'customer_name';
                $data = $plugins['customer']->get_customer($field_id);
                $primary_user_id = $data['primary_user_id'];
                $data = $plugins['user']->get_user($primary_user_id,false);
                return isset($data[$field_key]) ? $data[$field_key] : '';
            case 'user':
                $data = $plugins['user']->get_user($field_id,false);
                return isset($data[$field_key]) ? $data[$field_key] : '';
        }
        return false;
    }

    


    /**
     * Create a new email ready to send.
     * @return module_email
     */
    public static function &new_email(){
		$email = new self();
        $email -> reset();
        return $email;
    }
    public function replace($key,$val){
        $this->replace_values[$key] = $val;
    }
	/**
	 * Adds the sender of this email.
	 * @param  $type
	 * @param  $id
	 * @return void
	 */
    public function set_from($type,$id){
        $this->from = array(
            'type' => $type,
            'id' => $id,
            'name' => $this->get_summary($type,$id,'name').' '.$this->get_summary($type,$id,'last_name'),
            'email' => $this->get_summary($type,$id,'email'),
        );
    }
	/**
	 * Adds the sender of this email manually.
	 * @param  $email
	 * @param  $name
	 * @return void
	 */
    public function set_from_manual($email,$name=''){
        $this->from = array(
            'type' => 'manual',
            'id' => false,
            'name' => $name,
            'email' => $email,
        );
    }
	/**
	 * Adds the reply to of this email.
	 * @param  $type
	 * @param  $id
	 * @return void
	 */
    public function set_reply_to($email,$name){
        $this->reply_to = array($email,$name);
    }
	/**
	 * Adds a recipient to this email.
	 * @param  $type
	 * @param  $id
	 * @return void
	 */
    public function set_to($type,$id,$email='',$name=''){
        // grab the details of the recipient.
		// add it as a recipient to this email
        if(!$email){
            $email = $this->get_summary($type,$id,'email');
        }
        if(!$name){
            $name = $this->get_summary($type,$id,'name');
        }
        $this->to[] = array(
            'type' => $type,
            'id' => $id,
            'name' => $name,
            'email' => $email,
        );

    }
	/**
	 * Adds the to of this email manually.
	 * @param  $email
	 * @param  $name
	 * @return void
	 */
    public function set_to_manual($email,$name=''){
        $this->to[] = array(
            'type' => 'manual',
            'id' => false,
            'name' => $name,
            'email' => $email,
        );
    }
    public function set_bcc_manual($email,$name){
        $this->bcc[] = array(
            'name' => $name,
            'email' => $email,
        );
    }
    public function set_bounce_address($email){
        $this->bounce_address = $email;
    }
	/**
	 * Adds an attachment to the email
	 * the attachment name will be worked out from the path.
	 * @param  $path
	 * @return void
	 */
	public function add_attachment($path){
		$this->attachments[] = $path;
	}

	/**
	 * Sets the text for the email.
	 * @param  $text
	 * @return void
	 */
	public function set_text($text,$html=false){
		if($html){
			$this->message_html = $text;
			// convert it to text if none exists.
			if (!$this->message_text) {
				$this->message_text = strip_tags(preg_replace('/<br/', "\n<br", preg_replace('#\s+#', ' ', $text)));
			}
		}else{
			$this->message_text = $text;
			// convert it to html if none exists.
			if (!$this->message_html) {
				$this->message_html = nl2br($text);
			}
		}
	}
    public function set_html($html){
        $this->set_text($html,true);
    }
    public function set_subject($subject){
        $this->subject=$subject;
    }
    public function AddAttachment($file_path,$file_name=''){
        $this->attachments[$file_path] = array(
            'path' => $file_path,
            'name' => $file_name,
        );
    }
    public function is_email_limit_ok(){
        $limit_ok = true;
        switch(module_config::c('email_limit_period','day')){
            case 'day':
                $start_time = strtotime("-24 hours");
                break;
            case 'hour':
                $start_time = strtotime("-1 hour");
                break;
            case 'minute':
                $start_time = time() - 60;
                break;
            default:
                $start_time = 0;
        }
        $send_limit = (int)module_config::c('email_limit_amount',0);

        if($start_time > 0 && $send_limit > 0){
            // found a limit, see if it's broken
            $sql = "SELECT COUNT(email_id) AS send_count FROM `"._DB_PREFIX."email` WHERE sent_time > '$start_time'";
            $res = array_shift(qa($sql));
            if($res && $res['send_count']){
                // newsletters have been sent out - is it over the limit?
                if($res['send_count'] >= $send_limit){
                    $limit_ok = false;
                }
            }
        }
        return $limit_ok;
    }
	/**
	 * Sends the email we created above, startign with the new_email() method.
	 * @return bool
	 */
	public function send(){

        if(_DEBUG_MODE){
            module_debug::log(array('title'=>'Email Module','data'=>'Starting to send email'));
        }

        // we have to check our mail quota:
        if(!$this->is_email_limit_ok()){
            $this->status=_MAIL_STATUS_OVER_QUOTA;
            $this->error_text=_l('Email over quota, please wait a while and try again.');
            return false;
        }
        //$this->status=_MAIL_STATUS_OVER_QUOTA;//testing.

        // we have to add this email to the "email" table ready to be sent out.
        // once the email is queued for sending it will be processed (only if we are within our email quota)
        // if we are not in our email quota then the email will either be queued for sending or an error will be returned.
        // todo: queue for sending later
        // NOTE: at the moment we just return an error and do not queue the email for later sending.
        // emails are removed from the 'email' table if we are over quota for now.


        // preprocessing
        if(!$this->from){
            $this->set_from_manual(module_config::c('admin_email_address'),module_config::c('admin_system_name'));
        }
        if(!$this->to){
            $this->set_to_manual(module_config::c('admin_email_address'),module_config::c('admin_system_name'));
        }
        // process the message replacements etc..
        foreach($this->to as $to){
            $this->replace('TO_NAME',$to['name']);
            $this->replace('TO_EMAIL',$to['email']);
        }
        $this->replace('FROM_NAME',$this->from['name']);
        $this->replace('FROM_EMAIL',$this->from['email']);
        // hack - we do this loop twice because some replace keys may have replace keys in them.
        for($x=0;$x<2;$x++){
            foreach($this->replace_values as $key=>$val){
                if(is_array($val))continue;
                //$val = str_replace(array('\\', '$'), array('\\\\', '\$'), $val);
                $key = '{'.strtoupper($key).'}';
                // reply to name
                foreach($this->to as &$to){
                    if($to['name']){
                        $to['name'] = str_replace($key,$val,$to['name']);
                    }
                }
                // replace subject
                $this->subject = str_replace($key,$val,$this->subject);
                // replace message html
                $this->message_html = str_replace($key,$val,$this->message_html);
                // replace message text.html
                $this->message_text = str_replace($key,$val,$this->message_text);
            }
        }




        // get all the data together in an array that will be saved to the email table
        $header_data = array();
        if($this->reply_to){
            $header_data['ReplyToEmail'] = $this->reply_to[0];
            $header_data['ReplyToName'] = $this->reply_to[1];
            $header_data['Sender'] = isset($this->bounce_address) ? $this->bounce_address : $this->reply_to[0];
        }else{
            $header_data['Sender'] = isset($this->bounce_address) ? $this->bounce_address : false;
        }
        $header_data['FromEmail'] = isset($this->from['email']) ? $this->from['email'] : '';
        $header_data['FromName'] = isset($this->from['name']) ? $this->from['name'] : '';
        $header_data['to'] = $this->to;
        $header_data['cc'] = $this->cc;
        $header_data['bcc'] = $this->bcc;
        
        $email_data = array(
            'create_time' => time(),
            'status' => _MAIL_STATUS_PENDING,
            'customer_id' => isset($this->customer_id) ? $this->customer_id : 0,
            'newsletter_id' => isset($this->newsletter_id) ? $this->newsletter_id : 0,
            'send_id' => isset($this->send_id) ? $this->send_id : 0,
            'debug' => isset($this->debug_message) ? $this->debug_message : '',
            'message_id' => $this->message_id,
            'subject' => $this->subject,
            'headers' => $header_data, // computed above....
            'html_content' => $this->message_html,
            'text_content' => $this->message_text,
            'attachments' => array(), // below
        );
        foreach($this->email_fields as $fieldname=>$fd){
            if($fieldname != 'email_id' && property_exists($this,$fieldname) && !isset($email_data[$fieldname])){
                $email_data[$fieldname] = $this->{"$fieldname"};
            }
        }


        if($this->attachments){
            foreach($this->attachments as $file){
                if(is_array($file)){
                    $file_path = $file['path'];
                    $file_name = $file['name'];
                }else{
                    $file_path = $file;
                    $file_name = '';
                }
                if(is_file($file_path)){
                    $email_data['attachments'][$file_path] = $file_name;
                }
            }
        }

        $email_id = update_insert('email_id',false,'email',$email_data);
        //echo '<pre>'.$email_id;print_r($email_data);exit;

        $this->_send_queued_email($email_id);
        $this->email_id = $email_id;
        return ($this->status == _MAIL_STATUS_SENT);

    }
    private function _send_queued_email($email_id){

        if(!$email_id)return false;
        $this->reset();
        $email_data = get_single('email','email_id',$email_id);
        if(!$email_data || $email_data['email_id'] != $email_id)return false;

        $headers = unserialize($email_data['headers']);
        $attachments = unserialize($email_data['attachments']);

        try{

		    require_once("class.phpmailer.php");
            $mail = new PHPMailer();
            //$mail -> Hostname = 'yoursite.com';
            $mail->CharSet = 'UTF-8';
            // turn on HTML emails
            $mail->isHTML(true);
            // SeT SMTP or php Mail method:
            if(module_config::c('email_smtp',0)){
                if(_DEBUG_MODE){
                    module_debug::log(array('title'=>'Email Module','data'=>'Connecting via SMTP to: '.module_config::c('email_smtp_hostname','')));
                }
                $mail->IsSMTP();
                // turn on SMTP authentication
                $mail->SMTPSecure = module_config::c('email_smtp_auth','');
                $mail->SMTPAuth = module_config::c('email_smtp_authentication',0);
                $mail->Host     = module_config::c('email_smtp_hostname','');
                if($mail->SMTPAuth){
                    $mail->Username = module_config::c('email_smtp_username','');
                    $mail->Password = module_config::c('email_smtp_password','');
                }
            }else{
                $mail->IsMail();
            }

            // pull out the data from $email_data
            $mail->MessageID = $email_data['message_id'];
            $mail->Subject     = $email_data['subject'];
            $mail->Body    = $email_data['html_content'];
            $mail->AltBody    = $email_data['text_content'];

            // from the headers:
            $mail->Sender = $headers['Sender'];
            if(isset($headers['ReplyToEmail'])){
                $mail->AddReplyTo($headers['ReplyToEmail'],isset($headers['ReplyToName']) ? $headers['ReplyToName'] : '');
            }
		    $mail->From     = $headers['FromEmail'];
			$mail->FromName = $headers['FromName'];
            $test_to_str = '';
            foreach ($headers['to'] as $to) {
                $mail->AddAddress($to['email'], $to['name']);
                $test_to_str .= " TO: ".$to['email'] .' - '.$to['name'];
            }
            foreach($headers['cc'] as $cc){
                $mail->AddCC($cc['email'],$cc['name']);
            }
            foreach($headers['bcc'] as $bcc){
                $mail->AddBCC($bcc['email'],$bcc['name']);
            }

            // attachemnts
			foreach($attachments as $file_path => $file_name){
				if(is_file($file_path)){
					$mail->AddAttachment($file_path,$file_name);
				}
			}



        // debugging.
//        $html = $this->message_html;
//        $mail->ClearAllRecipients();
//        $mail->AddAddress('davidtest@blueteddy.com.au','David Test');
//        $html = $test_to_str.$html;


            if(_DEBUG_MODE){
                module_debug::log(array('title'=>'Email Module','data'=>'Sending to: '.$test_to_str));
            }
            if(!$mail->Send()){
                $this->error_text = $mail->ErrorInfo;
                // update sent times and status on success.
                update_insert('email_id',$email_id,'email',array(
                    'status' => _MAIL_STATUS_FAILED,
                ));
                // TODO: delete email from the database insetad of letting it queue later.
                // todo: re-do this later to leave the email there for quing.
                delete_from_db('email','email_id',$email_id);
                $this->status = _MAIL_STATUS_FAILED;
                if(_DEBUG_MODE){
                    module_debug::log(array('title'=>'Email Module','data'=>'Send failed: '.$this->error_text));
                }
                // todo - send error to admin ?
            }else{
                // update sent times and status on success.
                update_insert('email_id',$email_id,'email',array(
                    'sent_time' => time(),
                    'status' => _MAIL_STATUS_SENT,
                ));
                $this->status = _MAIL_STATUS_SENT;
                if(_DEBUG_MODE){
                    module_debug::log(array('title'=>'Email Module','data'=>'Send success'));
                }
            }

            /*  echo '<hr>';
            echo $this->subject;
            print_r($this->from);
            print_r($this->to);echo $this->status;*/

            //$this->status=_MAIL_STATUS_OVER_QUOTA;//testing.

            // todo : incrase mail count so that it sits within our specified boundaries.

            // true on succes, false on fail.
            return ($this->status == _MAIL_STATUS_SENT);
        }catch(Exception $e){
            return false;
        }
	}


    public static function print_compose($options) {

        include('pages/email_compose_basic.php');
    }

    public static function get_email_compose_options($options) {
        $options = array_merge($options,array(
            'subject' => isset($_REQUEST['subject']) ? $_REQUEST['subject'] : (isset($options['subject']) ? $options['subject'] : ''),
            'content' =>  isset($_REQUEST['content']) ? $_REQUEST['content'] : (isset($options['content']) ? $options['content'] : ''),
            'cancel_url' =>  isset($options['cancel_url']) ? $options['cancel_url'] : false,
            'complete_url' => isset($options['complete_url']) ? $options['complete_url'] : (isset($options['cancel_url']) ? $options['cancel_url'] : false),
            'from_email' => isset($_REQUEST['from_email']) && $_REQUEST['from_email'] ? $_REQUEST['from_email'] : module_config::c('admin_email_address'),
            'from_name' => isset($_REQUEST['from_name']) && $_REQUEST['from_name'] ? $_REQUEST['from_name'] : module_config::c('admin_system_name'),
            'to' => isset($_REQUEST['to']) ? $_REQUEST['to'] : (isset($options['to']) ? $options['to'] : array()),
            'to_select' => isset($_REQUEST['to_select']) ? $_REQUEST['to_select'] : (isset($options['to_select']) ? $options['to_select'] : false),
            'bcc' => isset($_REQUEST['bcc']) ? $_REQUEST['bcc'] : (isset($options['bcc']) ? $options['bcc'] : ''),
            'attachments' => isset($options['attachments']) ? $options['attachments'] : array(),
            'success_callback' => isset($options['success_callback']) ? $options['success_callback'] : '',
        ));
        return $options; 
    }

    private function _handle_send_email(){
        $options = unserialize(base64_decode($_REQUEST['options']));
        $options = $this->get_email_compose_options($options);
        if(isset($_REQUEST['custom_to'])){
            $custom_to = explode('||',$_REQUEST['custom_to']);
            $custom_to['email'] = $custom_to[0];
            $custom_to['name'] = $custom_to[1];
            $to = array($custom_to);
        }else{
            $to = isset($options['to']) && is_array($options['to']) ? $options['to'] : array();;
        }

        $email = $this->new_email();
        $email->subject = $options['subject'];
        foreach($to as $t){
            $email->set_to_manual($t['email'],$t['name']);
        }
        // set from is the default from address.
        if(isset($options['from_email'])){
            $email->set_from_manual($options['from_email'],isset($options['from_name'])?$options['from_name']:'');
        }
        if($options['bcc']){
            $bcc = explode(',',$options['bcc']);
            foreach($bcc as $b){
                $b = trim($b);
                if(strlen($b)){
                    $email->set_bcc_manual($b,'');
                }
            }
        }
        if(isset($options['customer_id'])){
            $email->customer_id = $options['customer_id'];
        }
        if(isset($options['newsletter_id'])){
            $email->newsletter_id = $options['newsletter_id'];
        }
        if(isset($options['send_id'])){
            $email->send_id = $options['send_id'];
        }
        if(isset($options['invoice_id'])){
            $email->invoice_id = $options['invoice_id'];
        }
        if(isset($options['job_id'])){
            $email->job_id = $options['job_id'];
        }
        if(isset($options['note_id'])){
            $email->note_id = $options['note_id'];
        }
        if(isset($options['debug_message'])){
            $email->debug_message = $options['debug_message'];
        }
        $email->set_html($options['content']);
        foreach($options['attachments'] as $attachment){
            $email->AddAttachment($attachment['path'],$attachment['name']);
        }
        // new addition, manually added attachments.
        if(isset($_FILES['manual_attachment']) && isset($_FILES['manual_attachment']['tmp_name'])){
            foreach($_FILES['manual_attachment']['tmp_name'] as $key => $tmp_name){
                if(is_uploaded_file($tmp_name) && isset($_FILES['manual_attachment']['name'][$key]) && strlen($_FILES['manual_attachment']['name'][$key])){
                    $email->AddAttachment($tmp_name,$_FILES['manual_attachment']['name'][$key]);
                }
            }
        }
        if($email->send()){
            if(isset($options['success_callback_args']) && count($options['success_callback_args']) && $options['success_callback'] && is_callable($options['success_callback'])){
                // new callback method using call_user_func_array
                $args = $options['success_callback_args'];
                $args['email_id'] = $email->email_id;
                call_user_func($options['success_callback'],$args);
            }else if($options['success_callback']){
                eval($options['success_callback']);
            }
            set_message('Email sent successfully');
            redirect_browser($options['complete_url']);
        }else{
            set_error('Sending email failed: '.$email->error_text);
            redirect_browser($options['cancel_url']);
        }
    }

    public static function display_emails($options) {

        if(!isset($options['search']['status'])){
            $options['search']['status'] = _MAIL_STATUS_SENT;
        }
        $emails = get_multiple('email',$options['search']);
        if(count($emails)>0){
            include("pages/email_widget.php");
        }
    }

    public function get_install_sql(){
        return 'CREATE TABLE `'._DB_PREFIX.'email` (
  `email_id` int(11) NOT NULL AUTO_INCREMENT,
  `create_time` int(11) NOT NULL DEFAULT \'0\',
  `sent_time` int(11) NOT NULL DEFAULT \'0\',
  `status` tinyint(1) NOT NULL DEFAULT \'0\',
  `customer_id` int(11) NOT NULL DEFAULT \'0\',
  `newsletter_id` int(11) NOT NULL DEFAULT \'0\',
  `send_id` int(11) NOT NULL DEFAULT \'0\',
  `debug` varchar(50) NOT NULL DEFAULT \'\',
  `message_id` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `headers` TEXT NOT NULL DEFAULT \'\',
  `html_content` TEXT NOT NULL DEFAULT \'\',
  `text_content` TEXT NOT NULL DEFAULT \'\',
  `attachments` TEXT NOT NULL DEFAULT \'\',
  `date_created` datetime NOT NULL,
  `date_updated` datetime NULL,
  `create_user_id` int(11) NOT NULL,
  `update_user_id` int(11) NULL,
  `create_ip_address` varchar(15) NOT NULL,
  `update_ip_address` varchar(15) NULL,
  PRIMARY KEY (`email_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;';
    }

    public function get_upgrade_sql(){
        $sql = '';
        if(!isset($this->email_fields['invoice_id'])){
            $sql .= "ALTER TABLE  `"._DB_PREFIX."email` ADD  `job_id` INT NOT NULL DEFAULT  '0' AFTER  `send_id` ,
ADD  `invoice_id` INT NOT NULL DEFAULT  '0' AFTER  `job_id` ,
ADD  `note_id` INT NOT NULL DEFAULT  '0' AFTER  `invoice_id` ,
ADD INDEX (  `job_id` ,  `invoice_id` ,  `note_id` )";
        }
        return $sql;
    }


}

