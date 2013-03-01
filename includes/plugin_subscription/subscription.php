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


class module_subscription extends module_base{

	public $links;
	public $subscription_types;
    public $subscription_id;

    public static function can_i($actions,$name=false,$category=false,$module=false){
        if(!$module)$module=__CLASS__;
        return parent::can_i($actions,$name,$category,$module);
    }
	public static function get_class() {
        return __CLASS__;
    }
    public function init(){
		$this->links = array();
		$this->subscription_types = array();
		$this->module_name = "subscription";
		$this->module_position = 30;
        $this->version = 2.143;
        // 2.13 - initial release
        // 2.131 - better integration with invoicing sysetem. eg: eamiling an invoice to a member. adding a member_id field to invoice.
        // 2.132 - delete fix.
        // 2.134 - permission fix.
        // 2.135 - submit_small in create
        // 2.136 - Delete member bug fix
        // 2.137 - hook into finance module to display nicer in finance listing
        // 2.138 - subscription support for customers.
        // 2.139 - permission fix
        // 2.140 - bug fixing
        // 2.141 - fix for subscription in finance upcoming items
        // 2.142 - customer subscription bug fix
        // 2.143 - dashboard alerts bug fix


        module_config::register_css('subscription','subscription.css');
;
        hook_add('invoice_sidebar','module_subscription::hook_invoice_sidebar');
        hook_add('invoice_deleted','module_subscription::hook_invoice_deleted');
        
        hook_add('member_edit','module_subscription::member_edit_form');
        hook_add('member_save','module_subscription::member_edit_form_save');
        hook_add('member_deleted','module_subscription::hook_member_deleted');

        hook_add('customer_edit','module_subscription::customer_edit_form');
        hook_add('customer_save','module_subscription::customer_edit_form_save');
        hook_add('customer_deleted','module_subscription::hook_customer_deleted');

        hook_add('finance_recurring_list','module_subscription::get_finance_recurring_items');
        hook_add('finance_invoice_listing','module_subscription::get_invoice_listing');

	}

    public function pre_menu(){

		if($this->can_i('view','Subscriptions') && $this->can_i('edit','Subscriptions') && module_config::can_i('view','Settings')){


            // how many subscriptions are there?
            $link_name = _l('Subscriptions');

			$this->links['subscriptions'] = array(
				"name"=>$link_name,
				"p"=>"subscription_admin",
				"args"=>array('subscription_id'=>false),
                'holder_module' => 'config', // which parent module this link will sit under.
                'holder_module_page' => 'config_admin',  // which page this link will be automatically added to.
                'menu_include_parent' => 0,
			);
		}

    }

    /** static stuff */

    
     public static function link_generate($subscription_id=false,$options=array(),$link_options=array()){
        // we accept link options from a bubbled link call.
        // so we have to prepent our options to the start of the link_options array incase
        // anything bubbled up to this method.
        // build our options into the $options variable and array_unshift this onto the link_options at the end.
        $key = 'subscription_id'; // the key we look for in data arrays, on in _REQUEST variables. for sub link building.

        // we check if we're bubbling from a sub link, and find the item id from a sub link
        if(${$key} === false && $link_options){
            foreach($link_options as $link_option){
                if(isset($link_option['data']) && isset($link_option['data'][$key])){
                    ${$key} = $link_option['data'][$key];
                    break;
                }
            }
            if(!${$key} && isset($_REQUEST[$key])){
                ${$key} = $_REQUEST[$key];
            }
        }
        // grab the data for this particular link, so that any parent bubbled link_generate() methods
        // can access data from a sub item (eg: an id)

        if(isset($options['full']) && $options['full']){
            // only hit database if we need to print a full link with the name in it.
            if(!isset($options['data']) || !$options['data']){
                if((int)$subscription_id>0){
                    $data = self::get_subscription($subscription_id);
                }else{
                    $data = array();
                    return _l('N/A');
                }
                $options['data'] = $data;
            }else{
                $data = $options['data'];
            }
            // what text should we display in this link?
            $options['text'] = $data['name'];
        }
        $options['text'] = isset($options['text']) ? htmlspecialchars($options['text']) : '';
        // generate the arguments for this link
        $options['arguments'] = array(
            'subscription_id' => $subscription_id,
        );
        // generate the path (module & page) for this link
        $options['page'] = 'subscription_admin';
        $options['module'] = 'subscription';

        // append this to our link options array, which is eventually passed to the
        // global link generate function which takes all these arguments and builds a link out of them.

         if(!self::can_i('view','Subscriptions')){
            if(!isset($options['full']) || !$options['full']){
                return '#';
            }else{
                return isset($options['text']) ? $options['text'] : _l('N/A');
            }
        }

        // optionally bubble this link up to a parent link_generate() method, so we can nest modules easily
        // change this variable to the one we are going to bubble up to:
        $bubble_to_module = false;
        $bubble_to_module = array(
            'module' => 'config',
            'argument' => 'subscription_id',
        );
        array_unshift($link_options,$options);
        if($bubble_to_module){
            global $plugins;
            return $plugins[$bubble_to_module['module']]->link_generate(false,array(),$link_options);
        }else{
            // return the link as-is, no more bubbling or anything.
            // pass this off to the global link_generate() function
            return link_generate($link_options);
        }
    }


