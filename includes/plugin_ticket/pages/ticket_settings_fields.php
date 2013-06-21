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

if(isset($_REQUEST['ticket_data_key_id']) && $_REQUEST['ticket_data_key_id']){
    $show_other_settings=false;
    $ticket_data_key_id = (int)$_REQUEST['ticket_data_key_id'];
    if($ticket_data_key_id > 0){
        $ticket_data_key = module_ticket::get_ticket_extras_key($ticket_data_key_id);
    }else{
        $ticket_data_key = array(
            'ticket_account_id' => '',
            'key' => '',
            'options' => '',
            'type' => '',
            'order' => '',
        );
    }
    ?>


        <form action="" method="post">
            <input type="hidden" name="_process" value="save_ticket_data_key">
            <input type="hidden" name="ticket_data_key_id" value="<?php echo $ticket_data_key_id; ?>" />
            <table cellpadding="10" width="100%">
                <tr>
                    <td valign="top">
                        <h3><?php echo _l('Edit Ticket Data Key'); ?></h3>

                        <table width="100%" border="0" cellspacing="0" cellpadding="2" class="tableclass tableclass_form">
                            <tbody>
                                <tr>
                                    <th class="width1">
                                        <?php echo _l('Name/Label'); ?>
                                    </th>
                                    <td>
                                        <input type="text" name="key"  value="<?php echo htmlspecialchars($ticket_data_key['key']); ?>" />
                                    </td>
                                </tr>
                                <tr>
                                    <th>
                                        <?php echo _l('Type'); ?>
                                    </th>
                                    <td>
                                        <input type="radio" name="type" value="text"<?php echo $ticket_data_key['type']=='text' ? ' checked':'';?>><?php _e('Text');?><br/>
                                        <input type="radio" name="type" value="textarea"<?php echo $ticket_data_key['type']=='textarea' ? ' checked':'';?>><?php _e('Text Area');?><br/>
                                        <input type="radio" name="type" value="select"<?php echo $ticket_data_key['type']=='select' ? ' checked':'';?>><?php _e('Select');?><br/>
                                    </td>
                                </tr>
                                <tr>
                                    <th>
                                        <?php echo _l('Order'); ?>
                                    </th>
                                    <td>
                                        <input type="text" name="order"  value="<?php echo htmlspecialchars($ticket_data_key['order']); ?>" />
                                    </td>
                                </tr>
                                <?php if($ticket_data_key['type']=='select'){
                                    $options = isset($ticket_data_key['options']) && $ticket_data_key['options'] ? unserialize($ticket_data_key['options']) : array();
                                    ?>
                                    <tr>
                                        <th>
                                            <?php _e('Drop Down Values:');?>
                                        </th>
                                        <td>
                                            <textarea rows="9" cols="30" name="options"><?php foreach($options as $key=>$val){
                                                if(!strlen($val))continue;
                                                if(!is_numeric($key) && $key!=$val)echo "$key|";
                                                echo $val."\n";
                                            } ?></textarea> <?php _h('Drop down values, one per line'); ?>
                                        </td>
                                    </tr>
                                <?php } ?>
                                <?php if(class_exists('module_encrypt',false) && ($ticket_data_key['type'] == 'text' || $ticket_data_key['type'] == 'textarea')){ ?>
                                <tr>
                                    <th>
                                        <?php echo _l('Encrypt Using Vault'); ?>
                                    </th>
                                    <td>
                                        <?php
                                        $encryption_keys = module_encrypt::get_encrypt_keys();
                                        echo print_select_box($encryption_keys,'encrypt_key_id',isset($ticket_data_key['encrypt_key_id'])?$ticket_data_key['encrypt_key_id']:false,'',true,'encrypt_key_name',false); ?>
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
            <?php echo create_link("Add New Field","add",module_ticket::link_open_field('new')); ?>
        </span>
        <?php echo _l('Ticket Extra Fields'); ?>
    </h2>
    <?php

    $ticket_data_keys = module_ticket::get_ticket_extras_keys();

    ?>


    <table width="100%" border="0" cellspacing="0" cellpadding="2" class="tableclass tableclass_rows">
        <thead>
        <tr class="title">
            <th><?php echo _l('Ticket Extra Field'); ?></th>
            <th><?php echo _l('Name'); ?></th>
            <th><?php echo _l('Type'); ?></th>
            <th><?php echo _l('Order'); ?></th>
        </tr>
        </thead>
        <tbody>
            <?php
            $c=0;
            foreach($ticket_data_keys as $ticket_data_key){
                ?>
                <tr class="<?php echo ($c++%2)?"odd":"even"; ?>">
                    <td class="row_action" nowrap="">
                        <?php echo module_ticket::link_open_field($ticket_data_key['ticket_data_key_id'],true);?>
                    </td>
                    <td>
                        <?php echo htmlspecialchars($ticket_data_key['key']); ?>
                    </td>
                    <td>
                        <?php echo htmlspecialchars($ticket_data_key['type']); ?>
                    </td>
                    <td>
                        <?php echo htmlspecialchars($ticket_data_key['order']); ?>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>

<?php } ?>