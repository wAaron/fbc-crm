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
switch($display_mode){
    case 'iframe':
        ?>
         </div>
        </body>
        </html>
        <?php
        module_debug::push_to_parent();
        break;
    case 'ajax':

        break;
    case 'mobile':
        if(class_exists('module_mobile',false)){
            module_mobile::render_stop($page_title,$page);
        }
        break;
    case 'normal':
    default:
        ?>

        </div>
          </div>
          <div id="footer">
              &copy; <?php echo module_config::s('admin_system_name','Ultimate Client Manager'); ?>
              - <?php echo date("Y"); ?>
              - Version: <?php echo module_config::current_version(); ?>
              - Time: <?php echo round(microtime(true)-$start_time,5);?>
              <?php if(class_exists('module_mobile',false) && module_config::c('mobile_link_in_footer',1)){ ?>
            - <a href="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); echo strpos($_SERVER['REQUEST_URI'],'?')===false ? '?' : '&'; ?>display_mode=mobile"><?php _e('Switch to Mobile Site');?></a>
            <?php } ?>
          </div>
        </div>
        </body>
        </html>
        <?php
        break;
}