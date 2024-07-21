<?php

// check if animated gif
// from: https://stackoverflow.com/questions/280658/can-i-detect-animated-gifs-using-php-and-gd
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
  
  function withinStorageLimit($fileSize,$lim=false) {
    if ($lim) {
      // convert megabytes in $lim to bytes
      $lim = ((int)$lim*1000000);
      if ($fileSize>$lim) {
        return false;
      }
    }
    return true;
  }
  
  function formatFileInfo($file,$tarDir,$oldName=null,$newName=null, $currDir=null) {
    // if file is not an upload, make sure to include full path
    $info=array();
    if (!is_null($oldName) && $oldName) {
        $oldName=trim($oldName);
    }
    if (isset($file['tmp_name'])) {
        $isUpload = is_uploaded_file($file['tmp_name']);
        $fileName = $file['name'];
        $filePath = $file['tmp_name'];
    } else {
        global $root;
        $isUpload = false;
        $filePath =  $tarDir.'/'.$file;
    }
    if ($isUpload) {
        $info['action'] = "upload";
        $fileName = $file['name'];
        $info['mime'] = $file['type'];
        $info['size'] = $file['size'];
    } else {
        $info['action'] = "import";
        if (!isset($fileName) && is_string($file)) {
            $fileName = $file;
        }
        $info['mime'] = mime_content_type($filePath);
        $info['size'] = filesize($filePath);
    }
    $info['name'] = $fileName;
    $info['tmp_name'] = $filePath;
    $info['stored_name'] = $oldName;
    $info['set_name'] = $newName;
    $info['type'] = strtolower(strtok($info['mime'], '/'));
    $info['ext'] = fileExt($fileName);
    if ($info['type']=='image') {
      $dimens = getimagesize($filePath);
      $info['dimensions'] = array("w"=>$dimens[0], "h"=>$dimens[1]);
      if ($info['ext'] === 'gif') {
        $info['img_animated'] = isAniGif($filePath);
      } else {
        $info['img_animated'] = false;
      }
    } else {
      $info['img_w']=$info['img_h']=$info['img_animated']=null;
    }
    return $info;
  }
  
  function formatDimensLims($w=false,$h=false,$wIsSoft=true,$hIsSoft=true) {
    // format image dimension limits
    if (!$w || !$h) {
      global $set;
      if (!$w) {$w=$set['max_img_dimns'];}
      if (!$h) {$h=$set['max_img_dimns'];}
    }
    return array("w"=>array("pixels"=>$w,"is_soft"=>$wIsSoft),"h"=>array("pixels"=>$h,"is_soft"=>$hIsSoft));
  }
  
  function chkImgDimnsLimit($imgSize,$dimnsLimit=null) {
    // make sure image is within the set image dimension limits (limit in settings is already accounted for)
    $val=array('result'=>true, 'msg'=>null);

    // check if formats are correct
    if (!$imgSize || !is_array($imgSize) || !($imgSize['h'] ?? null) || !($imgSize['w'] ?? null)) {
      $val['result'] = false;
      $val['msg'] = "Invalid image, or invalid image dimensions.";
      return $val;
    }
    if (!is_null($dimnsLimit) && (!is_array($dimnsLimit) || !is_array($dimnsLimit['w']) || !is_array($dimnsLimit['h']))) {
      $val['result'] = false;
      $val['msg'] = "Image dimension parameters are formatted incorrectly.";
      return $val;
    }

    $dimsMsg = '';
    // if a limit on pixels in width or height is imposed...
    if ($dimnsLimit['w']['pixels'] || $dimnsLimit['h']['pixels']) {
      // check image width
      if ($dimnsLimit['w']['pixels']) {
        if (!$dimnsLimit['w']['is_soft'] && ($imgSize['w'] != $dimnsLimit['w']['pixels'])) {
          $dimsMsg .= $dimnsLimit['w']['pixels'].' pixels in width';
          $val['result'] = false;
        } else if ($dimnsLimit['w']['is_soft'] && ($imgSize['w'] > $dimnsLimit['w']['pixels'])) {
          $dimsMsg .= 'less than '.$dimnsLimit['w']['pixels'].' pixels in width';
          $val['result'] = false;
        };
      }
      // check image height
        if ($dimnsLimit['h']['pixels']) {
          if ($dimnsLimit['h']['pixels'] && !$dimnsLimit['h']['is_soft'] && $imgSize['h'] != $dimnsLimit['h']['pixels']) {
            if ($dimsMsg>'') {
              $dimsMsg .= ' and ';
              };
            $dimsMsg .= $dimnsLimit['h']['pixels'].' pixels in height';
            $val['result'] = false;
          } else if ($dimnsLimit['h'] && $dimnsLimit['h']['is_soft'] && $imgSize['h'] > $dimnsLimit['h']['pixels']) {
            if ($dimsMsg>'') {
              $dimsMsg .= ' and ';
              };
            $dimsMsg .= 'less than '.$dimnsLimit['h']['pixels'].' pixels in height';
            $val['result'] = false;
          };
        }
      }
      if ($dimsMsg && !$val['result']) {
        $val['msg']= "Image must be ".$dimsMsg.".";
        return $val;
    }
    return $val;
  }
  
  function createTargetFile($fileName,$dir,$rename=false) {
    $fileType = fileExt($fileName,false);
    if ($rename) {
      $targetFile = cleanFileName($rename);
    } else {
      $targetFile = str_replace(".".$fileType, "", cleanFileName(basename($fileName)));
    }
    $targetFile = $dir.$targetFile.".".strtolower($fileType);
    return $targetFile;
  }
  
  function validateFile($fInfo,$tarDir,$valFTs=null,$dimens=null,$customStorLim=null) {
    global $root;
    if (!$customStorLim) {
      global $set;
      $storLim = $set['max_upld_storage']; 
    } else {
      $storLim = $customStorLim;
    }
    $dir = $root.$tarDir;
    $result = array("valid"=>null, "msg"=>null);
    if (!is_null($valFTs)) {
        if (is_string($valFTs)) {
            $valFTs = explode(',',$valFTs);
        } else if (!is_array($valFTs)) {
            $valFTs = false;
        }
    } else {
        $valFTs = false;
    }
    // determine whether we need to check image dimensions
    if ($dimens && ($fInfo['type']=='image' && !$fInfo['img_animated'])) {
      $dimensChk = chkImgDimnsLimit($fInfo['dimensions'],$dimens);
      $val['result'] = $dimensChk['result'];
    } else {
      // Not an image/not something we need valid dimensions for; don't worry about it.
      $val['result'] = true;
    }
    // Allow certain file formats
    if($valFTs && !in_array($fInfo['ext'], $valFTs)) {
      $result['valid'] = false;
      $result['msg'] = "'".$fInfo['ext']."' files are not accepted.<br/> The filetype must be one of the following: ".implode(', ', $valFTs).'. ';
    // If needed, check if filename already exists within folder
    } else if (
        (!$fInfo['set_name'] && $fInfo['stored_name']) &&
        ($tarDir.basename($fInfo["name"]) != $fInfo['stored_name']) &&
      ($tarDir.basename($fInfo["name"]) != $fInfo['stored_name']) &&
      (file_exists($dir.basename($fInfo["name"])))
      ) {
        $result['valid'] = false;
        $result['msg']= "A file with this name already exists and cannot be overwritten.";
    } else if (!withinStorageLimit($fInfo['size'],$storLim)) {
      $result['valid'] = false;
      $result['msg'] = "Your file is too large (over ".$storLim." MB). Save it at a smaller size or lower quality, and try again.";
    } else if (!$val['result']) {
      $result['valid'] = false;
      $result['msg'] = $dimensChk['msg'];
    } else {
        $result['valid'] = true;
    }
    return $result;
  }

