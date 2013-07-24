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

$module->page_title = _l('Customer Contacts');

$search = (isset($_REQUEST['search']) && is_array($_REQUEST['search'])) ? $_REQUEST['search'] : array();

$use_master_key = module_user::get_contact_master_key();
if(!$use_master_key){
	throw new Exception('Sorry no Customer or Supplier selected');
}else if(isset($_REQUEST[$use_master_key])){
	$search[$use_master_key] = $_REQUEST[$use_master_key];
}
if(!isset($search[$use_master_key]) || !$search[$use_master_key]){
    // we are just showing a list of all customer contacts.
    $show_customer_details = true;
    // check they have permissions to view all customer contacts.
    if(class_exists('module_security',false)){
        // if they are not allowed to "edit" a page, but the "view" permission exists
        // then we automatically grab the page and regex all the crap out of it that they are not allowed to change
        // eg: form elements, submit buttons, etc..
		module_security::check_page(array(
            'category' => 'Customer',
            'page_name' => 'All Customer Contacts',
            'module' => 'customer',
            'feature' => 'view',
		));
    }
	//throw new Exception('Please create a user correctly');
}else{
    $show_customer_details = false;
}
$users = module_user::get_contacts($search,true,false);

if(class_exists('module_group',false)){
    module_group::enable_pagination_hook(
        // what fields do we pass to the group module from this customers?
        array(
            'fields'=>array(
                'owner_id' => 'user_id',
                'owner_table' => 'user',
                'name' => 'name',
                'email' => 'email'
            ),
        )
    );
}


?>

<h2>
    <?php if(isset($search[$use_master_key]) && $search[$use_master_key] && module_user::can_i('create','Contacts','Customer')){ ?>
	<span class="button">
		<?php echo create_link("Add New Contact","add",module_user::link_generate('new',array('type'=>'contact'))); ?>
	</span>
    <?php } ?>
	<?php echo _l( ($show_customer_details ? 'All ' : '') . 'Customer Contacts'); ?>
</h2>

<form action="#" method="<?php echo _DEFAULT_FORM_METHOD;?>">
    <?php if($use_master_key && isset($search[$use_master_key])){ ?>
    <input type="hidden" name="<?php echo $use_master_key;?>" value="<?php echo $search[$use_master_key];?>">
    <?php } ?>


<table class="search_bar" width="100%">
	<tr>
		<th><?php _e('Filter By:'); ?></th>
		<td class="search_title">
			<?php _e('Contact Name, Email or Phone Number:');?>
		</td>
		<td class="search_input">
			<input type="text" name="search[generic]" value="<?php echo isset($search['generic'])?htmlspecialchars($search['generic']):''; ?>" size="30">
		</td>
		<td class="search_action">
			<?php echo create_link("Reset","reset",module_user::link_open_contact(false)); ?>
			<?php echo create_link("Search","submit"); ?>
		</td>
	</tr>
</table>

<?php
$pagination = process_pagination($users);
$colspan = 4;
?>

<?php echo $pagination['summary'];?>

<table width="100%" border="0" cellspacing="0" cellpadding="2" class="tableclass tableclass_rows">
	<thead>
	<tr class="title">
		<th id="contact_active" style="width:5px"></th>
		<th><?php echo _l('Name'); ?></th>
        <th><?php echo _l('Phone Number'); ?></th>
        <th><?php echo _l('Email Address'); ?></th>
        <?php if($show_customer_details){ ?>
        <th><?php echo _l('Customer'); ?></th>
        <?php } ?>
        <?php if(class_exists('module_group',false) && module_user::can_i('view','Contact Groups')){ ?>
        <th><?php echo _l('Group'); ?></th>
        <?php } ?>
        <?php if(class_exists('module_extra',false)){
        module_extra::print_table_header('user');
        } ?>
    </tr>
    </thead>
    <tbody>
		<?php
		$c=0;
		foreach($pagination['rows'] as $user){
            $user2 = module_user::get_user($user['user_id']); // for primary contact / link check
            ?>
		<tr class="<?php echo ($c++%2)?"odd":"even"; ?>">
            <td class="bg_active_<?php echo $user['contact_active']; ?>">
                
            </td>
			<td class="row_action">
				<?php echo module_user::link_open_contact($user['user_id'],true,$user2);?>
				<?php
                if($user['is_primary'] == $user['user_id']){
                    echo ' *';
                }
				?>
			</td>
			<td>
				<?php echo $user2['phone']; ?><?php echo ($user2['phone']&&$user2['mobile'])?",":""; ?> <?php echo $user2['mobile']; ?>
			</td>
			<td>
				<a href="mailto:<?php echo $user2['email']; ?>"><?php echo $user2['email']; ?></a>
			</td>
            <?php if($show_customer_details){ ?>
                <td>
                    <?php echo module_customer::link_open($user['customer_id'],true,$user); ?>
                </td>
            <?php } ?>
            <?php if(class_exists('module_group',false) && module_user::can_i('view','Contact Groups')){ ?>
            <td><?php
                    // find the groups for this customer.
                    $groups = module_group::get_groups_search(array(
                                                                  'owner_table' => 'user',
                                                                  'owner_id' => $user2['user_id'],
                                                              ));
                    $g=array();
                    foreach($groups as $group){
                        $g[] = $group['name'];
                    }
                    echo implode(', ',$g);
                ?></td>
            <?php } ?>
            <?php if(class_exists('module_extra',false)){
            module_extra::print_table_data('user',$user['user_id']);
            } ?>
		</tr>
		<?php } ?>
	</tbody>
</table>
    <?php echo $pagination['links'];?>
</form>