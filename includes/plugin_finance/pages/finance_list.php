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
$recent_transactions = module_finance::get_finances($search);

$total_debit = $total_credit = 0;
foreach($recent_transactions as $recent_transaction){
    $total_credit += $recent_transaction['credit'];
    $total_debit += $recent_transaction['debit'];
}


if(!module_finance::can_i('view','Finance')){
    redirect_browser(_BASE_HREF);
}



if(class_exists('module_table_sort',false)){
    module_table_sort::enable_pagination_hook(
    // pass in the sortable options.
    /*="sort_date"><?php echo _l('Date'); ?></th>
                    <th id="sort_name"><?php echo _l('Name'); ?></th>
                    <th><?php echo _l('Description'); ?></th>
                    <th id="sort_credit"><?php echo _l('Credit'); ?></th>
                    <th id="sort_debit"><?php echo _l('Debit'); ?></th>
                    <th id="sort_account"><?p*/
        array(
            'table_id' => 'finance_list',
            'sortable'=>array(
                // these are the "ID" values of the <th> in our table.
                // we use jquery to add the up/down arrows after page loads.
                'sort_date' => array(
                    'field' => 'transaction_date',
                    'current' => 2, // 1 asc, 2 desc
                ),
                'sort_name' => array(
                    'field' => 'name',
                ),
                'sort_credit' => array(
                    'field' => 'credit',
                ),
                'sort_debit' => array(
                    'field' => 'debit',
                ),
            ),
        )
    );
}


// hack to add a "export" option to the pagination results.
if(class_exists('module_import_export',false) && module_finance::can_i('view','Export Finance')){
    module_import_export::enable_pagination_hook(
    // what fields do we pass to the import_export module from this customers?
        array(
            'name' => 'Finance Export',
            'parent_form' => 'finance_form',
            'fields'=>array(
                'Date' => 'transaction_date',
                'Name' => 'name',
                'URL' => 'url',
                'Description' => 'description',
                'Credit' => 'credit',
                'Debit' => 'debit',
                'Account' => 'account_name',
                'Categories' => 'categories',
            ),
        )
    );
}

$recent_transactions_pagination = process_pagination($recent_transactions);

$upcoming_finances = array();

?>




            <h2>
            <?php if(module_finance::can_i('create','Finance')){ ?>
                <span class="button">
                    <?php echo create_link("Add New","add",module_finance::link_open('new')); ?>
                </span>
            <?php } ?>
                <?php echo _l('Financial Transactions'); ?>
            </h2>


            <form action="" method="post" id="finance_form">

            <table class="search_bar">
                <tr>
                    <th><?php _e('Filter By:'); ?></th>
                    <td class="search_title">
                        <?php _e('Name/Description:');?>
                    </td>
                    <td class="search_input">
                        <input type="text" name="search[generic]" value="<?php echo isset($search['generic'])?htmlspecialchars($search['generic']):''; ?>" size="20">
                    </td>
                    <td class="search_title">
                        <?php echo _l('Date:');?>
                    </td>
                    <td class="search_input">
                        <input type="text" name="search[date_from]" value="<?php echo isset($search['date_from'])?htmlspecialchars($search['date_from']):''; ?>" class="date_field">
                        <?php _e('to');?>
                        <input type="text" name="search[date_to]" value="<?php echo isset($search['date_to'])?htmlspecialchars($search['date_to']):''; ?>" class="date_field">
                    </td>
                    <td class="search_title">
                        <?php _e('Account:');?>
                    </td>
                    <td class="search_input">
                        <?php echo print_select_box(module_finance::get_accounts(),'search[finance_account_id]',isset($search['finance_account_id'])?$search['finance_account_id']:'','',true,'name'); ?>
                    </td>
                    <td class="search_title">
                        <?php _e('Category:');?>
                    </td>
                    <td class="search_input">
                        <?php echo print_select_box(module_finance::get_categories(),'search[finance_category_id]',isset($search['finance_category_id'])?$search['finance_category_id']:'','',true,'name'); ?>
                    </td>
                    <td class="search_action">
                        <?php echo create_link("Reset","reset",module_finance::link_open(false)); ?>
                        <?php echo create_link("Search","submit"); ?>
                    </td>
                </tr>
            </table>
            </form>

            <script type="text/javascript">
                function link_it(t){
                    // select all others of this same credit/debit price
                    $('.link_box').show();
                    $('.link_box').each(function(){
                        if(t && $(this).val() != t){
                            $(this).hide();
                        }
                    });
                }
                $(function(){
                    $('.link_box').each(function(){
                        $(this).change(function(){
                            link_it( $(this)[0].checked ? $(this).val() : false );
                        });
                        $(this).mouseup(function(){
                            link_it( $(this)[0].checked ? $(this).val() : false );
                        });
                    });
                });
            </script>

            <?php echo $recent_transactions_pagination['summary'];?>

