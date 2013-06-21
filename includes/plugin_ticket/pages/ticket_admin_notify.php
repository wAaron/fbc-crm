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
if(!$ticket_safe)die('failed');
$ticket_id = (int)$_REQUEST['ticket_id'];
$ticket = module_ticket::get_ticket($ticket_id);

?>

<?php print_heading(_l('Notify Staff About Ticket: %s',module_ticket::ticket_number($ticket['ticket_id']))); ?>

<?php

module_template::init_template('ticket_email_notify','Dear {STAFF_NAME},<br>
<br>
A support ticket has been assigned to you.<br>
Ticket Number: <strong>{TICKET_NUMBER}</strong><br>
Ticket Subject: <strong>{TICKET_SUBJECT}</strong><br>
To view this ticket please <a href="{TICKET_URL}">click here</a>.<br><br>
Thank you,<br><br>
{FROM_NAME}
','Ticket Assigned: {TICKET_NUMBER}',array(
                                       'STAFF_NAME' => 'Staff Name',
                                       'TICKET_NUMBER' => 'Ticket Number',
                                       'TICKET_SUBJECT' => 'Ticket Subject',
                                       'TICKET_URL' => 'Link to ticket for customer',
                                       ));



// template for sending emails.
// are we sending the paid one? or the dueone.
$template = module_template::get_template_by_key('ticket_email_notify');
$ticket['ticket_number'] = module_ticket::ticket_number($ticket['ticket_id']);
$ticket['from_name'] = module_security::get_loggedin_name();
$ticket['ticket_url'] = module_ticket::link_open($ticket_id);
$ticket['ticket_subject'] = $ticket['subject'];

// sending to the staff member.
$to = module_user::get_user($ticket['assigned_user_id']);
$ticket['staff_name'] = $to['name'].' '.$to['last_name'];
$to = array($to);

$template->assign_values($ticket);


module_email::print_compose(
    array(
        'to'=>$to,
        'bcc'=>module_config::c('admin_email_address',''),
        'content' => $template->render('html'),
        'subject' => $template->replace_description(),
        'success_url'=>module_ticket::link_open($ticket_id),
        'cancel_url'=>module_ticket::link_open($ticket_id),
    )
);
?>