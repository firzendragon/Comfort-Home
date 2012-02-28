<?
    require('database/appvars.php');
    $link = mysql_connect(DB_HOST, DB_USER, DB_PASSWORD) or die("NO CONNECT!!");
    mysql_query("SET NAMES 'utf8'");
    mysql_select_db(DB_NAME) or die("OPEN FAILED!!");

    $room        = $_POST["room"];
    $instruction = $_POST["instruction"];
    
    $sql = "update log_remote set status = 1 where room = '$room' and instruction = '$instruction'";
    mysql_query($sql, $link) or die("EXECUTE SQL FAILED!!");
    $sql = "insert into log_instruction (instruction) values ('$instruction')";
    mysql_query($sql, $link) or die("EXECUTE SQL FAILED!!");
?>