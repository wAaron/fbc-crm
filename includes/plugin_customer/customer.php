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


define('_CUSTOMER_ACCESS_ALL','All customers in system');
define('_CUSTOMER_ACCESS_CONTACTS','Only customer I am assigned to as a contact');
define('_CUSTOMER_ACCESS_TASKS','Only customers I am assigned to in a job');

define('_CUSTOMER_STATUS_OVERDUE',3);
define('_CUSTOMER_STATUS_OWING',2);
define('_CUSTOMER_STATUS_PAID',1);

class module_customer extends module_base{

	public $links;
	public $customer_types;
    public $customer_id;

    public static function can_i($actions,$name=false,$category=false,$module=false){
        if(!$module)$module=__CLASS__;
        return parent::can_i($actions,$name,$category,$module);
    }
	public static function get_class() {
        return __CLASS__;
    }
    public function init(){
		$this->links = array();
		$this->customer_types = array();
		$this->module_name = "customer";
		$this->module_position = 5.1;
        $this->version = 2.365;
        //2.31 - added group export
        //2.32 - added search by group
        //2.33 - search group permissions
        //2.331 - fix for group perms on main listing
        //2.332 - fix for customer_id null in get. retured an array with address.
        //2.333 - customer importing extra fields.
        //2.334 - customer contacts - all permissions on main customer listing.
        //2.335 - delete customer from group
        //2.336 - delete button on new customer page.
        //2.337 - import customers fixed.
        //2.34 - new feature: customer logo preview
        //2.35 - support for "ajax_contact_list" used in ticket edit area.
        //2.351 - more button on primary contact header in customer
        //2.352 - customer link htmlspecialchars fix
        //2.353 - showing notes in manual invoices
        //2.355 - importing user passwords and roles.
        //2.356 - subscriptions for customers
        //2.357 - bug fix on customers editing logos and searching by last name
        //2.358 - php5/6 fix
        //2.359 - create customer from ticket
        //2.360 - speed improvements
        //2.361 - address search fix for customers
        //2.362 - better moving customer contacts between customers
        //2.363 - show invoice list on main customer page (turn off with customer_list_show_invoices setting)
        //2.364 - extra fields update - show in main listing option
        //2.365 - support for customer signup system

	}

    public function pre_menu(){

		if($this->can_i('view','Customers')){

			$this->links['customers'] = array(
				"name"=>"Customers",
				"p"=>"customer_admin_list",
				"args"=>array('customer_id'=>false),
				/*'holder_module' => 'people', // which parent module this link will sit under.
				'holder_module_page' => 'people_admin',  // which page this link will be automatically added to.
				'menu_include_parent' => 0,*/
                //'current' => (isset($_REQUEST['m'][0]) && $_REQUEST['m'][0]=='customer'), // hack to get nested menu working correctly.
			);
            if(file_exists(dirname(__FILE__).'/pages/customer_signup.php')){
                $this->links['customer_settings'] = array(
                    "name"=>"Signup",
                    "p"=>"customer_signup",
                    'holder_module' => 'config', // which parent module this link will sit under.
                    'holder_module_page' => 'config_admin',  // which page this link will be automatically added to.
                    'menu_include_parent' => 0,
                );
            }
		}
    }

    public function ajax_search($search_key){
        // return results based on an ajax search.
        $ajax_results = array();
        $search_key = trim($search_key);
        if(strlen($search_key) > 2){
            //$sql = "SELECT * FROM `"._DB_PREFIX."customer` c WHERE ";
            //$sql .= " c.`customer_name` LIKE %$search_key%";
            //$results = qa($sql);
            $results = $this->get_customers(array('generic'=>$search_key));
            if(count($results)){
                foreach($results as $result){
                    // what part of this matched?
                    if(
                        preg_match('#'.preg_quote($search_key,'#').'#i',$result['name']) ||
                        preg_match('#'.preg_quote($search_key,'#').'#i',$result['last_name']) ||
                        preg_match('#'.preg_quote($search_key,'#').'#i',$result['phone'])
                    ){
                        // we matched the customer contact details.
                        $match_string = _l('Customer Contact: ');
                        $match_string .= _shl($result['customer_name'],$search_key);
                        $match_string .= ' - ';
                        $match_string .= _shl($result['name'],$search_key);
                        // hack
                        $_REQUEST['customer_id'] = $result['customer_id'];
                        $ajax_results [] = '<a href="'.module_user::link_open_contact($result['user_id']) . '">' . $match_string . '</a>';
                    }else{
                        $match_string = _l('Customer: ');
                        $match_string .= _shl($result['customer_name'],$search_key);
                        $ajax_results [] = '<a href="'.$this->link_open($result['customer_id']) . '">' . $match_string . '</a>';
                        //$ajax_results [] = $this->link_open($result['customer_id'],true);
                    }
                }
            }
        }
        return $ajax_results;
    }

