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


class module_user extends module_base{
	
	public $links;
	public $user_types;

    public $version = 2.246;
    // 2.21 - contact deletion redirect / permissions fix
    // 2.22 - all customer contacts permission expansion
    // 2.221 - viewing customer contact groups as permissions.
    // 2.222 - searching by user role.
    // 2.223 - adding user to role from customer creation.
    // 2.224 - redirect fixed on no "All Customer Contacts" permissions
    // 2.23 - theme support for contact and user pages
    // 2.24 - "staff members" are now considered people who can "edit" job tasks - this will confuse some people! and probably break some peoples configs. oh well.
    // 2.241 - perm bug fix
    // 2.242 - fix for forgot password
    // 2.243 - hashed passwords
    // 2.244 - fix for creating users from support tickets
    // 2.245 - speed improvements
    // 2.246 - speed improvements

    public static function can_i($actions,$name=false,$category=false,$module=false){
        if(!$module)$module=__CLASS__;
        return parent::can_i($actions,$name,$category,$module);
    }
	public static function get_class() {
        return __CLASS__;
    }
	
	public function init(){
		$this->links = array();
		$this->user_types = array();
		$this->module_name = "user";
		$this->module_position = 15;


        /*if(module_security::has_feature_access(array(
				'name' => 'Admin',
				'module' => 'config',
				'category' => 'Config',
				'view' => 1,
				'description' => 'view',
		))){*/

		if($this->can_i('view','Users','Config')){
			$this->links[] = array(
				"name"=>"Users",
				"p"=>"user_admin",
				"args"=>array('user_id'=>false),
				'holder_module' => 'config', // which parent module this link will sit under.
				'holder_module_page' => 'config_admin',  // which page this link will be automatically added to.
				'menu_include_parent' => 0,
                'order'=>3,
			);
		}

		if($this->can_i('view','Contacts','Customer')){
			// only display if a customer has been created.
			if(isset($_REQUEST['customer_id']) && $_REQUEST['customer_id'] && $_REQUEST['customer_id']!='new'){
                // how many contacts?
                $contacts = $this->get_contacts(array('customer_id'=>$_REQUEST['customer_id']),true,false);
                $name = _l('Contacts');
                if(mysql_num_rows($contacts)>0){
                    $name .= " <span class='menu_label'>".mysql_num_rows($contacts)."</span> ";
                }
				$this->links[] = array(
					"name"=>$name,
					"p"=>"contact_admin",
					"icon"=>"../../../images/icon_arrow_down.png",
					'args'=>array('user_id'=>false),
					'holder_module' => 'customer', // which parent module this link will sit under.
					'holder_module_page' => 'customer_admin_open',  // which page this link will be automatically added to.
					'menu_include_parent' => 0,
				);
			}
		}
		
	}

