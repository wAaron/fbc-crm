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
$websites = module_website::get_websites($search);


// hack to add a "group" option to the pagination results.
if(class_exists('module_group',false)){
    module_group::enable_pagination_hook(
        // what fields do we pass to the group module from this customers?
        array(
            'fields'=>array(
                'owner_id' => 'website_id',
                'owner_table' => 'website',
                'name' => 'name',
                'email' => ''
            ),
        )
    );
}
if(class_exists('module_table_sort',false)){
    module_table_sort::enable_pagination_hook(
    // pass in the sortable options.
        array(
            'table_id' => 'website_list',
            'sortable'=>array(
                // these are the "ID" values of the <th> in our table.
                // we use jquery to add the up/down arrows after page loads.
                'website_name' => array(
                    'field' => 'name',
                    'current' => 1, // 1 asc, 2 desc
                ),
                'website_url' => array(
                    'field' => 'url',
                ),
                'website_customer' => array(
                    'field' => 'customer_name',
                ),
                'website_status' => array(
                    'field' => 'status',
                ),
                // special case for group sorting.
                'website_group' => array(
                    'group_sort' => true,
                    'owner_table' => 'website',
                    'owner_id' => 'website_id',
                ),
            ),
        )
    );
}
// hack to add a "export" option to the pagination results.
if(class_exists('module_import_export',false) && module_website::can_i('view','Export '.module_config::c('project_name_plural','Websites'))){
    module_import_export::enable_pagination_hook(
        // what fields do we pass to the import_export module from this customers?
        array(
            'name' => module_config::c('project_name_single','Website').' Export',
            'fields'=>array(
                module_config::c('project_name_single','Website').' ID' => 'website_id',
                'Customer Name' => 'customer_name',
                'Customer Contact First Name' => 'customer_contact_fname',
                'Customer Contact Last Name' => 'customer_contact_lname',
                'Customer Contact Email' => 'customer_contact_email',
                module_config::c('project_name_single','Website').' Name' => 'name',
                'URL' => 'url',
                module_config::c('project_name_single','Website').' Status' => 'status',
            ),
            // do we look for extra fields?
            'extra' => array(
                'owner_table' => 'website',
                'owner_id' => 'website_id',
            ),
        )
    );
}

?>

<h2>
    <?php if(module_website::can_i('create','Websites')){ ?>
	<span class="button">
		<?php echo create_link("Add New ".module_config::c('project_name_single','Website'),"add",module_website::link_open('new')); ?>
	</span>
    <?php } ?>
    <?php if(class_exists('module_import_export',false) && module_website::can_i('view','Import '.module_config::c('project_name_plural','Websites'))){
        $link = module_import_export::import_link(
            array(
                'callback'=>'module_website::handle_import',
                'callback_preview'=>'module_website::handle_import_row_debug',
                'name'=>module_config::c('project_name_plural','Websites'),
                'return_url'=>$_SERVER['REQUEST_URI'],
                'group'=>'website',
                'fields'=>array(
                    module_config::c('project_name_single','Website').' ID' => 'website_id',
                    'Customer Name' => 'customer_name',
                    'Customer Contact First Name' => 'customer_contact_fname',
                    'Customer Contact Last Name' => 'customer_contact_lname',
                    'Customer Contact Email' => 'customer_contact_email',
                    module_config::c('project_name_single','Website').' Name' => 'name',
                    'URL' => 'url',
                    module_config::c('project_name_single','Website').' Status' => 'status',
                ),
                // extra args to pass to our website import handling function.
                'options' => array(
                    'duplicates'=>array(
                        'label' => _l('Duplicates'),
                        'form_element' => array(
                            'name' => 'duplicates',
                            'type' => 'select',
                            'blank' => false,
                            'value' => 'ignore',
                            'options' => array(
                                'ignore'=>_l('Skip Duplicates'),
                                'overwrite'=>_l('Overwrite/Update Duplicates')
                            ),
                        ),
                    ),
                ),
                // do we attempt to import extra fields?
                'extra' => array(
                    'owner_table' => 'website',
                    'owner_id' => 'website_id',
                ),
            )
        );
        ?>
        <span class="button">
            <?php echo create_link("Import ".module_config::c('project_name_plural','Websites'),"add",$link); ?>
        </span>
        <?php
    } ?>
	<?php echo _l('Customer '.module_config::c('project_name_plural','Websites')); ?>
