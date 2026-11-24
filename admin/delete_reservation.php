
<?php

require_once __DIR__ . "/auth.php";
require_once __DIR__ . "/../includes/db.php";

// Allow POST requests only
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    exit("Method not allowed.");
}

// Verify CSRF security token
$token = $_POST["csrf_token"] ?? "";

if (
    empty($_SESSION["csrf_token"]) ||
    !hash_equals($_SESSION["csrf_token"], $token)
) {
    http_response_code(403);
    exit("Invalid security token.");
}

// Validate reservation ID
$id = filter_input(INPUT_POST, "id", FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    http_response_code(400);
    exit("Invalid reservation ID.");
}

// Delete only the selected reservation
$stmt = $conn->prepare(
    "DELETE FROM reservations WHERE id = ?"
);

$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->close();

// Return to dashboard
header("Location: index.php");
exit;