<form action="" method="post" id="quick_add_form">
    <input type="hidden" name="_process" value="quick_save_finance">
    <input type="hidden" name="finance_id" value="new">
            <?php module_form::set_default_field('new_transaction_name'); ?>

            <table width="100%" border="0" cellspacing="0" cellpadding="2" class="tableclass tableclass_rows">
                <thead>
                <tr class="title">
                    <th id="sort_date"><?php echo _l('Date'); ?></th>
                    <th id="sort_name"><?php echo _l('Name'); ?></th>
                    <th><?php echo _l('Description'); ?></th>
                    <th id="sort_credit"><?php echo _l('Credit'); ?></th>
                    <th id="sort_debit"><?php echo _l('Debit'); ?></th>
                    <th id="sort_account"><?php echo _l('Account'); ?></th>
                    <th width="90"><?php echo _l('Categories'); ?></th>
                    <th> </th>
                     <?php if(class_exists('module_extra',false)){
                    module_extra::print_table_header('finance');
                    } ?>
                </tr>
                </thead>
                <?php if(module_finance::can_i('create','Finance')){ ?>
                <tbody>
                <tr>
                    <td>
                        <input type="text" name="transaction_date" class="date_field" value="<?php echo print_date(time());?>">
                    </td>
                    <td>
                        <input type="text" name="name" id="new_transaction_name">
                    </td>
                    <td>
                        <input type="text" name="description" style="width:95%">
                    </td>
                    <td class="success_text">
                        <?php echo currency('');?><input type="text" name="credit" class="currency">
                    </td>
                    <td class="error_text">
                        <?php echo currency('');?><input type="text" name="debit" class="currency">
                    </td>
                    <td>
                        <?php echo print_select_box(module_finance::get_accounts(),'finance_account_id','','',true,'name',true); ?>
                    </td>
                    <td>
                        <div style="height:18px; width:89px; overflow: hidden; position: absolute; background: #FFFFFF;" onmouseover="$(this).height('auto');$(this).width('auto');" onmouseout="$(this).height('18px');$(this).width('89px');">
                            <?php
                            $categories = module_finance::get_categories();
                            foreach($categories as $category){ ?>
                                <input type="checkbox" name="finance_category_id[]" value="<?php echo $category['finance_category_id'];?>" id="category_<?php echo $category['finance_category_id'];?>" <?php echo isset($finance['category_ids'][$category['finance_category_id']]) ? ' checked' : '';?>>
                                <label for="category_<?php echo $category['finance_category_id'];?>"><?php echo htmlspecialchars($category['name']);?></label> <br/>
                                <?php }
                            ?>
                            <input type="checkbox" name="finance_category_new_checked" value="new">
                            <input type="text" name="finance_category_new" value="">
                        </div> &nbsp;
                    </td>
                    <td>
                        <input type="submit" name="addnew" value="<?php _e('Quick Add');?>" class="small_button">
                    </td>
                </tr>
                </tbody>
                <?php } ?>
                <tbody>
                <?php
                $c=0;
                $displayed_finance_ids = array(); // keep track of which parent / child finance ids have been displayed.
                $displayed_invoice_payment_ids = array(); // keep track of which parent / child invoice_payment ids have been displayed.

                foreach($recent_transactions_pagination['rows'] as $finance){

                    $c++;

                    $link_rowspan = 1;
                    $description_rowspan = 1;
                    $shared_description = $finance['description'];
                    if(isset($finance['finance_id']) && $finance['finance_id']){
                        $finance_record = module_finance::get_finance($finance['finance_id']);
                        if(count($finance_record['linked_invoice_payments'])){
                            $link_rowspan += count($finance_record['linked_invoice_payments']);
                        }
                        if(count($finance_record['linked_finances'])){
                            $link_rowspan += count($finance_record['linked_finances']);
                        }
                        // a little hack to find if we use a shared description.
                        if($link_rowspan>1 && count($finance_record['linked_invoice_payments'])){
                            foreach($finance_record['linked_invoice_payments'] as $this_finance_record){
                                if(strlen(trim($shared_description)) && strlen(trim(strip_tags($this_finance_record['description']))) > 0 && trim(strip_tags($shared_description)) != trim(strip_tags($this_finance_record['description']))){
                                    $description_rowspan = 1;
                                    $shared_description = $finance['description'];
                                    break;
                                }else{
                                    $description_rowspan++;
                                    $shared_description = $this_finance_record['description'];
                                }
                            }
                            if($description_rowspan>1){
                                foreach($finance_record['linked_finances'] as $this_finance_record){
                                    if(strlen(trim($shared_description)) && strlen(trim(strip_tags($this_finance_record['description']))) > 0 && trim(strip_tags($shared_description)) != trim(strip_tags($this_finance_record['description']))){
                                        $description_rowspan = 1;
                                        $shared_description = $finance['description'];
                                        break;
                                    }else{
                                        $description_rowspan++;
                                    }
                                }
                            }
                        }
                    }
                    for($x=0;$x<$link_rowspan;$x++){
                        if($x>0){
                            if(count($finance_record['linked_finances'])){
                                $finance = array_shift($finance_record['linked_finances']);
                            }else if(count($finance_record['linked_invoice_payments'])){
                                $finance = array_shift($finance_record['linked_invoice_payments']);
                            }
                        }
                        ?>
                        <tr class="<?php echo ($c%2)?"odd":"even"; ?>">
                            <?php if($link_rowspan > 1){
                                ?>
                                <td>
                                    <?php
                                    if($x==0){
                                        // loop over all finance records and print the dates
                                        // only print dates if they differ from the others.
                                        $dates = array();
                                        $dates[print_date($finance['transaction_date'])]=true;
                                        foreach($finance_record['linked_finances'] as $f){
                                            $dates[print_date($f['transaction_date'])]=true;
                                        }
                                        foreach($finance_record['linked_invoice_payments'] as $f){
                                            $dates[print_date($f['transaction_date'])]=true;
                                        }
                                        echo implode(', ',array_keys($dates));
                                    }
                                    ?>
                                </td>
                                <?php
                            }else{
                                // just display the normal date:
                                ?>
                                <td>
                                    <?php echo print_date($finance['transaction_date']); ?>
                                </td>
                            <?php } ?>
                            <td>
                                <?php if($x>0 && isset($finance['invoice_id'])){
                                    // skip this link as it will promt to create a new entry
                                }else{ ?>
                                    <a href="<?php echo $finance['url'];?>"><?php echo !trim($finance['name']) ? 'N/A' :    htmlspecialchars($finance['name']);?></a>
                                <?php } ?>
                            </td>
                            <?php if($description_rowspan>1){ ?>
                                <?php if($x==0){ ?>
                                    <td rowspan="<?php echo $description_rowspan;?>">
                                        <?php echo $shared_description; ?>
                                    </td>
                                <?php } ?>
                            <?php }else{ ?>
                                <td>
                                    <?php echo $finance['description']; ?>
                                </td>
                            <?php } ?>
                            <?php if($x==0){ ?>
                            <td rowspan="<?php echo $link_rowspan;?>">
                                <span class="success_text"><?php echo $finance['credit'] > 0 ? '+'.dollar($finance['credit'],true,$finance['currency_id']) : '';?></span>
                            </td>
                            <td rowspan="<?php echo $link_rowspan;?>">
                                <span class="error_text"><?php echo $finance['debit'] > 0 ? '-'.dollar($finance['debit'],true,$finance['currency_id']) : '';?></span>
                            </td>
                            <?php } ?>
                            <td>
                                <?php echo htmlspecialchars($finance['account_name']);?>
                            </td>
                            <td>
                                <?php echo $finance['categories'];?>
                            </td>
                            <?php if($x==0){ ?>
                            <td align="center" rowspan="<?php echo $link_rowspan;?>">
                                <?php
                                // do we have any links at the moment??
                                /*if($finance['link_count'] > 0){
                                    echo $finance['link_count'];
                                }*/
                                ?>

                                <?php if(isset($finance['invoice_payment_id']) && $finance['invoice_payment_id'] && isset($finance['invoice_id']) && $finance['invoice_id']){ ?>
                                    <input type="checkbox" name="link_invoice_payment_ids[<?php echo $finance['invoice_payment_id'];?>]" value="<?php echo number_format($finance['credit'],2).'_'.number_format($finance['debit'],2);?>" class="link_box">
                                <?php }else{ ?>
                                    <input type="checkbox" name="link_finance_ids[<?php echo $finance['finance_id'];?>]" value="<?php echo number_format($finance['credit'],2).'_'.number_format($finance['debit'],2);?>" class="link_box">
                                <?php } ?>
                            </td>
                            <?php } ?>
                            <?php if(class_exists('module_extra',false)){
                            module_extra::print_table_data('finance',isset($finance['finance_id']) ? $finance['finance_id'] : 0);
                            } ?>
                        </tr>
                    <?php } ?>
                <?php } ?>
              </tbody>
                <tfoot>
                <tr>
                    <td colspan="3" align="right">
                        <?php /*_e('Total for all search results:'); */?>
                    </td>
                    <td>
                        <!--<strong><?php /*echo dollar($total_credit);*/?></strong>-->
                    </td>
                    <td>
                        <!--<strong><?php /*echo dollar($total_debit);*/?></strong>-->
                    </td>
                    <td colspan="2">

                    </td>
                    <td align="center">
                        <input type="button" name="link" value="<?php echo _l('Link');?>" class="small_button" onclick="$('#link_go').val('go'); $('#quick_add_form')[0].submit();">
                        <input type="hidden" name="link_go" value="0" id="link_go">
                        <?php _h('Combine transactions together. eg: an invoice payment history with corresponding bank statement transaction. Transactions need to be the same dollar amount to link successfully.');?>
                    </td>
                </tr>
                </tfoot>
            </table>

</form>
                <?php echo $recent_transactions_pagination['links'];?>


<p>
    <?php echo _l('Totals for all %s search results: %s Credit, %s Debit',count($recent_transactions),dollar($total_credit),dollar($total_debit)); ?>
</p>