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

if(!$ticket_safe){
    die('failed');
}


$ticket_id = (int)$_REQUEST['ticket_id'];
$ticket = module_ticket::get_ticket($ticket_id);

if($ticket['subject']){
    $module->page_title = _l('Ticket: '.htmlspecialchars($ticket['subject']));
}

$admins_rel = module_ticket::get_ticket_staff_rel();

// work out if this user is an "administrator" or a "customer"
// a user will have "edit" capabilities for tickets if they are an administrator
// a user will only have "view" Capabilities for tickets if they are a "customer"
// this will decide what options they have on the page (ie: assigning tickets to people)


if($ticket_id>0 && $ticket && $ticket['ticket_id']==$ticket_id){
	if(class_exists('module_security',false)){
		/*module_security::check_page(array(
            'module' => $module->module_name,
            'feature' => 'edit',
		));*/
        // we want to do our own special type of form modification here
        // so we don't pass it off to "check_page" which will hide all input boxes.
        if(!module_ticket::can_i('edit','Tickets') && !module_ticket::can_i('create','Tickets')){
            set_error('Access to editing or creating tickets is denied.');
            redirect_browser(module_ticket::link_open(false));
        }
	}
}else{
    $ticket_id = false;
	if(class_exists('module_security',false)){
		module_security::check_page(array(
            'module' => $module->module_name,
            'feature' => 'create',
		));
	}
}

if(module_ticket::can_edit_tickets()){
    module_ticket::mark_as_read($ticket_id,true);
}
$module->pre_menu(); // so the links are re-build and the correct "unread" count is at the top.


if(!module_security::can_access_data('ticket',$ticket)){
    echo 'Ticket access denied';
    exit;
}

$ticket_messages = module_ticket::get_ticket_messages($ticket['ticket_id']);

if(!isset($logged_in_user) || !$logged_in_user){
    // we assume the user is on the public side.
    // use the creator id as the logged in id.
    $logged_in_user = module_security::get_loggedin_id();
}
$ticket_creator = $ticket['user_id'];
if($ticket_creator == $logged_in_user){
    // we are sending a reply back to the admin, from the end user.
    $to_user_id = $ticket['assigned_user_id'] ? $ticket['assigned_user_id'] : 1;
    $from_user_id = $logged_in_user;
}else{
    // we are sending a reply back to the ticket user.
    $to_user_id = $ticket['user_id'];
    $from_user_id = $logged_in_user;
}
$to_user_a = module_user::get_user($to_user_id,false);
$from_user_a = module_user::get_user($from_user_id,false);

if(isset($ticket['ticket_account_id']) && $ticket['ticket_account_id']){
    $ticket_account = module_ticket::get_ticket_account($ticket['ticket_account_id']);
}else{
    $ticket_account = false;
}


if($ticket_account && $ticket_account['email']){
    $reply_to_address = $ticket_account['email'];
    $reply_to_name = $ticket_account['name'];
}else{
    // reply to creator.
    $reply_to_address = $from_user_a['email'];
    $reply_to_name = $from_user_a['name'];
}


if($ticket_creator == $logged_in_user){
    $send_as_name = $from_user_a['name'];
    $send_as_address = $from_user_a['email'];
}else{
    $send_as_address = $reply_to_address;
    $send_as_name = $reply_to_name;
}



$last_response_from = 'admin'; // or customer.

// find the prev/next tickets.
$temp_prev = $prev_ticket = $next_ticket = false;
$temp_tickets = isset($_SESSION['_ticket_nextprev']) ? $_SESSION['_ticket_nextprev'] : array();
foreach($temp_tickets as $key=>$val){
    if($prev_ticket && !$next_ticket){
        $next_ticket = $val;
    }
    if($val==$ticket_id){
        $prev_ticket = ($temp_prev)?$temp_prev:true;
    }
    $temp_prev = $val;
}

ob_start(); ?>
<script type="text/javascript">
    ucm.ticket = {};
    ucm.ticket.add_to_message = function(content){
        <?php if(module_config::c('ticket_message_text_or_html','html')=='html'){ ?>
        content = content.replace(/\n/g,"<br/>\n");
        tinyMCE.activeEditor.execCommand('mceInsertContent', false, content);
        <?php }else{ ?>
        $('#new_ticket_message').val(
                $('#new_ticket_message').val() + content
        );
        <?php } ?>
        return false;
    }
</script>

    <p align="center">
    <input type="submit" name="butt_save" id="butt_save" value="<?php echo _l('Save details'); ?>" class="submit_button save_button" />
    <?php if((int)$ticket_id && module_ticket::can_i('delete','Tickets')){ ?>
    <input type="submit" name="butt_del" id="butt_del" value="<?php echo _l('Delete'); ?>" class="submit_button delete_button" />
    <?php } ?>
    <input type="button" name="cancel" value="<?php echo _l('Cancel'); ?>" onclick="window.location.href='<?php echo module_ticket::link_open(false); ?>';" class="submit_button" />
    <?php if((int)$ticket_id && module_ticket::can_edit_tickets()){ ?>
        <input type="submit" name="mark_as_unread" value="<?php echo _l('Mark as unread'); ?>" class="submit_button" />
    <?php } ?>
</p>
<?php $action_buttons = ob_get_clean(); ?>
	
