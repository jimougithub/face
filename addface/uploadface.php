<?php
ini_set("display_errors", "On");
error_reporting(E_ALL);

$file_size=$_FILES['file_upload']['size'];
if($file_size>10*1024*1024) {
    echo "<script>alert('Upload failed: file size too big!');window.location='./';</script>";
    exit();  
}

function ImageAutoRotate($picAddr){
        $exif = exif_read_data($picAddr);
        $image = imagecreatefromjpeg($picAddr);
        print_r($exif);
        echo "<br/>";
        if($exif['Orientation'] == 3) {
                $result = imagerotate($image, 180, 0);
                imagejpeg($result, $picAddr, 100);
        } elseif($exif['Orientation'] == 6) {
                $result = imagerotate($image, -90, 0);
                imagejpeg($result, $picAddr, 100);
        } elseif($exif['Orientation'] == 8) {
                $result = imagerotate($image, 90, 0);
                imagejpeg($result, $picAddr, 100);
        }
        isset($result) && imagedestroy($result);
        imagedestroy($image);
}

function ResizeImage($uploadfile,$maxwidth,$maxheight,$name){
 //current image size
 $img = ImageCreateFromJpeg($uploadfile);
 $width = imagesx($img);
 $height = imagesy($img);
 //new image size
 if(($width > $maxwidth) || ($height > $maxheight)){
  //calculate new with & height
  $widthratio = $maxwidth/$width;
  $heightratio = $maxheight/$height;
  if($widthratio < $heightratio){
   $ratio = $widthratio;
  }else{
    $ratio = $heightratio;
  }
  $newwidth = intval($width * $ratio);
  $newheight = intval($height * $ratio);

  if(function_exists("imagecopyresampled")){
   $uploaddir_resize = imagecreatetruecolor($newwidth, $newheight);
   imagecopyresampled($uploaddir_resize, $img, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
  }else{
   $uploaddir_resize = imagecreate($newwidth, $newheight);
   imagecopyresized($uploaddir_resize, $img, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
  }

  ImageJpeg($uploaddir_resize,$name);
  ImageDestroy ($uploaddir_resize);
 }else{
  ImageJpeg ($img,$name);
 }
 return true;
}

$file_type=$_FILES['file_upload']['type'];  
if($file_type!="image/jpeg") {  
    echo "<script>alert('Upload failed: Only accept jpg');window.location='./';</script>";
    exit();
}
if($file_type=="image/jpeg") $file_type=".jpg";

$new_name = trim($_POST['username']);
$new_name = str_replace(" ","_",$new_name);
if($new_name==""){
	echo "<script>alert('Upload failed: Please input name of this person');window.location='./';</script>";
    exit();
}

if(is_uploaded_file($_FILES['file_upload']['tmp_name'])) {
	//Move uploaded file to right path
	$uploaded_file=$_FILES['file_upload']['tmp_name'];
	$move_to_file="/var/www/html/ai/face/knownpic/".$new_name;
	//if(move_uploaded_file($uploaded_file,$move_to_file)){
	ImageAutoRotate($uploaded_file);		//Fix iphone upload problem
	if(ResizeImage($uploaded_file,640,640,$move_to_file)){
		echo "<script>alert('Upload successful!');window.location='./';</script>";
	}else{
		echo "<script>alert('Upload failed: move failed');window.location='./';</script>";
	}
}else{
	echo "<script>alert('Upload failed');window.location='./';</script>";
}
?>