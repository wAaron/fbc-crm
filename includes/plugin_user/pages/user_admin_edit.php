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


$user_id = (int)$_REQUEST['user_id'];
$user = module_user::get_user($user_id);
if(!$user){
    $user_id = 'new';
}
if(!$user && $user_id > 0){
    // bad url. hack attempt?
    // direct back to customer page
    if(isset($_REQUEST['customer_id']) && (int)$_REQUEST['customer_id']){
        redirect_browser(module_customer::link_open($_REQUEST['customer_id']));
    }
}

if($user_id == 1 && module_security::get_loggedin_id() != 1){
    set_error('Sorry, only the Administrator can access this page.');
    redirect_browser(_UCM_HOST._BASE_HREF);
}



// permission check.
if(!$user_id){
    // check if can create.
    module_security::check_page(array(
        'category' => 'Config',
        'page_name' => 'Users',
        'module' => 'user',
        'feature' => 'Create',
    ));

    // are we creating a new user?
    $user['roles']=array(
        array('security_role_id'=>module_config::c('user_default_role',0)),
    );
}else{
    // check if can view/edit.
    module_security::check_page(array(
        'category' => 'Config',
        'page_name' => 'Users',
        'module' => 'user',
        'feature' => 'Edit',
    ));
}


// work out the user type and invluce that particular file
/*$user_type_id = (int)$user['user_type_id'];
if(!$user_type_id){
    if(in_array('config',$load_modules)){
        $user_type_id = 1;

    }else{
        $user_type_id = 2;
    }
}*/
//include('user_admin_edit'.$user_type_id.'.php');
//include('user_admin_edit1.php');

if(isset($user['customer_id']) && $user['customer_id']){
    // we have a contact!
    die('Wrong file');
}else{
    $use_master_key = false; // we have a normal site user..
}

// find a contact with matching email address.
if(isset($user['email']) && strlen($user['email'])>3){
    $contacts = module_user::get_contacts(array('email'=>$user['email']));
    if(count($contacts)>0){
        foreach($contacts as $c){
        ?>
        <div class="warning"><?php _e('Warning: a contact from the Customer %s exists with this same email address: %s <br/>This may create problems when trying to login. <br/>We suggest you remove/change THIS user account and use the existing CONTACT account instead.',module_customer::link_open($c['customer_id'],true),module_user::link_open_contact($c['user_id'],true));?></div>
        <?php
        }
    }
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

?>
	<table width="100%" cellpadding="10">
		<tbody>
			<tr>
				<td valign="top" width="50%">
					<h3><?php echo _l('User Details'); ?></h3>

					<?php 
                    $include_contact_extend = false;
                    include('contact_admin_form.php'); 
                    ?>


                    <?php if(module_config::c('users_have_address',0)){ ?>
                        <h3><?php echo _l('Address'); ?></h3>

                        <?php
                        handle_hook("address_block",$module,"physical","user","user_id");
                    }
                    ?>


                    <?php
                    if((int)$user_id > 0){
                        //handle_hook("note_list",$module,"user","user_id",$user_id);
                        module_note::display_notes(array(
                            'title' => 'User Notes',
                            'owner_table' => 'user',
                            'owner_id' => $user_id,
                            'view_link' => $module->link_open($user_id),
                           //'bypass_security' => true,
                            )
                        );
                        if(class_exists('module_group',false)){
    
                            module_group::display_groups(array(
                                 'title' => 'User Groups',
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
					<input type="submit" name="butt_save" id="butt_save" value="<?php echo _l('Save User'); ?>" class="save_button submit_button" />
                    <?php if($user_id != 1 &&
                        module_user::can_i('delete','Users','Config')
                        ){ ?>
					<input type="submit" name="butt_del" id="butt_del" value="<?php echo _l('Delete'); ?>" class="delete_button submit_button" />
                    <?php } ?>
					<input type="button" name="cancel" value="<?php echo _l('Cancel'); ?>" onclick="window.location.href='<?php echo $module->link_open(false); ?>';" class="submit_button" />
				</td>
			</tr>
		</tbody>
	</table>


</form>
