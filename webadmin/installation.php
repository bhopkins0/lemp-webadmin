<?php
session_start();
include '../config.php';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (!isset($_POST["admin_user"])) {
                $_SESSION["err"] = 8;
                header("Location: installation.php");
                die();
        }
        if (!isset($_POST["admin_pass"])) {
                $_SESSION["err"] = 9;
                header("Location: installation.php");
                die();
        }
        if (!isset($_POST["admin_cpass"]) || $_POST["admin_pass"] !== $_POST["admin_cpass"]) {
                $_SESSION["err"] = 10;
                header("Location: installation.php");
                die();
        }
        if (strlen($_POST["admin_pass"]) > 50 || strlen($_POST["admin_pass"]) < 8) {
                $_SESSION["err"] = 11;
                header("Location: installation.php");
                die();
        }
        if (strlen($_POST["admin_user"]) > 32) {
                $_SESSION["err"] = 12;
                header("Location: installation.php");
                die();
        }
        if (preg_match('/[^a-z_\-0-9]/i', $_POST["admin_user"])) {
                $_SESSION["err"] = 14;
                header("Location: installation.php");
                die();
        }
        $admin_user = strtolower($_POST["admin_user"]);
        $admin_pass = password_hash($_POST["admin_pass"], PASSWORD_BCRYPT);
        $time = time();
        $conn = new mysqli($dbservername, $dbusername, $dbpassword, $dbname);
        if ($conn->connect_error) {
                $_SESSION["err"] = 13;
                $_SESSION["errmsg"] = $conn->connect_error;
                header("Location: installation.php");
                die();
        }

        $sql = "INSERT INTO accounts (username, password, creation_time)
        VALUES ('$admin_user', '$admin_pass', $time)";

        if ($conn->query($sql) === TRUE) {

        } else {
                $_SESSION["err"] = 13;
                $_SESSION["errmsg"] = $conn->error;
                header("Location: installation.php");
                die();
        }

        $conn->close();
        
        unlink("installation.php");
        header("Location: /");
        die();
}

?>

<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Installation</title>
<link rel="stylesheet" href="bootstrap.min.css">
<style>
html,
body {
  height: 100%;
}

body {
  display: flex;
  align-items: center;
  padding-top: 40px;
  padding-bottom: 40px;
  background-color: #f5f5f5;
}

.install {
  width: 100%;
  max-width: 500px;
  margin: auto;
  padding: 15px;
}


</style>
</head>
<body>
<main class="install">
<?php
if ($_SESSION["err"] == 8) {
        echo '<div class="alert alert-danger" role="alert">Error: WebAdmin username blank</div>';
        $_SESSION["err"] = 0;
}
if ($_SESSION["err"] == 9) {
        echo '<div class="alert alert-danger" role="alert">Error: WebAdmin password blank</div>';
        $_SESSION["err"] = 0;
}
if ($_SESSION["err"] == 10) {
        echo '<div class="alert alert-danger" role="alert">Error: WebAdmin passwords do not match</div>';        $_SESSION["err"] = 0;
}
if ($_SESSION["err"] == 11) {
        echo '<div class="alert alert-danger" role="alert">Error: WebAdmin password must be between 8 and 50 characters</div>';
        $_SESSION["err"] = 0;
}
if ($_SESSION["err"] == 12) {
        echo '<div class="alert alert-danger" role="alert">Error: WebAdmin username must be less than 32 characters</div>';
        $_SESSION["err"] = 0;
}
if ($_SESSION["err"] == 13) {
        echo '<div class="alert alert-danger" role="alert">MySQL Error ('.$_SESSION["errmsg"].')</div>';
        $_SESSION["err"] = 0;
}
if ($_SESSION["err"] == 14) {
        echo '<div class="alert alert-danger" role="alert">Error: WebAdmin username must be alphanumeric</div>';
        $_SESSION["err"] = 0;
}
?>
<h1 class="display-6">Installation</h1>
<div>
<form method="post">
<div class="mb-3">
<label for="admin_user">WebAdmin Username - must be less than 32 characters and alphanumeric</label>
<input type="text" class="form-control" name="admin_user" id="admin_user" placeholder="username">
</div>
<div class="mb-3">
<label for="admin_pass">WebAdmin Password - must be between 8 and 50 characters</label>
<input type="password" class="form-control" name="admin_pass" id="admin_pass" placeholder="password">
</div>
<div class="mb-3">
<label for="admin_cpass">Confirm WebAdmin Password</label>
<input type="password" class="form-control" name="admin_cpass" id="admin_cpass" placeholder="password">
</div>
<input class="btn btn-primary w-100" type="submit" value="Install">
</form>
<p class="text-muted text-end">Brent's nginx WebAdmin beta</p>
</div>
</main>
</body>
</html>
