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


if(!module_config::can_i('view','Settings')){
    redirect_browser(_BASE_HREF);
}

$search = isset($_REQUEST['search']) ? $_REQUEST['search'] : array();
$product_categories = module_product::get_product_categories($search);

$pagination = process_pagination($product_categories);

?>

<h2>
    <?php if(module_product::can_i('create','Products')){ ?>
	<span class="button">
		<?php echo create_link("Create New Category","add",module_product::link_open_category('new')); ?>
	</span>
        
        <?php 
    if(false && class_exists('module_import_export',false)){
        $link = module_import_export::import_link(
            array(
                'callback'=>'module_product::handle_import_category',
                'name'=>'product_categories',
                'return_url'=>$_SERVER['REQUEST_URI'],
                'fields'=>array(
                    'Category ID' => 'product_category_id',
                    'Product Name' => 'product_category_name',
                ),
            )
        );
        ?>
        <span class="button">
            <?php echo create_link("Import Product Categories","add",$link); ?>
        </span>
        <?php
    } ?>
    <?php } ?>
    <span class="title">
		<?php echo _l('Job/Invoice Product Categories'); ?>
	</span>
</h2>


<form action="" method="post">

<?php echo $pagination['summary'];?>
<table width="100%" border="0" cellspacing="0" cellpadding="2" class="tableclass tableclass_rows">
	<thead>
	<tr class="title">
		<th><?php echo _l('Category Name'); ?></th>
    </tr>
    </thead>
    <tbody>
    <?php
	$c=0;
	foreach($pagination['rows'] as $product){ ?>
        <tr class="<?php echo ($c++%2)?"odd":"even"; ?>">
            <td class="row_action">
	            <?php echo module_product::link_open_category($product['product_category_id'],true,$product); ?>
            </td>
        </tr>
	<?php } ?>
  </tbody>
</table>
<?php echo $pagination['links'];?>
</form>