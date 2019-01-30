<?php
session_start();
$now = time();
if(isset($_SESSION['debug'])){$debug=$_SESSION['debug'];}
if (isset($_SESSION['timeout']) && $now > $_SESSION['timeout']) {
	session_unset();
	session_destroy();
	session_start();
}
if (isset($_SESSION['id'])) {
 session_unset();
 session_destroy();
 session_start();
 setcookie("id",null,$now - 600);
}
$_SESSION['timeout'] = $now + 1800;
if(isset($_SESSION['debug'])){$_SESSION['debug']=$debug;}
require_once "header.php";
$auth='false';
$usernameerr=$passwderr=$namefirsterr=$namelasterr='';
$username=$passwd=$namefirst=$namelast='';
if(isset($_SESSION['auth']) && $_SESSION['auth']=='true'){
 $auth='true';
 $useremail=$_SESSION['useremail'];
}
if($auth=='true'){
	$result=db_select("SELECT namefirst, namelast, orgid, manager FROM staff WHERE useremail='$useremail'");
	if(!$result){echo "Something's really weird here. How is this user authorised, but has no detail?";}else{
		$namefirst=$result[0]['namefirst'];
		$namelast=$result[0]['namelast'];
		$orgid=$result[0]['orgid'];
		$manager=$result[0]['manager'];
		$result=db_select("SELECT orgname FROM organisations WHERE orgid='$orgid'");
		if(!$result){echo "Couldn't find your organisation??";} else {
			$orgname=$result[0]['orgname'];
		}
	}
}
// *** Links Line
echo "<a href='index.php'>Home</a>";
if($auth=='true'){
	echo "<a href='roster.php'>Roster</a>";
	if($manager>=1){echo "<a href='shifts.php'>Staff</a>";}
	if($manager>=2){echo "<a href='roster.php'>Manage</a>";}
} else {
	echo "<a href='login.php?action=employee'>Login</a>";
}
echo "<a href='login.php?action=userreg'>Staff Registration</a>";
echo "<a href='login.php?action=register'>Organisation Registration</a>";
echo "<a href='resources/debug.php'>Debug</a>";
echo "<a href='login.php?action=logout'>Logout</a>";
// *** Front Page text
if($auth=='true'){
	echo "<p>Welcome back, " . $namefirst . " " . $namelast . " from " . $orgname . ".<br>";
	echo "You are logged in.<br><br></p>"; 
} else {
	echo "<p>Welcome to RosterEZ. Please choose from the links above.</p>";
	echo "<p>You are not logged in.</p><br>"; 
}
?>
  <br><br>
  </p>
 </div>
</div>
</body>
<?php
if(isset($_SESSION['debug']) && ($_SESSION['debug']=='true')){
 echo "</p><br><br><p>_COOKIE: <br>";
 var_dump($_COOKIE);
 echo "</p><br><br><p>_POST: <br>";
 var_dump($_POST);
 echo "</p><br><br><p>_SESSION: <br>";
 var_dump($_SESSION);
}
?>
  </p>
 </div>
</div>
</body>