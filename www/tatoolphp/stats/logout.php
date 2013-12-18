<?php

session_start();
$cookie_name = "tatoolStats";
setcookie($cookie_name, "");
session_destroy();
header('location:index.php');

?>