	public static function link_open($subscription_id,$full=false,$data=array()){
		return self::link_generate($subscription_id,array('full'=>$full,'data'=>$data));
	}



	public static function get_subscriptions($search=array()){

        $sql = "SELECT s.*";
        //$sql .= ", COUNT(sm.subscription_id) AS member_count ";
        $sql .= ", (SELECT COUNT(sm.subscription_id) FROM `"._DB_PREFIX."subscription_member` sm WHERE s.subscription_id = sm.subscription_id) AS member_count";
        //$sql .= ", COUNT(sc.subscription_id) AS customer_count ";
        $sql .= ", (SELECT COUNT(sc.subscription_id) FROM `"._DB_PREFIX."subscription_customer` sc WHERE s.subscription_id = sc.subscription_id) AS customer_count";
        $sql .= " FROM `"._DB_PREFIX."subscription` s ";
        //$sql .= " LEFT JOIN `"._DB_PREFIX."subscription_member` sm ON s.subscription_id = sm.subscription_id";
        //$sql .= " LEFT JOIN `"._DB_PREFIX."subscription_customer` sc ON s.subscription_id = sc.subscription_id";
        $sql .= " GROUP BY s.subscription_id";
        $sql .= " ORDER BY s.name";
        return qa($sql);
		//return get_multiple("subscription",$search,"subscription_id","fuzzy","name");
	}
	public static function get_subscriptions_by_member($member_id,$subscription_id=false){

        $sql = "SELECT s.*, sm.*, s.subscription_id AS id ";
        $sql .= " FROM `"._DB_PREFIX."subscription_member` sm ";
        $sql .= " LEFT JOIN `"._DB_PREFIX."subscription` s USING (subscription_id)";
        $sql .= " WHERE sm.member_id = ".(int)$member_id;
        $sql .=  " AND sm.`deleted` = 0";
        if($subscription_id){
            $sql .=  " AND sm.`subscription_id` = ".(int)$subscription_id;
        }
        return qa($sql);
		//return get_multiple("subscription",$search,"subscription_id","fuzzy","name");
	}
	public static function get_subscriptions_by_customer($customer_id,$subscription_id=false){

        $sql = "SELECT s.*, sc.*, s.subscription_id AS id ";
        $sql .= " FROM `"._DB_PREFIX."subscription_customer` sc ";
        $sql .= " LEFT JOIN `"._DB_PREFIX."subscription` s USING (subscription_id)";
        $sql .= " WHERE sc.customer_id = ".(int)$customer_id;
        $sql .=  " AND sc.`deleted` = 0";
        if($subscription_id){
            $sql .=  " AND sc.`subscription_id` = ".(int)$subscription_id;
        }
        return qa($sql);
		//return get_multiple("subscription",$search,"subscription_id","fuzzy","name");
	}
	public static function get_subscription_history($subscription_id,$member_id=false,$customer_id=false){

        $sql = "SELECT sh.* ";
        $sql .= " FROM `"._DB_PREFIX."subscription_history` sh ";
        $sql .= " WHERE sh.subscription_id = ".(int)$subscription_id;
        if($member_id>0){
            $sql .= " AND sh.member_id = ".(int)$member_id;
        }
        if($customer_id>0){
            $sql .= " AND sh.customer_id = ".(int)$customer_id;
        }
        $sql .= " ORDER BY sh.`paid_date` ASC"; // asc needed for next due date calculations.
        return qa($sql);
		//return get_multiple("subscription",$search,"subscription_id","fuzzy","name");
	}

