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


class module_paymethod_authorize extends module_base{


    public static function can_i($actions,$name=false,$category=false,$module=false){
        if(!$module)$module=__CLASS__;
        return parent::can_i($actions,$name,$category,$module);
    }
	public static function get_class() {
        return __CLASS__;
    }
	function init(){
        $this->version = 2.221;
		$this->module_name = "paymethod_authorize";
		$this->module_position = 8882;

        // 2.221 - 2013-07-15 - verifypeer option
        // 2.22 - 2013-04-27 - cancel url on payments screen
        // 2.21 - 2013-04-20 - initial release

	}

    public function pre_menu(){

        if(module_config::can_i('view','Settings')){
            $this->links[] = array(
                "name"=>"Authorize",
                "p"=>"authorize_settings",
                'holder_module' => 'config', // which parent module this link will sit under.
                'holder_module_page' => 'config_payment',  // which page this link will be automatically added to.
                'menu_include_parent' => 1,
            );
        }
    }

    public function handle_hook($hook){
        switch($hook){
            case 'get_payment_methods':
                return $this;
                break;
        }
    }

    public function is_method($method){
        return $method=='online';
    }
    public static function is_enabled(){
        return module_config::c('payment_method_authorize_enabled',0);
    }
    public function is_allowed_for_invoice($invoice_id){
        return module_config::c('__inv_authorize_'.$invoice_id,1);
    }
    public function set_allowed_for_invoice($invoice_id,$allowed=1){
        module_config::save_config('__inv_authorize_'.$invoice_id,$allowed);
    }

    public static function get_payment_method_name(){
        return module_config::s('payment_method_authorize_label','Authorize');
    }

    public function get_invoice_payment_description($invoice_id,$method=''){

    }

    public static function add_payment_data($invoice_payment_id,$key,$val){
        $payment = module_invoice::get_invoice_payment($invoice_payment_id);
        $payment_data = @unserialize($payment['data']);
        if(!is_array($payment_data))$payment_data = array();
        if(!isset($payment_data[$key]))$payment_data[$key]=array();
        $payment_data[$key][] = $val;
        update_insert('invoice_payment_id',$invoice_payment_id,'invoice_payment',array('data'=>serialize($payment_data)));
    }
    
    public static function start_payment($invoice_id,$payment_amount,$invoice_payment_id,$user_id=false){
        if($invoice_id && $payment_amount && $invoice_payment_id){
            // we are starting a payment via authorize!
            // setup a pending payment and redirect to authorize.
            $invoice_data = module_invoice::get_invoice($invoice_id);
            if(!$user_id)$user_id = $invoice_data['user_id'];
            if(!$user_id)$user_id = module_security::get_loggedin_id();
            $invoice_payment_data = module_invoice::get_invoice_payment($invoice_payment_id);
            if($invoice_payment_data && $invoice_payment_data['invoice_id'] == $invoice_data['invoice_id']){
                //self::authorize_redirect($description,$payment_amount,$user_id,$invoice_payment_id,$invoice_id,$invoice_payment_data['currency_id']);
                $currency = module_config::get_currency($invoice_payment_data['currency_id']);
                $currency_code = $currency['code'];
                ob_start();
                include('includes/plugin_paymethod_authorize/pages/authorize_form_default.php');
                module_template::init_template('authorize_credit_card_form',ob_get_clean(),'Form displayed for payments via Authorize.net','code');
                $form = module_template::get_template_by_key('authorize_credit_card_form');
//                $form = new module_template();
//                $form->content = ob_get_clean();
                ob_start();
                ?>
                <form action="<?php echo full_link(_EXTERNAL_TUNNEL.'?m=paymethod_authorize&h=pay&method=authorize');?>" method="POST" id="authorize-payment-form">
                <input type="hidden" name="invoice_payment_id" value="<?php echo $invoice_payment_id;?>">
                <input type="hidden" name="invoice_id" value="<?php echo $invoice_id;?>">
                    <?php echo $form->content; ?>
                </form>
                <?php
                $form->content = ob_get_clean();
                $form->assign_values(
                    array(
                        'INVOICE_NUMBER' => $invoice_data['name'],
                        'AMOUNT' => dollar($invoice_payment_data['amount'],true,$invoice_payment_data['currency_id']),
                        'CANCEL_URL' => module_invoice::link_public($invoice_id),
                    )
                );
                echo $form->render('pretty_html');
            }
            exit;
        }
        return false;
    }

    public function external_hook($hook){
         switch($hook){
             case 'pay':
                 // result is retured via ajax and displayed on the page.
                 $invoice_id = isset($_REQUEST['invoice_id']) ? $_REQUEST['invoice_id'] : false;
                 $invoice_payment_id = isset($_REQUEST['invoice_payment_id']) ? $_REQUEST['invoice_payment_id'] : false;
                 if($invoice_id && $invoice_payment_id){

                    $invoice_payment_data = module_invoice::get_invoice_payment($invoice_payment_id);
                    $invoice_data = module_invoice::get_invoice($invoice_id);
                    if($invoice_payment_data && $invoice_data && $invoice_id==$invoice_data['invoice_id'] && $invoice_payment_data['invoice_id']==$invoice_data['invoice_id']){
                        $currency = module_config::get_currency($invoice_payment_data['currency_id']);
                        $currency_code = $currency['code'];
                        $description = _l('Payment for invoice %s',$invoice_data['name']);

                        require_once 'includes/plugin_paymethod_authorize/anet_php_1.1.8/AuthorizeNet.php';

                        $transaction = new AuthorizeNetAIM(module_config::c('payment_method_authorize_api_login_id',''), module_config::c('payment_method_authorize_transaction_key',''));
                        $transaction->VERIFY_PEER =  module_config::c('payment_method_authorize_ssl_verify',1);
                        $transaction->amount = $invoice_payment_data['amount']; // USD ONLY
                        $transaction->card_num = isset($_POST['number']) ? $_POST['number'] : '';
                        $transaction->exp_date = $_POST['month'].'/'.$_POST['year'];
                        $transaction->card_code = $_POST['cvv'];

                        $name = $_POST['name'];

                        $response = $transaction->authorizeAndCapture();

                        if ($response->approved) {
//                          echo "<h1>Success! The test credit card has been charged!</h1>";
//                          echo "Transaction ID: " . $response->transaction_id;
                            update_insert("invoice_payment_id",$invoice_payment_id,"invoice_payment",array(
                                                                              'date_paid' => date('Y-m-d'),
                                                                                 ));
                            module_paymethod_stripe::add_payment_data($invoice_payment_id,'log',"Successfully paid: ".var_export($response,true));

                            // success!
                            // redirect to receipt page.
                            redirect_browser(module_invoice::link_receipt($invoice_payment_id));
                        } else {
                          echo $response->error_message;
                        }

                        exit;
                    }
                 }
                 echo 'Error paying via Authorize';
                 exit;
        }
    }

}