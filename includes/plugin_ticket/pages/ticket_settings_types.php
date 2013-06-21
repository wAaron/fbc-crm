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

$ticket_types = module_ticket::get_types(false);

if(isset($_REQUEST['ticket_type_id']) && $_REQUEST['ticket_type_id']){
    $show_other_settings=false;
    $ticket_type_id = (int)$_REQUEST['ticket_type_id'];
    if($ticket_type_id > 0){
        $ticket_type = module_ticket::get_ticket_type($ticket_type_id);
    }else{
        $ticket_type = array();
    }
    if(!$ticket_type){
        $ticket_type = array(
            'name' => '',
            'public' => '1',
        );
    }
    ?>


        <form action="" method="post">
            <input type="hidden" name="_process" value="save_ticket_type">
            <input type="hidden" name="ticket_type_id" value="<?php echo $ticket_type_id; ?>" />
            <table cellpadding="10" width="100%">
                <tr>
                    <td valign="top">
                        <h3><?php echo _l('Edit Ticket Type'); ?></h3>

                        <table width="100%" border="0" cellspacing="0" cellpadding="2" class="tableclass tableclass_form">
                            <tbody>
                                <tr>
                                    <th class="width1">
                                        <?php echo _l('Type/Department'); ?>
                                    </th>
                                    <td>
                                        <input type="text" name="name"  value="<?php echo htmlspecialchars($ticket_type['name']); ?>" />
                                    </td>
                                </tr>
                                <tr>
                                    <th>
                                        <?php echo _l('Public'); ?>
                                    </th>
                                    <td>
                                        <?php echo print_select_box(get_yes_no(),'public',$ticket_type['public'],'',false);?>
                                        <?php echo _h('If this is public this option will display in the public ticket submission form.'); ?>
                                    </td>
                                </tr>
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
            <?php echo create_link("Add New Type","add",module_ticket::link_open_type('new')); ?>
        </span>
        <?php echo _l('Ticket Types/Departments'); ?>
    </h2>


    <table width="100%" border="0" cellspacing="0" cellpadding="2" class="tableclass tableclass_rows">
        <thead>
        <tr class="title">
            <th><?php echo _l('Type/Department'); ?></th>
            <th><?php echo _l('Public'); ?></th>
        </tr>
        </thead>
        <tbody>
            <?php
            $c=0;
            foreach($ticket_types as $ticket_type_id => $name){
                $ticket_type = module_ticket::get_ticket_type($ticket_type_id);
                ?>
                <tr class="<?php echo ($c++%2)?"odd":"even"; ?>">
                    <td class="row_action" nowrap="">
                        <?php echo module_ticket::link_open_type($ticket_type_id,true);?>
                    </td>
                    <td>
                        <?php echo htmlspecialchars($ticket_type['public']); ?>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>

<?php } ?>