	public static function get_subscription($subscription_id){
        $subscription_id = (int)$subscription_id;
        $subscription = false;
        if($subscription_id>0){
            $sql = "SELECT s.*, COUNT(sm.subscription_id) AS member_count ";
            $sql .= " FROM `"._DB_PREFIX."subscription` s ";
            $sql .= " LEFT JOIN `"._DB_PREFIX."subscription_member` sm USING (subscription_id)";
            $sql .= " WHERE s.subscription_id = ".(int)$subscription_id."";
            $sql .=  " AND (sm.`deleted` = 0 OR sm.`deleted` IS NULL)";
            $sql .= " GROUP BY s.subscription_id";
            $subscription = qa1($sql);
        }
        if(!$subscription){
            $subscription = array(
                'subscription_id' => '0',
                'name' => '',
                'days' => '',
                'months' => '',
                'years' => '',
                'amount' => '',
                'currency_id' => '',
                'member_count' => 0,
            );
        }
		return $subscription;
	}


    
	public function process(){
		if(isset($_REQUEST['butt_del']) && $_REQUEST['butt_del'] && $_REQUEST['subscription_id']){
			$data = self::get_subscription($_REQUEST['subscription_id']);
            if(module_form::confirm_delete('subscription_id',"Really delete subscription: ".$data['name'],self::link_open($_REQUEST['subscription_id']))){
                $this->delete_subscription($_REQUEST['subscription_id']);
                set_message("Subscription deleted successfully");
                redirect_browser(self::link_open(false));
            }
		}else if("save_subscription" == $_REQUEST['_process']){
			$subscription_id = $this->save_subscription($_REQUEST['subscription_id'],$_POST);
			set_message("Subscription saved successfully");
			redirect_browser(self::link_open($subscription_id));
		}
	}


	public function save_subscription($subscription_id,$data){
		$subscription_id = update_insert("subscription_id",$subscription_id,"subscription",$data);

        module_extra::save_extras('subscription','subscription_id',$subscription_id);

		return $subscription_id;
	}


	public function delete_subscription($subscription_id){
		$subscription_id=(int)$subscription_id;
        $subscription = self::get_subscription($subscription_id);
        if($subscription && $subscription['subscription_id'] == $subscription_id){
            $sql = "DELETE FROM "._DB_PREFIX."subscription WHERE subscription_id = '".$subscription_id."' LIMIT 1";
            query($sql);
            module_extra::delete_extras('subscription','subscription_id',$subscription_id);
        }
	}

