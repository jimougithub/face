<?php
ini_set("display_errors", "On");
error_reporting(E_ALL);
require_once("./inc/conn.php");

$locale='en_US.UTF-8';
setlocale(LC_ALL,$locale);
putenv('LC_ALL='.$locale);


function getMillisecond() {
	list($t1, $t2) = explode(' ', microtime());
	return (float)sprintf('%.0f',(floatval($t1)+floatval($t2))*1000);
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

$file_size=$_FILES['file_upload']['size'];
if($file_size>10*1024*1024) {
    echo "<script>alert('Upload failed: file too big!');window.location='./';</script>";
    exit();  
}

$file_type=$_FILES['file_upload']['type'];  
if($file_type!="image/jpeg" && $file_type!='image/pjpeg' && $file_type!='image/png') {  
    echo "<script>alert('Only accept jpg, jpeg, png. ". $file_type ."');window.location='./';</script>";
    exit();  
}
if($file_type=="image/jpeg") $file_type=".jpg";
if($file_type=="image/pjpeg") $file_type=".jpeg";
if($file_type=="image/png") $file_type=".png";

$new_name = getMillisecond().$file_type;
$match_name = "Unknown";
$ppl_name = "";
$ppl_desc = "";
$match_distance = 1;
$temp_path = "/var/www/html/ai/face/temp/".session_id()."/";

if(is_uploaded_file($_FILES['file_upload']['tmp_name'])) {
	//Clean up the folder
	$output = shell_exec('rm -rf '.$temp_path);
	$output = shell_exec('mkdir '.$temp_path);
	
	//Move uploaded file to right path
	$uploaded_file=$_FILES['file_upload']['tmp_name'];
	$move_to_file=$temp_path.$new_name;
	//if(move_uploaded_file($uploaded_file,$move_to_file)){
	ImageAutoRotate($uploaded_file);		//Fix iphone upload problem
	if(ResizeImage($uploaded_file,640,640,$move_to_file)){
		$t1 = microtime(true);
		$output = shell_exec('python3 ./facerecong/facecompare.py --pic='.$move_to_file);
		$t2 = microtime(true);
		if(trim($output)==''){
			echo "<script>alert('Upload failed: recognition failed');window.location='./';</script>";
		}else{
			$result = json_decode($output,true);
			foreach($result as $distinct=>$pplid){
				$match_name = $pplid;
				$match_distance = $distinct;
				break;
			}
			//If hit rate is good then show
			if(floatval($match_distance)<0.35){
				$sql="SELECT * FROM knownpeople WHERE pplid=". $match_name;
				$result = mysql_query($sql);
				if($row = mysql_fetch_array($result)){
					$ppl_name = $row["pplname"];
					$ppl_desc = $row["ppldesc"];
				}
				mysql_close($conn);
			}else{
				$match_name = "Unknown";
			}
		}
	}else{
		echo "<script>alert('Upload failed: move failed');window.location='./';</script>";
	}
}else{
	echo "<script>alert('Upload failed');window.location='./';</script>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="apple-mobile-web-app-capable" content="yes">
	<title>Face Recognition - Recognize face</title>
	<link href="css/bootstrap.min.css" rel="stylesheet" type="text/css" />
	<link href="css/bootstrap-responsive.min.css" rel="stylesheet" type="text/css" />
	<link href="css/font-awesome.css" rel="stylesheet">
    <link href="css/font-googleapis.css" rel="stylesheet">
	<link href="css/style.css" rel="stylesheet" type="text/css">
	<link href="css/pages/plans.css" rel="stylesheet">
</head>

<body>
	<div class="navbar navbar-fixed-top">	
	<div class="navbar-inner">
		<div class="container">
			<a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
			</a>

			<a class="brand">
				Face recognition demo
			</a>
			
			<div class="nav-collapse">
				<ul class="nav pull-right">
					<li class="">
						<a href="./" class="">
							<i class="icon-search"></i> Recognize face
						</a>
					</li>
					<li class="">
						<a href="./addface/" class="">
							<i class="icon-plus-sign"></i> Add new faces
						</a>
					</li>
					<li class="">						
						<a href="./managefaces/" class="">
							<i class="icon-list"></i> Manage Faces
						</a>
					</li>
				</ul>
			</div><!--/.nav-collapse -->	
		</div> <!-- /container -->
	</div> <!-- /navbar-inner -->
</div> <!-- /navbar -->

<div class="main">
	<div class="main-inner">
	    <div class="container">
	      <div class="row">
	      	<div class="span12">
	      		<div class="widget">
				
					<div class="widget-content">
						<div class="pricing-plans plans-1">
						
					    <div class="plan-container">
					        <div class="plan green">
						        <div class="plan-header">
						        	<div class="plan-title">
						        		Recognition time <?php echo round($t2-$t1,4).' secs<br>'; ?>
						        		Recognition result <?php echo round($match_distance,4); ?>
					        		</div> <!-- /plan-title -->				
						        </div> <!-- /plan-header -->	          
						        
								<div class="plan" align="center">
									<?php echo $ppl_name; ?>
								</div>
								<div class="plan" align="center">
									<?php echo $ppl_desc; ?>
								</div>
								<div class="plan-title" align="center">
									<img src="<?php echo "./knownpic/".$match_name.".jpg"; ?>" width="300px">
								</div>
								
								<div class="plan-title" align="center">				
									<img src="<?php echo "./temp/".session_id()."/".$new_name; ?>" width="300px">
								</div>
								
								<div class="plan-actions">				
									<a href="./" class="btn">Try again</a>				
								</div><!-- /plan-actions -->
					
							</div> <!-- /plan -->
					    </div> <!-- /plan-container 2 -->
				
					</div> <!-- /pricing-plans -->
					</div> <!-- /widget-content -->
				</div> <!-- /widget -->					
		    </div> <!-- /span12 -->     	
	      </div> <!-- /row -->
	    </div> <!-- /container -->
	</div> <!-- /main-inner -->
</div> <!-- /main -->


<script src="js/jquery-1.7.2.min.js"></script>
<script src="js/bootstrap.js"></script>

</body>
</html>
