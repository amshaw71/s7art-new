<?php
session_start();
$now = time();
if (isset($_SESSION['timeout']) && $now > $_SESSION['timeout']) {
    session_unset();
    session_destroy();
    session_start();
}
$_SESSION['timeout'] = $now + 1800;
if(isset($_POST['orgname'])){$orgname=test_input($_POST['orgname']);}else{$orgname='Organisation Name';}
if(isset($_POST['orgphone'])){$orgphone=test_input($_POST['orgphone']);}else{$orgphone='Phone Number, for SMS';}
if(isset($_POST['orgemail'])){$orgemail=test_input($_POST['orgemail']);}else{$orgemail='youremail@organisation.com';}
if(isset($_POST['confirmstring'])){$confirmstring=$_POST['confirmstring'];}
if(isset($_POST['passwd'])){$passwd=$_POST['passwd'];}else{$passwd='Password (min 6 characters)';}
if(!isset($_POST['returning'])){$returning='false';}else{$returning='true';}
$orgnameerr=$orgphoneerr=$orgemailerr=$passwderr="";
if($returning=='true'){
	if(empty($_POST['orgname'])or($orgname=="Organisation Name")){
		$orgnameerr='Organisation Name is required';
	} else {
		if(!preg_match("/^[a-zA-Z0-9'\/, ]*$/",$orgname)){$orgnameerr = 'Only letters, white space and punctuation allowed';}
	}
	$result=db_select("SELECT orgid, orgemail FROM organisations WHERE orgname='$orgname'");
	if($result){$registered=$result[0]['orgemail'];$orgnameerr="That organisation is already registered to <strong>" . $registered . "</strong>. Send an email to gain access to the site";}
	if(empty($_POST['orgphone'])){$orgphoneerr='A phone number is required';}
	if(strlen($_POST['orgphone'])<10){$orgphoneerr='Phone Number not complete';}
	if(!preg_match("/^[a-zA-Z0-9+()\- ]*$/",$orgphone)){$orgphoneerr = 'Only numbers, white space and phone punctuation allowed in your phone number';}
	$result=db_select("SELECT orgname, orgemail FROM organisations WHERE orgphone='$orgphone'");
	if($result){
		$orgemailerr = "That phone number is already registered to <strong>";
		$orgemailerr .= $result[0]['orgname'];
		$orgemailerr .= "</strong>. Contact ";
		$orgemailerr .= $result[0]['orgemail'];
		$orgemailerr .= " to gain access";
	}
	if(empty($_POST['orgemail']) or $_POST['orgemail']=='youremail@organisation.com'){
		$orgemailerr = 'An email address is required';
	} else {
		$orgemail=test_input($_POST['orgemail']);
		if(!filter_var($orgemail, FILTER_VALIDATE_EMAIL)){
		$orgemailerr='Invalid username/email format';
		}
	}
	$result=db_select("SELECT orgname, orgphone FROM organisations WHERE orgemail='$orgemail'");
	if($result) {
		$orgemailerr="That email address has already been used at ";
		$orgemailerr.= $result[0]['orgname'];
	}
	if(empty($_POST['passwd'])or$_POST['passwd']=='Secret'){
		$passwderr='Password is required';
	} else {
		$passwd=$_POST['passwd'];
		if(strlen($passwd)<6) {
			$passwderr="Minimum 6 Characters required in your password";
		}
	}
}
?>
<p>This is where you register your organisation to begin rostering.</p>
<p>Fill out the form below to begin. All fields are required :)</p><br>
<div class="col-xs-12 col-sm-10 col-sm-offset-1 col-md-8 col-md-offset-0 col-lg-8 col-lg-offset-0">
	<form role="form" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . "?action=register";?>" target="_top">
		<div class="input-group-large">
		<input type="text" name="orgname" placeholder="Organisation Name" value="<?php if($_POST['returning']=='true'){echo $orgname;}?>" class="form-control"><br>
		<input type="text" name="orgphone" placeholder="Phone Number" value="<?php if($_POST['returning']=='true'){echo $orgphone;}?>" class="form-control"><br>
		<input type="text" name="orgemail" placeholder="Email Address" value="<?php if($_POST['returning']=='true'){echo $orgemail;}?>" class="form-control"><br>
		<input type="password" name="passwd" placeholder="Password, 6 character minimum" value="<?php if($_POST['returning']=='true'){echo $passwd;}?>" class="form-control"><br>
		<input type="hidden" name="clearpass" value="<?php echo $passwd; ?>">
<?php
if(isset($_POST['clearpass']) && $passwderr=='' && $orgnameerr=='' && $orgemailerr=='' && $orgphoneerr=='' && $orgname<>'Organisation Name'){ 
	$registering=true;
	$confirmstring = round($now * M_PI * mt_rand(1000,5000));
	$result=db_select("SELECT MAX(orgid) FROM organisations");
	$newid=1+$result[0]['MAX(orgid)'];
	$result=db_query("INSERT INTO organisations (orgid, orgname, orgphone, orgemail, orgcreated, confirmstring, passwd) 
	VALUES ($newid, '$orgname','$orgphone','$orgemail',(FROM_UNIXTIME($now)),$confirmstring, (SHA1('$passwd')))");
	if(!$result) { echo "Couldn't insert organisation to the database "; }
	echo "<p>Organisation registration complete!</p>";
	$registering='complete';
} else {
	if($orgnameerr){echo "<li>" . $orgnameerr . "</li>";}
	if($orgphoneerr){echo "<li>" . $orgphoneerr . "</li>";}
	if($orgemailerr){echo "<li>" . $orgemailerr . "</li>";}
	if($passwderr){echo "<li>" . $passwderr . "</li>";}
	echo "<br><input type='hidden' name='returning' value='true'>";
	echo "<button class='btn btn-default' type='submit'>Register</button>";
}  
?>
   <br>
  </div>
 </form>
<?php
if($registering=='complete') {
	$to=$orgemail;
	include "resources/orgemail.php";
	echo"<p>The email address supplied above will become the 'master' user and first manager of the organisation.</p>";
	echo"<p>We've sent you an email. To complete your individual registration, simply follow the link in your email.</p>";
}
if(isset($_SESSION['debug'])){
        if($_SESSION['debug']=='true'){
                echo "</p><br><br><p>_COOKIE: <br>";
                var_dump($_COOKIE);
                echo "</p><br><br><p>_POST: <br>";
                var_dump($_POST);
                echo "</p><br><br><p>_SESSION: <br>";
                var_dump($_SESSION);
                echo "</p><br><br><p>Everything Else: <br>";
		var_dump(get_defined_vars());
        }
}
?>
</div>