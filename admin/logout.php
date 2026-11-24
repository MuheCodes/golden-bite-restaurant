
<?php

require_once __DIR__ . "/../includes/session.php";

// Remove all session variables
$_SESSION = [];

// Delete the session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();

    setcookie(
        session_name(),
        "",
        [
            "expires" => time() - 42000,
            "path" => $params["path"],
            "domain" => $params["domain"],
            "secure" => $params["secure"],
            "httponly" => $params["httponly"],
            "samesite" => $params["samesite"]
        ]
    );
}

// Destroy the session
session_destroy();

// Return to login page
header("Location: login.php");
exit;
