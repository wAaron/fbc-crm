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
if(!$invoice_safe)die('failed');

$search = (isset($_REQUEST['search']) && is_array($_REQUEST['search'])) ? $_REQUEST['search'] : array();
if(isset($_REQUEST['customer_id'])){
    $search['customer_id'] = $_REQUEST['customer_id'];
}
$invoices = module_invoice::get_invoices($search);
$all_invoice_ids = array();
foreach($invoices as $invoice){
    $all_invoice_ids[]=$invoice['invoice_id'];
}

if(class_exists('module_table_sort',false)){

    // get full invoice data.
    // todo: only grab data if we're sorting by something
    // that isn't in the default invoice listing.
    foreach($invoices as $invoice_id=>$invoice){
        $invoices[$invoice_id] = module_invoice::get_invoice($invoice['invoice_id']);
    }

    module_table_sort::enable_pagination_hook(
    // pass in the sortable options.
        array(
            'table_id' => 'invoice_list',
            'sortable'=>array(
                // these are the "ID" values of the <th> in our table.
                // we use jquery to add the up/down arrows after page loads.
                'invoice_number' => array(
                    'field' => 'name',
                ),
                'invoice_status' => array(
                    'field' => 'status',
                ),
                'invoice_create_date' => array(
                    'field' => 'date_create',
                    'current' => 2, // 1 asc, 2 desc
                ),
                'invoice_due_date' => array(
                    'field' => 'date_due',
                ),
                'invoice_sent_date' => array(
                    'field' => 'date_sent',
                ),
                'invoice_paid_date' => array(
                    'field' => 'date_paid',
                ),

                /*'invoice_website' => array(
                    'field' => 'website_name',
                ),
                'invoice_job' => array(
                    'field' => 'job_name',
                ),*/
                'invoice_customer' => array(
                    'field' => 'customer_name',
                ),

                'invoice_total' => array(
                    'field' => 'total_amount',
                ),
                'invoice_total_due' => array(
                    'field' => 'total_amount_due',
                ),

            ),
        )
    );
}
?>

<h2>
    <?php if(module_invoice::can_i('create','Invoices')){ ?>
	<span class="button">
		<?php echo create_link("New Manual Invoice","add",module_invoice::link_open('new')); ?>
	</span>
    <?php } ?>
	<?php echo _l('Invoices'); ?>
</h2>

<form action="" method="post">


<table class="search_bar">
	<tr>
		<th><?php _e('Filter By:'); ?></th>
		<td class="search_title">
			<?php echo _l('Invoice Number:');?>
		</td>
		<td class="search_input">
			<input type="text" name="search[generic]" value="<?php echo isset($search['generic'])?htmlspecialchars($search['generic']):''; ?>" size="30" style="width:100px;">
		</td>
		<td class="search_title">
			<?php echo _l('Create Date:');?>
		</td>
		<td class="search_input">
			<input type="text" name="search[date_from]" value="<?php echo isset($search['date_from'])?htmlspecialchars($search['date_from']):''; ?>" class="date_field">
            <?php _e('to');?>
			<input type="text" name="search[date_to]" value="<?php echo isset($search['date_to'])?htmlspecialchars($search['date_to']):''; ?>" class="date_field">
		</td>
		<td class="search_title">
			<?php echo _l('Status:');?>
		</td>
		<td class="search_input">
			<?php echo print_select_box(module_invoice::get_statuses(),'search[status]',isset($search['status'])?$search['status']:''); ?>
		</td>
        <?php if(!isset($_REQUEST['customer_id']) && class_exists('module_group',false) && module_customer::can_i('view','Customer Groups')){ ?>
        <td class="search_title">
            <?php _e('Customer Group:');?>
        </td>
        <td class="search_input">
            <?php echo print_select_box(module_group::get_groups('customer'),'search[customer_group_id]',isset($search['customer_group_id'])?$search['customer_group_id']:false,'',true,'name'); ?>
        </td>
        <?php } ?>
		<td class="search_action">
			<?php echo create_link("Reset","reset",module_invoice::link_open(false)); ?>
			<?php echo create_link("Search","submit"); ?>
		</td>
	</tr>
</table>

<?php
$pagination = process_pagination($invoices,20,0,'invoices');
$colspan = 4;
?>

<?php echo $pagination['summary'];?>

