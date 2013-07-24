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

$search = (isset($_REQUEST['search']) && is_array($_REQUEST['search'])) ? $_REQUEST['search'] : array();
if(isset($_REQUEST['customer_id'])){
    $search['customer_id'] = $_REQUEST['customer_id'];
}
if(isset($_REQUEST['job_id']) && (int)$_REQUEST['job_id']>0){
    $search['job_id'] = (int)$_REQUEST['job_id'];
    //$job = module_job::get_job($search['job_id'],false);
}
$emails = module_email::get_emails($search);

?>

<h2>
    <?php if(module_email::can_i('create','Emails')){ ?>
	<span class="button">
		<?php echo create_link("Send New Email","add",module_email::link_open('new')); ?>
	</span>
    <?php } ?>
	<?php echo _l('Customer Emails'); ?>
</h2>

<form action="" method="post">


<table class="search_bar">
	<tr>
		<th><?php _e('Filter By:'); ?></th>
		<td class="search_title">
			<?php echo _l('Email Subject:'); ?>
		</td>
		<td class="search_input">
			<input type="text" name="search[generic]" value="<?php echo isset($search['generic'])?htmlspecialchars($search['generic']):''; ?>" size="30">
		</td>
        <td class="search_title">
			<?php echo _l('Sent Date:');?>
		</td>
		<td class="search_input">
			<input type="text" name="search[date_from]" value="<?php echo isset($search['date_from'])?htmlspecialchars($search['date_from']):''; ?>" class="date_field">
            <?php _e('to');?>
			<input type="text" name="search[date_to]" value="<?php echo isset($search['date_to'])?htmlspecialchars($search['date_to']):''; ?>" class="date_field">
		</td>
        <?php /*if (class_exists('module_job',false)){ ?>
		<td class="search_title">
			<?php echo _l('Job:'); ?>
		</td>
		<td class="search_input">
            <?php echo print_select_box(module_job::get_jobs(array('customer_id'=>$_REQUEST['customer_id'])),'search[job_id]',isset($search['job_id']) ? $search['job_id'] : false,'',true,'name'); ?>
		</td>
        <?php }*/ ?>
		<td class="search_action">
			<?php echo create_link("Reset","reset",module_email::link_open(false)); ?>
			<?php echo create_link("Search","submit"); ?>
		</td>
	</tr>
</table>

<?php
$pagination = process_pagination($emails);
$colspan = 4;
?>

<?php echo $pagination['summary'];?>

<table border="0" class="tableclass tableclass_rows">
	<thead>
	<tr class="title">
		<th><?php echo _l('Email Subject'); ?></th>
		<th><?php echo _l('Sent Date'); ?></th>
        <th><?php echo _l('Sent To'); ?></th>
        <th><?php echo _l('Sent By'); ?></th>
        <?php if(!isset($_REQUEST['customer_id'])){ ?>
        <th><?php echo _l('Customer'); ?></th>
        <?php } ?>
    </tr>
    </thead>
    <tbody>
		<?php
		$c=0;
		foreach($pagination['rows'] as $email){ ?>
		<tr class="<?php echo ($c++%2)?"odd":"even"; ?>">
			<td class="row_action">
				<?php echo module_email::link_open($email['email_id'],true); ?>
            </td>
            <td><?php echo print_date($email['sent_time']);?></td>
            <td><?php $headers = unserialize($email['headers']);
                if(isset($headers['to']) && is_array($headers['to'])){
                    foreach($headers['to'] as $to){
                        echo $to['email'].' ';
                    }
                }
                ?></td>
            <td><?php echo module_user::link_open($email['create_user_id'],true);?></td>
            <?php if(!isset($_REQUEST['customer_id'])){ ?>
            <td>
                <?php echo module_customer::link_open($email['customer_id'],true);?>
            </td>
            <?php } ?>
		</tr>
		<?php } ?>
	</tbody>
</table>
    <?php echo $pagination['links'];?>
</form>