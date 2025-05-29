<?php
session_start();
include('config.php');

if (!isset($_SESSION["id"])) {
    header("location: ../index.php");
    exit;
}

// Handle update
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["update_id"])) {
    $update_id = $_POST["update_id"];
    $new_status = $_POST["status"];
    $new_date = $_POST["preferred_date"];

    $stmt = mysqli_prepare($conn, "UPDATE missed_vaccinations SET status = ?, preferred_date = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "ssi", $new_status, $new_date, $update_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

$sql = "
    SELECT mv.id, c.first_name, c.last_name, mv.reason, mv.preferred_date, mv.status, mv.created_at
    FROM missed_vaccinations mv
    JOIN children c ON mv.child_id = c.child_id
    ORDER BY mv.created_at DESC
";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>All Missed Vaccinations</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">

</head>
<body>
<?php include('navbar.php'); ?>

<div class="container mt-5 mb-5">
    <h2>Missed Vaccination Reports</h2>

    <?php if (mysqli_num_rows($result) > 0): ?>
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Child Name</th>
                    <th>Reason</th>
                    <th>Preferred Date</th>
                    <th>Status</th>
                    <th>Reported On</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <form method="post">
                            <input type="hidden" name="update_id" value="<?= $row['id'] ?>">
                            <td><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?></td>
                            <td><?= htmlspecialchars($row['reason']) ?></td>
                            <td>
                                <input type="date" name="preferred_date" class="form-control" value="<?= $row['preferred_date'] ?>">
                            </td>
                            <td>
                                <select name="status" class="form-control">
                                    <option value="pending" <?= $row['status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
                                    <option value="rescheduled" <?= $row['status'] == 'rescheduled' ? 'selected' : '' ?>>Rescheduled</option>
                                    <option value="resolved" <?= $row['status'] == 'resolved' ? 'selected' : '' ?>>Resolved</option>
                                </select>
                            </td>
                            <td><?= htmlspecialchars($row['created_at']) ?></td>
                            <td>
                                <button type="submit" class="btn btn-sm btn-success">Update</button>
                            </td>
                        </form>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No missed vaccination reports found.</p>
    <?php endif; ?>
</div>
</body>
</html>
