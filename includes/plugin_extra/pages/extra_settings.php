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



if(!module_config::can_i('edit','Settings')){
    redirect_browser(_BASE_HREF);
}

if(isset($_REQUEST['extra_default_id']) && $_REQUEST['extra_default_id']){
    $show_other_settings=false;
    $extra_default = module_extra::get_extra_default($_REQUEST['extra_default_id']);
    ?>


        <form action="" method="post">
            <input type="hidden" name="_process" value="save_extra_default">
            <input type="hidden" name="extra_default_id" value="<?php echo (int)$_REQUEST['extra_default_id']; ?>" />
            <table cellpadding="10" width="100%">
                <tr>
                    <td valign="top">
                        <h3><?php echo _l('Edit Extra Default Field'); ?></h3>

                        <table width="100%" border="0" cellspacing="0" cellpadding="2" class="tableclass tableclass_form">
                            <tbody>
                                <tr>
                                    <th>
                                        <?php echo _l('Name/Label'); ?>
                                    </th>
                                    <td>
                                        <input type="text" name="extra_key"  value="<?php echo htmlspecialchars($extra_default['extra_key']); ?>" />
                                    </td>
                                </tr>
                                <tr>
                                    <th>
                                        <?php echo _l('Table'); ?>
                                    </th>
                                    <td>
                                        <?php echo htmlspecialchars($extra_default['owner_table']); ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th>
                                        <?php echo _l('Visibility'); ?>
                                    </th>
                                    <td>
                                        <?php echo print_select_box(module_extra::get_display_types(),'display_type',$extra_default['display_type'],'',false); ?>
                                        <?php _h('Default will display the extra field when opening an item (eg: opening a customer). If a user can view the customer they will be able to view the extra field information when viewing the customer. Public In Column means that this extra field will also display in the overall listing (eg: customer listing). More options coming soon (eg: private)'); ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th>
                                        <?php echo _l('Order'); ?>
                                    </th>
                                    <td>
                                        <input type="text" name="order"  value="<?php echo htmlspecialchars($extra_default['order']); ?>" />
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                    </td>
                </tr>
                <tr>
                    <td align="center">
                        <input type="submit" name="butt_save" id="butt_save" value="<?php echo _l('Save'); ?>" class="submit_button save_button" />
                       <!-- <input type="submit" name="butt_del" id="butt_del" value="<?php echo _l('Delete'); ?>" onclick="return confirm('<?php echo _l('Really delete this record?'); ?>');" class="submit_button" /> todo: make delete button only display once all 'extra' data is removed -->


                    </td>
                </tr>
            </table>

        </form>

    <?php
}else{
    ?>


    <h2>
        <!-- <span class="button">
            <?php echo create_link("Add New Field","add",module_extra::link_open_extra_default('new')); ?>
        </span> -->
        <?php echo _l('Extra Fields'); ?>
    </h2>
    <?php

    $extra_defaults = module_extra::get_defaults();
    $visibility_types = module_extra::get_display_types();
    ?>


    <table width="100%" border="0" cellspacing="0" cellpadding="2" class="tableclass tableclass_rows">
        <thead>
        <tr class="title">
            <th><?php echo _l('Section'); ?></th>
            <th><?php echo _l('Extra Field'); ?></th>
            <th><?php echo _l('Display Type'); ?></th>
            <th><?php echo _l('Order'); ?></th>
        </tr>
        </thead>
        <tbody>
            <?php
            $c=0;
            foreach($extra_defaults as $owner_table => $owner_table_defaults){
                foreach($owner_table_defaults as $owner_table_default){
                ?>
                <tr class="<?php echo ($c++%2)?"odd":"even"; ?>">
                    <td>
                        <?php echo htmlspecialchars($owner_table);?>
                    </td>
                    <td class="row_action" nowrap="">
                        <?php echo module_extra::link_open_extra_default($owner_table_default['extra_default_id'],true);?>
                    </td>
                    <td>
                        <?php echo isset($visibility_types[$owner_table_default['display_type']]) ? $visibility_types[$owner_table_default['display_type']] : 'N/A'; ?>
                    </td>
                    <td>
                        <?php echo htmlspecialchars($owner_table_default['order']); ?>
                    </td>
                </tr>
            <?php }
            } ?>
        </tbody>
    </table>

<?php } ?>