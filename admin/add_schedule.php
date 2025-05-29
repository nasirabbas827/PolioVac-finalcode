<?php
include('config.php');
session_start();

if (!isset($_SESSION["usertype"]) || $_SESSION["usertype"] !== "admin") {
    header("Location: admin_login.php");
    exit;
}

$message = "";

// Fetch centers for dropdown
$centers = mysqli_query($conn, "SELECT id, name FROM vaccination_centers");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $center_id = $_POST["center_id"];
    $vaccine_date = $_POST["vaccine_date"];
    $campaign_name = $_POST["campaign_name"];
    $notes = $_POST["notes"];

    $sql = "INSERT INTO vaccination_schedules (center_id, vaccine_date, campaign_name, notes) VALUES (?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "isss", $center_id, $vaccine_date, $campaign_name, $notes);
    if (mysqli_stmt_execute($stmt)) {
        $message = "Vaccination schedule added successfully.";
    } else {
        $message = "Error adding schedule.";
    }
    mysqli_stmt_close($stmt);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Vaccination Schedule</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">

</head>
<body>

<?php include('admin_navbar.php'); ?>

<div class="container mt-5 mb-5">
        <div class="card mx-auto" style="max-width: 500px;">
        <div class="card-body">
    <h2>Add Vaccination Schedule</h2>
    <?php if ($message): ?>
        <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    <form method="post">
        <div class="form-group">
            <label>Center:</label>
            <select name="center_id" class="form-control" required>
                <option value="">Select Center</option>
                <?php while ($row = mysqli_fetch_assoc($centers)): ?>
                    <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['name']) ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="form-group">
            <label>Vaccine Date:</label>
            <input type="date" name="vaccine_date" required class="form-control" min="<?= date('Y-m-d'); ?>">
        </div>
        <div class="form-group">
            <label>Campaign Name:</label>
            <input type="text" name="campaign_name" required class="form-control">
        </div>
        <div class="form-group">
            <label>Notes:</label>
            <textarea name="notes" class="form-control" rows="3"></textarea>
        </div>
        <button type="submit" class="btn btn-success">Add Schedule</button>
        <a href="view_schedules.php" class="btn btn-secondary ml-2">View Schedules</a>
    </form>
</div>
</div>
</div>

</body>
</html>