    public static function link_generate($user_id=false,$options=array(),$link_options=array()){

        $key = 'user_id';
        if($user_id === false && $link_options){
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

        if(isset($options['data'])){
            $data = $options['data'];
        }else{
            $data = array();
        }
        if(isset($options['full']) && $options['full']){
            // only hit database if we need to print a full link with the name in it.
            if(!$data && $user_id){
                $data = self::get_user($user_id,false,true,2);
                $options['data'] = $data;
            }
            // what text should we display in this link?
            $options['text'] = (!isset($data['name'])||!trim($data['name'])) ? 'N/A' : $data['name'].(isset($data['last_name'])?' '.$data['last_name']:'');
            if(!$data || !$user_id){
                // linking to a new user?
                // shouldn't happen in a "full" link.
                return $options['text'];
            }
            if(isset($data['_perms']) && !$data['_perms']){
                return $options['text'];
            }
        }

        if(isset($data['customer_id']) && $data['customer_id']){
            $options['type']='contact';
        }

        $use_master_key = self::get_contact_master_key();
        if(!isset($options['type']))$options['type']='user';
		switch($options['type']){
			case 'contact':
                // for a contact link under supplier or a customer
                $options['page'] = 'contact_admin';
                switch($use_master_key){
                    case 'customer_id':
                        if($user_id=='new' || (int)$user_id>0){ // so the "view all contacts" link works.
                            $bubble_to_module = array(
                                'module' => 'customer',
                                'argument' => 'customer_id',
                            );
                        }
                        break;
                }
		        break;
			default:
                $bubble_to_module = array(
                    'module' => 'config',
                );
                $options['page'] = 'user_admin';
		}

        $options['arguments'] = array(
            'user_id' => $user_id,
        );
        $options['module'] = 'user';


        array_unshift($link_options,$options);

        // check if people have permission to link to this item.
        /*if(isset($options['type']) && $options['type'] == 'contact'){
            // check they can access this particular contact type

        }*/
        if(
            !module_security::has_feature_access(array(
                'name' => 'Settings',
                'module' => 'config',
                'category' => 'Config',
                'view' => 1,
                'description' => 'view',
            )) && !module_security::has_feature_access(array(
                'name' => 'Customers',
                'module' => 'customer',
                'category' => 'Customer',
                'view' => 1,
                'description' => 'view',
            ))
            && (!isset($options['skip_permissions']) || !$options['skip_permissions'])
        ){
            if(!isset($options['full']) || !$options['full']){
                return '#';
            }else{
                return isset($options['text']) ? $options['text'] : 'N/A';
            }

        }
        if($bubble_to_module){
            global $plugins;
            if(isset($plugins[$bubble_to_module['module']])){
                return $plugins[$bubble_to_module['module']]->link_generate(false,array(),$link_options);
            }
        }
        // return the link as-is, no more bubbling or anything.
        // pass this off to the global link_generate() function
        return link_generate($link_options);


    }
    

	public static function link_open($user_id,$full=false,$data=array(),$skip_permissions=false){
        return self::link_generate($user_id,array('full'=>$full,'data'=>$data,'skip_permissions'=>$skip_permissions));
	}

	public static function link_open_contact($user_id,$full=false,$data=array(),$skip_permissions=false){
		return self::link_generate($user_id,array('type'=>'contact','full'=>$full,'data'=>$data,'skip_permissions'=>$skip_permissions));
	}


	
	public function process(){
        if(_DEMO_MODE && isset($_REQUEST['user_id']) && (int)$_REQUEST['user_id'] == 1){
            set_error('Sorry no changes to admin user in demo');
            redirect_browser($this->link_open(1));
        }
		$errors=array();
		if(isset($_REQUEST['butt_del_contact']) && $_REQUEST['butt_del_contact'] && $_REQUEST['user_id'] && $_REQUEST['user_id'] != 1 && self::can_i('delete','Contacts','Customer')){
            $data = self::get_user($_REQUEST['user_id']);
            if(module_form::confirm_delete('user_id',"Really delete contact: ".$data['name'],self::link_open_contact($_REQUEST['user_id']))){
                $this->delete_user($_REQUEST['user_id']);
                set_message("Contact deleted successfully");
                redirect_browser(module_customer::link_open($data['customer_id']));
            }
        }else if(isset($_REQUEST['butt_del']) && $_REQUEST['butt_del'] && $_REQUEST['user_id'] && self::can_i('delete','Users','Config')){
            $data = self::get_user($_REQUEST['user_id']);
            if(module_form::confirm_delete('user_id',"Really delete user: ".$data['name'],self::link_open($_REQUEST['user_id']))){
                $this->delete_user($_REQUEST['user_id']);
                set_message("User deleted successfully");
                redirect_browser(self::link_open(false));
            }
		}else if("save_user" == $_REQUEST['_process']){
            $user_id = (int)$_REQUEST['user_id'];
            if($user_id == 1 && module_security::get_loggedin_id() != 1){
                set_error('Sorry, only the Administrator can access this page.');
                redirect_browser(_UCM_HOST._BASE_HREF);
            }
            // check create permissions.
            $use_master_key = $this->get_contact_master_key();

            // are we creating or editing a user?
            if(!$user_id)$method = 'create';
            else{
                $method = 'edit';
                $existing_user = module_user::get_user($user_id,true,false);
                if(!$existing_user || $existing_user['user_id'] != $user_id){
                    $user_id = false;
                    $method = 'create';
                }
            }


            if(isset($_POST[$use_master_key]) && $_POST[$use_master_key]){
                if(!module_user::can_i($method,'Contacts','Customer')){
                    set_error('No permissions to '.$method.' contacts');
                    redirect_browser(module_customer::link_open($_POST['customer_id']));
                }
            }else if(!module_user::can_i($method,'Users','Config')){
                set_error('No permissions to '.$method.' users');
                redirect_browser(module_user::link_open(false));
            }

            $user_id = $this->save_user($user_id,$_POST);
            if($use_master_key && isset($_REQUEST[$use_master_key]) && $_REQUEST[$use_master_key]){
                set_message("Customer contact saved successfully");
                redirect_browser($this->link_open_contact($user_id));
            }else{
                set_message("User saved successfully");
                redirect_browser($this->link_open($user_id));
            }

		}/*else if("save_contact" == $_REQUEST['_process']){
			$user_id = $this->save_contact($_POST['user_id'],$_POST);
			$_REQUEST['_redirect'] = $this->link_open_contact(false);
			if($user_id){
				set_message("Contact saved successfully");
			}else{
				// todo error creating contact
			}
		}*/
		if(!count($errors)){
			redirect_browser($_REQUEST['_redirect']);
			exit;
		}
		print_error($errors,true);
	}
	
	public static function get_contact_master_key(){

        // for now we only support contacts assigned to a customer
        // so we're always going to return the "customer_id" primary key.
        // later on this may change again.
        return 'customer_id';


		// contacts can either be for a customer, a supplier, or .... ?
		$master_keys = array('customer',);
		$use_master_key = false;
		foreach($master_keys as $master_key){
			if(isset($_REQUEST[$master_key.'_id']) && $_REQUEST[$master_key.'_id']){
				$use_master_key = $master_key.'_id';
				break;
			}
		}
        if(!$use_master_key){
            foreach($master_keys as $master_key){
                if(isset($_REQUEST['m']) && in_array($master_key,$_REQUEST['m'])){
                    $use_master_key = $master_key.'_id';
                    break;
                }
            }
        }
		return $use_master_key;
	}


    public static function get_statuses(){
		return array(
			1 => 'Active',
			0 => 'Inactive',
		);
	}
	public static function get_users($search=array(),$mysql=false){
		// limit based on customer id
		/*if(!isset($_REQUEST['customer_id']) || !(int)$_REQUEST['customer_id']){
			return array();
		}*/
		// build up a custom search sql query based on the provided search fields
		$sql = "SELECT *,u.user_id AS id ";
        $sql .= ", u.name AS name ";
        $from = " FROM `"._DB_PREFIX."user` u ";
		$where = " WHERE 1 ";
        $where .= " AND u.customer_id = 0 ";
		if(isset($search['generic']) && $search['generic']){
			$str = mysql_real_escape_string($search['generic']);
			$where .= " AND ( ";
			$where .= " u.name LIKE '%$str%' OR ";
			$where .= " u.email LIKE '%$str%' OR ";
			$where .= " u.phone LIKE '%$str%' OR ";
			$where .= " u.mobile LIKE '%$str%' ";
			$where .= ' ) ';
		}
		if(isset($search['customer_id']) && $search['customer_id']){
			/*$str = mysql_real_escape_string($search['customer_id']);
			$where .= " AND u.customer_id = '$str'";
            $sql .= " , c.primary_user_id AS is_primary ";
            $from .= " LEFT JOIN `"._DB_PREFIX."customer` c ON u.customer_id = c.customer_id ";*/
            set_error('Bad usage of get_user() - please report this error.');
            return array();
		}
		if(isset($search['security_role_id']) && (int)$search['security_role_id']>0){
			$str = (int)$search['security_role_id'];
            $from .= " LEFT JOIN `"._DB_PREFIX."user_role` ur ON u.user_id = ur.user_id";
            $where .= " AND ur.security_role_id = $str";
		}
        foreach(array('email') as $key){
            if(isset($search[$key]) && $search[$key] !== ''&& $search[$key] !== false){
                $str = mysql_real_escape_string($search[$key]);
                $where .= " AND u.`$key` LIKE '$str'";
            }
        }
        foreach(array('site_id','customer_id') as $key){
            if(isset($search[$key]) && $search[$key] !== ''&& $search[$key] !== false){
                $str = mysql_real_escape_string($search[$key]);
                $where .= " AND u.`$key` = '$str'";
            }
        }
        if(class_exists('module_customer',false)){
            switch(module_customer::get_customer_data_access()){
                case _CUSTOMER_ACCESS_ALL:

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
                        $where .= " AND u.customer_id = '".(int)$_SESSION['_restrict_customer_id']."'";
                    }*/
                    break;
                case _CUSTOMER_ACCESS_TASKS:
                    // only customers who have linked jobs that I am assigned to.
                    $from .= " LEFT JOIN `"._DB_PREFIX."job` j ON u.customer_id = j.customer_id ";
                    $from .= " LEFT JOIN `"._DB_PREFIX."task` t ON j.job_id = t.job_id ";
                    $where .= " AND (j.user_id = ".(int)module_security::get_loggedin_id()." OR t.user_id = ".(int)module_security::get_loggedin_id().")";
                    break;
            }
        }
		$group_order = ' GROUP BY u.user_id ORDER BY u.name'; // stop when multiple company sites have same region
		$sql = $sql . $from . $where . $group_order;
        if($mysql){
            return query($sql);
        }
		$result = qa($sql);
		module_security::filter_data_set("user",$result);
		return $result;
//		return get_multiple("user",$search,"user_id","fuzzy","name");

	}
	public static function get_contact_customer_links($user_id){
        $sql = "SELECT * FROM `"._DB_PREFIX."user_customer_rel` WHERE `primary` = '".(int)$user_id."' OR `user_id` = '".(int)$user_id."'";
        return qa($sql);
    }
	public static function get_contacts($search=array(),$new_security_check=false,$as_array=true){
		// limit based on customer id

		// build up a custom search sql query based on the provided search fields
		$sql = "SELECT u.*,u.user_id AS id ";
        $sql .= ", u.name AS name ";
        $sql .= ", c.* ";
        $from = " FROM `"._DB_PREFIX."user` u ";
        $from .= " LEFT JOIN `"._DB_PREFIX."customer` c ON u.customer_id = c.customer_id ";
		$where = " WHERE u.customer_id > 0 ";
		if(isset($search['generic']) && $search['generic']){
			$str = mysql_real_escape_string($search['generic']);
			$where .= " AND ( ";
			$where .= " u.name LIKE '%$str%' OR ";
			$where .= " u.email LIKE '%$str%' OR ";
			$where .= " u.phone LIKE '%$str%' OR ";
			$where .= " u.mobile LIKE '%$str%' ";
			$where .= ' ) ';
		}
		if(isset($search['customer_id']) && $search['customer_id']){
			$str = (int)$search['customer_id'];
			$where .= " AND u.customer_id = '$str'";
        }
        $sql .= " , c.primary_user_id AS is_primary ";
        foreach(array('site_id','customer_id') as $key){
            if(isset($search[$key]) && $search[$key] !== ''&& $search[$key] !== false){
                $str = mysql_real_escape_string($search[$key]);
                $where .= " AND u.`$key` = '$str'";
            }
        }
        if(isset($search['security_role_id']) && (int)$search['security_role_id']>0){
            $str = (int)$search['security_role_id'];
            $from .= " LEFT JOIN `"._DB_PREFIX."user_role` ur ON u.user_id = ur.user_id";
            $where .= " AND ur.security_role_id = $str";
        }
        foreach(array('email') as $key){
            if(isset($search[$key]) && $search[$key] !== ''&& $search[$key] !== false){
                $str = mysql_real_escape_string($search[$key]);
                $where .= " AND u.`$key` LIKE '$str'";
            }
        }
        if(class_exists('module_customer',false)){
            switch(module_customer::get_customer_data_access()){
                case _CUSTOMER_ACCESS_ALL:

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
                    /*
                    if(isset($_SESSION['_restrict_customer_id']) && (int)$_SESSION['_restrict_customer_id']> 0){
                        // this session variable is set upon login, it holds their customer id.
                        $where .= " AND u.customer_id = '".(int)$_SESSION['_restrict_customer_id']."'";
                    }*/
                    break;
                case _CUSTOMER_ACCESS_TASKS:
                    // only customers who have linked jobs that I am assigned to.
                    $from .= " LEFT JOIN `"._DB_PREFIX."job` j ON u.customer_id = j.customer_id ";
                    $from .= " LEFT JOIN `"._DB_PREFIX."task` t ON j.job_id = t.job_id ";
                    $where .= " AND (j.user_id = ".(int)module_security::get_loggedin_id()." OR t.user_id = ".(int)module_security::get_loggedin_id().")";
                    break;
            }
        }
        if($new_security_check){
            // addition for the 'all customer contacts' permission
            // if user doesn't' have this permission then we only show ourselves in this list.
            if(!module_user::can_i('view','All Customer Contacts','Customer','customer')){
                $where .= " AND u.user_id = ".(int)module_security::get_loggedin_id();
                /*foreach($result as $key=>$val){
                    if($val['user_id']!=module_security::get_loggedin_id())unset($result[$key]);
                }*/
            }
        }
		$group_order = ' GROUP BY u.user_id ORDER BY c.customer_name, u.name'; // stop when multiple company sites have same region
		$sql = $sql . $from . $where . $group_order;
        if($as_array){
		    $result = qa($sql);
        }else{
            $result = query($sql);
        }
		//module_security::filter_data_set("user",$result);

		return $result;
//		return get_multiple("user",$search,"user_id","fuzzy","name");

	}

