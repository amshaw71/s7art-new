<?php
session_start();
$now = time();
if (isset($_SESSION['timeout']) && $now > $_SESSION['timeout']) {
    session_unset();
    session_destroy();
    session_start();
}
$_SESSION['timeout'] = $now + 1800;

require_once 'resources/opendb.php';
$id=0;
$auth=0;
$usernameerr=$passwderr=$namefirsterr=$namelasterr=$linktexterr=$linkdataerr=$decriptionerr='';
$username=$passwd=$namefirst=$namelast=$linktext=$linkdata=$description='';
$pipaddress='';
if($_SERVER['REQUEST_METHOD']=='POST'){
 if(empty($_POST['username'])){
  $nameErr = 'A Username is required';
 } else {
  $username=test_input($_POST['username']);
  if(!filter_var($username, FILTER_VALIDATE_EMAIL)){
   $usernameerr='Invalid username/email format';
  }
 }
 if(empty($_POST['passwd'])){
  $passwderr='Password is required';
 } else {
  $password=$_POST['passwd'];
  if(strlen($passwd)<6) {
   $passwderr="Minimum 6 Characters required";
  }
 }
 if(empty($_POST['namefirst'])){
  $namefirsterr = 'A name is required';
 } else {
  $namefirst = test_input($_POST['namefirst']);
  if(!preg_match("/^[a-zA-Z ]*$/",$namefirst)){
   $namefirsterr = 'Only letters and white space allowed';
  }
 } 
 if(empty($_POST['namelast'])){
  $namelasterr = 'A name is required';
 } else {
  $namelast = test_input($_POST['namelast']);
  if(!preg_match("/^[a-zA-Z ]*$/",$namelast)){
   $namelasterr = 'Only letters and white space allowed';
  }
 }
 if(empty($_POST['linktext'])){
  $linktext='';
 } else {
  $linktext=test_input($_POST['linktext']);
  if(!preg_match("/^[a-zA-Z0-9.:, ]*$/",$linktext)){
   $namelasterr = 'Only letters, white space and punctuation allowed';
  }
 }
 if(empty($_POST['linkdata'])){
  $linkdata='';
 } else {
  $linkdata=test_input($_POST['linkdata']);
  if(!preg_match("/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i",$linkdata)) {
   $linkdataerr = "Invalid URL";
  }
 }
 if (empty($_POST['description'])) {
  $description='';
 } else {
  $description=test_input($_POST['description']);
 }
}

// ******** Condition Handling ********
$condition='newrequest';
if (isset ($_COOKIE['id'])) {
 $cookieid= $_COOKIE['id'];
 $condition = "cookie";
} else { 
 $cookieid='';
}
if (isset( $_SESSION['id'])) {
 $sessionid = $_SESSION['id'];
 $condition = "session";
} else {
 $sessionid='';
}
if (isset ($_REQUEST['id'])) {
 $requestid = $_REQUEST['id'];
 $condition = "request";
} else {
 $requestid='';
}
if (isset ($_REQUEST['newid'])) {
 $requestid = $_REQUEST['newid'];
 $condition = "newrequest";
} else {
 $newid='';
}
switch ($condition) {
 case "cookie":
  $id = $_COOKIE['id'];
 break;
  case "session":
  $id = $_SESSION['id'];
 break;
  case "request":
  $id = $_REQUEST['id'];
 break;
  case "newrequest":
  if(isset($_REQUEST['newid'])){$id = $_REQUEST['newid'];}
 break;
 default:
  $id = 0;
  $condition = "unset";
}
$cookie_name="id";
setcookie($cookie_name,$id,time()+31536000,"/");
$session_name="id";
$_SESSION[$session_name]=$id;
if (isset ($_SESSION['auth']))  { $auth=$_SESSION['auth']; } // existing status in session is honoured
if (isset ($_REQUEST['auth']))  { $auth=false; }             // unless they request to logout  by request
if (isset ($_REQUEST['id']))    { $auth=false; }             // or change S7artID by request
if (isset ($_REQUEST['newid'])) { $auth=false; }             // or by selecting newid
if (isset ($_POST['passwd'])) {                              // this if statement runs only when password is supplied by POST. i.e. the only way to login
 $suppliedpw = SHA1($_POST['passwd']); 
 $result = db_select("SELECT passwd FROM user WHERE userid=$id");
 $regpwd= $result[0]['passwd'];
 if ($regpwd == $suppliedpw){                                //if user supplies the same password as we have stored in a request, then auth=true
  $auth=true;
 } else {
  $auth=false;                                               //if they blow it, they're still auth=false
 }
}
$suppliedpw='';                                            //dumping suppliedpw for undeclared errors otherwise


