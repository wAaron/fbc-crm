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
    $widgets = array();
    $week_count=1;
    $result = module_finance::get_dashboard_data();
    foreach($result as $r){
        extract($r);
        ob_start();
        ?>
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
        <?php

        $widgets[] = array(
            'id'=>'week_table_'.$week_count,
            'title' => $table_name,
            'icon' => 'piggy_bank',//todo - this is only in whitelable, maybe we move icons to a central point so all themes can use them?
            'content' => ob_get_clean(),
        );
        $week_count++;
    }

    // now do the line chart.
    ?>

<script type="text/javascript">

	/*----------------------------------------------------------------------*/
    /* Charts
    /*----------------------------------------------------------------------*/

    $(function(){
        $('.finance_chart').each(
            function(){$(this).wl_Chart({
                onClick: function(value, legend, label, id){
                    alert('Todo: show popup like other table with more info');
                    //$.msg("value is "+value+" from "+legend+" at "+label+" ("+id+")",{header:'Custom Callback'});
                },
                tooltipPattern: function (value, legend, label, id, itemobj) {
                    return legend + " in week of " + label + " was " + value;
                }
            });
        });
    });

</script>
    <?php

    // start content for inside widget.
    ob_start();

    $show_previous_weeks = module_config::c('dashboard_graph_previous_weeks',10);
    $show_coming_weeks = module_config::c('dashboard_graph_coming_weeks',7);
        $home_summary = array(
            array(
                "week_start" => date('Y-m-d', mktime(1, 0, 0, date('m'), date('d')-date('N')-(($show_previous_weeks)*7)+1, date('Y'))), // 7 weeks ago
                //"week_end" => date('Y-m-d', strtotime('-1 day',mktime(1, 0, 0, date('m'), date('d')+(6-date('N'))-(2*7)+2, date('Y')))), // 2 weeks ago
                "week_end" => date('Y-m-d', mktime(1, 0, 0, date('m'), date('d')+(6-date('N'))+2, date('Y'))), // today
                'table_name' => 'Finance Chart',
                'array_name' => 'finance_chart',
                'multiplyer' => 7,
                'col1' => 'week',
                'row_limit' => $show_previous_weeks,
            ),
        );

        foreach($home_summary as $home_sum){
            extract($home_sum); // hacky, better than old code tho.
            $data = module_finance::get_finance_summary($week_start,$week_end,$multiplyer,$row_limit);
            // return the bits that will be used in the output of the HTML table (and now in the calendar module output)
            $finance_data = array(
                'data' => $data,
                'table_name' => $table_name,
                'col1' => $col1,
            );
            //print_r($finance_data);
            ?>

<table class="finance_chart">
    <thead>
    <tr>
        <th></th>
        <?php foreach($finance_data['data'] as $week_name => $week_data){ ?>
            <th>
                <?php echo $week_data['week'];?>
            </th>
        <?php } ?>
    </tr>
    </thead>
    <tbody>
        <tr>
            <th><?php _e('Hours');?></th>
            <?php foreach($finance_data['data'] as $week_name => $week_data){ ?>
                <td><?php echo $week_data['chart_hours'];?></td>
            <?php } ?>
        </tr>
        <tr>
            <th><?php _e('Invoiced');?></th>
            <?php foreach($finance_data['data'] as $week_name => $week_data){ ?>
                <td><?php echo $week_data['chart_amount_invoiced'];?></td>
            <?php } ?>
        </tr>
        <tr>
            <th><?php _e('Income');?></th>
            <?php foreach($finance_data['data'] as $week_name => $week_data){ ?>
                <td><?php echo $week_data['chart_amount_paid'];?></td>
            <?php } ?>
        </tr>
        <?php if(module_finance::is_expense_enabled()){ ?>
        <tr>
            <th><?php _e('Expense');?></th>
            <?php foreach($finance_data['data'] as $week_name => $week_data){ ?>
                <td><?php echo $week_data['chart_amount_spent'];?></td>
            <?php } ?>
        </tr>
        <?php } ?>
        <?php if(class_exists('module_envato',false) && module_config::c('envato_include_in_dashbaord',1)){ ?>
        <tr>
            <th><?php _e('Envato');?></th>
            <?php foreach($finance_data['data'] as $week_name => $week_data){ ?>
                <td><?php echo $week_data['chart_envato_earnings'];?></td>
            <?php } ?>
        </tr>
        <?php } ?>
    </tbody>
    </table>
            <?php
        }
    ?>
    <?php
    $widgets[] = array(
        'id'=>'finance_chart',
        'title' => _l('Weekly Finance Chart'),
        'icon' => 'piggy_bank',
        'content' => ob_get_clean(),
    );


?>

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
