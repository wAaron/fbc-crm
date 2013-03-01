<form action="" method="post">


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
module_form::prevent_exit(array(
    'valid_exits' => array(
        // selectors for the valid ways to exit this form.
        '.submit_button',
    ))
);
?>

    <input type="hidden" name="_config_settings_hook" value="save_config">

    <table class="tableclass tableclass_rows">
        <thead>
        <tr>
            <!--<th>
                <?php /*_e('Key');*/?>
            </th>-->
            <th width="30%">
                <?php _e('Description');?>
            </th>
            <th>
                <?php _e('Value');?>
            </th>
        </tr>
        </thead>
        <tbody>
            <?php foreach($settings as $setting){ ?>
            <tr>
                <!--<td>
                    <?php /*echo $setting['key'];*/?>
                </td>-->
                <th><?php echo $setting['description'];?></th>
                <td>

                    <?php

                    module_form::generate_form_element(array(
                        'type' => $setting['type'],
                        'name' => 'config['.$setting['key'].']',
                        'value' => module_config::c($setting['key'],$setting['default']),
                        'options' => isset($setting['options']) ? $setting['options'] : array(),
                    ));


                    /*switch($setting['type']){
                        case 'number':
                        ?>
                            <input type="text" name="config[<?php echo $setting['key'];?>]" value="<?php echo htmlspecialchars(module_config::c($setting['key'],$setting['default']));?>" size="20">
                            <?php
                        break;
                        case 'text':
                        ?>
                            <input type="text" name="config[<?php echo $setting['key'];?>]" value="<?php echo htmlspecialchars(module_config::c($setting['key'],$setting['default']));?>" size="60">
                            <?php
                        break;
                        case 'textarea':
                        ?>
                            <textarea name="config[<?php echo $setting['key'];?>]" rows="6" cols="50"><?php echo htmlspecialchars(module_config::c($setting['key'],$setting['default']));?></textarea>
                            <?php
                        break;
                        case 'select':
                        ?>
                            <select name="config[<?php echo $setting['key'];?>]">
                                <option value=""><?php _e('N/A');?></option>
                                <?php foreach($setting['options'] as $key=>$val){ ?>
                                <option value="<?php echo $key;?>"<?php echo module_config::c($setting['key'],$setting['default']) == $key ? ' selected':'' ?>><?php echo htmlspecialchars($val);?></option>
                                <?php } ?>
                            </select>
                            <?php
                        break;
                        case 'checkbox':
                        ?>
                            <input type="hidden" name="config_default[<?php echo $setting['key'];?>]" value="1">
                            <input type="checkbox" name="config[<?php echo $setting['key'];?>]" value="1" <?php if(module_config::c($setting['key'])) echo ' checked'; ?>>
                            <?php
                        break;

                        }*/

                    if(isset($setting['help'])){
                        _h($setting['help']);
                    }
                        ?>

                </td>
            </tr>
            <?php } ?>

            <tr>
                <td colspan="3" align="center">
                    <input type="submit" name="save" value="<?php _e('Save settings');?>" class="submit_button save_button">
                </td>
            </tr>
        </tbody>
    </table>

</form>