// ******** Increment Handling ********

$moddatetime = time();
if ( isset ($_REQUEST['incr'])) {
 $incr = $_REQUEST['incr'];
} else {
 $incr=0;
}
$incrid=0;
$decrid=0;
$incruserid=0;
$decruserid=0;
if ($incr>1 && $auth==true) {
 $moddatetime = time();
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
?>


<!-- ******** Call common header ******** -->

<?php
require_once 'header.php';
?>

<!-- ******** Start Second Row ******** -->

<div class="row container-fluid">
<div class="col-xs-6 col-sm-5 col-sm-offset-1 col-md-3 col-md-offset-1 col-lg-2 col-lg-offset-2">
  <?php if ($id <> ""){echo "<span class='text-nowrap'><h4>S7ARTID: $id</h4></span>". PHP_EOL;} ?>
   <form role="form" method="post" action="index.php" target="_top">
   <div class="input-group input-sm">
    <input type="number" name="newid" placeholder="New ID:" value='<?php echo $id; ?>' class="form-control">
    <span class="input-group-btn">
    <button class="btn btn-default" type="submit">Change</button>
    </span></div></form>
</div>
<div class="col-xs-5 col-sm-5 col-md-7 col-lg-6 text-right">

 <h4><span class="text-nowrap" id="servertime">Current Date/Time...</span></h4>
<?php
echo "<span class='text-nowrap'><h4>Count: ";


// ******** Hit Counter ********

$result="";
$result = db_select("SELECT hc_count FROM hitcounter WHERE hc_index =1");
if($result === false) {
 echo "hitcounter query failed";
} else {
  echo $result[0]['hc_count']."</h4></span>";
}
$result = db_query("UPDATE hitcounter SET hc_count = hc_count+1 WHERE hc_index=1");
if($result === false) {
 echo "Couldn`t increment counter";
}

// ******** IP Address Display ********

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


// ******** ID Counter Handling ********
$count=0;
if ((!isset($_REQUEST['id'])) or (!isset($_REQUEST['newid']))) { //lookup count from database
 $result=db_select("SELECT count from user WHERE userid='$id'");
 $count=$result[0]['count'];
 $count++;
} else { // increment the count in session
 $count = $_SESSION['count'];
 $count++;
}       //also add the lastip and lastaccess
$result=db_query("UPDATE user SET count='$count', lastaccess=(FROM_UNIXTIME($now)), lastip='$ipaddress' WHERE userid='$id'"); //stores 'count' only at this point in the whole site
?>


 </div>
</div>


<!-- ******** End of Second Row ********  -->

<div class="row row-eq-height container-fluid">
  <div class="col-xs-12 col-sm-10 col-sm-offset-1 col-md-10 col-md-offset-1 col-lg-8 col-lg-offset-2">
<?php


// ******** Spit Variables for ID #1: This section displays connection and link increment detail if required for debugging ********

if ($id==110000 or $id==410000) {
   echo "Supplied password SHA1: " . $suppliedpw . "<br>";
   echo "\$auth: " . $auth . "<br>";
   echo "Count: " . $count . "<br>";
   echo "Cookie ID: " . $cookieid . "<br>";
   echo "Session ID: " . $sessionid . "<br>";
   echo "Request ID: " . $requestid . "<br>";
   echo "Increment ID: " . $incrid . "<br>";
   echo "Decrement ID: " . $decrid . "<br>";
   echo "Increment Link ID: " . $incruserid . "<br>";
   echo "Decrement Link ID: " . $decruserid . "<br>";
   echo "Condition is: " . $condition . "<br>";
   echo "Server time is: " . date("F d, Y H:i:s", $moddatetime) . "<br>";
   print_r($_SESSION);
}


// ******** Display User Custom Links ********

if ($id>0){
 echo "<h4>Custom Links: ";
 if ($auth==false) {
  echo "<a href='#login_form' target='_self' id='login_pop'>Log In</a></h4>" . PHP_EOL . "<p>". PHP_EOL;
 } else { 
  echo "<a href=\"index.php?auth=0\" target='_self'>Logout</a> -";
  echo "<a href='#add_form' target='_self' id='add_pop'>Add</a> -". PHP_EOL;
  echo "<a href='#remove_form' target='_top' id='remove_pop'>Remove</a> -". PHP_EOL;
  echo "<a href='manage.php' target='_top'>Manage</a></h4>". PHP_EOL . "<p>";
 }
 $result = db_query("SELECT * FROM links WHERE userid=$id AND active=1 ORDER BY userlinkid");
 if($result === false) {
  echo "user links query failed";
 } else {
  $rows = array();
  while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
   if ($auth==true){ echo "<span class='text-nowrap'><a href='index.php?incr=" . $row['userlinkid'];
    echo "' target='_self'> " . $row['userlinkid'] . "&nbsp;&larr;&nbsp;</a>";}
   if ($auth<>true){ echo "<span class='text-nowrap'>";}
   echo "<a href='" . $row['linkdata'] . "'>" . $row['linktext'] . "</a></span>" . "&nbsp;" . PHP_EOL;
  }
 echo "</p>" . PHP_EOL;
 }
} else {
 // Invite new user code here
 echo "<p>Enter your S7ARTID to load your links on any device" . "<br></p>";
 echo "<a href='#join_form' id='join_pop' target='_self'>Add your own links here - Make a new S7ARTID</a><br></p>" . PHP_EOL;
}
?>
  </div>
