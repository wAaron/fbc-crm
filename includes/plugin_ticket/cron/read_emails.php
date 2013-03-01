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


$autoreply_queue = array();

//set_time_limit(10);

// find all the mail setting accounts to check.
foreach(module_ticket::get_accounts() as $account){


    $updated_tickets = module_ticket::import_email($account['ticket_account_id']);
    if(is_array($updated_tickets)){
        $autoreply_queue = array_merge($autoreply_queue,$updated_tickets);
    }

}
imap_errors();

//print_r($autoreply_queue);


foreach($autoreply_queue as $ticket_id){


    ob_start();
    handle_hook('ticket_sidebar',$ticket_id); // to get envato hook working quicker
    ob_end_clean();

    // we have to send the email to admin notifying them about this ticket too.
    // if this latest email came from an admin user (ie: the user is replying to a customer via email)
    // then we don't send_admin_alert or autoreply, we just send reply back to customer.
    $ticket_data = module_ticket::get_ticket($ticket_id);
    $last_ticket_message = module_ticket::get_ticket_message($ticket_data['last_ticket_message_id']);
    $admins_rel = module_ticket::get_ticket_staff_rel();
    // if the last email was from admin, send customer alert.
    if(isset($admins_rel[$last_ticket_message['from_user_id']])){
//        echo "sending a customer alert ";
//        print_r($last_ticket_message);
        module_ticket::send_customer_alert($ticket_id);
    }else{
        // last email must have been from a customer
        // alert the admin to it, and send an auto reply if the message is the first.
        module_ticket::send_admin_alert($ticket_id);
        //echo "Sent an alert to admin... sending autoreply...";
        //print_r($ticket_data);
        //echo "<br><br>";
        if(module_config::c('ticket_autoreply_every_message',0) || $ticket_data['message_count']<=1){
            module_ticket::send_autoreply($ticket_id);
        }
    }


}