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

$options = isset($_REQUEST['options']) ? unserialize(base64_decode($_REQUEST['options'])) : array();

$file_id = (int)$_REQUEST['file_id'];
$file = module_file::get_file($file_id);
if($file_id>0 && $file && $file['file_id']==$file_id){
	if(class_exists('module_security',false)){

        if(class_exists('module_job',false) && class_exists('module_customer',false)){
            // check if we can access this customer
            // OR if we can access this job.
            $customer_access = module_customer::get_customer($file['customer_id']);
            $job_access = module_job::get_job($file['job_id']);
            // hack - support non existant customer or job.
            if(
                ($customer_access && ($customer_access['customer_id'] || $customer_access['customer_id'] == $file['customer_id'])) ||
                ($job_access && $job_access['job_id'] == $file['job_id'])
            ){
                // success! we can view this file.
            }else{
                die('Failed to access file. No permissions to view this customer ID '.$file['customer_id'].' or job ID '.$job_access['job_id']);
            }
        }


        //if(!module_security::can_access_data('file',$file,$file_id)){
            //echo 'Permission denied to access data linked to customer id '.$file['customer_id'].'. Please contact your administrator.';
            //exit;
        //}

        module_security::check_page(array(
			'module' => $module->module_name,
            'feature' => 'Edit',
		));

	}
}else{
    //$file = array();
	if(class_exists('module_security',false)){
		module_security::check_page(array(
			'module' => $module->module_name,
            'feature' => 'Create',
		));
	}
	module_security::sanatise_data('file',$file);
}


if($file_id>0 && $file['file_id']==$file_id){
    $module->page_title = _l('File: %s',$file['file_name']);

    // close off any notifications here.
    $sql = "UPDATE `"._DB_PREFIX."file_notification` SET `view_time` = '".time()."' WHERE `view_time` = 0 AND `user_id` = ".module_security::get_loggedin_id()." AND file_id = ".(int)$file_id;
    query($sql);

}else{
    $module->page_title = _l('File: %s',_l('New'));
}


if(!isset($file['customer_id'])||!$file['customer_id'])$file['customer_id']=false; // helps with drop downs below.


