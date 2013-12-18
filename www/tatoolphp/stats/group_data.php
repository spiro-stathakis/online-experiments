<?php
include("checker.php");
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html dir="ltr">

<head>
<script type="text/javascript" src="../script/dojo/dojo.js"
	djConfig="isDebug: true"></script>
<link rel="stylesheet" type="text/css" href="../style.css" />
<style type="text/css">
<!--
table.tatool {
	border: 1px solid #19191a;
	border-collapse: collapse;
}

.tatool th {
	border: 1px solid #19191a;
	background-color: #cc3300;
	color: #ffffff;
	padding: 3px;
}

.tatool .total {
	border: 1px solid #19191a;
	background-color: #fef2ee;
	padding: 3px;
	text-align: center;
	font-weight: bold;
}

.tatool .label {
	border: 1px solid #19191a;
	background-color: #fef2ee;
	padding: 3px;
	font-weight: bold;
}

.tatool .value {
	border: 1px solid #19191a;
	background-color: #f6f6f6;
	padding: 3px;
	font-weight: bold;
}

.tatool td {
	border: 1px solid #19191a;
	padding: 3px;
}
-->
</style>
</head>
<body>
	<br>

	<?php
	include("../data/setting.php");
	include("stat_functions.php");
	$cnx = connectdb();

	$study_id = $_GET["study_id"];;
	$module_id = $_GET["module_id"];;
	$group_id = $_GET["group_id"];

	// check whether current study should be blind for user
	$q_user = $cnx->prepare("SELECT BLIND_CD FROM tat_user_study WHERE USER_ID=:user AND STUDY_ID=:study_id");
	$q_user->bindParam(':user', $_SESSION['USER_ID']);
	$q_user->bindParam(':study_id', $study_id);
	$q_user->execute();

	while ($field = $q_user->fetch(PDO::FETCH_ASSOC)) {
  $blind_cd = $field['BLIND_CD'];
}