</div>


<!-- ******** End of Third Row ******** -->

<div class="row row-eq-height container-fluid">
  <div class="col-xs-12 col-sm-10 col-sm-offset-1 col-md-10 col-md-offset-1 col-lg-8 col-lg-offset-2">
    <h4>Common Use</h4>
    <p>
    <a href="http://www.asx.com.au/">ASX</a>
    <a href="http://www.apple.com/">Apple</a>
    <a href="http://www.americanexpress.com/australia/">Amex</a>
    <a href="http://www.cricket.com.au/">AusCricket</a>
    <a href="http://bellingenpasta.com">Bellingen Pasta</a>
    <a href="http://www.bom.gov.au">BOM</a>
    <a href="https://www.moneysmart.gov.au/managing-my-money/budgeting">Budgeting</a>
    <a href="http://www.carsales.com.au">CarSales</a>
    <a href="http://download.com">CNet</a>
    <a href="http://www.dictionary.com">Dictionary</a>
    <a href="http://www.ebay.com.au">E-Bay</a>
    <a href="http://www.eternalgrowthpartners.com/">EGP</a>
    <a href="http://www.eventcinemas.com.au/">Event</a>
    <a href="http://www.facebook.com/">Facebook</a>
    <a href="http://www.gmail.com/">Gmail</a>
    <a href="http://www.hotmail.com">Hotmail</a>
    <a href="http://www.imdb.com">IMDB</a>
    <a href="http://www.jetstar.com">Jetstar</a>
    <a href="http://www.linkedin.com">LinkedIn</a>
    <a href="http://maps.google.com">Maps</a>
    <a href="http://www.microsoft.com">Microsoft</a>
    <a href="http://www.nbnco.com.au">NBNCo</a>
    <a href="http://www.netflix.com">Netflix</a>
    <a href="http://www.news.com.au">News</a>
    <a href="http://www.nrl.com/">NRL</a>
    <a href="http://www.paypal.com/">PayPal</a>
    <a href="http://www.qantas.com.au/">Qantas</a>
    <a href="http://www.rsvp.com.au/">RSVP</a>
    <a href="http://www.scu.edu.au/">SCU</a>
    <a href="http://www.seek.com.au">Seek</a>
    <a href="http://www.smh.com.au/">SMH</a>
    <a href="http://www.theverge.com/">TheVerge</a>
    <a href="http://www.tradingpost.com.au/?intref=bg108">TradingPost</a>
    <a href="http://twitter.com/">Twitter</a>
    <a href="http://www.virginblue.com.au/">Virgin</a>
    <a href="http://www.whitepages.com.au">White</a>
    <a href="http://www.wikipedia.com">Wikipedia</a>
    <a href="http://www.yellowpages.com.au">Yellow</a>
    <a href="http://www.youtube.com">YouTube</a>
    </p>
    <h4>Real Estate</h4>
    <p>
    <a href="http://www.raywhite.com.au/">Ray White</a>
    <a href="http://www.remax.com.au">Remax</a>
    <a href="http://www.ljh.com.au/">LJH</a>
    <a href="http://www.realestate.com.au">RealEstate</a>
    <a href="http://www.rpdata.com/">RPData</a>
    <a href="http://www.firstnational.com.au/">FN</a>
    <a href="http://www.harcourts.com.au/">Harcourts</a>
    <a href="http://www.domain.com.au/">Domain</a>
    <a href="http://www.cbre.com.au/">CBRE</a>
    </p>
    <h4>Banking</h4>
    <p>
    <a href="http://www.anz.com.au/">ANZ</a>
    <a href="http://www.bcu.com.au/">BCU</a>
    <a href="http://www.boq.com.au/">BQLD</a>
    <a href="https://www.commbank.com.au/">CBA</a>
    <a href="http://www.ingdirect.com.au/">ING</a>
    <a href="http://www.macquarie.com.au/">Macq</a>
    <a href="http://www.nab.com.au">NAB</a>
    <a href="http://www.qpcu.org.au">QPCU</a>
    <a href="http://www.stgeorge.com.au">StGeorge</a>
    <a href="http://www.suncorp.com.au/">Suncorp</a>
    <a href="http://www.westpac.com.au/">Westpac</a>
    </p>
    <h4>TV &amp; Radio Stations</h4>
    <p>
    <a href="http://www.triplem.com.au/">MMM</a>
    <a href="http://www.nbntv.com.au/">NBN</a>
    <a href="http://www.ninemsn.com.au/">Ninemsn</a>
    <a href="http://www.nova1069.com.au/">Nova</a>
    <a href="http://www.iprime.com.au">Prime7</a>
    <a href="http://www.seven.com.au">Seven</a>
    <a href="http://www.ten.com.au">Ten</a>
    </p>
    <h4>Service Providers</h4>
    <p>
    <a href="http://www.bigpond.com">Bigpond</a>
    <a href="http://www.exetel.com.au/">Exetel</a>
    <a href="http://www.optus.com.au/">Optus</a>
    <a href="http://www.optusnet.com.au">Optusnet</a>
    <a href="http://telstra.com/">Telstra</a>
    <a href="http://www.tpg.com.au">TPG</a>
    <a href="http://www.iinet.net.au/">iinet</a>
    <a href="http://www.vodaphone.com.au/">Vodafone</a>
    <a href="http://www.westnet.com.au/">Westnet</a>
    </p>
    <h4>User Suggested - (S7ick it at S7art)</h4>
    <p>
