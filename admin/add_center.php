<?php
include('config.php');
session_start();

if (!isset($_SESSION["usertype"]) || $_SESSION["usertype"] !== "admin") {
    header("Location: admin_login.php");
    exit;
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"]);
    $location = trim($_POST["location"]);
    $contact_number = trim($_POST["contact_number"]);

    if (!empty($name) && !empty($location) && !empty($contact_number)) {
        $sql = "INSERT INTO vaccination_centers (name, location, contact_number) VALUES (?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "sss", $name, $location, $contact_number);
        if (mysqli_stmt_execute($stmt)) {
            $message = "Vaccination center added successfully.";
        } else {
            $message = "Failed to add center: " . mysqli_error($conn);
        }
        mysqli_stmt_close($stmt);
    } else {
        $message = "All fields are required.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Vaccination Center</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<?php include('admin_navbar.php'); ?>

<div class="container mt-5 mb-5">
    <div class="card mx-auto" style="max-width: 500px;">
        <div class="card-body">
            <h2 class="mb-4">Add Vaccination Center</h2>
            <?php if ($message): ?>
                <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>
            <form method="post">
                <div class="form-group">
                    <label>Center Name:</label>
                    <input type="text" name="name" required class="form-control">
                </div>
                <div class="form-group">
                    <label>Location:</label>
                    <input type="text" name="location" required class="form-control">
                </div>
                <div class="form-group">
                    <label>Contact Number:</label>
                    <input type="text" name="contact_number" required class="form-control">
                </div>
                <button type="submit" class="btn btn-success">Add Center</button>
                <a class="btn btn-secondary ml-2" href="view_centers.php">View Centers</a>
            </form>
        </div>
    </div>
</div>

</body>
</html>
