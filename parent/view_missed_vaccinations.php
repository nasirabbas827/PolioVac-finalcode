<?php
session_start();
include('config.php');

if (!isset($_SESSION["id"])) {
    header("location: ../index.php");
    exit;
}

$parent_id = $_SESSION["id"];

$sql = "
    SELECT mv.id, c.first_name, c.last_name, mv.reason, mv.preferred_date, mv.status, mv.created_at
    FROM missed_vaccinations mv
    JOIN children c ON mv.child_id = c.child_id
    WHERE mv.reported_by = ?
    ORDER BY mv.created_at DESC
";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $parent_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Missed Vaccinations</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">

</head>
<body>
<?php include('navbar.php'); ?>

<div class="container mt-5 mb-5">
    <h2>My Reported Missed Vaccinations</h2>

    <?php if (mysqli_num_rows($result) > 0): ?>
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Child Name</th>
                    <th>Reason</th>
                    <th>Preferred Date</th>
                    <th>Status</th>
                    <th>Reported On</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?></td>
                        <td><?= htmlspecialchars($row['reason']) ?></td>
                        <td><?= htmlspecialchars($row['preferred_date']) ?></td>
                        <td><span class="badge badge-<?= $row['status'] === 'pending' ? 'warning' : ($row['status'] === 'rescheduled' ? 'info' : 'success') ?>">
                            <?= ucfirst($row['status']) ?>
                        </span></td>
                        <td><?= htmlspecialchars($row['created_at']) ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No missed vaccinations reported yet.</p>
    <?php endif; ?>
</div>
</body>
</html>
