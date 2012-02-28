<head>
    <meta charset="UTF-8"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"/>
    <title>Comfort@Home</title>
    <link rel="stylesheet" href="styles.css" media="all"/>
    <!---------------------------------------- dialog -->
    <link type="text/css"  href="jquery-ui-1.8.16.custom/css/custom-theme/jquery-ui-1.8.16.custom.css" rel="stylesheet" />
</head>
<script type="text/javascript" src="yui-min.js"></script>
<script type="text/javascript">
YUI().use("node-base", "io", function (Y) {
    Y.on("io:complete", function (id, o, args) {
        Y.one("#showTemp").empty();
        Y.one("#showTemp").append(o.responseText);
    });
    Y.later(0, null, function () {
        Y.io("comet-temp.php?room="+document.roomForm.room.value);
    }, null, false);
    Y.later(1000, null, function () {
        Y.io("comet-temp.php?room="+document.roomForm.room.value);
    }, null, true);
});
YUI().use("node-base", "io", function (Y) {
    Y.on("io:complete", function (id, o, args) {
        Y.one("#showHumi").empty();
        Y.one("#showHumi").append(o.responseText);
    });
    Y.later(0, null, function () {
        Y.io("comet-humi.php?room="+document.roomForm.room.value);
    }, null, false);
    Y.later(1000, null, function () {
        Y.io("comet-humi.php?room="+document.roomForm.room.value);
    }, null, true);
});
YUI().use("node-base", "io", function (Y) {
    Y.on("io:complete", function (id, o, args) {
        Y.one("#showLight").empty();
        Y.one("#showLight").append(o.responseText);
    });
    Y.later(0, null, function () {
        Y.io("comet-light.php?room="+document.roomForm.room.value);
    }, null, false);
    Y.later(1000, null, function () {
        Y.io("comet-light.php?room="+document.roomForm.room.value);
    }, null, true);
});
YUI().use("node-base", "io", function (Y) {
    Y.on("io:complete", function (id, o, args) {
        Y.one("#showPeople").empty();
        Y.one("#showPeople").append(o.responseText);
    });
    Y.later(0, null, function () {
        Y.io("comet-people.php?room="+document.roomForm.room.value);
    }, null, false);
    Y.later(1000, null, function () {
        Y.io("comet-people.php?room="+document.roomForm.room.value);
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
<?
    // link
    include('dataProcess.php');
    require('database/appvars.php');
    $link = mysql_connect(DB_HOST, DB_USER, DB_PASSWORD) or die("NO CONNECT!!");
    mysql_query("SET NAMES 'utf8'");
    mysql_select_db(DB_NAME) or die("OPEN FAILED!!");

    // post
    $room = $_POST["room"];
    if(!$room) $room = 'CSIE_R513';
?>
<body>
    <div id="header">
    	<div id="title">
            <a href="index.php" id="titlelink">Comfort @ Home</a>
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
        </div>
        <!------------------------login / logout end-->
    </div>
    <div id="content">
        <div id="main">
            <div class="room-change">
                <form action="index.php" method="post" name="roomForm">
                    <select name="room" onChange="roomForm.submit();">
                        <option <?if($room=='CSIE_R513') echo "selected";?>>CSIE_R513
                        <option <?if($room=='CSIE_R441') echo "selected";?>>CSIE_R441
                    </select>
                    <span class="info">Mode :
                    <?
                    $sql = "select policy_name from policy where room = '$room' and status = 2";
                    $result = mysql_query($sql, $link) or die("EXECUTE SQL FAILED!!");
                    $row = mysql_fetch_assoc($result) or die("READ DATA FAILED");
                    echo $row["policy_name"];
                    ?>
                    </span>
                </form>
            </div>
            
            <ol>
            <li><div class="block">
                <form id='contacts-form' action='timelineTemp.php' method='post' name='tempForm'>
                <span class="block-title">temperature</span>
                <div class="block-img">
                    <input type="image" src="../images/icon_temperature.png"/>
                </div>
                <span class="block-info">
                    <!----------------------------->
                    <div id='showTemp'></div>
                    <!----------------------------->
                </span>
                </form>
                </div>
            </li>
            <li><div class="block">
                <form id='contacts-form' action='timelineHumi.php' method='post' name='humiForm'>
                <span class="block-title">humidity</span>
                <div class="block-img">
                    <input type="image" src="../images/icon_humidity.png"/>
                </div>
                <span class="block-info">
                    <!----------------------------->
                    <div id='showHumi'></div>
                    <!----------------------------->
                </span>
                </form>
                </div>
            </li>
            <li><div class="block">
                <form id='contacts-form' action='timelineLight.php' method='post' name='lightForm'>
                <span class="block-title">light</span>
                <div class="block-img">
                    <input type="image" src="../images/icon_light.png"/>
                </div>
                <span class="block-info">
                    <!----------------------------->
                    <div id='showLight'></div>
                    <!----------------------------->
                </span>
                </form>
                </div>
            </li>
            <li><div class="block">
            <form id='contacts-form' action='timelinePeople.php' method='post' name='peopleForm'>
                <span class="block-title">people count</span>
                <div class="block-img">
                    <input type="image" src="../images/icon_people.png"/>
                </div>
                <span class="block-info">
                    <!----------------------------->
                    <div id='showPeople'></div>
                    <!----------------------------->
                </span>
                </form>
                </div>
            </li>
            </ol>
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
