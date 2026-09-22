
<?php
include "../db.php";

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    exit("Invalid client ID.");
}

$sql = "SELECT * FROM clients WHERE client_id = ?";
$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

$get = mysqli_stmt_get_result($stmt);
$client = mysqli_fetch_assoc($get);

mysqli_stmt_close($stmt);

if (!$client) {
    exit("Client not found.");
}

$message = "";

if (isset($_POST['update'])) {
    $full_name = trim($_POST['full_name'] ?? "");
    $email = trim($_POST['email'] ?? "");
    $phone = trim($_POST['phone'] ?? "");
    $address = trim($_POST['address'] ?? "");

    if ($full_name === "" || $email === "") {
        $message = "Name and Email are required!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email address!";
    } else {
        $sql = "UPDATE clients
                SET full_name = ?,
                    email = ?,
                    phone = ?,
                    address = ?
                WHERE client_id = ?";

        $stmt = mysqli_prepare($conn, $sql);

        mysqli_stmt_bind_param(
            $stmt,
            "ssssi",
            $full_name,
            $email,
            $phone,
            $address,
            $id
        );

        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            header("Location: clients_list.php");
            exit;
        } else {
            $message = "Error updating client.";
        }

        mysqli_stmt_close($stmt);
    }

    // Keep submitted values visible if validation fails.
    $client['full_name'] = $full_name;
    $client['email'] = $email;
    $client['phone'] = $phone;
    $client['address'] = $address;
}
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Client</title>
</head>
<body>

<?php include "../nav.php"; ?>

<h2>Edit Client</h2>

<p style="color:red;">
    <?php echo htmlspecialchars($message); ?>
</p>

<form method="post">

    <label>Full Name*</label><br>
    <input type="text" name="full_name"
           required maxlength="150"
           value="<?php echo htmlspecialchars($client['full_name'] ?? ""); ?>">
    <br><br>

    <label>Email*</label><br>
    <input type="email" name="email"
           required maxlength="150"
           value="<?php echo htmlspecialchars($client['email'] ?? ""); ?>">
    <br><br>

    <label>Phone</label><br>
    <input type="text" name="phone"
           maxlength="50"
           value="<?php echo htmlspecialchars($client['phone'] ?? ""); ?>">
    <br><br>

    <label>Address</label><br>
    <input type="text" name="address"
           maxlength="255"
           value="<?php echo htmlspecialchars($client['address'] ?? ""); ?>">
    <br><br>

    <button type="submit" name="update">Update</button>

</form>

</body>
</html>