<?php
include("setting.php");
$cnx = connectdb();

$study_id = (isset($_GET['studyID'])) ? $_GET['studyID'] : null;
$module_nr = (isset($_GET['moduleNr'])) ? $_GET['moduleNr'] : null;
$subject_code = (isset($_GET['subjectCode'])) ? $_GET['subjectCode'] : null;
$codebase = (isset($_GET['codebase'])) ? $_GET['codebase'] : null;
$module_codebase = "";
// static log code value for download
$log_download_cd = 3;
$group_id = 0;

// check mandatory parameters - do not continue if missing
if (!isset($study_id) || !isset($module_nr)) {
	echo "General.creator.dataServerCreator.errorMessage.missingInformation";
	exit;
}

// get module information - do not continue if missing
$module_info = getModuleInfo($study_id, $module_nr);
if (!$module_info) {
	echo "General.creator.dataServerCreator.errorMessage.missingModule";
	exit;
}

$module_id = $module_info['MODULE_ID'];


// different module type handling
if ($module_info['MODULE_TYPE_CD'] == 1) {
	// private
	if (!$subject_code) {
		echo "General.creator.dataServerCreator.errorMessage.missingInformation";
		exit;
	}
	// check parameters - do not continue if missing
	$subject_info = getSubjectInfo($study_id, $subject_code);
	if (!$subject_info) {
		echo "General.creator.dataServerCreator.errorMessage.authenticationFailed";
		exit;
	}
	$subject_id = $subject_info['SUBJECT_ID'];
} else if ($module_info['MODULE_TYPE_CD'] == 2) {
	// public

	// get random group
	$q_group = $cnx->prepare("SELECT GRP.GROUP_ID"
			." FROM tat_module MDL, tat_group GRP, tat_group_module GRPMDL, tat_module_study MDLSTD"
			." WHERE"
			." GRPMDL.GROUP_ID = GRP.GROUP_ID"
			." AND GRPMDL.MODULE_ID = MDLSTD.MODULE_ID "
			." AND MDLSTD.MODULE_ID = MDL.MODULE_ID "
			." AND MDL.MODULE_ID = :module_id AND MDLSTD.STUDY_ID = :study_id");
	$q_group->bindParam(':module_id', $module_id);
	$q_group->bindParam(':study_id', $study_id);
	$q_group->execute();

	if ($q_group->rowCount() < 1) {
		echo "General.creator.dataServerCreator.errorMessage.missingGroup";
		exit;
	}

	$groups = array();
	while ($field = $q_group->fetch(PDO::FETCH_ASSOC)) {
		$group_id = $field['GROUP_ID'];
		$groups[] = $group_id;
	}

	$rand_group = rand(0,count($groups)-1);

	// get new unique code
	$datetime = date("Y-m-d H:i:s");
	$q_code = $cnx->prepare("INSERT INTO tat_code (CODE_ID, INSERT_DT) VALUES(0,:datetime)");
	$q_code->bindParam(':datetime', $datetime);
	$q_code->execute();
	$subject_code = $cnx->lastInsertId();

	// add new subject
	$q_subject = $cnx->prepare("INSERT INTO `tat_subject`(`SUBJECT_ID`, `STUDY_ID`, `SUBJECT_STATUS_CD`, `SUBJECT_CODE`, `SUBJECT_NAME`, `SUBJECT_MAIL`, `SUBJECT_PHONE`, `SUBJECT_SEX_CD`, `SUBJECT_BIRTH`, `SUBJECT_EDUCATION`) VALUES (0,:study_id,0,:subject_code,'Subject $subject_code','','',0,0,'')");
	$q_subject->bindParam(':study_id', $study_id);
	$q_subject->bindParam(':subject_code', $subject_code);
	$q_subject->execute();
	$subject_id = $cnx->lastInsertId();

	// add subject to group
	$q_sub_grp = $cnx->prepare("INSERT INTO `tat_subject_group`(`SUBJECT_GROUP_ID`, `SUBJECT_ID`, `GROUP_ID`) VALUES (0,:subject_id,:group_id)");
	$q_sub_grp->bindParam(':subject_id', $subject_id);
	$q_sub_grp->bindParam(':group_id', $groups[$rand_group]);
	$q_sub_grp->execute();
	
	// add start and end date for new subject
	$q_sub_grp_mdl = $cnx->prepare("INSERT INTO `tat_subject_module`(`SUBJECT_MODULE_ID`, `SUBJECT_ID`, `MODULE_ID`, `STATUS_CD`, `START_DT`) VALUES (0,:subject_id,:module_id,0,:start_dt)");
	$q_sub_grp_mdl->bindParam(':subject_id', $subject_id);
	$q_sub_grp_mdl->bindParam(':module_id', $module_id);
	$q_sub_grp_mdl->bindParam(':start_dt', $datetime);
	$q_sub_grp_mdl->execute();

}

