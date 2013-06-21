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

define('_JOB_TASK_CREATION_NOT_ALLOWED','Unable to create new tasks');
define('_JOB_TASK_CREATION_REQUIRES_APPROVAL','Created tasks require admin approval');
define('_JOB_TASK_CREATION_WITHOUT_APPROVAL','Created tasks do not require approval');

define('_JOB_ACCESS_ALL','All jobs in system');
define('_JOB_ACCESS_ASSIGNED','Only jobs I am assigned to');
define('_JOB_ACCESS_CUSTOMER','Jobs from customers I have access to');

define('_TASK_DELETE_KEY','-DELETE-');

//define('_TASK_TYPE_NORMAL',0);
//define('_TASK_TYPE_DEPOSIT',1);


class module_job extends module_base{

	public $links;
	public $job_types;

    public $version = 2.474;
    //2.422 - create job with single customer auto select in drop down fix.
    //2.423 - fix for saving extra fields against renewed jobs.
    //2.424 - delete job from group
    //2.425 - permission tweak.
    //2.426 - label change, remove 'Assign '
    //2.43 - new theme layout.
    //2.431 - job emailing
    //2.432 - job discussion hook
    //2.433 - menu fix
    //2.434 - customise the Hours column header
    //2.435 - removed setting "New" status on incomplete jobs
    //2.436 - testing non-taxable items
    //2.437 - permission fix - allow job task edit without job edit. plus staff member listing changed to only with EDIT TASKS
    //2.438 - task defaults fix
    //2.439 - external link only visible to edit task permissions
    //2.44 - bit of a (hopeful) fix on job task edit permissions
    //2.441 - email to default contact first
    //2.442 - job date on renewals
    //2.443 - fix for logging hours against tasks
    //2.444 - creating new jobs - auto fill task name
    //2.445 - create invoice from job, better button!
    //2.446 - search by job type
    //2.447 - quick search on job name fixed
    //2.448 - job import and export fix
    //2.449 - additional sortable "invoice total" in jobs listing
    //2.450 - bug fix for job currency in "edit website" page
    //2.451 - incrementing job numbers (see advanced 'job_name_incrementing' option), added 'job_task_lock_invoiced_items' advanced option too
    //2.452 - more fields added to external_job template - same fields as invoice print (eg: customer name, customer group, extra fields, etc...)
    //2.453 - add manual task amount back to job invoice total only when invoice is not a merged invoice
    //2.454 - bug fix: non-billable items + non-taxable items causing issues with "tax amount" and "create invoice" amount
    //2.455 - bug fix: create invoice button
    //2.456 - email staff a copy of assigned jobs.
    //2.457 - if staff has no 'view' invoice permission, job prices are hidden.
    //2.458 - starting work on handling job deposits and customer credit
    //2.459 - bug fix - import job tasks.
    //2.460 - job status fix
    //2.461 - currency fixes and email features
    //2.462 - better support for Job Quotes, added Quote Date. Alerts to homepage.
    //2.463 - dashboard link permission fixes
    //2.464 - choose different templates upon emailing a job to staff/customer.
    //2.465 - job finance linking
    //2.466 - mobile updates
    //2.467 - starting work on Job Products
    //2.468 - extra fields update - show in main listing option
    //2.469 - update for job discussion
    //2.47 - speed improvements
    //2.471 - search by completed/not-completed/quoted status
    //2.472 - bug fix: assigning job to a new customer when already assigned to a website
    //2.473 - delete task defaults by saving an empty task list.
    //2.474 - js improvement on editing tasks


    public static function can_i($actions,$name=false,$category=false,$module=false){
        if(!$module)$module=__CLASS__;
        return parent::can_i($actions,$name,$category,$module);
    }
	public static function get_class() {
        return __CLASS__;
    }

	public function init(){
		$this->links = array();
		$this->job_types = array();
		$this->module_name = "job";
		$this->module_position = 17;

        module_config::register_css('job','tasks.css');

	}

    public function pre_menu(){

        if($this->can_i('view','Jobs')){
            // only display if a customer has been created.
            if(isset($_REQUEST['customer_id']) && $_REQUEST['customer_id'] && $_REQUEST['customer_id']!='new'){
                // how many jobs?
                $jobs = $this->get_jobs(array('customer_id'=>$_REQUEST['customer_id']));
                $name = _l('Jobs');
                if(count($jobs)){
                    $name .= " <span class='menu_label'>".count($jobs)."</span> ";
                }
                $this->links[] = array(
                    "name"=>$name,
                    "p"=>"job_admin",
                    'args'=>array('job_id'=>false),
                    'holder_module' => 'customer', // which parent module this link will sit under.
                    'holder_module_page' => 'customer_admin_open',  // which page this link will be automatically added to.
                    'menu_include_parent' => 0,
                );
            }
            $this->links[] = array(
                "name"=>"Jobs",
                "p"=>"job_admin",
                'args'=>array('job_id'=>false),
            );
        }

    }


    public function ajax_search($search_key){
        // return results based on an ajax search.
        $ajax_results = array();
        $search_key = trim($search_key);
        if(strlen($search_key) > 3){
            $results = $this->get_jobs(array('generic'=>$search_key));
            if(count($results)){
                foreach($results as $result){
                    $match_string = _l('Job: ');
                    $match_string .= _shl($result['name'],$search_key);
                    $ajax_results [] = '<a href="'.$this->link_open($result['job_id']) . '">' . $match_string . '</a>';
                }
            }
        }
        return $ajax_results;
    }



