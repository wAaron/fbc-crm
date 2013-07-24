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

function config_sort_css($a,$b){
    return $a[3] > $b[3];
}
class module_config extends module_base{

    private static $config_vars = array();
    public static function can_i($actions,$name=false,$category=false,$module=false){
        if(!$module)$module=__CLASS__;
        return parent::can_i($actions,$name,$category,$module);
    }
	public static function get_class() {
        return __CLASS__;
    }
	public function init(){
		$this->module_name = "config";
		$this->module_position = 40;
        $this->version = 2.371;
        //2.371 - 2013-06-21 - different config vars per company
        //2.37 - 2013-04-30 - clearer upgrade instructions

        //2.31 - putting date_input to the general settings area
        //2.32 - friendly licence code names
        //2.33 - menu fix.
        //2.34 - js / css callbacks
        //2.35 - skipping custom files in the upgrade process
        //2.36 - permission fixes
        //2.361 - memory limit via config
        //2.362 - memory limit fix
        //2.363 - upload php limit fix
        //2.364 - php5/6 fix
        //2.365 - date format settings fix
        //2.366 - css/js updates
        //2.367 - css loading fix
        //2.368 - upgrade fixing
        //2.369 - click to edit config values

        // load some default configurations.
        if(!defined('_DATE_FORMAT')){
            define('_DATE_FORMAT',module_config::c('date_format','d/m/Y')); // todo: read from database
        }
        if(!defined('_DATE_INPUT')){
            // 1 = DD/MM/YYYY
            // 2 = YYYY/MM/DD
            // 3 = MM/DD/YYYY
            define('_DATE_INPUT',module_config::c('date_input','1')); 
        }
        if(!defined('_ERROR_EMAIL')){
            define('_ERROR_EMAIL',module_config::c('admin_email_address','info@'.$_SERVER['HTTP_HOST']));
        }

        date_default_timezone_set(module_config::c('timezone','America/New_York'));

        if(module_security::is_logged_in() && isset($_POST['_config_settings_hook']) && $_POST['_config_settings_hook'] == 'save_config'){
            $this->_handle_save_settings_hook();
        }

        // try to set our memory limit.
        $desired_limit_r = module_config::c('php_memory_limit','64M');
        $desired_limit = trim($desired_limit_r);
        $last = strtolower($desired_limit[strlen($desired_limit)-1]);
        switch($last) {
            // The 'G' modifier is available since PHP 5.1.0
            case 'g':
                $desired_limit *= 1024;
            case 'm':
                $desired_limit *= 1024;
            case 'k':
                $desired_limit *= 1024;
        }

        $memory_limit = ini_get('memory_limit');
        $val = trim($memory_limit);
        $last = strtolower($val[strlen($val)-1]);
        switch($last) {
            // The 'G' modifier is available since PHP 5.1.0
            case 'g':
                $val *= 1024;
            case 'm':
                $val *= 1024;
            case 'k':
                $val *= 1024;
        }


        if(!$memory_limit || $val < $desired_limit){
            // try to increase to 64M
            if(!_DEMO_MODE){
                @ini_set('memory_limit',$desired_limit_r);
            }
        }

/*
        // try to set our post_max_size limit.
        $desired_limit_r = module_config::c('php_post_max_size','10M');
        $desired_limit = trim($desired_limit_r);
        $last = strtolower($desired_limit[strlen($desired_limit)-1]);
        switch($last) {
            // The 'G' modifier is available since PHP 5.1.0
            case 'g':
                $desired_limit *= 1024;
            case 'm':
                $desired_limit *= 1024;
            case 'k':
                $desired_limit *= 1024;
        }

        $post_max_size_limit = ini_get('post_max_size');
        $val = trim($post_max_size_limit);
        $last = strtolower($val[strlen($val)-1]);
        switch($last) {
            // The 'G' modifier is available since PHP 5.1.0
            case 'g':
                $val *= 1024;
            case 'm':
                $val *= 1024;
            case 'k':
                $val *= 1024;
        }


        if(!$post_max_size_limit || $val < $desired_limit){
            // try to increase to 64M
            if(!_DEMO_MODE){
                @ini_set('post_max_size',$desired_limit_r);
            }
        }*/

	}

