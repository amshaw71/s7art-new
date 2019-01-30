<?php
session_start();
$now = time();
if (isset($_SESSION['timeout']) && $now > $_SESSION['timeout']) {
    session_unset();
    session_destroy();
    session_start();
}
$_SESSION['timeout'] = $now + 1800;
$useremailerr=$passwderr='';
// cookie only really determines the username and how many failed logins exist from previous login attempts.
if(isset($_COOKIE['useremail'])){ $useremail=$_COOKIE['useremail'];}
if(isset($_COOKIE['logincount'])){ $logincount=$_COOKIE['logincount'];}
if(isset($_SESSION['useremail'])){ $useremail=$_SESSION['useremail'];}
if($_SESSION['setcookie']=='checked'){ $setcookie='checked';}
if($_SESSION['setcookie']=='unchecked'){ $setcookie='unchecked';}
if($_POST['validating']=='true'){ 
	$logincount=$_POST['logincount'];
	if($_POST['setcookie']=='on'){
		setcookie("setcookie","checked",$now+31536000);
		setcookie("useremail","$useremail",$now+31536000);
		$_SESSION['setcookie']='checked';
		$setcookie='checked';
	} else {
		setcookie("setcookie", "unchecked", $now+31536000);
		setcookie("useremail", "", $now - 600);
		$_SESSION['setcookie']='unchecked';
		$setcookie='unchecked';
	}
	if(isset($_POST['useremail'])){
		$useremail=test_input($_POST['useremail']);
		if(empty($useremail)){$useremailerr = "You must supply an email address to login.";}
		if(!filter_var($useremail, FILTER_VALIDATE_EMAIL)){$useremailerr='Invalid username/email format';}
	}
	if(isset($_POST['passwd'])){
		$passwd=$_POST['passwd'];
		if(empty($passwd)){$passwderr='Password is required';} else {
			if(strlen($passwd)<6){
			$passwderr='Minimum 6 Characters required in your password';
			$auth=false;
}}}
if($passwderr=='' && $useremailerr==''){
	$passwd=sha1($_POST['passwd']);
	$result=db_select("SELECT useremail, passwd FROM staff WHERE useremail='$useremail'");
	if($result[0]['useremail']==''){
		$msg .= "We don't know that email address, try again..."; 
	} else {
		$msg.="Matching address found... ";
	}
	$passwdtrue=$result[0]['passwd'];
	if($passwd === $passwdtrue){
		$auth='true';
		$msg="Logged in successfully"; 
		$result=db_select("SELECT * FROM staff WHERE useremail='$useremail'");
		$auth=$_SESSION['auth']='true';
		$staffid=$_SESSION['staffid']=$result[0]['staffid'];
		$useremail=$_SESSION['useremail']=$result[0]['useremail'];
		$namefirst=$_SESSION['namefirst']=$result[0]['namefirst'];
		$namelast=$_SESSION['namelast']=$result[0]['namelast'];
		$count=$_SESSION['count']=$result[0]['count'];
		$orgid=$_SESSION['orgid']=$result[0]['orgid'];
		$lastaccess=$_SESSION['lastaccess']=$result[0]['lastaccess'];
		$staffid=$_SESSION['staffid']=$result[0]['staffid'];
		$hourlyrate=$_SESSION['hourlyrate']=$result[0]['hourlyrate'];
   		$result=db_query("UPDATE staff SET lastaccess=(FROM_UNIXTIME($now)) WHERE orgemail='$useremail'");
		header('Location: roster.php');
		} else {
			$msg.="Password incorrect, try again.";
			$auth='false';
			$_SESSION['auth']='false';
}}}
if($logincount>5){ 
	$msg.=" You have tried " . $logincount . " times...";
}
$logincount++;
?>
 <h2>Employee login</h2>
 <p>Welcome back to RosterEZ. <br></p>
 <p>Login with your email and password to continue.<br><br></p>
<div class="container">
 <form class="form-horizontal" role="form" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . "?action=submit";?>" target="_top">
  <input type="hidden" name="action" value="employee">
  <input type="hidden" name="validating" value='true'>
  <input type="hidden" name="logincount" value="<?php echo $logincount; ?>">
   <div class="form-group">
    <label class="control-label col-sm-1" for="email">Email:</label>
     <div class="col-sm-4">
     <?php echo $useremailerr;?>
     <input type="email" class="form-control" name="useremail" id="useremail" placeholder="Email Address" value="<?php echo $useremail; ?>">
    </div>
   </div>
  <div class="form-group">
   <label class="control-label col-sm-1" for="pwd">Password:</label>
    <div class="col-sm-4">
     <?php echo $passwderr; ?>
     <input type="password" class="form-control" name="passwd" id="passwd" placeholder="Password">
    </div>
  </div>
  <div class="form-group">        
   <div class="col-sm-offset-1 col-sm-2">
    <div class="checkbox">
     <label><input type="checkbox" name="setcookie" <?php echo $setcookie; ?>> Remember me</label>
     </div>
    </div>
   </div>
  <div class="form-group">        
   <div class="col-sm-offset-1 col-sm-2">
    <button type="submit" class="btn btn-default">Login</button>
   </div>
  </div>
 </form>
<?php
if($_SESSION['debug']=='true'){
 echo "<p>" . $msg . "<br><br><p>";
 echo "</p><br><br><p>_COOKIE: <br>";
 var_dump($_COOKIE);
 echo "</p><br><br><p>_POST: <br>";
 var_dump($_POST);
 echo "</p><br><br><p>_SESSION: <br>";
 var_dump($_SESSION);
}
?>
</div>