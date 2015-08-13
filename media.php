<?php
require_once(dirname(__FILE__) . '/vzaar/Vzaar.php');

function curPageURL()
{
    $pageURL = 'http';
    if ($_SERVER["HTTPS"] == "on") {
        $pageURL .= "s";
    }
    $pageURL .= "://";
    if ($_SERVER["SERVER_PORT"] != "80") {
        $pageURL .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"] . $_SERVER["REQUEST_URI"];
    } else {
        $pageURL .= $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
    }

    $trimStart = strlen($pageURL) - stripos($pageURL, "&");

    if (stristr($pageURL, "&")) {
        $pageURL = substr($pageURL, 0, strlen($pageURL) - $trimStart);
    }

    return $pageURL;
}

function hasNextPage($page)
{
    $title = (isset($_REQUEST['vmt']) && $_REQUEST['vmt'] != 'Title') ? $_REQUEST['vmt'] : '';
    $labels = '';
    $count = isset($_REQUEST['spp']) ? $_REQUEST['spp'] : 20;
    $sort = 'desc';
    Vzaar::$secret = get_option("vzaarAPIusername");
    Vzaar::$token = get_option("vzaarAPItoken");
    $video_list = Vzaar::searchVideoList(get_option("vzaarAPIusername"), true, $title, $labels, $count, $page, $sort);
    return !empty($video_list);
}

$title = (isset($_REQUEST['vmt']) && $_REQUEST['vmt'] != 'Title') ? $_REQUEST['vmt'] : '';
$labels = '';
$count = isset($_REQUEST['spp']) ? $_REQUEST['spp'] : 20;
$count = $count > 100 ? 100 : $count;
$page = isset($_REQUEST['vmp']) ? $_REQUEST['vmp'] : 1;
$sort = 'desc';

$baseURL = curPageURL();
?>

<div class="wrap">
<h2 id="page_title" style="margin-left: 1em;">vzaar Hosted Videos</h2>

<div id='loading_filter'><img src='<?php echo(plugins_url('', __FILE__))?>/dialogs/ajax-loader.gif'/> Loading results
    ... please wait!
</div>
<table class="form-table">
    <tr>
        <td>
            <form id="vzaar_filter_form" action="" method="POST">
                Videos per page:
                <input type="text" style="width: 30px; text-align: center;" name="spp" value="<?php echo $count; ?>"/>
                <small>(Max 100)</small>
                &nbsp;&nbsp; Search for title:
                <input type="text" name="vmt" value="<?php echo $title ? $title : ""; ?>"/> &nbsp;&nbsp;
                <input class="button-primary" type="submit" onclick="fadeContent();" value="Filter"/>
                <input class="button-primary" type="button" onclick="deleteSelected();" value="Delete selected"/>
            </form>
        </td>
    </tr>
