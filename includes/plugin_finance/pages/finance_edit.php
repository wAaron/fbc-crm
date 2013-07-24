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

$locked = false;

$linked_finances = $linked_invoice_payments = array();

$finance_id = (int)$_REQUEST['finance_id'];
$finance = module_finance::get_finance($finance_id);
if($finance_id <= 0){
    if(isset($_REQUEST['from_job_id'])){
        $job_data = module_job::get_job((int)$_REQUEST['from_job_id'],false);
        $finance['job_id'] = $job_data['job_id'];
        if($job_data['customer_id']){
            $finance['customer_id'] = $job_data['customer_id'];
        }
    }
    if(isset($_REQUEST['invoice_payment_id'])){
        $invoice_payment_data = module_invoice::get_invoice_payment($_REQUEST['invoice_payment_id']);
        $linked_invoice_payments[] = $invoice_payment_data;
        $invoice_data = module_invoice::get_invoice($invoice_payment_data['invoice_id']);
        $finance['customer_id'] = $invoice_data['customer_id'];
        if($invoice_data['job_ids']){
            foreach($invoice_data['job_ids'] as $job_id){
                $finance['job_id'] = $job_id;// meh! pick last one.
            }
        }
        $locked = true;
    }
}else{
    $linked_invoice_payments = $finance['linked_invoice_payments'];
    $linked_finances = $finance['linked_finances'];
    $module->page_title = $finance['name'];
}

// check permissions.
if(class_exists('module_security',false)){
    if(($finance_id>0 && $finance['finance_id']==$finance_id) || (isset($_REQUEST['invoice_payment_id']) && isset($invoice_payment_data) && $invoice_payment_data)){
        // if they are not allowed to "edit" a page, but the "view" permission exists
        // then we automatically grab the page and regex all the crap out of it that they are not allowed to change
        // eg: form elements, submit buttons, etc..
        module_security::check_page(array(
            'category' => 'Finance',
            'page_name' => 'Finance',
            'module' => 'finance',
            'feature' => 'Edit',
        ));
    }else{
        module_security::check_page(array(
            'category' => 'Finance',
            'page_name' => 'Finance',
            'module' => 'finance',
            'feature' => 'Create',
        ));
    }
    module_security::sanatise_data('finance',$finance);
}
if(isset($finance['invoice_payment_id']) && (int)$finance['invoice_payment_id'] > 0){
    $locked = true;
}

$finance_recurring_id = isset($_REQUEST['finance_recurring_id']) ? (int)$_REQUEST['finance_recurring_id'] : false;
if($finance_id > 0 && $finance && isset($finance['finance_recurring_id']) && $finance['finance_recurring_id']){
    $finance_recurring_id = $finance['finance_recurring_id'];
}
if($finance_recurring_id>0){
    $finance_recurring = module_finance::get_recurring($finance_recurring_id);
}
if(!$finance_id && $finance_recurring_id >0){
    $finance = array_merge($finance,$finance_recurring);
    //print_r($finance_recurring);
    $finance['transaction_date'] = $finance_recurring['next_due_date'];
    /*$finance['name'] = $finance_recurring['name'];
    $finance['amount'] = $finance_recurring['amount'];
    $finance['description'] = _l('Recurring expense');*/
}




if($finance_id>0 && count($linked_invoice_payments) || count($linked_finances)){
    $locked = true;
    echo _l('Transaction locked. Please unlink it if you would like to make changes to price, type or date.');
}

?>
<form action="" method="post">

      <?php
