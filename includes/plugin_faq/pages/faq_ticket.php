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

if(!isset($_REQUEST['iframe'])){
    $link = module_faq::link_open_list(isset($_REQUEST['faq_product_id'])?$_REQUEST['faq_product_id']:false);
    $link .= '&iframe&display_mode=iframe';
    echo '<iframe src="'.$link.'" style="width:100%; height:90%; border:0;" class="autosize" frameborder="0"></iframe>';
}else{

    $search = isset($_REQUEST['search']) ? $_REQUEST['search'] : array();
    if(isset($_REQUEST['faq_product_id']) && $_REQUEST['faq_product_id']){
        $search['faq_product_id'] = $_REQUEST['faq_product_id'];
    }

    $faqs = module_faq::get_faqs($search);

    ?>

    <h2>
        <?php if(module_faq::can_i('create','FAQ')){ ?>
            <span class="button">
                <?php echo create_link("Add New FAQ","add",module_faq::link_open('new')); ?>
            </span>
        <?php } ?>
        <?php echo _l('FAQs'); ?>
    </h2>

<form action="" method="POST">

    <input type="hidden" name="customer_id" value="<?php echo isset($_REQUEST['customer_id']) ? (int)$_REQUEST['customer_id'] : '';?>">


<table class="search_bar" width="100%">
	<tr>
        <td>
            <?php echo _l('Question:');?>
        </td>
        <td>
            <input type="text" name="search[question]" value="<?php echo isset($search['question'])?htmlspecialchars($search['question']):''; ?>">
        </td>
        <td width="30">
            <?php echo _l('Product:');?>
        </td>
        <td>
            <?php echo print_select_box(module_faq::get_faq_products_rel(),'search[faq_product_id]',isset($search['faq_product_id'])?$search['faq_product_id']:''); ?>
        </td>
        <td class="search_action">
            <?php echo create_link("Reset","reset",module_faq::link_open_list(false).'&iframe'); ?>
            <?php echo create_link("Search","submit"); ?>
        </td>
    </tr>
</table>

    <table width="100%" border="0" cellspacing="0" cellpadding="2" class="tableclass tableclass_rows">
        <thead>
        <tr class="title">
            <th><?php echo _l('Question'); ?></th>
            <th><?php echo _l('Linked FAQ Products'); ?></th>
            <?php //if(module_faq::can_i('edit','FAQ')){ ?>
            <th><?php echo _l('Action'); ?></th>
            <?php //} ?>
        </tr>
        </thead>
        <tbody>
            <?php
            $c=0;
            $products = module_faq::get_faq_products_rel();
            foreach($faqs as $faq_id => $data){
                $faq = module_faq::get_faq($faq_id);
                ?>
            <tr class="<?php echo ($c++%2)?"odd":"even"; ?>">
                <td nowrap="">
                    <?php if(module_faq::can_i('edit','FAQ')){
                        echo module_faq::link_open($faq_id,true);
                    }else{
                        ?> <a href="<?php echo str_replace('display_mode=iframe','',module_faq::link_open_public($faq_id)); ?>" target="_blank"><?php echo htmlspecialchars($faq['question']);?></a> <?php
                    }
                    ?>
                </td>
                <td>
                    <?php foreach($faq['faq_product_ids'] as $faq_product_id){
                        echo module_faq::link_open_faq_product($faq_product_id,true)." ";
                    } ?>
                </td>
                <?php //if(module_faq::can_i('edit','FAQ')){ ?>
                    <td>
                        <a href="<?php echo str_replace('display_mode=iframe','',module_faq::link_open_public($faq_id,false));?>" target="_blank" onclick="window.parent.jQuery('#new_ticket_message').val(window.parent.jQuery('#new_ticket_message').val() + $(this).attr('href')); window.parent.jQuery('.ui-dialog-content').dialog('close'); return false;"><?php _e('Insert Link');?></a>
                    </td>
                <?php //} ?>
            </tr>
                <?php } ?>
        </tbody>
    </table>

<?php } ?>