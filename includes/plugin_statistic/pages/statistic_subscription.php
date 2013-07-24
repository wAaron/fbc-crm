<?php 
/** 
  * Copyright: dtbaker 2012
  * Licence: Please check CodeCanyon.net for licence details. 
  * More licence clarification available here:  http://codecanyon.net/wiki/support/legal-terms/licensing-terms/ 
  * Deploy: 3053 c28b7e0e323fd2039bb168d857c941ee
  * Envato: 6b31bbe6-ead4-44a3-96e1-d5479d29505b
  * Package Date: 2013-02-27 19:23:35 
  * IP Address: 210.14.75.228
  */



if(!class_exists('module_subscription',false) || !module_statistic::can_i('view','Staff Report')){
    redirect_browser(_BASE_HREF);
}


$page_title = _l('Staff Report');

$search = isset($_REQUEST['search']) ? $_REQUEST['search'] : array(
    'date_from' => print_date(date('Y-m-d',strtotime('-1 month'))),
    'date_to' => print_date(date('Y-m-d'))
);
$subscription_reports = module_statistic::get_statistics_subscription($search);



if(class_exists('module_table_sort',false)){
    module_table_sort::enable_pagination_hook(
    // pass in the sortable options.
    /*="sort_date"><?php _e('Date'); ?></th>
                    <th id="sort_name"><?php _e('Name'); ?></th>
                    <th><?php _e('Description'); ?></th>
                    <th id="sort_credit"><?php _e('Credit'); ?></th>
                    <th id="sort_debit"><?php _e('Debit'); ?></th>
                    <th id="sort_account"><?p*/
        array(
            'table_id' => 'statistic_list',
            'sortable'=>array(
                // these are the "ID" values of the <th> in our table.
                // we use jquery to add the up/down arrows after page loads.
                'sort_date' => array(
                    'field' => 'transaction_date',
                    'current' => 2, // 1 asc, 2 desc
                ),
                'sort_name' => array(
                    'field' => 'name',
                ),
                'sort_credit' => array(
                    'field' => 'credit',
                ),
                'sort_debit' => array(
                    'field' => 'debit',
                ),
            ),
        )
    );
}
/*

// hack to add a "export" option to the pagination results.
if(class_exists('module_import_export',false) && module_statistic::can_i('view','Export Statistic')){
    module_import_export::enable_pagination_hook(
    // what fields do we pass to the import_export module from this customers?
        array(
            'name' => 'Statistic Export',
            'parent_form' => 'statistic_form',
            'fields'=>array(
                'Date' => 'transaction_date',
                'Name' => 'name',
                'URL' => 'url',
                'Description' => 'description',
                'Credit' => 'credit',
                'Debit' => 'debit',
                'Account' => 'account_name',
                'Categories' => 'categories',
            ),
        )
    );
}
*/

?>




<h2>
    <?php _e('Subscription Report'); ?> (beta)
</h2>


<form action="" method="post" id="statistic_form">

    <table class="search_bar" width="100%">
        <tr>
            <th><?php _e('Filter By:'); ?></th>
            <td class="search_title">
                <?php _e('Date:');?>
            </td>
            <td class="search_input">
                <input type="text" name="search[date_from]" value="<?php echo isset($search['date_from'])?htmlspecialchars($search['date_from']):''; ?>" class="date_field">
                <?php _e('to');?>
                <input type="text" name="search[date_to]" value="<?php echo isset($search['date_to'])?htmlspecialchars($search['date_to']):''; ?>" class="date_field">
            </td>
            <td class="search_action">
                <?php echo create_link("Search","submit"); ?>
            </td>
        </tr>
    </table>
</form>


<table width="100%" border="0" cellspacing="0" cellpadding="2" class="tableclass tableclass_rows">
<thead>
<tr class="title">
    <th id="sort_subscription"><?php _e('Subscription Name'); ?></th>
    <th id="sort_total"><?php _e('Total'); ?></th>
    <th id="sort_totalreceived"><?php _e('Invoices Paid'); ?></th>
    <th id="sort_totalunpaid"><?php _e('Invoices Unpaid'); ?></th>
    <th id="sort_membercount"><?php _e('Allocated Members'); ?></th>
    <th id="sort_customercount"><?php _e('Allocated Customers'); ?></th>
