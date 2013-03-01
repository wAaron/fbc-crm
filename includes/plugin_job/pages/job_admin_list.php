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

if(!$job_safe)die('denied');

$search = (isset($_REQUEST['search']) && is_array($_REQUEST['search'])) ? $_REQUEST['search'] : array();
if(isset($_REQUEST['customer_id'])){
    $search['customer_id'] = $_REQUEST['customer_id'];
}
if(!isset($search['completed'])){
    $search['completed'] = module_config::c('job_search_completed_default',1);
}
$jobs = module_job::get_jobs($search);

if(class_exists('module_table_sort',false)){

    // get full job data.
    // todo: only grab data if we're sorting by something
    // that isn't in the default invoice listing.
    foreach($jobs as $job_id=>$job){
        $jobs[$job_id] = array_merge($job,module_job::get_job($job['job_id']));
        $jobs[$job_id]['website_name'] = $job['website_name'];
    }

    module_table_sort::enable_pagination_hook(
    // pass in the sortable options.
        array(
            'table_id' => 'job_list',
            'sortable'=>array(
                // these are the "ID" values of the <th> in our table.
                // we use jquery to add the up/down arrows after page loads.
                'job_title' => array(
                    'field' => 'name',
                    'current' => 1, // 1 asc, 2 desc
                ),
                'job_start_date' => array(
                    'field' => 'date_start',
                ),
                'job_due_date' => array(
                    'field' => 'date_due',
                ),
                'job_completed_date' => array(
                    'field' => 'date_completed',
                ),
                'job_website' => array(
                    'field' => 'website_name',
                ),
                'job_customer' => array(
                    'field' => 'customer_name',
                ),
                'job_type' => array(
                    'field' => 'type',
                ),
                'job_status' => array(
                    'field' => 'status',
                ),
                'job_progress' => array(
                    'field' => 'total_percent_complete',
                ),
                'job_total' => array(
                    'field' => 'total_amount',
                ),
                'job_total_amount_invoiced' => array(
                    'field' => 'total_amount_invoiced',
                ),
                // special case for group sorting.
                'job_group' => array(
                    'group_sort' => true,
                    'owner_table' => 'job',
                    'owner_id' => 'job_id',
                ),
            ),
        )
    );
}

// hack to add a "export" option to the pagination results.
if(class_exists('module_import_export',false) && module_job::can_i('view','Export Jobs')){
    module_import_export::enable_pagination_hook(
        // what fields do we pass to the import_export module from this customers?
        array(
            'name' => 'Job Export',
            'fields'=>array(
                'Job ID' => 'job_id',
                'Job Title' => 'name',
                'Hourly Rate' => 'hourly_rate',
                'Start Date' => 'date_start',
                'Due Date' => 'date_due',
                'Completed Date' => 'date_completed',
                module_config::c('project_name_single','Website').' Name' => 'website_name',
                'Customer Name' => 'customer_name',
                'Type' => 'type',
                'Status' => 'status',
                'Staff Member' => 'staff_member',
                'Tax Name' => 'total_tax_name',
                'Tax Percent' => 'total_tax_rate',
                'Renewal Date' => 'date_renew',
            ),
            // do we look for extra fields?
            'extra' => array(
                'owner_table' => 'job',
                'owner_id' => 'job_id',
            ),
        )
    );
}
?>

<h2>
    <?php if(module_job::can_i('create','Jobs')){ ?>
	<span class="button">
		<?php echo create_link("Add New job","add",module_job::link_open('new')); ?>
	</span>
    <?php } ?>
    <?php if(class_exists('module_import_export',false) && module_job::can_i('view','Import Jobs')){
        $link = module_import_export::import_link(
            array(
                'callback'=>'module_job::handle_import',
                'callback_preview'=>'module_job::handle_import_row_debug',
                'name'=>'Jobs',
                'return_url'=>$_SERVER['REQUEST_URI'],
                'group'=>'job',
                'fields'=>array(
                    //'Job ID' => 'job_id',
                    'Job Title' => 'name',
                    'Hourly Rate' => 'hourly_rate',
                    'Start Date' => 'date_start',
                    'Due Date' => 'date_due',
                    'Completed Date' => 'date_completed',
                    module_config::c('project_name_single','Website').' Name' => 'website_name',
                    'Customer Name' => 'customer_name',
                    'Type' => 'type',
                    'Status' => 'status',
                    'Staff Member' => 'staff_member',
                    'Tax Name' => 'total_tax_name',
                    'Tax Percent' => 'total_tax_rate',
                    'Renewal Date' => 'date_renew',
                ),
                // do we attempt to import extra fields?
                'extra' => array(
                    'owner_table' => 'job',
                    'owner_id' => 'job_id',
                ),
            )
        );
        ?>
        <span class="button">
            <?php echo create_link("Import Jobs","add",$link); ?>
        </span>
        <?php
    } ?>
	<?php echo _l('Customer Jobs'); ?>