    public function pre_menu(){

        if($this->can_i('view','Settings')){
            $this->links[] = array(
                "name"=>"Settings",
                "p"=>"config_admin",
                "order"=>99,
            );
        }

    }


    public function handle_hook($hook,$mod=false){
        switch($hook){
			case "home_alerts":
				$alerts = array();
                // check if the cron job hasn'e run in a certian amount of time.

                if(module_config::can_i('view','Settings')){
                    $last_cron_run = module_config::c('cron_last_run',0);
                    if($last_cron_run < (time() - 86400)){
                        $alert_res = process_alert(date('Y-m-d'), _l('CRON Job Not Setup'));
                        if($alert_res){
                            $alert_res['link'] = $this->link_generate(false,array('page'=>'config_cron'));
                            $alert_res['name'] = _l('Has not run since: %s',($last_cron_run>0 ? print_date($last_cron_run) : _l('Never')));
                            $alerts[] = $alert_res;
                        }
                    }

                    // check our memory limit.
                    if(class_exists('module_pdf',false)){
                        $desired_limit_r = module_config::c('php_memory_limit','64M');
                        $desired_limit = trim($desired_limit_r);
                        $last = strtolower($desired_limit[strlen($desired_limit)-1]);
                        switch($last) {
                            // The 'G' modifier is available since PHP 5.1.0
                            case 'g':
                                $desired_limit *= 1024;
                            case 'm':
                                $desired_limit *= 1024;
                            case 'k':
                                $desired_limit *= 1024;
                        }

                        $memory_limit = ini_get('memory_limit');
                        $val = trim($memory_limit);
                        $last = strtolower($val[strlen($val)-1]);
                        switch($last) {
                            // The 'G' modifier is available since PHP 5.1.0
                            case 'g':
                                $val *= 1024;
                            case 'm':
                                $val *= 1024;
                            case 'k':
                                $val *= 1024;
                        }


                        if(!$memory_limit || $val < $desired_limit || $val < 67108864){
                            $alert_res = process_alert(date('Y-m-d'), _l('PDF Memory Limit Low'));
                            if($alert_res){
                                $alert_res['link'] = $this->link_generate(false,array('page'=>'config_settings'));
                                $alert_res['name'] = _l('php_memory_limit should be 64M or above: %s',$memory_limit);
                                $alerts[] = $alert_res;
                            }
                        }
                    }

                    /*$desired_limit_r = module_config::c('php_post_max_size','10M');
                    $desired_limit = trim($desired_limit_r);
                    $last = strtolower($desired_limit[strlen($desired_limit)-1]);
                    switch($last) {
                        // The 'G' modifier is available since PHP 5.1.0
                        case 'g':
                            $desired_limit *= 1024;
                        case 'm':
                            $desired_limit *= 1024;
                        case 'k':
                            $desired_limit *= 1024;
                    }

                    $memory_limit = ini_get('post_max_size');
                    $val = trim($memory_limit);
                    $last = strtolower($val[strlen($val)-1]);
                    switch($last) {
                        // The 'G' modifier is available since PHP 5.1.0
                        case 'g':
                            $val *= 1024;
                        case 'm':
                            $val *= 1024;
                        case 'k':
                            $val *= 1024;
                    }


                    if(!strlen($memory_limit) || $val < $desired_limit || $val < 10485760){
                        $alert_res = process_alert(date('Y-m-d'), _l('CSV Import Limit Too Low'));
                        if($alert_res){
                            $alert_res['link'] = $this->link_generate(false,array('page'=>'config_settings'));
                            $alert_res['name'] = _l('php_post_max_size should be %s or above: %s',$desired_limit_r. ' ('.$desired_limit.')',$memory_limit. " ($val)");
                            $alerts[] = $alert_res;
                        }
                    }*/
                }
				return $alerts;
				break;
        }
    }
    