module_form::prevent_exit(array(
    'valid_exits' => array(
        // selectors for the valid ways to exit this form.
        '.submit_button',
    ))
);
?>

    
	<input type="hidden" name="_process" value="save_finance" />
	<input type="hidden" name="finance_id" value="<?php echo $finance_id; ?>" />
	<input type="hidden" name="invoice_payment_id" value="<?php echo isset($_REQUEST['invoice_payment_id']) ? (int)$_REQUEST['invoice_payment_id'] : ''; ?>" />
	<input type="hidden" name="finance_recurring_id" value="<?php echo $finance_recurring_id; ?>" />

    <h2><?php
        if($finance_id>0){
            _e('Edit transaction');
        }else{
            if($finance_recurring_id){
                _e('Record recurring transaction');
            }else{
                _e('Create transaction');
            }
        }
        ?></h2>

    <table width="100%" border="0" cellspacing="0" cellpadding="2" class="tableclass tableclass_form">
        <tbody>
            <tr>
                <th class="width2">
                    <?php echo _l('Date'); ?>
                </th>
                <td>
                    <?php if($locked){ echo print_date($finance['transaction_date']); }else{ ?>
                    <input type="text" name="transaction_date" id="transaction_date" value="<?php echo print_date($finance['transaction_date']); ?>" class="date_field" />
                    <?php } ?>
                    <?php if(!(int)$finance_id && isset($finance_recurring['next_due_date'])){ ?>
                        <?php _e('(recurring on <a href="%s">%s</a>)','javascript:$(\'#transaction_date\').val(\''.print_date($finance_recurring['next_due_date']).'\'); return false;',print_date($finance_recurring['next_due_date'])); ?>
                    <?php } ?>
                </td>
            </tr>
            <?php
            if(count($linked_invoice_payments)){ ?>
                <tr>
                    <th>
                        <?php _e('Linked Invoices'); ?>
                    </th>
                    <td>
                        <?php foreach($linked_invoice_payments as $linked_invoice_payment){
                        echo module_invoice::link_open($linked_invoice_payment['invoice_id'],true);
                        echo '<br>';
                } ?>
                    </td>
                </tr>
            <?php }
            if(count($linked_finances)){ ?>
                <tr>
                    <th>
                        <?php _e('Linked Transactions'); ?>
                    </th>
                    <td>
                    <?php foreach($linked_finances as $linked_finance){
                        echo module_finance::link_open($linked_finance['finance_id'],true);
                        echo ' ';
                        echo dollar($linked_finance['amount']);
                        echo ' ';
                        echo print_date($linked_finance['transaction_date']);
                        echo '<br>';
                    } ?>
                    </td>
                </tr>
            <?php } ?>
            <?php if($finance_recurring_id>0){ ?>
            <tr>
                <th>
                    <?php echo _l('Recurring'); ?>
                </th>
                <td>
                    <a href="<?php echo module_finance::link_open_recurring($finance_recurring_id);?>"><?php if(!$finance_recurring['days']&&!$finance_recurring['months']&&!$finance_recurring['years']){
                    echo _l('Once off');
                }else{
                    echo _l('Every %s days, %s months and %s years between %s and %s',$finance_recurring['days'],$finance_recurring['months'],$finance_recurring['years'],($finance_recurring['start_date'] && $finance_recurring['start_date'] != '0000-00-00') ? print_date($finance_recurring['start_date']) : 'now',($finance_recurring['end_date'] && $finance_recurring['end_date'] != '0000-00-00') ? print_date($finance_recurring['end_date']) : 'forever');
                } ?></a>
                    <?php
                // see if we can find previous transactions from this recurring schedule.
                    // copied from recurring_edit
                    if(isset($finance_recurring['last_transaction_finance_id']) && $finance_recurring['last_transaction_finance_id']){
                        ?> <a href="<?php echo module_finance::link_open($finance_recurring['last_transaction_finance_id']);?>"><?php
                            echo _l('Last transaction: %s on %s',currency($finance_recurring['last_amount']),print_date($finance_recurring['last_transaction_date']));
                            ?></a>
                        (<a href="<?php echo module_finance::link_open(false);?>?search[finance_recurring_id]=<?php echo $finance_recurring_id;?>"><?php _e('view all');?></a>)
                        <?php
                    }
                ?>
                </td>
            </tr>
            <?php } ?>
            <tr>
                <th>
                    <?php echo _l('Name'); ?>
                </th>
                <td>
                    <input type="text" name="name" style="width: 350px;" value="<?php echo htmlspecialchars($finance['name']); ?>" />
                </td>
            </tr>
            <tr>
                <th>
                    <?php echo _l('Description'); ?>
                </th>
                <td>
                    <textarea name="description" rows="4" cols="30" style="width:350px; height: 100px;"><?php echo htmlspecialchars($finance['description']); ?></textarea>
                </td>
            </tr>
            <tr>
                <th>
                    <?php echo _l('Type'); ?>
                </th>
                <td valign="top">
                    <?php if($locked){ echo $finance['type'] == 'i' ? _l('Income') : _l('Expense'); }else{ ?>
                    <input type="radio" name="type" id="income" value="i"<?php echo $finance['type'] == 'i' ? ' checked' : '';?>> <label for="income">Income/Credit</label> <br/>
                    <input type="radio" name="type" id="expense" value="e"<?php echo $finance['type'] == 'e' ? ' checked' : '';?>> <label for="expense">Expense/Debit</label> <br/>
                    <?php } ?>
                </td>
            </tr>
            <tr>
                <th>
                    <?php echo _l('Amount'); ?>
                </th>
                <td valign="top">
                    <?php if($locked){ echo dollar($finance['amount']); }else{ ?>
                    <?php echo currency('');?>
                    <input type="text" name="amount" value="<?php echo htmlspecialchars($finance['amount']);?>" class="currency">
                    <?php } ?>
                </td>
            </tr>
            <tr>
                <th>
                    <?php echo _l('Currency'); ?>
                </th>
                <td valign="top">
                    <?php echo print_select_box(get_multiple('currency','','currency_id'),'currency_id',$finance['currency_id'],'',false,'code'); ?>
                </td>
            <tr>
                <th>
                    <?php echo _l('Account'); ?>
                </th>
                <td valign="top">
                    <?php echo print_select_box(module_finance::get_accounts(),'finance_account_id',isset($finance['finance_account_id'])?$finance['finance_account_id']:'','',true,'name',true); ?>
                </td>
            </tr>
            <tr>
                <th>
                    <?php echo _l('Categories'); ?>
                </th>
                <td valign="top">
                    <?php
                    $categories = module_finance::get_categories();
                    foreach($categories as $category){ ?>
                        <input type="checkbox" name="finance_category_id[]" value="<?php echo $category['finance_category_id'];?>" id="category_<?php echo $category['finance_category_id'];?>" <?php echo isset($finance['category_ids'][$category['finance_category_id']]) ? ' checked' : '';?>>
                        <label for="category_<?php echo $category['finance_category_id'];?>"><?php echo htmlspecialchars($category['name']);?></label> <br/>
                        <?php }
                    ?>
                    <input type="checkbox" name="finance_category_new_checked" value="new">
                    <input type="text" name="finance_category_new" value="">

                </td>
            </tr>
            <?php if(module_config::c('finance_link_to_jobs',1) && module_job::can_i('view','Jobs')){ ?>
                <tr>
                    <th>
                        <?php _e('Linked Customer');?>
                    </th>
                    <td>
                        <?php echo print_select_box(module_customer::get_customers(),'customer_id',$finance['customer_id'],'',true,'customer_name'); ?>
                        <script type="text/javascript">
                        $(function(){
                            $('#customer_id').change(function(){
                                // change our customer id.
                                var new_customer_id = $(this).val();
                                $.ajax({
                                    type: 'POST',
                                    url: '<?php echo module_job::link_open(false);?>',
                                    data: {
                                        '_process': 'ajax_job_list',
                                        'customer_id': new_customer_id
                                    },
                                    dataType: 'json',
                                    success: function(newOptions){
                                        $('#job_id').find('option:gt(0)').remove();
                                        $.each(newOptions, function(value, key) {
                                            $('#job_id').append($("<option></option>")
                                                .attr("value", value).text(key));
                                        });
                                    },
                                    fail: function(){
                                        alert('Changing customer failed, please refresh and try again.');
                                    }
                                });
                            });
                        });
                    </script>
                    </td>
                </tr>
                <tr>
                    <th>
                        <?php _e('Linked Job');?>
                    </th>
                    <td>
                        <?php

                        $d = array();
                        if($finance['customer_id']){
                            $jobs = module_job::get_jobs(array('customer_id'=>$finance['customer_id']));
                            foreach($jobs as $job){
                                $d[$job['job_id']] = $job['name'];
                            }
                        }

                        echo print_select_box($d, 'job_id', $finance['job_id']);
                        ?>
                    </td>
                </tr>
            <?php } ?>
            <tr>
                <th>
                    <?php echo _l('Attachment'); ?>
                </th>
                <td>
                     <?php if((int)$finance_id>0){ ?>
                         <?php
                         module_file::display_files(array(
                            'owner_table' => 'finance',
                            'owner_id' => $finance_id,
                            //'layout' => 'list',
                                 'layout' => 'gallery',
                                 'editable' => module_security::is_page_editable() && module_finance::can_i('edit','Finance'),
                            )
                        );
                        ?>
                    <?php }else{
                            _e('Please press save first');
                        } ?>
                    </td>
                </tr>
                <?php
            if((int)$finance_id>0){
                         module_extra::display_extras(array(
                            'owner_table' => 'finance',
                            'owner_key' => 'finance_id',
                            'owner_id' => $finance_id,
                            'layout' => 'table_row',
                                 'allow_new' => module_customer::can_i('edit','Finance'),
                                 'allow_edit' => module_customer::can_i('edit','Finance'),
                            )
                        );
            }
            ?>
        <tr>
            <td colspan="2" align="center">
                <input type="submit" name="butt_save" id="butt_save" value="<?php _e('Save'); ?>" class="submit_button save_button" />
                <?php if((int)$finance_recurring_id>0 && isset($_SESSION['_finance_recurring_ids'])){
                    // find if there is a next recurring id
                    $next = 0;
                    foreach($_SESSION['_finance_recurring_ids'] as $next_data){
                        if($next == -1){
                            $next = 1; // done.
                            ?>
                            <input type="hidden" name="recurring_next" id="recurring_next" value="">
                            <input type="submit" name="butt_save" value="<?php _e('Save & Next Transaction');?>" class="submit_button save_button" onclick="$('#recurring_next').val('<?php echo htmlspecialchars($next_data[1]);?>');">
                            <?php
                            break;
                        }else if($next == 0 && $next_data[0]==$finance_recurring_id){
                            $next = -1;
                        }
                    }
                }
                if((int)$finance_id>0){ ?>
                <input type="submit" name="butt_del" id="butt_del" value="<?php echo _l('Delete'); ?>" onclick="return confirm('<?php echo _l('Really delete this record?'); ?>');" class="submit_button delete_button" />
                <?php if(count($linked_finances) || count($linked_invoice_payments)){ ?>
                    <input type="submit" name="butt_unlink" value="<?php echo _l('Unlink'); ?>" class="submit_button" />
                    <?php } ?>
                <?php } ?>
                <input type="button" name="cancel" value="<?php echo _l('Cancel'); ?>" onclick="window.location.href='<?php echo $module->link('finance',array('finance_id'=>false)); ?>';" class="submit_button" />
            </td>
        </tr>

        </tbody>
    </table>




</form>