    public function handle_hook($hook,&$calling_module=false,$show_all=false){
		switch($hook){
            case 'dashboard_widgets':
                // see finance for example of widget usage.
                break;
			case "home_alerts":
				$alerts = array();
                /*if(module_config::c('job_task_alerts',1)){
                    // find out any overdue tasks or jobs.
                    $sql = "SELECT t.*,p.name AS job_name FROM `"._DB_PREFIX."task` t ";
                    $sql .= " LEFT JOIN `"._DB_PREFIX."job` p USING (job_id) ";
                    $sql .= " WHERE t.date_due != '0000-00-00' AND t.date_due <= '".date('Y-m-d',strtotime('+'.module_config::c('alert_days_in_future',5).' days'))."' AND ((t.hours = 0 AND t.completed = 0) OR t.completed < t.hours)";
                    $tasks = qa($sql);
                    foreach($tasks as $task){
                        $alert_res = process_alert($task['date_due'], _l('Job: %s',$task['job_name']));
                        if($alert_res){
                            $alert_res['link'] = $this->link_open($task['job_id']);
                            $alert_res['name'] = $task['description'];
                            $alerts[] = $alert_res;
                        }
                    }
                }*/
                if($show_all || module_config::c('job_alerts',1)){
                    // find any jobs that are past the due date and dont have a finished date.
                    $sql = "SELECT * FROM `"._DB_PREFIX."job` p ";
                    $sql .= " WHERE p.date_due != '0000-00-00' AND p.date_due <= '".date('Y-m-d',strtotime('+'.module_config::c('alert_days_in_future',5).' days'))."' AND p.date_completed = '0000-00-00'";
                    $tasks = qa($sql);
                    foreach($tasks as $task){
                        $job_permission_check = self::get_job($task['job_id']);
                        if(!$job_permission_check || $job_permission_check['job_id']!=$task['job_id'])continue;
                        $alert_res = process_alert($task['date_due'], _l('Incomplete Job'));
                        if($alert_res){
                            $alert_res['link'] = $this->link_open($task['job_id'],false,$task);
                            $alert_res['name'] = $task['name'];
                            $alerts['jobincomplete'.$task['job_id']] = $alert_res;
                        }
                    }
				}
                if($show_all || module_config::c('job_start_alerts',1)){
                    // find any jobs that haven't started yet (ie: have a start date, but no completed tasks)
                    $sql = "SELECT * FROM `"._DB_PREFIX."job` p ";
                    $sql .= " WHERE p.date_completed = '0000-00-00' AND p.date_start <= '".date('Y-m-d',strtotime('+'.module_config::c('alert_days_in_future',5).' days'))."'";
                    $jobs = qa($sql);
                    foreach($jobs as $job){
                        $job_permission_check = self::get_job($job['job_id']);
                        if(!$job_permission_check || $job_permission_check['job_id']!=$job['job_id'])continue;
                        $tasks = self::get_tasks($job['job_id']);
                        $job_started = false;
                        foreach($tasks as $task){
                            if($task['fully_completed']){
                                $job_started = true;
                                break;
                            }
                        }
                        if(!$job_started){

                            $alert_res = process_alert($job['date_start'], _l('Job Not Started'));
                            if($alert_res){
                                $alert_res['link'] = $this->link_open($job['job_id'],false,$job);
                                $alert_res['name'] = $job['name'];
                                $alerts[] = $alert_res;
                            }
                        }else{
                            // do the same alert as above.
                            if(!isset($alerts['jobincomplete'.$job['job_id']])){
                                $alert_res = process_alert($job['date_start'], _l('Incomplete Job'));
                                if($alert_res){
                                    $alert_res['link'] = $this->link_open($job['job_id'],false,$job);
                                    $alert_res['name'] = $job['name'];
                                    $alerts['jobincomplete'.$job['job_id']] = $alert_res;
                                }
                            }
                        }
                    }
				}
                if(module_config::c('job_allow_quotes',1) && ($show_all || module_config::c('job_quote_alerts',1))){
                    // find any jobs that dont have a start date yet.
                    $sql = "SELECT * FROM `"._DB_PREFIX."job` p ";
                    $sql .= " WHERE p.date_quote != '0000-00-00' AND p.date_start = '0000-00-00'";
                    $tasks = qa($sql);
                    foreach($tasks as $task){
                        $job_permission_check = self::get_job($task['job_id']);
                        if(!$job_permission_check || $job_permission_check['job_id']!=$task['job_id'])continue;
                        $alert_res = process_alert($task['date_quote'], _l('Pending Job Quote'));
                        if($alert_res){
                            $alert_res['link'] = $this->link_open($task['job_id'],false,$task);
                            $alert_res['name'] = $task['name'];
                            $alerts[] = $alert_res;
                        }
                    }
				}
                if($show_all || module_config::c('job_invoice_alerts',1)){
                    // find any completed jobs that don't have an invoice.
                    $sql = "SELECT j.* FROM `"._DB_PREFIX."job` j ";
                    $sql .= " LEFT JOIN `"._DB_PREFIX."task` t USING (job_id) ";
                    $sql .= " LEFT JOIN `"._DB_PREFIX."invoice_item` ii ON t.task_id = ii.task_id ";
                    $sql .= " LEFT JOIN `"._DB_PREFIX."invoice` i ON ii.invoice_id = i.invoice_id  ";
                    $sql .= " WHERE i.invoice_id IS NULL AND (j.date_completed != '0000-00-00')";
                    $sql .= " GROUP BY j.job_id";
                    $res = qa($sql);
                    foreach($res as $r){
                        $job = $this->get_job($r['job_id']);
                        if($job && $job['job_id'] == $r['job_id'] && $job['total_amount_invoicable'] > 0 && module_invoice::can_i('create','Invoices')){
                            $alert_res = process_alert($r['date_completed'], _l('Please Generate Invoice'));
                            if($alert_res){
                                $alert_res['link'] = $this->link_open($r['job_id'],false,$r);
                                $alert_res['name'] = $r['name'];
                                $alerts[] = $alert_res;
                            }
                        }
                    }
                }
                if($show_all || module_config::c('job_renew_alerts',1)){
                    // find any jobs that have a renew date soon and have not been renewed.
                    $sql = "SELECT p.* FROM `"._DB_PREFIX."job` p ";
                    $sql .= " WHERE p.date_renew != '0000-00-00'";
                    $sql .= " AND p.date_renew <= '".date('Y-m-d',strtotime('+'.module_config::c('alert_days_in_future',5).' days'))."'";
                    $sql .= " AND (p.renew_job_id IS NULL OR p.renew_job_id = 0)";
                    $res = qa($sql);
                    foreach($res as $r){
                        $job_permission_check = self::get_job($r['job_id']);
                        if(!$job_permission_check || $job_permission_check['job_id']!=$r['job_id'])continue;
                        $alert_res = process_alert($r['date_renew'], _l('Job Renewal Pending'));
                        if($alert_res){
                            $alert_res['link'] = $this->link_open($r['job_id'],false,$r);
                            $alert_res['name'] = $r['name'];
                            // work out renewal period
                            if($r['date_start'] && $r['date_start'] != '0000-00-00'){
                                $time_diff = strtotime($r['date_renew']) - strtotime($r['date_start']);
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
                if($show_all || module_config::c('job_approval_alerts',1)){
                    $job_task_creation_permissions = self::get_job_task_creation_permissions();
                    if($job_task_creation_permissions == _JOB_TASK_CREATION_WITHOUT_APPROVAL){

                        // find any jobs that have tasks requiring approval
                        $sql = "SELECT p.job_id,p.name, t.date_updated, COUNT(t.task_id) AS approval_count FROM `"._DB_PREFIX."job` p ";
                        $sql .= " LEFT JOIN `"._DB_PREFIX."task` t ON p.job_id = t.job_id";
                        $sql .= " WHERE t.approval_required = 1";
                        $sql .= " GROUP BY p.job_id ";
                        $res = qa($sql);
                        foreach($res as $r){
                            $job_permission_check = self::get_job($r['job_id']);
                            if(!$job_permission_check || $job_permission_check['job_id']!=$r['job_id'])continue;
                            $alert_res = process_alert($r['date_updated'], _l('Tasks Require Approval'));
                            if($alert_res){
                                $alert_res['link'] = $this->link_open($r['job_id'],false,$r);
                                $alert_res['name'] = _l('%s tasks in %s',$r['approval_count'],$r['name']);
                                $alerts[] = $alert_res;
                            }
                        }
                    }
                }
				return $alerts;
				break;
        }
        return false;
    }

    public static function link_generate($job_id=false,$options=array(),$link_options=array()){

        $key = 'job_id';
        if($job_id === false && $link_options){
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
        if(!isset($options['type']))$options['type']='job';
        $options['page'] = 'job_admin';
        if(!isset($options['arguments'])){
            $options['arguments'] = array();
        }
        $options['module'] = 'job';

        $data = array();
        if(isset($options['data'])){
            $data = $options['data'];
        }

        if(isset($options['full']) && $options['full']){
            // only hit database if we need to print a full link with the name in it.
            if(!isset($options['data']) || !$options['data']){
                if((int)$job_id>0){
                    $data = self::get_job($job_id,false,true);
                }else{
                    $data = array();
                }
                $options['data'] = $data;
            }else{
                $data = $options['data'];
            }
            // what text should we display in this link?
            $options['text'] = (!isset($data['name'])||!trim($data['name'])) ? _l('N/A') : $data['name'];
            if(!$data||!$job_id||isset($data['_no_access'])){
                return $options['text'];
            }
        }else{
            if(isset($_REQUEST['customer_id']) && (int)$_REQUEST['customer_id']>0){
                $data['customer_id'] = (int)$_REQUEST['customer_id'];
            }
        }
        $options['text'] = isset($options['text']) ? ($options['text']) : ''; // htmlspecialchars is done in link_generatE() function
        // generate the arguments for this link
        $options['arguments']['job_id'] = $job_id;

        if(isset($data['customer_id']) && $data['customer_id']>0){
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
        ))){

            $bubble_to_module = false;
            /*
            if(!isset($options['full']) || !$options['full']){
                return '#';
            }else{
                return isset($options['text']) ? $options['text'] : _l('N/A');
            }
            */

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

	public static function link_open($job_id,$full=false,$data=array()){
        return self::link_generate($job_id,array('full'=>$full,'data'=>$data));
    }
	public static function link_ajax_task($job_id,$full=false){
        return self::link_generate($job_id,array('full'=>$full,'arguments'=>array('_process'=>'ajax_task')));
    }


    public static function link_public($job_id,$h=false){
        if($h){
            return md5('s3cret7hash for job '._UCM_FOLDER.' '.$job_id);
        }
        return full_link(_EXTERNAL_TUNNEL_REWRITE.'m.job/h.public/i.'.$job_id.'/hash.'.self::link_public($job_id,true));
    }

    public static function get_replace_fields($job_id,$job_data){

        $customer_data = module_customer::get_customer($job_data['customer_id']);
        $address_combined = array();
        if(isset($customer_data['customer_address'])){
            foreach($customer_data['customer_address'] as $key=>$val){
                if(strlen(trim($val)))$address_combined[$key] = $val;
            }
        }
        // do we use the primary contact or a specified contact on the job.
        if(isset($job_data['user_id']) && $job_data['user_id']){
            $contact_data = module_user::get_user($job_data['user_id']);
        }else{
            $contact_data = module_user::get_user($customer_data['primary_user_id']);
        }


        $data = array(
            'job_number' => htmlspecialchars($job_data['name']),
            'project_type' => _l(module_config::c('project_name_single','Website')),
            'print_link' => self::link_public($job_id),

            'title' => module_config::s('admin_system_name'),
            'due_date' => print_date($job_data['date_due']),
            'customer_details' => ' - todo - ',
            'customer_name' => $customer_data['customer_name'] ? htmlspecialchars($customer_data['customer_name']) : _l('N/A'),
            'customer_address' => htmlspecialchars(implode(', ',$address_combined)),
            'contact_name' => ($contact_data['name'] != $contact_data['email']) ? htmlspecialchars($contact_data['name'].' '.$contact_data['last_name']) : '',
            'contact_email' => htmlspecialchars($contact_data['email']),
            'contact_phone' => htmlspecialchars($contact_data['phone']),
            'contact_mobile' => htmlspecialchars($contact_data['mobile']),
        );

        $data = array_merge($data,$job_data);
        

        foreach($customer_data['customer_address'] as $key=>$val){
            $data['address_'.$key] = $val;
        }


        if(class_exists('module_group',false)){
            // get the customer groups
            $g = array();
            if((int)$job_data['customer_id']>0){
                foreach(module_group::get_groups_search(array(
                    'owner_table' => 'customer',
                    'owner_id' => $job_data['customer_id'],
                )) as $group){
                    $g[] = $group['name'];
                }
            }
            $data['customer_group'] = implode(', ',$g);
            // get the job groups
            $wg = array();
            $g = array();
            if($job_id>0){
                $job_data = module_job::get_job($job_id);
                foreach(module_group::get_groups_search(array(
                    'owner_table' => 'job',
                    'owner_id' => $job_id,
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
            $data['job_group'] = implode(', ',$g);
            $data['website_group'] = implode(', ',$wg);
        }

        // addition. find all extra keys for this job and add them in.
        // we also have to find any EMPTY extra fields, and add those in as well.
        $all_extra_fields = module_extra::get_defaults('job');
        foreach($all_extra_fields as $e){
            $data[$e['key']] = _l('N/A');
        }
        // and find the ones with values:
        $extras = module_extra::get_extras(array('owner_table'=>'job','owner_id'=>$job_id));
        foreach($extras as $e){
            $data[$e['extra_key']] = $e['extra'];
        }
        // also do this for customer fields
        if($job_data['customer_id']){
            $all_extra_fields = module_extra::get_defaults('customer');
            foreach($all_extra_fields as $e){
                $data[$e['key']] = _l('N/A');
            }
            $extras = module_extra::get_extras(array('owner_table'=>'customer','owner_id'=>$job_data['customer_id']));
            foreach($extras as $e){
                $data[$e['extra_key']] = $e['extra'];
            }
        }


        return $data;
    }

    public function external_hook($hook){

        switch($hook){
            case 'public':
                $job_id = (isset($_REQUEST['i'])) ? (int)$_REQUEST['i'] : false;
                $hash = (isset($_REQUEST['hash'])) ? trim($_REQUEST['hash']) : false;
                if($job_id && $hash){
                    $correct_hash = $this->link_public($job_id,true);
                    if($correct_hash == $hash){
                        // all good to print a receipt for this payment.
                        $job_data = $this->get_job($job_id);

                        if($job_data){
                            $job_data = self::get_replace_fields($job_id,$job_data);
                            module_template::init_template('external_job','{HEADER}<h2>Job Overview</h2>
Job Name: <strong>{JOB_NAME}</strong> <br/>
{PROJECT_TYPE} Name: <strong>{PROJECT_NAME}</strong> <br/>

<br/>
<h3>Task List: {TASK_PERCENT_COMPLETED}</h3> <br/>
{TASK_LIST}
<br/><br/>
{JOB_INVOICES}
','Used when displaying the external view of a job.','code');
                            // correct!
                            // load up the receipt template.
                            $template = module_template::get_template_by_key('external_job');
                            // generate the html for the task output
                            ob_start();
                            include('pages/job_public.php');
                            $public_html = ob_get_clean();
                            $job_data['task_list'] = $public_html;
                            // do we link the job name?
                            $job_data['header'] = '';
                            if(module_security::is_logged_in() && $this->can_i('edit','Jobs')){
                                $job_data['header'] = '<div style="text-align: center; padding: 0 0 10px 0; font-style: italic;">You can send this page to your customer as a quote or progress update (this message will be hidden).</div>';
                            }
                            // is this a job or a quote?
                            if($job_data['date_quote'] != '0000-00-00' && ($job_data['date_start']=='0000-00-00' && $job_data['date_completed']=='0000-00-00')){
                                $job_data['job_or_quote'] = _l('Quote');
                            }else{
                                $job_data['job_or_quote'] = _l('Job');
                            }

                            //$job_data['job_name'] = $job_data['name'];
                            $job_data['job_name'] = self::link_open($job_id,true);
                            // format some dates:
                            $job_data['date_quote'] = $job_data['date_quote'] == '0000-00-00' ? _l('N/A') : print_date($job_data['date_quote']);
                            $job_data['date_start'] = $job_data['date_start'] == '0000-00-00' ? _l('N/A') : print_date($job_data['date_start']);
                            $job_data['date_due'] = $job_data['date_due'] == '0000-00-00' ? _l('N/A') : print_date($job_data['date_due']);
                            $job_data['date_completed'] = $job_data['date_completed'] == '0000-00-00' ? _l('N/A') : print_date($job_data['date_completed']);
                            $job_data['TASK_PERCENT_COMPLETED'] = ($job_data['total_percent_complete']>0 ? _l('(%s%% completed)',$job_data['total_percent_complete']*100) : '');



                            $job_data['job_invoices'] = '';
                            $invoices = module_invoice::get_invoices(array('job_id'=>$job_id));
                            $job_data['project_type'] = _l(module_config::c('project_name_single','Website'));
                            $website_data = $job_data['website_id'] ? module_website::get_website($job_data['website_id']) : array();
                            $job_data['project_name'] = isset($website_data['name']) && strlen($website_data['name']) ? $website_data['name'] : _l('N/A');
                            if(count($invoices)){
                                $job_data['job_invoices'] .= '<h3>'._l('Job Invoices:').'</h3>';
                                $job_data['job_invoices'] .= '<ul>';
                                foreach($invoices as $invoice){
                                    $job_data['job_invoices'] .= '<li>';
                                    $invoice = module_invoice::get_invoice($invoice['invoice_id']);
                                    $job_data['job_invoices'] .=  module_invoice::link_open($invoice['invoice_id'],true);
                                    $job_data['job_invoices'] .=  "<br/>";
                                    $job_data['job_invoices'] .=  _l('Total: ').dollar($invoice['total_amount'],true,$invoice['currency_id']);
                                    $job_data['job_invoices'] .=  "<br/>";
                                    $job_data['job_invoices'] .=  '<span class="';
                                    if($invoice['total_amount_due']>0){
                                        $job_data['job_invoices'] .=  'error_text';
                                    }else{
                                        $job_data['job_invoices'] .=  'success_text';
                                    }
                                    $job_data['job_invoices'] .=  '">';
                                    if($invoice['total_amount_due']>0){
                                        $job_data['job_invoices'] .=  dollar($invoice['total_amount_due'],true,$invoice['currency_id']);
                                        $job_data['job_invoices'] .=  ' '._l('due');
                                    }else{
                                        $job_data['job_invoices'] .=  _l('All paid');
                                    }
                                    $job_data['job_invoices'] .=  '</span>';
                                    $job_data['job_invoices'] .=  "<br>";
                                    // view receipts:
                                    $payments = module_invoice::get_invoice_payments($invoice['invoice_id']);
                                    if(count($payments)){
                                        $job_data['job_invoices'] .=  "<ul>";
                                        foreach($payments as $invoice_payment_id => $invoice_payment_data){
                                            $job_data['job_invoices'] .=  "<li>";
                                            $job_data['job_invoices'] .=  '<a href="'. module_invoice::link_receipt($invoice_payment_data['invoice_payment_id']) .'" target="_blank">'._l('View Receipt for payment of %s',dollar($invoice_payment_data['amount'],true,$invoice_payment_data['currency_id'])).'</a>';
                                            $job_data['job_invoices'] .=  "</li>";
                                        }
                                        $job_data['job_invoices'] .=  "</ul>";
                                    }
                                    $job_data['job_invoices'] .= '</li>';
                                }
                                $job_data['job_invoices'] .= '</ul>';
                            }
                            $template->assign_values($job_data);
                            $template->page_title = $job_data['name'];
                            echo $template->render('pretty_html');
                        }
                    }
                }
                break;
        }
    }


	public function process(){
		$errors=array();
		if(isset($_REQUEST['butt_del']) && $_REQUEST['butt_del'] && $_REQUEST['job_id']){
            $data = self::get_job($_REQUEST['job_id']);
            if(module_form::confirm_delete('job_id',"Really delete job: ".$data['name'],self::link_open($_REQUEST['job_id']))){
                $this->delete_job($_REQUEST['job_id']);
                set_message("job deleted successfully");
                redirect_browser($this->link_open(false));
            }

		}else if("ajax_job_list" == $_REQUEST['_process']){

            $customer_id = isset($_REQUEST['customer_id']) ? (int)$_REQUEST['customer_id'] : 0;
            $res = module_job::get_jobs(array('customer_id'=>$customer_id));
            $options = array();
            foreach($res as $row){
                $options[$row['job_id']] = $row['name'];
            }
            echo json_encode($options);
            exit;

		}else if("ajax_task" == $_REQUEST['_process']){

            // we are requesting editing a task.
            $job_id = (int)$_REQUEST['job_id'];
            $job = self::get_job($job_id,true);
            $job_tasks = self::get_tasks($job_id);

            if($job['job_id'] != $job_id)exit; // no permissions.
            if(!self::can_i('edit','Job Tasks'))exit; // no permissions

            if(isset($_REQUEST['delete_task_log_id']) && (int)$_REQUEST['delete_task_log_id'] > 0){

                $task_id = (int)$_REQUEST['task_id'];
                $task_log_id = (int)$_REQUEST['delete_task_log_id'];
                $sql = "DELETE FROM `"._DB_PREFIX."task_log` WHERE task_id = '$task_id' AND task_log_id = '$task_log_id' LIMIT 1";
                query($sql);
                echo 'done';


            }else if(isset($_REQUEST['update_task_order'])){

                // updating the task orders for this task..
                $task_order = (array)$_REQUEST['task_order'];
                foreach($task_order as $task_id => $new_order){
                    if((int)$new_order>0 && isset($job_tasks[$task_id])){
                        update_insert('task_id',$task_id,'task',array(
                                               'task_order' => (int)$new_order,
                                                            ));
                    }
                }
                echo 'done';
            }else{

                $task_id = (int)$_REQUEST['task_id'];
                $task_data = $job_tasks[$task_id];
                $task_editable = !($task_data['invoiced']);

                $job_task_creation_permissions = module_job::get_job_task_creation_permissions();

                // todo - load this select box in via javascript from existing one on page.
                $staff_members = module_user::get_staff_members();
                $staff_member_rel = array();
                foreach($staff_members as $staff_member){
                    $staff_member_rel[$staff_member['user_id']] = $staff_member['name'];
                }

                if(isset($_REQUEST['get_preview'])){
                    $after_task_id = $task_id; // this will put it right back where it started.
                    $previous_task_id = 0;
                    $job_tasks = self::get_tasks($job_id);
                    foreach($job_tasks as $k=>$v){
                        // find out where this new task position is!
                        if($k==$task_id){
                            $after_task_id = $previous_task_id;
                            break;
                        }
                        $previous_task_id = $k;
                    }
                    $create_invoice_button = '';
                    if($job['total_amount_invoicable'] > 0 && module_invoice::can_i('create','Invoices')){
                        $create_invoice_button = '<a class="submit_button save_button ui-button" href="'.module_invoice::link_generate('new',array('arguments'=>array(
                            'job_id' => $job_id,
                        ))).'">'._l('Create %s Invoice',dollar($job['total_amount_invoicable'],true,$job['currency_id'])).'</a>
                        ';
                    }
                    $result = array(
                        'task_id' => $task_id,
                        'after_task_id' => $after_task_id,
                        'html' => self::generate_task_preview($job_id,$job,$task_id,$task_data),
                        'summary_html' => self::generate_job_summary($job_id,$job),
                        'create_invoice_button' => $create_invoice_button,
                    );
                    echo json_encode($result);
                }else{
                    $show_task_numbers = (module_config::c('job_show_task_numbers',1) && $job['auto_task_numbers'] != 2);
                    ob_start();
                    include('pages/ajax_task_edit.php');
                    $result = array(
                        'task_id' => $task_id,
                        'hours' => isset($_REQUEST['hours']) ? (float)$_REQUEST['hours'] : 0,
                        'html' => ob_get_clean(),
                        //'summary_html' => self::generate_job_summary($job_id,$job),
                    );
                    echo json_encode($result);
                }
            }

            exit;
		}else if("save_job_tasks_ajax" == $_REQUEST['_process']){

            // do everything via ajax. trickery!
            // dont bother saving the job. it's already created.

            $job_id = (int)$_REQUEST['job_id'];
            $result = $this->save_job_tasks($job_id,$_POST);
            $job_data = self::get_job($job_id,false);
            $new_status = self::update_job_completion_status($job_id);
            $new_status = addcslashes(htmlspecialchars($new_status),"'");
            module_cache::clear_cache();
            $new_job_data = self::get_job($job_id,false);


            // we now have to edit the parent DOM to reflect these changes.
            // what were we doing? adding a new task? editing an existing task?
            switch($result['status']){
                case 'created':
                    // we added a new task.
                    // add a new task to the bottom (OR MID WAY!) through the task list.
                    if((int)$result['task_id']>0){
                        ?>
                        <script type="text/javascript">
                            parent.refresh_task_preview(<?php echo (int)$result['task_id'];?>);
                            parent.clear_create_form();
                            parent.ucm.add_message('<?php _e('New task created successfully');?>');
                            parent.ucm.display_messages(true);
                            <?php if($job_data['status']!=$new_status){ ?>parent.jQuery('#status').val('<?php echo $new_status;?>').change();<?php } ?>
                            <?php if($new_job_data['date_completed']!=$job_data['date_completed']){ ?>parent.jQuery('#date_completed').val('<?php echo print_date($new_job_data['date_completed']);?>').change();<?php } ?>
                        </script>
                    <?php }else{
                        set_error('New task creation failed.');
                        ?>
                        <script type="text/javascript">
                            top.location.href = '<?php echo $this->link_open($_REQUEST['job_id']);?>&added=true';
                        </script>
                    <?php
                    }
                    break;
                case 'deleted':
                    // we deleted a task.
                    set_message('Task removed successfully');
                    ?>
                    <script type="text/javascript">
                        top.location.href = '<?php echo $this->link_open($_REQUEST['job_id']);?>';
                        <?php if($job_data['status']!=$new_status){ ?>parent.jQuery('#status').val('<?php echo $new_status;?>').change();<?php } ?>
                    </script>
                    <?php
                    break;
                case 'error':
                    set_error('Something happened while trying to save a task. Unknown error.');
                    // something happened, refresh the parent browser frame
                    ?>
                    <script type="text/javascript">
                        top.location.href = '<?php echo $this->link_open($_REQUEST['job_id']);?>';
                    </script>
                    <?php
                    break;
                case 'edited':
                    // we changed a task (ie: completed?);
                    // update this task above.
                    if((int)$result['task_id']>0){
                        ?>
                        <script type="text/javascript">
                            parent.canceledittask();
                            //parent.refresh_task_preview(<?php echo (int)$result['task_id'];?>);
                            parent.ucm.add_message('<?php _e('Task saved successfully');?>');
                            parent.ucm.display_messages(true);
                            <?php if($job_data['status']!=$new_status){ ?>parent.jQuery('#status').val('<?php echo $new_status;?>').change();<?php } ?>
                            <?php if($new_job_data['date_completed']!=$job_data['date_completed']){ ?>parent.jQuery('#date_completed').val('<?php echo print_date($new_job_data['date_completed']);?>').change();<?php } ?>
                        </script>
                        <?php
                    }else{
                        ?>
                        <script type="text/javascript">
                            parent.canceledittask();
                            parent.ucm.add_error('<?php _e('Unable to save task');?>');
                            parent.ucm.display_messages(true);
                            <?php if($job_data['status']!=$new_status){ ?>parent.jQuery('#status').val('<?php echo $new_status;?>').change();<?php } ?>
                        </script>
                        <?php
                    }
                    break;
                default:
                    ?>
                    <script type="text/javascript">
                        parent.ucm.add_error('<?php _e('Unable to save task. Please check required fields.');?>');
                        parent.ucm.display_messages(true);
                    </script>
                    <?php
                    break;
            }

            exit;
		}else if("save_job" == $_REQUEST['_process']){



			$job_id = $this->save_job($_REQUEST['job_id'],$_POST);

            // look for the new tasks flag.
            if(isset($_REQUEST['default_task_list_id']) && isset($_REQUEST['default_tasks_action'])){
                switch($_REQUEST['default_tasks_action']){
                    case 'insert_default':
                        if((int)$_REQUEST['default_task_list_id']>0){
                            $default = self::get_default_task($_REQUEST['default_task_list_id']);
                            $task_data = $default['task_data'];
                            $new_task_data = array('job_task' => array());
                            foreach($task_data as $task){
                                $task['job_id']=$job_id;
                                if($task['date_due'] && $task['date_due']!='0000-00-00'){
                                    $diff_time = strtotime($task['date_due']) - $task['saved_time'];
                                    $task['date_due'] = date('Y-m-d',time() + $diff_time);
                                }
                                $new_task_data['job_task'][]=$task;
                            }
                            $this->save_job_tasks($job_id,$new_task_data);
                        }
                        break;
                    case 'save_default':
                        $new_default_name = trim($_REQUEST['default_task_list_id']);
                        if($new_default_name!=''){
                            // time to save it!
                            $task_data = self::get_tasks($job_id);
                            $cached_task_data = array();
                            foreach($task_data as $task){
                                unset($task['task_id']);
                                unset($task['date_done']);
                                unset($task['invoice_id']);
                                unset($task['task_order']);
                                unset($task['create_user_id']);
                                unset($task['update_user_id']);
                                unset($task['date_created']);
                                unset($task['date_updated']);
                                $task['saved_time'] = time();
                                $cached_task_data[] = $task;

                                /*$cached_task_data[] = array(
                                    'hours' => $task['hours'],
                                    'amount' => $task['amount'],
                                    'billable' => $task['billable'],
                                    'fully_completed' => $task['fully_completed'],
                                    'description' => $task['description'],
                                    'long_description' => $task['long_description'],
                                    'date_due' => $task['date_due'],
                                    'user_id' => $task['user_id'],
                                    'approval_required' => $task['approval_required'],
                                    'task_order' => $task['task_order'],
                                    'saved_time' => time(),
                                );*/
                            }
                            self::save_default_tasks((int)$_REQUEST['default_task_list_id'],$new_default_name,$cached_task_data);
                            unset($task_data);
                        }
                        break;
                }
            }

            // check if we are generating any renewals
            if(isset($_REQUEST['generate_renewal']) && $_REQUEST['generate_renewal'] > 0){
                $job = $this->get_job($job_id);
                if(strtotime($job['date_renew']) <= strtotime('+'.module_config::c('alert_days_in_future',5).' days')){
                    // /we are allowed to renew.
                    unset($job['job_id']);
                    // work out the difference in start date and end date and add that new renewl date to the new order.
                    $time_diff = strtotime($job['date_renew']) - strtotime($job['date_start']);
                    if($time_diff > 0){
                        // our renewal date is something in the future.
                        if(!$job['date_start'] || $job['date_start'] == '0000-00-00'){
                            set_message('Please set a job start date before renewing');
                            redirect_browser($this->link_open($job_id));
                        }
                        // work out the next renewal date.
                        $new_renewal_date = date('Y-m-d',strtotime($job['date_renew'])+$time_diff);

                        $job['date_quote'] = $job['date_renew'];
                        $job['date_start'] = $job['date_renew'];
                        $job['date_due'] = $job['date_renew'];
                        $job['date_renew'] = $new_renewal_date;
                        $job['status'] = module_config::s('job_status_default','New');
                        $job['date_completed'] = '';
                        // todo: copy the "more" listings over to the new job
                        // todo: copy any notes across to the new listing.


                        // hack to copy the 'extra' fields across to the new invoice.
                        // save_invoice() does the extra handling, and if we don't do this
                        // then it will move the extra fields from the original invoice to this new invoice.
                        $owner_table = 'job';
                        if(isset($_REQUEST['extra_'.$owner_table.'_field']) && is_array($_REQUEST['extra_'.$owner_table.'_field'])){
                            $x=1;
                            foreach($_REQUEST['extra_'.$owner_table.'_field'] as $extra_id => $extra_data){
                                $_REQUEST['extra_'.$owner_table.'_field']['new'.$x] = $extra_data;
                                unset($_REQUEST['extra_'.$owner_table.'_field'][$extra_id]);
                            }
                        }
                        $new_job_id = $this->save_job('new',$job);
                        if($new_job_id){
                            // now we create the tasks
                            $tasks = $this->get_tasks($job_id);
                            foreach($tasks as $task){
                                unset($task['task_id']);
                                //$task['completed'] = 0;
                                $task['job_id'] = $new_job_id;
                                $task['date_due'] = $job['date_due'];
                                update_insert('task_id','new','task',$task);
                            }
                            // link this up with the old one.
                            update_insert('job_id',$job_id,'job',array('renew_job_id'=>$new_job_id));
                        }
                        set_message("Job renewed successfully");
                        redirect_browser($this->link_open($new_job_id));
                    }
                }
            }

            if(isset($_REQUEST['butt_create_deposit']) && isset($_REQUEST['job_deposit']) && $_REQUEST['job_deposit'] > 0){
                // create an invoice for this job.
                $url = module_invoice::link_generate('new',array('arguments'=>array(
                    'job_id' => $job_id,
                    'as_deposit' => 1,
                    'amount_due' => $_REQUEST['job_deposit'],
                    'description' => _l('Deposit for job: %s',$_POST['name']), // bad
                )));
                redirect_browser($url);
            }

            set_message("Job saved successfully");
            redirect_browser($this->link_open($job_id));


		}
		if(!count($errors)){
			redirect_browser($_REQUEST['_redirect']);
			exit;
		}
		print_error($errors,true);
	}


	public static function get_jobs($search=array()){
		// limit based on customer id
		/*if(!isset($_REQUEST['customer_id']) || !(int)$_REQUEST['customer_id']){
			return array();
		}*/
		// build up a custom search sql query based on the provided search fields
		$sql = "SELECT u.*,u.job_id AS id ";
        $sql .= ", u.name AS name ";
        $sql .= ", c.customer_name ";
        $sql .= ", w.name AS website_name";// for export
        $sql .= ", us.name AS staff_member";// for export
        $from = " FROM `"._DB_PREFIX."job` u ";
        $from .= " LEFT JOIN `"._DB_PREFIX."customer` c USING (customer_id)";
        $from .= " LEFT JOIN `"._DB_PREFIX."website` w ON u.website_id = w.website_id"; // for export
        $from .= " LEFT JOIN `"._DB_PREFIX."user` us ON u.user_id = us.user_id"; // for export
		$where = " WHERE 1 ";
		if(isset($search['generic']) && $search['generic']){
			$str = mysql_real_escape_string($search['generic']);
			$where .= " AND ( ";
			$where .= " u.name LIKE '%$str%' "; //OR ";
			//$where .= " u.url LIKE '%$str%'  ";
			$where .= ' ) ';
		}
        foreach(array('customer_id','website_id','renew_job_id','status','type') as $key){
            if(isset($search[$key]) && $search[$key] !== ''&& $search[$key] !== false){
                $str = mysql_real_escape_string($search[$key]);
                $where .= " AND u.`$key` = '$str'";
            }
        }
        if(isset($search['completed']) && (int)$search['completed']>0){
            switch($search['completed']){
                case 1:
                    // both complete and not complete jobs, dont modify query
                    break;
                case 2:
                    // only completed jobs.
                    $where .= " AND u.date_completed != '0000-00-00'";
                    break;
                case 3:
                    // only non-completed jobs.
                    $where .= " AND u.date_completed = '0000-00-00'";
                    break;
                case 4:
                    // only quoted jobs
                    $where .= " AND u.date_start = '0000-00-00' AND u.date_quote != '0000-00-00'";
                    break;
                case 5:
                    // only not started jobs
                    $where .= " AND u.date_start = '0000-00-00'";
                    break;
            }
        }
		$group_order = ' GROUP BY u.job_id ORDER BY u.name';


        switch(self::get_job_access_permissions()){
            case _JOB_ACCESS_ALL:

                break;
            case _JOB_ACCESS_ASSIGNED:
                // only assigned jobs!
                $from .= " LEFT JOIN `"._DB_PREFIX."task` t ON u.job_id = t.job_id ";
                $where .= " AND (u.user_id = ".(int)module_security::get_loggedin_id()." OR t.user_id = ".(int)module_security::get_loggedin_id().")";
                break;
            case _JOB_ACCESS_CUSTOMER:
                break;
        }

        // tie in with customer permissions to only get jobs from customers we can access.
        switch(module_customer::get_customer_data_access()){
            case _CUSTOMER_ACCESS_ALL:
                // all customers! so this means all jobs!
                break;
            case _CUSTOMER_ACCESS_CONTACTS:
                // we only want customers that are directly linked with the currently logged in user contact.

                $valid_customer_ids = module_security::get_customer_restrictions();
                if(is_array($valid_customer_ids) && count($valid_customer_ids)){
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

		$sql = $sql . $from . $where . $group_order;
        //echo $sql;
		$result = qa($sql);
		//module_security::filter_data_set("job",$result);
		return $result;
//		return get_multiple("job",$search,"job_id","fuzzy","name");

	}
    public static function get_task($job_id,$task_id){
        return get_single('task',array('job_id','task_id'),array($job_id,$task_id));
    }
    public static function get_tasks($job_id,$order_by='task'){
        if((int)$job_id<=0)return array();
        $sql = "SELECT t.*, t.task_id AS id, i.invoice_item_id AS invoiced, i.invoice_id AS invoice_id ";
        $sql .= ", SUM(tl.hours) AS `completed` ";
        $sql .= ", inv.name AS invoice_number";
        $sql .= ", u.name AS user_name";
        $sql .= ", j.name AS job_name";
        $sql .= " FROM `"._DB_PREFIX."task` t ";
        $sql .= " LEFT JOIN `"._DB_PREFIX."task_log` tl ON t.task_id = tl.task_id";
        $sql .= " LEFT JOIN `"._DB_PREFIX."invoice_item` i ON t.task_id = i.task_id ";
        $sql .= " LEFT JOIN `"._DB_PREFIX."invoice` inv ON i.invoice_id = inv.invoice_id ";
        $sql .= " LEFT JOIN `"._DB_PREFIX."user` u ON t.user_id = u.user_id ";
        $sql .= " LEFT JOIN `"._DB_PREFIX."job` j ON t.job_id = j.job_id";
        $sql .= " WHERE t.`job_id` = ".(int)$job_id;
        $sql .= " GROUP BY t.task_id ";
        switch($order_by){
            case 'task':
                $sql .= " ORDER BY t.task_order, t.date_due ASC ";
                break;
            case 'date':
                $sql .= " ORDER BY t.date_due ASC ";
                break;
        }
        return qa($sql,false);
		//return get_multiple("task",array('job_id'=>$job_id),"task_id","exact","task_id");

	}
    public static function get_invoicable_tasks($job_id){

        $job = self::get_job($job_id,false);

        $sql = "SELECT t.*, t.task_id AS id ";
        $sql .= " ,SUM(tl.hours) AS `completed` ";
        $sql .= " FROM `"._DB_PREFIX."task` t ";
        $sql .= " LEFT JOIN `"._DB_PREFIX."task_log` tl ON t.task_id = tl.task_id";
        $sql .= " LEFT JOIN `"._DB_PREFIX."invoice_item` i ON t.task_id = i.task_id";
        $sql .= " WHERE t.`job_id` = ".(int)$job_id;
        $sql .= " AND i.invoice_id IS NULL ";
        //$sql .= " AND `completed` > 0 ";
        //$sql .= " AND t.`billable` != 0 ";
        //$sql .= " AND `completed` >= t.`hours` ";
        if(module_config::c('job_task_log_all_hours',1)){
            $sql .= " AND `fully_completed` = 1";
        }
        $sql .= " GROUP BY t.task_id ";
        $sql .= " ORDER BY t.task_order ASC ";
        $res = qa($sql);
        foreach($res as $rid=>$r){
            // todo: are we billing the hours worked, or the hours quoted.

            if(module_config::c('job_task_log_all_hours',1)){
                // we have to have a "fully_completed" flag before invoicing.
                if(!$r['billable']){
                    // unbillable - pass onto invoice as a blank.
                    // todo: better ! pass through hours/amount so customer can see.
                    $res[$rid]['hours'] = 0;
                    $res[$rid]['amount'] = 0;
                }
            }else{
                // old way, only completed hour tasks or "fully_completed" tasks come through.
                if(!$r['billable']){
                    // unbillable - pass onto invoice as a blank.
                    // todo: better ! pass through hours/amount so customer can see.
                    $res[$rid]['hours'] = 0;
                    $res[$rid]['amount'] = 0;
                    $res[$rid]['fully_completed'] = 1;
                }else if ($r['hours'] <= 0 && $r['amount'] <= 0 && !$r['fully_completed']){
                    // no hours, no amount, and not fully completed. skip this one.
                    unset($res[$rid]);
                }else if($r['hours'] <= 0 && $r['amount'] > 0 && !$r['fully_completed']){
                    // no hours set. but we have an amount. and we are not completed.
                    // skip.
                    unset($res[$rid]);
                }else if($r['hours'] <= 0 && $r['fully_completed']){
                    // no hours, but we are fully completed.
                    // keep this one
                }else if ($r['hours'] > 0 && ($r['completed'] <= 0 || $r['completed'] < $r['hours'])){
                    // we haven't yet completed this task based on the hours.
                    unset($res[$rid]);
                }
            }

            if(module_config::c('job_invoice_show_date_range',1)){
                // check if this job is a renewable job.
                if($job['date_renew']!='0000-00-00'){
                    $res[$rid]['custom_description'] = $r['description'] . ' ' . _l('(%s to %s)',print_date($job['date_start']),print_date(strtotime("-1 day",strtotime($job['date_renew']))));
                }
            }
        }
        return $res;
		//return get_multiple("task",array('job_id'=>$job_id),"task_id","exact","task_id");

	}
    public static function get_tasks_todo(){

        $tasks = array();

        // find all the tasks that are due for completion
        // sorted by due date.
        $sql = "SELECT ";
        $sql .= " SUM(tl.hours) AS `hours_completed` ";
        $sql .= " ,t.* ";
        $sql .= " FROM `"._DB_PREFIX."task` t ";
        $sql .= " LEFT JOIN `"._DB_PREFIX."task_log` tl ON t.task_id = tl.task_id";
        $sql .= " WHERE t.date_due != '0000-00-00' ";
        // from bharrison - task items based on logged in user
        $sql .= " AND ( t.`user_id` = 0 OR t.`user_id` = ".(int)module_security::get_loggedin_id().")";
        //$sql .= " AND ((t.hours = 0 AND `completed` = 0) OR `completed` < t.hours)";
        if(module_config::c('job_task_log_all_hours',1)){
            // tasks have to have a 'fully_completed' before they are done.
            $sql .= " AND t.fully_completed = 0";
        }
        $sql .= " AND t.approval_required = 0";
        $sql .= " GROUP BY t.task_id ";
        $sql .= " ORDER BY t.date_due ASC ";
        //$sql .= " LIMIT ".(int)module_config::c('todo_list_limit',6);
        $tasks_search = qa($sql);
        foreach($tasks_search as $task_id => $task){


            $job_permission_check = self::get_job($task['job_id']);
            if(!$job_permission_check || $job_permission_check['job_id']!=$task['job_id'])continue;

            if(module_config::c('job_task_log_all_hours',1)){
                // tasks have to have a 'fully_completed' before they are done.

            }else{
                // old way. based on logged hours:
                if( ($task['hours'] <= 0 && $task['fully_completed'] == 0) || ($task['hours'] > 0 && $task['hours_completed'] < $task['hours'])){
                    //keep
                }else{
                    continue;
                }
            }
            $tasks[$task_id] = $task;
            if(count($tasks)>module_config::c('todo_list_limit',6)){
                break;
            }
        }
        return $tasks;

	}
    public static function get_task_log($task_id){
		return get_multiple("task_log",array('task_id'=>$task_id),"task_log_id","exact","task_log_id");

	}
    
	public static function get_job($job_id,$full=true,$skip_permissions=false){
        $job_id = (int)$job_id;
        if($job_id<=0){
            $job=array();
        }else{
            $job = get_single("job","job_id",$job_id);
        }
        // check permissions
        if($job && isset($job['job_id']) && $job['job_id']==$job_id){
            switch(self::get_job_access_permissions()){
                case _JOB_ACCESS_ALL:

                    break;
                case _JOB_ACCESS_ASSIGNED:
                    // only assigned jobs!
                    $has_job_access = false;
                    if($job['user_id']==module_security::get_loggedin_id()){
                        $has_job_access=true;
                        break;
                    }
                    $tasks = module_job::get_tasks($job['job_id']);
                    foreach($tasks as $task){
                        if($task['user_id']==module_security::get_loggedin_id()){
                            $has_job_access=true;
                            break;
                        }
                    }
                    if(!$has_job_access){
                        if($skip_permissions){
                            $job['_no_access'] = true; // set a flag for custom processing. we check for this when calling get_customer with the skip permissions argument. (eg: in the ticket file listing link)
                        }else{
                            $job = false;
                        }
                    }
                    break;
                case _JOB_ACCESS_CUSTOMER:
                    // tie in with customer permissions to only get jobs from customers we can access.
                    $customers = module_customer::get_customers();
                    $has_job_access = false;
                    foreach($customers as $customer){
                        if($customer['customer_id']==$job['customer_id']){
                            $has_job_access = true;
                            break;
                        }
                    }
                    if(!$has_job_access){
                        if($skip_permissions){
                            $job['_no_access'] = true; // set a flag for custom processing. we check for this when calling get_customer with the skip permissions argument. (eg: in the ticket file listing link)
                        }else{
                            $job = false;
                        }
                    }
                    break;
            }
        }
        if(!$full)return $job;
        if(!$job){
            $customer_id = 0;
            if(isset($_REQUEST['customer_id']) && $_REQUEST['customer_id']){
                //
                $customer_id = (int)$_REQUEST['customer_id'];
                // find default website id to use.
                if(isset($_REQUEST['website_id'])){
                    $website_id = (int)$_REQUEST['website_id'];
                }else{

                }
            }
            $default_job_name = module_config::c('job_default_new_name','');
            if(module_config::c('job_name_incrementing',0)){
                $job_number = module_config::c('job_name_incrementing_next',1);
                // see if there is an job number matching this one.
                $this_job_number = $job_number;
                do{
                    $jobs = get_multiple('job',array('name'=>$this_job_number)); //'customer_id'=>$customer_id,
                    if(!count($jobs)){
                        $job_number = $this_job_number;
                    }else{
                        $this_job_number++;
                    }
                }while(count($jobs));
                module_config::save_config('job_name_incrementing_next',$job_number);
                $default_job_name = $job_number.$default_job_name;
            }
            
            $job = array(
                'job_id' => 'new',
                'customer_id' => $customer_id,
                'website_id' => (isset($_REQUEST['website_id'])? $_REQUEST['website_id'] : 0),
                'hourly_rate' => module_config::c('hourly_rate',60),
                'name' => $default_job_name,
                'date_quote' => date('Y-m-d'),
                'date_start' => module_config::c('job_allow_quotes',1) ? '' : date('Y-m-d'),
                'date_due' => '',
                'date_completed' => '',
                'date_renew' => '',
                'user_id' => module_security::get_loggedin_id(),
                'renew_job_id' => '',
                'status'  => module_config::s('job_status_default','New'),
                'type'  => module_config::s('job_type_default','Website Design'),
                'currency_id' => module_config::c('default_currency_id',1),
                'auto_task_numbers' => '0',
            );
            // some defaults from the db.
            $job['total_tax_rate'] = module_config::c('tax_percent',10);
            $job['total_tax_name'] = module_config::c('tax_name','TAX');
            if($customer_id>0){
                $customer_data = module_customer::get_customer($customer_id);
                if($customer_data && isset($customer_data['default_tax']) && $customer_data['default_tax'] >= 0){
                    $job['total_tax_rate'] = $customer_data['default_tax'];
                    $job['total_tax_name'] = $customer_data['default_tax_name'];
                }
            }
        }
        if($job){
            // work out total hours etc..
            $job['total_hours'] = 0;
            $job['total_hours_completed'] = 0;
            $job['total_hours_overworked'] = 0;
            $job['total_sub_amount'] = 0;
            $job['total_sub_amount_taxable'] = 0;
            $job['total_sub_amount_unbillable'] = 0;
            $job['total_sub_amount_invoicable'] = 0;
            $job['total_amount_invoicable'] = 0;
            $job['total_tasks_remain'] = 0;

            $job['total_amount_paid'] = 0;
            $job['total_amount_invoiced'] = 0;
            $job['total_amount_invoiced_deposit'] = 0;
            $job['total_amount_todo'] = 0;
            $job['total_amount_outstanding'] = 0;
            $job['total_amount_due'] = 0;
            $job['total_hours_remain'] = 0;
            $job['total_percent_complete'] = 0;

            $job['total_tax'] = 0;
            $job['total_tax_invoicable'] = 0;

            $job['invoice_discounts'] = 0;

            if($job_id>0){
                $non_hourly_job_count = $non_hourly_job_completed = 0;
                $tasks = self::get_tasks($job['job_id']);
                foreach($tasks as $task_id => $task){
                    if(module_config::c('job_task_log_all_hours',1)){
                        // jobs have to be marked fully_completd.
                        if(!$task['fully_completed']){
                            $job['total_tasks_remain']++;
                        }
                    }else{
                        if($task['amount'] != 0 && $task['completed'] <= 0){
                            $job['total_tasks_remain']++;
                        }else if($task['hours'] > 0 && $task['completed'] < $task['hours']){
                            $job['total_tasks_remain']++;
                        }
                    }
                    $tasks[$task_id]['sum_amount'] = 0;
                    if($task['amount'] != 0){
                        // we have a custom amount for this task
                        $tasks[$task_id]['sum_amount'] = $task['amount'];
                    }
                    if($task['hours'] > 0){
                        $job['total_hours'] += $task['hours'];
                        $task_completed_hours = min($task['hours'],$task['completed']);
                        if($task['fully_completed']){
                            // hack to record that we have worked 100% of this task.
                            $task_completed_hours = $task['hours'];
                        }
                        $job['total_hours_completed'] += $task_completed_hours;
                        if($task['completed'] > $task['hours']){
                            $job['total_hours_overworked'] += ($task['completed'] - $task['hours']);
                        }else if($task['completed'] > 0){
                            // underworked hours
                            $job['total_hours_overworked'] += ($task['completed'] - $task['hours']);
                        }
                        if($task['amount'] <= 0){
                            $tasks[$task_id]['sum_amount'] = ($task['hours'] * $job['hourly_rate']);
                        }
                    }else{
                        // it's a non-hourly task.
                        // work out if it's completed or not.
                        $non_hourly_job_count++;
                        if($task['fully_completed']){
                            $non_hourly_job_completed++;
                        }
                    }
                    if(!$task['invoiced'] && $task['billable'] &&
                       (
                           module_config::c('job_task_log_all_hours',1)
                           ||
                           ($task['hours'] > 0 && $task['completed'] > 0 && $task['completed'] >= $task['hours'])
                           ||
                           ($task['hours'] <= 0 && $task['fully_completed'] )
                       )
                    ){
                        if(module_config::c('job_task_log_all_hours',1)){
                            // a task has to be marked "fully_completeD" before it will be invoiced.
                            if($task['fully_completed']){
                                $job['total_sub_amount_invoicable'] += $tasks[$task_id]['sum_amount'];
                                if(module_config::c('tax_calculate_mode',0)==1){
                                    $job['total_tax_invoicable'] += round(($tasks[$task_id]['sum_amount'] * ($job['total_tax_rate'] / 100)),module_config::c('currency_decimal_places',2));
                                }
                            }
                        }else{
                            $job['total_sub_amount_invoicable'] += $tasks[$task_id]['sum_amount'];
                            if(module_config::c('tax_calculate_mode',0)==1){
                                $job['total_tax_invoicable'] += round(($tasks[$task_id]['sum_amount'] * ($job['total_tax_rate'] / 100)),module_config::c('currency_decimal_places',2));
                            }
                            //(min($task['hours'],$task['completed']) * $job['hourly_rate']);
                        }
                    }

                    if($task['taxable'] && $task['billable']){
                        $job['total_sub_amount_taxable'] += $tasks[$task_id]['sum_amount'];
                        if(module_config::c('tax_calculate_mode',0)==1){
                            $job['total_tax'] += round(($tasks[$task_id]['sum_amount'] * ($job['total_tax_rate'] / 100)),module_config::c('currency_decimal_places',2));
                        }
                    }
                    if($task['billable']){
                        $job['total_sub_amount'] += $tasks[$task_id]['sum_amount'];
                    }else{
                        $job['total_sub_amount_unbillable'] += $tasks[$task_id]['sum_amount'];
                    }
                }
                $job['total_hours_remain'] = $job['total_hours'] - $job['total_hours_completed'];
                if($job['total_hours'] > 0){
                    // total hours completed. work out job task based on hours completed.
                    $job['total_percent_complete'] = round($job['total_hours_completed'] / $job['total_hours'],2);
                }else if($non_hourly_job_count>0){
                    // work out job completed rate based on $non_hourly_job_completed and $non_hourly_job_count
                    $job['total_percent_complete'] = round($non_hourly_job_completed/$non_hourly_job_count,2);
                }


                // find any invoices
                $invoices = module_invoice::get_invoices(array('job_id'=>$job_id));
                foreach($invoices as $invoice){
                    $invoice = module_invoice::get_invoice($invoice['invoice_id']);
                    // we only ad up the invoiced tasks that are from this job
                    // an invoice could have added manually more items to it, so this would throw the price out.
                    $this_invoice = 0;
                    $this_invoice_taxable = 0;
                    $invoice_items = module_invoice::get_invoice_items($invoice['invoice_id']);
                    // first loop will find out of this is a merged invoice or not.
                    $merged_invoice = false;
                    foreach($invoice_items as $invoice_item){
                        if($invoice_item['task_id'] && !isset($tasks[$invoice_item['task_id']])){
                            $merged_invoice = true;
                        }
                    }
                    // if it's a merged invoice we don't add non-task-id items to the total.
                    // if its a normal non-merged invoice then we can add the non-task linked items to the total.
                    if(!$merged_invoice){
                        $this_invoice = $invoice['total_amount'];
                    }else{
                        foreach($invoice_items as $invoice_item){
                            if($invoice_item['task_id'] && isset($tasks[$invoice_item['task_id']]) && $tasks[$invoice_item['task_id']]['billable']){
                                $this_invoice += $tasks[$invoice_item['task_id']]['sum_amount'];
                                if($invoice_item['taxable']){
                                    $this_invoice_taxable += $tasks[$invoice_item['task_id']]['sum_amount'];
                                    if(module_config::c('tax_calculate_mode',0)==1){
                                        $this_invoice += round($tasks[$invoice_item['task_id']]['sum_amount'] * ($invoice['total_tax_rate'] / 100) ,module_config::c('currency_decimal_places',2));
                                    }
                                }
                            }
                        }
                    }
                    // any discounts ?
                    if($invoice['discount_amount']){
                        $this_invoice -= $invoice['discount_amount'];
                        $job['total_sub_amount'] -= $invoice['discount_amount'];
                        $job['invoice_discounts'] += $invoice['discount_amount'];
                    }
                    if(module_config::c('tax_calculate_mode',0)==0 && $this_invoice_taxable>0){
                        $this_invoice = ($this_invoice + ($this_invoice_taxable * ($invoice['total_tax_rate'] / 100)));
                    }
                    //print_r($invoice);

                    if($invoice['deposit_job_id'] == $job_id){
                        $job['total_amount_invoiced_deposit'] += $this_invoice;
                    }else{
                        $job['total_amount_invoiced'] += $this_invoice;
                        $job['total_amount_paid'] += min($invoice['total_amount_paid'],$this_invoice);
                    }


                }

                // todo: save these two values in the database so that future changes do not affect them.
                if(module_config::c('tax_calculate_mode',0)==0){
                    $job['total_tax'] = ($job['total_sub_amount_taxable'] * ($job['total_tax_rate'] / 100));
                    $job['total_tax_invoicable'] = $job['total_sub_amount_invoicable'] > 0 ? ($job['total_sub_amount_invoicable'] * ($job['total_tax_rate'] / 100)) : 0;
                }
                $job['total_amount'] = round($job['total_sub_amount'] + $job['total_tax'],module_config::c('currency_decimal_places',2));
                $job['total_amount_invoicable'] = $job['total_sub_amount_invoicable'] + $job['total_tax_invoicable']; // + ($job['total_sub_amount_invoicable'] * ($job['total_tax_rate'] / 100));

                $job['total_amount_due'] = $job['total_amount'] - $job['total_amount_paid']; //todo: chekc if this is wrong with non-invoicable tasks.
                $job['total_amount_outstanding'] = $job['total_amount_invoiced'] - $job['total_amount_paid'];


                $job['total_amount_todo'] = $job['total_amount'] -  $job['total_amount_invoiced'] - $job['total_amount_invoicable'];//$job['total_amount_paid'] -

            }


        }
		return $job;
	}
	public static function save_job($job_id,$data){
        if(isset($data['customer_id']) && $data['customer_id']>0){
            // check we have access to this customer from this job.
            $customer_check = module_customer::get_customer($data['customer_id']);
            if(!$customer_check || $customer_check['customer_id'] != $data['customer_id']){
                unset($data['customer_id']);
            }
        }
        if(isset($data['website_id']) && $data['website_id']){
            $website = module_website::get_website($data['website_id']);
            if($website && (int)$website['website_id'] > 0 && $website['website_id']==$data['website_id']){
                // website exists.
                // make this one match the website customer_id, or set teh website customer_id if it doesn't have any.
                if((int)$website['customer_id']>0){
                    if($data['customer_id']>0 && $data['customer_id'] != $website['customer_id']){
                        set_message('Changed this Job to match the Website customer');
                    }
                    $data['customer_id']=$website['customer_id'];
                }else if(isset($data['customer_id']) && $data['customer_id'] >0){
                    // set the website customer id to this as well.
                    update_insert('website_id',$website['website_id'],'website',array('customer_id'=>$data['customer_id']));
                }
            }
        }
        if((int)$job_id>0){
            $original_job_data = self::get_job($job_id,false);
            if(!$original_job_data || $original_job_data['job_id']!=$job_id){
                $original_job_data = array();
                $job_id = false;
            }
        }else{
            $original_job_data = array();
            $job_id = false;
        }

        // check create permissions.
        if(!$job_id && !self::can_i('create','Jobs')){
            // user not allowed to create jobs.
            set_error('Unable to create new Jobs');
            redirect_browser(self::link_open(false));
        }
        if(!(int)$job_id && module_config::c('job_name_incrementing',0)){
            // incrememnt next job number on save.
            $job_number = module_config::c('job_name_incrementing_next',1);
            module_config::save_config('job_name_incrementing_next',$job_number+1);
        }

		$job_id = update_insert("job_id",$job_id,"job",$data);
        if($job_id){
            $result = self::save_job_tasks($job_id,$data);
            $check_completed = true;
            switch($result['status']){
                case 'created':
                    // we added a new task.

                    break;
                case 'deleted':
                    // we deleted a task.

                    break;
                case 'edited':
                    // we changed a task (ie: completed?);

                    break;
                default:
                    // nothing changed.
                   // $check_completed = false;
                    break;
            }
            if($check_completed){
                self::update_job_completion_status($job_id);
            }
            if($original_job_data){
                // we check if the hourly rate has changed
                if(isset($data['hourly_rate']) && $data['hourly_rate'] != $original_job_data['hourly_rate']){
                    // update all the task hours.
                    $sql = "UPDATE `"._DB_PREFIX."task` SET `amount` = 0 WHERE `hours` > 0 AND job_id = ".(int)$job_id;
                    query($sql);

                }
                // check if the job assigned user id has changed.
                if(module_config::c('job_allow_staff_assignment',1)){
                    if(isset($data['user_id'])){ // && $data['user_id'] != $original_job_data['user_id']){
                        // user id has changed! update any that were the old user id.
                        $sql = "UPDATE `"._DB_PREFIX."task` SET `user_id` = ".(int)$data['user_id'].
                            " WHERE (`user_id` = ".(int)$original_job_data['user_id']." OR user_id = 0) AND job_id = ".(int)$job_id;
                        query($sql);
                    }
                }
                // check if the due date has changed.
                if(
                    isset($original_job_data['date_due']) && $original_job_data['date_due'] &&
                    isset($data['date_due']) && $data['date_due'] && $data['date_due'] != '0000-00-00' &&
                    $original_job_data['date_due'] != $data['date_due']
                ){
                    // the date has changed.
                    // update all the tasks with this new date.
                    $tasks = self::get_tasks($job_id);
                    foreach($tasks as $task){
                        if(!$task['date_due'] || $task['date_due'] == '0000-00-00'){
                            // no previously set task date. set it
                            update_insert('task_id',$task['task_id'],'task',array('date_due'=>$data['date_due']));
                        }else if($task['date_due'] == $original_job_data['date_due']){
                            // the date was the old date. do we change it?
                            // only change it on incompleted tasks.
                            $percentage = self::get_percentage($task);
                            if($percentage < 1 || (module_config::c('job_tasks_overwrite_completed_due_dates',0) && $percentage == 1)){
                                update_insert('task_id',$task['task_id'],'task',array('date_due'=>$data['date_due']));
                            }
                        }else{
                            // there's a new date
                            if(module_config::c('job_tasks_overwrite_diff_due_date',0)){
                                update_insert('task_id',$task['task_id'],'task',array('date_due'=>$data['date_due']));
                            }
                        }
                    }
                }
            }

        }
        module_extra::save_extras('job','job_id',$job_id);
		return $job_id;
	}


    public static function email_sent($job_id,$template_name){
        // add sent date if it doesn't exist
        self::add_history($job_id,_l('Job emailed to customer successfully'));
    }
    public static function staff_email_sent($options){
        $job_id = (int)$options['job_id'];
        // add sent date if it doesn't exist
        self::add_history($job_id,_l('Job emailed to staff successfully'));
    }

    public static function add_history($job_id,$message){
        module_note::save_note(array(
            'owner_table' => 'job',
            'owner_id' => $job_id,
            'note' => $message,
            'rel_data' => self::link_open($job_id),
            'note_time' => time(),
        ));
    }

    private static function save_job_tasks($job_id, $data) {

        $result = array(
            'status' => false,
        );

        $job_task_creation_permissions = self::get_job_task_creation_permissions();
        // check for new tasks or changed tasks.
        $tasks = self::get_tasks($job_id);
        if(isset($data['job_task']) && is_array($data['job_task'])){
            foreach($data['job_task'] as $task_id => $task_data){
                $original_task_id = $task_id;
                $task_id = (int)$task_id;
                if(!is_array($task_data))continue;
                if($task_id > 0 && !isset($tasks[$task_id])){
                    $task_id = 0; // creating a new task on this job.
                }
                if(!isset($task_data['description']) || $task_data['description'] == '' || $task_data['description'] == _TASK_DELETE_KEY){
                    if($task_id>0 && $task_data['description'] == _TASK_DELETE_KEY){
                        // remove task.
                        // but onyl remove it if it hasn't been invoiced.
                        if(isset($tasks[$task_id]) && $tasks[$task_id]['invoiced']){
                            // it has been invoiced! dont remove it.
                            set_error('Unable to remove an invoiced task');
                            $result['status'] = 'error';
                            break; // break out of loop saving tasks.
                        }else{
                            $sql = "DELETE FROM `"._DB_PREFIX."task` WHERE task_id = '$task_id' AND job_id = $job_id LIMIT 1";
                            query($sql);
                            $sql = "DELETE FROM `"._DB_PREFIX."task_log` WHERE task_id = '$task_id'";
                            query($sql);
                            $result['status'] = 'deleted';
                            $result['task_id'] = $task_id;
                        }
                    }
                    continue;
                }
                // add / save this task.
                $task_data['job_id'] = $job_id;
                // remove the amount of it equals the hourly rate.
                if(isset($task_data['amount']) && $task_data['amount'] > 0 && $task_data['hours'] > 0){
                    if(isset($data['hourly_rate']) && ($task_data['amount'] - ($task_data['hours'] * $data['hourly_rate']) == 0)){
                        unset($task_data['amount']);
                    }
                }
                // check if we haven't unticked a non-hourly task
                if(isset($task_data['fully_completed_t']) && $task_data['fully_completed_t']){
                    if(!isset($task_data['fully_completed']) || !$task_data['fully_completed']){
                        // we have unchecked that tickbox
                        $task_data['fully_completed'] = 0;
                    }else if(isset($tasks[$task_id]) && !$tasks[$task_id]['fully_completed']){
                        // we completed a preveiously incomplete task.
                        // hack: if we haven't logged any hours for this, we log the number of hours.
                        // if we have logged some hours already then we don't log anything extra.
                        // this is so they can log 0.5hours for a 1 hour completed task etc..
                        if($task_data['hours']>0 && !$task_data['log_hours']){
                            $logged_hours = 0;
                            foreach(get_multiple('task_log',array('job_id'=>$job_id,'task_id'=>$task_id)) as $task_log){
                                $logged_hours+=$task_log['hours'];
                            }
                            if($logged_hours==0){
                                $task_data['log_hours'] = $task_data['hours'];
                            }
                        }
                    }
                    $check_completed = true;
                }
                // check if we haven't unticked a billable task
                if(isset($task_data['billable_t']) && $task_data['billable_t'] && !isset($task_data['billable'])){
                    $task_data['billable'] = 0;
                }
                if(isset($task_data['taxable_t']) && $task_data['taxable_t'] && !isset($task_data['taxable'])){
                    $task_data['taxable'] = 0;
                }
                if(isset($task_data['completed']) && $task_data['completed'] > 0){
                    // check the completed date of all our tasks.
                    $check_completed = true;
                }
                if(!$task_id && isset($task_data['new_fully_completed']) && $task_data['new_fully_completed']){
                    $task_data['fully_completed'] = 1; // is this bad for set amount tasks?
                    $task_data['log_hours'] = $task_data['hours'];
                }

                // todo: move the task creation code into a public method so that the public user can add tasks to their jobs.
                if(!$task_id && module_security::is_logged_in() && !module_job::can_i('create','Job Tasks')){
                    continue; // dont allow new tasks.
                }

                // check if the user is allowed to create new tasks.

                // check the approval status of jobs
                switch($job_task_creation_permissions){
                    case _JOB_TASK_CREATION_NOT_ALLOWED:
                        if(!$task_id){
                            continue; // dont allow new tasks.
                        }
                        break;
                    case _JOB_TASK_CREATION_REQUIRES_APPROVAL:
                        $task_data['approval_required'] = 1;
                        break;
                    case _JOB_TASK_CREATION_WITHOUT_APPROVAL:
                         // no action required .
                        break;
                }

                $task_id = update_insert('task_id',$task_id,'task',$task_data); // todo - fix cross task job boundary issue. meh.
                $result['task_id'] = $task_id;
                if($task_id != $original_task_id){
                    $result['status'] = 'created';
                }else{
                    $result['status'] = 'edited';
                }

                if($task_id && isset($task_data['log_hours']) && (float)$task_data['log_hours'] > 0){
                    // we are increasing the task complete hours by the amount specified in log hours.
                    // log a new task record, and incrase the "completed" column.
                    //$original_task_data = $tasks[$task_id];
                    //$task_data['completed'] = $task_data['completed'] + $task_data['log_hours'];
                    update_insert('task_log_id','new','task_log',array(
                                                   'task_id' => $task_id,
                                                   'job_id' => $job_id,
                                                   'hours' => (float)$task_data['log_hours'],
                                                   'log_time' => time(),
                                                                 ));
                    $result['log_hours'] = $task_data['log_hours'];
                }
            }
        }

        return $result;
    }

	public static function delete_job($job_id){
		$job_id=(int)$job_id;
		if(_DEMO_MODE && $job_id == 1){
			return;
		}

        if((int)$job_id>0){
            $original_job_data = self::get_job($job_id);
            if(!$original_job_data || $original_job_data['job_id'] != $job_id){
                return false;
            }
        }

        if(!self::can_i('delete','Jobs')){
            return false;
        }
        
		$sql = "DELETE FROM "._DB_PREFIX."job WHERE job_id = '".$job_id."' LIMIT 1";
		$res = query($sql);
		$sql = "DELETE FROM "._DB_PREFIX."task WHERE job_id = '".$job_id."'";
		$res = query($sql);
		$sql = "DELETE FROM "._DB_PREFIX."task_log WHERE job_id = '".$job_id."'";
		$res = query($sql);
		$sql = "UPDATE "._DB_PREFIX."job SET renew_job_id = NULL WHERE renew_job_id = '".$job_id."'";
		$res = query($sql);

        if(class_exists('module_group',false)){
            module_group::delete_member($job_id,'job');
        }
        foreach(module_invoice::get_invoices(array('job_id'=>$job_id)) as $val){
            // only delete this invoice if it has no tasks left
            // it could be a combined invoice with other jobs now.
            $invoice_items = module_invoice::get_invoice_items($val['invoice_id']);
            if(!count($invoice_items)){
                module_invoice::delete_invoice($val['invoice_id']);
            }

        }
		module_note::note_delete("job",$job_id);
        module_extra::delete_extras('job','job_id',$job_id);

        hook_handle_callback('job_delete',$job_id);
	}
    public function login_link($job_id){
        return module_security::generate_auto_login_link($job_id);
    }

    public static function get_statuses(){
        $sql = "SELECT `status` FROM `"._DB_PREFIX."job` GROUP BY `status` ORDER BY `status`";
        $statuses = array();
        foreach(qa($sql) as $r){
            $statuses[$r['status']] = $r['status'];
        }
        return $statuses;
    }
    public static function get_types(){
        $sql = "SELECT `type` FROM `"._DB_PREFIX."job` GROUP BY `type` ORDER BY `type`";
        $statuses = array();
        foreach(qa($sql) as $r){
            $statuses[$r['type']] = $r['type'];
        }
        return $statuses;
    }



    public static function customer_id_changed($old_customer_id, $new_customer_id) {
        $old_customer_id = (int)$old_customer_id;
        $new_customer_id = (int)$new_customer_id;
        if($old_customer_id>0 && $new_customer_id>0){
            $sql = "UPDATE `"._DB_PREFIX."job` SET customer_id = ".$new_customer_id." WHERE customer_id = ".$old_customer_id;
            query($sql);
            module_invoice::customer_id_changed($old_customer_id,$new_customer_id);
            module_file::customer_id_changed($old_customer_id,$new_customer_id);
        }
    }

    public static function get_job_task_creation_permissions() {

        if(!module_security::is_logged_in()){
            //todo - option to allow guests to create tasks with approval? or not to create tasks at all.
            return _JOB_TASK_CREATION_REQUIRES_APPROVAL;
        }else if (class_exists('module_security',false)){
            return module_security::can_user_with_options(module_security::get_loggedin_id(),'Job Task Creation',array(
                _JOB_TASK_CREATION_WITHOUT_APPROVAL,
                _JOB_TASK_CREATION_REQUIRES_APPROVAL,
                _JOB_TASK_CREATION_NOT_ALLOWED,
            ));
        }else{
            return _JOB_TASK_CREATION_WITHOUT_APPROVAL; // default to all permissions.
        }
    }

    public static function get_job_access_permissions() {
        if (class_exists('module_security',false)){
            return module_security::can_user_with_options(module_security::get_loggedin_id(),'Job Data Access',array(
                _JOB_ACCESS_ALL,
                _JOB_ACCESS_ASSIGNED,
                _JOB_ACCESS_CUSTOMER,
            ));
        }else{
            return _JOB_ACCESS_ALL; // default to all permissions.
        }
    }


    public static function handle_import_row_debug($row, $add_to_group, $extra_options){
        return self::handle_import_row($row,true,$add_to_group,$extra_options);
    }

    /* Job Title	Hourly Rate	Start Date	Due Date	Completed Date	Website Name	Customer Name	Type	Status	Staff Member	Tax Name	Tax Percent	Renewal Date */
    public static function handle_import_row($row, $debug, $add_to_group, $extra_options){

        $debug_string = '';

        if(isset($row['job_id']) && (int)$row['job_id']>0){
            // check if this ID exists.
            $job = self::get_job($row['job_id']);
            if(!$job || $job['job_id'] != $row['job_id']){
                $row['job_id'] = 0;
            }
        }
        if(!isset($row['job_id']) || !$row['job_id']){
            $row['job_id'] = 0;
        }
        if(!isset($row['name']) || !strlen($row['name'])) {
            $debug_string .= _l('No job data to import');
            if($debug){
                echo $debug_string;
            }
            return false;
        }
        // duplicates.
        //print_r($extra_options);exit;
        if(isset($extra_options['duplicates']) && $extra_options['duplicates'] == 'ignore' && (int)$row['job_id']>0){
            if($debug){
                $debug_string .= _l('Skipping import, duplicate of job %s',self::link_open($row['job_id'],true));
                echo $debug_string;
            }
            // don't import duplicates
            return false;
        }
        $row['customer_id'] = 0; // todo - support importing of this id? nah
        if(isset($row['customer_name']) && strlen(trim($row['customer_name']))>0){
            // check if this customer exists.
            $customer = get_single('customer','customer_name',$row['customer_name']);
            if($customer && $customer['customer_id'] > 0){
                $row['customer_id'] = $customer['customer_id'];
                $debug_string .= _l('Linked to customer %s',module_customer::link_open($row['customer_id'],true)) .' ';
            }else{
                $debug_string .= _l('Create new customer: %s',htmlspecialchars($row['customer_name'])) .' ';
            }
        }else{
            $debug_string .= _l('No customer').' ';
        }
        if($row['job_id']){
            $debug_string .= _l('Replace existing job: %s',self::link_open($row['job_id'],true)).' ';
        }else{
            $debug_string .= _l('Insert new job: %s',htmlspecialchars($row['name'])).' ';
        }

        if($debug){
            echo $debug_string;
            return true;
        }
        if(isset($extra_options['duplicates']) && $extra_options['duplicates'] == 'ignore' && $row['customer_id'] > 0){
            // don't update customer record with new one.

        }else if((isset($row['customer_name']) && strlen(trim($row['customer_name']))>0) || $row['customer_id']>0){
            // update customer record with new one.
            $row['customer_id'] = update_insert('customer_id',$row['customer_id'],'customer',$row);

        }
        $job_id = (int)$row['job_id'];
        // check if this ID exists.
        $job = self::get_job($job_id);
        if(!$job || $job['job_id'] != $job_id){
            $job_id = 0;
        }
        $job_id = update_insert("job_id",$job_id,"job",$row);

        // handle any extra fields.
        $extra = array();
        foreach($row as $key=>$val){
            if(!strlen(trim($val)))continue;
            if(strpos($key,'extra:')!==false){
                $extra_key = str_replace('extra:','',$key);
                if(strlen($extra_key)){
                    $extra[$extra_key] = $val;
                }
            }
        }
        if($extra){
            foreach($extra as $extra_key => $extra_val){
                // does this one exist?
                $existing_extra = module_extra::get_extras(array('owner_table'=>'job','owner_id'=>$job_id,'extra_key'=>$extra_key));
                $extra_id = false;
                foreach($existing_extra as $key=>$val){
                    if($val['extra_key']==$extra_key){
                        $extra_id = $val['extra_id'];
                    }
                }
                $extra_db = array(
                    'extra_key' => $extra_key,
                    'extra' => $extra_val,
                    'owner_table' => 'job',
                    'owner_id' => $job_id,
                );
                $extra_id = (int)$extra_id;
                update_insert('extra_id',$extra_id,'extra',$extra_db);
            }
        }

        foreach($add_to_group as $group_id => $tf){
            module_group::add_to_group($group_id,$job_id,'job');
        }

        return $job_id;

    }

    public static function handle_import($data,$add_to_group,$extra_options){

        // woo! we're doing an import.
        $count = 0;
        // first we find any matching existing jobs. skipping duplicates if option is set.
        foreach($data as $rowid => $row){
            if(self::handle_import_row($row, false, $add_to_group, $extra_options)){
                $count++;
            }
        }
        return $count;


    }
    
    public static function handle_import_tasks($data,$add_to_group){

        $import_options = json_decode(base64_decode($_REQUEST['import_options']),true);
        $job_id = (int)$import_options['job_id'];
        if(!$import_options || !is_array($import_options) || $job_id<=0){
            echo 'Sorry import failed. Please try again';
            exit;
        }
        $existing_tasks = self::get_tasks($job_id);
        $existing_staff = module_user::get_staff_members();


        // woo! we're doing an import.
        // make sure we have a job id


        foreach($data as $rowid => $row){
            $row['job_id'] = $job_id;
            // check for required fields
            if(!isset($row['description']) || !trim($row['description'])){
                unset($data[$rowid]);
                continue;
            }
            if(!isset($row['task_id']) || !$row['task_id']){
                $data[$rowid]['task_id'] = 0;
            }
            // make sure this task id exists in the system against this job.
            if($data[$rowid]['task_id'] > 0){
                if(!isset($existing_tasks[$data[$rowid]['task_id']])){
                    $data[$rowid]['task_id'] = 0; // create a new task.
                    // this stops them updating a task in another job.
                }
            }
            if(!$data[$rowid]['task_id'] && $row['description']){
                // search for a task based on this name. dont want duplicates in the system.
                $existing_task = get_single('task',array('job_id','description'),array($job_id,$row['description']));
                if($existing_task){
                    $data[$rowid]['task_id'] = $existing_task['task_id'];
                }
            }

            // we have to save the user_name specially.
            /*if(isset($row['user_name']) && $row['user_name']){
                // see if this staff member exists.
                foreach($existing_staff as $staff_member){
                    if(strtolower($staff_member['name']) == strtolower($row['user_name'])){
                        $data[$rowid]['user_id'] = $staff_member['user_id'];
                    }
                }
            }*/

        }
        $c=0;
        $task_data = array();
        foreach($data as $rowid => $row){
            // now save the data.

            // we specify a "log_hours" value if we are logging more hours on a specific task.
            if(isset($row['completed']) && $row['completed'] > 0 && isset($row['hours']) && $row['hours']>0){
                if($row['task_id'] == 0){
                    // we are logging hours against a new task
                    $row['log_hours'] = $row['completed'];
                }else if($row['task_id']>0){
                    // we are adjusting hours on an existing task.
                    $existing_completed_hours = $existing_tasks[$row['task_id']]['completed'];
                    if($row['completed'] > $existing_completed_hours){
                        // we are logging additional hours against the job.
                        $row['log_hours'] = $row['completed'] - $existing_completed_hours;
                    }else if($row['completed'] < $existing_completed_hours){
                        // we are removing hours on this task!
                        // tricky!!
                        $sql = "DELETE FROM `"._DB_PREFIX."task_log` WHERE task_id = ".(int)$row['task_id'];
                        query($sql);
                        $row['log_hours'] = $row['completed'];
                    }
                }
            }

            if($row['task_id']>0){
                $task_id = $row['task_id'];
            }else{
                $task_id = 'new'.$c.'new';
                $c++;
            }

            $task_data[$task_id] = $row;

            /*foreach($add_to_group as $group_id => $tf){
                module_group::add_to_group($group_id,$task_id,'task');
            }*/
            
        }

        self::save_job($job_id,array(
                                  'job_id'=>$job_id,
                                  'job_task'=>$task_data,
                               ));


    }

    public static function generate_task_preview($job_id, $job, $task_id, $task_data, $task_editable=true){

        ob_start();
        // can we edit this task?
        // if its been invoiced we cannot edit it.
        if($task_editable && $task_data['invoiced'] && module_config::c('job_task_lock_invoiced_items',1)){
            $task_editable = false;// don't allow editable invoiced tasks
        }
        
        // todo-move this into a method so we can update it via ajax.


        $percentage = self::get_percentage($task_data);

        /*if($task_data['hours'] <= 0 && $task_data['fully_completed']){
            $percentage = 1;
        }else if ($task_data['completed'] > 0) {
            if($task_data['hours'] > 0){
                $percentage = round($task_data['completed'] / $task_data['hours'],2);
                $percentage = min(1,$percentage);
            }else{
                $percentage = 1;
            }
        }else{
            $percentage = 0;
        }*/

        $task_due_time = strtotime($task_data['date_due']);

        $show_task_numbers = (module_config::c('job_show_task_numbers',1) && $job['auto_task_numbers'] != 2);


        $staff_members = module_user::get_staff_members();
        $staff_member_rel = array();
        foreach($staff_members as $staff_member){
            $staff_member_rel[$staff_member['user_id']] = $staff_member['name'];
        }

        // hack to set the done_date if none exists.
        if($percentage>=1){
            if($task_data['task_id'] && isset($task_data['date_done']) && (!$task_data['date_done'] || $task_data['date_done'] == '0000-00-00')){
                $task_logs = module_job::get_task_log($task_id);
                $done_date = $task_data['date_updated'];
                foreach($task_logs as $task_log){
                    if($task_log['log_time'])$done_date = date('Y-m-d',$task_log['log_time']);
                }
                if($done_date){
                    update_insert('task_id',$task_data['task_id'],'task',array('date_done'=>$done_date));
                    $task_data['date_done'] = $done_date;
                }
            }
        }else{
            if($task_data['task_id'] && isset($task_data['date_done']) && $task_data['date_done'] && $task_data['date_done'] != '0000-00-00'){
                $done_date = '0000-00-00';
                update_insert('task_id',$task_data['task_id'],'task',array('date_done'=>$done_date));
                $task_data['date_done'] = $done_date;
            }
        }

        include('pages/ajax_task_preview.php');
        return ob_get_clean();
    }

    public static function get_default_tasks() {
        // we use the extra module for saving default task lists for now
        // why not? meh - use a new table later (similar to ticket default responses)
        $extra_fields = module_extra::get_extras(array('owner_table'=>'job_task_defaults','owner_id'=>1));
        $responses = array();
        foreach($extra_fields as $extra){
            $responses[$extra['extra_id']] = $extra['extra_key'];
        }
        return $responses;
    }
    public static function get_default_task($default_task_list_id) {
        $extra = module_extra::get_extra($default_task_list_id);
        return array(
            'default_task_list_id' => $extra['extra_id'],
            'name' => $extra['extra_key'],
            'task_data' => unserialize($extra['extra']),
        );
    }
    public static function save_default_tasks($default_task_list_id,$name,$task_data) {
        if((int)$default_task_list_id>0 && !count($task_data)){
            // deleting a task.
            delete_from_db('extra',array('extra_id','owner_table'),array($default_task_list_id,'job_task_defaults'));
            return false;
        }else{
            $extra_db = array(
                'extra' => serialize($task_data),
                'owner_table' => 'job_task_defaults',
                'owner_id' => 1,
            );
            if(!(int)$default_task_list_id){
                $extra_db['extra_key'] = $name; // don't update names of previous ones.
            }
            $extra_id = update_insert('extra_id',$default_task_list_id,'extra',$extra_db);
            return $extra_id;
        }
    }

    public static function get_percentage($task_data) {

        if(!$task_data['task_id'])return 0;
        $percentage = 0;
        if(module_config::c('job_task_log_all_hours',1)){
            if($task_data['fully_completed']){
                $percentage = 1;
            }else{
                // work out percentage based on hours.
                // default to 99% if not fully_completed is ticked yet.
                if ($task_data['completed'] > 0) {
                    if($task_data['hours'] > 0){
                        $percentage = round($task_data['completed'] / $task_data['hours'],2);
                        $percentage = min(1,$percentage);
                    }
                }
                if($percentage>=1){
                    // hack for invoiced tasks. mark this as fully completed.
                    if($task_data['invoiced']){
                        update_insert('task_id',$task_data['task_id'],'task',array('fully_completed'=>1));
                        $percentage = 1;
                    }else{
                        $percentage = 0.99;
                    }
                }
            }
        }else{
            if($task_data['hours'] <= 0 && $task_data['fully_completed']){
                $percentage = 1;
            }else if ($task_data['completed'] > 0) {
                if($task_data['hours'] > 0){
                    $percentage = round($task_data['completed'] / $task_data['hours'],2);
                    $percentage = min(1,$percentage);
                }else{
                    $percentage = 1;
                }
            }
        }
        return $percentage;
    }

    public static function generate_job_summary($job_id, $job) {
        $show_task_numbers = (module_config::c('job_show_task_numbers',1) && $job['auto_task_numbers'] != 2);
        ob_start();
        include('pages/ajax_job_summary.php');
        return ob_get_clean();
    }

    private static function update_job_completion_status($job_id) {
        module_cache::clear_cache();
        $data = self::get_job($job_id);
        $return_status = $data['status'];
        $tasks = self::get_tasks($job_id);
        $all_completed = (count($tasks)>0);
        foreach($tasks as $task){
            if(
                (
                    // tasks have to have a 'fully_completed' before they are done.
                    module_config::c('job_task_log_all_hours',1) && $task['fully_completed']
                )
                ||
                (
                    !module_config::c('job_task_log_all_hours',1) &&
                    (
                        $task['fully_completed']
                        ||
                        ($task['hours']>0 && ($task['completed'] >= $task['hours']))
                        ||
                        ($task['hours'] <= 0 && $task['completed'] > 0)
                    )
                )
            ){
                // this one is done!
            }else{
                $all_completed = false;
                break;
            }
        }
        if($all_completed){
            if(!isset($data['date_completed']) || !$data['date_completed'] || $data['date_completed'] == '0000-00-00'){
                // update, dont complete if no tasks.
                //if(count($tasks)){
                $return_status = ( $data['status'] == module_config::s('job_status_default','New') ? _l('Completed') : $data['status']);
                    update_insert("job_id",$job_id,"job",array(
                        'date_completed' => date('Y-m-d'),
                        'status' => $return_status,
                    ));
                //}
            }
        }else{
            // not completed. remove compelted date and reset the job status
            $return_status = ($data['status'] ==  _l('Completed')  ? module_config::s('job_status_default','New') : $data['status']);
            update_insert("job_id",$job_id,"job",array(
                'date_completed' => '0000-00-00',
                'status' => $return_status, //module_config::s('job_status_default','New'),
            ));
        }
        return $return_status;
    }


    public function get_upgrade_sql(){
        $sql = '';


        /*$installed_version = (string)$installed_version;
        $new_version = (string)$new_version;
        $options = array(
            '2' => array(
                '2.1' =>   'ALTER TABLE  `'._DB_PREFIX.'task` CHANGE  `project_id`  `job_id` INT( 11 ) NOT NULL;' .
                    'ALTER TABLE  `'._DB_PREFIX.'task_log` CHANGE  `project_id`  `job_id` INT( 11 ) NOT NULL;',
                '2.2' =>   'ALTER TABLE  `'._DB_PREFIX.'task` CHANGE  `project_id`  `job_id` INT( 11 ) NOT NULL;' .
                    'ALTER TABLE  `'._DB_PREFIX.'task_log` CHANGE  `project_id`  `job_id` INT( 11 ) NOT NULL;' .
                    'ALTER TABLE  `'._DB_PREFIX.'invoice` CHANGE  `project_id`  `job_id` INT( 11 ) NOT NULL;',
            ),
            '2.1' => array(
                '2.2' =>   'ALTER TABLE  `'._DB_PREFIX.'invoice` CHANGE  `project_id`  `job_id` INT( 11 ) NOT NULL;',
            ),

        );
        if(isset($options[$installed_version]) && isset($options[$installed_version][$new_version])){
            $sql = $options[$installed_version][$new_version];
        }*/


        $fields = get_fields('job');
        if(!isset($fields['auto_task_numbers'])){
            $sql .= 'ALTER TABLE  `'._DB_PREFIX.'job` ADD  `auto_task_numbers` TINYINT( 1 ) NOT NULL DEFAULT  \'0\' AFTER  `user_id`;';
        }
        if(!isset($fields['job_discussion'])){
            $sql .= 'ALTER TABLE  `'._DB_PREFIX.'job` ADD  `job_discussion` TINYINT( 1 ) NOT NULL DEFAULT  \'0\' AFTER `auto_task_numbers`;';
        }
        if(!isset($fields['currency_id'])){
            $sql .= 'ALTER TABLE  `'._DB_PREFIX.'job` ADD  `currency_id` int(11) NOT NULL DEFAULT  \'1\' AFTER  `user_id`;';
        }
        if(!isset($fields['date_quote'])){
            $sql .= 'ALTER TABLE  `'._DB_PREFIX.'job` ADD  `date_quote` date NOT NULL AFTER `total_tax_rate`;';
            $sql .= 'UPDATE `'._DB_PREFIX.'job` SET `date_quote` = `date_created`;';
        }

        $fields = get_fields('task');
        if(!isset($fields['long_description'])){
            $sql .= 'ALTER TABLE  `'._DB_PREFIX.'task` ADD `long_description` LONGTEXT NULL;';
        }
        if(!isset($fields['task_order'])){
            $sql .= 'ALTER TABLE  `'._DB_PREFIX.'task` ADD  `task_order` int(11) NOT NULL DEFAULT  \'0\' AFTER `approval_required`;';
        }
        if(!isset($fields['date_done'])){
            $sql .= 'ALTER TABLE  `'._DB_PREFIX.'task` ADD  `date_done` date NOT NULL AFTER `date_due`;';
        }
        if(!isset($fields['taxable'])){
            $sql .= 'ALTER TABLE  `'._DB_PREFIX.'task` ADD  `taxable` tinyint(1) NOT NULL DEFAULT \'1\' AFTER `amount`;';
        }
        /*if(!isset($fields['task_type'])){
            $sql .= 'ALTER TABLE  `'._DB_PREFIX.'task` ADD  `task_type` tinyint(2) NOT NULL DEFAULT \'0\' AFTER `task_order`;';
        }*/

        self::add_table_index('job','customer_id');
        self::add_table_index('job','user_id');
        self::add_table_index('job','website_id');
        self::add_table_index('task','job_id');
        self::add_table_index('task','user_id');
        self::add_table_index('task','invoice_id');
        self::add_table_index('task_log','task_id');
        self::add_table_index('task_log','job_Id');

        return $sql;
    }

    public function get_install_sql(){
        ob_start();
        ?>

    CREATE TABLE `<?php echo _DB_PREFIX; ?>job` (
    `job_id` int(11) NOT NULL auto_increment,
    `customer_id` INT(11) NULL,
    `website_id` INT(11) NULL,
    `hourly_rate` DECIMAL(10,2) NULL,
    `name` varchar(255) NOT NULL DEFAULT  '',
    `type` varchar(255) NOT NULL DEFAULT  '',
    `status` varchar(255) NOT NULL DEFAULT  '',
    `total_tax_name` varchar(20) NOT NULL DEFAULT  '',
    `total_tax_rate` DECIMAL(10,2) NULL,
    `date_quote` date NOT NULL,
    `date_start` date NOT NULL,
    `date_due` date NOT NULL,
    `date_done` date NOT NULL,
    `date_completed` date NOT NULL,
    `date_renew` date NOT NULL,
    `renew_job_id` INT(11) NULL,
    `user_id` INT NOT NULL DEFAULT  '0',
    `auto_task_numbers` TINYINT( 1 ) NOT NULL DEFAULT  '0',
    `job_discussion` TINYINT( 1 ) NOT NULL DEFAULT  '0',
    `currency_id` INT NOT NULL DEFAULT  '1',
    `create_user_id` int(11) NOT NULL,
    `update_user_id` int(11) NULL,
    `date_created` date NOT NULL,
    `date_updated` date NULL,
    PRIMARY KEY  (`job_id`),
        KEY `customer_id` (`customer_id`),
        KEY `user_id` (`user_id`),
        KEY `website_id` (`website_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;


    CREATE TABLE `<?php echo _DB_PREFIX; ?>task` (
    `task_id` int(11) NOT NULL AUTO_INCREMENT,
    `job_id` int(11) NULL,
    `hours` decimal(10,2) NOT NULL DEFAULT '0',
    `amount` decimal(10,2) NOT NULL DEFAULT '0',
    `taxable` tinyint(1) NOT NULL DEFAULT '1',
    `billable` tinyint(2) NOT NULL DEFAULT '1',
    `fully_completed` tinyint(2) NOT NULL DEFAULT '0',
    `description` text NULL,
    `long_description` LONGTEXT NULL,
    `date_due` date NOT NULL,
    `date_done` date NOT NULL,
    `invoice_id` int(11) NULL,
    `user_id` INT NOT NULL DEFAULT  '0',
    `approval_required` TINYINT( 1 ) NOT NULL DEFAULT  '0',
    `task_order` INT NOT NULL DEFAULT  '0',
    `create_user_id` int(11) NOT NULL,
    `update_user_id` int(11) NULL,
    `date_created` date NOT NULL,
    `date_updated` date NULL,
    PRIMARY KEY (`task_id`),
        KEY `job_id` (`job_id`),
        KEY `user_id` (`user_id`),
        KEY `invoice_id` (`invoice_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    CREATE TABLE `<?php echo _DB_PREFIX; ?>task_log` (
    `task_log_id` int(11) NOT NULL AUTO_INCREMENT,
    `task_id` int(11) NOT NULL,
    `job_id` int(11) NOT NULL,
    `hours` decimal(10,2) NOT NULL DEFAULT '0',
    `log_time` int(11) NULL,
    `create_user_id` int(11) NOT NULL,
    `update_user_id` int(11) NULL,
    `date_created` date NOT NULL,
    `date_updated` date NULL,
    PRIMARY KEY (`task_log_id`),
        KEY `task_id` (`task_id`),
        KEY `job_id` (`job_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    <?php
// todo: add default admin permissions.

        // `task_type` tinyint(2) NOT NULL DEFAULT  '0',

        return ob_get_clean();
    }

}