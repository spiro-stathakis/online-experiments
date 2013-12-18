<?php
include("../data/setting.php");
$cnx = connectdb();

$study_id = $_GET['study_id'];
$subject_id = $_GET['subject_id'];
$module_id = $_GET['module_id'];
$log_cd = $_GET['log_cd'];

// check whether current study is available for user
$q_user = $cnx->prepare("SELECT USER_STUDY_ID FROM tat_user_study WHERE USER_ID=:user AND STUDY_ID=:study_id");
$q_user->bindParam(':user', $_SESSION['USER_ID']);
$q_user->bindParam(':study_id', $study_id);
$q_user->execute();

if ($q_user->rowCount() != 1) {
  header("location: ". $_SERVER['HTTP_REFERER']);
}

$q_del_log = $cnx->prepare("DELETE
FROM tat_subject_log
WHERE SUBJECT_ID=:subject_id
AND STUDY_ID=:study_id
AND MODULE_ID=:module_id
AND LOG_CD=:log_cd");

$q_del_log->bindParam(':module_id', $module_id);
$q_del_log->bindParam(':study_id', $study_id);
$q_del_log->bindParam(':subject_id', $subject_id);
$q_del_log->bindParam(':log_cd', $log_cd);
$q_del_log->execute();

header("location: subject_data.php?subject_id=$subject_id&study_id=$study_id&module_id=$module_id");
?>
