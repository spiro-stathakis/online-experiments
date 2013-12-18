<?php
include("../data/setting.php");
$cnx = connectdb();

$username=$_POST['username'];
$password=$_POST['password'];
 
$q_user = $cnx->prepare("SELECT USER_ID, USER_NAME, USER_PASS FROM tat_user WHERE USER_NAME =:username");
$q_user->bindParam(':username', $username, PDO::PARAM_STR);
$q_user->execute();

$result = $q_user->fetch(PDO::FETCH_ASSOC);
var_dump($result); 
 
if(!$result['USER_NAME']){
header('location:index.php');
die();
}
if($password != $result['USER_PASS']){
header('location:index.php');
die();
}
 
$USER_ID = $result['USER_ID'];

$_SESSION['USER_ID'] = $USER_ID;
$_SESSION['username'] = $username;

$cookie_name = "tatoolStats";
$cookie_time = (3600 * 24 * 30); // 30 days
setcookie ($cookie_name, 'USER_ID='.$USER_ID.'&username='.$username, time() + $cookie_time);
header('location:study.php');
 
 
?>
