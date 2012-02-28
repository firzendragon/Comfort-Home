<?
$room = $_GET["room"];

require('database/appvars.php');
$link = mysql_connect(DB_HOST, DB_USER, DB_PASSWORD) or die("NO CONNECT!!");
mysql_query("SET NAMES 'utf8'");
mysql_select_db(DB_NAME) or die("OPEN FAILED!!");

$sql = "select * from profile_node where room = '$room'";
$result = mysql_query($sql, $link) or die("EXECUTE SQL FAILED!!");
for ($i=0; $i < mysql_num_rows($result); $i++) {
    $row = mysql_fetch_assoc($result) or die("READ DATA FAILED");
    $actuators = explode(",",$row["actuator"]);
    
    $node_id = $row["node_id"];
    $top     = $row["positionX"]-50;
    $left    = $row["positionY"]-18;

    if($row["actuator"]) {
        for ($j=0; $j < count($actuators); $j++) {
            // get actuator name
            $temp = explode(":",$actuators[$j]);
            $actuator_id = $temp[0];
            $sql = "select actuator_name from profile_actuator where actuator_id = '$actuator_id'";
            $result3 = mysql_query($sql, $link) or die("EXECUTE SQL FAILED!!");
            $row3 = mysql_fetch_assoc($result3) or die("READ DATA FAILED");
            $actuator_name = $row3["actuator_name"];
            
            // get actuator state
            $temp = explode(".",$temp[1]);
            $pin  = $temp[1];
            $top = $top + 46;
            
            echo "<div style='position:absolute; TOP:".$top."px; LEFT:".$left."px;'>";
            
            // find the last time turned-on
            $sql = "select context_id from log_instruction where instruction = '$node_id write $pin:1 ' order by context_id desc limit 1";
            $result2 = mysql_query($sql, $link) or die("EXECUTE SQL FAILED!!");
            $row2 = mysql_fetch_assoc($result2) or die("READ DATA FAILED");
            $turn_on = $row2["context_id"];
            
            // find the last time turned-off
            $sql = "select context_id from log_instruction where instruction = '$node_id write $pin:0 ' order by context_id desc limit 1";
            $result2 = mysql_query($sql, $link) or die("EXECUTE SQL FAILED!!");
            $row2 = mysql_fetch_assoc($result2) or die("READ DATA FAILED");
            $turn_off = $row2["context_id"];
            
            // compare
            if($turn_on > $turn_off) {
                echo "<Button name=instruction value='$node_id write $pin:0 ' onclick='control_click(this.value); return false;' style='cursor: pointer;'>";
                echo "<img src='../images/device-$actuator_name-on.jpg' title='Turn off $actuator_name' WIDTH=40 HEIGHT=40/></button>";
            }
            else if($turn_off > $turn_on) {
                echo "<Button name=instruction value='$node_id write $pin:1 ' onclick='control_click(this.value); return false;' style='cursor: pointer;'>";
                echo "<img src='../images/device-$actuator_name-off.jpg' title='Turn on $actuator_name' WIDTH=40 HEIGHT=40/></button>";
            }
            echo "</div>";
        }
    }
}
mysql_free_result($result);
mysql_close($link);
?>