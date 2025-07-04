<?php

function auto_level($immagine, $modo = 2, $dinamica = 254)
{

  ## Name: Normalize_img
  ## Description: PHP script for normalizing automatically the color in an image.
  ##              It uses GD Libraries.
  ## Copyright: Isacco Coccato - www.giacobbe85.altervista.org
  ## Version: 1.0

  ##############################################
  ### PART 1: RGB minimum and maximum search ###
  ##############################################

  # Variables initialization
  $red_min = 255;
  $green_min = 255;
  $blue_min = 255;
  $red_max = 0;
  $green_max = 0;
  $blue_max = 0;

  # Scans each image pixel
  for ($x = 0; $x < imagesx($immagine); $x++) {
    for ($y = 0; $y < imagesy($immagine); $y++) {
      # Computes RGB for the current pixel
      $pixel_corrente = imagecolorat($immagine, $x, $y);
      $red = ($pixel_corrente >> 16) & 255;
      $green = ($pixel_corrente >> 8) & 255;
      $blue = $pixel_corrente & 255;

      # Searches for the minimum
      if ($red < $red_min) $red_min = $red;
      if ($green < $green_min) $green_min = $green;
      if ($blue < $blue_min) $blue_min = $blue;

      # Searches for the maximum
      if ($red > $red_max) $red_max = $red;
      if ($green > $green_max) $green_max = $green;
      if ($blue > $blue_max) $blue_max = $blue;
    }
  }

  #######################################
  ### PART 2: normalization algorithm ###
  #######################################

  # Normalization with method 2
  $sposta_r = $red_min;
  $sposta_g = $green_min;
  $sposta_b = $blue_min;

  if ($red_max != $sposta_r) $scala_r = $dinamica / ($red_max - $sposta_r);
  else $scala_r = 1;
  if ($green_max != $sposta_g) $scala_g = $dinamica / ($green_max - $sposta_g);
  else $scala_g = 1;
  if ($blue_max != $sposta_b) $scala_b = $dinamica / ($blue_max - $sposta_b);
  else $scala_b = 1;

  # Normalization with method 1
  if ($modo == 1) {
    $sposta = min($sposta_r, $sposta_g, $sposta_b);
    if (max($red_max, $green_max, $blue_max) != $sposta) $scala = $dinamica / (max($red_max, $green_max, $blue_max) - $sposta);
    else $scala = 1;

    $sposta_r = $sposta;
    $sposta_g = $sposta;
    $sposta_b = $sposta;
    $scala_r = $scala;
    $scala_g = $scala;
    $scala_b = $scala;
  }

  #############################
  ### PART 3: Normalization ###
  #############################

  # Scans each pixel to normalize it
  for ($x = 0; $x < imagesx($immagine); $x++) {
    for ($y = 0; $y < imagesy($immagine); $y++) {
      # Computes the RGB values for the current pixel
      $pixel_corrente = imagecolorat($immagine, $x, $y);
      $red = ($pixel_corrente >> 16) & 255;
      $green = ($pixel_corrente >> 8) & 255;
      $blue = $pixel_corrente & 255;

      # Computes the normalized pixel and saves it in the image
      $colore_pixel = imagecolorallocate($immagine, ($red - $sposta_r) * $scala_r, ($green - $sposta_g) * $scala_g, ($blue - $sposta_b) * $scala_b);
      imagesetpixel($immagine, $x, $y, $colore_pixel);
    }
  }
  return 1;
}


/**
 *  PHP Image Resize
 *
 *  This file automatically generates the correctly sized image,
 *  caches it for future use, and displays it.
 *
 *  Supports jpg, gif, and png
 *
 *  Nathan Gardner <nathan@factory8.com>
 *  Factory8.com
 *  September 23rd, 2010
 *
 *  Released under the MIT, BSD, and GPL Licenses.
 *
 *  ----------------
 *
 *  Usage:
 *  image.php?f=full/path.jpg&w=width&h=height[&effect=(bestfit,crop,stretch)]
 *
 *  f = filename of the image, including directories (/absolute/is/best)
 *  w = the width of the image
 *  h = the height of the image
 *  effect = the resize effect. This is optional, defaults to bestfit. Other possible values are crop and stretch
 *
 *  w and h are both required if the effect is crop or stretch, otherwise only one of them is required
 *
 *  Example:
 *  <img src="/image.php?f=/images/myimage.jpg&w=180&h=135&effect=crop"/>
 *
 *  Limitations:
 *  Although the input file can be jpg, gif, or png - the output file
 *  will always be a jpg, so you cannot have transparencies or animations
 *
 */

