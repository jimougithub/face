<?php
$file_size=$_FILES['file_upload']['size'];
if($file_size>10*1024*1024) {
    echo "<script>alert('Upload failed: file too big!');window.location='./';</script>";
    exit();  
}

$file_type=$_FILES['file_upload']['type'];  
echo $file_type;  
if($file_type!="image/jpeg" && $file_type!='image/pjpeg') {  
    echo "<script>alert('Only accept jpg, jpeg, png');window.location='./';</script>";
    exit();  
}

if(is_uploaded_file($_FILES['file_upload']['tmp_name'])) {
	//Move uploaded file to right path
	$uploaded_file=$_FILES['file_upload']['tmp_name'];
	$file_true_name=$_FILES['myfile']['name'];
	$move_to_file="/var/www/html/ai/face/temp/".$file_true_name;
  if(move_uploaded_file($uploaded_file,$move_to_file)){
	  echo "<script>alert('Upload Successfully!');window.location='./';</script>";
	}else{
	  echo "<script>alert('Upload failed: move failed');window.location='./';</script>";
	}
}else{
	echo "<script>alert('Upload failed');window.location='./';</script>";
}
?>