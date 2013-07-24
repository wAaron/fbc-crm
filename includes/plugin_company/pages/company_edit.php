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

if(!module_config::can_i('edit','Settings')){
    redirect_browser(_BASE_HREF);
}

$company_id = (int)$_REQUEST['company_id'];
$company = array();
if($company_id>0){

    if(class_exists('module_security',false)){
        module_security::check_page(array(
            'category' => 'Company',
            'page_name' => 'Company',
            'module' => 'company',
            'feature' => 'edit',
        ));
    }
	$company = module_company::get_company($company_id);
}else{
}
if(!$company){
    $company_id = 'new';
	$company = array(
		'company_id' => 'new',
		'name' => '',
	);
	module_security::sanatise_data('company',$company);
}
?>
<form action="" method="post">
      <?php
module_form::prevent_exit(array(
    'valid_exits' => array(
        // selectors for the valid ways to exit this form.
        '.submit_button',
    ))
);
?>

    
	<input type="hidden" name="_process" value="save_company" />
	<input type="hidden" name="company_id" value="<?php echo $company_id; ?>" />

        <h3><?php echo _l('Company Details'); ?></h3>

        <table border="0" cellspacing="0" cellpadding="2" class="tableclass tableclass_form">
            <tbody>
            <tr>
                <th class="width2">
                    <?php echo _l('Company Name'); ?>
                </th>
                <td>
                    <input type="text" name="name" value="<?php echo htmlspecialchars($company['name']); ?>" />
                </td>
            </tr>

            <tr>
                <td align="center" colspan="2">
                    <input type="submit" name="butt_save" id="butt_save" value="<?php echo _l('Save'); ?>" class="submit_button save_button" />
                    <?php if((int)$company_id>0){ ?>
                    <input type="submit" name="butt_del" id="butt_del" value="<?php echo _l('Delete'); ?>" class="submit_button delete_button" />
                    <?php } ?>
                    <input type="button" name="cancel" value="<?php echo _l('Cancel'); ?>" onclick="window.location.href='<?php echo $module->link_open(false); ?>';" class="submit_button" />

                </td>
            </tr>
            </tbody>
        </table>

</form>
