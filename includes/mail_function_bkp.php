<?php
require "vendor/PHPMailer/src/PHPMailer.php";
require "vendor/PHPMailer/src/OAuthTokenProvider.php";
require "vendor/PHPMailer/src/OAuth.php";
require "vendor/PHPMailer/src/SMTP.php";
require "vendor/PHPMailer/src/POP3.php";
require "vendor/PHPMailer/src/Exception.php";
require 'vendor/PHPMailer/src/autoloader.php';
function send_mail($to,$subject,$body){


    $adminemail = "saisatya51@zoho.in"; 

    $mail = new PHPMailer\PHPMailer\PHPMailer();
    $mail->isSMTP();
    
    $mail->Host = "smtp.zoho.in";
    $mail->Port = "587";
    $mail->SMTPSecure = 'tls';
    // $mail->Port = "465";
    // $mail->SMTPSecure = 'ssl';
    $mail->SMTPAuth   = true;
    $mail->Username = $adminemail;
    $mail->Password = "Saikiran@1998";
    $mail->setFrom('saisatya51@zohomail.in','Dev');
    

    $mail->addAddress($to);
    $mail->Subject = $subject;
    $mail->msgHTML($body);
    if (!$mail->send()) {
        print_r($mail->ErrorInfo);
        die('fail');
    }else{
    }
}


send_mail('saikiran.m.v.s.s@gmail.com','Checked','Checked');

?>