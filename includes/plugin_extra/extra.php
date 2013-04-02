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

define('_EXTRA_FIELD_DELIM','$#%|');
define('_EXTRA_DISPLAY_TYPE_COLUMN',1);

    function sort_extra_defaults($a,$b){
        return $a['order']>$b['order'];
    }

class module_extra extends module_base{
	
	var $links;


    public static function can_i($actions,$name=false,$category=false,$module=false){
        if(!$module)$module=__CLASS__;
        return parent::can_i($actions,$name,$category,$module);
    }
	public static function get_class() {
        return __CLASS__;
    }
	function init(){
        $this->version = 2.24;
        // 2.16 - fix to disable editing when page isn't editable. this caused double ups on extra keys in table listings.
        // 2.17 - hooks for the encryption module to take over.
        // 2.18 - bug fix with new extra field types.
        // 2.19 - better saving of extra fields etc.. in sync with member external signup extra field feature
        // 2.2 - started work on sorting extra fields
        // 2.21 - bug fix
        // 2.22 - see Settings-Extra Fields for new options.
        // 2.23 - Extra bug fix
        // 2.24 - permission improvement

		$this->links = array();
		$this->module_name = "extra";
		$this->module_position = 8882;
        module_config::register_css('extra','extra.css');
	}

    public function pre_menu(){
        if($this->is_installed() && module_config::can_i('edit','Settings') && $this->can_i('edit','Extra Fields')){
            $this->links['extra_settings'] = array(
                "name"=>"Extra Fields",
                "p"=>"extra_settings",
                'args'=>array('extra_default_id'=>false),
                'holder_module' => 'config', // which parent module this link will sit under.
                'holder_module_page' => 'config_admin',  // which page this link will be automatically added to.
                'menu_include_parent' => 0,
            );
        }
    }

