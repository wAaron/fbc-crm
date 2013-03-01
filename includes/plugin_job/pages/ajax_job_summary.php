<?php 
/** 
  * Copyright: dtbaker 2012
  * Licence: Please check CodeCanyon.net for licence details. 
  * More licence clarification available here:  http://codecanyon.net/wiki/support/legal-terms/licensing-terms/ 
  * Deploy: 3053 c28b7e0e323fd2039bb168d857c941ee
  * Envato: 6b31bbe6-ead4-44a3-96e1-d5479d29505b
  * Package Date: 2013-02-27 19:09:56 
  * IP Address: 
  */ if(module_invoice::can_i('view','Invoices')){ ?>

<table border="0" cellspacing="0" cellpadding="2" class="tableclass tableclass_full">
<tbody>
    <?php if($job['total_sub_amount_unbillable']){ ?>
    <tr>
        <?php if($show_task_numbers){ ?>
        <td rowspan="2">&nbsp;</td>
        <?php } ?>
        <td>

        </td>
        <td>
            <?php _e('Sub Total:');?>
        </td>
        <td>
            <span class="currency">
            <?php echo dollar($job['total_sub_amount'] + $job['total_sub_amount_unbillable'],true,$job['currency_id']);?>
            </span>
        </td>
        <td>
            &nbsp;
        </td>
        <td>
            &nbsp;
        </td>
        <td rowspan="2">
            &nbsp;
        </td>
        <?php if(module_config::c('job_allow_staff_assignment',1)){ ?>
        <td rowspan="2">
            &nbsp;
        </td>
        <?php } ?>
    </tr>
    <tr>
        <td>

        </td>
        <td>
            <?php _e('Unbillable:');?>
        </td>
        <td>
            <span class="currency">
            <?php echo dollar($job['total_sub_amount_unbillable'],true,$job['currency_id']);?>
            </span>
        </td>
        <td>
            &nbsp;
        </td>
        <td>
            &nbsp;
        </td>
    </tr>
    <?php } ?>
    <tr>
        <?php if($show_task_numbers){ ?>
        <td rowspan="7">&nbsp;</td>
        <?php } ?>
        <td>
            <?php echo _l('%s Hours Total',$job['total_hours']);?>
            <?php if($job['total_hours_overworked']){ ?>
                <?php echo _l('(%s Hours Over)',$job['total_hours_overworked']);?>
            <?php } ?>
        </td>
        <td>
            <?php _e('Sub Total:');?>
        </td>
        <td>
            <span class="currency">
            <?php echo dollar($job['total_sub_amount'],true,$job['currency_id']);?>
            </span>
            <?php if($job['invoice_discounts']>0){ ?>
            <br/><?php _e('(%s discount)',dollar($job['invoice_discounts'],true,$job['currency_id']));?>
            <?php } ?>
        </td>
        <td>
            &nbsp;
        </td>
        <td>
            &nbsp;
        </td>
        <td rowspan="7">
            &nbsp;
        </td>
        <?php if(module_config::c('job_allow_staff_assignment',1)){ ?>
        <td rowspan="7">
            &nbsp;
        </td>
        <?php } ?>
    </tr>
    <tr>
        <td>
            <?php echo _l('%s Hours Done',$job['total_hours_completed']);?>
        </td>
        <td>
            <?php _e('Tax:');?>
        </td>
        <td>
            <span class="currency">
            <?php echo dollar($job['total_tax'],true,$job['currency_id']);?>
            </span>
        </td>
        <td>
            <?php echo $job['total_tax_name'] ;?> =
            <?php echo $job['total_tax_rate'] . '%' ;?>
        </td>
        <td>
            &nbsp;
        </td>
    </tr>
    <tr>
        <td>
            <?php echo _l('%s Hours / %s Tasks Remain',$job['total_hours_remain'],$job['total_tasks_remain']);?>
            <?php if($job['total_amount_todo']>0){ ?>
                <span class="error_text">
                    (<?php echo dollar($job['total_amount_todo'],true,$job['currency_id']);?>)
                </span>
            <?php } ?>
        </td>
        <td>
            <?php _e('Total:');?>
        </td>
        <td>
            <span class="currency" style="text-decoration: underline; font-weight: bold;">
                <?php echo dollar($job['total_amount'],true,$job['currency_id']);?>
            </span>
        </td>
        <td colspan="2">
            &nbsp;
        </td>
    </tr>
    <tr>
        <td colspan="5">&nbsp;</td>
    </tr>
    <tr>
        <td align="right">

        </td>
        <td>
            <?php _e('Invoiced:');?>
        </td>
        <td>
            <span class="currency">
                <?php echo dollar($job['total_amount_invoiced'],true,$job['currency_id']);?>
            </span>
            <?php if(isset($job['total_amount_invoiced_deposit']) && $job['total_amount_invoiced_deposit'] > 0){ ?>
                <br/>
                <span class="currency">
                (<?php echo dollar($job['total_amount_invoiced_deposit'],true,$job['currency_id']);?> <?php _e('deposit');?>)
            </span>
        <?php } ?>
        </td>
        <td colspan="2">

        </td>
    </tr>
    <tr>
        <td align="right">

        </td>
        <td>
            <?php _e('Paid:');?>
        </td>
        <td>
            <span class="currency success_text">
                <?php echo dollar($job['total_amount_paid'],true,$job['currency_id']);?>
            </span>
        </td>
        <td>
            &nbsp;
        </td>
        <td>
            &nbsp;
        </td>
    </tr>
    <tr>
        <td align="right">

        </td>
        <td>
            <?php _e('Unpaid:');?>
        </td>
        <td>
            <span class="currency error_text">
                <?php echo dollar($job['total_amount_outstanding'],true,$job['currency_id']);?>
            </span>
        </td>
        <td>
            &nbsp;
        </td>
        <td>
            &nbsp;
        </td>
    </tr>
    <!-- <tr>
        <td align="right">

        </td>
        <td>
            <?php _e('Invoicable:');?>
        </td>
        <td>
            <span class="currency error_text">
                <?php echo dollar($job['total_amount_invoicable'],true,$job['currency_id']);?>
            </span>
        </td>
        <td colspan="2">
            <?php

        $real_due = $due = $job['total_amount_invoicable'];// - $job['total_amount_invoiced'];
        if( $due > 0){
            if($due == $job['total_amount_invoicable']){
                $real_due = 0; // don't create a custom invoice
            }else{
                // create a custom invoice for this remainder amount
                // but take the tax off (if it exists) because the invoice will add that again ontop
                if($job['total_tax_rate'] > 0){
                    // reverse enginner the amount of tax on it:
                    $real_due = ($real_due / (1+$job['total_tax_rate']/100));
                }
            }
            ?>
                <a href="<?php echo module_invoice::link_generate('new',array('arguments'=>array(
                'job_id' => $job_id,
                'amount_due' => $real_due,
            ))); ?>"><?php echo _l('Create invoice for %s',dollar($due,true,$job['currency_id']));?></a>
        <?php } ?>
            </td>
        </tr>
        <tr>
            <td align="right">

            </td>
            <td>
                <?php _e('Todo:');?>
            </td>
            <td>
                <span class="currency error_text">
                    <?php echo dollar($job['total_amount_todo'],true,$job['currency_id']);?>
                </span>
            </td>
            <td colspan="2">

            </td>
        </tr> -->
    </tbody>
</table>
<?php }else{ ?>

<?php echo _l('%s Hours Total',$job['total_hours']);?>
<?php if($job['total_hours_overworked']){ ?>
    <?php echo _l('(%s Hours Over)',$job['total_hours_overworked']);?>
    <?php } ?>
    <br/>
<?php echo _l('%s Hours Done',$job['total_hours_completed']);?>
    <br>
<?php echo _l('%s Hours / %s Tasks Remain',$job['total_hours_remain'],$job['total_tasks_remain']);?>

<?php } ?>