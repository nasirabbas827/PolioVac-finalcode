<?php
session_start();
include('config.php');

if (!isset($_SESSION["id"])) {
    header("location: ../index.php");
    exit;
}

if (!isset($_GET['center_id']) || !is_numeric($_GET['center_id'])) {
    echo "Invalid center ID.";
    exit;
}

$center_id = intval($_GET['center_id']);

// Get center info
$stmt = mysqli_prepare($conn, "SELECT name, location FROM vaccination_centers WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $center_id);
mysqli_stmt_execute($stmt);
$result_center = mysqli_stmt_get_result($stmt);
$center = mysqli_fetch_assoc($result_center);

if (!$center) {
    echo "Center not found.";
    exit;
}

// Get vaccination schedules for this center
$stmt_schedules = mysqli_prepare($conn, "SELECT * FROM vaccination_schedules WHERE center_id = ? ORDER BY vaccine_date ASC");
mysqli_stmt_bind_param($stmt_schedules, "i", $center_id);
mysqli_stmt_execute($stmt_schedules);
$result_schedules = mysqli_stmt_get_result($stmt_schedules);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Vaccination Schedules for <?= htmlspecialchars($center['name']) ?></title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">

</head>
<body>
<?php include('navbar.php'); ?>

<div class="container mt-5 mb-5">
    <h2>Vaccination Schedules for <?= htmlspecialchars($center['name']) ?> (<?= htmlspecialchars($center['location']) ?>)</h2>
    <a href="view_centers.php" class="btn btn-secondary mb-3">Back to Centers</a>

    <?php if (mysqli_num_rows($result_schedules) > 0): ?>
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Vaccine Date</th>
                    <th>Campaign Name</th>
                    <th>Notes</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($schedule = mysqli_fetch_assoc($result_schedules)): ?>
                    <tr>
                        <td><?= htmlspecialchars($schedule['vaccine_date']) ?></td>
                        <td><?= htmlspecialchars($schedule['campaign_name']) ?></td>
                        <td><?= nl2br(htmlspecialchars($schedule['notes'])) ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No schedules found for this center.</p>
    <?php endif; ?>
</div>
</body>
</html>
