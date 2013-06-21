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


if(isset($_REQUEST['ticket_account_id']) && $_REQUEST['ticket_account_id']){
    $show_other_settings=false;
    $ticket_account_id = (int)$_REQUEST['ticket_account_id'];
    if($ticket_account_id > 0){
        $ticket_account = module_ticket::get_ticket_account($ticket_account_id);
    }else{
        $ticket_account = array(
            'name' => '',
            'email' => '',
            'username' => '',
            'password' => '',
            'host' => '',
            'port' => '110',
            'delete' => '0',
            'default_customer_id' => '0',
            'default_user_id' => '0',
            'default_type' => 0,
            'subject_regex' => '',
            'body_regex' => '',
            'to_regex' => '',
            'start_date' => '',
            'search_string' => '',
            'mailbox' => 'INBOX',
            'imap' => 0,
            'secure' => 0,
        );
    }
    ?>
<!-- updated -->

        <form action="" method="post">
            <input type="hidden" name="_process" value="save_ticket_account">
            <input type="hidden" name="ticket_account_id" value="<?php echo $ticket_account_id; ?>" />
            <table cellpadding="10" width="100%">
                <tr>
                    <td valign="top">
                        <h3><?php echo _l('Edit Ticket Account'); ?></h3>

                        <table width="100%" border="0" cellspacing="0" cellpadding="2" class="tableclass tableclass_form">
                            <tbody>
                                <tr>
                                    <th>
                                        <?php echo _l('Name/Label'); ?>
                                    </th>
                                    <td>
                                        <input type="text" name="name"  value="<?php echo htmlspecialchars($ticket_account['name']); ?>" />
                                    </td>
                                </tr>
                                <tr>
                                    <th>
                                        <?php echo _l('Email Address'); ?>
                                    </th>
                                    <td>
                                        <input type="text" name="email"  value="<?php echo htmlspecialchars($ticket_account['email']); ?>" />
                                    </td>
                                </tr>
                                <tr>
                                    <th>
                                        <?php echo _l('Connection Type'); ?>
                                    </th>
                                    <td>
                                        <input type="radio" name="imap" value="0"<?php echo !$ticket_account['imap']?' checked':'';?>><?php _e('POP3');?>
                                        <input type="radio" name="imap" value="1"<?php echo $ticket_account['imap']?' checked':'';?>><?php _e('IMAP');?>
                                    </td>
                                </tr>
                                <tr>
                                    <th>
                                        <?php echo _l('Secure/SSL'); ?>
                                    </th>
                                    <td>
                                        <input type="radio" name="secure" value="0"<?php echo !$ticket_account['secure']?' checked':'';?>><?php _e('No');?>
                                        <input type="radio" name="secure" value="1"<?php echo $ticket_account['secure']?' checked':'';?>><?php _e('Yes');?>
                                    </td>
                                </tr>
                                <tr>
                                    <th>
                                        <?php echo _l('Username'); ?>
                                    </th>
                                    <td>
                                        <input type="text" name="username"  value="<?php echo htmlspecialchars($ticket_account['username']); ?>" />
                                    </td>
                                </tr>
                                <tr>
                                    <th>
                                        <?php echo _l('Password'); ?>
                                    </th>
                                    <td>
                                        <input type="password" name="password"  value="<?php echo htmlspecialchars($ticket_account['password']); ?>" />
                                    </td>
                                </tr>
                                <tr>
                                    <th>
                                        <?php echo _l('Hostname'); ?>
                                    </th>
                                    <td>
                                        <input type="text" name="host"  value="<?php echo htmlspecialchars($ticket_account['host']); ?>" />
                                    </td>
                                </tr>
                                <tr>
                                    <th>
                                        <?php echo _l('Port'); ?>
                                    </th>
                                    <td>
                                        <input type="text" name="port"  value="<?php echo htmlspecialchars($ticket_account['port']); ?>" />
                                    </td>
                                </tr>
                                <tr>
                                    <th>
                                        <?php echo _l('Delete'); ?>
                                    </th>
                                    <td>
                                        <select name="delete">
                                            <option value="1"<?php echo $ticket_account['delete']==1 ? ' selected':'';?>>Yes, delete emails from account after importing</option>
                                            <option value="0"<?php echo $ticket_account['delete']==0 ? ' selected':'';?>>No, leave copy of email in account after importing</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <th>
                                        <?php echo _l('Default Customer'); ?>
                                    </th>
                                    <td>
                                        <?php
                                        $c = array();
                                        $res = module_customer::get_customers();
                                        while($row = array_shift($res)){
                                            $c[$row['customer_id']] = $row['customer_name'];
                                        }
                                        echo print_select_box($c,'default_customer_id',$ticket_account['default_customer_id']);
                                        ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th>
                                        <?php echo _l('Default Assigned Staff Members'); ?>
                                    </th>
                                    <td>
                                        <?php
                                        $admins_rel = module_ticket::get_ticket_staff_rel();
                                        echo print_select_box($admins_rel,'default_user_id',$ticket_account['default_user_id']);
                                        ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th>
                                        <?php echo _l('Default Type'); ?>
                                    </th>
                                    <td>
                                        <?php echo print_select_box(module_ticket::get_types(),'default_type',$ticket_account['default_type'],'',true); ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th>
                                        <?php echo _l('IMAP Settings:'); ?>
                                    </th>
                                    <td>

                                    </td>
                                </tr>
                                <tr>
                                    <th>
                                        <?php echo _l('IMAP Search String'); ?>
                                    </th>
                                    <td>
                                        <input type="text" name="search_string" value="<?php echo htmlspecialchars($ticket_account['search_string']); ?>" style="width:80%" ><br/>
                                        (eg: TO "you@site.com" SINCE 10-May-2011 - see <a href="http://php.net/imap_search" target="_blank">http://php.net/imap_search</a> for more details)
                                    </td>
                                </tr>
                                <tr>
                                    <th>
                                        <?php echo _l('Advanced Settings:'); ?>
                                    </th>
                                    <td>

                                    </td>
                                </tr>
                                <tr>
                                    <th>
                                        <?php echo _l('Mailbox'); ?>
                                    </th>
                                    <td>
                                        <input type="text" name="mailbox"  value="<?php echo htmlspecialchars($ticket_account['mailbox']); ?>" />
                                        (which mailbox to read / gmail label to read. Default is INBOX)
                                    </td>
                                </tr>
                                <tr>
                                    <th>
                                        <?php echo _l('Regex Subject Match'); ?>
                                    </th>
                                    <td>
                                        <input type="text" name="subject_regex"  value="<?php echo htmlspecialchars($ticket_account['subject_regex']); ?>" />
                                        (advanced: read about regex <a href="http://en.wikipedia.org/wiki/Regular_expression" target="_blank">here</a>, example: #support email#i)
                                    </td>
                                </tr>
                                <tr>
                                    <th>
                                        <?php echo _l('Regex Body Match'); ?>
                                    </th>
                                    <td>
                                        <input type="text" name="body_regex"  value="<?php echo htmlspecialchars($ticket_account['body_regex']); ?>" />
                                        (advanced: read about regex <a href="http://en.wikipedia.org/wiki/Regular_expression" target="_blank">here</a>, example: #support email#i)
                                    </td>
                                </tr>
                                <tr>
                                    <th>
                                        <?php echo _l('Regex To Match'); ?>
                                    </th>
                                    <td>
                                        <input type="text" name="to_regex"  value="<?php echo htmlspecialchars($ticket_account['to_regex']); ?>" />
                                        (advanced: read about regex <a href="http://en.wikipedia.org/wiki/Regular_expression" target="_blank">here</a>, example: #foo@bar\.com#i)
                                    </td>
                                </tr>
                                <tr>
                                    <th>
                                        <?php echo _l('Start Date'); ?>
                                    </th>
                                    <td>
                                        <input type="text" name="start_date" value="<?php echo print_date($ticket_account['start_date']); ?>" class="date_field" /> (only return emails after this date - SLOW! - better to use imap method with a SINCE search string)
                                    </td>
                                </tr>

                            </tbody>
                        </table>

                    </td>
                </tr>
                <tr>
                    <td align="center">
                        <input type="submit" name="butt_save" id="butt_save" value="<?php echo _l('Save'); ?>" class="submit_button save_button" />
                        <input type="submit" name="butt_save_test" value="<?php echo _l('Save &amp; Test Search'); ?>" class="submit_button" onclick="alert('This may take some time, please wait...');" />
                        <input type="submit" name="butt_del" id="butt_del" value="<?php echo _l('Delete'); ?>" onclick="return confirm('<?php echo _l('Really delete this record?'); ?>');" class="submit_button delete_button" />
                        <input type="button" name="cancel" value="<?php echo _l('Cancel'); ?>" onclick="window.location.href='<?php echo module_ticket::link_open_account(false); ?>';" class="submit_button" />

                    </td>
                </tr>
            </table>

        </form>

    <?php
}else{
    ?>


    <h2>
        <span class="button">
            <?php echo create_link("Add New Account","add",module_ticket::link_open_account('new')); ?>
        </span>
        <?php echo _l('Ticket Email Accounts'); ?>
    </h2>
    <?php

    $ticket_accounts = module_ticket::get_accounts();

    ?>


    <table width="100%" border="0" cellspacing="0" cellpadding="2" class="tableclass tableclass_rows">
        <thead>
        <tr class="title">
            <th><?php echo _l('Ticket Account'); ?></th>
            <th><?php echo _l('Email Address'); ?></th>
            <th><?php echo _l('Default Type'); ?></th>
            <th><?php echo _l('Default Customer'); ?></th>
            <th><?php echo _l('Default User'); ?></th>
            <th><?php echo _l('Last Checked'); ?></th>
        </tr>
        </thead>
        <tbody>
            <?php
            $c=0;
        $types = module_ticket::get_types();
            foreach($ticket_accounts as $ticket_account){
                ?>
                <tr class="<?php echo ($c++%2)?"odd":"even"; ?>">
                    <td class="row_action" nowrap="">
                        <?php echo module_ticket::link_open_account($ticket_account['ticket_account_id'],true);?>
                    </td>
                    <td>
                        <?php echo htmlspecialchars($ticket_account['email']); ?>
                    </td>
                    <td>
                        <?php echo htmlspecialchars( isset($types[$ticket_account['default_type']]) ? $types[$ticket_account['default_type']] : $ticket_account['default_type']); ?>
                    </td>
                    <td>
                        <?php echo module_customer::link_open($ticket_account['default_customer_id'],true); ?>
                    </td>
                    <td>
                        <?php echo module_user::link_open($ticket_account['default_user_id'],true); ?>
                    </td>
                    <td>
                        <?php echo print_date($ticket_account['last_checked']); ?>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>

<?php } ?>