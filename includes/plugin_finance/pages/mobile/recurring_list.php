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


if(!module_finance::can_i('view','Finance Upcoming')){
    redirect_browser(_BASE_HREF);
}

$module->page_title = 'Recurring';
$search = isset($_REQUEST['search']) ? $_REQUEST['search'] : array();
if(module_config::c('finance_recurring_show_finished',0)){
    $search['show_finished'] = true;
}
$balance=isset($_REQUEST['balance'])?(float)$_REQUEST['balance']:module_config::c('finance_recurring_start_balance',0);
module_config::save_config('finance_recurring_start_balance',$balance);

$upcoming_finances_unsorted = module_finance::get_recurrings($search);
$upcoming_finances = array();
$limit_timestamp = strtotime('+'.(int)module_config::c('finance_recurring_months',6).' months');
$duplicate_limit = 30;
$upcoming_finance_key = 0;
foreach($upcoming_finances_unsorted as $recurring){
    $time = strtotime($recurring['next_due_date']);
    $original=true;
    $count=0;
    while($time < $limit_timestamp){
        $next_time = 0;
        if($count++>$duplicate_limit)break;

        // we need a special case for the first one that hasn't had a last trasnaction

        // we need a specicl case for the last one that the due date is on the finish date.


        if($recurring['next_due_date'] == '0000-00-00' || (!$recurring['days']&&!$recurring['months']&&!$recurring['years'])){
            // it's a once off..
            // add it to the list but dont calculate the next one.
            
        }else if(!$original){
            // work out when the next one will be.
            $next_time = $time;
            $next_time = strtotime('+'.abs((int)$recurring['days']).' days',$next_time);
            $next_time = strtotime('+'.abs((int)$recurring['months']).' months',$next_time);
            $next_time = strtotime('+'.abs((int)$recurring['years']).' years',$next_time);
            $time = $next_time;
        }else{
            // it's the original one.
            $next_time = $time;
        }

        // make sure $time isn't past the recurring events normal time.
        $end_time = ($recurring['end_date'] && $recurring['end_date'] != '0000-00-00') ? strtotime($recurring['end_date']) : 0;
        if($end_time > 0 && $next_time > $end_time){
            // we've finished calculating the items here
            break;
        }else{
            // we have a recurring time item ready to add to the list.
            // modify the finance item and add it to our upcoming_finances listing.
        }
        $upcoming_finances[$upcoming_finance_key] = $recurring;
        if(!$original){
            // we have to modify the time in this item etc..
            $upcoming_finances[$upcoming_finance_key]['next_due_date'] = date('Y-m-d',$time);
            $upcoming_finances[$upcoming_finance_key]['last_transaction_finance_id'] = 0;
        }
        $original = false;
        if(!$next_time||!$time){
            break;
        }
        $upcoming_finance_key++;
    }
    $upcoming_finance_key++;
}
unset($upcoming_finances_unsorted);
// now we add any upcoming invoice payments to the finance listing.
// now we add any upcoming subscription payments to the finance listings.
if(function_exists('hook_handle_callback')){
    $others = hook_handle_callback('finance_recurring_list');
    if(is_array($others) && count($others)){
        foreach($others as $other){
            if(is_array($other) && count($other)){
                // this should be a list of compatible upcoming finance items.
                // these items wont have a "record" button
                // these items will have their own url to open them (ie: not take them to the normal recurring edit screen)
                foreach($other as $o){
                    $upcoming_finances[] = $o;
                }
            }
        }
    }
}
// sort finances by their next_due_date
function sort_recurring_finance($a,$b){
    $t1 = strtotime($a['next_due_date']);
    $t2 = strtotime($b['next_due_date']);
    return $t1>$t2;
}
uasort($upcoming_finances,'sort_recurring_finance');
?>

<script type="text/javascript">
    function set_starting_balance(){
        var balance = prompt('<?php _e('Please enter starting balance');?>',0);
        window.location.href = '<?php echo module_finance::link_open_recurring(false,false);?>?balance='+balance;
    }
</script>
<h2>
    <span class="button">
        <?php echo create_link("Add New","add",module_finance::link_open_recurring('new')); ?>
    </span>
    <?php echo _l('Upcoming Transactions for next %s months',(int)module_config::c('finance_recurring_months',6)); ?>
</h2>

<table width="100%" border="0" cellspacing="0" cellpadding="2" class="tableclass tableclass_rows">
    <thead>
    <tr class="title">
        <th><?php echo _l('Next Due Date'); ?></th>
        <th><?php echo _l('Name'); ?></th>
        <th><?php echo _l('Credit'); ?></th>
        <th><?php echo _l('Debit'); ?></th>
    </tr>
    </thead>
    <tbody>
    <?php
    $c=0;
    foreach($upcoming_finances as $recurring){
        $show_record_button = true;
        if(isset($recurring['url'])&&$recurring['url']){
            $show_record_button=false;
        }
        if(!$recurring['next_due_date'] || $recurring['next_due_date'] == '0000-00-00'){
            $show_record_button = false;
        }
        $days = 0;
        ?>
        <tr class="<?php echo ($c++%2)?"odd":"even"; ?>">
            <td>
                <?php if($show_record_button){ ?>
                <a href="<?php echo module_finance::link_open_record_recurring($recurring['finance_recurring_id']);?>">
                <?php } ?>
            <?php
                $next_due = strtotime($recurring['next_due_date']);
                if(!$recurring['next_due_date'] || $recurring['next_due_date'] == '0000-00-00'){
                    echo _l('(recurring finished)');
                }else if ($next_due < time()){
                    echo '<span class="important">';
                    echo print_date($recurring['next_due_date']);
                    echo '</span>';
                }else{
                    echo print_date($recurring['next_due_date']);
                }
                //if($show_record_button){
                    $days = ceil((($next_due+1) - time())/86400);
                    if(abs($days) == 0){
                        _e('(today)');
                    }else{
                        _e(' (%s days)',$days);
                    }
                //}


                ?>
                <?php if($show_record_button){ ?>
                </a>
                <?php } ?>
            </td>
            <td class="row_action">
                <?php echo isset($recurring['url'])&&$recurring['url'] ? $recurring['url'] : module_finance::link_open_recurring($recurring['finance_recurring_id'],true,$recurring);?>
            </td>
            <td>
                <?php if($recurring['type']=='i'){ $balance+=$recurring['amount']; ?><span class="success_text">+<?php echo dollar($recurring['amount'],true,$recurring['currency_id']); ?></span><?php } ?>
            </td>
            <td>
                <?php if($recurring['type']=='e'){ $balance-=$recurring['amount']; ?><span class="error_text">-<?php echo dollar($recurring['amount'],true,$recurring['currency_id']); ?></span><?php } ?>
            </td>
        </tr>
    <?php } ?>
  </tbody>
</table>

    <!-- end -->