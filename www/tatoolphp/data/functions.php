<?php
/* =================================================== */
/* FUNCTIONS			       		       */
/* =================================================== */

/*------------------------------------------------------------------------------
 Connect to a MySQL database
------------------------------------------------------------------------------*/
function connectdb() {
  global $MySQL_Host, $MySQL_User, $MySQL_Pass, $MySQL_Database;
  
  if (!isset($dbh)) {
	$dbh = new PDO("mysql:host=$MySQL_Host;dbname=$MySQL_Database", $MySQL_User, $MySQL_Pass, array(PDO::MYSQL_ATTR_FOUND_ROWS => true));
  }

  return $dbh;
}

/*------------------------------------------------------------------------------
 Gets XML data from a file and replaces xmlns namespaces in order for the 
 simpleXML to work.
 
return: xml object of the given file
------------------------------------------------------------------------------*/
function getModuleXML($filepath) {
  if (file_exists($filepath)) {
    $data = file_get_contents($filepath);
    $processed_string = str_replace("xmlns=", "ns=", $data); 
    $xml = new SimpleXMLElement($processed_string);
    return $xml;
  } else {
    echo "Error: File couldn't be found";
    exit;
  }
}

/*------------------------------------------------------------------------------
 Add a log entry for an action of a subject
------------------------------------------------------------------------------*/
function addLogEntry($study_id, $group_id, $subject_id, $module_id, $log_cd) {
  if (!$study_id) {
    $study_id = 0;
  }
  if (!$group_id) {
    $group_id = 0;
  }
  if (!$subject_id) {
    $subject_id = 0;
  }
  if (!$module_id) {
    $module_id = 0;
  }
  if (!$log_cd) {
    $log_cd = 0;
  }
  
  $datetime = date("Y-m-d H:i:s");
  
  $cnx = connectdb();
  $q_select = $cnx->prepare("INSERT INTO tat_subject_log"
  ." (SUBJECT_LOG_ID, STUDY_ID, GROUP_ID, SUBJECT_ID, MODULE_ID, LOG_CD, INSERT_DT)" 
  ." VALUES(0, :study_id, :group_id, :subject_id, :module_id, :log_cd, :datetime)");
  $q_select->bindParam(':study_id', $study_id);
  $q_select->bindParam(':group_id', $group_id);
  $q_select->bindParam(':subject_id', $subject_id);
  $q_select->bindParam(':module_id', $module_id);
  $q_select->bindParam(':log_cd', $log_cd);
  $q_select->bindParam(':datetime', $datetime);
  $q_select->execute();
}

/*------------------------------------------------------------------------------
 Get the latest log entry with the given arguments. If the argument is greater
 than 0 it will be used in the where query.
 
 return: log entry in an associative array
------------------------------------------------------------------------------*/
function getLogEntry($study_id, $group_id, $subject_id, $module_id, $log_cd) { 
  $study = "";
  $group = "";
  $subject = "";
  $train = "";
  $log = "";
  
  if ($study_id > 0) {
    $study = " AND log.STUDY_ID = :study_id";
  }
  if ($group_id > 0) {
    $group = " AND log.GROUP_ID = :group_id";
  }
  if ($subject_id > 0) {
    $subject = " AND log.SUBJECT_ID = :subject_id";
  }
  if ($module_id > 0) {
    $train = " AND log.MODULE_ID = :module_id";
  }
  if ($log_cd > 0) {
    $log = " AND log.LOG_CD = :log_cd";
  }
  
  $SQL_select = "SELECT log.SUBJECT_LOG_ID, log.STUDY_ID, log.GROUP_ID, log.SUBJECT_ID, log.MODULE_ID, log.LOG_CD, log.INSERT_DT" 
  ." FROM tat_subject_log LOG"
  ." WHERE 1=1"
  .$study
  .$group
  .$subject
  .$train
  .$log
  ." ORDER BY INSERT_DT DESC, SUBJECT_LOG_ID DESC LIMIT 1";
  
  $cnx = connectdb();
  $q_select = $cnx->prepare($SQL_select);  
  if ($study_id > 0) {
    $q_select->bindParam(':study_id', $study_id);
  }
  if ($group_id > 0) {
    $q_select->bindParam(':group_id', $group_id);
  }
  if ($subject_id > 0) {
    $q_select->bindParam(':subject_id', $subject_id);
  }
  if ($module_id > 0) {
    $q_select->bindParam(':module_id', $module_id);
  }
  if ($log_cd > 0) {
    $q_select->bindParam(':log_cd', $log_cd);
  }
  $q_select->execute();
  
  $row = $q_select->fetch(PDO::FETCH_ASSOC);
  return $row;
}

