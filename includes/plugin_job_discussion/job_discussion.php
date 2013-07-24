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

class module_job_discussion extends module_base{
	
    public static function can_i($actions,$name=false,$category=false,$module=false){
        if(!$module)$module=__CLASS__;
        return parent::can_i($actions,$name,$category,$module);
    }
	public static function get_class() {
        return __CLASS__;
    }
	function init(){
		$this->links = array();
		$this->module_name = "job_discussion";
		$this->module_position = 17.1; //17 is job

        $this->version = 2.142;
        // 2.1 - initial
        // 2.11 - date change
        // 2.12 - better linking and auto open
        // 2.13 - possible bug fix in discussion saving
        // 2.14 - bug fixing.
        // 2.141 - bug fix for IE.
        // 2.142 - send email to staff option (as well as customer)

        module_config::register_css('job_discussion','job_discussion.css');
        module_config::register_js('job_discussion','job_discussion.js');

        //if(self::can_i('view','Job Discussions')){
        if(get_display_mode() != 'mobile'){
            hook_add('job_task_after','module_job_discussion::hook_job_task_after');
        }


        if(class_exists('module_template',false)){
        module_template::init_template('job_discussion_email_customer','Dear {CUSTOMER_NAME},<br>
<br>
A new comment has been added to a task in your job: {JOB_NAME}.<br><br>
Task: {TASK_NAME} <br/><br/>
Note: {NOTE}<br/><br/>
You can view this job and the comments online by <a href="{JOB_URL}">clicking here</a>.<br><br>
Thank you,<br><br>
{FROM_NAME}
','New Job Comment: {JOB_NAME}',array(
            'CUSTOMER_NAME' => 'Customers Name',
            'JOB_NAME' => 'Job Name',
            'FROM_NAME' => 'Your name',
            'JOB_URL' => 'Link to job for customer',
            'TASK_NAME' => 'name of the task the note was added to',
            'NOTE' => 'Copy of the note',
        ));

        module_template::init_template('job_discussion_email_staff','Dear {STAFF_NAME},<br>
<br>
A new comment has been added to a task in your job: {JOB_NAME}.<br><br>
Task: {TASK_NAME} <br/><br/>
Note: {NOTE}<br/><br/>
You can view this job and the comments online by <a href="{JOB_URL}">clicking here</a>.<br><br>
Thank you,<br><br>
{FROM_NAME}
','New Comment on your Job: {JOB_NAME}',array(
            'STAFF_NAME' => 'Staff Name',
            'JOB_NAME' => 'Job Name',
            'FROM_NAME' => 'Your name',
            'JOB_URL' => 'Link to job for staff member',
            'TASK_NAME' => 'name of the task the note was added to',
            'NOTE' => 'Copy of the note',
        ));
        }


    }

    public static function link_public($job_id,$task_id,$h=false){
        if($h){
            return md5('s3cret7hash for job discussion '._UCM_FOLDER.' '.$job_id.' with task '.$task_id);
        }
        $url = _EXTERNAL_TUNNEL_REWRITE.'m.job_discussion/h.public/i.'.$job_id.'/t.'.$task_id.'/hash.'.self::link_public($job_id,$task_id,true);
        return full_link($url);
    }


    public static function external_hook($hook){

        switch($hook){
            case 'public':
                $job_id = (isset($_REQUEST['i'])) ? (int)$_REQUEST['i'] : false;
                $task_id = (isset($_REQUEST['t'])) ? (int)$_REQUEST['t'] : false;
                $hash = (isset($_REQUEST['hash'])) ? trim($_REQUEST['hash']) : false;
                if($job_id && $task_id && $hash){
                    $correct_hash = self::link_public($job_id,$task_id,true);
                    if($correct_hash == $hash){
                        module_job_discussion::print_discussion($job_id,$task_id);
                    }
                }
                break;
        }
    }
    public static function print_discussion($job_id,$task_id,$job_data=array(),$task_data=array(),$allow_new=true){

        $job_data = $job_data ? $job_data : module_job::get_job($job_id,true,true);

        if($job_data && isset($job_data['job_discussion']) && $job_data['job_discussion'] == 1){
            // disabled & hidden.
            return;
        }
        $task_data = $task_data ? $task_data : module_job::get_task($job_id,$task_id);

        $comments = get_multiple('job_discussion',array('job_id'=>$job_id,'task_id'=>$task_id),'job_discussion_id','exact','job_discussion_id');
        $current_user_id = module_security::get_loggedin_id();
        $customer = module_customer::get_customer($job_data['customer_id']);
        if(!$current_user_id){
            if($job_data['customer_id'] && $customer['primary_user_id']){
                $current_user_id = $customer['primary_user_id'];
            }
        }


        foreach($comments as $comment){
            ?>
        <div class="task_job_discussion_comments">
            <div class="info">
                <?php echo $comment['user_id'] ? module_user::link_open($comment['user_id'],true) : 'Unknown';?>
                <?php echo print_date($comment['date_created'],true); ?>
            </div>
            <?php echo forum_text($comment['note']); ?>
        </div>
        <?php
        }
        if($job_data && isset($job_data['job_discussion']) && $job_data['job_discussion'] == 2){
            // disabled & shown.
            return;
        }
        if($allow_new){
        ?>
    <div class="task_job_discussion_comments">
        <div class="info">
            <?php echo $current_user_id ? module_user::link_open($current_user_id,true) : 'Unknown';?>
            <?php echo print_date(time(),true); ?>
        </div>
        <textarea rows="4" cols="30" name="new_comment"></textarea> <br/>
        <input type="button" name="add" value="<?php _e('Add Comment');?>" class="task_job_discussion_add small_button"> <!--  post_url="<?php echo self::link_public($job_id,$task_id);?>" -->
        <input type="hidden" name="discussion_job_id" value="<?php echo $job_id;?>">
        <input type="hidden" name="discussion_task_id" value="<?php echo $task_id;?>">
        <?php

        $send_to_customer_ids = array();
        $send_to_staff_ids = array();
        if(module_security::get_loggedin_id() && $job_data['customer_id'] && $customer['primary_user_id'] && $customer['primary_user_id'] != $current_user_id){
            $send_to_customer_ids[$customer['primary_user_id']] = module_config::c('job_discussion_customer_checked',1); // is it checked by default?
        }
        if($job_data['user_id'] && $job_data['user_id'] != $current_user_id && $job_data['user_id'] != $customer['primary_user_id']){
            $send_to_staff_ids[$job_data['user_id']] = module_config::c('job_discussion_staff_checked',1);
        }
        if($task_data['user_id'] && $task_data['user_id'] != $current_user_id && $task_data['user_id'] != $customer['primary_user_id']){
            $send_to_staff_ids[$task_data['user_id']] = module_config::c('job_discussion_staff_checked',1);
        }

        if(!module_security::is_logged_in()){ echo '<div style="display:none;">'; }
        foreach($send_to_customer_ids as $customer_id => $checked){
            // we are the admin, sending an email to customer
            ?>
            <br/>
            <input type="checkbox" name="sendemail_customer" value="yes" <?php echo $checked ? 'checked="checked"':'';?> class="sendemail_customer"> <?php _e('Yes, send email to customer %s',module_user::link_open($customer_id,true));?>
            <?php
        }

        foreach($send_to_staff_ids as $staff_id => $checked){
            // we are the admin, sending an email to assigned staff member
            ?>
            <br/>
            <input type="checkbox" name="sendemail_staff[]" value="<?php echo $staff_id;?>" <?php echo $checked ? 'checked="checked"':'';?> class="sendemail_staff"> <?php _e('Yes, send email to staff %s',module_user::link_open($staff_id,true));?>
        <?php
        }
        if(!module_security::is_logged_in()){ echo '</div>'; }

        ?>
        </div>
        <?php
        }
    }

    public static function hook_job_task_after($hook,$job_id,$task_id,$job_data,$task_data){

        $comments = get_multiple('job_discussion',array('job_id'=>$job_id,'task_id'=>$task_id),'job_discussion_id','exact','job_discussion_id');

        if($job_data && isset($job_data['job_discussion']) && $job_data['job_discussion'] == 1){
            // disabled & hidden.
            return;
        }
        if($job_data && isset($job_data['job_discussion']) && $job_data['job_discussion'] == 2 && count($comments) == 0){
            // disabled & shown.
            return;
        }


        if(isset($_POST['job_discussion_add_job_id']) && isset($_POST['job_discussion_add_task_id']) && $_POST['job_discussion_add_job_id'] == $job_id && $_POST['job_discussion_add_task_id'] == $task_id && isset($_POST['note']) && strlen($_POST['note'])){

            $x=0;
            while(ob_get_level()&&$x++<10)ob_end_clean();

            $current_user_id = module_security::get_loggedin_id();
            $customer = module_customer::get_customer($job_data['customer_id']);
            if(!$current_user_id){
                if($job_data['customer_id'] && $customer['primary_user_id']){
                    $current_user_id = $customer['primary_user_id'];
                }
            }

            $result = array();

            // adding a new note.
            $job_discussion_id = update_insert('job_discussion_id',0,'job_discussion',array(
                'job_id' => $job_id,
                'task_id' => $task_id,
                'user_id' => $current_user_id,
                'note' => $_POST['note'],
            ));
            $result['job_discussion_id'] = $job_discussion_id;
            $result['count'] = count($comments) + 1;
            $tasks = module_job::get_tasks($job_id);
            if(isset($_POST['sendemail_customer']) && $_POST['sendemail_customer'] == 'yes' && $customer['primary_user_id']){
                // send email to customer primary user id.
                $user = module_user::get_user($customer['primary_user_id'],false);
                if($user['user_id'] == $customer['primary_user_id']){
                    $values = array_merge($user,$job_data);
                    $values['job_url'] = module_job::link_public($job_id);
                    $values['job_url'] .= (strpos($values['job_url'],'?')===false ? '?' : '&').'discuss='.$task_id.'#discuss'.$task_id;
                    $values['job_name'] = $job_data['name'];
                    $values['customer_name'] = $user['name'].' '.$user['last_name'];
                    $values['note'] = $_POST['note'];
                    //todo: no order if no showning numbers
                    $values['task_name'] = '#'.$tasks[$task_id]['task_order'].': '.$tasks[$task_id]['description'];

                    $template = module_template::get_template_by_key('job_discussion_email_customer');
                    $template->assign_values($values);
                    $html = $template->render('html');

                    $email = module_email::new_email();
                    $email->replace_values = $values;
                    $email->set_to('user',$user['user_id']);
                    $email->set_from('user',$current_user_id);
                    $email->set_subject($template->description);
                    // do we send images inline?
                    $email->set_html($html);

                    if($email->send()){
                        // it worked successfully!!
                        $result['email_customer'] = 1;
                    }else{
                        /// log err?
                        $result['email_customer'] = 0;
                    }
                }else{
                    // log error?
                    $result['email_customer'] = 0;
                }

            }
            if(isset($_POST['sendemail_staff']) && is_array($_POST['sendemail_staff'])){ // == 'yes' && $job_data['user_id']
                // todo: handle the restul better when sending to multiple people
                $result['email_staff_list']=$_POST['sendemail_staff'];
                foreach($_POST['sendemail_staff'] as $staff_id){
                    // send email to staff
                    $staff_id = (int)$staff_id;
                    if(!$staff_id){
                        $result['nostaff']=1;
                        continue;
                    }

                    if(
                        isset($task_data['user_id']) && $task_data['user_id'] == $staff_id
                        ||
                        isset($job_data['user_id']) && $job_data['user_id'] == $staff_id
                    ){

                        //$user = module_user::get_user($job_data['user_id'],false);
                        $user = module_user::get_user($staff_id,false);
                        if($user['user_id'] == $staff_id){
                            $values = array_merge($user,$job_data);
                            $values['job_url'] = module_job::link_public($job_id);
                            $values['job_url'] .= (strpos($values['job_url'],'?')===false ? '?' : '&').'discuss='.$task_id.'#discuss'.$task_id;
                            $values['job_name'] = $job_data['name'];
                            $values['staff_name'] = $user['name'].' '.$user['last_name'];
                            $values['note'] = $_POST['note'];
                            //todo: no order if no showning numbers
                            $values['task_name'] = '#'.$tasks[$task_id]['task_order'].': '.$tasks[$task_id]['description'];

                            $template = module_template::get_template_by_key('job_discussion_email_staff');
                            $template->assign_values($values);
                            $html = $template->render('html');

                            $email = module_email::new_email();
                            $email->replace_values = $values;
                            $email->set_to('user',$staff_id);
                            $email->set_from('user',$current_user_id);
                            $email->set_subject($template->description);
                            // do we send images inline?
                            $email->set_html($html);

                            if($email->send()){
                                // it worked successfully!!
                                $result['email_staff'] = 1;
                            }else{
                                /// log err?
                                $result['email_staff'] = 0;
                            }
                        }else{
                            // log error?
                            $result['email_staff'] = 0;
                        }
                    }
                }

            }
            $x=0;
            while($x++<5 && ob_get_level())ob_end_clean();
            header("Content-type: text/javascript",true);
            echo json_encode($result);
            exit;
        }

        ?>
        <a href="<?php echo self::link_public($job_id,$task_id);?>" id="discuss<?php echo $task_id;?>" class="task_job_discussion" title="<?php _e('View Discussion');?>"><?php echo count($comments) > 0 ? count($comments) : '';?></a>
            <div class="task_job_discussion_holder"<?php echo isset($_REQUEST['discuss']) && $_REQUEST['discuss'] == $task_id ? ' style="display:block;"': '';?>>
                <?php if(isset($_REQUEST['discuss']) && $_REQUEST['discuss'] == $task_id){
                    $_REQUEST['t'] = $task_id;
                    $_REQUEST['i'] = $job_id;
                    $_REQUEST['hash'] = self::link_public($job_id,$task_id,true);
                    self::external_hook('public');
                } ?>
            </div>
        <?php
    }


    public function get_install_sql(){
        ob_start();
        ?>
        CREATE TABLE `<?php echo _DB_PREFIX; ?>job_discussion` (
        `job_discussion_id` int(11) NOT NULL auto_increment,
        `job_id` INT(11) NULL,
        `task_id` INT(11) NULL,
        `user_id` INT NOT NULL DEFAULT  '0',
        `seen` TINYINT (1) NOT NULL DEFAULT  '0',
        `note` TEXT NOT NULL DEFAULT  '',
        `create_user_id` int(11) NOT NULL,
        `update_user_id` int(11) NULL,
        `date_created` DATETIME NOT NULL,
        `date_updated` date NULL,
        PRIMARY KEY (`job_discussion_id`),
        KEY `job_id` (`job_id`),
        KEY `task_id` (`task_id`),
        KEY `seen` (`seen`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

        <?php
        return ob_get_clean();

    }
}