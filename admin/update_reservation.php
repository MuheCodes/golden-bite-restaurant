
<?php

require_once __DIR__ . "/auth.php";
require_once __DIR__ . "/../includes/db.php";

// Accept POST requests only
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    exit("Method not allowed.");
}

// Verify CSRF token
$token = $_POST["csrf_token"] ?? "";

if (
    empty($_SESSION["csrf_token"]) ||
    !hash_equals($_SESSION["csrf_token"], $token)
) {
    http_response_code(403);
    exit("Invalid security token.");
}

// Get reservation ID and requested status
$id = filter_input(INPUT_POST, "id", FILTER_VALIDATE_INT);
$status = $_POST["status"] ?? "";

// Allow only these statuses
$allowedStatuses = ["Confirmed", "Cancelled"];

if (!$id || !in_array($status, $allowedStatuses, true)) {
    http_response_code(400);
    exit("Invalid reservation information.");
}

// Update reservation safely
$stmt = $conn->prepare(
    "UPDATE reservations SET status = ? WHERE id = ?"
);

$stmt->bind_param("si", $status, $id);
$stmt->execute();

$stmt->close();

// Return to dashboard
header("Location: index.php");
exit;
