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

$labels = array();
global $labels;

class module_language extends module_base{
	
    public $version = 2.163;
    // 2.14 default system language in advanced settings.
    // 2.15 - added a default german translation (needs work!)
    // 2.16 - French translation added - thanks Amar Bou!
    // 2.161 - 2013-04-04 - fix for translation in User Roles
    // 2.162 - 2013-04-12 - language preference fix for users with little permissions
    // 2.163 - 2013-05-08 - spanish translation file - thanks pibe!

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
            $user = module_user::get_user(module_security::get_loggedin_id(),false);
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
            if(isset($_REQUEST['csv'])){
                foreach($_SESSION['ll'] as $file_name => $data){
                    //echo "\n".'/** '.$file_name.' **/'."\n\n";
                    foreach($data as $key => $val){
                        if($key==='0')continue;
                        // dont do duplicates
                        if(isset($done[$key]))continue;
                        $done[$key]=true;
                        //echo "   '".str_replace("'","\'",htmlspecialchars($key))."' => '".str_replace("'","\'",htmlspecialchars($key))."',\n";
                       echo "$key\n";
                        //echo "   '".str_replace("'","\'",htmlspecialchars($key))."' => '',\n";
                    }
                }
            }else{
            echo '<pre>';
                echo '$labels = array('."\n\n";
                $done=array();
                foreach($_SESSION['ll'] as $file_name => $data){
                    echo "\n".'/** '.$file_name.' **/'."\n\n";
                    foreach($data as $key => $val){
                        if($key==='0')continue;
                        // dont do duplicates
                        if(isset($done[$key]))continue;
                        $done[$key]=true;
                        //echo "   '".str_replace("'","\'",htmlspecialchars($key))."' => '".str_replace("'","\'",htmlspecialchars($key))."',\n";
                        echo "   '".str_replace("'","\'",htmlspecialchars($key))."' => '',\n";
                    }
                }
                echo "); \n";
            echo '</pre>';
            }
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