<table width="100%" border="0" cellspacing="0" cellpadding="2" class="tableclass tableclass_form">
	<tbody>
		<tr>
			<th>
				<?php 
/** 
  * Copyright: dtbaker 2012
  * Licence: Please check CodeCanyon.net for licence details. 
  * More licence clarification available here:  http://codecanyon.net/wiki/support/legal-terms/licensing-terms/ 
  * Deploy: 3053 c28b7e0e323fd2039bb168d857c941ee
  * Envato: 6b31bbe6-ead4-44a3-96e1-d5479d29505b
  * Package Date: 2013-02-27 19:09:56 
  * IP Address: 
  */ echo _l('Contact Name'); ?>
			</th>
			<td>
				<?php echo $user_data['name'];?>
				<a href="<?php echo $plugins['user']->link_open($user_id);?>">&raquo;</a>
			</td>
		</tr>
		<tr>
			<th>
				<?php echo _l('Phone'); ?>
			</th>
			<td>
				<?php echo $user_data['phone'];?>
			</td>
		</tr>
		<tr>
			<th>
				<?php echo _l('Mobile'); ?>
			</th>
			<td>
				<?php echo $user_data['mobile'];?>
			</td>
		</tr>
		<tr>
			<th>
				<?php echo _l('Fax'); ?>
			</th>
			<td>
				<?php echo $user_data['fax'];?>
			</td>
		</tr>
		<tr>
			<th>
				<?php echo _l('Email'); ?>
			</th>
			<td>
				<a href="mailto:<?php echo $user_data['email'];?>"><?php echo $user_data['email'];?></a>
			</td>
		</tr>
		<tr>
			<th>

			</th>
			<td>
                <a href="<?php echo $plugins['user']->login_link($user_id);?>">Login as this Administrator &raquo;</a>
			</td>
		</tr>
	</tbody>
</table>