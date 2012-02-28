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
    <style type="text/css">
    .drag {
        position: relative;
    }
	</style>
</head>
<!---------------------------------------------------------------->
<script src="http://www.google.com/jsapi"></script>
<script>
    google.load("jquery", "1.4");
    google.load("jqueryui", "1.7");
</script>
<script type='text/javascript'>
function move_node(){
    $.post("install_move.php",
    {
        node:  document.moveNodeForm.node_id.value,
        x:     document.moveNodeForm.x.value,
        y:     document.moveNodeForm.y.value
    },
    "json"
    );
}
function clearSensor() { document.newNodeForm.sensors.value=""; }
function updateSensor(){
    form = document.newNodeForm;
    if(form.sensors.value.length>0) {
        form.sensors.value=form.sensors.value+","
                          +form.column1.options[form.column1.selectedIndex].text+":"
                          +form.column2.options[form.column2.selectedIndex].text+"."
                          +form.column3.options[form.column3.selectedIndex].text;
    }
    else {
        form.sensors.value=form.column1.options[form.column1.selectedIndex].text+":"
                          +form.column2.options[form.column2.selectedIndex].text+"."
                          +form.column3.options[form.column3.selectedIndex].text;
    }
}
function clearActuator() { document.newNodeForm.actuators.value=""; }
function updateActuator(){
    form = document.newNodeForm;
    if(form.actuators.value.length>0) {
        form.actuators.value=form.actuators.value+","
                            +form.column4.options[form.column4.selectedIndex].text+":"
                            +form.column5.options[form.column5.selectedIndex].text+"."
                            +form.column6.options[form.column6.selectedIndex].text;
    }
    else {
        form.actuators.value=form.column4.options[form.column4.selectedIndex].text+":"
                            +form.column5.options[form.column5.selectedIndex].text+"."
                            +form.column6.options[form.column6.selectedIndex].text;
    }
}
function dialogNewNode(){
    $('#new_node').css({display:'inline'});
    $("#new_node").dialog('open');
    $("#new_node").dialog({
        title: 'Add new node:',
        bgiframe: true,
        width: 550,
        height: 600,
        modal: true,
        draggable: true,
        resizable: false,
        overlay:{opacity: 0.7, background: "#000000" },
        autoOpen:true,
        buttons: {
            "Save and Reconfigure": function() {
                document.newNodeForm.functionality.value = 1;
                document.newNodeForm.submit();
            },
            "Save": function() {
                document.newNodeForm.submit();
            },
            'Cancel': function() {
                $(this).dialog('close');
            }
        }
    });
}
function dialogSelectNode(){
    $('#select_node').css({display:'inline'});
    $("#select_node").dialog('open');
    $("#select_node").dialog({
        title: 'Select node to edit or remove:',
        bgiframe: true,
        width: 400,
        height: 600,
        modal: true,
        draggable: true,
        resizable: false,
        overlay:{opacity: 0.7, background: "#000000" },
        autoOpen:true,
        buttons: {
            "Edit": function() {
                document.selectNodeForm.submit();
            },
            "Remove": function() {
                document.selectNodeForm.functionality.value = 1;
                document.selectNodeForm.submit();
            },
            'Cancel': function() {
                $(this).dialog('close');
            }
        }
    });
}
function dialogEditNode(){
    $('#edit_node').css({display:'inline'});
    $("#edit_node").dialog('open');
    $("#edit_node").dialog({
        title: 'Select node to edit:',
        bgiframe: true,
        width: 570,
        height: 600,
        modal: true,
        draggable: true,
        resizable: false,
        overlay:{opacity: 0.7, background: "#000000" },
        autoOpen:true,
        buttons: {
            "Save and Reconfigure": function() {
                document.editForm.functionality.value = 1;
                document.editForm.submit();
            },
            "Save": function() {
                document.editForm.submit();
            },
            'Cancel': function() {
                $(this).dialog('close');
            }
        }
    });
}
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
<!---------------------------------------------------------------->
<?
    include('installProcess.php');
    require('database/appvars.php');
    $link = mysql_connect(DB_HOST, DB_USER, DB_PASSWORD) or die("NO CONNECT!!");
    mysql_query("SET NAMES 'utf8'");
    mysql_select_db(DB_NAME) or die("OPEN FAILED!!");
    
    $room = $_POST["room"];
    if(!$room) $room = 'CSIE_R513';
    
    if($_POST["newNode"]) {
        // to database
        $node_id    = $_POST["node_id"];
        $sensor     = $_POST["sensors"];
        $actuator   = $_POST["actuators"];
        
        // to location
        $name = 'node';
        $positionX  = $_POST["x"] -10;
        $positionY  = $_POST["y"] +85;
        
        if($node_id) {
            // add sensors and actuators
            // add node
            $sql = "insert into profile_node (node_id,room,positionX,positionY,sensor,actuator,node_name)
                    values ('$node_id','$room','$positionY','$positionX','$sensor','$actuator','node')";
            mysql_query($sql, $link) or die("EXECUTE SQL FAILED!!");
            
            // add basic instruction and remote of actuator
            if($actuator) {
                $temp = explode(",",$actuator);
                for ($i=0; $i<count($temp); $i++) {
                    $temp1 = explode(":",$temp[$i]);
                    $temp1 = explode(".",$temp1[1]);
                    $pin  = $temp1[1];
                    
                    $instruction1 = "$node_id write $pin:1 ";
                    $sql = "insert into log_instruction (instruction) values ('$instruction1')";
                    mysql_query($sql, $link) or die("EXECUTE SQL FAILED!!");
                    $instruction0 = "$node_id write $pin:0 ";
                    $sql = "insert into log_instruction (instruction) values ('$instruction0')";
                    mysql_query($sql, $link) or die("EXECUTE SQL FAILED!!");
                    
                    $sql = "insert into log_remote (room,instruction,status) values ('$room','$instruction1',0)";
                    mysql_query($sql, $link) or die("EXECUTE SQL FAILED!!");
                    $sql = "insert into log_remote (room,instruction,status) values ('$room','$instruction0',0)";
                    mysql_query($sql, $link) or die("EXECUTE SQL FAILED!!");
                }
            }
        }
        if($_POST["functionality"]==1) {  // Save and Reconfigure
            $sql = "update policy set status = 1 where room = '$room' and status = 2";
            mysql_query($sql, $link) or die("EXECUTE SQL FAILED!!");
        }
    }
    else if($_POST["editNode"]) {
        $node_id  = $_POST["node_id"];
        $room     = $_POST["room"];
        $sensor   = $_POST["sensor"];
        $actuator = $_POST["actuator"];
        
        $sql = "update profile_node set room = '$room', sensor = '$sensor', actuator = '$actuator' where node_id = '$node_id'";
        mysql_query($sql, $link) or die("EXECUTE SQL FAILED!!");
        
        if($actuator) {
            $temp = explode(",",$actuator);
            
            // delete old remote instruction
            $sql = "delete from log_remote where instruction like '%$node_id%'";
            mysql_query($sql, $link) or die("EXECUTE SQL FAILED!!");
            
            for ($i=0; $i<count($temp); $i++) {
                $temp1 = explode(":",$temp[$i]);
                $temp1 = explode(".",$temp1[1]);
                $pin  = $temp1[1];
                
                // insert instruction for actuator icon
                $instruction1 = "$node_id write $pin:1 ";
                $sql = "insert into log_instruction (instruction) values ('$instruction1')";
                mysql_query($sql, $link) or die("EXECUTE SQL FAILED!!");
                $instruction0 = "$node_id write $pin:0 ";
                $sql = "insert into log_instruction (instruction) values ('$instruction0')";
                mysql_query($sql, $link) or die("EXECUTE SQL FAILED!!");
                
                // insert new remote instruction
                $sql = "insert into log_remote (room,instruction,status) values ('$room','$instruction1',0)";
                mysql_query($sql, $link) or die("EXECUTE SQL FAILED!!");
                $sql = "insert into log_remote (room,instruction,status) values ('$room','$instruction0',0)";
                mysql_query($sql, $link) or die("EXECUTE SQL FAILED!!");
            }
        }
        if($_POST["functionality"]==1) { // Save and Reconfigure
            $sql = "update policy set status = 1 where room = '$room' and status = 2";
            mysql_query($sql, $link) or die("EXECUTE SQL FAILED!!");
        }
    }
