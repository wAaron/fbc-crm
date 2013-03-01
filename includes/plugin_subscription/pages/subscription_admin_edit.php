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


if(!module_config::can_i('view','Settings')){
    redirect_browser(_BASE_HREF);
}

$subscription_id = (int)$_REQUEST['subscription_id'];
$subscription = array();

$subscription = module_subscription::get_subscription($subscription_id);

// check permissions.
if(class_exists('module_security',false)){
    if($subscription_id>0 && $subscription['subscription_id']==$subscription_id){
        // if they are not allowed to "edit" a page, but the "view" permission exists
        // then we automatically grab the page and regex all the crap out of it that they are not allowed to change
        // eg: form elements, submit buttons, etc..
		module_security::check_page(array(
            'category' => 'Subscription',
            'page_name' => 'Subscriptions',
            'module' => 'subscription',
            'feature' => 'Edit',
		));
    }else{
		module_security::check_page(array(
			'category' => 'Subscription',
            'page_name' => 'Subscriptions',
            'module' => 'subscription',
            'feature' => 'Create',
		));
	}
	module_security::sanatise_data('subscription',$subscription);
}

?>
<form action="" method="post" id="subscription_form">
	<input type="hidden" name="_process" value="save_subscription" />
	<input type="hidden" name="subscription_id" value="<?php echo $subscription_id; ?>" />

    <?php
    module_form::set_required(array(
        'fields' => array(
            'name' => 'Name',
            'amount' => 'Amount',
        ))
    );
    module_form::prevent_exit(array(
        'valid_exits' => array(
            // selectors for the valid ways to exit this form.
            '.submit_button',
        ))
    );
    ?>

	<table cellpadding="10" width="100%">
		<tr>
			<td width="50%" valign="top">

				<h3><?php echo _l('Subscription Information'); ?></h3>

				<table width="100%" border="0" cellspacing="0" cellpadding="2" class="tableclass tableclass_form">
					<tbody>
						<tr>
							<th class="width1">
								<?php echo _l('Name'); ?>
							</th>
							<td>
								<input type="text" name="name" style="width:250px;" value="<?php echo htmlspecialchars($subscription['name']); ?>" />
							</td>
						</tr>
						<tr>
							<th>
								<?php echo _l('Amount'); ?>
							</th>
							<td>
                                <?php echo currency('');?>
								<input type="text" name="amount" value="<?php echo htmlspecialchars($subscription['amount']); ?>" class="currency" />
                                <?php echo print_select_box(get_multiple('currency','','currency_id'),'currency_id',$subscription['currency_id'],'',false,'code'); ?>
							</td>
						</tr><tr>
                            <th>
                                <?php echo _l('Repeat Every'); ?>
                            </th>
                            <td valign="top">
                                <input type="text" name="days" id="days" value="<?php echo $subscription['days']; ?>" style="width:30px;" /> <?php _e('Days'); ?><br/>
                                <input type="text" name="months" id="months" value="<?php echo $subscription['months']; ?>" style="width:30px;" /> <?php _e('Months'); ?><br/>
                                <input type="text" name="years" id="years" value="<?php echo $subscription['years']; ?>" style="width:30px;" /> <?php _e('Years'); ?><br/>
                            </td>
                        </tr>
                        <?php
                         /*module_extra::display_extras(array(
                            'owner_table' => 'subscription',
                            'owner_key' => 'subscription_id',
                            'owner_id' => $subscription_id,
                            'layout' => 'table_row',
                            )
                        );*/
                        ?>
					</tbody>
				</table>



			</td>




			<td width="50%" valign="top">
				<?php
				if($subscription_id && $subscription_id!='new'){

				}
				?>

			</td>
		</tr>
		<tr>
			<td colspan="2" align="center">
				<input type="submit" name="butt_save" id="butt_save" value="<?php echo _l('Save'); ?>" class="submit_button save_button" />
				<input type="submit" name="butt_del" id="butt_del" value="<?php echo _l('Delete'); ?>" class="submit_button delete_button" />
				<input type="button" name="cancel" value="<?php echo _l('Cancel'); ?>"
                       onclick="window.location.href='<?php echo $module->link_open(false); ?>';" class="submit_button" />

			</td>
		</tr>
	</table>

</form>

