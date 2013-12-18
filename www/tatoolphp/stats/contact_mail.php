<?php
include("checker.php");

include("../data/setting.php");
$cnx = connectdb();

$subject = $_POST["subject"];
$mail = $_POST["mail"];
$from = $_POST["from"];
$text = $_POST["text"];
$page = $_POST["page"];
$study_id = $_POST["study_id"];

if (!check_email_address($mail)) {
	header('Location: ' . $page . '&mail=error');
	return;
}

if (!check_email_address($from)) {
	header('Location: ' . $page . '&mail=error');
	return;
}

// check whether mail exists in db
$q_mail = $cnx->prepare("SELECT SUBJECT_MAIL FROM tat_subject SUB, tat_user_study UST WHERE UST.USER_ID=:user 
AND UST.STUDY_ID=:study_id AND SUB.STUDY_ID=:study_id AND SUBJECT_MAIL=:mail AND UST.STUDY_ID=SUB.STUDY_ID");

$q_mail->bindParam(':user', $_SESSION['USER_ID'], PDO::PARAM_INT);
$q_mail->bindParam(':study_id', $study_id, PDO::PARAM_INT);
$q_mail->bindParam(':mail', $mail, PDO::PARAM_STR);
$q_mail->execute();

while ($field = $q_mail->fetch(PDO::FETCH_ASSOC)) {
  $mail_check = $field['SUBJECT_MAIL'];
}

if ($mail != $mail_check) {
  header('Location: ' . $page . '&mail=error');  
} else {
  $mail_to=$mail;
  $mail_from=$from;

  mail($mail_to, $subject, $text,"from:$mail_from");
  header('Location: ' . $page . '&mail=success');
}

function check_email_address($email) {
  // First, we check that there's one @ symbol, 
  // and that the lengths are right.
  if (!ereg("^[^@]{1,64}@[^@]{1,255}$", $email)) {
    // Email invalid because wrong number of characters 
    // in one section or wrong number of @ symbols.
    return false;
  }
  // Split it into sections to make life easier
  $email_array = explode("@", $email);
  $local_array = explode(".", $email_array[0]);
  for ($i = 0; $i < sizeof($local_array); $i++) {
    if
(!ereg("^(([A-Za-z0-9!#$%&'*+/=?^_`{|}~-][A-Za-z0-9!#$%&
?'*+/=?^_`{|}~\.-]{0,63})|(\"[^(\\|\")]{0,62}\"))$",
$local_array[$i])) {
      return false;
    }
  }
  // Check if domain is IP. If not, 
  // it should be valid domain name
  if (!ereg("^\[?[0-9\.]+\]?$", $email_array[1])) {
    $domain_array = explode(".", $email_array[1]);
    if (sizeof($domain_array) < 2) {
        return false; // Not enough parts to domain
    }
    for ($i = 0; $i < sizeof($domain_array); $i++) {
      if
(!ereg("^(([A-Za-z0-9][A-Za-z0-9-]{0,61}[A-Za-z0-9])|
?([A-Za-z0-9]+))$",
$domain_array[$i])) {
        return false;
      }
    }
  }
  return true;
}

?>