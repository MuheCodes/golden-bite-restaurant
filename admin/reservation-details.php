
<?php

require_once __DIR__ . "/auth.php";
require_once __DIR__ . "/../includes/db.php";

$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    http_response_code(400);
    exit("Invalid reservation ID.");
}

$stmt = $conn->prepare(
    "SELECT * FROM reservations WHERE id = ?"
);

$stmt->bind_param("i", $id);
$stmt->execute();

$result = $stmt->get_result();
$reservation = $result->fetch_assoc();
$stmt->close();

if (!$reservation) {
    http_response_code(404);
    exit("Reservation not found.");
}

if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

$status = $reservation["status"];
$validStatuses = ["Pending", "Confirmed", "Cancelled"];

if (!in_array($status, $validStatuses, true)) {
    $status = "Pending";
}

$name = htmlspecialchars($reservation["customer_name"]);
$email = htmlspecialchars($reservation["email"]);
$phone = htmlspecialchars($reservation["phone"]);
$date = htmlspecialchars($reservation["reservation_date"]);
$time = htmlspecialchars($reservation["reservation_time"]);
$message = htmlspecialchars(
    $reservation["message"] ?: "No additional message was provided."
);

$formattedDate = date(
    "F j, Y",
    strtotime($reservation["reservation_date"])
);

$formattedTime = date(
    "g:i A",
    strtotime($reservation["reservation_time"])
);

$initial = strtoupper(substr($reservation["customer_name"], 0, 1));

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Reservation Details | Golden Bite</title>

<style>

:root {
    --gold: #c69b35;
    --gold-light: #f8f0dc;
    --dark: #191c24;
    --background: #f5f6f8;
    --white: #ffffff;
    --text: #292d37;
    --muted: #858b98;
    --border: #eceef2;
    --green: #198754;
    --red: #dc3545;
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: "Segoe UI", Arial, sans-serif;
    background: var(--background);
    color: var(--text);
    min-height: 100vh;
}

/* Top navigation */

.topbar {
    background: var(--dark);
    color: white;
    padding: 20px 6%;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.brand {
    color: var(--gold);
    font-size: 23px;
    font-weight: 850;
    letter-spacing: 1px;
}

.brand span {
    color: white;
}

.admin-label {
    font-size: 12px;
    color: #c1c5ce;
}

/* Main container */

.container {
    max-width: 1250px;
    margin: 0 auto;
    padding: 38px 25px;
}

/* Page heading */

.page-heading {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 20px;
    margin-bottom: 30px;
}

.page-heading h1 {
    font-size: 29px;
    font-weight: 800;
    letter-spacing: -0.8px;
}

.page-heading p {
    color: var(--muted);
    margin-top: 7px;
    font-size: 13px;
}

.back-link {
    display: inline-block;
    padding: 12px 17px;
    background: white;
    color: #9b741f;
    text-decoration: none;
    border: 1px solid var(--border);
    border-radius: 9px;
    font-size: 13px;
    font-weight: 700;
    transition: .2s;
}

.back-link:hover {
    background: var(--gold-light);
}

/* Layout */

.details-layout {
    display: grid;
    grid-template-columns: minmax(0, 1.7fr) minmax(280px, 0.8fr);
    gap: 24px;
    align-items: start;
}

/* Cards */

.panel {
    background: white;
    border: 1px solid var(--border);
    border-radius: 16px;
    padding: 27px;
    box-shadow: 0 5px 22px rgba(0,0,0,.025);
    margin-bottom: 22px;
}

.panel-title {
    font-size: 17px;
    font-weight: 750;
    margin-bottom: 23px;
}

/* Customer profile */

.customer-profile {
    display: flex;
    align-items: center;
    gap: 17px;
    padding-bottom: 25px;
    border-bottom: 1px solid var(--border);
    margin-bottom: 25px;
}

.avatar {
    width: 65px;
    height: 65px;
    background: var(--gold-light);
    color: #a47b20;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 17px;
    font-size: 25px;
    font-weight: 800;
}

.customer-profile h2 {
    font-size: 20px;
    margin-bottom: 6px;
}

.customer-profile p {
    color: var(--muted);
    font-size: 13px;
}

/* Information grid */

.details-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 25px;
}

.detail-item {
    padding-bottom: 17px;
    border-bottom: 1px solid var(--border);
    min-width: 0;
}

