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

$customer_id = (int)$_REQUEST['customer_id'];
$customer = array();

$customer = module_customer::get_customer($customer_id);

if($customer_id>0 && $customer['customer_id']==$customer_id){
    $module->page_title = _l('Customer: %s',$customer['customer_name']);
}else{
    $module->page_title = _l('Customer: %s',_l('New'));
}
// check permissions.
if(class_exists('module_security',false)){
    if($customer_id>0 && $customer['customer_id']==$customer_id){
        // if they are not allowed to "edit" a page, but the "view" permission exists
        // then we automatically grab the page and regex all the crap out of it that they are not allowed to change
        // eg: form elements, submit buttons, etc..
		module_security::check_page(array(
            'category' => 'Customer',
            'page_name' => 'Customers',
            'module' => 'customer',
            'feature' => 'Edit',
		));
    }else{
		module_security::check_page(array(
			'category' => 'Customer',
            'page_name' => 'Customers',
            'module' => 'customer',
            'feature' => 'Create',
		));
	}
	module_security::sanatise_data('customer',$customer);
}

$pms = module_customer::get_pm();
$sales = module_customer::get_sales();

?>
<form action="#" method="post" id="customer_form">
	<input type="hidden" name="_process" value="save_customer" />
	<input type="hidden" name="customer_id" value="<?php echo $customer_id; ?>" />

    <?php
    module_form::set_required(array(
        'fields' => array(
            'customer_name' => 'Name',
            'customer_no' => 'Customer NO',
            'name' => 'Contact Name',
        ))
    );
    module_form::prevent_exit(array(
        'valid_exits' => array(
            // selectors for the valid ways to exit this form.
            '.submit_button',
        ))
    );

    //!(int)$customer['customer_id'] &&
    if(isset($_REQUEST['move_user_id']) && (int)$_REQUEST['move_user_id']>0 && module_customer::can_i('create','Customers')){
        // we have to move this contact over to this customer as a new primary user id
        $customer['primary_user_id'] = (int)$_REQUEST['move_user_id'];
        ?>
        <input type="hidden" name="move_user_id" value="<?php echo $customer['primary_user_id'];?>">
        <?php
    }
    ?>

	<table cellpadding="10" width="100%">
		<tr>
			<td width="50%" valign="top">

				<h3><?php echo _l('Customer Core Information'); ?></h3>

				<table width="100%" border="0" cellspacing="0" cellpadding="2" class="tableclass tableclass_form">
					<tbody>
						<tr>
							<th class="width1">
								<?php echo _l('Customer Name'); ?>
							</th>
							<td>
								<input type="text" name="customer_name" id="customer_name" style="width:250px;" value="<?php echo htmlspecialchars($customer['customer_name']); ?>" />
							</td>
						</tr>
                        <?php if($customer_id && $customer_id!='new'){ ?>
                        <tr>
                            <th class="width1">
                                <?php echo _l('Customer NO'); ?>
                            </th>
                            <td>
                                <input type="text" name="customer_no" id="customer_no" style="width:250px;" value="<?php echo htmlspecialchars($customer['customer_no']); ?>" />
                            </td>
                        </tr>
                        <tr>
                            <th class="width1">
                                <?php echo _l('Main Project Manager'); ?>
                            </th>
                            <td>
                                <?php
                                echo print_select_box($pms,'customer_main_pm',$customer['customer_main_pm']);
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <th class="width1">
                                <?php echo _l('Backup Project Manager'); ?>
                            </th>
                            <td>
                                <?php
                                echo print_select_box($pms,'customer_backup_pm',$customer['customer_backup_pm']);
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <th class="width1">
                                <?php echo _l('Previous Salesman'); ?>
                            </th>
                            <td>
                                <?php
                                echo print_select_box($sales,'customer_ex_salesman',$customer['customer_ex_salesman']);
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <th class="width1">
                                <?php echo _l('Current Salesman'); ?>
                            </th>
                            <td>
                                <?php
                                echo print_select_box($sales,'customer_current_salesman',$customer['customer_current_salesman']);
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <th class="width1">
                                <?php echo _l('Customer Level'); ?>
                            </th>
                            <td>
                                <?php
                                echo print_select_box_nokey(array("G", "A", "B", "C"), "customer_level", $customer['customer_level']);
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <th class="width1">
                                <?php echo _l('Customer Type'); ?>
                            </th>
                            <td>
                                <?php
                                echo print_select_box_nokey(array("分部门", "子公司", "分支机构"), "customer_type", $customer['customer_type']);
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <th class="width1">
                                <?php echo _l('Customer Active'); ?>
                            </th>
                            <td>
                                <input type="hidden" name="customer_active" value="{{ng_customer_active}}" />
                                <select ng-model="ng_customer_active" ng-options="key as value for (key , value) in active_types" ng-init="ng_customer_active='<?php echo $customer['customer_active']; ?>'">
      								<option value=""> - Select - </option>
      							</select>
                            </td>
                        </tr>
                        <?php } ?>
                        <?php
                         module_extra::display_extras(array(
                            'owner_table' => 'customer',
                            'owner_key' => 'customer_id',
                            'owner_id' => $customer_id,
                            'layout' => 'table_row',
                                 'allow_new' => module_customer::can_i('create','Customers'),
                                 'allow_edit' => module_customer::can_i('create','Customers'),
                            )
                        );
                        ?>
					</tbody>
				</table>

				<?php if($customer_id && $customer_id!='new'){ ?>
				<h3><?php echo _l('Cooperation Information'); ?>
				    <span class="button">
                        <input type="checkbox" ng-model="info.corp.hide" ng-init="info.corp.hide='false'">
                    </span>
				</h3>

				<table width="100%" border="0" cellspacing="0" cellpadding="2" class="tableclass tableclass_form info-{{info.corp.hide}}">
					<tbody>
                        <tr>
                            <th class="width1">
                                <?php echo _l('Customer From'); ?>
                            </th>
                            <td>
                                <?php
                                echo print_select_box_nokey(array("互联网", "朋友介绍", "宣传资料", "电子邮件", "传真"), "customer_from", $customer['customer_from']);
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <th class="width1">
                                <?php echo _l('Cooperate From'); ?>
                            </th>
                            <td>
                                <?php
                                echo print_select_box_nokey(range(2002, 2020), "cooperate_from", $customer['cooperate_from']);
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <th class="width1">
                                <?php echo _l('Translation Speed'); ?>
                            </th>
                            <td>
                                <input type="text" name="translate_speed" id="translate_speed" class="currency" style="width:40px;" value="<?php echo htmlspecialchars($customer['translate_speed']); ?>" />
                                <?php
                                echo print_select_box_nokey(array("中文/天", "英文/天"), "translate_speed_unit", $customer['translate_speed_unit']);
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <th class="width1">
                                <?php echo _l('Success Stories'); ?>
                            </th>
                            <td>
                                <input type="text" name="customer_success_story" id="customer_success_story" style="width:250px;" value="<?php echo htmlspecialchars($customer['customer_success_story']); ?>" />
                            </td>
                        </tr>
                        
					</tbody>
				</table>
				<br/>
				
				<h3><?php echo _l('VIP Information'); ?>
				    <span class="button">
                        <input type="checkbox" ng-model="info.vip.hide" ng-init="info.vip.hide='false'">
                    </span>
				</h3>

				<table width="100%" border="0" cellspacing="0" cellpadding="2" class="tableclass tableclass_form info-{{info.vip.hide}}">
					<tbody>
                        <tr>
                            <th class="width1">
                                <?php echo _l('Customer is VIP'); ?>
                            </th>
                            <td>
                                <?php
                                echo print_select_box_nokey(array("是", "否"), "customer_vip", $customer['customer_vip']);
                                ?>
                            </td>
                        </tr>

                        <tr>
                            <th class="width1">
                                <?php echo _l('Customer VIP End'); ?>
                            </th>
                            <td>
                                <input type="text" name="customer_vip_end" id="customer_vip_end" class="date_field" value="<?php echo htmlspecialchars($customer['customer_vip_end']); ?>" />
                            </td>
                        </tr>
                        <tr>
                            <th class="width1">
                                <?php echo _l('Customer VIP Renew'); ?>
                            </th>
                            <td>
                                <input type="text" name="customer_vip_renew" id="customer_vip_renew" class="date_field" value="<?php echo htmlspecialchars($customer['customer_vip_renew']); ?>" />
                            </td>
                        </tr>
                        
					</tbody>
				</table>
				<br/>
				<?php } ?>






                    <h3><?php echo _l('Advanced'); ?>
				    <span class="button">
                        <input type="checkbox" ng-model="info.advance.hide" ng-init="info.advance.hide='false'">
                    </span>
                    </h3>

                    <table width="100%" border="0" cellspacing="0" cellpadding="2" class="tableclass tableclass_form info-{{info.advance.hide}}">
                        <tbody>
                            <?php if(module_customer::can_i('edit','Customer Credit')){ ?>
                            <tr>
                                <th class="width1">
                                    <?php echo _l('Credit'); ?>
                                </th>
                                <td>
                                    <?php echo currency('<input type="text" name="credit" value="'.htmlspecialchars($customer['credit']).'" class="currency" />'); ?>
                                    <?php _h('If the customer is given a credit here you will have an option to apply this credit to an invoice. If a customer over pays an invoice you will be prompted to add that overpayment as credit onto their account.');?>
                                </td>
                            </tr>
                            <?php } ?>
                            <?php if(module_invoice::can_i('edit','Invoices')){ ?>
                            <?php if(isset($customer['default_tax'])){ ?>
                            <tr>
                                <th>
                                    <?php echo _l('Default Tax'); ?>
                                </th>
                                <td>
                                    <input type="checkbox" name="default_tax_system" value="1"<?php if($customer['default_tax']<0)echo ' checked';?>> <?php _e('Use system default (%s @ %s%%)',module_config::c('tax_name','TAX'),module_config::c('tax_percent',10));?>
                                    <br/>
                                    <?php _e('Or custom tax:');?>
                                    <input type="text" name="default_tax_name" value="<?php echo htmlspecialchars($customer['default_tax_name']);?>" style="width:30px;">
                                    @
                                    <input type="text" name="default_tax" value="<?php echo $customer['default_tax']>=0 ? $customer['default_tax'] : '';?>" style="width:35px;">%

                                    <?php _h('If your customer needs a deafult tax rate that is different from the system default please enter it here.');?>
                                </td>
                            </tr>
                            <?php } ?>
                            <?php if(isset($customer['default_invoice_prefix'])){ ?>
                            <tr>
                                <th>
                                    <?php echo _l('Invoice Prefix'); ?>
                                </th>
                                <td>
                                    <input type="text" name="default_invoice_prefix" value="<?php echo htmlspecialchars($customer['default_invoice_prefix']);?>">
                                    <?php _h('Every time an invoice is generated for this customer the INVOICE NUMBER will be prefixed with this value.');?>
                                </td>
                            </tr>
                            <?php } ?>
                            <?php } ?>
                        </tbody>
                    </table>



			</td>




			<td width="50%" valign="top">
			
				<?php if($customer_id && $customer_id!='new'){ ?>
				<h3><?php echo _l('Company Information'); ?>
				    <span class="button">
                        <input type="checkbox" ng-model="info.company.hide" ng-init="info.company.hide='false'">
                    </span>
				</h3>
				<table width="100%" border="0" cellspacing="0" cellpadding="2" class="tableclass tableclass_form info-{{info.company.hide}}">
					<tbody>

						<tr>
							<th>
								<?php echo _l('Logo'); ?>
							</th>
							<td>
								 <?php
                                 module_file::display_files(array(
                                    //'title' => 'Certificate Files',
                                    'owner_table' => 'customer',
                                    'owner_id' => $customer_id,
                                    //'layout' => 'list',
                                         'layout' => 'gallery',
                                         'editable' => module_security::is_page_editable(),
                                    )
                                );
                                ?>
							</td>
						</tr>
                        <tr>
                            <th class="width1">
                                <?php echo _l('Customer Company Type'); ?>
                            </th>
                            <td>
                                <?php
                                echo print_select_box_nokey(array("国有大型", "外商独资法人企业", "外商驻华代表处", "中外合资企业", "国内私营企业"), "customer_company_type", $customer['customer_company_type']);
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <th class="width1">
                                <?php echo _l('Full Name'); ?>
                            </th>
                            <td>
                                <input type="text" name="customer_full_name" id="customer_full_name" style="width:250px;" value="<?php echo htmlspecialchars($customer['customer_full_name']); ?>" />
                            </td>
                        </tr>
                        <tr>
                            <th class="width1">
                                <?php echo _l('English Name'); ?>
                            </th>
                            <td>
                                <input type="text" name="customer_full_en" id="customer_full_en" style="width:250px;" value="<?php echo htmlspecialchars($customer['customer_full_en']); ?>" />
                            </td>
                        </tr>
                        <tr>
                            <th class="width1">
                                <?php echo _l('Customer Build From'); ?>
                            </th>
                            <td>
                                <?php
                                echo print_select_box_nokey(range(1900, 2020), "customer_build_from", $customer['customer_build_from']);
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <th class="width1">
                                <?php echo _l('Main Products'); ?>
                            </th>
                            <td>
                                <input type="text" name="customer_main_prod" id="customer_main_prod" style="width:250px;" value="<?php echo htmlspecialchars($customer['customer_main_prod']); ?>" />
                            </td>
                        </tr>
 
                        <tr>
                            <th class="width1">
                                <?php echo _l('Customer Staff'); ?>
                            </th>
                            <td>
                                <?php
                                echo print_select_box_nokey(array("10名以内", "10-50", "50-100名", "100名以上"), "customer_staff", $customer['customer_staff']);
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <th class="width1">
                                <?php echo _l('Company Size'); ?>
                            </th>
                            <td>
                                <input type="text" name="company_size" id="company_size" style="width:250px;" value="<?php echo htmlspecialchars($customer['company_size']); ?>" />
                            </td>
                        </tr>
					</tbody>
				</table>
				<br/>
			
				<h3><?php echo _l('Payment Information'); ?>
				    <span class="button">
                        <input type="checkbox" ng-model="info.pay.hide" ng-init="info.pay.hide='false'">
                    </span>
				</h3>

				<table width="100%" border="0" cellspacing="0" cellpadding="2" class="tableclass tableclass_form info-{{info.pay.hide}}">
					<tbody>
                        <tr>
                            <th class="width1">
                                <?php echo _l('Pay in Days'); ?>
                            </th>
                            <td>
                                <input type="text" name="customer_pay_days" id="customer_pay_days" class="currency" style="width:40px;" value="<?php echo htmlspecialchars($customer['customer_pay_days']); ?>" />
                                <?php echo _l('Unit Day'); ?>
                            </td>
                        </tr>
                        <tr>
                            <th class="width1">
                                <?php echo _l('Pay Period'); ?>
                            </th>
                            <td>
                                <?php
                                echo print_select_box_nokey(array("按月", "按季"), "customer_pay_period", $customer['customer_pay_period']);
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <th class="width1">
                                <?php echo _l('Customer Ticket Type'); ?>
                            </th>
                            <td>
                                <?php
                                echo print_select_box_nokey(array("青睐", "韦勋", "两者"), "customer_ticket_type", $customer['customer_ticket_type']);
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <th class="width1">
                                <?php echo _l('Customer Ticket Info'); ?>
                            </th>
                            <td>
                                <input type="text" name="customer_ticket_info" id="customer_ticket_info" style="width:250px;" value="<?php echo htmlspecialchars($customer['customer_ticket_info']); ?>" />
                            </td>
                        </tr>
                        
					</tbody>
				</table>
				<br/>
				<?php } ?>
				
				<h3><?php echo _l('Address'); ?>
				    <span class="button">
                        <input type="checkbox" ng-model="info.address.hide" ng-init="info.address.hide='false'">
                    </span>
				</h3>

				<?php
				handle_hook("address_block",$module,"physical","customer","customer_id");
				?>
			




			</td>
		</tr>
		
		<tr>
			<td valign="top">
                <h3>
                    <?php echo _l('Primary Contact Details'); ?>
                    <?php if($customer['primary_user_id'] && (int)$customer_id){ ?>
                    <span class="button">
                        <a href="<?php echo module_user::link_open_contact($customer['primary_user_id'],false);?>" class="uibutton"><?php _e('More');?></a>
                    </span>
                    <?php } ?>
                </h3>

				<?php
				// we use the "user" module to find the user details
				// for the currently selected primary contact id
				if($customer['primary_user_id']){

                    if(!module_user::can_i('view','All Customer Contacts','Customer','customer') && $customer['primary_user_id'] != module_security::get_loggedin_id()){
                        echo '<div class="content_box_wheader"><table width="100%" border="0" cellspacing="0" cellpadding="2" class="tableclass tableclass_form"><tbody><tr><td>';
                        _e('Details hidden');
                        echo '</td></tr></tbody></table></div>';
                    }else if(!module_user::can_i('edit','All Customer Contacts','Customer','customer') && $customer['primary_user_id'] != module_security::get_loggedin_id()){
                        // no permissions to edit.
                        echo '<div class="content_box_wheader"><table width="100%" border="0" cellspacing="0" cellpadding="2" class="tableclass tableclass_form"><tbody><tr><td>';
                        module_user::print_contact_summary($customer['primary_user_id'],'text',array('name','last_name','email'));
                        echo '</td></tr></tbody></table></div>';
                    }else{
                        module_user::print_contact_summary($customer['primary_user_id'],'new');
                    }
				}else{
					// hack to create new contact details.
                    module_user::print_contact_summary(false,'new');
				}
				?>
			</td>
			<td valign="top">
				<?php
				if($customer_id && $customer_id!='new'){

                    if(class_exists('module_group',false)){
                        module_group::display_groups(array(
                             'title' => 'Customer Groups',
                            'owner_table' => 'customer',
                            'owner_id' => $customer_id,
                            'view_link' => $module->link_open($customer_id),

                        ));
                    }

					$note_summary_owners = array();
					// generate a list of all possible notes we can display for this customer.
					// display all the notes which are owned by all the sites we have access to

					// display all the notes which are owned by all the users we have access to
					foreach(module_user::get_contacts(array('customer_id'=>$customer_id)) as $val){
						$note_summary_owners['user'][] = $val['user_id'];
					}
                    foreach(module_website::get_websites(array('customer_id'=>$customer_id)) as $val){
						$note_summary_owners['website'][] = $val['website_id'];
					}
                    foreach(module_job::get_jobs(array('customer_id'=>$customer_id)) as $val){
						$note_summary_owners['job'][] = $val['job_id'];
                        foreach(module_invoice::get_invoices(array('job_id'=>$val['job_id'])) as $val){
                            $note_summary_owners['invoice'][$val['invoice_id']] = $val['invoice_id'];
                        }
					}
                    foreach(module_invoice::get_invoices(array('customer_id'=>$customer_id)) as $val){
                        $note_summary_owners['invoice'][$val['invoice_id']] = $val['invoice_id'];
                    }
					module_note::display_notes(array(
						'title' => 'All Customer Notes',
						'owner_table' => 'customer',
						'owner_id' => $customer_id,
						'view_link' => $module->link_open($customer_id),
						'display_summary' => true,
						'summary_owners' => $note_summary_owners
						)
					);

                    hook_handle_callback('customer_edit',$customer_id);

				}
				?>
			</td>
		</tr>
		
		<tr>
			<td colspan="2" style="text-align: center">
				<input type="submit" name="butt_save" id="butt_save" value="<?php echo _l('Save'); ?>" class="submit_button save_button" />
                <?php if(module_customer::can_i('delete','Customers') && $customer_id > 0){ ?>
				<input type="submit" name="butt_del" id="butt_del" value="<?php echo _l('Delete'); ?>" class="submit_button delete_button" />
                <?php } ?>
				<input type="button" name="cancel" value="<?php echo _l('Cancel'); ?>"
                       onclick="window.location.href='<?php echo $module->link_open(false); ?>';" class="submit_button" />

			</td>
		</tr>
	</table>

</form>

