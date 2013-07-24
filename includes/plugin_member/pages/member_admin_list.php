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


$search = isset($_REQUEST['search']) ? $_REQUEST['search'] : array();
$members = module_member::get_members($search);

// hack to add a "group" option to the pagination results.
if(class_exists('module_group',false)){
    module_group::enable_pagination_hook(
        // what fields do we pass to the group module from this members?
        array(
            'fields'=>array(
                'owner_id' => 'member_id',
                'owner_table' => 'member',
            ),
            'bulk_actions'=>array(
                'delete'=>array(
                    'label'=>'Delete these members',
                    'type'=>'delete',
                    'callback'=>'module_member::handle_bulk_delete',
                ),
            ),
        )
    );
}
// hack to add a "export" option to the pagination results.
if(class_exists('module_import_export',false) && module_member::can_i('view','Export Members')){
    module_import_export::enable_pagination_hook(
        // what fields do we pass to the import_export module from this members?
        array(
            'name' => 'Member Export',
            'fields'=>array(
                'Member ID' => 'member_id',
                'First Name' => 'first_name',
                'Last Name' => 'last_name',
                'Business Name' => 'business',
                'Email' => 'email',
                'Phone' => 'phone',
                'Mobile' => 'mobile',
            ),
            // do we look for extra fields?
            'extra' => array(
                'owner_table' => 'member',
                'owner_id' => 'member_id',
            ),
        )
    );
}
$pagination = process_pagination($members);

?>

<h2>
    <?php if(module_member::can_i('create','Members')){ ?>
	<span class="button">
		<?php echo create_link("Create New Member","add",module_member::link_open('new')); ?>
	</span>
    <?php
    }
    if(class_exists('module_import_export',false) && module_member::can_i('view','Import Members')){
        $link = module_import_export::import_link(
            array(
                'callback'=>'module_member::handle_import',
                'name'=>'Members',
                'return_url'=>$_SERVER['REQUEST_URI'],
                'group'=>array('member','newsletter_subscription'),
                'fields'=>array(
                    'Member ID' => 'member_id',
                    'First Name' => 'first_name',
                    'Last Name' => 'last_name',
                    'Business Name' => 'business',
                    'Email' => 'email',
                    'Phone' => 'phone',
                    'Mobile' => 'mobile',
                ),
                // do we try to import extra fields?
                'extra' => array(
                    'owner_table' => 'member',
                    'owner_id' => 'member_id',
                ),
            )
        );
        ?>
        <span class="button">
            <?php echo create_link("Import Members","add",$link); ?>
        </span>
        <?php
    }
    ?>
	<span class="title">
		<?php echo _l('Members'); ?>
	</span>
</h2>


<form action="" method="post">

<table class="search_bar" width="100%">
	<tr>
		<th><?php _e('Filter By:'); ?></th>
		<td width="140px">
			<?php _e('Names, Phone or Email:');?>
		</td>
		<td>
			<input type="text" style="width: 240px;" name="search[generic]" class="" value="<?php echo isset($search['generic'])?htmlspecialchars($search['generic']):''; ?>">
		</td>
        <?php if(class_exists('module_group',false) && module_member::can_i('view','Member Groups')){ ?>
        <td width="60">
            <?php _e('Group:');?>
        </td>
        <td>
            <?php echo print_select_box(module_group::get_groups('member'),'search[group_id]',isset($search['group_id'])?$search['group_id']:false,'',true,'name'); ?>
        </td>
        <?php if(class_exists('module_newsletter',false)){ ?>
        <td width="60">
            <?php _e('Newsletter:');?>
        </td>
        <td>
            <?php echo print_select_box(module_group::get_groups('newsletter_subscription'),'search[group_id2]',isset($search['group_id2'])?$search['group_id2']:false,'',true,'name'); ?>
        </td>
        <?php } ?>
        <?php } ?>
		<td align="right" rowspan="2">
			<?php echo create_link("Reset","reset",module_member::link_open(false)); ?>
			<?php echo create_link("Search","submit"); ?>
		</td>
	</tr>
</table>