// check whether the subject is allowed to download the module
$q_select = $cnx->prepare("SELECT MDLSTD.STUDY_ID, MDL.MODULE_ID, GRPMDL.GROUP_ID, GRP.GROUP_NR, SUB.SUBJECT_ID, SUB.SUBJECT_CODE,"
		." GRPMDL.XML_FILE, GRPMDL.JNLP_FILE, MDL.MODULE_TYPE_CD"
		." FROM tat_subject SUB, tat_module MDL, tat_group GRP, tat_subject_group SUBGRP, tat_group_module GRPMDL, tat_module_study MDLSTD"
		." WHERE SUB.SUBJECT_ID = SUBGRP.SUBJECT_ID"
		." AND SUBGRP.GROUP_ID = GRPMDL.GROUP_ID"
		." AND GRPMDL.GROUP_ID = GRP.GROUP_ID"
		." AND GRPMDL.MODULE_ID = MDLSTD.MODULE_ID "
		." AND MDLSTD.MODULE_ID = MDL.MODULE_ID "
		." AND SUB.SUBJECT_CODE = :subject_code
		AND MDL.MODULE_ID = :module_id
		AND MDLSTD.STUDY_ID = :study_id");
$q_select->bindParam(':study_id', $study_id);
$q_select->bindParam(':subject_code', $subject_code);
$q_select->bindParam(':module_id', $module_id);
$q_select->execute();

if ($q_select->rowCount() < 1) {
	echo "General.creator.dataServerCreator.errorMessage.authorizationFailed";
	exit;
}

while ($field = $q_select->fetch(PDO::FETCH_ASSOC)) {
	$subject_id = $field['SUBJECT_ID'];
	$group_id = $field['GROUP_ID'];
	$group_nr = $field['GROUP_NR'];
	$filename =  $field['XML_FILE'];
	$module_codebase = $field['JNLP_FILE'];
}

// only allow download if codebase of requesting application matches the module codebase
if (!empty($module_codebase) && strcasecmp($module_codebase, $codebase) != 0) {
	if ($module_info['MODULE_TYPE_CD'] == 2) {
		$q_sub_grp = $cnx->prepare("DELETE FROM `tat_subject_group` WHERE SUBJECT_ID=:subject_id");
		$q_sub_grp->bindParam(':subject_id', $subject_id);
		$q_sub_grp->execute();
		
		$q_sub_grp_mdl = $cnx->prepare("DELETE FROM `tat_subject_module` WHERE SUBJECT_ID=:subject_id");
		$q_sub_grp_mdl->bindParam(':subject_id', $subject_id);
		$q_sub_grp_mdl->execute();
		
		$q_subject = $cnx->prepare("DELETE FROM `tat_subject` WHERE SUBJECT_ID=:subject_id");
		$q_subject->bindParam(':subject_id', $subject_id);
		$q_subject->execute();
	}
	echo "General.creator.dataServerCreator.errorMessage.wrongCodebase";
	exit;
}

// handling download limit of module
if ($module_info['LIMIT_DOWN_NUM'] > 0) {
	$log_info = getLogEntries($study_id, $group_id, $subject_id, $module_id, $log_download_cd);

	if ($log_info) {
		$count_down = 0;
		foreach ($log_info as $row) {
			$count_down++;
		}
		if ($count_down >= $module_info['LIMIT_DOWN_NUM']) {
			echo "General.creator.dataServerCreator.errorMessage.downloadLimit";
			exit;
		}
	}
}


// get the xml for the module
$filepath=$studiespath
."studies"."/".$study_id
."/"."modules"."/".$module_nr
."/"."groups"."/".$group_nr
."/".$filename;
//echo($filepath); //justin edit
$xml = getModuleXML($filepath);

// add additional dynamic parameters to XML used for Tatool Online
$node_map = $xml->xpath("/beans/bean[@id='moduleProperties']/property/map");

$node_subjectCode = $node_map[0]->addChild('entry');
$node_subjectCode->addAttribute('key', 'tatool.online.subject.code');
$node_subjectCode->addAttribute('value', $subject_code);

$node_studyID = $node_map[0]->addChild('entry');
$node_studyID->addAttribute('key', 'tatool.online.study.id');
$node_studyID->addAttribute('value', $study_id);

$node_moduleID = $node_map[0]->addChild('entry');
$node_moduleID->addAttribute('key', 'tatool.online.module.nr');
$node_moduleID->addAttribute('value', $module_nr);

$node_groupID = $node_map[0]->addChild('entry');
$node_groupID->addAttribute('key', 'tatool.online.group.nr');
$node_groupID->addAttribute('value', $group_nr);

// add a log entry for the download
addLogEntry($study_id, $group_id, $subject_id, $module_id, $log_download_cd);

// output file
header('Content-Type:text/xml');
header('Pragma:no-cache');
header('Expires:-1');

echo str_replace("ns=", "xmlns=", $xml->asXML());
?>