?>
	<div style="margin: 0 auto; width: 800px;">
		<div style="width: 200px; float: left;">
			<a href="index.php"><img src="../img/online/tatool_online.png"
				alt="Tatool online" title="Tatool online" border='0'> </a> <img
				src="../img/online/title_study_group.png">
		</div>
		<div style="width: 600px; float: right;">

			<table class="tatool">
				<?php

				// get study information
				$q_module_info = $cnx->prepare("SELECT STUDY_NAME, MODULE_NAME, GROUP_NAME, LIMIT_SESS_NUM
FROM tat_study S, tat_module M, tat_group G, tat_module_study MS, tat_group_module GM, tat_user_study US
WHERE S.STUDY_ID=:study_id
AND M.MODULE_ID=:module_id
AND G.GROUP_ID=:group_id
AND G.GROUP_ID = GM.GROUP_ID
AND GM.MODULE_ID = M.MODULE_ID
AND M.MODULE_ID = MS.MODULE_ID
AND MS.STUDY_ID = S.STUDY_ID
AND S.STUDY_ID = US.STUDY_ID
AND US.USER_ID=:user");

$q_module_info->bindParam(':user', $_SESSION['USER_ID']);
$q_module_info->bindParam(':study_id', $study_id);
$q_module_info->bindParam(':module_id', $module_id);
$q_module_info->bindParam(':group_id', $group_id);
$q_module_info->execute();

while ($field = $q_module_info->fetch(PDO::FETCH_ASSOC)) {
  $study_name = $field['STUDY_NAME'];
  $module_name = $field['MODULE_NAME'];
  $group_name = $field['GROUP_NAME'];
  $limit_sess_num = $field['LIMIT_SESS_NUM'];

  if ($limit_sess_num == 0) {
	$limit_sess_num = 99;
  }

  echo "<tr>";
  echo "<td class='label'><strong>Study</strong></td><td>" . $study_name . "</td>";
  echo "</tr>";
  echo "<tr>";
  echo "<td class='label'><strong>Module</strong></td><td><a href='module.php?study_id=$study_id&module_id=$module_id' class='ext_link'>" . $module_name . "</a></td>";
  echo "</tr>";
  echo "<tr>";
  echo "<td class='label'><strong>Group</strong></td><td>" . $group_name . "</td>";
  echo "</tr>";
}

?>
			</table>
		</div>

		<?php
		$handlers = array();
		$handlers_unitid = array();

		// get scoreAndLevelHandlers unitids
		$q_handler = $cnx->prepare("SELECT DISTINCT ELEMENT_ID
				FROM tat_module_DATA MD, tat_module M, tat_module_study MS, tat_study S, tat_user_study US
				WHERE
				MD.MODULE_ID=:module_id
				AND MD.GROUP_ID=:group_id
				AND S.STUDY_ID=:study_id
				AND MD.MODULE_ID = M.MODULE_ID
				AND M.MODULE_ID = MS.MODULE_ID
				AND MS.STUDY_ID = S.STUDY_ID
				AND S.STUDY_ID = US.STUDY_ID
				AND US.USER_ID = :user
				AND PROPERTY_NAME='registeredPointsAndLevelHandler'");

		$q_handler->bindParam(':user', $_SESSION['USER_ID']);
		$q_handler->bindParam(':study_id', $study_id);
		$q_handler->bindParam(':module_id', $module_id);
		$q_handler->bindParam(':group_id', $group_id);
		$q_handler->execute();

		while ($field = $q_handler->fetch(PDO::FETCH_ASSOC)) {
			$handler_string = $field['ELEMENT_ID'];
			$handlers_unitid[] = $handler_string;
		}

		// get description for handler
		if (isset($handlers_unitid)) {
			for ($i=0; $i < count($handlers_unitid); $i++) {

				$q_handler_desc = $cnx->prepare("SELECT DISTINCT PROPERTY_VALUE
						FROM tat_module_DATA
						WHERE
						MODULE_ID=:module_id
						AND GROUP_ID=:group_id
						and ELEMENT_ID='" . $handlers_unitid[$i] . "'
						AND PROPERTY_NAME='description'
						ORDER BY ELEMENT_ID");

				$q_handler_desc->bindParam(':module_id', $module_id);
				$q_handler_desc->bindParam(':group_id', $group_id);
				$q_handler_desc->execute();

				while ($field = $q_handler_desc->fetch(PDO::FETCH_ASSOC)) {
					$description = $field['PROPERTY_VALUE'];
					$handlers[$i]['unitid'] = $handlers_unitid[$i];
					$handlers[$i]['description'] = $description;
				}

			}
		}

		// order the handlers according to description text
		usort($handlers, "cmp");
		function cmp($a, $b)
		{
			return strcmp($a["description"], $b["description"]);
		}

		// do all the calculations
		$subjects = array();
		$minima = array();
		$maxima = array();
		$means = array();
		$graph_means = array();
		$max_session = array();

		for ($i=0; $i < count($handlers); $i++) {

			if ($blind_cd==1) {
				$order = "ORDER BY SUBJECT_CODE, SESS_ID";
			} else {
				$order = "ORDER BY SUBJECT_NAME, SESS_ID";
			}

			// get subject level data
			$q_sub_level = $cnx->prepare("SELECT S.SUBJECT_ID, SUBJECT_NAME, SUBJECT_CODE, S.SESSION_ID, PROPERTY_VALUE
					FROM tat_session S,
					tat_session_data SD,
					tat_subject SUB,
					tat_subject_group SGR,
					tat_group GRP,
					tat_group_module GRM,
					tat_module MDL,
					tat_module_study MST,
					tat_study STD,
					tat_user_study UST
					WHERE S.SESSION_ID = SD.SESSION_ID
					AND S.SUBJECT_ID = SUB.SUBJECT_ID
					AND SUB.SUBJECT_ID = SGR.SUBJECT_ID
					AND SGR.GROUP_ID = GRP.GROUP_ID
					AND GRP.GROUP_ID = GRM.GROUP_ID
					AND GRM.MODULE_ID = MDL.MODULE_ID
					AND MDL.MODULE_ID = MST.MODULE_ID
					AND MST.STUDY_ID = STD.STUDY_ID
					AND STD.STUDY_ID = UST.STUDY_ID
					AND UST.USER_ID = :user
					AND SD.ELEMENT_ID=:handler_id
					AND PROPERTY_NAME LIKE 'level'
					AND MDL.MODULE_ID=:module_id
					AND GRP.GROUP_ID=:group_id
					AND S.SESS_COMPLETED=1
					AND S.MODULE_ID=:module_id
					$order");

			$q_sub_level->bindParam(':user', $_SESSION['USER_ID']);
			$q_sub_level->bindParam(':module_id', $module_id);
			$q_sub_level->bindParam(':group_id', $group_id);
			$q_sub_level->bindParam(':handler_id', $handlers[$i]['unitid']);
			$q_sub_level->execute();

			$subject = array();
			$max_session[$i] = 0;
			$last_sub = 0;

			while ($field = $q_sub_level->fetch(PDO::FETCH_ASSOC)) {
  $subject_id = $field['SUBJECT_ID'];
  $subject_name = $field['SUBJECT_NAME'];
  $subject_code = $field['SUBJECT_CODE'];
  $session_id = $field['SESSION_ID'];
  $level = $field['PROPERTY_VALUE'];

  if ($last_sub != $subject_id && $last_sub != 0) {
    $subjects[$i][] = $subject;
    unset($subject);
  }

  $subject['subject_name'] = $subject_name;
  $subject['subject_code'] = $subject_code;
  $subject['subject_id'] = $subject_id;

  if (isset($subject['level'])) {
  	if (count($subject['level']) < $limit_sess_num) {
  	  $subject['level'][] = $level;
  	}
  } else {
	$subject['level'][] = $level;
  }

  if ($max_session[$i] < count($subject['level']) && count($subject['level']) <= $limit_sess_num) {
    $max_session[$i] = count($subject['level']);
  }

  $last_sub = $subject_id;
}

// add last subject
$subjects[$i][] = $subject;

// loop through subjects
for ($sub=0; $sub < count($subjects[$i]); $sub++) {

  // add session values
    for ($ses=0; $ses < count($subjects[$i][$sub]['level']); $ses++) {

      // add min
      if (!isset($minima[$i])) {
		$minima[$i][$ses] = $subjects[$i][$sub]['level'][$ses];
	  } else {
	  if (count($minima[$i]) < $ses + 1 || $minima[$i][$ses] > $subjects[$i][$sub]['level'][$ses]) {
        $minima[$i][$ses] = $subjects[$i][$sub]['level'][$ses];
      }
      }

      // add max
      if (!isset($maxima[$i][$ses])) {
      	$maxima[$i][$ses] = $subjects[$i][$sub]['level'][$ses];
      } else {
      	if ($maxima[$i][$ses] < $subjects[$i][$sub]['level'][$ses]) {
        	$maxima[$i][$ses] = $subjects[$i][$sub]['level'][$ses];
      	}
      }

      // add mean
      $means[$i][$ses][] = $subjects[$i][$sub]['level'][$ses];
    }

  }

  // calculate mean by session
  for ($mean=0; $mean < $max_session[$i]; $mean++) {
  $counter = 0;
  $sum = 0;
  for ($z=0; $z < count($means[$i][$mean]); $z++) {
    $counter++;
    $sum += $means[$i][$mean][$z];
  }
  $graph_means[$i][] = round($sum/$counter,1);
}

		}




		echo "<script type='text/javascript'>";
		echo "dojo.require('dojox.charting.Chart2D');";
		echo "dojo.require('dojox.charting.themes.Adobebricks');";
		echo "dojo.require('dojox.charting.widget.Legend');";


		echo "makeCharts = function(){";
		echo "var chart = new dojox.charting.Chart2D('levelchart', {fill: '#transparent'});";
		echo "chart.setTheme(dojox.charting.themes.Adobebricks);";
		echo "chart.addPlot('default', {type: 'Lines', markers: true});";
		echo "chart.addAxis('x', {fixUpper: 'major', minorTicks: false, minorLabels: false, microTicks: false, font: 'normal normal normal 8pt Tahoma'});";
		echo "chart.addAxis('y', {vertical: true, fixUpper: 'major', majorTickStep: 1, min: 1, minorTickStep: 0.5, minorLabels: false, font: 'normal normal normal 8pt Tahoma'});";

		for ($i=0; $i < count($handlers); $i++) {
  echo "chart.addSeries('" .  $handlers[$i]['description'] . "', [". implode(',',$graph_means[$i]) ."], {stroke: {width:2}});";
}
?>

		chart.render(); var legend = new dojox.charting.widget.Legend({chart:
		chart, horizontal: false}, 'legend'); }; dojo.addOnLoad(makeCharts);
		</script>


		<div
			style="width: 800px; float: left; margin-top: 20px; margin-bottom: 20px;">

			<?php

			if (isset($handlers_unitid) && count($handlers_unitid)>0) {
echo "<div style='margin:0 auto; width: 740px;'>";

echo "<div style='float:left; width: 550px;'>";
echo "<div>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<strong>Level</strong></div>";
echo "<div id='levelchart' style='width: 550px; height: 320px;'></div>";
echo "<div align=center><strong>Session</strong></div>";
echo "</div>";

echo "<div style='float:right; width: 180px; border:1px dotted #000; padding: 3px'>";
echo "<strong>Legend</strong>";
echo "<div id='legend' style='width: 180px;'></div>";
echo "</div>";

echo "</div>";

} else {

}

?>

			<div style="clear: both; margin-bottom: 20px"></div>

			<div style="width: 800px; float: left;">

				<?php
				for ($i=0; $i < count($handlers); $i++) {
  echo "<table class='tatool' width=800>";
  echo "<tr>";
  echo "<td class='label' width=100><strong>Description</strong></td><td>" . $handlers[$i]['description'] . "</td>";
  echo "</tr>";
  echo "<tr>";
  echo "<td class='label' width=100><strong>Handler ID</strong></td><td>" . $handlers[$i]['unitid'] . "</td>";
  echo "</tr>";
  echo "</table>";
  echo "<br>";

  echo "<table class='tatool' width=800>";
  echo "<tr><th>Subject \ Session</th>";
  for ($j=0; $j < $max_session[$i]; $j++) {
  echo "<th>" . ($j + 1) . "</th>";
 }
 echo "</tr>";

 // loop through subjects
 for ($sub=0; $sub < count($subjects[$i]); $sub++) {

  echo "<tr>";

  if ($blind_cd==1) {
    $name = $subjects[$i][$sub]['subject_code'];
    echo "<td width=160>" . $name . "</td>";
  } else {
    $name = $subjects[$i][$sub]['subject_name'];
    echo "<td width=160><a href='subject_data.php?subject_id=" . $subjects[$i][$sub]['subject_id'] . "&study_id=$study_id&module_id=$module_id' class='ext_link'>" . $name . "</a></td>";
  }



  // add session values
  for ($ses=0; $ses < count($subjects[$i][$sub]['level']); $ses++) {
    echo "<td align=center class='value' width=25>" . $subjects[$i][$sub]['level'][$ses] ."</td>";
  }

  // add missing cells
  if (count($subjects[$i][$sub]['level']) < $max_session[$i]) {
    $miss_cells = $max_session[$i] - count($subjects[$i][$sub]['level']);
    for ($m = 0; $m < $miss_cells; $m++) {
      echo "<td></td>";
    }
  }

  echo "</tr>";
 }

 // display mean total
 echo "<tr>";
 echo "<td class='label'>Mean</td>";
 for ($mean=0; $mean < $max_session[$i]; $mean++) {
  $counter = 0;
  $sum = 0;
  for ($z=0; $z < count($means[$i][$mean]); $z++) {
    $counter++;
    $sum += $means[$i][$mean][$z];
  }
  echo "<td class='total'>" . round($sum/$counter,1) . "</td>";
 }
 echo "</tr>";

 // display min total
 echo "<tr>";
 echo "<td class='label'>Min</td>";
 for ($min=0; $min < $max_session[$i]; $min++) {
  echo "<td class='total'>" . $minima[$i][$min] . "</td>";
 }
 echo "</tr>";

 // display max total
 echo "<tr>";
 echo "<td class='label'>Max</td>";
 for ($max=0; $max < $max_session[$i]; $max++) {
  echo "<td class='total'>" . $maxima[$i][$max] . "</td>";
 }
 echo "</tr>";

 echo "</table><br><br>";

}

?>

			</div>

			<br> <a href="index.php" class="text_link">Overview</a>
		</div>
	</div>
</body>


</html>