    /** static stuff */

    
     public static function link_generate($customer_id=false,$options=array(),$link_options=array()){
        // we accept link options from a bubbled link call.
        // so we have to prepent our options to the start of the link_options array incase
        // anything bubbled up to this method.
        // build our options into the $options variable and array_unshift this onto the link_options at the end.
        $key = 'customer_id'; // the key we look for in data arrays, on in _REQUEST variables. for sub link building.

        // we check if we're bubbling from a sub link, and find the item id from a sub link
        if(${$key} === false && $link_options){
            foreach($link_options as $link_option){
                if(isset($link_option['data']) && isset($link_option['data'][$key])){
                    ${$key} = $link_option['data'][$key];
                    break;
                }
            }
            if(!${$key} && isset($_REQUEST[$key])){
                ${$key} = $_REQUEST[$key];
            }
            // check if this still exists.
            // this is a hack incase the customer is deleted, the invoices are still left behind.
            if(${$key} && $link_options){
                $test = self::get_customer(${$key});
                if(!$test || !isset($test[$key]) || $test[$key] != ${$key}){
                    return link_generate($link_options);
                }
            }
        }
        // grab the data for this particular link, so that any parent bubbled link_generate() methods
        // can access data from a sub item (eg: an id)

        if(isset($options['full']) && $options['full']){
            // only hit database if we need to print a full link with the name in it.
            if(!isset($options['data']) || !$options['data']){
                if((int)$customer_id>0){
                    $data = self::get_customer($customer_id,true);
                }else{
                    $data = array();
                }
                $options['data'] = $data;
            }else{
                $data = $options['data'];
            }
            // what text should we display in this link?
            $options['text'] = (!isset($data['customer_name'])||!trim($data['customer_name'])) ? _l('N/A') : $data['customer_name'];
            if(!$data||!$customer_id||isset($data['_no_access'])){
                return $options['text'];
            }
        }
        //$options['text'] = isset($options['text']) ? htmlspecialchars($options['text']) : '';
        // generate the arguments for this link
        $options['arguments'] = array(
            'customer_id' => $customer_id,
        );
        // generate the path (module & page) for this link
        $options['page'] = 'customer_admin_' . (($customer_id||$customer_id=='new') ? 'open' : 'list');
        $options['module'] = 'customer';

        // append this to our link options array, which is eventually passed to the
        // global link generate function which takes all these arguments and builds a link out of them.

        if(!self::can_i('view','Customers')){
            if(!isset($options['full']) || !$options['full']){
                return '#';
            }else{
                return isset($options['text']) ? $options['text'] : 'N/A';
            }
        }

         if(isset($data['customer_status'])){
             switch($data['customer_status']){
                 case _CUSTOMER_STATUS_OVERDUE:
                     $link_options['class'] = 'customer_overdue';
                     break;
                 case _CUSTOMER_STATUS_OWING:
                     $link_options['class'] = 'customer_owing';
                     break;
                 case _CUSTOMER_STATUS_PAID:
                     $link_options['class'] = 'customer_paid';
                     break;
             }
        }

        // optionally bubble this link up to a parent link_generate() method, so we can nest modules easily
        // change this variable to the one we are going to bubble up to:
        $bubble_to_module = false;
        /*$bubble_to_module = array(
            'module' => 'people',
            'argument' => 'people_id',
        );*/
        array_unshift($link_options,$options);
        if($bubble_to_module){
            global $plugins;
            return $plugins[$bubble_to_module['module']]->link_generate(false,array(),$link_options);
        }else{
            // return the link as-is, no more bubbling or anything.
            // pass this off to the global link_generate() function
            return link_generate($link_options);
        }
    }


	public static function link_open($customer_id,$full=false,$data=array()){
		return self::link_generate($customer_id,array('full'=>$full,'data'=>$data));
	}

	public static function get_pm() {
		$admins = module_user::get_users_by_group('PM');
		$admins_rel = array();
		foreach($admins as $admin){
			$admins_rel[$admin['user_id']] = $admin['name'];
		}
		return $admins_rel;
	}
	
	public static function get_sales() {
		$admins = module_user::get_users_by_group('SALES');
		$admins_rel = array();
		foreach($admins as $admin){
			$admins_rel[$admin['user_id']] = $admin['name'];
		}
		return $admins_rel;
	}