    public static function customer_edit_form_save($callback_name, $customer_id){
        self::member_edit_form_save($callback_name, $customer_id, true);
    }
    public static function member_edit_form_save($callback_name, $member_id, $customer_hack=false){
        if(isset($_REQUEST['member_subscriptions_save'])){
            if($customer_hack){
                $members_subscriptions = module_subscription::get_subscriptions_by_customer($member_id);
            }else{
                $members_subscriptions = module_subscription::get_subscriptions_by_member($member_id);
            }
            // check if any are deleted.
            // check if any are added.
            if(isset($_REQUEST['subscription']) && is_array($_REQUEST['subscription'])){
                foreach($_REQUEST['subscription'] as $subscription_id => $tf){
                    if(isset($members_subscriptions[$subscription_id])){
                        unset($members_subscriptions[$subscription_id]);
                        // this one already exists as a member.
                        // option to update the start date for this one.
                        if(isset($_REQUEST['subscription_start_date']) && isset($_REQUEST['subscription_start_date'][$subscription_id])){
                            $date = input_date($_REQUEST['subscription_start_date'][$subscription_id]);
                            if($date){
                                if($customer_hack){
                                    $sql = "UPDATE `"._DB_PREFIX."subscription_customer` SET `start_date` = '".mysql_real_escape_string($date)."' WHERE `customer_id` = ".(int)$member_id." AND subscription_id = '".(int)$subscription_id."' LIMIT 1";
                                }else{
                                    $sql = "UPDATE `"._DB_PREFIX."subscription_member` SET `start_date` = '".mysql_real_escape_string($date)."' WHERE `member_id` = ".(int)$member_id." AND subscription_id = '".(int)$subscription_id."' LIMIT 1";
                                }
                                query($sql);
                            }
                        }
                        self::update_next_due_date($subscription_id,$member_id,$customer_hack);

                    }else{
                        $start_date = date('Y-m-d');
                        /*// find history. to modify start date based on first payment.
                        $history = self::get_subscription_history($subscription_id,$member_id);
                        if(count($history)>0){
                            foreach($history as $h){
                                if($h['paid_date']!='0000-00-00'){
                                    $start_date = $h['paid_date'];
                                    break;
                                }
                            }
                        }*/
                        // add this new one to this member.
                        if($customer_hack){
                            $sql = "REPLACE INTO `"._DB_PREFIX."subscription_customer` SET ";
                            $sql .= " customer_id = '".(int)$member_id."'";
                            $sql .= ", subscription_id = '".(int)$subscription_id."'";
                            $sql .= ", start_date = '$start_date'";
                        }else{
                            $sql = "REPLACE INTO `"._DB_PREFIX."subscription_member` SET ";
                            $sql .= " member_id = '".(int)$member_id."'";
                            $sql .= ", subscription_id = '".(int)$subscription_id."'";
                            $sql .= ", start_date = '$start_date'";
                        }
                        query($sql);

                        self::update_next_due_date($subscription_id,$member_id,$customer_hack);
                    }
                }
            }
            // remove any left in subscription history.
            foreach($members_subscriptions as $subscription_id => $subscription){
                if($customer_hack){
                    $sql = "UPDATE `"._DB_PREFIX."subscription_customer` SET `deleted` = 1 WHERE `customer_id` = ".(int)$member_id." AND subscription_id = '".(int)$subscription_id."' LIMIT 1";
                }else{
                    $sql = "UPDATE `"._DB_PREFIX."subscription_member` SET `deleted` = 1 WHERE `member_id` = ".(int)$member_id." AND subscription_id = '".(int)$subscription_id."' LIMIT 1";
                }
                query($sql);
            }
        }
        // handle the payment adding. invoice creation. etc.!!
        // similar to premium ticket creation.
        if(isset($_REQUEST['subscription_add_payment_amount']) && $_REQUEST['subscription_add_payment_amount'] > 0){
            if($customer_hack){
                $members_subscriptions = module_subscription::get_subscriptions_by_customer($member_id);
            }else{
                $members_subscriptions = module_subscription::get_subscriptions_by_member($member_id);
            }
            // we have an ammount! create an invoice for this amount/
            // assign it to a subscription (but not necessary!)
            $subscription_id = (int)$_REQUEST['subscription_add_payment'];
            if($subscription_id && !isset($members_subscriptions[$subscription_id])){
                die('Shouldnt happen');
            }
            $date = input_date($_REQUEST['subscription_add_payment_date']);
            $amount = $_REQUEST['subscription_add_payment_amount'];
            $amount_currency = module_config::c('subscription_currency',1);

            $data = array(
                'subscription_id' => $subscription_id,
                'member_id' => $member_id,
                'amount' => $amount,
                'currency_id' => $amount_currency,
                'invoice_id' => 0,
            );
            if($customer_hack){
                unset($data['member_id']);
                $data['customer_id'] = $member_id;
            }
            $subscription_history_id = update_insert('subscription_history_id',0,'subscription_history',$data);

            if($subscription_id){
                $subscription_data = self::get_subscription($subscription_id);
                $next_time = strtotime($date);
                $next_time = strtotime('+'.abs((int)$subscription_data['days']).' days',$next_time);
                $next_time = strtotime('+'.abs((int)$subscription_data['months']).' months',$next_time);
                $next_time = strtotime('+'.abs((int)$subscription_data['years']).' years',$next_time);
                $time_period = ' ('._l('%s to %s',print_date($date),print_date(strtotime("-1 day",$next_time))).')';
                $amount_currency = $subscription_data['currency_id'];
            }else{
                $time_period = '';
            }

            $invoice_data = module_invoice::get_invoice('new',true);
            if($customer_hack){
                $invoice_data['member_id'] = 0;
                $invoice_data['customer_id'] = $member_id;
            }else{
                $invoice_data['member_id'] = $member_id; // added in version 2.31 for invoice integration. eg: emailing invoice
                $invoice_data['customer_id'] = 0;
            }
            $invoice_data['user_id'] = 0;
            $invoice_data['currency_id'] = $amount_currency;
            $invoice_data['date_sent'] = '0000-00-00';
            $invoice_data['date_cancel'] = '0000-00-00';
            $invoice_data['date_create'] = $date;
            $invoice_data['date_due'] = $date;
            $invoice_data['name'] = 'S'.str_pad($subscription_history_id,6,'0',STR_PAD_LEFT);
            // pick a tax rate for this automatic invoice.
            $invoice_data['total_tax_name'] = module_config::c('subscription_invoice_tax_name','');
            $invoice_data['total_tax_rate'] = module_config::c('subscription_invoice_tax_rate','');

            $invoice_data['invoice_invoice_item']=array(
                'new' => array(
                    'description' => $members_subscriptions[$subscription_id]['name'] . $time_period,
                    'amount' => $amount,
                    'completed' => 1, // not needed?
                )
            );
            $invoice_id = module_invoice::save_invoice('new',$invoice_data);
            update_insert('subscription_history_id',$subscription_history_id,'subscription_history',array(
                'invoice_id'=>$invoice_id,
            ));
            module_invoice::add_history($invoice_id,'Created invoice from subscription #'.str_pad($subscription_history_id,6,'0',STR_PAD_LEFT).' from ID# '.$member_id);

            redirect_browser(module_invoice::link_open($invoice_id));

        }
    }


