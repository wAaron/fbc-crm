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

class Stripe_Charge extends Stripe_ApiResource
{
  public static function constructFrom($values, $apiKey=null)
  {
    $class = get_class();
    return self::scopedConstructFrom($class, $values, $apiKey);
  }

  public static function retrieve($id, $apiKey=null)
  {
    $class = get_class();
    return self::_scopedRetrieve($class, $id, $apiKey);
  }

  public static function all($params=null, $apiKey=null)
  {
    $class = get_class();
    return self::_scopedAll($class, $params, $apiKey);
  }

  public static function create($params=null, $apiKey=null)
  {
    $class = get_class();
    return self::_scopedCreate($class, $params, $apiKey);
  }
    
  public function save()
  {
    $class = get_class();
    return self::_scopedSave($class);
  }

  public function refund($params=null)
  {
    $requestor = new Stripe_ApiRequestor($this->_apiKey);
    $url = $this->instanceUrl() . '/refund';
    list($response, $apiKey) = $requestor->request('post', $url, $params);
    $this->refreshFrom($response, $apiKey);
    return $this;
  }

  public function capture($params=null)
  {
    $requestor = new Stripe_ApiRequestor($this->_apiKey);
    $url = $this->instanceUrl() . '/capture';
    list($response, $apiKey) = $requestor->request('post', $url, $params);
    $this->refreshFrom($response, $apiKey);
    return $this;
  }

  public function updateDispute($params=null)
  {
    $requestor = new Stripe_ApiRequestor($this->_apiKey);
    $url = $this->instanceUrl() . '/dispute';
    list($response, $apiKey) = $requestor->request('post', $url, $params);
    $this->refreshFrom(array('dispute' => $response), $apiKey, true);
    return $this->dispute;
  }
}