/*------------------------------------------------------------------------------
 Get log entries with a specific log code of a subject with a given id
 
 return: log entries in a two dimensional array
------------------------------------------------------------------------------*/
function getLogEntries($study_id, $group_id, $subject_id, $module_id, $log_cd) { 
  $study = "";
  $group = "";
  $subject = "";
  $module = "";
  $log = "";
  
  if ($study_id > 0) {
    $study = " AND log.STUDY_ID = :study_id";
  }
  if ($group_id > 0) {
    $group = " AND log.GROUP_ID = :group_id";
  }
  if ($subject_id > 0) {
    $subject = " AND log.SUBJECT_ID = :subject_id";
  }
  if ($module_id > 0) {
    $module = " AND log.MODULE_ID = :module_id";
  }
  if ($log_cd > 0) {
    $log = " AND log.LOG_CD = :log_cd";
  }
  
  $SQL_select = "SELECT log.SUBJECT_LOG_ID, log.STUDY_ID, log.GROUP_ID, log.SUBJECT_ID, log.MODULE_ID, log.LOG_CD, log.INSERT_DT" 
  ." FROM tat_subject_log LOG"
  ." WHERE 1=1"
  .$study
  .$group
  .$subject
  .$module
  .$log
  ." ORDER BY INSERT_DT DESC, SUBJECT_LOG_ID DESC";

  $cnx = connectdb();
  $q_select = $cnx->prepare($SQL_select);  
  if ($study_id > 0) {
    $q_select->bindParam(':study_id', $study_id);
  }
  if ($group_id > 0) {
    $q_select->bindParam(':group_id', $group_id);
  }
  if ($subject_id > 0) {
    $q_select->bindParam(':subject_id', $subject_id);
  }
  if ($module_id > 0) {
    $q_select->bindParam(':module_id', $module_id);
  }
  if ($log_cd > 0) {
    $q_select->bindParam(':log_cd', $log_cd);
  }
  $q_select->execute();
  
  $rows = array();
  while ($row = $q_select->fetch(PDO::FETCH_ASSOC)) {
    $rows[] = $row;  
  }

  return $rows;
}

/*------------------------------------------------------------------------------
 Get a module of a given study with a given nr
 
return: module info in an associative array
------------------------------------------------------------------------------*/

function getModuleInfo($study_id, $module_nr) {
  $cnx = connectdb();
  $q_select = $cnx->prepare("SELECT MDL.MODULE_ID, MDL.STUDY_ID, MDL.MODULE_NR, MDL.MODULE_NAME, MDL.MODULE_TYPE_CD, MDL.LIMIT_DOWN_NUM, MDL.LIMIT_SESS_NUM" 
  ." FROM tat_module MDL"
  ." WHERE MDL.STUDY_ID = :study_id"
  ." AND MDL.MODULE_NR = :module_nr");
  $q_select->bindParam(':module_nr', $module_nr);
  $q_select->bindParam(':study_id', $study_id);
  $q_select->execute();

  $row = $q_select->fetch(PDO::FETCH_ASSOC);
  return $row;
}

/*------------------------------------------------------------------------------
 Get a subject with a given code and a given study id
 
return: subject info in an associative array
------------------------------------------------------------------------------*/

