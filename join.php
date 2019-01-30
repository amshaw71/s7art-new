<?php
session_start ();
require_once 'header.php'
?>
<div class="row container-fluid">
 <div class="col-xs-12 col-sm-8 col-sm-offset-2 col-md-8 col-md-offset-3">
  <p>
<?php
$confirmstring=$id=$dupusername=0;
$err=false;
$sw3='';
//var_dump(get_defined_vars());
require_once 'resources/opendb.php';
if ( isset ($_REQUEST['confirmstring'])) {
 $confirmstring=db_quote($_REQUEST['confirmstring']);
 $confirmstring = trim($confirmstring,"'");
}
if ($confirmstring==0) {
 $sw1="register";
 } else {
 $sw1="confirm";
}
switch ($sw1) {
case "confirm":
 $result = db_query("SELECT userid, useractive, namefirst, namelast FROM user WHERE confirmstring='$confirmstring';");
 while($row = mysqli_fetch_array($result, MYSQLI_ASSOC)){
  $id=$row["userid"];
  $useractive=$row["useractive"];
  $namefirst=$row["namefirst"];
  $namelast=$row["namelast"];
 }
 if ($id == ""){ $sw2 = "notfound";
 } elseif ($useractive == "1"){ $sw2 = "alreadyconfirmed";
 } else {
 $sw2 = "setactive";
 }
 break;
case "register":
 if ($_POST['username']=="") {
  $err=true;
 } else {
 $username=db_quote($_POST['username']);
 }
 if ($_POST['passwd']=="") {
  $err=true;
 } else {
  $passwd=$_POST["passwd"];
 }
 if ($_POST['namefirst']=="") {
  $err=true;
 } else {
  $namefirst=db_quote($_POST["namefirst"]);
 }
 if ($_POST['namelast']=="") {
  $err=true;
 } else {
  $namelast=db_quote($_POST["namelast"]);
 }
 if ($err==true){
  $sw2="missing";
 } else {
  echo "Good one... You've completed the form successfully.</p><br>";
  $sw2="checkdupid";
 }
break;
default:
}
switch($sw2) {
case "missing":
	echo "(In Yoda Voice) OOoh, a mistake you have made, failed your registration is...<br>";
	echo "One of the required details is missing, please use the link below to return and try again!<br>";
break;
case "notfound":
	echo "<p>Your confirmation ID was not found, try copying and pasting the link from your email directly into your web browser.</p>";
break;
case "alreadyconfirmed":
	echo "<p>We've already confirmed that S7artID. Thanks for stopping by, all the same!</p>";
break;
case "checkdupid":
	$chkusername = trim($username,"'");
	echo "<p>Checking for a user with email address: " . $chkusername . " already in the system...</p><br>";
	$result = db_query("SELECT * FROM user WHERE username = '$chkusername';");
	while ($row = mysqli_fetch_assoc($result)){
		$dupid= $row['userid'];
		$dupusername= $row['username'];
		$dupnamefirst= $row['namefirst'];
		$dupnamelast= $row['namelast'];
		$dupcreatedate= $row['createdate'];
		$dupdatemodified= $row['datemodified'];
		$dupuseractive= $row['useractive'];}
	if ($dupusername == '') {
		echo "<p>Not Found, continuing registration...</p>";
		$sw3="registering";
	} else {
		$id=$dupid;
		echo "<p> We found S7artID: " . $dupid . " registered to " . $dupusername . " by " . $dupnamefirst . " ". $dupnamelast;
		echo ", on " . $dupcreatedate .".</p><br>";
		if ($dupdatemodified<>"") { echo "<p>S7artID last modified: " . $dupdatemodified . "</p><br>"; }
		if ($dupuseractive ==0) { echo "<p>Check your email for the confirmation link sent when you created your account</p><br>"; }
		echo "<p>Follow the link below to load your links</p>";
	}
break;
case "setactive":
	$result = db_query("UPDATE user SET useractive = '1' WHERE userid = '$id'");
	if(!$result){
	echo "Oops, updating useractive failed";
	}
	$sw3 = "endconfirm";
 break;
 default:
}
switch ($sw3) {
	case "registering":
		$createdate=time();
		$confirmstring = $createdate * M_PI * mt_rand(1000,5000);
		$count=0;
		$useractive=0;
		$result=db_query("SELECT MAX(userid) FROM user;");
		while($row=mysqli_fetch_array($result)){
			$maxuserid=$row['MAX(userid)'];
		}
		$userid=$maxuserid+1;
		$result=db_query("INSERT into user (userid, username, namefirst, namelast, passwd, createdate, useractive, confirmstring, count)
		VALUES ($userid," . $username . "," . $namefirst . "," . $namelast . ", SHA1('$passwd'), (FROM_UNIXTIME($createdate)), $useractive, $confirmstring, 0)");
		if(!$result){
		die("Oops, user insertion failed");
		}
		$id=$userid;
		$result=db_query("SELECT username, namefirst, namelast, confirmstring FROM user WHERE userid=$id;");
		while($row=mysqli_fetch_array($result, MYSQLI_ASSOC)){
		$username=$row["username"];
		$namefirst=$row["namefirst"];
		$namelast=$row["namelast"];
		$confirmstring=$row["confirmstring"];
		}
		$to=$username;
		require_once 'resources/joinemail.php';
		//  echo "<h3>(REMOVEME) Confirmlink: <a href='join.php?confirmstring=" . $confirmstring . "'>Confirm</a></h3>";
		echo "<br><p>Your registration is nearly completed... A confirmation email has been sent.<br>";
		echo "Please check your email (especially your Spam or Junk Folder) and click on the confirmation link to fully activate.<br>";
		echo "Your S7artID will be: <br></p>";
		echo "<h2>" . $userid . "</h2>";
	break;
	case "endconfirm":
		echo "<br>Your S7artID " . $id . " has been activated. You can now load your own links onto the homepage.";
		echo "<br>We hope you enjoy using S7art to load your links on any system that accesses the Internet.";
		echo "<br><br>Please tell all your friends about us!";
	default:
}
?>
   </p>
   <a href="index.php?id=<?php echo $id;?>">S7art Home</a>
   </form>
  </div>
 </div>
</body>
</html>
