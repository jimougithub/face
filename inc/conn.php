<?php
require_once("dbinfo.php");

header("Content-Type: text/html;charset=utf-8");
date_default_timezone_set('PRC');

$conn=mysql_pconnect($mysql_server_name, $mysql_username,$mysql_password, MYSQL_CLIENT_COMPRESS);
mysql_select_db($mysql_database, $conn);
?>