</table>
<form id="videos">
    <?php
    $baseURL .= "&spp=" . $count;
    $baseURL .= ($title != "" ? "&vmt=" . urlencode($title) : "");

    Vzaar::$secret = get_option("vzaarAPIusername");
    Vzaar::$token = get_option("vzaarAPItoken");
    $video_list = Vzaar::searchVideoList(get_option('vzaarAPIusername'), true, $title, $labels, $count, $page, $sort,'active,processing');
    if (!empty($video_list)) {
        foreach ($video_list as $i => $video) {
            try {
                Vzaar::$secret = get_option("vzaarAPIusername");
                Vzaar::$token = get_option("vzaarAPItoken");
                $video_detail = Vzaar::getVideoDetails($video->id, true);

            } catch (Exception $e) {
                echo 'Caught exception: ', $e->getMessage(), "\n";
            }
            $video_detail->description = $video_detail->description ? $video_detail->description : "none";
            if(2 === $video_detail->videoStatus){
                $video_status = "Active";
            }else{
                $video_status = "Processing";
            }
            echo "<style>
                    div.vid_container {
                      float: left;
                      border: 1px solid silver;
                      width: 500px;
                      height: 155px;
                      overflow: auto;
                      margin: 5px;
                      margin-bottom: 15px;
                      display: inline-block;
                    }

                    div.checkbox_container {
                      border-right: 1px solid silver;
                      height: 155px;
                      width: 40px;
                      float: left;
                      line-height: 155px;
                      display: inline-block;
                      text-align: center;
                      float: left;
                    }

                    div.video_table_container {
                      width: 290px;
                      display: inline-block;
                      margin-top: 10px;
                      margin-left: 20px;
                      position: relative;
                      float: left;
                    }

                    span.video_titles {
                      white-space: nowrap;
                      display: block;
                      text-overflow: ellipsis;
                      width: 170px;
                      overflow: hidden;
                    }

                    td {
                      text-align: left !important;
                    }

                    div.vid_control {
                      display: inline-block;
                    }

                    div.vid_control:hover {
                      text-decoration: underline;
                      cursor: pointer;
                    }

                    div.addToPostControler {
                      display: inline-block;
                    }

                    div.addToPostControler:hover {
                      text-decoration: underline;
                      cursor: pointer;
                    }

                    div.vid_delete {
                      background: white;
                      color: red;
                      font-weight: bold;
                      display: inline-block;
                      padding: 0px 3px;
                    }

                    div.vid_delete:hover {
                      background: red;
                      color: white;
                      cursor: pointer;
                    }

                    div.videoLinkOptions {
                      display:none;
                    }

                    div.videoLinkOptions {
                      clear: both;
                      margin-top: 10px;
                      position: relative;
                      color: #21759B;
                    }

                    div.thumb_container {
                      display: inline-block;
                      text-align: center;
                    }

                    img.thumbnail_image {
                      cursor: pointer;
                      margin-top: 10px;
                      clip: rect(0px, 120px, 90px, 0px);
                      position: absolute;
                    }

                    div.missing_img {
                      background:grey;
                      cursor: pointer;
                      margin-top: 10px;
                    }

                    div.page_control {
                      clear: both;
                      text-align: center;
                    }

                    input.button-primary {
                      text-decoration: none;
                      margin: 2px;
                    }

                    div#player_holder {
                      display: hidden;
                    }
                  </style>
                  <div class='vid_container'>
                    <div class='checkbox_container'>
                      <input type='checkbox' name='vid' value='" . $video->id . "'/>
                    </div>
                    <div class='video_table_container'>
                        <table>
                            <tr>
                                <td class='left'>Title:</td>
                                <td>
                                  <span class='video_titles'>"
                                    . urldecode($video->title) .
                                  "</span>
                                </td>
                            </tr>
                            <tr>
                                <td class='left'>Duration:</td>
                                <td>" . $video->duration . " seconds</td>
                            </tr>
                            <tr>
                                <td class='left'>Views:</td>
                                <td>" . $video->playCount . "</td>
                            </tr>
                            <tr>
                                <td class='left'>Media ID:</td>
                                <td>" . $video->id . "</td>
                            </tr>
                            <tr>
                                <td class='left'>Status:</td>
                                <td>" . $video_status . "</td>
                            </tr>
                        </table>
                        <script>
                        jQuery(document).ready(function() {
                            if(typeof addToPost != 'function') {
                                jQuery('.addToPostControler').remove();
                            }
                            jQuery('.videoLinkOptions').fadeIn('slow');
                         });
                        </script>
                        <div class='videoLinkOptions'>
                          <div class='addToPostControler' onclick='addToPost(
                            \"" . $video->id . "\",
                            \"" . urlencode($video->title) . "\",
                            \"" . urlencode($video_detail->description) . "\",
                            \"" . urlencode($video->duration) . "\",
                            \"" . urlencode($video_detail->height) . "\",
                            \"" . urlencode($video_detail->width) . "\",
                            \"" . urlencode($video_detail->html) . "\"
                          );'>Add to post | </div>
                          <div class='vid_control' onclick='playerShow(
                            \"" . urlencode($video->title) . "\",
                            \"" . urlencode($video_detail->html) . "\"
                          );'>View</div> |
                          <div class='vid_control' onclick='editVideo(
                            \"" . $video->id . "\",
                            \"" . urlencode($video_detail->title) . "\",
                            \"" . urlencode($video_detail->description) . "\"
                          )'>Edit</div> |
                          <div class='vid_delete' onclick='deleteVideo(" . $video->id . ")'>Delete</div>
                        </div>
                    </div>
                    <div class='thumb_container'>";
                    if(2 === $video_detail->videoStatus){
                        echo "<img src='" . $video_detail->thumbnailUrl . "' onerror='imgError(this)' onclick='playerShow(
                          \"" . urlencode($video->title) . "\",
                          \"" . urlencode($video_detail->html) . "\"
                        );' class='thumbnail_image'/>";
                    }else{
                        echo "<div class='missing_img'></div>";
                    }

                    echo "</div>
                </div>
                ";
        }

    } else {
        echo 'No data found';
    }
       echo "<div class='page_control'>";
    //PREVIOUS PAGE
    if ($page > 1) {
        echo "<input type='button' class=\"button-primary\" onclick='redirect(
          \"" . str_replace(" ", "", urlencode($baseURL . "&tab=vzaarAPIMedia&vmp=" . ($page - 1))) . "\"
        );' value='Previous page'/>";
    } else {
        echo "<input type='button' class=\"button-primary\" value='Previous page' disabled='disabled'/>";
    }

    //NEXT PAGE BUTTON
    if (hasNextPage($page + 1)) {
        echo "<input type='button' class=\"button-primary\" onclick='redirect(
          \"" . str_replace(" ", "", urlencode($baseURL . "&tab=vzaarAPIMedia&vmp=" . ($page + 1))) . "\"
        );' value='Next page'/>";
    } else {
        echo "<input type='button' class=\"button-primary\" value='Next page' disabled='disabled'/>";
    }
    echo "</div>";

    echo '<div id="player_holder"></div>';

    ?>