.detail-item small {
    display: block;
    color: var(--muted);
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: .8px;
    font-weight: 700;
    margin-bottom: 9px;
}

.detail-item strong {
    font-size: 14px;
    overflow-wrap: anywhere;
}

.detail-item a {
    color: #a47b20;
    text-decoration: none;
}

.detail-item a:hover {
    text-decoration: underline;
}

/* Special request */

.message-box {
    background: #fbf9f4;
    border: 1px solid #f0e7d2;
    padding: 20px;
    border-radius: 11px;
    margin-top: 25px;
}

.message-box h3 {
    font-size: 14px;
    margin-bottom: 10px;
}

.message-box p {
    color: #686d77;
    line-height: 1.8;
    font-size: 13px;
    overflow-wrap: anywhere;
}

/* Status */

.status {
    display: inline-block;
    padding: 8px 14px;
    border-radius: 30px;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: .4px;
}

.status.Pending {
    color: #a66b13;
    background: #fff2d8;
}

.status.Confirmed {
    color: #167747;
    background: #d9f5e5;
}

.status.Cancelled {
    color: #c42f3d;
    background: #ffe0e0;
}

.reservation-number {
    color: var(--muted);
    font-size: 12px;
    margin-bottom: 13px;
}

/* Management panel */

.management-panel {
    position: sticky;
    top: 25px;
}

.management-panel h2 {
    font-size: 18px;
    margin-bottom: 12px;
}

.management-panel > p {
    color: var(--muted);
    font-size: 13px;
    line-height: 1.7;
    margin-bottom: 22px;
}

.current-status {
    background: #f8f9fb;
    border: 1px solid var(--border);
    padding: 17px;
    border-radius: 11px;
    margin-bottom: 22px;
}

.current-status small {
    display: block;
    color: var(--muted);
    font-size: 11px;
    margin-bottom: 11px;
    font-weight: 700;
    text-transform: uppercase;
}

/* Buttons */

.action-form {
    margin-top: 11px;
}

.action-button {
    display: block;
    width: 100%;
    padding: 13px 15px;
    border: none;
    border-radius: 9px;
    font-size: 13px;
    font-weight: 750;
    cursor: pointer;
    transition: .2s;
}
.action-button.delete {
    background: #dc3545;
    color: white;
    border: none;
}
.action-button.delete:hover {
    background: #b02a37;
}
.action-button.delete:hover {
    background: #b02a37;
}

.confirm {
    background: var(--green);
    color: white;
}

.cancel {
    background: #fff0f0;
    color: var(--red);
    border: 1px solid #f4d5d8;
}

.action-button:hover {
    transform: translateY(-2px);
    filter: brightness(.96);
}

.contact-link {
    display: block;
    text-align: center;
    padding: 12px;
    border: 1px solid var(--border);
    color: #555c68;
    text-decoration: none;
    border-radius: 9px;
    font-size: 12px;
    font-weight: 700;
    margin-top: 12px;
}

.contact-link:hover {
    background: #f8f9fb;
}

.no-actions {
    padding: 15px;
    background: #f8f9fb;
    color: var(--muted);
    border-radius: 9px;
    font-size: 12px;
    line-height: 1.6;
}

/* Responsive */

@media(max-width: 850px) {

    .details-layout {
        grid-template-columns: 1fr;
    }

    .management-panel {
        position: static;
    }

}

@media(max-width: 550px) {

    .topbar {
        padding: 17px 20px;
    }

    .brand {
        font-size: 19px;
    }

    .container {
        padding: 25px 14px;
    }

    .page-heading h1 {
        font-size: 23px;
    }

    .panel {
        padding: 20px;
    }

    .details-grid {
        grid-template-columns: 1fr;
        gap: 18px;
    }

    .customer-profile h2 {
        font-size: 17px;
    }

}

</style>

</head>

<body>

<header class="topbar">

    <div class="brand">
        GOLDEN <span>BITE.</span>
    </div>

    <div class="admin-label">
        Management Workspace
    </div>

</header>

