<?php
session_start ();
$now = time();
if (isset($_SESSION['timeout']) && $now > $_SESSION['timeout']) {
	session_unset();
	session_destroy();
	session_start();
}
$_SESSION['timeout'] = $now + 1800;
$id=$_SESSION['id'];
$auth=$_SESSION['auth'];
$asc='ASC';
$count=$_SESSION['count'];
$condition='manage'; //Starting Condition is 'manage'
$incrid=0;
$decrid=0;
if(isset($_POST['recommend'])){$recommend=$_POST['recommend'];}
$incruserid=0;
$decruserid=0;
$sw2='unset';

require_once 'resources/opendb.php';
require_once 'header.php';
?>

<!-- ******** Start of Second Row ******** -->

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

<!-- ******** End of Second Row ******** -->

<div class="row container-fluid">
	<div class="col-xs-12 col-sm-10 col-sm-offset-1 col-md-10 col-md-offset-1 col-lg-8 col-lg-offset-2 text-justify">
		<h3>Link and User Management</h3>
		<p>Use this page to manage your links, profile and password</p>
		<p><a href="?reorder='alpha'" target="_self">Alphabetical Reorder</a> - 
		<a href="?reorder='numeric'" target="_self">Numeric Reorder</a> -
		<a href="index.php" target="_self">S7art Home</a></p>
	</div>
</div>
<div class="row container-fluid">
	<div class="col-xs-12 col-sm-10 col-sm-offset-1 col-md-10 col-md-offset-1 col-lg-8 col-lg-offset-2 text-justify">
		<p>
<?php
// ******** Condition Handling ********
// ******** Testing ********
//var_dump(get_defined_vars());
if(isset($_REQUEST['delete']) or isset($_POST['delete'])) {$condition='remove';}
if(isset($_POST['add'])){$condition='add';}
if(isset($_REQUEST['incr']) or isset($_POST['incr'])) {$condition='increment';}
if(isset($_REQUEST['decr']) or isset($_POST['decr'])) {$condition='decrement';}
if(isset($_REQUEST['reorder'])) {$condition='reorder';$reorder=$_REQUEST['reorder'];}
if(isset($_REQUEST['modifyform'])) {$condition='modifyform';}
if(isset($_POST['modify'])) {$condition='modify';}
if(isset($_POST['usersuggest'])) {$condition='usersuggest';}
if($_SESSION['auth']<>true){$condition='unauth';}
if(isset($_POST['recommend'])) {$condition='recommend';}