    // oldstyle hook handling, before hook registration
    public function handle_hook($hook){
        switch($hook){
            case "invoice_paid":
                $foo = func_get_args();
                $invoice_id = (int)$foo[1];
                if($invoice_id>0){
                    // see if any subscriptions match this invoice.
                    $invoice = module_invoice::get_invoice($invoice_id);
                    $subscription = get_single('subscription_history','invoice_id',$invoice_id);
                    if($subscription){
                        // mark subscription as paid and move onto the next date.
                        update_insert('subscription_history_id',$subscription['subscription_history_id'],'subscription_history',array(
                            'paid_date' => $invoice['date_paid'],
                        ));
                        if($subscription['customer_id']){
                            $this->update_next_due_date($subscription['subscription_id'],$subscription['customer_id'],true);
                        }else{
                            $this->update_next_due_date($subscription['subscription_id'],$subscription['member_id'],false);
                        }
                    }
                }

                break;

            case "home_alerts":
                $alerts = array();
                if(module_config::c('subscription_alerts',1)){

                    // find renewals due in a certain time.
                    $time = date('Y-m-d',strtotime('+'.module_config::c('alert_days_in_future',5).' days'));


                    $sql = "SELECT s.*, sm.*, m.* ";
                    $sql .= " FROM `"._DB_PREFIX."subscription_member` sm ";
                    $sql .= " LEFT JOIN `"._DB_PREFIX."subscription` s USING (subscription_id)";
                    $sql .= " LEFT JOIN `"._DB_PREFIX."member` m ON sm.member_id = m.member_id";
                    $sql .= " WHERE sm.next_due_date <= '".$time."'";
                    $sql .=  " AND sm.`deleted` = 0";
                    $items = qa($sql);
                    foreach($items as $item){
//                        echo '<hr>';print_r($item);echo '<hr>';
                        $alert_res = process_alert($item['next_due_date'], _l('Member Subscription Due'));
                        if($alert_res){
                            $alert_res['link'] = module_member::link_open($item['member_id']);
                            $alert_res['name'] = $item['first_name'];
                            $alerts[] = $alert_res;
                        }
                    }
//                    print_r($alerts);
                    $sql = "SELECT s.*, sm.*, m.* ";
                    $sql .= " FROM `"._DB_PREFIX."subscription_customer` sm ";
                    $sql .= " LEFT JOIN `"._DB_PREFIX."subscription` s USING (subscription_id)";
                    $sql .= " LEFT JOIN `"._DB_PREFIX."customer` m ON sm.customer_id = m.customer_id";
                    $sql .= " WHERE sm.next_due_date <= '".$time."'";
                    $sql .=  " AND sm.`deleted` = 0";
                    $items = qa($sql);
                    //print_r($items);
                    foreach($items as $item){
                        $alert_res = process_alert($item['next_due_date'], _l('Customer Subscription Due'));
                        if($alert_res){
                            $alert_res['link'] = module_customer::link_open($item['customer_id']);
                            $alert_res['name'] = $item['customer_name'];
                            $alerts[] = $alert_res;
                        }
                    }
                }
                return $alerts;

                break;
        }
    }

