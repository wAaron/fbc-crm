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


$search = (isset($_REQUEST['search']) && is_array($_REQUEST['search'])) ? $_REQUEST['search'] : array();
$search['customer_id'] = 0;
$users = module_user::get_users($search);
$pagination = process_pagination($users);

// grab a list of customer sites
$sites = array();
$user_statuses = module_user::get_statuses();
$roles = module_security::get_roles();
?>

<h2>
    <?php if(module_user::can_i('create','Users','Config')){ ?>
	<span class="button">
		<?php echo create_link("Add new user","add",$module->link_open('new')); ?>
	</span>
    <?php } ?>
	<?php echo _l('User Administration'); ?>
</h2>

<form action="" method="post">


<table class="search_bar">
	<tr>
		<th><?php _e('Filter By:'); ?></th>
		<td class="search_title">
			<?php _e('Users Name:');?>
		</td>
		<td class="search_input">
			<input type="text" name="search[generic]" value="<?php echo isset($search['generic'])?htmlspecialchars($search['generic']):''; ?>">
		</td>
		<td class="search_action">
			<?php echo create_link("Reset","reset",$module->link()); ?>
			<?php echo create_link("Search","submit"); ?>
		</td>
	</tr>
</table>

<?php echo $pagination['summary'];?>
<table width="100%" border="0" cellspacing="0" cellpadding="2" class="tableclass tableclass_rows">
	<thead>
        <tr class="title">
            <th><?php echo _l('Users Name'); ?></th>
            <th><?php echo _l('Email Address'); ?></th>
            <th><?php echo _l('Role / Permissions'); ?></th>
            <th><?php echo _l('Can Login'); ?></th>
        </tr>
    </thead>
    <tbody>
		<?php
		$c=0;
		foreach($pagination['rows'] as $user){
            $user = module_user::get_user($user['user_id']); ?>
            <tr class="<?php echo ($c++%2)?"odd":"even"; ?>">
                <td class="row_action">
                    <?php echo module_user::link_open($user['user_id'],true);?>
                </td>
                <td>
                    <?php echo htmlspecialchars($user['email']); ?>
                </td>
                <td>
                    <?php
                    if($user['user_id']==1){
                        echo _l('Everything');
                    }else{
                        if(isset($user['roles']) && $user['roles']){
                            foreach($user['roles'] as $role){
                                echo $roles[$role['security_role_id']]['name'];
                            }
                        }
                    }
                    ?>
                </td>
                <td>
                    <?php echo module_security::can_user_login($user['user_id']) ? _l('Yes') : _l('No'); ?>
                </td>
            </tr>
		<?php } ?>
	</tbody>
</table>
				<?php echo $pagination['links'];?>
</form>