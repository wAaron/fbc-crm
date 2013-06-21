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
if(isset($_REQUEST['customer_id'])){
    $search['customer_id'] = $_REQUEST['customer_id'];
}
if(isset($_REQUEST['job_id']) && (int)$_REQUEST['job_id']>0){
    $search['job_id'] = (int)$_REQUEST['job_id'];
    //$job = module_job::get_job($search['job_id'],false);
}
$files = module_file::get_files($search);

?>

<h2>
    <?php if(module_file::can_i('create','Files')){ ?>
	<span class="button">
		<?php echo create_link("Add New file","add",module_file::link_open('new')); ?>
	</span>
    <?php } ?>
	<?php echo _l('Customer files'); ?>
</h2>

<form action="" method="post">


<table class="search_bar">
	<tr>
		<th><?php _e('Filter By:'); ?></th>
		<td class="search_title">
			<?php echo _l('File Name / Description:'); ?>
		</td>
		<td class="search_input">
			<input type="text" name="search[generic]" value="<?php echo isset($search['generic'])?htmlspecialchars($search['generic']):''; ?>" size="30">
		</td>
        <?php if (class_exists('module_job',false)){ ?>
		<td class="search_title">
			<?php echo _l('Job:'); ?>
		</td>
		<td class="search_input">
            <?php echo print_select_box(module_job::get_jobs(),'search[job_id]',isset($search['job_id']) ? $search['job_id'] : false,'',true,'name'); ?>
		</td>
        <?php } ?>
		<td class="search_action">
			<?php echo create_link("Reset","reset",module_file::link_open(false)); ?>
			<?php echo create_link("Search","submit"); ?>
		</td>
	</tr>
</table>

<?php
$pagination = process_pagination($files);
$colspan = 4;
?>

<?php echo $pagination['summary'];?>

<table width="100%" border="0" cellspacing="0" cellpadding="2" class="tableclass tableclass_rows">
	<thead>
	<tr class="title">
		<th><?php echo _l('File Name'); ?></th>
		<th><?php echo _l('Description'); ?></th>
        <th><?php echo _l('File Size'); ?></th>
        <?php if(!isset($_REQUEST['customer_id'])){ ?>
        <th><?php echo _l('Customer'); ?></th>
        <?php } ?>
        <?php if (class_exists('module_job',false)){ ?>
        <th><?php echo _l('Job'); ?></th>
        <?php } ?>
        <th><?php echo _l('Date Added'); ?></th>
    </tr>
    </thead>
    <tbody>
		<?php
		$c=0;
		foreach($pagination['rows'] as $file){ ?>
		<tr class="<?php echo ($c++%2)?"odd":"even"; ?>">
			<td class="row_action">
				<?php echo module_file::link_open($file['file_id'],true);
                if(isset($file['file_url'])&&strlen($file['file_url'])){
                    echo ' ';
                    echo '<a href="'.htmlspecialchars($file['file_url']).'">'.htmlspecialchars($file['file_url']).'</a>';
                }
                ?>
			</td>
            <td>
                <?php echo nl2br(htmlspecialchars($file['description']));?>
            </td>
            <td>
                <?php
                if(file_exists($file['file_path'])){
                    echo module_file::format_bytes(filesize($file['file_path']));
                }
                ?>
            </td>
            <?php if(!isset($_REQUEST['customer_id'])){ ?>
            <td>
                <?php echo module_customer::link_open($file['customer_id'],true);?>
            </td>
            <?php } ?>
            <?php if (class_exists('module_job',false)){ ?>
            <td>
                <?php echo module_job::link_open($file['job_id'],true);?>
            </td>
            <?php } ?>
            <td>
                <?php echo _l('%s by %s',print_date($file['date_created']),module_user::link_open($file['create_user_id'],true));?>
            </td>
		</tr>
		<?php } ?>
	</tbody>
</table>
    <?php echo $pagination['links'];?>
</form>