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


if(!module_config::can_i('view','Settings')){
    redirect_browser(_BASE_HREF);
}

$search = isset($_REQUEST['search']) ? $_REQUEST['search'] : array();
$subscriptions = module_subscription::get_subscriptions($search);

$pagination = process_pagination($subscriptions);

?>

<h2>
    <?php if(module_subscription::can_i('create','Subscriptions')){ ?>
	<span class="button">
		<?php echo create_link("Create New Subscription","add",module_subscription::link_open('new')); ?>
	</span>
    <?php } ?>
    <span class="title">
		<?php echo _l('Subscriptions'); ?>
	</span>
</h2>


<form action="" method="post">

<?php echo $pagination['summary'];?>
<table width="100%" border="0" cellspacing="0" cellpadding="2" class="tableclass tableclass_rows">
	<thead>
	<tr class="title">
		<th><?php echo _l('Subscription Name'); ?></th>
		<th><?php echo _l('Repeat Every'); ?></th>
		<th><?php echo _l('Amount'); ?></th>
		<th><?php echo _l('Member Count'); ?></th>
		<th><?php echo _l('Customer Count'); ?></th>
    </tr>
    </thead>
    <tbody>
    <?php
	$c=0;
	foreach($pagination['rows'] as $subscription){ ?>
        <tr class="<?php echo ($c++%2)?"odd":"even"; ?>">
            <td class="row_action">
	            <?php echo module_subscription::link_open($subscription['subscription_id'],true,$subscription); ?>
            </td>
            <td>
				<?php
                if(!$subscription['days']&&!$subscription['months']&&!$subscription['years']){
                    echo _l('Once off');
                }else{
                    $bits = array();
                    if($subscription['days']>0){
                        $bits[] = _l('%s days',$subscription['days']);
                    }
                    if($subscription['months']>0){
                        $bits[] = _l('%s months',$subscription['months']);
                    }
                    if($subscription['years']>0){
                        $bits[] = _l('%s years',$subscription['years']);
                    }
                    echo _l('Every %s',implode(', ',$bits));
                }
				?>
            </td>
            <td>
				<?php
                echo dollar($subscription['amount'],true,$subscription['currency_id']);
				?>
            </td>
            <td>
				<?php
                echo htmlspecialchars($subscription['member_count']);
				?>
            </td>
            <td>
				<?php
                echo htmlspecialchars($subscription['customer_count']);
				?>
            </td>
        </tr>
	<?php } ?>
  </tbody>
</table>
<?php echo $pagination['links'];?>
</form>