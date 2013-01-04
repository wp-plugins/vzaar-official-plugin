<?php
session_start();
session_id($_POST["SSID"]);
if(!$_SESSION["UID"]) {echo "<script>window.location='/';</script>";}
require_once '../Vzaar.php';
require_once '../credentials.php';
Vzaar::$token = VzaarCredentials::$token;
Vzaar::$secret = VzaarCredentials::$secret;

header('Content-type: text/html');

if (isset($_POST['guid'])) {
    $apireply = Vzaar::processVideo($_POST['guid'], $_POST['title'], $_POST['description'], $_POST["label"], Profile::Original);
    echo ($apireply);
}
else
{
    echo('GUID is missing');
}
?>