<table width="100%" border="0" cellspacing="0" cellpadding="2" class="tableclass tableclass_rows">
	<thead>
	<tr class="title">
		<th id="invoice_number"><?php echo _l('Invoice Number'); ?></th>
		<th id="invoice_status"><?php echo _l('Status'); ?></th>
		<th id="invoice_create_date"><?php echo _l('Create Date'); ?></th>
		<th id="invoice_due_date"><?php echo _l('Due Date'); ?></th>
		<th id="invoice_sent_date"><?php echo _l('Sent Date'); ?></th>
		<th id="invoice_paid_date"><?php echo _l('Paid Date'); ?></th>
		<th id="invoice_website"><?php echo _l(module_config::c('project_name_single','Website')); ?></th>
		<th id="invoice_job"><?php echo _l('Job'); ?></th>
        <?php if(!isset($_REQUEST['customer_id'])){ ?>
		<th id="invoice_customer"><?php echo _l('Customer'); ?></th>
        <?php } ?>
		<th id="invoice_total"><?php echo _l('Invoice Total'); ?></th>
		<th id="invoice_total_due"><?php echo _l('Amount Due'); ?></th>
    </tr>
    </thead>
    <tbody>
		<?php
		$c=0;
		foreach($pagination['rows'] as $invoice){
            $invoice = module_invoice::get_invoice($invoice['invoice_id']);
            ?>
            <tr class="<?php echo ($c++%2)?"odd":"even"; ?>">
                <td class="row_action">
                    <?php echo module_invoice::link_open($invoice['invoice_id'],true,$invoice);?>
                </td>
                <td>
                    <?php echo htmlspecialchars($invoice['status']); ?>
                </td>
                <td>
                    <?php
                    if((!$invoice['date_create']||$invoice['date_create']=='0000-00-00')){
                        //echo print_date($invoice['date_created']);
                    }else{
                        echo print_date($invoice['date_create']);
                    }
                    ?>
                </td>
                <td>
                    <?php
                    if((!$invoice['date_paid']||$invoice['date_paid']=='0000-00-00') && strtotime($invoice['date_due']) < time()){
                        echo '<span class="error_text">';
                        echo print_date($invoice['date_due']);
                        echo '</span>';
                    }else{
                        echo print_date($invoice['date_due']);
                    }
                    ?>
                </td>
                <td>
                    <?php if($invoice['date_sent'] && $invoice['date_sent'] != '0000-00-00'){ ?>
                        <?php echo print_date($invoice['date_sent']);?>
                    <?php }else{ ?>
                        <span class="error_text"><?php _e('Not sent');?></span>
                    <?php } ?>
                </td>
                <td>
                    <?php if($invoice['date_paid'] && $invoice['date_paid'] != '0000-00-00'){ ?>
                        <?php echo print_date($invoice['date_paid']);?>
                    <?php }else if(($invoice['date_cancel'] && $invoice['date_cancel']!='0000-00-00')){ ?>
                        <span class="error_text"><?php _e('Cancelled');?></span>
                    <?php }else if(($invoice['date_due'] && $invoice['date_due']!='0000-00-00') && (!$invoice['date_paid'] || $invoice['date_paid'] == '0000-00-00') && strtotime($invoice['date_due']) < time()){ ?>
                        <span class="error_text" style="font-weight: bold; text-decoration: underline;"><?php _e('Overdue');?></span>
                    <?php }else{ ?>
                        <span class="error_text"><?php _e('Not paid');?></span>
                    <?php } ?>
                </td>
                <td>
                    <?php
                    foreach($invoice['website_ids'] as $website_id){
                        if((int)$website_id>0){
                            echo module_website::link_open($website_id,true);
                            echo '<br/>';
                        }
                    }
                    ?>
                </td>
                <td>
                    <?php
                    foreach($invoice['job_ids'] as $job_id){
                        if((int)$job_id>0){
                            echo module_job::link_open($job_id,true);
                            $job_data = module_job::get_job($job_id);
                            if($job_data['date_start'] && $job_data['date_start']!='0000-00-00' && $job_data['date_renew'] && $job_data['date_renew']!='0000-00-00' ){
                                _e(' (%s to %s)',print_date($job_data['date_start']),print_date(strtotime("-1 day",strtotime($job_data['date_renew']))));
                            }
                            echo "<br/>\n";

                        }
                    }
                    hook_handle_callback('invoice_admin_list_job',$invoice['invoice_id']);
                    ?>
                </td>
                <?php if(!isset($_REQUEST['customer_id'])){ ?>
                <td>
                    <?php echo module_customer::link_open($invoice['customer_id'],true);?>
                </td>
                <?php } ?>
                <td>
                    <?php echo dollar($invoice['total_amount'],true,$invoice['currency_id']);?>
                </td>
                <td>
                    <?php echo dollar($invoice['total_amount_due'],true,$invoice['currency_id']);?>
                    <?php if($invoice['total_amount_credit'] > 0){ ?>
                    <span class="success_text"><?php echo _l('Credit: %s',dollar($invoice['total_amount_credit'],true,$invoice['currency_id']));?></span>
                        <?php
                    } ?>
                </td>
            </tr>
		<?php } ?>
	</tbody>
</table>
    <?php echo $pagination['links'];?>
</form>

<?php if(function_exists('convert_html2pdf') && get_display_mode() != 'mobile'){ ?>
    <form action="<?php echo module_invoice::link_generate($invoice_id,array('arguments'=>array('print'=>1)));?>" method="post">
        <input type="hidden" name="invoice_ids" value="<?php echo implode(",",$all_invoice_ids);?>">
        <input type="submit" name="butt_print" value="<?php echo _l('Export all results as PDF'); ?>" class="submit_button" />
    </form>
<?php } ?>