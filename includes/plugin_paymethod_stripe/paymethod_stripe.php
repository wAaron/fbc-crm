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

//306412171 ucm_1306412206_per@gmail.com



class module_paymethod_stripe extends module_base{


    public static function can_i($actions,$name=false,$category=false,$module=false){
        if(!$module)$module=__CLASS__;
        return parent::can_i($actions,$name,$category,$module);
    }
	public static function get_class() {
        return __CLASS__;
    }
	function init(){
        $this->version = 2.21;
		$this->module_name = "paymethod_stripe";
		$this->module_position = 8882;

        // 2.21 - 2013-04-16 - initial release

	}

    public function pre_menu(){

        if(module_config::can_i('view','Settings')){
            $this->links[] = array(
                "name"=>"Stripe",
                "p"=>"stripe_settings",
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
        return module_config::c('payment_method_stripe_enabled',0);
    }
    public function is_allowed_for_invoice($invoice_id){
        return module_config::c('__inv_stripe_'.$invoice_id,1);
    }
    public function set_allowed_for_invoice($invoice_id,$allowed=1){
        module_config::save_config('__inv_stripe_'.$invoice_id,$allowed);
    }

    public static function get_payment_method_name(){
        return module_config::s('payment_method_stripe_label','Stripe');
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
            // we are starting a payment via stripe!
            // setup a pending payment and redirect to stripe.
            $invoice_data = module_invoice::get_invoice($invoice_id);
            if(!$user_id)$user_id = $invoice_data['user_id'];
            if(!$user_id)$user_id = module_security::get_loggedin_id();
            $invoice_payment_data = module_invoice::get_invoice_payment($invoice_payment_id);
            $description = _l('Payment for invoice %s',$invoice_data['name']);
            //self::stripe_redirect($description,$payment_amount,$user_id,$invoice_payment_id,$invoice_id,$invoice_payment_data['currency_id']);
        $currency = module_config::get_currency($invoice_payment_data['currency_id']);
            $currency_code = $currency['code'];
            $template = new module_template();
             ob_start();
             include(module_theme::include_ucm('includes/plugin_paymethod_stripe/pages/stripe_form.php'));
             $template->content = ob_get_clean();
             echo $template->render('pretty_html');
             exit;
        }
        return false;
    }

    public function external_hook($hook){
         switch($hook){
             case 'pay':
                 $invoice_id = isset($_REQUEST['invoice_id']) ? $_REQUEST['invoice_id'] : false;
                 $invoice_payment_id = isset($_REQUEST['invoice_payment_id']) ? $_REQUEST['invoice_payment_id'] : false;
                 if($invoice_id && $invoice_payment_id && isset($_POST['stripeToken'])){

                    $invoice_payment_data = module_invoice::get_invoice_payment($invoice_payment_id);
                    $invoice_data = module_invoice::get_invoice($invoice_id);
                    if($invoice_payment_data && $invoice_data && $invoice_id==$invoice_data['invoice_id'] && $invoice_payment_data['invoice_id']==$invoice_data['invoice_id']){
                        $currency = module_config::get_currency($invoice_payment_data['currency_id']);
                        $currency_code = $currency['code'];
                        $description = _l('Payment for invoice %s',$invoice_data['name']);

                        $template = new module_template();
                        ob_start();
                        include(module_theme::include_ucm('includes/plugin_paymethod_stripe/pages/stripe_form.php'));
                        $template->content = ob_get_clean();
echo $template->render('pretty_html');
                        exit;
                    }
                 }
                 echo 'Error paying via Stripe';
                 exit;
        }
    }

}