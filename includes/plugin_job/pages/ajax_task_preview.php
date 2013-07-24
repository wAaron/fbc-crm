    <tr class="task_row_<?php 
/** 
  * Copyright: dtbaker 2012
  * Licence: Please check CodeCanyon.net for licence details. 
  * More licence clarification available here:  http://codecanyon.net/wiki/support/legal-terms/licensing-terms/ 
  * Deploy: 3053 c28b7e0e323fd2039bb168d857c941ee
  * Envato: 6b31bbe6-ead4-44a3-96e1-d5479d29505b
  * Package Date: 2013-02-27 19:23:35 
  * IP Address: 210.14.75.228
  */ echo $task_id;?> task_preview<?php echo $percentage>=1 ?' tasks_completed':'';?> <?php echo ($task_editable) ? ' task_editable' : '';?>" rel="<?php echo $task_id;?>">
        <?php if($show_task_numbers){ ?>
            <td valign="top" class="task_order task_drag_handle"><?php echo $task_data['task_order'];?></td>
        <?php } ?>
        <td valign="top">
            <?php
            if($task_data['approval_required']){
                echo '<span style="font-style: italic;" class="error_text">'._l('(approval required)').'</span> ';
            }
            if($task_editable){ // $task_editable ?>
                <a href="#" onclick="edittask(<?php echo $task_id;?>,0); return false;" class="<?php
                            // set color
                            if($percentage==1){
                                echo 'success_text';
                            }else if($percentage!=1 && $task_due_time < time()){
                                echo 'error_text';
                            }
                            ?>"><?php echo (!trim($task_data['description'])) ? 'N/A' : htmlspecialchars($task_data['description']);?></a>
<?php }else{ ?>
                    <span class="<?php
                            // set color
                            if($percentage==1){
                                echo 'success_text';
                            }else if($percentage!=1 && $task_due_time < time()){
                                echo 'error_text';
                            }
                            ?>"><?php echo (!trim($task_data['description'])) ? 'N/A' : htmlspecialchars($task_data['description']);?></span>
<?php }

               /*  <div style="z-index: 5; position: relative; min-height:18px; margin-bottom: -18px;"></div>
            <div class="task_percentage task_width"> */
           /* if(module_config::c('job_task_percentage',1) && ($percentage==1 || $task_data['hours']>0)){
                // work out the percentage.


                ?>
                    <div class="task_percentage_label task_width"><?php echo $percentage*100;?>%</div>
                    <div class="task_percentage_bar task_width" style="width:<?php echo round($percentage * $width);?>px;"></div>
                    <?php <div class="task_description">
                        <a href="#" onclick="edittask(<?php echo $task_id;?>,0); return false;" class="<?php
                            // set color
                            if($percentage==1){
                                echo 'success_text';
                            }else if($percentage!=1 && $task_due_time < time()){
                                echo 'error_text';
                            }
                            ?>"><?php echo (!trim($task_data['description'])) ? 'N/A' : htmlspecialchars($task_data['description']);?></a>
                    </div> ?>
            <?php }else{ ?>

            <?php } */
            /*</div>*/

            if(isset($task_data['long_description']) && $task_data['long_description'] != ''){ ?>
                <a href="#" class="task_toggle_long_description">&raquo;</a>
                <div class="task_long_description" <?php if(module_config::c('job_tasks_show_long_desc',0)){ ?> style="display:block;" <?php } ?>><?php echo forum_text(trim($task_data['long_description']));?></div>
            <?php }else{ ?>
                &nbsp;
            <?php }
            if(function_exists('hook_handle_callback'))hook_handle_callback('job_task_after',$task_data['job_id'],$task_data['task_id'],$job,$task_data);
            ?>
        </td>
        <td valign="top" class="task_drag_handle">
            <?php
            if($task_data['hours'] == 0 && $task_data['manual_task_type'] == _TASK_TYPE_AMOUNT_ONLY){
            // only amount, no hours or qty
            }else{
                // are the logged hours different to the billed hours?
                // are we completed too?
                if($percentage == 1 && $task_data['completed'] < $task_data['hours']){
                    echo '<span class="success_text">';
                    echo $task_data['hours']>0 ? $task_data['hours'] : '-';
                    echo '</span>';
                }else if($percentage == 1 && $task_data['completed'] > $task_data['hours']){
                    echo '<span class="error_text">';
                    echo $task_data['hours']>0 ? $task_data['hours'] : '-';
                    echo '</span>';
                }else{
                    echo $task_data['hours']>0 ? $task_data['hours'] : '-';
                }
            }

            ?>
        </td>
        <?php if(module_invoice::can_i('view','Invoices')){ ?>
        <td valign="top" class="task_drag_handle">
            <span class="currency <?php echo $task_data['billable'] ? 'success_text' : 'error_text';?>">
            <?php echo $task_data['amount']>0 ? dollar($task_data['amount'],true,$job['currency_id']) : dollar($task_data['hours']*$job['hourly_rate'],true,$job['currency_id']);?>
                <?php if($task_data['manual_task_type'] == _TASK_TYPE_QTY_AMOUNT){
                    $full_amount = $task_data['hours'] * $task_data['amount'];
                    if($full_amount != $task_data['amount']){
                        echo '<br/>('.dollar($full_amount,true,$job['currency_id']).')';
                    }
                } ?>
            </span>
        </td>
        <?php } ?>
        <?php if(module_config::c('job_show_due_date',1)){ ?>
        <td valign="top" class="task_drag_handle">
            <?php
            if($task_data['date_due'] && $task_data['date_due'] != '0000-00-00'){

                if($percentage!=1 && $task_due_time < time()){
                    echo '<span class="error_text">';
                    echo print_date($task_data['date_due']);
                    echo '</span>';
                }else{
                    echo print_date($task_data['date_due']);
                }
            }
            ?>
        </td>
        <?php } ?>
        <?php if(module_config::c('job_show_done_date',1)){ ?>
        <td valign="top" class="task_drag_handle">
            <?php
            if(isset($task_data['date_done']) && $task_data['date_done'] && $task_data['date_done'] != '0000-00-00'){
                echo print_date($task_data['date_done']);
            }
            ?>
        </td>
        <?php } ?>
        <?php if(module_config::c('job_allow_staff_assignment',1)){ ?>
            <td valign="top" class="task_drag_handle">
                <?php echo isset($staff_member_rel[$task_data['user_id']]) ? $staff_member_rel[$task_data['user_id']] : ''; ?>
            </td>
        <?php } ?>
        <td valign="top">
           <span class="<?php echo $percentage >= 1 ? 'success_text' : 'error_text';?>">
                <?php echo $percentage*100;?>%
            </span>
        </td>
        <td align="center" valign="top">
            <?php if($task_data['invoiced'] && $task_data['invoice_id']){
                if(module_invoice::can_i('view','Invoices')){
                    //$invoice = module_invoice::get_invoice($task_data['invoice_id']);
                    echo module_invoice::link_open($task_data['invoice_id'],true);
                }
                /*echo " ";
                echo '<span class="';
                if($invoice['total_amount_due']>0){
                    echo 'error_text';
                }else{
                    echo 'success_text';
                }
                echo '">';
                if($invoice['total_amount_due']>0){
                    echo dollar($invoice['total_amount_due'],true,$job['currency_id']);
                    echo ' '._l('due');
                }else{
                    echo _l('All paid');
                }
                echo '</span>';*/
            }else if($task_editable){ ?>
                <?php if(module_config::c('job_task_edit_icon',0)){ // old icon:  ?>
                <a href="#" class="ui-state-default ui-corner-all ui-icon ui-icon-<?php echo $percentage == 1 ? 'pencil' : 'check';?>" title="<?php _e( $percentage == 1 ? 'Edit' : 'Complete');?>" onclick="edittask(<?php echo $task_id;?>,<?php echo ($task_data['hours']>0?($task_data['hours']-$task_data['completed']):1);?>); return false;"><?php _e('Edit');?></a>
                <?php }else{ ?>
                    <input type="button" name="edit" value="<?php _e('Edit');?>" class="small_button" onclick="edittask(<?php echo $task_id;?>,<?php echo ($task_data['hours']>0?($task_data['hours']-$task_data['completed']):1);?>); return false;">
                <?php } ?>

            <?php } ?>
        </td>
    </tr>