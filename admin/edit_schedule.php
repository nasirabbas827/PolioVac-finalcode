<?php
include('config.php');
session_start();

if (!isset($_SESSION["usertype"]) || $_SESSION["usertype"] !== "admin") {
    header("Location: admin_login.php");
    exit;
}

$id = intval($_GET['id']);
$message = "";

// Get centers
$centers = mysqli_query($conn, "SELECT id, name FROM vaccination_centers");

// Get current schedule data
$result = mysqli_query($conn, "SELECT * FROM vaccination_schedules WHERE id = $id");
$data = mysqli_fetch_assoc($result);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $center_id = $_POST["center_id"];
    $vaccine_date = $_POST["vaccine_date"];
    $campaign_name = $_POST["campaign_name"];
    $notes = $_POST["notes"];

    $sql = "UPDATE vaccination_schedules SET center_id=?, vaccine_date=?, campaign_name=?, notes=? WHERE id=?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "isssi", $center_id, $vaccine_date, $campaign_name, $notes, $id);

    if (mysqli_stmt_execute($stmt)) {
        $message = "Schedule updated successfully.";
        header("Location: view_schedules.php?updated=1");
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
    <title>Edit Schedule</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">

</head>
<body>

<?php include('admin_navbar.php'); ?>

<div class="container mt-5 mb-5">
        <div class="card mx-auto" style="max-width: 500px;">
        <div class="card-body">
    <h2>Edit Vaccination Schedule</h2>
    <?php if ($message): ?>
        <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    <form method="post">
        <div class="form-group">
            <label>Center:</label>
            <select name="center_id" class="form-control" required>
                <?php while ($center = mysqli_fetch_assoc($centers)): ?>
                    <option value="<?= $center['id'] ?>" <?= ($center['id'] == $data['center_id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($center['name']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="form-group">
            <label>Vaccine Date:</label>
            <input type="date" name="vaccine_date" required class="form-control" value="<?= $data['vaccine_date'] ?>" min="<?= date('Y-m-d'); ?>">
        </div>
        <div class="form-group">
            <label>Campaign Name:</label>
            <input type="text" name="campaign_name" required class="form-control" value="<?= htmlspecialchars($data['campaign_name']) ?>">
        </div>
        <div class="form-group">
            <label>Notes:</label>
            <textarea name="notes" class="form-control" rows="3"><?= htmlspecialchars($data['notes']) ?></textarea>
        </div>
        <button type="submit" class="btn btn-success">Update Schedule</button>
        <a href="view_schedules.php" class="btn btn-secondary ml-2">Cancel</a>
    </form>
</div>
</div>
</div>

</body>
</html>
