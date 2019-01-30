<?php
$mail_from="root@shawtech.com.au";
$subject = "RosterEZ Registration email";
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
 font-family: 'Helvetica',Helvetica,Arial,sans-serif;
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
<h3>A new organisation has been created at RosterEZ using your email address.</h3>
<p>You'll soon be organising your rosters simply and efficiently.</p>
<p>From here, you just need to click the link below, fill out the form on our website 
and you're on your way ;)</p><p>
<a href='http://s7art.com/roster/userreg.php?confirmstring=
";
$message .= $confirmstring;
$message.= "'>Register</a></p>
<p>If that didn't work, you can copy and paste this link into your favourite web browser:</p>
<p>http://s7art.com/roster/userreg.php?confirmstring=";
$message.= $confirmstring;
$message.= "</p>
<p>We know you'll enjoy using RosterEZ and hope you tell all your business associates and friends about it ;)</p>
<p>Thanks a million...</p>
<p>The team at RosterEZ</p>
</body>
</html>
";

$headers = "MIME-Version: 1.0" . "\r\n";
$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";

// More headers
$headers .= "From: <root@shawtech.com.au>" . "\r\n";

mail($to,$subject,$message,$headers, -f.$mail_from);
?> 
