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


if(!module_statistic::can_i('view','Staff Report')){
    redirect_browser(_BASE_HREF);
}


$page_title = _l('Staff Report');

$search = isset($_REQUEST['search']) ? $_REQUEST['search'] : array(
    'date_from' => print_date(date('Y-m-d',strtotime('-1 month'))),
    'date_to' => print_date(date('Y-m-d'))
);
$staff_reports = module_statistic::get_statistics_staff($search);



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
    <?php _e('Staff Report'); ?> (beta)
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
    <th id="sort_staff"><?php _e('Staff Member'); ?></th>
    <th id="sort_jobcount"><?php _e('Assigned Jobs'); ?></th>
    <th id="sort_taskcount"><?php _e('Assigned Tasks'); ?></th>
    <th id="sort_taskcomplete"><?php _e('Tasks Completed'); ?></th>
    <th id="sort_hourslogged"><?php _e('Hours Logged'); ?></th>
    <th id="sort_hoursbilled"><?php _e('Hours Billed'); ?></th>
    <th id="sort_amountbilled"><?php _e('Amount Billed'); ?></th>
    <th id="sort_amountinvoiced"><?php _e('Amount Invoiced'); ?></th>
</tr>
</thead>
<tbody>
<?php
$c=0;
$total=array(
    'job_count'=>0,
    'task_count'=>0,
    'tasks_complete'=>0,
    'hours_logged'=>0,
    'hours_billed'=>0,
    'amount_billed'=>0,
    'amount_invoiced'=>0,
);
foreach($staff_reports as $staff_report){
    $total['job_count']+=$staff_report['job_count'];
    $total['task_count']+=$staff_report['task_count'];
    $total['tasks_complete']+=$staff_report['tasks_complete'];
    $total['hours_logged']+=$staff_report['hours_logged'];
    $total['hours_billed']+=$staff_report['hours_billed'];
    $total['amount_billed']+=$staff_report['amount_billed'];
    $total['amount_invoiced']+=$staff_report['amount_invoiced'];
    ?>
    <tr class="<?php echo ($c++%2)?"odd":"even"; ?>">
        <td>
            <?php echo module_user::link_open($staff_report['user_id'],true);?>
        </td>
        <td>
            <?php echo $staff_report['job_count'];?>
        </td>
        <td>
            <?php echo $staff_report['task_count'];?>
        </td>
        <td>
            <?php echo $staff_report['tasks_complete'];?>
        </td>
        <td>
            <?php echo $staff_report['hours_logged'];?>
        </td>
        <td>
            <?php echo $staff_report['hours_billed'];?>
        </td>
        <td>
            <?php echo dollar($staff_report['amount_billed']);?>
        </td>
        <td>
            <?php echo dollar($staff_report['amount_invoiced']);?>
        </td>
    </tr>
    <?php } ?>
</tbody>
<tfoot>
<tr>
    <td>
        <strong><?php _e('Totals:'); ?></strong>
    </td>
    <td>
        <?php echo $total['job_count'];?>
    </td>
    <td>
        <?php echo $total['task_count'];?>
    </td>
    <td>
        <?php echo $total['tasks_complete'];?>
    </td>
    <td>
        <?php echo $total['hours_logged'];?>
    </td>
    <td>
        <?php echo $total['hours_billed'];?>
    </td>
    <td>
        <?php echo dollar($total['amount_billed']);?>
    </td>
    <td>
        <?php echo dollar($total['amount_invoiced']);?>
    </td>
</tr>
</tfoot>
</table>

<ul>
    <li>Staff Members: users who have the "Job Tasks" - "edit" permission</li>
    <li>Assigned Jobs: number of jobs where job "Create Date" is within range and staff members is assigned to overall job</li>
    <li>Assigned Tasks: number of individual job tasks where job "Create Date" is within range and staff members is assigned to individual job task</li>
    <li>Tasks Completed: number of tasks that were assigned to this staff member and have a "done date" within specified range</li>
    <li>Hours Logged: how many hours were logged against all tasks (complete or not) within date range. Staff can log more (or less) hours than task was quoted.</li>
    <li>Hours Billed: how many hours were logged against all completed tasks.</li>
    <li>Amount Billed: value of the "hours billed" </li>
    <li>Hours Invoiced: if the completed task was invoiced, include amount here</li>
</ul>