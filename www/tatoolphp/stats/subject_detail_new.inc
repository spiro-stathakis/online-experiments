<?php
include("../data/setting.php");
$cnx = connectdb();

$study_id = -1;
$group_id_manage = -1;
$error = "";

if (isset($_GET['study_id'])) {
	$study_id = $_GET['study_id'];
}
if (isset($_GET['group_id'])) {
	$group_id_manage = $_GET['group_id'];
}
if (isset($_GET['error'])) {
	$error = $_GET['error'];
}

// check whether current study is available for user
$q_user = $cnx->prepare("SELECT USER_STUDY_ID FROM tat_user_study WHERE USER_ID=:user AND STUDY_ID=:study_id");
$q_user->bindParam(':user', $_SESSION['USER_ID']);
$q_user->bindParam(':study_id', $study_id);
$q_user->execute();

if ($q_user->rowCount() != 1) {
	header("location: ". $_SERVER['HTTP_REFERER']);
}

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

$q_group_module = $cnx->prepare("SELECT DISTINCT MDL.MODULE_ID, MDL.MODULE_NAME
		FROM tat_group_module GRM,
		tat_module MDL
		WHERE MDL.STUDY_ID = :study_id
		AND GRM.GROUP_ID = :group_id");
$q_group_module->bindParam(':study_id', $study_id);
$q_group_module->bindParam(':group_id', $group_id);
$q_group_module->execute();

$subject_modules = array();
$counter = 0;
while ($field = $q_group_module->fetch(PDO::FETCH_ASSOC)) {
	$module_id =  $field['MODULE_ID'];
	$module_name =  $field['MODULE_NAME'];
	$subject_modules[$counter]['module_id'] = $module_id;
	$subject_modules[$counter]['module_name'] = $module_name;
	$counter++;
}

if ($error == 1) {
	echo "<table class='tatool' width=870>";
	echo "<tr>";
	echo "<th>Error: The code has to be unique within a study.</th>";
	echo "</tr>";
	echo "</table><br>";
}

?>
<form action="subject_detail_add.php" method="post"
	name="add_form">
	<!-- table header -->
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

		echo "<tr>";
		echo "<td><input type='text' name='subject_code' value='' maxlength=10 size=7></td>";

		// STATUS_CD
		echo "<td><select name='subject_status_cd'>";
		echo "<option value='0'>active</option>";
		echo "<option value='1'>done</option>";
		echo "<option value='2'>dropout</option>";
		echo "</select></td>";

		echo "<td><input type='text' name='subject_name' value=''></td>";
		echo "<td><input type='text' name='subject_mail' value=''></td>";
		echo "<td><input type='text' name='subject_phone' value='' size=15></td>";

		// SEX
		echo "<td><select name='subject_sex_cd'>";
		echo "<option value='0'>-</option>";
		echo "<option value='1'>M</option>";
		echo "<option value='2'>F</option>";
		echo "</select></td>";

		echo "<td><input type='text' name='subject_birth' value='' size=2></td>";

		echo "<td><select name='subject_group'>";
		for ($k = 0; $k < count($groups); $k++) {
    echo "<option value='" . $groups[$k]['group_id'] . "'>" . $groups[$k]['group_name'] . "</option>";
  }
  echo "</select></td>";

  echo "</tr>";

  echo "<input type=hidden name='study_id' value=$study_id>";

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
		echo "<tr>";
		echo "<td>" .  $subject_modules[$k]['module_name'] . "</td>";
		echo "<td><input type='text' name='module_date[" . $subject_modules[$k]['module_id'] . "][]' maxlength=10 value=''></td>";
		echo "<td><input type='text' name='module_date[" . $subject_modules[$k]['module_id'] . "][]' maxlength=10 value=''></td>";
		echo "</tr>";
	}
	?>
	</table>
	
	<br>
	<center>
		<a href='javascript: submitform()' class='grey-button pcb'> <span>Add
				subject</span>
		</a>
		<?php
echo "<a href='#' class='grey-button pcb' onclick=\"window.location.href='subjects.php?study_id=$study_id&group_id=$group_id_manage'\">
    <span>Cancel</span>
  </a>";
?>
	</center>
</form>