	public static function get_customers($search=array()){

        // work out what customers this user can access?
        $customer_access = self::get_customer_data_access();

		// build up a custom search sql query based on the provided search fields
		$sql = "SELECT c.*, c.customer_id AS id, u.user_id, u.name, u.last_name, u.phone ";
		$sql .= " , pu.user_id, pu.name AS primary_user_name, pu.last_name AS primary_user_last_name, pu.phone AS primary_user_phone, pu.email AS primary_user_email";
        $sql .= " , a.line_1, a.line_2, a.suburb, a.state, a.region, a.country, a.post_code ";
        $sql .= " FROM `"._DB_PREFIX."customer` c ";
		$where = "";
        if(defined('_SYSTEM_ID')) $sql .= " AND c.system_id = '"._SYSTEM_ID."' ";
		$group_order = '';
        $sql .= ' LEFT JOIN `'._DB_PREFIX."user` u ON c.customer_id = u.customer_id"; //c.primary_user_id = u.user_id AND 
        $sql .= ' LEFT JOIN `'._DB_PREFIX."user` pu ON c.primary_user_id = pu.user_id";
        $sql .= ' LEFT JOIN `'._DB_PREFIX."address` a ON c.customer_id = a.owner_id AND a.owner_table = 'customer' AND a.address_type = 'physical'";
		
        if(isset($search['customer_no']) && trim($search['customer_no'])){
            $str = mysql_real_escape_string(trim($search['customer_no']));
            // search the customer name, contact name, cusomter phone, contact phone, contact email.
            //$where .= 'AND u.customer_id IS NOT NULL AND ( ';
            $where .= " AND ( ";
            $where .= "c.customer_no LIKE '%$str%' ";
            $where .= ') ';
        }
        
        if(isset($search['core_completed']) && trim($search['core_completed'])){
        	$str = mysql_real_escape_string(trim($search['core_completed']));
			if ($str === 'yes') {
	        	$where .= " AND ( ";
	        	$where .= "c.core_completed >= 100 ";
	        	$where .= ') ';
			} else {
				$where .= " AND ( ";
				$where .= "c.core_completed < 100 ";
				$where .= ') ';
			}
        }
        
	        if(isset($search['full_completed']) && trim($search['full_completed'])){
        	$str = mysql_real_escape_string(trim($search['full_completed']));
			if ($str === 'yes') {
	        	$where .= " AND ( ";
	        	$where .= "c.full_completed >= 100 ";
	        	$where .= ') ';
			} else {
				$where .= " AND ( ";
				$where .= "c.full_completed < 100 ";
				$where .= ') ';
			}
        }

        if(isset($search['generic']) && trim($search['generic'])){
			$str = mysql_real_escape_string(trim($search['generic']));
			// search the customer name, contact name, cusomter phone, contact phone, contact email.
			//$where .= 'AND u.customer_id IS NOT NULL AND ( ';
			$where .= " AND ( ";
			$where .= "c.customer_name LIKE '%$str%' OR ";
			// $where .= "c.phone LIKE '%$str%' OR "; // search company phone number too.
			$where .= "u.name LIKE '%$str%' OR u.email LIKE '%$str%' OR ";
			$where .= "u.last_name LIKE '%$str%' OR ";
			$where .= "u.phone LIKE '%$str%' OR u.fax LIKE '%$str%' ";
			$where .= ') ';
		}
		if(isset($search['address']) && trim($search['address'])){
			$str = mysql_real_escape_string(trim($search['address']));
			// search all the customer site addresses.
			$where .= " AND ( ";
            $where .= " a.line_1 LIKE '%$str%' OR ";
            $where .= " a.line_2 LIKE '%$str%' OR ";
            $where .= " a.suburb LIKE '%$str%' OR ";
            $where .= " a.state LIKE '%$str%' OR ";
            $where .= " a.region LIKE '%$str%' OR ";
            $where .= " a.country LIKE '%$str%' OR ";
            $where .= " a.post_code LIKE '%$str%' ";
            $where .= " ) ";
		}
		if(isset($search['state_id']) && trim($search['state_id'])){
			$str = (int)$search['state_id'];
			// search all the customer site addresses.
			$sql .= " LEFT JOIN `"._DB_PREFIX."address` a ON (a.owner_id = c.customer_id)"; // swap join around? meh.
			$where .= " AND (a.state_id = '$str' AND a.owner_table = 'customer')";
		}
		if(isset($search['group_id']) && trim($search['group_id'])){
			$str = (int)$search['group_id'];
			$sql .= " LEFT JOIN `"._DB_PREFIX."group_member` gm ON (c.customer_id = gm.owner_id)";
			$where .= " AND (gm.group_id = '$str' AND gm.owner_table = 'customer')";
		}
        switch($customer_access){
            case _CUSTOMER_ACCESS_ALL:

                break;
            case _CUSTOMER_ACCESS_CONTACTS:
                // we only want customers that are directly linked with the currently logged in user contact.
//                if(isset($_SESSION['_restrict_customer_id']) && (int)$_SESSION['_restrict_customer_id']> 0){
                    // this session variable is set upon login, it holds their customer id.
                    // todo - share a user account between multiple customers!
                    //$where .= " AND c.customer_id IN (SELECT customer_id FROM )";
                $valid_customer_ids = module_security::get_customer_restrictions();
                if(count($valid_customer_ids)){
                    $where .= " AND ( ";
                    foreach($valid_customer_ids as $valid_customer_id){
                        $where .= " c.customer_id = '".(int)$valid_customer_id."' OR ";
                    }
                    $where = rtrim($where,'OR ');
                    $where .= " )";
                }
//                }
                break;
            case _CUSTOMER_ACCESS_TASKS:
                // only customers who have linked jobs that I am assigned to.
                $sql .= " LEFT JOIN `"._DB_PREFIX."job` j ON c.customer_id = j.customer_id ";
                $sql .= " LEFT JOIN `"._DB_PREFIX."task` t ON j.job_id = t.job_id ";
                $where .= " AND (j.user_id = ".(int)module_security::get_loggedin_id()." OR t.user_id = ".(int)module_security::get_loggedin_id().")";
                break;
        }
		
		$group_order = ' GROUP BY c.customer_id ORDER BY c.customer_name ASC'; // stop when multiple company sites have same region
		$sql = $sql . (strlen($where)>0 ? ' WHERE 1'.$where :''). $group_order;
		$result = qa($sql);
        /*if(!function_exists('sort_customers')){
            function sort_customers($a,$b){
                return strnatcasecmp($a['customer_name'],$b['customer_name']);
            }
        }
        uasort($result,'sort_customers');*/

        // we are filtering in the SQL code now..
		//module_security::filter_data_set("customer",$result);
        
		return $result;
		//return get_multiple("customer",$search,"customer_id","fuzzy","name");
	}

    private static $_customer_cache = array();

	public static function get_customer($customer_id,$skip_permissions=false){
        $customer_id = (int)$customer_id;
        if(isset(self::$_customer_cache[$customer_id]))return self::$_customer_cache[$customer_id];
        $customer = false;
        if($customer_id>0){
            $customer = get_single("customer","customer_id",$customer_id);

            // get their address.
            if($customer && isset($customer['customer_id']) && $customer['customer_id'] == $customer_id){
                $customer['customer_address'] = module_address::get_address($customer_id,'customer','physical',true);
            }

            switch(self::get_customer_data_access()){
                case _CUSTOMER_ACCESS_ALL:

                    break;
                case _CUSTOMER_ACCESS_CONTACTS:
                    // we only want customers that are directly linked with the currently logged in user contact.
                    //if(isset($_SESSION['_restrict_customer_id']) && (int)$_SESSION['_restrict_customer_id']> 0){
                        // this session variable is set upon login, it holds their customer id.
                        //$where .= " AND c.customer_id = '".(int)$_SESSION['_restrict_customer_id']."'";

                        $valid_customer_ids = module_security::get_customer_restrictions();
                        if(count($valid_customer_ids)){
                            $is_valid_customer = false;
                            foreach($valid_customer_ids as $valid_customer_id){
                                if($customer['customer_id'] == $valid_customer_id){
                                    $is_valid_customer = true;
                                }
                            }
                            if(!$is_valid_customer){
                                if($skip_permissions){
                                    $customer['_no_access'] = true; // set a flag for custom processing. we check for this when calling get_customer with the skip permissions argument. (eg: in the ticket file listing link)
                                }else{
                                    $customer = false;
                                }
                            }
                        }
                   // }
                    break;
                case _CUSTOMER_ACCESS_TASKS:
                    // only customers who have linked jobs that I am assigned to.
                    //$sql .= " LEFT JOIN `"._DB_PREFIX."job` j ON c.customer_id = j.customer_id ";
                    //$sql .= " LEFT JOIN `"._DB_PREFIX."task` t ON j.job_id = t.job_id ";
                    //$where .= " AND (j.user_id = ".(int)module_security::get_loggedin_id()." OR t.user_id = ".(int)module_security::get_loggedin_id().")";
                    $has_job_access = false;
                    $jobs = module_job::get_jobs(array('customer_id'=>$customer_id));
                    foreach($jobs as $job){
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
                    }
                    if(!$has_job_access){
                        if($skip_permissions){
                            $customer['_no_access'] = true; // set a flag for custom processing. we check for this when calling get_customer with the skip permissions argument. (eg: in the ticket file listing link)
                        }else{
                            $customer = false;
                        }
                    }
                    break;
            }
        }
        if(!$customer){
            $customer = array(
                'customer_id' => 'new',
                'customer_name' => '',
                'primary_user_id' => '',
                'credit' => '0',
                'customer_address' => array(),
            );
        }
		//$customer['customer_industry_id'] = get_multiple('customer_industry_rel',array('customer_id'=>$customer_id),'customer_industry_id');
		//echo $customer_id;print_r($customer);exit;
        self::$_customer_cache[$customer_id] = $customer;
		return $customer;
	}


