<?php
include('config.php');
session_start();

if (!isset($_SESSION["usertype"]) || $_SESSION["usertype"] !== "admin") {
    header("Location: admin_login.php");
    exit;
}

if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    mysqli_query($conn, "DELETE FROM vaccination_schedules WHERE id = $id");
    header("Location: view_schedules.php?deleted=1");
    exit;
}

$schedules = mysqli_query($conn, "
    SELECT vaccination_schedules.*, vaccination_centers.name AS center_name
    FROM vaccination_schedules
    JOIN vaccination_centers ON vaccination_schedules.center_id = vaccination_centers.id
    ORDER BY vaccine_date ASC
");

?>

<!DOCTYPE html>
<html>
<head>
    <title>View Vaccination Schedules</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">

</head>
<body>

<?php include('admin_navbar.php'); ?>

<div class="container mt-5 mb-5">
    <h2>Vaccination Schedules</h2>
    <?php if (isset($_GET['deleted'])): ?>
        <div class="alert alert-success">Schedule deleted successfully.</div>
    <?php endif; ?>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>#</th>
                <th>Center</th>
                <th>Date</th>
                <th>Campaign</th>
                <th>Notes</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php $count = 1; while ($row = mysqli_fetch_assoc($schedules)): ?>
            <tr>
                <td><?= $count++ ?></td>
                <td><?= htmlspecialchars($row['center_name']) ?></td>
                <td><?= $row['vaccine_date'] ?></td>
                <td><?= htmlspecialchars($row['campaign_name']) ?></td>
                <td><?= htmlspecialchars($row['notes']) ?></td>
                <td>
                    <a href="edit_schedule.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-primary">Edit</a>
                    <a href="?delete=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this schedule?');">Delete</a>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>

</body>
</html>
