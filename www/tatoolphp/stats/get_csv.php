<?php
include("checker.php");
include("../data/setting.php");

$cnx = connectdb();

$study_id = $_GET['study_id'];
$module_nr = $_GET['module_nr'];
$group_nr = $_GET['group_nr'];
$subject_code = $_GET['subject_code'];
$prefix = $_GET['prefix'];

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

$file = $prefix . "_trialData.csv";

if (file_exists($filepath . $file))
{
  $fp = @fopen($filepath . $file, 'rb');

  if (strstr($_SERVER['HTTP_USER_AGENT'], "MSIE"))
  {
    header('Content-Type: "application/octet-stream"');
    header('Content-Disposition: attachment; filename="'. $file . '"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header("Content-Transfer-Encoding: binary");
    header('Pragma: public');
    header("Content-Length: ".filesize($filepath . $file));
  }
  else
  {
    header('Content-Type: "application/octet-stream"');
    header('Content-Disposition: attachment; filename="'. $file . '"');
    header("Content-Transfer-Encoding: binary");
    header('Expires: 0');
    header('Pragma: no-cache');
    header("Content-Length: ".filesize($filepath . $file));
  }

  fpassthru($fp);
  fclose($fp);

} else {
  header("location: ". $_SERVER['HTTP_REFERER']);
}

?>
