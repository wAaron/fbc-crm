
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
// do we show the qty or not?
$show_qty = true;
$show_price = true;
$show_hourly = true;
$show_description = true;
$show_date = module_config::c('invoice_task_list_show_date',1);

$colspan = 0;
?>


<table cellpadding="4" cellspacing="0" width="100%" class="table tableclass tableclass_rows">
	<thead>
		<tr class="task_header">
			<th width="20px" align="center">
				#
			</th>
            <?php if($show_description){
                $colspan++;
                ?>
			<th align="left">
				<?php _e('Description');?>
			</th>
			<?php } ?>
            <?php if($show_date){
                $colspan++; ?>
			<th width="10%" align="center">
				<?php _e('Date');?>
			</th>
            <?php } ?>
            <?php if($show_qty){
                $colspan++; ?>
			<th width="10%" align="center">
                <?php echo module_config::c('task_hours_name',_l('Hours'));?>
			</th>
            <?php } ?>
            <?php if($show_hourly){
                $colspan++; ?>
			<th width="14%" align="center">
				<?php _e('Price');?>
			</th>
            <?php } ?>
			<th width="14%" align="right">
				<?php _e('Sub-Total');?>
			</th>
		</tr>
	</thead>
	<tbody>
	<?php
	$item_count = 0;// changed from 1
       foreach(module_invoice::get_invoice_items($invoice_id) as $invoice_item_id => $invoice_item_data){

/*
           $task_hourly = isset($invoice_item_data['hourly_rate']) && $invoice_item_data['hourly_rate']>0 ? $invoice_item_data['hourly_rate'] : $invoice['hourly_rate'];
           if(!$invoice_item_data['hours']){
               $task_hourly=0;
           }
           $invoice_item_amount = $invoice_item_data['amount'] > 0 ? $invoice_item_data['amount'] : $invoice_item_data['hours']*$task_hourly;
           if($invoice_item_data['amount']>0 && !$invoice_item_data['hours']){
               $invoice_item_amount = $invoice_item_data['amount'];
               $invoice_item_data['hours'] = 1;
               $task_hourly = $invoice_item_data['amount']; // not sure if this will be buggy
           }else{
               $invoice_item_amount = $invoice_item_data['hours']*$task_hourly;
           }
*/
           // copy any changes here to template/invoice_task_list.php and multisafepay
           $task_hourly_rate = isset($invoice_item_data['hourly_rate']) && $invoice_item_data['hourly_rate']!=0 ? $invoice_item_data['hourly_rate'] : $invoice['hourly_rate'];
           // if there are no hours logged against this task
           if(!$invoice_item_data['hours']){
               //$task_hourly_rate=0;
           }
           // if we have a custom price for this task
           if($invoice_item_data['amount']!=0 && $invoice_item_data['amount'] != ($invoice_item_data['hours']*$task_hourly_rate)){
               $invoice_item_amount = $invoice_item_data['amount'];
               if(module_config::c('invoice_calculate_item_price_auto',1) && $invoice_item_data['hours'] > 0){
                    $task_hourly_rate = round($invoice_item_amount/$invoice_item_data['hours'],module_config::c('currency_decimal_places',2));
                }else{
                    $task_hourly_rate = false;
                }
           }else if($invoice_item_data['hours']>0){
               $invoice_item_amount = $invoice_item_data['hours']*$task_hourly_rate;
           }else{
               $invoice_item_amount = 0;
               $task_hourly_rate = false;
           }
           /*$invoice_item_amount = $invoice_item_data['amount'] > 0 ? $invoice_item_data['amount'] : $invoice_item_data['hours']*$task_hourly_rate;
           if($invoice_item_data['amount']>0 && !$invoice_item_data['hours']){
               $invoice_item_amount = $invoice_item_data['amount'];
               $invoice_item_data['hours'] = 1;
               $task_hourly_rate = $invoice_item_data['amount']; // not sure if this will be buggy
           }else{
               $invoice_item_amount = $invoice_item_data['hours']*$task_hourly_rate;
           }*/

           // new feature, date done.
           if(isset($invoice_item_data['date_done']) && $invoice_item_data['date_done'] != '0000-00-00'){
               // $invoice_item_data['date_done'] is ok to print!
           }else{
               $invoice_item_data['date_done'] = '0000-00-00';
               // check if this is linked to a task.
               if($invoice_item_data['task_id']){
                   $task = get_single('task','task_id',$invoice_item_data['task_id']);
                   if($task && isset($task['date_done']) && $task['date_done'] != '0000-00-00'){
                       $invoice_item_data['date_done'] = $task['date_done']; // move it over ready for printing below
                   }else{
                       if(isset($invoice['date_create']) && $invoice['date_create'] != '0000-00-00'){
                           $invoice_item_data['date_done'] = $invoice['date_create'];
                       }
                   }
               }
           }

            ?>

                <tr class="<?php echo $item_count++%2 ? 'odd' : 'even';?>">
                    <td align="center">
                        <?php
                        if(isset($invoice_item_data['custom_task_order']) && (int)$invoice_item_data['custom_task_order']>0){
                            echo $invoice_item_data['custom_task_order'];
                        }else if(isset($invoice_item_data['task_order']) && $invoice_item_data['task_order']>0){
                            echo $invoice_item_data['task_order'];
                        }else{
                            echo $item_count;
                        }
                        ?>
                    </td>
                    <td>
                        <?php
                            echo $invoice_item_data['custom_description'] ? htmlspecialchars($invoice_item_data['custom_description']) : htmlspecialchars($invoice_item_data['description']);
                            if(module_config::c('invoice_show_long_desc',1)){
                                $long_description = trim($invoice_item_data['custom_long_description'] ? htmlspecialchars($invoice_item_data['custom_long_description']) : htmlspecialchars($invoice_item_data['long_description']));
                                if($long_description!=''){
                                    echo '<br/><em>'.forum_text($long_description).'</em>';
                                }
                            }
                        ?>
                    </td>
                    <?php if($show_date){  ?>
                    <td>
                        <?php if(isset($invoice_item_data['date_done']) && $invoice_item_data['date_done'] != '0000-00-00'){
                            echo print_date($invoice_item_data['date_done']);
                        }else{
                            // check if this is linked to a task.
                            if($invoice_item_data['task_id']){
                                $task = get_single('task','task_id',$invoice_item_data['task_id']);
                                if($task && isset($task['date_done']) && $task['date_done'] != '0000-00-00'){
                                    echo print_date($task['date_done']);
                                }else{
                                    // check if invoice has a date.
                                    if(isset($invoice['date_create']) && $invoice['date_create'] != '0000-00-00'){
                                        echo print_date($invoice['date_create']);
                                    }
                                }
                            }
                        }
                     ?>
                    </td>
                    <?php } ?>
                    <td>
                        <?php echo $invoice_item_data['hours']>0 ? $invoice_item_data['hours'] : '-';?>
                    </td>
                    <td>
                        <?php
                        //$rate = (isset($invoice_item_data['hourly_rate']) && $invoice_item_data['hourly_rate']>0) ? $invoice_item_data['hourly_rate'] : $invoice_data['hourly_rate'];
                        //echo dollar($rate,true,$invoice['currency_id']);
                        if($task_hourly_rate!=0){
                            echo dollar($task_hourly_rate,true,$invoice['currency_id']);
                        }else{
                            echo '-';
                        }
                        /*if($task_hourly>0){
                            echo dollar($task_hourly,true,$invoice['currency_id']);
                        }else{
                            echo '-';
                        }*/
                        ?>
                    </td>
                    <td align="right">
                        <?php
                        //echo $invoice_item_data['amount']>0 ? dollar($invoice_item_data['amount'],true,$invoice['currency_id']) : dollar($invoice_item_data['hours']*$rate,true,$invoice['currency_id']);
                        ?>
                        <?php
                        echo dollar($invoice_item_amount,true,$invoice['currency_id']);
                        ?>
                    </td>
                </tr>
        <?php } ?>
	</tbody>
