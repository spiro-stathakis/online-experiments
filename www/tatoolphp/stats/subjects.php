<?php
include("checker.php");
?>

<?php
include("../data/setting.php");
$cnx = connectdb();

$study_id= $_GET['study_id'];
$group_id= $_GET['group_id'];

// check whether current study should be blind for user
$q_user = $cnx->prepare("SELECT BLIND_CD FROM tat_user_study WHERE USER_ID=:user AND STUDY_ID=:study_id");
$q_user->bindParam(':user', $_SESSION['USER_ID']);
$q_user->bindParam(':study_id', $study_id);
$q_user->execute();

while ($field = $q_user->fetch(PDO::FETCH_ASSOC)) {
  $blind_cd = $field['BLIND_CD'];
}

?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html dir="ltr">
    
    <head>
        <link rel="stylesheet" type="text/css" href="../style.css" />
        <link rel="stylesheet" type="text/css" href="../buttons.css" />
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

<script type="text/javascript">
<!--
function confirmation(link) {
	var answer = confirm("Do you really want to delete the subject?")
	if (answer){
		window.location = link;
	}
}

function demo() {
	alert("Function is disabled in the Tatool Online demo.")
}
//-->
</script>
    </head>
    
    <body> <br>
    <div style="margin: 0 auto;width:800px;">
    <div style="width:200px;float:left;">
      <a href="index.php"><img src="../img/online/tatool_online.png" alt="Tatool online" title="Tatool online" border='0'></a><br>
      <img src="../img/online/title_study.png">
    </div>
    <div style="width:600px;float:right;">
      <table class="tatool">    
<?php

// get study information
$q_study_info = $cnx->prepare("SELECT STUDY_NAME
FROM tat_study S, tat_user_study US
WHERE S.STUDY_ID=:study_id
AND S.STUDY_ID = US.STUDY_ID
AND US.USER_ID=:user");

$q_study_info->bindParam(':user', $_SESSION['USER_ID']);
$q_study_info->bindParam(':study_id', $study_id);
$q_study_info->execute();

while ($field = $q_study_info->fetch(PDO::FETCH_ASSOC)) {
  $study_name = $field['STUDY_NAME'];
  
  echo "<tr>";
  echo "<td class='label'><strong>Study</strong></td><td>" . $study_name . "</td>";
  echo "</tr>";
}
       
?>
      </table>
    </div>
<div style="clear:both"></div>       
<div style="width:920px;float:left;margin-top:20px;margin-bottom:20px">        
  <?php include 'subjects.inc' ?>       
</div> 
            </div>
            
            

    </body>
   

</html>