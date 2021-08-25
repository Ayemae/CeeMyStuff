<?php

$allowedFT = array('jpg','jpeg','png','gif','webp','svg');

function is_image($file) {
  global $allowedFT;
  $whitelist = array();
  foreach ($allowedFT AS $ft) {
    if ($ft === 'svg') ($ft = $ft.'+xml');
          $$whitelist[] = 'image/'.$ft;
  }
  if(function_exists('finfo_open')){    //(PHP >= 5.3.0, PECL fileinfo >= 0.1.0)
     $fileinfo = finfo_open(FILEINFO_MIME_TYPE);
      if (!in_array(finfo_file($fileinfo, $file['tmp_name']), $whitelist)) {
        return false;
      }
      }else if(function_exists('mime_content_type')){  //supported (PHP 4 >= 4.3.0, PHP 5)
      if (!in_array(mime_content_type($file['tmp_name']), $whitelist)) {
        return false;
      }
      }else{
     if (!@getimagesize($file['tmp_name'])) {  //@ - for hide warning when image not valid
        return false;
     }
     return true;
  }
}

// check if animated gif
function isAniGif($filename) {
  if(!($fh = @fopen($filename, 'rb')))
      return false;
  $count = 0;
  //an animated gif contains multiple "frames", with each frame having a
  //header made up of:
  // * a static 4-byte sequence (\x00\x21\xF9\x04)
  // * 4 variable bytes
  // * a static 2-byte sequence (\x00\x2C) (some variants may use \x00\x21 ?)
 
  // We read through the file until we reach the end of the file, or we've found
  // at least 2 frame headers
  while(!feof($fh) && $count < 2) {
      $chunk = fread($fh, 1024 * 100); //read 100kb at a time
      $count += preg_match_all('#\x00\x21\xF9\x04.{4}\x00(\x2C|\x21)#s', $chunk, $matches);
 }
  fclose($fh);
  return $count > 1;
}

function uploadImage ($target_dir, $file, $w=false, $h=false, $wIsSoft=false, $hIsSoft=false, $setName=false, $storageLimit=false) {
    $dir = $_SERVER['DOCUMENT_ROOT'].$target_dir;
    global $allowedFT;
    $msg = "<div class='error'><h2>Invalid Image Upload</h2>";
    //Check if the directory already exists.
    if(!is_dir($dir)){
        //Create directory if does not exist. 'True' makes this recursive.
        mkdir($dir, 0755, true);
    };
    
    $target_file = $dir . basename($file["name"]);
    $valid = true;
    $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));

    if ($setName) {
      $setName = preg_replace("/[^A-Za-z0-9 \-_]/", '', $setName);
      $setName = str_replace(" ","-",$setName);
      $target_file = $dir.$setName.".".$imageFileType;
  }
    
    $check = getimagesize($file["tmp_name"]);
    $width = $check[0];
    $height = $check[1];
    
    // Check if image file is a actual image or fake image
    if($check && is_image($file["tmp_name"])) {
    $valid = true;
    } else {
      $msg .= "File is not an image.<br/>";
    $valid = false;
  };
 
    // Allow certain file formats
    if(!in_array($imageFileType, $allowedFT)) {
      $msg .= "'".$imageFileType."' files are not accepted.<br/>";
      $valid = false;
    };
    if ($storageLimit && ($file["size"] > $storageLimit)) {
      $msg .= "Your file is too large (over ".formatSizeUnits($storageLimit)."). Save it at a smaller size or lower quality, and try again.<br/>";
      $valid = false;
    };
    // if there is no $setName for this file
    if ($file_input["tmp_name"] && !$setName) {
      // Check if filename already exists within folder
      if (file_exists($target_file)) {
        $errorMsg .= "Sorry, a file with this name already exists.<br/>";
        $uploadOk = false;
      }
        };
    // if file is not an animated gif, check the size
    if ($imageFileType !== 'gif' && isAniGif($file["tmp_name"]) == false) {
      if ($w || $h) {
        $sizeError = "";
        if ($w && !$wIsSoft && $width != $w) {
            $sizeError = $sizeError.''.$w.' pixels in width';
            $valid = false;
            };
        if ($w && $wIsSoft && $width < $w) {
            $sizeError = $sizeError.'less than '.$w.' pixels in width';
            $valid = false;
            };
        if ($w && $h) {
            $sizeError = $sizeError.' and ';
            };
        if ($h && !$hIsSoft && $height != $h) {
            $sizeError = $sizeError.''.$h.' pixels in height';
            $valid = false;
            };
        if ($h && $hIsSoft && $height < $h) {
            $sizeError = $sizeError.'less than '.$h.' pixels in height';
            $valid = false;
            };
        if ($sizeError && $valid == 0) {
            $sizeError= "Image must be ".$sizeError.".<br/>";
            $msg .= $sizeError;
        };
    } elseif ($width > 1800 || $height > 6000) {
      $msg .= 'Uploaded images can be no bigger than 1800 pixels in width or 6000 pixels in height.';
        $valid = false;
    }
  }
    // Check if $valid is false
    if (!$valid) {
      $msg .= "Your image could not be uploaded.</div>";
      echo $msg;
    // if everything is ok, try to upload file
    } else {
      if (move_uploaded_file($file["tmp_name"], $target_file)) {
            return $target_dir.basename($file["name"]);
      } else {
        $msg = "<div class='error'><h2>Image Upload Failed</h2>There was an error uploading your image. Please try again.</div>";
        return false;
      }
    }
  };