<?php echo $pagination['summary'];?>
<table width="100%" border="0" cellspacing="0" cellpadding="2" class="tableclass tableclass_rows">
	<thead>
	<tr class="title">
		<th><?php echo _l('Member Name'); ?></th>
		<th><?php echo _l('Business'); ?></th>
		<th><?php echo _l('Phone'); ?></th>
		<th><?php echo _l('Mobile'); ?></th>
		<th><?php echo _l('Email Address'); ?></th>
        <?php if(class_exists('module_subscription',false)){ ?>
        <th><?php _e('Subscription');?></th>
        <?php } ?>
        <?php if(class_exists('module_group',false)){ ?>
        <th><?php echo _l('Group'); ?></th>
            <?php if(class_exists('module_newsletter',false)){ ?>
            <th><?php echo _l('Newsletter'); ?></th>
            <?php } ?>
        <?php } ?>
    </tr>
    </thead>
    <tbody>
    <?php
	$c=0;
	foreach($pagination['rows'] as $member){ ?>
        <tr class="<?php echo ($c++%2)?"odd":"even"; ?>">
            <td class="row_action">
	            <?php echo module_member::link_open($member['member_id'],true); ?>
            </td>
            <td>
				<?php
				echo htmlspecialchars($member['business']);
				?>
            </td>
            <td>
				<?php
                echo htmlspecialchars($member['phone']);
				?>
            </td>
            <td>
				<?php
                echo htmlspecialchars($member['mobile']);
				?>
            </td>
            <td>
                <?php echo htmlspecialchars($member['email']); ?>
            </td>
            <?php if(class_exists('module_subscription',false)){ ?>
            <td>
                <?php foreach(module_subscription::get_subscriptions_by_member($member['member_id']) as $subscription){
                echo dollar($subscription['amount'],true,$subscription['currency_id']);
                echo ' ';
                echo htmlspecialchars($subscription['name']);
                echo ' ';
                $next_due = strtotime($subscription['next_due_date']);
                if ($next_due < time()){
                    echo ' <span class="important">';
                    echo _e('Overdue: ');
                    echo '</span> ';
                }else{
                    _e('Due: ');
                }
                echo print_date($next_due);
                $days = ceil(($next_due - time())/86400);
                if(abs($days) == 0){
                    _e(' (today)');
                }else{
                    _e(' (%s days)',$days);
                }
                // todo - work out if overdue - or when next due.
            } ?>
            </td>
            <?php } ?>
            <?php if(class_exists('module_group',false)){ ?>
                <td><?php
                // find the groups for this member.
                    $g=array();
                    $groups = module_group::get_groups_search(array(
                        'owner_table' => 'member',
                        'owner_id' => $member['member_id'],
                    ));
                    foreach($groups as $group){
                        $g[] = $group['name'];
                    }
                    echo implode(', ',$g);
                ?></td>
                <?php if(class_exists('module_newsletter',false)){ ?>
                <td><?php
                    // find the groups for this member.
                    $g=array();
                    $groups = module_group::get_groups_search(array(
                        'owner_table' => 'newsletter_subscription',
                        'owner_id' => $member['member_id'],
                    ));;
                    foreach($groups as $group){
                        $g[] = $group['name'];
                    }
                    echo implode(', ',$g);
                    echo ' ';
                    $newsletter_member_id = module_newsletter::member_from_email($member,false);
                    if($newsletter_member_id){
                        if($res = module_newsletter::is_member_unsubscribed($newsletter_member_id,$member)){
                            if(isset($res['unsubscribe_send_id']) && $res['unsubscribe_send_id']){
                                // they unsubscribed from a send.
                                $send_data = module_newsletter::get_send($res['unsubscribe_send_id']);
                                _e('(unsubscribed %s)',print_date($res['time']));
                            }else if(isset($res['reason']) && $res['reason'] == 'no_email'){
                                _e('(do not send)');
                            }else if(isset($res['reason']) && $res['reason'] == 'doubleoptin'){
                                _e('(double opt-in incomplete)',print_date($res['time']));
                            }else{
                                _e('(unsubscribed %s)',print_date($res['time']));
                            }
                        }
                    }
                    ?></td>
                <?php } ?>
            <?php } ?>
        </tr>
	<?php } ?>
  </tbody>
</table>
<?php echo $pagination['links'];?>
</form>