function getSubjectInfo($study_id, $subject_code) {
  $cnx = connectdb();
  $q_select = $cnx->prepare("SELECT SUBJECT_ID, STUDY_ID, SUBJECT_STATUS_CD, SUBJECT_CODE, SUBJECT_NAME, SUBJECT_MAIL, SUBJECT_PHONE, SUBJECT_SEX_CD, SUBJECT_BIRTH, SUBJECT_EDUCATION" 
  ." FROM tat_subject"
  ." WHERE STUDY_ID = :study_id"
  ." AND SUBJECT_CODE = :subject_code");
  $q_select->bindParam(':subject_code', $subject_code);
  $q_select->bindParam(':study_id', $study_id);
  $q_select->execute();

  $row = $q_select->fetch(PDO::FETCH_ASSOC);
  return $row;
}

/*------------------------------------------------------------------------------
 Get the session data
 
return: session info in an associative array
------------------------------------------------------------------------------*/
function getSessionInfo($module_id, $group_id, $subject_id, $sess_id, $sess_start_dt) {
  $cnx = connectdb();
  $q_select = $cnx->prepare("SELECT SES.SESSION_ID, SES.MODULE_ID, SES.GROUP_ID, SES.SUBJECT_ID, SES.SESS_ID, SES.SESS_START_DT, SES.SESS_END_DT" 
  ." FROM tat_session SES"
  ." WHERE SES.MODULE_ID = :module_id"
  ." AND SES.GROUP_ID = :group_id"
  ." AND SES.SUBJECT_ID = :subject_id"
  ." AND SES.SESS_ID = :sess_id"
  ." AND SES.SESS_START_DT = :sess_start_dt");
  $q_select->bindParam(':module_id', $module_id);
  $q_select->bindParam(':group_id', $group_id);
  $q_select->bindParam(':subject_id', $subject_id);
  $q_select->bindParam(':sess_id', $sess_id);
  $q_select->bindParam(':sess_start_dt', $sess_start_dt);
  $q_select->execute();

  $row = $q_select->fetch(PDO::FETCH_ASSOC);
  return $row;
}

/*------------------------------------------------------------------------------
 Adds a session to tat_session
------------------------------------------------------------------------------*/
function addSession($module_id, $group_id, $subject_id, $data, $prefix) {
  if (!$prefix) {
    $prefix = "";
  }
  $datetime = date("Y-m-d H:i:s");

  $cnx = connectdb();
  $q_select = $cnx->prepare("INSERT INTO tat_session"
  ." (SESSION_ID, MODULE_ID, GROUP_ID, SUBJECT_ID, FILE_PREFIX, SESS_ID, SESS_START_DT, SESS_END_DT, SESS_COMPLETED, INSERT_DT)" 
  ." VALUES(0, :module_id, :group_id, :subject_id, :prefix, :sess_id, :sess_start_dt, :sess_end_dt, :sess_completed, :datetime)");
  $q_select->bindParam(':group_id', $group_id);
  $q_select->bindParam(':subject_id', $subject_id);
  $q_select->bindParam(':module_id', $module_id);
  $q_select->bindParam(':prefix', $prefix);
  $q_select->bindParam(':datetime', $datetime);
  $q_select->bindParam(':sess_id', $data[2]);
  $q_select->bindParam(':sess_start_dt', $data[4]);
  $q_select->bindParam(':sess_end_dt', $data[5]);
  $q_select->bindParam(':sess_completed', $data[6]);
  $q_select->execute();
  
  return $cnx->lastInsertId();
}

/*------------------------------------------------------------------------------
 Adds session data to tat_session_data
------------------------------------------------------------------------------*/
function addSessionData($session_id, $data) {
  $datetime = date("Y-m-d H:i:s");

  $cnx = connectdb();
  $q_select = $cnx->prepare("INSERT INTO tat_session_data"
  ." (SESSION_DATA_ID, SESSION_ID, ELEMENT_ID, PROPERTY_NAME, PROPERTY_VALUE, INSERT_DT)" 
  ." VALUES(0, :session_id, :element_id, :property_name, :property_value, :datetime)");
  $q_select->bindParam(':session_id', $session_id);
  $q_select->bindParam(':datetime', $datetime);
  $q_select->bindParam(':element_id', $data[7]);
  $q_select->bindParam(':property_name', $data[8]);
  $q_select->bindParam(':property_value', $data[9]);
  $q_select->execute();
  
  return $cnx->lastInsertId();
}