<?php
// ******** Display User Suggestions *********

$result = db_query("SELECT * FROM links WHERE userid=0 AND active=2 ORDER BY userlinkid");
if($result === false) {
 echo "Suggested links query failed";
} else {
 $rows = array();
 while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
  echo "<span class='text-nowrap'><a href='" . $row['linkdata'] . "'>" . $row['linktext'] . "</a></span>" . "&nbsp;" . PHP_EOL;
 }
}

?>

    <br>
  </div>
</div>
  <div class="col-xs-12 col-sm-10 col-sm-offset-1 col-md-10 col-md-offset-1 col-lg-8 col-lg-offset-2">
<?php
 if ($auth===true) {
  echo "<h3>User Suggestions</h3>" . PHP_EOL;
  echo "<p>Only available to S7artID holders. ";
  echo "You must be logged in to make a suggestion.<br>" . PHP_EOL;
  echo "<a href='#suggest_form' target='_top'>Suggest a site</a><br>";


   //********* Suggestion Counter **********

  $result = db_query("SELECT * FROM suggestions");
  $num_rows = mysqli_num_rows($result);
  if(!$result){ 
   die("OOps.. Suggestion Counter Broken...");
  }
  echo"$num_rows" . " ";
  echo "Suggestions. The site is useful because of input from people just like you...<br>". PHP_EOL;
  echo "Many links that have been suggested are now integrated above.</p>";

}
?>
</div>

<!-- ******** End of Fourth Row ******** -->