    public function process(){
        if('save_extra_default' == $_REQUEST['_process']){

            if(!module_config::can_i('edit','Settings')){
                die('No perms to save ticket settings.');
            }
            $extra_default_id = update_insert('extra_default_id',$_REQUEST['extra_default_id'],'extra_default',$_POST);
            if(isset($_REQUEST['butt_del'])){
                // deleting ticket data_key all together
                delete_from_db('extra_default','extra_default_id',$_REQUEST['extra_default_id']);
                set_message('Extra field deleted successfully.');
                redirect_browser($_SERVER['REQUEST_URI']);
            }
            set_message('Extra field saved successfully');
            redirect_browser($_SERVER['REQUEST_URI']);
            
            
        }
    }

    
    public static function link_generate($extra_default_id=false,$options=array(),$link_options=array()){

        $key = 'extra_default_id';
        if($extra_default_id === false && $link_options){
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
        if(!isset($options['type']))$options['type']='extra';
        $options['page'] = 'extra_settings';
        if(!isset($options['arguments'])){
            $options['arguments'] = array();
        }
        $options['arguments']['extra_default_id'] = $extra_default_id;
        $options['module'] = 'extra';
        $data = self::get_extra_default($extra_default_id);
        $options['data'] = $data;
        // what text should we display in this link?
        $options['text'] = (!isset($data['extra_key'])||!trim($data['extra_key'])) ? 'N/A' : htmlspecialchars($data['extra_key']);
        //if(isset($data['extra_default_id']) && $data['extra_default_id']>0){
            $bubble_to_module = array(
                'module' => 'config',
                'argument' => 'extra_default_id',
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

    public static function link_open_extra_default($extra_default_id,$full=false){
        return self::link_generate($extra_default_id,array('full'=>$full));
    }


    
	public static function display_extras($options){
		$owner_id = (isset($options['owner_id']) && $options['owner_id']) ? (int)$options['owner_id'] : false;
		$owner_table = (isset($options['owner_table']) && $options['owner_table']) ? $options['owner_table'] : false;
		$layout = (isset($options['layout']) && $options['layout']) ? $options['layout'] : false;
        $allow_new = true;
        if(isset($options['allow_new']) && !$options['allow_new'])$allow_new = false;
        $allow_edit = (!isset($options['allow_edit']) || (isset($options['allow_edit'])&&$options['allow_edit']));
        if(!module_security::is_page_editable())$allow_edit=false;
        // todo ^^ flow this permission check through to the "save" section.
        $html = '';
		if($owner_id && $owner_table){
            $default_fields = self::get_defaults($owner_table);
            // we have all that we need to display some extras!! yey!!
			$extra_items = self::get_extras(array('owner_table'=>$owner_table,'owner_id'=>$owner_id));
            $extra_items = self::sort_extras($extra_items,$default_fields);
			foreach($extra_items as $extra_item){
                $extra_id=$extra_item['extra_id'];
                $id = 'extra_'. preg_replace('#\W+#','_',$extra_item['extra_key']);
				ob_start();
                ?>
                <tr id="extra_<?php echo $extra_id;?>">
                    <th>
                        <?php if($allow_edit){ ?>
                            <span class="extra_field_key" onclick="$(this).hide(); $(this).parent().find('input').show();"><?php echo htmlspecialchars($extra_item['extra_key']);?></span>
                            <input type="text" name="extra_<?php echo $owner_table;?>_field[<?php echo $extra_id;?>][key]" value="<?php echo htmlspecialchars($extra_item['extra_key']);?>" class="extra_field" style="display:none;">
                        <?php }else{
                            echo htmlspecialchars($extra_item['extra_key']);?>
                            <input type="hidden" name="extra_<?php echo $owner_table;?>_field[<?php echo $extra_id;?>][key]" value="<?php echo htmlspecialchars($extra_item['extra_key']);?>">
                        <?php } ?>
                    </th>
                    <td>
                        <input type="text" name="extra_<?php echo $owner_table;?>_field[<?php echo $extra_id;?>][val]" id="<?php echo $id;?>" value="<?php echo htmlspecialchars($extra_item['extra']);?>">
                    </td>
                </tr>
                <?php
                $html .= ob_get_clean();
			}
            if(module_security::is_page_editable()){
            $extra_id = 'new';
            ob_start();

            // check if there are any "more" fields to add
            $more_fields_available = $allow_new;
            //if(!$more_fields_available){
                foreach($default_fields as $default_id => $default){
                    // check this key islany already existing.
                    foreach($extra_items as $extra_item){
                        if($extra_item['extra_key'] == $default['key']){
                            unset($default_fields[$default_id]);
                            continue 2;
                        }
                    }
                    $more_fields_available = true;
                }
            //}
            if($more_fields_available){
                ?>
                <tr id="extra_<?php echo $owner_table;?>_options_<?php echo $extra_id;?>" <?php if(!module_config::c('hide_extra',1)){ ?>style="display:none;"<?php } ?>>
                    <th>

                    </th>
                    <td>
                        <a href="#" onclick="$('#extra_<?php echo $owner_table;?>_options_<?php echo $extra_id;?>').hide();$('#extra_<?php echo $owner_table;?>_holder_<?php echo $extra_id;?>').show(); return false;"><?php _e('more fields &raquo;');?></a>
                    </td>
                </tr>
            <?php } // more fields available ?>

            <?php if(count($default_fields) || $allow_new){ ?>
                <tbody id="extra_<?php echo $owner_table;?>_holder_<?php echo $extra_id;?>" <?php if(module_config::c('hide_extra',1)){ ?>style="display:none;"<?php } ?>>
                <!-- show all other options here from this $owner_table -->
                <?php
                $defaultid = 0;
                foreach($default_fields as $default){
                    $defaultid ++;
                    $id = 'extra_'. preg_replace('#\W+#','_',$default['key']);
                    ?>
                    <tr>
                        <th>

                            <?php if($allow_edit){ ?>
                                <span class="extra_field_key" onclick="$(this).hide(); $(this).parent().find('input').show();"><?php echo htmlspecialchars($default['key']);?></span>
                                <input type="text" name="extra_<?php echo $owner_table;?>_field[new<?php echo $defaultid;?>][key]" value="<?php echo htmlspecialchars($default['key']);?>" class="extra_field" style="display:none;">
                            <?php }else{
                                echo htmlspecialchars($default['key']);?>
                                <input type="hidden" name="extra_<?php echo $owner_table;?>_field[new<?php echo $defaultid;?>][key]" value="<?php echo htmlspecialchars($default['key']);?>">
                            <?php } ?>

                        </th>
                        <td>
                            <input type="text" name="extra_<?php echo $owner_table;?>_field[new<?php echo $defaultid;?>][val]" id="<?php echo $id;?>" value="<?php ?>">
                        </td>
                    </tr>
                <?php } ?>
                <?php if($allow_new && module_security::is_admin()){ ?>
                    <tr id="extra_<?php echo $extra_id;?>">
                        <th>
                            <input type="text" name="extra_<?php echo $owner_table;?>_field[<?php echo $extra_id;?>][key]" value="<?php ?>" class="extra_field">
                        </th>
                        <td>
                            <input type="text" name="extra_<?php echo $owner_table;?>_field[<?php echo $extra_id;?>][val]" value="<?php ?>">
                            <?php _h('Enter anything you like in this blank field. eg: Passwords, Links, Notes, etc..'); ?>
                        </td>
                    </tr>
                <?php } ?>
                </tbody>
            <?php
            }
            $html .= ob_get_clean();
            }
		}

        // pass it out for a hook
        // this is really only used in the security module.
        $result = hook_handle_callback('extra_fields_output',$owner_table,$owner_id,$html);
        if($result && count($result)){
            foreach($result as $r){
                $html = $r; // bad. handle multiple hooks.
            }
        }

        print $html;
	}

    public static $config = array();
    public static function save_extras($owner_table,$owner_key,$owner_id,$allow_new_keys=true,$allow_new_values=true){

        // hack to add extra configuration
        if(isset(self::$config['allow_new_keys'])){
            $allow_new_keys = self::$config['allow_new_keys'];
        }
        if(isset(self::$config['allow_new_values'])){
            $allow_new_keys = self::$config['allow_new_values'];
        }

        if(isset($_REQUEST['extra_'.$owner_table.'_field']) && is_array($_REQUEST['extra_'.$owner_table.'_field'])){
            $owner_id = (int)$owner_id;
            if($owner_id<=0){
                if(isset($_REQUEST[$owner_key])){
                    $owner_id = (int)$_REQUEST[$owner_key];
                }
            }
            if($owner_id<=0)return; // failed for some reason?
            $existing_extras = self::get_extras(array('owner_table'=>$owner_table,'owner_id'=>$owner_id));
            $default_keys = self::get_defaults($owner_table);
            foreach($_REQUEST['extra_'.$owner_table.'_field'] as $extra_id => $extra_data){
                $key = trim($extra_data['key']);
                $val = trim($extra_data['val']);
                if(!$key || $val==''){
                    unset($_REQUEST['extra_'.$owner_table.'_field'][$extra_id]);
                    continue;
                }
                // check if this key exists in the system.
                if(!$allow_new_keys){
                    $exists=false;
                    foreach($default_keys as $default_key){
                        if($default_key['key']==$key){
                            $exists=true;
                        }
                    }
                    if(!$exists){
                        unset($_REQUEST['extra_'.$owner_table.'_field'][$extra_id]);
                        continue;
                    }
                }
                $extra_db = array(
                    'extra_key' => $key,
                    'extra' => $val,
                    'owner_table' => $owner_table,
                    'owner_id' => $owner_id,
                );
                $extra_id = (int)$extra_id;
                // security checking.
                if($extra_id > 0){
                    // check if this extra is an existing one.
                    if(!isset($existing_extras[$extra_id])){
                        $extra_id = 0; // not updating an existing one against this owner
                    }
                }
                if(!$extra_id && !$allow_new_values){
                    // we are not allowed to create new values, only update existing values.
                    // disallow this.
                    unset($_REQUEST['extra_'.$owner_table.'_field'][$extra_id]);
                    continue;
                }
                $extra_id = update_insert('extra_id',$extra_id,'extra',$extra_db);
            }
            // work out which ones were not saved.
            foreach($existing_extras as $existing_extra){
                // we don't want to delete extra fields when saving a public customer signup form.
                // customer signup (and other parts down the track) will set these flags for us.
                if(
                    (!isset(self::$config['delete_existing_empties']) || (isset(self::$config['delete_existing_empties']) && self::$config['delete_existing_empties']))
                    &&
                    !isset($_REQUEST['extra_'.$owner_table.'_field'][$existing_extra['extra_id']])
                ){
                    // remove it.
                    $sql = "DELETE FROM "._DB_PREFIX."extra WHERE extra_id = '".(int)$existing_extra['extra_id']."' AND `owner_table` = '".mysql_real_escape_string($owner_table)."' AND `owner_id` = '".(int)$owner_id."' LIMIT 1";
                    query($sql);
                }
            }
		}
    }

	public static function delete_extras($owner_table,$owner_key,$owner_id){
		$extra_items = self::get_extras(array('owner_table'=>$owner_table,'owner_id'=>$owner_id));
        foreach($extra_items as $extra_item){
            $sql = "DELETE FROM "._DB_PREFIX."extra WHERE extra_id = '".(int)$extra_item['extra_id']."' LIMIT 1";
            query($sql);
        }

    }
	public static function get_extra($extra_id){
		$extra = get_single("extra","extra_id",$extra_id);
		if($extra){
			// optional processing here later on.
		}
		return $extra;
	}

	public static function get_extras($search=false){
		return get_multiple("extra",$search,"extra_id","exact","extra_id");
	}


    /**
     * @static
     * @param $owner_table
     * @return array
     *
     * search the extra fields for default keys
     * (ie: keys that have been used on this owner_table before)
     *
     */
    public static function sort_extras($extra_items,$default_items){
        // hack to sort our extra list based on the provided default list.
        foreach($extra_items as $extra_id => $extra_item){
            $extra_items[$extra_id]['order'] = isset($default_items[$extra_item['extra_key']]) ? $default_items[$extra_item['extra_key']]['order'] : 0;
        }
        uasort($extra_items,'sort_extra_defaults');
        return $extra_items;
    }
    public static function get_defaults($owner_table=false) {

        $defaults = array();
        $nextorder = array();
        if($owner_table&&strlen($owner_table)){
            $where = " WHERE e.owner_table = '".mysql_real_escape_string($owner_table)."' ";
            $defaults[$owner_table]=array();
            $nextorder[$owner_table]=0;
        }else{
            $where = '';
        }
        $sql = "SELECT `extra_default_id`,`extra_key`, `order`, `display_type`, `owner_table` FROM `"._DB_PREFIX."extra_default` e $where ORDER BY e.`order` ASC";
        foreach(qa($sql) as $r){
            if(!isset($defaults[$r['owner_table']])){
                $defaults[$r['owner_table']] = array();
            }
            if(!isset($nextorder[$r['owner_table']])){
                $nextorder[$r['owner_table']] = 0;
            }
            $defaults[$r['owner_table']][$r['extra_key']] = array(
                'key' => $r['extra_key'],
                'order'=>$r['order'],
                'extra_default_id' => $r['extra_default_id'],
                'display_type' => $r['display_type'],
            );
            $nextorder[$r['owner_table']] = max($r['order'],$nextorder[$r['owner_table']]);
        }
        // search database for keys.
        $sql = "SELECT `extra_key`,`owner_table` FROM `"._DB_PREFIX."extra` e $where GROUP BY e.extra_key";
        foreach(qa($sql) as $r){
            if(!isset($nextorder[$r['owner_table']])){
                $nextorder[$r['owner_table']] = 0;
            }
            if(!isset($defaults[$r['owner_table']]) || !isset($defaults[$r['owner_table']][$r['extra_key']])){
                $nextorder[$r['owner_table']]++;
                $extra_default_id = update_insert('extra_default_id',false,'extra_default',array(
                    'owner_table' => $r['owner_table'],
                    'extra_key' => $r['extra_key'],
                    'order' => $nextorder[$r['owner_table']],
                    'display_type' => 0,
                ));
                $defaults[$r['owner_table']][$r['extra_key']] = array();
                $defaults[$r['owner_table']][$r['extra_key']]['key'] = $r['extra_key'];
                $defaults[$r['owner_table']][$r['extra_key']]['order'] = $nextorder[$r['owner_table']];
                $defaults[$r['owner_table']][$r['extra_key']]['extra_default_id'] = $extra_default_id;
                $defaults[$r['owner_table']][$r['extra_key']]['display_type'] = 0;
                module_cache::clear_cache(false);
            }
            if(!isset($defaults[$r['owner_table']][$r['extra_key']]['order'])){
                $defaults[$r['owner_table']][$r['extra_key']]['order'] = 0;
            }
            /*$defaults[$r['owner_table']][$r['extra_key']] = array(
                'key' => $r['extra_key'],
                'order'=> isset($defaults[$r['extra_key']]) ? $defaults[$r['extra_key']]['order'] : 0,
            );*/
        }

        if($owner_table){
            uasort($defaults[$owner_table],'sort_extra_defaults');
            return $defaults[$owner_table];
        }else{
            return $defaults;//return all for settings area
        }

/*        switch($owner_table){
            case 'website':
                $defaults = array(
                    array('key' => 'FTP Username',),
                    array('key' => 'FTP Password',),
                    array('key' => 'FTP Provider',),
                    array('key' => 'Host Username',),
                    array('key' => 'Host Password',),
                    array('key' => 'Host Provider',),
                    array('key' => 'WordPress User',),
                    array('key' => 'WordPress Pass',),
                    array('key' => 'Analytics Account',),
                    array('key' => 'Webmaster Account',),
                );
                break;
        }*/
    }

    public static function get_extra_default($extra_default_id){
        $extra_default_id = (int)$extra_default_id;
        $extra_data_key = false;
        if($extra_default_id > 0){
            $extra_data_key = get_single('extra_default','extra_default_id',$extra_default_id);
        }
        if(!$extra_data_key){
            $extra_data_key = array(
                'extra_default_id' => '',
                'owner_table' => '',
                'extra_key' => '',
                'display_type' => '',
                'order' => '',
            );
        }
        return $extra_data_key;
    }


    public static function get_display_types() {
        return array(
            0 => 'Default',
            _EXTRA_DISPLAY_TYPE_COLUMN => 'Public + In Columns',
            //2 => 'Private By Permissions',
        );
    }

    static $column_headers = array();
    public static function print_table_header($owner_table,$options=array())
    {
        if(self::can_i('view','Extra Fields')){
            if(isset(self::$column_headers[$owner_table])){
                $column_headers = self::$column_headers[$owner_table];
            }else{
                $defaults = self::get_defaults($owner_table);
                $column_headers = array();
                foreach($defaults as $default){
                    if(isset($default['display_type']) && $default['display_type'] == _EXTRA_DISPLAY_TYPE_COLUMN){
                        $column_headers[$default['key']] = $default;
                    }
                }
                self::$column_headers[$owner_table] = $column_headers;
            }
            foreach($column_headers as $column_header){
                $this_options = array();
                if(isset($options[0])){
                    $this_options = $options[0];
                }
                if(isset($options[$column_header['key']])){
                    $this_options = $options[$column_header['key']];
                }
                ?>
                <th<?php echo isset($this_options['style']) ? ' style="'.$this_options['style'].'"' : '';?>>
                    <?php echo $column_header['key'];?>
                </th>
                <?php
            }
        }
    }
    public static function print_table_data($owner_table,$owner_id)
    {
        if(self::can_i('view','Extra Fields') && isset(self::$column_headers[$owner_table])){
            $extra_data = get_multiple('extra',array('owner_table'=>$owner_table,'owner_id'=>$owner_id),'extra_key');
            foreach(self::$column_headers[$owner_table] as $column_header){
                ?>
                <td>
                    <?php echo isset($extra_data[$column_header['key']]) ? htmlspecialchars($extra_data[$column_header['key']]['extra']) : '';?>
                </td>
                <?php
            }
        }
    }


    public function get_upgrade_sql(){
        $sql = '';
        if(!self::db_table_exists('extra')){
            $sql .= 'CREATE TABLE `'._DB_PREFIX.'extra` (
  `extra_id` int(11) NOT NULL AUTO_INCREMENT,
  `owner_id` int(11) NOT NULL,
  `owner_table` varchar(80) NOT NULL,
  `extra_key` varchar(100) NOT NULL,
  `extra` longtext NOT NULL,
  `date_created` datetime NOT NULL,
  `date_updated` datetime NULL,
  `create_user_id` int(11) NOT NULL,
  `update_user_id` int(11) NULL,
  `create_ip_address` varchar(15) NOT NULL,
  `update_ip_address` varchar(15) NULL,
  PRIMARY KEY (`extra_id`),
  KEY `owner_id` (`owner_id`),
  KEY `owner_table` (`owner_table`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;';
        }
        if(!self::db_table_exists('extra_default')){
            $sql .= 'CREATE TABLE `'._DB_PREFIX.'extra_default` (
  `extra_default_id` int(11) NOT NULL AUTO_INCREMENT,
  `owner_table` varchar(80) NOT NULL,
  `extra_key` varchar(100) NOT NULL,
  `order` int(11) NOT NULL DEFAULT \'0\',
  `display_type` tinyint(2) NOT NULL DEFAULT \'0\',
  `date_created` datetime NOT NULL,
  `date_updated` datetime NULL,
  `create_user_id` int(11) NOT NULL,
  `update_user_id` int(11) NULL,
  PRIMARY KEY (`extra_default_id`),
  KEY `owner_table` (`owner_table`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;';
        }else{
            $fields = get_fields('extra_default');
            if(!isset($fields['display_type'])){
                $sql .= 'ALTER TABLE  `'._DB_PREFIX.'extra_default` ADD  `display_type` tinyint(2) NOT NULL DEFAULT \'0\' AFTER `order`;';
            }
        }
        /*if(!self::db_table_exists('extra_key')){
            $sqlnow = 'CREATE TABLE `'._DB_PREFIX.'extra_key` (
  `extra_key_id` int(11) NOT NULL AUTO_INCREMENT,
  `owner_table` varchar(80) NOT NULL,
  `extra_key` varchar(100) NOT NULL,
  `date_created` datetime NOT NULL,
  `date_updated` datetime NULL,
  `create_user_id` int(11) NOT NULL,
  `update_user_id` int(11) NULL,
  `create_ip_address` varchar(15) NOT NULL,
  `update_ip_address` varchar(15) NULL,
  PRIMARY KEY (`extra_key_id`),
  KEY `owner_table` (`owner_table`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;';
            query($sqlnow);// so it's ready for below:
        }
        $fields = get_fields('extra');
        if(!isset($fields['extra_key_id'])){
            $sql_now = 'ALTER TABLE  `'._DB_PREFIX.'extra` ADD  `extra_key_id` int(11) NOT NULL DEFAULT \'0\' AFTER `extra_id`;';
            query($sql_now);
            $sql_now = 'ALTER TABLE  `'._DB_PREFIX.'extra` ADD INDEX (  `extra_key_id` )';
            query($sql_now);
            $sql_update = "SELECT * FROM `"._DB_PREFIX."extra` GROUP BY owner_table,extra_key";
            $existing_extras = qa($sql_update);
            if(class_exists('module_cache',false))module_cache::clear_cache();
            foreach($existing_extras as $existing_extra){
                $extra_key = trim($existing_extra['extra_key']);
                if(strlen($extra_key)){
                    // find if it exists.
                    $existing_in_db = get_single('extra_key','extra_key',$extra_key,true);
                    if(!$existing_in_db || !$existing_in_db['extra_key_id']){
                        // doesn't exists. woot.
                        $existing_in_db = array();
                        $existing_in_db['extra_key_id'] = update_insert('extra_key_id','new','extra_key',array('extra_key'=>$extra_key,'owner_table'=>$existing_extra['owner_table']));
                    }
                    if($existing_in_db['extra_key_id']){
                        $sql_update_keys = "UPDATE `"._DB_PREFIX."extra` SET `extra_key_id` = ".(int)$existing_in_db['extra_key_id']." WHERE extra_key = '".mysql_real_escape_string($extra_key)."' AND owner_table = '".mysql_real_escape_string($existing_extra['owner_table'])."'";
                        query($sql_update_keys);
                    }
                }
            }
        }*/
        return $sql;
    }

    public function get_install_sql(){
        return 'CREATE TABLE `'._DB_PREFIX.'extra` (
  `extra_id` int(11) NOT NULL AUTO_INCREMENT,
  `owner_id` int(11) NOT NULL,
  `owner_table` varchar(80) NOT NULL,
  `extra_key` varchar(100) NOT NULL,
  `extra` longtext NOT NULL,
  `date_created` datetime NOT NULL,
  `date_updated` datetime NULL,
  `create_user_id` int(11) NOT NULL,
  `update_user_id` int(11) NULL,
  `create_ip_address` varchar(15) NOT NULL,
  `update_ip_address` varchar(15) NULL,
  PRIMARY KEY (`extra_id`),
  KEY `owner_id` (`owner_id`),
  KEY `owner_table` (`owner_table`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;


CREATE TABLE `'._DB_PREFIX.'extra_default` (
  `extra_default_id` int(11) NOT NULL AUTO_INCREMENT,
  `owner_table` varchar(80) NOT NULL,
  `extra_key` varchar(100) NOT NULL,
  `order` int(11) NOT NULL DEFAULT \'0\',
  `display_type` tinyint(2) NOT NULL DEFAULT \'0\',
  `date_created` datetime NOT NULL,
  `date_updated` datetime NULL,
  `create_user_id` int(11) NOT NULL,
  `update_user_id` int(11) NULL,
  PRIMARY KEY (`extra_default_id`),
  KEY `owner_table` (`owner_table`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;


';

        //`extra_key_id` int(11) NOT NULL DEFAULT \'0\',
        /*
CREATE TABLE `'._DB_PREFIX.'extra_key` (
  `extra_key_id` int(11) NOT NULL AUTO_INCREMENT,
  `owner_table` varchar(80) NOT NULL,
  `extra_key` varchar(100) NOT NULL,
  `date_created` datetime NOT NULL,
  `date_updated` datetime NULL,
  `create_user_id` int(11) NOT NULL,
  `update_user_id` int(11) NULL,
  `create_ip_address` varchar(15) NOT NULL,
  `update_ip_address` varchar(15) NULL,
  PRIMARY KEY (`extra_key_id`),
  KEY `owner_table` (`owner_table`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;*/
    }

}