<?php
include("../data/setting.php");
$cnx = connectdb();

$q_studies = $cnx->prepare("SELECT STD.STUDY_ID, STD.STUDY_NAME, MDL.MODULE_ID, MDL.MODULE_NR, MDL.MODULE_NAME, GRP.GROUP_ID, GRP.GROUP_NR, GRP.GROUP_NAME 
FROM tat_user_study UST
, tat_study STD
, tat_module MDL
, tat_group GRP
, tat_module_study MST
, tat_group_module GRM 
WHERE UST.STUDY_ID = STD.STUDY_ID
AND STD.STUDY_ID = MST.STUDY_ID
AND MST.MODULE_ID = MDL.MODULE_ID
AND GRM.MODULE_ID = MDL.MODULE_ID
AND GRM.GROUP_ID = GRP.GROUP_ID
AND UST.USER_ID = :user 
ORDER BY STUDY_NAME, MODULE_NR, GROUP_NR");
$q_studies->bindParam(':user', $_SESSION['USER_ID']);

$q_studies->execute();

?>

<!-- table header -->
<table class="tatool">
  <tr>
    <th>Study</th>
    <th>Module</th>
    <th>Group</th>
    <th>Subjects</th>
  </tr>

<!-- table rows -->  
<?php
$last_study_id = 0;
$last_module_id = 0;

while ($field = $q_studies->fetch(PDO::FETCH_ASSOC)) {
  $study_id = $field['STUDY_ID'];
  $study_name = $field['STUDY_NAME'];
  $module_id = $field['MODULE_ID'];
  $module_name = $field['MODULE_NAME'];
  $group_id = $field['GROUP_ID'];
  $group_nr = $field['GROUP_NR'];
  $group_name = $field['GROUP_NAME'];
  
  if ($study_id == $last_study_id) {
  	$study_name = "";
  } else if ($study_id != $last_study_id && $last_study_id != 0) {
     echo "<tr><td colspan=4 class='label'>&nbsp;</td></tr>";    	
  }
  if ($module_id == $last_module_id) {
  	$module_name = "";
  }

  echo "<tr>";
  echo "<td class='label'>$study_name</td>";
  echo "<td><a href='module.php?study_id=$study_id&module_id=$module_id' class='ext_link'>$module_name</a></td>";
  echo "<td><img src='../img/online/group_$group_nr.png' alt='$group_name' title='$group_name'> <a href='group_data.php?study_id=$study_id&module_id=$module_id&group_id=$group_id' class='ext_link'>$group_name</a></td>";
  echo "<td><a href='subjects.php?study_id=$study_id&group_id=$group_id' class='text_link'>manage</a></td>";
  echo "</tr>";

  $last_study_id = $study_id;
  $last_module_id = $module_id;
  
}
?>

</table>