<form action="#" method="post" id="ticket_form" enctype="multipart/form-data">
	<input type="hidden" name="_process" value="save_ticket" />
    <input type="hidden" name="ticket_id" value="<?php echo $ticket['ticket_id']; ?>" />

    <!-- next / prev links -->
    <table width="80%" align="center">
        <tbody>
        <tr>
            <td align="left">
                <?php if($prev_ticket && $prev_ticket!==true){ ?>
                <a href="<?php echo module_ticket::link_open($prev_ticket);?>" class="uibutton">&laquo; <?php _e('Prev Ticket');?></a>
                <?php } ?>
            </td>
            <td align="center">
                <?php echo $action_buttons;?>
            </td>
            <td align="right">
                <?php if($next_ticket){ ?>
                <a href="<?php echo module_ticket::link_open($next_ticket);?>" class="uibutton"><?php _e('Next Ticket');?> &raquo;</a>
                <?php } ?>
            </td>
        </tr>
        </tbody>
    </table>

    <?php

    $fields = array(
    'fields' => array(
        'subject' => 'Subject',
    ));
    module_form::set_required(
        $fields
    );
    module_form::prevent_exit(array(
        'valid_exits' => array(
            // selectors for the valid ways to exit this form.
            '.submit_button',
            '.save_task',
            '.notify',
            '.delete',
            '.attachment_link',
        ))
    );
    

    ?>

	<table cellpadding="10" width="100%">
		<tbody>
			<tr>
				<td valign="top" width="35%">
					<h3><?php echo _l('Ticket Details'); ?></h3>



					<table border="0" cellspacing="0" cellpadding="2" class="tableclass tableclass_form tableclass_full">
						<tbody>
							<tr>
								<th class="width1">
									<?php echo _l('Ticket Number'); ?>
								</th>
								<td>
									<span class="ticket_status_<?php echo (int)$ticket['status_id'];?>"><?php echo module_ticket::ticket_number($ticket['ticket_id']);?></span>
                                    <?php
                                    if($ticket['status_id'] == 2 || $ticket['status_id'] == 3 || $ticket['status_id'] == 5){
                                        echo _l('(%s out of %s tickets)',ordinal($ticket['position']),$ticket['total_pending']);
                                    } 
                                    ?>
                                    <input type="hidden" name="status_id" value="<?php echo $ticket['status_id'];?>" />
								</td>
							</tr>
                            <?php if($ticket['last_message_timestamp']){ ?>
                            <tr>
                                <th>
                                    <?php _e('Date/Time');?>
                                </th>
                                <td>
                                    <?php
                                    if($ticket['last_message_timestamp'] < $limit_time){
                                        echo '<span class="important">';
                                    }
                                    echo print_date($ticket['last_message_timestamp'],true);
                                    // how many days ago was this?
                                    echo ' ';
                                    $days = ceil((($ticket['last_message_timestamp']+1) - time())/86400);
                                    if(abs($days) == 0){
                                        _e('(today)');
                                    }else{
                                        _e(' (%s days ago)',abs($days));
                                    }
                                    if($ticket['last_message_timestamp'] < $limit_time){
                                        echo '</span>';
                                    }
                                    ?>
                                </td>
                            </tr>
                            <?php } ?>
                            <tr>
                                <th>
                                    <?php _e('Subject');?>
                                </th>
                                <td>
                                    <?php if($ticket['subject']){
                                    echo htmlspecialchars($ticket['subject']);
                                }else{ ?>
                                    <input type="text" name="subject" id="subject" value="<?php echo htmlspecialchars($ticket['subject']); ?>" />
    <?php } ?>
                                </td>
                            </tr>
							<tr>
								<th>
									<?php echo _l('Assigned Staff'); ?>
								</th>
								<td>
									<?php
                                    if(module_ticket::can_edit_tickets()){
                                        echo print_select_box($admins_rel,'assigned_user_id',$ticket['assigned_user_id']);
                                        echo _h('This is anyone with ticket EDIT permissions.');
                                        ?>

                                        <input type="submit" name="butt_notify_staff" value="<?php _e('Notify');?>" class="notify small_button">
                                        <?php
                                    }else{
                                        echo friendly_key($admins_rel,$ticket['assigned_user_id']);
                                    }
                                    ?>
								</td>
							</tr>
							<tr>
								<th>
									<?php echo _l('Assigned Contact'); ?>
								</th>
								<td>
									<?php
                                    $create_user = module_user::get_user($ticket['user_id'],false);
                                    if(module_ticket::can_edit_tickets() && !(int)$ticket_id){
                                        $c = array();
                                        if($ticket['customer_id']){
                                            $res = module_user::get_contacts(array('customer_id'=>$ticket['customer_id']));
                                        }else{
                                            $res = array();
                                        }
                                        while($row = array_shift($res)){
                                            $c[$row['user_id']] = $row['name'].' '.$row['last_name'];
                                        }
                                        if($ticket['user_id'] && !isset($c[$ticket['user_id']])){
                                            // this option isn't in the listing. add it in.
                                            $c[$ticket['user_id']] = $create_user['name'].' '.$create_user['last_name'];
                                            if($create_user['customer_id']>=0) $c[$ticket['user_id']] .= ' '._l('(under different customer)');
                                            else{
                                                // user not assigned to a customer.
                                            }
                                        }
                                        echo print_select_box($c,'change_user_id',$ticket['user_id']);
                                    }else{
                                        //
                                        if($create_user['customer_id']){
                                            echo module_user::link_open_contact($ticket['user_id'],true);
                                        }else{
                                            echo module_user::link_open($ticket['user_id'],true);
                                        }
                                        echo ' ' .htmlspecialchars($create_user['email']);
                                    }
                                    ?>
								</td>
							</tr>
							<tr>
								<th>
									<?php echo _l('Type/Department'); ?>
								</th>
								<td>
									<?php
                                    if(module_ticket::can_edit_tickets()){
                                        echo print_select_box(module_ticket::get_types(),'ticket_type_id',$ticket['ticket_type_id'],'',true);
                                        //echo print_select_box(module_ticket::get_types(),'type',$ticket['type'],'',true,false,true);
                                    }else{
                                        echo print_select_box(module_ticket::get_types(),'ticket_type_id',$ticket['ticket_type_id'],'',false);
                                    }
                                    ?>
								</td>
							</tr>
                            <?php if(class_exists('module_faq',false) && module_config::c('ticket_faq_link',1) && module_faq::get_faq_products()>0){ ?>
                                <tr>
                                    <th>
                                        <?php echo _l('Product'); ?>
                                    </th>
                                    <td>
                                        <?php
                                        if(module_ticket::can_edit_tickets()){
                                            echo print_select_box(module_faq::get_faq_products_rel(),'faq_product_id',$ticket['faq_product_id']);
                                            _h('Use this to link a ticket to a product. Set products in Settings > FAQ. This allows you to have different FAQ items for different products. Users are shown the FAQ items before submitting a support ticket.');
                                        }else{
                                            echo friendly_key(module_faq::get_faq_products_rel(),$ticket['faq_product_id']);
                                        }
                                        // show a button that does a jquery popup with the list of faq items and an option to create new one.
                                        //if(module_faq::can_i('edit','FAQ')){                                                                            echo ' ';
                                            echo popup_link('<a href="'.module_faq::link_open_list($ticket['faq_product_id']).'">'._l('FAQ').'</a>',array(
                                                'force'=>true,
                                                'width'=>1100,
                                                'height'=>600,
                                            ));
                                        //}
                                        ?>
                                    </td>
                                </tr>

                            <?php } ?>
                            <?php if(module_config::c('ticket_support_accounts',1) && module_ticket::get_accounts_rel()){ ?>
							<tr>
								<th>
									<?php echo _l('Account'); ?>
								</th>
								<td>
									<?php
                                    if(module_ticket::can_edit_tickets()){
                                        echo print_select_box(module_ticket::get_accounts_rel(),'ticket_account_id',$ticket['ticket_account_id']);
                                    }else{
                                        echo friendly_key(module_ticket::get_accounts_rel(),$ticket['ticket_account_id']);
                                    }
                                    ?>
								</td>
							</tr>
                            <?php } ?>
							<tr>
								<th>
									<?php echo _l('Status'); ?>
								</th>
								<td>
									<?php
                                    if(module_ticket::can_edit_tickets()){
                                        echo print_select_box(module_ticket::get_statuses(),'status_id',$ticket['status_id']);
                                    }else{
                                        echo friendly_key(module_ticket::get_statuses(),$ticket['status_id']);
                                    }
                                    ?>
								</td>
							</tr>
                            <?php if (module_ticket::can_edit_tickets() || module_config::c('ticket_allow_priority_selection',0)){

                                $priorities = module_ticket::get_ticket_priorities();
                                if(!module_ticket::can_edit_tickets() && isset($priorities[_TICKET_PRIORITY_STATUS_ID]) && $ticket['priority'] != _TICKET_PRIORITY_STATUS_ID){
                                    unset($priorities[_TICKET_PRIORITY_STATUS_ID]);
                                }
                                ?>

                                <tr>
                                    <th>
                                        <?php echo _l('Priority'); ?>
                                    </th>
                                    <td>
                                        <?php
                                            echo print_select_box($priorities,'priority',$ticket['priority'],'',false);
                                        ?>
                                    </td>
                                </tr>
                            <?php } ?>
						</tbody>
                        <?php
                        if(isset($ticket['ticket_id']) && $ticket['ticket_id'] && module_ticket::can_edit_tickets()){
                             module_extra::display_extras(array(
                                'owner_table' => 'ticket',
                                'owner_key' => 'ticket_id',
                                'owner_id' => $ticket['ticket_id'],
                                'layout' => 'table_row',
                                )
                            );
                        }
                        ?>
					</table>


                    <?php
                    if($ticket['user_id']){
                        $other_tickets = module_ticket::get_tickets(array('user_id'=>$ticket['user_id']));
                        if(mysql_num_rows($other_tickets)>1){
                            ?>
                        <h3><?php _e('%s Other Support Tickets',mysql_num_rows($other_tickets)); ?></h3>
                        <table border="0" cellspacing="0" cellpadding="2" class="tableclass tableclass_form tableclass_full tbl_fixed">
                            <tbody>
                                <?php while($other_ticket = mysql_fetch_assoc($other_tickets)){ ?>
                            <tr>
                                <td style="width:55px;">
                                    <?php echo $other_ticket['ticket_id'] == $ticket_id ? '&raquo;':'';?>
                                    <?php echo module_ticket::link_open($other_ticket['ticket_id'],true);?>
                                </td>
                                <td>
                                    <?php if($other_ticket['priority']==_TICKET_PRIORITY_STATUS_ID){ echo '$'; } ?>
                                    <?php echo htmlspecialchars($other_ticket['subject']);?>
                                </td>
                                <td style="width:100px;">
                                    <?php echo htmlspecialchars(module_ticket::$ticket_statuses[$other_ticket['status_id']]); ?>
                                </td>
                            </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                            <?php
                        }
                    } ?>

                    <?php
                    if((int)$ticket_id > 0 && file_exists(dirname(__FILE__).'/../inc/ticket_priority_sidebar.php')){
                        //if($ticket['priority'] == _TICKET_PRIORITY_STATUS_ID || (isset($ticket['invoice_id']) && $ticket['invoice_id'])){
                            include(dirname(__FILE__).'/../inc/ticket_priority_sidebar.php');
                       // }
                    }
                    //(int)$ticket_id > 0 &&
                    if(file_exists(dirname(__FILE__).'/../inc/ticket_extras_sidebar.php')){
                        include(dirname(__FILE__).'/../inc/ticket_extras_sidebar.php');
                    }  ?>


                    <?php
                    if(isset($ticket['ticket_id']) && $ticket['ticket_id'] && module_ticket::can_edit_tickets()){
                        if(class_exists('module_group',false) && module_config::c('ticket_enable_groups',1)){
                            module_group::display_groups(array(
                                 'title' => 'Ticket Groups',
                                'owner_table' => 'ticket',
                                'owner_id' => $ticket['ticket_id'],
                                'view_link' => module_ticket::link_open($ticket['ticket_id']),

                             ));
                        }
                    }
                    ?>

                    <?php if(module_ticket::can_edit_tickets()){ ?>
                    <h3><?php echo _l('Related to'); ?></h3>
                    <table border="0" cellspacing="0" cellpadding="2" class="tableclass tableclass_form tableclass_full">
						<tbody>

							<tr>
								<th class="width1">
									<?php echo _l('Customer'); ?>
								</th>
								<td>
                                    <?php
                                        if(module_ticket::can_edit_tickets()){
                                            $c = array();
                                            $res = module_customer::get_customers();
                                            while($row = array_shift($res)){
                                                $c[$row['customer_id']] = $row['customer_name'];
                                            }
                                            if($ticket['customer_id']<0)$ticket['customer_id']=false;
                                            echo print_select_box($c,'change_customer_id',$ticket['customer_id'],'',_l(' - No Customer -'));
                                            if(module_customer::can_i('create','Customers') && $ticket['user_id'] && (int)$ticket_id>0){
                                                // is this a user, or a staff member. don't allow moving of staff members. (or maybe later we will)

                                                //$user_temp = module_user::get_user($ticket['user_id'],false);
                                                //if($user_temp['customer_id']<=0){
                                                ?>
                                                <input type="button" name="new_customer" value="<?php _e('New');?>" onclick="window.location.href='<?php echo module_customer::link_open('new',false);?>&move_user_id=<?php echo $ticket['user_id'];?>';" class="small_button"><?php _h('Create a new customer and move this "Assigned Contact" to this new customer.'); ?>
                                                <?php
                                                //}
                                            } ?>
                                            <!-- <script type="text/javascript">
                                                $(function(){
                                                    $('#change_customer_id').change(function(){
                                                        // change our customer id.
                                                        var new_customer_id = $(this).val();
                                                        $.ajax({
                                                            type: 'POST',
                                                            url: '<?php echo module_customer::link_open(false);?>',
                                                            data: {
                                                                '_process': 'ajax_contact_list',
                                                                'customer_id': new_customer_id
                                                            },
                                                            dataType: 'json',
                                                            success: function(newOptions){
                                                                var currentuser_key = $('#change_user_id').val();
                                                                var currentuser_val = '';
                                                                if(currentuser_key){
                                                                    currentuser_val =$('#change_user_id').find('option[value="'+currentuser_key+'"]').html();
                                                                }
                                                                $('#change_user_id').find('option:gt(0)').remove();
                                                                $.each(newOptions, function(value, key) {
                                                                    $('#change_user_id').append($("<option></option>")
                                                                        .attr("value", value).text(key));
                                                                });
                                                                if(currentuser_key){
                                                                    $('#change_user_id').append($("<option></option>")
                                                                        .attr("value", currentuser_key).text(currentuser_val));
                                                                    $('#change_user_id').val(currentuser_key);
                                                                }
                                                            },
                                                            fail: function(){
                                                                alert('Changing customer failed, please refresh and try again.');
                                                            }
                                                        });
                                                    });
                                                });
                                            </script> -->
                                            <?php
                                        }else{
                                            echo module_customer::link_open($ticket['customer_id'],true);
                                        }
                                    /*$c = array();
                                    $res = module_customer::get_customers();
                                    while($row = array_shift($res)){
                                        $c[$row['customer_id']] = $row['customer_name'];
                                    }
                                    if(false && module_ticket::can_i('edit','Related to','Tickets')){
                                        echo print_select_box($c,'customer_id',$ticket['customer_id']);
                                    }else if($ticket['customer_id']){
                                        echo isset($c[$ticket['customer_id']]) ? $c[$ticket['customer_id']] : 'N/A';
                                    }*/
                                    ?>
								</td>
							</tr>
                            <?php /*if(false){ ?>
							<tr>
								<th>
									<?php echo _l('Contact'); ?>
								</th>
								<td>
                                    <?php
                                    if(module_ticket::can_edit_tickets()){
                                        $c = array();
                                        if($ticket['customer_id']){
                                            $res = module_user::get_contacts(array('customer_id'=>$ticket['customer_id']));
                                        }else{
                                            $res = array();
                                        }
                                        while($row = array_shift($res)){
                                            $c[$row['user_id']] = $row['name'].' '.$row['last_name'];
                                        }
                                        if($ticket['user_id'] && !isset($c[$ticket['user_id']])){
                                            // this option isn't in the listing. add it in.
                                            $user_temp = module_user::get_user($ticket['user_id'],false);
                                            $c[$ticket['user_id']] = $user_temp['name'].' '.$user_temp['last_name'];
                                            if($user_temp['customer_id']>=0) $c[$ticket['user_id']] .= ' '._l('(under different customer)');
                                            else{
                                                // user not assigned to a customer.
                                            }
                                        }
                                        echo print_select_box($c,'change_user_id',$ticket['user_id']);
                                    }else{
                                        echo module_user::link_open_contact($ticket['user_id'],true);
                                    }*/
                                        /*
                                    $c = array();
                                    $res = module_user::get_users(array('customer_id'=>$ticket['customer_id']));
                                    while($row = array_shift($res)){
                                        $c[$row['user_id']] = $row['name'];
                                    }
                                    if(false && module_ticket::can_i('edit','Related to')){
                                        echo print_select_box($c,'user_id',$ticket['user_id']);
                                    }else if($ticket['user_id']){
                                        echo isset($c[$ticket['user_id']]) ? $c[$ticket['user_id']] : 'N/A';
                                    }*//*
                                    ?>
								</td>
							</tr>
                                <?php
                            }*/

                            $res = module_website::get_websites(array('customer_id'=>$ticket['customer_id']));
                                if(count($res)){
                            ?>
							<tr>
								<th>
									<?php echo _l(''.module_config::c('project_name_single','Website')); ?>
								</th>
								<td>
                                    <?php
                                    $c = array();
                                    while($row = array_shift($res)){
                                        $c[$row['website_id']] = $row['name'];
                                    }
                                    echo print_select_box($c,'website_id',$ticket['website_id']);
                                    ?>
								</td>
							</tr>
                            <?php // } ?>
                            <?php } ?>
                            <?php if((int)$ticket_id>0){ ?>
							<tr>
								<th>
									<?php _e('Public link');?>
								</th>
								<td>
                                    <a href="<?php echo module_ticket::link_public($ticket_id);?>" target="_blank">click here</a>
								</td>
							</tr>
                            <?php } ?>
						</tbody>
					</table>


                    <?php handle_hook('ticket_sidebar',$ticket_id); ?>

                    <?php } // end can edit ?>
                    



				</td>
                <td valign="top">
                    <h3><?php echo _l('Ticket Messages'); ?></h3>
                    <div id="ticket_container" style="<?php echo module_config::c('ticket_scroll',0) ? ' max-height: 400px; overflow-y:auto;' : '';?>">
                                            <?php
                                            $reply__ine_default = '----- (Please reply above this line) -----'; // incase they change it
                                            $reply__ine =   module_config::s('ticket_reply_line',$reply__ine_default);
                                            $ticket_message_count = count($ticket_messages);
                                            $ticket_message_counter = 0;

                                            $show_messages_after = 0;
                                            if(count($ticket_messages) > module_config::c('ticket_hide_previous_messages',5) && !isset($_REQUEST['show_all'])){
                                                // we want to show the last "ticket_hide_previous_messages" messages on the page, or if the latest messages are all from the user in a row we show all those.
                                                $last_admin_message_count = 0;
                                                $x=0;
                                                foreach($ticket_messages as $ticket_message){
                                                    $x++;
                                                    if($ticket_message['cache']!='autoreply' && isset($admins_rel[$ticket_message['from_user_id']])){
                                                        // the admin created this message.
                                                        $last_admin_message_count = $x;
                                                    }
                                                }
                                                $show_messages_after=min(count($ticket_messages)-module_config::c('ticket_hide_previous_messages',5)+1 , $last_admin_message_count);

                                                ?>
                                                <div style="text-align: center">
                                                    <a href="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']);?>&show_all" class="uibutton">Show Previous <?php echo count($ticket_messages)-module_config::c('ticket_hide_previous_messages',5);?> Messages</a>
                                                </div>
                                                <?php
                                            }


                                            foreach($ticket_messages as $ticket_message){
                                                $ticket_message_counter++;
                                                if($ticket_message_counter < $show_messages_after)continue;
                                                $attachments = module_ticket::get_ticket_message_attachments($ticket_message['ticket_message_id']);

                                                $last_response_from = isset($admins_rel[$ticket_message['from_user_id']]) ? 'admin' : 'customer';

                                                if($ticket_message['cache']=='autoreply'){
                                                    $last_response_from = 'autoreply';
                                                    ?>
                                                    <a href="#" onclick="$(this).next('.ticket_message').show(); $(this).hide(); return false;" class="show_auto_reply_button"><?php _e('Show auto reply &raquo;');?></a>
                                                <?php } ?>
                                                <div class="ticket_message ticket_message_<?php
                                                    echo isset($admins_rel[$ticket_message['from_user_id']]) ? 'admin' : 'creator';
                                                    //echo $ticket['user_id'] == $ticket_message['from_user_id'] ? 'creator' : 'admin';
                                                    //echo $ticket_message['message_type_id'] == _TICKET_MESSAGE_TYPE_CREATOR ? 'creator' : 'admin';
                                                    ?>"<?php if($ticket_message['cache']=='autoreply'){ echo ' style="display:none;"'; } ?>>
                                                    <div class="ticket_message_title">
                                                        <div class="ticket_message_title_summary">
                                                            <strong><?php
                                                                if(module_security::get_loggedin_id() == $ticket_message['from_user_id']){
                                                                    // this message was from me !
                                                                    echo _l('Me:');
                                                                }else{
                                                                   // this message was from someone else.
                                                                    // eg, the Customer, or the Response from admin.
                                                                    //if($ticket['user_id'] == $ticket_message['from_user_id']){
                                                                    if(!isset($admins_rel[$ticket_message['from_user_id']])){
                                                                        echo _l('Customer:');
                                                                    }else{
                                                                        echo _l('Support:');
                                                                    }
                                                                }
                                                                ?></strong>
                                                            <?php echo print_date($ticket_message['message_time']); ?>
                                                            <a href="#" onclick="jQuery(this).parent().hide(); jQuery(this).parent().parent().find('.ticket_message_title_full').show(); return false;"><?php echo _l('more &raquo;');?></a>
                                                        </div>
                                                        <div class="ticket_message_title_full">
                                                            <?php
                                                                $header_cache = @unserialize($ticket_message['cache']); ?>

                                                            <span>
                                                                <?php _e('Date:');?> <strong>
                                              <?php echo print_date($ticket_message['message_time'],true); ?></strong>
                                                            </span><br/>
                                                            <span>
                                                                <?php _e('From:');?> <strong><?php
                                                                $from_temp = module_user::get_user($ticket_message['from_user_id'],false);
                                                                echo htmlspecialchars($from_temp['name']);?> &lt;<?php echo htmlspecialchars($from_temp['email']);?>&gt;</strong>
                                                            </span><br/>
                                                            <span>
                                                                <?php _e('To:');?>
                                                                <strong><?php
                                                                $to_temp = array();
                                                                if($ticket_message['to_user_id']){
                                                                    $to_temp = module_user::get_user($ticket_message['to_user_id'],false);
                                                                }else{
                                                                    if($header_cache && isset($header_cache['to_email'])){
                                                                        $to_temp['email'] = $header_cache['to_email'];
                                                                    }
                                                                }
                                                                if(isset($to_temp['name']))echo htmlspecialchars($to_temp['name']);
                                                                if(isset($to_temp['email'])){ ?>
                                                                    &lt;<?php echo htmlspecialchars($to_temp['email']); ?>&gt;
                                                                <?php } ?>
                                                                </strong><?php
                                                                // hack support for other to fields.
                                                                if($header_cache && isset($header_cache['to_emails']) && is_array($header_cache['to_emails'])){
                                                                    foreach($header_cache['to_emails'] as $to_email_additional){
                                                                        if(isset($to_email_additional['address']) && strlen($to_email_additional['address']) && $to_email_additional['address'] != $to_temp['email']){
                                                                            echo ', <strong>';
                                                                            if(isset($to_email_additional['name'])){
                                                                                echo htmlspecialchars($to_email_additional['name']);
                                                                            }
                                                                            ?> &lt;<?php echo htmlspecialchars($to_email_additional['address']); ?>&gt; <?php
                                                                            echo '</strong>';
                                                                        }
                                                                    }
                                                                }
                                                                ?>
                                                            </span><br/>
                                                            <?php
                                                            // hack support for other to fields.
                                                            if($header_cache && isset($header_cache['cc_emails']) && is_array($header_cache['cc_emails']) && count($header_cache['cc_emails'])){
                                                                ?> 
                                                                <span>
                                                                    <?php _e('CC:');?>
                                                                    <?php
                                                                    $donecc=false;
                                                                    foreach($header_cache['cc_emails'] as $cc_email_additional){
                                                                        if(isset($cc_email_additional['address']) && strlen($cc_email_additional['address'])){
                                                                            if($donecc)echo ', ';
                                                                            $donecc=true;
                                                                            echo '<strong>';
                                                                            if(isset($cc_email_additional['name'])){
                                                                                echo htmlspecialchars($cc_email_additional['name']);
                                                                            }
                                                                            ?> &lt;<?php echo htmlspecialchars($cc_email_additional['address']); ?>&gt; <?php
                                                                            echo '</strong>';
                                                                        }
                                                                    }
                                                                    ?> 
                                                                </span>  <br/>
                                                                <?php 
                                                            }
                                                            // hack support for other to fields.
                                                            if($header_cache && isset($header_cache['bcc_emails']) && is_array($header_cache['bcc_emails']) && count($header_cache['bcc_emails'])){
                                                                ?> 
                                                                <span>
                                                                    <?php _e('BCC:');?>
                                                                    <?php
                                                                    $donebcc=false;
                                                                    foreach($header_cache['bcc_emails'] as $bcc_email_additional){
                                                                        if(isset($bcc_email_additional['address']) && strlen($bcc_email_additional['address'])){
                                                                            if($donebcc)echo ', ';
                                                                            $donebcc=true;
                                                                            echo '<strong>';
                                                                            if(isset($bcc_email_additional['name'])){
                                                                                echo htmlspecialchars($bcc_email_additional['name']);
                                                                            }
                                                                            ?> &lt;<?php echo htmlspecialchars($bcc_email_additional['address']); ?>&gt; <?php
                                                                            echo '</strong>';
                                                                        }
                                                                    }
                                                                    ?> 
                                                                </span>  <br/>
                                                                <?php 
                                                            }
                                                            ?>
                                                        </div>
                                                            <?php
                                                            if(count($attachments)){
                                                                echo '<span>';
                                                                _e('Attachments:', 'wpetss');
                                                                foreach($attachments as $attachment){
                                                                    ?>
                                                                    <a href="<?php echo module_ticket::link_open_attachment($ticket_id,$attachment['ticket_message_attachment_id']);?>" class="attachment_link"><?php echo htmlspecialchars($attachment['file_name']);?> (<?php echo file_exists('includes/plugin_ticket/attachments/'.$attachment['ticket_message_attachment_id']) ? frndlyfilesize(filesize('includes/plugin_ticket/attachments/'.$attachment['ticket_message_attachment_id'])) : _l('File Not Found');?>)</a>
                                                                    <?php
                                                                }
                                                                echo '</span>';
                                                            }
                                                            ?>
                                                    </div>
                                                    <div class="ticket_message_text">
                                                        <?php

                                                        // copied to ticket.php in autoresponder:
                                                        // todo: move this out to a function in ticket.php
                                                        /*if(preg_match('#<br[^>]*>#i',$ticket_message['content'])){
                                                            $ticket_message['content'] = preg_replace("#\r?\n#",'',$ticket_message['content']);
                                                        }*/
                                                        /*if(isset($_REQUEST['ticket_page_debug'])){
                                                            echo "UTF8 method: ".module_config::c('ticket_utf8_method',1). "<br>\n";
                                                            echo "Cache: ".$ticket_message['cache']."<br>\n";
                                                            echo "<hr> Raw Content: <hr>";
                                                            echo $ticket_message['content'];
                                                            echo "<hr> HTML Content: <hr>";
                                                            echo $ticket_message['htmlcontent'];
                                                            echo "<hr> Content: <hr>";
                                                            echo htmlspecialchars($ticket_message['content']);
                                                            echo "<hr>";

                                                        }*/

                                                        // do we use html or plain text?
                                                        $text = '';
                                                        if(module_config::c('ticket_message_text_or_html','html')=='html'){
                                                            $text = $ticket_message['htmlcontent'];
                                                            // linkify the text, without messing with existing <a> links. todo: move this into a global method for elsewhere (eg: eamils)
                                                            //$text = preg_replace('@(?!(?!.*?<a)[^<]*<\/a>)(?:(?:https?|ftp|file)://|www\.|ftp\.)[-A-Z0-9+&#/%=~_|$?!:,.]*[A-Z0-9+&#/%=~_|$]@i','<a href="\0" target="_blank">\0</a>', $text );
                                                        }
                                                        if(!strlen(trim($text))){
                                                            $text = $ticket_message['content'];
                                                            $text = preg_replace("#<br[^>]*>#i",'',$text);
                                                            $text = preg_replace('#(\r?\n\s*){2,}#',"\n\n",$text);
                                                            switch(module_config::c('ticket_utf8_method',1)){
                                                                case 1:
                                                                    $text = forum_text($text,true);
                                                                    break;
                                                                case 2:
                                                                    $text = forum_text(utf8_encode($text),true);
                                                                    break;
                                                                case 3:
                                                                    $text = forum_text(utf8_encode(utf8_decode($text)),true);
                                                                    break;
                                                            }
                                                        }


                                                        if($ticket_message['cache']=='autoreply' && strlen($ticket_message['htmlcontent'])>2){
                                                            $text = $ticket_message['htmlcontent']; // override for autoreplies, always show as html.
                                                        }

                                                        if((!$text || !strlen($text)) && strlen($ticket_message['content'])){
                                                            $text = nl2br($ticket_message['content']);
                                                        }

                                                        $text = preg_replace("#<br[^>]>#i","$0\n",$text);
                                                        $text = preg_replace("#</p>#i","$0\n",$text);
                                                        $text = preg_replace("#</div>#i","$0\n",$text);
                                                        $lines = explode("\n",$text);
                                                        $do_we_hide = count($lines)>4 && module_config::c('ticket_hide_messages',1) && $ticket_message_counter<$ticket_message_count && $ticket_message_count!=2;

                                                        if($do_we_hide){
                                                            ?>
                                                            <div class="ticket_message_hider">
                                                            <?php
                                                        }

                                                        //$blank_line_limit = module_config::c('ticket_message_max_blank_lines',1);
                                                        if(true){
                                                            $hide__ines = $print__ines = array();
                                                            $blank_line_count = 0;
                                                            foreach($lines as $line_number => $line){
                                                                // hide anything after
                                                                $line = trim($line);
                                                                //if(preg_replace('#[\r\n\s]*#','',$line)==='')$blank_line_count++;
                                                                //else $blank_line_count=0;

                                                                //if($blank_line_limit>0 && $blank_line_count>$blank_line_limit)continue;

                                                                if(
                                                                    count($hide__ines) ||
                                                                    preg_match('#^>#',$line) ||
                                                                    preg_match('#'.preg_quote($reply__ine,'#').'#ims',$line) ||
                                                                    preg_match('#'.preg_quote($reply__ine_default,'#').'#ims',$line)
                                                                ){
                                                                    if(!count($hide__ines) && module_config::c('ticket_message_text_or_html','html') != 'html'){
                                                                        // move the line before if it exists.
                                                                        if(isset($print__ines[$line_number-1])){
                                                                            if(trim(preg_replace('#<br[^>]*>#i','',$print__ines[$line_number-1]))){
                                                                                $hide__ines[$line_number-1] = $print__ines[$line_number-1];
                                                                            }
                                                                            unset($print__ines[$line_number-1]);
                                                                        }
                                                                        // move the line before if it exists.
                                                                        if(isset($print__ines[$line_number-2])){
                                                                            if(trim(preg_replace('#<br[^>]*>#i','',$print__ines[$line_number-2]))){
                                                                                $hide__ines[$line_number-2] = $print__ines[$line_number-2];
                                                                            }
                                                                            unset($print__ines[$line_number-2]);
                                                                        }
                                                                        // move the line before if it exists.
                                                                        if(isset($print__ines[$line_number-3]) && preg_match('#^On #',trim($print__ines[$line_number-3]))){
                                                                            if(trim(preg_replace('#<br[^>]*>#i','',$print__ines[$line_number-3]))){
                                                                                $hide__ines[$line_number-3] = $print__ines[$line_number-3];
                                                                            }
                                                                            unset($print__ines[$line_number-3]);
                                                                        }
                                                                    }
                                                                    $hide__ines [$line_number] = $line;
                                                                    unset($print__ines[$line_number]);
                                                                }else{
                                                                    // not hidden yet.
                                                                    $print__ines[$line_number] = $line;
                                                                }
                                                            }
                                                            ksort($hide__ines);
                                                            ksort($print__ines);
                                                            //echo module_security::purify_html(implode("\n",$hide__ines)); echo '<hr>';
                                                            echo module_security::purify_html(implode("\n",$print__ines));
                                                            //print_r($print__ines);
                                                            if(count($hide__ines)){
                                                                echo '<a href="#" onclick="jQuery(this).parent().find(\'div\').show(); jQuery(this).hide(); return false;">'._l('- show quoted text -').'</a> ';
                                                                echo '<div style="display:none;">';
                                                                echo module_security::purify_html(implode("\n",$hide__ines));
                                                                echo '</div>';
                                                                //print_r($hide__ines);
                                                            }
                                                        }else{
                                                            echo $text;
                                                        }
                                                        /*if($ticket_message['cache']=='autoreply'){
                                                            ?>
                                                            </div>
                                                            <?php
                                                        }else */ if($do_we_hide){
                                                            ?>
                                                            </div>
                                                            <div>
                                                                <span class="shower">
                                                                    <a href="#" onclick="jQuery(this).parent().parent().parent().find('.ticket_message_hider').addClass('ticket_message_hider_show'); jQuery(this).parent().parent().find('.hider').show(); jQuery(this).parent().hide();return false;"><?php _e('Show entire message &raquo;');?></a>
                                                                </span>
                                                                <span class="hider" style="display:none;">
                                                                    <a href="#" onclick="jQuery(this).parent().parent().parent().find('.ticket_message_hider').removeClass('ticket_message_hider_show'); jQuery(this).parent().parent().find('.shower').show(); jQuery(this).parent().hide(); return false;"><?php _e('&laquo; Hide message');?></a>
                                                                </span>
                                                            </div>
                                                            <?php
}
                                                        ?>
                                                    </div>
                                                </div>
                                            <?php } ?>


                                            <?php
                                            if(true){ //$logged_in_user || is_user_logged_in()){
                                            ?>

                                            <?php if(false && count($ticket_messages)){ ?>
                                            <div id="ticket_reply_button">
                                                <input type="button" name="reply" onclick="jQuery('#ticket_reply_button').hide(); jQuery('#ticket_reply_holder').show(); jQuery('#new_ticket_message')[0].focus(); return false;" value="<?php echo _l('Reply to ticket');?>" class="submit_button">
                                            </div>
                                            <div style="display: none;" class="ticket_reply" id="ticket_reply_holder">
                                            <?php }else{ ?>
                                            <div id="ticket_reply_holder" class="ticket_reply">
                                                 <script type="text/javascript">
                                                     /*jQuery(function(){
                                                         jQuery('#new_ticket_message')[0].focus();
                                                     });*/
                                                 </script>
                                            <?php } ?>

                                                <div class="ticket_message ticket_message_<?php
                                                    echo $ticket['user_id'] == module_security::get_loggedin_id() ? 'creator' : 'admin';
                                                    ?>">
                                                    <div class="ticket_message_title" style="text-align: left;">
                                                        <?php if(module_ticket::can_edit_tickets()){ ?>
                                                        <div style="float:right; margin: -3px 5px 0 0;">
                                                            <div id="canned_response"> <!-- style="display:none;"  -->
                                                                <?php
                                                                $canned_responses = module_ticket::get_saved_responses();
                                                                echo print_select_box($canned_responses,'canned_response_id','','',true,'',true);
                                                                ?>
                                                                <input type="button" name="s" id="save_saved" value="<?php _e('Save');?>" class="small_button">
                                                                <input type="button" name="i" id="insert_saved" value="<?php _e('Insert');?>" class="small_button">
                                                                <script type="text/javascript">
                                                                    $(function(){
                                                                        $('#save_saved').click(function(){
                                                                            $.ajax({
                                                                                url: '<?php echo module_ticket::link_open($ticket_id,false);?>',
                                                                                type: 'POST',
                                                                                data: '_process=save_saved_response&saved_response_id='+$('#canned_response_id').val()+'&value='+escape($('#new_ticket_message').val()),
                                                                                dataType: 'json',
                                                                                success: function(r){
                                                                                    alert('<?php _e('Saved successfully');?>');
                                                                                }
                                                                            });
                                                                        });
                                                                        $('#insert_saved').click(function(){
                                                                            $.ajax({
                                                                                url: '<?php echo module_ticket::link_open($ticket_id,false);?>',
                                                                                data: '_process=insert_saved_response&saved_response_id='+$('#canned_response_id').val(),
                                                                                dataType: 'json',
                                                                                success: function(r){
                                                                                    ucm.ticket.add_to_message(r.value);
                                                                                }
                                                                            });
                                                                        });
                                                                    });
                                                                </script>
                                                            </div>
                                                            <!-- <a href="#" onclick="$('#canned_response').show(); $(this).hide(); return false;"><?php _e('Saved Response');?></a> -->
                                                        </div>
                                                        <?php } ?>
                                                        <strong><?php echo _l('Enter Your Message:');?></strong>
                                                    </div>
                                                    <div class="ticket_message_text">

                                                        <textarea rows="6" cols="20" name="new_ticket_message" id="new_ticket_message"></textarea>
                                                        <?php if(module_config::c('ticket_create_wysiwyg',1)){ ?>
                                    <script type="text/javascript" src="<?php echo _BASE_HREF;?>js/tiny_mce3.4.4/jquery.tinymce.js"></script>
                                                            <script type="text/javascript">
                                                                var done_auto_insert=false;
                                                                function tinymce_focus(){
                                                                    // if the user has entered a default reply, insert it here.
                                                                    <?php
                                                                    //module_template::init_template('ticket_reply_default','','Default reply text to appear when admin replies to a ticket');
                                                                    $template = module_template::get_template_by_key('ticket_reply_default');
                                                                    if($template->template_id){ ?>
                                                                    if(!done_auto_insert){
                                                                        done_auto_insert = true;
                                                                        ucm.ticket.add_to_message("<?php echo preg_replace("#[\r\n]+#",'', addcslashes($template->content,'"'));?>");
                                                                    }
                                                                    <?php } ?>

                                                                }
                                                                function tinymce_blur(){

                                                                }
	$(function() {
		$('#new_ticket_message').tinymce({
			// Location of TinyMCE script
			script_url : '<?php echo _BASE_HREF;?>js/tiny_mce3.4.4/tiny_mce.js',

            relative_urls : false,
            convert_urls : false,

			// General options
			theme : "advanced",
			plugins : "autolink,lists,pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template,advlist",

			// Theme options
            theme_advanced_buttons1 : "undo,redo,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,formatselect,fontselect,fontsizeselect",
            theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,bullist,numlist,|,link,unlink,anchor,image,cleanup,code,|,forecolor,backcolor",
            theme_advanced_buttons3 : "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,emotions,iespell",
			/*theme_advanced_buttons1 : "save,newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,styleselect,formatselect,fontselect,fontsizeselect",
			theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,code,|,insertdate,inserttime,preview,|,forecolor,backcolor",
			theme_advanced_buttons3 : "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,emotions,iespell,media,advhr,|,print,|,ltr,rtl,|,fullscreen",
			theme_advanced_buttons4 : "insertlayer,moveforward,movebackward,absolute,|,styleprops,|,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,template,pagebreak",*/
			theme_advanced_toolbar_location : "top",
			theme_advanced_toolbar_align : "left",
			theme_advanced_statusbar_location : "bottom",
			theme_advanced_resizing : true,

            height : '400px',
            width : '100%',

            setup: function(ed){
                ed.onInit.add(function(ed){
                    $(ed.getDoc()).contents().find('body').focus(function(){tinymce_focus();});
                    $(ed.getDoc()).contents().find('body').blur(function(){tinymce_blur();});
                });
            }

		});
	});
</script>
                                                        <?php } ?>
                                                        <table align="center" class="tableclass tableclass_full tableclass_space">
                                                            <tbody>

                                                            <?php if(module_config::c('ticket_allow_attachment',1)){ ?>
                                                            <tr>
                                                                <td style="text-align: right" width="50%">
                                                                    <?php _e('Add Attachment'); ?>
                                                                </td>
                                                                <td align="left">
                                                                    <input type="file" name="attachment[]">
                                                                </td>
                                                            </tr>
                                                            <?php } ?>

                                                            <?php if(module_ticket::can_edit_tickets()){ ?>

                                                            <tr>
                                                                <td style="text-align: right" width="50%">
                                                                    <?php _e('Change status:'); ?>
                                                                </td>
                                                                <td align="left">
                                                                    <?php
                                                                    $current_status = $ticket['status_id'];
                                                                    if(count($ticket_messages)){
                                                                        if($current_status <= 2){
                                                                            //$current_status = 3; // change to replied
                                                                            $current_status = module_config::c('ticket_reply_status_id',_TICKET_STATUS_RESOLVED_ID); // resolved
                                                                        }else{
                                                                            //$current_status = 5; // change to in progress
                                                                            $current_status = module_config::c('ticket_reply_status_id',_TICKET_STATUS_RESOLVED_ID); // resolved
                                                                        }
                                                                    }
                                                                    echo print_select_box(module_ticket::get_statuses(),'change_status_id',$current_status);
                                                                    ?>
                                                                </td>
                                                            </tr>
                                                            <?php if(module_config::c('ticket_show_change_staff',0) && $ticket['assigned_user_id'] != module_security::get_loggedin_id()){ ?>
                                                            <tr>
                                                                <td style="text-align: right" width="50%">
                                                                    <?php _e('Change staff:'); ?>
                                                                </td>
                                                                <td align="left"> 
                                                                    <?php
                                                                    echo print_select_box($admins_rel,'change_assigned_user_id',$ticket['assigned_user_id']);
                                                                    ?>
                                                                </td>
                                                            </tr>
                                                            <?php } ?>

                                                        <?php } ?>

                                                            </tbody>
                                                            <?php if(module_ticket::can_edit_tickets() && module_config::c('ticket_allow_cc_bcc',1)){ ?>
                                                            <tbody id="ticket_cc_bcc" style="display:none;">
                                                                <tr>
                                                                    <td style="text-align:right;">
                                                                        <?php _e('Email CC'); ?>
                                                                    </td>
                                                                    <td>
                                                                        <input type="text" name="ticket_cc" class="email_input"> <?php _h('Enter a list of email addresses here (comma separated) and this ticket message will be carbon copied to these recipients.  These recipients can reply to the ticket and their reply will appeear here in the ticketing system if you have POP3/IMAP setup correctly.'); ?>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td style="text-align:right;">
                                                                        <?php _e('Email BCC'); ?>
                                                                    </td>
                                                                    <td>
                                                                        <input type="text" name="ticket_bcc" class="email_input"> <?php _h('Enter a list of email addresses here (comma separated) and this ticket message will be blind carbon copied to these recipients. These recipients can reply to the ticket and their reply will appeear here in the ticketing system if you have POP3/IMAP setup correctly.'); ?>
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                            <?php } ?>
                                                            <tbody>
                                                            <?php /* <tr>
                                                                <td align="right">
                                                                     <?php _e('Send message as:');?>
                                                                </td>
                                                                <td align="left">
                                                                    <input type="hidden" name="creator_id" value="<?php echo module_security::get_loggedin_id();?>">
                                                                    <input type="hidden" name="creator_hash" value="<?php echo module_ticket::creator_hash(module_security::get_loggedin_id());?>">
                                                                    <strong>
                                                                    <?php echo htmlspecialchars($send_as_name);?>
                                                                    &lt;<?php echo htmlspecialchars($send_as_address);?>&gt;
                                                                    </strong>
                                                                    <?php _e('Reply To:');?> <strong><?php echo htmlspecialchars($to_user_a['email']);?></strong> 
                                                                </td>
                                                            </tr> */ ?>
                                                            <tr>
                                                                <td colspan="2" style="text-align: center">

                                                                <?php if(module_ticket::can_edit_tickets() && module_config::c('ticket_allow_cc_bcc',1)){ ?>
                                                                        <input type="button" name="show_cc_bcc" class="submit_button" value="<?php _e('Add CC/BCC');?>" onclick="$('#ticket_cc_bcc').show(); $(this).hide();">
                                                                <?php } ?>
                                                                    <?php if($next_ticket){ ?>
                                                                    <input type="submit" name="newmsg" value="<?php _e('Submit Message');?>" class="submit_button">
                                                                    <input type="submit" name="newmsg_next" value="<?php _e('Submit Message &amp; Go To Next Ticket');?>" class="submit_button save_button">
                                                                    <input type="hidden" name="next_ticket_id" value="<?php echo $next_ticket;?>">
                                                                <?php }else{ ?>
                                                                    <input type="submit" name="newmsg" value="<?php _e('Submit Message');?>" class="submit_button save_button">
                                                                <?php } ?>
                                                                </td>
                                                            </tr>
                                                            </tbody>
                                                        </table>


                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php } ?>
                                        </div>
                                    
                </td>
			</tr>
		</tbody>
	</table>
    <!-- next / prev links -->
    <table width="80%" align="center">
        <tbody>
        <tr>
            <td align="left">
                <?php if($prev_ticket && $prev_ticket!==true){ ?>
                <a href="<?php echo module_ticket::link_open($prev_ticket);?>" class="uibutton">&laquo; <?php _e('Prev Ticket');?></a>
                <?php } ?>
            </td>
            <td align="center">
                <?php echo $action_buttons;?>
            </td>
            <td align="right">
                <?php if($next_ticket){ ?>
                <a href="<?php echo module_ticket::link_open($next_ticket);?>" class="uibutton"><?php _e('Next Ticket');?> &raquo;</a>
                <?php } ?>
            </td>
        </tr>
        </tbody>
    </table>

</form>

<?php

if(($last_response_from == 'customer' || $last_response_from == 'autoreply') && $ticket['status_id'] != _TICKET_STATUS_RESOLVED_ID ){ // don't do this for resolved tickets
    // only set the default field if the last respose was from the customer.
    module_form::set_default_field('new_ticket_message');
}

?>