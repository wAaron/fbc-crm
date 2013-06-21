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
    case 'mobile':
        if(class_exists('module_mobile',false)){
            module_mobile::render_start($page_title,$page);
        }
        break;
    case 'ajax':

        break;
    case 'iframe':
    case 'normal':
    default:


        ?>

        <!DOCTYPE html>
        <html ng-app>
        <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title><?php echo $page_title; ?></title>

        <?php $header_favicon = module_theme::get_config('theme_favicon','');
            if($header_favicon){ ?>
                <link rel="icon" href="<?php echo htmlspecialchars($header_favicon);?>">
        <?php } ?>

            <link type="text/css" href="<?php echo _BASE_HREF;?>css/smoothness/jquery-ui-1.9.2.custom.min.css?ver=<?php echo _SCRIPT_VERSION;?>" rel="stylesheet" />
            <link rel="stylesheet" href="<?php echo _BASE_HREF;?>css/desktop.css?ver=<?php echo _SCRIPT_VERSION;?>" type="text/css" />
            <link rel="stylesheet" href="<?php echo _BASE_HREF;?>css/styles.css?ver=<?php echo _SCRIPT_VERSION;?>" type="text/css" />
            <link rel="stylesheet" href="<?php echo _BASE_HREF;?>css/jRating.jquery.css" type="text/css" />
            <?php module_config::print_css(_SCRIPT_VERSION);?>



        <script language="javascript" type="text/javascript">
            // by dtbaker.
            var ajax_search_ini = '';
            var ajax_search_xhr = false;
            var ajax_search_url = '<?php echo _BASE_HREF;?>ajax.php';
        </script>

        <script type="text/javascript" src="<?php echo _BASE_HREF;?>js/jquery-1.8.3.min.js?ver=<?php echo _SCRIPT_VERSION;?>"></script>
        <script type="text/javascript" src="<?php echo _BASE_HREF;?>js/jquery-ui-1.9.2.custom.min.js?ver=<?php echo _SCRIPT_VERSION;?>"></script>
        <script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/angularjs/1.0.7/angular.min.js"></script>
        <script type="text/javascript" src="<?php echo _BASE_HREF;?>js/jRating.jquery.js"></script>
        <script type="text/javascript" src="<?php echo _BASE_HREF;?>js/timepicker.js?ver=<?php echo _SCRIPT_VERSION;?>"></script>
        <script type="text/javascript" src="<?php echo _BASE_HREF;?>js/cookie.js?ver=<?php echo _SCRIPT_VERSION;?>"></script>
        <script type="text/javascript" src="<?php echo _BASE_HREF;?>js/javascript.js?ver=<?php echo _SCRIPT_VERSION;?>"></script>
        
        <?php module_config::print_js(_SCRIPT_VERSION);?>


        <!--
        Author: David Baker (dtbaker.com.au)
        10/May/2010
        -->
        <script type="text/javascript">
            $(function(){
                init_interface();
                // calendar defaults
                <?php
                switch(strtolower(module_config::s('date_format','d/m/Y'))){
                    case 'd/m/y':
                        $js_cal_format = 'dd/mm/yy';
                        break;
                    case 'y/m/d':
                        $js_cal_format = 'yy/mm/dd';
                        break;
                    case 'm/d/y':
                        $js_cal_format = 'mm/dd/yy';
                        break;
                    default:
                        $js_cal_format = 'yy-mm-dd';
                }
                ?>
                $.datepicker.regional['ucmcal'] = {
                    closeText: '<?php _e('Done');?>',
                    prevText: '<?php _e('Prev');?>',
                    nextText: '<?php _e('Next');?>',
                    currentText: '<?php _e('Today');?>',
                    monthNames: ['<?php _e('January');?>','<?php _e('February');?>','<?php _e('March');?>','<?php _e('April');?>','<?php _e('May');?>','<?php _e('June');?>', '<?php _e('July');?>','<?php _e('August');?>','<?php _e('September');?>','<?php _e('October');?>','<?php _e('November');?>','<?php _e('December');?>'],
                    monthNamesShort: ['Jan', '<?php _e('Feb');?>', '<?php _e('Mar');?>', '<?php _e('Apr');?>', '<?php _e('May');?>', '<?php _e('Jun');?>', '<?php _e('Jul');?>', '<?php _e('Aug');?>', '<?php _e('Sep');?>', '<?php _e('Oct');?>', '<?php _e('Nov');?>', '<?php _e('Dec');?>'],
                    dayNames: ['<?php _e('Sunday');?>', '<?php _e('Monday');?>', '<?php _e('Tuesday');?>', '<?php _e('Wednesday');?>', '<?php _e('Thursday');?>', '<?php _e('Friday');?>', '<?php _e('Saturday');?>'],
                    dayNamesShort: ['<?php _e('Sun');?>', '<?php _e('Mon');?>', '<?php _e('Tue');?>', '<?php _e('Wed');?>', '<?php _e('Thu');?>', '<?php _e('Fri');?>', '<?php _e('Sat');?>'],
                    dayNamesMin: ['<?php _e('Su');?>','<?php _e('Mo');?>','<?php _e('Tu');?>','<?php _e('We');?>','<?php _e('Th');?>','<?php _e('Fr');?>','<?php _e('Sa');?>'],
                    weekHeader: '<?php _e('Wk');?>',
                    dateFormat: '<?php echo $js_cal_format;?>',
                    firstDay: <?php echo module_config::c('calendar_first_day_of_week','1');?>,
                    yearRange: '<?php echo module_config::c('calendar_year_range','-90:+3');?>'
                };
                $.datepicker.setDefaults($.datepicker.regional['ucmcal']);

            	$(".contact_star_rate").jRating({
            		  showRateInfo:false,
            		  step:true,
            		  length : 5,
            		  canRateAgain : true,
            		  nbRates: 100000,
            	});
            });
        </script>


        </head>
        <body <?php if($display_mode=='iframe') echo ' style="background:#FFF;"';?>>

