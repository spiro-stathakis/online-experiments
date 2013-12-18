
<center>
	<?php

if ($blind_cd == 0) {
echo "<a href='#' class='grey-button pcb' onclick=\"window.location.href='subject_detail_new.php?study_id=$study_id&group_id=$group_id'\">
    <span>Add Subject</span>
  </a>";
} else {
echo "<a href='#' class='grey-button pcb' onclick=\"demo()\">
    <span>Add Subject</span>
  </a>";
}
?>
</center>
<br>

<?php

if (isset($_GET['order'])) {
	if ($_GET['order'] == "code") {
		$order_cd = "SUBJECT_CODE ASC";
	} else if ($_GET['order'] == "name") {
		$order_cd = "SUBJECT_NAME ASC, SUB.SUBJECT_ID ASC";
	} else if ($_GET['order'] == "group") {
		$order_cd = "GROUP_NAME ASC, SUBJECT_NAME, SUB.SUBJECT_ID";
	} else {
		$order_cd = "SUB.SUBJECT_CODE ASC";
	}
} else {
	$order_cd = "SUB.SUBJECT_CODE ASC";
}

$q_sub = $cnx->prepare("SELECT SUB.SUBJECT_ID, SUBJECT_STATUS_CD, SUBJECT_CODE, SUBJECT_NAME, SUBJECT_MAIL, SUBJECT_PHONE, SUBJECT_SEX_CD, SUBJECT_BIRTH, SUBJECT_EDUCATION, GRP.GROUP_ID, GRP.GROUP_NR, GRP.GROUP_NAME
		FROM tat_user_study UST
		, tat_study STD
		, tat_group GRP
		, tat_subject_group SUG
		, tat_subject SUB
		WHERE UST.STUDY_ID = STD.STUDY_ID
		AND STD.STUDY_ID = SUB.STUDY_ID
		AND SUG.GROUP_ID = GRP.GROUP_ID
		AND SUG.SUBJECT_ID = SUB.SUBJECT_ID
		AND UST.USER_ID = :user
		AND STD.STUDY_ID = :study_id
		AND SUB.STUDY_ID = :study_id
		AND GRP.GROUP_ID = :group_id
		ORDER BY $order_cd");

$q_sub->bindParam(':user', $_SESSION['USER_ID']);
$q_sub->bindParam(':study_id', $study_id);
$q_sub->bindParam(':group_id', $group_id);
$q_sub->execute();

$q_group = $cnx->prepare("SELECT GROUP_ID, GROUP_NR, GROUP_NAME
		FROM tat_group
		WHERE STUDY_ID = :study_id");
$q_group->bindParam(':study_id', $study_id);
$q_group->execute();

$groups = array();
$counter = 0;
while ($field = $q_group->fetch(PDO::FETCH_ASSOC)) {
  $group_id =  $field['GROUP_ID'];
  $group_name =  $field['GROUP_NAME'];
  $groups[$counter]['group_id'] = $group_id;
  $groups[$counter]['group_name'] = $group_name;
  $counter++;
}

?>

<!-- table header -->
<table class="tatool">
	<tr>
		<th><a
			href="<?php echo "subjects.php?study_id=$study_id&group_id=$group_id&order=code";?>"
			class="top_link">Code</a></th>
		<th>Status</th>
		<th><a
			href="<?php echo "subjects.php?study_id=$study_id&group_id=$group_id&order=name";?>"
			class="top_link">Name</a></th>
		<th>Mail</th>
		<th>Phone</th>
		<th>Sex</th>
		<th>Birth</th>
		<th><a
			href="<?php echo "subjects.php?study_id=$study_id&group_id=$group_id&order=group";?>"
			class="top_link">Group</a></th>
		<th></th>
	</tr>

	<!-- table rows -->
	<?php
	while ($field = $q_sub->fetch(PDO::FETCH_ASSOC)) {
  $subject_id = $field['SUBJECT_ID'];
  $subject_code = $field['SUBJECT_CODE'];
  $subject_status = $field['SUBJECT_STATUS_CD'];
  $subject_name= $field['SUBJECT_NAME'];
  $subject_mail= $field['SUBJECT_MAIL'];
  $subject_phone= $field['SUBJECT_PHONE'];
  $subject_sex= $field['SUBJECT_SEX_CD'];
  $subject_birth= $field['SUBJECT_BIRTH'];
  $subject_education= $field['SUBJECT_EDUCATION'];
  $group_id= $field['GROUP_ID'];
  $group_name= $field['GROUP_NAME'];

  echo "<tr>";
  echo "<td>$subject_code</td>";

  // STATUS_CD
  echo "<td>";
  if ($subject_status == 0) {
    echo "active";
  } else if ($subject_status == 1) {
    echo "done";
  } else if ($subject_status == 2) {
    echo "dropout";
  }
  echo "</td>";

  echo "<td>$subject_name</td>";
  echo "<td>$subject_mail</td>";
  echo "<td>$subject_phone</td>";

  // SEX
  echo "<td>";
  if ($subject_sex == 1) {
    echo "M";
  } else if ($subject_sex == 2) {
    echo "F";
  } else {
    echo "-";
  }
  echo "</td>";

  echo "<td>$subject_birth</td>";

  echo "<td>$group_name</td>";

  if ($blind_cd == 0) {
  echo "<td width=110>
  <a href='#' class='grey-button pcb' onclick=\"window.location.href='subject_detail.php?study_id=$study_id&group_id=$group_id&subject_id=$subject_id'\">
  <span>Edit</span>
  </a><a href='#' class='grey-button pcb' onclick=\"confirmation('subject_detail_delete.php?study_id=$study_id&subject_id=$subject_id&group_id=$group_id')\">
  <span>Delete</span>
  </a>
  </td>";
  } else {
   echo "<td width=110>
		<a href='#' class='grey-button pcb' onclick=\"demo()\">
		<span>Edit</span>
		</a><a href='#' class='grey-button pcb' onclick=\"demo()\">
		<span>Delete</span>
		</a>
		</td>";
  }

  echo "</tr>";

}
?>

</table>
<br>
