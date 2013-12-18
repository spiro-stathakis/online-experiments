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

.tatool .done {
	border: 1px solid #19191a;
	padding: 6px;
	background-color: #efffef;
}

.tatool .done_miss {
	border: 1px solid #19191a;
	padding: 6px;
	background-color: #feffc7;
}

.tatool .abort {
	border: 1px solid #19191a;
	padding: 6px;
	background-color: #ecebeb;
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
				alt="Tatool online" title="Tatool online" border='0'> </a><br> <img
				src="../img/online/title_study_module.png">
		</div>
		<div style="width: 600px; float: right;">
			<table class="tatool" align="right">
				<tr>
					<th></th>
					<th>Group</th>
					<th><img src="../img/online/download.png" alt="Download"
						title="Download" style="margin-top: 2px;"></th>
					<th><img src="../img/online/upload.png" alt="Upload" title="Upload"
						style="margin-top: 2px;"></th>
					<th><img src="../img/online/idle.png" alt="Idle" title="Idle"
						style="margin-top: 2px;"></th>
					<th><img src="../img/online/male.png" alt="Male" title="Male"
						style="margin-top: 2px;"></th>
					<th><img src="../img/online/female.png" alt="Female" title="Female"
						style="margin-top: 2px;"></th>
					<th>&#8721;</th>
				</tr>
				<?php
				include("../data/setting.php");
				$cnx = connectdb();

				$study_id= $_GET['study_id'];
				$module_id= $_GET['module_id'];

				// check whether current study should be blind for user
				$q_user = $cnx->prepare("SELECT BLIND_CD FROM tat_user_study WHERE USER_ID=:user AND STUDY_ID=:study_id");
				$q_user->bindParam(':user', $_SESSION['USER_ID']);
				$q_user->bindParam(':study_id', $study_id);
				$q_user->execute();
				$blind_cd = 0;

				while ($field = $q_user->fetch(PDO::FETCH_ASSOC)) {
					$blind_cd = $field['BLIND_CD'];
				}

				// select subject totals
				$q_sub_total = $cnx->prepare("SELECT STUDY_ID,
						GROUP_ID,
						GROUP_NR,
						GROUP_NAME,
						COUNT(SUBJECT_ID) AS TOTAL,
						SUM(MALE) AS MALE,
						SUM(FEMALE) AS FEMALE,
						SUM(DOWNLOAD_FLG) AS DOWNLOAD_CNT,
						SUM(UPLOAD_FLG) AS UPLOAD_CNT
						FROM
						(SELECT STUDY_ID,
						GROUP_ID,
						GROUP_NR,
						GROUP_NAME,
						SUBJECT_ID,
						MAX(MALE) AS MALE,
						MAX(FEMALE) AS FEMALE,
						CASE
						WHEN SUM(DOWNLOAD_CNT) > 0 THEN 1
						ELSE 0
						END AS DOWNLOAD_FLG,
						SUM(DOWNLOAD_CNT) AS DOWNLOAD_CNT,
						CASE
						WHEN SUM(UPLOAD_CNT) > 0 THEN 1
						ELSE 0
						END AS UPLOAD_FLG,
						SUM(UPLOAD_CNT) AS UPLOAD_CNT,
						MAX(DOWNLOAD_DT) AS DOWNLOAD_DT,
						MAX(UPLOAD_DT) AS UPLOAD_DT
						FROM
						(SELECT S.STUDY_ID,
						G.GROUP_ID,
						G.GROUP_NR,
						GROUP_NAME,
						S.SUBJECT_ID,
						CASE S.SUBJECT_SEX_CD
						WHEN 1 THEN 1
						ELSE 0
						END AS MALE,
						CASE S.SUBJECT_SEX_CD
						WHEN 2 THEN 1
						ELSE 0
						END AS FEMALE,
						CASE L.LOG_CD
						WHEN 3 THEN 1
						ELSE 0
						END AS DOWNLOAD_CNT,
						CASE L.LOG_CD
						WHEN 4 THEN 1
						ELSE 0
						END AS UPLOAD_CNT,
						CASE L.LOG_CD
						WHEN 3 THEN INSERT_DT
						ELSE '0000-00-00 00:00:00'
						END AS DOWNLOAD_DT,
						CASE L.LOG_CD
						WHEN 4 THEN INSERT_DT
						ELSE '0000-00-00 00:00:00'
						END AS UPLOAD_DT
						FROM tat_subject S
						LEFT OUTER JOIN
						tat_subject_log L
						ON S.SUBJECT_ID = L.SUBJECT_ID AND L.STUDY_ID=:study_id AND L.MODULE_ID=:module_id,
						tat_subject_group SG,
						tat_group G,
						tat_group_module GM,
						tat_module M,
						tat_module_study MS,
						tat_study STD,
						tat_user_study UST
						WHERE S.SUBJECT_ID = SG.SUBJECT_ID
						AND SG.GROUP_ID = G.GROUP_ID
						AND G.GROUP_ID = GM.GROUP_ID
						AND GM.MODULE_ID = M.MODULE_ID
						AND M.MODULE_ID = MS.MODULE_ID
						AND MS.STUDY_ID = STD.STUDY_ID
						AND STD.STUDY_ID = UST.STUDY_ID
						AND S.SUBJECT_STATUS_CD!=2
						AND STD.STUDY_ID=$study_id
						AND M.MODULE_ID=$module_id
						AND UST.USER_ID=:user) QRY
						GROUP BY STUDY_ID, GROUP_ID, SUBJECT_ID) QRY
						GROUP BY STUDY_ID, GROUP_ID");
				$q_sub_total->bindParam(':user', $_SESSION['USER_ID']);
				$q_sub_total->bindParam(':study_id', $study_id);
				$q_sub_total->bindParam(':module_id', $module_id);
				$q_sub_total->execute();

				$group_overall_total = 0;
				$group_overall_a_down = 0;
				$group_overall_a_up = 0;
				$group_overall_idle = 0;
				$group_overall_male = 0;
				$group_overall_female = 0;

				while ($field = $q_sub_total->fetch(PDO::FETCH_ASSOC)) {
  $group_id = $field['GROUP_ID'];
  $group_nr = $field['GROUP_NR'];
  $group_name = $field['GROUP_NAME'];
  $group_total = $field['TOTAL'];
  $group_active_down = $field['DOWNLOAD_CNT'];
  $group_active_up = $field['UPLOAD_CNT'];
  $group_male = $field['MALE'];
  $group_female = $field['FEMALE'];
  $group_idle = $group_total - $group_active_down;

  $group_overall_total += $group_total;
  $group_overall_a_down +=  $group_active_down;
  $group_overall_a_up += $group_active_up;
  $group_overall_idle += $group_idle;
  $group_overall_male += $group_male;
  $group_overall_female += $group_female;

  $group_img_id = $group_nr;
  echo "<tr>";
  echo "<td><img src='../img/online/group_$group_img_id.png' alt='$group_name' title='$group_name'></td><td><a href='group_data.php?study_id=$study_id&module_id=$module_id&group_id=$group_id' class='ext_link'>$group_name</a></td><td align=center>" . $group_active_down . "</td><td align=center>" . $group_active_up . "</td><td align=center>" . $group_idle . "</td><td align=center>" . $group_male . "</td><td align=center>" . $group_female . "</td><td align=center>" . $group_total . "</td>";
  echo "</tr>";
}
echo "<tr>";
echo "<td class='total'></td><td class='total'></td><td class='total'>" . $group_overall_a_down . "</td><td class='total'>" . $group_overall_a_up . "</td><td class='total'>" . $group_overall_idle . "</td><td class='total'>" . $group_overall_male . "</td><td class='total'>" . $group_overall_female . "</td><td class='total'>" . $group_overall_total . "</td>";
echo "</tr>";
?>
			</table>

			<table class="tatool">
				<?php

				// select module details
				$q_module_info = $cnx->prepare("SELECT STUDY_NAME, MODULE_NAME, MODULE_TYPE_CD, LIMIT_DOWN_NUM, LIMIT_SESS_NUM, NOTICE_UP_FREE_HR_NUM, NOTICE_SCHED_VALUE_NUM, SCHEDULER
FROM tat_study S, tat_module M, tat_module_study MS, tat_user_study UST
WHERE
UST.STUDY_ID = S.STUDY_ID
AND S.STUDY_ID = MS.STUDY_ID
AND MS.MODULE_ID = M.MODULE_ID
AND UST.USER_ID=:user
AND S.STUDY_ID=:study_id
AND M.MODULE_ID=:module_id");

$q_module_info->bindParam(':user', $_SESSION['USER_ID']);
$q_module_info->bindParam(':study_id', $study_id);
$q_module_info->bindParam(':module_id', $module_id);
$q_module_info->execute();

$limit_sess_num = 0;
$notice_up_free_hr_num = 0;
$notice_sched_value_num = 0;
$scheduler = "";
while ($field = $q_module_info->fetch(PDO::FETCH_ASSOC)) {
  $study_name = $field['STUDY_NAME'];
  $module_name = $field['MODULE_NAME'];
  $module_type_cd = $field['MODULE_TYPE_CD'];
  $limit_down_num = $field['LIMIT_DOWN_NUM'];
  $limit_sess_num = $field['LIMIT_SESS_NUM'];
  $notice_up_free_hr_num = $field['NOTICE_UP_FREE_HR_NUM'];
  $notice_sched_value_num = $field['NOTICE_SCHED_VALUE_NUM'];
  $scheduler = $field['SCHEDULER'];

  echo "<tr>";
  echo "<td class='label'><strong>Study</strong></td><td>" . $study_name . "</td>";
  echo "</tr>";
  echo "<tr>";
  echo "<td class='label'><strong>Module</strong></td><td>" . $module_name . "</td>";
  echo "</tr>";

  echo "<tr>";
  if ($module_type_cd == 1) {
    echo "<td class='label'><strong>Module Type</strong></td><td>Private</td>";
  } else if ($module_type_cd ==2) {
    echo "<td class='label'><strong>Module Type</strong></td><td>Public</td>";
  } else {
    echo "<td class='label'><strong>Module Type</strong></td><td></td>";
  }
  echo "</tr>";

  echo "<tr>";
  echo "<td class='label'><strong>Download Limit</strong></td><td>" . $limit_down_num . "</td>";
  echo "</tr>";
}
?>
			</table>

		</div>
		<div style="clear: both"></div>
		<div
			style="width: 800px; float: left; margin-top: 20px; margin-bottom: 20px">
			<table class="tatool" width=800>
				<tr>
					<th></th>
					<!-- Number -->
					<th></th>
					<!-- Sex -->
					<!-- Group -->
					<?php
					if ($blind_cd==0) {
            			echo "<th></th>";
          			}
          			?>
					<th><a
						href="<?php echo "module.php?study_id=$study_id&module_id=$module_id&order=name";?>"
						class="top_link">Name</a></th>
					<th><a
						href="<?php echo "module.php?study_id=$study_id&module_id=$module_id&order=start";?>"
						class="top_link">Start Date</a></th>
					<th><a
						href="<?php echo "module.php?study_id=$study_id&module_id=$module_id&order=end";?>"
						class="top_link">End Date</a></th>
					<th><a
						href="<?php echo "module.php?study_id=$study_id&module_id=$module_id&order=session";?>"
						class="top_link">Sessions</a></th>
					<th><a
						href="<?php echo "module.php?study_id=$study_id&module_id=$module_id&order=download";?>"
						class="top_link">Download</a></th>
					<th><a
						href="<?php echo "module.php?study_id=$study_id&module_id=$module_id&order=upload";?>"
						class="top_link">Last Upload</a></th>
					<th><img src="../img/online/upload.png" alt="Upload Status"
						title="Upload Status"></th>
				</tr>

				<?php
				if (isset($_GET['order'])) {

if ($_GET['order'] == "download") {
  $order_cd = "SUBJECT_STATUS_CD, DNL.LAST_DOWNLOAD DESC, UPL.LAST_UPLOAD DESC, ISNULL ASC, SUBJECT_NAME ASC";
} else if ($_GET['order'] == "upload") {
  $order_cd = "SUBJECT_STATUS_CD, UPL.LAST_UPLOAD DESC, DNL.LAST_DOWNLOAD DESC, ISNULL ASC, SUBJECT_NAME ASC";
} else if ($_GET['order'] == "code") {
  $order_cd = "SUBJECT_CODE ASC, ISNULL ASC";
} else if ($_GET['order'] == "name") {
  $order_cd = "ISNULL ASC, SUBJECT_NAME ASC";
} else if ($_GET['order'] == "session") {
  $order_cd = "ISNULL ASC, ANZ_SESS DESC, UPL.LAST_UPLOAD DESC, DNL.LAST_DOWNLOAD DESC, SUBJECT_NAME ASC";
} else if ($_GET['order'] == "start") {
  $order_cd = "ISNULL ASC, START_DT ASC, SUBJECT_NAME ASC";
} else if ($_GET['order'] == "end") {
  $order_cd = "ISNULL ASC, START_DT ASC, SUBJECT_NAME ASC";
}
} else {
	$order_cd = "SUBJECT_STATUS_CD, UPL.LAST_UPLOAD DESC, DNL.LAST_DOWNLOAD DESC, ISNULL ASC, SUBJECT_NAME ASC";
}

// get subjects

$q_subject = $cnx->prepare("SELECT SUB.SUBJECT_ID, SUBGRP.GROUP_ID, GRP.GROUP_NAME, GRP.GROUP_NR, SUBJECT_NAME, SUBJECT_CODE, SUBJECT_STATUS_CD, SUBJECT_SEX_CD, SUBJECT_MAIL, START_DT, END_DT, SES.ANZ_SESS, UPL.LAST_UPLOAD, DNL.LAST_DOWNLOAD, IF(SUBJECT_NAME IS NULL or SUBJECT_NAME=' ', 1, 0) AS ISNULL
FROM tat_subject SUB
INNER JOIN
tat_subject_group SUBGRP
ON SUB.SUBJECT_ID = SUBGRP.SUBJECT_ID
INNER JOIN
tat_group GRP
ON SUBGRP.GROUP_ID = GRP.GROUP_ID
INNER JOIN
tat_group_module GRPMDL
ON GRP.GROUP_ID = GRPMDL.GROUP_ID
INNER JOIN
tat_module MDL
ON GRPMDL.MODULE_ID = MDL.MODULE_ID
INNER JOIN
tat_module_study MST
ON MDL.MODULE_ID = MST.MODULE_ID
INNER JOIN
tat_study STD
ON MST.STUDY_ID = STD.STUDY_ID
INNER JOIN
tat_user_study UST
ON STD.STUDY_ID = UST.STUDY_ID
INNER JOIN
tat_subject_module SUBMDL
ON MDL.MODULE_ID = SUBMDL.MODULE_ID AND SUB.SUBJECT_ID  = SUBMDL.SUBJECT_ID
LEFT OUTER JOIN
(SELECT SUBJECT_ID, MODULE_ID, COUNT(*) AS ANZ_SESS
FROM tat_session
WHERE SESS_COMPLETED = 1
AND MODULE_ID=:module_id
GROUP BY SUBJECT_ID, MODULE_ID) SES
ON SUB.SUBJECT_ID = SES.SUBJECT_ID
LEFT OUTER JOIN
(SELECT SUBJECT_ID, MODULE_ID, MAX(INSERT_DT) AS LAST_UPLOAD
FROM tat_subject_log
WHERE LOG_CD=4
AND MODULE_ID=:module_id
AND STUDY_ID=:study_id
GROUP BY SUBJECT_ID, MODULE_ID) UPL
ON SUB.SUBJECT_ID = UPL.SUBJECT_ID
LEFT OUTER JOIN
(SELECT SUBJECT_ID, MODULE_ID, MAX(INSERT_DT) AS LAST_DOWNLOAD
FROM tat_subject_log
WHERE LOG_CD=3
AND MODULE_ID=:module_id
AND STUDY_ID=:study_id
GROUP BY SUBJECT_ID, MODULE_ID) DNL
ON SUB.SUBJECT_ID = DNL.SUBJECT_ID
WHERE STD.STUDY_ID=:study_id
AND MDL.MODULE_ID=:module_id
AND UST.USER_ID=:user
ORDER BY $order_cd");

$q_subject->bindParam(':user', $_SESSION['USER_ID']);
$q_subject->bindParam(':study_id', $study_id);
$q_subject->bindParam(':module_id', $module_id);
$q_subject->execute();

$nr = 0;
while ($field = $q_subject->fetch(PDO::FETCH_ASSOC)) {
  $subject_name = $field['SUBJECT_NAME'];
  $subject_code = $field['SUBJECT_CODE'];
  $subject_sex = $field['SUBJECT_SEX_CD'];
  $subject_mail = $field['SUBJECT_MAIL'];
  $subject_status_cd = $field['SUBJECT_STATUS_CD'];
  $sessions = $field['ANZ_SESS'];
  $uploadDate =  $field['LAST_UPLOAD'];
  $downloadDate =  $field['LAST_DOWNLOAD'];
  $subject_id = $field['SUBJECT_ID'];
  $group_id = $field['GROUP_ID'];
  $group_nr = $field['GROUP_NR'];
  $group_name = $field['GROUP_NAME'];
  $start_dt = $field['START_DT'];
  $end_dt = $field['END_DT'];
  $nr++;

  $diff_up = time() - strtotime($uploadDate);
  if ($diff_up > 0 && $uploadDate) {
    $duration = floor($diff_up / 60 / 60);
  } else {
    $duration = -1;
  }

  if ($sessions >= $limit_sess_num) {
    echo "<tr class='done'>";
  } else if ($subject_status_cd == 1) {
    echo "<tr class='done_miss'>";
  } else if ($subject_status_cd == 2) {
    echo "<tr class='abort'>";
  } else {
    echo "<tr>";
  }


  echo "<td class='total'>" . $nr . "</td>";

  if  ($subject_sex == 1) {
    echo "<td align=center><img src='../img/online/male.png' alt='Male' title='Male'></td>";
  } else if ($subject_sex == 2) {
    echo "<td align=center><img src='../img/online/female.png' alt='Female' title='Female'></td>";
  } else {
    echo "<td align=center><img src='../img/online/unknown.gif' alt='Unknown' title='Unknown'></td>";
  }

  if ($blind_cd==0) {
    $group_img_id = $group_nr;
    if  ($group_id < 0) {
      echo "<td align=center></td>";
    } else {
      echo "<td align=center><img src='../img/online/group_$group_img_id.png' alt='$group_name' title='$group_name'></td>";
    }
  }

  if (empty($subject_name)) {
    $name = $subject_code;
  } else {
    $name = $subject_name;
  }
  $mail = "(<a href='contact.php?subject_id=$subject_id&study_id=$study_id&module_id=$module_id' class='text_link'>e</a>)";
  echo "<td><a href='subject_data.php?subject_id=$subject_id&study_id=$study_id&module_id=$module_id' class='ext_link'>$name</a> $mail</td>";

  $start_dt_conv = "";
  if ($start_dt == null || $start_dt == "0000-00-00 00:00:00") {
  	$start_dt_conv = "";
  } else {
  	$start_dt_conv = date('d.m.Y',strtotime($start_dt));
  }
  echo "<td align=center>".$start_dt_conv."</td>";

  $end_dt_conv = "";
  if ($end_dt == null || $end_dt == "0000-00-00 00:00:00") {
  	$end_dt_conv = "";
  } else {
  	$end_dt_conv = date('d.m.Y',strtotime($end_dt));
  }
  echo "<td align=center>".$end_dt_conv."</td>";

  if (strcmp($scheduler, "DailyModuleScheduler") == 0) {
	$module_length = strtotime($end_dt) - time();
	if ($module_length > 0 && $limit_sess_num > 0) {
		$module_days_total = intval($module_length / 60 / 60 / 24);
		$module_days_left = $module_days_total - ($limit_sess_num - $sessions);
		if ($module_days_left < $notice_sched_value_num) {
			echo "<td align=center class='label'>$sessions <span style='color:#b72e00;font-weight:bold'>($module_days_left)</span></td>";
		} else {
			echo "<td align=center>$sessions ($module_days_left)</td>";
		}
	} else {
		echo "<td align=center>$sessions</td>";
	}
  } else {
	echo "<td align=center>$sessions</td>";
  }

  if  ($downloadDate == "") {
    echo "<td></td>";
  } else {
    echo "<td align='center'>" . date('d.m.Y H:i:s',strtotime($downloadDate)) . "</td>";
  }

  if  ($uploadDate == "") {
    echo "<td></td>";
  } else {
    echo "<td align='center'>" . date('d.m.Y H:i:s',strtotime($uploadDate)) . "</td>";
  }

  $duration_final = "";
  $duration_days = intval($duration / 24);
  $duration_hours = $duration % 24;
  if ($duration_days > 0) {
	$duration_final = $duration_days . " days";
  }
  if ($duration_hours > 0) {
  	$duration_final .= " " . $duration_hours . " hours";
  }

  if  ($notice_up_free_hr_num > 0 && $duration >= $notice_up_free_hr_num && $sessions < $limit_sess_num && $subject_status_cd < 1) {
    echo "<td align=center><img src='../img/online/error.png' alt='Last upload more than $duration_final ago' title='Last upload more than $duration_final ago'></td>";
  } else if ($notice_up_free_hr_num > 0 && $duration >= 0 && $duration < $notice_up_free_hr_num && $subject_status_cd < 1 && $sessions < $limit_sess_num) {
    echo "<td align=center><img src='../img/online/ok.png' alt='OK' title='OK'></td>";
  } else {
    echo "<td align=center></td>";
  }

  echo "</tr>";
}

?>
			</table>
		</div>
	</div>

</body>


</html>
