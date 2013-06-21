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


$website_id = (int)$_REQUEST['website_id'];
$website = module_website::get_website($website_id);


if($website_id>0 && $website['website_id']==$website_id){
    $module->page_title = module_config::c('project_name_single','Website') .': '.$website['name'];
}else{
    $module->page_title = module_config::c('project_name_single','Website') .': '._l('New');
}

if($website_id>0 && $website){
	if(class_exists('module_security',false)){
		module_security::check_page(array(
            'module' => $module->module_name,
            'feature' => 'edit',
		));
	}
}else{
	if(class_exists('module_security',false)){
		module_security::check_page(array(
            'module' => $module->module_name,
            'feature' => 'create',
		));
	}
	module_security::sanatise_data('website',$website);
}


?>


	
<form action="" method="post">
	<input type="hidden" name="_process" value="save_website" />
    <input type="hidden" name="website_id" value="<?php echo $website_id; ?>" />
    

    <?php

    $fields = array(
    'fields' => array(
        'name' => 'Name',
    ));
    module_form::set_required(
        $fields
    );
    module_form::prevent_exit(array(
        'valid_exits' => array(
            // selectors for the valid ways to exit this form.
            '.submit_button',
            '.form_save',
        ))
    );
    

    ?>

	<table cellpadding="10" width="100%">
		<tbody>
			<tr>
				<td valign="top" width="35%">
					<h3><?php echo _l(module_config::c('project_name_single','Website').' Details'); ?></h3>



					<table border="0" cellspacing="0" cellpadding="2" class="tableclass tableclass_form tableclass_full">
						<tbody>
							<tr>
								<th class="width1">
									<?php echo _l('Name'); ?>
								</th>
								<td>
									<input type="text" name="name" id="name" value="<?php echo htmlspecialchars($website['name']); ?>" />
								</td>
							</tr>
                            <?php if(module_config::c('project_display_url',1)){ ?>
							<tr>
								<th>
									<?php echo _l('URL'); ?>
								</th>
								<td>
									http://<input type="text" name="url" id="url" style="width: 200px;" value="<?php echo htmlspecialchars($website['url']); ?>" />
                                    <?php if($website['url']){ ?><a href="<?php echo module_website::urlify($website['url']);?>" target="_blank"><?php _e('open &raquo;');?></a><?php } ?>
								</td>
							</tr>
                            <?php } ?>
							<tr>
								<th>
									<?php echo _l('Status'); ?>
								</th>
								<td>
									<?php echo print_select_box(module_website::get_statuses(),'status',$website['status'],'',true,false,true); ?>
								</td>
							</tr>
						</tbody>
                        <?php
                         module_extra::display_extras(array(
                            'owner_table' => 'website',
                            'owner_key' => 'website_id',
                            'owner_id' => $website['website_id'],
                            'layout' => 'table_row',
                                 'allow_new' => module_website::can_i('create','Websites'),
                                 'allow_edit' => module_website::can_i('create','Websites'),
                            )
                        );
                        ?>
					</table>
                    <h3><?php echo _l('Advanced'); ?></h3>
                    <table border="0" cellspacing="0" cellpadding="2" class="tableclass tableclass_form tableclass_full">
						<tbody>

							<tr>
								<th class="width2">
									<?php echo _l('Change Customer'); ?>
								</th>
								<td>
                                    <?php
                                    $c = array();
                                    $res = module_customer::get_customers();
                                    foreach($res as $row){
                                        $c[$row['customer_id']] = $row['customer_name'];
                                    }
                                    if(count($res)<=1 && $website['customer_id']){
                                        if(isset($c[$website['customer_id']])){
                                            echo htmlspecialchars($c[$website['customer_id']]);
                                            ?>
                                            <input type="hidden" name="customer_id" value="<?php echo $website['customer_id'];?>">
                                            <?php
                                        }
                                    }else{
                                        echo print_select_box($c,'customer_id',$website['customer_id']);
                                        _h('Changing a customer will also change all the current linked jobs and invoices across to this new customer.');
                                    }
                                    ?>
                                </td>
							</tr>
						</tbody>
					</table>
                    <?php if((int)$website_id>0){
                        if(class_exists('module_group',false)){
                        module_group::display_groups(array(
                             'title' => module_config::c('project_name_single','Website').' Groups',
                            'owner_table' => 'website',
                            'owner_id' => $website_id,
                            'view_link' => module_website::link_open($website_id),

                         ));
                        }

                    // and a hook for our new change request plugin
                    hook_handle_callback('website_sidebar',$website_id);

                    } ?>

				</td>
                <td valign="top">
                    <?php
                    if($website_id && $website_id!='new'){
                        $note_summary_owners = array();
                        // generate a list of all possible notes we can display for this website.
                        // display all the notes which are owned by all the sites we have access to

                        module_note::display_notes(array(
                            'title' => module_config::c('project_name_single','Website').' Notes',
                            'owner_table' => 'website',
                            'owner_id' => $website_id,
                            'view_link' => module_website::link_open($website_id),
                            )
                        );

                        // show the jobs linked to this website.
                        $h = array(
                            'type'=>'h3',
                            'title'=>module_config::c('project_name_single','Website').' Jobs',
                        );
                        if(module_job::can_i('create','Jobs')){
                            $h['button']=array(
                                'title'=>'New Job',
                                'url' =>module_job::link_generate('new',array('arguments'=>array(
                                    'website_id' => $website_id,
                                ))),
                            );
                        }

                        print_heading($h);

                        $c=0;
                        ?>
                         <div class="content_box_wheader">
                        <table border="0" cellspacing="0" cellpadding="2" class="tableclass tableclass_rows tableclass_full">
                            <thead>
                            <tr>
                                <th>
                                    <?php _e('Job Title'); ?>
                                </th>
                                <th>
                                    <?php _e('Date'); ?>
                                </th>
                                <th>
                                    <?php _e('Due Date'); ?>
                                </th>
                                <th>
                                    <?php _e('Complete'); ?>
                                </th>
                                <?php if(module_invoice::can_i('view','Invoices')){ ?>
                                <th>
                                    <?php _e('Amount'); ?>
                                </th>
                                <th>
                                    <?php _e('Invoice'); ?>
                                </th>
                                <?php } ?>
                            </tr>
                            </thead>
                            <tbody>
                                <?php foreach(module_job::get_jobs(array('website_id'=>$website_id)) as $job){
                                    $job = module_job::get_job($job['job_id']);
                                    ?>
                                    <tr class="<?php echo ($c++%2)?"odd":"even"; ?>">
                                        <td class="row_action">
                                            <?php echo module_job::link_open($job['job_id'],true);?>
                                        </td>
                                        <td>
                                            <?php
                                            echo print_date($job['date_start']);
                                                //is there a renewal date?
                                                if(isset($job['date_renew']) && $job['date_renew'] && $job['date_renew'] != '0000-00-00'){
                                                    _e(' to %s',print_date(strtotime("-1 day",strtotime($job['date_renew']))));
                                                }
                                            ?>
                                        </td>
                                        <td>
                                            <?php
                                            if($job['total_percent_complete']!=1 && strtotime($job['date_due']) < time()){
                                                echo '<span class="error_text">';
                                                echo print_date($job['date_due']);
                                                echo '</span>';
                                            }else{
                                                echo print_date($job['date_due']);
                                            }
                                            ?>
                                        </td>
                                        <td>
                                            <span class="<?php
                                                echo $job['total_percent_complete'] >= 1 ? 'success_text' : '';
                                                ?>">
                                                <?php echo ($job['total_percent_complete']*100).'%';?>
                                            </span>
                                        </td>
                                        <?php if(module_invoice::can_i('view','Invoices')){ ?>
                                        <td>
                                            <span class="currency">
                                            <?php echo dollar($job['total_amount'],true,$job['currency_id']);?>
                                            </span>
                                            <?php if($job['total_amount_invoiced'] > 0 && $job['total_amount'] != $job['total_amount_invoiced']){ ?>
                                                <br/>
                                                <span class="currency">(<?php echo dollar($job['total_amount_invoiced'],true,$job['currency_id']);?>)</span>
                                            <?php } ?>
                                        </td>
                                        <td>
                                            <?php
                                            foreach(module_invoice::get_invoices(array('job_id'=>$job['job_id'])) as $invoice){
                                                $invoice = module_invoice::get_invoice($invoice['invoice_id']);
                                                echo module_invoice::link_open($invoice['invoice_id'],true);
                                                echo " ";
                                                echo '<span class="';
                                                if($invoice['total_amount_due']>0){
                                                    echo 'error_text';
                                                }else{
                                                    echo 'success_text';
                                                }
                                                echo '">';
                                                if($invoice['total_amount_due']>0){
                                                    echo dollar($invoice['total_amount_due'],true,$invoice['currency_id']);
                                                    echo ' '._l('due');
                                                }else{
                                                    echo _l('%s paid',dollar($invoice['total_amount'],true,$invoice['currency_id']));
                                                }
                                                echo '</span>';
                                                echo "<br>";
                                            }  ?>
                                        </td>
                                        <?php } ?>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                        </div>
                        <?php


                        // and a hook for our new change request plugin
                        hook_handle_callback('website_main',$website_id);
                    }
                    ?>

                </td>
			</tr>
			<tr>
				<td align="center" colspan="2">
					<input type="submit" name="butt_save" id="butt_save" value="<?php echo _l('Save '.module_config::c('project_name_single','Website')); ?>" class="submit_button save_button" />
					<?php if((int)$website_id && module_website::can_i('delete','Websites')){ ?>
					<input type="submit" name="butt_del" id="butt_del" value="<?php echo _l('Delete'); ?>" class="submit_button delete_button" />
					<?php } ?>
					<input type="button" name="cancel" value="<?php echo _l('Cancel'); ?>" onclick="window.location.href='<?php echo module_website::link_open(false); ?>';" class="submit_button" />
				</td>
			</tr>
		</tbody>
	</table>


</form>
