<?php 
/** 
  * Copyright: dtbaker 2012
  * Licence: Please check CodeCanyon.net for licence details. 
  * More licence clarification available here:  http://codecanyon.net/wiki/support/legal-terms/licensing-terms/ 
  * Deploy: 3053 c28b7e0e323fd2039bb168d857c941ee
  * Envato: 6b31bbe6-ead4-44a3-96e1-d5479d29505b
  * Package Date: 2013-02-27 19:09:56 
  * IP Address: 
  */ if ( module_config::c('dashboard_income_summary',1) && $this->can_i('view','Dashboard Finance Summary')) { ?>

    <?php
    // todo: work out what data this current customer can view.
    $result = module_finance::get_dashboard_data();
    ?>

<table class="tableclass tableclass_full">
<tbody>
<tr>
    <?php
    foreach($result as $r){
        extract($r);
        ?>

        <td width="33%" valign="top">
            <?php print_heading(array('title'=>$table_name,'type'=>'h3'));?>
            <div class="content_box_wheader">

            <table class="tableclass tbl_fixed tableclass_rows finance_summary" width="100%">
                <thead>
                <tr>
                    <th width="10%" class=""> <?php _e(ucwords($col1));?> </th>
                    <th width="14%" class=""> <?php _e('Hours');?> </th>
                    <th width="10%" class=""> <?php _e('Invoiced');?> </th>
                    <th width="10%" class=""> <?php _e('Income');?> </th>
                    <?php if(module_finance::is_expense_enabled()){ ?>
                        <th width="10%" class=""> <?php _e('Expense');?> </th>
                    <?php } ?>
                    <?php if(class_exists('module_envato',false) && module_config::c('envato_include_in_dashbaord',1)){ ?>
                        <th width="10%" class=""> <?php _e('Envato');?> </th>
                    <?php } ?>
                </tr>
                </thead>
                <tbody>
                <?php
                $c = 0;
                foreach($data as $key => $row){
                    ?>
                    <tr class="<?php
                        echo $c++%2 ? 'odd' : 'even';
                        if(isset($row['highlight'])){
                            echo ' highlight';
                        }
                        ?>">
                        <td><?php echo $row[$col1]; ?></td>
                        <td><?php echo (isset($row['hours_link'])) ? $row['hours_link'] : $row['hours'];?></td>
                        <td><?php echo (isset($row['amount_invoiced_link'])) ? $row['amount_invoiced_link'] : $row['amount_invoiced'];?></td>
                        <td><?php echo (isset($row['amount_paid_link'])) ? $row['amount_paid_link'] : $row['amount_paid'];?></td>
                        <?php if(module_finance::is_expense_enabled()){ ?>
                            <td><?php echo (isset($row['amount_spent_link'])) ? $row['amount_spent_link'] : $row['amount_spent'];?></td>
                        <?php } ?>
                        <?php if(class_exists('module_envato',false) && module_config::c('envato_include_in_dashbaord',1)){ ?>
                        <td><?php echo (isset($row['envato_earnings_link'])) ? $row['envato_earnings_link'] : $row['envato_earnings'];?></td>
                        <?php } ?> 
                    </tr>
                <?php } ?>
                </tbody>

            </table>
            </div>

        </td>
        <?php
    }

?>

</tbody>
</table>

<?php if(get_display_mode()!='mobile'){ ?>
<script type="text/javascript">
$(function() {
    $("#summary-form").dialog({
        autoOpen: false,
        height: 550,
        width: 750,
        modal: true,
        buttons: {
            '<?php _e('Close');?>': function() {
                $(this).dialog('close');
            }
        },
        close: function() {
            // reset contents
            $(this).html('');
        }
    });

    $('.summary_popup')
        .click(function() {
            $('#summary-form')
                    .load($(this).attr('href'))
                    .dialog('open');
            return false;
        });

});
</script>
<div id="summary-form" class="dialog-form"></div>
<?php } ?>
<?php } ?>
