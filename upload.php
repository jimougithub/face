<?php
function getMillisecond() {
list($t1, $t2) = explode(' ', microtime());
return (float)sprintf('%.0f',(floatval($t1)+floatval($t2))*1000);
}

$file_size=$_FILES['file_upload']['size'];
if($file_size>10*1024*1024) {
    echo "<script>alert('Upload failed: file too big!');window.location='./';</script>";
    exit();  
}

$file_type=$_FILES['file_upload']['type'];  
if($file_type!="image/jpeg" && $file_type!='image/pjpeg' && $file_type!='image/png') {  
    echo "<script>alert('Only accept jpg, jpeg, png');window.location='./';</script>";
    exit();  
}
if($file_type=="image/jpeg") $file_type=".jpg";
if($file_type=="image/pjpeg") $file_type=".jpeg";
if($file_type=="image/png") $file_type=".png";

$new_name = getMillisecond().$file_type;
$match_name = "";
$match_distance = "";

if(is_uploaded_file($_FILES['file_upload']['tmp_name'])) {
	//Clean up the folder
	$output = shell_exec('rm -rf /var/www/html/ai/face/temp/*');
	
	//Move uploaded file to right path
	$uploaded_file=$_FILES['file_upload']['tmp_name'];
	$move_to_file="/var/www/html/ai/face/temp/".$new_name;
	if(move_uploaded_file($uploaded_file,$move_to_file)){
		$output = shell_exec('face_recognition /var/www/html/ai/face/knownpic/ /var/www/html/ai/face/temp/ --tolerance 0.45 --show-distance true');
		$result = split("\n",$output);
		$result = split(",",$result[0]);
		$match_name = $result[1];
		$match_distance = $result[2];
		//echo $result[1] ." ---". $result[2];
		//echo "<script>alert('". $match_name ." ---". $match_distance ."');";
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
						        		Recognition result <?php echo $match_distance; ?>
					        		</div> <!-- /plan-title -->				
						        </div> <!-- /plan-header -->	          
						        
								<div class="plan" align="center">
									<?php echo $match_name; ?>
								</div>
						        <div class="plan-title" align="center">
									<img src="<?php echo "./knownpic/".$match_name.".jpg"; ?>" width="300px">
								</div>
								
								<div class="plan-title" align="center">				
									<img src="<?php echo "./temp/".$new_name; ?>" width="300px">
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
