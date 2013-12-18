<?php
include("../data/setting.php");
$cnx = connectdb();

$study_id= $_GET['study_id'];
$subject_id= $_GET['subject_id'];
$group_id= $_GET['group_id'];

$q_sub = $cnx->prepare("SELECT SUB.SUBJECT_ID, SUBJECT_STATUS_CD, SUBJECT_CODE, SUBJECT_NAME, SUBJECT_MAIL, SUBJECT_PHONE, SUBJECT_SEX_CD, SUBJECT_BIRTH, SUBJECT_EDUCATION, GRP.GROUP_ID, GRP.GROUP_NR, GRP.GROUP_NAME
		FROM tat_user_study UST
		, tat_study STD
		, tat_group GRP
		, tat_subject_group SUG
		, tat_subject SUB
		WHERE UST.STUDY_ID = STD.STUDY_ID
		AND STD.STUDY_ID = SUB.STUDY_ID
		AND SUG.GROUP_ID = GRP.GROUP_ID
		AND SUG.SUBJECT_ID = SUB.SUBJECT_ID
		AND UST.USER_ID = :user
		AND STD.STUDY_ID = :study_id
		AND SUB.STUDY_ID = :study_id
		AND SUB.SUBJECT_ID = :subject_id");

$q_sub->bindParam(':user', $_SESSION['USER_ID']);
$q_sub->bindParam(':study_id', $study_id);
$q_sub->bindParam(':subject_id', $subject_id);
$q_sub->execute();

$q_group = $cnx->prepare("SELECT GROUP_ID, GROUP_NR, GROUP_NAME
		FROM tat_group
		WHERE STUDY_ID = :study_id");
$q_group->bindParam(':study_id', $study_id);
$q_group->execute();

$groups = array();
$counter = 0;
while ($field = $q_group->fetch(PDO::FETCH_ASSOC)) {
	$group_id =  $field['GROUP_ID'];
	$group_name =  $field['GROUP_NAME'];
	$groups[$counter]['group_id'] = $group_id;
	$groups[$counter]['group_name'] = $group_name;
	$counter++;
}

$q_group_module = $cnx->prepare("SELECT DISTINCT MDL.MODULE_ID, MDL.MODULE_NAME, SMO.START_DT, SMO.END_DT
		FROM tat_group_module GRM,
		tat_module MDL
		LEFT OUTER JOIN
		tat_subject_module SMO
		ON SMO.MODULE_ID = MDL.MODULE_ID AND SMO.SUBJECT_ID = :subject_id
		WHERE MDL.STUDY_ID = :study_id
		AND GRM.GROUP_ID = :group_id");
$q_group_module->bindParam(':study_id', $study_id);
$q_group_module->bindParam(':group_id', $group_id);
$q_group_module->bindParam(':subject_id', $subject_id);
$q_group_module->execute();

$subject_modules = array();
$counter = 0;
while ($field = $q_group_module->fetch(PDO::FETCH_ASSOC)) {
	$module_id =  $field['MODULE_ID'];
	$module_name =  $field['MODULE_NAME'];
	$module_start_dt = $field['START_DT'];
	$module_end_dt = $field['END_DT'];
	$subject_modules[$counter]['module_id'] = $module_id;
	$subject_modules[$counter]['module_name'] = $module_name;
	$subject_modules[$counter]['start_dt'] = $module_start_dt;
	$subject_modules[$counter]['end_dt'] = $module_end_dt;
	$counter++;
}

?>

<form id="edit_form"
	action="subject_detail_edit.php" method="post">
	<!-- table header -->
	<strong>Subject Details</strong>
	<table class="tatool">
		<tr>
			<th>Code</th>
			<th>Status</th>
			<th>Name</th>
			<th>Mail</th>
			<th>Phone</th>
			<th>Sex</th>
			<th>Birth</th>
			<th>Group</th>
		</tr>

		<!-- table rows -->
		<?php
		$last_study_id = 0;
		$last_module_id = 0;
		while ($field = $q_sub->fetch(PDO::FETCH_ASSOC)) {
			$subject_id = $field['SUBJECT_ID'];
			$subject_code = $field['SUBJECT_CODE'];
			$subject_status = $field['SUBJECT_STATUS_CD'];
			$subject_name= $field['SUBJECT_NAME'];
			$subject_mail= $field['SUBJECT_MAIL'];
			$subject_phone= $field['SUBJECT_PHONE'];
			$subject_sex= $field['SUBJECT_SEX_CD'];
			$subject_birth= $field['SUBJECT_BIRTH'];
			$subject_education= $field['SUBJECT_EDUCATION'];
			$group_id= $field['GROUP_ID'];
			$group_name= $field['GROUP_NAME'];

			echo "<tr>";
			echo "<td>$subject_code</td>";

			// STATUS_CD
			echo "<td><select name='subject_status_cd'>";
			if ($subject_status == 0) {
				echo "<option value='0' selected>active</option>";
				echo "<option value='1'>done</option>";
				echo "<option value='2'>dropout</option>";
			} else if ($subject_status == 1) {
				echo "<option value='0'>active</option>";
				echo "<option value='1' selected>done</option>";
				echo "<option value='2'>dropout</option>";
			} else if ($subject_status == 2) {
				echo "<option value='0'>active</option>";
				echo "<option value='1'>done</option>";
				echo "<option value='2' selected>dropout</option>";
			}
			echo "</select></td>";

			echo "<td><input type='text' name='subject_name' value='$subject_name'></td>";
			echo "<td><input type='text' name='subject_mail' value='$subject_mail'></td>";
			echo "<td><input type='text' name='subject_phone' value='$subject_phone' size=15></td>";

			// SEX
			echo "<td><select name='subject_sex_cd'>";
			if ($subject_sex == 1) {
    echo "<option value='0'>-</option>";
    echo "<option value='1' selected>M</option>";
    echo "<option value='2'>F</option>";
  } else if ($subject_sex == 2) {
    echo "<option value='0'>-</option>";
    echo "<option value='1'>M</option>";
    echo "<option value='2' selected>F</option>";
  } else {
    echo "<option value='0' selected>-</option>";
    echo "<option value='1'>M</option>";
    echo "<option value='2'>F</option>";
  }
  echo "</select></td>";

  echo "<td><input type='text' name='subject_birth' value='$subject_birth' size=2></td>";

  echo "<td><select name='subject_group'>";
  for ($k = 0; $k < count($groups); $k++) {
    if ($groups[$k]['group_name'] == $group_name) {
      echo "<option value='" . $groups[$k]['group_id'] . "' selected>" . $groups[$k]['group_name'] . "</option>";
    } else {
      echo "<option value='" . $groups[$k]['group_id'] . "'>" . $groups[$k]['group_name'] . "</option>";
    }
  }
  echo "</select></td>";

  echo "</tr>";

  echo "<input type=hidden name='study_id' value=$study_id>";
  echo "<input type=hidden name='subject_id' value=$subject_id>";

		}
		?>

	</table>
	<br>
	
	<strong>Module Configuration</strong>
	<table class="tatool">
		<tr>
			<th>Module</th>
			<th>Start Date</th>
			<th>End Date</th>
		</tr>
		
	<?php 
	
	for ($k = 0; $k < count($subject_modules); $k++) {
		$module_start_conv =  "";
		$module_end_conv = "";
		
		if ($subject_modules[$k]['start_dt'] == null || $subject_modules[$k]['start_dt'] == "0000-00-00 00:00:00") {
			$module_start_conv = "";
		} else {
			$module_start_conv = date('d.m.Y',strtotime($subject_modules[$k]['start_dt']));
		}
		
		if ($subject_modules[$k]['end_dt'] == null || $subject_modules[$k]['end_dt'] == "0000-00-00 00:00:00") {
			$module_end_conv = "";
		} else {
			$module_end_conv = date('d.m.Y',strtotime($subject_modules[$k]['end_dt']));
		}

		echo "<tr>";
		echo "<td>" .  $subject_modules[$k]['module_name'] . "</td>";
		echo "<td><input type='text' name='module_date[" . $subject_modules[$k]['module_id'] . "][]' maxlength=10 value='". $module_start_conv . "'></td>";
		echo "<td><input type='text' name='module_date[" . $subject_modules[$k]['module_id'] . "][]' maxlength=10 value='". $module_end_conv . "'></td>";
		echo "</tr>";
	}
	?>
	</table>
	
	<br>
	<center>
		<a href='javascript: submitform()' class='grey-button pcb'> <span>Save
				changes</span>
		</a>
		<?php
		echo "<a href='#' class='grey-button pcb' onclick=\"window.location.href='subjects.php?study_id=$study_id&group_id=$group_id'\">
		<span>Cancel</span>
		</a>";
		?>
	</center>
</form>