    private static $_user_cache = array();
	public static function get_user($user_id,$perms=true,$do_link=true,$basic=false){
        if(isset(self::$_user_cache[$user_id])){
            return self::$_user_cache[$user_id];
        }
		$user = get_single("user","user_id",$user_id);
        if($do_link && $user && isset($user['linked_parent_user_id']) && $user['linked_parent_user_id'] && $user['linked_parent_user_id'] != $user['user_id']){
            return self::get_user($user['linked_parent_user_id']);
        }
        if($user){

            // if this user is a linked contact to the current contact then we allow access.
            if(isset($user['linked_parent_user_id']) && $user['linked_parent_user_id'] == module_security::get_loggedin_id()){
                // allow all access.
            }else{

                if(class_exists('module_customer',false)){
                    switch(module_customer::get_customer_data_access()){
                        case _CUSTOMER_ACCESS_ALL:

                            break;
                        case _CUSTOMER_ACCESS_CONTACTS:
                            // we only want customers that are directly linked with the currently logged in user contact.

                            $valid_customer_ids = module_security::get_customer_restrictions();
                            if(count($valid_customer_ids)){
                                $is_valid_user = false;
                                foreach($valid_customer_ids as $valid_customer_id){
                                    if($user['customer_id'] && $user['customer_id'] == $valid_customer_id){
                                        $is_valid_user = true;
                                    }
                                }
                                if(!$is_valid_user){
                                    if($perms){
                                        $user = false;
                                    }else{
                                        // eg for linking.
                                        $user['_perms'] = false;
                                    }
                                }
                            }
                            /*
                            if(isset($_SESSION['_restrict_customer_id']) && (int)$_SESSION['_restrict_customer_id']> 0){
                                // this session variable is set upon login, it holds their customer id.
                                //$where .= " AND c.customer_id = '".(int)$_SESSION['_restrict_customer_id']."'";
                                if(!$user['customer_id'] || $user['customer_id'] != $_SESSION['_restrict_customer_id']){
                                    if($perms){
                                        $user = false;
                                    }else{
                                        // eg for linking.
                                        $user['_perms'] = false;
                                    }
                                }
                            }*/
                            break;
                        case _CUSTOMER_ACCESS_TASKS:
                            // only customers who have linked jobs that I am assigned to.
                            //$sql .= " LEFT JOIN `"._DB_PREFIX."job` j ON c.customer_id = j.customer_id ";
                            //$sql .= " LEFT JOIN `"._DB_PREFIX."task` t ON j.job_id = t.job_id ";
                            //$where .= " AND (j.user_id = ".(int)module_security::get_loggedin_id()." OR t.user_id = ".(int)module_security::get_loggedin_id().")";
                            if(!$user['customer_id']){
                                $has_job_access = false;
                            }else{
                                $has_job_access = false;
                                $jobs = module_job::get_jobs(array('customer_id'=>$user['customer_id']));
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
                            }
                            if(!$has_job_access){
                                if($perms){
                                    $user = false;
                                }else{
                                    // eg for linking.
                                    $user['_perms'] = false;
                                }
                            }
                            break;
                    }
                }
            }
        }
        if(!$user){
            $user = array(
                'user_id' => 'new',
                'customer_id' => 0,
                //'user_type_id' => 0,
                'name' => '',
                'last_name' => '',
                'email' => '',
                'password' => '',
                'phone' => '',
                'mobile' => '',
                'fax' => '',
                'roles' => array(),
                'language' => module_config::c('default_language','en'),
            );
            $use_master_key = self::get_contact_master_key();
            if(isset($_REQUEST[$use_master_key])){
                $user[$use_master_key] = $_REQUEST[$use_master_key];
            }
        }else{
            $user['roles'] = get_multiple('user_role',array('user_id'=>$user_id));
            self::$_user_cache[$user_id] = $user;
        }
		return $user;
	}

