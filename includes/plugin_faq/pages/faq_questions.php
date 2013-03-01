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


if(!module_config::can_i('view','Settings') || !module_faq::can_i('edit','FAQ')){
    redirect_browser(_BASE_HREF);
}

$faqs = module_faq::get_faqs();

if(isset($_REQUEST['faq_id']) && $_REQUEST['faq_id']){
    $show_other_settings=false;
    $faq_id = (int)$_REQUEST['faq_id'];
    if($faq_id > 0){
        $faq = module_faq::get_faq($faq_id);
    }else{
        $faq = array();
    }
    if(!$faq){
        $faq = array(
            'question' => '',
            'answer' => '',
            'faq_product_ids' => array(),
        );
    }
    ?>


<form action="" method="post">
    <input type="hidden" name="_process" value="save_faq">
    <input type="hidden" name="faq_id" value="<?php echo $faq_id; ?>" />
    <table cellpadding="10" width="100%">
        <tr>
            <td valign="top">
                <h3><?php echo _l('Edit FAQ'); ?></h3>

                <table width="100%" border="0" cellspacing="0" cellpadding="2" class="tableclass tableclass_form">
                    <tbody>
                    <tr>
                        <th class="width1">
                            <?php echo _l('Question'); ?>
                        </th>
                        <td>
                            <input type="text" name="question"  value="<?php echo htmlspecialchars($faq['question']); ?>" style="width:80%" />
                        </td>
                    </tr>
                    <tr>
                        <th>
                            <?php echo _l('Answer'); ?>
                        </th>
                        <td>
                            <textarea name="answer" cols="50" rows="10" style="width:80%"><?php echo htmlspecialchars($faq['answer']); ?></textarea>
                        </td>
                    </tr>
                    <tr>
                        <th>
                            <?php echo _l('Linked Products'); ?>
                        </th>
                        <td>
                            <?php
                            $default_types = module_ticket::get_types();
                            foreach(module_faq::get_faq_products_rel() as $faq_product_id => $product_name){
                            $faq_product = module_faq::get_faq_product($faq_product_id);
                            ?>
                            <div>
                                <input type="checkbox" name="faq_product_ids[]" value="<?php echo $faq_product_id;?>" id="multi_<?php echo $faq_product_id;?>" <?php echo in_array($faq_product_id,$faq['faq_product_ids']) ? ' checked' : '';?>>
                                <label for="multi_<?php echo $faq_product_id;?>"><?php echo htmlspecialchars($product_name);?> (<?php echo ($faq_product['default_type_id']) ? $default_types[$faq_product['default_type_id']] : _l('N/A');?>)</label>
                                <a href="<?php echo module_faq::link_open_faq_product($faq_product_id,false);?>">(edit)</a>
                                <br/>
                            </div>
                            <?php } ?>
                            <div>
                            <input type="checkbox" name="new_product_go" value="1"> <input type="text" name="new_product_name"> (new)
                            </div>
                        </td>
                    </tr>
                    <?php if($faq_id>0){ ?>

                    <tr>
                        <th>
                            <?php echo _l('Public Link'); ?>
                        </th>
                        <td>
                            <a href="<?php echo module_faq::link_open_public($faq_id); ?>" target="_blank"><?php _e('Open');?></a>
                        </td>
                    </tr>
                    <?php } ?>
                    </tbody>
                </table>

            </td>
        </tr>
        <tr>
            <td align="center">
                <input type="submit" name="butt_save" id="butt_save" value="<?php echo _l('Save'); ?>" class="submit_button save_button" />
                <input type="submit" name="butt_del" id="butt_del" value="<?php echo _l('Delete'); ?>" onclick="return confirm('<?php echo _l('Really delete this record?'); ?>');" class="submit_button" />
            </td>
        </tr>
    </table>

</form>

<?php
}else{
    ?>


<h2>
        <span class="button">
            <?php echo create_link("Add New FAQ","add",module_faq::link_open('new')); ?>
        </span>
    <?php echo _l('FAQs'); ?>
</h2>


<table width="100%" border="0" cellspacing="0" cellpadding="2" class="tableclass tableclass_rows">
    <thead>
    <tr class="title">
        <th><?php echo _l('Question'); ?></th>
        <th><?php echo _l('Linked FAQ Products'); ?></th>
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
            <td class="row_action" nowrap="">
                <?php echo module_faq::link_open($faq_id,true);?>
            </td>
            <td>
                <?php foreach($faq['faq_product_ids'] as $faq_product_id){
                echo module_faq::link_open_faq_product($faq_product_id,true)." ";
                } ?>
            </td>
        </tr>
            <?php } ?>
    </tbody>
</table>

<?php } ?>