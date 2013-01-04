<?php
    session_start();
    session_id($_REQUEST["PHPSESSID"]);
    if(!$_SESSION["UID"]) {echo "<script>window.location='/';</script>";}
    $user='fitioze_mshare';
    $pass='Q!P)W@O(';
    
    $mysqli = new mysqli('localhost',$user,$pass,'fitioze_mshare');
    
    $resp_time = time();
    $mdate = date("Y-m-d");
	$mtime = time();
    $active="active='1'";
    $space=0;
    
    $sql="insert into files_video set vzaarId='".$_REQUEST["vzaarId"]."', vzaarHosted='1', uid='$_SESSION[UID]', vtitle='".str_replace('_',' ',$_REQUEST["vtitle"])."', vdescr='".$_REQUEST["vdescr"]."', vtags='".$_REQUEST["vtags"]."', vcategs='0,".$_REQUEST["listch"].",0', vtype='".$_REQUEST["vpriv"]."', adddate='$mdate', addtime='$mtime', $active, vspace='$space';";
    $mysqli->query($sql);
    $id=$mysqli->insert_id;
	$key=substr(md5($id),13,20);
    $sql="update files_video set vkey='$key' where vid='$id';";
    $mysqli->query($sql);
    
    $sql="update members set files_video=files_video+1 where uid='$_SESSION[UID]'";
    $mysqli->query($sql);
    
    $mysqli->close();
    
?>