    public function create_user($user_data,$user_type=false){
        // todo - check user data is correct.
        $user_data['status_id'] = 1;
        if(isset($user_data['password'])){
            $user_data['password_new'] = $user_data['password'];
        }
        $user_id = $this->save_user('new',$user_data);
        //self::set_user_type($user_id,$user_type);
        return $user_id;
    }

	public function save_user($user_id,$data){
        $user_id = (int)$user_id;
        $temp_user = array();
        if($user_id>0){
            // check permissions
            $temp_user = $this->get_user($user_id,true,false);
            if(!$temp_user || $temp_user['user_id'] != $user_id || isset($temp_user['_perms'])){
                $user_id = false;
            }
        }
        // check the customer id is valid assignment to someone who has these perms.
        if(isset($data['customer_id']) && (int)$data['customer_id'] > 0){
            $temp_customer = module_customer::get_customer($data['customer_id']);
            if(!$temp_customer || $temp_customer['customer_id'] != $data['customer_id']){
                unset($temp_customer['customer_id']);
            }
        }
        if(isset($data['password'])){
            unset($data['password']);
        }
        // we do the password hash thing here.
        if(isset($data['password_new']) && strlen($data['password_new'])){
            // an admin is trying to set the password for this account.
            // same permissions checks as on the user_admin_edit_login.php page
            if(!$user_id || (isset($temp_user['password']) && !$temp_user['password']) || module_user::can_i('create','Users Passwords','Config') || (isset($_REQUEST['reset_password']) && $_REQUEST['reset_password'] == module_security::get_auto_login_string($user_id))){
                // we allow the admin to set a new password without typing in previous password.
                $data['password'] = $data['password_new'];
            }else{
                set_error('Sorry, no permissions to set a new password.');
            }
        }else if($user_id && isset($data['password_new1']) && isset($data['password_new2']) && strlen($data['password_new1'])){
            // the user is trying to change their password.
            // only do this if the user has edit password permissions and their password matches.
            if(module_user::can_i('edit','Users Passwords','Config') || $user_id == module_security::get_loggedin_id()){
                if(isset($data['password_old']) && (md5($data['password_old']) == $temp_user['password'] || $data['password_old'] == $temp_user['password'])){
                    // correct old password
                    // verify new password.
                    if($data['password_new1'] == $data['password_new2']){
                        $data['password'] = $data['password_new1'];
                    }else{
                        set_error('Verified password mismatch. Password unchanged.');
                    }
                }else{
                    set_error('Old password does not match. Password unchanged.');
                }
            }else{
                set_error('No permissions to change passwords');
            }
        }
        // and we finally hash our password
        if(isset($data['password']) && strlen($data['password']) > 0){
            $data['password'] = md5($data['password']);
            // if you change md5 also change it in customer import.
            // todo - salt? meh.
        }
		$user_id = update_insert("user_id",$user_id,"user",$data);

        $use_master_key = $this->get_contact_master_key();
        // this will be customer_id or supplier_id
        if(
            $use_master_key && (isset($data[$use_master_key]) && $data[$use_master_key])
        ){
            if($user_id){
                if(isset($data['customer_primary']) && $data['customer_primary']){
                    // update the customer/supplier to mark them as primary or not..
                    switch($use_master_key){
                        case 'customer_id':
                            module_customer::set_primary_user_id($data['customer_id'],$user_id);
                            break;
                    }
                }else{
                    // check if this contact was the old customer/supplier primary and
                    switch($use_master_key){
                        case 'customer_id':
                            $customer_data = module_customer::get_customer($data['customer_id']);
                            if($customer_data['primary_user_id'] == $user_id){
                                module_customer::set_primary_user_id($data['customer_id'],0);
                            }
                            break;
                    }
                }
            }
        }

        // hack for linked user accounts.
        if($user_id && isset($data['link_customers']) && $data['link_customers'] == 'yes' && isset($data['link_user_ids']) && is_array($data['link_user_ids']) && isset($data['email']) && $data['email']){
            $others = module_user::get_contacts(array('email'=>$data['email']));
            foreach($data['link_user_ids'] as $link_user_id){
                if(!(int)$link_user_id)continue;
                if($link_user_id == $user_id)continue; // shouldnt happen
                foreach($others as $other){
                    if($other['user_id'] == $link_user_id){
                        // success! they'renot trying to hack us.
                        $sql = "REPLACE INTO `"._DB_PREFIX."user_customer_rel` SET user_id = '".(int)$link_user_id."', customer_id = '".(int)$other['customer_id']."', `primary` = ".(int)$user_id;
                        query($sql);
                        update_insert('user_id',$link_user_id,'user',array('linked_parent_user_id'=>$user_id));
                    }
                }
            }
            update_insert('user_id',$user_id,'user',array('linked_parent_user_id'=>$user_id));
        }

        if($user_id && isset($data['unlink']) && $data['unlink'] == 'yes'){
            $sql = "DELETE FROM `"._DB_PREFIX."user_customer_rel` WHERE user_id = '".(int)$user_id."'";
            query($sql);
            update_insert('user_id',$user_id,'user',array('linked_parent_user_id'=>0));
        }

		handle_hook("address_block_save",$this,"physical","user","user_id",$user_id);
		handle_hook("address_block_save",$this,"postal","user","user_id",$user_id);
        module_extra::save_extras('user','user_id',$user_id);

		// find current role / permissions
		$user_data = $this->get_user($user_id);
		$previous_user_roles = $user_data['roles'];
		$re_save_role_perms = false;

		// hack to support only 1 role (we may support multi-role in the future)
        // TODO: check we have permissions to set this role id, otherwise anyone can set their own role.
		if(isset($_REQUEST['role_id'])){
			$sql = "DELETE FROM `"._DB_PREFIX."user_role` WHERE user_id = '".(int)$user_id."'";
			query($sql);
            if((int)$_REQUEST['role_id'] > 0){
                if(!isset($previous_user_roles[$_REQUEST['role_id']])){
                    $re_save_role_perms = (int)$_REQUEST['role_id'];
                }
                $_REQUEST['role'] = array(
                    $_REQUEST['role_id'] => 1,
                );
            }
		}
		// save users roles (support for multi roles in future - but probably will never happen)
		if(isset($_REQUEST['role']) && is_array($_REQUEST['role'])){
			foreach($_REQUEST['role'] as $role_id => $tf){
                $this->add_user_to_role($user_id,$role_id);
			}
		}

		if($re_save_role_perms){
			// copy role permissiosn to user permissions
			$sql = "DELETE FROM `"._DB_PREFIX."user_perm` WHERE user_id = ".(int)$user_id;
			query($sql);
            // update - we are not relying on these permissions any more.
            // if the user has a role assigned, we use those permissions period
            // we ignore all permissions in the user_perm table if the user has a role.
            // if the user doesn't have a role, then we use these user_perm permissions.
			/*$security_role = module_security::get_security_role($re_save_role_perms);
			foreach($security_role['permissions'] as $security_permission_id => $d){
				$sql = "INSERT INTO `"._DB_PREFIX."user_perm` SET user_id = ".(int)$user_id.", security_permission_id = '".(int)$security_permission_id."'";
				foreach(module_security::$available_permissions as $perm){
					$sql .= ", `".$perm."` = ".(int)$d[$perm];
				}
				query($sql);
			}*/
		}else if(isset($_REQUEST['permission']) && is_array($_REQUEST['permission'])){
			$sql = "DELETE FROM `"._DB_PREFIX."user_perm` WHERE user_id = '".(int)$user_id."'";
			query($sql);
			// update permissions for this user.
			foreach($_REQUEST['permission'] as $security_permission_id => $permissions){
				$actions = array();
				foreach(module_security::$available_permissions as $permission){
					if(isset($permissions[$permission]) && $permissions[$permission]){
						$actions[$permission] = 1;
					}
				}
				$sql = "REPLACE INTO `"._DB_PREFIX."user_perm` SET user_id = '".(int)$user_id."', security_permission_id = '".(int)$security_permission_id."' ";
				foreach($actions as $permission => $tf){
					$sql .= ", `".mysql_real_escape_string($permission)."` = 1";
				}
				query($sql);
			}

		}

        /*global $plugins;
		if($user_id && isset($data['user_type_id']) && $data['user_type_id'] == 1 && $data['site_id']){
			// update the site.
			$plugins['site']->set_primary_user_id($data['site_id'],$user_id);
		}else{
            //this use isn't (or isnt any more) the sites primary user.
            // unset this if he was the primary user before
            $site_data = $plugins['site']->get_site($data['site_id']);
            if(isset($site_data['primary_user_id']) && $site_data['primary_user_id'] == $user_id){
                $plugins['site']->set_primary_user_id($data['site_id'],0);
            }
        }*/

		return $user_id;
	}

