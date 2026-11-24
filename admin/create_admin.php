
<?php

require_once __DIR__ . "/../includes/db.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    if (
        $name === "" ||
        !filter_var($email, FILTER_VALIDATE_EMAIL) ||
        strlen($password) < 12
    ) {
        $message = "Enter a valid name, email, and password of at least 12 characters.";
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

            $message = "Admin account created successfully. Delete this setup file now.";

            $stmt->close();

        } catch (mysqli_sql_exception $e) {
            $message = "Could not create account. This email may already exist.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Admin</title>
</head>
<body>

<h2>Create Golden Bite Admin</h2>

<p><?= htmlspecialchars($message) ?></p>

<form method="POST">

    <label>Admin Name</label><br>
    <input type="text" name="name" required><br><br>

    <label>Email</label><br>
    <input type="email" name="email" required><br><br>

    <label>Password (minimum 12 characters)</label><br>
    <input type="password" name="password" minlength="12" required><br><br>

    <button type="submit">Create Admin Account</button>

</form>

</body>
</html>
