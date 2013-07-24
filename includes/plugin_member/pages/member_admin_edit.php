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

$member_id = (int)$_REQUEST['member_id'];
$member = array();

$member = module_member::get_member($member_id);

// check permissions.
if(class_exists('module_security',false)){
    if($member_id>0 && $member['member_id']==$member_id){
        // if they are not allowed to "edit" a page, but the "view" permission exists
        // then we automatically grab the page and regex all the crap out of it that they are not allowed to change
        // eg: form elements, submit buttons, etc..
		module_security::check_page(array(
            'category' => 'Member',
            'page_name' => 'Members',
            'module' => 'member',
            'feature' => 'Edit',
		));
    }else{
		module_security::check_page(array(
			'category' => 'Member',
            'page_name' => 'Members',
            'module' => 'member',
            'feature' => 'Create',
		));
	}
	module_security::sanatise_data('member',$member);
}

$module->page_title = _l('Member: %s',htmlspecialchars($member['first_name'].' '.$member['last_name']));

?>
<form action="" method="post" id="member_form">
	<input type="hidden" name="_process" value="save_member" />
	<input type="hidden" name="member_id" value="<?php echo $member_id; ?>" />

    <?php
    module_form::set_required(array(
        'fields' => array(
            'first_name' => 'Name',
            'email' => 'Email',
        ))
    );
    module_form::prevent_exit(array(
        'valid_exits' => array(
            // selectors for the valid ways to exit this form.
            '.submit_button',
            '.submit_small', // subscription add
        ))
    );
    ?>

	<table cellpadding="10" width="100%">
		<tr>
			<td width="50%" valign="top">

				<h3><?php echo _l('Member Information'); ?></h3>

				<table width="100%" border="0" cellspacing="0" cellpadding="2" class="tableclass tableclass_form">
					<tbody>
						<tr>
							<th class="width1">
								<?php echo _l('First Name'); ?>
							</th>
							<td>
								<input type="text" name="first_name" style="width:250px;" value="<?php echo htmlspecialchars($member['first_name']); ?>" />
							</td>
						</tr>
						<tr>
							<th>
								<?php echo _l('Last Name'); ?>
							</th>
							<td>
								<input type="text" name="last_name" style="width:250px;" value="<?php echo htmlspecialchars($member['last_name']); ?>" />
							</td>
						</tr>
						<tr>
							<th>
								<?php echo _l('Business Name'); ?>
							</th>
							<td>
								<input type="text" name="business" style="width:250px;" value="<?php echo htmlspecialchars($member['business']); ?>" />
							</td>
						</tr>
						<tr>
							<th>
								<?php echo _l('Email'); ?>
							</th>
							<td>
								<input type="text" name="email" style="width:250px;" value="<?php echo htmlspecialchars($member['email']); ?>" />
							</td>
						</tr>
						<tr>
							<th>
								<?php echo _l('Phone'); ?>
							</th>
							<td>
								<input type="text" name="phone" style="width:250px;" value="<?php echo htmlspecialchars($member['phone']); ?>" />
							</td>
						</tr>
						<tr>
							<th>
								<?php echo _l('Mobile'); ?>
							</th>
							<td>
								<input type="text" name="mobile" style="width:250px;" value="<?php echo htmlspecialchars($member['mobile']); ?>" />
							</td>
						</tr>
                        <?php
                         module_extra::display_extras(array(
                            'owner_table' => 'member',
                            'owner_key' => 'member_id',
                            'owner_id' => $member_id,
                            'layout' => 'table_row',
                            )
                        );
                        ?>
                        <?php if($member_id>0){ ?>
                        <tr>
                            <th>
                                <?php _e('External'); ?>
                            </th>
                            <td>
                                <a href="<?php echo module_member::link_public_details($member_id);?>" target="_blank"><?php _e('Edit Details');?></a> <?php _h('You can send this link to your customer so they can edit their details.'); ?>
                            </td>
                        </tr>
                        <?php } ?>
					</tbody>
				</table>
                <?php if($member_id && (int)$member_id>0){
                    hook_handle_callback('member_edit',$member_id);
                } ?>


			</td>




			<td width="50%" valign="top">
				<?php
				if($member_id && (int)$member_id>0){
                    if(class_exists('module_group',false)){
                        module_group::display_groups(array(
                             'title' => 'Member Groups',
                            'description' => _l('These are for you to group your members. The member cannot see or change these groups. You can choose members based on these groups.'),
                            'owner_table' => 'member',
                            'owner_id' => $member_id,
                            'view_link' => $module->link_open($member_id),

                        ));

                        if(class_exists('module_newsletter',false)){
                            module_group::display_groups(array(
                                'title' => 'Newsletter',
                                'description' => _l('The member can choose which of the below subscriptions they would like to receive. The member can see and change these subscriptions themselves. You can choose members based on these subscriptions.'),
                                'owner_table' => 'newsletter_subscription',
                                'owner_id' => $member_id,
                            ));
                        }
                    }

				}
				?>

			</td>
		</tr>
		<tr>
			<td colspan="2" align="center">
				<input type="submit" name="butt_save" id="butt_save" value="<?php echo _l('Save'); ?>" class="submit_button save_button" />
                <?php if($member_id>0){ ?>
				<input type="submit" name="butt_del" id="butt_del" value="<?php echo _l('Delete'); ?>" class="submit_button delete_button" />
                <?php } ?>
				<input type="button" name="cancel" value="<?php echo _l('Cancel'); ?>"
                       onclick="window.location.href='<?php echo $module->link_open(false); ?>';" class="submit_button" />

			</td>
		</tr>
	</table>

</form>