<div class='row row-eq-height container-fluid'>
 <div class="col-xs-12 col-sm-10 col-sm-offset-1 col-md-6 col-md-offset-1 col-lg-8 col-lg-offset-2">
  <base target="_new">
  <p>Recommend the site:</p>
  <div class="col-xs-8 col-sm-8 col-md-8 col-lg-5">
   <form role="form" action="manage.php" method="post" target="_top">
   <div class="input-group input-sm">
    <input type="text" name="email" placeholder="someone@somewhere.com" value="" class="form-control">
     <span class="input-group-btn">
     <button class="btn btn-default" type="submit">Recommend</button>
     </span>
     <input type="hidden" name="recommend" value="">
    </div>
   </form>
  </div>
  <div class="col-xs-12 col-sm-10 col-md-6 col-lg-8">
   <br><p>
   <a href='#join_form' target='_self' id='join_pop'>New User</a> -
   <a href="#login_form" target="_self" id="login_pop">Log In</a> -
   <a href='https://www.google.com/search?q=set+home+page' target='_new'>Set your homepage</a> -
   <a href="#pwreset_form" target="_top">Reset Password</a> - S7artID determined by: <?php echo $condition; ?><br></p><br>

 <p>
<?php
// ******** Spit Variables for ID : This section displays connection and link increment detail if required for debugging ********
if ($id==42343342324) {
 echo "<p>";
 echo "Supplied password SHA1: " . $suppliedpw . "<br>";
 echo "\$auth: " . $auth . "<br>";
 echo "Count: " . $count . "<br>";
 echo "Cookie ID: " . $cookieid . "<br>";
 echo "Session ID: " . $sessionid . "<br>";
 echo "Request ID: " . $requestid . "<br>";
 echo "Increment ID: " . $incrid . "<br>";
 echo "Decrement ID: " . $decrid . "<br>";
 echo "Increment Link ID: " . $incruserid . "<br>";
 echo "Decrement Link ID: " . $decruserid . "<br>";
 echo "Condition is: " . $condition . "<br>";
 echo "Server time is: " . date("F d, Y H:i:s", $moddatetime) . "<br>";
 echo "<br><br>";
 var_dump(get_defined_vars());

/* // print_r(get_defined_vars());
 echo "<br><br>Session:<br>";
 print_r($_SESSION);
 echo "<br><br>Request:<br>";
 print_r($_REQUEST);
 echo "<br><br>Cookie:<br>";
 print_r($_COOKIE);
 echo "<br><br>Server:<br>";
 print_r($_SERVER);
 echo "<br><br>HTTP_REFERER:<br>";
 echo $_SERVER["HTTP_REFERER"];
 echo "<br><br>HTTP_REQUEST_TIME_FLOAT:<br>";
 echo $_SERVER["REQUEST_TIME_FLOAT"];
 echo "<br><br>";
 echo "<br><br>";
 echo "<br><br></p>";
*/


}

