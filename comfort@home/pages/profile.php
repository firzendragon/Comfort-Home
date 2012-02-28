<?
    $pass = $_COOKIE["pass"];
    if($pass != "TRUE")
    {
        header("location:index.php");
        exit();
    }
?>
<head>
    <meta charset="UTF-8"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"/>
    <title>Comfort@Home</title>
    <link rel="stylesheet" href="styles.css" media="all"/>
    <link rel="stylesheet" href="http://jquery-ui.googlecode.com/svn/tags/latest/themes/base/jquery-ui.css" type="text/css" media="all" />
    <link type="text/css"  href="jquery-ui-1.8.16.custom/css/custom-theme/jquery-ui-1.8.16.custom.css" rel="stylesheet" />
    <!-- td input-->
    <script src="td_input/jquery-1.4.2.min.js" type="text/javascript"></script>
    <script src="td_input/tdchange.js" type="text/javascript"></script>
</head>
<?
    include('installProcess.php');
    require('database/appvars.php');
    $link = mysql_connect(DB_HOST, DB_USER, DB_PASSWORD) or die("NO CONNECT!!");
    mysql_query("SET NAMES 'utf8'");
    mysql_select_db(DB_NAME) or die("OPEN FAILED!!");
    
    if($_POST["sensor"]) {
        profileSensor($link);
    }
    else if($_POST["actuator"]) {
        profileActuator($link);
    }
?>
<!---------------------------------------- dialog -->
<script src="http://www.google.com/jsapi"></script>
<script>
    google.load("jquery", "1.4");
    google.load("jqueryui", "1.7");
</script>
<script type="text/javascript">
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
        <a href="log.php" id="logout" onclick="logout(); return false;" style="font-family:calibri;">logout</a>
        <div id="checkleave" style="display:none; font-family:calibri;">
            <form action="log.php" method="post" name="logoutForm">
            &nbsp;Do you really want to leave?
            <input type=hidden name=logout value=1>
            </form>
        </div>
        </div>
        <!------------------------login / logout end-->
    </div>
    <div id="content">
    <div id="main">

    <!---------------------------------------- actuator -->
    <div class="single">
    <ul>
    <form id="contacts-form" action="profile.php" METHOD="post" NAME="actuatorForm">
        <li><span class="info">Profile - Actuator</span></li><hr>
        <!-- table start -->
        <li>
        <table cellpadding="3" border="1" rules="all" cellspacing="0" bordercolor="#bfbfbf" style="border-collapse: collapse" width=580>
            <tr BGCOLOR="#9f9f9f">
                <td><font face="Calibri" size="2" color="White">Actuator ID</td>
                <td><font face="Calibri" size="2" color="White">Actuator Name</td>
                <td><font face="Calibri" size="2" color="White">Voltage</td>
                <td><font face="Calibri" size="2" color="White">Power</td>
                <td><font face="Calibri" size="2" color="White">Attribute</td>
            </tr>
            <tbody id='tbcontainer1'>
            <?
            $sql = "select * from profile_actuator";
            $result = mysql_query($sql, $link) or die("EXECUTE SQL FAILED!!");
            while($row = mysql_fetch_assoc($result)) {
                echo "<tr>";
                echo "<td>" .$row["actuator_id"]. "</td>";
                echo "<td>" .$row["actuator_name"]. "</td>";
                echo "<td>" .$row["voltage"]      . "</td>";
                echo "<td>" .$row["power"]        . "</td>";
                echo "<td>" .$row["attribute"]    . "</td>";
                echo "</tr>";
            }
            ?>
            </tbody>
        </table>
        <br/></li>
        <!-- table end-->
    </form>
    </ul>
    </div>
    <!---------------------------------------- sensor -->
    <div class="single">
    <ul>
    <form id="contacts-form" action="profile.php" METHOD="post" NAME="sensorForm">
        <li><span class="info">Profile - Sensor</span></li><hr>
        <!-- table start -->
        <li>
        <table cellpadding="3" border="1" rules="all" cellspacing="0" bordercolor="#bfbfbf" style="border-collapse: collapse" width=580>
            <tr BGCOLOR="#9f9f9f">
                <td><font face="Calibri" size="2" color="White">Sensor ID</td>
                <td><font face="Calibri" size="2" color="White">Sensor Name</td>
                <td><font face="Calibri" size="2" color="White">Sample Period</td>
                <td><font face="Calibri" size="2" color="White">Valid Range</td>
                <td><font face="Calibri" size="2" color="White">Attribute</td>
            </tr>
            <tbody id='tbcontainer2'>
            <?
            $sql = "select * from profile_sensor";
            $result = mysql_query($sql, $link) or die("EXECUTE SQL FAILED!!");
            while($row = mysql_fetch_assoc($result)) {
                echo "<tr>";
                echo "<td>" .$row["sensor_id"]    . "</td>";
                echo "<td>" .$row["sensor_name"]  . "</td>";
                echo "<td>" .$row["sample_period"]. "</td>";
                echo "<td>" .$row["valid"]        . "</td>";
                echo "<td>" .$row["attribute"]    . "</td>";
                echo "</tr>";
            }
            ?>
            </tbody>
        </table>
        <br/></li>
        <!-- table end-->
    </form>
    </ul>
    </div>
    
    </div><!-- end of main -->
    <div id="aside" class="meta-act">
    <ul>
        <li><a href="index.php"   title="Dashboard"  class="meta-act-link meta-home"><span>Dashboard</span></a></li>
        <li><a href="control.php" title="Control" class="meta-act-link meta-control"><span>Control</span></a></li>
        <li><a href="install.php" title="Install" class="meta-act-link meta-install"><span>Install</span></a></li>
        <li><a href="policy.php"  title="Policy"  class="meta-act-link meta-policy"><span>Policy</span></a></li>
        <li><a href="profile.php" title="Profile" class="meta-act-link meta-profile current"><span>Profile</span></a></li>
    <ul>
    </div>
    
    </div>
</body></html>
<?
    mysql_free_result($result);
    mysql_close($link);
?>
