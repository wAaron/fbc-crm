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

if(!$user_safe)die('fail');

$module->page_title = _l('Contact');

$user_id = (int)$_REQUEST['user_id'];
$user = module_user::get_user($user_id,true,false);
if(!$user || $user['user_id'] != $user_id){
    $user_id = 0;
}
if((!$user || $user['user_id'] != $user_id) && $user_id > 0){
    // bad url. hack attempt?
    // direct back to customer page
    if(isset($_REQUEST['customer_id']) && (int)$_REQUEST['customer_id']){
        redirect_browser(module_customer::link_open($_REQUEST['customer_id']));
    }else{
        redirect_browser('/');
    }
}


// addition for the 'all customer contacts' permission
// if user doesn't' have this permission then we only show ourselves in this list.
// todo: this is a problem - think about how this new "All Contacts" permission affects staff members viewing contact details, not just user contacts.
if($user_id && !module_user::can_i('view','All Customer Contacts','Customer','customer')){
     if($user_id!=module_security::get_loggedin_id())redirect_browser(module_customer::link_open($user['customer_id']));
}
if($user_id && !module_user::can_i('edit','All Customer Contacts','Customer','customer')){
     if($user_id!=module_security::get_loggedin_id()){
         // dont let them edit this page
         ob_start();
         module_security::disallow_page_editing();
     }
}


// permission check.
if(!$user_id){
    // check if can create.
    module_security::check_page(array(
        'category' => 'Customer',
        'page_name' => 'Contacts',
        'module' => 'user',
        'feature' => 'create',
    ));
}else{
    // check if can view/edit.
    module_security::check_page(array(
        'category' => 'Customer',
        'page_name' => 'Contacts',
        'module' => 'user',
        'feature' => 'edit',
    ));
}


if($user_id>0 && $user['user_id']==$user_id){
    $module->page_title = _l('User: %s',$user['name']);
}else{
    $module->page_title = _l('User: %s',_l('New'));
}

if(isset($user['customer_id']) && $user['customer_id']){
    // we have a contact!
    $use_master_key = 'customer_id'; // for the "primary contact" thingey.
    // are we creating a new user?
    if(!$user_id || $user_id == 'new'){
        $user['roles']=array(
            array('security_role_id'=>module_config::c('contact_default_role',0)),
        );
    }
}else{
    die('Wrong file');
}

?>



<form action="" method="post">
	<input type="hidden" name="_process" value="save_user" />
	<!-- <input type="hidden" name="_redirect" value="<?php echo $module->link("",array("saved"=>true,"user_id"=>((int)$user_id)?$user_id:'')); ?>" /> -->
	<input type="hidden" name="user_id" value="<?php echo $user_id; ?>" />
	<input type="hidden" name="customer_id" value="<?php echo $user['customer_id']; ?>" />


    <?php

    module_form::prevent_exit(array(
        'valid_exits' => array(
            // selectors for the valid ways to exit this form.
            '.submit_button',
        ))
    );

    module_form::set_required(array(
         'fields' => array(
             'name' => 'Name',
             'email' => 'Email',
             //'password' => 'Password',
             //'status_id' => 'Status',
         ),
      ));

  // check if this customer is linked to anyone else. and isn't the primary
    if((int)$user_id>0){
        $this_one_is_linked_primary = false;
        $contact_links = module_user::get_contact_customer_links($user['user_id']);
        if(count($contact_links)){
            // check if this user is primary.

            $this_one_is_linked_primary = ($user['linked_parent_user_id'] == $user_id);
            $c = array();
            foreach($contact_links as $contact_link){
                $other_contact = module_user::get_user($contact_link['user_id']);
                if($this_one_is_linked_primary && !$other_contact['linked_parent_user_id']){
                    // hack to ensure data validity
                    $other_contact['linked_parent_user_id'] = $user_id;
                    update_insert('user_id',$other_contact['user_id'],'user',array('linked_parent_user_id'=>$user_id));
                }
                $c[] = module_customer::link_open($contact_link['customer_id'],true);
            }
            if($this_one_is_linked_primary){
                ?>
                <div>
                    <?php _e('Notice: This contact is primary and has access to the other linked customers: %s',implode(', ',$c)); ?>
                </div>
                <?php
            }else if($user['linked_parent_user_id']){
                ?>
                <div>
                    <?php _e('Notice: This contact has been linked to %s. Please go there to edit their details.',module_user::link_open_contact($user['linked_parent_user_id'],true)); ?>

                    <p>&nbsp;</p>
                    <p>&nbsp;</p>
                    <p>&nbsp;</p>
                    <p>&nbsp;</p>
                    <input type="hidden" name="unlink" id="unlink" value="">
                    <input type="button" name="go" value="<?php _e('Unlink this contact from the group');?>" onclick="$('#unlink').val('yes'); this.form.submit();" class="delete_button submit_button">
                </div>
                </form>

                <?php
                return;
            }else{
                ?>
                Fatal contact linking error. Sorry I rushed this feature!
                <?php
            }
        }
    }

