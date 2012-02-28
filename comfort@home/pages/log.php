<?
    if($_POST["checkpwd"]) {
        if($_POST["account"] == 'installer' and $_POST["password"] == 'installer') {
            setcookie("pass","TRUE");
        }
    }
    if($_POST["logout"]) {
        setcookie("pass","");
    }
    header("location:index.php");
    exit();
?>