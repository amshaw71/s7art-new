<!-- Set servertime by JScript -->
<script type="text/javascript">
var offset = new Date().getTimezoneOffset()
var currenttime = '<?php echo date("F d, Y H:i:s", time())?>' //PHP method of getting server date
var montharray=new Array("Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec")
var serverdate=new Date(currenttime)
function padlength(what){
 var output=(what.toString().length==1)? "0"+what : what
 return output
}
function displaytime(){
 serverdate.setSeconds(serverdate.getSeconds()+1)
 var datestring=montharray[serverdate.getMonth()]+" "+padlength(serverdate.getDate())+", "+serverdate.getFullYear()
 var timestring=padlength(serverdate.getHours())+":"+padlength(serverdate.getMinutes())+":"+padlength(serverdate.getSeconds())
 document.getElementById("servertime").innerHTML=datestring+" "+timestring
}
window.onload=function(){
 setInterval("displaytime()", 1000)}
</script>
<?php
require_once "resources/opendb.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
 <title>RosterEasy - Simplify!</title>
 <meta charset="utf-8">
 <meta name="viewport" content="width=device-width, initial-scale=1">
 <meta http-equiv="refresh" content="1800; URL=login.php?action=logout">
 <meta http-equiv="cache-control" content="no-cache">
 <link rel="icon" href="favicon.ico" type="image/vnd.microsoft.icon">
 <link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
 <link rel="stylesheet" href="resources/style.css">
 <base target="_self">
 <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
 <script src="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
</head>
<body>
<div class="row row-eq-height container-fluid">
 <div class="col-xs-0 col-sm-1 col-md-1 col-lg-2">
 </div>
 <div class="col-xs-8 col-sm-6 col-md-7 col-lg-4">
  <a href="index.php" target="_self"><img src="img/rosterez.gif" alt="Manage your shop roster simply with Roster E-Z" class=""></a>
 </div>
 <div class="col-xs-12 col-sm-4 col-md-3 col-lg-4 form-group">
 </div>
 <div class="col-xs-0 col-sm-1 col-md-1 col-lg-2">
 </div>
</div>
<!-- ******** End of First and Start of Second Row ******** -->
<div class="row container-fluid">
 <div class="col-xs-12 col-sm-10 col-sm-offset-1 col-md-8 col-md-offset-2 col-lg-8 col-lg-offset-2">