</h2>

<form action="#" method="post">


<table class="search_bar">
	<tr>
        <th><?php _e('Filter By:'); ?></th>
        <td class="search_title">
            <?php _e('Quotation Name');?>:
        </td>
        <td class="search_input">
            <input type="text" name="search[generic]" value="<?php echo isset($search['generic'])?htmlspecialchars($search['generic']):''; ?>" size="30">
        </td>
		<td class="search_title">
        <?php _e('Status:');?>
        </td>
        <td class="search_input">
        <?php echo print_select_box(module_website::get_statuses(),'search[status]',isset($search['status'])?$search['status']:''); ?>
        </td>
        <td class="search_action">
			<?php echo create_link("Reset","reset",module_website::link_open(false)); ?>
			<?php echo create_link("Search","submit"); ?>
		</td>
	</tr>
</table>

<?php
$pagination = process_pagination($websites);
$colspan = 4;
?>

<?php echo $pagination['summary'];?>

<table width="100%" border="0" cellspacing="0" cellpadding="2" class="tableclass tableclass_rows">
	<thead>
	<tr class="title">
		<th id="website_name"><?php echo _l('Quotation Name'); ?></th>
		
        <?php if(!isset($_REQUEST['customer_id'])){ ?>
		<th id="website_customer"><?php echo _l('Customer'); ?></th>
        <?php } ?>
        
		<th id="website_status"><?php echo _l('Task Type'); ?></th>
		<th id="website_status"><?php echo _l('Service Type'); ?></th>
		<th id="website_status"><?php echo _l('Service Price'); ?></th>
		<th id="website_status"><?php echo _l('Price Unit'); ?></th>
		
        <?php if(class_exists('module_group',false)){ ?>
        <th id="website_group"><?php echo _l('Group'); ?></th>
        <?php } ?>
        
        <?php if(class_exists('module_extra',false)){
        module_extra::print_table_header('website');
        } ?>
    </tr>
    </thead>
    <tbody>
		<?php
		$c=0;
		foreach($pagination['rows'] as $website){
            ?>
		<tr class="<?php echo ($c++%2)?"odd":"even"; ?>">
			<td class="row_action">
				<?php echo module_website::link_open($website['website_id'],true);?>
			</td>
            
            <?php if(!isset($_REQUEST['customer_id'])){ ?>
            <td>
                <?php echo module_customer::link_open($website['customer_id'],true);?>
            </td>
            <?php } ?>
            
            <td>
                {{task_types['<?php echo htmlspecialchars($website['task_type']);?>']}}
            </td>
            
            <td>
                {{service_types.<?php echo htmlspecialchars($website['task_type']);?>.<?php echo htmlspecialchars($website['service_type']);?>}}
            </td>
            
            <td>
                <?php echo dollar($website['service_price'],true,$website['currency_id']);?>
            </td>
            <td>
                {{price_units.<?php echo htmlspecialchars($website['task_type']);?>.<?php echo htmlspecialchars($website['price_unit']);?>}}
            </td>
            
            <?php if(class_exists('module_group',false)){ ?>
            <td><?php

                if(isset($website['group_sort_website'])){
                    echo htmlspecialchars($website['group_sort_website']);
                }else{
                    // find the groups for this website.
                    $groups = module_group::get_groups_search(array(
                        'owner_table' => 'website',
                        'owner_id' => $website['website_id'],
                    ));
                    $g=array();
                    foreach($groups as $group){
                        $g[] = $group['name'];
                    }
                    echo htmlspecialchars(implode(', ',$g));
                }
                ?></td>
                <?php } ?>
                <?php if(class_exists('module_extra',false)){
                module_extra::print_table_data('website',$website['website_id']);
                } ?>
		</tr>
		<?php } ?>
	</tbody>
</table>
    <?php echo $pagination['links'];?>
</form>