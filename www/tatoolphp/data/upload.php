<?php
include("setting.php");
$cnx = connectdb();
  
if (strcmp($_SERVER['REQUEST_METHOD'],'POST')) {
	echo 'Not a post request: '.$_SERVER['REQUEST_METHOD'];
	return;
}

$subject_code = (isset($_POST['subjectCode'])) ?  $_POST['subjectCode'] : null;
$study_id = (isset($_POST['studyID'])) ?  $_POST['studyID'] : null;
$module_nr = (isset($_POST['moduleNr'])) ?  $_POST['moduleNr'] : null;
$group_nr = (isset($_POST['groupNr'])) ?  $_POST['groupNr'] : null;

// static log code value for upload
$log_upload_cd = 4;

// check parameters - do not continue if missing
if (!isset($subject_code) || !isset($study_id) || !isset($module_nr) || !isset($group_nr)) {
  echo "DataExportError.online.missingInformation";
  exit;
}

// check whether the subject is allowed to upload data to this module of tatool online
$q_select = $cnx->prepare("SELECT SUB.SUBJECT_ID, MDL.MODULE_ID, GRP.GROUP_ID"
." FROM tat_subject SUB, tat_module MDL, tat_group GRP, tat_subject_group SUBGRP, tat_group_module GRPMDL, tat_module_study MDLSTD "
." WHERE SUB.SUBJECT_ID = SUBGRP.SUBJECT_ID "
." AND SUBGRP.GROUP_ID = GRPMDL.GROUP_ID "
." AND GRPMDL.GROUP_ID = GRP.GROUP_ID "
." AND GRPMDL.MODULE_ID = MDLSTD.MODULE_ID "
." AND MDLSTD.MODULE_ID = MDL.MODULE_ID "
." AND SUB.STUDY_ID = :study_id "
." AND SUB.SUBJECT_CODE = :subject_code "
." AND MDL.MODULE_NR = :module_nr "
." AND MDLSTD.STUDY_ID = :study_id");
$q_select->bindParam(':study_id', $study_id);
$q_select->bindParam(':subject_code', $subject_code);
$q_select->bindParam(':module_nr', $module_nr);
$q_select->execute();

if ($q_select->rowCount() < 1) {
  echo "DataExportError.online.authorizationFailed";
  exit;
}

while ($field = $q_select->fetch(PDO::FETCH_ASSOC)) {
  $subject_id = $field['SUBJECT_ID'];
  $module_id = $field['MODULE_ID'];
  $group_id = $field['GROUP_ID'];
}

// set the upload path
$filepath=$studiespath 
."studies"."/".$study_id
."/"."modules"
."/".$module_nr
."/"."groups"
."/".$group_nr
."/"."upload"
."/".$subject_code
."/";

// check if the directory already exists, create otherwise
if (! file_exists($filepath)) {
	if (! mkdir($filepath, 0700)) {
		die('Unable to create directory: '.$filepath);
	}
} else {
	// make sure this represents a directory
	if (! is_dir($filepath)) {
		// TODO: cleanup
		die('Path does not represent a directory: '.$filepath);
	}
}

// create a date prefix and suffix used for all uploaded filenames.
$prefix = date('YmdHis');
$suffix_zip = ".zip";
$suffix_csv = ".csv";

// move all files to that directory, using the prefix and suffix for all files
$uploadCount = 0;
$filenames_zip = array();
$filenames_csv = array();
foreach ($_FILES as $file) {
    if ($file['error'] == UPLOAD_ERR_OK) {
        $tmp_name = $file["tmp_name"];
        $name_zip = $prefix . "_" . $file["name"] . $suffix_zip;  
        $name_csv = $prefix . "_" . $file["name"] . $suffix_csv;
        $filenames_zip[$file["name"]] = "$name_zip";
        $filenames_csv[$file["name"]] = "$name_csv";
        if (move_uploaded_file($tmp_name, "$filepath$name_zip")) {
			$uploadCount++;
			// unzip file
			$zip = new ZipArchive();
			$res = $zip->open($filepath . $filenames_zip[$file["name"]]);
			if ($res === TRUE) {
				$zip->extractTo($filepath);
				$zip->close();
				rename($filepath . $file["name"], $filepath . $filenames_csv[$file["name"]]);
				unlink($filepath . $filenames_zip[$file["name"]]);
			}
		}
    }
}

// add a log entry for the upload
addLogEntry($study_id, $group_id, $subject_id, $module_id, $log_upload_cd);

/**
 * Session Data Upload
 */ 
$row = 0;
$handle = fopen ($filepath . $filenames_csv['sessionData'], "r");
$sess_id = 0;
$last_sess_id = 0;
$session_id = 0;
while ( ($data = fgetcsv ($handle, 1000, ";")) !== FALSE ) {
  $num = count ($data);
  $row++;
  
  // skip header
  if ($row > 1) {
    $sess_id = $data[2];
    if ($sess_id != $last_sess_id) {
    
      // check whether session already exists
    	$session_info = getSessionInfo($module_id, $group_id, $subject_id, $data[2], $data[4]);
    	
    	// add new session
      if (!$session_info) {
        $session_id = addSession($module_id, $group_id, $subject_id, $data, $prefix);
      }
    }
    
    // add session data
    if (!$session_info) {
      addSessionData($session_id, $data);
    }
    
    $last_sess_id = $sess_id;
  }
}
fclose ($handle); 
unlink($filepath . $filenames_csv['sessionData']);


/**
 * Module Data Upload
 */ 
$row = 0;
$handle = fopen ($filepath . $filenames_csv['moduleData'], "r");
while ( ($data = fgetcsv ($handle, 1000, ";")) !== FALSE ) {
  $num = count ($data);
  $row++;
  
  // skip header
  if ($row > 1) {
    // insert/update module data
    addUpdateModuleData($module_id, $group_id, $subject_id, $data);    
  }

}

fclose ($handle); 
unlink($filepath . $filenames_csv['moduleData']);


/**
 * Account Data Upload
 */ 
$row = 0;
$handle = fopen ($filepath . $filenames_csv['accountData'], "r");
while ( ($data = fgetcsv ($handle, 1000, ";")) !== FALSE ) {
  $num = count ($data);
  $row++;
  
  // skip header
  if ($row > 1) {
    // insert/update training data
    addUpdateAccountData($subject_id, $data);    
  }

}

fclose ($handle); 
unlink($filepath . $filenames_csv['accountData']);

function clean_filename($filename){//function to clean a filename string so it is a valid filename
  $reserved = preg_quote('\/:*?"<>|', '/');//characters that are  illegal on any of the 3 major OS's
  //replaces all characters up through space and all past ~ along with the above reserved characters
  return preg_replace("/([\\x00-\\x20\\x7f-\\xff{$reserved}])/e", "_", $filename);
}
?>