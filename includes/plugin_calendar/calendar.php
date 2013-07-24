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

class module_calendar extends module_base{
	
	var $links;

    public static function can_i($actions,$name=false,$category=false,$module=false){
        if(!$module)$module=__CLASS__;
        return parent::can_i($actions,$name,$category,$module);
    }
	public static function get_class() {
        return __CLASS__;
    }
	function init(){
		$this->links = array();
		$this->module_name = "calendar";
		$this->module_position = 8882;

        $this->version = 2.131;
        // 2.1 - initial
        // 2.11 - date format fix in cal export
        // 2.12 - permissoin fix
        // 2.13 - buffering fix
        // 2.131 - 2013-07-02 - language translation fix

        if(module_security::has_feature_access(array(
				'name' => 'Settings',
				'module' => 'config',
				'category' => 'Config',
				'view' => 1,
				'description' => 'view',
		))){
			$this->links[] = array(
				"name"=>"GoogleCal",
				"p"=>"calendar_settings",
				"icon"=>"icon.png",
				"args"=>array('calendar_id'=>false),
				'holder_module' => 'config', // which parent module this link will sit under.
				'holder_module_page' => 'config_admin',  // which page this link will be automatically added to.
				'menu_include_parent' => 0,
			);
        }
	}
    public static function link_calendar($calendar_type,$options=array(),$h=false){
        if($h){
            return md5('s3cret7hash for calendar '._UCM_FOLDER.' '.$calendar_type.serialize($options));
        }
        return full_link(_EXTERNAL_TUNNEL_REWRITE.'m.calendar/h.ical/i.'.$calendar_type.'/o.'.base64_encode(serialize($options)).'/hash.'.self::link_calendar($calendar_type,$options,true).'/cal.ics');
    }

     public function external_hook($hook){
        switch($hook){
            case 'ical':
                $calendar_type = (isset($_REQUEST['i'])) ? $_REQUEST['i'] : false;
                $options = (isset($_REQUEST['hash'])) ? (array)unserialize(base64_decode($_REQUEST['o'])) : array();
                $hash = (isset($_REQUEST['hash'])) ? trim($_REQUEST['hash']) : false;
                if($calendar_type && $hash){
                    $correct_hash = $this->link_calendar($calendar_type,$options,true);
                    if($correct_hash == $hash){

                        if(ob_get_level())ob_end_clean();
                        include('pages/ical_'.basename($calendar_type).'.php');
                        exit;

                    }
                }
                break;
        }


     }
}