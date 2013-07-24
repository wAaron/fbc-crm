<div class="blob">
	<?php 
/** 
  * Copyright: dtbaker 2012
  * Licence: Please check CodeCanyon.net for licence details. 
  * More licence clarification available here:  http://codecanyon.net/wiki/support/legal-terms/licensing-terms/ 
  * Deploy: 3053 c28b7e0e323fd2039bb168d857c941ee
  * Envato: 6b31bbe6-ead4-44a3-96e1-d5479d29505b
  * Package Date: 2013-02-27 19:23:35 
  * IP Address: 210.14.75.228
  */

    $step = (isset($_REQUEST['step'])) ? (int)$_REQUEST['step'] : 0;

    //print_heading('Setup Wizard (step '.$step.' of 4)');?>

    
    <p>
        <?php echo _l('Hello, Welcome to the setup wizard. You are currently on step %s of 5.',$step); ?>
    </p>

    <?php
    include('setup'.$step.'.php');
    ?>
      
</div>


