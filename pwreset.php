<?php
session_start ();
//$id=$_SESSION['id'];
//$count=$_SESSION['count'];
//$count++;
$passwderr='';
$passwd='';
$condition='retrieve'; //always assuming we're retrieving a password first time

require_once 'header.php';
// ******** Launch Email - remove after sending ********
require_once 'resources/opendb.php';

// ******** Password Validation *********
if($_SERVER['REQUEST_METHOD']=='POST'){
 if(empty($_POST['passwd'])){
  $passwderr='Password is required';
 } else {
  $passwd=$_POST['passwd'];
  if(strlen($passwd)<6) {
   $passwderr="Minimum 6 Characters required";
  }
 }
}

// ******** Condition Handling ********


if(isset($_POST['username'])) {
 $condition='retrieve';
} elseif ((isset($_POST['passwd'])) && ($passwderr=='')){
 $condition='reset';
} else {
 $condition='confirm';
}
// $_SESSION['id']=$id;
// $_SESSION['auth']=$auth;
// $_SESSION['count']=$count;


?>



<!-- ******** Start of Second Row ********  -->

<div class="row container-fluid">
 <div class="col-xs-6 col-sm-5 col-sm-offset-1 col-md-3 col-md-offset-1 col-lg-2 col-lg-offset-2">
  <?php if ($id <> ""){echo "<span class='text-nowrap'><h4>S7ARTID: $id</h4></span>". PHP_EOL;} ?>
 </div>
 <div class="col-xs-5 col-sm-5 col-md-7 col-lg-6 text-right">
 <h4><span class="text-nowrap" id="servertime">Current Date/Time...</span></h4>
<?php
 $moddatetime=time();
 $forwarded_for='HTTP_X_FORWARDED_FOR';
 $remote_addr='REMOTE_ADDR';
 if (getenv($forwarded_for)) {
  $pipaddress = getenv($forwarded_for);
  $ipaddress = getenv($remote_addr);
  echo "<h4>IP: ".$pipaddress. " (proxy $ipaddress)</h4>";
 } else {
  $ipaddress = getenv($remote_addr);
  echo "<h4>IP: $ipaddress</h4>";
}
?>
 </div>
</div>


<!-- ******** End Second Row - Start Third Row ********  -->

<div class="row row-eq-height container-fluid">
 <div class="col-xs-12 col-sm-6 col-sm-offset-1 col-md-5 col-md-offset-1 col-lg-4 col-lg-offset-2 text-justify">
 <h3>Password Update</h3>