// SETTINGS 
ini_set('display_errors', 0);
$cacheDir = $_SERVER['DOCUMENT_ROOT'] . '/ai-cache/';
$imageQuality = 70; // 0 (bad quality, small file) to 100 (high quality, big file)

// echo $cacheDir;
// exit;

//Clear cache
if (isset($_GET['clear'])) {
  $files = glob($cacheDir . '*', GLOB_BRACE);
  foreach ($files as $file) {
    if (is_file($file))
      unlink($file); // delete file
    echo 'cleared ' . $file . '<br>';
  }
  exit;
}

##############################################################################
##############################################################################

// INSTALL
if (!is_dir($cacheDir)) {
  mkdir($cacheDir, 0777, true);
}

##############################################################################
##############################################################################

// SETUP
$orignalImage = !empty($_GET['f']) ? $_GET['f'] : 0;

if (substr($orignalImage, 0, 1) == '/') {
  $orignalImage = substr($orignalImage, 1);
}

$maxWidth = !empty($_GET['w']) ? intval($_GET['w']) : 0;
$maxHeight = !empty($_GET['h']) ? intval($_GET['h']) : 0;
$effect = !empty($_GET['effect']) ? $_GET['effect'] : 'bestfit';
$blur   = !isset($_GET['blur']) ? 'blur' : '';
$gray   = !isset($_GET['gray']) ? 'gray' : '';
$nocorrect = !isset($_GET['nocorrect']) ? 'nocorrect' : '';


// foreach($_GET as $GET){
//   echo $GET.'<br>';
// }
##############################################################################
##############################################################################

