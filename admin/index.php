
<?php

require_once __DIR__ . "/auth.php";
require_once __DIR__ . "/../includes/db.php";

// CSRF protection
if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

// Get reservation statistics
$total = $conn->query(
    "SELECT COUNT(*) AS total FROM reservations"
)->fetch_assoc()["total"];

$pending = $conn->query(
    "SELECT COUNT(*) AS total FROM reservations WHERE status = 'Pending'"
)->fetch_assoc()["total"];

$confirmed = $conn->query(
    "SELECT COUNT(*) AS total FROM reservations WHERE status = 'Confirmed'"
)->fetch_assoc()["total"];

$cancelled = $conn->query(
    "SELECT COUNT(*) AS total FROM reservations WHERE status = 'Cancelled'"
)->fetch_assoc()["total"];

// Get all reservations
$result = $conn->query(
    "SELECT * FROM reservations ORDER BY created_at DESC"
);

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Golden Bite | Admin Dashboard</title>

<style>

:root {
    --gold: #c69b35;
    --gold-light: #f8f0dc;
    --dark: #17191f;
    --sidebar: #1d2028;
    --background: #f6f7f9;
    --white: #ffffff;
    --text: #282b33;
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
}

/* Main layout */

.dashboard {
    display: flex;
    min-height: 100vh;
}

/* Premium sidebar */

.sidebar {
    width: 260px;
    min-height: 100vh;
    background: linear-gradient(180deg, #191c24 0%, #222631 100%);
    color: #fff;
    padding: 32px 20px;
    flex-shrink: 0;
    transition: all 0.3s ease;
    position: relative;
    box-shadow: 5px 0 25px rgba(0,0,0,0.04);
}

.brand {
    font-size: 25px;
    font-weight: 850;
    color: #d5a83e;
    margin-bottom: 55px;
    padding-left: 12px;
    letter-spacing: 1px;
}

.brand span {
    color: #ffffff;
}

.menu-label {
    color: #777f90;
    font-size: 10px;
    text-transform: uppercase;
    letter-spacing: 2px;
    padding: 0 13px;
    margin-bottom: 17px;
    font-weight: 700;
}

.sidebar nav a {
    display: flex;
    align-items: center;
    color: #b9bfcc;
    text-decoration: none;
    padding: 15px 14px;
    margin-bottom: 9px;
    border-radius: 10px;
    font-size: 13px;
    font-weight: 600;
    transition: all 0.25s ease;
}

.sidebar nav a:hover {
    background: rgba(255,255,255,0.07);
    color: #fff;
    transform: translateX(3px);
}

.sidebar nav a.active {
    background: linear-gradient(100deg, #b98c2d, #d5ad54);
    color: #1d2028;
    border-left: none;
    box-shadow: 0 5px 15px rgba(198,155,53,0.2);
}

.sidebar .logout-link {
    margin-top: 45px;
    color: #ff8585;
}

.sidebar .logout-link:hover {
    background: rgba(220,53,69,0.12);
    color: #ffaaaa;
}



/* Main content */

.main-content {
    flex: 1;
    min-width: 0;
    padding: 35px 38px;
}

/* Premium dashboard header */

.topbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
    margin-bottom: 38px;
    padding-bottom: 25px;
    border-bottom: 1px solid #e9ebef;
}

.topbar h1 {
    font-size: 29px;
    font-weight: 800;
    letter-spacing: -0.8px;
    color: #20232b;
}

.topbar p {
    color: #89909d;
    font-size: 13px;
    margin-top: 9px;
}

.admin-profile {
    display: flex;
    align-items: center;
    gap: 12px;
    background: #fff;
    padding: 12px 19px;
    border-radius: 12px;
    border: 1px solid #eceef2;
    box-shadow: 0 4px 15px rgba(0,0,0,0.035);
    font-weight: 650;
    font-size: 13px;
    color: #333946;
}

.admin-profile span {
    color: #198754;
    font-size: 11px;
}



/* Statistics */

.cards {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 20px;
    margin-bottom: 35px;
}

.card {
    position: relative;
    overflow: hidden;
    background: white;
    padding: 25px;
    border-radius: 15px;
    border: 1px solid var(--border);
    box-shadow: 0 5px 20px rgba(0,0,0,0.025);
    transition: transform 0.25s ease, box-shadow 0.25s ease;
}

.card:hover {
    transform: translateY(-4px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.07);
}

.card h3 {
    font-size: 13px;
    color: var(--muted);
    font-weight: 600;
    margin-bottom: 17px;
}

.card .number {
    font-size: 34px;
    font-weight: 750;
    letter-spacing: -1px;
}

.card.total .number {
    color: var(--gold);
}

.card.pending .number {
    color: #e49b26;
}

.card.confirmed .number {
    color: var(--green);
}

.card.cancelled .number {
    color: var(--red);
}

/* Reservations section */

.section {
    background: white;
    border: 1px solid var(--border);
    border-radius: 16px;
    padding: 28px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.025);
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 18px;
    margin-bottom: 25px;
}

.section-header h2 {
    font-size: 20px;
    font-weight: 700;
}

