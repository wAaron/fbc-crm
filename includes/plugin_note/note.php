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

function sort_notes($a,$b){
    return $a['note_time'] < $b['note_time'];
}

class module_note extends module_base{
	
	var $links;
    public $version = 2.242;
    // 2.23 - note delete
    // 2.231 - note delete bug fix
    // 2.232 - regenerate note links based on owner table/id rather than using rel_data for home page alerts.
    // 2.233 - bug fix for "reminder" notes applied to customer contacts.
    // 2.234 - php5/6 fix
    // 2.235 - new dashboard layout!.
    // 2.236 - speed improvements
    // 2.237 - some layout fixes
    // 2.238 - support for public invoice notes
    // 2.239 - bug fix
    // 2.24 - note 'public' bug fix
    // 2.241 - view permission fix to see full notes
    // 2.242 - javascript fix

    public static function can_i($actions,$name=false,$category=false,$module=false){
        if(!$module)$module=__CLASS__;
        return parent::can_i($actions,$name,$category,$module);
    }
	public static function get_class() {
        return __CLASS__;
    }
	function init(){
		$this->links = array();
		$this->user_types = array();
		$this->module_name = "note";
		$this->module_position = 8882;
        module_config::register_css('note','notes.css');
		
	}
	
	
	function handle_hook($hook,$calling_module=false,$owner_table=false,$key_name=false,$key_value=false,$rel_data=false){
		switch($hook){
			case "home_alerts":
				$alerts = array();
                if(module_config::c('allow_note_reminders',1)){
                    // find any jobs that are past the due date and dont have a finished date.
                    $sql = "SELECT * FROM `"._DB_PREFIX."note` n ";
                    $sql .= " WHERE n.`reminder` = 1 AND n.note_time < ".(int)strtotime('+'.module_config::c('alert_days_in_future',5).' days')."";
                    $sql .= " AND ( n.`user_id` = 0 OR n.`user_id` = ".module_security::get_loggedin_id().")";
                    $tasks = qa($sql);
                    foreach($tasks as $task){
                        switch($task['owner_table']){
                            case 'invoice':
                                $invoice_data = module_invoice::get_invoice($task['owner_id'],true);
                                if(!$invoice_data)continue;
                                break;
                        }
                        $alert_res = process_alert(date('Y-m-d',$task['note_time']), _l('Note Reminder'));
                        if($alert_res){
                            $alert_res['link'] = $task['rel_data'];
                            // fix for linking when changing folder.
                            switch($task['owner_table']){
                                case 'user':
                                    $user = module_user::get_user($task['owner_id']);
                                    if($user['customer_id']){
                                        $alert_res['link'] = module_user::link_open_contact($task['owner_id']);
                                    }else{
                                        $alert_res['link'] = module_user::link_open($task['owner_id']);
                                    }
                                    break;
                                case 'website':
                                    $alert_res['link'] = module_website::link_open($task['owner_id']);
                                    break;
                                case 'customer':
                                    $alert_res['link'] = module_customer::link_open($task['owner_id']);
                                    break;
                                case 'job';
                                    $alert_res['link'] = module_job::link_open($task['owner_id']);
                                    break;
                                // todo - add others.
                            }
                            $alert_res['name'] = $task['note'];
                            $alerts[] = $alert_res;
                        }
                    }
				}
				return $alerts;
				break;
			/*case "note_list":
				if($owner_id && $owner_id != 'new'){

					$note_items = $this->get_notes(array("owner_table"=>$owner_table,"owner_id"=>$owner_id));
					foreach($note_items as &$note_item){
						// do it in loop here because of $this issues in static method below.
						// instead of include file below.
						$note_item['html'] = $this->print_note($note_item['note_id']);
					}
					include("pages/note_list.php");
				}else{
					echo 'Please save first before creating notes.';
				}
				break;*/
			case "note_delete":
                // find the key we are saving this address against.
                $owner_id = (int)$key_value;
                if(!$owner_id || $owner_id == 'new'){
                    // find one in the post data.
                    if(isset($_REQUEST[$key_name])){
                        $owner_id = $_REQUEST[$key_name];
                    }
                }
                $note_hash = md5($owner_id.'|'.$owner_table); // just for posting unique arrays.
				if($owner_table && $owner_id){
					$this->note_delete($owner_table,$owner_id);
				}
				break;
			
		}
	}

