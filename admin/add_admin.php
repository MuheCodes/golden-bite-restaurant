
<?php

require_once __DIR__ . "/auth.php";
require_once __DIR__ . "/../includes/db.php";
// Only the restaurant owner can create administrators
$roleStmt = $conn->prepare(
    "SELECT role FROM admins WHERE id = ?"
);

$roleStmt->bind_param("i", $_SESSION["admin_id"]);
$roleStmt->execute();

$roleResult = $roleStmt->get_result();
$adminAccount = $roleResult->fetch_assoc();

$roleStmt->close();

if (!$adminAccount || $adminAccount["role"] !== "owner") {
    header("Location: index.php?error=access_denied");
    exit;
}

// Generate a security token
if (empty($_SESSION["admin_csrf"])) {
    $_SESSION["admin_csrf"] = bin2hex(random_bytes(32));
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $token = $_POST["csrf_token"] ?? "";

    if (
        !hash_equals($_SESSION["admin_csrf"], $token)
    ) {
        $message = "Security verification failed.";

    } else {

        $name = trim($_POST["name"] ?? "");
        $email = trim($_POST["email"] ?? "");
        $password = $_POST["password"] ?? "";

        if ($name === "" || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = "Please enter a valid name and email.";

        } elseif (strlen($password) < 12) {
            $message = "Password must contain at least 12 characters.";

        } else {

            $hashedPassword = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            try {
                $stmt = $conn->prepare(
                    "INSERT INTO admins (name, email, password)
                     VALUES (?, ?, ?)"
                );

                $stmt->bind_param(
                    "sss",
                    $name,
                    $email,
                    $hashedPassword
                );

                $stmt->execute();
                $stmt->close();

                $message = "New administrator created successfully.";

            } catch (mysqli_sql_exception $e) {
                if ($e->getCode() === 1062) {
                    $message = "This email already belongs to an administrator.";
                } else {
                    error_log($e->getMessage());
                    $message = "Unable to create administrator.";
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Administrator | Golden Bite</title>
</head>

<body>

    <h1>Create Administrator Account</h1>

    <p>
        Logged in as:
        <?= htmlspecialchars($_SESSION["admin_name"]) ?>
    </p>

    <p><?= htmlspecialchars($message) ?></p>

    <form method="POST">

        <input
            type="hidden"
            name="csrf_token"
            value="<?= htmlspecialchars($_SESSION["admin_csrf"]) ?>"
        >

        <label>Administrator Name</label><br>
        <input type="text" name="name" maxlength="100" required>
        <br><br>

        <label>Email Address</label><br>
        <input type="email" name="email" maxlength="150" required>
        <br><br>

        <label>Password (minimum 12 characters)</label><br>
        <input type="password" name="password" minlength="12" required>
        <br><br>

        <button type="submit">Create Administrator</button>

    </form>

    <br>

    <a href="index.php">Back to Dashboard</a>

</body>
</html>
