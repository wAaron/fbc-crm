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
$payment_historyies = module_invoice::get_invoice_payments($invoice_id);
foreach($payment_historyies as $invoice_payment_id => $invoice_payment_data){
    if(module_config::c('invoice_hide_pending_payments',1)){
        if(!trim($invoice_payment_data['date_paid']) || $invoice_payment_data['date_paid'] == '0000-00-00'){
            unset($payment_historyies[$invoice_payment_id]);
        }
    }
}
if(count($payment_historyies)){
?>

<?php if(!isset($mode) || $mode=='html'){ ?>
    <h3><?php _e('Payment History:');?></h3>
<?php }else{ ?>
    <strong><?php _e('Payment History:');?></strong><br/>
<?php } ?>
        
<table cellpadding="4" cellspacing="0" width="100%" class="table tableclass tableclass_rows">
	<thead>
		<tr style="background-color: #000000; color:#FFFFFF;">
            <th><?php _e('Payment Date');?></th>
            <th><?php _e('Payment Method');?></th>
            <?php if(module_config::c('invoice_payments_show_details',1)){ ?>
            <th><?php _e('Details');?></th>
            <?php } ?>
            <th><?php _e('Amount');?></th>
            <th> </th>
        </tr>
    </thead>
    <tbody>
    <?php foreach($payment_historyies as $invoice_payment_id => $invoice_payment_data){
        ?>
            <tr>
                <td>
                    <?php echo (!trim($invoice_payment_data['date_paid']) || $invoice_payment_data['date_paid'] == '0000-00-00') ? _l('Pending on %s',print_date($invoice_payment_data['date_created'])) : print_date($invoice_payment_data['date_paid']);?>
                </td>
                <td>
                    <?php echo htmlspecialchars($invoice_payment_data['method']);?>
                </td>
                <?php if(module_config::c('invoice_payments_show_details',1)){ ?>
                <td>
                    <?php
                    if(isset($invoice_payment_data['data'])&&$invoice_payment_data['data']){
                        $details = unserialize($invoice_payment_data['data']);
                        if(isset($details['custom_notes'])){
                            echo htmlspecialchars($details['custom_notes']);
                        }
                    }
                    ?>
                </td>
                <?php } ?>
                <td>
                    <?php echo dollar($invoice_payment_data['amount'],true,$invoice_payment_data['currency_id']); ?>
                </td>
                <td align="center">
                    <a href="<?php echo module_invoice::link_receipt($invoice_payment_data['invoice_payment_id']);?>" target="_blank"><?php _e('View Receipt');?></a>
                </td>
            </tr>
    <?php } ?>
    </tbody>
</table>
<?php } ?>