<?php
include("checker.php");
include("../data/setting.php");
$cnx = connectdb();

$study_id = $_GET['study_id'];
$subject_id = $_GET['subject_id'];
$group_id = $_GET['group_id'];

// check whether current study is available for user
$q_user = $cnx->prepare("SELECT USER_STUDY_ID FROM tat_user_study WHERE USER_ID=:user AND STUDY_ID=:study_id");
$q_user->bindParam(':user', $_SESSION['USER_ID']);
$q_user->bindParam(':study_id', $study_id);
$q_user->execute();

if ($q_user->rowCount() != 1) {
  header("location: ". $_SERVER['HTTP_REFERER']);
}

$q_del_sub = $cnx->prepare("DELETE FROM tat_subject
WHERE SUBJECT_ID=:subject_id AND STUDY_ID=:study_id");
$q_del_sub->bindParam(':study_id', $study_id);
$q_del_sub->bindParam(':subject_id', $subject_id);
$q_del_sub->execute();

$q_del_group = $cnx->prepare("DELETE FROM tat_subject_group
WHERE SUBJECT_ID=:subject_id");
$q_del_group->bindParam(':subject_id', $subject_id);
$q_del_group->execute();

$q_del_log = $cnx->prepare("DELETE FROM tat_subject_log
WHERE SUBJECT_ID=:subject_id");
$q_del_log->bindParam(':subject_id', $subject_id);
$q_del_log->execute();

$q_del_module_data = $cnx->prepare("DELETE FROM tat_module_DATA
WHERE SUBJECT_ID=:subject_id");
$q_del_module_data->bindParam(':subject_id', $subject_id);
$q_del_module_data->execute();

$q_del_acc_data = $cnx->prepare("DELETE FROM tat_account_data
WHERE SUBJECT_ID=:subject_id");
$q_del_acc_data->bindParam(':subject_id', $subject_id);
$q_del_acc_data->execute();

$q_get_session = $cnx->prepare("SELECT SESSION_ID FROM tat_session
WHERE SUBJECT_ID=:subject_id");
$q_get_session->bindParam(':subject_id', $subject_id);
$q_get_session->execute();

while ($field = $q_get_session->fetch(PDO::FETCH_ASSOC)) {
	$session_id = $field['SESSION_ID'];
	$q_del_sess_data = $cnx->prepare("DELETE FROM tat_session_data
	WHERE SESSION_ID=:session_id");
	$q_del_sess_data->bindParam(':session_id', $session_id);
	$q_del_sess_data->execute();
}

$q_del_session = $cnx->prepare("DELETE FROM tat_session
WHERE SUBJECT_ID=:subject_id");
$q_del_session->bindParam(':subject_id', $subject_id);
$q_del_session->execute();

$q_del_sub_module = $cnx->prepare("DELETE FROM tat_subject_module
WHERE SUBJECT_ID=:subject_id");
$q_del_sub_module->bindParam(':subject_id', $subject_id);
$q_del_sub_module->execute();

header("location: subjects.php?study_id=$study_id&group_id=$group_id");
?>