switch ($condition) {
	case 'unauth':
		echo "<p>You aren't logged in....</p>";
		$sw2='unauth';
	break;


	case 'remove':
		if(isset($_POST['delete'])) {$remove=$_POST['delete'];}
		if(isset($_REQUEST['delete'])) {$remove=$_REQUEST['delete'];}
		$result=db_query("UPDATE links SET active=0 WHERE userid='$id' AND userlinkid='$remove'");
		echo "<p>Removed Link number: " . $remove . "</p>";
		$neglinkid=$remove*-1;
		$result=db_query("UPDATE links SET userlinkid=$neglinkid WHERE userid='$id' AND userlinkid='$remove'");
	break;


	case 'add':
// EOF fields must be the first character on the line, structure a little compromised here, i.e. :
//echo <<<EOF
//<div class="row container-fluid">
//<div class="col-xs-12 col-sm-8 col-sm-offset-2 col-md-6 col-md-offset-3">
//<p>
//EOF;
		$createdate=$moddatetime;
		$result = db_select("SELECT namefirst, namelast FROM user WHERE userid='$id'");
		$namefirst=$result[0]['namefirst'];
		$namelast=$result[0]['namelast'];
		echo "<br>Welcome, $namefirst $namelast, your S7artID is: " . $id;
		echo "<br>Your new link text is: " . htmlspecialchars($_POST['linktext']) . PHP_EOL;
		echo "<br>Your new link URL is: " . htmlspecialchars($_POST['linkdata']) . PHP_EOL;
		echo "<br>Your new description will be: " . htmlspecialchars($_POST['description']) . PHP_EOL;
		// ******** Sanitise DB Input ********
		$linktext = db_quote($_POST['linktext']);
		$linkdata = db_quote($_POST['linkdata']);
		$description = db_quote($_POST['description']);
		// ******** Determine next available userlinkid ******** 
		$result = db_select("SELECT MAX(userlinkid) FROM links WHERE userid=$id");
		$maxuserlinkid=$result[0]['MAX(userlinkid)'];
		$newuserlinkid=$maxuserlinkid+1;
		$result = db_select("SELECT MAX(linkid) FROM links");
		$maxlinkid=$result[0]['MAX(linkid)'];
		$newlinkid=$maxlinkid+1;
		// ******** Insert newest link into database ********
		$result= db_query("INSERT INTO links (linkid, userid, linktext, createdatetime, moddatetime, description, active, linkdata, userlinkid)
		VALUES ($newlinkid, $id, $linktext, (FROM_UNIXTIME($createdate)), (FROM_UNIXTIME($moddatetime)),$description, 1, $linkdata, $newuserlinkid)");
		if($result===false) {
			$error = db_error();
			echo '\N Error Detected: ' . $error;
		} else {
			echo "<h3>Your New Link is Number: " . $newuserlinkid . "</h3>" . PHP_EOL;
		}
	break;

	case 'increment':
		if ( isset ($_REQUEST['incr'])) {
			$incr = $_REQUEST['incr'];
		} else {
			$incr=0;
		}
		$incrid=0;
		$decrid=0;
		$incruserid=0;
		$decruserid=0;
		if ($incr>1) {
			$result = db_select("SELECT linkid, userlinkid FROM links WHERE userid=$id AND userlinkid=$incr");
			$incrid=$result[0]['linkid'];
			$incruserid=$result[0]['userlinkid'];
			$result = db_select("SELECT linkid, userlinkid FROM links WHERE userid=$id AND userlinkid=($incr-1)");
			$decrid=$result[0]['linkid'];
			$decruserid=$result[0]['userlinkid'];
			$incruserid = $incruserid-1;
			$decruserid = $decruserid+1;
			$result = db_query("UPDATE links SET userlinkid = $incruserid, moddatetime = (FROM_UNIXTIME($moddatetime)) WHERE linkid = $incrid");
			$result = db_query("UPDATE links SET userlinkid = $decruserid, moddatetime = (FROM_UNIXTIME($moddatetime)) WHERE linkid = $decrid");
		}
	break;

	case 'decrement':
	if ( isset ($_REQUEST['decr'])) {
		$decr = $_REQUEST['decr'];
	} else {
		$decr=0;
	}
	$incrid=0;
	$decrid=0;
	$incruserid=0;
	$decruserid=0;
	if ($decr>0) {
		$result = db_select("SELECT linkid, userlinkid FROM links WHERE userid=$id AND userlinkid=$decr");
		$decrid=$result[0]['linkid'];
		$decruserid=$result[0]['userlinkid'];
		$result = db_select("SELECT linkid, userlinkid FROM links WHERE userid=$id AND userlinkid=($decr+1)");
		$incrid=$result[0]['linkid'];
		$incruserid=$result[0]['userlinkid'];
		$incruserid = $incruserid-1;
		$decruserid = $decruserid+1;
		$result = db_query("UPDATE links SET userlinkid = $incruserid, moddatetime = (FROM_UNIXTIME($moddatetime)) WHERE linkid = $incrid");
		$result = db_query("UPDATE links SET userlinkid = $decruserid, moddatetime = (FROM_UNIXTIME($moddatetime)) WHERE linkid = $decrid");
	}
	break;


	case 'reorder':
	if($_SESSION['asc']=='ASC'){
		$_SESSION['asc']='DESC';
		$asc='DESC';
	} else {
		$_SESSION['asc']='ASC';
	}
	if($reorder=="'numeric'") {
		echo " Chose Numeric reorder: ";
		$result = db_select("SELECT COUNT(userlinkid) AS linkstotal FROM links WHERE userid=$id AND active=1");
		echo $result[0]['linkstotal'] . " Links re-ordered...";
		$total=$result[0]['linkstotal'];
		$result = db_select("SELECT linkid, userlinkid FROM links WHERE userid=$id AND active=1 ORDER BY userlinkid $asc");
		echo "<br>"; 
		for ($x=0 ; $x < $total ; $x++) {
			$new=$result[$x]['linkid'];
			db_query("UPDATE links SET userlinkid=$x+1 WHERE linkid=$new AND userid=$id");
		}
	}
	if ($reorder=="'alpha'") {
		echo " Chose Alphabetical Re-order. "; 
		$result = db_select("SELECT COUNT(userlinkid) AS linkstotal FROM links WHERE userid=$id AND active=1");
		echo $result[0]['linkstotal'] . " Links re-ordered...</p>";
		$total=$result[0]['linkstotal'];
		$result = db_select("SELECT linkid, userlinkid, linktext, description, linkdata FROM links WHERE userid=$id AND active=1 ORDER BY linktext $asc");
		for ($x=0 ; $x < $total ; $x++) {
			$new=$result[$x]['linkid'];
			db_query("UPDATE links SET userlinkid=$x+1 WHERE linkid=$new AND userid=$id");
		}
	} 
	break; 

	
	case 'modify':
		echo " Updating... ";
		$id=$_SESSION['id'];
		$userlinkid=$_POST['userlinkid'];
		$linktext= db_quote($_POST['linktext']);
		$linkdata= db_quote($_POST['linkdata']);
		$description= db_quote($_POST['description']);
		$moddatetime=time();
		$result=db_query("UPDATE links SET linktext=$linktext, linkdata=$linkdata, description=$description, moddatetime=(FROM_UNIXTIME($moddatetime)) 
		WHERE userid='$id' AND userlinkid='$userlinkid'");
		if (!$result) { 
			echo " Couldn't update link $userlinkid...."; 
		} else {
			echo " Modified Link: " . $_POST['userlinkid']; 
		}
	break;

	
	case 'modifyform':
		$userlinkid=db_quote($_REQUEST['modifyform']);
		$id=$_SESSION['id'];
		$result=db_select("SELECT linktext, linkdata, description FROM links WHERE userid='$id' AND userlinkid=$userlinkid");
		if(!$result) { echo "No records"; }
		$linktext=$result[0]['linktext'];
		$linkdata=$result[0]['linkdata'];
		$description=$result[0]['description'];
		echo "<p>Modifying Link: " . $userlinkid . "<br>";
echo <<<EOF
<div>
<table class='table' style='width:100%;'>
<tbody>
<tr>
<td>$userlinkid</td><td>$linktext</td><td>$linkdata</td><td>$description</td><td><a href='?delete=$userlinkid'>Delete</a></td>
</tr>
</div>
EOF;
echo PHP_EOL;
echo <<<EOF
<form role='form' action='' method='post' target='_top'>
<div class='input-group'>
<table class='table'>
<tbody>
<td>
<div>
<label for='linktext'>Link Text</label>
<input class='form-control' type='text' name='linktext' placeholder=$linktext value='$linktext'>
</div>
</td>
<td>
<div> 
<label for='linkdata'>Link Data (URL)</label>
<input class='form-control' type='text' name='linkdata' placeholder='$linkdata' value='$linkdata'>
</div>
</td>
<td>
<div>
<label for='description'>Your Description</label>
<input class='form-control' type='text' name='description' placeholder='$description' value='$description'>
<input type='hidden' name='userlinkid' value=$userlinkid>
<input type='hidden' name='modify' value=$userlinkid>
</div>
<div>
<span class='input-group-btn'><button class='btn btn-default' type='submit'>Modify</button></span>
</td></tr></table>
</div>
</div>
</form>;
EOF;
	break;

	case 'usersuggest':
		echo "<h2>User Suggestion Received</h2>";
		$createdate=$moddatetime;
		$result = db_select("SELECT namefirst, namelast FROM user WHERE userid='$id'");
		if(!$result){ echo "Unable to update your suggestion, please try again."; }
		$namefirst=$result[0]['namefirst'];
		$namelast=$result[0]['namelast'];
		echo "<p><br>Welcome, $namefirst $namelast.";
		echo "<br>Your link text is: " . htmlspecialchars($_POST['linktext']) . PHP_EOL;
		echo "<br>Your link URL is: " . htmlspecialchars($_POST['linkdata']) . PHP_EOL;
		echo "<br>Your description will be: " . htmlspecialchars($_POST['description']) . PHP_EOL;
		// ******** Sanitise DB Input ********
		$linktext = db_quote($_POST['linktext']);
		$linkdata = db_quote($_POST['linkdata']);
		$description = db_quote($_POST['description']);
		// ******** Determine next available userlinkid ********
		$result = db_select("SELECT MAX(userlinkid) FROM links WHERE userid=0");
		$maxlinkid=$result[0]['MAX(userlinkid)'];
		$newlinkid=$maxlinkid+1;
		// ******** Insert newest link into database ********
		$result= db_query("INSERT INTO links (userid, linktext, createdatetime, moddatetime, description, active, linkdata, userlinkid)
		VALUES (0, $linktext, (FROM_UNIXTIME($createdate)), (FROM_UNIXTIME($moddatetime)),$description, 2, $linkdata, $newlinkid)");
		echo "<h3>Your New Link has been added to the homepage and will be visible when you re-load.</p>";
		echo "<p>Your suggestion is Number: " . $newlinkid . "</p>" . PHP_EOL;

	break;


	case 'recommend':
		$to=test_input($_POST["email"]);
		if(!filter_var($to, FILTER_VALIDATE_EMAIL)) {
			$emailErr = "Invalid email format";
			echo "Invalid email format. " . $to . " doesn't appear to be an email address :/";
		} else {
			$result=db_select("SELECT * FROM referrals WHERE referredemail='$to'");
			$total=count($result);
			// echo $total;
			if ($total==0) {
				echo $to . " will be sent an email...";
				$send=true;
			}
			if (($total>=1) and ($total<=3)) {
				echo $to . " has been emailed a total of " . $total . " times.";
				$send=true;
			}
			if ($total>3) {
				echo $to . " has been emailed too many times.";
				$send=false;
			}
			if ($send==true) {
				require 'resources/recommend.php';
				echo "Now sending email to: " . $to . "<br>";
				$result=db_query("INSERT INTO referrals (userid, referredemail, referdatetime, referralip, referralproxy) VALUES ($id, '$to', (FROM_UNIXTIME($now)),'$ipaddress', '$pipaddress')");
			if (!$result) { 
				echo "Insert referral query failed"; 
			} else {
			echo "Inserted your referral to the database";
			// More work required here for recording the referral
			}
		}
	}
	break;

	
	default:
	
	// end of switch 1
	}
	

	switch($sw2){
	case 'unauth':
	break;


	default:
	// ******** Display all links and link management options ********

	echo "<div>";
	$result=db_select("SELECT * from links WHERE userid=$id AND active=1 ORDER BY userlinkid ASC");
	$total=count($result);
	echo "<br><div class='container-fluid'>" . PHP_EOL . "<table class='table' style='width:100%;'>" . PHP_EOL . "<tr>" . PHP_EOL;
	for($x=0; $x < $total; $x++) {
	// echo "<td>" . $result[$x]['linkid'] . "</td>";
	echo "<td>" . $result[$x]['userlinkid'] . "</td><td><a href='" . $result[$x]['linkdata'] ."' target='_blank'>" . $result[$x]['linktext'];
	echo "</a></td><td>" . $result[$x]['description'] . "</td><td><a href='?modifyform=" . $result[$x]['userlinkid'] . "'>Modify</a>" . PHP_EOL;
	echo "</td><td><a href='?delete=" . $result[$x]['userlinkid'] . "'>Delete</a>" . PHP_EOL;
	echo "</td><td><a href='?incr=" . $result[$x]['userlinkid'] . "' target='_self'>&uarr;" . "</a></td><td><a href='?decr=" . $result[$x]['userlinkid'];
	echo "' target='_self'>&darr;" . "</a></td></tr>" . PHP_EOL . "<tr>" . PHP_EOL;
	}
	echo "</table></div>";
	// ******** Increment Counter and Store to session (Common except for unauth visitors) ********
	$count++;
	$_SESSION['id']=$id;
	$_SESSION['auth']=$auth;
	$_SESSION['count']=$count;
	
	break;
	}

	echo "</div></div></div>";


	if ($id==4) {
	echo "<div class='row container-fluid'>";
	echo " <div class='col-xs-12 col-sm-10 col-sm-offset-1 col-md-10 col-md-offset-1 col-lg-8 col-lg-offset-2 text-justify'>";
	echo " <p>";
	echo "\$asc: " . $asc . "<br />";
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
	echo "<br>";
	print_r($_POST);
	echo "</p></div></div></div>";
	}

	?>
