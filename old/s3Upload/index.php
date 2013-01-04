<?php 
    session_start();
    session_id($_REQUEST["PHPSESSID"]); 
    if(!$_SESSION["UID"]) {echo "<script>window.location='/';</script>";}
    $user='fitioze_mshare';
    $pass='Q!P)W@O(';
    
    function makeLabels($categs)
    {
        GLOBAL $user;
        GLOBAL $pass;
        
        $labels=explode(",",$categs);
        $output="";
        $mysqli = new mysqli('localhost',$user,$pass,'fitioze_mshare2');
        foreach ($labels as $row=>$label)
        {
            if ($label)
            {
                $result = $mysqli->query("SELECT * FROM categories WHERE cid='".$label."';");
                $row = $result->fetch_object();
                $output.=$row->name.",";
            }
        }
        return rtrim($output, ',');
        $mysqli->close();
    }
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>S3_Upload test</title>
        <script type="text/javascript" src="s3_upload.js"></script>

        <script type="text/javascript">
            var s3_swf1 = s3_swf_init('s3_swf1', {
                signatureUrl: 'signature.php',
                width:  500,
                height: 38,
                onSuccess: function(key){
                    //$('#uploadButton').show('slow');

                    this.key = key;
                    //alert(this.key);

                    $('#key').val(this.key);
                    $('#orginal_filename').val(this.fileName);
                    //$('#video_file_size').val(this.fileSize);

                    var arrKey = this.key.split('/');
                    var guid = arrKey[arrKey.length-2];

                    $('#status').html('File has been uploaded. GUID: ' + guid + ', calling Process Video API...');
                    //submit form and send additional parameters;
                    $.post('processVideo.php', {
                        guid: guid,
                        title: '<?php echo $_REQUEST["vtitle"]; ?>',
                        description: '<?php echo $_REQUEST["vdescr"]; ?>',
                        label: '<?php echo makeLabels($_REQUEST["listch"]); ?>',
                        SSID: '<?php echo $_REQUEST["PHPSESSID"]; ?>'
                    }, function(data){
                        $("#s3_swf1").hide();
                        $("#status").html(data);
                        vzId=data;
                        count=0;
                        
                        //WITH PING
                        //setInterval("$.post('../getVideoDetails.php?id='+vzId,'',function(data) {response = eval(\"(\" + data + \")\"); var date = new Date(response.time*1000); var hours = date.getHours(); var minutes = date.getMinutes(); var seconds = date.getSeconds(); var formattedTime = hours + ':' + minutes + ':' + seconds; if(response.videoStatus==2) {$.post('saveRegs.php?vzaarId='+vzId,<?php echo str_replace("\"","\\\"",json_encode($_REQUEST)); ?>,function(data) {window.top.location.href = \"/my_videos\";});} $(\"#status\").html('Video status: '+response.videoStatusDescription+' <br/> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Processing (this might take a while) ... '+formattedTime);});",1000);
                        
                        //WITHOUT PING
                        $.post('saveRegs.php?vzaarId='+data,<?php echo json_encode($_REQUEST); ?>,function(data) {window.top.location.href = "/my_videos";});
                    })
                },
                onFailed: function(status){
                    alert(status);
                    //$('#uploadButton').show('slow');
                },
                onFileSelected: function(filename, size){
                    this.fileName = filename;
                    this.fileSize = size;
                    uploader_file_field = filename;

                    //alert(this.fileName);
                    //alert(this.fileSize);

                    if ((this.fileSize*1) > (2097152000*1)) {
                        alert("The file you have selected is bigger than the upload limit. Please select a smaller file.");
                    }else{
                        EnableButton();
                    }

                },
                onCancel: function(){
                }
            });
            
            function upload()
            {
                $("#status").html("Working ...");
                s3_swf1.upload('s3/');
            }
            
        </script>
    </head>
    <body>
        <input id="key" name="key" type="hidden" />
        <input id="orginal_filename" name="original_filename" type="hidden" />
        <input id="encoding" name="encoding" type="hidden" value="true" />
        <div id="s3_swf1">
            Please <a href="http://www.adobe.com/go/getflashplayer">Update</a> your Flash Player to Flash v9.0.1 or higher...
        </div>
        <br />
        <small><span id="status"></span></small>
    </body>
</html>
