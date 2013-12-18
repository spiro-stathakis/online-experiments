<?php
include("checker.php");
include("../data/setting.php");
include("stat_functions.php");

$cnx = connectdb();

$study_id = $_GET['study_id'];
$module_nr = $_GET['module_nr'];
$group_nr = $_GET['group_nr'];
$subject_code = $_GET['subject_code'];

// check whether current study is available for user
$q_user = $cnx->prepare("SELECT USER_STUDY_ID FROM tat_user_study WHERE USER_ID=:user AND STUDY_ID=:study_id");
$q_user->bindParam(':user', $_SESSION['USER_ID']);
$q_user->bindParam(':study_id', $study_id);
$q_user->execute();

if ($q_user->rowCount() != 1) {
  header("location: ". $_SERVER['HTTP_REFERER']);
}

// set the download path
$filepath=$studiespath 
."studies"."/".$study_id
."/"."modules"
."/".$module_nr
."/"."groups"
."/".$group_nr
."/"."upload"
."/".$subject_code
."/";

$file = "trials.csv";

createSubjectCSV($study_id, $module_nr, $group_nr, $subject_code, $filepath);
getCSV($study_id, $module_id, $subject_code, $filepath, $file);

?>
