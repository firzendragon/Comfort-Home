<head>
    <meta charset="UTF-8"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"/>
    <title>Comfort@Home</title>
    <link rel="stylesheet" href="styles.css" media="all"/>
    <link rel="stylesheet" href="http://jquery-ui.googlecode.com/svn/tags/latest/themes/base/jquery-ui.css" type="text/css" media="all" />
    <link type="text/css"  href="jquery-ui-1.8.16.custom/css/custom-theme/jquery-ui-1.8.16.custom.css" rel="stylesheet" />
</head>
<?
    include('dataProcess.php');
    require('database/appvars.php');
    $link = mysql_connect(DB_HOST, DB_USER, DB_PASSWORD) or die("NO CONNECT!!");
    mysql_query("SET NAMES 'utf8'");
    mysql_select_db(DB_NAME) or die("OPEN FAILED!!");

    $time = $_POST["time"];
    $room = $_POST["room"];
    $node_id = $_POST["node_id"];
    if(!$time)    $time = 'Today';
    if(!$room)    $room = 'CSIE_R513';
    if(!$node_id) $node_id = '0001';
   
    $timechange = $_POST["timechange"];
    $timeStart = strftime('%Y-%m-%d',time()+24*60*60*$timechange);
    $timeEnd   = strftime('%Y-%m-%d',time()+24*60*60*($timechange+1));
 
    $querytime = $_POST["querytime"]; // 1 hour
    if(!$querytime) $querytime = 60;
    $section = (24*60) / $querytime; // how many sections
    $value = getTimeline($link, $node_id, $querytime, 'light', $timeStart, $timeEnd);
    xmlWrite('sample.xml', 'c6a300', $node_id, $value, 'Lumen', $section, $timeStart, $timeEnd);
?>
<script type="text/javascript" src="yui-min.js"></script> 
<script type="text/javascript">
YUI().use("node-base", "io", function (Y) {
    Y.on("io:complete", function (id, o, args) {
        Y.one("#showLight").empty();
        Y.one("#showLight").append(o.responseText);
    });
    Y.later(0, null, function () {
        Y.io("comet-light.php?room="+document.queryForm.room.value);
    }, null, false);
    Y.later(1000, null, function () {
        Y.io("comet-light.php?room="+document.queryForm.room.value);
    }, null, true);
});
</script>
<!---------------------------------------- dialog -->
<script src="http://www.google.com/jsapi"></script>
<script>
    google.load("jquery", "1.4");
    google.load("jqueryui", "1.7");