</tr>
</thead>
<tbody>
<?php
$c=0;
$total_total = array(0,0);
$total_received = array(0,0);
$total_unpaid = array(0,0);
$total_members = 0;
$total_customers = 0;
foreach($subscription_reports as $subscription_report){
    ?>
    <tr class="<?php echo ($c++%2)?"odd":"even"; ?>">
        <td>
            <?php echo module_subscription::link_open($subscription_report['subscription_id'],true,$subscription_report);?>
        </td>
        <td>
            <?php
            $total_total[0] += ($subscription_report['total_received_count']+$subscription_report['total_unpaid_count']);
            $total_total[1] += ($subscription_report['total_received']+$subscription_report['total_unpaid']); // todo - multicurrency
            echo $subscription_report['total_received_count']+$subscription_report['total_unpaid_count'];?> =
            <?php echo dollar($subscription_report['total_received']+$subscription_report['total_unpaid'],true,$subscription_report['currency_id']);?>
        </td>
        <td>
            <?php
            $total_received[0] += ($subscription_report['total_received_count']);
            $total_received[1] += ($subscription_report['total_received']); // todo - multicurrency
            echo $subscription_report['total_received_count'];?> =
            <?php echo dollar($subscription_report['total_received'],true,$subscription_report['currency_id']);?>
        </td>
        <td>
            <?php
            $total_unpaid[0] += ($subscription_report['total_unpaid_count']);
            $total_unpaid[1] += ($subscription_report['total_unpaid']); // todo - multicurrency
            echo $subscription_report['total_unpaid_count'];?> =
            <?php echo dollar($subscription_report['total_unpaid'],true,$subscription_report['currency_id']);?>
        </td>
        <td>
            <?php
            $total_members+=count($subscription_report['members']);
            echo count($subscription_report['members']);?> <br/>
            <ul>
                <?php foreach($subscription_report['members'] as $member_id => $member_data){ ?>
                    <li>
                        <?php echo module_member::link_open($member_id,true);?> (<?php echo $member_data['received_payments'] .' = '. dollar($member_data['received_total']);?>)
                        <?php if($member_data['unpaid_payments']>0){ ?>
                            <strong><?php echo $member_data['unpaid_payments'];?> UNPAID! = <?php echo dollar($member_data['unpaid_total']);?></strong>
                        <?php } ?>
                    </li>
                <?php } ?>
            </ul>
        </td>
        <td>
            <?php
            $total_customers+=count($subscription_report['customers']);
            echo count($subscription_report['customers']);?> <br/>
            <ul>
                <?php foreach($subscription_report['customers'] as $customer_id => $customer_data){ ?>
                <li>
                    <?php echo module_customer::link_open($customer_id,true);?> (<?php echo $customer_data['received_payments'] .' = '. dollar($customer_data['received_total']);?>)
                    <?php if($customer_data['unpaid_payments']>0){ ?>
                    <strong><?php echo $customer_data['unpaid_payments'];?> UNPAID! = <?php echo dollar($customer_data['unpaid_total']);?></strong>
                    <?php } ?>
                </li>
                <?php } ?>
            </ul>
        </td>
    </tr>
    <?php } ?>
    <tr>
        <td>
            <?php _e('Total:'); ?>
        </td>
        <td>
            <?php echo $total_total[0] .' = ' .dollar($total_total[1]); ?>
        </td>
        <td>
            <?php echo $total_received[0] .' = ' .dollar($total_received[1]); ?>
        </td>
        <td>
            <?php echo $total_unpaid[0] .' = ' .dollar($total_unpaid[1]); ?>
        </td>
        <td>
            <?php echo $total_members;?>
        </td>
        <td>
            <?php echo $total_customers;?>
        </td>
    </tr>
</tbody>
</table>