// PROCESS
if (file_exists($orignalImage) && is_file($orignalImage)) {

  $cacheFile = md5($orignalImage . $maxWidth . $maxHeight . $effect . $blur . $gray . $nocorrect) . (getimagesize($orignalImage)[2] == 3 ? '.png' : '.jpg');

  // if width and height arent set, effect gets set to bestfit
  if (empty($maxWidth) || empty($maxHeight)) {
    $effect = 'bestfit';
  }

  // see if we have a cached version, and that the orignal image has not been updated
  if (!file_exists($cacheDir . $cacheFile) || filemtime($orignalImage) > filemtime($cacheDir . $cacheFile)) {

    $imageInfo = getimagesize($orignalImage);
    $orignalWidth = intval($imageInfo[0]);
    $orignalHeight = intval($imageInfo[1]);
    $orignalType = intval($imageInfo[2]);
    $orignalRatio = $orignalWidth / $orignalHeight;

    // determine output width and height
    switch ($effect) {

      case "crop":
      case "stretch":
        $width = $maxWidth;
        $height = $maxHeight;
        break;

      case "bestfit":
      default:

        if ($maxWidth && $maxHeight) {

          if ($orignalWidth > $orignalHeight) {

            $width = $maxWidth;
            $height = $maxWidth * ($orignalHeight / $orignalWidth);
          } else {

            $height = $maxHeight;
            $width = $maxHeight * ($orignalWidth / $orignalHeight);
          }

          if ($height > $maxHeight) {

            $height = $maxHeight;
            $width = $maxHeight * ($orignalWidth / $orignalHeight);
          }
        } else {

          if ($maxWidth) {

            $width = $maxWidth;
            $height = $maxWidth * ($orignalHeight / $orignalWidth);
          } else {

            $height = $maxHeight;
            $width = $maxHeight * ($orignalWidth / $orignalHeight);
          }
        }

        break;
    }

    $newRatio = $width / $height;

    // load in the orignal image
    switch ($orignalType) {

      case 1:
        $loadedImage = imagecreatefromgif($orignalImage);
        break;
      case 2:
        $loadedImage = imagecreatefromjpeg($orignalImage);
        break;
      case 3:
        $loadedImage = imagecreatefrompng($orignalImage);
        break;
    }

    //    if ($maxWidth >=  2000 || $maxHeight >= 2000) {
    //         outputError( 250, 100, $_SERVER['REMOTE_ADDR']." Seriously ?");
    //         exit();
    //      }

    if ($loadedImage) {

      // create new image
      $newImage = imagecreatetruecolor($width, $height);

      // put orignal image in new image
      switch ($effect) {

        case 'bestfit':
        case 'stretch':
          imagecopyresampled($newImage, $loadedImage, 0, 0, 0, 0, $width, $height, $orignalWidth, $orignalHeight);
          break;

        case 'crop':

          if ($newRatio > $orignalRatio) {

            $start_x = 0;
            $crop_width = $orignalWidth;
            $crop_height = $crop_width * ($height / $width);
            $start_y = ($orignalHeight - $crop_height) / 2;
          } else {

            $start_y = 0;
            $crop_height = $orignalHeight;
            $crop_width = $crop_height * $newRatio;
            $start_x = ($orignalWidth - $crop_width) / 2;
          }
          imagecopyresampled($newImage, $loadedImage, 0, 0, $start_x, $start_y, $width, $height, $crop_width, $crop_height);

          break;
      }
      /****************************************************
       *
       *
       *   EDIT : ADDED WATERMARK
       *
       *
       ******************************************************/
      //Add Watermark
      if (isset($_GET['mark'])) {
        $text_color = imagecolorallocate($newImage, 113, 113, 108);
        $watermarktext = $_GET['mark'];
        imagestring($newImage, 3, $width - ($width / 1.02), $height - ($height / 20),  $watermarktext, $text_color);
      }

      /*  
      if($maxWidth >  2000) {
          $maxWidth->clear();
      }
      if($maxHeight >  2000) {
          $maxHeight->clear();
      }*/

      /****************************************************
       *
       *
       *   EDIT : ADDED GRAYSCALE (à rajouter les filtres dans l'URL et dans le nom de fichier)
       *
       *
       ******************************************************/
      //add Grayscale
      if (isset($_GET['gray'])) {
        imagefilter($newImage, IMG_FILTER_GRAYSCALE);
        if (isset($_GET['multiply'])) {
          //multiply here
          //add background-color + blend
        }
      }

      /****************************************************
       *
       *
       *   EDIT :COLOR REPLACEMENT
       *
       *
       ******************************************************/
      function ReplaceColour($img, $r1, $g1, $b1, $r2, $g2, $b2)
      {
        if (!imageistruecolor($img))
          imagepalettetotruecolor($img);
        $col1 = (($r1 & 0xFF) << 16) + (($g1 & 0xFF) << 8) + ($b1 & 0xFF);
        $col2 = (($r2 & 0xFF) << 16) + (($g2 & 0xFF) << 8) + ($b2 & 0xFF);

        $width = imagesx($img);
        $height = imagesy($img);
        for ($x = 0; $x < $width; $x++)
          for ($y = 0; $y < $height; $y++) {
            $colrgb = imagecolorat($img, $x, $y);
            if ($col1 !== $colrgb)
              continue;
            imagesetpixel($img, $x, $y, $col2);
          }
      }
      if (isset($_GET['rc'])) {
        $index = imagecolorclosest($newImage,  255, 255, 255); // get White COlor
        imagecolorset($newImage, $index, 92, 92, 92); // SET NEW COLOR
      }
      /****************************************************
       *
       *
       *   EDIT : ADDED BLUR
       *
       *
       ******************************************************/
      if (isset($_GET['blur'])) {
        $image = $newImage;

        /* Get original image size */

        $w = $width;
        $h = $height;
        /* Create array with width and height of down sized images */
        $size = array(
          'sm' => array('w' => intval($w / 4), 'h' => intval($h / 4)),
          'md' => array('w' => intval($w / 2), 'h' => intval($h / 2))
        );
        /* Scale by 25% and apply Gaussian blur */
        $sm = imagecreatetruecolor($size['sm']['w'], $size['sm']['h']);
        imagecopyresampled($sm, $image, 0, 0, 0, 0, $size['sm']['w'], $size['sm']['h'], $w, $h);

        for ($x = 1; $x <= 3.5; $x++) { //40
          imagefilter($sm, IMG_FILTER_GAUSSIAN_BLUR, 999);
        }
        imagefilter($sm, IMG_FILTER_SMOOTH, 99);
        imagefilter($sm, IMG_FILTER_BRIGHTNESS, 10);

        /* Scale result by 200% and blur again */
        $md = imagecreatetruecolor($size['md']['w'], $size['md']['h']);
        imagecopyresampled($md, $sm, 0, 0, 0, 0, $size['md']['w'], $size['md']['h'], $size['sm']['w'], $size['sm']['h']);
        imagedestroy($sm);

        for ($x = 1; $x <= 2.5; $x++) { //25
          imagefilter($md, IMG_FILTER_GAUSSIAN_BLUR, 999);
        }
        imagefilter($md, IMG_FILTER_SMOOTH, 99);
        imagefilter($md, IMG_FILTER_BRIGHTNESS, 10);

        /* Scale result back to original size */
        imagecopyresampled($image, $md, 0, 0, 0, 0, $w, $h, $size['md']['w'], $size['md']['h']);
        imagedestroy($md);

        // Apply filters of upsized image if you wish, but probably not needed
        //imagefilter($image, IMG_FILTER_GAUSSIAN_BLUR); 
        //imagefilter($image, IMG_FILTER_SMOOTH,99);
        //imagefilter($image, IMG_FILTER_BRIGHTNESS, 10);       
      }


      /****************************************************
       *
       *
       *   EDIT : ADDED INTERLACE
       *
       *
       ******************************************************/

      // Activation de l'entrelacement
      imageinterlace($newImage, true);

      /****************************************************
       *
       *
       *   EDIT : ADDED COLOR CORRECTION
       *
       *
       ******************************************************/

      //Image color Correction

      if (!isset($_GET['nocorrect'])) {
        auto_level($newImage, 2, 254);
      }
      /****************************************************
       *
       *
       *   EDIT : FIX EXIF ROTATION
       *
       *
       ******************************************************/
      //Fix EXIF Rotation
      if (function_exists('exif_read_data')) {
        //read exif from original image
        $exif = exif_read_data($orignalImage);
        if ($exif && isset($exif['Orientation'])) {
          $orientation = $exif['Orientation'];
          if ($orientation != 1) {

            $deg = 0;
            switch ($orientation) {
              case 3:
                $deg = 180;
                break;
              case 6:
                $deg = 270;
                break;
              case 8:
                $deg = 90;
                break;
            }
            if ($deg) {
              $newImage = imagerotate($newImage, $deg, 0);
            }
          }
        }
      }

      // save to cache folder
      if (getimagesize($orignalImage)[2] == 3) {

        imagepng($newImage, $cacheDir . $cacheFile);
      } else {
        imagejpeg($newImage, $cacheDir . $cacheFile, $imageQuality);
      }

      // display it
      outputImage($cacheDir . $cacheFile);
    } else {

      outputError($maxWidth, $maxHeight, 'Image format not supported.');
    }
  } else {

    // have cached version, display it!
    outputImage($cacheDir . $cacheFile);
  }
} else {

  outputError($maxWidth, $maxHeight, $effect . '/' . $maxWidth . '/' . $maxHeight . '/' . $orignalImage);
}

##############################################################################
##############################################################################

function outputError($width, $height, $errorMsg)
{

  if (empty($width)) {

    $width = $height;
  }

  if (empty($height)) {

    $height = $width;
  }

  if (empty($width)) {

    $width = 160;
    $height = 53;
  }

  $errorImage = imagecreate($width, $height);
  $background = imagecolorallocate($errorImage, 230, 230, 230);
  $black = imagecolorallocate($errorImage, 0, 0, 0);
  header('Content-type: image/jpeg');
  imagestring($errorImage, 2, 10, 10, $errorMsg, $black);
  imagejpeg($errorImage);
  imagedestroy($errorImage);
  exit();
}

function outputImage($fileName)
{
  global $orignalImage;
  header('Content-type: image/' . (getimagesize($orignalImage)[2] == 3 ? 'png' : 'jpeg'));
  //setup cache expire headers
  $lastModified = gmdate('D, d M Y H:i:s', filemtime($fileName)) . ' GMT';
  $etag = md5_file($fileName);
  header('Last-Modified: ' . $lastModified);
  header('Etag: ' . $etag);
  header('Cache-Control: public');
  echo file_get_contents($fileName);
  exit();
}
