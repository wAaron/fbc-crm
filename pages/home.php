
<div class="final_content_wrap">
<h2><?php 
/** 
  * Copyright: dtbaker 2012
  * Licence: Please check CodeCanyon.net for licence details. 
  * More licence clarification available here:  http://codecanyon.net/wiki/support/legal-terms/licensing-terms/ 
  * Deploy: 3053 c28b7e0e323fd2039bb168d857c941ee
  * Envato: 6b31bbe6-ead4-44a3-96e1-d5479d29505b
  * Package Date: 2013-02-27 19:23:35 
  * IP Address: 210.14.75.228
  */ echo _l('Home Page'); ?></h2>
<?php if(false && _DEMO_MODE){ ?>
    <div style="float:right; padding:5px; font-style: italic;">
        <strong>UCM Demonstration Website</strong> <br/> This is a demo website for the <span style="text-decoration: underline;">Ultimate Client Manager</span>. <br/>This demo resets every now and then. <br/>The Ultimate Client Manager can be downloaded from CodeCanyon. <br/><a href="http://codecanyon.net/item/ultimate-client-manager-lite-edition/47626?ref=dtbaker">Please click here for more details</a>.
    </div>
<?php } ?>
<p>
    <?php echo _l('Hi %s, and Welcome to %s', htmlspecialchars($_SESSION['_user_name']), module_config::s('admin_system_name')); ?>
</p>

<?php if(module_config::c('dashboard_new_layout',1) && class_exists('module_dashboard',false)){

    module_dashboard::output_dashboard_alerts();

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

<?php if(get_display_mode()=='mobile'){ ?>
<!-- end page -->
<p>
    <a href="?display_mode=desktop"><?php _e('Switch to desktop mode');?></a>
</p>
<?php } ?>
</div>