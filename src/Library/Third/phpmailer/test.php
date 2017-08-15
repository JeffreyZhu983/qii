<?
require("class.phpmailer.php");

 $smtp_server = "121.52.212.84";  // SMTP servers
 $smtp_auth = true;        // turn on SMTP authentication
 $smtp_username = "zjh@castertroy.net";    // SMTP username 
 $smtp_password = "A119328118a";   // SMTP password
 $from = "zjh@castertroy.net";
 $to = "jinhui.zhu@live.cn";
 $subject = "Test Mail"; 

 // send mail via SMTP
 $mail = new PHPMailer();
 $image_content_id=
	$html_message="<html>
<head>
<title>$subject</title>
<style type=\"text/css\"><!--
body { color: black ; font-family: arial, helvetica, sans-serif ; background-color: #A3C5CC }
A:link, A:visited, A:active { text-decoration: underline }
--></style>
</head>
<body>
<table background=\"$background_image_content_id\" width=\"100%\">
<tr>
<td>
<center><h1>$subject</h1></center>
<hr>
<P>Hello ".strtok($to_name," ").",<br><br>
This message is just to let you know that the <a href=\"http://www.phpclasses.org/mimemessage\">MIME E-mail message composing and sending PHP class</a> is working as expected.<br><br>
<center><h2>Here is an image embedded in a message as a separate part:</h2></center>
<center><img src=\"cid:png1\"></center>".
/*
 * This example of embedding images in HTML messages is commented out
 * because not all mail programs support this method.
 *
 * <center><h2>Here is an image embedded directly in the HTML:</h2></center>
 * <center><img src=\"".$image_data_url."\"></center>
 */
"Thank you,<br>
$from_name</p>
</td>
</tr>
</table>
</body>
</html>";
 $mail->IsSMTP();                                   
 $mail->Host     = $smtp_server;
 $mail->SMTPAuth = $smtp_auth;
 $mail->Username = $smtp_username;
 $mail->Password = $smtp_password;
 //AddEmbeddedImage(var $path, var $cid, var $name, var $encoding, var $type) 
 $mail->AddEmbeddedImage('mail.jpg', 'png1', 'mail.jpg','base64',"image/jpg");


 $mail->From     = $from;
 $mail->AddAddress($to); 
 
 $mail->IsHTML(true);                               // send as HTML
 
 $mail->Subject  =  $subject;
 $mail->Body     =  $html_message;
 //$mail->AltBody  =  $html_message;
 
 if(!$mail->Send())
 {
    echo "Message was not sent <p>";
    echo "Mailer Error: " . $mail->ErrorInfo;
    exit;
 }

 echo "Message has been sent";

?>