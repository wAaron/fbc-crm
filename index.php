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


/**
 * Ultimate Client Manager
 * Version 2
 *
 * Author: David Baker
 * Email: dtbaker@gmail.com
 * Please send all EMAILS through http://codecanyon.net/user/dtbaker
 * Copyright 2011 David Baker
 * You must have purchased a valid license from CodeCanyon to use this script.
 *
 * If you would like to re-use parts of this system or ideas from this system elsewhere please ask permission first.
 */

if (get_magic_quotes_gpc()) {
    $process = array(&$_GET, &$_POST, &$_COOKIE, &$_REQUEST);
    while (list($key, $val) = each($process)) {
        foreach ($val as $k => $v) {
            unset($process[$key][$k]);
            if (is_array($v)) {
                $process[$key][stripslashes($k)] = $v;
                $process[] = &$process[$key][stripslashes($k)];
            } else {
                $process[$key][stripslashes($k)] = stripslashes($v);
            }
        }
    }
    unset($process);
}

if(preg_match('#/external/(m.*$)#',$_SERVER['REQUEST_URI'],$matches)){
    // hack for dodgy email clients.
    $parts = explode('/',trim($matches[1],'/'));
    foreach($parts as $key=>$val){
        $foo = explode('.',$val);
        $_REQUEST[$foo[0]] = preg_replace('#\?.*$#','',$foo[1]);
    }
    
    include('ext.php');
    exit;
}


$start_time = microtime(true);
header( 'Content-Type: text/html; charset=UTF-8' );
require_once('init.php');

if(!_UCM_INSTALLED){
    $_REQUEST['m'] = 'setup';
    $_REQUEST['display_mode' ] = 'normal';
}

$display_mode = get_display_mode();

// stop more than 1 of the same page load.
$loaded_pages = array();

$current_menu_level = 0; // increases each time we load a menu on the page.


// this is an update for design_menu.php
$menu_modules = $load_modules;
$menu_module_index = count($menu_modules);