.filters {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.filters input,
.filters select {
    padding: 12px 14px;
    border: 1px solid var(--border);
    background: #fafbfc;
    border-radius: 9px;
    outline: none;
    font-size: 13px;
    color: var(--text);
    transition: border 0.2s, box-shadow 0.2s;
}

.filters input:focus,
.filters select:focus {
    border-color: var(--gold);
    box-shadow: 0 0 0 3px rgba(198,155,53,0.12);
}

/* Premium reservation table */

.table-wrapper {
    width: 100%;
    overflow-x: auto;
    border: 1px solid #edf0f4;
    border-radius: 12px;
    background: #fff;
}

table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    min-width: 1050px;
    background: #fff;
}

thead th {
    background: #f8f9fb;
    color: #777e8b;
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    font-weight: 700;
    padding: 17px 14px;
    border-bottom: 1px solid #e9ecf1;
    white-space: nowrap;
}

tbody td {
    padding: 17px 14px;
    font-size: 13px;
    color: #454954;
    border-bottom: 1px solid #edf0f4;
    vertical-align: middle;
}

tbody tr {
    transition: background 0.2s ease;
}

tbody tr:hover {
    background: #fffcf5;
}

tbody tr:last-child td {
    border-bottom: none;
}

tbody td a {
    color: #ad7d1e;
    text-decoration: none;
    font-weight: 650;
}

tbody td a:hover {
    color: #805a12;
    text-decoration: underline;
}


/* Status badges */

.status {
    display: inline-block;
    padding: 7px 12px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 700;
    white-space: nowrap;
}

.status.Pending {
    background: #fff2d8;
    color: #a66b13;
}

.status.Confirmed {
    background: #d9f5e5;
    color: #167747;
}

.status.Cancelled {
    background: #ffe0e0;
    color: #c42f3d;
}

/* Action buttons */

.action-form {
    display: inline-block;
    margin: 2px;
}

button {
    padding: 9px 13px;
    border: none;
    border-radius: 7px;
    color: white;
    cursor: pointer;
    font-weight: 600;
    font-size: 11px;
    transition: all 0.2s ease;
}

.confirm-btn {
    background: #198754;
}

.cancel-btn {
    background: #dc3545;
}

.confirm-btn:hover {
    background: #126b40;
    transform: translateY(-1px);
}

.cancel-btn:hover {
    background: #b92c3a;
    transform: translateY(-1px);
}

.no-results {
    text-align: center;
    padding: 35px;
    color: var(--muted);
}

/* Mobile menu button */

.menu-toggle {
    display: none;
    background: var(--dark);
    font-size: 20px;
}

/* Responsive design */

@media (max-width: 1100px) {
    .main-content {
        padding: 28px 22px;
    }

    .cards {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 750px) {
    .dashboard {
        display: block;
    }

    .sidebar {
        width: 100%;
        padding: 17px;
    }

    .brand {
        margin-bottom: 15px;
    }

    .sidebar nav {
        display: none;
    }

    .sidebar.open nav {
        display: block;
    }

    .menu-toggle {
        display: inline-block;
    }

    .main-content {
        padding: 22px 14px;
    }

    .topbar {
        align-items: flex-start;
        flex-wrap: wrap;
    }

    .topbar h1 {
        font-size: 22px;
    }

    .admin-profile {
        font-size: 12px;
    }

    .cards {
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
    }

    .card {
        padding: 18px;
    }

    .card .number {
        font-size: 27px;
    }

    .section {
        padding: 17px;
    }

    .section-header {
        align-items: flex-start;
    }

    .filters {
        width: 100%;
    }

    .filters input,
    .filters select {
        width: 100%;
    }
}

@media (max-width: 400px) {
    .cards {
        grid-template-columns: 1fr;
    }
}

/* Enhanced statistic cards */

.card-top {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
}

.card-note {
    color: var(--muted);
    font-size: 12px;
    margin-top: 15px;
}

.stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 25px;
    font-weight: bold;
    flex-shrink: 0;
}

.gold-icon {
    color: #b88625;
    background: #f8f0dc;
}

.orange-icon {
    color: #d58b19;
    background: #fff2d8;
}

.green-icon {
    color: #198754;
    background: #d9f5e5;
}

.red-icon {
    color: #dc3545;
    background: #ffe0e0;
}

.card h3 {
    line-height: 1.5;
}

.card .number {
    margin-top: 8px;
}


</style>
</head>

<body>

<div class="dashboard">

    <!-- SIDEBAR -->

    <aside class="sidebar" id="sidebar">

        <div class="brand">
            GOLDEN <span>BITE.</span>
        </div>

        <button class="menu-toggle" id="menuToggle">
            ☰
        </button>

        <nav>

            <p class="menu-label">Main Menu</p>

            <a href="index.php" class="active">
                ▦ &nbsp; Dashboard
            </a>

     <a href="index.php#reservations">
    ▤ &nbsp; Reservations
</a>       

<?php if (($_SESSION["admin_role"] ?? "") === "owner"): ?>
    <a href="add_admin.php">
        + &nbsp; Add Administrator
    </a>
