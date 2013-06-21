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
if(!isset($options) && isset($_REQUEST['options'])){
    $options = unserialize(base64_decode($_REQUEST['options']));
}
if(!isset($options)){
    $options=array();
}
$options = module_email::get_email_compose_options($options);
extract($options);
?>

<form action="" method="post" id="template_change_form">
    <input type="hidden" name="template_name" value="" id="template_name_change">
</form>

<form action="<?php echo full_link('email.email_compose_basic');?>" method="post" enctype="multipart/form-data">
    <input type="hidden" name="_process" value="send_email">
    <input type="hidden" name="options" value="<?php echo base64_encode(serialize($options));?>">

<table class="tableclass tableclass_form tableclass_full">
    <tr>
        <th class="width1">
            <?php _e('From:');?>
        </th>
        <td>
            <div id="email_from_view">
            <?php echo htmlspecialchars(module_config::c('admin_system_name') . ' <'.module_config::c('admin_email_address').'>'); ?> <a href="#" onclick="$(this).parent().hide(); $('#email_from_edit').show(); return false;"><?php _e('edit');?></a>
            </div>
            <div id="email_from_edit" style="display:none;">
            <input type="text" name="from_name" value="<?php echo htmlspecialchars(module_config::c('admin_system_name'));?>">
            &lt;<input type="text" name="from_email" value="<?php echo htmlspecialchars(module_config::c('admin_email_address'));?>">&gt
            </div>
        </td>
    </tr>
    <tr>
        <th>
            <?php _e('To:');?>
        </th>
        <td>
            <?php
            // drop down with various options, or a blank inbox box with an email address.
            if(count($to) > 1){
            ?>
            <select name="custom_to">
                <!-- <option value=""><?php _e('Please select');?></option> -->
                <?php foreach($to as $t){ ?>
                    <option value="<?php echo htmlspecialchars($t['email']);?>||<?php echo htmlspecialchars($t['name']);?>"<?php if(isset($to_select)&&$to_select==$t['email'])echo ' selected';?>><?php echo htmlspecialchars($t['email']) . ' - ' . htmlspecialchars($t['name']);?></option>
                <?php } ?>
            </select>
            <?php }else{
                $t = array_shift($to);
                ?>

                    <?php echo htmlspecialchars($t['email']) . ' - ' . htmlspecialchars($t['name']);?>

            <?php } ?>
        </td>
    </tr>
    <tr>
        <th>
            <?php _e('BCC:');?>
        </th>
        <td>
            <input type="text" name="bcc" value="<?php echo htmlspecialchars($bcc);?>" style="width:400px">
        </td>
    </tr>
    <tr>
        <th>
            <?php _e('Subject:');?>
        </th>
        <td>
            <input type="text" name="subject" value="<?php echo htmlspecialchars($subject);?>" style="width:400px;">
        </td>
    </tr>
    <tr>
        <th>
            <?php _e('Attachment:'); ?>
        </th>
        <td>
            <?php
            // uploado an attachment here, or generate one from a pdf on send.
            // (eg: sending an invoice pdf)
            foreach($attachments as $attachment){
                if($attachment['preview']){
                    echo '<a href="'.$attachment['preview'].'">';
                }
                echo $attachment['name'];
                if($attachment['preview']){
                    echo '</a>';
                }
                echo '<br/>';
            }
            ?>
            <div id="email_attach_holder">
                <div class="dynamic_block">
                    <input type="file" name="manual_attachment[]" value="">
                    <a href="#" class="add_addit" onclick="return seladd(this);">+</a>
                    <a href="#" class="remove_addit" onclick="return selrem(this);">-</a>
                </div>
            </div>
            <script type="text/javascript">
                set_add_del('email_attach_holder');
            </script>
        </td>
    </tr>
    <?php  if(isset($find_other_templates) && strlen($find_other_templates) && isset($current_template) && strlen($current_template)){
        $other_templates = array();
        foreach(module_template::get_templates() as $possible_template){
            if(strpos($possible_template['template_key'],$find_other_templates)!==false){
                // found another one!
                $other_templates[$possible_template['template_key']] = $possible_template['description'];
            }
        }
        if(count($other_templates)>1){
            ?>
            <tr>
                <th>
                    <?php _e('Email Template');?>
                </th>
                <td>
                    <select name="template_name" id="template_name">
                        <?php foreach($other_templates as $other_template_key => $other_template_name){ ?>
                            <option value="<?php echo htmlspecialchars($other_template_key);?>"<?php echo $current_template==$other_template_key ? ' selected':'';?>><?php echo htmlspecialchars($other_template_name);?></option>
                        <?php } ?>
                    </select>
                    <script type="text/javascript">
                        $(function(){
                           $('#template_name').change(function(){
                               $('#template_name_change').val($(this).val());
                               $('#template_change_form')[0].submit();
                           });
                        });
                    </script>
                </td>
            </tr>
            <?php
        }
    } ?>
    <tr>
        <th>
            <?php _e('Message:'); ?>
        </th>
        <td>
            <textarea name="content" id="email_content" rows="10" cols="30" style="width:450px; height: 350px;"><?php echo htmlspecialchars($content); ?></textarea>

                                    <script type="text/javascript" src="<?php echo _BASE_HREF;?>js/tiny_mce3.4.4/jquery.tinymce.js"></script>
<script type="text/javascript">
	$().ready(function() {
		$('#email_content').tinymce({
			// Location of TinyMCE script
			script_url : '<?php echo _BASE_HREF;?>js/tiny_mce3.4.4/tiny_mce.js',

            relative_urls : false,
            convert_urls : false,

			// General options
			theme : "advanced",
			plugins : "fullpage,autolink,lists,pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template,advlist",

			// Theme options
            theme_advanced_buttons1 : "undo,redo,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,formatselect,fontselect,fontsizeselect",
            theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,bullist,numlist,|,link,unlink,anchor,image,cleanup,code,|,forecolor,backcolor",
            theme_advanced_buttons3 : "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,emotions,iespell",
			/*theme_advanced_buttons1 : "save,newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,styleselect,formatselect,fontselect,fontsizeselect",
			theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,code,|,insertdate,inserttime,preview,|,forecolor,backcolor",
			theme_advanced_buttons3 : "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,emotions,iespell,media,advhr,|,print,|,ltr,rtl,|,fullscreen",
			theme_advanced_buttons4 : "insertlayer,moveforward,movebackward,absolute,|,styleprops,|,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,template,pagebreak",*/
			theme_advanced_toolbar_location : "top",
			theme_advanced_toolbar_align : "left",
			theme_advanced_statusbar_location : "bottom",
			theme_advanced_resizing : true,

            height : '300px',
            width : '100%'

		});
	});
</script>
        </td>
    </tr>
    <tr>
        <td colspan="2" align="center">
            <?php if($cancel_url){ ?>
            <input type="button" name="cancel" value="<?php _e('Cancel');?>" class="submit_button" onclick="window.location.href='<?php echo htmlspecialchars($cancel_url);?>';">
            <?php } ?>
            <input type="submit" name="send" value="<?php _e('Send email');?>" class="submit_button save_button">
        </td>
    </tr>
</table>


</form>