<?php
session_start();
include 'resources/functions.php';

if (!isAuthenticated()) {
    header("Location: /");
    die();
}

?>

<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Information</title>
    <link rel="stylesheet" href="resources/bootstrap.min.css">
</head>
<body class="bg-dark">
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarToggler" aria-controls="navbarToggler" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarToggler">
            <a class="navbar-brand" href="#">LEMP Manager</a>
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link" href="home.php">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="webmanager.php">Website Manager</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" aria-current="page" href="sysinfo.php">System Information</a>
                </li>

            </ul>
            <ul class="navbar-nav mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link" href="webadmin_manager.php">WebAdmin Manager</a>
                </li>
            </ul>

        </div>
    </div>
</nav>
<div class="mt-4 text-white d-flex align-items-center justify-content-center">
    <?php
    echo '<div class="mt-5 d-flex align-items-center justify-content-center"><h1>System Information</h1></div></div>';
    ?>
    <div class='p-1 mt-5 d-flex align-items-center justify-content-center'>
        <div class="table-responsive">
            <table class="table table-dark">
                <thead>
                <tr>
                    <th scope="col">Service</th>
                    <th scope="col">Status</th>
                    <th scope="col">Version</th>
                </tr>
                </thead>
                <tbody>
                <?php
                // You might be thinking, if any of these services go down, I won't be able to view this webpage
                // Why would I need this table anyways?
                // That is a fair question
                $services = retrieveServices();
                foreach ($services as $service) {
                    echo '<tr><th scope="row">'.$service[0].'</th>';
                    echo '<td>'.$service[1].'</td>';
                    echo '<td>'.$service[2].'</td>';
                }
                ?>
                </tbody>
            </table>
        </div></div></div>
<?php
$key = $_SESSION["key"];
echo <<<EOL
<div class="p-4 mt-2 d-flex align-items-center justify-content-center">
<form method="post" action="index.php" class="col-sm-9 col-md-6 col-lg-8">
<input type="hidden" id="lo" name="lo" value="$key">
<input class="btn btn-outline-danger w-100" type="submit" value="Logout">
</form>
</div>
EOL;?>
<script src="resources/bootstrap.bundle.min.js"></script>
</body>
</html>