<tfoot>

                        <tr>
                            <td colspan="<?php echo $colspan;?>">&nbsp;</td>
                        </tr>


                        <tr>
                            <td colspan="<?php echo $colspan;?>">
                                &nbsp;
                            </td>
                            <td>
                                <?php _e('Sub Total:');?>
                            </td>
                            <td align="right">
                                <?php echo dollar($invoice['total_sub_amount'],true,$invoice['currency_id']);?>
                            </td>
                        </tr>
                <?php if($invoice['discount_type']==1){ // after tax discount ?>
                        <?php if($invoice['total_tax_rate']>0 ){ ?>
                        <tr>
                            <td colspan="<?php echo $colspan;?>">
                                &nbsp;
                            </td>
                            <td>
                                <?php echo $invoice['total_tax_name'] ;?>
                                <?php echo $invoice['total_tax_rate'] . '%' ;?>
                            </td>
                            <td align="right">
                                <?php echo dollar($invoice['total_tax'],true,$invoice['currency_id']);?>
                            </td>
                        </tr>
                            <?php } ?>
                        <tr>
                            <td colspan="<?php echo $colspan;?>">
                                &nbsp;
                            </td>
                            <td>
                                <?php _e('Sub Total:');?>
                            </td>
                            <td align="right">
                                <?php echo dollar($invoice['total_sub_amount']+$invoice['total_tax'],true,$invoice['currency_id']);?>
                            </td>
                        </tr>
                        <?php if($invoice['discount_amount'] > 0){ ?>
                            <tr>
                                <td colspan="<?php echo $colspan;?>">
                                    &nbsp;
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($invoice['discount_description']);?>
                                </td>
                                <td align="right">
                                    <?php echo dollar($invoice['discount_amount'],true,$invoice['currency_id']);?>
                                </td>
                            </tr>
                        <?php } ?>
    <?php }else{ ?>
                        <?php if($invoice['discount_amount'] > 0){ ?>
                        <tr>
                            <td colspan="<?php echo $colspan;?>">
                                &nbsp;
                            </td>
                            <td>
                                <?php echo htmlspecialchars($invoice['discount_description']);?>
                            </td>
                            <td align="right">
                                <?php echo dollar($invoice['discount_amount'],true,$invoice['currency_id']);?>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="<?php echo $colspan;?>">
                                &nbsp;
                            </td>
                            <td>
                                <?php _e('Sub Total:');?>
                            </td>
                            <td align="right">
                                <?php echo dollar($invoice['total_sub_amount']-$invoice['discount_amount'],true,$invoice['currency_id']);?>
                            </td>
                        </tr>
                            <?php } ?>
                        <?php if($invoice['total_tax_rate']>0 ){ ?>
                            <tr>
                                <td colspan="<?php echo $colspan;?>">
                                    &nbsp;
                                </td>
                                <td>
                                    <?php echo $invoice['total_tax_name'] ;?>
                                    <?php echo $invoice['total_tax_rate'] . '%' ;?>
                                </td>
                                <td align="right">
                                    <?php echo dollar($invoice['total_tax'],true,$invoice['currency_id']);?>
                                </td>
                            </tr>
                            <?php } ?>
                        <?php } ?>

                        <tr>
                            <td colspan="<?php echo $colspan;?>">
                                &nbsp;
                            </td>
                            <td>
                                <?php _e('Total:');?>
                            </td>
                            <td align="right">
                                <span style="font-weight: bold;">
                                    <?php echo dollar($invoice['total_amount'],true,$invoice['currency_id']);?>
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="<?php echo $colspan+2;?>">&nbsp;</td>
                        </tr>
                        <tr>
                            <td colspan="<?php echo $colspan;?>" align="right">

                            </td>
                            <td>
                                <?php _e('Paid:');?>
                            </td>
                            <td align="right">
                                    <?php echo dollar($invoice['total_amount_paid'],true,$invoice['currency_id']);?>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="<?php echo $colspan;?>" align="right">

                            </td>
                            <td>
                                <?php _e('Due:');?>
                            </td>
                            <td align="right">
                                <span style="text-decoration: underline; font-weight: bold; color:#FF0000;">
                                    <?php echo dollar($invoice['total_amount_due'],true,$invoice['currency_id']);?>
                                </span>
                            </td>
                        </tr>
</tfoot>
</table>