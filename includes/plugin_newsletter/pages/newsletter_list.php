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

$search = (isset($_REQUEST['search']) && is_array($_REQUEST['search'])) ? $_REQUEST['search'] : array();
if(isset($show_draft)){
    $search['draft'] = 1;
}
if(isset($show_pending)){
    $search['pending'] = 1;
}
$newsletters = module_newsletter::get_newsletters($search);
?>

<h2>
	<span class="button">
		<?php echo create_link("Add New newsletter","add",module_newsletter::link_open('new')); ?>
	</span>
	<?php
    if(isset($show_draft) && $show_draft){
        echo _l('Newsletter Drafts (have not been sent yet)');
    }else{

        echo _l('Newsletters');
    }
    ?>
</h2>

<form action="" method="post">


<table class="search_bar">
	<tr>
		<th><?php _e('Filter By:'); ?></th>
		<td class="search_title">
			<?php _e('Subject:');?>
		</td>
		<td class="search_input">
			<input type="text" name="search[generic]" value="<?php echo isset($search['generic'])?htmlspecialchars($search['generic']):''; ?>" size="30">
		</td>
		<td class="search_action">
			<?php echo create_link("Reset","reset",module_newsletter::link_list(false)); ?>
			<?php echo create_link("Search","submit"); ?>
		</td>
	</tr>
</table>

<?php
$pagination = process_pagination($newsletters);
$colspan = 4;
?>

<?php echo $pagination['summary'];?>

<table width="100%" border="0" cellspacing="0" cellpadding="2" class="tableclass tableclass_rows">
	<thead>
	<tr class="title">
		<th><?php echo _l('Email Subject'); ?></th>
		<th><?php echo _l('Sent From'); ?></th>
		<th><?php echo _l('Sent Date'); ?></th>
		<th><?php echo _l('Sent To'); ?></th>
		<th><?php echo _l('Views'); ?></th>
		<th><?php echo _l('Clicks'); ?></th>
		<th><?php echo _l('Unsubscribes'); ?></th>
		<th><?php echo _l('Bounces'); ?></th>
		<th><?php echo _l('Template'); ?></th>
        <th width="150"><?php echo _l('Action'); ?></th>
    </tr>
    </thead>
    <tbody>
		<?php
		$c=0;
		foreach($pagination['rows'] as $newsletter){
            $send_data = false;
            if($newsletter['send_id']){
                $send_data = module_newsletter::get_send($newsletter['send_id']);
                // special cache for old newsletter subject.
                if(isset($send_data['cache']) && strlen($send_data['cache'])>1){
                    $cache = unserialize($send_data['cache']);
                    if($cache){
                        $newsletter = array_merge($newsletter,$cache);
                    }
                }
            }
            ?>
		<tr class="<?php echo ($c++%2)?"odd":"even"; ?>">
			<td class="row_action">
				<?php echo module_newsletter::link_open($newsletter['newsletter_id'],true,$newsletter);?>
			</td>
            <td>
                &lt;<?php echo htmlspecialchars($newsletter['from_name']);?>&gt;
                <?php echo htmlspecialchars($newsletter['from_email']);?>
            </td>
            <td>
                <?php if(!$send_data || !$send_data['finish_time']){ echo _l('Never sent'); }else{
                    echo print_date($send_data['finish_time'],true);
                }
                ?>
            </td>
            <td>
                <?php
                if($send_data){
                    echo _l('%s of %s',(int)$send_data['total_sent_count'],(int)$send_data['total_member_count']);
                }
                ?>
            </td>
            <td>
                <?php
                if($send_data){
                    echo (int)$send_data['total_open_count'];
                    echo ' ';
                    if($send_data['total_member_count']>0){
                        echo '(' . (int)(($send_data['total_open_count']/$send_data['total_member_count'])*100).'%)';
                    }
                }
                ?>
            </td>
            <td>
                <?php
                if($send_data){
                    echo (int)$send_data['total_link_clicks'];
                    echo ' ';
                    if($send_data['total_member_count']>0){
                        echo '(' . (int)(($send_data['total_link_clicks']/$send_data['total_member_count'])*100).'%)';
                    }
                }
                ?>
            </td>
            <td>
                <?php
                if($send_data){
                    echo (int)$send_data['total_unsubscribe_count'];
                }
                ?>
            </td>
            <td>
                <?php
                if($send_data){
                    echo (int)$send_data['total_bounce_count'];
                }
                ?>
            </td>
            <td>
                <?php echo htmlspecialchars($newsletter['newsletter_template_name']);?>
            </td>
            <td>
                <?php
                
                if($send_data){
                    switch($send_data['status']){
                        case _NEWSLETTER_STATUS_SENT:
                            ?>
                            <a href="<?php echo module_newsletter::link_statistics($newsletter['newsletter_id'],$newsletter['send_id']);?>"><?php _e('View Statistics');?></a>
                            <a href="<?php echo module_newsletter::view_online_url($newsletter['newsletter_id'],0,$newsletter['send_id']);?>"><?php _e('Preview');?></a>
                            <?php
                            break;
                        case _NEWSLETTER_STATUS_PAUSED:
                            ?> <a href="<?php echo module_newsletter::link_queue_watch($newsletter['newsletter_id'],$newsletter['send_id']);?>"><?php _e('SENDING PAUSED');?></a>  <?php
                            break;
                        case _NEWSLETTER_STATUS_PENDING:
                            ?> <a href="<?php echo module_newsletter::link_queue_watch($newsletter['newsletter_id'],$newsletter['send_id']);?>"><?php _e('CURRENTLY SENDING');?></a>  <?php
                            break;
                        case _NEWSLETTER_STATUS_NEW:
                            ?> <a href="<?php echo module_newsletter::link_queue($newsletter['newsletter_id'],$newsletter['send_id']);?>"><?php _e('SEND');?></a> |

                                <a href="<?php echo module_newsletter::link_preview($newsletter['newsletter_id']);?>"><?php _e('Preview');?></a> |
                                <a href="<?php echo module_newsletter::link_open($newsletter['newsletter_id']);?>"><?php _e('Edit');?></a>
                                <?php
                            break;
                    }
                } ?>
            </td>
		</tr>
		<?php } ?>
	</tbody>
</table>
    <?php echo $pagination['links'];?>
</form>