    public static function note_delete($owner_table,$owner_id,$note_id=false){
        $sql = "DELETE FROM `"._DB_PREFIX."note` WHERE owner_table = '".mysql_real_escape_string($owner_table)."' AND owner_id = '".mysql_real_escape_string($owner_id)."'";
        if($note_id){
            $sql .= " AND note_id = ".(int)$note_id;
        }
        query($sql);
    }

	public static function display_notes($options){

        $owner_table = (isset($options['owner_table']) && $options['owner_table']) ? $options['owner_table'] : false;
        global $plugins;

        // permission checking.
        $can_create = $can_edit = $can_view = $can_delete = true;
        if($options && isset($options['bypass_security'])){
            // do nothing?
        }else if(isset($options) && isset($options['owner_table']) && $options['owner_table'] && isset($options['title']) && $options['title']){
            $can_view = $plugins[$options['owner_table']]->can_i('view',$options['title']);
            $can_edit = $plugins[$options['owner_table']]->can_i('edit',$options['title']);
            $can_create = $plugins[$options['owner_table']]->can_i('create',$options['title']);
            $can_delete = $plugins[$options['owner_table']]->can_i('delete',$options['title']);
        }
        if(!module_security::is_page_editable()){
            $can_edit=$can_create=$can_delete=false;
        }
        if(!$can_view)return '';
        // display links in a popup?
        $popup_links = get_display_mode() != 'mobile'; // disable popups in mobile version.

		$owner_id = (isset($options['owner_id']) && $options['owner_id']) ? (int)$options['owner_id'] : false;

		if($owner_id && $owner_table){
			// we have all that we need to display some notes!! yey!!
			// do we display a summary or not?

			$note_items = self::get_notes(array('owner_table'=>$owner_table,'owner_id'=>$owner_id));
			$display_summary = (isset($options['display_summary']) && $options['display_summary']);
			if(isset($options['summary_owners']) && is_array($options['summary_owners'])){
				// generate a list of other notes we have to display int eh list.
				foreach($options['summary_owners'] as $summary_owner_table => $summary_owner_ids){
					if(is_array($summary_owner_ids)){
                        //$sql = "SELECT *, note_id AS id FROM `"._DB_PREFIX."note` n WHERE owner_table = '".mysql_real_escape_string($summary_owner_table)."' AND ( ";
                        $sql = "SELECT *, note_id AS id FROM `"._DB_PREFIX."note` n WHERE n.`owner_table` = '".mysql_real_escape_string($summary_owner_table)."' AND n.`owner_id` IN ( ";
						/*foreach($summary_owner_ids as $summary_owner_id){
							//$note_items = array_merge($note_items,self::get_notes(array('owner_table'=>$summary_owner_table,'owner_id'=>$summary_owner_id)));
                            $sql .= " `owner_id` = '".(int)$summary_owner_id."' OR ";
						}
                        $sql = rtrim($sql,'OR ');*/
                        foreach($summary_owner_ids as $k=>$summary_owner_id){
                            $summary_owner_ids[$k] = (int)$summary_owner_id;
                        }
                        $sql .= implode(',',$summary_owner_ids);
                        $sql .= " )";
                        $note_items = array_merge($note_items,qa($sql));
					}
				}
			}
            // moved to 'note_list.php'
			/*foreach($note_items as $key=>$note_item){
				$note_items[$key]['html'] = self::print_note($note_item['note_id'],$note_item,$display_summary,$can_edit,$can_delete,$options);
			}*/
            uasort($note_items,'sort_notes');
			if(isset($options['view_link'])){
				$rel_data = $options['view_link'];
			}
            $note_list_safe = true;
			include("pages/note_list.php");
		}
	}
    
