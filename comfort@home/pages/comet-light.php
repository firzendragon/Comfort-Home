<?php
$room = $_GET["room"];
if($room=='CSIE_R513') $node_id = '0001';
else                   $node_id = '0002';

require('database/appvars.php');
$link = mysql_connect(DB_HOST, DB_USER, DB_PASSWORD) or die("NO CONNECT!!");
mysql_query("SET NAMES 'utf8'");
mysql_select_db(DB_NAME) or die("OPEN FAILED!!");

$sql = "select value from data where node_id = '$node_id' and sensor_name = 'light' order by context_id desc limit 1";
$result = mysql_query($sql, $link) or die("EXECUTE SQL FAILED!!");
$row = mysql_fetch_assoc($result) or die("READ DATA FAILED");
echo "<font size=50 face='Impact'>".intval($row["value"])."</font> <font style='font-size:22px;'>lm</font>";

mysql_free_result($result);
mysql_close($link);
?>