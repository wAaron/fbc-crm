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

$newsletter_templates = module_newsletter::get_templates($search);
?>

<h2>
	<span class="button">
		<?php echo create_link("Add New Template","add",module_newsletter::link_open_template('new')); ?>
	</span>
	<?php
        echo _l('Newsletter Templates');
    ?>
</h2>


<table width="100%" border="0" cellspacing="0" cellpadding="2" class="tableclass tableclass_rows">
	<thead>
	<tr class="title">
		<th><?php echo _l('Template Name'); ?></th>
        <th width="150"><?php echo _l('Action'); ?></th>
    </tr>
    </thead>
    <tbody>
		<?php
		$c=0;
		foreach($newsletter_templates as $newsletter_template){ ?>
		<tr class="<?php echo ($c++%2)?"odd":"even"; ?>">
			<td class="row_action">
				<?php echo module_newsletter::link_open_template($newsletter_template['newsletter_template_id'],true);?>
			</td>
            <td>
                <a href="<?php echo module_newsletter::link_open_template($newsletter_template['newsletter_template_id']);?>">Edit</a>
            </td>
		</tr>
		<?php } ?>
	</tbody>
</table>
