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

define('_THEME_CONFIG_PREFIX','_theme_');

class module_theme extends module_base{
	
	var $links;

    public static $current_theme = '';

    public static function can_i($actions,$name=false,$category=false,$module=false){
        if(!$module)$module=__CLASS__;
        return parent::can_i($actions,$name,$category,$module);
    }
	public static function get_class() {
        return __CLASS__;
    }
    function module_theme(){
        $display_mode = get_display_mode();
        if($display_mode!='mobile'){
            module_config::register_css('theme','theme.php',true,100);
        }
        if($display_mode=='iframe'){
            module_config::register_css('theme','iframe.css',true,100);
        }

        if(_DEMO_MODE && $display_mode!='mobile' && is_dir('includes/plugin_theme/themes/pro/')){
            hook_add('header_print_js','module_theme::hook_header_print_js');
            if(isset($_REQUEST['demo_theme'])){
                $_SESSION['_demo_theme'] = basename($_REQUEST['demo_theme']);
                if(!$_SESSION['_demo_theme'])$_SESSION['_demo_theme'] = module_config::c('theme_name','default');
            }
            self::$current_theme = isset($_SESSION['_demo_theme']) ? $_SESSION['_demo_theme'] : module_config::c('theme_name','default');
        }else{
            self::$current_theme = module_config::c('theme_name','default');
            if(module_security::is_logged_in() && module_config::c('theme_per_user',0)){
                // we allow users to pick their own themes.
                self::$current_theme = module_config::c('theme_name_'.module_security::get_loggedin_id(),self::$current_theme);
            }
        }
        $current_theme = basename(self::$current_theme);
        if(strlen($current_theme)>2 && is_dir('includes/plugin_theme/themes/'.$current_theme.'/')){
            // we have an active theme!
            $file = 'includes/plugin_theme/themes/'.$current_theme.'/init.php';
            if(is_file($file)){
                include($file);
            }
        }
    }
    
	function init(){
        $this->version = 2.345;
        // 2.2 - handling including of files.
        // 2.3 - new pro dark theme beginnings
        // 2.31 - theme selector on settings page
        // 2.32 - change loction of mobile files and custom files.
        // 2.33 - demo theme support
        // 2.34 - left menu theme.
        // 2.341 - left menu update.
        // 2.342 - foreach() bug fix
        // 2.343 - permissoin fix
        // 2.344 - php5/6 fix
        // 2.345 - mobile layout fixes


		$this->links = array();
		$this->module_name = "theme";
		$this->module_position = 8882;

        if(file_exists('includes/plugin_theme/pages/theme_settings.php') && module_security::has_feature_access(array(
                'name' => 'Settings',
                'module' => 'config',
                'category' => 'Config',
                'view' => 1,
                'description' => 'view',
        ))){
            $this->links[] = array(
                "name"=>"Theme",
                "p"=>"theme_settings",
                'args'=>array(),
                'holder_module' => 'config', // which parent module this link will sit under.
                'holder_module_page' => 'config_admin',  // which page this link will be automatically added to.
                'menu_include_parent' => 0,
            );
        }


	}

    public static function hook_header_print_js(){
        $u = preg_replace('#.demo_theme=\w+#','',$_SERVER['REQUEST_URI']);
        ?>
    <script type="text/javascript">
        $(function(){
            $('#profile_info').append('<div id="demo_theme" style="position:absolute; top:0; margin-left: -223px; color:#000; background: #FFF; border-right:1px solid #CCC; border-left:1px solid #CCC; border-bottom:1px solid #CCC; border-bottom-left-radius:5px; border-bottom-right-radius:5px; opacity: 0.5; padding:5px; text-align: center;">' +
                'Change <a href="http://codecanyon.net/item/ultimate-client-manager-pro-edition/2621629?ref=dtbaker" target="_blank" style="color:#000;">UCM Pro</a> Theme: ' +
                '<a href="<?php echo htmlspecialchars($u); echo strpos($u,'?')===false ? '?' : '&'; ?>demo_theme=pro" style="display:inline-block; border:2px solid #<?php echo self::$current_theme == 'pro' ? '000':'FFF';?>; padding:0px 6px; background: #555; color:#FFF; margin:0 3px;" title="UCM Pro - Dark Theme Preview">1</a>' +
                '<a href="<?php echo htmlspecialchars($u); echo strpos($u,'?')===false ? '?' : '&'; ?>demo_theme=blue" style="display:inline-block; border:2px solid #<?php echo self::$current_theme == 'blue' ? '000':'FFF';?>; padding:0px 6px; background: #DBEFF5; color:#0079C2; margin:0 3px;" title="UCM Pro - Blue Theme Preview">2</a>' +
                '<a href="<?php echo htmlspecialchars($u); echo strpos($u,'?')===false ? '?' : '&'; ?>demo_theme=left" style="display:inline-block; border:2px solid #<?php echo self::$current_theme == 'left' ? '000':'FFF';?>; padding:0px 6px; background: #555; color:#FFF; margin:0 3px;" title="UCM Pro - Left Menu Theme Preview">3</a>' +
                '<a href="<?php echo htmlspecialchars($u); echo strpos($u,'?')===false ? '?' : '&'; ?>demo_theme=default" style="display:inline-block; border:2px solid #<?php echo self::$current_theme == 'default' ? '000':'FFF';?>; padding:0px 6px; background: #A7A5A5; color:#FFF; margin:0 3px;" title="UCM Pro - Default Theme Preview">4</a>' +
                '<a href="<?php echo htmlspecialchars($u); echo strpos($u,'?')===false ? '?' : '&'; ?>demo_theme=whitelabel1" style="display:inline-block; border:2px solid #<?php echo self::$current_theme == 'whitelabel1' ? '000':'FFF';?>; padding:0px 6px; background: #f3f3f3; color:#95cd00; margin:0 3px;" title="UCM Pro - White Label Theme Preview">5</a>' +
                '<br/>' +
                'Like this software? <a href="http://codecanyon.net/item/ultimate-client-manager-pro-edition/2621629?ref=dtbaker" title="Download Ultimate Client Manager Pro Edition" target="_blank" style="color:#000; text-decoration: underline;">Click here</a> to get it!' +
                '</div>');
            $('#demo_theme').hover(function(){
                $(this).stop().animate({"opacity": 1});
            },function(){
                $(this).stop().animate({"opacity": 0.5});
            });
        });
    </script>
    <?php
    }

