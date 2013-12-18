<?php
include("checker.php");
include("../data/setting.php");
$cnx = connectdb();

$study_id = $_POST['study_id'];
$module_date =  $_POST['module_date'];
$subject_group = $_POST['subject_group'];

// check whether current study is available for user
$q_user = $cnx->prepare("SELECT USER_STUDY_ID FROM tat_user_study WHERE USER_ID=:user AND STUDY_ID=:study_id");
$q_user->bindParam(':user', $_SESSION['USER_ID']);
$q_user->bindParam(':study_id', $study_id);
$q_user->execute();

if ($q_user->rowCount() != 1) {
	header("location: ". $_SERVER['HTTP_REFERER']);
}

$subject_code = $_POST['subject_code'];
if (!isset($subject_code) || empty($subject_code)) {
	header("Location: subject_detail_new.php?study_id=$study_id&group_id=$subject_group&error=1");
	exit();
} else {
}

$subject_name = $_POST['subject_name'];
if (!isset($subject_name)) {
	$subject_name = '';
}
$subject_status_cd = $_POST['subject_status_cd'];
$subject_mail = $_POST['subject_mail'];
if (!isset($subject_mail)) {
	$subject_mail = '';
}
$subject_phone = $_POST['subject_phone'];
if (!isset($subject_phone)) {
	$subject_phone = '';
}
$subject_sex_cd = $_POST['subject_sex_cd'];
$subject_birth = $_POST['subject_birth'];
if (!isset($subject_birth) || empty($subject_birth)) {
	$subject_birth = 0;
}

$q_sub_code = $cnx->prepare("SELECT SUBJECT_ID
		FROM tat_subject
		WHERE STUDY_ID=:study_id
		AND SUBJECT_CODE=:subject_code");

$q_sub_code->bindParam(':study_id', $study_id);
$q_sub_code->bindParam(':subject_code', $subject_code, PDO::PARAM_STR);
$q_sub_code->execute();

if ($q_sub_code->rowCount() > 0) {
	header("Location: subject_detail_new.php?study_id=$study_id&group_id=$subject_group&error=1");
	exit();
}

$q_sub_insert = $cnx->prepare("INSERT INTO tat_subject
		(STUDY_ID,
		SUBJECT_STATUS_CD,
		SUBJECT_CODE,
		SUBJECT_NAME,
		SUBJECT_MAIL,
		SUBJECT_PHONE,
		SUBJECT_SEX_CD,
		SUBJECT_BIRTH)
		VALUES(:study_id,
		:subject_status_cd,
		:subject_code,
		:subject_name,
		:subject_mail,
		:subject_phone,
		:subject_sex_cd,
		:subject_birth
)");

$q_sub_insert->bindParam(':study_id', $study_id);
$q_sub_insert->bindParam(':subject_status_cd', $subject_status_cd);
$q_sub_insert->bindParam(':subject_code', $subject_code, PDO::PARAM_STR);
$q_sub_insert->bindParam(':subject_name', $subject_name, PDO::PARAM_STR);
$q_sub_insert->bindParam(':subject_mail', $subject_mail, PDO::PARAM_STR);
$q_sub_insert->bindParam(':subject_phone', $subject_phone, PDO::PARAM_STR);
$q_sub_insert->bindParam(':subject_sex_cd', $subject_sex_cd);
$q_sub_insert->bindParam(':subject_birth', $subject_birth);
$q_sub_insert->execute();

$subject_id = $cnx->lastInsertId();

$q_sub_group_insert = $cnx->prepare("INSERT INTO tat_subject_group
		(SUBJECT_ID,
		GROUP_ID)
		VALUES(
		:subject_id,
		:subject_group)");

$q_sub_group_insert->bindParam(':subject_id', $subject_id);
$q_sub_group_insert->bindParam(':subject_group', $subject_group);
$q_sub_group_insert->execute();

foreach ( $module_date as $key => $value ) {

	$module_id = $key;

	$start_dt = dmy2mysql($value[0]);
	$end_dt = dmy2mysql($value[1]);

	if ($start_dt == null || empty($start_dt)) {
		$start_dt = "0000-00-00 00:00:00";
	}
	if ($end_dt == null || empty($end_dt)) {
		$end_dt = "0000-00-00 00:00:00";
	}

	$q_ins_sub_module = $cnx->prepare("INSERT INTO tat_subject_module
			(SUBJECT_ID, MODULE_ID, STATUS_CD, START_DT, END_DT)
			VALUES (:subject_id, :module_id, '', :start_dt, :end_dt)");
	$q_ins_sub_module->bindParam(':module_id', $module_id);
	$q_ins_sub_module->bindParam(':subject_id', $subject_id);
	$q_ins_sub_module->bindParam(':start_dt', $start_dt);
	$q_ins_sub_module->bindParam(':end_dt', $end_dt);
	$q_ins_sub_module->execute();

}

function dmy2mysql($input) {
	$output = false;
	$d = preg_split('#[-/:. ]#', $input);
	if (is_array($d) && count($d) == 3) {
		if (checkdate($d[1], $d[0], $d[2])) {
			$output = "$d[2]-$d[1]-$d[0]";
		}
	}

	return $output;
}

header("location: subjects.php?study_id=$study_id&group_id=$subject_group");
?>