<main class="container">

    <div class="page-heading">

        <div>
            <h1>Reservation Details</h1>
            <p>Review customer information and manage this reservation.</p>
        </div>

        <a href="index.php#reservations" class="back-link">
            ← Back to reservations
        </a>

    </div>

    <div class="details-layout">

        <!-- CUSTOMER INFORMATION -->

        <div>

            <section class="panel">

                <p class="reservation-number">
                    RESERVATION #<?= (int)$reservation["id"] ?>
                </p>

                <div class="customer-profile">

                    <div class="avatar">
                        <?= htmlspecialchars($initial) ?>
                    </div>

                    <div>
                        <h2><?= $name ?></h2>
                        <p>Golden Bite customer</p>
                    </div>

                </div>

                <h3 class="panel-title">
                    Customer Information
                </h3>

                <div class="details-grid">

                    <div class="detail-item">
                        <small>Customer name</small>
                        <strong><?= $name ?></strong>
                    </div>

                    <div class="detail-item">
                        <small>Email address</small>
                        <strong>
                            <a href="mailto:<?= $email ?>">
                                <?= $email ?>
                            </a>
                        </strong>
                    </div>

                    <div class="detail-item">
                        <small>Phone number</small>
                        <strong>
                            <a href="tel:<?= $phone ?>">
                                <?= $phone ?>
                            </a>
                        </strong>
                    </div>

                    <div class="detail-item">
                        <small>Number of guests</small>
                        <strong><?= (int)$reservation["guests"] ?> Guests</strong>
                    </div>

                </div>

            </section>

            <section class="panel">

                <h3 class="panel-title">
                    Reservation Schedule
                </h3>

                <div class="details-grid">

                    <div class="detail-item">
                        <small>Reservation date</small>
                        <strong><?= htmlspecialchars($formattedDate) ?></strong>
                    </div>

                    <div class="detail-item">
                        <small>Reservation time</small>
                        <strong><?= htmlspecialchars($formattedTime) ?></strong>
                    </div>

                </div>

                <div class="message-box">

                    <h3>Customer's Special Requests</h3>

                    <p><?= nl2br($message) ?></p>

                </div>

            </section>

        </div>

        <!-- RESERVATION MANAGEMENT -->

        <aside>

            <section class="panel management-panel">

                <h2>Manage Reservation</h2>

                <p>
                    Review the booking status and take action on this
                    customer's reservation.
                </p>

                <div class="current-status">

                    <small>Current reservation status</small>

                    <span class="status <?= htmlspecialchars($status) ?>">
                        <?= htmlspecialchars($status) ?>
                    </span>

                </div>

                <?php if ($status !== "Confirmed"): ?>

                    <form action="update_reservation.php"
                          method="POST"
                          class="action-form">

                        <input type="hidden" name="id"
                               value="<?= (int)$reservation["id"] ?>">

                        <input type="hidden" name="csrf_token"
                               value="<?= htmlspecialchars($_SESSION["csrf_token"]) ?>">

                        <input type="hidden" name="status"
                               value="Confirmed">

                        <button type="submit"
                                class="action-button confirm">
                            ✓ Confirm Reservation
                        </button>

                    </form>

<form action="delete_reservation.php"
      method="POST"
      onsubmit="return confirm('Are you sure you want to permanently delete this reservation?');">

    <input type="hidden" name="id"
           value="<?= (int)$reservation["id"] ?>">

    <input type="hidden" name="csrf_token"
           value="<?= htmlspecialchars($_SESSION["csrf_token"]) ?>">

    <button type="submit" class="action-button delete">
        🗑 Delete Reservation
    </button>

</form>


                <?php endif; ?>

                <?php if ($status !== "Cancelled"): ?>

                    <form action="update_reservation.php"
                          method="POST"
                          class="action-form"
                          onsubmit="return confirm('Are you sure you want to cancel this reservation?');">

                        <input type="hidden" name="id"
                               value="<?= (int)$reservation["id"] ?>">

                        <input type="hidden" name="csrf_token"
                               value="<?= htmlspecialchars($_SESSION["csrf_token"]) ?>">

                        <input type="hidden" name="status"
                               value="Cancelled">

                        <button type="submit"
                                class="action-button cancel">
                            × Cancel Reservation
                        </button>

                    </form>

                <?php endif; ?>

                <?php if ($status === "Confirmed" || $status === "Cancelled"): ?>

                    <div class="no-actions">
                        This reservation is currently
                        <?= htmlspecialchars(strtolower($status)) ?>.
                        You can still review the customer's information.
                    </div>

                <?php endif; ?>

                <a class="contact-link"
                   href="mailto:<?= $email ?>">
                    ✉ Contact Customer
                </a>

                <a class="contact-link"
                   href="tel:<?= $phone ?>">
                    ☎ Call Customer
                </a>

            </section>

        </aside>

    </div>

</main>

</body>
</html>