try{
	foreach($load_modules as $load_module_key => $load_module){

		$load_module = basename($load_module);
		$load_page = isset($load_pages[$load_module_key])  ? basename($load_pages[$load_module_key]) : false;
		// if the user isn't logged in, display the login page.
        if(!_UCM_INSTALLED || $load_module == 'setup'){
            $load_page = 'setup';
            $load_module = 'setup';
        }else if(!getcred()){
            if(is_callable('module_security::check_ssl'))module_security::check_ssl();
            $load_page = 'login';
        }else if(!$load_page){
            $load_page = 'home';
        }
        if($load_page && is_file("pages/".$load_page.".php")){
            $page = "pages/".$load_page.".php";
        }else{
            $page = 'pages/home.php';
        }
		// load this particular module so other scripts can access the $module variable.
		$module = false;
		if(isset($plugins[$load_module])){
			$module = &$plugins[$load_module];
		}

		if(module_security::getcred() || $load_module == 'setup'){
			
			// handle any form submits for this module.
			if(isset($_REQUEST['_process']) && $_REQUEST['_process']){
				if($module){
					module_debug::log(array(
						'title' => 'Process Post Back',
						'file' => 'index.php',
						'data' => "_process variable found, passing this through to module: $load_module",
					));
					$module->process();
				}else{
					module_debug::log(array(
						'title' => 'Process Post Back',
						'file' => 'index.php',
						'data' => "_process variable found, passing this through to includes/process.php file",
					));
					require_once("includes/process.php");
				}
			}
			if($module && $load_page && is_file("includes/plugin_".$load_module."/pages/".$load_page.".php")){
				// pull out the module in a local var ready for these pages to use.
				$page = "includes/plugin_".$load_module."/pages/".$load_page.".php";
			}else if($load_page && is_file("pages/".$load_page.".php")){
				$page = "pages/".$load_page.".php";
			}
        
            module_debug::log(array(
                'title' => 'Found Page',
                'file' => 'index.php',
                'data' => "found a page to load for module $load_module: $page",
            ));
		}


		if(!isset($loaded_pages[$page])){
			$loaded_pages[$page] = true;
			ob_start(); // START INNER CONTENT OB
            module_debug::log(array(
                'title' => 'Page Render (0)',
                'file' => 'index.php',
                'data' => "Including this page: $page",
            ));
            // update! we check if this "page" has a custom version as per the current display (eg: mobile) or theme
            include(module_theme::include_ucm($page));
			//include($page);
            if(class_exists('module_security',false)){
                // this will do some magic if the user only has "view" permissions to this editable page.
                module_security::render_page_finished();
            }
			if($module){
				// we find any sub module LINKS that have to be displayed here,
				// which will guve the user the option of navigating to a sub module.
				$has_sub_links = false;
				foreach($plugins as $plugin_name => &$plugin){
					if($plugin->get_menu($module->module_name,$load_page)){
						$has_sub_links = true;
						break;
					}
				}
				if((isset($links)&&count($links)) || $has_sub_links){
					$menu_include_parent = $current_menu_level;
                    module_debug::log(array(
                        'title' => 'Page Render (1)',
                        'file' => 'index.php',
                        'data' => "Including this page (the menu): design_menu.php",
                    ));
                    ob_start(); // START MENU OB
                    if(is_file('design_menu.php')){
                        // remove 'final_content_wrap' from other outputs!
                        include(module_theme::include_ucm("design_menu.php"));
                        if(!isset($do_menu_wrap)){
                            echo '<div class="final_content_wrap">';
                            $do_menu_wrap=true;
                        }
                    }
					// todo - fix but with more than 2 levels of menus.
					// maybe instead of "include parents" we just pass whatever level we are currently on to the script
					// and it will work out the rest.
					// could maybe move the design_menu call from design_header.php up here to fix the issue. all in 1 place then :)
					$current_menu_level++;
					?>
					<div class="content">
						<?php
						// the inner content will display where this place holder is:
						if(isset($inner_content) && count($inner_content)){
                            module_debug::log(array(
                                'title' => 'Page Render (2)',
                                'file' => 'index.php',
                                'data' => "Displaying content from the 'inner_content' array.",
                            ));
							echo array_shift($inner_content);
						}else if($current_selected_link){
							if(isset($current_selected_link['default_page']) && is_file("includes/plugin_".$current_selected_link['m']."/pages/".basename($current_selected_link['default_page']).".php")){
                                $module_page = "includes/plugin_".$current_selected_link['m']."/pages/".basename($current_selected_link['default_page']).".php";
                                module_debug::log(array(
                                    'title' => 'Page Render (3)',
                                    'file' => 'index.php',
                                    'data' => "Including this page: $module_page",
                                ));
								//include($module_page);
                                include(module_theme::include_ucm($module_page));
                                if(class_exists('module_security',false)){
                                    // this will do some magic if the user only has "view" permissions to this editable page.
                                    module_security::render_page_finished();
                                }
							}
						}
						?>
					</div>
                    <?php
                    if(isset($do_menu_wrap) && $do_menu_wrap){
                        echo '</div>';
                    }
                    echo ob_get_clean(); // END MENU OB
				}else if(!isset($do_menu_wrap)){
                    // no sub links!
                    $do_menu_wrap = false;
                    $content = ob_get_contents();
                    ob_clean();
                    echo '<div class="final_content_wrap">';
                    echo $content;
                    echo '</div>';

                }
				if(isset($links)){
					unset($links);
				}
			}
			$inner_content [] = ob_get_clean(); // END INNER CONTENT OB
		}

		// see if this module has a page title.
		if($module && module_security::is_logged_in()){
			if($module->get_page_title()){
				$page_title = htmlspecialchars($module->get_page_title()) . $page_title_delim . $page_title;
			}
		}
        
		if(isset($module)){
			unset($module);
		}
		/*if(preg_match('#\{INNER_CONTENT\}#',$inner_content)){
			$inner_content = preg_replace('#\{INNER_CONTENT\}#',$this_content,$inner_content);
		}else{
			$inner_content .= $this_content;
		}
		unset($this_content);*/
		unset($load_page);

        if($display_mode == 'iframe' || $display_mode == 'ajax'){
            break;
        }

	}
}catch(Exception $e){
	$inner_content[] = 'Error: ' . $e->getMessage();
}
// combine any inner content together looking for place holders.

$page_title = trim(preg_replace('#'.preg_quote($page_title_delim,'#').'\s*$#','',$page_title));
if(!trim($page_title)){
    $page_title = htmlspecialchars(module_config::s('admin_system_name','Ultimate Client Manager'));
}



require_once(module_theme::include_ucm("design_header.php"));
echo implode('',$inner_content);
require_once(module_theme::include_ucm("design_footer.php"));


hook_finish();