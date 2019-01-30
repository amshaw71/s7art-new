<?php
$mail_from="root@shawtech.com.au";
$subject = "S7art.com Password Reset";
$message ="
<html>
<head>
</head>
<body>
<style>
body {
 background-color: #fff;
 color: #000;
 font-weight: normal;
 font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif;
 font-size: 16px;
 text-justify: auto;
}
a {
 color: #00f;
}
p {
 color: #000;
}
</style>
<p>Hello, ";
$message .=$namefirst;
$message .=" ";
$message .=$namelast;
$message .=",</p><h3> Password Reset!</h3>
<p>A password reset request has been submitted from the website <a href='http://s7art.com'>S7art.com</a>. If this wasn't you, 
sorry about that, please just delete this message and we won't bug you anymore :(</p>
<p>If you DID request a password reset, please follow the link below to the password reset page. From there, you can reset your password:</p>";
$message .= "<a href='http://s7art.com/pwreset.php?confirmstring=";
$message .=$confirmstring;
$message .="'>Confirm</a><br><br>
<p>If that didn't work, you can copy and paste this link into your favourite web browser:<br>
http://s7art.com/pwreset.php?confirmstring=";
$message .=$confirmstring;
$message .= "</p>
<p>We hope you enjoy using S7art.com and hope you tell all your friends about it ;)</p><br>
<p>Thanks a million...</p>
<p>The team at S7art.com</p>
</body>
</html>
";

$headers = "MIME-Version: 1.0" . "\r\n";
$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";

// More headers
$headers .= 'From: <root@shawtech.com.au>' . "\r\n";

mail($to,$subject,$message,$headers, -f.$mail_from);
?> 
