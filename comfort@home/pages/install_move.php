<?
    require('database/appvars.php');
    $link = mysql_connect(DB_HOST, DB_USER, DB_PASSWORD) or die("NO CONNECT!!");
    mysql_query("SET NAMES 'utf8'");
    mysql_select_db(DB_NAME) or die("OPEN FAILED!!");
    
    $node_id   = $_POST["node"];
    $positionX = $_POST["x"];
    $positionY = $_POST["y"];
    
    $sql = "update profile_node set positionX = '$positionX', positionY = '$positionY' where node_id = '$node_id'";
    mysql_query($sql, $link) or die("EXECUTE SQL FAILED!!");
?>