</form>

<script src="<?php echo plugin_dir_url(__FILE__); ?>js/jquery.corner.js"></script>

<script type="text/javascript">

    jQuery(document).ready(function($)
    {
        jQuery("#loading_filter").fadeOut("slow");
        jQuery(".vid_container").corner();
        jQuery(".vid_container").fadeIn("slow");
        jQuery(".page_control").corner();
    });

    function imgError(obj)
    {
        obj.src = "<?php echo plugin_dir_url(__FILE__); ?>images/audio_only.png";
    }

    function fadeContent()
    {
        jQuery(".vid_container").fadeOut("slow");
        jQuery(".page_control").fadeOut("slow");
        jQuery("#vzaar_filter_form").fadeOut("slow");
        jQuery("#loading_filter").fadeIn("slow");
    }

    function deleteVideo(id)
    {
        if (confirm("Delete video?"))
        {
            jQuery(".vid_container").fadeOut("slow");
            jQuery(".page_control").fadeOut("slow");
            jQuery("#vzaar_filter_form").fadeOut("slow");
            jQuery("#loading_filter").html("<img src='<?php echo(plugins_url('', __FILE__))?>/dialogs/ajax-loader.gif'/> Deleting videos ... please wait!");
            jQuery("#loading_filter").fadeIn("slow", function ()
            {
                var data = {
                    action:'deleteVzaarVideos',
                    token:"<?php echo get_option('vzaarAPItoken'); ?>",
                    secret:"<?php echo get_option('vzaarAPIusername'); ?>",
                    vids:"vid=" + id
                };

                jQuery.post(ajaxurl, data, function (response)
                {
                    response = response;
                    var backButton = "<input type='button' class='button-primary' onclick='backToList();' style='text-decoration: none;' value='Back to list'/>";
                    jQuery("#loading_filter").html(response + backButton);
                });
            });
        }
    }

    function playerHide()
    {
        jQuery("#page_title").fadeOut("slow");
        jQuery("#player_holder").fadeOut("slow", function ()
        {
            jQuery("#loading_filter").fadeIn("slow");
            jQuery("#page_title").html("vzaar Hosted Media");

            jQuery("#page_title").fadeIn("slow", function ()
            {
                jQuery("#vzaar_filter_form").fadeIn("fast", function ()
                {
                    jQuery(".vid_container").fadeIn("slow");
                    jQuery(".page_control").fadeIn("slow", function ()
                    {
                        jQuery("#loading_filter").fadeOut("slow");
                    });
                });
            });
        });

    }

    function playerShow(title, player, auxButton, pageTitle)
    {
        if (typeof auxButton == 'undefined') auxButton = '';
        if (typeof pageTitle == 'undefined') pageTitle = "Viewing now: " + decodeURIComponent((title + '').replace(/\+/g, '%20'));
        jQuery(".vid_container").fadeOut("slow");
        jQuery("#vzaar_filter_form").fadeOut("slow");
        jQuery(".page_control").fadeOut("slow");
        jQuery("#page_title").fadeOut("slow", function ()
        {
            jQuery("#loading_filter").fadeIn("slow", function ()
            {
                jQuery("#page_title").html(pageTitle).fadeIn("slow");
                var backButton = "<input type='button' class='button-primary' onclick='playerHide();' style='text-decoration: none;' value='Back to list'/>";
                jQuery("#player_holder").html(decodeURIComponent((player + '').replace(/\+/g, '%20')) + "<br/>" + backButton + auxButton).fadeIn("slow");
                jQuery(".back_link").corner();
                jQuery("#player_holder").fadeIn("slow", function ()
                {
                    jQuery("#loading_filter").fadeOut("slow");
                });
            });
        });
    }

    function editVideo(id, title, description)
    {
        title = decodeURIComponent((title + '').replace(/\+/g, '%20'));
        description = decodeURIComponent((description + '').replace(/\+/g, '%20'));
        /* KW
        http://forum.jquery.com/topic/is-jquery-stripping-form-tags#14737000003548757
        <form> elements being stripped when used with .html()
        Added </form> to the beginning of the content variable and this is the only element stripeed
        */

        content = "</form><form id='videoDetails'>";
        content += 'Title: <input style="width: 200px;" type="text" name="title" id="title" value="' + title + '"/><br/><br/>';
        content += 'Description: <br/><textarea name="description" rows="10" cols="100" id="description">' + description + '</textarea><br/>';
        content += "<input hidden='hidden' type='text' name='vid' value='" + id + "'/>";
        content += "</form>";

        var saveButton = "<input type='button' class='button-primary' onclick='saveDetails();' style='text-decoration: none;' value='Save details'/>";

        playerShow(title, content, saveButton, "Editing: " + decodeURIComponent((title + '').replace(/\+/g, '%20')));
    }

    function saveDetails()
    {
        jQuery(".vid_container").fadeOut("slow");
        jQuery(".page_control").fadeOut("slow");
        jQuery("#vzaar_filter_form").fadeOut("slow");
        jQuery("#loading_filter").html("<img src='<?php echo(plugins_url('', __FILE__))?>/dialogs/ajax-loader.gif'/> Saving video details ... please wait!");
        jQuery("#loading_filter").fadeIn("slow", function ()
        {
            var data = {
                action:'updateVzaarVideos',
                token: "<?php echo get_option('vzaarAPItoken'); ?>",
                secret: "<?php echo get_option('vzaarAPIusername'); ?>",
                details: encodeURI(jQuery("#videoDetails").serialize())
            };

            jQuery.post(ajaxurl, data, function (response)
            {

                jQuery("#loading_filter").html(response + " <small>Redirecting back to list in 3 seconds.</small>");
                setTimeout("closeEditBox()", 3000);
            });
        });
    }

    function redirect(url)
    {
        fadeContent();
        url = decodeURIComponent(url);
        window.location = url;
    }

    function backToList()
    {
        jQuery("#loading_filter").fadeOut("slow", function ()
        {
            jQuery("#loading_filter").html("<img src='<?php echo(plugins_url('', __FILE__))?>/dialogs/ajax-loader.gif'/> Loading results ... please wait!").fadeIn("slow");
        });
        window.location.reload();
    }

    function closeEditBox()
    {
        jQuery("#player_holder").fadeOut("slow", function ()
        {
            jQuery("#page_title").fadeOut("slow");
            jQuery("#loading_filter").fadeOut("slow", function ()
            {
                jQuery("#loading_filter").html("<img src='<?php echo(plugins_url('', __FILE__))?>/dialogs/ajax-loader.gif'/> Redirecting ... please wait!").fadeIn("slow");
            });
        });
        window.location.reload();
    }

    function deleteSelected()
    {
        if (confirm("Delete videos?"))
        {
            jQuery(".vid_container").fadeOut("slow");
            jQuery(".page_control").fadeOut("slow");
            jQuery("#vzaar_filter_form").fadeOut("slow");
            jQuery("#loading_filter").html("<img src='<?php echo(plugins_url('', __FILE__))?>/dialogs/ajax-loader.gif'/> Deleting videos ... please wait!");
            jQuery("#loading_filter").fadeIn("slow", function ()
            {
                var data = {
                    action:'deleteVzaarVideos',
                    token:"<?php echo get_option('vzaarAPItoken'); ?>",
                    secret:"<?php echo get_option('vzaarAPIusername'); ?>",
                    vids:jQuery("#videos").serialize()
                };

                jQuery.post(ajaxurl, data, function (response)
                {
                    response = response;
                    var backButton = "<input type='button' class='button-primary' onclick='backToList();' style='text-decoration: none;' value='Back to list'/>";
                    jQuery("#loading_filter").html(response + backButton);
                });
            });
        }
    }

</script>
</div>