</script>
<script type="text/javascript">
function checkPassword(){
    $('#checkpwd').css({display:'inline'});
    $("#checkpwd").dialog('open');
    $("#checkpwd").dialog({
        title: 'Check your password:',
        bgiframe: true,
        width: 300,
        height: 600,
        modal: true,
        draggable: true,
        resizable: false,
        buttons: {
            "Login": function() {
                document.pwdForm.submit();
            },
            'Back': function() {
                $(this).dialog('close');
            }
        }
    });
}
function logout(){
    $('#checkleave').css({display:'inline'});
    $("#checkleave").dialog('open');
    $("#checkleave").dialog({
        title: 'Logout:',
        bgiframe: true,
        width: 400,
        height: 600,
        modal: true,
        draggable: true,
        resizable: false,
        buttons: {
            "Yes": function() {
                document.logoutForm.submit();
            },
            'No': function() {
                $(this).dialog('close');
            }
        }
    });
}
</script>
<body>
    <div id="header">
        <div id="title"><a href="index.php" id="titlelink">Comfort @ Home</a>
        <!------------------------login / logout -->
        <? if($_COOKIE["pass"] != "TRUE") { ?>
        <a href="log.php" id="login"  onclick="checkPassword(); return false;" style="font-family:calibri;">login</a>
        <div id="checkpwd" style="display:none; font-family:calibri;">
            <form action="log.php" method="post" name="pwdForm">
            <br/>Account:<br/>
            <input type="text"     name="account"  value="installer" style="font-family:calibri; width:300px;">
            <br/>Password:<br/>
            <input type="password" name="password" value="installer" style="font-family:calibri; width:300px;">
            <input type=hidden name=checkpwd value=1>
            </form>
        </div>
        <? } else { ?>
        <a href="log.php" id="logout" onclick="logout(); return false;" style="font-family:calibri;">logout</a>
        <div id="checkleave" style="display:none; font-family:calibri;">
            <form action="log.php" method="post" name="logoutForm">
            &nbsp;Do you really want to leave?
            <input type=hidden name=logout value=1>
            </form>
        </div>
        <? }?>
        <!------------------------login / logout end-->
    </div>
    <div id="content">
    <div id="main">
         <div class="room-change">
             <form action="timelineLight.php" method="post" name="queryForm">
                <select name="time" onChange="queryForm.submit();">
                    <option>Today
                </select>
                <select name="room" onChange="queryForm.submit();">
                    <option <?if($room=='CSIE_R513') echo "selected";?>>CSIE_R513
                    <option <?if($room=='CSIE_R441') echo "selected";?>>CSIE_R441
                </select>
                <select name="node_id" onChange="queryForm.submit();">
                    <?
                        $sql = "select node_id from profile_node where room = '$room'";
                        $result = mysql_query($sql, $link) or die("EXECUTE SQL FAILED!!");
                        while($row = mysql_fetch_assoc($result)){
                            if($row[node_id] == $node_id)
                                echo "<option value=".$row["node_id"]." selected>".$row["node_id"];
                            else
                                echo "<option value=".$row["node_id"].">".$row["node_id"];
                        }
                    ?>
                </select>
                <select name="querytime" onChange="queryForm.submit();">
                    <option value="60" <?if($querytime==60) echo "selected";?>>60 mins
                    <option value="30" <?if($querytime==30) echo "selected";?>>30 mins
                    <option value="10" <?if($querytime==10) echo "selected";?>>10 mins
                </select>
                <input type="hidden" name="timechange"  value=<?echo $timechange;?>>
            </form>
        </div>
        <div id="type-sidebar">
            <div id="type-title">now</div>
            <div id="type-image">
                <img src="../images/icon_light.png" width="40" />
            </div>
            <span id="type-info"><div id='showLight'></div></span>  
            <ul id="type-detail">
                <li>Status : comfortable</li>
                <li>Mode : 
                <?
                $sql = "select policy_name from policy where room = '$room' and status = 2";
                $result = mysql_query($sql, $link) or die("EXECUTE SQL FAILED!!");
                $row = mysql_fetch_assoc($result) or die("READ DATA FAILED");
                echo $row["policy_name"];
                ?>
                </li>
            </ul>
            <div id="type-back"><span><a href="index.php" style="color:white; text-decoration:none">back</a></span></div>
        </div>
        <div id="timeline">
            <div style="float:left;">
            <form action="timelineLight.php" method="post" name="previousForm">
                <input type="image"  src="../images/icon-previous.gif" value="previous" onclick="previousForm.submit()">
                <input type="hidden" name="timechange" value=<?echo $timechange-1;?>>
                <input type="hidden" name="room"       value=<?echo $room;?>>
                <input type="hidden" name="node_id"    value=<?echo $node_id;?>>
                <input type="hidden" name="querytime"  value=<?echo $querytime;?>>
            </form>
            </div>
            <? if($timechange != 0) { ?>
            <div style="float:right;">
            <form action="timelineLight.php" method="post" name="forwardForm">
                <input type="image"  src="../images/icon-foward.gif" value="previous" onclick="forwardForm.submit()">
                <input type="hidden" name="timechange" value=<?echo $timechange+1;?>>
                <input type="hidden" name="room"       value=<?echo $room;?>>
                <input type="hidden" name="node_id"    value=<?echo $node_id;?>>
                <input type="hidden" name="querytime"  value=<?echo $querytime;?>>
            </form>
            </div>
            <? } ?>
            <!------------------------------------------->
            <script language="javascript">AC_FL_RunContent = 0;</script>
            <script language="javascript"> DetectFlashVer = 0; </script>
            <script src="AC_RunActiveContent.js" language="javascript"></script>
            <script language="JavaScript" type="text/javascript">
            <!--
            var requiredMajorVersion = 10;
            var requiredMinorVersion = 0;
            var requiredRevision = 45;
            -->
            </script>
            <script language="JavaScript" type="text/javascript">
            <!--
            if (AC_FL_RunContent == 0 || DetectFlashVer == 0) {
                alert("This page requires AC_RunActiveContent.js.");
            } else {
                var hasRightVersion = DetectFlashVer(requiredMajorVersion, requiredMinorVersion, requiredRevision);
                if(hasRightVersion) { 
                    AC_FL_RunContent(
                        'codebase', 'http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=10,0,45,2',
                        'width', '470',
                        'height', '300',
                        'scale', 'noscale',
                        'salign', 'TL',
                        'bgcolor', '#ffffff',
                        'wmode', 'opaque',
                        'movie', 'charts',
                        'src', 'charts',
                        'FlashVars', 'library_path=charts_library&xml_source=sample.xml', 
                        'id', 'my_chart',
                        'name', 'my_chart',
                        'menu', 'true',
                        'allowFullScreen', 'true',
                        'allowScriptAccess','sameDomain',
                        'quality', 'high',
                        'align', 'middle',
                        'pluginspage', 'http://www.macromedia.com/go/getflashplayer',
                        'play', 'true',
                        'devicefont', 'false'
                        ); 
                } else { 
                    var alternateContent = 'This content requires the Adobe Flash Player. '
                    + '<u><a href=http://www.macromedia.com/go/getflash/>Get Flash</a></u>.';
                    document.write(alternateContent); 
                }
            }
            // -->
            </script>
            <noscript>
                <P>This content requires JavaScript.</P>
            </noscript>

            </div>            
    </div>
    <div id="aside" class="meta-act">
    <ul>
        <li><a href="index.php"   title="Dashboard"  class="current meta-act-link meta-home"><span>Dashboard</span></a></li>
        <li><a href="control.php" title="Control" class="meta-act-link meta-control"><span>Control</span></a></li>
        <? if($_COOKIE["pass"] == "TRUE") { ?>
        <li><a href="install.php" title="Install" class="meta-act-link meta-install"><span>Install</span></a></li>
        <li><a href="policy.php"  title="Policy"  class="meta-act-link meta-policy"><span>Policy</span></a></li>
        <li><a href="profile.php" title="Profile" class="meta-act-link meta-profile"><span>Profile</span></a></li>
        <? } ?>
    <ul>
    </div>

    </div>
</body></html>
<?
    mysql_free_result($result);
    mysql_close($link);
?>
