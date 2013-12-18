<?php
include("checker.php");
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html dir="ltr">

<head>
<link rel="stylesheet" type="text/css" href="../style.css" />
<style type="text/css">
<!--
table.none {
	
}

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
				alt="Tatool online" title="Tatool online" border='0'> </a> <img
				src="../img/online/title_study_subject.png">
		</div>
		<div style="width: 600px; float: right;">

			<table class="tatool" align="right">
				<?php
				include("../data/setting.php");
				$cnx = connectdb();

				$subject_id = $_GET["subject_id"];
				$study_id = $_GET["study_id"];
				$module_id = $_GET["module_id"];

				// check whether current study should be blind for user
				$q_user = $cnx->prepare("SELECT BLIND_CD FROM tat_user_study WHERE USER_ID=:user AND STUDY_ID=:study_id");
				$q_user->bindParam(':user', $_SESSION['USER_ID']);
				$q_user->bindParam(':study_id', $study_id);
				$q_user->execute();

				while ($field = $q_user->fetch(PDO::FETCH_ASSOC)) {
					$blind_cd = $field['BLIND_CD'];
				}

				// select study details
				$q_module_info = $cnx->prepare("SELECT STUDY_NAME, MODULE_NR, MODULE_NAME, GROUP_NAME, G.GROUP_ID, SUBJECT_CODE
						FROM tat_user_study UST, tat_study S, tat_module M, tat_module_study MS, tat_group_module GM, tat_group G, tat_subject_group SG, tat_subject SUB
						WHERE UST.STUDY_ID = S.STUDY_ID
						AND S.STUDY_ID = MS.STUDY_ID
						AND MS.MODULE_ID = M.MODULE_ID
						AND M.MODULE_ID = GM.MODULE_ID
						AND GM.GROUP_ID = G.GROUP_ID
						AND G.GROUP_ID = SG.GROUP_ID
						AND SG.SUBJECT_ID = SUB.SUBJECT_ID
						AND SUB.SUBJECT_ID=:subject_id
						AND S.STUDY_ID=:study_id
						AND M.MODULE_ID=:module_id
						AND UST.USER_ID=:user");

				$q_module_info->bindParam(':user', $_SESSION['USER_ID']);
				$q_module_info->bindParam(':study_id', $study_id);
				$q_module_info->bindParam(':module_id', $module_id);
				$q_module_info->bindParam(':subject_id', $subject_id);
				$q_module_info->execute();

				$subject_code = "";

				while ($field = $q_module_info->fetch(PDO::FETCH_ASSOC)) {
  $study_name = $field['STUDY_NAME'];
  $module_nr = $field['MODULE_NR'];
  $module_name = $field['MODULE_NAME'];
  $group_name = $field['GROUP_NAME'];
  $group_id = $field['GROUP_ID'];
  $subject_code = $field['SUBJECT_CODE'];

  echo "<tr>";
  echo "<td class='label'><strong>Study</strong></td><td>" . $study_name . "</td>";
  echo "</tr>";
  echo "<tr>";
  echo "<td class='label'><strong>Module</strong></td><td><a href='module.php?study_id=$study_id&module_id=$module_id' class='ext_link'>" . $module_name . "</a></td>";
  echo "</tr>";
  echo "<tr>";
  echo "<td class='label'><strong>Group</strong></td><td><a href='group_data.php?study_id=$study_id&module_id=$module_id&group_id=$group_id' class='ext_link'>$group_name</a></td>";
  echo "</tr>";
  echo "<tr>";
  echo "<td class='label'><strong>Code</strong></td><td>" . $subject_code . "</td>";
  echo "</tr>";
}
?>
			</table>

			<table class="tatool">
				<?php
				// select subject details
				$q_sub_detail = $cnx->prepare("SELECT SUB.SUBJECT_ID, SUB.SUBJECT_NAME, SUB.SUBJECT_MAIL, SUB.SUBJECT_SEX_CD, SUB.SUBJECT_BIRTH, ACC.ACCOUNT_NAME, HOM.HOME, OS.OSNAME
						FROM tat_subject SUB
						LEFT OUTER JOIN
						(SELECT SUBJECT_ID, ACCOUNT_NAME
						FROM tat_account_data
						WHERE SUBJECT_ID=:subject_id
						GROUP BY SUBJECT_ID
						ORDER BY INSERT_DT DESC) ACC
						ON ACC.SUBJECT_ID = SUB.SUBJECT_ID
						LEFT OUTER JOIN
						(SELECT SUBJECT_ID, PROPERTY_VALUE AS HOME
						FROM tat_account_data
						WHERE SUBJECT_ID=:subject_id
						AND PROPERTY_NAME='UserHome') HOM
						ON HOM.SUBJECT_ID = SUB.SUBJECT_ID
						LEFT OUTER JOIN
						(SELECT SUBJECT_ID, PROPERTY_VALUE AS OSNAME
						FROM tat_account_data
						WHERE SUBJECT_ID=:subject_id
						AND PROPERTY_NAME='OSname') OS
						ON OS.SUBJECT_ID = SUB.SUBJECT_ID
						INNER JOIN
						tat_subject_group SGR, tat_group GRP, tat_group_module GRM, tat_module MDL, tat_module_study MST, tat_study STD, tat_user_study UST
		WHERE
		SUB.SUBJECT_ID = SGR.SUBJECT_ID
		AND SGR.GROUP_ID = GRP.GROUP_ID
		AND GRP.GROUP_ID = GRM.GROUP_ID
		AND GRM.MODULE_ID = MDL.MODULE_ID
		AND MDL.MODULE_ID = MST.MODULE_ID
		AND MST.STUDY_ID = STD.STUDY_ID
		AND STD.STUDY_ID = UST.STUDY_ID
		AND SUB.SUBJECT_ID=:subject_id
		AND MDL.MODULE_ID=:module_id
		AND STD.STUDY_ID=:study_id
		AND UST.USER_ID=:user");

				$q_sub_detail->bindParam(':user', $_SESSION['USER_ID']);
				$q_sub_detail->bindParam(':study_id', $study_id);
				$q_sub_detail->bindParam(':module_id', $module_id);
				$q_sub_detail->bindParam(':subject_id', $subject_id);
				$q_sub_detail->execute();

				while ($field = $q_sub_detail->fetch(PDO::FETCH_ASSOC)) {
  $subject_name = $field['SUBJECT_NAME'];
  $subject_mail = $field['SUBJECT_MAIL'];
  $subject_birth = $field['SUBJECT_BIRTH'];
  $subject_sex_cd = $field['SUBJECT_SEX_CD'];
  $account_name = $field['ACCOUNT_NAME'];
  $home_folder = $field['HOME'];
  $subject_os = $field['OSNAME'];
  $age =  (date('Y') - $subject_birth);

  if ($blind_cd==1) {
    $name = $subject_code;
    $mail = "contact (<a href='contact.php?subject_id=$subject_id&study_id=$study_id&module_id=$module_id' class='text_link'>e</a>)";
  } else {
    $name = $subject_name;
    $mail = "$subject_mail (<a href='contact.php?subject_id=$subject_id&study_id=$study_id&module_id=$module_id' class='text_link'>e</a>)";
  }

  if ($blind_cd==1) {
    echo "<tr>";
    echo "<td class='label'><strong>E-mail</strong></td><td>contact (<a href='contact.php?subject_id=$subject_id&study_id=$study_id&module_id=$module_id' class='text_link'>e</a>)</td>";
    echo "</tr>";
  } else {
    echo "<tr>";
    echo "<td class='label'><strong>Name</strong></td><td>" . $subject_name . "</td>";
    echo "</tr>";
    echo "<tr>";
    echo "<td class='label'><strong>E-mail</strong></td><td>$subject_mail (<a href='contact.php?subject_id=$subject_id&study_id=$study_id&module_id=$module_id' class='text_link'>e</a>)</td>";
    echo "</tr>";
    echo "<tr>";
  }

  if ($age < 100) {
    echo "<td class='label'><strong>Age</strong></td><td>" . $age . "</td>";
  } else {
    echo "<td class='label'><strong>Age</strong></td><td></td>";
  }

  echo "</tr>";
  echo "<tr>";
  if ($subject_sex_cd == '1') {
    echo "<td class='label'><strong>Sex</strong></td><td>Male</td>";
  } else if ($subject_sex_cd == '2') {
    echo "<td class='label'><strong>Sex</strong></td><td>Female</td>";
  }  else {
    echo "<td class='label'><strong>Sex</strong></td><td></td>";
  }
  echo "</tr>";

  if ($blind_cd!=0) {
    // show no information
  } else {
    echo "<tr>";
    echo "<td class='label'><strong>Account Name</strong></td><td>" . $account_name . "</td>";
    echo "</tr>";

    echo "<tr>";
    echo "<td class='label'><strong>Home Folder</strong></td><td>" . $home_folder . " (";
    if  (strpos(strtolower($subject_os),'windows') !== false) {
      echo "<img src='../img/online/os_windows.png' alt='Windows' title='Windows'>";
    } else if (strpos(strtolower($subject_os),'linux') !== false) {
      echo "<img src='../img/online/os_linux.png' alt='Linux' title='Linux'>";
    } else if (strpos(strtolower($subject_os),'os') !== false) {
      echo "<img src='../img/online/os_mac.png' alt='Mac' title='Mac'>";
    } else {
      echo "";
    }
    echo ")</td>";
    echo "</tr>";
  }
}
?>
			</table>


		</div>
		<div style="clear: both"></div>
		<div
			style="width: 800px; float: left; margin-top: 20px; margin-bottom: 20px">

			<?php

			if (isset($_GET["mail"])) {
			if ($_GET["mail"] == 'success') {
  echo "<table class='tatool' width=800>";
  echo "<tr>";
  echo "<th>Mail has been sent!</th>";
  echo "</tr>";
  echo "</table><br>";
} else if ($_GET["mail"] == 'error') {
  echo "<table class='tatool' width=800>";
  echo "<tr>";
  echo "<th>Mail error!</th>";
  echo "</tr>";
  echo "</table><br>";
}
}
?>

			<form action="contact_mail.php" method="post" name="contact">
				<table class="tatool" width=800>
					<?php

					// select session details
					$q_mail = $cnx->prepare("SELECT SUB.SUBJECT_ID, SUB.SUBJECT_CODE, SUB.SUBJECT_NAME, SUB.SUBJECT_SEX_CD, SUB.SUBJECT_MAIL, SUB.SUBJECT_SEX_CD, USR.USER_MAIL
FROM tat_subject SUB, tat_subject_group SGR, tat_group GRP, tat_group_module GRM, tat_module MDL, tat_module_study MST, tat_study STD, tat_user_study UST, tat_user USR
WHERE SUB.SUBJECT_ID = SGR.SUBJECT_ID
AND SGR.GROUP_ID = GRP.GROUP_ID
AND GRP.GROUP_ID = GRM.GROUP_ID
AND GRM.MODULE_ID = MDL.MODULE_ID
AND MDL.MODULE_ID = MST.MODULE_ID
AND MST.STUDY_ID = STD.STUDY_ID
AND STD.STUDY_ID = UST.STUDY_ID
AND UST.USER_ID = USR.USER_ID
AND SUB.SUBJECT_ID=:subject_id
AND MDL.MODULE_ID=:module_id
AND STD.STUDY_ID=:study_id
AND UST.USER_ID=:user");

$q_mail->bindParam(':user', $_SESSION['USER_ID']);
$q_mail->bindParam(':study_id', $study_id);
$q_mail->bindParam(':module_id', $module_id);
$q_mail->bindParam(':subject_id', $subject_id);
$q_mail->execute();

if ($q_mail->rowCount() == 0) {
  echo "<tr>";
  echo "<td colspan=5>No mail address available.</td>";
  echo "</tr>";
} else {

}

while ($field = $q_mail->fetch(PDO::FETCH_ASSOC)) {
  $code = $field['SUBJECT_CODE'];
  $name = $field['SUBJECT_NAME'];
  $email = $field['SUBJECT_MAIL'];
  $user_mail = $field['USER_MAIL'];
  $sex_cd = $field['SUBJECT_SEX_CD'];
}

if ($blind_cd == 1) {
  $to = $code;
} else {
  $to = $name . " ($email)";
}

if (empty($email)) {
  echo "<tr>";
  echo "<th>No mail address available.</th>";
  echo "</tr>";
} else {
echo "<tr>";
echo "<td class='label'>From:</td><td><input type='text' name='from' size=60 value='$user_mail' readonly></td>";
echo "</tr>";
echo "<tr>";
echo "<td class='label'>To:</td><td>$to</td>";
echo "</tr>";
echo "<tr>";
echo "<td class='label'>Subject:</td><td><input type='text' name='subject' size=60></td>";
echo "</tr>";
echo "<tr>";
echo "<td class='label' valign='top'>Text:</td><td>";
echo "<div style='float:left'>";
echo "<textarea name='text' cols='45' rows='20'></textarea>";
echo "</div><div style='float:left;padding-left:20px'>";
echo "</div>";
echo "</td></tr>";
echo "<tr>";
echo "<td class='label'><input type='hidden' name='mail' value='$email'><input type='hidden' name='page' value='contact.php?subject_id=$subject_id&study_id=$study_id&module_id=$module_id'></td>";
echo "<td class='label'><input type='hidden' name='study_id' value='$study_id'><input type='submit' value='Send Mail'></td>";
echo "</tr>";
}



?>
				</table>
			</form>
			<br> <a href="index.php" class="text_link">Overview</a>
		</div>
	</div>
</body>


</html>