    public static function add_user_to_role($user_id,$role_id){
        $sql = "REPLACE INTO `"._DB_PREFIX."user_role` SET user_id = '".(int)$user_id."', security_role_id = '".(int)$role_id."'";
        query($sql);
    }


	public static function print_user_summary($user_id,$output='html',$fields=array()) {
		global $plugins;
		$user_data = $plugins['user']->get_user($user_id);
		if(!$fields){
			$fields = array('name');
		}
		$user_output = '';
		foreach($fields as $key){
			if(isset($user_data[$key]) && $user_data[$key]){
				$user_output .= $user_data[$key].', ';
			}
		}
		$user_output = rtrim($user_output,', ');
		if($user_data){
			switch($output){
				case 'text':
			        echo $user_output;
			        break;
				case 'html':
					?>
					<span class="user">
						<a href="<?php echo $plugins['user']->link_open($user_id);?>">
							<?php echo $user_output;?>
						</a>
					</span>
					<?php
					break;
				case 'full':
					include('pages/user_summary.php');
					break;
			}
		}
	}

	public static function print_contact_summary($user_id,$output='html',$fields=array()) {
		$user = self::get_user($user_id);
		if(!$fields){
			$fields = array('name');
		}
		$user_output = '';
		foreach($fields as $key){
            foreach(explode('|',$key) as $k){
                if(isset($user[$k]) && $user[$k]){
                    $user_output .= $user[$k].', ';
                    break;
                }
            }
		}
		$user_output = rtrim($user_output,', ');
        $user_output = htmlspecialchars($user_output);
        switch($output){
            case 'text':
                echo $user_output;
                break;
            case 'html':
                ?>
                <span class="user">
                    <a href="<?php echo self::link_open_contact($user_id,false,$user);?>">
                        <?php echo $user_output;?>
                    </a>
                </span>
                <?php
                break;
            case 'full':
            case 'new':
                include('pages/contact_admin_form.php');
                break;
        }

	}




