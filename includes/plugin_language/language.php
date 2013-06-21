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

$labels = array();
global $labels;

class module_language extends module_base{
	
    public $version = 2.14;
    // 2.14 default system language in advanced settings.

    public static function can_i($actions,$name=false,$category=false,$module=false){
        if(!$module)$module=__CLASS__;
        return parent::can_i($actions,$name,$category,$module);
    }
	public static function get_class() {
        return __CLASS__;
    }
	function init(){

        $this->module_name = "language";
        global $labels;

        $language_id = module_config::c('default_language');

        if(module_security::is_logged_in()){
            $user = module_user::get_user(module_security::get_loggedin_id());
            if($user && $user['user_id'] && isset($user['language']) && $user['language']){
                $language_id = basename($user['language']);
            }
        }

        if(@include('custom/'.$language_id.'.php')){
            //define('_UCM_LANG',$language);
        }else if(@include('labels/'.$language_id.'.php')){
            //define('_UCM_LANG',$language);
        }

        if(_DEBUG_MODE && isset($_REQUEST['export_lang'])){
            ob_end_clean();
            echo '<pre>';
            echo '$labels = array('."\n\n";

            foreach($_SESSION['ll'] as $file_name => $data){
                echo "\n".'/** '.$file_name.' **/'."\n\n";
                foreach($data as $key => $val){
                    //echo "   '".str_replace("'","\'",htmlspecialchars($key))."' => '".str_replace("'","\'",htmlspecialchars($key))."',\n";
                    echo "   '".str_replace("'","\'",htmlspecialchars($key))."' => '',\n";
                }
            }
            echo "); \n";

            echo '</pre>';
            exit;
        }

	}
    public static function get_languages_attributes(){
        $all = array();
        $language_files = glob(_UCM_FOLDER.'includes/plugin_language/custom/*.php');
        if(is_array($language_files)){
            foreach($language_files as $language){
                $language = str_replace('.php','',basename($language));
                if($language[0]=='_')continue;
                $all[$language] = $language;
            }
        }
        $language_files = glob(_UCM_FOLDER.'includes/plugin_language/labels/*.php');
        if(is_array($language_files)){
            foreach($language_files as $language){
                $language = str_replace('.php','',basename($language));
                if($language[0]=='_')continue;
                $all[$language] = $language;
            }
        }
        return $all;
    }

}