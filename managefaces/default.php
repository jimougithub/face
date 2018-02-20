<?php
require_once("../inc/managepass.php");
require_once("../inc/conn.php");

ini_set("display_errors", "On");
error_reporting(E_ALL);

function getFileList($directory) {        
    $files = array();        
    if(is_dir($directory)) {        
        if($files = scandir($directory)) {        
            $files = array_slice($files,2);        
        }        
    }        
    return $files;
}


//Login ---------------------------------------------
if(!empty($_REQUEST['username']) && !empty($_REQUEST['password'])){
	$username = trim($_REQUEST['username']);
	$password = trim($_REQUEST['password']);
	if($username!="" && $password!=""){
		if($username!="administrator" || $password!=$admin_pass){
			$_SESSION["admin"]=null;
			echo "<script>alert(\"Invalid logon！\");window.location='./login.htm';</script>";
			exit();
		}else{
			$_SESSION["admin"]="Y";
		}
	}
}

// Check login --------------------------------------
$loginFlag = "N";
if(!empty($_SESSION['admin'])){
    $loginFlag=$_SESSION['admin'];
}
if($loginFlag!="Y"){
	echo "<script>alert(\"Please logon！\");window.location='./login.htm';</script>";
	exit();
}

// Remove pic-------------------------------------
if(!empty($_REQUEST['remove'])){
	$removeid = trim($_REQUEST['remove']);
	if($removeid!=""){
		$sql="DELETE FROM `knownpic` WHERE pplid = ".$removeid;
		if (mysql_query($sql)){
			$sql="DELETE FROM `knownpeople` WHERE pplid = ".$removeid;
			if (mysql_query($sql)){
				$output = shell_exec('rm -rf /var/www/html/ai/face/knownpic/'.$removeid.".jpg");
			}
		}
	}
}

// List pic --------------------------------------
$list = getFileList("../knownpic/");
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="apple-mobile-web-app-capable" content="yes">
	<title>Face Recognition - Manage known faces</title>
	<link href="../css/bootstrap.min.css" rel="stylesheet" type="text/css" />
	<link href="../css/bootstrap-responsive.min.css" rel="stylesheet" type="text/css" />
	<link href="../css/font-awesome.css" rel="stylesheet">
    <link href="../css/font-googleapis.css" rel="stylesheet">
	<link href="../css/style.css" rel="stylesheet" type="text/css">
	<link href="../css/pages/plans.css" rel="stylesheet">
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
						<a href="../" class="">
							<i class="icon-search"></i> Recognize face
						</a>
					</li>
					<li class="">
						<a href="../addface/" class="">
							<i class="icon-plus-sign"></i> Add new faces
						</a>
					</li>
					<li class="">						
						<a href="./" class="">
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
						        		All known faces
					        		</div> <!-- /plan-title -->				
						        </div> <!-- /plan-header -->	          
						        
								<div class="plan" align="center" style="padding-top:20px">
									<table class="table table-striped table-bordered">
										<thead>
										  <tr>
											<th> Name </th>
											<th> Picture</th>
											<th class="td-actions"> Remove </th>
										  </tr>
										</thead>
										<tbody>
										<?php
											$sql="SELECT A.*, B.picname FROM `knownpeople` A JOIN knownpic B ON (A.pplid = B.pplid)";
											$result = mysql_query($sql);
											while($row = mysql_fetch_array($result)){
												echo "<tr>";
												echo "<td> <img src='../knownpic/". $row["picname"] ."' width='200px'> </td>";
												echo "<td> ". $row["pplname"] ." </td>";
												echo "<td> ". $row["ppldesc"] ." </td>";
												echo "<td class='td-actions'><a href='./?remove=". $row["pplid"] ."' class='btn btn-danger btn-small'><i class='btn-icon-only icon-remove'> </i></a></td>";
												echo "</tr>";
											}
										?>
										</tbody>
									</table>
								</div>
					
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

<script src="../js/jquery-1.7.2.min.js"></script>
<script src="../js/bootstrap.js"></script>
</body>
</html>
