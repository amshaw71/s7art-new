<?php
session_start();
$now=time();
if(isset($_SESSION['debug'])){$debug=$_SESSION['debug'];}
if (isset($_SESSION['timeout']) && $now > $_SESSION['timeout']) {
	session_unset();
	session_destroy();
	session_start();
}
$_SESSION['timeout'] = $now + 1800;
$auth=$_SESSION['auth'];
if($auth<>'true'){
	header('Location: index.php');
}
$_SESSION['timeout']=$now+1800;
include "header.php";
$manager='0';
$confirmstring='';
$listoptions=array("Any","Weekdays","Weekends","Midweek");
$weekdaylist=array('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday');
$hourlist=array('00','01','02','03','04','05','06','07','08','09','10','11','12','13','14','15','16','17','18','19','20','21','22','23','24','25');
$minlist=array('00','05','10','15','20','25','30','35','40','45','50','55');
$optionlist=array_merge($weekdaylist,$listoptions);
$useremail=$_SESSION['useremail'];
$namefirst=$_SESSION['namefirst'];
$namelast=$_SESSION['namelast'];
$count=$_SESSION['count'];
$manager=$_SESSION['manager'];
$orgid=$_SESSION['orgid'];
$lastaccess=$_SESSION['lastaccess'];
$staffid=$_SESSION['staffid'];
$hourlyrate=$_SESSION['hourlyrate'];
$selected='';
$starthour='06';
$endhour='20';
$skip='false';
$addshifts='';
if(isset($_POST['submit']) && ($_POST['submit']=='Submit')){;$add_shifts='true';}else{$addshifts='false';}
if(isset($_POST['orgid'])){$orgid=$_POST['orgid'];}else{$orgid='';}
if(isset($_POST['weekchoice'])){$weekchoice=$_POST['weekchoice'];}else{$weekchoice='';}
if(isset($_POST['starthour'])){$starthour=$_POST['starthour'];}
if(isset($_POST['startminutes'])){$startminutes=$_POST['startminutes'];}else{$startminutes='';}
if(isset($_POST['endhour'])){$endhour=$_POST['endhour'];}else{$endhour='20';}
if(isset($_POST['endminutes'])){$endminutes=$_POST['endminutes'];}else{$endminutes='';}
if(isset($_POST['weekchoice'])){$weekchoice=$_POST['weekchoice'];}
// ******** Test for input data and submit to database ********
if((isset($_POST['delete']))or($_POST['submit']=='Adjust')){
	$remove=$_POST['shiftindex'];
	$result=db_query("DELETE FROM availabilities WHERE shiftindex='$remove'");
	if(!$result){$msg.="Couldn't find that shift to remove. Perhaps a manager removed it since you last refreshed this page.";}
}
if($add_shifts=='true'){
	if($weekchoice=="Any"){$days=array("01","02","03","04","05","06","07");}
	elseif($weekchoice=="Weekdays"){$days=array("01","02","03","04","05");}
        elseif($weekchoice=="Weekends"){$days=array("06","07");}
        elseif($weekchoice=="Midweek"){$days=array("02","03","04");}
	else {$days=array(base_day($weekchoice));}
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
		$result=db_select("SELECT shiftstarttime, shiftendtime from availabilities WHERE staffid='$staffid'");
		if(!$result && $skip=='false'){
			$skip='false';
		} else {
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
		$result=db_query("INSERT INTO availabilities (shiftstarttime,shiftendtime,staffid) VALUES ('$shiftstarttime','$shiftendtime','$staffid')");
}	}	}
// ******** Query database for shift data and test and display ********
$result=db_select("SELECT shiftindex, shiftendtime, shiftstarttime FROM availabilities WHERE staffid='$staffid' ORDER BY shiftstarttime");
$msg.="</p>";
if(!$result){ $msg.= "   <p>Couldn't find any shift preferences to display...</p>\n<br><br>"; }else{
	foreach($result as $value){
		$time=$value['shiftstarttime'];
		$shiftstartday=strip_mysqlday($time);
		$shiftstarthour=strip_mysqlhour($time);
		$shiftstartmin=strip_mysqlmin($time); 
		$shiftid=$value['shiftindex'];
		$time=$value['shiftendtime'];
		$shiftendhour=strip_mysqlhour($time);
		$shiftendmin=strip_mysqlmin($time);
		$msg.="<!-- ******** Preferences Form ******** -->
		   <div class='container'>
		        <form role='form' class='form-inline' method='post' action='";
		$msg.= htmlspecialchars($_SERVER['PHP_SELF']) . "?action=submit'>\n";
		$msg.="   <div class='form-group'>
		           <label for='displaylist'>Day</label>
		           <select class='form-control' name='weekchoice' id='weekchoice' value=''>\n";
		$count=count($optionlist);
// Day Option
		for($y=0;$y<=$count-1;$y++){
		        $option=$optionlist[$y];
		        if($option==$shiftstartday){$selected=' selected';} else {$selected='';}
		        $msg.= "                <option value='" .  $option . "'" . $selected . ">" . $option . "</option>\n";
		}
		$msg.= " <p>" . $shiftstartday . "</p>";
		$msg.="    </select>
		          </div>
		          <div class='form-group'>
		           <label for='start'>Start</label>
		           <select class='form-control' id='starthour' name='starthour'>\n";
// Start Hour Option
		for($y=0;$y<=count($hourlist)-1;$y++){
		        $option=$hourlist[$y];
		        if($option==$shiftstarthour){$selected=' selected';} else {$selected='';}
		        if($y==25){$option='Off';}
		        $msg.= "                <option value='" .  $option . "'" . $selected . ">" . $option . "</option>\n";}
		$msg.="    </select>
		           <select class='form-control' name='startminutes' id='startminutes'>\n";
// Start Minutes Option
		for($y=0;$y<=count($minlist)-1;$y++){
		        $option=$minlist[$y];
		        if($option==$shiftstartmin){$selected=' selected';} else {$selected='';}
		        $msg.= "                <option value='" . $option . "'" . $selected . ">" . $option . "</option>\n";}
		$msg.="    </select>
		         </div>
		         <div class='form-group'>
		          <label for='finish'>Finish</label>
		          <select class='form-control' name='endhour' id='endhour'>\n";
// End Hours Option
		for($y=0;$y<=count($hourlist)-1;$y++){
		        $option=$hourlist[$y];
		        if($option==$shiftendhour){$selected=' selected';} else {$selected='';}
		        if($y==25){$option='Off';}
		        $msg.= "           <option value='" .  $option . "'" . $selected . ">" . $option . "</option>\n";
		}
		$msg.="   </select>
		          <select class='form-control' name='endminutes' id='endminutes'>\n";
// End Minutes Option
		for($y=0;$y<=count($minlist)-1;$y++){
		        $option=$minlist[$y];
		        if($option==$shiftendmin){$selected=' selected';} else {$selected='';}
		        $msg.= "           <option value='" . $option . "'" . $selected . ">" . $option . "</option>\n";}
		$msg.= "          </select>
		         </div>
		     <div class='form-group'>
		      <input type='submit' name='submit' value='Adjust' class='form-control'>
		      <input type='submit' name='delete' value='Delete' class='form-control'>
				<br>
		           </div>
		                <input type='hidden' name='orgid' value='";$msg.=$orgid ."'>
		                <input type='hidden' name='shiftindex' value='";$msg.=$value['shiftindex'] ."'>
		                <input type='hidden' name='manager' value='";$msg.= $manager . "'>
		                <br>
		          </form>
		         </div>
		";
	}
}
$msg1.="<a href='index.php'>Home</a>\n";
$msg1.="<a href='roster.php'>Roster</a>\n";
$msg1.="<a href='shifts.php'>Staff</a>\n";
if($manager>=1){$msg1.="<a href='managers.php'>Manage</a>\n";}
$msg1.="<a href='login.php?action=logout'>Logout</a>\n";
$msg1.="<p>Welcome, " . $namefirst . ". You are logged in.</p><br>\n";
$msg1.="<h3>Current Regular Shifts</h3>\n";
$msg1.="<p></p>\n";
echo $msg1;
echo $msg;
$msg='';
$msg.="   <br><h3>Set Availabilities and Non-Availabilities</h3>\n";
$msg.="   <br><p>To record a new roster preference, please fill out the form below. All fields are required :)</p><br>\n";
if($skip<>'false'){$msg.=$skip;}
echo $msg;
// ******* Display Existing user shift preferences ********
// ******* Using for loop to loop form html with adjustments into $msg. Display $msg on completion  ********
$msg='';
echo $msg;
$msg="<!-- ******** Preferences Form ******** -->
   <div class='container'>
	<form role='form' class='form-inline' method='post' action='";
