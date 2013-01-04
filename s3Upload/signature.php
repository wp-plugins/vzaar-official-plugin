<?php
/*
 * S3_Upload Test
 * Generating Signature for Amazon S3 uploads. Add your own security layer here
 * if necessary.
 */
 
require_once '../vzaar/Vzaar.php';

Vzaar::$token = $_REQUEST["token"];
Vzaar::$secret = $_REQUEST["secret"];
Vzaar::$enableFlashSupport = true;

header('Content-type: text/xml');

echo(Vzaar::getUploadSignatureAsXml());
?>
