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

$search = isset($_REQUEST['search']) ? $_REQUEST['search'] : array();
$products = module_product::get_products($search);

$pagination = process_pagination($products);

?>

<h2>
    <?php if(module_product::can_i('create','Products')){ ?>
	<span class="button">
		<?php echo create_link("Create New Product","add",module_product::link_open('new')); ?>
	</span>
        
        <?php 
    if(class_exists('module_import_export',false)){
        $link = module_import_export::import_link(
            array(
                'callback'=>'module_product::handle_import',
                'name'=>'Products',
                'return_url'=>$_SERVER['REQUEST_URI'],
                'fields'=>array(
                    'Product ID' => 'product_id',
                    'Product Name' => 'name',
                    'Hours/Qty' => 'quantity',
                    'Amount' => 'amount',
                    'Description' => 'description',
                ),
            )
        );
        ?>
        <span class="button">
            <?php echo create_link("Import Products","add",$link); ?>
        </span>
        <?php
    } ?>
    <?php } ?>
    <span class="title">
		<?php echo _l('Job/Invoice Products'); ?> (BETA!)
	</span>
</h2>


<form action="" method="post">

<?php echo $pagination['summary'];?>
<table width="100%" border="0" cellspacing="0" cellpadding="2" class="tableclass tableclass_rows">
	<thead>
	<tr class="title">
		<th><?php echo _l('Product Name'); ?></th>
		<th><?php echo _l('Hours/Quantity'); ?></th>
		<th><?php echo _l('Amount'); ?></th>
    </tr>
    </thead>
    <tbody>
    <?php
	$c=0;
	foreach($pagination['rows'] as $product){ ?>
        <tr class="<?php echo ($c++%2)?"odd":"even"; ?>">
            <td class="row_action">
	            <?php echo module_product::link_open($product['product_id'],true,$product); ?>
            </td>
            <td>
				<?php
                echo $product['quantity'];
				?>
            </td>
            <td>
				<?php
                echo dollar($product['amount']); //,true,$product['currency_id']
				?>
            </td>
        </tr>
	<?php } ?>
  </tbody>
</table>
<?php echo $pagination['links'];?>
</form>