    public function link_generate($config_id=false,$options=array(),$link_options=array()){
		
        // we accept link options from a bubbled link call.
        // so we have to prepent our options to the start of the link_options array incase
        // anything bubbled up to this method.
        // build our options into the $options variable and array_unshift this onto the link_options at the end.

        // we check if we're bubbling from a sub link, and find the item id from a sub link
        if($config_id === false && $link_options){
            $key = 'config_id';
            foreach($link_options as $link_option){
                if(isset($link_option['data']) && isset($link_option['data'][$key])){
                    ${$key} = $link_option['data'][$key];
                    break;
                }
            }
        }
        // grab the data for this particular link, so that any parent bubbled link_generate() methods
        // can access data from a sub item (eg: an id)
        $data = array();
        $options['data'] = $data;
        // what text should we display in this link?
        $options['text'] = (!isset($data['part_number'])||!trim($data['part_number'])) ? 'N/A' : $data['part_number'];
        // generate the arguments for this link
        $options['arguments'] = array(
            'config_id' => $config_id,
        );
        // generate the path (module & page) for this link
        $options['page'] = (isset($options['page'])) ? $options['page'] : 'config_admin';
        $options['module'] = $this->module_name;
        // append this to our link options array, which is eventually passed to the
        // global link generate function which takes all these arguments and builds a link out of them.

        // optionally bubble this link up to a parent link_generate() method, so we can nest modules easily
        // change this variable to the one we are going to bubble up to:
        $bubble_to_module = false;
		if(isset($options['bubble_to_module'])){
			$bubble_to_module = $options['bubble_to_module'];
		}
		
        array_unshift($link_options,$options);
        if($bubble_to_module){
            global $plugins;
            return $plugins[$bubble_to_module['module']]->link_generate(false,$bubble_to_module,$link_options);
        }else{
            // return the link as-is, no more bubbling or anything.
            // pass this off to the global link_generate() function
            return link_generate($link_options);
        }
    }
    
	
	public function process(){
		if('save_config' == $_REQUEST['_process']){
            $count = $this->handle_post_save_config();
            set_message($count.' configuration values saved successfully');
            redirect_browser($_SERVER['REQUEST_URI']);
        }else if('save_select_box_popup' == $_REQUEST['_process']){
			// INSECURE!!! oh well.
			list($db_table,$db_key,$db_val,$config) = explode("|",$_REQUEST['hash']);
			$config = unserialize(base64_decode($config));
			//print_r(array($db_table,$db_key,$db_val,$config));print_r($_POST);exit;
			foreach($_POST['data'] as $primary_key => $row_data){
				if(isset($row_data['_delete_key_']) && $row_data['_delete_key_']){
					// deleting an old one.
					$sql = "DELETE FROM `"._DB_PREFIX.mysql_real_escape_string($db_table)."`
						WHERE `".mysql_real_escape_string($db_key)."` = '".mysql_real_escape_string($primary_key)."' LIMIT 1";
					$res = query($sql);
				}else if($row_data){
					// adding / updating
					reset($row_data);
					$first = trim(current($row_data));
					if($first){
						// checking if there's data
						update_insert($db_key,$primary_key,$db_table,$row_data);
					}
				}
			}
			// update the parent ui select box with new select box.
			$hash = 'dynamic_' . md5($db_table.'|'.$db_key.'|'.$db_val.(isset($config['db_search'])?serialize($config['db_search']):''));
			$html = module_config::_get_db_select_box_html($config);
			$html = preg_replace('/\r|\n/',' ',$html);
			$html = addcslashes($html,"'");
			?>
			<script type="text/javascript">
				window.parent.set_html('<?php echo $hash;?>','<?php echo $html;?>');
			</script>
			<?php
			exit;
		}
	}

