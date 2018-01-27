<?php
$file = $_FILES['file'];
$name = $file['file_upload'];							//File name
$type = strtolower(substr($name,strrpos($name,'.')+1)); //File type
$allow_type = array('jpg','jpeg','gif','png'); 			//Define accept file types
if(!in_array($type, $allow_type)){
  return;												//Upload file type not accept
}
if(!is_uploaded_file($file['tmp_name'])){
  return ;												//Not upload through HTTP POST
}
$upload_path = "./temp/";								//Define upload path
//Moving the uploaded file to right path
if(move_uploaded_file($file['tmp_name'],$upload_path.$file['name'])){
  echo "<script>alert('Upload Successfully!');window.location='./';</script>";
}else{
  echo "<script>alert('Upload failed!');window.location='./';</script>";
}
?>