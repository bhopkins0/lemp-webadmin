<?php
session_start();

function isValidDomain($domain) {
    include '/var/www/config.php';
    if (!preg_match("/^(?!\-)(?:(?:[a-zA-Z\d][a-zA-Z\d\-]{0,61})?[a-zA-Z\d]\.){1,126}(?!\d+)[a-zA-Z\d]{1,63}$/", $domain) || strlen($domain) > 253) {
        return false;
    } else {
        $conn = mysqli_connect($dbservername, $dbusername, $dbpassword, $dbname);
        if (!$conn) {
            return false;
        }
        $sql = "SELECT url FROM websites WHERE url='$domain'";
        $result = mysqli_query($conn, $sql);
        if (mysqli_num_rows($result) > 0) {
            // Domain already in use
            return false;
        }
        mysqli_close($conn);
    }
    return true;
}
function addDomainToDatabase($name, $domain) {
    include '/var/www/config.php';

    // 0 - Success
    // 1 - MySQL error
    // 2 - Invalid name/name already used
    // 3 - Invalid domain name/domain already used

    if (preg_match("/[^a-z_\-0-9]/i", $name) || strlen($name) < 2) {
        return 2;
    }


    $conn = mysqli_connect($dbservername, $dbusername, $dbpassword, $dbname);
    if (!$conn) {
        return 1;
    }
    $sql = "SELECT name FROM websites WHERE name='$name'";
    $result = mysqli_query($conn, $sql);
    if (mysqli_num_rows($result) > 0) {
        return 2;
    }
    mysqli_close($conn);

    if (isValidDomain($domain)) {
        $time = time();
        $conn = new mysqli($dbservername, $dbusername, $dbpassword, $dbname);
        if ($conn->connect_error) {
            return 1;
        }

        $sql = "INSERT INTO websites (name, url, creation_time)
        VALUES ('$name', '$domain', '$time')";

        if ($conn->query($sql) === TRUE) {
            return 0;
        } else {
            return 1;
        }
        $conn->close();
    }
}

function retrieveWebsites() {
    include '/var/www/config.php';
    $conn = new mysqli($dbservername, $dbusername, $dbpassword, $dbname);
    $sql = "SELECT * FROM websites";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $websites = array();
        while($row = $result->fetch_assoc()) {
            array_push($websites, array($row[name],$row[url], $row[creation_time]));
        }
    }
    mysqli_close($conn);
    return $websites;
}

function retrieveServices() {
    include '/var/www/config.php';
    $services = array();

    // This will need to be changed to support PHP versions other than 7.4
    // May move away from shell_exec if I can find an alternative
    if (strstr(shell_exec("service php7.4-fpm status"), 'Active: active (running)')) {
        $phpstatus = "<p class='text-success'>Online</p>";
    } else {
        $phpstatus = "<p class='text-danger'>Offline</p>";
    }

    if (strstr(shell_exec("service nginx status"), 'Active: active (running)')) {
        $nginxstatus = "<p class='text-success'>Online</p>";
    } else {
        $nginxstatus = "<p class='text-danger'>Offline</p>";
    }

    if (strstr(shell_exec("service mysql status"), 'Active: active (running)')) {
        $mysqlstatus = "<p class='text-success'>Online</p>";
    } else {
        $mysqlstatus = "<p class='text-danger'>Offline</p>";
    }


    array_push($services, array("PHP",$phpstatus, $phpversion));
    array_push($services, array("nginx",$nginxstatus, $nginxversion));
    array_push($services, array("MySQL",$mysqlstatus, $mysqlversion));
    array_push($services, array("LEMP Manager","<p class='text-success'>Online</p>", $managerversion));
    return $services;
}

function authenticateUser($username, $password) {
    include '/var/www/config.php';

    if (!preg_match('/^[A-Za-z0-9]+$/', $username) || strlen($username) > 32) {
        return false;
    }
    if (strlen($password) > 50 || strlen($password) < 8) {
        return false;
    }

    $username = strtolower($username);
    $conn = new mysqli($dbservername, $dbusername, $dbpassword, $dbname);
    $sql = "SELECT password FROM accounts WHERE username='$username'";
    $result = $conn->query($sql);
    if (mysqli_num_rows($result) > 0) {
        while($row = $result->fetch_assoc()) {
            if (password_verify($password, $row["password"])) {
                $_SESSION["auth"]  = true;
                $_SESSION["user"]  = $username;
                $_SESSION["key"] = random_int(1,9999999999); // Idea here is to prevent potential CSRF attack
                return true;
            } else {
                return false;
            }
        }
    }
    mysqli_close($conn);
}

function logoutUser($key) {
    // 0 - Success
    // 1 - Invalid Key

    if ($key == $_SESSION['key']) {
        unset($_SESSION['auth']);
        unset($_SESSION['user']);
        unset($_SESSION['key']);
        session_destroy();
        session_write_close();
        setcookie(session_name(),'',0,'/');
        session_regenerate_id(true);
        return 0;
    } else {
        return 1;
    }
}

function isAuthenticated(): bool {
    if ($_SESSION['auth']) {
        return true;
    } else {
        return false;
    }
}


