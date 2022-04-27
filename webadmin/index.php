<?php
session_start();
include 'resources/functions.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Log out
    if ($_POST["lo"] == $_SESSION['key'] && $_SESSION['auth'] == true) {
        logoutUser($_SESSION['key']);
        header("Location: /");
        die();
    }

    // Authenticate
    if (isset($_POST['username']) && isset($_POST['password'])) {
        if (authenticateUser($_POST['username'], $_POST['password'])) {
            header("Location: home.php");
            die();
        } else {
            $_SESSION['loginerr'] = true;
            header("Location: index.php");
            die();
        }
    }
}

?>

<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nginx Manager</title>
    <link rel="stylesheet" href="resources/bootstrap.min.css">
</head>
<body class="bg-dark">
<div class="mt-4 text-white d-flex align-items-center justify-content-center"><div>
        <h1 class="display-4 text-white">nginx manager</h1>
        <div><form method="post">
                <?php
                if ($_SESSION["loginerr"]) {
                    echo '<div class="alert alert-danger" role="alert">Invalid username or password</div>';
                    unset($_SESSION["loginerr"]);
                }
                ?>
                <div class="form-group">
                    <input type="text" class="form-control" name="username" placeholder="Username">
                </div><br>
                <div class="form-group">
                    <input type="password" class="form-control" name="password" placeholder="Password">
                </div><br>
                <input class="btn btn-primary w-100" type="submit" value="Login">
            </form>
        </div></div></div>
</body>
</html>