?>


	
<form action="" method="post" enctype="multipart/form-data">
	<input type="hidden" name="_process" value="save_file" class="no_permissions" />
    <input type="hidden" name="file_id" value="<?php echo $file_id; ?>" class="no_permissions" />
    <input type="hidden" name="options" value="<?php echo base64_encode(serialize($options)); ?>" class="no_permissions" />


    <?php

    $fields = array(
    'fields' => array(
        'url' => 'Name',
    ));
    module_form::set_required(
        $fields
    );
    module_form::prevent_exit(array(
        'valid_exits' => array(
            // selectors for the valid ways to exit this form.
            '.submit_button',
        ))
    );

    $type = (isset($file['file_url']) && $file['file_url']) ? 'remote' : 'upload';
    if(isset($_REQUEST['file_type']))$type = $_REQUEST['file_type'];

    ?>

	<table cellpadding="10" width="100%">
		<tbody>
			<tr>
				<td valign="top" width="50%">
                    <?php
                    print_heading(array(
                        'type'=>'h3',
                        'title'=>'File Details',
                        'button'=>array(
                            'url'=>module_file::link_open($file_id).'&file_type='.($type=='upload'?'remote':'upload'),
                            'title'=>($type=='upload'?'Swap to URL':'Swap to Upload'),
                        )
                    ));
                    ?>



					<table border="0" cellspacing="0" cellpadding="2" class="tableclass tableclass_form tableclass_full">
						<tbody>
                            <?php if( $type == 'upload' ){ ?>
							<tr>
								<th class="width1">
									<?php echo _l('Upload File'); ?>
								</th>
								<td>
									<input type="file" name="file_upload">
                                    <?php if($file_id){ ?>
                                    <a href="<?php echo $module->link('file_edit',array('_process'=>'download','file_id'=>$file['file_id']),'file',false);?>"><?php echo nl2br(htmlspecialchars($file['file_name']));?></a>
                                    <?php } ?>
								</td>
							</tr>
                            <?php }else{ ?>
							<tr>
								<th class="width1">
									<?php echo _l('File Name'); ?>
								</th>
								<td>
									<input type="text" name="file_name" value="<?php echo htmlspecialchars($file['file_name']);?>">
								</td>
							</tr>
							<tr>
								<th class="width1">
									<?php echo _l('File URL'); ?>
								</th>
								<td>
                                    <?php if(module_file::can_i('edit','Files')){ ?>
									<input type="text" name="file_url" value="<?php echo htmlspecialchars($file['file_url']);?>">
                                        <?php if($file['file_url']){ ?>
                                        <a href="<?php echo htmlspecialchars($file['file_url']);?>" target="_blank"><?php _e('Open');?></a>
                                        <?php } ?>
                                    <?php _h('You can enter a full URL to a file here (eg: http://yoursite.com/file.txt) and that link will become available through this system.'); ?>
                                    <?php }else{ ?>
                                        <a href="<?php echo htmlspecialchars($file['file_url']);?>" target="_blank"><?php echo htmlspecialchars($file['file_url']);?></a>
                                    <?php } ?>
								</td>
							</tr>
                            <?php } ?>
							<tr>
								<th>
									<?php echo _l('Status'); ?>
								</th>
								<td>
									<?php echo print_select_box(module_file::get_statuses(),'status',$file['status'],'',true,false,true); ?>
								</td>
							</tr>
							<tr>
								<th>
									<?php echo _l('Customer'); ?>
								</th>
								<td>
                                    <?php
                                    $c = array();
                                    $res = module_customer::get_customers();
                                    foreach($res as $row){
                                        $c[$row['customer_id']] = $row['customer_name'];
                                    }
                                    if($file['customer_id'] && !isset($c[$file['customer_id']])){
                                        // this file is related to another job. from another customer.
                                        $related_customer = module_customer::get_customer($file['customer_id'],true);
                                        $c[$file['customer_id']] = $related_customer['customer_name'];
                                    }
                                    echo print_select_box($c,'customer_id',$file['customer_id'],'',false);
                                    ?>
								</td>
							</tr>
                        <?php if (class_exists('module_job',false)){ ?>
							<tr>
								<th>
									<?php echo _l('Job'); ?>
								</th>
								<td>
                                    <?php
                                    $c = array();
                                    $res = module_job::get_jobs(array('customer_id'=>$file['customer_id']));
                                    foreach($res as $row){
                                        $c[$row['job_id']] = $row['name'];
                                    }
                                    if($file['job_id'] && !isset($c[$file['job_id']])){
                                        // this file is related to another job. from another customer.
                                        $related_job = module_job::get_job($file['job_id'],false,true);
                                        if($related_job && $related_job['job_id'] == $file['job_id']){
                                            $related_customer = module_customer::get_customer($related_job['customer_id'],true);
                                            if($related_customer && $related_customer['customer_id'] == $related_job['customer_id']){
                                                $c[$file['job_id']] = _l('%s (from %s)',$related_job['name'],$related_customer['customer_name']);
                                            }else{
                                                $file['job_id'] = false;
                                            }
                                        }else{
                                            $file['job_id'] = false;
                                        }
                                    }
                                    echo print_select_box($c,'job_id',$file['job_id']);
                                    if($file['job_id']){
                                        echo ' ';
                                        echo '<a href="'.module_job::link_open($file['job_id'],false).'">'._l('Open Job &raquo;').'</a>';
                                    }
                                    ?>
								</td>
							</tr>
                        <?php } ?>
						</tbody>
                        <?php
                         module_extra::display_extras(array(
                            'owner_table' => 'file',
                            'owner_key' => 'file_id',
                            'owner_id' => $file['file_id'],
                            'layout' => 'table_row',
                            )
                        );
                        ?>
					</table>
				</td>
				<td valign="top" width="50%">

					<h3><?php echo _l('File Description'); ?></h3>

					<table border="0" cellspacing="0" cellpadding="2" class="tableclass tableclass_form tableclass_full">
						<tbody>
							<tr>
								<td>
									<textarea name="description" rows="4" cols="50" style="width:100%;"><?php echo htmlspecialchars($file['description']);?></textarea>
								</td>
							</tr>
						</tbody>
					</table>
				</td>
			</tr>
			<tr>
				<td align="center" colspan="2">
					<input type="submit" name="butt_save" id="butt_save" value="<?php echo _l('Save file'); ?>" class="submit_button save_button" />
					<?php if((int)$file_id && module_file::can_i('delete','Files')){ ?>
					<input type="submit" name="butt_del" id="butt_del" value="<?php echo _l('Delete'); ?>" class="submit_button delete_button" />
					<?php } ?>
					<input type="button" name="cancel" value="<?php echo _l('Cancel'); ?>" onclick="window.location.href='<?php echo module_file::link_open(false); ?>';" class="submit_button" />
				</td>
			</tr>
		</tbody>
	</table>

    <?php
    if((int)$file_id > 0 && $file['file_path'] && is_file($file['file_path']) && module_file::can_i('view','File Comments')){ ?>
    <h2><?php echo _l('File Comments'); ?></h2>

                <style type="text/css">
                    .pointer-ids{
                        background-color:#EFEFEF;
                        border:1px solid #CCC;
                        cursor:pointer;
                    }
                </style>
                <script language="javascript">
                    var pointers = new Array();
                    var pointer_id;
                    <?php
                    $pointers = explode('|',$file['pointers']);
                    if(!is_array($pointers)){
                        $pointers=array();
                    }
                    $pointer_id=0;
                    foreach($pointers as $pointer){
                        if(!trim($pointer))continue;
                        $p = explode(",",$pointer);
                        $pointer_id = max($p[0],$pointer_id);
                        ?>
                        pointers.push({
                            id:<?php echo $p[0]; ?>,
                            x:<?php echo $p[1]; ?>,
                            y:<?php echo $p[2]; ?>,
                            printed:false
                        });
                        <?php
                    }
                    ?>
                    pointer_id = <?php echo $pointer_id; ?>;
                    var add_pointer=true;
                    function print_pointers(){
                        var pointer_save = '';
                        for(var i in pointers){
                            pointer_save += pointers[i].id + ',' + pointers[i].x + ',' + pointers[i].y + '|';
                            if(pointers[i].printed)continue;
                            pointers[i].dom = $('#pointer_template').clone(true).attr('id','').show();
                            $('.pointer_id',pointers[i].dom).html(pointers[i].id);
                            $(pointers[i].dom).css('marginTop',pointers[i].y+'px');
                            $(pointers[i].dom).css('marginLeft',pointers[i].x+'px');
                            $(pointers[i].dom).attr('node_id',pointers[i].id);
                            $(pointers[i].dom).data('id',i);
                            $(pointers[i].dom).attr('class','pointer-ids pointer-id-'+pointers[i].id);
                            $(pointers[i].dom).click(function(){
                                add_pointer=false;
                                var id = $(this).data('id');
                                var node_id = $(this).attr('node_id');
                                if(confirm('Really remove pointer '+node_id+'?')){
                                    $(pointers[id].dom).remove();
                                    delete(pointers[id]);
                                    print_pointers();
                                }
                            });
                            $('#pointer_holder').prepend(pointers[i].dom);
                            pointers[i].printed=true;
                            pointer_hover_node(pointers[i].dom);
                        }
                        $('#pointer_save').val(pointer_save);
                    }
                    function pointer_hover_node(node){
                        $(node).hover(
                            function(){
                                var id = $(node).attr('node_id');
                                $('.pointer-id-'+id).css('backgroundColor','#FFC707');
                            },
                            function(){
                                var id = $(node).attr('node_id');
                                $('.pointer-id-'+id).css('backgroundColor','#EFEFEF');
                            }
                        );
                    }
                    $(document).ready(function(){
                        <?php if(module_file::can_i('create','File Comments')){ ?>
                        $("#file_preview").click(function(e){
                            if(!add_pointer){
                                add_pointer=true;
                                return;
                            }
                            pointer_id++;
                            var offset = $(this).offset();
                            pointers.push({
                                id: pointer_id,
                                x: (e.pageX - offset.left) + this.scrollLeft,
                                y: e.pageY - offset.top,
                                printed: false
                            });
                            print_pointers();
                        });
                        <?php } ?>
                        print_pointers();
                        $('#file_notes .pointer-ids').each(function(){pointer_hover_node(this);});
                    });

                </script>
                    <?php
                    $show_output=false;
                    if(preg_match('/\.(\w\w\w\w?)$/',$file['file_name'],$matches)){
                        switch(strtolower($matches[1])){
                            case 'jpg':
                            case 'jpeg':
                            case 'gif':
                            case 'png':
                                $show_output=true;
                                break;
                        }
                    }
                    ?>
        <div>
            <div style="width:70%;float:left;border:1px solid #EFEFEF; overflow:auto; <?php echo ($show_output)?'cursor:pointer;':''; ?>" id="file_preview">
                <?php if($show_output){ ?>
                <?php if(module_file::can_i('create','File Comments')){ ?>
                    <input type="hidden" name="pointers" id="pointer_save" value="<?php echo $file['pointers']; ?>" class="no_permissions">
                <?php } ?>
                <div id="pointer_template" style="width:19px; height:12px; padding:3px; background-color:#EFEFEF; border:1px solid #CCC; position:absolute; display:none;">
                    #<span class="pointer_id">0</span>
                </div>
                <div id="pointer_holder" style="position: relative; float: left; height: 0pt;"></div>
                <?php } ?>
                <?php if(preg_match('/\.(\w\w\w\w?)$/',$file['file_name'],$matches)){
                    switch(strtolower($matches[1])){
                        case 'jpg':
                        case 'jpeg':
                        case 'gif':
                        case 'png':
                            ?>
                                <img src="<?php echo module_file::link_public_view($file_id);?>" alt="<?php _e('Click to add a comment');?>"> <!-- style="max-width: 100%" -->
                                <?php
                            break;
                        default:
                            // file download link
                            ?>
                                <div style="text-align:center; padding:20px;">
                                    <a href="<?php echo module_file::link_public_view($file_id); ?>">Download File</a>
                                </div>
                                <?php
                        }
                    } ?>

            </div>
            <div style="width:29%; float:right;" id="file_notes">
                <div class="tableclass" style="margin:0 0 10px 0">
                    <?php if(module_file::can_i('create','File Comments')){ ?>
                    <textarea name="new_comment_text" style="width:98%;" class="no_permissions"></textarea>
                    <?php } ?>
                    <div style="text-align:right;">
                        <?php if(module_file::can_i('create','File Comments')){ ?>
                        <input type="submit" name="butt_save_note" id="butt_save_note" value="<?php echo _l('Add Comment'); ?>" class="submit_button no_permissions">
                        <?php } ?>
                        <input type="hidden" name="delete_file_comment_id" id="delete_file_comment_id" value="0" class="no_permissions">
                    </div>
                </div>
                <?php foreach(module_file::get_file_comments($file_id) as $item){
                $note_text = forum_text($item['comment']);
                if(preg_match_all('/#(\d+)/',$note_text,$matches)){
                    //
                    foreach($matches[1] as $digit){
                        $note_text = preg_replace('/#'.$digit.'([^\d])/','<span node_id='.$digit.' class="pointer-ids pointer-id-'.$digit.'">#'.$digit.'</span>$1',$note_text);
                    }
                }
                ?>
                <div style="border-top:1px dashed #CCCCCC; padding:3px; margin:3px 0;">
                    <?php echo $note_text; ?>
                    <div style="font-size:10px; text-align:right; color:#CCCCCC;">From <?php echo module_user::link_open($item['create_user_id'],true); ?> on <?php echo print_date($item['date_created'],true); ?>
                        <?php if(module_file::can_i('delete','File Comments') || $item['create_user_id'] == module_security::get_loggedin_id()){ ?>
                        <a href="#" onclick="if(confirm('<?php echo _l('Really remove this comment?'); ?>')){$('#delete_file_comment_id').val('<?php echo $item['file_comment_id']; ?>'); $('#butt_save_note').click(); } return false;" style="color:#FF0000">x</a>
                        <?php } ?>
                    </div>
                </div>
                <?php
            }
                ?>
            </div>
            <div class="clear"></div>
        </div>
        <?php } ?>


</form>