	/*public function save_contact($user_id,$data){
		// user must have a customer_id
		// todo, check user has access to this customer id and they're not just messing with the contacts.
		$use_master_key = $this->get_contact_master_key();
        // this will be customer_id or supplier_id
        if(
            (isset($data[$use_master_key]) && $data[$use_master_key])
        ){
            $data['user_type'] = 1; // marks the 'user' as a contact in the db.
            $user_id = update_insert("user_id",$user_id,"user",$data);
            if($user_id){
                global $plugins;
                if(isset($data['customer_primary']) && $data['customer_primary']){
                    // update the customer/supplier to mark them as primary or not..
                    switch($use_master_key){
                        case 'customer_id':
                            $plugins['customer']->set_primary_user_id($data['customer_id'],$user_id);
                            break;
                    }
                }else{
                    // check if this contact was the old customer/supplier primary and
                    switch($use_master_key){
                        case 'customer_id':
                            $customer_data = $plugins['customer']->get_customer($data['customer_id']);
                            if($customer_data['primary_user_id'] == $user_id){
                                $plugins['customer']->set_primary_user_id($data['customer_id'],0);
                            }
                            break;
                    }
                }
            }
        }
        module_extra::save_extras('user','user_id',$user_id);

        return $user_id;
	}*/
	public static function delete_user($user_id){
		$user_id=(int)$user_id;
		if(_DEMO_MODE && $user_id == 1){
			return;
		}
		$sql = "DELETE FROM "._DB_PREFIX."user WHERE user_id = '".$user_id."' LIMIT 1";
		$res = query($sql);
		module_note::note_delete("user",$user_id);
        $sql = "DELETE FROM "._DB_PREFIX."user_customer_rel WHERE user_id = '".$user_id."'";
        $res = query($sql);
        $sql = "UPDATE "._DB_PREFIX."user SET linked_parent_user_id = 0 WHERE linked_parent_user_id = '".$user_id."'";
        $res = query($sql);
	}
    public function login_link($user_id){
        return module_security::generate_auto_login_link($user_id);
    }



    /*
    array(
    'category' => 'Ticket',
    'name' => 'Tickets',
    'module' => 'ticket',
    'edit' => 1,
    )
    */