<?php
switch ($condition) {


case 'retrieve':
 if ($id==1123341 or $id==123413244) {
  echo "Supplied password SHA1: " . $suppliedpw . "<br />";
  echo "\$auth: " . $auth . "<br />";
  echo "Count: " . $count . "<br />";
  echo "Cookie ID: " . $_COOKIE['id'] . "<br />";
  echo "Session ID: " . $_SESSION['id'] . "<br />";
  echo "Increment ID: " . $incrid . "<br />";
  echo "Decrement ID: " . $decrid . "<br />";
  echo "Increment Link ID: " . $incruserid . "<br />";
  echo "Decrement Link ID: " . $decruserid . "<br />";
  echo "Condition is: " . $condition . "<br />";
  echo "Server time is: " . date("F d, Y H:i:s", $moddatetime) . "<br />";
  print_r($_SESSION);
 }
 $suppliedusername=db_quote($_POST['username']);
 echo "<p>Checking for username: " . $_POST['username'] . " in the database...</p><br>";
 $result=db_select("SELECT * from user WHERE username=$suppliedusername LIMIT 1;");
 if(!$result) {
  echo "<p>We couldn't find that email address, are you sure it was entered correctly?</p>";
  echo "<p><a href='index.php#pwreset_form'>Back</a> - ";
  echo "<a href='index.php'>S7art Home</a></p>";
 } else {
  $to=$result[0]['username'];
  $userid=$result[0]['userid'];
  $username=$result[0]['username'];
  $namefirst=$result[0]['namefirst'];
  $namelast=$result[0]['namelast'];
  $createdate=time();
  $confirmstring = intval($createdate * M_PI * mt_rand(1000,5000));
  $result=db_query("UPDATE user SET confirmstring='$confirmstring', datemodified=(FROM_UNIXTIME($createdate)) WHERE userid='$userid';");
  require 'resources/pwemail.php';
  echo "<p>Email address: " . $username . " is linked with S7artID: " . $userid . "</p><br><br>" . PHP_EOL;
  echo "<p>We've sent an email.<br> Please click the confirmation link in the email to reset your password :->.</p><br>" . PHP_EOL;
 }
 break;


 case 'confirm':
  $confirmstring=$_REQUEST['confirmstring'];
  $result=db_select("SELECT userid, username, namefirst, namelast from user WHERE confirmstring=$confirmstring;");
  if(!$result) { // Confirmstring failed, notify user:
   echo "<p>There has been some sort of error with your confirmation, or that link has already been used.";
   echo " If you're sure it hasn't been used, try copying and pasting the link we sent you directly into your web browser...<br>";
   echo "<a href='index.php'>S7art Home</a></p>";
   
  } else {
  $namefirst=$result[0]['namefirst'];
  $namelast=$result[0]['namelast'];
  $userid=$result[0]['userid'];
  $username=$result[0]['username'];
  $namefirst=$result[0]['namefirst'];
  $namefirst=$result[0]['namefirst'];
echo <<<EOF
<h2>Hello $namefirst $namelast</h2>
<p>Your S7artID is: $userid, linked to email address: $username.</p>
<p>Enter your password and confirm here to reset:</p><br>
EOF;
echo "<form role='form' action='" . htmlspecialchars($_SERVER["PHP_SELF"]) . "' method='post' target='_top'>";
echo <<<EOF
 <div class="input-group">
  <div>
   <label for="password">Password</label>
   <input class="form-control" type="password" name="passwd" placeholder="New Password" value="" />
  </div>
  <div>
   <label for="confirm">Confirm</label>
   <input type="password" name="confirm" placeholder="Confirm" value="" class="form-control">
   <input type="hidden" name="userid" value=$userid>
   <input type="hidden" name="confirmstring" value=$confirmstring>
   <div></div>
  <span class="input-group-btn"><button class="btn btn-default" type="submit">Reset</button></span>
 </div>
</div>
</form>
<h4>$passwderr</h4>
EOF;
 }
 break;


 case 'reset':
 //code to reset the pw in db here
 echo "<p>Resetting your password now...";
 $userid=$_POST['userid'];
 $passwd=$_POST['passwd'];
 $result=db_query("UPDATE user SET passwd=(SHA1('$passwd')) WHERE userid='$userid';");
 if(!result){
  echo "Error updating your password in the database...";
 } else {
  echo "Password update complete...";
  echo "<br><br><p>Click the link below to return to your home page, your password is reset!</p>";
  echo "<p><a href='index.php?id=" . $userid . "'>S7art Home</a>";
  $_SESSION['auth']=true;
  $_SESSION['id']=$userid;
  $createdate=time();
  $confirmstring = $createdate * M_PI * mt_rand(1000,5000);
  $result=db_query("UPDATE user SET confirmstring=$confirmstring WHERE userid='$userid';");
  if(!result){
  echo "Error clearing confirmstring in the database";
  }
 }
 break;

default:
}


if ($id==3423451 or $id==13123414) {
   echo "<br>";
   echo "Supplied password SHA1: " . $suppliedpw . "<br />";
   echo "\$auth: " . $auth . "<br />";
   echo "Count: " . $count . "<br />";
   echo "Cookie ID: " . $cookieid . "<br />";
   echo "Session ID: " . $sessionid . "<br />";
   echo "Request ID: " . $requestid . "<br />";
   echo "Increment ID: " . $incrid . "<br />";
   echo "Decrement ID: " . $decrid . "<br />";
   echo "Increment Link ID: " . $incruserid . "<br />";
   echo "Decrement Link ID: " . $decruserid . "<br />";
   echo "Condition is: " . $condition . "<br />";
   echo "Server time is: " . date("F d, Y H:i:s", $moddatetime) . "<br />";
   var_dump(get_defined_vars());
   echo "<br><br>";
}







?> 
