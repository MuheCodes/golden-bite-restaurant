
<?php

require_once __DIR__ . "/../includes/session.php";
require_once __DIR__ . "/../includes/db.php";

$error = "";

$maxAttempts = 5;
$lockoutSeconds = 900;

// Initialize login protection
if (!isset($_SESSION["admin_login_attempts"])) {
    $_SESSION["admin_login_attempts"] = 0;
}

if (!isset($_SESSION["admin_login_lockout_until"])) {
    $_SESSION["admin_login_lockout_until"] = 0;
}

// Check whether login is temporarily blocked
if (time() < $_SESSION["admin_login_lockout_until"]) {
    $error = "Too many failed attempts. Please try again in 15 minutes.";
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && $error === "") {

    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";
$stmt = $conn->prepare(
    "SELECT id, name, email, password, role
     FROM admins
     WHERE email = ?"
);

    $stmt->bind_param("s", $email);
    $stmt->execute();

    $admin = $stmt->get_result()->fetch_assoc();

    if ($admin && password_verify($password, $admin["password"])) {

        // Regenerate session ID after successful login
        session_regenerate_id(true);

        $_SESSION["admin_id"] = $admin["id"];
        $_SESSION["admin_role"] = $admin["role"];
        $_SESSION["admin_name"] = $admin["name"];
        $_SESSION["admin_login_time"] = time();
$_SESSION["admin_last_activity"] = time();

        // Reset failed attempts
        unset($_SESSION["admin_login_attempts"]);
        unset($_SESSION["admin_login_lockout_until"]);

        header("Location: index.php");
        exit;

    } else {

        $_SESSION["admin_login_attempts"]++;

        if ($_SESSION["admin_login_attempts"] >= $maxAttempts) {

            $_SESSION["admin_login_lockout_until"] =
                time() + $lockoutSeconds;

            $error = "Too many failed attempts. Please try again in 15 minutes.";

        } else {
            $error = "Invalid email or password.";
        }
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Admin Login - Golden Bite</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #222;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }

        .login-box {
            background: white;
            padding: 35px;
            border-radius: 12px;
            width: 320px;
        }

        input {
            width: 100%;
            padding: 12px;
            margin: 10px 0 18px;
            box-sizing: border-box;
        }

        button {
            width: 100%;
            padding: 13px;
            background: #b8860b;
            color: white;
            border: none;
            cursor: pointer;
        }

        .error {
            color: red;
        }
    </style>
</head>

<body>

<div class="login-box">

    <h2>Golden Bite Admin</h2>
    <p>Sign in to manage reservations.</p>

    <?php if ($error): ?>
        <p class="error">
            <?= htmlspecialchars($error) ?>
        </p>
    <?php endif; ?>

    <form method="POST">

        <label>Email</label>
        <input
            type="email"
            name="email"
            maxlength="150"
            autocomplete="username"
            required
        >

        <label>Password</label>
        <input
            type="password"
            name="password"
            autocomplete="current-password"
            required
        >

        <button type="submit">Login</button>

    </form>

</div>

</body>
</html>
