<?php
session_start();
$cookie_name = "tatoolStats";
header('Cache-control: private');
header('Last-Modified: ' . gmdate("D, d M Y H:i:s") . ' GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');

if(isset($cookie_name))
{
	// Check if the cookie exists
	if(isset($_COOKIE[$cookie_name]))
	{
		parse_str($_COOKIE[$cookie_name]);

		// Make a verification
		if(isset($USER_ID))
		{
			// Register the session
			$_SESSION['USER_ID'] = $USER_ID;
		}
	}
}


if(isset($_SESSION['USER_ID'])){
	header('location:study.php');
	die();
}
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html dir="ltr">

<head>
<link rel="stylesheet" type="text/css" href="../style.css" />
<style type="text/css">
<!--
table.tatool {
	border: 1px solid #19191a;
	border-collapse: collapse;
}

.tatool th {
	border: 1px solid #19191a;
	background-color: #cc3300;
	color: #ffffff;
	padding: 3px;
}

.tatool .total {
	border: 1px solid #19191a;
	background-color: #fef2ee;
	padding: 3px;
	text-align: center;
	font-weight: bold;
}

.tatool .label {
	border: 1px solid #19191a;
	background-color: #fef2ee;
	padding: 3px;
	font-weight: bold;
}

.tatool td {
	border: 1px solid #19191a;
	padding: 3px;
}
-->
</style>
</head>

<body>
	<br>
	<div style="margin: 0 auto; width: 800px;">
		<div style="width: 200px; float: left;">
			<a href="index.php"><img src="../img/online/tatool_online.png"
				alt="Tatool online" title="Tatool online" border='0'> </a><br>

		</div>
		<div style="width: 600px; float: right;"></div>
		<div style="clear: both"></div>
		<div
			style="width: 800px; float: left; margin-top: 20px; margin-bottom: 20px">
			<?php include 'login.inc' ?>
		</div>
	</div>

</body>


</html>