<?php if($display_mode=='iframe'){ ?>
<div id="iframe">
<?php }else{ ?>
<?php if(_DEBUG_MODE){
    module_debug::print_heading();
} ?>
<div id="holder">


	<div id="header">

        <div>
            <div style="position:absolute; z-index:4; margin-left:367px;width:293px; display:none;" id="message_popdown">
                <?php if(print_header_message()){
                    ?>
                    <script type="text/javascript">
                        $('#message_popdown').fadeIn('slow');
                        <?php if(module_config::c('header_messages_fade_out',1)){ ?>
                        $(function(){
                            setTimeout(function(){
                                $('#message_popdown').fadeOut();
                            },4000);
                        });
                        <?php } ?>
                    </script>
                        <?php
                } ?>
            </div>
        </div>

        <?php if(_DEMO_MODE && preg_match('#/demo_lite/#',$_SERVER['REQUEST_URI'])){ ?>
        <div style="margin: 10px 0 0 296px;position:absolute;">
            <a href="http://goo.gl/YYgVJ" title="Download Ultimate Client Manager"><img src="http://ultimateclientmanager.com/webimages/like-what-you-see-here.png" border="0" alt="Freelance Database - php client manager"></a>
        </div>
        <?php } ?>

		<div id="header_logo">
            <?php if($header_logo = module_theme::get_config('theme_logo',_BASE_HREF.'images/logo.png')){ ?>
                <a href="<?php echo _BASE_HREF;?>"><img src="<?php echo htmlspecialchars($header_logo);?>" border="0" title="<?php echo htmlspecialchars(module_config::s('header_title','UCM'));?>"></a>
            <?php }else{ ?>
                <a href="<?php echo _BASE_HREF;?>"><?php echo module_config::s('header_title','UCM');?></a>
            <?php } ?>
		</div>
		<?php
		if(module_security::getcred()){
			?>
	    	<div id="profile_info">
				<?php echo module_user::link_open($_SESSION['_user_id'],true);?> <span class="sep">|</span>
                <a href="<?php echo _BASE_HREF;?>index.php?_logout=true"><?php _e('Logout');?></a>
                <div class="date"><?php echo date('l jS \of F Y'); ?></div>
			</div>
		<?php
		}
		?>

	</div>

	<div id="main_menu">
        <?php
        $menu_include_parent=false;
        $show_quick_search=true;
        if(is_file('design_menu.php')){
            //include("design_menu.php");
            include(module_theme::include_ucm("design_menu.php"));
        }
        ?>
	</div>

	<div id="page_middle">
    <?php }
    ?>

		<div class="content">

                
        <?php
}
