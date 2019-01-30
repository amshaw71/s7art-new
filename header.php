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
function test_input($data) {
   $data = trim($data);
   $data = stripslashes($data);
   $data = htmlspecialchars($data);
   return $data;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
 <title>S7art.com-Link Everything!</title>
 <meta charset="utf-8">
 <meta name="viewport" content="width=device-width, initial-scale=1">
 <meta http-equiv="refresh" content="3600; URL=index.php">
 <meta http-equiv="cache-control" content="no-cache">
 <link rel="icon" href="favicon.ico" type="image/vnd.microsoft.icon">
 <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
 <link rel="stylesheet" href="style.css">
 <base target="_blank">
 <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
 <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
<!--[if lt IE 9]>
 <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js" integrity="sha256-KXn5puMvxCw+dAYznun+drMdG1IFl3agK0p/pqT9KAo= sha512-2e8qq0ETcfWRI4HJBzQiA3UoyFk6tbNyG+qSaIBZLyW9Xf3sWZHN/lxe9fTh1U45DpPf07yj94KsUHHWe4Yk1A==" crossorigin="anonymous"></script>
 <script src="https://cdnjs.cloudflare.com/ajax/libs/html5shiv/3.7.3/html5shiv.js"></script>
 <script src="https://cdnjs.cloudflare.com/ajax/libs/respond.js/1.4.2/respond.js"></script>
<![endif]-->
</head>
<body>

<div class="row row-eq-height container-fluid">


 <div class="col-xs-0 col-sm-1 col-md-1 col-lg-2"></div>


 <div class="col-xs-8 col-sm-6 col-md-7 col-lg-4">


  <a href="index.php" target="_self"><img src="img/s7logo.gif" alt="Store all your favourite links at S7ART.com" class=""></a>
 </div>
 <div class="col-xs-12 col-sm-4 col-md-3 col-lg-4 form-group">
  <p></p>
 <form role="form" method="get" action="https://www.google.com/search" target="_blank">
  <div class="input-group">
     <input type="text" name="q" placeholder="Google Search" class="form-control">
    <span class="input-group-btn">
     <button class="btn btn-default" type="submit">Google</button>
    </span>
  </div>
 </form>
 <form role="form" method="get" action="https://search.yahoo.com/search" target="_blank">
  <div class="input-group">
    <input type="text" name="q" placeholder="Yahoo Search" class="form-control">
    <span class="input-group-btn">
     <button class="btn btn-default" type="submit">Yahoo!</button>
    </span>
  </div>
 </form>
</div>


<div class="col-xs-0 col-sm-1 col-md-1 col-lg-2"></div>


</div>
<!-- ******** End of First Row ******** -->

