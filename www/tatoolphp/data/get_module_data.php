<?php
include("setting.php");
$cnx = connectdb();

$study_id = $_GET['studyID'];
$module_nr = $_GET['moduleNr'];
$subject_code = $_GET['subjectCode'];
$log_download_cd = 3;
$group_id = 0;

// check parameters - do not continue if missing
if (!$study_id || !$module_nr || !$subject_code) {
	echo "Error: Missing information";
	exit;
}

// get module information - do not continue if missing
$module_info = getModuleInfo($study_id, $module_nr);
if (!$module_info) {
	echo "Error: Module does not exist online";
	exit;
}

$subject_info = getSubjectInfo($study_id, $subject_code);
if (!$subject_info) {
	echo "Error: Subject does not exist online";
	exit;
}

$subject_id = $subject_info['SUBJECT_ID'];
$module_id = $module_info['MODULE_ID'];

// handling download limit of training
if ($module_info['LIMIT_DOWN_NUM'] > 0) {
	$log_info = getLogEntries($study_id, $group_id, $subject_id, $module_id, $log_download_cd);

	if ($log_info) {
		$count_down = 0;
		foreach ($log_info as $row) {
			$count_down++;
		}
		if ($count_down >= $module_info['LIMIT_DOWN_NUM']) {
			echo "Error: You reached the maximum amount of downloads for this module";
			exit;
		}
	}
}

// check whether the subject is allowed to download the module
$q_select = $cnx->prepare("SELECT MDLSTD.STUDY_ID, MDL.MODULE_ID, GRPMDL.GROUP_ID, GRP.GROUP_NR, SUB.SUBJECT_ID, SUB.SUBJECT_CODE,"
		." GRPMDL.XML_FILE, MDL.MODULE_TYPE_CD"
		." FROM tat_subject SUB, tat_module MDL, tat_group GRP, tat_subject_group SUBGRP, tat_group_module GRPMDL, tat_module_study MDLSTD"
		." WHERE SUB.SUBJECT_ID = SUBGRP.SUBJECT_ID"
		." AND SUBGRP.GROUP_ID = GRPMDL.GROUP_ID"
		." AND GRPMDL.GROUP_ID = GRP.GROUP_ID"
		." AND GRPMDL.MODULE_ID = MDLSTD.MODULE_ID "
		." AND MDLSTD.MODULE_ID = MDL.MODULE_ID "
		." AND SUB.SUBJECT_CODE = :subject_code "
		." AND MDL.MODULE_ID = :module_id "
		." AND MDLSTD.STUDY_ID = :study_id");
$q_select->bindParam(':subject_code', $subject_code);
$q_select->bindParam(':study_id', $study_id);
$q_select->bindParam(':module_id', $module_id);
$q_select->execute();

if ($q_select->rowCount() < 1) {
	echo "Error: Wrong code or the training you're looking for is not available";
	exit;
}

while ($field = $q_select->fetch(PDO::FETCH_ASSOC)) {
	$subject_id = $field['SUBJECT_ID'];
	$group_id = $field['GROUP_ID'];
	$group_nr = $field['GROUP_NR'];
}

// get module data
$q_module_data = $cnx->prepare("SELECT ELEMENT_ID, PROPERTY_NAME, PROPERTY_VALUE"
		." FROM tat_module_DATA"
		." WHERE SUBJECT_ID = :subject_id "
		." AND MODULE_ID = :module_id "
		." AND GROUP_ID = :group_id");
$q_module_data->bindParam(':subject_id', $subject_id);
$q_module_data->bindParam(':group_id', $group_id);
$q_module_data->bindParam(':module_id', $module_id);
$q_module_data->execute();

$csv_output = "";
while ($field = $q_module_data->fetch(PDO::FETCH_ASSOC)) {
	$element_id = $field['ELEMENT_ID'];
	$property_name = $field['PROPERTY_NAME'];
	$property_value = $field['PROPERTY_VALUE'];

	$csv_output .= $element_id . ";" . $property_name . ";" . $property_value . "\n";
}

print $csv_output;

// output file
header('Content-Type:text');
header('Pragma:no-cache');
header('Expires:-1');
?>
