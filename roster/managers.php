<?php
session_start();
$now=time();
if(isset($_SESSION['debug'])){$debug=$_SESSION['debug'];}
if(isset($_SESSION['timeout']) && $now > $_SESSION['timeout']) {
	session_unset();
	session_destroy();
	session_start();
}
$_SESSION['timeout']=$now+1800;
$auth=$_SESSION['auth'];
if($auth<>'true'){ // *** redirect on unauth to index page
	header('Location: login.php?action=employee');
}
include "header.php";
$msg=$confirmstring=$selected=$addshifts=$namefirsterr=$namelasterr=$useremailerr=$userphoneerr='';
$listoptions=array("Any","Weekdays","Weekends","Midweek");
$weekdaylist=array('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday');
$hourlist=array('00','01','02','03','04','05','06','07','08','09','10','11','12','13','14','15','16','17','18','19','20','21','22','23','24','25');
$minlist=array('00','05','10','15','20','25','30','35','40','45','50','55');
$optionlist=array_merge($weekdaylist,$listoptions);
$operatoruseremail=$_SESSION['useremail'];
$operatornamefirst=$_SESSION['namefirst'];
$operatornamelast=$_SESSION['namelast'];
$count=$_SESSION['count'];
$manager=$_SESSION['manager'];
$orgid=$_SESSION['orgid'];
$lastaccess=$_SESSION['lastaccess'];
$staffid=$_SESSION['staffid'];
$hourlyrate=$_SESSION['hourlyrate'];
$starthour='06';
$endhour='20';
$skip='false';
if(isset($_POST['submit'])){$submit=$_POST['submit'];}else{$submit='';}
if(isset($_POST['weekchoice'])){$weekchoice=$_POST['weekchoice'];}else{$weekchoice='';}
if(isset($_POST['starthour'])){$starthour=$_POST['starthour'];}
if(isset($_POST['startminutes'])){$startminutes=$_POST['startminutes'];}else{$startminutes='';}
if(isset($_POST['endhour'])){$endhour=$_POST['endhour'];}else{$endhour='20';}
if(isset($_POST['endminutes'])){$endminutes=$_POST['endminutes'];}else{$endminutes='';}
if(isset($_POST['weekchoice'])){$weekchoice=$_POST['weekchoice'];}else{$weekchoice='';}
// *** Section for AddStaff form
if(isset($_POST['addstaff'])){$addstaff=true;}else{$addstaff=false;}
if(isset($_POST['namefirst'])){$namefirst=$_POST['namefirst'];}else{$namefirst='';}
if(isset($_POST['namelast'])){$namelast=$_POST['namelast'];}else{$namelast='';}
if(isset($_POST['useremail'])){$useremail=$_POST['useremail'];}else{$useremail='';}
if(isset($_POST['userphone'])){$userphone=$_POST['userphone'];}else{$userphone='';}
// ******** Test for input data and submit to database ********
if($submit=='Submit'){;$addshifts='true';}else{$addshifts='false';}
if((isset($_POST['delete']))or($_POST['submit']=='Adjust')){
	$remove=$_POST['shiftindex'];
	//$result=db_query("DELETE FROM shifts WHERE shiftindex='$remove'");
	//if(!$result){$msg.="Couldn't find that shift to remove. Perhaps the user or another manager removed it since you last refreshed this page.";}
}
if($addstaff==true){
// *** Section to add unregistered staff
	if(empty($_POST['namefirst'])){
		$namefirsterr='First Name is required<br>'; 
	}else{
		if(!preg_match("/^[a-zA-Z0-9'\-\/, ]*$/",$namefirst)){
			$namefirsterr='Only letters, white space and punctuation allowed<br>';
		}
	}
	if(empty($_POST['namelast'])){ $namelasterr='Last name is required<br>'; }else{
		if(!preg_match("/^[a-zA-Z0-9'\-\/, ]*$/",$namelast)){
			$namelasterr='Only letters, white space and punctuation allowed<br>';
		}
	}
	$formemail=test_input($_POST['useremail']);
	if(!filter_var($useremail, FILTER_VALIDATE_EMAIL)){$useremailerr='Invalid username/email format<br>';}
	if(empty($useremail)){$useremailerr="An email address is recommended for user registration.<br>";}
	if(empty($userphone)){
		$userphoneerr="You must use a phone number for registration.";
	}else{
		if(strlen($userphone)<10){
			$userphoneerr='Phone Number not complete';
		}
	}
	if(!preg_match("/^[a-zA-Z0-9+()\- ]*$/",$userphone)){
		$userphoneerr='Only numbers, white space and phone punctuation allowed in your phone number';
	}
	if($namefirsterr=='' && $namelasterr=='' && $userphoneerr==''){
		$result=db_select("SELECT MAX(staffid) FROM staff");
		if(!$result){$msg.="<br>Problem with determining the next available userid<br>";}
		$newid=$result[0]['MAX(staffid)']+1;
		$result=db_query("INSERT INTO staff (staffid, orgid, namefirst, namelast, useremail, userphone, manager) 
		VALUES ('$newid', '$orgid','$namefirst','$namelast','$useremail','$userphone','0')");
		if(!$result){$msg.="<br>Problem with inserting that user to database<br>";}
	}
}
if($addshifts=='true'){
	if($weekchoice=="Any"){
		$days=array("01","02","03","04","05","06","07");
	}elseif($weekchoice=="Weekdays"){
		$days=array("01","02","03","04","05");
	}elseif($weekchoice=="Weekends"){
		$days=array("06","07");
	}elseif($weekchoice=="Midweek"){
		$days=array("02","03","04");
	}else{
		$days=array(base_day($weekchoice));
	}
	$shiftstarttime="2001-01-" . $days[0] . " " . $starthour . ":" . $startminutes . ":00";
	$shiftendtime="2001-01-" . $days[0] . " " . $endhour . ":" . $endminutes . ":00";
	$length=abs(strtotime($shiftendtime)-strtotime($shiftstarttime));
	if((abs($length))<7200){$skip="";$skip.='<p>Shift is less than 2 hours<br>';}
	if($length>32400){$skip='';$skip.='<p>Shift is longer than 9 hours<br>';}
	if(strtotime($shiftstarttime)>strtotime($shiftendtime)){$skip="";$skip.='<p>Start time is before finish time<br>';}
	$inputcount=count($days);
	for($x=0;$x<$inputcount;$x++){
		$shiftstarttime="2001-01-" . $days[$x] . " " . $starthour . ":" . $startminutes . ":00";
		$shiftendtime="2001-01-" . $days[$x] . " " . $endhour . ":" . $endminutes . ":00";
		$result=db_select("SELECT shiftstarttime, shiftendtime from shifts WHERE staffid='$staffid'");
		if(!$result && $skip=='false'){
			$skip='false';
		}else{
			$y=0;
			foreach($result as $value){
				$y++;
				$checkstarttime=$value['shiftstarttime'];
				$checkendtime=$value['shiftendtime'];
				if($checkstarttime==$shiftstarttime && $checkendtime==$shiftendtime){ 
					$skip='<p>That entry was already in the database.<br>';
				}
			}	
		}
	if($skip=='false'){
		$result=db_query("INSERT INTO shifts (shiftstarttime,shiftendtime,staffid) VALUES ('$shiftstarttime','$shiftendtime','$staffid')");
		}
	}
}
// *** Display First section
$msg1="<a href='index.php'>Home</a>\n";
$msg1.="<a href='roster.php'>Roster</a>\n";
$msg1.="<a href='shifts.php'>Staff</a>\n";
if($manager>=1){$msg1.="<a href='managers.php'>Manage</a>\n";}
$msg1.="<a href='login.php?action=logout'>Logout</a>\n";
$msg1.="<p>Welcome, " . $operatornamefirst . ". You are logged in.</p><br>\n";
$msg1.="<h3>Staff</h3>\n";
$msg1.="<br>\n";
echo $msg1;
echo $msg;
$msg='';
$msg="<div class='container-fluid'>
<table class='table-bordered table-responsive' style='width:100%;'>
";
// ******** Query database for staff data, test and display ********
$result=db_select("SELECT staffid, useremail, namefirst, namelast, description, hourlyrate, manager, userphone FROM 
staff WHERE orgid='$orgid' ORDER BY manager DESC, namefirst ASC, namelast ASC");
if(!$result){$msg.= " <p>Couldn't find any associated staff or employees to display...</p>\n<br><br>"; }else{
	foreach($result as $value){
		$lustaffid=$value['staffid'];
		$luuseremail=$value['useremail'];
		$lunamefirst=$value['namefirst'];
		$lunamelast=$value['namelast'];
		$ludescription=$value['description'];
		$luhourlyrate=$value['hourlyrate'];
		$lumanager=$value['manager'];
		$luuserphone=$value['userphone'];
		$msg.="<tr><td>";
		$msg.= $lunamefirst . "</td><td>" . $lunamelast . "</td><td>" . $luuseremail . "</td><td>";
		if($lumanager==0){$msg.="Staff";} elseif ($lumanager==1){$msg.="Manager";}elseif($lumanager==2){$msg.="Account Owner";}
		$msg.="</td><td>" . $ludescription . "</td>";
		$msg.="<td>$" . $luhourlyrate . "</td><td>" . $luuserphone . "</td>";
		$msg.="<td><form method='post' action='managers.php'>
			<input type='hidden' name='modify' value='"; $msg.=$staffid . "'>
			<input type='submit' value='Modify' class='form-control'>
			</form></td></tr>\n";
	}
	$msg.="</table><br><p>Add a new staff member</p><br></table></div>\n<form role='form' method='post' action='" . htmlspecialchars($_SERVER["PHP_SELF"]);
	$msg.="?action=addstaff'>
		<div class='form-group'>" . $namefirsterr . "
			<label for='namefirst'>First Name:</label>
			<input type='text' class='form-control' id='namefirst' name='namefirst' placeholder='First Name' value='" . $namefirst . "'>
		</div>
		<div class='form-group'>" . $namelasterr . "
			<label for='namelast'>Last Name:</label>
			<input type='text' class='form-control' id='namelast' name='namelast' placeholder='Last Name' value='" . $namelast . "'>
		</div>
		<div class='form-group'>" . $useremailerr . "
			<label for='useremail'>Email:</label>
			<input type='email' class='form-control' id='useremail' name='useremail' placeholder='Enter email' value='" . $useremail . "'>
		</div>
		<div class='form-group'>" . $userphoneerr . "
			<label for='userphone'>Phone:</label>
			<input type='text' class='form-control' id='userphone' name='userphone' placeholder='Phone Number' value='" . $userphone . "'>
		</div>		
			<input type='hidden' name='addstaff' value='addstaff'>
			<input type='submit' value='Submit' class='form-control'>
			</form>";
}
echo $msg;
$msg=" <br><h3>Set Availabilities and Non-Availabilities</h3>\n";
$msg.=" <br><p>To record a new roster preference, please fill out the form below. All fields are required :)</p><br>\n";
if($skip<>'false'){$msg.=$skip;}
echo $msg;
// ******* Display Existing user shift preferences ********
// ******* Using for loop to loop form html with adjustments into $msg. Display $msg on completion ********
$msg="<!-- ******** Preferences Form ******** -->
 <div class='container'>
	<form role='form' class='form-inline' method='post' action='";
$msg.= htmlspecialchars($_SERVER['PHP_SELF']) . "?action=submit'>\n";
$msg.="	 <div class='form-group'>
	 <label for='displaylist'>Day</label>
	 <select class='form-control' name='weekchoice' id='weekchoice' value=''>\n";
$count=count($optionlist);
for($y=0;$y<=$count-1;$y++){
	$option=$optionlist[$y];
	if($weekchoice==$option){$selected=' selected';}else{$selected='';}
	$msg.= "		<option value='" . $option . "'" . $selected . ">" . $option . "</option>\n";
}
$msg.="	 </select>
	 </div>
 	 <div class='form-group'>
	 <label for='start'>Start</label>
 	 <select class='form-control' id='starthour' name='starthour'>\n";
for($y=0;$y<=count($hourlist)-1;$y++){
	$option=$hourlist[$y];
 if($y==$starthour){$selected=' selected';}else{$selected='';}
 if($y==25){$option='Off';}
	$msg.= "		<option value='" . $option . "'" . $selected . ">" . $option . "</option>\n";
}
$msg.="	 </select>
	 <select class='form-control' name='startminutes' id='startminutes'>\n";
for($y=0;$y<=count($minlist)-1;$y++){
	$option=$minlist[$y];
	if($option==$startminutes){$selected=' selected';}else{$selected='';} 
	$msg.= "	 	<option value='" . $option . "'" . $selected . ">" . $option . "</option>\n";
}
$msg.="	 </select>
	 </div>
	 <div class='form-group'>
	 <label for='finish'>Finish</label>
	 <select class='form-control' name='endhour' id='endhour'>\n";
for($y=0;$y<=count($hourlist)-1;$y++){
	$option=$hourlist[$y];
	if($option==$endhour){$selected=' selected';}else{$selected='';}
	if($y==25){$option='Off';}
	$msg.= "	 <option value='" . $option . "'" . $selected . ">" . $option . "</option>\n";
}
$msg.="	 </select>
	 <select class='form-control' name='endminutes' id='endminutes'>\n";
for($y=0;$y<=count($minlist)-1;$y++){
	$option=$minlist[$y];
	if($option==$endminutes){$selected=' selected';}else{$selected='';}
	$msg.= "	 <option value='" . $option . "'" . $selected . ">" . $option . "</option>\n";
}
$msg.= "	 </select>
	 </div>
 <div class='form-group'>
 <input type='submit' name='submit' placeholder='Submit' value='Submit' class='form-control'><br>
	 </div>
		<input type='hidden' name='orgid' value='";
$msg.=$orgid ."'>
		<input type='hidden' name='manager' value='";
$msg.= $manager . "'>
		<br>
	 </form>
	 </div>
";
echo $msg
?>
		<br>
	</div>
</div>
<!-- ******** Debug Session ******** -->
<?php
if(isset($_SESSION['debug'])){
 if($_SESSION['debug']=='true'){
 echo "</p><br><br><p>_COOKIE: <br>";
 var_dump($_COOKIE);
 echo "</p><br><br><p>_POST: <br>";
 var_dump($_POST);
 echo "</p><br><br><p>_SESSION: <br>";
 var_dump($_SESSION);
 echo "</p><br><br><p>Everything else: <br>";
 var_dump(get_defined_vars());
 }
}
?>