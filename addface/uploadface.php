<?php
ini_set("display_errors", "On");
error_reporting(E_ALL);
require_once("../inc/conn.php");

$file_size=$_FILES['file_upload']['size'];
if($file_size>10*1024*1024) {
    echo "<script>alert('Upload failed: file size too big!');window.location='./';</script>";
    exit();  
}

function ImageAutoRotate($picAddr){
        $exif = exif_read_data($picAddr);
		if(!isset($exif['Orientation'])) return;
		
        $image = imagecreatefromjpeg($picAddr);
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

$pplname = trim($_POST['username']);
$ppldesc = trim($_POST['userdesc']);
$new_name = str_replace(" ","_",$pplname);
if($new_name==""){
	echo "<script>alert('Upload failed: Please input name of this person');window.location='./';</script>";
    exit();
}

$pplid = 1000001;
$sql="SELECT MAX(pplid)+1 as newpplid FROM knownpeople";
$result = mysql_query($sql);
if($row = mysql_fetch_array($result)){
	if($row["newpplid"]!=null){
		$pplid = $row["newpplid"];
	}
}
$new_name = $pplid.$file_type;

if(is_uploaded_file($_FILES['file_upload']['tmp_name'])) {
	//Move uploaded file to right path
	$uploaded_file=$_FILES['file_upload']['tmp_name'];
	$move_to_file="/var/www/html/ai/face/knownpic/".$new_name;
	//if(move_uploaded_file($uploaded_file,$move_to_file)){
	ImageAutoRotate($uploaded_file);		//Fix iphone upload problem
	if(ResizeImage($uploaded_file,640,640,$move_to_file)){
		$sql="INSERT INTO knownpeople(pplid, pplname, ppldesc) VALUES(". $pplid .", '". $pplname ."', '". $ppldesc ."')";
		if (mysql_query($sql)){
			$output = shell_exec('python ./addface.py --pic='. $move_to_file .' --id='. $pplid);
			echo "<script>alert('Upload successful!');window.location='./';</script>";
		}
	}else{
		echo "<script>alert('Upload failed: move failed');window.location='./';</script>";
	}
}else{
	echo "<script>alert('Upload failed');window.location='./';</script>";
}
?>