?>
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
        <!---------------------------------------- room-change -->
        <div class="room-change">
            <form action='install.php' method='post' name='readForm'>
            <select name="room" onChange="readForm.submit();">
                <option <?if($room=='CSIE_R513') echo "selected";?>>CSIE_R513
                <option <?if($room=='CSIE_R441') echo "selected";?>>CSIE_R441
            </select>
            </form>
        </div>
        
        <div>
        <input type="button" onclick="dialogNewNode()"     value="New Node"    style="cursor: pointer; font-family:Calibri;" class="submit_bt">
        <input type="button" onclick="dialogSelectNode()"  value="Select Node" style="cursor: pointer; font-family:Calibri;" class="submit_bt">
        <!---------------------------------------- floor plan-->
        <div>
            <img src="../images/<? echo $room; ?>.png" width=600 style="opacity:0.8;">
        </div>
        <!---------------------------------------- new node section -->
        <div id="new_node" style="display:none; font-family:calibri;">
            <form id="contacts-form" action="install.php" METHOD="post" NAME="newNodeForm">
                <font face="Calibri">
                ID : <input type='text' name="node_id"><BR/>
                Room : &nbsp;&nbsp;<? echo $room;?><BR/>
                Sensor :
                <select name="column1" style="font-family:calibri; cursor: pointer;">
                <?
                $sql = "select sensor_name from policy_attribute";
                $result = mysql_query($sql, $link) or die("EXECUTE SQL FAILED!!");
                for ($i=1; $i <= mysql_num_rows($result); $i++) {
                    $row = mysql_fetch_assoc($result) or die("READ DATA FAILED");
                    echo "<option>".$row["sensor_name"];
                }
                ?>
                </select>
                <select name="column2" style="font-family:calibri; cursor: pointer;">
                    <option>digital
                    <option>analog
                </select>
                <select name="column3" style="font-family:calibri; cursor: pointer;">
                    <?  for($i=0; $i<15; $i++) echo "<option value=$i>$i"; ?>
                </select>
                <input type="button" value="Add"   onClick="updateSensor();" style="font-family:calibri; cursor: pointer;" class="submit_bt">
                <input type="button" value="Clear" onClick="clearSensor();"  style="font-family:calibri; cursor: pointer;" class="submit_bt"><BR/>
                <textarea name="sensors" style="font-family:calibri;"></textarea><BR/>
                Actuator :
                <select name="column4" style="font-family:calibri; cursor: pointer;">
                <?
                $sql = "select actuator_name from policy_relation";
                $result = mysql_query($sql, $link) or die("EXECUTE SQL FAILED!!");
                for ($i=1; $i <= mysql_num_rows($result); $i++) {
                    $row = mysql_fetch_assoc($result) or die("READ DATA FAILED");
                    echo "<option>".$row["actuator_name"];
                }
                ?>
                </select>
                <select name="column5" style="font-family:calibri; cursor: pointer;">
                    <option>digital
                    <option>analog
                </select>
                <select name="column6" style="font-family:calibri; cursor: pointer;">
                    <?  for($i=0; $i<15; $i++) echo "<option value=$i>$i"; ?>
                </select>
                <input type="button" value="Add"   onClick="updateActuator();"  style="font-family:calibri; cursor: pointer;" class="submit_bt">
                <input type="button" value="Clear" onClick="clearActuator();"   style="font-family:calibri; cursor: pointer;" class="submit_bt"><BR/>
                <textarea name="actuators" style="font-family:calibri;"></textarea><BR/>
                </font>
                <input type=hidden name=newNode value=1>
                <input type=hidden name=functionality value=0>
                <input type=hidden name=room value=<? echo $room;?>>
            </form>
        </div>
        <!---------------------------------------- edit node section -->
        <?
        if($_POST["editSection"]) {
            $functionality = $_POST["functionality"];
            $node_id       = $_POST["node_id"];
            $sql = "select * from profile_node where node_id = '$node_id'";
            $result = mysql_query($sql, $link) or die("EXECUTE SQL FAILED!!");
            $row = mysql_fetch_assoc($result) or die("READ DATA FAILED");
            
            // remove node
            if($functionality == 1) {
                // delete old remote instruction
                $sql = "delete from log_remote where instruction like '%$node_id%'";
                mysql_query($sql, $link) or die("EXECUTE SQL FAILED!!");
                
                // delete node
                $sql = "delete from profile_node where node_id = '$node_id'";
                mysql_query($sql, $link) or die("EXECUTE SQL FAILED!!");
            }
            if($functionality == 0) {
        ?>
            <div id="edit_node" style="display:none; font-family:calibri;">
            <form id="contacts-form" action="install.php" METHOD="post" NAME="editForm">
                ID :       <? echo $row["node_id"]; ?><BR/>
                Room :     <? echo $row["room"]; ?><BR/>
                Sensor :   <textarea cols="25" rows="4" name="sensor"   style="font-family:calibri;"><? echo $row["sensor"]; ?></textarea><BR/>
                Actuator : <textarea cols="25" rows="4" name="actuator" style="font-family:calibri;"><? echo $row["actuator"]; ?></textarea><BR/>
                <input type=hidden name=editNode value=1>
                <input type=hidden name=functionality value=0>
                <input type=hidden name=node_id value=<? echo $row["node_id"];?>>
                <input type=hidden name=room value=<? echo $room;?>>
            </form>
            </div>
        <?
                echo "<script>dialogEditNode();</script>";
            }
        }
        ?>
        <!---------------------------------------- select node -->
        <div id="select_node" style="display:none; font-family:calibri;">
        <form id="contacts-form" action="install.php" METHOD="post" NAME="selectNodeForm">
            <?
            $sql = "select node_id, node_name from profile_node where room = '$room'";
            $result = mysql_query($sql, $link) or die("EXECUTE SQL FAILED!!");
            for ($i=1; $i <= mysql_num_rows($result); $i++) {
                $row = mysql_fetch_assoc($result) or die("READ DATA FAILED");
                if($row["node_name"] == 'node')
                    echo "<input type='radio' name='node_id' value=".$row["node_id"].">".$row["node_id"]."<br />";
            }
            ?>
            <input type=hidden name=editSection   value=1>
            <input type=hidden name=functionality value=0>
            <input type=hidden name=room value=<?echo $room;?>>
        </form>
        </div>
        <!---------------------------------------- move node -->
        <form id="contacts-form" action="install.php" METHOD="post" NAME="moveNodeForm" onclick="move_node(); return false;">
            <?
            $sql = "select * from profile_node where room = '$room'";
            $result = mysql_query($sql, $link) or die("EXECUTE SQL FAILED!!");
            for ($i=1; $i <= mysql_num_rows($result); $i++) {
                $row = mysql_fetch_assoc($result) or die("READ DATA FAILED");
                echo "<input type='image' class='drag' src='../images/".$row["node_name"].".png' title=".$row["node_id"]." name=".$row["node_name"]." value=".$row["node_id"]." style='position:absolute; TOP:".$row["positionX"]."px; LEFT:".$row["positionY"]."px; WIDTH:25px; HEIGHT:25px;' />";
            }
            ?>
            <input type=hidden name=node_id value=0>
            <input type=hidden name=x       value=0>
            <input type=hidden name=y       value=0>
        </form>
        
        </div>
        </div><!-- end of main -->

        <div id="aside" class="meta-act">
            <ul>
                <li><a href="index.php"   title="Dashboard"  class="meta-act-link meta-home"><span>Dashboard</span></a></li>
                <li><a href="control.php" title="Control" class="meta-act-link meta-control"><span>Control</span></a></li>
                <li><a href="install.php" title="Install" class="current meta-act-link meta-install"><span>Install</span></a></li>
                <li><a href="policy.php"  title="Policy"  class="meta-act-link meta-policy"><span>Policy</span></a></li>
                <li><a href="profile.php" title="Profile" class="meta-act-link meta-profile"><span>Profile</span></a></li>
            <ul>
        </div>
    </div>