    static $users_by_perm_cache = array();
    public static function get_users_by_permission($access_requirements){

        $cache_key = md5(serialize($access_requirements));
        if(isset(self::$users_by_perm_cache[$cache_key]))return self::$users_by_perm_cache[$cache_key];

        // find all the users that have these permissions set.
        $permission = get_single('security_permission',array(
            'name',
            'category',
            'module',
        ),array(
            $access_requirements['name'],
            $access_requirements['category'],
            $access_requirements['module'],
        ));
        $security_permission_id = false;
        if($permission){
            $security_permission_id = $permission['security_permission_id'];
        }
        if(!$security_permission_id){
            return array();
        }
        // we have the ID!
        // time to check the actual permission now.
        $check_for_permissions = array();
        foreach(module_security::$available_permissions as $available_permission){
            if(isset($access_requirements[$available_permission])){
                // we want users with this permission.
                $check_for_permissions[$available_permission] = true;
            }
        }
        //echo $security_permission_id;
        //print_r($check_for_permissions);
        // do a query to find out permissions based on the users role, or by the hardcoded assigned roles.
        $sql = "SELECT u.*, u.user_id AS id FROM `"._DB_PREFIX."user` u WHERE u.user_id IN (";
        $sql .= "SELECT ur.user_id FROM `"._DB_PREFIX."security_role_perm` sp LEFT JOIN `"._DB_PREFIX."user_role` ur
        USING (security_role_id) WHERE sp.security_permission_id = $security_permission_id";
        foreach($check_for_permissions as $permission_type => $tf){
            $sql .= " AND sp.`".$permission_type."` = 1";
        }
        $sql .= ') OR u.user_id IN (';
        // no role set - just use hardcoded perms on the user account.
        $sql .= "SELECT up.user_id FROM `"._DB_PREFIX."user_perm` up WHERE security_permission_id = $security_permission_id";
        foreach($check_for_permissions as $permission_type => $tf){
            $sql .= " AND up.`".$permission_type."` = 1";
        }
        $sql .= ') OR u.user_id = 1';
//        echo $sql;
        $users = qa($sql);

        self::$users_by_perm_cache[$cache_key] = $users;
        return $users;

    }
    
    static $users_by_group_cache = array();
    public static function get_users_by_group($group_name){
    	$cache_key = md5(serialize($group_name));
    	if(isset(self::$users_by_group_cache[$cache_key]))return self::$users_by_group_cache[$cache_key];
    	
    	
    	$sql = "SELECT u.*, u.user_id AS id FROM `"._DB_PREFIX."user` u WHERE u.user_id IN (";
    	
    	$sql .= "SELECT gm.owner_id";
    	$sql .= " FROM `"._DB_PREFIX."group_member` gm";
    	$sql .= " , `"._DB_PREFIX."group` g WHERE g.group_id = gm.group_id";
    	$sql .= " AND g.name = '".mysql_real_escape_string($group_name)."'";
    	$sql .= " AND gm.owner_table = 'user'";
    	
    	$sql .= ')';
    	
    	error_log($sql);
    	
    	$users = qa($sql);
    	
    	self::$users_by_group_cache[$cache_key] = $users;
    	return $users;
    }

    public static function get_staff_members() {
        // todo: a different kinda perimssion outlines staff members maybe?
        return self::get_users_by_permission(
                array(
                    'category' => 'Job',
                    'name' => 'Job Tasks',
                    'module' => 'job',
                    'edit' => 1,
                )
            );
    }


