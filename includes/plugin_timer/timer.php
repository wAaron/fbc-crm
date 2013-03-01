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



class module_timer extends module_base{


    public $version = 2.11;
    //2.11 - first Task Timer version

    public static function can_i($actions,$name=false,$category=false,$module=false){
        if(!$module)$module=__CLASS__;
        return parent::can_i($actions,$name,$category,$module);
    }
	public static function get_class() {
        return __CLASS__;
    }
	public function init(){
		$this->module_name = "timer";
		$this->module_position = 0;

        if(module_security::is_logged_in() && module_config::c('timer_enabled',1) && self::can_i('view','Task Timer') && get_display_mode() != 'mobile'){

            module_config::register_css('timer','timer.css');
            module_config::register_js('timer','timer.js');
            hook_add('job_task_after','module_timer::hook_job_task_after');
        }
    }

    public function handle_hook($hook_name){
        if($hook_name=='top_menu_end' && module_config::c('timer_enabled',1) && module_security::is_logged_in() && self::can_i('view','Task Timer') && get_display_mode() != 'mobile'){

            ?>
            <li id="timer_menu_button">
                <div id="timer_menu_options">
                    <div class="timer_title">
                        <?php _e('Active Timers');?>
                    </div>
                    <ul id="active_timer_list">
                    </ul>
                </div>
                <a href="#" onclick="return false;" title="<?php _e('Timer');?>"><span><?php _e('Timers');?><span class="menu_label" id="current_timer_count">1</span></span></a>
            </li>
            <?php
        }
    }

    public static function hook_job_task_after($hook,$job_id,$task_id,$job_data,$task_data){
        ?>
            <a href="#" class="timer_task ui-state-default ui-corner-all ui-icon ui-icon-clock" data-jobid="<?php echo (int)$job_id;?>" data-taskid="<?php echo (int)$task_id;?>" data-completed="<?php echo (int)$task_data['fully_completed'];?>" <?php echo $task_data['fully_completed'] ? ' style="display:none;"':'';?> title="<?php _e('Task Timer');?>">Timer</a>
        <?php
    }
}