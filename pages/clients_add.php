
<?php
include "../db.php";

$message = "";

if (isset($_POST['save'])) {
    $full_name = trim($_POST['full_name'] ?? "");
    $email = trim($_POST['email'] ?? "");
    $phone = trim($_POST['phone'] ?? "");
    $address = trim($_POST['address'] ?? "");

    if ($full_name === "" || $email === "") {
        $message = "Name and Email are required!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email address!";
    } else {
        $sql = "INSERT INTO clients
                (full_name, email, phone, address)
                VALUES (?, ?, ?, ?)";

        $stmt = mysqli_prepare($conn, $sql);

        mysqli_stmt_bind_param(
            $stmt,
            "ssss",
            $full_name,
            $email,
            $phone,
            $address
        );

        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            header("Location: clients_list.php");
            exit;
        } else {
            $message = "Error saving client.";
        }

        mysqli_stmt_close($stmt);
    }
}
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Add Client</title>
</head>
<body>

<?php include "../nav.php"; ?>

<h2>Add Client</h2>

<p style="color:red;">
    <?php echo htmlspecialchars($message); ?>
</p>

<form method="post">
    <label>Full Name*</label><br>
    <input type="text" name="full_name"
           required maxlength="150"><br><br>

    <label>Email*</label><br>
    <input type="email" name="email"
           required maxlength="150"><br><br>

    <label>Phone</label><br>
    <input type="text" name="phone"
           maxlength="50"><br><br>

    <label>Address</label><br>
    <input type="text" name="address"
           maxlength="255"><br><br>

    <button type="submit" name="save">Save</button>
</form>

</body>
</html>