if(isset($_SERVER['HTTP_REFERER'])){$referringpage=$_SERVER['HTTP_REFERER'];
} else {
	$referringpage='';
}
$phpsessid=session_id();
$requesttime=$_SERVER['REQUEST_TIME_FLOAT'];
if(isset($_SERVER['HTTP_USER_AGENT'])){$httpuseragent=$_SERVER['HTTP_USER_AGENT'];}
$result=db_query("INSERT INTO pageloads (userid, loadtime, referringpage, sessionid, ipaddress, proxiedaddress, HTTP_USER_AGENT) VALUES 
($id, FROM_UNIXTIME($requesttime), '$referringpage','$phpsessid','$ipaddress','$pipaddress','$httpuseragent')");

?>
 </div>
</div>
<!-- <div class="row row-eq-height container-fluid">   This is any 'next row'
 <div class="col-xs-12 col-sm-8 col-sm-offset-2 col-md-6 col-md-offset-3"> -->
<?php


// ******** Load User Variables (if id set) ********

if ($id>0 && $auth==true) {
 $result=db_select("SELECT namefirst, namelast FROM user WHERE userid=$id");
 $namefirst=$result[0]["namefirst"];
 $namelast=$result[0]["namelast"];
} else {
 $namefirst='';
 $namelast='';
}
?>


<!-- ******** popup form - join ******** -->

        <a href="#x" class="overlay" id="join_form"></a>
        <div class="popup">
         <form action="join.php" method="post" target="_top">
            <h2>Sign Up</h2>
            <p>Please enter your details here</p>
            <div>
                <label for="email">Login (Email)</label>
                <input type="text" name="username">
            </div>
            <div>
                <label for="pass">Password</label>
                <input type="password" name="passwd" value="">
            </div>
            <div>
                <label for="firstname">First name</label>
                <input type="text" name="namefirst" value="">
            </div>
            <div>
                <label for="lastname">Last name</label>
                <input type="text" name="namelast" value="">
            </div>
            <input type="submit" value="Submit">
            <a class="close" target="_top" href="#close"></a>
         </form>
        </div>

<!-- ******** popup form - add links ********  -->

       <a href="#x" class="overlay" id="add_form"></a>
        <div class="popup">
         <form action="manage.php" method="post" target="_top">
         <h2>Hello <?php echo $namefirst; ?></h2>
         <p>You can use this section to add a link:</p>
          <div>
           <label for="linktext">Link Text</label>
           <input type="text" name="linktext" placeholder="Displayed Text" value="<?php echo $linktext; ?>">
          </div>
          <div>
           <label for="linkdata">Link URL</label>
           <input type="text" name="linkdata" placeholder="http://www.someurl.com" value="<?php echo $linkdata; ?>">
          </div>
          <div>
           <label for="description">Description</label>
           <input type="text" name="description" placeholder="Your Description" value="<?php echo $description; ?>">
          </div>
          <input type="submit" value="Submit">
          <input type="hidden" name="add" value="">
          <a class="close" target="_top" href="#close"></a>
         </form>
       </div>



<!-- ******** popup form - removelinks ********  -->

        <a href="#x" class="overlay" id="remove_form"></a>
        <div class="popup">
         <form action="manage.php" method="post" target="_top">
            <h2>Hello <?php echo $namefirst; ?></h2>
            <p>You can use this section to remove a link:</p>
            <div>
                <label for="removelink">Remove Link Number</label>
                <input type="number" name="delete" placeholder="Link Number" value="">
            </div>
            <input type="submit" value="Remove Link">
            <input type="hidden" name="remove" value="">
            <a class="close" target="_top" href="#close"></a>
         </form>
        </div>



<!-- ******** popup form - login ********  -->

        <a href="#x" class="overlay" id="login_form"></a>
        <div class="popup">
         <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="post" target="_top">
<?php
if($id<1){
 echo "<p>Enter your S7artID and password to login:</p>";
 echo "<div>" . PHP_EOL . "<label for='id'>S7artID</label>" . PHP_EOL . "<input type='number' name='id' value=''></div>";
} else {
 echo "<h2>Hello S7artID $id</h2>";
}
?>
            <p>Enter your password here to adjust your links:</p>
            <div>
                <label for="password">Password</label>
                <input type="password" name="passwd" value="">
            </div>
            <input type="submit" value="Login">
            <a class="close" target="_self" href="#close"></a>
         </form>
        </div>



<!-- ******** popup form - password reset ********  -->

        <a href="#x" class="overlay" id="pwreset_form"></a>
        <div class="popup">
         <form action="pwreset.php" method="post" target="_top">
            <h2>Hello there...</h2>
            <p>If you've forgotten your password or S7artID, you can enter your</p>
            <p>email below and we'll send you an email to reset your password:</p>
            <div>
                <label for="username">Email address</label>
                <input type="text" name="username" placeholder="you@somewhere.com" value="">
            </div>
            <button type="submit" class="btn btn-primary">Send</button>
            <a class="close" target="_top" href="#close"></a> 
         </form>
        </div>

<!-- ******** popup form - user suggestions ********  -->

        <a href="#x" class="overlay" id="suggest_form"></a>
        <div class="popup">
         <form action="manage.php" method="post" target="_top">
            <h2>Hi there...</h2>
            <p>If you've got a link you'd like to share with other users, please submit it here.</p>
            <p>Only registered S7art users can post user suggested links, but they will be made</p>
            <p>available to everyone who visits the site.</p>
            <div class="input-lg">
                <label for="linktext">Link Text</label>
                <input type="text" name="linktext" placeholder="Link Text (Visible Link)" value="">
            </div>
            <div class="input-lg">
                <label for="linkdata">Link URL</label>
                <input type="text" name="linkdata" placeholder="Link URL (http://" value="">
            </div>
            <div class="input-lg">
                <label for="description">Description</label>
                <input type="text" name="description" placeholder="A description of the link for the site admins." value="">
            </div>
            <button type="submit" class="btn btn-primary">Send</button>
            <a class="close" target="_top" href="#close"></a>
            <input type="hidden" name="usersuggest" value="<?php echo $id; ?>">
            <input type="hidden" name="userid" value="<?php echo $id; ?>">
         </form>
        </div>


<?php

// ******** Prepare to close - Write Session variables ********

$_SESSION['id']=$id;
$_SESSION['auth']=$auth;
$_SESSION['count']=$count;
?>


</body>
</html>
