<?php

$allowedFT = array('jpg','jpeg','png','gif','webp','svg');

function is_image($file) {
  global $allowedFT;
  $whitelist = array();
  foreach ($allowedFT AS &$ft) {
    if ($ft === 'svg') {
        $ft = $ft.'+xml';}
          $whitelist[] = 'image/'.$ft;
  }
  if(function_exists('finfo_open')){    //(PHP >= 5.3.0, PECL fileinfo >= 0.1.0)
     $fileinfo = finfo_open(FILEINFO_MIME_TYPE);
      if (!in_array(finfo_file($fileinfo, $file['tmp_name']), $whitelist)) {
        return false;
      } else {return true;}
  } else if(function_exists('mime_content_type')) {  //supported (PHP 4 >= 4.3.0, PHP 5)
      if (!in_array(mime_content_type($file['tmp_name']), $whitelist)) {
        return false;
      } else {return true;}
  } else {
     if (!@getimagesize($file['tmp_name'])) {  //@ - for hide warning when image not valid
        echo 'fails @ 3';
        return false;
     } else {return true;}
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

function uploadImage ($target_dir, $file, $w=false, $h=false, $wIsSoft=false, $hIsSoft=false, $setName=false, $storageLimit=false, $storedName=false) {
  global $root;
  global $allowedFT;
    $dir = $root.$target_dir;
    $msg = "<div class='error'><h2>Invalid Image Upload</h2>";
    //Check if the directory already exists.
    if(!is_dir($dir)){
        //Create directory if does not exist. 'True' makes this recursive.
        mkdir($dir, 0755, true);
    };

    $imageFileType = strtolower(pathinfo(basename($file["name"]),PATHINFO_EXTENSION));
    if ($setName) {
      $filename = $setName;
    } else {
      $filename = str_replace(".".$imageFileType, "", basename($file["name"]));
    }
    $target_file = $dir.$filename.".".$imageFileType;
    $valid = true;
    
    $check = getimagesize($file["tmp_name"]);
    $upldWidth = $check[0];
    $height = $check[1];
    
    // Check if image file is a actual image or fake image
    if($check && is_image($file)) {
    $valid = true;
    } else {
      $msg .= "File is not an image.<br/>";
    $valid = false;
    return false;
  };
 
    // Allow certain file formats
    if(!in_array($imageFileType, $allowedFT)) {
      $msg .= "'".$imageFileType."' files are not accepted.<br/>";
      $valid = false;
    };
    if ($storageLimit) {
      // convert kilobytes in $storage limit to bytes
      $storageLimit = ($storageLimit*1000);
      if ($file["size"] > $storageLimit) {
        $msg .= "Your file is too large (over ".formatSizeUnits($storageLimit)."). Save it at a smaller size or lower quality, and try again.<br/>";
        $valid = false;
      }
    };
    // if there is no $setName for this file, or the image being uploaded does not have the same name as the stored version
    if ((!$setName) || $storedName && ($target_dir.basename($file["name"]) != $storedName)) {
      // Check if filename already exists within folder
      if ($target_dir.basename($file["name"]) == $storedName) {
          $msg .= "<br/>The filenames are the same.";
      } else {
        $msg .= "<br/>The filenames are NOT the same.";
      }

      if (file_exists($dir.basename($file["name"]))) {
        $msg .= "Sorry, a file with this name already exists.<br/>";
        $valid = false;
      }
        };
    // if file is not an animated gif, check the size
    if ($imageFileType != 'gif' && !isAniGif($file["tmp_name"])) {
      if ($w || $h) {
        $sizeError = "";
        if ($w) {
          if (!$wIsSoft && ($upldWidth != $w)) {
            $sizeError .= $w.' pixels in width';
            $valid = false;
            } else if ($wIsSoft && ($upldWidth > $w)) {
            $sizeError .= 'less than '.$w.' pixels in width';
            $valid = false;
            };
        }
        if ($w && $h && $sizeError) {
            $sizeError .= ' and ';
            };
          if ($h) {
            if ($h && !$hIsSoft && $height != $h) {
              $sizeError .= $h.' pixels in height';
              $valid = false;
            } else if ($h && $hIsSoft && $height > $h) {
              $sizeError .= 'less than '.$h.' pixels in height';
              $valid = false;
            };
          }
        if ($sizeError && !$valid) {
            $sizeError= "Image must be ".$sizeError.".<br/>";
            $msg .= $sizeError;
        };
    }
  }
    // Check if $valid is false
    if (!$valid) {
      $_SESSION['Msg'] = $msg;
    // if everything is ok, try to upload file
    } else {
      if (move_uploaded_file($file["tmp_name"], $target_file)) {
            if (file_exists($dir.basename($file["name"]))) {
              return $target_dir.basename($file["name"]);
            } else {
              $msg = "<div class='error'><h2>Image Upload Failed</h2>There was an error uploading your image. Please try again.</div>";
              $_SESSION['Msg'] = $msg;
              return false;
            }
      } else {
        $msg = "<div class='error'><h2>Image Upload Failed</h2>There was an error uploading your image. Please try again.</div>";
        $_SESSION['Msg'] = $msg;
        return false;
      }
    }
  };


function mkThumb($dir, $destImage, $oriImage, $newW, $newH, $resizeRatio=false, $crop=false) {
  global $root;
  $oriImage = $root.$oriImage; 

  //get filetype
  $fileType = strtolower(pathinfo($oriImage,PATHINFO_EXTENSION));
  if($fileType == 'jpeg') {$fileType = 'jpg';}
  switch($fileType){
    case 'gif': $img = imagecreatefromgif($oriImage); break;
    case 'jpg': $img = imagecreatefromjpeg($oriImage); break;
    case 'png': $img = imagecreatefrompng($oriImage); break;
    case 'webp': $img = imagecreatefromwebp($oriImage); break;
    case '' : 
    case null : $_SESSION['Msg'] = "Image is invalid. Cannot create thumbnail."; return;
    default : $_SESSION['Msg'] = "Thumbnail creation does not support ".$fileType." images."; return;
  }
  $destImage = preg_replace("/[^A-Za-z0-9. \-_]/", '', $destImage);
  $destImage = str_replace(" ","-",$destImage);
  $destImage = str_replace('.'.$fileType, "_thumb.".$fileType, $destImage);
  $destImgPublic = $dir.$destImage;
  $destImage = $root.$dir.$destImage;

    // Get dimensions
    $check = getimagesize($oriImage);
    $oriW = $check[0];
    $oriH = $check[1];
    if (!$oriW || !$oriH) {
      $_SESSION['Msg'] = "Image is invalid. Cannot create thumbnail.";
      return;
    } elseif ($oriW < $newW || $oriH < $newH) {
      $_SESSION['Msg'] = "Uploaded image is smaller than the thumbnail size. Thumbnail could not be created.";
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
  if (!$destImgPublic) {
    $_SESSION['Msg'] = 'Thumbnail creation failed.';
    return false;
  } else {
    return $destImgPublic;
  }
}