    public static function update_next_due_date($subscription_id,$member_id,$customer_hack=false){
        // todo
        $subscription = self::get_subscription($subscription_id);
        if($customer_hack){
            $history = self::get_subscription_history($subscription_id,false,$member_id);
            $link = array_shift(self::get_subscriptions_by_customer($member_id,$subscription_id));
        }else{
            $history = self::get_subscription_history($subscription_id,$member_id,false);
            $link = array_shift(self::get_subscriptions_by_member($member_id,$subscription_id));
        }
        if(!$link)return;

        $last_paid_date = $link['start_date'];

        $has_history = false;
        foreach($history as $h){
            if($h['paid_date']!='0000-00-00'){
                $has_history = true;
                $last_paid_date = $h['paid_date'];
                // find out when this invoice was due.
                // this is the date we go off.
                if($h['invoice_id']){
                    $invoice = module_invoice::get_invoice($h['invoice_id']);
                    $last_paid_date = $invoice['date_due'];
                }
            }
        }

        $next_time = strtotime($last_paid_date);
        if($has_history){
            $next_time = strtotime('+'.abs((int)$subscription['days']).' days',$next_time);
            $next_time = strtotime('+'.abs((int)$subscription['months']).' months',$next_time);
            $next_time = strtotime('+'.abs((int)$subscription['years']).' years',$next_time);
        }

        if($customer_hack){
            $sql = "UPDATE `"._DB_PREFIX."subscription_customer` SET `next_due_date` = '".date('Y-m-d',$next_time)."' WHERE `customer_id` = ".(int)$member_id." AND subscription_id = '".(int)$subscription_id."' LIMIT 1";
        }else{
            $sql = "UPDATE `"._DB_PREFIX."subscription_member` SET `next_due_date` = '".date('Y-m-d',$next_time)."' WHERE `member_id` = ".(int)$member_id." AND subscription_id = '".(int)$subscription_id."' LIMIT 1";
        }
        query($sql);
    }

    public static function member_edit_form($callback_name, $member_id){
        if(self::can_i('view','Subscriptions')){
            $customer_hack = false;
            include('hooks/member_edit.php');
        }
    }
    public static function customer_edit_form($callback_name, $member_id){
        if(self::can_i('view','Subscriptions')){
            $customer_hack = true;
            include('hooks/member_edit.php');
        }
    }
    public static function hook_invoice_sidebar($callback_name, $invoice_id){
        if((int)$invoice_id>0){
            // check if this invoice is linked to any subscription payments.
            $subscription = get_single('subscription_history','invoice_id',$invoice_id);
            if($subscription){
                include('hooks/invoice_sidebar.php');
            }

        }
    }
    public static function hook_invoice_deleted($callback_name, $invoice_id){
        if((int)$invoice_id>0){
            // check if this invoice is linked to any subscription payments.
            $subscription = get_single('subscription_history','invoice_id',$invoice_id);
            if($subscription){
                // remove this subscription payment from the subscription history
                delete_from_db('subscription_history','subscription_history_id',$subscription['subscription_history_id']);
            }

        }
    }
    public static function hook_member_deleted($callback_name, $member_id){
        if((int)$member_id>0){
            // check if this member is linked to any subscription payments.
            delete_from_db('subscription_history','member_id',$member_id);
            delete_from_db('subscription_member','member_id',$member_id);
        }
    }
    public static function hook_customer_deleted($callback_name, $customer_id){
        if((int)$customer_id>0){
            // check if this customer is linked to any subscription payments.
            delete_from_db('subscription_history','customer_id',$customer_id);
            delete_from_db('subscription_customer','customer_id',$customer_id);
        }
    }


