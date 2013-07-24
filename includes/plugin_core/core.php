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



class module_core extends module_base{

    public static function can_i($actions,$name=false,$category=false,$module=false){
        if(!$module)$module=__CLASS__;
        return parent::can_i($actions,$name,$category,$module);
    }
	public static function get_class() {
        return __CLASS__;
    }
	public function init(){
		$this->module_name = "core";
		$this->module_position = 0;

        $this->version = 2.12;
        //2.12 - 2013-05-08 - fix for static error on some php versions
        //2.11 - 2013-04-27 - fix for number rounding with international currency formats
        //2.1 - 2013-04-21 - initial release

	}

}

/* placeholder module to contain various functions used through out the system */
@include_once 'includes/functions.php'; // so we don't re-create old functions.


if(!function_exists('number_in')){
    function number_in($value){
        // convert a number in this format (eg: 1.234,56) to a system compatible format (eg: 1234.56)
        // only modify this number if it isn't already in db friendly format:
        $decimal_separator = module_config::c('currency_decimal_separator','.');
        $thounds_separator = module_config::c('currency_thousand_separator',',');
        $dec_positions = (int)module_config::c('currency_decimal_places',2);
        if( !is_numeric($value) || (float)$value != @number_format($value,$dec_positions,'.','')){
            //echo "Converting $value into ";
            $value = str_replace($thounds_separator,'',$value);
            if($decimal_separator!='.'){
                $value = str_replace($decimal_separator,'.',$value);
            }
            //echo "$value <br>";
        }
        return $value;
    }
}

if(!function_exists('number_out')){
    function number_out($value){
        $decimal_separator = module_config::c('currency_decimal_separator','.');
        $thounds_separator = module_config::c('currency_thousand_separator',',');
        $dec_positions = module_config::c('currency_decimal_places',2);
        return number_format($value,$dec_positions,$decimal_separator,$thounds_separator);
    }
}
if(!function_exists('dollar')){
    function dollar($number,$show_currency=true,$currency_id=false){
        return currency(number_out($number),$show_currency,$currency_id);
    }
}
if(!function_exists('currency')){
    function currency($data,$show_currency=true,$currency_id=false){
        // find the default currency.
        if(!defined('_DEFAULT_CURRENCY_ID')){
            $default_currency_id = module_config::c('default_currency_id',1);
            foreach(get_multiple('currency','','currency_id') as $currency){
                if($currency['currency_id']==$default_currency_id){
                    define('_DEFAULT_CURRENCY_ID',$default_currency_id);
                    define('_DEFAULT_CURRENCY_SYMBOL',$currency['symbol']);
                    define('_DEFAULT_CURRENCY_LOCATION',$currency['location']);
                    define('_DEFAULT_CURRENCY_CODE',$currency['code']);
                }
            }
        }
        $currency_symbol = defined('_DEFAULT_CURRENCY_SYMBOL') ? _DEFAULT_CURRENCY_SYMBOL : '$';
        $currency_location = defined('_DEFAULT_CURRENCY_LOCATION') ? _DEFAULT_CURRENCY_LOCATION : 1;
        $currency_code = defined('_DEFAULT_CURRENCY_CODE') ? _DEFAULT_CURRENCY_CODE : 'USD';
        $show_name = false;

        if($currency_id && defined('_DEFAULT_CURRENCY_ID') && $currency_id != _DEFAULT_CURRENCY_ID){
            if($show_currency){
                $show_name = true;
            }
            $currency = get_single('currency','currency_id',$currency_id);
            if($currency){
                $currency_symbol = $currency['symbol'];
                $currency_location = $currency['location'];
                $currency_code = $currency['code'];
            }
            /*
            foreach(get_multiple('currency','','currency_id') as $currency){
                if($currency['currency_id']==$currency_id){
                    $currency_symbol = $currency['symbol'];
                    $currency_location = $currency['location'];
                    $currency_code = $currency['code'];
                }
            }*/
        }
        /*$currency_location = module_config::c('currency_location','before');
        $currency_code = module_config::c('currency','$');
        $currency_name = module_config::c('currency_name','USD');*/

        switch(strtolower($currency_symbol)){
            case "yen":
                $currency_symbol = '&yen;';
                break;
            case "eur":
                $currency_symbol = '&euro;';
                break;
            case "gbp":
                $currency_symbol = '&pound;';
                break;
            default:
                break;
        }

        if(!$show_currency){
            $currency_symbol = '';
        }
        if(module_config::c('currency_show_code_always',0)){
            $data .= ' '.$currency_code;
        }else if($show_name && module_config::c('currency_show_non_default',1)){
            $data .= ' '.$currency_code;
        }

        switch($currency_location){
            case 'after':
            case 0:
                return $data.$currency_symbol;
                break;
            case 1:
            default:
                return $currency_symbol.$data;
        }
    }
}