    public static function print_customer_summary($customer_id,$output='html',$fields=array()) {
		global $plugins;
		$customer_data = $plugins['customer']->get_customer($customer_id);
		if(!$fields){
			$fields = array('customer_name');
		}
		$customer_output = '';
		foreach($fields as $key){
			if(isset($customer_data[$key]) && $customer_data[$key]){
				$customer_output .= $customer_data[$key].', ';
			}
		}
		$customer_output = rtrim($customer_output,', ');
		if($customer_data){
			switch($output){
				case 'text':
			        echo $customer_output;
			        break;
				case 'html':
					?>
					<span class="customer">
						<a href="<?php echo $plugins['customer']->link_open($customer_id);?>">
							<?php echo $customer_output;?>
						</a>
					</span>
					<?php
					break;
				case 'full':
					include('pages/customer_summary.php');
					break;
			}
		}
	}


    /** methods  */

    
	public function process(){
		if(isset($_REQUEST['butt_del']) && $_REQUEST['butt_del'] && $_REQUEST['customer_id'] && module_customer::can_i('delete','Customers')){
			$data = self::get_customer($_REQUEST['customer_id']);
            if($data['customer_id'] && $data['customer_id'] = $_REQUEST['customer_id']){
                if(module_form::confirm_delete('customer_id',"Really delete customer: ".$data['customer_name'],self::link_open($_REQUEST['customer_id']))){
                    $this->delete_customer($_REQUEST['customer_id']);
                    hook_handle_callback('customer_deleted',$_REQUEST['customer_id']);
                    set_message("Customer deleted successfully");
                    redirect_browser(self::link_open(false));
                }
            }
		}else if("ajax_contact_list" == $_REQUEST['_process']){

            $customer_id = isset($_REQUEST['customer_id']) ? (int)$_REQUEST['customer_id'] : 0;
            $res = module_user::get_contacts(array('customer_id'=>$customer_id));
            $options = array();
            foreach($res as $row){
                $options[$row['user_id']] = $row['name'].' '.$row['last_name'];
            }
            echo json_encode($options);
            exit;

		}else if("save_customer" == $_REQUEST['_process']){
			$customer_id = $this->save_customer($_REQUEST['customer_id'],$_POST);
            hook_handle_callback('customer_save',$customer_id);
			set_message("Customer saved successfully");
			redirect_browser(self::link_open($customer_id));
		}
	}

