
<?php

$isHttps = (
    isset($_SERVER["HTTPS"]) &&
    $_SERVER["HTTPS"] !== "off"
);

ini_set("session.use_strict_mode", "1");

session_set_cookie_params([
    "lifetime" => 0,
    "path" => "/",
    "domain" => "",
    "secure" => $isHttps,
    "httponly" => true,
    "samesite" => "Lax"
]);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