    public static function include_ucm($page){
        // what folder do we search for?
        // custom/includes/plugin_mobile/custom_layout/theme/$theme_name/$page
        // custom/includes/plugin_mobile/custom_layout/$page
        // custom/theme/$theme_name/$page
        // custom/$page
        // includes/plugin_mobile/custom_layout/theme/$theme_name/$page
        // includes/plugin_mobile/custom_layout/$page
        // theme/$theme_name/$page
        // $page

        // sanatise $page.


        $display_mode = get_display_mode();


        $check_files = array();
        $current_theme = basename(self::$current_theme);
        if(strlen($current_theme)>2 && is_dir('includes/plugin_theme/themes/'.$current_theme.'/')){
            // we have an active theme!
        }else{
            $current_theme = false;
        }

        // build up our file listing.
        if($display_mode == 'mobile'){
            $check_files[] = 'custom/'.dirname($page).'/mobile/'.basename($page);
        }
        $check_files[] = 'custom/'.$page;
        if($display_mode == 'mobile'){
            //$check_files[] = 'includes/plugin_mobile/custom_layout/'.$page;
            $check_files[] = dirname($page).'/mobile/'.basename($page);
        }
        if($current_theme)$check_files[] = 'includes/plugin_theme/themes/'.$current_theme.'/'.$page;
        $check_files[] = $page;



        foreach($check_files as $file){
            module_debug::log(array(
                'title' => 'IncludeUCM',
                'file' => 'includes/plugin_theme/theme.php',
                'data' => "Checking for include file: ".$file,
            ));
            if(is_file($file)){
                module_debug::log(array(
                    'title' => 'IncludeUCM',
                    'file' => 'includes/plugin_theme/theme.php',
                    'data' => "FOUND FILE! ".$file,
                ));
                return $file;
            }
        }


        module_debug::log(array(
            'title' => 'IncludeUCM',
            'file' => 'includes/plugin_theme/theme.php',
            'data' => "Warning: File not found ".$page,
        ));
        return $page; // as a defult, wont ever get here.
    }

    public static function get_theme_styles($theme='default'){
        // return an array of the css styles to display on the page, pretty simple.

        $styles = array();


        $styles ['body'] = array(
            'd' => 'Overall page settings',
            'v'=>array(
                'background-color' => '#E7E7E7',
                'background-image' => 'none',
		        'font-family' => 'Arial, Helvetica, sans-serif',
		        'font-size' => '12px',
            ),
        );
        $styles ['body,#profile_info a'] = array(
            'd' => 'Main font color',
            'v'=>array(
                'color' => '#000000',
            ),
        );
        $styles ['#header,#page_middle,#main_menu'] = array(
            'd' => 'Content width',
            'v'=>array(
                'width' => '1294px',
            ),
        );
        $styles ['#header'] = array(
            'd' => 'Header height',
            'v'=>array(
                'height' => '76px',
            ),
        );
        $styles ['#header_logo'] = array(
            'd' => 'Logo padding',
            'v'=>array(
                'padding' => '10px 0 0 12px',
            ),
        );
        $styles ['.nav>ul>li>a,#quick_search_box'] = array(
            'd' => 'Menu items',
            'v'=>array(
                'color' => '#FFFFFF',
                'background-color' => '#A7A5A5',
            ),
        );
        $styles ['.nav>ul>li>a:hover'] = array(
            'd' => 'Menu items (when hovering)',
            'v'=>array(
                'color' => '#000000',
                'background-color' => '#FFFFFF',
            ),
        );
        $styles ['#page_middle>.content,.nav>ul>li>a,#page_middle .nav,#quick_search_box'] = array(
            'd' => 'Menu outline color',
            'v'=>array(
                'border-color' => '#CBCBCB',
            ),
        );
        $styles ['h2'] = array(
            'd' => 'Main Page Title',
            'v'=>array(
                'color' => '#333333',
                'background-color' => '#EEEEEE',
                'border' => '1px solid #cbcbcb',
                'font-size' => '19px',
            ),
        );
        $styles ['h3'] = array(
            'd' => 'Sub Page Title',
            'v'=>array(
                'color' => '#666666',
                'background-color' => '#DFDFDF',
                'font-size' => '15px',
            ),
        );


        $current_theme = basename($theme);
        if(strlen($current_theme)>2 && is_dir('includes/plugin_theme/themes/'.$current_theme.'/')){
            // we have an active theme!
        }else{
            $current_theme = '';
        }
        if($current_theme){
            $file = 'includes/plugin_theme/themes/'.$current_theme.'/style.php';
            if(is_file($file)){
                include($file);
            }
        }

        foreach($styles as $style_id => $style){
            $styles[$style_id]['r'] = $style_id; // backwards compat
            foreach($style['v'] as $k=>$v){
                $styles[$style_id]['v'][$k] = array(self::get_config($theme.$style_id.'_'.$k,$v),$v);
            }
        }

        return $styles;
    }

    public static function get_config($key,$default=''){
        $style = module_config::c(_THEME_CONFIG_PREFIX.$key,false);
        if(!$style)return $default;
        return $style;
    }
}