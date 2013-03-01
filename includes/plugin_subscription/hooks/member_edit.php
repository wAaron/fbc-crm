<h3><?php 
/** 
  * Copyright: dtbaker 2012
  * Licence: Please check CodeCanyon.net for licence details. 
  * More licence clarification available here:  http://codecanyon.net/wiki/support/legal-terms/licensing-terms/ 
  * Deploy: 3053 c28b7e0e323fd2039bb168d857c941ee
  * Envato: 6b31bbe6-ead4-44a3-96e1-d5479d29505b
  * Package Date: 2013-02-27 19:09:56 
  * IP Address: 
  */ echo _l('Subscriptions &amp; Payments'); ?></h3>

<table width="100%" border="0" cellspacing="0" cellpadding="2" class="tableclass tableclass_form">
    <tbody>
    <tr>
        <th class="width1">
            <?php echo _l('Subscriptions'); ?>
        </th>
        <td>
            <?php if (module_subscription::can_i('edit','Subscriptions')){ ?>
            <input type="hidden" name="member_subscriptions_save" value="1">
            <input type="hidden" name="subscription_add_payment" value="" id="subscription_add_payment">
            <input type="hidden" name="subscription_add_payment_date" value="" id="subscription_add_payment_date">
            <input type="hidden" name="subscription_add_payment_amount" value="" id="subscription_add_payment_amount">
            <?php
            }

            $next_due_time = time();

            global $members_subscriptions;
            if($customer_hack){
                $members_subscriptions = module_subscription::get_subscriptions_by_customer($member_id);
            }else{
                $members_subscriptions = module_subscription::get_subscriptions_by_member($member_id);
            }

            $sorted_subscriptions = module_subscription::get_subscriptions();
            if(!function_exists('sort_subscriptions')){
                function sort_subscriptions($a,$b){
                    global $members_subscriptions;
                    if(isset($members_subscriptions[$a['subscription_id']]) && isset($members_subscriptions[$b['subscription_id']])){
                        return 0;
                    }else if(isset($members_subscriptions[$a['subscription_id']]) && !isset($members_subscriptions[$b['subscription_id']])){
                        return -1;
                    }else{
                        return 1;
                    }
                }
            }
            uasort($sorted_subscriptions,'sort_subscriptions');

            foreach($sorted_subscriptions as $subscription){
                if(!module_subscription::can_i('edit','Subscriptions') && !isset($members_subscriptions[$subscription['subscription_id']]))continue;
                ?>
                <div class="subscription<?php echo isset($members_subscriptions[$subscription['subscription_id']])?' active':'';?>">
                <input type="checkbox" name="subscription[<?php echo $subscription['subscription_id'];?>]" value="1" id="subscription_<?php echo $subscription['subscription_id'];?>" <?php if(isset($members_subscriptions[$subscription['subscription_id']])) echo 'checked';?>>
                    <label for="subscription_<?php echo $subscription['subscription_id'];?>"><?php echo htmlspecialchars($subscription['name']);?></label> - <?php echo dollar($subscription['amount']);

                if(!$subscription['days']&&!$subscription['months']&&!$subscription['years']){
                    //echo _l('Once off');
                }else{
                    $bits = array();
                    if($subscription['days']>0){
                        $bits[] = _l('%s days',$subscription['days']);
                    }
                    if($subscription['months']>0){
                        $bits[] = _l('%s months',$subscription['months']);
                    }
                    if($subscription['years']>0){
                        $bits[] = _l('%s years',$subscription['years']);
                    }
                    echo ' ';
                    echo _l('Every %s',implode(', ',$bits));
                }
                if(isset($members_subscriptions[$subscription['subscription_id']])){
                    echo _l(' starting from ');
                    ?>
                    <input type="text" name="subscription_start_date[<?php echo $subscription['subscription_id'];?>]" value="<?php echo print_date($members_subscriptions[$subscription['subscription_id']]['start_date']);?>" class="date_field">
                    <?php
                }
                ?>  <br/>
                    <?php


                // and if it is active, when the next one is due.
                if(isset($members_subscriptions[$subscription['subscription_id']])){
                    if($members_subscriptions[$subscription['subscription_id']]['next_due_date'] && $members_subscriptions[$subscription['subscription_id']]['next_due_date'] != '0000-00-00'){
                        echo '<strong>';
                        _e('Next due date is: %s',print_date($members_subscriptions[$subscription['subscription_id']]['next_due_date']));
                        echo '</strong>';
                        $next_due_time = strtotime($members_subscriptions[$subscription['subscription_id']]['next_due_date']);

                        $days = ceil(($next_due_time - time())/86400);
                        if ($next_due_time < time()){
                            echo ' <span class="important">';
                            if(abs($days)==0){
                                _e('DUE TODAY');
                            }else{
                                _e('OVERDUE');
                            }
                            echo '</span> ';
                        }else{
                            //echo print_date($recurring['next_due_date']);
                        }
                        if(abs($days) == 0){
                            //_e('(today)');
                        }else{
                            _e(' (in %s days)',$days);
                        }

                        echo '<br/>';
                    }
                }

                $invoice_history_html = '';

                // we have to look up the history for this subscription and show the last payment made,
                if($customer_hack){
                    $history = module_subscription::get_subscription_history($subscription['subscription_id'],false,$member_id);
                }else{
                    $history = module_subscription::get_subscription_history($subscription['subscription_id'],$member_id,false);
                }
                $next_due_time_invoice_created = false;
                if(count($history)>0){
                    foreach($history as $h){
                        if(!$h['invoice_id']){
                            $invoice_history_html .= 'ERROR! NO invoice id specified for subscription history. Please report this bug.';
                        }else{
                            $invoice_data = module_invoice::get_invoice($h['invoice_id']);
                            if($invoice_data['date_cancel']!='0000-00-00')continue;
                            if(print_date($next_due_time) == print_date($invoice_data['date_create'])){
                                // this invoice is for the next due date.
                                $next_due_time_invoice_created = $invoice_data;
                            }
                            $invoice_history_html .= '<li>';
                            $invoice_history_html .= _l('Invoice #%s for %s on %s (paid on %s)',
                                module_invoice::link_open($h['invoice_id'],true,$invoice_data),
                                dollar($invoice_data['total_amount'],true,$invoice_data['currency_id']),
                                print_date($invoice_data['date_create']),
                                $invoice_data['date_paid']!='0000-00-00' ? print_date($invoice_data['date_paid']) : '<span class="important">'._l('UNPAID').'</span>'
                            );
                            $invoice_history_html .= '</li>';
                        }
                    }
                }


                if(isset($members_subscriptions[$subscription['subscription_id']]) && module_security::is_page_editable()){
                    //echo '<li>';
                    if($next_due_time_invoice_created){
                        _e('The next invoice has been created for %s. Please mark it as paid.','<a href="'.module_invoice::link_open($next_due_time_invoice_created['invoice_id'],false,$next_due_time_invoice_created).'">'.print_date($next_due_time).'</a>');
                        echo ' <a href="#" onclick="$(\'#next_invoice_'.$subscription['subscription_id'].'\').show(); $(this).hide();">New</a>';;
                        echo '<span id="next_invoice_'.$subscription['subscription_id'].'" style="display:none;"><br/>';
                    }
                        ?>
                        New Invoice for <?php echo currency('');?><input type="text" name="foo" id="amount_<?php echo $subscription['subscription_id'];?>" value="<?php echo $subscription['amount'];?>" class="currency">
                        dated <input type="text" name="foo" id="date_<?php echo $subscription['subscription_id'];?>" value="<?php echo print_date($next_due_time);?>" class="date_field">
                        <input type="button" name="gen_invoice" value="<?php _e('Create Invoice');?>" onclick="$('#subscription_add_payment').val(<?php echo $subscription['subscription_id'];?>); $('#subscription_add_payment_amount').val($('#amount_<?php echo $subscription['subscription_id'];?>').val()); $('#subscription_add_payment_date').val($('#date_<?php echo $subscription['subscription_id'];?>').val()); this.form.submit();" class="submit_small">
                        <?php

                    if($next_due_time_invoice_created){
                        echo '</span>';
                    }
                    //echo '</li>';
                }
                echo '<ul>';
                echo $invoice_history_html;
                echo '</ul>';

                // todo - handle if one of these invoices has been deleted.
                // remove the payment, and remove the subscriptino history entry.


                ?>
                </div>

            <?php } ?>
        </td>
    </tr>
    </tbody>
</table>