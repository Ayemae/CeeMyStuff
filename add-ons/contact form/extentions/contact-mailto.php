<?php
$siteEmail = $set['site_email'];
// If you'd like this contact form to send to a different email, uncomment line 7 and write out the email you would like to use instead.
// IMPORTANT, make sure it is in quotes!
// // Example:
// // $siteEmail = 'my-email@example.com';
// $siteEmail = '';


if (isset($_POST['send_message_from_contact'])) {
    $method = 'post';
    $email = $message = $name = null;
    $email = filter_var($_POST['contact_email'],FILTER_VALIDATE_EMAIL);
    if (!$email) {
        $msg=fdbkMsg(0,"Please enter a valid email address.");
        return;
    }
    $message = trim(strip_tags($_POST['contact_message']));
    if (!$message) {
        $msg=fdbkMsg(0,"Please include who this message is from.");
        return;
    }
    $name = trim(strip_tags($_POST['contact_name']));
    if (!$name) {
        $msg=fdbkMsg(0,"Please include who this message is from.");
        return;
    }
    $body = 'Sent via the contact form at '.$baseURL.$_SERVER['REQUEST_URI'].': 
        <br/><br/>
        <blockquote>
        '.$message.'
        </blockquote>
        <br/><br/>From: '.$name.'<br/><a href="mailto:"'.$email.'">'.$email.'</a>';
    if ($email && $message && $name) {
        if (mail($siteEmail, '"'.$name.'" sent a message via the '.$set['site_name'].' website', $body, $emailHeaders))
        $msg =fdbkMsg(1,"Message sent!");
    } else {
        $msg=fdbkMsg(0,'Message failed to send. Please try again.');
    }
}
unset($siteEmail);