    public function load($customer_id){
        $data = self::get_customer($customer_id);
        foreach($data as $key=>$val){
            $this->$key = $val;
        }
        return $data;
    }
	public function save_customer($customer_id,$data){

        $customer_id = (int)$customer_id;
        if($customer_id>0){
            // check permissions
            $temp_customer = $this->get_customer($customer_id);
            if(!$temp_customer || $temp_customer['customer_id'] != $customer_id){
                $customer_id = false;
            }
        }

        if(isset($data['default_tax_system']) && $data['default_tax_system']){
            $data['default_tax'] = -1;
            $data['default_tax_name'] = '';
        }
        
        $core_fields = array("customer_name", "customer_no", "customer_main_pm", "customer_backup_pm", "customer_ex_salesman", "customer_current_salesman", "customer_level", "customer_type");
        
        $core_filled = 0;
        $core_sum = count($core_fields);
        foreach ($core_fields as $field) {
        	if (!empty($data[$field])) {
        		$core_filled++;
        	}
        }
        $data['core_completed'] =  floor($core_filled / $core_sum * 100);
        
        $full_fields = array('customer_status', 'customer_name', 'customer_no', 'customer_type', 'customer_from', 'cooperate_from', 'customer_main_prod', 'customer_pay_days', 'customer_pay_period', 'customer_success_story', 'customer_main_pm', 'customer_backup_pm', 'customer_full_name', 'customer_full_en', 'customer_ex_salesman', 'customer_current_salesman', 'customer_company_type', 'customer_level', 'customer_staff', 'company_size', 'customer_build_from', 'customer_vip', 'customer_vip_end', 'customer_vip_renew', 'customer_ticket_type', 'customer_ticket_info', 'translate_speed');
        
        $full_filled = 0;
        $full_sum = count($full_fields);
        foreach ($full_fields as $field) {
        	if (!empty($data[$field])) {
        		$full_filled++;
        	}
        }
        
        $data['full_completed'] =  floor($full_filled / $full_sum * 100);

		$customer_id = update_insert("customer_id",$customer_id,"customer",$data);
        if(isset($_REQUEST['user_id'])){
            $user_id = (int)$_REQUEST['user_id'];
            if($user_id>0){
                // check permissions
                $temp_user = module_user::get_user($user_id);
                if(!$temp_user || $temp_user['user_id'] != $user_id){
                    $user_id = false;
                }
            }
            // assign specified user_id to this customer.
            // could this be a problem?
            // maybe?
            // todo: think about security precautions here, maybe only allow admins to set primary contacts.
            $data['customer_id']=$customer_id;
            if(!$user_id){
                // hack to set the default role of a contact (if one is set in settings).
                $user_id = update_insert("user_id",false,"user",$data);
                $role_id = module_config::c('contact_default_role',0);
                if($role_id>0){
                    module_user::add_user_to_role($user_id,$role_id);
                }
                $this->set_primary_user_id($customer_id,$user_id);
            }else{
                // make sure this user is part of this customer.
                // wait! addition, we want to be able to move an existing customer contact to this new customer.
                $saved_user_id = false;
                if(isset($_REQUEST['move_user_id']) && (int)$_REQUEST['move_user_id'] && module_customer::can_i('create','Customers')){
                    $old_user = module_user::get_user((int)$_REQUEST['move_user_id']);
                    if($old_user && $old_user['user_id']==(int)$_REQUEST['move_user_id']){
                        $saved_user_id = $user_id = update_insert("user_id",$user_id,"user",$data);
                        hook_handle_callback('customer_contact_moved',$user_id,$old_user['customer_id'],$customer_id);
                        $this->set_primary_user_id($customer_id,$user_id);
                    }
                }else{
                    // save normally, only those linked to this account:
                    $users = module_user::get_contacts(array('customer_id'=>$customer_id));
                    foreach($users as $user){
                        if($user['user_id']==$user_id){
                            $saved_user_id = $user_id = update_insert("user_id",$user_id,"user",$data);
                            $this->set_primary_user_id($customer_id,$user_id);
                            break;
                        }
                    }
                }
                if(!$saved_user_id){
                    $this->set_primary_user_id($customer_id,0);
                }
            }
            // todo: move this functionality back into the user class.
            // maybe with a static save_user method ?
            if($user_id>0){
                module_extra::save_extras('user','user_id',$user_id);
            }
        }
		
		handle_hook("address_block_save",$this,"physical","customer","customer_id",$customer_id);
		//handle_hook("address_block_save",$this,"postal","customer","customer_id",$customer_id);
        module_extra::save_extras('customer','customer_id',$customer_id);

		return $customer_id;
	}

	public static function set_primary_user_id($customer_id,$user_id){
		update_insert('customer_id',$customer_id,'customer',array('primary_user_id'=>$user_id));
	}
	public function delete_customer($customer_id){
		$customer_id=(int)$customer_id;
        if($customer_id>0){
            $customer = self::get_customer($customer_id);
            if($customer && $customer['customer_id'] == $customer_id){
                $sql = "DELETE FROM "._DB_PREFIX."customer WHERE customer_id = '".$customer_id."' LIMIT 1";
                query($sql);
                if(class_exists('module_group',false)){
                    module_group::delete_member($customer_id,'customer');
                }
                foreach(module_user::get_contacts(array('customer_id'=>$customer_id)) as $val){
                    if($val['customer_id'] && $val['customer_id'] == $customer_id){
                        module_user::delete_user($val['user_id']);
                    }
                }
                foreach(module_website::get_websites(array('customer_id'=>$customer_id)) as $val){
                    if($val['customer_id'] && $val['customer_id'] == $customer_id){
                        module_website::delete_website($val['website_id']);
                    }
                }
                foreach(module_job::get_jobs(array('customer_id'=>$customer_id)) as $val){
                    if($val['customer_id'] && $val['customer_id'] == $customer_id){
                        module_job::delete_job($val['job_id']);
                    }
                }
                module_note::note_delete("customer",'customer_id',$customer_id);
                handle_hook("address_delete",$this,'all',"customer",'customer_id',$customer_id);
                handle_hook("file_delete",$this,"customer",'customer_id',$customer_id);
                module_extra::delete_extras('customer','customer_id',$customer_id);
            }
        }
	}

