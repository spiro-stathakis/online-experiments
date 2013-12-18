<?php
include("checker.php");
include("../data/setting.php");
$cnx = connectdb();

$study_id = $_POST['study_id'];
$subject_id = $_POST['subject_id'];
$subject_name = $_POST['subject_name'];
$module_date =  $_POST['module_date'];

// check whether current study is available for user
$q_user = $cnx->prepare("SELECT USER_STUDY_ID FROM tat_user_study WHERE USER_ID=:user AND STUDY_ID=:study_id");
$q_user->bindParam(':user', $_SESSION['USER_ID']);
$q_user->bindParam(':study_id', $study_id);
$q_user->execute();

if ($q_user->rowCount() != 1) {
	header("location: ". $_SERVER['HTTP_REFERER']);
}

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
$subject_group = $_POST['subject_group'];

$q_upd_sub = $cnx->prepare("UPDATE tat_subject
		SET
		SUBJECT_NAME=:subject_name,
		SUBJECT_STATUS_CD=:subject_status_cd,
		SUBJECT_MAIL = :subject_mail,
		SUBJECT_PHONE = :subject_phone,
		SUBJECT_SEX_CD = :subject_sex_cd,
		SUBJECT_BIRTH = :subject_birth
		WHERE SUBJECT_ID=:subject_id");

$q_upd_sub->bindParam(':subject_name', $subject_name, PDO::PARAM_STR);
$q_upd_sub->bindParam(':subject_status_cd', $subject_status_cd);
$q_upd_sub->bindParam(':subject_mail', $subject_mail, PDO::PARAM_STR);
$q_upd_sub->bindParam(':subject_phone', $subject_phone, PDO::PARAM_STR);
$q_upd_sub->bindParam(':subject_sex_cd', $subject_sex_cd);
$q_upd_sub->bindParam(':subject_birth', $subject_birth);
$q_upd_sub->bindParam(':subject_id', $subject_id);
$q_upd_sub->execute();

$q_upd_sub_group = $cnx->prepare("UPDATE tat_subject_group
		SET
		GROUP_ID=:subject_group
		WHERE SUBJECT_ID=:subject_id");
$q_upd_sub_group->bindParam(':subject_group', $subject_group);
$q_upd_sub_group->bindParam(':subject_id', $subject_id);
$q_upd_sub_group->execute();


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

	$q_upd_sub_module = $cnx->prepare("UPDATE tat_subject_module
			SET
			START_DT=:start_dt, END_DT=:end_dt
			WHERE SUBJECT_ID=:subject_id
			AND MODULE_ID=:module_id");
	$q_upd_sub_module->bindParam(':module_id', $module_id);
	$q_upd_sub_module->bindParam(':subject_id', $subject_id);
	$q_upd_sub_module->bindParam(':start_dt', $start_dt);
	$q_upd_sub_module->bindParam(':end_dt', $end_dt);
	$q_upd_sub_module->execute();

	if ($q_upd_sub_module->rowCount() == 0) {
		$q_ins_sub_module = $cnx->prepare("INSERT INTO tat_subject_module
				(SUBJECT_ID, MODULE_ID, STATUS_CD, START_DT, END_DT)
				VALUES (:subject_id, :module_id, '', :start_dt, :end_dt)");
		$q_ins_sub_module->bindParam(':module_id', $module_id);
		$q_ins_sub_module->bindParam(':subject_id', $subject_id);
		$q_ins_sub_module->bindParam(':start_dt', $start_dt);
		$q_ins_sub_module->bindParam(':end_dt', $end_dt);
		$q_ins_sub_module->execute();
	}
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
