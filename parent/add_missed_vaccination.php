<?php
session_start();
include('config.php');

if (!isset($_SESSION["id"])) {
    header("location: ../index.php");
    exit;
}

$parent_id = $_SESSION["id"];
$message = "";

// Fetch children of the logged-in parent
$children_result = mysqli_query($conn, "SELECT child_id, first_name, last_name FROM children WHERE parent_id = $parent_id");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $child_id = $_POST["child_id"];
    $reason = trim($_POST["reason"]);
    $preferred_date = $_POST["preferred_date"];

    $stmt = mysqli_prepare($conn, "INSERT INTO missed_vaccinations (child_id, reported_by, reason, preferred_date, status) VALUES (?, ?, ?, ?, 'pending')");
    mysqli_stmt_bind_param($stmt, "iiss", $child_id, $parent_id, $reason, $preferred_date);

    if (mysqli_stmt_execute($stmt)) {
        $message = "Missed vaccination reported successfully.";
    } else {
        $message = "Error: " . mysqli_error($conn);
    }
    mysqli_stmt_close($stmt);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Report Missed Vaccination</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">

</head>
<body>
<?php include('navbar.php'); ?>

<div class="container mt-5 mb-5">
        <div class="card mx-auto" style="max-width: 500px;">
        <div class="card-body">
    <h2>Report Missed Vaccination</h2>

    <?php if ($message): ?>
        <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <form method="post">
        <div class="form-group">
            <label for="child_id">Select Child:</label>
            <select name="child_id" required class="form-control">
                <option value="">-- Select Child --</option>
                <?php while ($row = mysqli_fetch_assoc($children_result)): ?>
                    <option value="<?= $row['child_id'] ?>">
                        <?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="reason">Reason for Missing:</label>
            <input type="text" name="reason" required class="form-control">
        </div>

        <div class="form-group">
            <label for="preferred_date">Preferred Reschedule Date:</label>
            <input type="date" name="preferred_date" required class="form-control" min="<?= date('Y-m-d'); ?>">
        </div>

        <button type="submit" class="btn btn-success">Report Missed Vaccination</button>
        <a href="parent_dashboard.php" class="btn btn-secondary ml-2">Back</a>
    </form>
</div>
</div>
</div>
</body>
</html>
