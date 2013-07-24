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


if(!module_customer::can_i('view','Customers')){
    redirect_browser(_BASE_HREF);
}


$search = isset($_REQUEST['search']) ? $_REQUEST['search'] : array();
$customers = module_customer::get_customers($search);
// hack to add a "group" option to the pagination results.
if(class_exists('module_group',false)){
    module_group::enable_pagination_hook(
        // what fields do we pass to the group module from this customers?
        array(
            'fields'=>array(
                'owner_id' => 'customer_id',
                'owner_table' => 'customer',
                'title' => 'Customer Groups',
                'name' => 'customer_name',
                'email' => 'primary_user_email'
            ),
        )
    );
}
if(class_exists('module_table_sort',false)){
    module_table_sort::enable_pagination_hook(
        // pass in the sortable options.
        array(
            'table_id' => 'customer_list',
            'sortable'=>array(
                // these are the "ID" values of the <th> in our table.
                // we use jquery to add the up/down arrows after page loads.
                'customer_name' => array(
                    'field' => 'customer_name',
                    'current' => 1, // 1 asc, 2 desc
                ),
                'primary_contact_name' => array(
                    'field' => 'primary_user_name',
                ),
                'primary_contact_email' => array(
                    'field' => 'primary_user_email',
                ),
                // special case for group sorting.
                'customer_group' => array(
                    'group_sort' => true,
                    'owner_table' => 'customer',
                    'owner_id' => 'customer_id',
                ),
            ),
        )
    );
}
// hack to add a "export" option to the pagination results.
if(class_exists('module_import_export',false) && module_customer::can_i('view','Export Customers')){
    module_import_export::enable_pagination_hook(
        // what fields do we pass to the import_export module from this customers?
        array(
            'name' => 'Customer Export',
            'fields'=>array(
                'Customer ID' => 'customer_id',
                'Customer Name' => 'customer_name',
            	'Customer NO' => 'customer_no',
            	'Customer Type' => 'customer_type',
            	'Customer From' => 'customer_from',
            	'Cooperate From' => 'cooperate_from',
            	'Main Products' => 'customer_main_prod',
            		'Pay in Days' => 'customer_pay_days',
            		'Pay Period' => 'customer_pay_period',
            		'Success Stories' => 'customer_success_story',
            		'Full Name' => 'customer_full_name',
            		'English Name' => 'customer_full_en',
            		'Customer Company Type' => 'customer_company_type',
            		'Customer Level' => 'customer_level',
            		'Customer Staff' => 'customer_staff',
            		'Company Size' => 'company_size',
            		'Customer Build From' => 'customer_build_from',
            		'Customer is VIP' => 'customer_vip',
            		'Customer VIP End' => 'customer_vip_end',
            		'Customer VIP Renew' => 'customer_vip_renew',
            		'Customer Ticket Type' => 'customer_ticket_type',
            		'Customer Ticket Info' => 'customer_ticket_info',
            		'Translation Speed' => 'translate_speed',
            		'Translation Speed Unit' => 'translate_speed_unit',
                'Credit' => 'credit',
                'Address Line 1' => 'line_1',
                'Address Line 2' => 'line_2',
                'Address Suburb' => 'suburb',
                'Address Country' => 'country',
                'Address State' => 'state',
                'Address Region' => 'region',
                'Address Post Code' => 'post_code',
                'Primary Contact First Name' => 'primary_user_name',
                'Primary Contact Last Name' => 'primary_user_last_name',
                'Primary Phone' => 'primary_user_phone',
                'Primary Email' => 'primary_user_email'
            ),
            // do we look for extra fields?
            'extra' => array(
                'owner_table' => 'customer',
                'owner_id' => 'customer_id',
            ),
            'group' => array(
                array(
                    'title' => 'Customer Group',
                    'owner_table' => 'customer',
                    'owner_id' => 'customer_id',
                )
            ),
        )
    );
}
$pagination = process_pagination($customers);

?>

