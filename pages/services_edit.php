<?php
include "../db.php";

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    exit("Invalid service ID.");
}

// Get service
$stmt = mysqli_prepare(
    $conn,
    "SELECT * FROM services WHERE service_id = ?"
);

mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$service = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

if (!$service) {
    exit("Service not found.");
}

// Update service
if (isset($_POST['update'])) {

    $name = trim($_POST['service_name'] ?? "");
    $desc = trim($_POST['description'] ?? "");
    $rate = $_POST['hourly_rate'] ?? "";
    $active = isset($_POST['is_active'])
        ? (int)$_POST['is_active']
        : 0;

    if ($name === "") {
        $message = "Service name is required!";
    } elseif (!is_numeric($rate) || $rate < 0) {
        $message = "Please enter a valid hourly rate!";
    } else {

        $rate = (float)$rate;

        $stmt = mysqli_prepare(
            $conn,
            "UPDATE services
             SET service_name = ?,
                 description = ?,
                 hourly_rate = ?,
                 is_active = ?
             WHERE service_id = ?"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "ssdii",
            $name,
            $desc,
            $rate,
            $active,
            $id
        );

        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);

            header("Location: services_list.php");
            exit;
        } else {
            $message = "Error updating service.";
        }

        mysqli_stmt_close($stmt);
    }

    // Keep submitted values if there is an error
    $service['service_name'] = $name;
    $service['description'] = $desc;
    $service['hourly_rate'] = $rate;
    $service['is_active'] = $active;
}
?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Service</title>
</head>

<body>

<?php include "../nav.php"; ?>

<h2>Edit Service</h2>

<?php if (!empty($message)) { ?>
    <p style="color:red;">
        <?php echo htmlspecialchars($message); ?>
    </p>
<?php } ?>

<form method="post">

    <label>Service Name</label><br>

    <input
        type="text"
        name="service_name"
        value="<?php echo htmlspecialchars($service['service_name']); ?>"
        required
    >

    <br><br>


    <label>Description</label><br>

    <textarea
        name="description"
        rows="4"
        cols="40"
    ><?php echo htmlspecialchars($service['description'] ?? ""); ?></textarea>

    <br><br>


    <label>Hourly Rate</label><br>

    <input
        type="number"
        name="hourly_rate"
        step="0.01"
        min="0"
        value="<?php echo htmlspecialchars($service['hourly_rate']); ?>"
        required
    >

    <br><br>


    <label>Active</label><br>

    <select name="is_active">

        <option
            value="1"
            <?php if ($service['is_active'] == 1) echo "selected"; ?>
        >
            Yes
        </option>

        <option
            value="0"
            <?php if ($service['is_active'] == 0) echo "selected"; ?>
        >
            No
        </option>

    </select>

    <br><br>

    <button type="submit" name="update">
        Update
    </button>

</form>

</body>
</html>