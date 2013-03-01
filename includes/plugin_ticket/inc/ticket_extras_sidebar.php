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
//$ticket_id > 0 &&
if(module_config::c('ticket_allow_extra_data',1)){
    $extras = module_ticket::get_ticket_extras_keys($ticket['ticket_account_id']);
    if(count($extras)){
        ?>
    <h3><?php echo _l('Extra Information'); ?></h3>
<table border="0" cellspacing="0" cellpadding="2" class="tableclass tableclass_form tableclass_full">
    <tbody>
    <?php
        foreach($extras as $extra){
            ?>
        <tr>
            <th class="width1" style="white-space: nowrap;">
                <?php echo htmlspecialchars($extra['key']);?>
            </th>
            <td>
                <?php
                // we do a hook in here with the encryption plugin.
                // if some of these fields are marked for encryption we display a nice lock
                // and some stars instead of the value.
                // if there isn't a value then we don't display any stars, just a lock symbol

                // we also have to show an "unlock" button so the admin can unlock the value.

                if((!isset($extras_editable) || $extras_editable) ){
                    module_form::generate_form_element(array(
                    'type' => $extra['type'],
                    'name' => 'ticket_extra['.$extra['ticket_data_key_id'].']',
                    'value' => isset($ticket['extra_data'][$extra['ticket_data_key_id']]) ? $ticket['extra_data'][$extra['ticket_data_key_id']]['value'] : '',
                    'options' => isset($extra['options']) && $extra['options'] ? unserialize($extra['options']) : array(),
                    'class' => 'no_permissions',
                        // encryption is available on this field
                    'encrypt' => (class_exists('module_encrypt',false) && isset($extra['encrypt_key_id']) && $extra['encrypt_key_id']),
                    'page_name' => 'ticket_extras', // this is also set within ticket.php in public saving.
                    'id' => 'ticket_extras_'.$extra['ticket_data_key_id'], // this is also set within ticket.php in public saving.
                ));
                }else{
                    if(class_exists('module_encrypt',false) && isset($extra['encrypt_key_id']) && $extra['encrypt_key_id']){
                        echo '********';
                    }else{
                        echo h(isset($ticket['extra_data'][$extra['ticket_data_key_id']]) ? $ticket['extra_data'][$extra['ticket_data_key_id']]['value'] : '');
                    }
                }?>
            </td>
        </tr>
        <?php } ?>

    </tbody>
</table>
    <?php } ?>
<?php } ?>