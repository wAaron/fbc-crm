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

$search = isset($_REQUEST['search']) ? $_REQUEST['search'] : array();
if(isset($_REQUEST['faq_product_id']) && $_REQUEST['faq_product_id']){
    $search['faq_product_id'] = $_REQUEST['faq_product_id'];
}

$faqs = module_faq::get_faqs($search);

?>


<form action="" method="<?php echo _DEFAULT_FORM_METHOD;?>">

    <input type="hidden" name="customer_id" value="<?php echo isset($_REQUEST['customer_id']) ? (int)$_REQUEST['customer_id'] : '';?>">


    <table class="search_bar" width="100%">
        <tr>
            <td>
                <?php echo _l('Search Questions:');?>
            </td>
            <td>
                <input type="text" name="search[question]" value="<?php echo isset($search['question'])?htmlspecialchars($search['question']):''; ?>">
            </td>
            <td>
                <?php echo _l('Search Products:');?>
            </td>
            <td>
                <?php echo print_select_box(module_faq::get_faq_products_rel(),'search[faq_product_id]',isset($search['faq_product_id'])?$search['faq_product_id']:''); ?>
            </td>
            <td class="search_action">
                <?php echo create_link("Reset","reset",module_faq::link_open_public(-1)); ?>
                <?php echo create_link("Search","submit"); ?>
            </td>
        </tr>
    </table>

    <table width="100%" border="0" cellspacing="0" cellpadding="2" class="tableclass tableclass_rows">
        <thead>
        <tr class="title">
            <th><?php echo _l('Question'); ?></th>
            <th><?php echo _l('Products'); ?></th>
        </tr>
        </thead>
        <tbody>
        <?php
        $c=0;
        $products = module_faq::get_faq_products_rel();
        foreach($faqs as $data){
            $faq = module_faq::get_faq($data['faq_id']);
            ?>
        <tr class="<?php echo ($c++%2)?"odd":"even"; ?>">
            <td nowrap="">
                <a href="<?php echo module_faq::link_open_public($data['faq_id'],false);?>&faq_product_id=<?php echo isset($search['faq_product_id'])?(int)$search['faq_product_id']:'';?>"><?php echo htmlspecialchars($faq['question']); ?></a>
            </td>
            <td>
                <?php
                $items = array();
                foreach($faq['faq_product_ids'] as $faq_product_id){
                    if(module_faq::can_i('edit','FAQ')){
                        $items[]=module_faq::link_open_faq_product($faq_product_id,true);
                    }else{
                        $items[]=$products[$faq_product_id];
                    }
                }
                echo implode(', ',$items);
                ?>
            </td>
        </tr>
            <?php } ?>
        </tbody>
    </table>