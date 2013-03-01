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

// load the address they're trying to access.
$address_id = (isset($_REQUEST['address_id']) && (int)$_REQUEST['address_id']) ? (int)$_REQUEST['address_id'] : false;
if($address_id){
	$address_data = module_address::get_address_by_id($address_id);
	// load the form using the normal module callback.
	// todo - move this into a static method call instead of all the complicated hooks with optional parameters.
	//module_address::print_address_form($address_id);
	// do a form as well.
	?>
	<form action="<?php echo $module->link();?>" method="post">
	<input type="hidden" name="_process" value="save_from_popup">
	<input type="hidden" name="_redirect" class="redirect" value="">
	<?php
	handle_hook("address_block",$module,$address_data['address_type'],$address_data['owner_table'],false,$address_data['owner_id']);
	?>
	</form>
	<?php
}
// exit so ajax load doesn't do everything
exit;