</h2>

<form action="" method="post">


<table class="search_bar">
	<tr>
		<th><?php _e('Filter By:'); ?></th>
		<td class="search_title">
			<?php echo _l('Job Title:');?>
		</td>
		<td class="search_input">
			<input type="text" name="search[generic]" value="<?php echo isset($search['generic'])?htmlspecialchars($search['generic']):''; ?>" size="30">
		</td>
        <td class="search_title">
            <?php _e('Type:');?>
        </td>
        <td class="search_input">
            <?php echo print_select_box(module_job::get_types(),'search[type]',isset($search['type'])?$search['type']:''); ?>
        </td>
		<td class="search_title">
			<?php echo _l('Status:');?>
		</td>
		<td class="search_input">
			<?php echo print_select_box(module_job::get_statuses(),'search[status]',isset($search['status'])?$search['status']:''); ?>
		</td>
		<td class="search_title">
			<?php echo _l('Completed:');?>
		</td>
		<td class="search_input">
			<?php echo print_select_box(array(
                1=>_l('Both Completed and Non-Completed Jobs'),
                2=>_l('Only Completed Jobs'),
                3=>_l('Only Non-Completed Jobs'),
                4=>_l('Only Quoted Jobs'),
            ),'search[completed]',isset($search['completed'])?$search['completed']:''); ?>
		</td>
		<td class="search_action">
			<?php echo create_link("Reset","reset",module_job::link_open(false)); ?>
			<?php echo create_link("Search","submit"); ?>
		</td>
	</tr>
</table>

<?php
$pagination = process_pagination($jobs);
$colspan = 4;
?>

<?php echo $pagination['summary'];?>

