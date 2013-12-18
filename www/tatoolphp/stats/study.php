<?php
include("checker.php");
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html dir="ltr">
    
    <head>
        <link rel="stylesheet" type="text/css" href="../style.css" />
        <style type="text/css">
<!--
table.tatool {
border:1px solid #19191a;
border-collapse:collapse;
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
    
    <body> <br>
    <div style="margin: 0 auto;width:800px;">
    <div style="width:200px;float:left;">
      <a href="index.php"><img src="../img/online/tatool_online.png" alt="Tatool online" title="Tatool online" border='0'></a><br>
      <img src="../img/online/title_study.png">
    </div>
    <div style="width:600px;float:right;">
      <a href="logout.php" class='ext_link'>Logout</a>
    </div>
<div style="clear:both"></div>       
<div style="width:800px;float:left;margin-top:20px;margin-bottom:20px">        
  <?php include 'study.inc' ?>       
</div>
            </div>

    </body>
   

</html>