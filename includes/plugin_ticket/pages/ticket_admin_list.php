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


$ticket_count = 0;
switch(module_config::c('ticket_show_summary_type','unread')){
    case 'unread':
        $ticket_count = module_ticket::get_unread_ticket_count();
        break;
    case 'total':
    default:
        $ticket_count = module_ticket::get_total_ticket_count();
        break;
}

if($ticket_count>0){
    $module->page_title = _l('Tickets (%s)',$ticket_count);
}else{
    $module->page_title = _l('Tickets');
}

// hack to add a "group" option to the pagination results.
if(class_exists('module_group',false) && module_config::c('ticket_enable_groups',1)){
    module_group::enable_pagination_hook(
        // what fields do we pass to the group module from this customers?
        array(
            'fields'=>array(
                'owner_id' => 'ticket_id',
                'owner_table' => 'ticket',
            ),
        )
    );
}

$search = (isset($_REQUEST['search']) && is_array($_REQUEST['search'])) ? $_REQUEST['search'] : array();
if(isset($_REQUEST['faq_product_id']) && !isset($search['faq_product_id'])){
    $search['faq_product_id'] = (int)$_REQUEST['faq_product_id'];
}
if(isset($_REQUEST['customer_id']) && (int)$_REQUEST['customer_id']>0){
    $search['customer_id'] = (int)$_REQUEST['customer_id'];
}else{
    $search['customer_id'] = false;
}


$search_statuses = module_ticket::get_statuses();
$search_statuses['2,3,5'] = 'New/Replied/In Progress';
if(!isset($search['status_id']) && module_ticket::can_edit_tickets()){
    $search['status_id'] = '2,3,5';
}

$tickets = module_ticket::get_tickets($search,true);
if(!isset($_REQUEST['nonext'])){
    $_SESSION['_ticket_nextprev'] = array();
    while($ticket = mysql_fetch_assoc($tickets)){
        $_SESSION['_ticket_nextprev'][] = $ticket['ticket_id'];
    }
    if(mysql_num_rows($tickets)>0){
        mysql_data_seek($tickets,0);
    }
}

$priorities = module_ticket::get_ticket_priorities();

?>

<h2>
    <?php if(module_ticket::can_i('create','Tickets')){ ?>
	<span class="button">
		<?php echo create_link("Add New ticket","add",module_ticket::link_open('new')); ?>
	</span>
    <?php } ?>
	<?php echo _l('Customer Tickets'); ?>
</h2>

<form action="#" method="<?php echo _DEFAULT_FORM_METHOD;?>">

    <input type="hidden" name="customer_id" value="<?php echo isset($_REQUEST['customer_id']) ? (int)$_REQUEST['customer_id'] : '';?>">


<table class="search_bar">
	<tr>
		<th rowspan="2"><?php _e('Filter By:'); ?></th>
		<td class="search_title">
			<?php echo _l('Number:');?>
		</td>
		<td class="search_input">
			<input type="text" name="search[ticket_id]" value="<?php echo isset($search['ticket_id'])?htmlspecialchars($search['ticket_id']):''; ?>" size="5">
		</td>
		<td class="search_title">
			<?php echo _l('Subject:');?>
		</td>
		<td class="search_input">
			<input type="text" name="search[generic]" value="<?php echo isset($search['generic'])?htmlspecialchars($search['generic']):''; ?>" size="10">
		</td>
		<td class="search_title">
			<?php echo _l('Contact:');?>
		</td>
		<td class="search_input">
			<input type="text" name="search[contact]" value="<?php echo isset($search['contact'])?htmlspecialchars($search['contact']):''; ?>" size="10">
		</td>
		<td class="search_title">
			<?php echo _l('Date:');?>
		</td>
		<td class="search_input">
			<input type="text" name="search[date_from]" value="<?php echo isset($search['date_from'])?htmlspecialchars($search['date_from']):''; ?>" class="date_field">
            <?php _e('to');?>
			<input type="text" name="search[date_to]" value="<?php echo isset($search['date_to'])?htmlspecialchars($search['date_to']):''; ?>" class="date_field">
		</td>
        <?php if(class_exists('module_envato',false)){ ?>
        <td class="search_title" rowspan="2">
            <?php echo _l('Envato:');?>
        </td>
        <td class="search_input" rowspan="2">
            <?php echo print_multi_select_box(array(-1=>'No product')+module_envato::get_envato_items_rel(),'search[envato_item_id]',isset($search['envato_item_id'])?$search['envato_item_id']:array()); ?>
        </td>
        <?php } ?>
		<td class="search_action" rowspan="2">
			<?php echo create_link("Reset","reset",module_ticket::link_open(false)); ?>
			<?php echo create_link("Search","submit"); ?>
		</td>
	</tr>
    <tr>
		<td class="search_title">
			<?php echo _l('Type:');?>
		</td>
		<td class="search_input">
			<?php echo print_select_box(module_ticket::get_types(),'search[ticket_type_id]',isset($search['ticket_type_id'])?$search['ticket_type_id']:''); ?>
		</td>
		<td class="search_title">
			<?php echo _l('Status:');?>
		</td>
		<td class="search_input">
			<?php echo print_select_box($search_statuses,'search[status_id]',isset($search['status_id'])?$search['status_id']:''); ?>
		</td>
		 <td class="search_title">
			<?php echo _l('Priority:');?>
		</td>
		<td class="search_input">
            <?php echo print_select_box(module_ticket::get_ticket_priorities(),'search[priority]',isset($search['priority'])?$search['priority']:''); ?>
		</td>
        <?php if(class_exists('module_faq',false) && module_config::c('ticket_show_product_list',1)){ ?>
        <td class="search_title">
            <?php echo _l('Product:');?>
        </td>
        <td class="search_input">
            <?php echo print_select_box(module_faq::get_faq_products_rel(),'search[faq_product_id]',isset($search['faq_product_id'])?$search['faq_product_id']:''); ?>
        </td>
        <?php } ?>
    </tr>