<table width="100%" border="0" cellspacing="0" cellpadding="2" class="tableclass tableclass_rows">
	<thead>
	<tr class="title">
		<th id="job_title"><?php echo _l('Job Title'); ?></th>
		<th id="job_start_date"><?php echo _l('Date'); ?></th>
		<th id="job_due_date"><?php echo _l('Due Date'); ?></th>
		<th id="job_completed_date"><?php echo _l('Completed Date'); ?></th>
		<th id="job_website"><?php echo _l(module_config::c('project_name_single','Website')); ?></th>
        <?php if(!isset($_REQUEST['customer_id'])){ ?>
		<th id="job_customer"><?php echo _l('Customer'); ?></th>
        <?php } ?>
		<th id="job_type"><?php echo _l('Type'); ?></th>
		<th id="job_status"><?php echo _l('Status'); ?></th>
        <?php if(module_config::c('job_allow_staff_assignment',1)){ ?>
		<th id="job_staff"><?php echo _l('Staff Member'); ?></th>
        <?php  } ?>
		<th id="job_progress"><?php echo _l('Progress'); ?></th>
        <?php if(module_invoice::can_i('view','Invoices')){ ?>
		<th id="job_total"><?php echo _l('Job Total'); ?></th>
		<th id="job_total_amount_invoiced"><?php echo _l('Invoice'); ?></th>
        <?php } ?>
        <?php if(class_exists('module_group',false)){ ?>
        <th id="job_group"><?php echo _l('Group'); ?></th>
        <?php } ?>
         <?php if(class_exists('module_extra',false)){
        module_extra::print_table_header('job');
        } ?>
    </tr>
    </thead>
    <tbody>
		<?php
		$c=0;
		foreach($pagination['rows'] as $job_original){
//            print_r(array_keys($job_original));
            //echo $job_original['website_name'];
            $job = module_job::get_job($job_original['job_id']);
            ?>
            <tr class="<?php echo ($c++%2)?"odd":"even"; ?>">
                <td class="row_action">
                    <?php echo module_job::link_open($job['job_id'],true);?>
                </td>
                <td>
                    <?php
                    echo print_date($job['date_start']);
                        //is there a renewal date?
                        if(isset($job['date_renew']) && $job['date_renew'] && $job['date_renew'] != '0000-00-00'){
                            _e(' to %s',print_date(strtotime("-1 day",strtotime($job['date_renew']))));
                        }
                    ?>
                </td>
                <td>
                    <?php
                    if($job['total_percent_complete']!=1 && strtotime($job['date_due']) < time()){
                        echo '<span class="error_text">';
                        echo print_date($job['date_due']);
                        echo '</span>';
                    }else{
                        echo print_date($job['date_due']);
                    }
                    ?>
                </td>
                <td>
                    <?php echo print_date($job['date_completed']);?>
                </td>
                <td>
                    <?php  echo module_website::link_open($job['website_id'],true);?>
                </td>
                <?php if(!isset($_REQUEST['customer_id'])){ ?>
                <td>
                    <?php echo module_customer::link_open($job['customer_id'],true);?>
                </td>
                <?php } ?>
                <td>
                    <?php echo htmlspecialchars($job['type']);?>
                </td>
                <td>
                    <?php echo htmlspecialchars($job['status']);?>
                </td>
                <?php if(module_config::c('job_allow_staff_assignment',1)){ ?>
                <td>
                    <?php
                    echo module_user::link_open($job_original['user_id'],true);
                    //echo htmlspecialchars($job_original['staff_member']);
                    ?>
                </td>
                <?php } ?>
                <td>
                    <span class="<?php
                        echo $job['total_percent_complete'] >= 1 ? 'success_text' : '';
                        ?>">
                        <?php echo ($job['total_percent_complete']*100).'%';?>
                    </span>
                </td>
                <?php if(module_invoice::can_i('view','Invoices')){ ?>
                <td>
                    <span class="currency">
                    <?php echo dollar($job['total_amount'],true,$job['currency_id']);?>
                    </span>
                    <?php
                    if($job['total_amount_invoiced'] > 0 && $job['total_amount'] != $job['total_amount_invoiced']){ ?>
                        <br/>
                        <span class="currency">
                        (<?php echo dollar($job['total_amount_invoiced'],true,$job['currency_id']);?>)
                        </span>
                    <?php } ?>
                </td>
                <td>
                    <?php
                    foreach(module_invoice::get_invoices(array('job_id'=>$job['job_id'])) as $invoice){
                        $invoice = module_invoice::get_invoice($invoice['invoice_id']);
                        echo module_invoice::link_open($invoice['invoice_id'],true);
                        echo " ";
                        echo '<span class="';
                        if($invoice['total_amount_due']>0){
                            echo 'error_text';
                        }else{
                            echo 'success_text';
                        }
                        echo '">';
                        if($invoice['total_amount_due']>0){
                            echo dollar($invoice['total_amount_due'],true,$invoice['currency_id']);
                            echo ' '._l('due');
                        }else{
                            echo _l('%s paid',dollar($invoice['total_amount'],true,$invoice['currency_id']));
                        }
                        echo '</span>';
                        echo "<br>";
                    }  ?>
                </td>
                <?php } ?>
                <?php if(class_exists('module_group',false)){ ?>
                <td><?php
                    // find the groups for this customer.
                    $groups = module_group::get_groups_search(array(
                        'owner_table' => 'job',
                        'owner_id' => $job['job_id'],
                    ));
                    $g=array();
                    foreach($groups as $group){
                        $g[] = $group['name'];
                    }
                    echo implode(', ',$g);
                    ?></td>
                <?php } ?>
                <?php if(class_exists('module_extra',false)){
            module_extra::print_table_data('job',$job['job_id']);
            } ?>
            </tr>
		<?php } ?>
	</tbody>
</table>
    <?php echo $pagination['links'];?>
</form>