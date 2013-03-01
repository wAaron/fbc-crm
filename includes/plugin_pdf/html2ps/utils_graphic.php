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
// $Header: /cvsroot/html2ps/utils_graphic.php,v 1.9 2007/01/24 18:56:10 Konstantin Exp $

function do_image_open($filename, &$type) {
  // Gracefully process missing GD extension
  if (!extension_loaded('gd')) {
    return null;
  };

  // Disable interlacing for the generated images, as we do not need progressive images 
  // if PDF files (futhermore, FPDF does not support such images)
  $image = do_image_open_wrapped($filename, $type);
  if (!is_resource($image)) { return null; };

  if (!is_null($image)) {
    imageinterlace($image, 0);
  };

  return $image;
}

function do_image_open_wrapped($filename, &$type) {
  // FIXME: it will definitely cause problems;
  global $g_config;
  if (!$g_config['renderimages']) {
    return null;
  };

  // get the information about the image
  if (!$data = @getimagesize($filename)) { return null; };
  switch ($data[2]) {
  case 1: // GIF
    $type = 'image/png';
    // Handle lack of GIF support in older versions of PHP
    if (function_exists('imagecreatefromgif')) {
      return @imagecreatefromgif($filename);
    } else {
      return null;
    };
  case 2: // JPG
    $type = 'image/jpeg';
    return @imagecreatefromjpeg($filename);
  case 3: // PNG
    $type = 'image/png';
    $image = imagecreatefrompng($filename);
//     imagealphablending($image, false);
//     imagesavealpha($image, true);
    return $image;
  case 15: // WBMP
    $type = 'image/png';
    return @imagecreatefromwbmp($filename);
  };
  return null;
};
?>