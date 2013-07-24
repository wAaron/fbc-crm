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



class module_dashboard extends module_base{


    public static function can_i($actions,$name=false,$category=false,$module=false){
        if(!$module)$module=__CLASS__;
        return parent::can_i($actions,$name,$category,$module);
    }
	public static function get_class() {
        return __CLASS__;
    }

	public function init(){
		$this->module_name = "dashboard";
		$this->module_position = 0;


        $this->version = 2.12;
        //2.12 - 2013-05-27 - dashboard alert improvements
        //2.11 - 2013-04-11 - initial release

	}

    public static $group_settings=array();
    public static function register_group($group_name,$settings){
        self::$group_settings[$group_name] = $settings;
    }

    public static function get_dashboard_alerts(){
        $dashboard_alerts = array();

        if(module_security::can_user(module_security::get_loggedin_id(),'Show Dashboard Alerts')){
            $results = handle_hook("home_alerts");
            if (is_array($results)) {
                $alerts = array();
                foreach ($results as $res) {
                    if (is_array($res)) {
                        foreach ($res as $r) {
                            $alerts[] = $r;
                        }
                    }
                }
                // sort the alerts
                function sort_alert($a,$b){
                    if(isset($a['time'])&&isset($b['time'])){
                        return $a['time'] > $b['time'];
                    }
                    return strtotime($a['date']) > strtotime($b['date']);
                }
                uasort($alerts,'sort_alert');
                foreach($alerts as $alert){
                    $group_key = isset($alert['group'])?$alert['group']:$alert['item'];
                    if(!isset($dashboard_alerts[$group_key])){
                        $dashboard_alerts[$group_key] = array();
                    }
                    $dashboard_alerts[$group_key][] = $alert;
                }
            }
        }

        $limit = module_config::c('dashboard_tabs_group_limit',0);
        $items_to_hide = json_decode(module_config::c('_dashboard_item_hide'.module_security::get_loggedin_id(),'{}'),true);
        if(!is_array($items_to_hide))$items_to_hide = array();
        if(isset($_REQUEST['hide_item'])&&strlen($_REQUEST['hide_item'])){
            $items_to_hide[] = $_REQUEST['hide_item'];
            module_config::save_config('_dashboard_item_hide'.module_security::get_loggedin_id(),json_encode($items_to_hide));
        }
        $all_listing = array();
        foreach($dashboard_alerts as $key => $val){
            // see if any of these "$val" alert entries are marked as hidden
            if(!isset($_REQUEST['show_hidden'])){
                foreach($val as $k=>$v){
                    $hide_key = md5($v['link'].$v['item'].$v['name']);
                    $dashboard_alerts[$key][$k]['hide_key'] = $val[$k]['hide_key'] = $hide_key;
                    if(in_array($hide_key,$items_to_hide)){
                        unset($val[$k]);
                        unset($dashboard_alerts[$key][$k]);
                    }
                }
            }
            if(count($val)>$limit){
                // this one gets it's own tab!
            }else{
                // this one goes into the all_listing bin
                $all_listing = array_merge($all_listing,$val);
                unset($dashboard_alerts[$key]);
            }
        }
        if(count($all_listing)){
            $dashboard_alerts = array(_l('Alerts')=>$all_listing) + $dashboard_alerts;
        }
        ksort($dashboard_alerts);
        return $dashboard_alerts;
    }
    public static function output_dashboard_alerts(){

        // we collect alerts from various places using our UCM hooks:
        $dashboard_alerts = self::get_dashboard_alerts();

        include(module_theme::include_ucm('includes/plugin_dashboard/pages/dashboard_alerts.php'));
    }

}