    public static function get_invoice_listing($hook,$invoice_id,$full_finance_item){
        // check if this invoice id is a subscription payment
        $subscription = get_single('subscription_history','invoice_id',$invoice_id);
        if($subscription){
            if($subscription['customer_id']){
                $member_name = module_customer::link_open($subscription['customer_id'],true);
            }else{
                $member_name = module_member::link_open($subscription['member_id'],true);
            }
            $subscription_name = module_subscription::link_open($subscription['subscription_id'],true);
            $new_finance = array(
                'name' => _l('Subscription Payment'),
                'description' => _l('Payment from %s on subscription %s',$member_name,$subscription_name),
            );
            return $new_finance;
        }
    }
    public static function get_finance_recurring_items(){
        /**
         * next_due_date
         * url
         * type (i or e)
         * amount
         * currency_id
         * days
         * months
         * years
         * last_transaction_finance_id
         * account_name
         * categories
         * finance_recurring_id
         */
        // find list of all members.
        // then go through and fine list of all upcoming subscription payments.
        // add these ones (and future ones up to (int)module_config::c('finance_recurring_months',6) months from todays date.

        $end_date = strtotime("+".(int)module_config::c('finance_recurring_months',6).' months');


        $sql = "SELECT s.*, sm.*";
        $sql .= " FROM `"._DB_PREFIX."subscription_member` sm ";
        $sql .= " LEFT JOIN `"._DB_PREFIX."subscription` s USING (subscription_id)";
        $sql .= " WHERE sm.`deleted` = 0";
        $members =  qa($sql);
        $sql = "SELECT s.*, sc.*";
        $sql .= " FROM `"._DB_PREFIX."subscription_customer` sc ";
        $sql .= " LEFT JOIN `"._DB_PREFIX."subscription` s USING (subscription_id)";
        $sql .= " WHERE sc.`deleted` = 0";
        $customers =  qa($sql);
        //$members = module_member::get_members(array());
        $return = array();
        $items = array_merge($members,$customers);
        foreach($items as $member){

            if(isset($member['member_id']) && $member['member_id']){
                $subscriptions = module_subscription::get_subscriptions_by_member($member['member_id']);
            }else if(isset($member['customer_id']) && $member['customer_id']){
                $subscriptions = module_subscription::get_subscriptions_by_customer($member['customer_id']);
            }else{
                $subscriptions = array();
            }
            foreach($subscriptions as $subscription){

                $time = strtotime($subscription['next_due_date']);
                if(!$time)continue;

                if(isset($subscription['customer_id']) && $subscription['customer_id']){
                    $type = 'customer';
                    $member_name = module_customer::link_open($subscription['customer_id'],true);
                    $subscription_invoices = self::get_subscription_history($subscription['subscription_id'],false,$subscription['customer_id']);
                }else{
                    $type = 'member';
                    $member_name = module_member::link_open($subscription['member_id'],true);
                    $subscription_invoices = self::get_subscription_history($subscription['subscription_id'],$subscription['member_id'],false);
                }
                $subscription_name = module_subscription::link_open($subscription['subscription_id'],true);
                foreach($subscription_invoices as $subscription_invoice_id => $subscription_invoice){
                    if($subscription_invoice['invoice_id']){
                        $subscription_invoices[$subscription_invoice_id] = array_merge($subscription_invoice,module_invoice::get_invoice($subscription_invoice['invoice_id']));
                    }
                }


                $original=true;
                while($time < $end_date){
                    $next_time = 0;
                    if(!$subscription['days']&&!$subscription['months']&&!$subscription['years']){
                        // it's a once off..
                        // add it to the list but dont calculate the next one.

                    }else if(!$original){
                        // work out when the next one will be.
                        $next_time = $time;
                        $next_time = strtotime('+'.abs((int)$subscription['days']).' days',$next_time);
                        $next_time = strtotime('+'.abs((int)$subscription['months']).' months',$next_time);
                        $next_time = strtotime('+'.abs((int)$subscription['years']).' years',$next_time);
                        $time = $next_time;
                    }else{
                        $original = false;
                        // it's the original one.
                        $next_time = $time;
                    }

                    if($next_time){


                        // don't show it here if an invoice has already been generated.
                        // because invoice will already be in the list as outstanding
                        foreach($subscription_invoices as $subscription_invoice){
                            if(isset($subscription_invoice['date_create']) && $subscription_invoice['date_create'] == date('Y-m-d',$next_time)){
                                //echo 'match';
                                continue 2;
                            }
                        }

                        $return[] = array(
                            'next_due_date' => date('Y-m-d',$next_time), //$subscription['next_due_date'],
                            'url' => _l('Subscription: %s',$member_name),
                            'type' => 'i',
                            'amount' => $subscription['amount'],
                            'currency_id' => $subscription['currency_id'],
                            'days' => $subscription['days'],
                            'months' => $subscription['months'],
                            'years' => $subscription['years'],
                            'last_transaction_finance_id' => 0,
                            'account_name' => '',
                            'categories' => '',
                            'finance_recurring_id' => 0,
                            'last_transaction_text' => '(see member page)',
                            'end_date' => '0000-00-00',
                            'start_date' => $subscription['start_date'],
                            'recurring_text' => _l('Payment from %s %s on subscription %s',$type,$member_name,$subscription_name)
                        );
                    }
                }

            }
        }


        return $return;
    }


