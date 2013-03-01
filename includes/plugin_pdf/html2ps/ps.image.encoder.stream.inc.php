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


/**
 * @author Konstantin Bournayev
 * @version 1.0
 * @created 24-џэт-2006 20:56:23
 */
class PSImageEncoderStream
{
  var $last_image_id;

  // Generates new unique image identifier
  // 
  // @return generated identifier
  //
  function generate_id()
	{
    	$this->last_image_id ++;
    	return $this->last_image_id;
	}

}

/**
 * @created 24-џэт-2006 20:56:23
 * @author Konstantin Bournayev
 * @version 1.0
 * @updated 24-џэт-2006 21:19:35
 */
class PSImageEncoder
{

	var $last_image_id;

	function __construct()
	{
	}



	/**
	 * Generates new unique image identifier
	 * @return generated identifier
	 */
	function generate_id()
	{
	}

}
?>