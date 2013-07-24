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


if(defined('COMPANY_UNIQUE_CONFIG') && COMPANY_UNIQUE_CONFIG && function_exists('hook_add')){
    // here so we catch config vars sooner rather than later
    hook_add('config_init_vars','module_company::hook_config_init_vars');
}

define('_COMPANY_ACCESS_ALL','All companies in system'); // do not change string
define('_COMPANY_ACCESS_ASSIGNED','Only companies I am assigned to in staff area'); // do not change string
define('_COMPANY_ACCESS_CONTACT','Only companies I am assigned to as a contact'); // do not change string

// todo: remove the two "Only Companies" entries from security database that would have been added to early adopters

class module_company extends module_base{
	
	var $links;
    public $version = 2.123;
    // 2.123 - 2013-07-15 - permission improvement
    // 2.122 - 2013-07-02 - bug fix with single companies defined
    // 2.121 - 2013-06-26 - update to edit config.php if unique config variables required -should fix errors
    // 2.12 - 2013-06-21 - custom configuration variables available per company (see company_unique_config setting)
    // 2.11 - 2013-06-21 - custom configuration variables available per company (see company_unique_config setting)
    // 2.1 - 2013-06-18 - first release - basic company/customer linking

    private static $do_company_custom_config = false;

    public static function can_i($actions,$name=false,$category=false,$module=false){
        if(!$module)$module=__CLASS__;
        return parent::can_i($actions,$name,$category,$module);
    }
	public static function get_class() {
        return __CLASS__;
    }
	function init(){
		$this->links = array();
		$this->module_name = "company";
		$this->module_position = 10;


        if(self::can_i('edit','Company')){
			$this->links[] = array(
				"name"=>"Company",
				"p"=>"company_settings",
				"icon"=>"icon.png",
				"args"=>array('company_id'=>false),
				'holder_module' => 'config', // which parent module this link will sit under.
				'holder_module_page' => 'config_admin',  // which page this link will be automatically added to.
				'menu_include_parent' => 0,
			);
        }

        self::$do_company_custom_config = defined('COMPANY_UNIQUE_CONFIG') && COMPANY_UNIQUE_CONFIG; //module_config::c('company_unique_config',0);

	}

    
    public static function link_generate($company_id=false,$options=array(),$link_options=array()){

        $key = 'company_id';
        if($company_id === false && $link_options){
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
        if(!isset($options['type']))$options['type']='company';
        $options['page'] = 'company_settings';
        if(!isset($options['arguments'])){
            $options['arguments'] = array();
        }
        $options['arguments']['company_id'] = $company_id;
        $options['module'] = 'company';
        $data = self::get_company($company_id);
        $options['data'] = $data;
        // what text should we display in this link?
        $options['text'] = (!isset($data['name'])||!trim($data['name'])) ? 'N/A' : $data['name'];
        //if(isset($data['company_id']) && $data['company_id']>0){
            $bubble_to_module = array(
                'module' => 'config',
                'argument' => 'company_id',
            );
       // }
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

	public static function link_open($company_id,$full=false){
        return self::link_generate($company_id,array('full'=>$full));
    }



    public function process(){
		if(isset($_REQUEST['butt_del']) && $_REQUEST['butt_del'] && $_REQUEST['company_id'] && self::can_i('delete','Company')){
            $data = self::get_company($_REQUEST['company_id']);
            if($data && $data['company_id'] == $_REQUEST['company_id'] && module_form::confirm_delete('company_id',"Really delete company: ".$data['name'],self::link_open($_REQUEST['company_id']))){
                $this->delete_company($_REQUEST['company_id']);
                set_message("company deleted successfully");
                redirect_browser($this->link_open(false));
            }
		}else if('save_company' == $_REQUEST['_process'] && self::can_i('edit','Company')){
			$company_id = update_insert('company_id',$_REQUEST['company_id'],'company',$_POST);
            set_message('Company saved successfully');
            redirect_browser($this->link_open($company_id));
		}
	}

    public static function get_current_logged_in_company_id(){
        if(module_security::is_logged_in()){
            $company_access = self::get_company_data_access();
            switch($company_access){
                case _COMPANY_ACCESS_ALL:

                    break;
                case _COMPANY_ACCESS_ASSIGNED:
                case _COMPANY_ACCESS_CONTACT:
                    // this is a possibility that this user only has access to a single customer
                    $companies = self::get_companys();
                    if(count($companies)==1){
                        // only 1 woo! get this id and load in any custom config values.
                        $company = array_shift($companies);
                        if($company && $company['company_id']>0){
                            return $company['company_id'];
                        }
                    }
            }
        }
        return false;
    }

    public static function hook_config_init_vars($callback_name, $existing_config_vars){
        $new_config_vars = array();
        if(self::$do_company_custom_config){
            // only do this if the current logged in user is restricted to a single company.
            // todo - manually check 'company_unique_config' field in db with manual sql to ensure we're still doing this right
            $company_id = self::get_current_logged_in_company_id();
            if($company_id>0){
                $sql = "SELECT `key`,`val` FROM `"._DB_PREFIX."company_config` WHERE company_id = ".(int)$company_id;
                foreach(qa($sql) as $c){
                    $new_config_vars[$c['key']] = $c['val'];
                }
            }
        }
        return $new_config_vars;
    }

    public static function save_company_config($key,$val){
        if(self::$do_company_custom_config){
            $company_id = self::get_current_logged_in_company_id();
            if($company_id>0){
                // only save this value if it's different to the current value.
                $current_value = module_config::c($key);
                if($val != $current_value){
                    $sql = "REPLACE INTO `"._DB_PREFIX."company_config` SET `key` = '".mysql_real_escape_string($key)."', company_id = ".(int)$company_id.", `val` = '".mysql_real_escape_string($val)."'";
                    query($sql);
                    set_message('Successfully saved unique company configuration');
                    return true;
                }
            }
        }
        return false;
    }

    public static function delete_company($company_id){
        $sql = "DELETE FROM `"._DB_PREFIX."company` WHERE `company_id` = ".(int)$company_id."";
        query($sql);
        $sql = "DELETE FROM `"._DB_PREFIX."company_customer` WHERE `company_id` = ".(int)$company_id."";
        query($sql);
        $sql = "DELETE FROM `"._DB_PREFIX."company_user_rel` WHERE `company_id` = ".(int)$company_id."";
        query($sql);

    }

	public static function get_company($company_id){
		$company = get_single("company","company_id",$company_id);
		if($company){
			// optional processing here later on.
		}
		return $company;
	}

	public static function get_companys_by_customer($customer_id){
		$sql = "SELECT c.*, c.company_id AS id ";
        $sql .= " FROM `"._DB_PREFIX."company_customer` cc ";
        $sql .= " LEFT JOIN `"._DB_PREFIX."company` c USING (company_id) ";
        $sql .= " WHERE cc.customer_id = '".(int)$customer_id."'";
        $sql .= " GROUP BY c.company_id ";
        return qa($sql);
	}

    public static function get_company_data_access() {

        if(class_exists('module_security',false)){
            return module_security::can_user_with_options(module_security::get_loggedin_id(),'Company Data Access',array(
                                                                                                   _COMPANY_ACCESS_ALL,
                                                                                                   _COMPANY_ACCESS_ASSIGNED,
                                                                                                   _COMPANY_ACCESS_CONTACT,
                                                                                                                       ));
        }else{
            return true;
        }
    }

    static $get_companys_cache=false;

    public static function get_companys($search=false){
        if(self::$get_companys_cache!==false)return self::$get_companys_cache;
        $where = 'WHERE 1';
        $sql = "SELECT c.*, c.company_id AS id ";
        $sql .= " FROM `"._DB_PREFIX."company` c ";
        $company_access = self::get_company_data_access();
        switch($company_access){
            case _COMPANY_ACCESS_ALL:

                break;
            case _COMPANY_ACCESS_ASSIGNED:
                // we only want companies that are directly linked with the currently logged in user contact (from the staff user account settings area)
                $sql .= " LEFT JOIN `"._DB_PREFIX."company_user_rel` cur ON c.company_id = cur.company_id ";
                $where .= " AND (cur.user_id = ".(int)module_security::get_loggedin_id().")";
                break;
            case _COMPANY_ACCESS_CONTACT:
                // only parent company of current user account contact
                $sql .= " LEFT JOIN `"._DB_PREFIX."company_customer` cc USING (company_id) ";
                $sql .= " LEFT JOIN `"._DB_PREFIX."user` u ON cc.customer_id = u.customer_id ";
                $where .= " AND u.user_id = ".(int)module_security::get_loggedin_id()."";
                break;
        }
        $sql .= $where;
        $sql .= " GROUP BY c.company_id ";
        self::$get_companys_cache = qa($sql);
        return self::$get_companys_cache;
	}

    
	public static function get_customers($company_id){
		$sql = "SELECT gm.company_id, gm.customer_id ";
        $sql .= " FROM `"._DB_PREFIX."company_customer` gm ";
        $sql .= " WHERE gm.company_id = ".(int)$company_id;
        return qa($sql);
	}
    
    public static function delete_customer($company_id,$customer_id){
        $sql = "DELETE FROM `"._DB_PREFIX."company_customer` WHERE ";
        $sql .= " `company_id` = '".(int)$company_id."' AND ";
        $sql .= " `customer_id` = '".(int)$customer_id."' LIMIT 1";
        query($sql);
    }
    public static function add_customer_to_company($company_id,$customer_id){
        if($company_id > 0 && $customer_id > 0){
            $sql = "REPLACE INTO `"._DB_PREFIX."company_customer` SET ";
            $sql .= " `company_id` = '".(int)$company_id."', ";
            $sql .= " `customer_id` = '".(int)$customer_id."' ";
            query($sql);
        }
    }


    public static function get_companys_by_user($user_id){
		$sql = "SELECT c.*, c.company_id AS id ";
        $sql .= " FROM `"._DB_PREFIX."company_user_rel` cc ";
        $sql .= " LEFT JOIN `"._DB_PREFIX."company` c USING (company_id) ";
        $sql .= " WHERE cc.user_id = '".(int)$user_id."'";
        $sql .= " GROUP BY c.company_id ";
        return qa($sql);
	}
    public static function delete_user($company_id,$user_id){
        $sql = "DELETE FROM `"._DB_PREFIX."company_user_rel` WHERE ";
        $sql .= " `company_id` = '".(int)$company_id."' AND ";
        $sql .= " `user_id` = '".(int)$user_id."' LIMIT 1";
        query($sql);
    }
    public static function add_user_to_company($company_id,$user_id){
        if($company_id > 0 && $user_id > 0){
            $sql = "REPLACE INTO `"._DB_PREFIX."company_user_rel` SET ";
            $sql .= " `company_id` = '".(int)$company_id."', ";
            $sql .= " `user_id` = '".(int)$user_id."' ";
            query($sql);
        }
    }


    // stops our loopback bug
    private static $checking_enabled = false;
    public static function is_enabled(){
        if(self::$checking_enabled)return false;
        self::$checking_enabled=true;
        $companys = self::get_companys();
        return count($companys)>0 && module_config::c('company_enabled',1);
    }
    public function get_upgrade_sql(){
        $sql = '';
        if(!self::db_table_exists('company_user_rel')){
            $sql .= 'CREATE TABLE `'._DB_PREFIX.'company_user_rel` (
  `company_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY  (`company_id`, `user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;';
        }
        if(!self::db_table_exists('company_config')){
            $sql .= 'CREATE TABLE `'._DB_PREFIX.'company_config` (
  `key` varchar(255) NOT NULL,
  `company_id` int(11) NOT NULL,
  `val` text NOT NULL,
  PRIMARY KEY  (`key`, `company_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;';
        }
        return $sql;
    }

    public function get_install_sql(){
        $sql = 'CREATE TABLE `'._DB_PREFIX.'company` (
  `company_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL DEFAULT \'\',
  `date_created` datetime NOT NULL,
  `date_updated` datetime NULL,
  `create_user_id` int(11) NOT NULL,
  `update_user_id` int(11) NULL,
  `create_ip_address` varchar(15) NOT NULL,
  `update_ip_address` varchar(15) NULL,
  PRIMARY KEY (`company_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8; ';

        $sql .= "\n";

        $sql .= 'CREATE TABLE `'._DB_PREFIX.'company_customer` (
  `company_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  KEY `company_id` (`company_id`),
  KEY `customer_id` (`customer_id`),
  PRIMARY KEY ( `company_id`, `customer_id` )
) ENGINE=InnoDB  DEFAULT CHARSET=utf8; ';

        $sql .= 'CREATE TABLE `'._DB_PREFIX.'company_user_rel` (
  `company_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  KEY `company_id` (`company_id`),
  KEY `user_id` (`user_id`),
  PRIMARY KEY  (`company_id`, `user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;';

        $sql .= 'CREATE TABLE `'._DB_PREFIX.'company_config` (
  `key` varchar(255) NOT NULL,
  `company_id` int(11) NOT NULL,
  `val` text NOT NULL,
  PRIMARY KEY  (`key`, `company_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;';

       /* $sql .= 'CREATE TABLE `'._DB_PREFIX.'company_template` (
  `company_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  KEY `company_id` (`company_id`),
  KEY `user_id` (`user_id`),
  PRIMARY KEY  (`company_id`, `user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;';*/

        return $sql;
    }


}