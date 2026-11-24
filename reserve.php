
<?php

require_once __DIR__ . "/includes/db.php";

date_default_timezone_set("Africa/Addis_Ababa");

// Public restaurant homepage
$home = "/golden-bite-restaurant/";

// Accept POST requests only
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: " . $home);
    exit;
}

// Safely collect form data
$getPostString = static function ($key) {
    $value = $_POST[$key] ?? "";
    return is_string($value) ? trim($value) : "";
};

$name = $getPostString("customer_name");
$email = $getPostString("email");
$phone = $getPostString("phone");
$date = $getPostString("reservation_date");
$time = $getPostString("reservation_time");
$message = $getPostString("message");

$guests = filter_var(
    $_POST["guests"] ?? null,
    FILTER_VALIDATE_INT
);

$errors = [];

// Validate name
$nameLength = preg_match_all('/./us', $name);

if (
    $name === "" ||
    $nameLength === false ||
    $nameLength < 2 ||
    $nameLength > 100 ||
    !preg_match("/^[\p{L}\p{M}\s'-]+$/u", $name)
) {
    $errors[] = "Please enter a valid name.";
}

// Validate email
if (
    !filter_var($email, FILTER_VALIDATE_EMAIL) ||
    strlen($email) > 150
) {
    $errors[] = "Please enter a valid email address.";
}

// Validate Ethiopian phone
if (!preg_match('/^(0[79][0-9]{8}|\+251[79][0-9]{8})$/', $phone)) {
    $errors[] = "Please enter a valid Ethiopian mobile number.";
}

// Validate date
$dateObject = DateTimeImmutable::createFromFormat("!Y-m-d", $date);
$dateErrors = DateTimeImmutable::getLastErrors();

if (
    !$dateObject ||
    ($dateErrors !== false &&
        ($dateErrors["warning_count"] > 0 ||
         $dateErrors["error_count"] > 0)) ||
    $dateObject->format("Y-m-d") !== $date
) {
    $errors[] = "Please enter a valid reservation date.";
} elseif ($dateObject < new DateTimeImmutable("today")) {
    $errors[] = "Reservation date cannot be in the past.";
}

// Validate time
$timeObject = DateTimeImmutable::createFromFormat("!H:i", $time);
$timeErrors = DateTimeImmutable::getLastErrors();

if (
    !$timeObject ||
    ($timeErrors !== false &&
        ($timeErrors["warning_count"] > 0 ||
         $timeErrors["error_count"] > 0)) ||
    $timeObject->format("H:i") !== $time
) {
    $errors[] = "Please enter a valid reservation time.";
} else {
    if ($time < "09:00" || $time > "22:00") {
        $errors[] = "Reservations are available between 9:00 AM and 10:00 PM.";
    }

    if (
        isset($dateObject) &&
        $dateObject->format("Y-m-d") === date("Y-m-d") &&
        $time <= date("H:i")
    ) {
        $errors[] = "Please choose a future reservation time.";
    }
}

// Validate guests
if ($guests === false || $guests < 1 || $guests > 8) {
    $errors[] = "Guests must be between 1 and 8.";
}

// Validate message
$messageLength = preg_match_all('/./us', $message);

if ($messageLength === false || $messageLength > 2000) {
    $errors[] = "Your message is too long.";
}

// Display validation errors
if (!empty($errors)) {
    http_response_code(400);

    echo "<h2>Please correct these errors:</h2><ul>";

    foreach ($errors as $error) {
        echo "<li>" .
            htmlspecialchars($error, ENT_QUOTES, "UTF-8") .
            "</li>";
    }

    echo "</ul>";
    echo "<a href='" . $home . "'>Return to Golden Bite</a>";

    exit;
}

// Save reservation to database
try {

    $sql = "INSERT INTO reservations
        (
            customer_name,
            email,
            phone,
            reservation_date,
            reservation_time,
            guests,
            message
        )
        VALUES (?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);

    $stmt->bind_param(
        "sssssis",
        $name,
        $email,
        $phone,
        $date,
        $time,
        $guests,
        $message
    );

    $stmt->execute();
    $stmt->close();

    echo "<h1>Reservation received!</h1>";

    echo "<p>Thank you, " .
        htmlspecialchars($name, ENT_QUOTES, "UTF-8") .
        "!</p>";

    echo "<p>Your reservation has been saved successfully.</p>";
    echo "<p>Status: Pending confirmation</p>";

    echo "<a href='" . $home . "'>Return to Golden Bite</a>";

} catch (mysqli_sql_exception $e) {

    error_log($e->getMessage());

    http_response_code(500);

    echo "<h2>Reservation could not be saved.</h2>";
    echo "<p>Please try again later.</p>";

    echo "<a href='" . $home . "'>Return to Golden Bite</a>";
}

?>