function validImg($imgServerPath,$ckhValidFTs=true,$customImgFTs=null){
  if (!$customImgFTs) {
      global $validImgTypes;
      $validFTs=$validImgTypes;
  } else {
      $validFTs=$customImgFTs;
  }
  $mime = mime_content_type($imgServerPath);
  $type = strtolower(strtok($mime, '/'));
  if ($type==="image") {
      if ($ckhValidFTs) {
          $ext=fileExt($imgServerPath);
          if (!in_array($ext, $validFTs)) {
              return false;
          }
      }
      return true;
  } else {
      return false;
  }
}

function createFilePath(
                     $targetDir,
                     $file,
                     $validFTs=null,
                     $dimens=null,
                     $storedName=null,
                     $setName=null,
                     $method=null) {
  global $root; global $set;
  $dir = $root.$targetDir;
  if(!is_dir($dir)){
    //Create directory if does not exist. 'True' makes this recursive.
    mkdir($dir, 0755, true);
  };
  $val = array("result"=>null, "msg"=>'');
  if ($validFTs && is_string($validFTs)) {
    $validFTs = explode(',', $filetypes);
  }
  if ($file && isset($file["name"])) {
    $targetFile = createTargetFile($file["name"],$dir,$setName);
  } else {
    $val['msg'] = 'The system was unable to identify any valid files.';
    return $val;
  }
  $fileInfo = formatFileInfo($file,$targetDir,$storedName,$setName);
  $chkFile = validateFile($fileInfo,$targetDir,$validFTs,$dimens);
  
  // Check if $valid is false
  if (!$chkFile['valid']) {
    $val['msg'] = $chkFile['msg'];
    return $val;
  } else {
    //Check if targetDir's directory already exists.
    if ($fileInfo['action'] == "upload") {
      if (move_uploaded_file($file["tmp_name"], $targetFile)) {
        if (file_exists($dir.basename($targetFile))) {
          $val['result'] = $targetDir.basename($targetFile);
          if ($val['result']) {
            $val['msg'] = "File uploaded successfully.";
            return $val;
          } else {$val['msg'] = "Could not create new file path for uploaded file.";}
        } else {$val['msg'] = "File could not be found after upload.";}
      } else {$val['msg'] = "File failed to upload.";}
      return $val;
    } else {
      if (!is_dir($fileInfo["tmp_name"])) {
        if ($method!=='move') { //uploading file
          $result = copy($fileInfo["tmp_name"], $targetFile);
        } else { // moving/importing file
          $result = rename($fileInfo["tmp_name"], $targetFile);
        }
      } else {
        $val['msg'] =  'Folders cannot be imported.';
        $val['result'] = false;
        return $val;
      }
      if ($result) {
        if (file_exists($dir.basename($targetFile))) {
          $val['result'] = $targetDir.basename($targetFile);
          if ($val['result']) {
            $val['msg'] = "File imported successfully.";
            return $val;
          } else {$val['msg'] = "Could not create new file path for imported file.";}
        } else {$val['msg'] = "File could not be found after importing.";}
      } else {$val['msg'] = "File failed to import.";}
    return $val;
    }
  }
  $val['msg'] = "An error occurred. File creation failed.";
  return $val;
}