/*------------------------------------------------------------------------------
 Adds or updates module data in tat_module_DATA
------------------------------------------------------------------------------*/
function addUpdateModuleData($module_id, $group_id, $subject_id, $data) {
  $datetime = date("Y-m-d H:i:s");

  $cnx = connectdb();
  $q_insert = $cnx->prepare("INSERT INTO tat_module_DATA"
  ." (MODULE_DATA_ID, MODULE_ID, GROUP_ID, SUBJECT_ID, ELEMENT_ID, PROPERTY_NAME, PROPERTY_VALUE, INSERT_DT, UPDATE_DT)" 
  ." VALUES(0, :module_id, :group_id, :subject_id, :element_id, :property_name, :property_value, :datetime, :datetime)");
  $q_insert->bindParam(':module_id', $module_id);
  $q_insert->bindParam(':group_id', $group_id);
  $q_insert->bindParam(':subject_id', $subject_id);
  $q_insert->bindParam(':element_id', $data[2]);
  $q_insert->bindParam(':property_name', $data[3]);
  $q_insert->bindParam(':property_value', $data[4]);
  $q_insert->bindParam(':datetime', $datetime);
  
  $q_update = $cnx->prepare("UPDATE tat_module_DATA"
  ." SET PROPERTY_VALUE=:property_value, UPDATE_DT=:datetime" 
  ." WHERE MODULE_ID=:module_id AND GROUP_ID=:group_id AND SUBJECT_ID=:subject_id AND ELEMENT_ID=:element_id AND PROPERTY_NAME=:property_name");
  $q_update->bindParam(':module_id', $module_id);
  $q_update->bindParam(':group_id', $group_id);
  $q_update->bindParam(':subject_id', $subject_id);
  $q_update->bindParam(':element_id', $data[2]);
  $q_update->bindParam(':property_name', $data[3]);
  $q_update->bindParam(':property_value', $data[4]);
  $q_update->bindParam(':datetime', $datetime);
  $q_update->execute();

  if ($q_update->rowCount()<1) {
     $q_insert->execute();  
  }

}

/*------------------------------------------------------------------------------
 Adds or updates account data in tat_account_data
------------------------------------------------------------------------------*/
function addUpdateAccountData($subject_id, $data) {
  $datetime = date("Y-m-d H:i:s");

  $cnx = connectdb();
  $q_insert = $cnx->prepare("INSERT INTO tat_account_data"
  ." (ACCOUNT_DATA_ID, SUBJECT_ID, ACCOUNT_NAME, ACCOUNT_PASSWORD, ACCOUNT_FOLDER, PROPERTY_NAME, PROPERTY_VALUE, INSERT_DT, UPDATE_DT)" 
  ." VALUES(0, :subject_id, :account_name, :account_password, :account_folder, :property_name, :property_value, :datetime, :datetime)");
  $q_insert->bindParam(':subject_id', $subject_id);
  $q_insert->bindParam(':account_name', $data[1]);
  $q_insert->bindParam(':account_password', $data[2]);
  $q_insert->bindParam(':account_folder', $data[3]);
  $q_insert->bindParam(':property_name', $data[4]);
  $q_insert->bindParam(':property_value', $data[5]);
  $q_insert->bindParam(':datetime', $datetime);
  
  $q_update = $cnx->prepare("UPDATE tat_account_data"
  ." SET PROPERTY_VALUE=:property_value, UPDATE_DT=:datetime" 
  ." WHERE SUBJECT_ID=:subject_id AND PROPERTY_NAME=:property_name");
  $q_update->bindParam(':subject_id', $subject_id);
  $q_update->bindParam(':property_name', $data[4]);
  $q_update->bindParam(':property_value', $data[5]);
  $q_update->bindParam(':datetime', $datetime);
  $q_update->execute();

  if ($q_update->rowCount()<1) {
     $q_insert->execute();  
  }
}

?>