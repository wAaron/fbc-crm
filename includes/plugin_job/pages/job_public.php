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
$job = $job_data; // hack

$colspan = 2;
?>

<script type="text/javascript">

    $(function(){
        $('.task_toggle_long_description').click(function(event){
            event.preventDefault();
            $(this).parent().find('.task_long_description').slideToggle();
            return false;
        });
    });
</script>
                    <table border="0" cellspacing="0" cellpadding="2" class="tableclass tableclass_rows tableclass_full">
                        <thead>
                        <tr>
                            <?php if(module_config::c('job_show_task_numbers',1)){
                            ?>
                            <th width="10">#</th>
                            <?php } ?>
                            <th class="task_column task_width"><?php _e('Description');?></th>
                            <th><?php echo module_config::c('task_hours_name',_l('Hours'));?></th>
                            <th><?php _e('Amount');?></th>
                            <?php if(module_config::c('job_show_due_date',1)){
                            $colspan++; ?>
                            <th nowrap="nowrap"><?php _e('Due Date');?></th>
                            <?php } ?>
                            <?php if(module_config::c('job_show_done_date',1)){
                            $colspan++; ?>
                            <th nowrap="nowrap"><?php _e('Done Date');?></th>
                            <?php } ?>
                            <th nowrap="nowrap">%</th>
                            <th nowrap="nowrap"><?php _e('Invoiced');?></th>
                        </tr>
                        </thead>
                        <?php
                        $c=0;
                        foreach(module_job::get_tasks($job_id) as $task_id => $task_data){
                            $c++;
                            // todo-move this into a method so we can update it via ajax.
                            $percentage = module_job::get_percentage($task_data);
                            $task_due_time = strtotime($task_data['date_due']);
                            ?>

                            <tbody id="task_preview_<?php echo $task_id;?>" class="task_preview">
                                <tr class="<?php echo $c%2 ? 'odd' : 'even';?>">
                                    <?php if(module_config::c('job_show_task_numbers',1)){ ?>
                                    <td valign="top">
                                        <?php echo $task_data['task_order'];?>
                                    </td>
                                    <?php } ?>
                                    <td class="task_width" valign="top">
                                        <?php

                                        if($task_data['approval_required']){
                                            echo '<span style="font-style: italic;" class="error_text">'._l('(approval required)').'</span> ';
                                        }
                                        ?>
                                        <?php echo (!trim($task_data['description'])) ? 'N/A' : htmlspecialchars($task_data['description']);?>
                                        <?php /*
                                        <div style="z-index: 5; position: relative; min-height:18px; margin-bottom: -18px;">
                                            <?php echo (!trim($task_data['description'])) ? 'N/A' : htmlspecialchars($task_data['description']);?>
                                        </div>
                                        <div class="task_percentage task_width">
                                            <?php
                                            if(module_config::c('job_task_percentage',1) && ($percentage==1 || $task_data['hours']>0)){
                                            // work out the percentage.

                                            ?>
                                                <div class="task_percentage_label task_width"><?php echo $percentage*100;?>%</div>
                                                <div class="task_percentage_bar task_width" style="width:<?php echo round($percentage * $width);?>px;"></div>
                                                 <div class="task_description">
                                                    <?php echo (!trim($task_data['description'])) ? 'N/A' : htmlspecialchars($task_data['description']);?>
                                                </div>
                                            <?php }else{ ?>

                                            <?php } ?>
                                        </div> */ ?>
                                        <?php if(isset($task_data['long_description']) && $task_data['long_description'] != ''){ ?>
                                            <a href="#" class="task_toggle_long_description">&raquo;</a>
                                        <div class="task_long_description" <?php if(module_config::c('job_tasks_show_long_desc',0)){ ?> style="display:block;" <?php } ?>><?php echo forum_text(trim($task_data['long_description']));?></div>
                                        <?php }else{ ?>
                                            &nbsp;
                                        <?php }
                                        if(function_exists('hook_handle_callback') && !isset($ignore_task_hook))hook_handle_callback('job_task_after',$task_data['job_id'],$task_data['task_id'],$job,$task_data);
                                        ?>
                                    </td>
                                    <td valign="top">
                                        <?php echo $task_data['hours']>0 ? $task_data['hours'] : '-';?>
                                    </td>
                                    <td valign="top">
                                        <span class="currency <?php echo $task_data['billable'] ? 'success_text' : 'error_text';?>">
                                        <?php echo $task_data['amount']>0 ? dollar($task_data['amount'],true,$job['currency_id']) : dollar($task_data['hours']*$job['hourly_rate'],true,$job['currency_id']);?>
                                        </span>
                                    </td>
                                    <?php if(module_config::c('job_show_due_date',1)){ ?>
                                    <td valign="top">
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
                                    <td valign="top">
                                        <?php
                                        if($task_data['date_done'] && $task_data['date_done'] != '0000-00-00'){
                                            echo print_date($task_data['date_done']);
                                        }
                                        ?>
                                    </td>
                                    <?php } ?>
                                    <td valign="top">
                                        <span class="<?php echo $percentage >= 1 ? 'success_text' : 'error_text';?>">
                                            <?php echo $percentage*100;?>%
                                        </span>
                                    </td>
                                    <td align="center" valign="top">
                                        <?php if($task_data['invoiced'] && $task_data['invoice_id']){
                                            $invoice = module_invoice::get_invoice($task_data['invoice_id']);
                                            ?> <a href="<?php echo module_invoice::link_public($invoice['invoice_id']);?>"><?php echo $invoice['name'];?></a> <?php 
                                        }else{
                                            if($percentage>=1){
                                                echo '<span class="success_text">'._l('Done').'</span>';
                                            }else{
                                                echo _l('N/A');
                                            }
                                        } ?>
                                    </td>
                                </tr>
                            </tbody>
                        <?php } ?>
                        <?php if((int)$job_id>0){ ?>
                        <tfoot style="border-top:1px solid #CCC;">

                            <?php if($job['total_sub_amount_unbillable']){
                                ?>
                                <tr>
                                    <?php if(module_config::c('job_show_task_numbers',1)){ ?>
                                        <td rowspan="2">&nbsp;</td>
                                    <?php } ?>
                                    <td>
                                        &nbsp;
                                    </td>
                                    <td>
                                        <?php _e('Sub Total:');?>
                                    </td>
                                    <td>
                                        <span class="currency">
                                        <?php echo dollar($job['total_sub_amount']+$job['total_sub_amount_unbillable'],true,$job['currency_id']);?>
                                        </span>
                                    </td>
                                    <td colspan="<?php echo $colspan;?>">
                                        &nbsp;
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        &nbsp;
                                    </td>
                                    <td>
                                        <?php _e('Unbillable:');?>
                                    </td>
                                    <td>
                                        <span class="currency">
                                        <?php echo dollar($job['total_sub_amount_unbillable'],true,$job['currency_id']);?>
                                        </span>
                                    </td>
                                    <td colspan="<?php echo $colspan;?>">
                                        &nbsp;
                                    </td>
                                </tr>
                        <?php } ?>
                        <tr>
                            <td colspan="2">
                                <?php echo _l('%s Hours Total',$job['total_hours']);?>
                            </td>
                            <td>
                                <?php _e('Sub Total:');?>
                            </td>
                            <td>
                                <span class="currency">
                                <?php echo dollar($job['total_sub_amount'],true,$job['currency_id']);?>
                                </span>
                            </td>
                            <td colspan="<?php echo $colspan;?>">
                                &nbsp;
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2">
                                <?php echo _l('%s Hours Done',$job['total_hours_completed']);?>
                                <?php if($job['total_amount_invoicable'] > 0){ ?>
                                <span class="success_text">(<?php echo _l('Pending %s Invoice',dollar($job['total_amount_invoicable'],true,$job['currency_id']));?>)</span>
                                <?php } ?>
                            </td>
                            <td>
                                <?php _e('Tax:');?>
                            </td>
                            <td>
                                <span class="currency">
                                <?php echo dollar($job['total_tax'],true,$job['currency_id']);?>
                                </span>
                            </td>
                            <td colspan="<?php echo $colspan;?>">
                                <?php echo $job['total_tax_name'] ;?> =
                                <?php echo $job['total_tax_rate'] . '%' ;?>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2">
                                <?php echo _l('%s Hours / %s Tasks Remain',$job['total_hours_remain'],$job['total_tasks_remain']);?>
                                <?php if($job['total_amount_todo']>0){ ?>
                                <span class="error_text">
                                    (<?php echo dollar($job['total_amount_todo'],true,$job['currency_id']);?>)
                                </span>
                                <?php } ?>
                            </td>
                            <td>
                                <?php _e('Total:');?>
                            </td>
                            <td>
                                <span class="currency" style="text-decoration: underline; font-weight: bold;">
                                    <?php echo dollar($job['total_amount'], true,$job['currency_id']);?>
                                </span>
                            </td>
                            <td colspan="<?php echo $colspan;?>">
                                &nbsp;
                            </td>
                        </tr>
                        <tr>
                            <td colspan="8">&nbsp;</td>
                        </tr>



                        <tr>
                            <td colspan="2" align="right">

                            </td>
                            <td>
                                <?php _e('Invoiced:');?>
                            </td>
                            <td>
                                <span class="currency">
                                    <?php echo dollar($job['total_amount_invoiced'],true,$job['currency_id']);?>
                                </span>
                            </td>
                            <td colspan="<?php echo $colspan;?>">
                                &nbsp;
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2" align="right">

                            </td>
                            <td>
                                <?php _e('Paid:');?>
                            </td>
                            <td>
                                <span class="currency success_text">
                                    <?php echo dollar($job['total_amount_paid'],true,$job['currency_id']);?>
                                </span>
                            </td>
                            <td colspan="<?php echo $colspan;?>">
                                &nbsp;
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2" align="right">

                            </td>
                            <td>
                                <?php _e('Unpaid:');?>
                            </td>
                            <td>
                                <span class="currency error_text">
                                    <?php echo dollar($job['total_amount_outstanding'],true,$job['currency_id']);?>
                                </span>
                            </td>
                            <td colspan="<?php echo $colspan;?>">
                                &nbsp;
                            </td>
                        </tr>
                        </tfoot>
<?php } ?>
					</table>