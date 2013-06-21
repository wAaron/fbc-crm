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

if(!$invoice_safe)die('failed');

$invoice_id = (int)$_REQUEST['invoice_id'];
if(isset($_REQUEST['go'])){
    // send the actual invoice.
    // step1, generate the PDF for the invoice...
    $pdf_file = module_invoice::generate_pdf($invoice_id);

	if($pdf_file && is_file($pdf_file)){
        ob_end_clean();

		// send pdf headers and prompt the user to download the PDF

		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Cache-Control: private",false);
		header("Content-Type: application/pdf");
		header("Content-Disposition: attachment; filename=\"".basename($pdf_file)."\";");
		header("Content-Transfer-Encoding: binary");
		header("Content-Length: ".filesize($pdf_file));
		readfile($pdf_file);

	}
	exit;

}else{

    // hack for multi print
    if(isset($_REQUEST['invoice_ids']) && $_REQUEST['invoice_ids']){
        ?>
        <?php print_heading('Print Multiple PDFs'); ?>
        <form action="" method="post" id="printform">
            <input type="hidden" name="invoice_id" id="print_invoice_id" value="0">
            <input type="hidden" name="go" value="yes">
            <input type="hidden" name="print" value="1">
        </form>
        <script type="text/javascript">
            function generate(invoice_id,l){
               $(l).html('Generating .... please wait');
                $('#print_invoice_id').val(invoice_id);
                $('#printform')[0].submit();
                return false;
            }
        </script>
            <p>Click on each link below to save the invoice as PDF</p>
        <ul>
        <?php
        foreach(explode(',',$_REQUEST['invoice_ids']) as $invoice_id){
            $invoice = module_invoice::get_invoice($invoice_id);
            ?>
            <li><a href="#" onclick="return generate(<?php echo $invoice_id;?>,this);"><?php echo $invoice['name'];?></a></li>
            <?php
        }
        ?> </ul> <?php
    }else{

    ?>

        <?php print_heading('Generating PDF'); ?>

        <p><?php _e('Please wait...');?></p>

        <?php if(get_display_mode() == 'mobile'){ ?>

        <script type="text/javascript">
            window.onload = function(){
                window.location.href='<?php echo $module->link_generate($invoice_id,array('arguments'=>array('go'=>1,'print'=>1),'page'=>'invoice_admin','full'=>false));?>';
            }
        </script>

        <?php }else{

            ?>

        <iframe src="<?php echo $module->link_generate($invoice_id,array('arguments'=>array('go'=>1,'print'=>1),'page'=>'invoice_admin','full'=>false));?>" style="display:none;"></iframe>


        <?php } ?>

        <p><?php echo _l('After printing is complete you can <a href="%s">click here</a> return to invoice %s',module_invoice::link_open($invoice_id),module_invoice::link_open($invoice_id,true));?></p>

    <?php } ?>

<?php } ?>