<?php
$mail_from="root@shawtech.com.au";
$subject = "S7art.com Confirmation email";
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
$message .=",</p><h3> Welcome to S7art.com,</h3><br>
<p>You have just created a new S7artID ('Start I dee') at <a href='http://s7art.com'>S7art.com</a>. If this wasn't you, 
sorry about that, please just delete this message and we won't bug you anymore :(</p><br>
<p>If you DID create a S7artID, you'll soon be able to load your favorite web links onto your homepage and
access them from any device you use to view the internet. From here, you just need to click Confirm below to activate:</p>";
$message .= "<a href='http://s7art.com/join.php?confirmstring=";
$message .=$confirmstring;
$message .="'>Confirm</a><br><br>
<p>If that didn't work, you can copy and paste this link into your favourite web browser:<br><br>
<p>http://s7art.com/join.php?confirmstring=";
$message .=$confirmstring;
$message .= "</p><br>
<p>We hope you enjoy using S7art.com and hope you tell all your friends about it ;)</p><br><br>
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
