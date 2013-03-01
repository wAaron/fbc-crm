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
$hash = $_REQUEST['hash'];
$form_data = $_SESSION['_delete_data'][$hash];
if(!$form_data){
    echo 'Error, please go back and try again';
    exit;
}

//$data = array($message,$post_data,$post_uri,$cancel_url);
print_heading(htmlspecialchars($form_data[0]));
?>

<form action="<?php echo $form_data[2];?>" method="post">
    <input type="hidden" name="_confirm_delete" value="<?php echo htmlspecialchars($hash);?>">
<?php foreach($form_data[1] as $key=>$val){
    if(is_array($val)){
        foreach($val as $key2=>$val2){
            if(is_array($val2))continue;
            ?>
            <input type="hidden" name="<?php echo htmlspecialchars($key);?>[<?php echo htmlspecialchars($key2);?>]" value="<?php echo htmlspecialchars($val2);?>">
            <?php
        }
    }else{
    ?>
    <input type="hidden" name="<?php echo htmlspecialchars($key);?>" value="<?php echo htmlspecialchars($val);?>">
    <?php } ?>
<?php } ?>
    <input type="submit" value="<?php _e('Confirm Delete');?>" class="submit_button delete_button">
    <input type="button" onclick="window.location.href='<?php echo $form_data[3];?>'" class="submit_button" value="<?php _e('Cancel');?>">
</form>