    public static function handle_import($data,$add_to_group){

        // woo! we're doing an import.

        // our first loop we go through and find matching customers by their "customer_name" (required field)
        // and then we assign that customer_id to the import data.
        // our second loop through if there is a customer_id we overwrite that existing customer with the import data (ignoring blanks).
        // if there is no customer id we create a new customer record :) awesome.

        foreach($data as $rowid => $row){
            if(!isset($row['customer_name']) || !trim($row['customer_name'])){
                unset($data[$rowid]);
                continue;
            }
            if(!isset($row['customer_id']) || !$row['customer_id']){
                $data[$rowid]['customer_id'] = 0;
            }

        }

        // now save the data.
        foreach($data as $rowid => $row){
            module_cache::clear_cache();
            $customer_id = isset($row['customer_id']) ? (int)$row['customer_id'] : 0;
            // check if this ID exists.
            if($customer_id > 0){
                $customer = self::get_customer($customer_id);
                if(!$customer || !isset($customer['customer_id']) || $customer['customer_id'] != $customer_id){
                    $customer_id = 0;
                }
            }
            if(!$customer_id){
                // search for a custoemr based on name.
                $customer = get_single('customer','customer_name',$row['customer_name']);
                //print_r($row); print_r($customer);echo '<hr>';
                if($customer && $customer['customer_id'] > 0){
                    $customer_id = $customer['customer_id'];
                }
            }
            $customer_id = update_insert("customer_id",$customer_id,"customer",$row);
            // see if we're updating an old contact, or adding a new primary contact.
            // match on name since that's a required field.
            $users = module_user::get_contacts(array('customer_id'=>$customer_id));
            $user_match = 0;
            foreach($users as $user){
                if($user['name']==$row['primary_user_name']){
                    $user_match = $user['user_id'];
                    break;
                }
            }
            $user_match = update_insert("user_id",$user_match,"user",array(
                                                     'customer_id'=>$customer_id,
                                                     'name' => $row['primary_user_name'],
                                                     'last_name' => $row['primary_user_last_name'],
                                                     'email' => $row['primary_user_email'],
                                                     'phone' => $row['primary_user_phone'],
                                                     'password' => isset($row['password']) && strlen($row['password']) ? md5(trim($row['password'])) : '',
                                                 ));
            if($user_match && isset($row['role']) && strlen(trim($row['role']))){
                // find this role name and assign it to this user.
                $role = module_security::get_roles(array('name'=>$row['role']));
                if($role){
                    $user_role = array_shift($role);
                    $role_id = $user_role['security_role_id'];
                    module_user::add_user_to_role($user_match,$role_id);
                }
            }
            self::set_primary_user_id($customer_id,$user_match);

            // do a hack to save address.
            $existing_address = module_address::get_address($customer_id,'customer','physical');
            $address_id = ($existing_address&&isset($existing_address['address_id'])) ? (int)$existing_address['address_id'] : 'new';
            $address = array_merge($row,array(
                                           'owner_id'=>$customer_id,
                                           'owner_table'=>'customer',
                                           'address_type'=>'physical',
                                        ));
            module_address::save_address($address_id,$address);

            foreach($add_to_group as $group_id => $tf){
                module_group::add_to_group($group_id,$customer_id,'customer');
            }

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
                    $existing_extra = module_extra::get_extras(array('owner_table'=>'customer','owner_id'=>$customer_id,'extra_key'=>$extra_key));
                    $extra_id = false;
                    foreach($existing_extra as $key=>$val){
                        if($val['extra_key']==$extra_key){
                            $extra_id = $val['extra_id'];
                        }
                    }
                    $extra_db = array(
                        'extra_key' => $extra_key,
                        'extra' => $extra_val,
                        'owner_table' => 'customer',
                        'owner_id' => $customer_id,
                    );
                    $extra_id = (int)$extra_id;
                    update_insert('extra_id',$extra_id,'extra',$extra_db);
                }
            }

        }


    }

    public function get_upgrade_sql(){
        $sql = '';
        $fields = get_fields('customer');
        if(!isset($fields['default_tax'])){
            $sql .= 'ALTER TABLE  `'._DB_PREFIX.'customer` ADD  `default_tax` double(10,2) NOT NULL DEFAULT \'-1\' AFTER `credit`;';
        }
        if(!isset($fields['default_tax_name'])){
            $sql .= 'ALTER TABLE  `'._DB_PREFIX.'customer` ADD  `default_tax_name` varchar(10) NOT NULL DEFAULT \'\' AFTER `default_tax`;';
        }
        if(!isset($fields['default_invoice_prefix'])){
            $sql .= 'ALTER TABLE  `'._DB_PREFIX.'customer` ADD  `default_invoice_prefix` varchar(10) NOT NULL DEFAULT \'\' AFTER `default_tax_name`;';
        }
        if(!isset($fields['customer_status'])){
            $sql .= 'ALTER TABLE  `'._DB_PREFIX.'customer` ADD  `customer_status` tinyint(2) NOT NULL DEFAULT \'0\' AFTER `primary_user_id`;';
        }
        return $sql;
    }


    public function get_install_sql(){
        ob_start();
        ?>

CREATE TABLE `<?php echo _DB_PREFIX; ?>customer` (
  `customer_id` int(11) NOT NULL auto_increment,
  `primary_user_id` int(11) NOT NULL DEFAULT '0',
  `customer_status` tinyint(2) NOT NULL DEFAULT '0',
  `customer_name` varchar(255) NOT NULL DEFAULT '',
  `credit` double(10,2) NOT NULL DEFAULT '0',
  `default_tax` double(10,2) NOT NULL DEFAULT '-1',
  `default_tax_name` varchar(10) NOT NULL DEFAULT '',
  `default_invoice_prefix` varchar(10) NOT NULL DEFAULT '',
  `date_created` date NOT NULL,
  `date_updated` date NULL,
  PRIMARY KEY  (`customer_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

INSERT INTO `<?php echo _DB_PREFIX; ?>customer` VALUES (1, 3, 0, 'Bobs Printing Service', 0, -1, '', '', NOW(), NOW());
INSERT INTO `<?php echo _DB_PREFIX; ?>customer` VALUES (2, 4, 0, 'Richards Roof Repairs', 0, -1, '', '', NOW(), NOW());

<?php
        return ob_get_clean();
    }

    public static function add_credit($customer_id, $credit) {
        $customer_data = self::get_customer($customer_id);
        $customer_data['credit'] += $credit;
        update_insert('customer_id',$customer_id,'customer',array('credit'=>$customer_data['credit']));
        //self::add_history($customer_id,'Added '.dollar($credit).' credit to customers account.');
    }
    public static function remove_credit($customer_id, $credit) {
        $customer_data = self::get_customer($customer_id);
        $customer_data['credit'] -= $credit;
        update_insert('customer_id',$customer_id,'customer',array('credit'=>$customer_data['credit']));
        //self::add_history($customer_id,'Added '.dollar($credit).' credit to customers account.');
    }

    
    public static function add_history($customer_id,$message){
		module_note::save_note(array(
			'owner_table' => 'customer',
			'owner_id' => $customer_id,
			'note' => $message,
			'rel_data' => self::link_open($customer_id),
			'note_time' => time(),
		));
	}

    public static function get_customer_data_access() {
        if(class_exists('module_security',false)){
            return module_security::can_user_with_options(module_security::get_loggedin_id(),'Customer Data Access',array(
                                                                                                   _CUSTOMER_ACCESS_ALL,
                                                                                                   _CUSTOMER_ACCESS_CONTACTS,
                                                                                                   _CUSTOMER_ACCESS_TASKS,
                                                                                                                       ));
        }else{
            return true;
        }
    }

    public static function link_public_signup(){
        return full_link(_EXTERNAL_TUNNEL_REWRITE.'m.customer/h.public_signup');
    }

    public function external_hook($hook){

        switch($hook){
            case 'public_signup':

                if(!module_config::c('customer_signup_allowed',0)){
                    echo 'Customer signup disabled';
                    exit;
                }

                //recaptcha on signup form.
                if(module_config::c('captcha_on_signup_form',0)){
                    if(!module_captcha::check_captcha_form()){
                        echo 'Captcha fail, please go back and enter correct captcha code.';
                        exit;
                    }
                }

                $customer = isset($_POST['customer']) ? $_POST['customer'] : array();
                $customer_extra = isset($customer['extra']) ? $customer['extra'] : array();
                $website = isset($_POST['website']) ? $_POST['website'] : array();
                $website_extra = isset($website['extra']) ? $website['extra'] : array();
                $job = isset($_POST['job']) ? $_POST['job'] : array();
                $address = isset($_POST['address']) ? $_POST['address'] : array();

                // sanatise possibly problematic fields:
                // customer:
                $allowed = array('name','customer_name','email','phone','mobile','extra');
                foreach($customer as $key=>$val){
                    if(!in_array($key,$allowed)){
                        unset($customer[$key]);
                    }
                }
                if(isset($customer['email']))$customer['email']=strtolower(trim($customer['email']));
                // website:
                $allowed = array('url','name','extra','notes');
                foreach($website as $key=>$val){
                    if(!in_array($key,$allowed)){
                        unset($website[$key]);
                    }
                }

                $website['url'] = isset($website['url']) ? strtolower(trim($website['url'])) : '';

                // todo - check for required fields.

                if(!isset($customer['customer_name'])||!strlen($customer['customer_name'])){
                    $customer['customer_name'] = isset($customer['name']) ? $customer['name'] : '';
                }
                if(!strlen($customer['customer_name'])){
                    echo "Failed, please go back and provide a name";
                    exit;
                }
                if(!strlen($customer['email'])){
                    echo "Failed, please go back and provide an email address";
                    exit;
                }

                // check if this customer already exists in the system, based on email address
                $customer_id = false;
                $creating_new = true;
                $_REQUEST['user_id'] = 0;
                if(isset($customer['email']) && strlen($customer['email']) && !module_config::c('customer_signup_always_new',0)){
                    $users = module_user::get_contacts(array('email'=>$customer['email']));
                    foreach($users as $user){
                        if(isset($user['customer_id']) && (int)$user['customer_id']>0){
                            // this user exists as a customer! yey!
                            // add them to this listing.
                            $customer_id = $user['customer_id'];
                            $creating_new = false;
                            $_REQUEST['user_id'] = $user['user_id'];
                        }
                    }
                }

                $_REQUEST['extra_customer_field'] = array();
                $_REQUEST['extra_user_field'] = array();
                module_extra::$config['allow_new_keys']=false;
                module_extra::$config['delete_existing_empties']=false;

                // save customer extra fields.
                if(count($customer_extra)){
                    // format the address so "save_customer" handles the save for us
                    foreach($customer_extra as $key=>$val){
                        $_REQUEST['extra_customer_field'][] = array(
                            'key'=>$key,
                            'val'=>$val,
                        );
                    }
                }
                // save customer and customer contact details:
                $customer_id = $this->save_customer($customer_id,$customer);
                if(!$customer_id){
                    echo 'failed to create customer';
                    exit;
                }
                $customer_data = module_customer::get_customer($customer_id);
                if(!$customer_data['primary_user_id']){
                    echo "Failed to create customer contact";
                    exit;
                }
                // save customer address fields.
                if(count($address)){
                    $address_db = module_address::get_address( $customer_id, 'customer', 'physical');
                    $address_id = $address_db && isset($address_db['address_id']) ? (int)$address_db['address_id'] : false;
                    $address['owner_id'] = $customer_id;
                    $address['owner_table'] = 'customer';
                    $address['address_type'] = 'physical';
                    // we have post data to save, write it to the table!!
                    module_address::save_address($address_id,$address);
                }
                // save website fields:
                $website_id = 0;
                if(count($website)){
                    if(strlen($website['url'])){
                        // see if website already exists, don't create or update existing one for now.
                        $existing_websites = module_website::get_websites(array('customer_id'=>$customer_id,'url'=>$website['url']));
                        foreach($existing_websites as $existing_website){
                            $website_id = $existing_website['website_id'];
                        }
                    }
                    if(!$website_id){
                        $website_data = module_website::get_website($website_id);
                        $website_data['url'] = isset($website['url']) ? $website['url'] : 'N/A';
                        $website_data['name'] = isset($website['url']) ? $website['url'] : 'N/A';
                        $website_data['customer_id'] = $customer_id;
                        $website_id = update_insert('website_id',false,'website',$website_data);
                        // save website extra data.
                        if($website_id && count($website_extra)){
                            foreach($customer_extra as $key=>$val){
                                $_REQUEST['extra_website_field'][] = array(
                                    'key'=>$key,
                                    'val'=>$val,
                                );
                            }
                            module_extra::save_extras('website','website_id',$website_id);
                        }
                        if($website_id && isset($website['notes']) && strlen($website['notes'])){
                            // add notes to this website.
                            $note_data = array(
                                'note_id' => false,
                                'owner_id' => $website_id,
                                'owner_table' => 'website',
                                'note_time' => time(),
                                'note' => $website['notes'],
                                'rel_data' => module_website::link_open($website_id),
                                'reminder' => 0,
                                'user_id' => $customer_data['primary_user_id'],
                            );
                            $note_id = update_insert('note_id',false,'note',$note_data);
                        }
                    }
                }
                // generate jobs for this customer.
                $job_created = array();
                if($job && isset($job['type']) && is_array($job['type'])){
                    foreach(module_job::get_types() as $type_id => $type){
                        foreach($job['type'] as $type_name){
                            if($type_name == $type){
                                // we have a match in our system. create the job.
                                $job_data = module_job::get_job(false);
                                $job_data['type'] = $type;
                                $job_data['name'] = $type;
                                $job_data['website_id'] = $website_id;
                                $job_data['customer_id'] = $customer_id;
                                $job_id = update_insert('job_id',false,'job',$job_data);
                                // todo: add default tasks for this job type.
                                $job_created [] = $job_id;
                            }
                        }
                    }
                }
                // save files against customer
                $uploaded_files = array();
                if(isset($_FILES['customerfiles']) && isset($_FILES['customerfiles']['tmp_name'])){
                    foreach($_FILES['customerfiles']['tmp_name'] as $file_id => $tmp_file){
                        if(is_uploaded_file($tmp_file)){
                            // save to file module for this customer
                            $file_name = basename($_FILES['customerfiles']['name'][$file_id]);
                            if(strlen($file_name)){
                                $file_path = 'includes/plugin_file/upload/'.md5(time().$file_name);
                                if(move_uploaded_file($tmp_file,$file_path)){
                                    // success! write to db.
                                    $file_data = array(
                                        'customer_id' => $customer_id,
                                        'job_id' => false,
                                        'website_id' => $website_id, // doesn't actually save anywhere
                                        'status' => module_config::c('file_default_status','Uploaded'),
                                        'pointers' => false,
                                        'description' => "Uploaded from Customer Signup form",
                                        'file_time' => time(), // allow UI to set a file time? nah.
                                        'file_name' => $file_name,
                                        'file_path' => $file_path,
                                        'file_url' => false,
                                    );
                                    $file_id = update_insert('file_id',false,'file',$file_data);
                                    $uploaded_files[] = $file_id;
                                }
                            }
                        }
                    }
                }


                module_template::init_template('customer_signup_thank_you_page','<h2>Thank You</h2>
    <p>Thank you. Your  request has been submitted successfully.</p>
    <p>Please check your email.</p>
    ','Displayed after a customer signs up.','code');

                module_template::init_template('customer_signup_email_welcome','Dear {CUSTOMER_NAME},<br>
<br>
Thank you for completing the information form on our website. We will be in touch shortly.<br><br>
Kind Regards,<br><br>
{FROM_NAME}
','Welcome {CUSTOMER_NAME}',array(
                ));

                module_template::init_template('customer_signup_email_admin','Dear Admin,<br>
<br>
A customer has signed up in the system!<br><br>
View/Edit this customer by going here: {CUSTOMER_NAME_LINK}<br><br>
Website: {WEBSITE_NAME_LINK}<br><br>
Jobs: {JOB_LINKS}<br><br>
Notes: {NOTES}<br><br>
{UPLOADED_FILES}<br><br>
{SYSTEM_NOTE}
','New Customer Signup: {CUSTOMER_NAME}',array(
                ));

                // email the admin when a customer signs up.
                $values = array_merge($customer,$customer_extra,$website,$website_extra,$address);
                $values['customer_name'] = $customer['customer_name'];
                $values['CUSTOMER_LINK'] = module_customer::link_open($customer_id);
                $values['CUSTOMER_NAME_LINK'] = module_customer::link_open($customer_id,true);
                if($website_id){
                    $values['WEBSITE_LINK'] = module_website::link_open($website_id);
                    $values['WEBSITE_NAME_LINK'] = module_website::link_open($website_id,true);
                }else{
                    $values['WEBSITE_LINK'] = _l('N/A');
                    $values['WEBSITE_NAME_LINK'] = _l('N/A');
                }
                $values['JOB_LINKS'] = '';
                if(count($job_created)){
                    $values['JOB_LINKS'] .= 'The customer created '.count($job_created).' jobs in the system: <br>';
                    foreach($job_created as $job_created_id){
                        $values['JOB_LINKS'] .= module_job::link_open($job_created_id,true)."<br>\n";
                    }
                }else{
                    $values['JOB_LINKS'] = _l('N/A');
                }

                if(count($uploaded_files)){
                    $values['uploaded_files']='The customer uploaded '.count($uploaded_files)." files:<br>\n";
                    foreach($uploaded_files as $uploaded_file){
                        $values['uploaded_files'].= module_file::link_open($uploaded_file,true)."<br>\n";
                    }
                }else{
                    $values['uploaded_files']='No files were uploaded';
                }
                $values['WEBSITE_NAME'] = (isset($website['url'])) ? $website['url'] : 'N/A';
                if(!$creating_new){
                    $values['system_note'] = "Note: this signup updated the existing customer record in the system.";
                }else{
                    $values['system_note'] = "Note: this signup created a new customer record in the system.";
                }

                $template = module_template::get_template_by_key('customer_signup_email_admin');
                $template->assign_values($values);
                $html = $template->render('html');
                $email = module_email::new_email();
                $email->replace_values = $values;
                $email->set_subject($template->description);
                // do we send images inline?
                $email->set_html($html);
                if($email->send()){
                    // it worked successfully!!
                }else{
                    /// log err?
                }
                $template = module_template::get_template_by_key('customer_signup_email_welcome');
                $template->assign_values($values);
                $html = $template->render('html');
                $email = module_email::new_email();
                $email->replace_values = $values;
                $email->set_subject($template->description);
                $email->set_to('user',$customer_data['primary_user_id']);
                // do we send images inline?
                $email->set_html($html);
                if($email->send()){
                    // it worked successfully!!
                }else{
                    /// log err?

                }

                //todo: optional redirect to url
                // load up the receipt template.
                $template = module_template::get_template_by_key('customer_signup_thank_you_page');
                $template->page_title = _l("Customer Signup");
                foreach($values as $key=>$val){
                    if(!is_array($val))$values[$key]=htmlspecialchars($val);
                }
                $template->assign_values($values);
                echo $template->render('pretty_html');
                exit;

                break;
        }
    }
}