    public function get_upgrade_sql(){
        $sql = '';
        $fields = get_fields('subscription_history');
        if(!isset($fields['member_id'])){
            $sql .= 'ALTER TABLE `'._DB_PREFIX.'subscription_history` ADD `member_id` INT(11) NOT NULL DEFAULT \'0\' AFTER `subscription_id`;';
        }
        if(!isset($fields['customer_id'])){
            $sql .= 'ALTER TABLE `'._DB_PREFIX.'subscription_history` ADD `customer_id` INT(11) NOT NULL DEFAULT \'0\' AFTER `member_id`;';
        }
        $res = qa1("SHOW TABLES LIKE '"._DB_PREFIX."subscription_customer'");
        if(isset($_REQUEST['upgrade_debug'])){
            echo "SHOW TABLES LIKE '"._DB_PREFIX."subscription_customer'";
            var_export($res);exit;
        }
        if(!$res || !count($res)){
            $sql .= 'CREATE TABLE `'._DB_PREFIX.'subscription_customer` (
 `subscription_id` int(11) NOT NULL ,
  `customer_id` int(11) NOT NULL,
  `deleted` INT NOT NULL DEFAULT  \'0\',
`start_date` date NOT NULL,
`next_due_date` date NOT NULL COMMENT \'calculated in php when saving\',
  PRIMARY KEY  (`subscription_id`, `customer_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;';
        }
        return $sql;
    }
    public function get_install_sql(){
        ob_start();
        ?>

CREATE TABLE `<?php echo _DB_PREFIX; ?>subscription` (
  `subscription_id` int(11) NOT NULL auto_increment,
  `name` varchar(255) NOT NULL DEFAULT '',
  `days` int(11) NOT NULL DEFAULT '0',
  `months` int(11) NOT NULL DEFAULT '0',
  `years` int(11) NOT NULL DEFAULT '0',
  `amount` double(10,2) NOT NULL DEFAULT '0',
  `currency_id` INT NOT NULL DEFAULT '1',
  `date_created` date NOT NULL,
  `date_updated` date NULL,
  PRIMARY KEY  (`subscription_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

CREATE TABLE `<?php echo _DB_PREFIX; ?>subscription_member` (
  `subscription_id` int(11) NOT NULL ,
  `member_id` int(11) NOT NULL,
  `deleted` INT NOT NULL DEFAULT  '0',
`start_date` date NOT NULL,
`next_due_date` date NOT NULL COMMENT 'calculated in php when saving',
  PRIMARY KEY  (`subscription_id`, `member_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

CREATE TABLE `<?php echo _DB_PREFIX; ?>subscription_customer` (
  `subscription_id` int(11) NOT NULL ,
  `customer_id` int(11) NOT NULL,
  `deleted` INT NOT NULL DEFAULT  '0',
`start_date` date NOT NULL,
`next_due_date` date NOT NULL COMMENT 'calculated in php when saving',
  PRIMARY KEY  (`subscription_id`, `customer_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `<?php echo _DB_PREFIX; ?>subscription_history` (
`subscription_history_id` int(11) NOT NULL AUTO_INCREMENT,
`subscription_id` int(11) NOT NULL DEFAULT '0',
`member_id` int(11) NOT NULL DEFAULT '0',
`customer_id` INT(11) NOT NULL DEFAULT  '0',
`invoice_id` int(11) NOT NULL DEFAULT '0',
`amount` double(10,2) NOT NULL DEFAULT '0',
`currency_id` INT NOT NULL DEFAULT '1',
`paid_date` DATE NOT NULL,
`date_created` date NOT NULL,
`date_updated` date DEFAULT NULL,
PRIMARY KEY (`subscription_history_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


<?php
        return ob_get_clean();
    }


}
