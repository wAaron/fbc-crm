<div class="file_<?php 
/** 
  * Copyright: dtbaker 2012
  * Licence: Please check CodeCanyon.net for licence details. 
  * More licence clarification available here:  http://codecanyon.net/wiki/support/legal-terms/licensing-terms/ 
  * Deploy: 3053 c28b7e0e323fd2039bb168d857c941ee
  * Envato: 6b31bbe6-ead4-44a3-96e1-d5479d29505b
  * Package Date: 2013-02-27 19:09:56 
  * IP Address: 
  */ echo $owner_table;?>_<?php echo $owner_id;?>">
    <?php
    foreach($file_items as $file_item){
        $file_item = self::get_file($file_item['file_id']);
        ?>
        
        <div style="width:110px; height:90px; overflow:hidden; ">

            <?php
            // /display a thumb if its supported.
            if(preg_match('/\.(\w\w\w\w?)$/',$file_item['file_name'],$matches)){
                switch(strtolower($matches[1])){
                    case 'jpg':
                    case 'jpeg':
                    case 'gif':
                    case 'png':
                        ?>
                            <img src="<?php echo _BASE_HREF . nl2br(htmlspecialchars($file_item['file_path']));?>" width="100" alt="preview" border="0">
                        <?php
                        break;
                    default:
                        echo 'n/a';
                }
            }
            ?>
        </div>
    <?php
    }
    ?>
</div>