    public static function save_config($key,$val){

        if(_DEMO_MODE && isset(self::$config_vars[$key])){
            // dont save particular values
            switch($key){
                case 'system_base_dir':
                case 'system_base_href':
                case '_theme_theme_logo':
                    set_error('Changing some settings is disabled in DEMO mode.');
                    return $val;
            }
        }

        if(class_exists('module_company',false) && module_company::is_enabled()){
            // pass setting saving over to company module for now
            // if company module returns true we don't save it below
            if(module_company::save_company_config($key,$val)){
                // saved in company module, don't save in defaults below
                self::$config_vars[$key] = $val;
                return true;
            }
        }

        $sql = "SELECT * FROM `"._DB_PREFIX."config` c ";
        $sql .= " WHERE `key` = '".mysql_real_escape_string($key)."'";
        $res = qa1($sql);
        if(!$res){
            $sql = "INSERT INTO `"._DB_PREFIX."config` SET `key` = '".mysql_real_escape_string($key)."', `val` = '".mysql_real_escape_string($val)."'";
            query($sql);
        }else{
            $sql = "UPDATE `"._DB_PREFIX."config` SET `val` = '".mysql_real_escape_string($val)."' WHERE `key` = '".mysql_real_escape_string($key)."' LIMIT 1";
            query($sql);
        }
        self::$config_vars[$key] = $val;
    }

    public function handle_post_save_config(){

        if(!module_config::can_i('edit','Settings')){
            die("Permission denied to Edit 'Config &raquo; Settings'. Please ask Administrator to adjust settings.");
        }
        $x=0;

        if(isset($_POST['config']) && is_array($_POST['config'])){
			foreach($_POST['config'] as $key=>$val){
				$this->save_config($key,$val);
                $x++;
			}
		}
        return $x;
    }

	public static function get_setting($key) {
		$val = get_single('config','key',$key);
		return (isset($val['val'])) ? $val['val'] : false;
	}