function getThumbSizes($imgSize, $thumbSize, $axis) {
  $msgExt=" Could not create thumbnail.";
  $img=array();
  $thumb=array("w"=>false,"h"=>false);
  $val = array("result"=>true,"sizes"=>$thumb,"msg"=>null);

  if (!is_array($imgSize)){
    $val['result']=false;
    $val['msg'] = "Incorrect thumbnail validation format.".$msgExt;
    return $val;
  }

  // Get dimensions
  $img['w'] = $imgSize[0];
  $img['h'] = $imgSize[1];
  if (!$img['w'] || !$img['h']) {
    $val['result']=false;
    $val['msg'] = "Image is invalid.".$msgExt;
    return $val;
  }

  switch ($axis) {
    case 0: //width
      $thumb['w'] = $thumbSize;
      break;
    case 1: //height
      $thumb['h'] = $thumbSize;
      break;
    case 2: //smallest axis
      if ($img['w']>$img['h']) {
        $thumb['h']=$thumbSize;
      } else {
        $thumb['w']=$thumbSize;
      }
      break;
    case 3: //largest axis
      if ($img['w']<$img['h']) {
        $thumb['h']=$thumbSize;
      } else {
        $thumb['w']=$thumbSize;
      }
      break;
    case 4: //axes are equal
    default:
      $thumb['w']=$thumb['h']=$thumbSize;
      break;
  }

  if ($img['w'] < $thumb['w'] || $img['h'] < $thumb['h']) {
    $val['result']=false;
    $val['msg'] = "Uploaded image is smaller than the thumbnail size.".$msgExt;
    return $val;
  }

  $val['sizes']=$thumb;
  return $val;
}

