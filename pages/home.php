
<div class="final_content_wrap">
<h2><?php 
/** 
  * Copyright: dtbaker 2012
  * Licence: Please check CodeCanyon.net for licence details. 
  * More licence clarification available here:  http://codecanyon.net/wiki/support/legal-terms/licensing-terms/ 
  * Deploy: 3053 c28b7e0e323fd2039bb168d857c941ee
  * Envato: 6b31bbe6-ead4-44a3-96e1-d5479d29505b
  * Package Date: 2013-02-27 19:09:56 
  * IP Address: 
  */ echo _l('Home Page'); ?></h2>
<?php if(false && _DEMO_MODE){ ?>
    <div style="float:right; padding:5px; font-style: italic;">
        <strong>UCM Demonstration Website</strong> <br/> This is a demo website for the <span style="text-decoration: underline;">Ultimate Client Manager</span>. <br/>This demo resets every now and then. <br/>The Ultimate Client Manager can be downloaded from CodeCanyon. <br/><a href="http://codecanyon.net/item/ultimate-client-manager-lite-edition/47626?ref=dtbaker">Please click here for more details</a>.
    </div>
<?php } ?>
<p>
    <?php echo _l('Hi %s, and Welcome to %s', htmlspecialchars($_SESSION['_user_name']), module_config::s('admin_system_name')); ?>
</p>