<?php endif; ?>

            <p class="menu-label" style="margin-top:30px;">
                Account
            </p>

            <a href="logout.php" class="logout-link">
                ↪ &nbsp; Logout
            </a>

        </nav>

    </aside>


    <!-- MAIN CONTENT -->

    <main class="main-content">

        <header class="topbar">

            <div>
                <h1>Dashboard Overview</h1>
                <p>Welcome back! Here's what's happening at Golden Bite.</p>
            </div>

            <div class="admin-profile">
                <span>●</span>
                <?= htmlspecialchars($_SESSION["admin_name"] ?? "Administrator") ?>
            </div>

        </header>


<!-- STATISTICS -->

<section class="cards">

    <div class="card total">
        <div class="card-top">
            <div>
                <h3>Total Reservations</h3>
                <div class="number"><?= (int)$total ?></div>
            </div>
            <div class="stat-icon gold-icon">▤</div>
        </div>
        <p class="card-note">All customer bookings</p>
    </div>

    <div class="card pending">
        <div class="card-top">
            <div>
                <h3>Pending Reservations</h3>
                <div class="number"><?= (int)$pending ?></div>
            </div>
            <div class="stat-icon orange-icon">◷</div>
        </div>
        <p class="card-note">Awaiting your response</p>
    </div>

    <div class="card confirmed">
        <div class="card-top">
            <div>
                <h3>Confirmed Reservations</h3>
                <div class="number"><?= (int)$confirmed ?></div>
            </div>
            <div class="stat-icon green-icon">✓</div>
        </div>
        <p class="card-note">Successfully confirmed</p>
    </div>

    <div class="card cancelled">
        <div class="card-top">
            <div>
                <h3>Cancelled Reservations</h3>
                <div class="number"><?= (int)$cancelled ?></div>
            </div>
            <div class="stat-icon red-icon">×</div>
        </div>
        <p class="card-note">Cancelled bookings</p>
    </div>

</section>


        <!-- RESERVATIONS -->

        <section class="section" id="reservations">

            <div class="section-header">

                <h2>Customer Reservations</h2>

                <div class="filters">

                    <input
                        type="search"
                        id="searchInput"
                        placeholder="Search customer..."
                    >

                    <select id="statusFilter">
                        <option value="">All statuses</option>
                        <option value="Pending">Pending</option>
                        <option value="Confirmed">Confirmed</option>
                        <option value="Cancelled">Cancelled</option>
                    </select>

                </div>

            </div>


            <div class="table-wrapper">

                <table id="reservationTable">

                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Customer</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Guests</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody>

                    <?php if ($result->num_rows > 0): ?>

                        <?php while ($row = $result->fetch_assoc()): ?>

                            <tr>

                              <td>
    <a href="reservation-details.php?id=<?= (int)$row["id"] ?>"
       style="color:#b88625;font-weight:bold;text-decoration:none;">
        #<?= (int)$row["id"] ?> — View
    </a>
</td>
                                <td>
    <a href="reservation-details.php?id=<?= (int)$row["id"] ?>"
       style="color:#b88625; font-weight:bold; text-decoration:none;">
        <?= htmlspecialchars($row["customer_name"]) ?>
    </a>
</td>
                                <td>
                                    <?= htmlspecialchars($row["email"]) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($row["phone"]) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($row["reservation_date"]) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($row["reservation_time"]) ?>
                                </td>

                                <td>
                                    <?= (int)$row["guests"] ?>
                                </td>

                                <td>
                                    <span class="status <?= htmlspecialchars($row["status"]) ?>">
                                        <?= htmlspecialchars($row["status"]) ?>
                                    </span>
                                </td>

                                


    
<td class="action-cell">
    <a class="action-view"
       href="reservation-details.php?id=<?= (int)$row["id"] ?>">
        View Details →
    </a>
</td>



                            </tr>

                        <?php endwhile; ?>

                    <?php else: ?>

                        <tr>
                            <td colspan="9" class="no-results">
                                No reservations have been received yet.
                            </td>
                        </tr>

                    <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </section>

    </main>

</div>


<script>

// Mobile sidebar
const menuToggle = document.querySelector("#menuToggle");
const sidebar = document.querySelector("#sidebar");

menuToggle.addEventListener("click", () => {
    sidebar.classList.toggle("open");
});

// Search reservations
const searchInput = document.querySelector("#searchInput");
const statusFilter = document.querySelector("#statusFilter");
const tableRows = document.querySelectorAll("#reservationTable tbody tr");

function filterReservations() {

    const search = searchInput.value.toLowerCase();
    const status = statusFilter.value;

    tableRows.forEach(row => {

        const text = row.textContent.toLowerCase();
        const rowStatus = row.querySelector(".status")?.textContent.trim() || "";

        const matchesSearch = text.includes(search);
        const matchesStatus = !status || rowStatus === status;

        row.style.display =
            matchesSearch && matchesStatus ? "" : "none";

    });
}

searchInput.addEventListener("input", filterReservations);
statusFilter.addEventListener("change", filterReservations);

</script>

</body>
</html>
