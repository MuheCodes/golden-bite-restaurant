
<?php

require_once __DIR__ . "/../includes/session.php";

// Check login
if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

// Session security limits
$idleLimit = 1200;       // 20 minutes
$maximumLifetime = 28800; // 8 hours

$now = time();

$loginTime = $_SESSION["admin_login_time"] ?? $now;
$lastActivity = $_SESSION["admin_last_activity"] ?? $now;

// Check session expiration
if (
    ($now - $lastActivity) > $idleLimit ||
    ($now - $loginTime) > $maximumLifetime
) {
    $_SESSION = [];

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

    session_destroy();

    header("Location: login.php?expired=1");
    exit;
}

// Update last activity
$_SESSION["admin_last_activity"] = $now;
