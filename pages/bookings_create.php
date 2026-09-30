<?php
include "../db.php";

$clients = mysqli_query(
    $conn,
    "SELECT * FROM clients ORDER BY full_name ASC"
);

$services = mysqli_query(
    $conn,
    "SELECT * FROM services
     WHERE is_active = 1
     ORDER BY service_name ASC"
);

$message = "";

if (isset($_POST['create'])) {

    $client_id = filter_input(
        INPUT_POST,
        'client_id',
        FILTER_VALIDATE_INT
    );

    $service_id = filter_input(
        INPUT_POST,
        'service_id',
        FILTER_VALIDATE_INT
    );

    $booking_date = $_POST['booking_date'] ?? "";

    $hours = filter_input(
        INPUT_POST,
        'hours',
        FILTER_VALIDATE_INT
    );

    if (!$client_id || !$service_id) {
        $message = "Please select a client and service.";
    } elseif (empty($booking_date)) {
        $message = "Please select a booking date.";
    } elseif (!$hours || $hours < 1) {
        $message = "Hours must be at least 1.";
    } else {

        // Get service hourly rate
        $stmt = mysqli_prepare(
            $conn,
            "SELECT hourly_rate
             FROM services
             WHERE service_id = ?
             AND is_active = 1"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "i",
            $service_id
        );

        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);
        $service = mysqli_fetch_assoc($result);

        mysqli_stmt_close($stmt);

        if (!$service) {

            $message = "Selected service was not found.";

        } else {

            $rate = (float)$service['hourly_rate'];

            // Calculate total cost
            $total = $rate * $hours;

            // Insert booking
            $stmt = mysqli_prepare(
                $conn,
                "INSERT INTO bookings
                (
                    client_id,
                    service_id,
                    booking_date,
                    hours,
                    hourly_rate_snapshot,
                    total_cost,
                    status
                )
                VALUES (?, ?, ?, ?, ?, ?, 'PENDING')"
            );

            mysqli_stmt_bind_param(
                $stmt,
                "iisidd",
                $client_id,
                $service_id,
                $booking_date,
                $hours,
                $rate,
                $total
            );

            if (mysqli_stmt_execute($stmt)) {

                mysqli_stmt_close($stmt);

                header("Location: bookings_list.php");
                exit;

            } else {

                $message = "Error creating booking.";
            }

            mysqli_stmt_close($stmt);
        }
    }
}
?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1">

    <title>Create Booking</title>
</head>

<body>

<?php include "../nav.php"; ?>

<h2>Create Booking</h2>

<?php if (!empty($message)) { ?>

    <p style="color:red;">
        <?php echo htmlspecialchars($message); ?>
    </p>

<?php } ?>

<form method="post">

    <label>Client</label><br>

    <select name="client_id" required>

        <option value="">-- Select Client --</option>

        <?php while ($c = mysqli_fetch_assoc($clients)) { ?>

            <option value="<?php echo (int)$c['client_id']; ?>">
                <?php echo htmlspecialchars($c['full_name']); ?>
            </option>

        <?php } ?>

    </select>

    <br><br>


    <label>Service</label><br>

    <select name="service_id" required>

        <option value="">-- Select Service --</option>

        <?php while ($s = mysqli_fetch_assoc($services)) { ?>

            <option value="<?php echo (int)$s['service_id']; ?>">

                <?php echo htmlspecialchars($s['service_name']); ?>

                (₱<?php echo number_format(
                    (float)$s['hourly_rate'],
                    2
                ); ?>/hr)

            </option>

        <?php } ?>

    </select>

    <br><br>


    <label>Date</label><br>

    <input
        type="date"
        name="booking_date"
        required
    >

    <br><br>


    <label>Hours</label><br>

    <input
        type="number"
        name="hours"
        min="1"
        value="1"
        required
    >

    <br><br>


    <button type="submit" name="create">
        Create Booking
    </button>

</form>

</body>
</html>