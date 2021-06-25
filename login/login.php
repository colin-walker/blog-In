<?php
// Initialize the session
session_start();

define('APP_RAN', '');

// Include config file
require_once "../config.php";

$authsql = $connsel->prepare("SELECT Option_Value FROM " . OPTIONS . " WHERE Option_Name = 'Auth' ");
$authsql->execute();
$authresult = mysqli_stmt_get_result($authsql);
$row = $authresult->fetch_assoc();
$dbauth = $row["Option_Value"];
$authsql->close();

if ($_SESSION['auth'] == $dbauth) {
    header("location: " . BASE_URL);
    exit;
}


// Processing form data when form is submitted

if($_SERVER["REQUEST_METHOD"] == "POST"){
    $username = $connsel->real_escape_string(trim($_POST["username"]));
    $password = $connsel->real_escape_string(md5(trim($_POST["password"])));

    $sql = $connsel->prepare("SELECT Option_Value FROM " . OPTIONS . " WHERE Option_Name = 'Login' ");
    $sql->execute();
    $sql_result = mysqli_stmt_get_result($sql);
    $row = $sql_result->fetch_assoc();
    $login = $row["Option_Value"];
    $sql->close();
    //echo $login;
    
    $sql = $connsel->prepare("SELECT Option_Value FROM " . OPTIONS . " WHERE Option_Name = 'Password' ");
    $sql->execute();
    $sql_result = mysqli_stmt_get_result($sql);
    $row = $sql_result->fetch_assoc();
    $pass = $row["Option_Value"];
    $sql->close();
    //echo $pass;

        if($username == $login && $password == $pass) {
            // Password is correct, so start a new session
            session_start();

            $hash = password_hash($password, PASSWORD_DEFAULT);
            //echo $hash;

            $hash_sql = $conn->prepare("UPDATE " . OPTIONS . " SET Option_Value=? WHERE Option_Name = 'Auth' ");
            $hash_sql->bind_param("s", $hash);
            $hash_sql->execute();
            $hash_sql->close();
            
            $_SESSION['loggedin'] = 'true';
            $_SESSION['auth'] = $hash;
            
            $session_id = session_id();
                        
            if ($_GET['return'] == 'muse') {
                header("location: https://colinwalker.blog/muse-letter/");
                exit;
            } else {
                header("location: " . BASE_URL . "");
                exit;
            }
        } else {
            // Display an error message if password is not valid
            $password_err = "The password you entered was not valid.";
        }

}
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta name="viewport" content="width=device-width, initial-scale=1">
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="../style.css">
    <style type="text/css">
        .wrapper{ width: 350px; padding: 20px; }
    </style>
</head>
<body>
    <div class="wrapper">
        <h2 class="titleSpan">Login</h2>
        <form id="login_form" action="" method="post">
            <div>
                <label>Username</label>
                <input type="text" name="username" class="form-control" value="">
            </div>
            <div>
                <label>Password</label>
                <input type="password" name="password" class="form-control">
            </div>
            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="Login">
            </div>
        </form>
    </div>

</body>
</html>
