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

if(!module_config::can_i('view','Settings')){
    redirect_browser(_BASE_HREF);
}
$template_id = $_REQUEST['template_id'];
$template = array();
if($template_id && $template_id != 'new'){
	$template = module_template::get_template($template_id);
}
if(!$template){
	$template_id = 'new';
	$template = array(
		'template_id' => 'new',
		'template_key' => '',
		'description' => '',
		'content' => '',
		'name' => '',
		'default_text' => '',
		'wysiwyg' => 1,
	);
	module_security::sanatise_data('template',$template);
}
?>
<form action="<?php echo module_template::link_open(false);?>" method="post" id="template_form">

      <?php
module_form::prevent_exit(array(
    'valid_exits' => array(
        // selectors for the valid ways to exit this form.
        '.submit_button',
    ))
);
?>

    
	<input type="hidden" name="_process" value="save_template" />
	<input type="hidden" name="template_id" value="<?php echo $template_id; ?>" />
	<input type="hidden" name="return" value="<?php echo isset($_REQUEST['return']) ? htmlspecialchars(urldecode($_REQUEST['return'])):''; ?>" /> <!-- for popup editing -->
	<table cellpadding="10" width="100%">
		<tr>
			<td valign="top">
				<?php print_heading('Edit Template'); ?>

				<table width="100%" border="0" cellspacing="0" cellpadding="2" class="tableclass">
					<tbody>
						<tr>
							<th class="width2">
								<?php echo _l('Template Key'); ?>
							</th>
							<td>
								<input type="text" name="template_key" style="width: 350px;" id="template_key" value="<?php echo htmlspecialchars($template['template_key']); ?>" />
							</td>
						</tr>
						<tr>
							<th>
								<?php echo _l('Template Description'); ?>
							</th>
							<td>
								<input type="text" name="description" style="width: 350px;" id="description" value="<?php echo htmlspecialchars($template['description']); ?>" />
							</td>
						</tr>
						<tr id="wysiwyg">
							<th>
								<?php echo _l('WYSIWYG'); ?>
							</th>
							<td>
								<?php echo print_select_box(get_yes_no(),'wysiwyg',isset($template['wysiwyg'])?$template['wysiwyg']:0,'',false); ?> (advanced setting, don't change unless you know what you're doing)
							</td>
						</tr>
						<tr>
							<th>
								<?php echo _l('Default Text'); ?>
							</th>
							<td valign="top">
								<textarea name="content" id="template_content" rows="10" cols="30" style="width:450px; height: 350px;"><?php echo htmlspecialchars($template['content']); ?></textarea>

                                <?php if(isset($template['wysiwyg']) && $template['wysiwyg']){ ?>

                                    <script type="text/javascript" src="<?php echo _BASE_HREF;?>js/tiny_mce3.4.4/jquery.tinymce.js"></script>
<script type="text/javascript">
	$().ready(function() {
		$('#template_content').tinymce({
			// Location of TinyMCE script
			script_url : '<?php echo _BASE_HREF;?>js/tiny_mce3.4.4/tiny_mce.js',

            relative_urls : false,
            convert_urls : false,

			// General options
			theme : "advanced",
			plugins : "<?php echo (stripos($template['content'],'<html')!==false||stripos($template['content'],'<body')!==false)?'fullpage,':''; ?>autolink,lists,pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template,advlist",

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

            height : '600px',
            width : '100%'

		});
	});
</script>

                                <?php } ?>

							</td>
						</tr>
                    <tr>
                        <th> </th>
                        <td>
                            <?php
                                if(isset($template['tags']) && strlen($template['tags']) &&  $tags = @unserialize($template['tags'])){
                                    echo '<ul>';
                                    foreach($tags as $key=>$val){
                                        echo '<li>';
                                        echo '<strong>{'.htmlspecialchars($key).'}</strong>';
                                        if($val){
                                            echo ' ' .htmlspecialchars($val);
                                        }
                                        echo '</li>';
                                    }
                                    echo '</ul>';
                                }else{
                                } ?>
                            <br/><br/>
                            <a href="#" onclick="<?php if(isset($template['wysiwyg']) && $template['wysiwyg']){ ?> $('#template_content').tinymce().remove(); <?php } ?> $('#template_content').val(''); $('#template_form')[0].submit(); return false;">Restore Default</a>
                        </td>
                    </tr>

						
					</tbody>
				</table>

			</td>
		</tr>
		<tr>
			<td align="center">
				<input type="submit" name="butt_save" id="butt_save" value="<?php echo _l('Save'); ?>" class="submit_button" />
				<!-- <input type="submit" name="butt_del" id="butt_del" value="<?php echo _l('Delete'); ?>" onclick="return confirm('<?php echo _l('Really delete this record?'); ?>');" class="submit_button" /> -->
				<input type="button" name="cancel" value="<?php echo _l('Cancel'); ?>" onclick="window.location.href='<?php echo $module->link('template',array('template_id'=>false)); ?>';" class="submit_button" />

			</td>
		</tr>
	</table>

</form>