	public static function _get_db_select_box_html($options){
		// build up a search option
		if(isset($options['db_sql'])){
			$all_data = qa($options['db_sql']);
			$data = array();
			$val = $options['db_val'];
			if(strpos($val,'{') === false){
				$val = '{'.$val.'}';
			}
			foreach($all_data as $d){
				$dbval = $val;
				if(preg_match_all('/\{([^\}]+)\}/',$val,$matches)){
					foreach($matches[0] as $k => $v){
						$dbval = str_replace($v,$d[$matches[1][$k]],$dbval);
					}
				}
				$data[$d[$options['db_key']]] = $dbval;
			}
        }else if(isset($options['db_search'])){
			$all_data = get_multiple($options['db_table'],$options['db_search'],$options['db_key'],'exact',$options['db_order']);
			$data = array();
			$val = $options['db_val'];
			if(strpos($val,'{') === false){
				$val = '{'.$val.'}';
			}
			foreach($all_data as $d){
				$dbval = $val;
				if(preg_match_all('/\{([^\}]+)\}/',$val,$matches)){
					foreach($matches[0] as $k => $v){
						$dbval = str_replace($v,$d[$matches[1][$k]],$dbval);
					}
				}
				$data[$d[$options['db_key']]] = $dbval;
			}
		}else{
			$data = get_col_vals($options['db_table'],$options['db_key'],$options['db_val'],$options['db_order']);
		}
		$cur = isset($options['val']) ? $options['val'] : false;
		$sel = '<select
				name="'.$options['name'].'"
				id="'.((isset($options['id'])) ? $options['id'] : $options['name']).'"
				class="'.((isset($options['class'])) ? $options['class'] : '').'">';

		if(!isset($options['blank']) || $options['blank'] != false){
			$sel .= '<option value="">'. ((!isset($options['blank'])||$options['blank']===true) ? ' - Select - ' : $options['blank']) .'</option>';
		}
		$found_selected = false;
		$current_val = '';
		foreach($data as $key => $val){
			$sel .= '<option value="'.$key.'"';
			if(is_array($val)){
				$array_id = key($val);
				/*if(!$array_id){
					if(isset($val[$id]))$array_id = $id;
					else $array_id = key($val);
				}*/
				$printval = $val[$array_id];
			}else{
				$printval = $val;
			}
			if($key == $cur){
				$current_val = $printval;
				$sel .= ' selected';
				$found_selected = true;
			}
			$sel .= '>'.$printval.'</option>';
		}
		/*if($cur && !$found_selected){
			$sel .= '<option value="'.$cur.'" selected>'.$cur.'</option>';
		}*/
		$sel .= '</select>';
		if(isset($options['read_only']) && $options['read_only']){
			$sel = $current_val;
		}
		return $sel;
	}
	public static function print_db_select_box($options){
        global $plugins;
		static $printed_dialog_code = false;
		$sel = '';
		if(isset($options['allow_new']) && $options['allow_new'] != false){
			// outer span so that ajax can update this with newwer upon save.
			$sel .= '<span class="dynamic_'.md5($options['db_table'].'|'.$options['db_key'].'|'.$options['db_val'].(isset($options['db_search'])?serialize($options['db_search']):'')) . '">';
			$sel .= self::_get_db_select_box_html($options);
			$sel .= '</span>';
			ob_start();
			?>
			<a href="#" class="edit_select_box" rel="<?php echo $options['db_table'].'|'.$options['db_key'].'|'.$options['db_val'];?>|<?php
				echo base64_encode(serialize($options));?>">edit</a>
			<?php if(!$printed_dialog_code){ ?>
				<div id="edit_select_popup" title="Edit select box">
					<div class="modal_inner"></div>
				</div>
			<?php } ?>

			<script type="text/javascript">
				var edit_select_hash = '';
				<?php if(!$printed_dialog_code){ ?>
				function set_html(hash,html){
					$('.'+hash).html(html);
					$('#edit_select_popup').dialog('close');
				}
				<?php } ?>
				$(function(){
					<?php if(!$printed_dialog_code){ ?>
					$("#edit_select_popup").dialog({
						autoOpen: false,
						width: 600,
						height: 300,
						modal: true,
						buttons: {
							'Save': function() {
								$('form',this)[0].submit();
							},
							Cancel: function() {
								$(this).dialog('close');
							}
						},
						open: function(){
							var t = this;
							$.ajax({
								type: "GET",
								url: '<?php echo $plugins['config']->link('select_box_ui',array(
                                    'foo' => 'bar',
                                ));?>&hash='+edit_select_hash, 
                                //'?m=config&p=select_box_ui&hash='+edit_select_hash,
								dataType: "html",
								success: function(d){
									$('.modal_inner',t).html(d);
								}
							});
						},
						close: function() {
							$('.modal_inner',this).html('');
						}
					});
					<?php } ?>

					$('.edit_select_box').each(function(){
						if(!$(this).hasClass('edit_select_box_done')){
							// because this could have been run multiple times.
							$(this).addClass('edit_select_box_done');
							$(this).click(function(){
								// open popup to edit this hash
								edit_select_hash = $(this).attr('rel');
								$('#edit_select_popup').dialog('open');
								return false;
							});
						}
					});
				});
			</script>
			<?php
			$printed_dialog_code = true;
			$sel .= ob_get_clean();
			return $sel;
		}else{
			return self::_get_db_select_box_html($options);
		}
	}

    private static function _init_vars(){
        if(self::$config_vars)return;
        self::$config_vars = array();
        $sql = "SELECT `key`,`val` FROM `"._DB_PREFIX."config` ";
        foreach(qa($sql) as $c){
            self::$config_vars[$c['key']] = $c['val'];
        }
        if(function_exists('hook_handle_callback')){
            // hook into the company module (or any other modules in the future) to modify this if needed
            $new_configs = hook_handle_callback('config_init_vars',self::$config_vars);
            // returns a list of new configs from other modules
            if(is_array($new_configs)){
                foreach($new_configs as $new_config){
                    if(is_array($new_config)){
                        self::$config_vars = array_merge(self::$config_vars,$new_config);
                    }
                }
            }
        }
    }

    /**
     * @static returns a setting from the database.
     * @param  $key
     * @param bool $default
     * @return mixed|string
     */
    public static function c($key,$default=false){

        // check config table exists.
        if(!_UCM_INSTALLED){
            if(_DB_USER&&_DB_NAME){
                db_connect();
                $sql = "SHOW TABLES LIKE '"._DB_PREFIX."config'";
                $res = qa1($sql);
            }else{
                $res = array();
            }
            if($res!=false && count($res)){
                // config table exists, we're right to query
            }else{
                return $default;
            }
        }
        // load all vars if needed.
        self::_init_vars();

        if(!isset(self::$config_vars[$key]) && $default!==false){
            self::save_config($key,$default);
            /*$sql = "INSERT INTO `"._DB_PREFIX."config` SET `key` = '".mysql_real_escape_string($key)."', `val` = '".mysql_real_escape_string($default)."'";
            query($sql);
            self::$config_vars[$key] = $default;*/
        }
        return isset(self::$config_vars[$key]) ? self::$config_vars[$key] : false;
    }

    /**
     * @static Returns a translated string from a database call
     * @param  $key
     * @param bool $default
     * @return mixed|string
     */
    public static function s($key,$default=false){
        return _l(self::c($key,$default));
    }

    private static $css_files=array();
    public static function register_css($module, $file_name, $url=true, $position=10) {
        self::$css_files[$module.$file_name] = array($module, $file_name, $url, $position);
    }
    public static function print_css($version=false) {
        // sort the css files by position
        uasort(self::$css_files,'config_sort_css');
        foreach(self::$css_files as $hash=>$css_file_info){
            if(strlen($css_file_info[2])<3){ // url is set to 'true', use the module/file name combo  ?>
                <link rel="stylesheet" href="<?php echo _BASE_HREF;?>includes/plugin_<?php echo $css_file_info[0];?>/css/<?php echo $css_file_info[1]; echo ($version && strpos('?',$css_file_info[1])===false) ? '?ver='.$version : ''; ?>" type="text/css"> <?php
            }else{ ?>
                <link rel="stylesheet" href="<?php echo htmlspecialchars($css_file_info[2]);?>" type="text/css"> <?php
            }
        }
        if(function_exists('hook_handle_callback')){
            hook_handle_callback('header_print_css');
        }
    }
    private static $js_files=array();
    public static function register_js($module, $file_name, $url=true) {
        self::$js_files[$module][$file_name] = $url;
    }
    public static function print_js($version=false) {
        foreach(self::$js_files as $module=>$file_names){
            foreach($file_names as $file_name => $url){
                if($url === true){
                ?> <script type="text/javascript" language="javascript" src="<?php echo _BASE_HREF;?>includes/plugin_<?php echo $module;?>/js/<?php echo $file_name; echo ($version && strpos('?',$file_name)===false) ? '?ver='.$version : '';?>"></script> <?php
                }else{
                ?>
                <script type="text/javascript" language="javascript" src="<?php echo htmlspecialchars($url);?>"></script> <?php
                }
            }
        }
        if(function_exists('hook_handle_callback')){
            hook_handle_callback('header_print_js');
        }
    }

    public static function print_settings_form($settings){
        include('pages/settings_form.php');
    }
    private function _handle_save_settings_hook(){


        if(!module_config::can_i('edit','Settings')){
            die("Permission denied to Edit 'Config &raquo; Settings'. Please ask Administrator to adjust settings.");
        }

        $config = isset($_REQUEST['config']) && is_array($_REQUEST['config']) ? $_REQUEST['config'] : array();
        $config_defaults = isset($_REQUEST['default_config']) && is_array($_REQUEST['default_config']) ? $_REQUEST['default_config'] : array();
        foreach($config_defaults as $key=>$val){
            if(!isset($config[$key])){
                $config[$key] = ''; // the checkbox has been unticked, save a blank option.
            }
        }
        foreach($config as $key=>$val){
            $this->save_config($key,$val);
        }
        set_message('Configuration saved successfully');
        redirect_browser($_SERVER['REQUEST_URI']);
    }

    public function get_install_sql(){
        ob_start();
        ?>
        CREATE TABLE  `<?php echo _DB_PREFIX;?>config` (
        `key` VARCHAR( 255 ) NOT NULL ,
        `val` TEXT NOT NULL ,
        PRIMARY KEY (  `key` )
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        <?php
        return ob_get_clean();
    }

    public static function get_currency($currency_id) {
         return get_single('currency','currency_id',$currency_id);
    }

    public static function download_update($update_name) {

        $update = self::check_for_upgrades($update_name,true);

        return $update;

    }
    public static function check_for_upgrades($requested_plugin='',$get_file_contents=0) {

        // compile a list of current plugins
        // along with the users installation code
        // send it to our server and get a response with a list of available updates for this user.

        $current_plugins = array();
        $current_files = array();
        global $plugins;
        foreach($plugins as $plugin_name => &$p){
            $current_plugins[$plugin_name] = $p->get_plugin_version();
            // find all the files related to this plugin.
            if(function_exists('getFilesFromDir') && module_config::c('upgrade_post_file_list',1)){
                $directory = 'includes/plugin_'.$plugin_name.'/';
                $files = getFilesFromDir($directory);
                $files = array_flip($files);
                foreach($files as $file=>$tf){
                    // ignore certain files.
                    if(
                        strpos($file,'plugin_file/upload')!==false ||
                        strpos($file,'/cache/')!==false ||
                        strpos($file,'/html2ps/')!==false ||
                        strpos($file,'/tmp/')!==false
                        ){
                        unset($files[$file]);
                    }else{
                        $d = preg_replace('#Envato:[^\r\n]*#','',preg_replace('#Package Date:[^\r\n]*#','',preg_replace('#IP Address:[^\r\n]*#','',preg_replace('#Licence:[^\r\n]*#','',file_get_contents($file)))));
                        $files[$file] = md5(base64_encode($d));
                    }
                }
                $current_files[$plugin_name] = $files;
            }
        }
        //print_r($current_files);exit;

        $available_updates= array();

        $post_fields = array(
            'application' => _APPLICATION_ID,
            'installation_code' => module_config::c('_installation_code'),
            'current_version' => module_config::c('_admin_system_version',2.1),
            'current_plugins' => json_encode($current_plugins),
            'current_files' => json_encode($current_files),
            'client_ip' => $_SERVER['REMOTE_ADDR'],
            'installation_location' => full_link('/'),
            'requested_plugin' => $requested_plugin,
            'get_file_contents' => $get_file_contents,
        );
        $url = module_config::c('ucm_upgrade_url','http://ultimateclientmanager.com/api/upgrade.php');
        if(_DEMO_MODE && $url != 'http://ultimateclientmanager.com/api/upgrade.php'){
            exit;
        }
        if(!function_exists('curl_init')){
            $postdata = http_build_query(
                $post_fields
            );
            $opts = array('http' =>
                array(
                    'method'  => 'POST',
                    'header'  => 'Content-type: application/x-www-form-urlencoded',
                    'content' => $postdata
                )
            );
            $context  = stream_context_create($opts);
            $result = file_get_contents($url, false, $context);
        }else{
            //$url = 'http://localhost/ucm/web/api/upgrade.php';
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
            curl_setopt($ch, CURLOPT_HEADER,false);
            curl_setopt($ch, CURLOPT_POST,true);
            curl_setopt($ch, CURLOPT_POSTFIELDS,$post_fields);
            curl_setopt($ch,CURLOPT_HTTP_VERSION,CURL_HTTP_VERSION_1_0); // fixes netregistr, may break others?
            $result = curl_exec($ch);
        }
        $data = json_decode($result,true);

        if($data && isset($data['available_updates']) && is_array($data['available_updates'])){
            $available_updates = $data['available_updates'];
        }
        if($data && isset($data['licence_codes']) && is_array($data['licence_codes'])){
            // find out what the licence codes  are (url / name) so we can dispaly this under each code nicely.
            foreach($data['licence_codes'] as $code => $foo){
                if(strlen($code)>10 && strlen($foo)>10){
                    module_config::save_config('_licence_code_'.$code,$foo); // this might not be working
                }
            }
        }

        if(!$data){
            echo $result;
        }
        //echo '<pre>';print_r($current_plugins);print_r($result);echo '</pre>';

        return $available_updates;

    }


    public static function current_version() {
        return self::c('_admin_system_version',2.1);
    }
    public static function set_system_version($version) {
        return self::save_config('_admin_system_version',$version);
    }



}