
<?php
include "db.php";

$clientsResult = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS c FROM clients"
);

$servicesResult = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS c FROM services"
);

$bookingsResult = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS c FROM bookings"
);

$revenueResult = mysqli_query(
    $conn,
    "SELECT IFNULL(SUM(amount_paid), 0) AS s
     FROM payments"
);

if (
    !$clientsResult ||
    !$servicesResult ||
    !$bookingsResult ||
    !$revenueResult
) {
    die("Dashboard query failed: "
        . mysqli_error($conn));
}

$clients = mysqli_fetch_assoc($clientsResult)['c'];
$services = mysqli_fetch_assoc($servicesResult)['c'];
$bookings = mysqli_fetch_assoc($bookingsResult)['c'];

$revRow = mysqli_fetch_assoc($revenueResult);
$revenue = $revRow['s'];
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1">
    <title>Dashboard</title>
</head>
<body>

<?php include "nav.php"; ?>

<h2>Dashboard</h2>

<ul>
    <li>
        Total Clients:
        <b><?php echo (int)$clients; ?></b>
    </li>

    <li>
        Total Services:
        <b><?php echo (int)$services; ?></b>
    </li>

    <li>
        Total Bookings:
        <b><?php echo (int)$bookings; ?></b>
    </li>

    <li>
        Total Revenue:
        <b>₱<?php echo number_format((float)$revenue, 2); ?></b>
    </li>
</ul>

<p>
    Quick links:
    <a href="/assessment_beginner/pages/clients_add.php">
        Add Client
    </a>
    |
    <a href="/assessment_beginner/pages/bookings_create.php">
        Create Booking
    </a>
</p>

</body>
</html>