<?php 
/** 
  * Copyright: dtbaker 2012
  * Licence: Please check CodeCanyon.net for licence details. 
  * More licence clarification available here:  http://codecanyon.net/wiki/support/legal-terms/licensing-terms/ 
  * Deploy: 3053 c28b7e0e323fd2039bb168d857c941ee
  * Envato: 6b31bbe6-ead4-44a3-96e1-d5479d29505b
  * Package Date: 2013-02-27 19:09:56 
  * IP Address: 
  */ if($subscription['member_id']){ ?>
<h3><?php echo _l('Member Subscription'); ?></h3>
<table border="0" cellspacing="0" cellpadding="2" class="tableclass tableclass_form tableclass_full">
    <tbody>
    <tr>
        <td>
            <?php
            $member_name = module_member::link_open($subscription['member_id'],true);
            $subscription_name = module_subscription::link_open($subscription['subscription_id'],true);
            _e('This is a subscription payment for member %s on the subscription: %s',$member_name,$subscription_name); ?>
        </td>
    </tr>
    </tbody>
</table>
<?php }else if($subscription['customer_id']){ ?>
<h3><?php echo _l('Customer Subscription'); ?></h3>
<table border="0" cellspacing="0" cellpadding="2" class="tableclass tableclass_form tableclass_full">
    <tbody>
    <tr>
        <td>
            <?php
            $customer_name = module_customer::link_open($subscription['customer_id'],true);
            $subscription_name = module_subscription::link_open($subscription['subscription_id'],true);
            _e('This is a subscription payment for customer %s on the subscription: %s',$customer_name,$subscription_name); ?>
        </td>
    </tr>
    </tbody>
</table>    
<?php } ?>