</body></html>
<?
    mysql_free_result($result);
    mysql_close($link);
?>

<script language="JavaScript" type="text/javascript">
var _startX = 0;			// mouse starting positions
var _startY = 0;
var _offsetX = 0;			// current element offset
var _offsetY = 0;
var _dragElement;			// needs to be passed from OnMouseDown to OnMouseMove
var _oldZIndex = 0;			// we temporarily increase the z-index during drag

InitDragDrop();

function InitDragDrop()
{
	document.onmousedown = OnMouseDown;
	document.onmouseup   = OnMouseUp;
}
function OnMouseDown(e)
{
	if (e.target.className == 'drag')
	{
		// grab the mouse position
		_startX = e.clientX;
		_startY = e.clientY;
        
		// grab the clicked element's position
		_offsetX = ExtractNumber(e.target.style.left);
		_offsetY = ExtractNumber(e.target.style.top);

		// bring the clicked element to the front while it is being dragged
		_oldZIndex = e.target.style.zIndex;
		e.target.style.zIndex = 10000;

		// we need to access the element in OnMouseMove
		_dragElement = e.target;

		// tell our code to start moving the element with the mouse
		document.onmousemove = OnMouseMove;

		// cancel out any text selections
		document.body.focus();

		// prevent text selection in IE
		document.onselectstart = function () { return false; };
        
		// prevent IE from trying to drag an image
		e.target.ondragstart = function() { return false; };

		// prevent text selection (except IE)
		return false;
	}
}

function ExtractNumber(value)
{
	var n = parseInt(value);
	return n == null || isNaN(n) ? 0 : n;
}

function OnMouseMove(e)
{
	_dragElement.style.left = (_offsetX + e.clientX - _startX) + 'px';
	_dragElement.style.top  = (_offsetY + e.clientY - _startY) + 'px';
}

function OnMouseUp(e)
{
	if (_dragElement != null)
	{
		_dragElement.style.zIndex = _oldZIndex;

		// we're done with these events until the next OnMouseDown
		document.onmousemove = null;
		document.onselectstart = null;
		_dragElement.ondragstart = null;

		// this is how we know we're not dragging
		_dragElement = null;
        
        document.moveNodeForm.node_id.value = e.target.value;
        document.moveNodeForm.x.value = _offsetY + e.clientY - _startY;
        document.moveNodeForm.y.value = _offsetX + e.clientX - _startX;
	}
}
</script>