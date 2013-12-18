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

<script type="text/javascript">
<!--
function confirmation(link) {
	var answer = confirm("Do you really want to delete the log history?")
	if (answer){
		window.location = link;
	}
}
//-->
</script>
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
				$study_id = $_GET["study_id"];;
				$module_id = $_GET["module_id"];;

				// check whether current study should be blind for user
				// check whether current study should be blind for user
				$q_user = $cnx->prepare("SELECT BLIND_CD FROM tat_user_study WHERE USER_ID=:user AND STUDY_ID=:study_id");
				$q_user->bindParam(':user', $_SESSION['USER_ID']);
				$q_user->bindParam(':study_id', $study_id);
				$q_user->execute();

				while ($field = $q_user->fetch(PDO::FETCH_ASSOC)) {
					$blind_cd = $field['BLIND_CD'];
				}

				// select study details
				$q_module_info = $cnx->prepare("SELECT STUDY_NAME, MODULE_NAME, MODULE_NR, GROUP_NAME, GROUP_NR, G.GROUP_ID, SUBJECT_CODE
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
  $module_name = $field['MODULE_NAME'];
  $group_name = $field['GROUP_NAME'];
  $group_id = $field['GROUP_ID'];
  $subject_code = $field['SUBJECT_CODE'];
  $module_nr = $field['MODULE_NR'];
  $group_nr = $field['GROUP_NR'];

  echo "<tr>";
  echo "<td class='label'><strong>Study</strong></td><td>" . $study_name . "</td>";
  echo "</tr>";
  echo "<tr>";
  echo "<td class='label'><strong>Module</strong></td><td><a href='module.php?study_id=$study_id&module_id=$module_id' class='ext_link'>" . $module_name . "</a></td>";
  echo "</tr>";

  if ($blind_cd!=1) {
    echo "<tr>";
    echo "<td class='label'><strong>Group</strong></td><td><a href='group_data.php?study_id=$study_id&module_id=$module_id&group_id=$group_id' class='ext_link'>$group_name</a></td>";
    echo "</tr>";
    echo "<tr>";
    echo "<td class='label'><strong>Code</strong></td><td>" . $subject_code . "</td>";
    echo "</tr>";
  }

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


  $name = $subject_name;
  $mail = "$subject_mail (<a href='contact.php?subject_id=$subject_id&study_id=$study_id&module_id=$module_id' class='text_link'>e</a>)";

  echo "<tr>";
  echo "<td class='label'><strong>Name</strong></td><td>" . $subject_name . "</td>";
  echo "</tr>";
  echo "<tr>";
  echo "<td class='label'><strong>E-mail</strong></td><td>$subject_mail (<a href='contact.php?subject_id=$subject_id&study_id=$study_id&module_id=$module_id' class='text_link'>e</a>)</td>";
  echo "</tr>";
  echo "<tr>";

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
			<br> <strong>Sessions</strong> <br>
			<table class="tatool" width=800>
				<tr>
					<th>Session</th>
					<!-- Sex -->
					<th>Session Start</th>
					<!-- OS -->
					<th>Session End</th>
					<th><img src="../img/online/clock.png" alt="Duration"
						title="Duration"></th>
					<th><img src="../img/online/performance.png" alt="Performance"
						title="Performance"></th>
					<th></th>
					<th></th>
				</tr>


				<?php

				// select session details
				$q_sess_detail = $cnx->prepare("SELECT SES.SUBJECT_ID, SES.SESSION_ID, SES.SESS_START_DT, SES.SESS_END_DT, SES.INSERT_DT, SES.FILE_PREFIX, SES.SESS_COMPLETED, TOTAL_POINTS, TOTAL_MAX_POINTS
FROM
tat_session SES
LEFT OUTER JOIN
(SELECT SUBJECT_ID, S.SESSION_ID, SESS_START_DT, SESS_END_DT, S.FILE_PREFIX, S.INSERT_DT, SUM(PROPERTY_VALUE) AS TOTAL_POINTS FROM
tat_session S, tat_session_data SD
WHERE S.SESSION_ID = SD.SESSION_ID
AND SD.PROPERTY_NAME LIKE 'totalPoints%'
AND S.SUBJECT_ID=:subject_id
AND S.MODULE_ID=:module_id
GROUP BY S.SUBJECT_ID, S.SESSION_ID) A
ON A.SUBJECT_ID=SES.SUBJECT_ID AND A.SESSION_ID=SES.SESSION_ID
LEFT OUTER JOIN
(SELECT SUBJECT_ID, S.SESSION_ID, SUM(PROPERTY_VALUE) AS TOTAL_MAX_POINTS FROM
tat_session S, tat_session_data SD
WHERE S.SESSION_ID = SD.SESSION_ID
AND SD.PROPERTY_NAME LIKE 'totalMaxPoints%'
AND S.SUBJECT_ID=:subject_id
AND S.MODULE_ID=:module_id
GROUP BY S.SUBJECT_ID, S.SESSION_ID) B
ON B.SUBJECT_ID=A.SUBJECT_ID AND B.SESSION_ID=A.SESSION_ID
,tat_subject_group SGR, tat_group GRP, tat_group_module GRM, tat_module MDL, tat_module_study MST, tat_study STD, tat_user_study UST
		WHERE SGR.GROUP_ID = GRP.GROUP_ID
		AND GRP.GROUP_ID = GRM.GROUP_ID
		AND GRM.MODULE_ID = MDL.MODULE_ID
		AND MDL.MODULE_ID = MST.MODULE_ID
		AND MST.STUDY_ID = STD.STUDY_ID
		AND STD.STUDY_ID = UST.STUDY_ID
		AND SES.SUBJECT_ID = SGR.SUBJECT_ID
		AND SES.MODULE_ID=:module_id
		AND SES.SUBJECT_ID=:subject_id
		AND MDL.MODULE_ID=:module_id
		AND STD.STUDY_ID=:study_id
		AND UST.USER_ID=:user
order by SESS_START_DT");

$q_sess_detail->bindParam(':user', $_SESSION['USER_ID']);
$q_sess_detail->bindParam(':study_id', $study_id);
$q_sess_detail->bindParam(':module_id', $module_id);
$q_sess_detail->bindParam(':subject_id', $subject_id);
$q_sess_detail->execute();

if ($q_sess_detail->rowCount() == 0) {
  echo "<tr>";
  echo "<td colspan=7>No session data available.</td>";
  echo "</tr>";
}

$counter = 0;
$nr = 0;
$performance_total = 0;
$duration_total = 0;
while ($field = $q_sess_detail->fetch(PDO::FETCH_ASSOC)) {
  $session_id = $field['SESSION_ID'];
  $sess_start_dt = $field['SESS_START_DT'];
  $sess_end_dt = $field['SESS_END_DT'];
  $sess_completed = $field['SESS_COMPLETED'];
  $total_points = $field['TOTAL_POINTS'];
  $total_max_points = $field['TOTAL_MAX_POINTS'];
  $insert_dt = $field['INSERT_DT'];
  $prefix = $field['FILE_PREFIX'];
  $difference = strtotime($sess_end_dt) - strtotime($sess_start_dt);

  // calculate performance and duration only if session is completed
  if ($sess_completed == 1) {
	if ($total_max_points != 0) {
		$performance = floor(($total_points / $total_max_points) * 100);
	} else {
		$performance = 0;
	}
	$performance_total += $performance;
	$performance = $performance . "%";
	$hour = intval($difference / 3600);
	$min = intval(($difference / 60) % 60); 
	$sec = intval($difference % 60);
	$duration = str_pad($hour, 2, "0", STR_PAD_LEFT). ":" . str_pad($min, 2, "0", STR_PAD_LEFT). ":" . str_pad($sec, 2, "0", STR_PAD_LEFT);
	$duration_total += $difference;
	$sess_end_dt = date('d.m.Y H:i:s',strtotime($sess_end_dt));
  } else {
    $performance = "0%";
    if ($sess_end_dt == '0000-00-00 00:00:00') {
		$sess_end_dt = '-';
		$duration = "-";
	} else {
		$sess_end_dt = date('d.m.Y H:i:s',strtotime($sess_end_dt));
		$hour = intval($difference / 3600);
		$min = intval(($difference / 60) % 60);
		$sec = intval($difference % 60);
		$duration = str_pad($hour, 2, "0", STR_PAD_LEFT). ":" . str_pad($min, 2, "0", STR_PAD_LEFT). ":" . str_pad($sec, 2, "0", STR_PAD_LEFT);
	}
  }

  echo "<tr>";

  if ($sess_completed == 1) {
    $nr = $counter +1;
  } else {
    $nr = "-";
  }

  echo "<td align='center'>$nr</td>";
  echo "<td align='center'>" . date('d.m.Y H:i:s',strtotime($sess_start_dt)) . "</td>";
  echo "<td align='center'>" . $sess_end_dt . "</td>";
  echo "<td align='center'>" . $duration . "</td>";
  echo "<td align='center'>" . $performance . "</td>";

  echo "<td align='center'><a href='get_csv.php?subject_code=$subject_code&module_nr=$module_nr&study_id=$study_id&group_nr=$group_nr&prefix=$prefix'><img src='../img/online/excel.png' alt='CSV' title='CSV' border='0'></a></td>";
   
  if  ($sess_completed == 1) {
    echo "<td align=center><img src='../img/online/ok.png' alt='OK' title='OK'></td>";
  } else {
    echo "<td align=center><img src='../img/online/error.png' alt='Session has been aborted' title='Session has been aborted'></td>";
  }

  echo "</tr>";

  if ($sess_completed == 1) {
    $counter++;
  }

}

if ($counter == 0) {
	$counter = 1;
}

$hour = intval(($duration_total/$counter) / 3600);
$min = intval((($duration_total/$counter) / 60) % 60);
$sec = intval(($duration_total/$counter) % 60);
$duration = str_pad($hour, 2, "0", STR_PAD_LEFT). ":" . str_pad($min, 2, "0", STR_PAD_LEFT). ":" . str_pad($sec, 2, "0", STR_PAD_LEFT);

echo "<tr>";
echo "<td class='label' colspan=3>Mean</td><td class='label' align='center'>" . $duration ."</td>";
echo "<td class='label' align='center'>" . intval($performance_total/$counter)  . "%</td>";
echo "<td class='label' align='center'><a href='get_subject_csv.php?subject_code=$subject_code&module_nr=$module_nr&study_id=$study_id&group_nr=$group_nr'><img src='../img/online/excel_all.png' alt='CSV' title='CSV' border='0'></a></td>";
echo "<td class='label' align='center'></td>";
echo "</tr>";

?>
			</table>
			<br> <strong>Subject Log</strong> <br>
			<table class="tatool" width=800>
				<tr>
					<th>Log Date/Time</th>
					<th>Action</th>
				</tr>
				<?php
$rows = getLogEntries($study_id, $group_id, $subject_id, $module_id, 0);
 
for ($i = 0; $i < count($rows); $i++) {
  $row = $rows[$i];
  $action = "";
  if ($row['LOG_CD'] == 1) {
  } else if ($row['LOG_CD'] == 2) {
  } else if ($row['LOG_CD'] == 3) {
    $action = "Download";
  } else if ($row['LOG_CD'] == 4) {
    $action = "Upload";
  }
  echo "<tr>";
  echo "<td>" . date('d.m.Y H:i:s',strtotime($row['INSERT_DT'])) . "</td><td>" . $action . "</td>";
  echo "</tr>";
}
?>
			</table>
			<br>

			<?php
echo "<a href='module.php?study_id=$study_id&module_id=$module_id' class='text_link'>Module Overview</a> | ";
if ($blind_cd == 0) {
	echo "<a href='#' onClick=\"confirmation('subject_reset_log.php?subject_id=$subject_id&study_id=$study_id&module_id=$module_id&log_cd=3')\" class='text_link'>Delete Download History</a> | ";
	echo "<a href='#' onClick=\"confirmation('subject_reset_log.php?subject_id=$subject_id&study_id=$study_id&module_id=$module_id&log_cd=4')\" class='text_link'>Delete Upload History</a> ";
}
?>
			<br> <br>

		</div>
	</div>

</body>


</html>