function copyResizeImage($dir, $destImage, $oriImage, $newW, $newH, $resizeRatio=false, $crop=false) {
  $destImgPublic = $dir.$destImage;
  $destImage = preg_replace("/[^A-Za-z0-9. \-_]/", '', $destImage);
  $destImage = str_replace(" ","-",$destImage);
  $destImage = $_SERVER['DOCUMENT_ROOT'].$dir.$destImage;
  $oriImage = $_SERVER['DOCUMENT_ROOT'].$oriImage; 

  //get filetype
  $fileType = strtolower(pathinfo($oriImage,PATHINFO_EXTENSION));
  if($fileType == 'jpeg') $fileType = 'jpg';
  switch($fileType){
    case 'gif': $img = imagecreatefromgif($oriImage); break;
    case 'jpg': $img = imagecreatefromjpeg($oriImage); break;
    case 'png': $img = imagecreatefrompng($oriImage); break;
    case 'webp': $img = imagecreatefromwebp($oriImage); break;
    default : echo "Unsupported picture type!"; return;
  }
    // Get dimensions
    $check = getimagesize($oriImage);
    $oriW = $check[0];
    $oriH = $check[1];
    if($oriW < $newW || $oriH < $newH) {
      echo "Uploaded image is too small.";
      return;
    }

    if (!$newW && $newH) {
      $percentDiff = ($newH/$oriH);
      $newW = round($oriW*$percentDiff);
    } else if ($newW && !$newH) {
      $percentDiff = ($newW/$oriW);
      $newH = round($oriH*$percentDiff);
    }

    if($crop){
      $resizeRatio = max($newW/$oriW, $newH/$oriH);
      $oriH = $newH / $resizeRatio;
      $x = ($oriW - $newW / $resizeRatio) / 2;
      $oriW = $newW / $resizeRatio;
    }
    else if ($resizeRatio) {
      $resizeRatio = min($newW/$oriW, $newH/$oriH);
      $newW = $oriW * $resizeRatio;
      $newH = $oriH * $resizeRatio;
      $x = 0;
    } else {
      $x = 0;
    }

    $newImg = imagecreatetruecolor($newW, $newH);

    // preserve transparency
    if($fileType == "gif" or $fileType == "png"){
    imagecolortransparent($newImg, imagecolorallocatealpha($newImg, 0, 0, 0, 127));
    imagealphablending($newImg, false);
    imagesavealpha($newImg, true);
    }

  imagecopyresampled($newImg, $img, 0, 0, $x, 0, $newW, $newH, $oriW, $oriH);

  switch($fileType){
    case 'gif': imagegif($newImg, $destImage); break;
    case 'jpg': imagejpeg($newImg, $destImage); break;
    case 'png': imagepng($newImg, $destImage); break;
    case 'webp': imagewebp($newImg, $destImage); break;
  }
  return $destImgPublic;
}
