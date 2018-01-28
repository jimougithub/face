<?php
$file_size=$_FILES['file_upload']['size'];
if($file_size>1024*1024) {
    echo "<script>alert('Upload failed: file size too big!');window.location='./';</script>";
    exit();  
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
	if(move_uploaded_file($uploaded_file,$move_to_file)){
		echo "<script>alert('Upload successful!');window.location='./';</script>";
	}else{
		echo "<script>alert('Upload failed: move failed');window.location='./';</script>";
	}
}else{
	echo "<script>alert('Upload failed');window.location='./';</script>";
}
?>