    public function get_upgrade_sql(){
        $sql = '';
        $fields = get_fields('user');
        if(!isset($fields['last_name'])){
            $sql .= 'ALTER TABLE  `'._DB_PREFIX.'user` ADD  `last_name` VARCHAR( 90 ) NOT NULL DEFAULT  \'\' AFTER  `name`;';
        }
        if(!isset($fields['linked_parent_user_id'])){
            $sql .= 'ALTER TABLE  `'._DB_PREFIX.'user` ADD  `linked_parent_user_id` INT( 11 ) NOT NULL DEFAULT  \'0\' AFTER  `customer_id`;';
        }

        // check for indexes
        self::add_table_index('user','customer_id');
        self::add_table_index('user','linked_parent_user_id');
        /*$sql_check = 'SHOW INDEX FROM `'._DB_PREFIX.'user';
        $res = qa($sql_check);
        //print_r($res);exit;
        $add_index=true;
        foreach($res as $r){
            if(isset($r['Column_name']) && $r['Column_name'] == 'customer_id'){
                $add_index=false;
            }
        }
        if($add_index){
            $sql .= 'ALTER TABLE  `'._DB_PREFIX.'user` ADD INDEX ( `customer_id` );';
        }

        $add_index=true;
        foreach($res as $r){
            if(isset($r['Column_name']) && $r['Column_name'] == 'linked_parent_user_id'){
                $add_index=false;
            }
        }
        if($add_index){
            $sql .= 'ALTER TABLE  `'._DB_PREFIX.'user` ADD INDEX ( `linked_parent_user_id` );';
        }*/


        $sql_check = "SHOW TABLES LIKE '"._DB_PREFIX."user_customer_rel'";
        $res = qa1($sql_check);
        if(!$res || !count($res)){
            // create our new table.
            $sql .= 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX.'user_customer_rel` (
            `user_id` int(11) NOT NULL,
            `customer_id` int(11) NOT NULL,
            `primary` INT NOT NULL DEFAULT  \'0\',
            PRIMARY KEY (`user_id`,`customer_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;';
        }else{
            // check primary exists
            $fields = get_fields('user_customer_rel');
            if(!isset($fields['primary'])){
                $sql .= 'ALTER TABLE  `'._DB_PREFIX.'user_customer_rel` ADD  `primary` INT NOT NULL DEFAULT  \'0\'';
            }
        }
        return $sql;
    }


    public function get_install_sql(){
        ob_start();
        //`user_type_id` INT(11) NOT NULL DEFAULT '2',
        /*
CREATE TABLE IF NOT EXISTS `<?php echo _DB_PREFIX; ?>user_type` (
  `user_type_id` int(11) NOT NULL AUTO_INCREMENT,
  `type_name` varchar(50) NULL,
  `date_created` datetime NULL,
  `date_updated` datetime NULL,
  `create_user_id` int(11) NULL,
  `update_user_id` int(11) NULL,
  PRIMARY KEY (`user_type_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;*/
        ?>

    CREATE TABLE `<?php echo _DB_PREFIX; ?>user` (
    `user_id` int(11) NOT NULL auto_increment,
    `customer_id` INT(11) NULL,
    `linked_parent_user_id` INT(11) NOT NULL DEFAULT '0',
    `status_id` INT(11) NOT NULL DEFAULT '1',
    `email` varchar(255) NOT NULL DEFAULT  '',
    `password` varchar(255) NOT NULL DEFAULT  '',
    `name` varchar(255) NOT NULL DEFAULT  '',
    `last_name` varchar(255) NOT NULL DEFAULT  '',
    `phone` varchar(255) NOT NULL DEFAULT  '',
    `fax` varchar(255) NOT NULL DEFAULT  '',
    `mobile` varchar(255) NOT NULL DEFAULT  '',
    `language` varchar(4) NOT NULL DEFAULT  '',
    `date_created` date NULL,
    `date_updated` date NULL,
    PRIMARY KEY  (`user_id`),
    KEY `customer_id` (`customer_id`),
    KEY `linked_parent_user_id` (`linked_parent_user_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;



    INSERT INTO `<?php echo _DB_PREFIX; ?>user` VALUES (1, 0, 0, 1, 'admin@example.com', 'password', 'Administrator', '',  '+61 7 55 123 456', '+61 7 56 321 654', '+61419789789', 'en', NOW(), NOW());
    INSERT INTO `<?php echo _DB_PREFIX; ?>user` VALUES (2, 0, 0, 1, 'user@example.com', 'password', 'User', '', '+61 7 55 123 456', '+61 7 56 321 654', '+61419789789', 'en', NOW(), NOW());
    INSERT INTO `<?php echo _DB_PREFIX; ?>user` VALUES (3, 1, 0, 1, 'user1@example.com', 'password', 'Contact 1', '',  '+61 7 55 123 456', '+61 7 56 321 654', '+61419789789', 'en', NOW(), NOW());
    INSERT INTO `<?php echo _DB_PREFIX; ?>user` VALUES (4, 2, 0, 1, 'user2@example.com', 'password', 'Contact 2', '', '+61 7 55 123 456', '+61 7 56 321 654', '+61419789789', 'en', NOW(), NOW());

    CREATE TABLE IF NOT EXISTS `<?php echo _DB_PREFIX; ?>user_perm` (
    `user_id` int(11) NOT NULL,
    `security_permission_id` int(11) NOT NULL,
    `view` tinyint(4) NOT NULL DEFAULT '0',
    `edit` tinyint(4) NOT NULL DEFAULT '0',
    `delete` tinyint(4) NOT NULL DEFAULT '0',
    `create` tinyint(4) NOT NULL DEFAULT '0',
    `date_created` datetime NULL,
    `date_updated` datetime NULL,
    `create_user_id` int(11) NULL,
    `update_user_id` int(11) NULL,
    PRIMARY KEY (`user_id`,`security_permission_id`),
    KEY `security_permission_id` (`security_permission_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    CREATE TABLE IF NOT EXISTS `<?php echo _DB_PREFIX; ?>user_role` (
    `user_id` int(11) NOT NULL,
    `security_role_id` int(11) NOT NULL,
    PRIMARY KEY (`user_id`,`security_role_id`),
    KEY `security_role_id` (`security_role_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;


    CREATE TABLE IF NOT EXISTS `<?php echo _DB_PREFIX; ?>user_customer_rel` (
    `user_id` int(11) NOT NULL,
    `customer_id` int(11) NOT NULL,
    `primary` INT NOT NULL DEFAULT  '0',
    PRIMARY KEY (`user_id`,`customer_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;


    ALTER TABLE `<?php echo _DB_PREFIX; ?>user_perm`
    ADD CONSTRAINT `<?php echo _DB_PREFIX; ?>user_perm_ibfk_1`  FOREIGN KEY (`security_permission_id`) REFERENCES `<?php echo _DB_PREFIX; ?>security_permission` (`security_permission_id`) ON DELETE CASCADE,
    ADD CONSTRAINT `<?php echo _DB_PREFIX; ?>user_perm_ibfk_2`  FOREIGN KEY (`user_id`) REFERENCES `<?php echo _DB_PREFIX; ?>user` (`user_id`) ON DELETE CASCADE;

    ALTER TABLE `<?php echo _DB_PREFIX; ?>user_role`
    ADD CONSTRAINT `<?php echo _DB_PREFIX; ?>user_role_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `<?php echo _DB_PREFIX; ?>user` (`user_id`) ON DELETE CASCADE,
    ADD CONSTRAINT `<?php echo _DB_PREFIX; ?>user_role_ibfk_2` FOREIGN KEY (`security_role_id`) REFERENCES `<?php echo _DB_PREFIX; ?>security_role` (`security_role_id`) ON DELETE CASCADE;



    <?php
        /*INSERT INTO `<?php echo _DB_PREFIX; ?>user_type` VALUES (1, 'User', NOW(), NOW(), 1, 0);
        INSERT INTO `<?php echo _DB_PREFIX; ?>user_type` VALUES (2, 'Contact', NOW(), NOW(), 1, 0);
        INSERT INTO `<?php echo _DB_PREFIX; ?>user_type` VALUES (3, 'Support', NOW(), NOW(), 1, 0);
        */
        return ob_get_clean();
    }


}