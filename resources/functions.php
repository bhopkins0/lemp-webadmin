<?php
session_start();

function isValidDomain($domain) {
    include '/var/www/config.php';
    if (!preg_match("/^(?!\-)(?:(?:[a-zA-Z\d][a-zA-Z\d\-]{0,61})?[a-zA-Z\d]\.){1,126}(?!\d+)[a-zA-Z\d]{1,63}$/", $domain) || strlen($domain) > 253) {
        // Regex for domain - this is not perfect
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

function authenticateUser($username, $password, $confirmpassword) {
    include '/var/www/config.php';

    // 0 - Success
    // 1 - Passwords do not match
    // 2 - Invalid username or password

    if ($password !== $confirmpassword) {
        return 1;
    }
    if (preg_match('/[^a-z_\-0-9]/i', $username) || strlen($username) > 32) {
        return 2;
    }
    if (strlen($password) > 50 || strlen($password) < 8) {
        return 2;
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
                return 0;
            } else {
                return 2;
            }
        }
    }
    mysqli_close($conn);
}
