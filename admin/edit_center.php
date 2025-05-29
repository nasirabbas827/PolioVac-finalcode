<?php
include('config.php');
session_start();

if (!isset($_SESSION["usertype"]) || $_SESSION["usertype"] !== "admin") {
    header("Location: admin_login.php");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: view_centers.php");
    exit;
}

$id = intval($_GET['id']);
$message = "";

// Fetch existing data
$sql = "SELECT * FROM vaccination_centers WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$center = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$center) {
    die("Center not found.");
}

// Update on POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = $_POST["name"];
    $location = $_POST["location"];
    $contact = $_POST["contact_number"];

    $sql = "UPDATE vaccination_centers SET name = ?, location = ?, contact_number = ? WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "sssi", $name, $location, $contact, $id);
    if (mysqli_stmt_execute($stmt)) {
        $message = "Center updated successfully.";
        header("Location: view_centers.php");
        exit;
    } else {
        $message = "Update failed.";
    }
    mysqli_stmt_close($stmt);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Vaccination Center</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">

</head>
<body>

<?php include('admin_navbar.php'); ?>

<div class="container mt-5 mb-5">
        <div class="card mx-auto" style="max-width: 500px;">
        <div class="card-body">
    <h2>Edit Center</h2>
    <?php if ($message): ?>
        <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    <form method="post">
        <div class="form-group">
            <label>Center Name:</label>
            <input type="text" name="name" required value="<?= htmlspecialchars($center['name']) ?>" class="form-control">
        </div>
        <div class="form-group">
            <label>Location:</label>
            <input type="text" name="location" required value="<?= htmlspecialchars($center['location']) ?>" class="form-control">
        </div>
        <div class="form-group">
            <label>Contact Number:</label>
            <input type="text" name="contact_number" required value="<?= htmlspecialchars($center['contact_number']) ?>" class="form-control">
        </div>
        <button type="submit" class="btn btn-primary">Update Center</button>
        <a href="view_centers.php" class="btn btn-secondary ml-2">Cancel</a>
    </form>
</div>
</div>
</div>

</body>
</html>
