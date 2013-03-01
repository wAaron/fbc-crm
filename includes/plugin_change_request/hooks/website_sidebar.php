<h3><?php 
/** 
  * Copyright: dtbaker 2012
  * Licence: Please check CodeCanyon.net for licence details. 
  * More licence clarification available here:  http://codecanyon.net/wiki/support/legal-terms/licensing-terms/ 
  * Deploy: 3053 c28b7e0e323fd2039bb168d857c941ee
  * Envato: 6b31bbe6-ead4-44a3-96e1-d5479d29505b
  * Package Date: 2013-02-27 19:09:56 
  * IP Address: 
  */ echo _l('Change Request Settings'); ?> (BETA)</h3>
<table border="0" cellspacing="0" cellpadding="2" class="tableclass tableclass_form tableclass_full">
    <tbody>
    <tr>
        <th class="width1">
            <?php _e('Enable Changes');?>
        </th>
        <td>
            <?php echo print_select_box(get_yes_no(),'change_request[enabled]',isset($change_request_website['enabled'])?$change_request_website['enabled']:'0','',false); ?>
            <?php _h('Allow change requests. This SUPER COOL feature allows your customer to highlight something on their website and request a change. The "change request" will come through here and you can invoice for that change.'); ?>
        </td>
    </tr>
    <tr>
        <th>
            <?php _e('Website Code');?>
        </th>
        <td>
            <?php
            $script = '<script type="text/javascript" language="javascript" src="'.full_link('includes/plugin_change_request/js/public.js').'"></script>';
            $script .= '<script type="text/javascript" language="javascript">dtbaker_public_change_request.init("'.module_change_request::link_script($website_id).'");</script>';
            ?>
            <input type="text"  value="<?php echo htmlspecialchars($script);?>">
            <?php
            _h('Add this code to EVERY PAGE of your customers website (eg: Same as Google Analytics or in WordPress theme footer.php file). Make sure this is loaded AFTER jQuery is loaded. (Advanced users can copy this public.js file to clients website to improve load times).');
            ?>
        </td>
    </tr>
    <tr>
        <th>
            <?php _e('Customer Limit');?>
        </th>
        <td>
            <input type="text" name="change_request[limit_number]" value="<?php echo isset($change_request_website['limit_number'])?$change_request_website['limit_number']:5;?>" size="4">
            <?php _e('per');?>
            <?php echo print_select_box(array(
            '1' => _l('Week'),
            '2' => _l('Month'),
            '3' => _l('Year'),
            '0' => _l('All Time'),
        ),'change_request[limit_per]',isset($change_request_website['limit_per'])?$change_request_website['limit_per']:0,'',false);
            ?>
            <?php _h('You can limit your customer to a certain number of change requests (eg: if you are charging them a monthly maintenance fee)');?>
            <br/>
            <?php
            $change_history = module_change_request::get_remaining_changes($website_id);
            ?>
            <strong><?php echo max(0,$change_history[1] - $change_history[0]);?></strong> of <?php echo $change_history[1];?> <?php _e('changes remaining');?>
        </td>
    </tr>
    <tr>
        <th>
            <?php _e('Request Link');?>
        </th>
        <td>
            <a href="<?php echo module_change_request::link_public($website_id);?>" target="_blank"><?php _e('Request Change Link');?></a>
            <?php _h('This is the special link you can email to your customer. Using this link the customer can request a change on their website.');?>
        </td>
    </tr>
    <!-- <tr>
        <th>
            <?php _e('Email');?>
        </th>
        <td>
            <input type="submit" name="butt_change_request_send" value="<?php _e('Send Email Instructions');?>" class="submit_button">
            <?php _h('This sends a customisable email with instructions on how to request a change'); ?>
        </td>
    </tr> -->
    </tbody>
</table>