function mkThumb($dir, $oriImage, $newImage, $size, $axis, $tag="thumb") {
  global $root;
  $oriImage=str_replace($dir,'',$oriImage);
  $oriImagePath = $root.$dir.$oriImage; 
//NOTE:
// '$oriImage' is the name of the file we are creating a thumbnail from.
// '$newImage' is the name that we would like the new thumbnail file to have.
// The same thing can be input into both arguments if apropriate, but we want $oriImage to remain unchanged, 
// and $newImage to be rewritten as a home for a brand new image, so they cannot be the same variable.

  $val=array("result"=>null, "msg"=>'');

  //get filetype
  $fileType = strtolower(fileExt($oriImage));
  if($fileType == 'jpeg') {$fileType = 'jpg';}
  switch($fileType){
    case 'gif': $img = imagecreatefromgif($oriImagePath); break;
    case 'jpg': $img = imagecreatefromjpeg($oriImagePath); break;
    case 'png': $img = imagecreatefrompng($oriImagePath); break;
    case 'webp': $img = imagecreatefromwebp($oriImagePath); break;
    case '' : 
    case null : $val['msg'] = "Image is invalid. Cannot create thumbnail. "; return;
    default : $val['msg'] = "Thumbnail creation does not support ".$fileType." files. "; return;
  }

  $newDir = $dir.'thumbnails/';
  if(!is_dir($root.$dir)){
    //Create directory if does not exist. 'True' makes this recursive.
    mkdir($root.$dir, 0755, true);
  };
  $newImage = insertFilenameTag($newImage, $tag, '_', true);
  $newImagePublic = $newDir.$newImage;
  $newImage = $root.$newDir.$newImage;

  $imgSize= getimagesize($oriImagePath);
  $thumbSize = getThumbSizes($imgSize, $size, $axis);
  if (!$thumbSize['result']) {
    $val['msg'].=$thumbSize['msg'];
    return;
  } else {
    $thumb=$thumbSize['sizes'];
    $ogImg=array("w"=>$imgSize[0],"h"=>$imgSize[1]);
  }

    if (!$thumb['w'] && $thumb['h']) {
      $percentDiff = ($thumb['h']/$ogImg['h']);
      $thumb['w'] = round($ogImg['w']*$percentDiff);
    } else if ($thumb['w'] && !$thumb['h']) {
      $percentDiff = ($thumb['w']/$ogImg['w']);
      $thumb['h'] = round($ogImg['h']*$percentDiff);
    }

    // if($crop){
    //   $resizeRatio = max($thumb['w']/$ogImg['w'], $thumb['h']/$ogImg['h']);
    //   $ogImg['h'] = $thumb['h'] / $resizeRatio;
    //   $x = ($ogImg['w'] - $thumb['w'] / $resizeRatio) / 2;
    //   $ogImg['w'] = $thumb['w'] / $resizeRatio;
    // }
    // else if ($resizeRatio) {
    //   $resizeRatio = min($thumb['w']/$ogImg['w'], $thumb['h']/$ogImg['h']);
    //   $thumb['w'] = $ogImg['w'] * $resizeRatio;
    //   $thumb['h'] = $ogImg['h'] * $resizeRatio;
    //   $x = 0;
    // } else {
    //   $x = 0;
    // }

    $newImgInfo = imagecreatetruecolor($thumb['w'], $thumb['h']);

    // preserve transparency
    if($fileType == "gif" or $fileType == "png"){
    imagecolortransparent($newImgInfo, imagecolorallocatealpha($newImgInfo, 0, 0, 0, 127));
    imagealphablending($newImgInfo, false);
    imagesavealpha($newImgInfo, true);
    }

  imagecopyresampled($newImgInfo, $img, 0, 0, ($x ?? 0), 0, $thumb['w'], $thumb['h'], $ogImg['w'], $ogImg['h']);

  switch($fileType){
    case 'gif': imagegif($newImgInfo, $newImage); break;
    case 'jpg': imagejpeg($newImgInfo, $newImage, ($set['thumb_quality'] ?? -1)); break;
    case 'png': imagepng($newImgInfo, $newImage, ($set['thumb_quality'] ?? -1)); break;
    case 'webp': imagewebp($newImgInfo, $newImage, ($set['thumb_quality'] ?? -1)); break;
  }
  if (!$newImagePublic || !file_exists($newImage)) {
    $val['msg'] = 'Thumbnail creation failed.';
  } else {
    $val['result']=$newImagePublic;
  }
  return $val;
}
