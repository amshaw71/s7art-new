<?php
session_start();
$now = time();
if (isset($_SESSION['timeout']) && $now > $_SESSION['timeout']) {
    session_unset();
    session_destroy();
    session_start();
}
$_SESSION['timeout'] = $now + 1800;
if(isset($_COOKIE['useremail'])){
 $useremail=$_COOKIE['useremail'];
}
if(isset($_SESSION['useremail'])){
 $useremail=$_SESSION['useremail'];
}
include "header.php";
$action='';
if(isset($_REQUEST['action'])){
 $action=$_REQUEST['action'];
} else {
 if(isset($_POST['action'])){$action=$_POST['action'];}
}
switch ($action) {
 case 'employee':
  $type='Employees';
  include "login2.php";
 break;
 case 'userreg';
  $type='Staff';
  include "userreg.php";
 break;
 case 'manager':
  $type='Managers';
  include "login2.php";
 break;
 case 'organisation':
  $type='Organisations';
  include "login2.php";
 break;
 case 'register':
  include "orgreg.php";
 break;
 case 'logout':
	setcookie('logincount',0,$now+31536000);
	if(isset($_SESSION['setcookie']) && $_SESSION['setcookie']=='checked'){
		$_COOKIE['useremail']=$_SESSION['useremail'];
		if(isset($_COOKIE['logincount'])){$_COOKIE['logincount']=$_SESSION['logincount'];}
		$_SESSION['useremail']='';
	} else {
		$_COOKIE['useremail']='';
		$_SESSION['useremail']='';
	}
	$debug=$_SESSION['debug'];
	session_unset();
	session_destroy();
	session_start();
	$_SESSION['debug']=$debug;
	echo "<p>You are now logged out. We hope to see you soon (but not too soon ;-)</p><p>" . PHP_EOL;
	echo "<a href='index.php'>RosterEZ</a></p><p>" . PHP_EOL;
	echo "<a href='login.php?action=employee'>Log Back In</a></p><p>" . PHP_EOL;
	echo "<a href='http://s7art.com/'>S7art.com</a></p><p>" . PHP_EOL;
	echo "</p><br><br><p>_COOKIE: <br>" . PHP_EOL;
	var_dump($_COOKIE);
	echo "</p><br><br><p>_POST: <br>" . PHP_EOL;
	var_dump($_POST);
	echo "</p><br><br><p>_SESSION: <br>" . PHP_EOL;
	var_dump($_SESSION);
	header('Location: index.php');
 break;
 default:
  echo "<p>Whoa back now, Johnny! That DID NOT compute, y'all.</p>";
}
?>