?>
	<table width="100%" cellpadding="10">
		<tbody>
			<tr>
				<td valign="top" width="50%">
					<h3><?php echo _l('Contact Details'); ?></h3>

					<?php
                    $include_contact_extend = true;
                    include('contact_admin_form.php'); ?>


                    <?php if(module_config::c('users_have_address',0)){ ?>
                        <h3><?php echo _l('Address'); ?></h3>

                        <?php
                        handle_hook("address_block",$module,"physical","user","user_id");
                    }

                    ?>


                    <?php
                    if((int)$user_id > 0 && module_user::can_i('edit','Contacts','Customer') && strlen($user['email'])>2){
                        // check if contact exists under other customer accounts.
                        $others = module_user::get_contacts(array('email'=>$user['email']));
                        if(count($others)>1){
                            foreach($others as $other_id=>$other){
                                if($other['user_id']==$user['user_id']){
                                    // this "other" person is from teh same customer as us.
                                    unset($others[$other_id]);
                                }else if(count($contact_links)){
                                    // check if this one is already linked somewhere.
                                    foreach($contact_links as $contact_link){
                                        if($contact_link['user_id'] == $other['user_id']){
                                            unset($others[$other_id]);
                                            break;
                                        }
                                    }
                                }
                            }
                            if(count($others)){
                                print_heading(array(
                                    'type'=>'h3',
                                    'title'=>_l('Create Linked Contacts'),
                                    'help' => 'This email address exists as a contact in another user account. By linking these accounts together, this user will be able to access all the linked customers from this single login. '
                                ));
                                ?>
                                <table border="0" cellspacing="0" cellpadding="2" class="tableclass tableclass_form tableclass_full">
                                    <tbody>
                                    <?php foreach($others as $other){ ?>
                                    <tr>
                                        <td>
                                            <input type="hidden" name="link_user_ids[]" value="<?php echo $other['user_id'];?>"> <!-- todo- checkbox -->
                                            <?php echo _l('%s under customer %s',module_user::link_open_contact($other['user_id'],true,$other),module_customer::link_open($other['customer_id'],true));?>
                                        </td>
                                    </tr>
                                    <?php } ?>
                                    <tr>
                                        <td align="center">
                                            <input type="hidden" name="link_customers" id="link_customers" value="">
                                            <input type="button" name="link" value="<?php _e('Link above contacts to this one, and make THIS one primary');?>" onclick="$('#link_customers').val('yes'); this.form.submit();">
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>

                                <?php
                            }
                        }

                        //todo: display a warning if the same email address is used within the same customer as a different contact
                        //todo: display a warning if this email address is used as a main system "user" (similar to what we do in users anyway).

                    }
                    if((int)$user_id>0){
                        //handle_hook("note_list",$module,"user","user_id",$user_id);
                        module_note::display_notes(array(
                            'title' => 'Contact Notes',
                            'owner_table' => 'user',
                            'owner_id' => $user_id,
                            'view_link' => $module->link_open($user_id),
                           //'bypass_security' => true,
                            )
                        );
                        if(class_exists('module_group',false)){
    
                            module_group::display_groups(array(
                                 'title' => 'Contact Groups',
                                'owner_table' => 'user',
                                'owner_id' => $user_id,
                                'view_link' => module_user::link_open($user_id),

                             ));
                        }
                    }
                    ?>


				</td>
				<td valign="top" width="50%">

					<?php include('user_admin_edit_login.php'); ?>



                </td>
			</tr>
			<tr>
				<td colspan="2" align="center">
					<input type="submit" name="butt_save" id="butt_save" value="<?php echo _l('Save Contact'); ?>" class="save_button submit_button" />
                    <?php if((int)$user_id>1 && module_user::can_i('delete','Contacts','Customer')) { ?>
                    <input type="submit" name="butt_del_contact" id="butt_del_contact" value="<?php echo _l('Delete'); ?>" class="delete_button submit_button" />
                    <?php } ?>
					<input type="button" name="cancel" value="<?php echo _l('Cancel'); ?>" onclick="window.location.href='<?php echo module_customer::link_open($user['customer_id']); ?>';" class="submit_button" />
				</td>
			</tr>
		</tbody>
	</table>


</form>