<?php if(module_config::c('dashboard_new_layout',1)){

    // group alerts by type.
    // if there are more than X alert of a particular type we create a tab for that item.
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
                return strtotime($a['date']) > strtotime($b['date']);
            }
            uasort($alerts,'sort_alert');
            foreach($alerts as $alert){
                if(!isset($dashboard_alerts[$alert['item']])){
                    $dashboard_alerts[$alert['item']] = array();
                }
                $dashboard_alerts[$alert['item']][] = $alert;
            }
        }
    }
    if(class_exists('module_job',false) && module_security::can_user(module_security::get_loggedin_id(),'Show Dashboard Todo List')){
        $todo_list = module_job::get_tasks_todo();
        $x=0;
        $key = _l('Job Todo');
        foreach ($todo_list as $todo_item) {
            if($todo_item['hours_completed'] > 0){
                if($todo_item['hours'] > 0){
                    $percentage = round($todo_item['hours_completed'] / $todo_item['hours'],2);
                    $percentage = min(1,$percentage);
                }else{
                    $percentage = 1;
                }
            }else{
                $percentage = 0;
            }
            $job_data = module_job::get_job($todo_item['job_id'],false);
            if(!isset($dashboard_alerts[$key])){
                $dashboard_alerts[$key] = array();
            }
            $alert = process_alert($todo_item['date_due'],'temp');
            $dashboard_alerts[$key][] = array(
                'link'=>module_job::link_open($todo_item['job_id'],false,$job_data),
                'item'=>$job_data['name'],
                'name'=>($percentage * 100).'% '.$todo_item['description'],
                'warning'=>$alert['warning'],
                'days'=>$alert['days'],
                'date'=>$alert['date'],
            );
        }
    }
    $limit = module_config::c('dashboard_tabs_group_limit',1);
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
    if(get_display_mode()=='mobile'){
        foreach($dashboard_alerts as $key=>$alerts){ ?>

            <?php print_heading(array('title'=>$key.' ('.count($alerts).')','type'=>'h3'));?>
            <div class="content_box_wheader">
            <table class="tableclass tableclass_rows tableclass_full tbl_fixed">
                <tbody>
                <?php
                if (count($alerts)) {
                    $y = 0;
                    foreach ($alerts as $alert) {
                        ?>
                        <tr class="<?php echo ($y++ % 2) ? 'even' : 'odd'; ?>">
                            <td class="row_action">
                                <a href="<?php echo $alert['link']; ?>"><?php echo htmlspecialchars($alert['item']); ?></a>
                            </td>
                            <?php if($display_mode!='mobile'){ ?>
                                <td>
                                    <?php echo isset($alert['name']) ? htmlspecialchars($alert['name']) : ''; ?>
                                </td>
                                <td width="16%">
                                    <?php echo ($alert['warning']) ? '<span class="important">' : ''; ?>
                                    <?php echo $alert['days']; ?>
                                    <?php echo ($alert['warning']) ? '</span>' : ''; ?>
                                </td>
                            <?php } ?>
                            <td width="16%">
                                <?php echo ($alert['warning']) ? '<span class="important">' : ''; ?>
                                <?php echo print_date($alert['date']); ?>
                                <?php echo ($alert['warning']) ? '</span>' : ''; ?>
                            </td>
                        </tr>
                    <?php
                    }
                } else {
                    ?>
                    <tr>
                        <td class="odd" colspan="4"><?php _e('Yay! No alerts!');?></td>
                    </tr>
                <?php  } ?>
                </tbody>
            </table>
        </div>
        <?php
        } ?>
    <?php
    }else{
    ?>
    <script type="text/javascript">
        $(function(){
            $('#dashboard_tabs').tabs();
            $('#dashboard_tabs').tabs('select', Get_Cookie("dashboard_tab"));
            $("#dashboard_tabs").click(function() {
                Set_Cookie("dashboard_tab", $( "#dashboard_tabs" ).tabs('option', 'selected')+1);
            });
        });
        function hide_item(key){
            $('#hide_item').val(key);
            $('#hide_item_form')[0].submit();
        }
    </script>
        <form action="" method="post" id="hide_item_form">
            <input type="hidden" name="hide_item" value="" id="hide_item">
        </form>
    <div id="dashboard_tabs">
        <ul>
            <?php
            $x=1;
            foreach($dashboard_alerts as $key=>$val){ ?>
            <li><a href="#tabs-<?php echo $x;?>"><?php echo $key;?> (<?php echo count($val);?>)</a></li>
            <?php
            $x++;
            } ?>
        </ul>
        <?php
        $x=1;
        foreach($dashboard_alerts as $key=>$alerts){ ?>
        <div id="tabs-<?php echo $x;?>">
            <table class="tableclass tableclass_rows tableclass_full tbl_fixed">
                <tbody>
                <?php
                if (count($alerts)) {
                    $y = 0;
                    foreach ($alerts as $alert) {
                        ?>
                        <tr class="<?php echo ($y++ % 2) ? 'even' : 'odd'; ?>">
                            <td class="row_action">
                                <a href="<?php echo $alert['link']; ?>"><?php echo htmlspecialchars($alert['item']); ?></a>
                            </td>
                            <?php if($display_mode!='mobile'){ ?>
                                <td>
                                    <?php echo isset($alert['name']) ? htmlspecialchars($alert['name']) : ''; ?>
                                </td>
                                <td width="16%">
                                    <?php echo ($alert['warning']) ? '<span class="important">' : ''; ?>
                                    <?php echo $alert['days']; ?>
                                    <?php echo ($alert['warning']) ? '</span>' : ''; ?>
                                </td>
                            <?php } ?>
                            <td width="16%">
                                <?php echo ($alert['warning']) ? '<span class="important">' : ''; ?>
                                <?php echo print_date($alert['date']); ?>
                                <?php echo ($alert['warning']) ? '</span>' : ''; ?>
                            </td>
                            <?php if(isset($alert['hide_key']) && $alert['hide_key']){ ?>
                            <td width="10">
                                <a href="#" class="ui-corner-all ui-icon ui-icon-trash" onclick="return hide_item('<?php echo $alert['hide_key'];?>');">[x]</a>
                            </td>
                            <?php } ?>
                        </tr>
                    <?php
                    }
                } else {
                    ?>
                    <tr>
                        <td class="odd" colspan="4"><?php _e('Yay! No alerts!');?></td>
                    </tr>
                <?php  } ?>
                </tbody>
            </table>
        </div>
        <?php
            $x++;
        } ?>
    </div>
    <?php
    }

}else{ // show old layout:  ?>


<table width="100%" cellpadding="5">
    <tr>
        <td width="50%" valign="top">

            <?php if(module_security::can_user(module_security::get_loggedin_id(),'Show Dashboard Alerts')){

                $alerts = array();
                $results = handle_hook("home_alerts");
                if (is_array($results)) {
                    foreach ($results as $res) {
                        if (is_array($res)) {
                            foreach ($res as $r) {
                                $alerts[] = $r;
                            }
                        }
                    }
                    // sort the alerts
                    function sort_alert($a,$b){
                        return strtotime($a['date']) > strtotime($b['date']);
                    }
                    uasort($alerts,'sort_alert');
                }

                ?>

            <?php print_heading(array('title'=>'Your Alerts','type'=>'h3'));?>
            <div class="content_box_wheader">

            <table class="tableclass tableclass_rows tableclass_full tbl_fixed">
                <tbody>
                <?php
                if (count($alerts)) {
                    $x = 0;
                    foreach ($alerts as $alert) {
                        ?>
                        <tr class="<?php echo ($x++ % 2) ? 'even' : 'odd'; ?>">
                            <td class="row_action">
                                <a href="<?php echo $alert['link']; ?>"><?php echo htmlspecialchars($alert['item']); ?></a>
                            </td>
                            <?php if($display_mode!='mobile'){ ?>
                            <td>
                                <?php echo isset($alert['name']) ? htmlspecialchars($alert['name']) : ''; ?>
                            </td>
                            <td width="16%">
                                <?php echo ($alert['warning']) ? '<span class="important">' : ''; ?>
                                <?php echo $alert['days']; ?>
                                <?php echo ($alert['warning']) ? '</span>' : ''; ?>
                            </td>
                            <?php } ?>
                            <td width="16%">
                                <?php echo ($alert['warning']) ? '<span class="important">' : ''; ?>
                                <?php echo print_date($alert['date']); ?>
                                <?php echo ($alert['warning']) ? '</span>' : ''; ?>
                            </td>
                        </tr>
                        <?php
                    }
                } else {
                    ?>
                    <tr>
                        <td class="odd" colspan="4"><?php _e('Yay! No alerts!');?></td>
                    </tr>
                <?php  } ?>
                </tbody>
            </table>
                </div>
                <?php  } ?>
        </td>
        <td valign="top">

            <?php if(class_exists('module_job',false) && module_security::can_user(module_security::get_loggedin_id(),'Show Dashboard Todo List')){ ?>

            <?php print_heading(array('title'=>'Todo List','type'=>'h3'));?>
                <div class="content_box_wheader">

             <table class="tableclass tableclass_rows tableclass_full">
                <tbody>
                <?php
                $todo_list = module_job::get_tasks_todo();
                $x=0;
                if(!count($todo_list)){
                    ?>
                    <tr>
                        <td>
                            <?php _e('Yay! No todo list!'); ?>
                        </td>
                    </tr>
                    <?php
                }else{
                foreach ($todo_list as $todo_item) {
                    if($todo_item['hours_completed'] > 0){
                        if($todo_item['hours'] > 0){
                            $percentage = round($todo_item['hours_completed'] / $todo_item['hours'],2);
                            $percentage = min(1,$percentage);
                        }else{
                            $percentage = 1;
                        }
                    }else{
                        $percentage = 0;
                    }
                    $job_data = module_job::get_job($todo_item['job_id'],false);
                    ?>
                    <tr class="<?php echo ($x++ % 2) ? 'even' : 'odd'; ?>">
                        <td class="row_action">
                            <a href="<?php echo module_job::link_open($todo_item['job_id'],false,$job_data); ?>"><?php echo $todo_item['description']; ?></a>
                        </td>
                        <?php if($display_mode!='mobile'){ ?>
                        <td width="5%">
                            <?php echo $percentage*100;?>%
                        </td>
                        <td>
                            <?php echo module_job::link_open($todo_item['job_id'],true,$job_data);?>
                        </td>
                        <td width="16%">
                            <?php
                            $alert = process_alert($todo_item['date_due'],'temp');
                            ?>
                            <?php echo ($alert['warning']) ? '<span class="important">' : ''; ?>
                            <?php echo $alert['days']; ?>
                            <?php echo ($alert['warning']) ? '</span>' : ''; ?>
                        </td>
                        <?php } ?>
                        <td width="16%">
                            <?php echo ($alert['warning']) ? '<span class="important">' : ''; ?>
                            <?php echo print_date($alert['date']); ?>
                            <?php echo ($alert['warning']) ? '</span>' : ''; ?>
                        </td>
                    </tr>
                    <?php }
                }
                ?>
                </tbody>
             </table>
                 </div>

    <?php } ?>

        </td>
    </tr>
</table>

<?php } ?>

<?php
$calling_module='home';
handle_hook('dashboard',$calling_module);
?>

<!-- end page -->

<?php if(get_display_mode()=='mobile'){ ?>
<p>
    <a href="?display_mode=desktop"><?php _e('Switch to desktop mode');?></a>
</p>
<?php } ?>
</div>