$msg.= htmlspecialchars($_SERVER['PHP_SELF']) . "?action=submit'>\n";
$msg.="	  <div class='form-group'>
	   <label for='displaylist'>Day</label>
	   <select class='form-control' name='weekchoice' id='weekchoice' value=''>\n";
$count=count($optionlist);
for($y=0;$y<=$count-1;$y++){
	$option=$optionlist[$y];
	if($weekchoice==$option){$selected=' selected';} else {$selected='';}
	$msg.= "		<option value='" .  $option . "'" . $selected . ">" . $option . "</option>\n";
}
$msg.="	   </select>
	  </div>
 	  <div class='form-group'>
	   <label for='start'>Start</label>
 	   <select class='form-control' id='starthour' name='starthour'>\n";
for($y=0;$y<=count($hourlist)-1;$y++){
	$option=$hourlist[$y];
        if($y==$starthour){$selected=' selected';} else {$selected='';}
        if($y==25){$option='Off';}
	$msg.= "		<option value='" .  $option . "'" . $selected . ">" . $option . "</option>\n";
}
$msg.="	   </select>
	   <select class='form-control' name='startminutes' id='startminutes'>\n";
for($y=0;$y<=count($minlist)-1;$y++){
	$option=$minlist[$y];
	if($option==$startminutes){$selected=' selected';} else {$selected='';} 
	$msg.= "	 	<option value='" . $option . "'" . $selected . ">" . $option . "</option>\n";
}
$msg.="	   </select>
	 </div>
	 <div class='form-group'>
	  <label for='finish'>Finish</label>
	  <select class='form-control' name='endhour' id='endhour'>\n";
for($y=0;$y<=count($hourlist)-1;$y++){
	$option=$hourlist[$y];
	if($option==$endhour){$selected=' selected';} else {$selected='';}
	if($y==25){$option='Off';}
	$msg.= "	   <option value='" .  $option . "'" . $selected . ">" . $option . "</option>\n";
}
$msg.="	  </select>
	  <select class='form-control' name='endminutes' id='endminutes'>\n";
for($y=0;$y<=count($minlist)-1;$y++){
	$option=$minlist[$y];
	if($option==$endminutes){$selected=' selected';} else {$selected='';}
	$msg.= "	   <option value='" . $option . "'" . $selected . ">" . $option . "</option>\n";
}
$msg.= "	  </select>
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