<h2>
    <?php if(module_customer::can_i('create','Customers')){ ?>
	<span class="button">
		<?php echo create_link("Create New Customer","add",module_customer::link_open('new')); ?>
	</span>
    <?php
    }
    if(class_exists('module_import_export',false) && module_customer::can_i('view','Import Customers')){
        $link = module_import_export::import_link(
            array(
                'callback'=>'module_customer::handle_import',
                'name'=>'Customers',
                'return_url'=>$_SERVER['REQUEST_URI'],
                'group'=>'customer',
                'fields'=>array(
                    'Customer ID' => 'customer_id',
                    'Customer Name' => 'customer_name',
                		'Customer NO' => 'customer_no',
                		'Customer Type' => 'customer_type',
                		'Customer From' => 'customer_from',
                		'Cooperate From' => 'cooperate_from',
                		'Main Products' => 'customer_main_prod',
                		'Pay in Days' => 'customer_pay_days',
                		'Pay Period' => 'customer_pay_period',
                		'Success Stories' => 'customer_success_story',
                		'Full Name' => 'customer_full_name',
                		'English Name' => 'customer_full_en',
                		'Customer Company Type' => 'customer_company_type',
                		'Customer Level' => 'customer_level',
                		'Customer Staff' => 'customer_staff',
                		'Company Size' => 'company_size',
                		'Customer Build From' => 'customer_build_from',
                		'Customer is VIP' => 'customer_vip',
                		'Customer VIP End' => 'customer_vip_end',
                		'Customer VIP Renew' => 'customer_vip_renew',
                		'Customer Ticket Type' => 'customer_ticket_type',
                		'Customer Ticket Info' => 'customer_ticket_info',
                		'Translation Speed' => 'translate_speed',
                		'Translation Speed Unit' => 'translate_speed_unit',
                    'Credit' => 'credit',
                    'Address Line 1' => 'line_1',
                    'Address Line 2' => 'line_2',
                    'Address Suburb' => 'suburb',
                    'Address Country' => 'country',
                    'Address State' => 'state',
                    'Address Region' => 'region',
                    'Address Post Code' => 'post_code',
                    'Primary Contact First Name' => 'primary_user_name',
                    'Primary Contact Last Name' => 'primary_user_last_name',
                    'Primary Phone' => 'primary_user_phone',
                    'Primary Email' => 'primary_user_email',
                    'Password' => 'password',
                    'User Role Name' => 'role',
                ),
                // do we try to import extra fields?
                'extra' => array(
                    'owner_table' => 'customer',
                    'owner_id' => 'customer_id',
                ),
            )
        );
        ?>
        <span class="button">
            <?php echo create_link("Import Customers","add",$link); ?>
        </span>
        <?php
    }
    if(module_user::can_i('view','All Customer Contacts','Customer','customer')){
    ?>
	<span class="button">
		<?php echo create_link("View All Contacts","link",module_user::link_open_contact(false)); ?>
	</span>
    <?php } ?>
	<span class="title">
		<?php echo _l('Customers'); ?>
	</span>
</h2>


<form action="#" method="post">

