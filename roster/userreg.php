<?php
session_start();
$now = time();
if (isset($_SESSION['timeout']) && $now > $_SESSION['timeout']) {
    session_unset();
    session_destroy();
    session_start();
}
$_SESSION['timeout'] = $now + 1800;
require_once "header.php";
$passwderr=$userphoneerr=$useremailerr=$namefirsterr=$namelasterr='';
$confirmstring=test_input($_REQUEST['confirmstring']);
// echo $confirmstring;
$manager=0;
$namefirst=$namelast=$passwd="";
$userphone="Phone number, for SMS";
$useremail="Email Address";
if($_REQUEST['action']=='submit'){
 $namefirst=test_input($_POST['namefirst']);
 $namelast=test_input($_POST['namelast']);
 $userphone=test_input($_POST['userphone']);
 $useremail=test_input($_POST['useremail']);
 $passwd=$_POST['passwd'];
 $manager=$_POST['manager'];
 $confirmstring=$_POST['confirmstring'];
 $result=db_select("SELECT orgemail, passwd FROM organisations WHERE passwd=(SHA1('$passwd'))");
 if(!$result){
  echo $result[0]['orgemail'] . " " . $result[0]['passwd'];
 } else {
  echo "Email address has changed from company registration";
  $newpass='true';
 }
}
if($confirmstring<>'' && ($_POST['action']<>"submit")) {
 $result=db_select("SELECT orgid, orgemail, orgphone, passwd FROM organisations WHERE confirmstring='$confirmstring'");
 $orgid=$result[0]['orgid'];
 $userphone=$result[0]['orgphone'];
 $useremail=$result[0]['orgemail'];
 $passwd=$result[0]['passwd'];
 $manager=2;
 $orgemail=$useremail;
}
if(empty($_POST['namefirst'])){ $namefirsterr='First Name is required'; } else {
 if(!preg_match("/^[a-zA-Z0-9'\-\/, ]*$/",$namefirst)){
  $namefirsterr = 'Only letters, white space and punctuation allowed';
 }
}
if(empty($_POST['namelast'])){ $namelasterr='Last name is required'; } else {
 if(!preg_match("/^[a-zA-Z0-9'\-\/, ]*$/",$namelast)){
  $namelasterr = 'Only letters, white space and punctuation allowed';
 }
}
$formemail=test_input($_POST['useremail']);
if($_POST['action']=='submit' && $formemail<>$orgemail){$useremailerr = "You must use the same email address as used in the company registration.";}
if(empty($useremail)){$useremailerr = "You must use an email address for registration.";}
if(!filter_var($useremail, FILTER_VALIDATE_EMAIL)){$useremailerr='Invalid username/email format';}
if(empty($userphone)){ $userphoneerr = "You must use a phone number for registration.";} 
else {
 if(strlen($userphone)<10) {$userphoneerr='Phone Number not complete';}
}
if(!preg_match("/^[a-zA-Z0-9+()\- ]*$/",$userphone)){
 $userphoneerr = 'Only numbers, white space and phone punctuation allowed in your phone number';
}
if(empty($passwd)){
 $passwderr='Password is required';
} else {
 if(strlen($passwd)<6){
  $passwderr='Minimum 6 Characters required in your password';
 }
}
if($passwderr=='' && $userphoneerr=='' && $useremailerr=='' && $namefirsterr=='' && $namelasterr==''){
 // echo "Passed Validation";
} else {
 // echo "Failed Validation";
}
?>
 <div class="col-xs-12 col-sm-5 col-sm-offset-1 col-md-4 col-md-offset-2 col-lg-4 col-lg-offset-2">
  <p>Welcome back to RosterEZ. Tell us a little more about the 'real' you.</p>
  <form role="form" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . "?action=userreg";?>" target="_top">
   <div class="input-group-large">
    <?php echo $namefirsterr; ?>
    <input type="text" name="namefirst" placeholder="First Name" value="<?php echo $namefirst; ?>" class="form-control"><br>
    <?php echo $namelasterr; ?>
    <input type="text" name="namelast" placeholder="Last Name" value="<?php echo $namelast; ?>" class="form-control"><br>
    <?php echo $useremailerr; ?>
    <input type="email" name="useremail" placeholder="<?php echo $useremail; ?>" value="<?php echo $useremail; ?>" class="form-control"><br>
    <?php echo $userphoneerr; ?>
    <input type="text" name="userphone" placeholder="<?php echo $userphone; ?>" value="<?php echo $userphone; ?>" class="form-control"><br>
    <?php echo $passwderr; ?>
    <input type="password" name="passwd" placeholder="<?php echo $passwd; ?>" value="<?php echo $passwd; ?>" class="form-control"><br>
    <input type='hidden' name="orgid" value="<?php echo $orgid; ?>">
    <input type='hidden' name="manager" value="<?php echo $manager; ?>">
    <input type='hidden' name="confirmstring" value="<?php echo $confirmstring; ?>">
    <br>
<?php
 if($passwderr=='' && $userphoneerr=='' && $useremailerr=='' && $namefirsterr=='' && $namelasterr==''){
 echo "<p>Checking that user in the database...</p><br>". PHP_EOL . "</div>" . PHP_EOL . "</form>";
 $result=db_select("SELECT useremail FROM staff WHERE useremail='$useremail'");
 if(!$result){ 
  echo "<p>No duplicate email address found, registration continuing.</p>"; 
 } else {
  echo "<p>Duplicate user found - Registration halted. </p>";
  $useremail="";
  $insert='false';
 }
 $result=db_select("SELECT MAX(staffid) FROM staff");
 $maxstaffid=$result[0]['MAX(staffid)'];
 $staffid=$maxstaffid+1;
 if($newpass=='true'){ 
  $passwd=sha1($_POST['passwd']);
 }
 if($insert=='false'){ 
  echo "<p>Likely you have already registered this address. Try logging in or resetting your password for that account.</p>"; 
 } else {
  $result=db_query("INSERT INTO staff (staffid, orgid, useremail, namefirst, namelast, userphone, passwd, createdate, useractive, lastaccess, confirmstring, count, manager)  
  VALUES ($staffid, $orgid, '$useremail', '$namefirst', '$namelast', '$userphone', '$passwd', (FROM_UNIXTIME($now)), 1, (FROM_UNIXTIME($now)), '$confirmstring', 1, '$manager')");
  if(!$result){ echo "<p>Database insert failed.</p>"; } else {
   echo "<br><p>User created successfully. You can now use the employee login.</p>";
   if($manager=='2'){
    echo "<p>Your username is now updated as the master user for your organisation. You can now create shifts for your organisation and assign other managers.<br>";
    echo "<p>Follow the Login link below to continue.</p><br><a href='login.php?action=employee'>Login</a><br>";
    $result=db_query("UPDATE organisations SET masteruserid='$staffid' WHERE orgemail='$useremail'");
    if(!$result){
     echo "Couldn't update organisation with this staffid.";
    }
   }
  }
 }
} else {
 echo "<br><button class='btn btn-default' type='submit'>Register</button>" . PHP_EOL . "</div>" . PHP_EOL . "</form>";
}
?>