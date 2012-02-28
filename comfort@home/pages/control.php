<head>
    <meta charset="UTF-8"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"/>
    <title>Comfort@Home</title>
    <link rel="stylesheet" href="styles.css" media="all"/>
    <!---------------------------------------- dialog -->
    <link type="text/css"  href="jquery-ui-1.8.16.custom/css/custom-theme/jquery-ui-1.8.16.custom.css" rel="stylesheet" />
</head>
<script type='text/javascript'>
function dialogSelectPolicy(){
    $('#selectPolicy').css({display:'inline'});
    $("#selectPolicy").dialog('open');
    $("#selectPolicy").dialog({
        title: 'Select your mode:',
        bgiframe: true,
        width: 400,
        height: 600,
        modal: true,
        draggable: true,
        resizable: false,
        buttons: {
            "Edit": function() {
                document.selectPolicyForm.submit();
            },
            "Remove": function() {
                document.selectPolicyForm.functionality.value = 1;
                document.selectPolicyForm.submit();
            },
            'Cancel': function() {
                $(this).dialog('close');
            }
        }
    });
}
function dialogNewPolicy(){
    $('#newPolicy').css({display:'inline'});
    $("#newPolicy").dialog('open');
    $("#newPolicy").dialog({
        title: 'Create new mode:',
        bgiframe: true,
        width: 400,
        height: 600,
        modal: true,
        draggable: true,
        resizable: false,
        buttons: {
            "Save": function() {
                document.newPolicyForm.submit();
            },
            'Cancel': function() {
                $(this).dialog('close');
            }
        }
    });
}
function dialogEditPolicy(){
    $('#editPolicy').css({display:'inline'});
    $("#editPolicy").dialog('open');
    $("#editPolicy").dialog({
        title: 'Edit mode:',
        bgiframe: true,
        width: 400,
        height: 600,
        modal: true,
        draggable: true,
        resizable: false,
        buttons: {
            "Save": function() {
                document.editPolicyForm.submit();
            },
            'Cancel': function() {
                $(this).dialog('close');
            }
        }
    });
}
</script>
<!---------------------------------------- slider -->
<script type="text/javascript" src="jquery-ui-1.8.16.custom/js/jquery-1.6.2.min.js"></script>
<script type="text/javascript">
$(function(){
    $('#climate').slider({
        range: 'min',
        value: 5,
        min: 0,
        max: 10,
        slide: function(event, ui) {
            $('#amountClimate').val(ui.value);
        }
    });
    $('#amountClimate').val($('#climate').slider('value'));
    $('#lighting').slider({
        range: 'min',
        value: 5,
        min: 0,
        max: 10,
        slide: function(event, ui) {
            $('#amountLighting').val(ui.value);
        }
    });
    $('#amountLighting').val($('#lighting').slider('value'));
    $('#ventilation').slider({
        range: 'min',
        value: 5,
        min: 0,
        max: 10,
        slide: function(event, ui) {
            $('#amountVentilation').val(ui.value);
        }
    });
    $('#amountVentilation').val($('#ventilation').slider('value'));
    $('#energy').slider({
        range: 'min',
        value: 50,
        min: 0,
        max: 100,
        slide: function(event, ui) {
            $('#amountEnergy').val(ui.value+'%');
        }
    });
    $('#amountEnergy').val($('#energy').slider('value')+'%');
});
</script>
<script type="text/javascript" src="yui-min.js"></script>
<script type="text/javascript">
YUI().use("node-base", "io", function (Y) {
    Y.on("io:complete", function (id, o, args) {
        Y.one("#show").empty();
        Y.one("#show").append(o.responseText);
    });
    Y.later(0, null, function () {
        Y.io("control_actuator.php?room="+document.readForm.room.value);
    }, null, false);
    Y.later(1000, null, function () {
        Y.io("control_actuator.php?room="+document.readForm.room.value);
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
function control_click(value){
    
    $.post("control_click.php",
    {
        room:         document.controlForm.room.value,
        instruction:  value
    },
    "json"
    );
}
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
    include('dataProcess.php');
    require('database/appvars.php');
    $link = mysql_connect(DB_HOST, DB_USER, DB_PASSWORD) or die("NO CONNECT!!");
    mysql_query("SET NAMES 'utf8'");
    mysql_select_db(DB_NAME) or die("OPEN FAILED!!");
    
    $room = $_POST["room"];
    if(!$room) $room = 'CSIE_R513';
    
    if($_POST["switchMode"]) {
        $policy = $_POST["policy"];
        
        $sql = "update policy set status = 0 where room = '$room' and status = 2";
        mysql_query($sql, $link) or die("EXECUTE SQL FAILED!!");
        
        $sql = "update policy set status = 1 where room = '$room' and policy_name = '$policy'";
        mysql_query($sql, $link) or die("EXECUTE SQL FAILED!!");
    }
    else if($_POST["editPolicy"]) {
        $policy_name    = $_POST["policy_name"];
        $climate        = $_POST["climate"];
        $lighting       = $_POST["lighting"];
        $ventilation    = $_POST["ventilation"];
        $energy_saving  = $_POST["energy_saving"];
        $activity_level = $_POST["activity_level"];
        $sql = "update policy set climate = '$climate',lighting = '$lighting',ventilation = '$ventilation',energy_saving = '$energy_saving',activity_level = '$activity_level' where policy_name = '$policy_name'";
        mysql_query($sql, $link) or die("EXECUTE SQL FAILED!!");
    }
    else if($_POST["newPolicy"]) {
        $policy_name = $_POST["policy_name"];
        $climate     = $_POST["climate"];
        $lighting    = $_POST["lighting"];
        $ventilation = $_POST["ventilation"];
        $energy_saving  = $_POST["energy_saving"];
        $activity_level = $_POST["activity_level"];
        $sql = "insert into policy (room,policy_name,climate,lighting,ventilation,energy_saving,activity_level,status) values('$room','$policy_name','$climate','$lighting','$ventilation','$energy_saving','$activity_level',0)";
        mysql_query($sql, $link) or die("EXECUTE SQL FAILED!!");
    }
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
                <form action='control.php' method='post' name='readForm'>
                    <select name="room" onChange="readForm.submit();">
                        <option <?if($room=='CSIE_R513') echo "selected";?>>CSIE_R513
                        <option <?if($room=='CSIE_R441') echo "selected";?>>CSIE_R441
                    </select>
                    <select name="policy" onChange="readForm.submit();">
                        <?
                        // delete mode
                        $selectPolicy  = $_POST["selectPolicy"];
                        $functionality = $_POST["functionality"];
                        if($selectPolicy && $functionality == 1) {
                            $policy = $_POST["policy"];
                            $sql = "delete from policy where room = '$room' and policy_name = '$policy'";
                            mysql_query($sql, $link) or die("EXECUTE SQL FAILED!!");
                        }
                        $sql = "select policy_name,status from policy where room = '$room'";
                        $result = mysql_query($sql, $link) or die("EXECUTE SQL FAILED!!");
                        while($row = mysql_fetch_assoc($result)) {
                            if($row["status"]==2)  echo "<option selected>".$row["policy_name"];
                            else                   echo "<option>".$row["policy_name"];
                        }
                        ?>
                    </select>
                    <input type=hidden name=switchMode  value=1>
                </form>
            </div>

            <div>
                <input type="button" onclick="dialogNewPolicy()"    value="New Mode"    style="cursor: pointer; font-family:Calibri;" class="submit_bt">
                <input type="button" onclick="dialogSelectPolicy()" value="Select Mode" style="cursor: pointer; font-family:Calibri;" class="submit_bt">
                <!---------------------------------------- floor plan-->
                <div>
                    <img name="background" src="../images/<? echo $room; ?>.png" width=600 style="opacity:0.8;">
                    <!---------------------------------------- actuators -->
                    <form action='control.php' method='post' name='controlForm'>
                        <div id="show"></div>
                        <input type=hidden name=control     value=1>
                        <input type=hidden name=room        value=<?echo $room;?>>
                    </form>
                </div>
                <!---------------------------------------- new mode -->
                <div id="newPolicy" style="display:none; font-family:calibri;">
                    <form id="contacts-form" action="control.php" METHOD="post" NAME="newPolicyForm">
                        Name : <input type="text" id="inputext" name="policy_name" value=""/><br />
                        <!-- Slider -->
                        <div>Climate :        <input type="text" id="amountClimate" style="font-family:calibri; border:0;font-weight:bold;" name="climate"/></div>
                        <div id="climate"     style="width:398px;"></div>
                        <div>Lighting :       <input type="text" id="amountLighting" style="font-family:calibri; border:0;font-weight:bold;" name="lighting"/></div>
                        <div id="lighting"    style="width:398px;"></div>
                        <div>Ventilation :    <input type="text" id="amountVentilation" style="font-family:calibri; border:0;font-weight:bold;" name="ventilation"/></div>
                        <div id="ventilation" style="width:398px;"></div>
                        <div>Energy Saving :  <input type="text" id="amountEnergy" style="font-family:calibri; border:0;font-weight:bold;" name="energy_saving"/></div>
                        <div id="energy"      style="width:398px;"></div>
                        <br/>Activity Level :
                        <input type='radio' name='activity_level' value='slow' checked>Slow
                        <input type='radio' name='activity_level' value='rapid'>Rapid
                        <input type=hidden name=newPolicy value=1>
                        <input type=hidden name=room value=<? echo $room;?>>
                    </form>
                </div>
                <!---------------------------------------- edit mode -->
                <?
                if($selectPolicy && $functionality == 0) {
                    $policy = $_POST["policy"];
                    $sql = "select * from policy where policy_name = '$policy'";
                    $result = mysql_query($sql, $link) or die("EXECUTE SQL FAILED!!");
                    $row = mysql_fetch_assoc($result) or die("READ DATA FAILED");
                ?>
                    <div id="editPolicy" style="display:none; font-family:calibri;">
                        <form id="contacts-form" action="control.php" METHOD="post" NAME="editPolicyForm">
                            Name : <? echo $row["policy_name"];?><br />
                            <!-- Slider -->
                            <div>Climate :        <input type="text" id="amountClimate" style="font-family:calibri; border:0;font-weight:bold;" name="climate" value="<? echo $row["climate"];?>"/></div>
                            <div id="climate"     style="width:398px;"></div>
                            <div>Lighting :       <input type="text" id="amountLighting" style="font-family:calibri; border:0;font-weight:bold;" name="lighting" value="<? echo $row["lighting"];?>"/></div>
                            <div id="lighting"    style="width:398px;"></div>
                            <div>Ventilation :    <input type="text" id="amountVentilation" style="font-family:calibri; border:0;font-weight:bold;" name="ventilation" value="<? echo $row["ventilation"];?>"/></div>
                            <div id="ventilation" style="width:398px;"></div>
                            <div>Energy Saving :  <input type="text" id="amountEnergy" style="font-family:calibri; border:0;font-weight:bold;" name="energy_saving" value="<? echo $row["energy_saving"];?>"/></div>
                            <div id="energy"      style="width:398px;"></div>
                            <br/>Activity Level :
                            <input type='radio' name='activity_level' value='slow'  <? if($row["activity_level"]=='slow')  echo "checked"; ?>>Slow
                            <input type='radio' name='activity_level' value='rapid' <? if($row["activity_level"]=='rapid') echo "checked"; ?>>Rapid
                            <input type=hidden name=editPolicy value=1>
                            <input type=hidden name=policy_name value=<? echo $row["policy_name"];?>>
                            <input type=hidden name=room value=<? echo $room;?>>
                        </form>
                    </div>
                <?
                    echo "<script>dialogEditPolicy();</script>";
                }
                ?>
                <!---------------------------------------- select mode -->
                <div id="selectPolicy" style="display:none; font-family:calibri;">
                    <form id="contacts-form" action="control.php" METHOD="post" NAME="selectPolicyForm">
                        <?
                        $sql = "select policy_name,status from policy where room = '$room'";
                        $result = mysql_query($sql, $link) or die("EXECUTE SQL FAILED!!");
                        while($row = mysql_fetch_assoc($result)) {
                            echo "<input type='radio' name='policy' value=".$row["policy_name"].">".$row["policy_name"]."<br />";
                        }
                        ?>
                        <input type=hidden name=selectPolicy  value=1>
                        <input type=hidden name=functionality value=0>
                        <input type=hidden name=room value=<?echo $room;?>>
                    </form>
                </div>
                <!------------------------------------------>
            </div>
        </div>
        <!-- end of main -->
        <div id="aside" class="meta-act">
            <ul>
                <li><a href="index.php"   title="Dashboard"  class="meta-act-link meta-home"><span>Dashboard</span></a></li>
                <li><a href="control.php" title="Control" class="current meta-act-link meta-control"><span>Control</span></a></li>
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
