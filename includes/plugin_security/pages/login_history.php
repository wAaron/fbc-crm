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

$sql = "SELECT * FROM `"._DB_PREFIX."security_login` l LEFT JOIN `"._DB_PREFIX."user` u ON l.user_id = u.user_id ORDER BY user_login_id DESC LIMIT 60";
$history = qa($sql);
?>

<table width="100%">
    <tr>
        <td width="50%" valign="top">
            <h3><?php _e('Login History');?></h3>

            <table border="0" cellspacing="0" cellpadding="2" class="tableclass tableclass_rows">
                <thead>
                <tr>
                    <th>
                        Date/Time
                    </th>
                    <th>IP</th>
                    <th>Host</th>
                    <th>User</th>
                </tr>
                </thead>
                <tbody>
                <?php
                $ips = array();
                foreach($history as $h){
                    if(!isset($ips[$h['ip_address']])){
                        $ips[$h['ip_address']] = gethostbyaddr($h['ip_address']);
                    }
                    ?>
                <tr>
                    <td>
                        <?php echo print_date($h['time'],true);?>
                    </td>
                    <td>
                        <?php echo $h['ip_address'];?>
                    </td>
                    <td>
                        <?php echo $ips[$h['ip_address']];?>
                    </td>
                    <td>
                        <?php echo module_user::link_open($h['user_id'],true);?>
                    </td>
                </tr>
                    <?php } ?>
                </tbody>
            </table>

        </td>
        <td width="50%" valign="top">
            <h3><?php _e('Session History');?></h3>



        </td>
    </tr>
</table>
