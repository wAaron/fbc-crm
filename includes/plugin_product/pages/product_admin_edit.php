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

$product_id = (int)$_REQUEST['product_id'];
$product = array();

$product = module_product::get_product($product_id);

// check permissions.
if(class_exists('module_security',false)){
    if($product_id>0 && $product['product_id']==$product_id){
        // if they are not allowed to "edit" a page, but the "view" permission exists
        // then we automatically grab the page and regex all the crap out of it that they are not allowed to change
        // eg: form elements, submit buttons, etc..
		module_security::check_page(array(
            'category' => 'Product',
            'page_name' => 'Products',
            'module' => 'product',
            'feature' => 'Edit',
		));
    }else{
		module_security::check_page(array(
			'category' => 'Product',
            'page_name' => 'Products',
            'module' => 'product',
            'feature' => 'Create',
		));
	}
	module_security::sanatise_data('product',$product);
}

?>
<form action="" method="post" id="product_form">
	<input type="hidden" name="_process" value="save_product" />
	<input type="hidden" name="product_id" value="<?php echo $product_id; ?>" />

    <?php
    module_form::set_required(array(
        'fields' => array(
            'name' => 'Name',
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

				<h3><?php echo _l('Product Information'); ?></h3>

				<table width="100%" border="0" cellspacing="0" cellpadding="2" class="tableclass tableclass_form">
					<tbody>
						<tr>
							<th class="width1">
								<?php echo _l('Name'); ?>
							</th>
							<td>
								<input type="text" name="name" style="width:250px;" value="<?php echo htmlspecialchars($product['name']); ?>" />
							</td>
						</tr>
						<tr>
							<th>
								<?php echo _l('Hours/Quantity'); ?>
							</th>
							<td>
								<input type="text" name="quantity" value="<?php echo htmlspecialchars($product['quantity']); ?>" />
							</td>
						</tr>
						<tr>
							<th>
								<?php echo _l('Amount'); ?>
							</th>
							<td>
                                <?php echo currency('');?>
								<input type="text" name="amount" value="<?php echo htmlspecialchars($product['amount']); ?>" class="currency" />
                                <?php //echo print_select_box(get_multiple('currency','','currency_id'),'currency_id',$product['currency_id'],'',false,'code'); ?>
							</td>
						</tr>
                        <tr>
                            <th>
                                <?php echo _l('Description'); ?>
                            </th>
                            <td valign="top">
                                <textarea rows="6" cols="60" name="description"><?php echo htmlspecialchars($product['description']);?></textarea>
                            </td>
                        </tr>
                        <?php
                         /*module_extra::display_extras(array(
                            'owner_table' => 'product',
                            'owner_key' => 'product_id',
                            'owner_id' => $product_id,
                            'layout' => 'table_row',
                            )
                        );*/
                        ?>
					</tbody>
				</table>



			</td>




			<td width="50%" valign="top">
				<?php
				if($product_id && $product_id!='new'){

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

