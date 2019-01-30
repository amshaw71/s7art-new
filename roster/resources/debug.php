<?php
session_start();
if(isset($_SESSION['debug'])&&$_SESSION['debug']=='true'){
	$_SESSION['debug']='false';
} else {
	$_SESSION['debug']='true';
}
header('Location: ../index.php');
/*
echo "</p><br><br><p>_COOKIE: <br>";
var_dump($_COOKIE);
echo "</p><br><br><p>_POST: <br>";
var_dump($_POST);
echo "</p><br><br><p>_SESSION: <br>";
var_dump($_SESSION);
*/
?>