</table>

<?php

if(class_exists('module_envato',false) && module_config::c('envato_show_ticket_earning',0)){
    $item_ticket_count = array();
    $envato_count = module_cache::time_get('envato_ticket_earning');
    //if($envato_count===false){
        while($ticket = mysql_fetch_assoc($tickets)){
            $items = module_envato::get_items_by_ticket($ticket['ticket_id']);
            if(count($items)){
                foreach($items as $item_id => $item){
                    if(!isset($item_ticket_count[$item_id])){
                        $item_ticket_count[$item_id] = array(
                            'envato_id' => $item_id,
                            'name' => $item['name'],
                            'count' => 0,
                            'cost' => $item['cost'],
                        );
                    }
                    $item_ticket_count[$item_id]['count']++;
                    $envato_count += $item['cost'];
                }
            }else{
                $item_id = '-1';
                if(!isset($item_ticket_count[$item_id])){
                    $item_ticket_count[$item_id] = array(
                        'envato_id' => $item_id,
                        'name' => 'No product',
                        'count' => 0,
                        'cost' => 0,
                    );
                }
                $item_ticket_count[$item_id]['count']++;
            }
        }
        if(mysql_num_rows($tickets)>0){
            mysql_data_seek($tickets,0);
        }
        module_cache::time_save('envato_ticket_earning',$envato_count);
    //}
    function sort_envato_ticket_count($a,$b){
        //return ($a['count']*$a['cost'])<=($b['count']*$b['cost']);
        return $a['count']<=$b['count'];
    }
    uasort($item_ticket_count,'sort_envato_ticket_count');
    foreach($item_ticket_count as $i){
        ?> <a href="?search[envato_item_id][]=<?php echo $i['envato_id'];?>"><?php echo htmlspecialchars($i['name']);?> (<?php echo $i['count'];?><?php echo $i['cost']? ' - '.dollar($i['count']*$i['cost']):'';?>)</a> <?php
    }
}

$pagination = process_pagination($tickets,module_config::c('ticket_list_default_per_page',70),0,'ticket_list');
$colspan = 4;
?>

<?php echo $pagination['summary'];?>
    <?php echo $pagination['links'];?>