    public static function link_generate($note_id=false,$options=array(),$link_options=array()){

        $key = 'note_id';
        if($note_id === false && $link_options){
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
        $options['page'] = 'note_admin';
        if(!isset($options['arguments'])){
            $options['arguments'] = array();
        }
        $options['arguments']['note_id'] = $note_id;
        $options['module'] = 'note';
        $data = array(); //self::get_note($note_id);
        $options['data'] = $data;
        // what text should we display in this link?
        $options['text'] = 'note'; //(!isset($data['name'])||!trim($data['name'])) ? 'N/A' : $data['name'];
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

	public static function link_open($note_id,$full=false,$options=array()){
        return self::link_generate($note_id,array('full'=>$full,'arguments'=>array(
                                               'options'=>base64_encode(serialize($options)),
                                           )));
    }

	public static function print_note($note_id,$note_item,$display_summary=false,$can_edit=true,$can_delete=true,$options=array()){
		if(!$note_item)$note_item = self::get_note($note_id);
		static $x = 0;


        global $plugins;
        $can_view = $can_edit = $can_create = $can_delete = false;
        // re-check permissions...
        if(isset($options) && isset($options['owner_table']) && $options['owner_table'] && isset($options['title']) && $options['title']){
            $can_view = $plugins[$options['owner_table']]->can_i('view',$options['title']);
            $can_edit = $plugins[$options['owner_table']]->can_i('edit',$options['title']);
            $can_create = $plugins[$options['owner_table']]->can_i('create',$options['title']);
            $can_delete = $plugins[$options['owner_table']]->can_i('delete',$options['title']);
        }else{

        }

        if(!module_security::is_page_editable()){
            $can_edit=$can_create=$can_delete=false;
        }
        
        //
        if(!trim($note_item['note']))$note_item['note'] = 'none';


		ob_start();
		?>
		<tr id="note_<?php echo $note_item['note_id'];?>" class="<?php echo ($x++%2)?'odd':'even';?>">
			<td>
                <?php
                if($note_item['reminder'])echo '<strong>';
                echo print_date($note_item['note_time']);
                if($note_item['reminder'])echo '</strong>';
                ?>
			</td>
			<td>
                <?php
                if(isset($note_item['public']) && $note_item['public'])echo '* ';
                if($can_edit){
                    $note_text = nl2br(htmlspecialchars(substr($note_item['note'],0,module_config::c('note_trim_length',35))));
                    $note_text .= strlen($note_item['note']) > module_config::c('note_trim_length',35) ? '...' : '';
                    ?>
                <a href="<?php echo self::link_open($note_item['note_id'],false,$options);?>" class="note_edit" rel="<?php echo $note_item['note_id'];?>"> <?php echo $note_text; ?> </a>
                <?php }else{
                    echo forum_text($note_item['note']);
                } ?>
			</td>
			<td nowrap="nowrap">
				<?php
				if($display_summary){
					if($note_item['rel_data']){
                        echo $plugins[$note_item['owner_table']]->link_open($note_item['owner_id'],true);
					}
				}else{
					// find the user name who made thsi note.
					$user_data = module_user::get_user($note_item['create_user_id']);
					echo $user_data['name'];
				}
				?>
			</td>
            <?php if($can_delete){ ?>
            <td><a href="<?php echo self::link_open($note_item['note_id'],false,array_merge($options,array('do_delete'=>'yes','note_id'=>$note_item['note_id'])));?>" rel="<?php echo $note_item['note_id'];?>" onclick="if(confirm('<?php _e('Really Delete Note?');?>'))return true; else return false;" class="note_delete delete ui-state-default ui-corner-all ui-icon ui-icon-trash">[x]</a></td>
            <?php } ?>
		</tr>
		<?php
		return ob_get_clean();
	}
	function process(){
		if('save_note' == $_REQUEST['_process']){
			$note_id = $_REQUEST['note_id'];
            $options = unserialize(base64_decode($_REQUEST['options']));

            if(!$options)return;
			if(!$note_id || $note_id == 'new'){
				$note_data = array(
					'note_id' => $note_id,
					'owner_id' => $options['owner_id'],
					'owner_table' => $options['owner_table'],
					'note_time' => strtotime(input_date(urldecode($_REQUEST['note_time']),true)),
					'note' => urldecode($_REQUEST['note']),
					'rel_data' => isset($_REQUEST['rel_data']) ? $_REQUEST['rel_data'] : '',
					'reminder' => isset($_REQUEST['reminder']) ? $_REQUEST['reminder'] : 0,
					'user_id' => isset($_REQUEST['user_id']) ? $_REQUEST['user_id'] : 0,
                );
			}else{
				// some fields we dont want to overwrite on existing notes:
				$note_data = array(
					'note_id' => $note_id,
					'note_time' => strtotime(input_date(urldecode($_REQUEST['note_time']),true)),
					'note' => urldecode($_REQUEST['note']),
					'reminder' => isset($_REQUEST['reminder']) ? $_REQUEST['reminder'] : 0,
					'user_id' => isset($_REQUEST['user_id']) ? $_REQUEST['user_id'] : 0,
				);
			}
            if(isset($_REQUEST['public_chk']) && $_REQUEST['public_chk']){
                $note_data['public'] = isset($_REQUEST['public']) ? $_REQUEST['public'] : 0;
            }
			// TODO - sanatise this note data with security module.
			// make sure we're saving a note we have access too.
			//module_security::sanatise_data('note',$note_data);
            // sanatise broke our update code.
            $note_id = update_insert('note_id',$note_id,'note',$note_data);
            if(isset($_REQUEST['from_normal'])){
                set_message('Note saved successfully');
                redirect_browser($this->link_open($note_id,false,$options));
            }
			echo $this->print_note($note_id,false,(isset($options['display_summary']) && $options['display_summary']),false,false,$options);
			exit;
		}
	}

	public static function save_note($data=array()){
		//$this->note_id = isset($this->note_id) ? (int)$this->note_id : false;
		$note_id = update_insert('note_id','new','note',$data);
        return $note_id;
	}


	function delete($note_id){
		$note_id=(int)$note_id;
		$sql = "DELETE FROM "._DB_PREFIX."note WHERE note_id = '".$note_id."' LIMIT 1";
		query($sql);
	}

	public static function get_note($note_id){
		$note = get_single("note","note_id",$note_id);
		if($note){
			// optional processing here later on.
			
		}
		return $note;
	}

	public static function get_notes($search=false){
		return get_multiple("note",$search,"note_id","exact","note_id");
	}



    public function get_install_sql(){
        return 'CREATE TABLE `'._DB_PREFIX.'note` (
  `note_id` int(11) NOT NULL AUTO_INCREMENT,
  `owner_id` int(11) NOT NULL,
  `owner_table` varchar(80) NOT NULL,
  `note` text NOT NULL,
  `note_time` int(11) NOT NULL,
  `rel_data` text NULL,
  `reminder` TINYINT( 1 ) NOT NULL DEFAULT  \'0\',
  `user_id` int(11) NOT NULL DEFAULT  \'0\',
  `public` TINYINT( 1 ) NOT NULL DEFAULT  \'0\',
  `date_created` datetime NOT NULL,
  `date_updated` datetime NULL,
  `create_user_id` int(11) NOT NULL,
  `update_user_id` int(11) NULL,
  `create_ip_address` varchar(15) NOT NULL,
  `update_ip_address` varchar(15) NULL,
  PRIMARY KEY (`note_id`),
  KEY `owner_id` (`owner_id`),
  KEY `owner_table` (`owner_table`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;';
    }

    public function get_upgrade_sql(){
        $sql = '';
        /*$installed_version = (string)$installed_version;
        $new_version = (string)$new_version;*/
        /*$options = array(
            '2.1' => array(
                '2.2' => 'ALTER TABLE  `'._DB_PREFIX.'note` ADD `user_id` INT( 11 ) NOT NULL DEFAULT \'0\' AFTER `rel_data`;',
            ),
        );
        if(isset($options[$installed_version]) && isset($options[$installed_version][$new_version])){
            $sql = $options[$installed_version][$new_version];
        }*/
        $fields = get_fields('note');
        if(!isset($fields['reminder'])){
            $sql .= 'ALTER TABLE  `'._DB_PREFIX.'note` ADD `reminder` INT( 11 ) NOT NULL DEFAULT \'0\' AFTER `rel_data`;';
        }
        if(!isset($fields['user_id'])){
            $sql .= 'ALTER TABLE  `'._DB_PREFIX.'note` ADD `user_id` INT( 11 ) NOT NULL DEFAULT \'0\' AFTER `rel_data`;';
        }
        if(!isset($fields['public'])){
            $sql .= 'ALTER TABLE  `'._DB_PREFIX.'note` ADD `public` TINYINT( 1 ) NOT NULL DEFAULT \'0\' AFTER `user_id`;';
        }
        return $sql;
    }
}