<table class="search_bar" border="0">
	<tr>
		<th rowspan="2"><?php _e('Filter By:'); ?></th>
        <td class="search_title">
            <?php _e('Customer NO:');?>
        </td>
        <td class="search_input">
            <input type="text" style="width: 90px;" name="search[customer_no]" class="" value="<?php echo isset($search['customer_no'])?htmlspecialchars($search['customer_no']):''; ?>">
        </td>
		<td class="search_title">
			<?php _e('Names, Phone or Email:');?>
		</td>
		<td class="search_input">
			<input type="text" style="width: 190px;" name="search[generic]" class="" value="<?php echo isset($search['generic'])?htmlspecialchars($search['generic']):''; ?>">
		</td>
		<td class="search_title">
            <?php _e('Address:');?>
		</td>
		<td class="search_input">
			<input type="text" style="width: 290px;" name="search[address]" class="" value="<?php echo isset($search['address'])?htmlspecialchars($search['address']):''; ?>">
		</td>
		<td class="search_action" rowspan="2">
			<?php echo create_link("Reset","reset",module_customer::link_open(false)); ?>
			<?php echo create_link("Search","submit"); ?>
		</td>
	</tr>
	<tr>
        <?php if(class_exists('module_group',false) && module_customer::can_i('view','Customer Groups')){ ?>
        <td class="search_title">
            <?php _e('Group:');?>
        </td>
        <td class="search_input">
            <?php echo print_select_box(module_group::get_groups('customer'),'search[group_id]',isset($search['group_id'])?$search['group_id']:false,'',true,'name'); ?>
        </td>
        <?php } ?>
        <td class="search_title">
            <?php _e('Completed?');?>:
        </td>
        <td class="search_input">
         <?php
         echo print_select_box(array('yes'=>"涓讳俊鎭凡瀹屾垚", 'no'=>"涓讳俊鎭湭瀹屾垚"), "search[core_completed]", isset($search['core_completed'])?$search['core_completed']:false);
         echo print_select_box(array('yes'=>"鍏ㄩ儴淇℃伅瀹屾垚", 'no'=>"鏈叏閮ㄥ畬鎴�), "search[full_completed]", isset($search['full_completed'])?$search['full_completed']:false);
         ?>
        </td>
        <td class="search_title">
            <?php _e('Customer Active');?>:
        </td>
			<td class="search_input"><input type="hidden" name="search[customer_active]" value="{{ng_customer_active}}" />
			<select ng-model="ng_customer_active"
				ng-options="key as value for (key , value) in active_types" ng-init="ng_customer_active='<?php echo isset($search['customer_active'])?$search['customer_active']:''; ?>'">
					<option value="">- Select -</option>
			</select>
			</td>
		</tr>

</table>

<?php echo $pagination['summary'];?>
<table width="100%" border="0" cellspacing="0" cellpadding="2" class="tableclass tableclass_rows">
	<thead>
	<tr class="title">
		<th id="customer_active" style="width:5px"></th>
        <th id="customer_no"><?php echo _l('Customer NO'); ?></th>
		<th id="customer_name"><?php echo _l('Customer Name'); ?></th>
		<th id="primary_contact_name"><?php echo _l('Primary Contact'); ?></th>
		<th><?php echo _l('Phone Number'); ?></th>
		<th id="primary_contact_email"><?php echo _l('Email Address'); ?></th>
		<th width="30%"><?php echo _l('Address'); ?></th>
        <?php if(class_exists('module_group',false) && module_customer::can_i('view','Customer Groups')){ ?>
        <th id="customer_group"><?php echo _l('Group'); ?></th>
        <?php } ?>
        <?php if(class_exists('module_invoice',false) && module_invoice::can_i('view','Invoices')){ ?>
        <th id="customer_invoices"><?php echo _l('Invoices'); ?></th>
        <?php } ?>
        <?php if(class_exists('module_extra',false)){
        module_extra::print_table_header('customer');
        } ?>
        <th><?php echo _l('Completed?'); ?></th>
    </tr>
    </thead>
    <tbody>
    <?php
	$c=0;
	foreach($pagination['rows'] as $customer){
        module_debug::log(array('title'=>'row'));
        
        ?>
        <tr class="<?php echo ($c++%2)?"odd":"even"; ?>">
            <td class="bg_active_<?php echo $customer['customer_active']; ?>">
                
            </td>
            <td>
                <?php echo $customer['customer_no']; ?>
            </td>

            <td class="row_action">
	            <?php echo module_customer::link_open($customer['customer_id'],true); ?>
            </td>
            <td>
				<?php
				if($customer['primary_user_id']){
					echo module_user::link_open_contact($customer['primary_user_id'],true);
				}else{
					echo '';
				}
				?>
            </td>
            <td>
				<?php
				if($customer['primary_user_id']){
					module_user::print_contact_summary($customer['primary_user_id'],'html',array('phone|mobile'));
				}else{
					echo '';
				}
				?>
            </td>
            <td>
				<?php
				if($customer['primary_user_id']){
					module_user::print_contact_summary($customer['primary_user_id'],'html',array('email'));
				}else{
					echo '';
				}
				?>
            </td>
            <td>
                <?php module_address::print_address($customer['customer_id'],'customer','physical'); ?>
            </td>
            <?php if(class_exists('module_group',false) && module_customer::can_i('view','Customer Groups')){ ?>
            <td><?php

                    if(isset($customer['group_sort_customer'])){
                        echo htmlspecialchars($customer['group_sort_customer']);
                    }else{
                        // find the groups for this customer.
                        $groups = module_group::get_groups_search(array(
                                                                      'owner_table' => 'customer',
                                                                      'owner_id' => $customer['customer_id'],
                                                                  ));
                        $g=array();
                        foreach($groups as $group){
                            $g[] = $group['name'];
                        }
                        echo htmlspecialchars(implode(', ',$g));
                    }
                ?></td>
            <?php } ?>
        <?php if(class_exists('module_invoice',false) && module_invoice::can_i('view','Invoices') && module_config::c('customer_list_show_invoices',1)){
                ?>
            <td>
                <?php
                $invoices = module_invoice::get_invoices(array('customer_id'=>$customer['customer_id']));
                if(count($invoices)){
                    $total_due = 0;
                    $total_paid = 0;
                    foreach($invoices as $invoice){
                        $invoice = module_invoice::get_invoice($invoice['invoice_id']);
                        $total_due += $invoice['total_amount_due'];
                        $total_paid += $invoice['total_amount_paid'];
                    }
                    $_REQUEST['customer_id'] = $customer['customer_id'];
                    echo '<a href="'.module_invoice::link_open(false).'">'._l('%s invoice%s: %s',count($invoices),count($invoices)>1?'s':'',
                        (
                            $total_due>0
                                ?
                            '<span class="error_text">'._l('%s due',dollar($total_due,true,$invoice['currency_id'])).' </span>'
                                :
                            ''
                        ).(
                            $total_paid>0
                                ?
                            '<span class="success_text">'._l('%s paid',dollar($total_paid,true,$invoice['currency_id'])).' </span>'
                                :
                            ''
                        )
                    ).'</a>';
                    unset($_REQUEST['customer_id']);
                }
                ?>
            </td>
            <?php } ?>
            <?php if(class_exists('module_extra',false)){
            module_extra::print_table_data('customer',$customer['customer_id']);
            } ?>
			<td width="100">
				<div class="progressbar_core" p-value="<?php echo $customer['core_completed']; ?>"></div>
				<div class="progressbar_full" p-value="<?php echo $customer['full_completed']; ?>"></div>
			</td>
        </tr>
	<?php } ?>
  </tbody>
</table>
<?php echo $pagination['links'];?>
</form>

  <script>
  $(function() {
    $(".progressbar_core" ).each(function() {
    	var pvalue = parseInt($(this).attr('p-value'));
    	$(this).progressbar({
        	value:  pvalue
        });
    });
    $(".progressbar_full" ).each(function() {
    	var pvalue = parseInt($(this).attr('p-value'));
    	$(this).progressbar({
        	value:  pvalue
        });
    });
  });
  </script>