<table width="100%" border="0" cellspacing="0" cellpadding="2" class="tableclass tableclass_rows tbl_fixed">
	<thead>
	<tr class="title">
		<th style="width:8%;"><?php echo _l('Number'); ?></th>
		<th style="width:28%"><?php echo _l('Subject'); ?></th>
		<th style="width:16%;"><?php echo _l('Last Date/Time'); ?></th>
		<th style="width:12%;"><?php echo _l('Type'); ?></th>
		<th style="width:9%;"><?php echo _l('Status'); ?></th>
		<!-- <th><?php echo _l(module_config::c('project_name_single','Website')); ?></th>
        <?php if(!isset($_REQUEST['customer_id'])){ ?>
		<th><?php echo _l('Customer'); ?></th>
        <?php } ?>-->
		<th style="width:10%;"><?php echo _l('Staff'); ?></th>
		<th style="width:10%;"><?php echo _l('Contact'); ?></th>
        <?php if(class_exists('module_faq',false) && module_config::c('ticket_show_product_list',1)){ ?>
        <th style="width:10%;"><?php echo _l('Product');?></th>
        <?php } ?>
        <?php if(class_exists('module_envato',false)){ ?>
        <th style="width:10%;"><?php echo _l('Envato');
            if(module_config::c('envato_show_ticket_earning',0)){
                // work out how much we have earnt from the outstanding support envatos.
                echo ' ('.dollar($envato_count*.7).')';
            }
            ?></th>
        <?php } ?>
        <?php if(class_exists('module_group',false) && module_config::c('ticket_enable_groups',1) && module_group::groups_enabled()){ ?>
        <th width="9%"><?php echo _l('Group'); ?></th>
        <?php } ?>
        <?php if(module_config::c('ticket_allow_priority',0) && module_config::c('ticket_show_priority',1)){ ?>
        <th width="5%"><?php _e('Priority');?></th>
        <?php } ?>
        <?php if(class_exists('module_extra',false)){
        module_extra::print_table_header('ticket',array(
            array('style'=>'width:10%')
        ));
        } ?>
    </tr>
    </thead>
    <tbody>
		<?php
		$c=0;
        $time = time();
        $today = strtotime(date('Y-m-d'));
        $seconds_into_today = $time - $today;

		foreach($pagination['rows'] as $ticket){

            if(class_exists('module_envato',false) && isset($_REQUEST['faq_product_envato_hack']) && (!$ticket['faq_product_id'] || $ticket['faq_product_id'] == $_REQUEST['faq_product_envato_hack'])){
                $ticket = module_ticket::get_ticket($ticket['ticket_id']);
            }
            ?>
            <tr class="<?php echo ($c++%2)?"odd":"even"; ?>">
                <td class="row_action" nowrap="">
                    <?php echo module_ticket::link_open($ticket['ticket_id'],true,$ticket);?> (<?php echo $ticket['message_count'];?>)
                </td>
                <td>
                    <?php
                    // todo, pass off to envato module as a hook
                    $ticket['subject'] = preg_replace('#Message sent via your Den#','',$ticket['subject']);
                    if($ticket['priority']){

                    }
                    if($ticket['unread']){
                        echo '<strong>';
                        echo ' '._l('* '). ' ';
                        echo htmlspecialchars($ticket['subject']);
                        echo '</strong>';
                    }else{
                        echo htmlspecialchars($ticket['subject']);
                    }
                    ?>
                </td>
                <td>
                    <?php
                    if($ticket['last_message_timestamp']>0){
                        if($ticket['last_message_timestamp'] < $limit_time){
                            echo '<span class="important">';
                        }
                        echo print_date($ticket['last_message_timestamp'],true);
                        // how many days ago was this?
                        echo ' ';
                        //echo '<br>'.$seconds_into_today ."<br>".($ticket['last_message_timestamp']+1).'<br>';
                        if($ticket['last_message_timestamp']>=$today){
                            echo '<span class="success_text">';
                            _e('(today)');
                            echo '</span>';
                        }else{
                            $days = ceil(($today - $ticket['last_message_timestamp'])/86400);

                            _e(' (%s days)',abs($days));
                        }
                        if($ticket['last_message_timestamp'] < $limit_time){
                            echo '</span>';
                        }
                    }
                    ?>
                </td>
                <td>
                    <?php echo htmlspecialchars($ticket['type']); ?>
                </td>
                <td>
                    <?php echo htmlspecialchars(module_ticket::$ticket_statuses[$ticket['status_id']]); ?>
                </td>
                <!-- <td>
                    <?php echo module_website::link_open($ticket['website_id'],true); ?>
                </td>
                <?php if(!isset($_REQUEST['customer_id'])){ ?>
                <td>
                    <?php echo module_customer::link_open($ticket['customer_id'],true);?>
                </td>
                <?php } ?>-->
                <td>
                    <?php echo module_user::link_open($ticket['assigned_user_id'],true); ?>
                </td>
                <td>
                    <?php echo module_user::link_open($ticket['user_id'],true); ?>
                </td>
                <?php if(class_exists('module_faq',false) && module_config::c('ticket_show_product_list',1)){ ?>
                <td>
                    <?php
                    // find out details about this envato contact
                    // their username and what items they have purchased.
                    if($ticket['faq_product_id']){
                        $faq_product = module_faq::get_faq_product($ticket['faq_product_id']);
                        echo htmlspecialchars($faq_product['name']);
                    }
                    ?>
                </td>
                <?php } ?>
                <?php if(class_exists('module_envato',false)){ ?>
                <td>
                    <?php
                    // find out details about this envato contact
                    // their username and what items they have purchased.
                    $items = module_envato::get_items_by_ticket($ticket['ticket_id']);
                    foreach($items as $item){
                        echo '<a href="'.$item['url'].'">'.htmlspecialchars($item['name']).'</a> ';
                    }
                    ?>
                </td>
                <?php } ?>
                <?php if(class_exists('module_group',false) && module_config::c('ticket_enable_groups',1) && module_group::groups_enabled()){ ?>
                    <td><?php
                    // find the groups for this customer.
                    $groups = module_group::get_groups_search(array(
                                                                  'owner_table' => 'ticket',
                                                                  'owner_id' => $ticket['ticket_id'],
                                                              ));
                    $g=array();
                    foreach($groups as $group){
                        $g[] = $group['name'];
                    }
                    echo implode(', ',$g);
                ?></td>
                <?php } ?>
                <?php
                if(module_config::c('ticket_allow_priority',0) && module_config::c('ticket_show_priority',1)){ ?>
                    <td>
                        <?php echo $priorities[$ticket['priority']];?>
                    </td>
                <?php } ?>
                <?php if(class_exists('module_extra',false)){
                module_extra::print_table_data('ticket',$ticket['ticket_id']);
                } ?>
            </tr>
		<?php } ?>
	</tbody>
</table>
    <?php echo $pagination['links'];?>
</form>